<?php
/**
 * app/views/frontend/user/profile.php
 * Owner  : All members (common)
 * Title  : My Profile
 *
 * Purpose: Two-section layout: (1) Edit name, email, avatar upload — POSTs to /user/saveProfile. (2) Change password — POSTs to /user/changePassword. Both need CSRF token.
 *
 * Variables available (set by controller via View::render):
 *   $user (array)
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
<!-- TODO: Implement My Profile -->
<section class="py-4">
  <div class="container">
    <h1 class="mb-3">My Profile</h1>
    <p class="text-muted">Owner: All members (common)</p>
    <p class="small">Two-section layout: (1) Edit name, email, avatar upload — POSTs to /user/saveProfile. (2) Change password — POSTs to /user/changePassword. Both need CSRF token.</p>
  </div>
</section>
