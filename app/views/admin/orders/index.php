<?php
/**
 * app/views/admin/orders/index.php
 * Owner  : Hai Nam 
 * Title  : Orders
 *
 * Purpose: Filter tabs by status (all/pending/confirmed/cancelled/done). Paginated table: user, product, type, status dropdown form, date. CSRF on status change.
 *
 * Variables available (set by controller via View::render):
 *   $orders (array), $status (string), $pg (Pagination)
 *
 *
 * -----------------------------------------------------------------------
 * Rules:
 *  - Always escape output: <?= htmlspecialchars($var) ?>
 *  - Include CSRF in every form: <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
 *  - Include pagination partial where needed: include ROOT."/app/views/frontend/partials/pagination.php"
 */
?>
