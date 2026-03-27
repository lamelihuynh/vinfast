<?php
/**
 * app/views/admin/users/edit.php
 * Owner  : All members (common)
 * Title  : Edit User
 *
 * Purpose: Edit name, email. Reset password button (generates random password). CSRF token required.
 *
 * Variables available (set by controller via View::render):
 *   $userData (array)
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
<!-- TODO: Implement Edit User -->
<section class="py-4">
  <div class="container">
    <h1 class="mb-3">Edit User</h1>
    <p class="text-muted">Owner: All members (common)</p>
    <p class="small">Edit name, email. Reset password button (generates random password). CSRF token required.</p>
  </div>
</section>
