<?php

/**
 * app/controllers/admin/OrderAdminController.php
 * Owner: Hai Nam 
 * Routes: /admin/orders/detail/{id}  POST /admin/orders/setStatus/{id}
 *
 *  * View all deposit and test-drive orders. 
 * Admin can filter by status and update each order's status.
 */
class OrderAdminController
{
    private const PER_PAGE = 5;

    public function index(): void
    {
        $q = trim((string)($_GET['q'] ?? ''));
        $status = trim((string)($_GET['status'] ?? 'all'));
        $dateFrom = trim((string)($_GET['date_from'] ?? ''));
        $dateTo = trim((string)($_GET['date_to'] ?? ''));

        $allowed = array_merge(['all'], Order::validStatuses());
        if (!in_array($status, $allowed, true)) {
            $status = 'all';
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $filters = [
            'q' => $q,
            'status' => $status,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];

        $total = Order::countAdminList($filters);
        $pg = new Pagination($total, $page, self::PER_PAGE);
        $orders = Order::getAdminList($filters, $pg->current, $pg->perPage);

        $summary = Order::countAdminPaymentSummary([
            'q' => $q,
            'status' => $status,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]);

        $query = [];
        if ($q !== '') {
            $query['q'] = $q;
        }
        if ($status !== 'all') {
            $query['status'] = $status;
        }
        if ($dateFrom !== '') {
            $query['date_from'] = $dateFrom;
        }
        if ($dateTo !== '') {
            $query['date_to'] = $dateTo;
        }
        $baseQuery = http_build_query($query);
        $pageUrl = ADMIN_URL . 'orders?' . ($baseQuery !== '' ? $baseQuery . '&' : '') . 'page=';

        SEO::set('Admin Orders');
        View::render('admin/orders/index', [
            'orders' => $orders,
            'status' => $status,
            'q' => $q,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'summary' => $summary,
            'pg' => $pg,
            'pageUrl' => $pageUrl,
        ], 'admin');
    }

    public function detail($id = 0): void
    {
        $id = (int)$id;
        if ($id <= 0) {
            $_SESSION['errors'] = ['Mã đơn không hợp lệ.'];
            header('Location: ' . ADMIN_URL . 'orders');
            exit;
        }

        $order = Order::getById($id);
        if (!$order) {
            $_SESSION['errors'] = ['Không tìm thấy đơn hàng.'];
            header('Location: ' . ADMIN_URL . 'orders');
            exit;
        }

        $note = [];
        $rawNote = trim((string)($order['note'] ?? ''));
        if ($rawNote !== '') {
            $decoded = json_decode($rawNote, true);
            if (is_array($decoded)) {
                $note = $decoded;
            }
        }

        SEO::set('Order Detail #' . (int)$order['id']);
        View::render('admin/orders/detail', [
            'order' => $order,
            'note' => $note,
            'allowedNextStatuses' => Order::allowedNextStatuses((string)($order['status'] ?? 'pending')),
        ], 'admin');
    }

    public function setstatus($id = 0): void
    {
        Auth::verifyCsrf();

        $id = (int)$id;
        $nextStatus = trim((string)($_POST['status'] ?? ''));
        $redirect = trim((string)($_POST['redirect'] ?? 'index'));

        if ($id <= 0) {
            $_SESSION['errors'] = ['Mã đơn không hợp lệ.'];
            $this->redirectAfterUpdate($redirect, $id);
        }

        $order = Order::getById($id);
        if (!$order) {
            $_SESSION['errors'] = ['Không tìm thấy đơn hàng.'];
            $this->redirectAfterUpdate($redirect, $id);
        }

        $currentStatus = trim((string)($order['status'] ?? 'pending'));
        $paymentStatus = Order::getPaymentStatusFromNote($order['note'] ?? null);
        if (!in_array($nextStatus, Order::validStatuses(), true)) {
            $_SESSION['errors'] = ['Trạng thái cập nhật không hợp lệ.'];
            $this->redirectAfterUpdate($redirect, $id);
        }

        if ($nextStatus === 'confirmed' && $paymentStatus !== 'paid') {
            $_SESSION['errors'] = ['Chỉ có thể xác nhận đơn khi trạng thái thanh toán là Đã nhận cọc.'];
            $this->redirectAfterUpdate($redirect, $id);
        }

        if ($nextStatus === $currentStatus) {
            $_SESSION['flash'] = 'Đơn hàng đã ở trạng thái ' . $this->statusLabel($currentStatus) . '.';
            $this->redirectAfterUpdate($redirect, $id);
        }

        if (!Order::canTransition($currentStatus, $nextStatus)) {
            $nextAllowed = array_map([$this, 'statusLabel'], Order::allowedNextStatuses($currentStatus));
            $hint = empty($nextAllowed) ? 'Không thể chuyển tiếp.' : ('Chỉ cho phép: ' . implode(', ', $nextAllowed) . '.');
            $_SESSION['errors'] = ['Không thể chuyển từ ' . $this->statusLabel($currentStatus) . ' sang ' . $this->statusLabel($nextStatus) . '. ' . $hint];
            $this->redirectAfterUpdate($redirect, $id);
        }

        $ok = Order::updateStatus($id, $nextStatus);
        if (!$ok) {
            $_SESSION['errors'] = ['Không thể cập nhật trạng thái đơn hàng.'];
            $this->redirectAfterUpdate($redirect, $id);
        }

        $_SESSION['flash'] = 'Đã cập nhật trạng thái đơn #' . (int)$id . ' sang ' . $this->statusLabel($nextStatus) . '.';
        $this->redirectAfterUpdate($redirect, $id);
    }

    public function setpayment($id = 0): void
    {
        Auth::verifyCsrf();

        $id = (int)$id;
        $nextPaymentStatus = trim((string)($_POST['payment_status'] ?? 'pending_verify'));
        $redirect = trim((string)($_POST['redirect'] ?? 'index'));

        if ($id <= 0) {
            $_SESSION['errors'] = ['Mã đơn không hợp lệ.'];
            $this->redirectAfterUpdate($redirect, $id);
        }

        $order = Order::getById($id);
        if (!$order) {
            $_SESSION['errors'] = ['Không tìm thấy đơn hàng.'];
            $this->redirectAfterUpdate($redirect, $id);
        }

        if (!in_array($nextPaymentStatus, Order::validPaymentStatuses(), true)) {
            $_SESSION['errors'] = ['Trạng thái thanh toán không hợp lệ.'];
            $this->redirectAfterUpdate($redirect, $id);
        }

        $ok = Order::updatePaymentStatus($id, $nextPaymentStatus);
        if (!$ok) {
            $_SESSION['errors'] = ['Không thể cập nhật trạng thái thanh toán.'];
            $this->redirectAfterUpdate($redirect, $id);
        }

        $_SESSION['flash'] = 'Đã cập nhật thanh toán đơn #' . (int)$id . ' sang ' . $this->paymentStatusLabel($nextPaymentStatus) . '.';
        $this->redirectAfterUpdate($redirect, $id);
    }

    private function redirectAfterUpdate(string $redirect, int $id): void
    {
        if ($redirect === 'detail' && $id > 0) {
            header('Location: ' . ADMIN_URL . 'orders/detail/' . $id);
            exit;
        }

        header('Location: ' . ADMIN_URL . 'orders');
        exit;
    }

    private function statusLabel(string $status): string
    {
        $map = [
            'pending' => 'Chờ xử lý',
            'confirmed' => 'Đã xác nhận',
            'done' => 'Hoàn tất',
            'cancelled' => 'Đã hủy',
        ];
        return $map[$status] ?? $status;
    }

    private function paymentStatusLabel(string $status): string
    {
        $map = [
            'unpaid' => 'Chưa thanh toán',
            'pending_verify' => 'Chờ xác nhận thanh toán',
            'paid' => 'Đã nhận cọc',
            'failed' => 'Thanh toán thất bại',
            'refunded' => 'Đã hoàn tiền',
        ];

        return $map[$status] ?? $status;
    }
}
