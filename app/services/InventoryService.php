<?php

/**
 * app/services/InventoryService.php — Tồn kho (PostgreSQL: inventory)
 * Chủ file: T5 (Concurrency). Phase 0.5 chỉ dựng phần đọc + tạo dòng.
 *
 * Mỗi (product_id, variant_id) có 1 dòng. Số bán được = stock - reserved.
 *
 * Hợp đồng T4 <-> T5: decrement() — T4 gọi trong transaction của OrderService; T5 thay phần thân
 * bằng 4 chiến lược (pessimistic | optimistic | atomic | naive), chọn qua tham số hoặc env INVENTORY_STRATEGY.
 */
class InventoryService
{
    public const STRATEGIES = ['pessimistic', 'optimistic', 'atomic', 'naive'];

    /**
     * Trừ $qty khỏi tồn kho của 1 phiên bản. Gọi BÊN TRONG transaction PG của người gọi ($pdo).
     *
     * @throws OutOfStockException       không đủ hàng (stock - reserved < qty) hoặc vi phạm CHECK 23514
     * @throws ConcurrentUpdateException hết lượt retry / xung đột (40001, 40P01, 55P03)
     *
     * TODO(T5): hiện mọi strategy đều chạy bản 'atomic' (1 câu UPDATE có điều kiện) để T4 làm luồng
     * checkout ngay được. T5 hiện thực pessimistic (SELECT ... FOR UPDATE), optimistic (cột version +
     * retry có backoff), naive (đọc rồi ghi, để minh họa lost update) và giữ nguyên chữ ký hàm.
     */
    public static function decrement(PDO $pdo, string $productId, string $variantId, int $qty, ?string $strategy = null): void
    {
        if ($qty <= 0) {
            throw new InvalidArgumentException('qty phải > 0');
        }
        $strategy = $strategy ?? Db::env('INVENTORY_STRATEGY', 'atomic');
        if (!in_array($strategy, self::STRATEGIES, true)) {
            throw new InvalidArgumentException("Strategy không hợp lệ: {$strategy}");
        }

        try {
            $stmt = $pdo->prepare('UPDATE inventory SET stock = stock - :qty, version = version + 1
                WHERE product_id = :pid AND variant_id = :vid AND stock - reserved >= :qty');
            $stmt->execute([':qty' => $qty, ':pid' => $productId, ':vid' => $variantId]);
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

    /**
     * Tạo dòng tồn kho cho các variant (nếu chưa có) và đặt stock.
     * stock không bao giờ đặt thấp hơn reserved (CHECK reserved BETWEEN 0 AND stock).
     *
     * @param array<string,int|null> $stockByVariant variantId => stock (null = giữ nguyên, dòng mới = 0)
     */
    public static function ensureRows(string $productId, array $stockByVariant): void
    {
        $pdo = Db::pg();
        $insert = $pdo->prepare('INSERT INTO inventory (product_id, variant_id, stock) VALUES (?, ?, ?)
            ON CONFLICT (product_id, variant_id) DO NOTHING');
        $update = $pdo->prepare('UPDATE inventory SET stock = GREATEST(?, reserved), version = version + 1
            WHERE product_id = ? AND variant_id = ? AND stock <> GREATEST(?, reserved)');

        foreach ($stockByVariant as $variantId => $stock) {
            $variantId = (string)$variantId;
            $insert->execute([$productId, $variantId, max(0, (int)$stock)]);
            if ($stock !== null) {
                $stock = max(0, (int)$stock);
                $update->execute([$stock, $productId, $variantId, $stock]);
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

    /** @return array<string,array{stock:int,reserved:int,available:int}> variantId => số liệu */
    public static function byProduct(string $productId): array
    {
        $stmt = Db::pg()->prepare('SELECT variant_id, stock, reserved FROM inventory WHERE product_id = ?');
        $stmt->execute([$productId]);
        $out = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $out[(string)$row['variant_id']] = [
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
