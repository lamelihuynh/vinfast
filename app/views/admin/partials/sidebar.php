<?php

/**
 * app/views/admin/partials/sidebar.php — Srtdash Sidebar Navigation
 * Owner: All members (common)
 *
 * Sidebar links map to admin controllers.
 * Each member adds their section's links in the relevant group.
 * Srtdash CSS class: sidebar-menu / metismenu
 */

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
$isActive = static function (string $needle) use ($requestPath): bool {
  return strpos($requestPath, $needle) !== false;
};

$isDashboardGroup = $isActive('/admin/dashboard');
$isUserGroup = $isActive('/admin/users');
$isProductGroup = $isActive('/admin/products') || $isActive('/admin/orders');
$isContentGroup = $isActive('/admin/settings') || $isActive('/admin/contacts') || $isActive('/admin/faq');
$isNewsGroup = $isActive('/admin/news') || $isActive('/admin/comments');
?>
<div class="sidebar-menu">
  <div class="sidebar-header">
    <div class="logo">
      <a href="<?= ADMIN_URL ?>dashboard">
        <?php $adminLogo = SiteSetting::imageUrl(SiteSetting::all()['logo'] ?? '', 'public/images/logo/vinfast-logo.png'); ?>
        <img src="<?= htmlspecialchars($adminLogo) ?>" alt="VinFast logo" style="max-width: 130px;">
        <small class="text-white d-block mt-1">Admin Panel</small>
      </a>
    </div>
  </div>

  <div class="main-menu">
    <div class="menu-inner">
      <nav>
        <ul class="metismenu" id="menu">
          <li>
            <a href="<?= BASE_URL ?>"><i class="ti-home"></i><span>Trở về trang chủ</span></a>
          </li>
          <li class="<?= $isDashboardGroup ? 'active' : '' ?>">
            <a href="javascript:void(0)" aria-expanded="<?= $isDashboardGroup ? 'true' : 'false' ?>">
              <i class="ti-dashboard"></i><span>dashboard</span>
            </a>
            <ul class="collapse <?= $isDashboardGroup ? 'show' : '' ?>">
              <li class="<?= $isActive('/admin/dashboard') ? 'active' : '' ?>">
                <a href="<?= ADMIN_URL ?>dashboard">Tổng quan</a>
              </li>
            </ul>
          </li>

          <li class="<?= $isUserGroup ? 'active' : '' ?>">
            <a href="javascript:void(0)" aria-expanded="<?= $isUserGroup ? 'true' : 'false' ?>">
              <i class="ti-user"></i><span>Quản trị người dùng</span>
            </a>
            <ul class="collapse <?= $isUserGroup ? 'show' : '' ?>">
              <li class="<?= $isActive('/admin/users') ? 'active' : '' ?>">
                <a href="<?= ADMIN_URL ?>users">Danh sách người dùng</a>
              </li>
            </ul>
          </li>

          <li class="<?= $isProductGroup ? 'active' : '' ?>">
            <a href="javascript:void(0)" aria-expanded="<?= $isProductGroup ? 'true' : 'false' ?>">
              <i class="ti-package"></i><span> Sản phẩm và Đơn hàng</span>
            </a>
            <ul class="collapse <?= $isProductGroup ? 'show' : '' ?>">
              <li class="<?= $isActive('/admin/products') ? 'active' : '' ?>"><a href="<?= ADMIN_URL ?>products">Sản phẩm</a></li>
              <li class="<?= $isActive('/admin/orders') ? 'active' : '' ?>"><a href="<?= ADMIN_URL ?>orders">Đơn hàng</a></li>
            </ul>
          </li>

          <li class="<?= $isContentGroup ? 'active' : '' ?>">
            <a href="javascript:void(0)" aria-expanded="<?= $isContentGroup ? 'true' : 'false' ?>">
              <i class="ti-layout"></i><span>Nội dung website</span>
            </a>
            <ul class="collapse <?= $isContentGroup ? 'show' : '' ?>">
              <li class="<?= $isActive('/admin/settings') ? 'active' : '' ?>"><a href="<?= ADMIN_URL ?>settings">Trang chủ</a></li>
              <li class="<?= $isActive('/admin/page-content/about') ? 'active' : '' ?>"><a href="<?= ADMIN_URL ?>page-content/about">Trang Giới Thiệu</a></li>
              <li class="<?= $isActive('/admin/contacts') ? 'active' : '' ?>"><a href="<?= ADMIN_URL ?>contacts">Trang Liên hệ</a></li>
              <li class="<?= $isActive('/admin/faq') ? 'active' : '' ?>"><a href="<?= ADMIN_URL ?>faq">Trang Câu Hỏi Thường Gặp</a></li>

            </ul>
          </li>
          <li> <a href="<?= ADMIN_URL ?>faq-topic">
                      <i class="fa-solid fa-layer-group"></i>
                      <span>Chủ đề FAQ</span>
                  </a>
          </li>
          <li class="<?= $isNewsGroup ? 'active' : '' ?>">
            <a href="javascript:void(0)" aria-expanded="<?= $isNewsGroup ? 'true' : 'false' ?>">
              <i class="ti-write"></i><span>Tin tức và Bình luận</span>
            </a>
            <ul class="collapse <?= $isNewsGroup ? 'show' : '' ?>">
              <li class="<?= $isActive('/admin/news') ? 'active' : '' ?>"><a href="<?= ADMIN_URL ?>news">Tin tức</a></li>
              <li class="<?= $isActive('/admin/comments') ? 'active' : '' ?>"><a href="<?= ADMIN_URL ?>comments">Bình luận</a></li>
            </ul>
          </li>

          <li>
            <a href="<?= BASE_URL ?>auth/logout"><i class="ti-power-off"></i><span>Đăng xuất</span></a>
          </li>
        </ul>
      </nav>
    </div>
  </div>
</div>