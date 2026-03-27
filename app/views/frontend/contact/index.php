<?php
/**
 * app/views/frontend/contact/index.php
 * Owner  : Tang Vu 
 * Title  : Contact
 *
 * Purpose: Contact form: name, email, phone, message. POSTs to /contact/send. Company info (address, phone, email) from $settings.
 *
 * Variables available (set by controller via View::render):
 *   $settings (array)
 *
 *
 * -----------------------------------------------------------------------
 * Rules:
 *  - Always escape output: <?= htmlspecialchars($var) ?>
 *  - Include CSRF in every form: <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
 *  - Include pagination partial where needed: include ROOT."/app/views/frontend/partials/pagination.php"
 */
?>
<!-- TODO: Implement Contact -->
<