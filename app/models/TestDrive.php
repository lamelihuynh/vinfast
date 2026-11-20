<?php
/**
 * app/models/TestDrive.php — Test Drive Registration Model
 * Table: test_drives (PostgreSQL)
 * Owner: Tang Vu (Member 1)
 * Used by: ContactController (frontend submit), ContactAdminController (admin view)
 *
 * Columns: id, user_id, name, email, phone, product_id (VARCHAR, Mongo productID),
 *          variant_id, product_name_snapshot, province, showroom,
 *          preferred_date, note, status(pending|confirmed|cancelled|done),
 *          created_at, updated_at
 *
 * Products live in MongoDB now, so there is no JOIN: the product name is
 * snapshotted at insert time and returned as `product_name`.
 */
class TestDrive {

    public static function create(
        string $name,
        string $email,
        string $phone,
        int $productId,
        string $province,
        string $showroom,
        string $preferredDate,
        ?string $note
    ): bool {
        global $pdo;

        $productName = '';
        try {
            $product = Product::getByIdAdmin((int)$productId);
            $productName = (string)($product['name'] ?? '');
        } catch (Throwable $e) {
            error_log('TestDrive::create could not load product ' . $productId . ': ' . $e->getMessage());
        }

        $stmt = $pdo->prepare(
            "INSERT INTO test_drives
             (name, email, phone, product_id, product_name_snapshot, province, showroom, preferred_date, note, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')"
        );
        return $stmt->execute([
            $name,
            $email,
            $phone,
            (string)$productId,
            $productName,
            $province,
            $showroom,
            $preferredDate,
            $note !== '' ? $note : null,
        ]);
    }

    public static function getPaginated(int $page = 1, int $perPage = PER_PAGE, ?string $status = null): array
    {
        global $pdo;
        $page = max(1, $page);
        $offset = max(0, ($page - 1) * $perPage);

        $sql = "SELECT td.id, td.user_id, td.name, td.email, td.phone, td.product_id, td.variant_id,
                       td.province, td.showroom, td.preferred_date, td.note, td.status,
                       to_char(td.created_at, 'YYYY-MM-DD HH24:MI:SS') AS created_at,
                       to_char(td.updated_at, 'YYYY-MM-DD HH24:MI:SS') AS updated_at,
                       td.product_name_snapshot AS product_name
                FROM test_drives td";
        $params = [];
        if ($status !== null && $status !== '') {
            $sql .= ' WHERE td.status = :status';
            $params[':status'] = $status;
        }
        $sql .= " ORDER BY CASE td.status
                              WHEN 'pending' THEN 1 WHEN 'confirmed' THEN 2
                              WHEN 'done' THEN 3 WHEN 'cancelled' THEN 4 ELSE 5 END,
                           td.updated_at DESC, td.created_at DESC
                 LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function countAll(?string $status = null): int
    {
        global $pdo;
        $sql = 'SELECT COUNT(*) FROM test_drives';
        if ($status !== null && $status !== '') {
            $stmt = $pdo->prepare($sql . ' WHERE status = ?');
            $stmt->execute([$status]);
            return (int)$stmt->fetchColumn();
        }
        return (int)$pdo->query($sql)->fetchColumn();
    }

    public static function setStatus(int $id, string $status): bool
    {
        global $pdo;
        $allowed = ['pending', 'confirmed', 'cancelled', 'done'];
        if (!in_array($status, $allowed, true)) return false;
        $stmt = $pdo->prepare('UPDATE test_drives SET status = ? WHERE id = ?');
        return $stmt->execute([$status, $id]);
    }

    public static function deleteById(int $id): bool
    {
        global $pdo;
        $stmt = $pdo->prepare('DELETE FROM test_drives WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
