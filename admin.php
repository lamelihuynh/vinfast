<?php

/**
 * admin.php — Admin Panel Front Controller
 *
 * Entry point for all admin pages.
 * URL format: /admin/controller/method/param
 * Example:    /admin/products/edit/5  → ProductAdminController::edit(5)
 *
 * ALL routes through this file are protected — Auth::requireAdmin()
 * is called before any controller is instantiated.
 *
 * Owner: All members (common)
 */
require_once __DIR__ . '/config/bootstrap.php';

// Block non-admins at the gate
Auth::requireAdmin();

$url      = trim($_GET['url'] ?? 'dashboard', '/');
$segments = explode('/', filter_var($url, FILTER_SANITIZE_URL));

// Controller lives in app/controllers/admin/
$ctrlName = ucfirst(strtolower($segments[0] ?? 'dashboard')) . 'AdminController';
$method   = strtolower($segments[1] ?? 'index');
$params   = array_slice($segments, 2);

$ctrlFile = __DIR__ . "/app/controllers/admin/{$ctrlName}.php";

// Graceful fallback: products -> ProductAdminController, orders -> OrderAdminController, etc.
if (!file_exists($ctrlFile) && preg_match('/sAdminController$/', $ctrlName) === 1) {
    $singular = preg_replace('/sAdminController$/', 'AdminController', $ctrlName);
    $singularFile = __DIR__ . "/app/controllers/admin/{$singular}.php";
    if (file_exists($singularFile)) {
        $ctrlName = $singular;
        $ctrlFile = $singularFile;
    }
}

if (file_exists($ctrlFile)) {
    require_once $ctrlFile;
    $ctrl = new $ctrlName();
    if (method_exists($ctrl, $method)) {
        call_user_func_array([$ctrl, $method], $params);
    } else {
        http_response_code(404);
        echo 'Admin page not found.';
    }
} else {
    http_response_code(404);
    echo 'Admin controller not found.';
}
