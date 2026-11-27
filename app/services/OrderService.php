<?php

/** One PostgreSQL transaction owns order, stock, coupon and cart writes. */
final class OrderService
{
    public static function placeFromCart(int $userId, string $key, string $expectedQuote, string $coupon, array $note): int
    {
        self::validateKey($key);
        $previous = self::existing(Db::pg(), $userId, $key, 'purchase');
        if ($previous !== null) {
            return $previous;
        }
        // Read the catalog before the transaction; only the PG cart is locked below.
        $cart = CartService::summary($userId);
        if (!$cart['ready'] || !hash_equals($cart['quote_version'], $expectedQuote)) {
            // A concurrent copy of this same submission may have cleared the cart.
            $previous = self::existing(Db::pg(), $userId, $key, 'purchase');
            if ($previous !== null) {
                return $previous;
            }
            throw new InvalidArgumentException('Giỏ hàng hoặc giá đã thay đổi. Vui lòng kiểm tra lại trước khi đặt.');
        }
        return self::place($userId, $cart['items'], [
            'type' => 'purchase', 'key' => $key, 'note' => $note,
            'coupon' => trim($coupon), 'cart_version' => $cart['cart_version'],
        ]);
    }

    public static function placeDeposit(int $userId, array $product, string $variantId, array $note, string $key): int
    {
        self::validateKey($key);
        $previous = self::existing(Db::pg(), $userId, $key, 'deposit');
        if ($previous !== null) {
            return $previous;
        }
        $variant = CartService::variant($product, $variantId);
        if (!$variant || !empty($product['is_accessory'])) {
            throw new InvalidArgumentException('Phiên bản xe không còn bán.');
        }
        $amount = max(0, (int)($product['specs']['deposit_amount'] ?? 15000000));
        $note['deposit_amount'] = $note['deposit_base_amount'] = $amount;
        $note['variant_name'] = (string)$variant['name'];
        return self::place($userId, [[
            'product_id' => (string)$product['product_id'],
            'variant_id' => $variantId,
            'product_name' => $product['name'],
            'variant_name' => $variant['name'],
            'color_code' => ($note['color']['code'] ?? '') ?: null,
            'color_name' => ($note['color']['name'] ?? '') ?: null,
            'quantity' => 1,
            'unit_price' => $amount,
        ]], ['type' => 'deposit', 'key' => $key, 'note' => $note]);
    }

    private static function place(int $userId, array $items, array $opts): int
    {
        // Match the T5 inventory lock order for every checkout.
        usort($items, static fn(array $a, array $b): int =>
            strcmp($a['product_id'], $b['product_id']) ?: strcmp($a['variant_id'], $b['variant_id']));
        $subtotal = array_sum(array_map(static fn(array $i): int => $i['unit_price'] * $i['quantity'], $items));
        if ($subtotal < 0 || $subtotal > 999999999999999) {
            throw new InvalidArgumentException('Giá trị đơn hàng vượt giới hạn.');
        }

        for ($attempt = 1; ; $attempt++) {
            try {
                [$id, $created, $couponId] = Db::transaction(static function (PDO $pdo) use ($userId, $items, $opts, $subtotal): array {
                    $type = $opts['type'];
                    $cartId = $type === 'purchase' ? Cart::lock($pdo, $userId) : null;
                    $previous = self::existing($pdo, $userId, $opts['key'], $type);
                    if ($previous !== null) {
                        return [$previous, false, null];
                    }
                    if ($type === 'purchase' && ($cartId === null
                        || Cart::fingerprint(Cart::items($pdo, $userId)) !== $opts['cart_version'])) {
                        throw new InvalidArgumentException('Giỏ hàng đã thay đổi. Vui lòng kiểm tra lại.');
                    }

                    [$couponId, $discount] = CouponService::quote($pdo, $opts['coupon'] ?? '', $userId, $subtotal);
                    $payment = $type === 'deposit' ? 'pending_verify' : 'unpaid';
                    $note = $opts['note'];
                    $note['payment_status'] = $payment;
                    $stmt = $pdo->prepare('INSERT INTO orders (user_id, type, payment_status, subtotal_amount,
                        discount_amount, total_amount, coupon_id, note, idempotency_key)
                        VALUES (?, ?, ?, ?, ?, ?, ?, CAST(? AS jsonb), ?)
                        ON CONFLICT (user_id, idempotency_key) WHERE idempotency_key IS NOT NULL
                        DO NOTHING RETURNING id');
                    $stmt->execute([$userId, $type, $payment, $subtotal, $discount, $subtotal - $discount,
                        $couponId, json_encode($note, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE), $opts['key']]);
                    $id = $stmt->fetchColumn();
                    if ($id === false) {
                        return [self::existing($pdo, $userId, $opts['key'], $type)
                            ?? throw new RuntimeException('Không tìm thấy đơn đã ghi.'), false, null];
                    }
                    $id = (int)$id;
                    self::failPoint('after_order');
                    $line = $pdo->prepare('INSERT INTO order_items (order_id, product_id, variant_id,
                        product_name_snapshot, variant_name_snapshot, color_code, color_name_snapshot,
                        unit_price, quantity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
                    foreach ($items as $i) {
                        $line->execute([$id, $i['product_id'], $i['variant_id'], $i['product_name'], $i['variant_name'],
                            $i['color_code'] ?? null, $i['color_name'] ?? null, $i['unit_price'], $i['quantity']]);
                    }
                    self::failPoint('after_items');
                    foreach ($items as $i) {
                        InventoryService::decrement($pdo, $i['product_id'], $i['variant_id'], $i['quantity']);
                    }
                    self::failPoint('after_inventory');
                    if ($couponId !== null) {
                        CouponService::redeem($pdo, $couponId, $userId, $id, $discount);
                    }
                    if ($cartId !== null) {
                        Cart::clear($pdo, $cartId);
                    }
                    self::failPoint('after_cart_delete');
                    return [$id, true, $couponId];
                }, 'READ COMMITTED');
                break;
            } catch (ConcurrentUpdateException $e) {
                if ($attempt === 3) {
                    throw $e;
                }
                usleep(random_int(5000, 30000));
            }
        }
        if ($created) {
            self::afterCommit($id, $items, $opts['type'], $couponId);
        }
        return $id;
    }

    private static function existing(PDO $pdo, int $userId, string $key, string $type): ?int
    {
        $stmt = $pdo->prepare('SELECT id, type FROM orders WHERE user_id = ? AND idempotency_key = ?');
        $stmt->execute([$userId, $key]);
        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }
        if ($row['type'] !== $type) {
            throw new InvalidArgumentException('Biểu mẫu không còn hợp lệ. Vui lòng mở lại trang.');
        }
        return (int)$row['id'];
    }

    private static function validateKey(string $key): void
    {
        if (!preg_match('/\A[0-9a-f]{32}\z/', $key)) {
            throw new InvalidArgumentException('Biểu mẫu không còn hợp lệ. Vui lòng mở lại trang.');
        }
    }

    private static function failPoint(string $point): void
    {
        if (PHP_SAPI === 'cli' && in_array(Db::env('APP_ENV'), ['local', 'bench'], true)
            && Db::env('FAIL_AT') === $point) {
            throw new RuntimeException("FAIL_AT: {$point}");
        }
    }

    private static function afterCommit(int $orderId, array $items, string $type, ?int $couponId): void
    {
        try {
            $products = Db::mongo()->selectCollection('products');
            foreach ($items as $item) {
                $products->updateOne(['productID' => $item['product_id']],
                    ['$inc' => ['totalSold' => $item['quantity']]]);
            }
        } catch (Throwable $e) {
            error_log('OrderService: MongoDB totalSold chưa đồng bộ cho đơn #' . $orderId);
        }
        ActivityLog::record($type === 'deposit' ? 'deposit' : 'place_order', (string)$orderId);
        if ($couponId !== null) {
            ActivityLog::record('apply_coupon', (string)$orderId, ['customData' => ['couponId' => $couponId]]);
        }
    }
}
