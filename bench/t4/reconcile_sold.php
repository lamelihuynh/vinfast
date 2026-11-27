<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit(1);
}
require_once __DIR__ . '/../../config/bootstrap.php';

if (!in_array(Db::env('APP_ENV'), ['local', 'bench'], true)
    || $argc !== 2 || !preg_match('/\A[0-9]{1,20}\z/', $argv[1])) {
    fwrite(STDERR, "Dùng trong local/bench, khi tạm dừng ghi đơn: php bench/t4/reconcile_sold.php PRODUCT_ID\n");
    exit(1);
}

$stmt = Db::pg()->prepare("SELECT COALESCE(SUM(i.quantity), 0) FROM order_items i
    JOIN orders o ON o.id = i.order_id WHERE i.product_id = ? AND o.status <> 'cancelled'");
$stmt->execute([$argv[1]]);
$sold = (int)$stmt->fetchColumn();
$result = Db::mongo()->selectCollection('products')->updateOne(['productID' => $argv[1]], ['$set' => ['totalSold' => $sold]]);
if ($result->getMatchedCount() !== 1) {
    fwrite(STDERR, "Không tìm thấy sản phẩm trong MongoDB.\n");
    exit(1);
}
echo "productID={$argv[1]} totalSold={$sold}\n";
