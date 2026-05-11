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

    $url = trim($_GET['url'] ?? '', '/');
    if ($url === '') {
        $url = 'home';
    }
    $segments = explode('/', filter_var($url, FILTER_SANITIZE_URL));

    // Controller lives in app/controllers/frontend/
    $ctrlName = ucfirst(strtolower($segments[0] ?? 'home')) . 'Controller';
    $method   = strtolower($segments[1] ?? 'index');
    $params   = array_slice($segments, 2);

    $ctrlFile = __DIR__ . "/app/controllers/frontend/{$ctrlName}.php";

    // Graceful fallback: products -> ProductController, news -> NewsController, etc.
    if (!file_exists($ctrlFile) && substr($ctrlName, -11) === 'sController') {
        $singular = substr($ctrlName, 0, -11) . 'Controller';
        $singularFile = __DIR__ . "/app/controllers/frontend/{$singular}.php";
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
            include __DIR__ . '/app/views/frontend/404.php';
        }
    } else {
        http_response_code(404);
        include __DIR__ . '/app/views/frontend/404.php';
    }

