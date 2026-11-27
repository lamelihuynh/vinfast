<?php

/**
 * app/models/ProductReview.php — Đánh giá sản phẩm trên PostgreSQL (nguồn chính, T1-15)
 * Owner: T1 (Data Storage)
 *
 * Bảng: product_reviews (id, user_id -> users, product_id VARCHAR(32), rating 1..5,
 *       body, helpful_count, is_approved BOOLEAN, created_at TIMESTAMPTZ)
 * Ràng buộc: UNIQUE (user_id, product_id)
 * Index (T2):
 *   - idx_reviews_product_approved (product_id, created_at DESC) WHERE is_approved
 *   - idx_reviews_pending (created_at) WHERE NOT is_approved
 */
class ProductReview
{
    private const SELECT_COLS = "r.id, r.user_id, r.product_id, r.rating, r.body, r.helpful_count,
        r.is_approved::int AS is_approved,
        to_char(r.created_at, 'YYYY-MM-DD HH24:MI:SS') AS created_at,
        r.created_at AS created_at_raw";

    /**
     * Tạo đánh giá mới ở trạng thái chờ duyệt (`is_approved = FALSE`).
     * Trả `id` dòng vừa tạo, hoặc `null` nếu user này đã từng đánh giá sản phẩm đó.
     */
    public static function create(int $userId, string $productId, int $rating, string $body): ?int
    {
        $stmt = Db::pg()->prepare(
            'INSERT INTO product_reviews (user_id, product_id, rating, body, is_approved)
             VALUES (:uid, :pid, :rating, :body, FALSE)
             ON CONFLICT (user_id, product_id) DO NOTHING
             RETURNING id'
        );
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':pid', trim($productId), PDO::PARAM_STR);
        $stmt->bindValue(':rating', $rating, PDO::PARAM_INT);
        $stmt->bindValue(':body', trim($body), PDO::PARAM_STR);
        $stmt->execute();

        $id = $stmt->fetchColumn();
        return $id !== false ? (int)$id : null;
    }

    /** Kiểm tra user đã gửi đánh giá cho sản phẩm này chưa (kể cả đang chờ duyệt). */
    public static function findByUserAndProduct(int $userId, string $productId): ?array
    {
        $stmt = Db::pg()->prepare(
            'SELECT id, rating, body, is_approved::int AS is_approved,
                    to_char(created_at, \'YYYY-MM-DD HH24:MI:SS\') AS created_at
               FROM product_reviews
              WHERE user_id = :uid AND product_id = :pid'
        );
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':pid', trim($productId), PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Tính điểm trung bình và tổng số bài đã duyệt của 1 sản phẩm.
     * @return array{0: float, 1: int} [$avgRating, $totalReviews]
     */
    public static function summary(string $productId): array
    {
        $stmt = Db::pg()->prepare(
            'SELECT COALESCE(ROUND(AVG(rating)::numeric, 2), 0) AS avg_rating,
                    COUNT(*) AS total_reviews
               FROM product_reviews
              WHERE product_id = :pid AND is_approved'
        );
        $stmt->execute([':pid' => trim($productId)]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            (float)($row['avg_rating'] ?? 0.0),
            (int)($row['total_reviews'] ?? 0),
        ];
    }

    /**
     * Lấy $limit bài đánh giá mới nhất đã duyệt của 1 sản phẩm để nhúng vào Mongo `products.reviews[]`.
     * Tận dụng partial index `idx_reviews_product_approved (product_id, created_at DESC) WHERE is_approved`.
     */
    public static function latestApproved(string $productId, int $limit = 5): array
    {
        $limit = max(1, $limit);
        $stmt = Db::pg()->prepare(
            'SELECT r.id, r.user_id, r.product_id, r.rating, r.body, r.helpful_count,
                    r.created_at, u.name AS author_name
               FROM product_reviews r
               JOIN users u ON u.id = r.user_id
              WHERE r.product_id = :pid AND r.is_approved
              ORDER BY r.created_at DESC, r.id DESC
              LIMIT :lim'
        );
        $stmt->bindValue(':pid', trim($productId), PDO::PARAM_STR);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Phân trang danh sách đánh giá đã duyệt trên trang chi tiết sản phẩm (T1-16).
     * Hỗ trợ cả OFFSET (`$page`, `$perPage`) và Keyset (`$beforeCreatedAt`, `$beforeId`).
     */
    public static function getApprovedByProduct(
        string $productId,
        int $page = 1,
        int $perPage = 10,
        ?string $beforeCreatedAt = null,
        ?int $beforeId = null
    ): array {
        $page = max(1, $page);
        $perPage = max(1, min(50, $perPage));

        if ($beforeCreatedAt !== null && trim($beforeCreatedAt) !== '' && $beforeId !== null && $beforeId > 0) {
            $stmt = Db::pg()->prepare(
                'SELECT ' . self::SELECT_COLS . ', u.name AS author_name, u.avatar AS author_avatar
                   FROM product_reviews r
                   JOIN users u ON u.id = r.user_id
                  WHERE r.product_id = :pid
                    AND r.is_approved
                    AND (r.created_at, r.id) < (CAST(:before_ts AS timestamptz), :before_id)
                  ORDER BY r.created_at DESC, r.id DESC
                  LIMIT :lim'
            );
            $stmt->bindValue(':pid', trim($productId), PDO::PARAM_STR);
            $stmt->bindValue(':before_ts', trim($beforeCreatedAt), PDO::PARAM_STR);
            $stmt->bindValue(':before_id', $beforeId, PDO::PARAM_INT);
            $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }

        $offset = ($page - 1) * $perPage;
        $stmt = Db::pg()->prepare(
            'SELECT ' . self::SELECT_COLS . ', u.name AS author_name, u.avatar AS author_avatar
               FROM product_reviews r
               JOIN users u ON u.id = r.user_id
              WHERE r.product_id = :pid AND r.is_approved
              ORDER BY r.created_at DESC, r.id DESC
              LIMIT :lim OFFSET :off'
        );
        $stmt->bindValue(':pid', trim($productId), PDO::PARAM_STR);
        $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function countApprovedByProduct(string $productId): int
    {
        $stmt = Db::pg()->prepare(
            'SELECT COUNT(*) FROM product_reviews WHERE product_id = :pid AND is_approved'
        );
        $stmt->execute([':pid' => trim($productId)]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Danh sách đánh giá sản phẩm cho Admin duyệt (T1-17), có phân trang để an toàn ở dataset 10k/100k.
     * @param string|null $status 'pending' | 'approved' | 'all' | null
     */
    public static function getAdminList(?string $status = null, int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $where = '';
        if ($status === 'pending') {
            $where = 'WHERE NOT r.is_approved';
        } elseif ($status === 'approved') {
            $where = 'WHERE r.is_approved';
        }

        $stmt = Db::pg()->prepare(
            'SELECT ' . self::SELECT_COLS . ', u.name AS author_name, u.email AS author_email, u.avatar AS author_avatar
               FROM product_reviews r
               JOIN users u ON u.id = r.user_id
               ' . $where . '
              ORDER BY r.is_approved ASC, r.created_at DESC, r.id DESC
              LIMIT :lim OFFSET :off'
        );
        $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function countAdminList(?string $status = null): int
    {
        if ($status === 'pending') {
            return self::countPending();
        }
        if ($status === 'approved') {
            return (int)Db::pg()->query('SELECT COUNT(*) FROM product_reviews WHERE is_approved')->fetchColumn();
        }
        return (int)Db::pg()->query('SELECT COUNT(*) FROM product_reviews')->fetchColumn();
    }

    public static function countPending(): int
    {
        return (int)Db::pg()->query('SELECT COUNT(*) FROM product_reviews WHERE NOT is_approved')->fetchColumn();
    }

    /** @return array{total:int,approved:int,pending:int} */
    public static function getCounts(): array
    {
        $row = Db::pg()->query(
            'SELECT COUNT(*) AS total,
                    COUNT(*) FILTER (WHERE is_approved) AS approved,
                    COUNT(*) FILTER (WHERE NOT is_approved) AS pending
               FROM product_reviews'
        )->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total'    => (int)($row['total'] ?? 0),
            'approved' => (int)($row['approved'] ?? 0),
            'pending'  => (int)($row['pending'] ?? 0),
        ];
    }

    /**
     * Đổi trạng thái duyệt của 1 đánh giá và trả về `product_id` để đồng bộ sang Mongo.
     * Trả `null` nếu không tìm thấy `$id`.
     */
    public static function setApproved(int $id, bool $ok): ?string
    {
        $stmt = Db::pg()->prepare(
            'UPDATE product_reviews SET is_approved = :ok WHERE id = :id RETURNING product_id'
        );
        $stmt->bindValue(':ok', $ok, PDO::PARAM_BOOL);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $pid = $stmt->fetchColumn();
        return $pid !== false ? (string)$pid : null;
    }

    /**
     * Xóa vĩnh viễn 1 đánh giá và trả về `product_id` để tính lại tóm tắt bên Mongo.
     */
    public static function delete(int $id): ?string
    {
        $stmt = Db::pg()->prepare(
            'DELETE FROM product_reviews WHERE id = :id RETURNING product_id'
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $pid = $stmt->fetchColumn();
        return $pid !== false ? (string)$pid : null;
    }
}
