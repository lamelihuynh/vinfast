<?php
/**
 * app/models/TestDrive.php — Test Drive Registration Model
 * Table: test_drives
 * Owner: Tang Vu (Member 1)
 * Used by: ContactController (frontend submit), ContactAdminController (admin view)
 *
 * Columns: id, name, email, phone, product_id, province, showroom,
 *          preferred_date, note, status(pending|confirmed|cancelled|done), created_at
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
        $stmt = $pdo->prepare(
            'INSERT INTO test_drives
             (name, email, phone, product_id, province, showroom, preferred_date, note, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, "pending")'
        );
        return $stmt->execute([
            $name,
            $email,
            $phone,
            $productId,
            $province,
            $showroom,
            $preferredDate,
            $note !== '' ? $note : null,
        ]);
    }

    public static function getPaginated(int $page = 1, int $perPage = PER_PAGE): array
    {
        global $pdo;
        $page = max(1, $page);
        $offset = max(0, ($page - 1) * $perPage);

        $stmt = $pdo->prepare(
            'SELECT td.*, p.name as product_name
             FROM test_drives td
             LEFT JOIN products p ON td.product_id = p.id
             ORDER BY td.created_at DESC
             LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function countAll(): int
    {
        global $pdo;
        return (int)$pdo->query('SELECT COUNT(*) FROM test_drives')->fetchColumn();
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

