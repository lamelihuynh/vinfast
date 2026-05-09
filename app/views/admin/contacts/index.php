<?php
/**
 * app/views/admin/contacts/index.php
 * Owner  : Tang Vu
 * Title  : Customer Contacts & Test Drive Registrations
 *
 * Purpose: Dual-tab admin page: manage contact messages and test drive requests.
 *          Uses Shadcn-style resources for layout.
 *
 * Variables available:
 *   $section (string), $items (array), $pg (Pagination), $counts (array), $status (string)
 */
?>
<?php
$isContacts = $section === 'contacts';
$isTestDrives = $section === 'test-drives';

function vf_contact_status_badge(string $status): string
{
    $status = strtolower(trim($status));
    if ($status === 'unread') return '<span class="status-p bg-danger text-white" style="padding:6px 12px;border-radius:4px;display:inline-block"><i class="fa-solid fa-envelope me-1"></i>Chưa đọc</span>';
    if ($status === 'replied') return '<span class="status-p bg-success text-white" style="padding:6px 12px;border-radius:4px;display:inline-block"><i class="fa-solid fa-reply me-1"></i>Đã phản hồi</span>';
    return '<span class="status-p bg-secondary text-white" style="padding:6px 12px;border-radius:4px;display:inline-block"><i class="fa-regular fa-envelope-open me-1"></i>Đã đọc</span>';
}

function vf_testdrive_status_badge(string $status): string
{
    $status = strtolower(trim($status));
    if ($status === 'pending') return '<span class="status-p bg-warning text-dark" style="padding:6px 12px;border-radius:4px;display:inline-block"><i class="fa-regular fa-clock me-1"></i>Chờ duyệt</span>';
    if ($status === 'confirmed') return '<span class="status-p bg-primary text-white" style="padding:6px 12px;border-radius:4px;display:inline-block"><i class="fa-solid fa-check me-1"></i>Xác nhận</span>';
    if ($status === 'cancelled') return '<span class="status-p bg-danger text-white" style="padding:6px 12px;border-radius:4px;display:inline-block"><i class="fa-solid fa-ban me-1"></i>Đã hủy</span>';
    return '<span class="status-p bg-success text-white" style="padding:6px 12px;border-radius:4px;display:inline-block"><i class="fa-solid fa-check-double me-1"></i>Hoàn tất</span>';
}

function vf_testdrive_status_next(string $current): array
{
    $map = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['done', 'cancelled'],
        'cancelled' => [],
        'done' => [],
    ];
    return $map[strtolower(trim($current))] ?? [];
}

function vf_testdrive_action_label(string $status): string
{
    $labels = [
        'confirmed' => 'Xác nhận',
        'cancelled' => 'Huỷ',
        'done' => 'Hoàn tất',
    ];
    return $labels[$status] ?? $status;
}

function vf_testdrive_btn_class(string $status): string
{
    $map = [
        'confirmed' => 'btn-outline-primary',
        'cancelled' => 'btn-outline-danger',
        'done' => 'btn-outline-success',
    ];
    return $map[$status] ?? 'btn-outline-secondary';
}
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h4 class="header-title mb-0"><i class="ti-email me-2"></i>Quản lý Liên hệ & Lái thử</h4>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <div class="btn-group" role="group">
            <a href="<?= ADMIN_URL ?>contacts?section=contacts" class="btn <?= $isContacts ? 'btn-primary' : 'btn-outline-primary' ?>">Tin nhắn</a>
            <a href="<?= ADMIN_URL ?>contacts?section=test-drives" class="btn <?= $isTestDrives ? 'btn-primary' : 'btn-outline-primary' ?>">Lái thử xe</a>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <?php if ($isContacts): ?>
        <div class="col-6 col-xl-3">
            <a href="<?= ADMIN_URL ?>contacts?section=contacts&status=" class="text-decoration-none">
                <div class="card border-0 text-white" style="background:linear-gradient(135deg,#1464f4,#3b7cf8)">
                    <div class="card-body py-3">
                        <div class="small opacity-75">Tất cả</div>
                        <div class="h4 mb-0"><?= (int)($counts['all'] ?? 0) ?></div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-xl-3">
            <a href="<?= ADMIN_URL ?>contacts?section=contacts&status=unread" class="text-decoration-none">
                <div class="card border-0 text-white" style="background:linear-gradient(135deg,#ef4444,#dc2626)">
                    <div class="card-body py-3">
                        <div class="small opacity-75">Chưa đọc</div>
                        <div class="h4 mb-0"><?= (int)($counts['unread'] ?? 0) ?></div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-xl-3">
            <a href="<?= ADMIN_URL ?>contacts?section=contacts&status=read" class="text-decoration-none">
                <div class="card border-0 text-white" style="background:linear-gradient(135deg,#6b7280,#4b5563)">
                    <div class="card-body py-3">
                        <div class="small opacity-75">Đã đọc</div>
                        <div class="h4 mb-0"><?= (int)($counts['read'] ?? 0) ?></div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-xl-3">
            <a href="<?= ADMIN_URL ?>contacts?section=contacts&status=replied" class="text-decoration-none">
                <div class="card border-0 text-white" style="background:linear-gradient(135deg,#10b981,#059669)">
                    <div class="card-body py-3">
                        <div class="small opacity-75">Đã phản hồi</div>
                        <div class="h4 mb-0"><?= (int)($counts['replied'] ?? 0) ?></div>
                    </div>
                </div>
            </a>
        </div>
    <?php else: ?>
        <div class="col-6 col-xl-3">
            <a href="<?= ADMIN_URL ?>contacts?section=test-drives&status=" class="text-decoration-none">
                <div class="card border-0 text-white" style="background:linear-gradient(135deg,#1464f4,#3b7cf8)">
                    <div class="card-body py-3">
                        <div class="small opacity-75">Tất cả</div>
                        <div class="h4 mb-0"><?= (int)($counts['all'] ?? 0) ?></div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-xl-3">
            <a href="<?= ADMIN_URL ?>contacts?section=test-drives&status=pending" class="text-decoration-none">
                <div class="card border-0 text-white" style="background:linear-gradient(135deg,#f59e0b,#d97706)">
                    <div class="card-body py-3">
                        <div class="small opacity-75">Chờ duyệt</div>
                        <div class="h4 mb-0"><?= (int)($counts['pending'] ?? 0) ?></div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-xl-3">
            <a href="<?= ADMIN_URL ?>contacts?section=test-drives&status=confirmed" class="text-decoration-none">
                <div class="card border-0 text-white" style="background:linear-gradient(135deg,#10b981,#059669)">
                    <div class="card-body py-3">
                        <div class="small opacity-75">Xác nhận</div>
                        <div class="h4 mb-0"><?= (int)($counts['confirmed'] ?? 0) ?></div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-xl-3">
            <a href="<?= ADMIN_URL ?>contacts?section=test-drives&status=cancelled" class="text-decoration-none">
                <div class="card border-0 text-white" style="background:linear-gradient(135deg,#ef4444,#dc2626)">
                    <div class="card-body py-3">
                        <div class="small opacity-75">Đã hủy</div>
                        <div class="h4 mb-0"><?= (int)($counts['cancelled'] ?? 0) ?></div>
                    </div>
                </div>
            </a>
        </div>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body border-bottom pb-3">
        <form method="GET" action="<?= ADMIN_URL ?>contacts" class="row g-2 align-items-end mb-2">
            <input type="hidden" name="section" value="<?= htmlspecialchars($section) ?>">
            <div class="col-md-4">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="" <?= $status === '' ? 'selected' : '' ?>>Tất cả trạng thái</option>
                    <?php if ($isContacts): ?>
                        <option value="unread" <?= $status === 'unread' ? 'selected' : '' ?>>Chưa đọc</option>
                        <option value="read" <?= $status === 'read' ? 'selected' : '' ?>>Đã đọc</option>
                        <option value="replied" <?= $status === 'replied' ? 'selected' : '' ?>>Đã phản hồi</option>
                    <?php else: ?>
                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Chờ duyệt</option>
                        <option value="confirmed" <?= $status === 'confirmed' ? 'selected' : '' ?>>Xác nhận</option>
                        <option value="done" <?= $status === 'done' ? 'selected' : '' ?>>Hoàn tất</option>
                        <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <button class="btn btn-primary w-100 btn-sm" type="submit">
                    <i class="fa-solid fa-filter me-1"></i>Lọc
                </button>
            </div>
            <div class="col-6 col-md-2">
                <a href="<?= ADMIN_URL ?>contacts?section=<?= htmlspecialchars($section) ?>" class="btn btn-outline-secondary w-100 btn-sm">
                    <i class="fa-solid fa-rotate-left me-1"></i>Đặt lại
                </a>
            </div>
        </form>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <?php if ($isContacts): ?>
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase">
                        <tr>
                            <th scope="col" style="width: 140px;">Trạng thái</th>
                            <th scope="col">Người gửi</th>
                            <th scope="col">Nội dung</th>
                            <th scope="col" style="width: 170px;">Ngày tạo</th>
                            <th scope="col" style="width: 150px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fa-solid fa-inbox" style="font-size:3em;opacity:0.3"></i>
                                        <p class="mt-3">Chưa có tin nhắn nào.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($items as $m): ?>
                                <tr>
                                    <td><?= vf_contact_status_badge((string)($m['status'] ?? 'unread')) ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars((string)($m['name'] ?? '')) ?></div>
                                        <div class="text-muted small">
                                            <?= htmlspecialchars((string)($m['email'] ?? '')) ?>
                                            <?php if (!empty($m['phone'])): ?>
                                                · <?= htmlspecialchars((string)$m['phone']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                            $msg = (string)($m['message'] ?? '');
                                            $excerpt = mb_strlen($msg) > 120 ? mb_substr($msg, 0, 120) . '…' : $msg;
                                        ?>
                                        <div class="text-muted small"><?= htmlspecialchars($excerpt) ?></div>
                                    </td>
                                    <td class="text-muted small">
                                        <?= htmlspecialchars((string)($m['created_at'] ?? '')) ?>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-start gap-2">
                                            <form action="<?= ADMIN_URL ?>contacts/setStatus/<?= (int)($m['id'] ?? 0) ?>" method="post">
                                                <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                                                <input type="hidden" name="status" value="read">
                                                <button class="btn btn-link text-secondary p-0" type="submit" title="Đánh dấu đã đọc">
                                                    <i class="fa-regular fa-eye"></i>
                                                </button>
                                            </form>
                                            <form action="<?= ADMIN_URL ?>contacts/setStatus/<?= (int)($m['id'] ?? 0) ?>" method="post">
                                                <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                                                <input type="hidden" name="status" value="replied">
                                                <button class="btn btn-link text-success p-0" type="submit" title="Đánh dấu đã phản hồi">
                                                    <i class="fa-solid fa-reply"></i>
                                                </button>
                                            </form>
                                            <form action="<?= ADMIN_URL ?>contacts/delete/<?= (int)($m['id'] ?? 0) ?>" method="post" onsubmit="return confirm('Bạn có chắc chắn muốn xoá tin nhắn này?');">
                                                <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                                                <button class="btn btn-link text-danger p-0" type="submit" title="Xóa">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase">
                        <tr>
                            <th scope="col" style="width: 140px;">Trạng thái</th>
                            <th scope="col">Người đăng ký</th>
                            <th scope="col">Phương tiện</th>
                            <th scope="col">Địa điểm & Ngày hẹn</th>
                            <th scope="col" style="width: 170px;">Ngày tạo</th>
                            <th scope="col" style="width: 200px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fa-solid fa-inbox" style="font-size:3em;opacity:0.3"></i>
                                        <p class="mt-3">Chưa có đăng ký lái thử nào.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($items as $m): ?>
                                <tr>
                                    <td><?= vf_testdrive_status_badge((string)($m['status'] ?? 'pending')) ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars((string)($m['name'] ?? '')) ?></div>
                                        <div class="text-muted small">
                                            <?= htmlspecialchars((string)($m['email'] ?? '')) ?>
                                            <?php if (!empty($m['phone'])): ?>
                                                · <?= htmlspecialchars((string)$m['phone']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge text-black badge-primary"><?= htmlspecialchars((string)($m['product_name'] ?? '—')) ?></span>
                                    </td>
                                    <td class="small">
                                        <div class="text-dark fw-semibold"><?= htmlspecialchars((string)($m['showroom'] ?? '')) ?></div>
                                        <div class="text-muted mb-1"><?= htmlspecialchars((string)($m['province'] ?? '')) ?></div>
                                        <div class="text-primary"><i class="fa-regular fa-calendar me-1"></i><?= htmlspecialchars((string)($m['preferred_date'] ?? '—')) ?></div>
                                    </td>
                                    <td class="text-muted small">
                                        <?= htmlspecialchars((string)($m['created_at'] ?? '')) ?>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-start gap-1 flex-wrap">
                                            <?php
                                                $currentStatus = strtolower(trim((string)($m['status'] ?? 'pending')));
                                                $nextStatuses = vf_testdrive_status_next($currentStatus);
                                            ?>
                                            <?php foreach ($nextStatuses as $ns): ?>
                                                <form action="<?= ADMIN_URL ?>contacts/setTestDriveStatus/<?= (int)($m['id'] ?? 0) ?>" method="post">
                                                    <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                                                    <input type="hidden" name="status" value="<?= htmlspecialchars($ns) ?>">
                                                    <button class="btn btn-sm <?= vf_testdrive_btn_class($ns) ?> py-0 px-2" style="font-size: 11px;" type="submit">
                                                        <?= htmlspecialchars(vf_testdrive_action_label($ns)) ?>
                                                    </button>
                                                </form>
                                            <?php endforeach; ?>
                                            <form action="<?= ADMIN_URL ?>contacts/deleteTestDrive/<?= (int)($m['id'] ?? 0) ?>" method="post" onsubmit="return confirm('Bạn có chắc chắn muốn xoá đăng ký này?');">
                                                <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                                                <button class="btn btn-link text-danger p-0 ms-2" type="submit" title="Xóa">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <?php
        $itemName = $isContacts ? 'tin nhắn' : 'lượt đăng ký';
        if (!empty($pg) && ($pg->pages ?? 1) > 1) {
            include ROOT . '/app/views/admin/partials/pagination.php';
        }
        ?>
    </div>
</div>
