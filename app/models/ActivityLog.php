<?php

/**
 * app/models/ActivityLog.php — Log hành vi người dùng (MongoDB: userActivityLogs), quyết định 7.
 *
 * Ghi best-effort: mọi lỗi bị nuốt và chỉ error_log, không bao giờ làm request lỗi.
 * actionType phải nằm trong enum của validator (database/mongo/001_collections.js):
 *   view_product, search, add_to_cart, remove_from_cart, add_to_wishlist, remove_from_wishlist,
 *   place_order, deposit, apply_coupon, review
 *
 * Đang ghi: view_product, search (ProductController). T2/T4 gọi thêm ở Cart/Wishlist/OrderService.
 * T3 dùng collection này để thống kê từ khóa tìm kiếm, sản phẩm được xem nhiều.
 */
class ActivityLog
{
    public static function record(string $actionType, ?string $targetId = null, array $metadata = []): void
    {
        try {
            $userId = class_exists('Auth') ? Auth::id() : null;
            $device = 'desktop';
            $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
            if (preg_match('/Mobile|Android|iPhone/i', $ua)) {
                $device = 'mobile';
            }

            Db::mongo()->selectCollection('userActivityLogs')->insertOne([
                'userId' => $userId ? (int)$userId : null,
                'sessionId' => session_status() === PHP_SESSION_ACTIVE ? session_id() : null,
                'targetId' => $targetId,
                'actionType' => $actionType,
                'timestamp' => new MongoDB\BSON\UTCDateTime(),
                'metadata' => [
                    'source' => $metadata['source'] ?? 'web',
                    'keyword' => isset($metadata['keyword']) ? mb_substr((string)$metadata['keyword'], 0, 200) : null,
                    'device' => $device,
                    'page' => (string)parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH),
                    'customData' => isset($metadata['customData']) ? (object)$metadata['customData'] : null,
                ],
            ]);
        } catch (Throwable $e) {
            error_log('ActivityLog::record: ' . $e->getMessage());
        }
    }
}
