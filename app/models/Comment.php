<?php
/**
 * app/models/Comment.php — Comment / Review Model
 * Table: comments
 * Owner: Nhat Tan (Member 4)
 * Used by: CommentController (frontend post), CommentAdminController (moderation)
 *
 * Columns: id, user_id, news_id(nullable), product_id(nullable),
 *          body, is_approved, created_at
 */
class Comment
{
    public static function create(int $userId, int $newsId, string $body, int $rating = 5): bool 
    {
        global $pdo;
        $stmt = $pdo->prepare(
            'INSERT INTO comments (user_id, news_id, body, rating, is_approved)
             VALUES (:user_id, :news_id, :body, :rating, 0)'
        );
        return $stmt->execute([
            ':user_id' => $userId,
            ':news_id' => $newsId,
            ':body'    => trim($body),
            ':rating'  => $rating
        ]);
    }

    public static function getPending(): array
    {
        global $pdo;
        $stmt = $pdo->query(
            'SELECT c.*, u.name AS author_name, u.avatar AS author_avatar, 
                    n.title AS news_title
               FROM comments c
               JOIN users u ON u.id = c.user_id
          LEFT JOIN news n ON n.id = c.news_id
              WHERE c.is_approved = 0
           ORDER BY c.created_at DESC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function approve(int $id): bool
    {
        global $pdo;
        $stmt = $pdo->prepare('UPDATE comments SET is_approved = 1 WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public static function delete(int $id): bool
    {
        global $pdo;
        $stmt = $pdo->prepare('DELETE FROM comments WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public static function incrementHelpful(int $id): bool
    {
        global $pdo;
        $stmt = $pdo->prepare('UPDATE comments SET helpful_count = helpful_count + 1 WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public static function getAllAdmin(): array
    {
        global $pdo;
        $stmt = $pdo->query(
            'SELECT c.*, u.name AS author_name, u.email AS author_email, u.avatar AS author_avatar, 
                    n.title AS news_title
               FROM comments c
               JOIN users u ON u.id = c.user_id
          LEFT JOIN news n ON n.id = c.news_id
           ORDER BY c.created_at DESC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function getApprovedByNewsId(int $newsId): array
    {
        global $pdo;
        $stmt = $pdo->prepare(
            'SELECT c.*, u.name AS author_name, u.avatar AS author_avatar 
               FROM comments c
               JOIN users u ON u.id = c.user_id
              WHERE c.news_id = :news_id AND c.is_approved = 1
           ORDER BY c.created_at DESC'
        );
        $stmt->execute([':news_id' => $newsId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function getCounts(): array
    {
        global $pdo;
        $stmt = $pdo->query('
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN is_approved = 1 THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN is_approved = 0 THEN 1 ELSE 0 END) as pending
            FROM comments
        ');
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total' => 0, 'approved' => 0, 'pending' => 0];
    }
}