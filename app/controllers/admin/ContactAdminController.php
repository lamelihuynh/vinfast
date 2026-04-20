<?php
/**
 * app/controllers/admin/ContactAdminController.php
 * Owner: Tang Vu (Member 1)
 * Routes: POST /admin/contacts/setStatus/{id}   POST /admin/contacts/delete/{id} , ..... more nhe
 *
 *  * View and manage customer contact messages.
 * Admin can mark messages as read, replied, or delete them.
 */
class ContactAdminController {
    public function index(int $page = 1): void
    {
        $page = max(1, (int)$page);
        $total = Contact::countAll();
        $pg = new Pagination($total, $page, PER_PAGE);

        $messages = Contact::getPaginated($pg->current, $pg->perPage);

        SEO::set('Customer contacts');
        View::render('admin/contacts/index', [
            'messages' => $messages,
            'pg' => $pg,
        ], 'admin');
    }

    public function setstatus(int $id): void
    {
        Auth::verifyCsrf();
        $status = (string)($_POST['status'] ?? 'read');
        Contact::setStatus((int)$id, $status);

        $_SESSION['flash'] = 'Đã cập nhật trạng thái.';
        header('Location: ' . ADMIN_URL . 'contacts');
        exit;
    }

    public function delete(int $id): void
    {
        Auth::verifyCsrf();
        Contact::deleteById((int)$id);

        $_SESSION['flash'] = 'Đã xoá liên hệ.';
        header('Location: ' . ADMIN_URL . 'contacts');
        exit;
    }
}
