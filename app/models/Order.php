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
class Order {
    public static function create($userId, $productId, $type = 'deposit', $note = null) {
        global $pdo;
        $stmt = $pdo->prepare("
            INSERT INTO orders (user_id, product_id, type, status, note) 
            VALUES (?, ?, ?, 'pending', ?)
        ");
        return $stmt->execute([$userId, $productId, $type, $note]);
    }

    public static function getByUserId($userId) {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT o.*, p.name as product_name, p.price 
            FROM orders o 
            JOIN products p ON o.product_id = p.id 
            WHERE o.user_id = ? 
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id) {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT o.*, p.name as product_name, p.price, u.name as user_name, u.email 
            FROM orders o 
            JOIN products p ON o.product_id = p.id 
            JOIN users u ON o.user_id = u.id 
            WHERE o.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getAll($page = 1, $perPage = 10) {
        global $pdo;
        $page = max(1, $page);
        $offset = max(0, (int)($page - 1) * $perPage);
        $stmt = $pdo->prepare("
            SELECT o.*, p.name as product_name, u.name as user_name 
            FROM orders o 
            JOIN products p ON o.product_id = p.id 
            JOIN users u ON o.user_id = u.id 
            ORDER BY o.created_at DESC 
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countAll() {
        global $pdo;
        $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM orders");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['cnt'] ?? 0;
    }

    public static function updateStatus($id, $status) {
        global $pdo;
        $allowedStatuses = ['pending', 'confirmed', 'cancelled', 'done'];
        if (!in_array($status, $allowedStatuses, true)) {
        // Nếu status không hợp lệ, trả về false ngay lập tức mà không chạy SQL
        return false; 
    }
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public static function delete($id) {
        global $pdo;
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public static function getByStatus($status, $page = 1, $perPage = 10) {
        global $pdo;
        $page = max(1, $page);
        $offset = max(0, (int)($page - 1) * $perPage);
        $sql= "SELECT o.*, p.name as product_name, u.name as user_name 
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
