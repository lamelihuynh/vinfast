<?php
/**
 * app/views/admin/partials/topbar.php — Admin Topbar
 * Owner: All members (common)
 *
 * Shows logged-in admin name, toggle button for sidebar.
 */
?>
<header class="header" id="header">
  <div class="header__container">
    <div class="header__toggle" id="header-toggle">
      <i class="fa-solid fa-bars" id="header-icon"></i>
    </div>
    <div class="header__img">
      <i class="fa-solid fa-user-tie"></i>
      <span class="ms-2 small"><?= htmlspecialchars(Auth::name()) ?></span>
    </div>
  </div>
</header>
