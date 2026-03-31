<?php

/**
 * app/models/Order.php — Deposit / Test-Drive Order Model
 * Table: orders
 * Owner: Hai Nam (Member 3)
 * Used by: CartController, OrderAdminController
 *
 * Columns: id, user_id, product_id, type(deposit|test_drive),
 *          status(pending|confirmed|cancelled|done), note, created_at
 */
class Order
{
    public static function create($userId, $productId, $type = 'deposit', $note = null)
    {
        global $pdo;
        $stmt = $pdo->prepare("\n            INSERT INTO orders (user_id, product_id, type, status, note) \n            VALUES (?, ?, ?, 'pending', ?)\n        ");
        return $stmt->execute([$userId, $productId, $type, $note]);
    }

    public static function getByUserId($userId, $page = 1, $perPage = 10)
    {
        global $pdo;
        $page = max(1, (int)$page);
        $perPage = max(1, (int)$perPage);
        $offset = max(0, (int)($page - 1) * $perPage);

        $stmt = $pdo->prepare("\n            SELECT o.*, p.name as product_name, p.price \n            FROM orders o \n            JOIN products p ON o.product_id = p.id \n            WHERE o.user_id = ? \n            ORDER BY o.created_at DESC\n            LIMIT :limit OFFSET :offset\n        ");
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
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
        $allowedStatuses = ['pending', 'confirmed', 'cancelled', 'done'];
        if (!in_array($status, $allowedStatuses, true)) {
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
}
