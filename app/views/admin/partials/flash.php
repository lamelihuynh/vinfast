<?php
/**
 * app/views/admin/partials/flash.php — Flash Messages (Admin)
 * Owner: All members (common)
 */
if (!empty($_SESSION["flash"])): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert" id="flashSuccess">
    <i class="fa-solid fa-circle-check me-2"></i><?= htmlspecialchars($_SESSION["flash"]) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <script>
    // Auto dismiss success message after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
      const alert = document.getElementById('flashSuccess');
      if (alert) {
        setTimeout(function() {
          const bsAlert = new bootstrap.Alert(alert);
          bsAlert.close();
        }, 5000);
      }
    });
  </script>
  <?php unset($_SESSION["flash"]); endif; ?>
<?php if (!empty($_SESSION["errors"])): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fa-solid fa-circle-exclamation me-2"></i><strong>Lỗi:</strong>
    <ul class="mb-0 mt-2"><?php foreach ($_SESSION["errors"] as $e): ?>
      <li><?= htmlspecialchars($e) ?></li>
    <?php endforeach; ?></ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION["errors"]); endif; ?>