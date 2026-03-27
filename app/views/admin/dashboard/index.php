<?php
/**
 * app/views/admin/dashboard/index.php
 * Owner  : All members (common)
 * Title  : Dashboard
 *
 * Purpose: Srtdash stat cards: total users, products, unread contacts, pending orders. Quick-action buttons to each admin section.
 *
 * Variables available (set by controller via View::render):
 *   $stats (assoc array: users/products/contacts/orders)
 *
 *
 * -----------------------------------------------------------------------
 * Rules:
 *  - Always escape output: <?= htmlspecialchars($var) ?>
 *  - Include CSRF in every form: <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
 *  - Include pagination partial where needed: include ROOT."/app/views/frontend/partials/pagination.php"
 */
?>
