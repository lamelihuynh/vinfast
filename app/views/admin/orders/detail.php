<?php

/**
 * app/views/admin/orders/detail.php
 * Owner  : All members (common)
 * Title  : Order Detail
 */
$order = is_array($order ?? null) ? $order : [];
$note = is_array($note ?? null) ? $note : [];
$allowedNextStatuses = is_array($allowedNextStatuses ?? null) ? $allowedNextStatuses : [];

$id = (int)($order['id'] ?? 0);
$orderCode = 'VF-ORD-' . str_pad((string)$id, 6, '0', STR_PAD_LEFT);
$status = trim((string)($order['status'] ?? 'pending'));
$createdAt = trim((string)($order['created_at'] ?? ''));

$statusMap = [
    'pending' => ['label' => 'Cho xu ly', 'badge' => 'badge-warning'],
    'confirmed' => ['label' => 'Da xac nhan', 'badge' => 'badge-info'],
    'done' => ['label' => 'Hoan tat', 'badge' => 'badge-success'],
    'cancelled' => ['label' => 'Da huy', 'badge' => 'badge-danger'],
];

$labelByStatus = static function (string $value) use ($statusMap): string {
    return $statusMap[$value]['label'] ?? ucfirst($value);
};

$badgeByStatus = static function (string $value) use ($statusMap): string {
    return $statusMap[$value]['badge'] ?? 'badge-secondary';
};

$typeMap = [
    'deposit' => 'Dat coc',
    'test_drive' => 'Lai thu',
];

$depositAmount = (float)($note['deposit_amount'] ?? 0);
$phone = trim((string)($note['phone'] ?? ''));
$email = trim((string)($note['email'] ?? ($order['email'] ?? '')));
$province = trim((string)($note['province'] ?? ''));
$showroom = trim((string)($note['showroom'] ?? ''));
$payMethod = trim((string)($note['pay_method'] ?? ''));

$payMethodMap = [
    'card-intl' => 'The quoc te',
    'card-domestic' => 'The ATM / Internet Banking',
    'transfer' => 'Chuyen khoan ngan hang',
];
?>

<div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
    <div>
        <h4 class="mb-1">Chi tiet don hang</h4>
        <div class="text-muted small"><?= htmlspecialchars($orderCode) ?></div>
    </div>
    <a class="btn btn-outline-secondary" href="<?= ADMIN_URL ?>orders">Quay lai danh sach</a>
</div>

<div class="row g-3">
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="mb-3">Thong tin don</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="small text-muted">Khach hang</div>
                        <div class="fw-semibold"><?= htmlspecialchars((string)($order['user_name'] ?? '')) ?></div>
                        <div class="small text-muted"><?= htmlspecialchars((string)($order['email'] ?? '')) ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">San pham</div>
                        <div class="fw-semibold"><?= htmlspecialchars((string)($order['product_name'] ?? '')) ?></div>
                        <div class="small text-muted"><?= number_format((float)($order['price'] ?? 0), 0, ',', '.') ?> VND</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Loai don</div>
                        <div><?= htmlspecialchars($typeMap[(string)($order['type'] ?? '')] ?? (string)($order['type'] ?? '')) ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Ngay tao</div>
                        <div><?= htmlspecialchars($createdAt !== '' ? date('d/m/Y H:i', strtotime($createdAt)) : '--') ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="mb-3">Thong tin dat coc tu client</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="small text-muted">So dien thoai</div>
                        <div><?= htmlspecialchars($phone !== '' ? $phone : '--') ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Email lien he</div>
                        <div><?= htmlspecialchars($email !== '' ? $email : '--') ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Tinh/Thanh</div>
                        <div><?= htmlspecialchars($province !== '' ? $province : '--') ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Showroom</div>
                        <div><?= htmlspecialchars($showroom !== '' ? $showroom : '--') ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Thanh toan</div>
                        <div><?= htmlspecialchars($payMethodMap[$payMethod] ?? ($payMethod !== '' ? $payMethod : '--')) ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Tien coc</div>
                        <div class="fw-semibold"><?= $depositAmount > 0 ? htmlspecialchars(number_format($depositAmount, 0, ',', '.') . ' VND') : '--' ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="mb-3">Trang thai hien tai</h6>
                <span class="badge <?= htmlspecialchars($badgeByStatus($status)) ?> mb-3"><?= htmlspecialchars($labelByStatus($status)) ?></span>

                <form method="post" action="<?= ADMIN_URL ?>orders/setstatus/<?= $id ?>">
                    <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                    <input type="hidden" name="redirect" value="detail">

                    <label for="status" class="form-label small text-muted">Cap nhat trang thai</label>
                    <select id="status" name="status" class="form-select mb-3">
                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Cho xu ly</option>
                        <option value="confirmed" <?= $status === 'confirmed' ? 'selected' : '' ?>>Da xac nhan</option>
                        <option value="done" <?= $status === 'done' ? 'selected' : '' ?>>Hoan tat</option>
                        <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Da huy</option>
                    </select>

                    <button type="submit" class="btn btn-primary w-100">Luu trang thai</button>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="mb-3">Luong xu ly hop le</h6>
                <ul class="mb-0 ps-3">
                    <li>Cho xu ly -> Da xac nhan hoac Da huy</li>
                    <li>Da xac nhan -> Hoan tat hoac Da huy</li>
                    <li>Hoan tat, Da huy la trang thai ket thuc</li>
                </ul>
                <hr>
                <div class="small text-muted mb-1">Trang thai co the chuyen tiep tu hien tai:</div>
                <?php if (empty($allowedNextStatuses)): ?>
                    <span class="badge badge-secondary">Khong co</span>
                <?php else: ?>
                    <?php foreach ($allowedNextStatuses as $next): ?>
                        <span class="badge badge-light border me-1 mb-1"><?= htmlspecialchars($labelByStatus((string)$next)) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>