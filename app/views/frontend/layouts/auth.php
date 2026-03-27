<?php
/**
 * app/views/frontend/layouts/auth.php — Minimal Auth Layout
 * Owner: All members (common)
 *
 * Used only for login and register pages.
 * Centred card layout — no navbar or footer.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <?= SEO::titleTag() ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>public/css/frontend/global.css">
</head>
<body class="bg-light d-flex align-items-center min-vh-100">
  <div class="container py-5">
    <?php include ROOT . '/app/views/frontend/partials/flash.php'; ?>
    <?= $content ?>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?= BASE_URL ?>public/js/frontend/validate.js"></script>
</body>
</html>
