<?php
/**
 * app/views/frontend/partials/flash.php — Flash Messages (Frontend)
 * Owner: All members (common)
 *
 * Reads $_SESSION["flash"] (success) and $_SESSION["errors"] (validation errors),
 * renders Bootstrap alerts, then clears them.
 */
if (!empty($_SESSION["flash"])): ?>
  <div class="alert alert-success alert-dismissible fade show m-2" role="alert">
    <?= htmlspecialchars($_SESSION["flash"]) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION["flash"]); endif; ?>

<?php if (!empty($_SESSION["errors"])): ?>
  <div class="alert alert-danger alert-dismissible fade show m-2" role="alert">
    <ul class="mb-0">
      <?php foreach ($_SESSION["errors"] as $e): ?>
        <li><?= htmlspecialchars($e) ?></li>
      <?php endforeach; ?>
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION["errors"]); endif; ?>
