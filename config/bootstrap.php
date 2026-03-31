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
define('BASE_URL',      'http://localhost/vinfast/');
define('ADMIN_URL',     BASE_URL . 'admin/');
define('UPLOAD_PATH',   ROOT . '/public/images/uploads/');
define('UPLOAD_URL',    BASE_URL . 'public/images/uploads/');
define('PER_PAGE',      10);
define('MAX_FILE_SIZE', 2 * 1024 * 1024);            // 2 MB
define('ALLOWED_MIME',  ['image/jpeg', 'image/png', 'image/webp']);

require_once ROOT . '/config/database.php';

// Auto-load helpers (always needed)
foreach (['Auth', 'Validator', 'Upload', 'Pagination', 'SEO', 'View', 'AssetHelper'] as $h) {
    require_once ROOT . "/app/helpers/{$h}.php";
}

// Auto-load models on demand
spl_autoload_register(function (string $class): void {
    $file = ROOT . "/app/models/{$class}.php";
    if (file_exists($file)) require_once $file;
});

session_start();
