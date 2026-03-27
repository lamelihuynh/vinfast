<?php
/**
 * app/views/admin/faq/form.php
 * Owner  : Nhat Linh 
 * Title  : FAQ Form
 *
 * Purpose: Question input (max 500), answer textarea (max 2000), sort order number, active checkbox. Hidden id field for edit. CSRF token. POSTs to /admin/faq/save.
 *
 * Variables available (set by controller via View::render):
 *   $faq (array|null — null for new)
 *
 *
 * -----------------------------------------------------------------------
 * Rules:
 *  - Always escape output: <?= htmlspecialchars($var) ?>
 *  - Include CSRF in every form: <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
 *  - Include pagination partial where needed: include ROOT."/app/views/frontend/partials/pagination.php"
 */
?>
