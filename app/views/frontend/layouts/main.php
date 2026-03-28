<?php
/**
 * app/views/frontend/layouts/main.php — Public Layout
 * Owner: All members (common)
 *
 * Wraps every customer-facing page.
 * $content is injected by View::render() via output buffering.
 * CSS: public/css/frontend/  |  JS: public/js/frontend/
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <?= SEO::titleTag() ?>
  <?= SEO::metaTags() ?>
  <meta name="csrf-token" content="<?= Auth::csrfToken() ?>">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            vfNavy: '#0B233F',
            vfGold: '#c8a22e'
          }
        }
      }
    };
  </script>
  <link rel="stylesheet" href="<?= BASE_URL ?>public/css/frontend/global.css">
</head>
<body>
  <?php include ROOT . '/app/views/frontend/partials/navbar.php'; ?>
  <?php include ROOT . '/app/views/frontend/partials/flash.php'; ?>
  <main>
    <?= $content ?>
  </main>
  <?php include ROOT . '/app/views/frontend/partials/footer.php'; ?>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?= BASE_URL ?>public/js/frontend/main.js"></script>
  <!-- Extra scripts injected by child views via $scripts variable -->
  <?= $scripts ?? '' ?>
</body>
</html>
