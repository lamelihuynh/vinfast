<?php
/**
 * app/views/admin/products/index.php
 * Owner  : Hai Nam 
 * Title  : Product List
 *
 * Purpose: Search bar. Paginated table: thumbnail, name, category, price, active badge, edit/delete actions.
 *
 * Variables available (set by controller via View::render):
 *   $products (array), $q (string), $pg (Pagination)
 *
 *
 * -----------------------------------------------------------------------
 * Rules:
 *  - Always escape output: <?= htmlspecialchars($var) ?>
 *  - Include CSRF in every form: <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
 *  - Include pagination partial where needed: include ROOT."/app/views/frontend/partials/pagination.php"
 */
?>
