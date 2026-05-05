<?php
/**
 * app/views/admin/contacts/index.php
 * Owner  : Tang Vu
 * Title  : Customer Contacts & Test Drive Registrations
 *
 * Purpose: Dual-tab admin page: manage contact messages and test drive requests.
 *          Uses Srtdash-style tab navigation and Bootstrap tables.
 *
 * Variables available:
 *   $section (string), $items (array), $pg (Pagination)
 */
?>

<?php
$isContacts = $section === 'contacts';
$isTestDrives = $section === 'test-drives';

function vf_contact_status_badge(string $status): string
{
    $status = strtolower(trim($status));
    if ($status === 'unread') return '<span class="badge bg-danger">unread</span>';
    if ($status === 'replied') return '<span class="badge bg-success">replied</span>';
    return '<span class="badge bg-secondary">read</span>';
}

function vf_testdrive_status_badge(string $status): string
{
    $status = strtolower(trim($status));
    if ($status === 'pending') return '<span class="badge bg-warning text-dark">pending</span>';
    if ($status === 'confirmed') return '<span class="badge bg-success">confirmed</span>';
    if ($status === 'cancelled') return '<span class="badge bg-danger">cancelled</span>';
    return '<span class="badge bg-secondary">done</span>';
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
        'confirmed' => 'btn-outline-success',
        'cancelled' => 'btn-outline-danger',
        'done' => 'btn-outline-secondary',
    ];
    return $map[$status] ?? 'btn-outline-secondary';
}
?>

<style>
  .vf-admin-tabs {
    border-bottom: 2px solid #e9ecef;
    margin-bottom: 1.25rem;
  }
  .vf-admin-tabs a {
    display: inline-block;
    padding: 0.75rem 1.25rem;
    font-size: 0.9375rem;
    font-weight: 600;
    color: #6c757d;
    position: relative;
    text-decoration: none;
    transition: color 0.25s ease;
  }
  .vf-admin-tabs a:hover {
    color: #495057;
  }
  .vf-admin-tabs a.active {
    color: #4336fb;
  }
  .vf-admin-tabs a::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: -2px;
    width: 100%;
    height: 3px;
    background: #4336fb;
    border-radius: 3px 3px 0 0;
    transform: scaleX(0);
    transform-origin: center;
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }
  .vf-admin-tabs a.active::after {
    transform: scaleX(1);
  }
</style>

<div class="row">
  <div class="col-12">
    <!-- Srtdash Card Header Style -->
    <div class="card mb-4 mt-4">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-4">
          <h4 class="header-title mb-0"><i class="ti-email"></i> Quản lý Liên hệ & Lái thử</h4>
          <span class="badge badge-primary px-3 py-2">Tổng số: <?= htmlspecialchars((string)($pg->total ?? 0)) ?></span>
        </div>

        <!-- Tab Navigation - Srtdash Style -->
        <div class="vf-admin-tabs">
          <a href="<?= ADMIN_URL ?>contacts?section=contacts" class="<?= $isContacts ? 'active' : '' ?>">
            <i class="ti-comment-alt mr-2"></i>Tin nhắn khách hàng
          </a>
          <a href="<?= ADMIN_URL ?>contacts?section=test-drives" class="<?= $isTestDrives ? 'active' : '' ?>">
            <i class="ti-car mr-2"></i>Đăng ký lái thử xe
          </a>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <?php if ($isContacts): ?>
            <!-- ===== Contacts Table ===== -->
            <table class="table table-hover align-middle">
              <thead class="bg-light">
                <tr>
                  <th style="width: 140px;"><i class="ti-info-alt"></i> Trạng thái</th>
                  <th><i class="ti-user"></i> Người gửi</th>
                  <th><i class="ti-comment"></i> Nội dung</th>
                  <th style="width: 170px;"><i class="ti-time"></i> Ngày tạo</th>
                  <th style="width: 260px;" class="text-end">Hành động</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($items)): ?>
                  <tr><td colspan="5" class="text-center text-muted py-5">Chưa có tin nhắn nào.</td></tr>
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
                        <div class="text-muted"><?= htmlspecialchars($excerpt) ?></div>
                      </td>
                      <td class="text-muted small">
                        <?= htmlspecialchars((string)($m['created_at'] ?? '')) ?>
                      </td>
                      <td class="text-end">
                        <div class="d-flex justify-content-end gap-1">
                          <form action="<?= ADMIN_URL ?>contacts/setStatus/<?= (int)($m['id'] ?? 0) ?>" method="post">
                            <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                            <input type="hidden" name="status" value="read">
                            <button class="btn btn-sm btn-outline-secondary" type="submit" title="Mark as read">
                              <i class="fa-regular fa-eye"></i>
                            </button>
                          </form>

                          <form action="<?= ADMIN_URL ?>contacts/setStatus/<?= (int)($m['id'] ?? 0) ?>" method="post">
                            <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                            <input type="hidden" name="status" value="replied">
                            <button class="btn btn-sm btn-outline-success" type="submit" title="Mark as replied">
                              <i class="fa-solid fa-reply"></i>
                            </button>
                          </form>

                          <form action="<?= ADMIN_URL ?>contacts/delete/<?= (int)($m['id'] ?? 0) ?>" method="post" onsubmit="return confirm('Delete this message?');">
                            <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                            <button class="btn btn-sm btn-outline-danger" type="submit" title="Delete">
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
            <!-- ===== Test Drives Table ===== -->
            <table class="table table-hover align-middle">
              <thead class="bg-light">
                <tr>
                  <th style="width: 120px;"><i class="ti-info-alt"></i> Trạng thái</th>
                  <th><i class="ti-user"></i> Người đăng ký</th>
                  <th><i class="ti-car"></i> Phương tiện</th>
                  <th><i class="ti-location-pin"></i> Địa điểm</th>
                  <th style="width: 120px;"><i class="ti-calendar"></i> Ngày hẹn</th>
                  <th style="width: 170px;"><i class="ti-time"></i> Ngày tạo</th>
                  <th style="width: 300px;" class="text-end">Hành động</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($items)): ?>
                  <tr><td colspan="7" class="text-center text-muted py-5">Chưa có đăng ký lái thử nào.</td></tr>
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
                        <?= htmlspecialchars((string)($m['product_name'] ?? '—')) ?>
                      </td>
                      <td class="small">
                        <div class="text-muted"><?= htmlspecialchars((string)($m['province'] ?? '')) ?></div>
                        <div class="text-dark"><?= htmlspecialchars((string)($m['showroom'] ?? '')) ?></div>
                      </td>
                      <td class="small text-muted">
                        <?= htmlspecialchars((string)($m['preferred_date'] ?? '—')) ?>
                      </td>
                      <td class="text-muted small">
                        <?= htmlspecialchars((string)($m['created_at'] ?? '')) ?>
                      </td>
                      <td class="text-end">
                        <div class="d-flex justify-content-end gap-1">
                          <?php
                            $currentStatus = strtolower(trim((string)($m['status'] ?? 'pending')));
                            $nextStatuses = vf_testdrive_status_next($currentStatus);
                          ?>
                          <?php foreach ($nextStatuses as $ns): ?>
                            <form action="<?= ADMIN_URL ?>contacts/setTestDriveStatus/<?= (int)($m['id'] ?? 0) ?>" method="post">
                              <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                              <input type="hidden" name="status" value="<?= htmlspecialchars($ns) ?>">
                              <button class="btn btn-sm <?= vf_testdrive_btn_class($ns) ?>" type="submit">
                                <?= htmlspecialchars(vf_testdrive_action_label($ns)) ?>
                              </button>
                            </form>
                          <?php endforeach; ?>

                          <form action="<?= ADMIN_URL ?>contacts/deleteTestDrive/<?= (int)($m['id'] ?? 0) ?>" method="post" onsubmit="return confirm('Delete this registration?');">
                            <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                            <button class="btn btn-sm btn-outline-danger" type="submit" title="Delete">
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

        <!-- Pagination -->
        <?php if (!empty($pg) && ($pg->pages ?? 1) > 1): ?>
          <nav aria-label="Pagination">
            <ul class="pagination justify-content-center mb-0">
              <li class="page-item <?= $pg->hasPrev() ? '' : 'disabled' ?>">
                <a class="page-link" href="<?= ADMIN_URL ?>contacts/index/<?= max(1, $pg->current - 1) ?>?section=<?= htmlspecialchars($section) ?>">Prev</a>
              </li>
              <?php for ($i = 1; $i <= (int)$pg->pages; $i++): ?>
                <li class="page-item <?= $i === (int)$pg->current ? 'active' : '' ?>">
                  <a class="page-link" href="<?= ADMIN_URL ?>contacts/index/<?= $i ?>?section=<?= htmlspecialchars($section) ?>"><?= $i ?></a>
                </li>
              <?php endfor; ?>
              <li class="page-item <?= $pg->hasNext() ? '' : 'disabled' ?>">
                <a class="page-link" href="<?= ADMIN_URL ?>contacts/index/<?= min((int)$pg->pages, $pg->current + 1) ?>?section=<?= htmlspecialchars($section) ?>">Next</a>
              </li>
            </ul>
          </nav>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

