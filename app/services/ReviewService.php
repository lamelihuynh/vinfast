<?php

/**
 * app/services/ReviewService.php — Điều phối duyệt/xóa review giữa PG và MongoDB (T1-15)
 * Owner: T1 (Data Storage)
 *
 * Mô hình Subset Pattern + Eventual Consistency:
 *   1. Ghi PostgreSQL (`product_reviews`) trước — đây là nguồn chân lý (source of truth).
 *   2. Tính lại `avgRating`, `totalReviews` và tối đa 5 bài đã duyệt mới nhất rồi `$set`
 *      sang document `products` bên MongoDB theo cơ chế best-effort (nếu Mongo tạm ngắt,
 *      PG vẫn lưu thành công và dùng `bench/t1/tools/reviews_resync.php` để đồng bộ lại).
 */
class ReviewService
{
    public static function approve(int $id): bool
    {
        $productId = ProductReview::setApproved($id, true);
        if ($productId === null) {
            return false;
        }
        self::refreshSummary($productId);
        return true;
    }

    public static function reject(int $id): bool
    {
        $productId = ProductReview::setApproved($id, false);
        if ($productId === null) {
            return false;
        }
        self::refreshSummary($productId);
        return true;
    }

    public static function delete(int $id): bool
    {
        $productId = ProductReview::delete($id);
        if ($productId === null) {
            return false;
        }
        self::refreshSummary($productId);
        return true;
    }

    /**
     * Chuyển 1 dòng `product_reviews` (JOIN `users`) sang đúng hình dạng BSON của validator
     * `products.reviews[]` trong `database/mongo/001_collections.js`.
     */
    public static function toEmbedded(array $row): array
    {
        $rawTs = (string)($row['created_at_raw'] ?? $row['created_at'] ?? '');
        $ts = $rawTs !== '' ? strtotime($rawTs) : false;
        $ms = ($ts !== false ? $ts : time()) * 1000;

        return [
            'reviewId'         => (int)$row['id'],
            'userId'           => (int)$row['user_id'],
            'userNameSnapshot' => (string)($row['author_name'] ?? $row['name'] ?? ''),
            'rating'           => max(1, min(5, (int)$row['rating'])),
            'comment'          => (string)($row['body'] ?? ''),
            'images'           => [],
            'createdAt'        => new MongoDB\BSON\UTCDateTime((int)$ms),
            'helpfulCount'     => max(0, (int)($row['helpful_count'] ?? 0)),
        ];
    }

    /**
     * Đồng bộ trực tiếp 1 sản phẩm từ PG sang Mongo (ném lỗi nếu Mongo không phản hồi — dùng cho CLI resync).
     */
    public static function syncProduct(string $productId): bool
    {
        $productId = trim($productId);
        if ($productId === '') {
            return false;
        }

        [$avg, $count] = ProductReview::summary($productId);
        $top = array_values(array_map([self::class, 'toEmbedded'], ProductReview::latestApproved($productId, 5)));

        $res = Db::mongo()->selectCollection('products')->updateOne(
            ['productID' => $productId],
            ['$set' => [
                'avgRating'    => round((float)$avg, 2),
                'totalReviews' => (int)$count,
                'reviews'      => $top,
            ]]
        );

        return $res->getMatchedCount() > 0;
    }

    /**
     * Đồng bộ best-effort sau thao tác Admin: nếu Mongo lỗi, chỉ ghi log để không làm hỏng request PG.
     */
    public static function refreshSummary(string $productId): void
    {
        try {
            self::syncProduct($productId);
        } catch (Throwable $e) {
            error_log("review summary {$productId}: " . $e->getMessage());
        }
    }
}
