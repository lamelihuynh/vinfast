<?php
/**
 * app/views/admin/news/form.php
 * Owner  : Nhat Tan
 * Title  : News Article Editor
 *
 * Purpose: Title input (also feeds slug). Body textarea with TinyMCE WYSIWYG. Thumbnail upload. SEO section: meta_title, meta_description. Hidden id for edit. CSRF. POSTs to /admin/news/save.
 *
 * Variables available (set by controller via View::render):
 *   $article (array|null)
 *
 *
 * -----------------------------------------------------------------------
 * Rules:
 *  - Always escape output: <?= htmlspecialchars($var) ?>
 *  - Include CSRF in every form: <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
 *  - Include pagination partial where needed: include ROOT."/app/views/frontend/partials/pagination.php"
 */
?>

