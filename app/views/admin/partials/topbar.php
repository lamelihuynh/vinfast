<?php

/**
 * app/views/admin/partials/topbar.php — Admin Topbar
 * Owner: All members (common)
 *
 * Shows logged-in admin name, toggle button for sidebar.
 */

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
$sectionTitle = 'Hệ thống quản trị';
if (strpos($requestPath, '/admin/products') !== false) {
  $sectionTitle = 'Quản lý sản phẩm';
} elseif (strpos($requestPath, '/admin/orders') !== false) {
  $sectionTitle = 'Quản lý đơn hàng';
} elseif (strpos($requestPath, '/admin/news') !== false) {
  $sectionTitle = 'Quản lý tin tức';
} elseif (strpos($requestPath, '/admin/comments') !== false) {
  $sectionTitle = 'Quản lý bình luận';
} elseif (strpos($requestPath, '/admin/users') !== false) {
  $sectionTitle = 'Quản lý người dùng';
} elseif (strpos($requestPath, '/admin/settings') !== false) {
  $sectionTitle = 'Cấu hình website';
}
?>
<div class="header-area" id="sticky-header">
  <div class="row align-items-center">
    <div class="col-md-8 col-sm-12 clearfix">
      <div class="d-flex align-items-center gap-3">
        <div class="nav-btn pull-left">
          <span></span>
          <span></span>
          <span></span>
        </div>

        <div>
          <h6 class="mb-0"><?= htmlspecialchars($sectionTitle) ?></h6>
          <small class="text-muted">VinFast Administration</small>
        </div>
      </div>
    </div>

    <div class="col-md-4 col-sm-12 clearfix mt-3 mt-md-0">
      <ul class="notification-area pull-right list-unstyled mb-0 d-flex align-items-center justify-content-md-end gap-3">
        <li class="user-dropdown d-flex align-items-center gap-2">
          <i class="ti-user text-secondary"></i>
          <div class="d-flex flex-column lh-sm">
            <span class="text-muted small">Quản trị viên</span>
            <strong><?= htmlspecialchars(Auth::name()) ?></strong>
          </div>
        </li>
        <li>
          <a href="<?= BASE_URL ?>auth/logout" class="btn btn-sm btn-outline-secondary">
            <i class="ti-power-off me-1"></i>Đăng xuất
          </a>
        </li>
      </ul>
    </div>
  </div>
</div>