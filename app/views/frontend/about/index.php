<?php
/**
 * app/views/frontend/about/index.php
 * Owner  : Nhat Linh (Member 2)
 * Title  : About VinFast
 *
 * Purpose: Company story, mission/vision, timeline. Content from $settings["about_text"] and $settings["about_image"].
 *
 * Variables available (set by controller via View::render):
 *   $settings (array)
 *
 *
 * -----------------------------------------------------------------------
 * Rules:
 *  - Always escape output: <?= htmlspecialchars($var) ?>
 *  - Include CSRF in every form: <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
 *  - Include pagination partial where needed: include ROOT."/app/views/frontend/partials/pagination.php"
 */
?>
