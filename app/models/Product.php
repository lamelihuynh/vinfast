<?php
/**
 * app/models/Product.php — Vehicle / Product Model
 * Table: products
 * Owner: Hai Nam (Member 3)
 * Used by: ProductController (frontend), ProductAdminController (admin)
 *
 * Columns: id, category_id, name, slug, description,
 *          specs(JSON), price, images(JSON), is_active, created_at
 */
class Product {
    public static function create($categoryId, $name, $slug, $description, $specs, $price, $images, $isActive) {
        global $pdo;
        $stmt = $pdo->prepare("
            INSERT INTO products (category_id, name, slug, description, specs, price, images, is_active) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $categoryId, 
            $name, 
            $slug, 
            $description, 
            json_encode($specs), 
            $price, 
            json_encode($images), 
            $isActive
        ]);
    }
    private static function formatProduct($product) {
        if (!$product) return null;
        $product['specs'] = json_decode($product['specs'] ?? '', true) ?? [];
        $product['images'] = json_decode($product['images'] ?? '', true) ?? [];
        return $product;
    }
    public static function getById($id) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM products WHERE is_active = 1 AND id = ? LIMIT 0,1");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        return self::formatProduct($product);
    }
    public static function getAll($page = 1, $perPage = 10) {
        global $pdo;
        $page = max(1, $page);
        $offset = max(0, (int)($page - 1) * $perPage);
        $stmt = $pdo->prepare("SELECT * FROM products WHERE is_active = 1 ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return array_map([self::class, 'formatProduct'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    public static function countAll() {
        global $pdo;
        $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1");
        return $stmt->fetchColumn();
    }
    public static function getByCategory($categoryId, $page = 1, $perPage = 10) {
        global $pdo;
        $page = max(1, $page);
        $offset = max(0, (int)($page - 1) * $perPage);
        $sql = "SELECT * FROM products 
            WHERE is_active = 1 AND category_id = :id 
            ORDER BY created_at DESC 
            LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return array_map([self::class, 'formatProduct'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    public static function search($keyword, $page = 1, $perPage = 10) {
        global $pdo;
        $page = max(1, $page);
        $offset = max(0, (int)($page - 1) * $perPage);
        $sql = "SELECT * FROM products 
            WHERE is_active = 1 AND name LIKE :keyword 
            ORDER BY created_at DESC 
            LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':keyword', "%$keyword%", PDO::PARAM_STR);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return array_map([self::class, 'formatProduct'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }
     public static function countByCategory ($categoryId) {
        global $pdo;
        $query = "SELECT COUNT(*) as cnt FROM products WHERE is_active = 1 AND category_id = ?";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$categoryId]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['cnt'] : 0;
    }
}