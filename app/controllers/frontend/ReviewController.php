<?php

/**
 * app/controllers/frontend/ReviewController.php — Gửi & phân trang đánh giá sản phẩm (T1-16)
 * Owner: T1 (Data Storage)
 *
 * Routes:
 *   POST /review/post          -> post()
 *   GET  /review/list/{id}     -> list($productId)
 */
class ReviewController
{
    /** Khớp kích thước mảng `reviews[]` nhúng trên Mongo (5 bài/trang) để trang 2 nối tiếp từ bài thứ 6. */
    private const PER_PAGE = 5;

    public function post(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: ' . BASE_URL . 'products');
            exit;
        }

        $productIdInt = (int)($_POST['product_id'] ?? 0);
        $detailPath = $productIdInt > 0 ? ('/products/detail/' . $productIdInt . '#danhgia') : '/products';

        if (!Auth::check()) {
            header('Location: ' . BASE_URL . 'auth/login?redirect=' . urlencode($detailPath));
            exit;
        }

        Auth::verifyCsrf();

        $product = $productIdInt > 0 ? Product::getById($productIdInt) : null;
        if (!$product) {
            $_SESSION['errors'] = ['Sản phẩm không tồn tại hoặc đã ngừng kinh doanh.'];
            header('Location: ' . BASE_URL . 'products');
            exit;
        }

        $productIdStr = (string)$product['product_id'];
        $rating = (int)($_POST['rating'] ?? 0);
        $body = trim(strip_tags((string)($_POST['body'] ?? '')));
        $len = function_exists('mb_strlen') ? mb_strlen($body, 'UTF-8') : strlen($body);

        $errors = [];
        if ($rating < 1 || $rating > 5) {
            $errors[] = 'Số sao đánh giá phải nằm trong khoảng từ 1 đến 5.';
        }
        if ($len < 10 || $len > 2000) {
            $errors[] = 'Nội dung đánh giá phải từ 10 đến 2000 ký tự.';
        }

        if ($errors) {
            $_SESSION['errors'] = $errors;
            $_SESSION['review_old'] = [
                'product_id' => $productIdStr,
                'rating'     => ($rating >= 1 && $rating <= 5) ? $rating : 5,
                'body'       => $body,
            ];
            header('Location: ' . BASE_URL . 'products/detail/' . (int)$product['id'] . '#danhgia');
            exit;
        }

        $reviewId = ProductReview::create((int)Auth::id(), $productIdStr, $rating, $body);
        if ($reviewId === null) {
            $_SESSION['errors'] = ['Bạn đã gửi đánh giá cho sản phẩm này trước đó.'];
            header('Location: ' . BASE_URL . 'products/detail/' . (int)$product['id'] . '#danhgia');
            exit;
        }

        try {
            ActivityLog::record('review', $productIdStr, [
                'customData' => [
                    'reviewId' => $reviewId,
                    'rating'   => $rating,
                ],
            ]);
        } catch (Throwable $e) {
            error_log('ReviewController::post ActivityLog: ' . $e->getMessage());
        }

        $_SESSION['flash'] = 'Đánh giá đang chờ duyệt.';
        header('Location: ' . BASE_URL . 'products/detail/' . (int)$product['id'] . '#danhgia');
        exit;
    }

    /**
     * Trả HTML partial các bài đánh giá tiếp theo đọc từ bảng PostgreSQL `product_reviews`.
     *
     * Lý do chọn phân trang (T1-16):
     *   - Trang 1 (5 bài đầu tiên) đã đọc trực tiếp từ mảng nhúng `products.reviews[]` bên Mongo (0 query PG).
     *   - Từ trang 2 trở đi (`?page=2`, `PER_PAGE = 5`), hỗ trợ cả hai chế độ:
     *     1. Keyset pagination (`?before_ts=...&before_id=...`) với điều kiện `(created_at, id) < (...)`
     *        trên partial index `idx_reviews_product_approved`: giữ độ trễ ổn định O(log N) kể cả khi sản phẩm
     *        có hàng chục nghìn review và không bị trùng/trượt dòng nếu có bài mới vừa được duyệt.
     *     2. OFFSET pagination (`?page=n`): đơn giản cho URL đánh số trang trực tiếp khi số trang nhỏ.
     */
    public function list($productId = ''): void
    {
        $pid = trim((string)$productId);
        if ($pid === '' || preg_match('/^[0-9]+$/', $pid) !== 1) {
            http_response_code(400);
            echo '';
            return;
        }

        $page = max(1, (int)($_GET['page'] ?? 2));
        $beforeTs = isset($_GET['before_ts']) ? trim((string)$_GET['before_ts']) : null;
        $beforeId = isset($_GET['before_id']) ? (int)$_GET['before_id'] : null;

        $reviews = ProductReview::getApprovedByProduct(
            $pid,
            $page,
            self::PER_PAGE,
            ($beforeTs !== null && $beforeTs !== '') ? $beforeTs : null,
            ($beforeId !== null && $beforeId > 0) ? $beforeId : null
        );

        header('Content-Type: text/html; charset=UTF-8');
        View::render('frontend/products/partials/detail/review-list', [
            'reviews' => $reviews,
        ], 'none');
    }
}
