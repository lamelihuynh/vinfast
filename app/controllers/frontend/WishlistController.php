<?php

/**
 * app/controllers/frontend/WishlistController.php — Wishlist frontend controller
 * Owner: T2 (Indexing)
 * Routes:
 *   GET  /wishlist
 *   POST /wishlist/toggle
 *   POST /wishlist/remove
 */
class WishlistController
{
    public function index(): void
    {
        Auth::requireLogin();
        $userId = (int)Auth::id();

        $items = Wishlist::listOf($userId);
        $productIds = array_values(array_unique(array_column($items, 'product_id')));
        $productsById = $productIds ? Product::getByIds($productIds, true) : [];

        $wishlistItems = [];
        foreach ($items as $item) {
            $pid = (string)$item['product_id'];
            $product = $productsById[$pid] ?? null;
            $wishlistItems[] = [
                'item'            => $item,
                'product'         => $product,
                'is_discontinued' => $product === null || empty($product['is_active']),
            ];
        }

        SEO::set(
            'Sản phẩm yêu thích',
            'Danh sách xe điện và phụ kiện yêu thích của bạn tại VinFast',
            'vinfast, yêu thích, wishlist'
        );

        View::render('frontend/wishlist/index', [
            'wishlistItems' => $wishlistItems,
            'wishlistIds'   => $productIds,
        ]);
    }

    public function toggle(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (!Auth::check()) {
            $returnTo = $this->resolveReturnTo();
            http_response_code(401);
            echo json_encode([
                'status'    => 'error',
                'message'   => 'Vui lòng đăng nhập để lưu sản phẩm yêu thích.',
                'login_url' => BASE_URL . 'auth/login?return_to=' . urlencode($returnTo),
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        Auth::verifyCsrf();

        $input = $_POST;
        $raw = file_get_contents('php://input');
        if ($raw) {
            $json = json_decode($raw, true);
            if (is_array($json)) {
                $input = array_merge($input, $json);
            }
        }

        $productId = trim((string)($input['product_id'] ?? ''));
        $variantRaw = trim((string)($input['variant_id'] ?? ''));
        $variantId = $variantRaw !== '' ? $variantRaw : null;
        $state = trim((string)($input['state'] ?? ''));

        if ($productId === '' || !ctype_digit($productId)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Mã sản phẩm không hợp lệ.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (!in_array($state, ['on', 'off'], true)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Trạng thái wishlist không hợp lệ.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($state === 'on') {
            $product = Product::getByIds([$productId], false)[$productId] ?? null;
            if (!$product) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Sản phẩm không tồn tại hoặc đã ngừng bán.'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            if ($variantId !== null) {
                $variantIds = array_column((array)($product['variants'] ?? []), 'variantId');
                if (!in_array($variantId, $variantIds, true)) {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'Phiên bản sản phẩm không hợp lệ.'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }
        }

        $userId = (int)Auth::id();
        Wishlist::set($userId, $productId, $variantId, $state === 'on');

        ActivityLog::record(
            $state === 'on' ? 'add_to_wishlist' : 'remove_from_wishlist',
            $productId,
            ['customData' => ['variantId' => $variantId]]
        );

        echo json_encode([
            'status' => 'ok',
            'state'  => $state,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function remove(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        Auth::requireLogin();
        Auth::verifyCsrf();

        $productId = trim((string)($_POST['product_id'] ?? ''));
        $variantRaw = trim((string)($_POST['variant_id'] ?? ''));
        $variantId = $variantRaw !== '' ? $variantRaw : null;

        if ($productId !== '' && ctype_digit($productId)) {
            Wishlist::set((int)Auth::id(), $productId, $variantId, false);
            ActivityLog::record(
                'remove_from_wishlist',
                $productId,
                ['customData' => ['variantId' => $variantId]]
            );
            $_SESSION['flash'] = 'Đã bỏ sản phẩm khỏi danh sách yêu thích.';
        }

        header('Location: ' . BASE_URL . 'wishlist');
        exit;
    }

    private function resolveReturnTo(): string
    {
        $referer = trim((string)($_SERVER['HTTP_REFERER'] ?? ''));
        if ($referer === '') {
            return 'products';
        }

        $parsed = parse_url($referer);
        $baseHost = parse_url(BASE_URL, PHP_URL_HOST);
        if (!empty($parsed['host']) && $baseHost && strcasecmp((string)$parsed['host'], (string)$baseHost) !== 0) {
            return 'products';
        }

        $path = trim((string)($parsed['path'] ?? ''), '/');
        $basePath = trim((string)(parse_url(BASE_URL, PHP_URL_PATH) ?? ''), '/');
        if ($basePath !== '' && strpos($path, $basePath) === 0) {
            $path = trim(substr($path, strlen($basePath)), '/');
        }

        if ($path === '') {
            $path = 'products';
        }
        if (!empty($parsed['query'])) {
            $path .= '?' . $parsed['query'];
        }

        return $path;
    }
}
