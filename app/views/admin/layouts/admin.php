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
  <link rel="stylesheet" href="<?= SRTDASH_LIB_URL ?>css/bootstrap.min.css">
  <link rel="stylesheet" href="<?= SRTDASH_LIB_URL ?>css/fontawesome.min.css">
  <link rel="stylesheet" href="<?= SRTDASH_LIB_URL ?>css/themify-icons.css">
  <link rel="stylesheet" href="<?= SRTDASH_LIB_URL ?>css/metismenujs.min.css">
  <link rel="stylesheet" href="<?= SRTDASH_LIB_URL ?>css/typography.css">
  <link rel="stylesheet" href="<?= SRTDASH_LIB_URL ?>css/default-css.css">
  <link rel="stylesheet" href="<?= SRTDASH_LIB_URL ?>css/styles.css">
  <link rel="stylesheet" href="<?= SRTDASH_LIB_URL ?>css/responsive.css">
  <?= $styles ?? '' ?>
</head>

<body>
  <div id="preloader">
    <div class="loader"></div>
  </div>

  <div class="page-container">
    <?php include ROOT . '/app/views/admin/partials/sidebar.php'; ?>

    <div class="main-content">
      <?php include ROOT . '/app/views/admin/partials/topbar.php'; ?>

      <div class="main-content-inner">
        <div class="container-fluid pt-4">
          <?php include ROOT . '/app/views/admin/partials/flash.php'; ?>
          <?= $content ?>
        </div>
      </div>
    </div>
  </div>

  <script src="<?= SRTDASH_LIB_URL ?>js/bootstrap.bundle.min.js"></script>
  <script src="<?= SRTDASH_LIB_URL ?>js/metismenujs.min.js"></script>
  <script src="<?= SRTDASH_LIB_URL ?>js/scripts.js"></script>
  <script src="<?= BASE_URL ?>public/js/admin/main.js"></script>
  <?= $scripts ?? '' ?>
</body>

</html>