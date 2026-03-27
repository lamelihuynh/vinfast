<?php
/**
 * index.php — Public Front Controller
 *
 * Entry point for all customer-facing pages.
 * Routes: /  /products  /news  /contact  /auth/login  etc.
 * Admin pages are handled separately by admin.php.
 *
 * Owner: All members (common)
 */
require_once __DIR__ . '/config/bootstrap.php';

$url      = trim($_GET['url'] ?? 'home', '/');
$segments = explode('/', filter_var($url, FILTER_SANITIZE_URL));

// Controller lives in app/controllers/frontend/
$ctrlName = ucfirst(strtolower($segments[0] ?? 'home')) . 'Controller';
$method   = strtolower($segments[1] ?? 'index');
$params   = array_slice($segments, 2);

$ctrlFile = __DIR__ . "/app/controllers/frontend/{$ctrlName}.php";

if (file_exists($ctrlFile)) {
    require_once $ctrlFile;
    $ctrl = new $ctrlName();
    if (method_exists($ctrl, $method)) {
        call_user_func_array([$ctrl, $method], $params);
    } else {
        http_response_code(404);
        include __DIR__ . '/app/views/frontend/404.php';
    }
} else {
    http_response_code(404);
    include __DIR__ . '/app/views/frontend/404.php';
}
