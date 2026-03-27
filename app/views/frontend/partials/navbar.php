<?php
/**
 * app/views/frontend/partials/navbar.php — Customer Navbar
 * Owner: All members (common)
 *
 * Sticky Bootstrap 5 navbar. Shows login/register for guests,
 * user dropdown + cart icon for members.
 * Admin link appears only when role === "admin".
 */
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
  <div class="container">
    <a class="navbar-brand" href="<?= BASE_URL ?>">
      <img src="<?= BASE_URL ?>public/images/logo/vinfast-logo.png" alt="VinFast" height="38">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>products">Vehicles</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>news">News</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>about">About</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>faq">FAQ</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>contact">Contact</a></li>
      </ul>
      <ul class="navbar-nav ms-auto align-items-center">
        <?php if (Auth::check()): ?>
          <li class="nav-item me-2">
            <a class="nav-link" href="<?= BASE_URL ?>cart">
              <i class="fa-solid fa-cart-shopping"></i>
              <span id="cart-badge" class="badge bg-danger rounded-pill">0</span>
            </a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
              <i class="fa-regular fa-circle-user me-1"></i><?= htmlspecialchars(Auth::name()) ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="<?= BASE_URL ?>user/profile">My Profile</a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>user/orders">My Orders</a></li>
              <?php if (Auth::isAdmin()): ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-primary" href="<?= ADMIN_URL ?>dashboard">
                  <i class="fa-solid fa-gauge me-1"></i>Admin Panel</a></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>auth/logout">Logout</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>auth/login">Login</a></li>
          <li class="nav-item"><a class="btn btn-primary nav-link text-white ms-2 px-3"
            href="<?= BASE_URL ?>auth/register">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
