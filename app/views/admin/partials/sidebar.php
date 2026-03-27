<?php
/**
 * app/views/admin/partials/sidebar.php — Srtdash Sidebar Navigation
 * Owner: All members (common)
 *
 * Sidebar links map to admin controllers.
 * Each member adds their section's links in the relevant group.
 * Srtdash CSS class: l-navbar / nav__list / nav__link
 */
$cur = $_SERVER['REQUEST_URI'];
?>
<div class="l-navbar" id="nav-bar">
  <nav class="nav">
    <div>
      <a href="<?= ADMIN_URL ?>dashboard" class="nav__logo">
        <img src="<?= BASE_URL ?>public/images/logo/vinfast-logo.png" alt="VinFast" height="30">
        <span class="nav__logo-name ms-2">VinFast Admin</span>
      </a>
      <div class="nav__list">
        <!-- Common -->
        <a href="<?= ADMIN_URL ?>dashboard" class="nav__link">
          <i class="fa-solid fa-gauge nav__icon"></i><span class="nav__name">Dashboard</span></a>
        <a href="<?= ADMIN_URL ?>users" class="nav__link">
          <i class="fa-solid fa-users nav__icon"></i><span class="nav__name">Users</span></a>

        <!-- Tang Vu (Member 1) -->
        <a href="<?= ADMIN_URL ?>settings" class="nav__link">
          <i class="fa-solid fa-gear nav__icon"></i><span class="nav__name">Site Settings</span></a>
        <a href="<?= ADMIN_URL ?>contacts" class="nav__link">
          <i class="fa-solid fa-envelope nav__icon"></i><span class="nav__name">Messages</span></a>

        <!-- Nhat Linh (Member 2) -->
        <a href="<?= ADMIN_URL ?>faq" class="nav__link">
          <i class="fa-solid fa-circle-question nav__icon"></i><span class="nav__name">FAQ</span></a>

        <!-- Hai Nam (Member 3) -->
        <a href="<?= ADMIN_URL ?>products" class="nav__link">
          <i class="fa-solid fa-car nav__icon"></i><span class="nav__name">Products</span></a>
        <a href="<?= ADMIN_URL ?>orders" class="nav__link">
          <i class="fa-solid fa-clipboard-list nav__icon"></i><span class="nav__name">Orders</span></a>

        <!-- Nhat Tan (Member 4) -->
        <a href="<?= ADMIN_URL ?>news" class="nav__link">
          <i class="fa-solid fa-newspaper nav__icon"></i><span class="nav__name">News</span></a>
        <a href="<?= ADMIN_URL ?>comments" class="nav__link">
          <i class="fa-solid fa-comments nav__icon"></i><span class="nav__name">Comments</span></a>
      </div>
    </div>
    <a href="<?= BASE_URL ?>auth/logout" class="nav__link">
      <i class="fa-solid fa-right-from-bracket nav__icon"></i><span class="nav__name">Logout</span></a>
  </nav>
</div>
