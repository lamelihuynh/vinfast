<?php
/**
 * app/views/admin/faq/index.php
 * Owner  : Nhat Linh 
 * Title  : FAQ List
 *
 * Purpose: Paginated table: sort order, question preview, active badge, edit/delete actions. Link to /admin/faq/form for new entry.
 *
 * Variables available (set by controller via View::render):
 *   $faqs (array), $pg (Pagination)
 *
 *
 * -----------------------------------------------------------------------
 * Rules:
 *  - Always escape output: <?= htmlspecialchars($var) ?>
 *  - Include CSRF in every form: <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
 *  - Include pagination partial where needed: include ROOT."/app/views/frontend/partials/pagination.php"
 */
?>

