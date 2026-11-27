<?php
/**
 * app/controllers/admin/CommentAdminController.php
 * Owner: Nhat Tan 
 *
 *  * Comment moderation panel.
 * Admin can approve pending comments or delete inappropriate ones.
 */
class CommentAdminController 
{
    private const REVIEW_PER_PAGE = 20;

    public function index(): void
    {
        $tab = (($_GET['tab'] ?? 'news') === 'reviews') ? 'reviews' : 'news';
        $newsCounts = Comment::getCounts();
        $reviewCounts = ProductReview::getCounts();

        if ($tab === 'reviews') {
            $status = trim((string)($_GET['status'] ?? 'all'));
            if (!in_array($status, ['all', 'pending', 'approved'], true)) {
                $status = 'all';
            }
            $filterStatus = $status === 'all' ? null : $status;
            $page = max(1, (int)($_GET['page'] ?? 1));

            $total = ProductReview::countAdminList($filterStatus);
            $pg = new Pagination($total, $page, self::REVIEW_PER_PAGE);
            $reviews = ProductReview::getAdminList($filterStatus, $pg->current, $pg->perPage);

            // T1-12 & T1-17: Lấy tên/slug sản phẩm cho cả trang chỉ bằng 1 lệnh find $in duy nhất
            // Review nằm ở PG, tên sản phẩm nằm ở Mongo. Mongo lỗi thì trang vẫn phải mở được để admin
            // duyệt tiếp (thao tác duyệt chỉ cần PG); view hiện "Sản phẩm #id" kèm cảnh báo.
            $productIds = array_column($reviews, 'product_id');
            $catalogUnavailable = false;
            try {
                $productsMap = Product::getManyByIds($productIds, ['name', 'slug']);
            } catch (MongoDB\Driver\Exception\Exception $e) {
                error_log('CommentAdminController reviews: không đọc được tên sản phẩm từ Mongo: ' . $e->getMessage());
                $productsMap = [];
                $catalogUnavailable = true;
            }

            $queryParams = ['tab' => 'reviews'];
            if ($status !== 'all') {
                $queryParams['status'] = $status;
            }
            $pageUrl = ADMIN_URL . 'comments?' . http_build_query($queryParams) . '&page=';

            SEO::set('Quản lý đánh giá sản phẩm');
            View::render('admin/comments/index', [
                'tab'          => 'reviews',
                'counts'       => $reviewCounts,
                'newsCounts'   => $newsCounts,
                'reviewCounts' => $reviewCounts,
                'reviews'      => $reviews,
                'productsMap'  => $productsMap,
                'catalogUnavailable' => $catalogUnavailable,
                'reviewStatus' => $status,
                'pg'           => $pg,
                'pageUrl'      => $pageUrl,
            ], 'admin');
            return;
        }

        $allComments = Comment::getAllAdmin();

        SEO::set('Quản lý bình luận tin tức');
        View::render('admin/comments/index', [
            'tab'          => 'news',
            'comments'     => $allComments,
            'counts'       => $newsCounts,
            'newsCounts'   => $newsCounts,
            'reviewCounts' => $reviewCounts,
        ], 'admin');
    }

    public function approve(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . ADMIN_URL . 'comment');
            exit;
        }

        Auth::verifyCsrf();

        if (Comment::approve($id)) {
            $_SESSION['flash'] = 'Đã duyệt bình luận hiển thị lên trang chủ.';
        } else {
            $_SESSION['errors'] = ['Lỗi không thể duyệt bình luận.'];
        }

        header('Location: ' . ADMIN_URL . 'comment');
        exit;
    }

    public function delete(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . ADMIN_URL . 'comment');
            exit;
        }

        Auth::verifyCsrf();

        if (Comment::delete($id)) {
            $_SESSION['flash'] = 'Đã xóa bình luận.';
        } else {
            $_SESSION['errors'] = ['Không thể xóa bình luận này.'];
        }

        header('Location: ' . ADMIN_URL . 'comment');
        exit;
    }

    // ------------------------------------------------------------------
    // Đánh giá sản phẩm (T1-17: POST + CSRF -> ReviewService đồng bộ PG & Mongo)
    // ------------------------------------------------------------------

    public function reviewApprove($id = 0): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: ' . ADMIN_URL . 'comments?tab=reviews');
            exit;
        }

        Auth::verifyCsrf();
        $id = (int)$id;

        if ($id > 0 && ReviewService::approve($id)) {
            $_SESSION['flash'] = 'Đã duyệt đánh giá sản phẩm và cập nhật điểm trung bình.';
        } else {
            $_SESSION['errors'] = ['Không tìm thấy hoặc không thể duyệt đánh giá này.'];
        }

        header('Location: ' . $this->reviewReturnUrl());
        exit;
    }

    public function reviewReject($id = 0): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: ' . ADMIN_URL . 'comments?tab=reviews');
            exit;
        }

        Auth::verifyCsrf();
        $id = (int)$id;

        if ($id > 0 && ReviewService::reject($id)) {
            $_SESSION['flash'] = 'Đã chuyển đánh giá về trạng thái ẩn (chờ duyệt) và cập nhật lại tóm tắt.';
        } else {
            $_SESSION['errors'] = ['Không tìm thấy hoặc không thể bỏ duyệt đánh giá này.'];
        }

        header('Location: ' . $this->reviewReturnUrl());
        exit;
    }

    public function reviewDelete($id = 0): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: ' . ADMIN_URL . 'comments?tab=reviews');
            exit;
        }

        Auth::verifyCsrf();
        $id = (int)$id;

        if ($id > 0 && ReviewService::delete($id)) {
            $_SESSION['flash'] = 'Đã xóa đánh giá sản phẩm và cập nhật điểm trung bình.';
        } else {
            $_SESSION['errors'] = ['Không tìm thấy hoặc không thể xóa đánh giá này.'];
        }

        header('Location: ' . $this->reviewReturnUrl());
        exit;
    }

    private function reviewReturnUrl(): string
    {
        $status = trim((string)($_POST['return_status'] ?? ''));
        $page = max(1, (int)($_POST['return_page'] ?? 1));
        $params = ['tab' => 'reviews'];
        if (in_array($status, ['pending', 'approved'], true)) {
            $params['status'] = $status;
        }
        if ($page > 1) {
            $params['page'] = $page;
        }
        return ADMIN_URL . 'comments?' . http_build_query($params);
    }
}