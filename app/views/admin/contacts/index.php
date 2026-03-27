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
