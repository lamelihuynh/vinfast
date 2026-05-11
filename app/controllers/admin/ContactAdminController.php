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
class ContactAdminController
{

    public function index(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $section = trim((string) ($_GET['section'] ?? 'contacts'));
        $status = trim((string) ($_GET['status'] ?? ''));

        if (!in_array($section, ['contacts', 'test-drives'], true)) {
            $section = 'contacts';
        }

        if ($section === 'test-drives') {
            $total = TestDrive::countAll($status);
            $counts = [
                'all' => TestDrive::countAll(),
                'pending' => TestDrive::countAll('pending'),
                'confirmed' => TestDrive::countAll('confirmed'),
                'done' => TestDrive::countAll('done'),
                'cancelled' => TestDrive::countAll('cancelled'),
            ];
            $pg = new Pagination($total, $page, 7);
            $items = TestDrive::getPaginated($pg->current, $pg->perPage, $status);
        } else {
            $total = Contact::countAll($status);
            $counts = [
                'all' => Contact::countAll(),
                'unread' => Contact::countAll('unread'),
                'read' => Contact::countAll('read'),
                'replied' => Contact::countAll('replied'),
            ];
            $pg = new Pagination($total, $page, 7);
            $items = Contact::getPaginated($pg->current, $pg->perPage, $status);
        }

        $query = array_filter([
            'section' => $section !== 'contacts' ? $section : null,
            'status' => $status !== '' ? $status : null,
        ], static function ($value): bool {
            return $value !== null;
        });
        $baseQuery = http_build_query($query);
        $pageUrl = ADMIN_URL . 'contacts/index?' . ($baseQuery !== '' ? $baseQuery . '&' : '') . 'page=';

        SEO::set('Customer contacts');
        View::render('admin/contacts/index', [
            'section' => $section,
            'status' => $status,
            'counts' => $counts,
            'items' => $items,
            'pg' => $pg,
            'pageUrl' => $pageUrl,
        ], 'admin');
    }

    public function setstatus(int $id): void
    {
        Auth::verifyCsrf();
        $status = (string) ($_POST['status'] ?? 'read');
        Contact::setStatus((int) $id, $status);

        $_SESSION['flash'] = 'Đã cập nhật trạng thái.';
        header('Location: ' . ADMIN_URL . 'contacts?section=contacts');
        exit;
    }

    public function delete(int $id): void
    {
        Auth::verifyCsrf();
        Contact::deleteById((int) $id);

        $_SESSION['flash'] = 'Đã xoá liên hệ.';
        header('Location: ' . ADMIN_URL . 'contacts?section=contacts');
        exit;
    }

    public function settestdrivestatus(int $id): void
    {
        Auth::verifyCsrf();
        $status = (string) ($_POST['status'] ?? 'confirmed');
        TestDrive::setStatus((int) $id, $status);

        $_SESSION['flash'] = 'Đã cập nhật trạng thái đăng ký lái thử.';
        header('Location: ' . ADMIN_URL . 'contacts?section=test-drives');
        exit;
    }

    public function deletetestdrive(int $id): void
    {
        Auth::verifyCsrf();
        TestDrive::deleteById((int) $id);

        $_SESSION['flash'] = 'Đã xoá đăng ký lái thử.';
        header('Location: ' . ADMIN_URL . 'contacts?section=test-drives');
        exit;
    }
}

