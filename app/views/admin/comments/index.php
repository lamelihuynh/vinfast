<?php
/**
 * app/views/admin/comments/index.php
 * Owner  : Nhat Tan 
 * Title  : Comment Moderation
 *
 * Purpose: Paginated table: user, source (article or product link), comment excerpt, status badge (approved/pending), approve/delete actions. CSRF on both actions.
 *
 * Variables available (set by controller via View::render):
 *   $comments (array), $pg (Pagination)
 *
 *
 * -----------------------------------------------------------------------
 * Rules:
 *  - Always escape output: <?= htmlspecialchars($var) ?>
 *  - Include CSRF in every form: <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
 *  - Include pagination partial where needed: include ROOT."/app/views/frontend/partials/pagination.php"
 */
?>
