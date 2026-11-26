<?php
/**
 * config/database.php — Kết nối DB
 * Owner: All members (common)
 *
 * Giữ biến global $pdo (PostgreSQL) để mọi model cũ dùng `global $pdo` vẫn chạy.
 * MongoDB lấy qua Db::mongo(). Cấu hình bằng biến môi trường, xem app/core/Db.php.
 */
try {
    $pdo = Db::pg();
} catch (PDOException $e) {
    error_log('DB error: ' . $e->getMessage());
    http_response_code(500);
    die('Database connection error.');
}
