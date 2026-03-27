<?php
/**
 * app/views/admin/partials/flash.php — Flash Messages (Admin)
 * Owner: All members (common)
 */
if (!empty($_SESSION["flash"])): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($_SESSION["flash"]) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION["flash"]); endif; ?>
<?php if (!empty($_SESSION["errors"])): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <ul class="mb-0"><?php foreach ($_SESSION["errors"] as $e): ?>
      <li><?= htmlspecialchars($e) ?></li>
    <?php endforeach; ?></ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION["errors"]); endif; ?>
