<?php
/**
 * app/views/admin/users/index.php
 * Owner  : All members (common)
 * Title  : User Management
 *
 * Purpose: Search bar. Paginated table: ID, name, email, role, status (locked badge), actions (edit, lock, delete). All POST actions include CSRF.
 *
 * Variables available (set by controller via View::render):
 *   $users (array), $q (string), $pg (Pagination)
 *
  Assets    : (none)
 *
 * TODO: Replace the placeholder below with the actual HTML implementation.
 * -----------------------------------------------------------------------
 * Rules:
 *  - Always escape output: <?= htmlspecialchars($var) ?>
 *  - Include CSRF in every form: <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
 *  - Include pagination partial where needed: include ROOT."/app/views/frontend/partials/pagination.php"
 */
?>
<!-- TODO: Implement User Management -->
<section class="py-4">
  <div class="container">
    <h1 class="mb-3">User Management</h1>
    <p class="text-muted">Owner: All members (common)</p>
    <p class="small">Search bar. Paginated table: ID, name, email, role, status (locked badge), actions (edit, lock, delete). All POST actions include CSRF.</p>
  </div>
</section>
