<?php

/**
 * app/views/admin/orders/index.php
 * Owner  : Hai Nam 
 * Title  : Orders
 *
 * Purpose: Filter tabs by status (all/pending/confirmed/cancelled/done). Paginated table: user, product, type, status dropdown form, date. CSRF on status change.
 *
 * Variables available (set by controller via View::render):
 *   $orders (array), $status (string), $q (string), $summary (array), $pg (Pagination), $pageUrl (string)
 *
 *
 * -----------------------------------------------------------------------
 * Rules:
 *  - Always escape output: <?= htmlspecialchars($var) ?>
 *  - Include CSRF in every form: <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
 *  - Include pagination partial where needed: include ROOT."/app/views/frontend/partials/pagination.php"
 */
?>
<?php
$ordersRaw = $orders ?? [];
$orders = is_iterable($ordersRaw) ? $ordersRaw : [];
$status = trim((string)($status ?? 'all'));
$q = trim((string)($q ?? ''));
$summary = is_array($summary ?? null) ? $summary : [];

$statusMap = [
    'pending' => ['label' => 'Chờ xử lý', 'badge' => 'badge-warning'],
    'confirmed' => ['label' => 'Đã xác nhận', 'badge' => 'badge-info'],
    'done' => ['label' => 'Hoàn tất', 'badge' => 'badge-success'],
    'cancelled' => ['label' => 'Đã hủy', 'badge' => 'badge-danger'],
];

$typeMap = [
    'deposit' => 'Đặt cọc',
    'test_drive' => 'Lái thử',
];

$labelByStatus = static function (string $value) use ($statusMap): string {
    return $statusMap[$value]['label'] ?? ucfirst($value);
};

$badgeByStatus = static function (string $value) use ($statusMap): string {
    return $statusMap[$value]['badge'] ?? 'badge-secondary';
};

$extractPhone = static function ($note): string {
    $raw = trim((string)$note);
    if ($raw === '') {
        return '';
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return '';
    }

    return trim((string)($decoded['phone'] ?? ''));
};

$extractOrderDetail = static function (array $order) use ($typeMap, $labelByStatus, $extractPhone): array {
    $id = (int)($order['id'] ?? 0);
    $rawNote = trim((string)($order['note'] ?? ''));
    $note = [];
    if ($rawNote !== '') {
        $decoded = json_decode($rawNote, true);
        if (is_array($decoded)) {
            $note = $decoded;
        }
    }

    $payMethodMap = [
        'card-intl' => 'Thẻ quốc tế',
        'card-domestic' => 'Thẻ ATM / Internet Banking',
        'transfer' => 'Chuyển khoản ngân hàng',
    ];

    $paymentStatusMap = [
        'unpaid' => ['label' => 'Chưa thanh toán', 'badge' => 'badge-secondary'],
        'pending_verify' => ['label' => 'Chờ xác nhận thanh toán', 'badge' => 'badge-primary'],
        'paid' => ['label' => 'Đã nhận cọc', 'badge' => 'badge-success'],
        'failed' => ['label' => 'Thanh toán thất bại', 'badge' => 'badge-danger'],
        'refunded' => ['label' => 'Đã hoàn tiền', 'badge' => 'badge-info'],
    ];

    $paymentStatusRaw = Order::getPaymentStatusFromNote($note);
    $paymentStatusUi = $paymentStatusMap[$paymentStatusRaw] ?? $paymentStatusMap['pending_verify'];

    return [
        'id' => $id,
        'orderCode' => 'VF-ORD-' . str_pad((string)$id, 6, '0', STR_PAD_LEFT),
        'createdAt' => !empty($order['created_at']) ? date('d/m/Y H:i', strtotime((string)$order['created_at'])) : '--',
        'customerName' => (string)($note['full_name'] ?? ($order['user_name'] ?? '')),
        'email' => (string)($note['email'] ?? ''),
        'phone' => (string)($note['phone'] ?? $extractPhone($order['note'] ?? '')),
        'productName' => (string)($order['product_name'] ?? ''),
        'price' => number_format((float)($order['price'] ?? 0), 0, ',', '.') . ' VND',
        'type' => $typeMap[(string)($order['type'] ?? '')] ?? (string)($order['type'] ?? ''),
        'status' => $labelByStatus((string)($order['status'] ?? 'pending')),
        'statusRaw' => (string)($order['status'] ?? 'pending'),
        'ownerType' => (string)($note['owner_type'] ?? ''),
        'province' => (string)($note['province'] ?? ''),
        'showroom' => (string)($note['showroom'] ?? ''),
        'payMethod' => $payMethodMap[(string)($note['pay_method'] ?? '')] ?? (string)($note['pay_method'] ?? ''),
        'depositAmount' => !empty($note['deposit_amount']) ? number_format((float)$note['deposit_amount'], 0, ',', '.') . ' VND' : '--',
        'paymentStatus' => $paymentStatusUi['label'],
        'paymentStatusRaw' => $paymentStatusRaw,
        'paymentStatusBadge' => $paymentStatusUi['badge'],
    ];
};
?>

<?php include ROOT . '/app/views/admin/orders/partials/summary.php'; ?>

<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <?php include ROOT . '/app/views/admin/orders/partials/filters.php'; ?>
            </div>
        </div>
    </div>
</div>

<?php include ROOT . '/app/views/admin/orders/partials/table.php'; ?>
<?php include ROOT . '/app/views/admin/orders/partials/detail-modal.php'; ?>
