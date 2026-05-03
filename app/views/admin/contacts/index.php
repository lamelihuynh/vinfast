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
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h4 class="mb-0"><?= $isContacts ? 'Customer Messages' : 'Test Drive Registrations' ?></h4>
      <span class="text-muted small">Total: <?= htmlspecialchars((string)($pg->total ?? 0)) ?></span>
    </div>

    <!-- Tab Navigation -->
    <div class="vf-admin-tabs">
      <a href="<?= ADMIN_URL ?>contacts?section=contacts" class="<?= $isContacts ? 'active' : '' ?>">
        <i class="fa-regular fa-envelope mr-1"></i> Liên hệ
      </a>
      <a href="<?= ADMIN_URL ?>contacts?section=test-drives" class="<?= $isTestDrives ? 'active' : '' ?>">
        <i class="fa-solid fa-car mr-1"></i> Đăng ký lái thử
      </a>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <?php if ($isContacts): ?>
            <!-- ===== Contacts Table ===== -->
            <table class="table table-hover align-middle">
              <thead>
                <tr>
                  <th style="width: 140px;">Status</th>
                  <th>Sender</th>
                  <th>Message</th>
                  <th style="width: 170px;">Created</th>
                  <th style="width: 260px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($items)): ?>
                  <tr><td colspan="5" class="text-center text-muted py-4">No messages.</td></tr>
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
                        <form class="d-inline" action="<?= ADMIN_URL ?>contacts/setStatus/<?= (int)($m['id'] ?? 0) ?>" method="post">
                          <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                          <input type="hidden" name="status" value="read">
                          <button class="btn btn-sm btn-outline-secondary" type="submit">Mark read</button>
                        </form>

                        <form class="d-inline" action="<?= ADMIN_URL ?>contacts/setStatus/<?= (int)($m['id'] ?? 0) ?>" method="post">
                          <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                          <input type="hidden" name="status" value="replied">
                          <button class="btn btn-sm btn-outline-success" type="submit">Mark replied</button>
                        </form>

                        <form class="d-inline" action="<?= ADMIN_URL ?>contacts/delete/<?= (int)($m['id'] ?? 0) ?>" method="post" onsubmit="return confirm('Delete this message?');">
                          <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                          <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>

          <?php else: ?>
            <!-- ===== Test Drives Table ===== -->
            <table class="table table-hover align-middle">
              <thead>
                <tr>
                  <th style="width: 120px;">Status</th>
                  <th>Registrant</th>
                  <th>Vehicle</th>
                  <th>Location</th>
                  <th style="width: 120px;">Preferred Date</th>
                  <th style="width: 170px;">Created</th>
                  <th style="width: 300px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($items)): ?>
                  <tr><td colspan="7" class="text-center text-muted py-4">No test drive registrations.</td></tr>
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
                        <?php
                          $currentStatus = strtolower(trim((string)($m['status'] ?? 'pending')));
                          $nextStatuses = vf_testdrive_status_next($currentStatus);
                        ?>
                        <?php foreach ($nextStatuses as $ns): ?>
                          <form class="d-inline" action="<?= ADMIN_URL ?>contacts/setTestDriveStatus/<?= (int)($m['id'] ?? 0) ?>" method="post">
                            <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                            <input type="hidden" name="status" value="<?= htmlspecialchars($ns) ?>">
                            <button class="btn btn-sm <?= vf_testdrive_btn_class($ns) ?>" type="submit"><?= htmlspecialchars(vf_testdrive_action_label($ns)) ?></button>
                          </form>
                        <?php endforeach; ?>

                        <form class="d-inline" action="<?= ADMIN_URL ?>contacts/deleteTestDrive/<?= (int)($m['id'] ?? 0) ?>" method="post" onsubmit="return confirm('Delete this registration?');">
                          <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                          <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                        </form>
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

