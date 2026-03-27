<?php
/**
 * app/views/admin/layouts/admin.php — Admin Dashboard Layout (Srtdash)
 * Owner: All members (common)
 *
 * Wraps every admin page with the Srtdash sidebar and topbar.
 * Template assets: public/libs/srtdash/
 * Reference: https://github.com/puikinsh/srtdash-admin-dashboard
 * Demo: https://colorlib.com/polygon/srtdash/index.html
 *
 * $content is injected by View::render().
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <?= SEO::titleTag() ?>
  <meta name="csrf-token" content="<?= Auth::csrfToken() ?>">
  <link rel="stylesheet" href="<?= BASE_URL ?>public/libs/srtdash/css/srtdash.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>public/css/admin/global.css">
</head>
<body id="body-pd">
  <?php include ROOT . '/app/views/admin/partials/sidebar.php'; ?>
  <?php include ROOT . '/app/views/admin/partials/topbar.php'; ?>
  <div class="body-padding">
    <?php include ROOT . '/app/views/admin/partials/flash.php'; ?>
    <main class="p-4">
      <?= $content ?>
    </main>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?= BASE_URL ?>public/libs/srtdash/js/main.js"></script>
  <script src="<?= BASE_URL ?>public/js/admin/main.js"></script>
  <?= $scripts ?? '' ?>
</body>
</html>
