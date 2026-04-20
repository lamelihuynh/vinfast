<?php
/**
 * app/models/News.php — News Article Model
 * Table: news
 * Owner: Nhat Tan (Member 4)
 * Used by: NewsController (frontend), NewsAdminController (admin)
 *
 * Columns: id, author_id, title, slug, body, thumbnail,
 *          meta_title, meta_description, created_at
 */
class News {

    public static function getLatest(int $limit = 3): array
    {
        global $pdo;
        $limit = max(1, min(12, $limit));

        // NOTE: table `news` is part of schema.sql; nếu DB chưa import sample,
        // hàm này chỉ trả về mảng rỗng và homepage sẽ tự fallback.
        $stmt = $pdo->prepare('SELECT id, title, slug, thumbnail, created_at FROM news ORDER BY created_at DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
