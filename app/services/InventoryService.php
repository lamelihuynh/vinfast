<?php

/** Tồn kho PostgreSQL: số lượng có thể bán là stock - reserved. */
class InventoryService
{
    /** Luôn dùng atomic trong transaction của T4. $strategy chỉ giữ để tương thích chữ ký cũ. */
    public static function decrement(PDO $pdo, string $productId, string $variantId, int $qty, ?string $strategy = null): void
    {
        if ($qty <= 0) {
            throw new InvalidArgumentException('qty phải > 0');
        }
        if (!$pdo->inTransaction()) {
            throw new LogicException('decrement() phải chạy trong transaction tạo đơn.');
        }

        try {
            $stmt = $pdo->prepare('UPDATE inventory SET stock = stock - :qty, version = version + 1
                WHERE product_id = :pid AND variant_id = :vid AND stock - reserved >= :required');
            $stmt->execute([':qty' => $qty, ':required' => $qty, ':pid' => $productId, ':vid' => $variantId]);
        } catch (PDOException $e) {
            $state = (string)($e->errorInfo[0] ?? $e->getCode());
            if ($state === '23514') {
                throw new OutOfStockException("Hết hàng: {$variantId}", 0, $e);
            }
            if (in_array($state, ['40001', '40P01', '55P03'], true)) {
                throw new ConcurrentUpdateException($e->getMessage(), 0, $e);
            }
            throw $e;
        }

        if ($stmt->rowCount() === 0) {
            throw new OutOfStockException("Không đủ hàng cho {$variantId} (cần {$qty})");
        }
    }

    /** Chỉ cho phép đặt lại tồn kho nếu Admin đang sửa đúng phiên bản đã đọc. */
    public static function setStock(string $productId, string $variantId, int $newStock, int $expectedVersion): bool
    {
        $stmt = Db::pg()->prepare('UPDATE inventory SET stock = ?, version = version + 1
            WHERE product_id = ? AND variant_id = ? AND version = ? AND reserved <= ?');
        $stmt->execute([$newStock, $productId, $variantId, $expectedVersion, $newStock]);
        return $stmt->rowCount() === 1;
    }

    /** Tạo các dòng tồn kho mới; không cập nhật SKU đã tồn tại bằng số tồn thiếu version. */
    public static function ensureRows(string $productId, array $stockByVariant): void
    {
        $insert = Db::pg()->prepare('INSERT INTO inventory (product_id, variant_id, stock) VALUES (?, ?, ?)
            ON CONFLICT (product_id, variant_id) DO NOTHING');
        foreach ($stockByVariant as $variantId => $stock) {
            $insert->execute([$productId, (string)$variantId, max(0, (int)$stock)]);
            if ($insert->rowCount() !== 1) {
                throw new ConcurrentUpdateException("SKU đã tồn tại: {$variantId}");
            }
        }
    }

    /** Xóa dòng tồn kho của các variant không còn tồn tại (chỉ khi không có hàng đang giữ). */
    public static function removeRowsExcept(string $productId, array $keepVariantIds): void
    {
        $keep = array_values(array_map('strval', $keepVariantIds));
        $sql = 'DELETE FROM inventory WHERE product_id = ? AND reserved = 0';
        $params = [$productId];
        if ($keep) {
            $sql .= ' AND variant_id <> ALL (CAST(? AS text[]))';
            $params[] = '{' . implode(',', array_map(static fn($v) => '"' . addcslashes($v, '"\\') . '"', $keep)) . '}';
        }
        Db::pg()->prepare($sql)->execute($params);
    }

    /** @return array<string,array{stock:int,reserved:int,available:int,version:int}> variantId => số liệu */
    public static function byProduct(string $productId): array
    {
        $stmt = Db::pg()->prepare('SELECT variant_id, stock, reserved, version FROM inventory WHERE product_id = ?');
        $stmt->execute([$productId]);
        $out = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $out[(string)$row['variant_id']] = [
                'version' => (int)$row['version'],
                'stock' => (int)$row['stock'],
                'reserved' => (int)$row['reserved'],
                'available' => (int)$row['stock'] - (int)$row['reserved'],
            ];
        }
        return $out;
    }

    public static function deleteProduct(string $productId): void
    {
        Db::pg()->prepare('DELETE FROM inventory WHERE product_id = ?')->execute([$productId]);
    }
}
