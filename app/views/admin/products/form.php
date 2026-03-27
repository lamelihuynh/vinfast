<?php
/**
 * app/views/admin/products/form.php
 * Owner  : Hai Nam 
 * Title  : Product Form
 *
 * Purpose: Category select. Name, slug (auto). Description textarea. Specs section: dynamic key-value rows (JS). Price input. Dropzone.js multi-image upload area. Hidden id for edit. CSRF. POSTs to /admin/products/save.
 *
 * Variables available (set by controller via View::render):
 *   $product (array|null), $cats (array)
 *
 *
 * -----------------------------------------------------------------------
 * Rules:
 *  - Always escape output: <?= htmlspecialchars($var) ?>
 *  - Include CSRF in every form: <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
 *  - Include pagination partial where needed: include ROOT."/app/views/frontend/partials/pagination.php"
 */
?>

