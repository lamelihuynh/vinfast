<?php
/**
 * app/controllers/admin/UserAdminController.php
 * Owner: All members (common)
 * Routes: /admin/users  /admin/users/edit/{id}
 *         POST /admin/users/lock/{id}
 *         POST /admin/users/delete/{id}
 *         POST /admin/users/resetPassword/{id}
 *
 * Full user management: list, edit, lock/unlock, delete, reset password.
 */
class UserAdminController {
    private User $user;

    public function __construct()
    {
        $this->user = new User();
    }

    public function index($page = 1): void
    {
        $q = trim((string)($_GET['q'] ?? ''));
        $role = trim((string)($_GET['role'] ?? ''));
        $page = max(1, (int)$page);

        $result = $this->user->paginateAdmin($q, $role, $page, 10);

        View::render('admin/users/index', [
            'users' => $result['users'],
            'pg' => $result['pg'],
            'q' => $q,
            'role' => $role
        ], 'admin');
    }

    public function updateRole(int $id): void
    {
        Auth::verifyCsrf();
        $role = trim((string)($_POST['role'] ?? 'member'));
        if (!in_array($role, ['admin', 'member'])) {
            $role = 'member';
        }

        $target = $this->user->findById($id);
        if ($target) {
            // Prevent changing one's own role
            if ((int)$target['id'] === (int)Auth::id()) {
                $_SESSION['errors'] = ['Không thể tự thay đổi quyền của chính mình.'];
            } else {
                $this->user->updateRole($id, $role);
                $_SESSION['flash'] = 'Cập nhật phân quyền thành công.';
            }
        }

        header('Location: ' . ADMIN_URL . 'users');
        exit;
    }

    public function delete(int $id): void
    {
        Auth::verifyCsrf();

        $target = $this->user->findById($id);
        if ($target) {
            if ((int)$target['id'] === (int)Auth::id()) {
                $_SESSION['errors'] = ['Không thể xóa tài khoản của chính mình.'];
            } else {
                $this->user->delete($id);
                $_SESSION['flash'] = 'Đã xóa tài khoản.';
            }
        }

        header('Location: ' . ADMIN_URL . 'users');
        exit;
    }
}
