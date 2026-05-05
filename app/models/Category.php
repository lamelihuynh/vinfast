<?php

/**
 * app/models/Category.php — Vehicle Category Model
 * Table: categories
 * Owner: Hai Nam (Member 3)
 *
 * Columns: id, name, slug
 * Examples: "Electric Motorbike", "Electric Car"
 */
class Category
{
    public static function getAll()
    {
        global $pdo;
        $query = "SELECT id, name, slug FROM categories ORDER BY name ASC";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function getById($id)
    {
        global $pdo;
        $query = "SELECT * FROM categories WHERE id = ? LIMIT 0,1";

        $stmt = $pdo->prepare($query);
        $stmt->execute([$id]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$category) return null;
        return $category;
    }
    public static function countBySlug($slug)
    {
        global $pdo;
        $query = "SELECT COUNT(*) as cnt FROM categories WHERE slug = ?";

        $stmt = $pdo->prepare($query);
        $stmt->execute([$slug]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['cnt'] : 0;
    }

    public static function create(string $name, string $slug): int
    {
        global $pdo;
        $query = "INSERT INTO categories (name, slug) VALUES (:name, :slug)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':name' => $name,
            ':slug' => $slug,
        ]);

        return (int)$pdo->lastInsertId();
    }

    public static function delete(int $id): bool
    {
        global $pdo;
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }
}
