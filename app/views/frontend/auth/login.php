<?php
/**
 * app/views/frontend/auth/login.php
 * Owner  : All members (common)
 * Title  : Login
 *
 * Purpose: Bootstrap card, centred. Email + password inputs. CSRF hidden input. Client-side validation (validate.js). Link to /auth/register.
 *
 * Variables available (set by controller via View::render):
 *   None (flash/errors from session)
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
<!-- TODO: Implement Login -->
<section class="py-4">
  <div class="container">
    <h1 class="mb-3">Login</h1>
    <p class="text-muted">Owner: All members (common)</p>
    <p class="small">Bootstrap card, centred. Email + password inputs. CSRF hidden input. Client-side validation (validate.js). Link to /auth/register.</p>
  </div>
</section>
