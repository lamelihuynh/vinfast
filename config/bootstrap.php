<?php

/**
 * config/bootstrap.php — Application Bootstrap
 * Owner: All members (common)
 *
 * Loaded by BOTH index.php and admin.php.
 * Order matters: constants → DB → helpers → session.
 */

define('ROOT',          __DIR__ . '/..');
define('APP_NAME',      'VinFast');
// define('BASE_URL',      'http://localhost/vinfast/');


// Tự động lấy giao thức (http hoặc https)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

// Tự động lấy tên miền hoặc IP đang truy cập
$host = $_SERVER['HTTP_HOST']; 

// Tự động lấy thư mục gốc của dự án (ví dụ: /vinfast/)
$project_folder = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);

// Kết hợp lại thành BASE_URL hoàn chỉnh
define('BASE_URL', $protocol . $host . $project_folder);




define('ADMIN_URL',     BASE_URL . 'admin/');
define('SRTDASH_LIB_URL', BASE_URL . 'public/libs/srtdash/');
define('UPLOAD_PATH',   ROOT . '/public/images/uploads/');
define('UPLOAD_URL',    BASE_URL . 'public/images/uploads/');
define('AUTH_COOKIE_SECRET', hash('sha256', ROOT . '|vinfast|auth-cookie-v1'));
define('PER_PAGE',      10);
define('MAX_FILE_SIZE', 5 * 1024 * 1024);            // 2 MB
define('ALLOWED_MIME',  ['image/jpeg', 'image/png', 'image/webp']);


require_once ROOT . '/config/database.php';

// Auto-load helpers (always needed)
foreach (['Auth', 'Validator', 'Upload', 'Pagination', 'SEO', 'View', 'AssetHelper', 'ProductViewHelper', 'CheckoutViewHelper'] as $h) {
    require_once ROOT . "/app/helpers/{$h}.php";
}

// Auto-load models on demand
spl_autoload_register(function (string $class): void {
    $file = ROOT . "/app/models/{$class}.php";
    if (file_exists($file)) require_once $file;
});

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['SERVER_PORT'] ?? '') === '443');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);

if (PHP_VERSION_ID >= 70300) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => $isHttps,
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => 1,
    ]);
} else {
    session_start();
}