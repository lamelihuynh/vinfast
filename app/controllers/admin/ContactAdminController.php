<?php
/**
 * app/controllers/admin/ContactAdminController.php
 * Owner: Tang Vu (Member 1)
 * Routes:
 *   GET  /admin/contacts/index/{page}?section=contacts|test-drives
 *   POST /admin/contacts/setStatus/{id}
 *   POST /admin/contacts/delete/{id}
 *   POST /admin/contacts/setTestDriveStatus/{id}
 *   POST /admin/contacts/deleteTestDrive/{id}
 *
 * View and manage customer contact messages AND test drive registrations.
 * Admin can mark messages as read/replied, or update test drive status.
 */
class ContactAdminController {

    public function index(int $page = 1): void
    {
        $page = max(1, (int)$page);
        $section = trim((string)($_GET['section'] ?? 'contacts'));
        if (!in_array($section, ['contacts', 'test-drives'], true)) {
            $section = 'contacts';
        }

        if ($section === 'test-drives') {
            $total = TestDrive::countAll();
            $pg = new Pagination($total, $page, PER_PAGE);
            $items = TestDrive::getPaginated($pg->current, $pg->perPage);
        } else {
            $total = Contact::countAll();
            $pg = new Pagination($total, $page, PER_PAGE);
            $items = Contact::getPaginated($pg->current, $pg->perPage);
        }

        SEO::set('Customer contacts');
        View::render('admin/contacts/index', [
            'section' => $section,
            'items' => $items,
            'pg' => $pg,
        ], 'admin');
    }

    public function setstatus(int $id): void
    {
        Auth::verifyCsrf();
        $status = (string)($_POST['status'] ?? 'read');
        Contact::setStatus((int)$id, $status);

        $_SESSION['flash'] = 'Đã cập nhật trạng thái.';
        header('Location: ' . ADMIN_URL . 'contacts?section=contacts');
        exit;
    }

    public function delete(int $id): void
    {
        Auth::verifyCsrf();
        Contact::deleteById((int)$id);

        $_SESSION['flash'] = 'Đã xoá liên hệ.';
        header('Location: ' . ADMIN_URL . 'contacts?section=contacts');
        exit;
    }

    public function settestdrivestatus(int $id): void
    {
        Auth::verifyCsrf();
        $status = (string)($_POST['status'] ?? 'confirmed');
        TestDrive::setStatus((int)$id, $status);

        $_SESSION['flash'] = 'Đã cập nhật trạng thái đăng ký lái thử.';
        header('Location: ' . ADMIN_URL . 'contacts?section=test-drives');
        exit;
    }

    public function deletetestdrive(int $id): void
    {
        Auth::verifyCsrf();
        TestDrive::deleteById((int)$id);

        $_SESSION['flash'] = 'Đã xoá đăng ký lái thử.';
        header('Location: ' . ADMIN_URL . 'contacts?section=test-drives');
        exit;
    }
}

