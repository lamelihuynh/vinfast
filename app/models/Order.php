<?php

/**
 * app/models/Order.php — Deposit / Test-Drive Order Model
 * Table: orders
 * Owner: Hai Nam (Member 3)
 * Used by: ProductController, UserController, OrderAdminController
 *
 * Columns: id, user_id, product_id, type(deposit|test_drive),
 *          status(pending|confirmed|cancelled|done), note, created_at
 */
class Order
{
    private const ALLOWED_STATUSES = ['pending', 'confirmed', 'cancelled', 'done'];

    private const ALLOWED_PAYMENT_STATUSES = ['unpaid', 'pending_verify', 'paid', 'failed', 'refunded'];

    private const STATUS_TRANSITIONS = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['done', 'cancelled'],
        'done' => [],
        'cancelled' => [],
    ];

    private const SQL_BASE = "SELECT o.*, p.name AS product_name, p.price, u.name AS user_name, u.email
        FROM orders o
        JOIN products p ON o.product_id = p.id
        JOIN users u ON o.user_id = u.id";

    public static function create($userId, $productId, $type = 'deposit', $note = null)
    {
        global $pdo;
        $stmt = $pdo->prepare("\n            INSERT INTO orders (user_id, product_id, type, status, note) \n            VALUES (?, ?, ?, 'pending', ?)\n        ");
        $ok = $stmt->execute([$userId, $productId, $type, $note]);
        if (!$ok) {
            return false;
        }

        return (int)$pdo->lastInsertId();
    }

    public static function getByUserId($userId, $page = 1, $perPage = 10)
    {
        global $pdo;
        $page = max(1, (int)$page);
        $perPage = max(1, (int)$perPage);
        $offset = max(0, (int)($page - 1) * $perPage);

        $stmt = $pdo->prepare("\n            SELECT o.*, p.name as product_name, p.price \n            FROM orders o \n            JOIN products p ON o.product_id = p.id \n            WHERE o.user_id = :user_id \n            ORDER BY o.created_at DESC\n            LIMIT :limit OFFSET :offset\n        ");
        $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countByUserId($userId)
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM orders WHERE user_id = ?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['cnt'] ?? 0);
    }

    public static function getById($id)
    {
        global $pdo;
        $stmt = $pdo->prepare("\n            SELECT o.*, p.name as product_name, p.price, u.name as user_name, u.email \n            FROM orders o \n            JOIN products p ON o.product_id = p.id \n            JOIN users u ON o.user_id = u.id \n            WHERE o.id = ?\n        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getAll($page = 1, $perPage = 10)
    {
        global $pdo;
        $page = max(1, (int)$page);
        $perPage = max(1, (int)$perPage);
        $offset = max(0, (int)($page - 1) * $perPage);

        $stmt = $pdo->prepare("\n            SELECT o.*, p.name as product_name, u.name as user_name \n            FROM orders o \n            JOIN products p ON o.product_id = p.id \n            JOIN users u ON o.user_id = u.id \n            ORDER BY o.created_at DESC \n            LIMIT :limit OFFSET :offset\n        ");
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countAll()
    {
        global $pdo;
        $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM orders");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['cnt'] ?? 0);
    }

    public static function updateStatus($id, $status)
    {
        global $pdo;
        if (!in_array($status, self::ALLOWED_STATUSES, true)) {
            return false;
        }

        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public static function delete($id)
    {
        global $pdo;
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function getByStatus($status, $page = 1, $perPage = 10)
    {
        global $pdo;
        $page = max(1, (int)$page);
        $perPage = max(1, (int)$perPage);
        $offset = max(0, (int)($page - 1) * $perPage);

        $sql = "SELECT o.*, p.name as product_name, u.name as user_name 
            FROM orders o 
            JOIN products p ON o.product_id = p.id 
            JOIN users u ON o.user_id = u.id 
            WHERE o.status = :status
            ORDER BY o.created_at DESC 
            LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAdminList(array $filters = [], int $page = 1, int $perPage = 10): array
    {
        global $pdo;

        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;

        [$where, $params] = self::buildAdminFilters($filters);

        $sql = self::SQL_BASE;
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY o.created_at DESC, o.id DESC LIMIT :limit OFFSET :offset';

        $stmt = $pdo->prepare($sql);
        self::bindParams($stmt, $params);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countAdminList(array $filters = []): int
    {
        global $pdo;

        [$where, $params] = self::buildAdminFilters($filters);

        $sql = "SELECT COUNT(*) AS cnt
                FROM orders o
                JOIN products p ON o.product_id = p.id
                JOIN users u ON o.user_id = u.id";

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $stmt = $pdo->prepare($sql);
        self::bindParams($stmt, $params);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['cnt'] ?? 0);
    }

    public static function validStatuses(): array
    {
        return self::ALLOWED_STATUSES;
    }

    public static function allowedNextStatuses(string $status): array
    {
        $status = trim($status);
        return self::STATUS_TRANSITIONS[$status] ?? [];
    }

    public static function canTransition(string $fromStatus, string $toStatus): bool
    {
        $fromStatus = trim($fromStatus);
        $toStatus = trim($toStatus);

        if (!in_array($fromStatus, self::ALLOWED_STATUSES, true) || !in_array($toStatus, self::ALLOWED_STATUSES, true)) {
            return false;
        }

        if ($fromStatus === $toStatus) {
            return true;
        }

        return in_array($toStatus, self::allowedNextStatuses($fromStatus), true);
    }

    public static function validPaymentStatuses(): array
    {
        return self::ALLOWED_PAYMENT_STATUSES;
    }

    public static function getPaymentStatusFromNote($note): string
    {
        $decoded = self::decodeOrderNote($note);
        $status = trim((string)($decoded['payment_status'] ?? 'pending_verify'));
        if (!in_array($status, self::ALLOWED_PAYMENT_STATUSES, true)) {
            return 'pending_verify';
        }

        return $status;
    }

    public static function getPaymentStatusById(int $orderId): string
    {
        $order = self::getById($orderId);
        if (!$order) {
            return 'pending_verify';
        }

        return self::getPaymentStatusFromNote($order['note'] ?? null);
    }

    public static function updatePaymentStatus(int $orderId, string $paymentStatus): bool
    {
        global $pdo;

        $paymentStatus = trim($paymentStatus);
        if (!in_array($paymentStatus, self::ALLOWED_PAYMENT_STATUSES, true)) {
            return false;
        }

        $order = self::getById($orderId);
        if (!$order) {
            return false;
        }

        $note = self::decodeOrderNote($order['note'] ?? null);
        $note['payment_status'] = $paymentStatus;
        $note['payment_updated_at'] = date('c');

        if ($paymentStatus === 'paid' && empty($note['payment_verified_at'])) {
            $note['payment_verified_at'] = date('c');
        }

        if ($paymentStatus !== 'paid' && $paymentStatus !== 'refunded' && isset($note['payment_verified_at'])) {
            unset($note['payment_verified_at']);
        }

        $json = self::encodeOrderNote($note);
        $stmt = $pdo->prepare('UPDATE orders SET note = ? WHERE id = ?');
        return $stmt->execute([$json, $orderId]);
    }

    private static function decodeOrderNote($note): array
    {
        if (is_array($note)) {
            return $note;
        }

        $raw = trim((string)$note);
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private static function encodeOrderNote(array $note): string
    {
        $json = json_encode($note, JSON_UNESCAPED_UNICODE);
        return $json !== false ? $json : '{}';
    }

    private static function buildAdminFilters(array $filters = []): array
    {
        $where = [];
        $params = [];

        $status = trim((string)($filters['status'] ?? 'all'));
        if ($status !== '' && $status !== 'all' && in_array($status, self::ALLOWED_STATUSES, true)) {
            $where[] = 'o.status = :status';
            $params[':status'] = $status;
        }

        $q = trim((string)($filters['q'] ?? ''));
        if ($q !== '') {
            $where[] = '(u.name LIKE :q1 OR u.email LIKE :q2 OR p.name LIKE :q3 OR CAST(o.id AS CHAR) LIKE :q4)';
            $params[':q1'] = '%' . $q . '%';
            $params[':q2'] = '%' . $q . '%';
            $params[':q3'] = '%' . $q . '%';
            $params[':q4'] = '%' . $q . '%';
        }

        return [$where, $params];
    }

    private static function bindParams(PDOStatement $stmt, array $params): void
    {
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
    }
}
