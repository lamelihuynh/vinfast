<?php
/**
 * app/views/admin/news/index.php
 * Owner  : Nhat Tan (Member 4)
 * Title  : News List
 *
 * Purpose: Search bar. Paginated table: thumbnail, title, author, date, edit/delete actions.
 *
 * Variables available (set by controller via View::render):
 *   $articles (array), $q (string), $pg (Pagination)
 *
 *
 * -----------------------------------------------------------------------
 * Rules:
 *  - Always escape output: <?= htmlspecialchars($var) ?>
 *  - Include CSRF in every form: <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
 *  - Include pagination partial where needed: include ROOT."/app/views/frontend/partials/pagination.php"
 */
?>
