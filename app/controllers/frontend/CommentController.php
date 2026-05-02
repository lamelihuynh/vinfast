<?php
/**
 * app/controllers/frontend/CommentController.php
 * Owner: Nhat Tan (Member 4)
 * Routes: POST /comment/post
 *
 *  * Handles comment submission from members on news articles and product pages.
 * Comments are stored as is_approved=0 until admin approves them.
 * Redirects back to the referring page after submission.
 */
class CommentController
{
    public function post(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL); exit;
        }
 
        Auth::requireLogin();
        Auth::verifyCsrf();
 
        $body      = trim($_POST['body'] ?? '');
        $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : null;
        $rating    = isset($_POST['rating']) ? (int)$_POST['rating'] : 5;
 
        if ($body === '') {
            $_SESSION['flash_error'] = 'Nội dung đánh giá không được để trống.';
            $this->redirectBack($productId);
            return;
        }

        if ($productId === null) {
            $_SESSION['flash_error'] = 'Yêu cầu không hợp lệ.';
            header('Location: ' . BASE_URL); exit;
        }

        if (mb_strlen($body) > 1000) {
            $_SESSION['flash_error'] = 'Đánh giá không được vượt quá 1000 ký tự.';
            $this->redirectBack($productId);
            return;
        }
 
        $ok = Comment::create(Auth::id(), $productId, $body, $rating);
 
        if ($ok) {
            $_SESSION['flash_success'] = 'Đánh giá của bạn đã được gửi và đang chờ duyệt.';
        } else {
            $_SESSION['flash_error'] = 'Có lỗi xảy ra, vui lòng thử lại.';
        }
        $this->redirectBack($productId);
    }

    public function vote_helpful(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::verifyCsrf();
            $commentId = (int)($_POST['comment_id'] ?? 0);
            if ($commentId > 0) {
                Comment::incrementHelpful($commentId);
            }
        }
        $this->redirectBack(null); 
    }
 
    private function redirectBack(?int $productId): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if ($referer !== '' && str_starts_with($referer, BASE_URL)) {
            header('Location: ' . $referer);
            exit;
        }
        
        if ($productId !== null) {
            header('Location: ' . BASE_URL . 'products/detail/' . $productId); 
            exit;
        }

        header('Location: ' . BASE_URL);
        exit;
    }
}
