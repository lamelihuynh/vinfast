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
    public function index(): void
    {
        $counts = Comment::getCounts();
        $allComments = Comment::getAllAdmin();

        View::render('admin/comments/index', [
            'comments' => $allComments,
            'counts'   => $counts
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
}