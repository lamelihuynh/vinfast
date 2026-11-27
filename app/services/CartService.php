<?php

final class CartService
{
    /** Returns only an active variant that belongs to the product. */
    public static function variant(array $product, string $id): ?array
    {
        if (empty($product['is_active'])) {
            return null;
        }
        foreach ($product['variants'] as $variant) {
            if ((string)$variant['variantId'] === $id && !empty($variant['isActive'])) {
                return $variant;
            }
        }
        return null;
    }

    public static function add(int $userId, string $productId, string $variantId, int $qty): void
    {
        self::validateQuantity($qty);
        $product = Product::getByIds([$productId], false)[$productId] ?? [];
        $variant = self::variant($product, $variantId);
        if (empty($product['is_accessory']) || !$variant) {
            throw new InvalidArgumentException('Phiên bản phụ kiện không còn bán.');
        }
        $available = InventoryService::byProduct($productId)[$variantId]['available'] ?? 0;
        if ($qty > $available) {
            throw new OutOfStockException('Không đủ hàng cho số lượng đã chọn.');
        }
        Db::transaction(static function (PDO $pdo) use ($userId, $productId, $variantId, $qty, $variant): void {
            $cartId = Cart::lock($pdo, $userId, true);
            Cart::add($pdo, $cartId, $productId, $variantId, $qty, (int)$variant['price']);
        });
        ActivityLog::record('add_to_cart', $productId, ['customData' => ['variantId' => $variantId, 'qty' => $qty]]);
    }

    public static function edit(int $userId, int $itemId, ?int $qty): void
    {
        if ($qty !== null) {
            self::validateQuantity($qty);
        }
        $productId = Db::transaction(static function (PDO $pdo) use ($userId, $itemId, $qty): ?string {
            $cartId = Cart::lock($pdo, $userId);
            return $cartId === null ? null : Cart::edit($pdo, $cartId, $itemId, $qty);
        });
        if ($productId === null) {
            throw new InvalidArgumentException('Không tìm thấy món này trong giỏ hàng.');
        }
        if ($qty === null) {
            ActivityLog::record('remove_from_cart', $productId);
        }
    }

    public static function summary(int $userId): array
    {
        $rows = Cart::items(Db::pg(), $userId);
        $products = Product::getByIds(array_column($rows, 'product_id'), false);
        $items = [];
        $subtotal = 0;
        $ready = $rows !== [];
        foreach ($rows as $row) {
            $product = $products[$row['product_id']] ?? [];
            $variant = self::variant($product, $row['variant_id']);
            $valid = !empty($product['is_accessory']) && $variant !== null;
            $price = $valid ? (int)$variant['price'] : (int)$row['unit_price'];
            $line = [
                'id' => (int)$row['id'],
                'product_id' => $row['product_id'],
                'variant_id' => $row['variant_id'],
                'product_name' => $product['name'] ?? ('Sản phẩm #' . $row['product_id']),
                'variant_name' => $variant['name'] ?? $row['variant_id'],
                'quantity' => (int)$row['quantity'],
                'unit_price' => $price,
                'available' => $valid,
            ];
            $subtotal += $price * $line['quantity'];
            $ready = $ready && $valid;
            $items[] = $line;
        }
        return [
            'items' => $items, 'subtotal' => $subtotal, 'ready' => $ready,
            'cart_version' => Cart::fingerprint($rows),
            'quote_version' => Cart::fingerprint($items),
        ];
    }

    private static function validateQuantity(int $qty): void
    {
        if ($qty < 1 || $qty > 99) {
            throw new InvalidArgumentException('Số lượng phải từ 1 đến 99.');
        }
    }
}
