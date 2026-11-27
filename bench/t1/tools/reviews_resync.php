<?php

/**
 * bench/t1/tools/reviews_resync.php — Đồng bộ lại tóm tắt review từ PostgreSQL sang MongoDB (T1-15)
 *
 * Cách chạy:
 *   docker compose exec app php bench/t1/tools/reviews_resync.php
 *   docker compose exec app php bench/t1/tools/reviews_resync.php --product=165
 *
 * Idempotent: chạy nhiều lần đều ra đúng trạng thái khớp 100% với bảng PG `product_reviews`
 * (kể cả trường hợp bài đánh giá cuối cùng của sản phẩm vừa bị từ chối/xóa lúc Mongo đang tắt).
 */

require_once __DIR__ . '/../../../config/bootstrap.php';

$targetProduct = null;
foreach (array_slice($argv ?? [], 1) as $arg) {
    if (strncmp($arg, '--product=', 10) === 0) {
        $targetProduct = trim(substr($arg, 10));
    }
}

$t0 = hrtime(true);

if ($targetProduct !== null && $targetProduct !== '') {
    $matched = ReviewService::syncProduct($targetProduct);
    [$avg, $count] = ProductReview::summary($targetProduct);
    printf(
        "resync product=%s (%s): avgRating=%.2f, totalReviews=%d, elapsed=%.2f ms\n",
        $targetProduct,
        $matched ? 'updated' : 'not found in Mongo',
        $avg,
        $count,
        (hrtime(true) - $t0) / 1e6
    );
    exit($matched ? 0 : 1);
}

$pdo = Db::pg();
$productsColl = Db::mongo()->selectCollection('products');

// Tập sản phẩm cần đồng bộ = mọi product_id có trong PG product_reviews
// HỢP với mọi sản phẩm bên Mongo đang có totalReviews > 0 hoặc mảng reviews khác rỗng (để reset về 0 nếu đã xóa hết bên PG).
$pids = [];
foreach ($pdo->query('SELECT DISTINCT product_id FROM product_reviews') as $r) {
    $pid = trim((string)($r['product_id'] ?? ''));
    if ($pid !== '') {
        $pids[$pid] = true;
    }
}
foreach ($productsColl->find(
    ['$or' => [['totalReviews' => ['$gt' => 0]], ['reviews.0' => ['$exists' => true]]]],
    ['projection' => ['_id' => 0, 'productID' => 1]]
) as $doc) {
    $pid = trim((string)($doc['productID'] ?? ''));
    if ($pid !== '') {
        $pids[$pid] = true;
    }
}

$synced = 0;
foreach (array_keys($pids) as $pid) {
    if (ReviewService::syncProduct((string)$pid)) {
        $synced++;
    }
}

printf(
    "resync all: checked=%d products, synced=%d in %.2f ms\n",
    count($pids),
    $synced,
    (hrtime(true) - $t0) / 1e6
);
exit(0);
