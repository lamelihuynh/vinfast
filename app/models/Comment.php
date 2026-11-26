<?php
/**
 * app/models/Comment.php — Comment / Review Model
 * Table: comments (PostgreSQL)
 * Owner: Nhat Tan (Member 4)
 * Used by: CommentController (frontend post), CommentAdminController (moderation)
 *
 * Columns: id, user_id, news_id(nullable), rating, body, helpful_count,
 *          is_approved (BOOLEAN), created_at (TIMESTAMPTZ)
 *
 * is_approved is returned as 0/1 and created_at as 'YYYY-MM-DD HH24:MI:SS'
 * so views keep the shape they had on MySQL.
 */
class Comment
{
    /** Common column list for rows returned to views. */
    private const COLS = "c.id, c.user_id, c.news_id, c.rating, c.body, c.helpful_count,
                    c.is_approved::int AS is_approved,
                    to_char(c.created_at, 'YYYY-MM-DD HH24:MI:SS') AS created_at";

    public static function create(int $userId, int $newsId, string $body, int $rating = 5): bool
    {
        global $pdo;
        $stmt = $pdo->prepare(
            'INSERT INTO comments (user_id, news_id, body, rating, is_approved)
             VALUES (:user_id, :news_id, :body, :rating, FALSE)'
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
            'SELECT ' . self::COLS . ', u.name AS author_name, u.avatar AS author_avatar,
                    n.title AS news_title
               FROM comments c
               JOIN users u ON u.id = c.user_id
          LEFT JOIN news n ON n.id = c.news_id
              WHERE NOT c.is_approved
           ORDER BY c.created_at DESC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function approve(int $id): bool
    {
        global $pdo;
        $stmt = $pdo->prepare('UPDATE comments SET is_approved = TRUE WHERE id = :id');
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
            'SELECT ' . self::COLS . ', u.name AS author_name, u.email AS author_email, u.avatar AS author_avatar,
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
            'SELECT ' . self::COLS . ', u.name AS author_name, u.avatar AS author_avatar
               FROM comments c
               JOIN users u ON u.id = c.user_id
              WHERE c.news_id = :news_id AND c.is_approved
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
                COUNT(*) AS total,
                COUNT(*) FILTER (WHERE is_approved) AS approved,
                COUNT(*) FILTER (WHERE NOT is_approved) AS pending
            FROM comments
        ');
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total' => 0, 'approved' => 0, 'pending' => 0];
    }
}
