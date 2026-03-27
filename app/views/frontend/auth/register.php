<?php
/**
 * app/views/frontend/auth/register.php
 * Owner  : All members (common)
 * Title  : Register
 *
 * Purpose: Name, email, password, confirm-password. CSRF token. Matching password check in validate.js. Link to /auth/login.
 *
 * Variables available (set by controller via View::render):
 *   None
 *
  Assets    : public/js/frontend/validate.js
 *
 * TODO: Replace the placeholder below with the actual HTML implementation.
 * -----------------------------------------------------------------------
 * Rules:
 *  - Always escape output: <?= htmlspecialchars($var) ?>
 *  - Include CSRF in every form: <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
 *  - Include pagination partial where needed: include ROOT."/app/views/frontend/partials/pagination.php"
 */
?>
<!-- TODO: Implement Register -->
<section class="py-4">
  <div class="container">
    <h1 class="mb-3">Register</h1>
    <p class="text-muted">Owner: All members (common)</p>
    <p class="small">Name, email, password, confirm-password. CSRF token. Matching password check in validate.js. Link to /auth/login.</p>
  </div>
</section>
