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

    private static function since(int $days): MongoDB\BSON\UTCDateTime
    {
        return new MongoDB\BSON\UTCDateTime((time() - max(1, $days) * 86400) * 1000);
    }

    /**
     * Most searched keywords over the last $days days.
     * Keywords are stored as typed, so they are lower-cased and trimmed here ("VF 8" = "vf 8").
     * The first $match must stay first and untouched so it can use idx_action_time.
     * @return list<array{keyword:string, count:int, uniqueUsers:int}>
     */
    public static function topKeywords(int $days = 30, int $limit = 10): array
    {
        $cursor = Db::mongo()->selectCollection('userActivityLogs')->aggregate([
            ['$match' => ['actionType' => 'search', 'timestamp' => ['$gte' => self::since($days)]]],
            ['$project' => [
                'userId' => 1,
                'kw' => ['$toLower' => ['$trim' => ['input' => ['$ifNull' => ['$metadata.keyword', '']]]]],
            ]],
            ['$match' => ['kw' => ['$ne' => '']]],
            ['$group' => ['_id' => '$kw', 'count' => ['$sum' => 1], 'users' => ['$addToSet' => '$userId']]],
            // Guests log userId null; without this they would count as one extra user.
            ['$project' => [
                '_id' => 0, 'keyword' => '$_id', 'count' => 1,
                'uniqueUsers' => ['$size' => ['$setDifference' => ['$users', [null]]]],
            ]],
            ['$sort' => ['count' => -1, 'keyword' => 1]],
            ['$limit' => max(1, $limit)],
        ]);

        $out = [];
        foreach ($cursor as $doc) {
            $out[] = [
                'keyword' => (string)$doc['keyword'],
                'count' => (int)$doc['count'],
                'uniqueUsers' => (int)$doc['uniqueUsers'],
            ];
        }
        return $out;
    }

    /**
     * Most viewed products over the last $days days, named via $lookup into products.
     * $limit runs before $lookup so only the top N products are looked up.
     * Deleted products are kept (preserveNullAndEmptyArrays) and shown by id.
     * @return list<array{productID:string, name:string, isActive:bool, views:int}>
     */
    public static function topViewedProducts(int $days = 30, int $limit = 10): array
    {
        $cursor = Db::mongo()->selectCollection('userActivityLogs')->aggregate([
            ['$match' => ['actionType' => 'view_product', 'timestamp' => ['$gte' => self::since($days)]]],
            ['$group' => ['_id' => '$targetId', 'views' => ['$sum' => 1]]],
            ['$sort' => ['views' => -1, '_id' => 1]],
            ['$limit' => max(1, $limit)],
            ['$lookup' => ['from' => 'products', 'localField' => '_id', 'foreignField' => 'productID', 'as' => 'p']],
            ['$unwind' => ['path' => '$p', 'preserveNullAndEmptyArrays' => true]],
            ['$project' => ['_id' => 0, 'productID' => '$_id', 'name' => '$p.name', 'isActive' => '$p.isActive', 'views' => 1]],
        ]);

        $out = [];
        foreach ($cursor as $doc) {
            $out[] = [
                'productID' => (string)$doc['productID'],
                'name' => (string)($doc['name'] ?? ''),
                'isActive' => !empty($doc['isActive']),
                'views' => (int)$doc['views'],
            ];
        }
        return $out;
    }
}
