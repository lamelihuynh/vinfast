<?php
/**
 * app/views/admin/contacts/index.php
 * Owner  : Tang Vu 
 * Title  : Customer Messages
 *
 * Purpose: Paginated table: sender name, email, phone, message excerpt, status badge (colour-coded), date. Actions: set status (unread/read/replied), delete. CSRF on all POST forms.
 *
 * Variables available (set by controller via View::render):
 *   $messages (array), $pg (Pagination)
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
// =========================================================
// ADMIN: CONTACT MESSAGES
// - List + update status + delete
// - Mục tiêu: hỗ trợ requirement #1 trong assignment.pdf
// =========================================================

function vf_status_badge(string $status): string
{
    $status = strtolower(trim($status));
    if ($status === 'unread') return 'badge bg-danger';
    if ($status === 'replied') return 'badge bg-success';
    return 'badge bg-secondary';
}
?>

<div class="row">
  <div class="col-12">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h4 class="mb-0">Customer messages</h4>
      <span class="text-muted small">Total: <?= htmlspecialchars((string)($pg->total ?? 0)) ?></span>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
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
              <?php if (empty($messages)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">No messages.</td></tr>
              <?php else: ?>
                <?php foreach ($messages as $m): ?>
                  <tr>
                    <td>
                      <?php $status = (string)($m['status'] ?? 'unread'); ?>
                      <span class="<?= vf_status_badge($status) ?>"><?= htmlspecialchars($status) ?></span>
                    </td>
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
                      <!-- ===== Update status ===== -->
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

                      <!-- ===== Delete ===== -->
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
        </div>

        <!-- ===== Pagination (simple) ===== -->
        <?php if (!empty($pg) && ($pg->pages ?? 1) > 1): ?>
          <nav aria-label="Pagination">
            <ul class="pagination justify-content-center mb-0">
              <li class="page-item <?= $pg->hasPrev() ? '' : 'disabled' ?>">
                <a class="page-link" href="<?= ADMIN_URL ?>contacts/index/<?= max(1, $pg->current - 1) ?>">Prev</a>
              </li>
              <?php for ($i = 1; $i <= (int)$pg->pages; $i++): ?>
                <li class="page-item <?= $i === (int)$pg->current ? 'active' : '' ?>">
                  <a class="page-link" href="<?= ADMIN_URL ?>contacts/index/<?= $i ?>"><?= $i ?></a>
                </li>
              <?php endfor; ?>
              <li class="page-item <?= $pg->hasNext() ? '' : 'disabled' ?>">
                <a class="page-link" href="<?= ADMIN_URL ?>contacts/index/<?= min((int)$pg->pages, $pg->current + 1) ?>">Next</a>
              </li>
            </ul>
          </nav>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
