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
<?php
$q = (string)($q ?? '');
$cat = (int)($cat ?? 0);
$status = (string)($status ?? 'all');
$products = is_array($products ?? null) ? $products : [];
$cats = is_array($cats ?? null) ? $cats : [];
$summary = is_array($summary ?? null) ? $summary : [];
$productsForModal = is_array($productsForModal ?? null) ? $productsForModal : [];
?>

<?php include ROOT . '/app/views/admin/products/partials/page-header.php'; ?>

<?php include ROOT . '/app/views/admin/products/partials/stats.php'; ?>

<div class="card">
    <?php include ROOT . '/app/views/admin/products/partials/filters.php'; ?>
    <?php include ROOT . '/app/views/admin/products/partials/table.php'; ?>
    <?php include ROOT . '/app/views/admin/products/partials/pagination.php'; ?>
</div>
<?php include ROOT . '/app/views/admin/products/partials/modal.php'; ?>
<?php include ROOT . '/app/views/admin/products/partials/modal-script.php'; ?>