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
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <?= SEO::titleTag() ?>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>public/css/frontend/global.css?v=<?= filemtime(ROOT . '/public/css/frontend/global.css') ?>">
</head>

<body class="min-h-screen bg-slate-100" style="font-family: Inter, 'Segoe UI', Roboto, sans-serif;">
  <div class="min-h-screen">
    <?php include ROOT . '/app/views/frontend/partials/flash.php'; ?>
    <?= $content ?>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?= BASE_URL ?>public/js/frontend/validate.js?v=<?= filemtime(ROOT . '/public/js/frontend/validate.js') ?>"></script>
</body>

</html>