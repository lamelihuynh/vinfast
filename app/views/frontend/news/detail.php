<?php
/**
 * app/views/frontend/news/detail.php
 * Owner  : Nhat Tan 
 * Title  : Article Reader
 *
 * Purpose: Full article: thumbnail, title, author, date, body (echo $article["body"] — sanitise TinyMCE output with appropriate method). Comment list. Comment form (members only, CSRF token required).
 *
 * Variables available (set by controller via View::render):
 *   $article (array), $comments (array)
 *
  Assets    : public/css/frontend/news.css  |  public/js/frontend/comments.js
 *
 * TODO: Replace the placeholder below with the actual HTML implementation.
 * -----------------------------------------------------------------------
 * Rules:
 *  - Always escape output: <?= htmlspecialchars($var) ?>
 *  - Include CSRF in every form: <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
 *  - Include pagination partial where needed: include ROOT."/app/views/frontend/partials/pagination.php"
 */
?>
<!-- TODO: Implement Article Reader -->
