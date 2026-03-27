<?php
/**
 * app/views/admin/settings/index.php
 * Owner  : Tang Vu 
 * Title  : Site Settings
 *
 * Purpose: Form groups: (1) Logo upload. (2) Banner 1-3 upload with preview. (3) Contact info: address, phone, email. (4) About page: text (textarea), image upload. (5) Social links. All use CSRF token.
 *
 * Variables available (set by controller via View::render):
 *   $settings (assoc array)
 *
 *
 * -----------------------------------------------------------------------
 * Rules:
 *  - Always escape output: <?= htmlspecialchars($var) ?>
 *  - Include CSRF in every form: <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
 *  - Include pagination partial where needed: include ROOT."/app/views/frontend/partials/pagination.php"
 */
?>
