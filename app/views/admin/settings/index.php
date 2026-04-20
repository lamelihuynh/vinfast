<?php
/**
 * app/views/admin/settings/index.php
 * Owner  : Tang Vu 
 * Title  : Site Settings
 *
 * Purpose: Form groups: (1) Logo upload. (2) Banner 1-3 upload with preview. (3) Contact info: address, phone, email. (4) About page: text (textarea), image upload. (5) Social links. All use CSRF token.
 *
 * Variables available (set by controller via View::render):
 *   $settings (assoc array)
 *
 *
 * -----------------------------------------------------------------------
 * Rules:
 *  - Always escape output: <?= htmlspecialchars($var) ?>
 *  - Include CSRF in every form: <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
 *  - Include pagination partial where needed: include ROOT."/app/views/frontend/partials/pagination.php"
 */
?>

<?php
// =========================================================
// ADMIN: SITE SETTINGS
// - Mục tiêu: tập trung 1 nơi để chỉnh nội dung các trang public
// - Dữ liệu lưu ở bảng `site_settings` (key-value)
// =========================================================

$logoUrl = SiteSetting::imageUrl($settings['logo'] ?? '', 'public/images/logo/vinfast-logo.png');
$banner1 = SiteSetting::imageUrl($settings['banner_1'] ?? '', 'public/images/banners/banner_01.png');
$banner2 = SiteSetting::imageUrl($settings['banner_2'] ?? '', 'public/images/banners/banner_02.png');
$banner3 = SiteSetting::imageUrl($settings['banner_3'] ?? '', 'public/images/banners/banner_03.png');
$aboutImg = SiteSetting::imageUrl($settings['about_image'] ?? '', 'public/images/banners/banner_background.png');
?>

<div class="row">
  <div class="col-12">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h4 class="mb-0">Site settings</h4>
      <a class="btn btn-sm btn-outline-secondary" href="<?= BASE_URL ?>" target="_blank" rel="noreferrer">View site</a>
    </div>

    <div class="card">
      <div class="card-body">
        <form action="<?= ADMIN_URL ?>settings/save" method="post" enctype="multipart/form-data">
          <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">

          <!-- =========================
               SECTION: Branding
          ========================== -->
          <h6 class="text-uppercase text-muted mb-3">Branding</h6>
          <div class="row g-3 mb-4">
            <div class="col-lg-6">
              <label class="form-label">Tagline (hiển thị ở banner)</label>
              <input class="form-control" name="tagline" value="<?= htmlspecialchars((string)($settings['tagline'] ?? '')) ?>" placeholder="VD: Boundless Together">
            </div>
            <div class="col-lg-6">
              <label class="form-label">Logo</label>
              <input class="form-control" type="file" name="logo" accept="image/*">
              <div class="mt-2">
                <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo preview" style="height:40px">
              </div>
              <small class="text-muted d-block mt-1">Nếu upload mới sẽ thay thế logo cũ (lưu vào `public/images/uploads/site/`).</small>
            </div>
          </div>

          <!-- =========================
               SECTION: Homepage banners
          ========================== -->
          <h6 class="text-uppercase text-muted mb-3">Homepage banners</h6>
          <div class="row g-3 mb-4">
            <div class="col-lg-4">
              <label class="form-label">Banner 1</label>
              <input class="form-control" type="file" name="banner_1" accept="image/*">
              <div class="mt-2">
                <img src="<?= htmlspecialchars($banner1) ?>" alt="Banner 1" class="img-fluid rounded border">
              </div>
            </div>
            <div class="col-lg-4">
              <label class="form-label">Banner 2</label>
              <input class="form-control" type="file" name="banner_2" accept="image/*">
              <div class="mt-2">
                <img src="<?= htmlspecialchars($banner2) ?>" alt="Banner 2" class="img-fluid rounded border">
              </div>
            </div>
            <div class="col-lg-4">
              <label class="form-label">Banner 3</label>
              <input class="form-control" type="file" name="banner_3" accept="image/*">
              <div class="mt-2">
                <img src="<?= htmlspecialchars($banner3) ?>" alt="Banner 3" class="img-fluid rounded border">
              </div>
            </div>
          </div>

          <!-- =========================
               SECTION: Contact info
          ========================== -->
          <h6 class="text-uppercase text-muted mb-3">Contact info (footer + contact page)</h6>
          <div class="row g-3 mb-4">
            <div class="col-lg-6">
              <label class="form-label">Address</label>
              <input class="form-control" name="address" value="<?= htmlspecialchars((string)($settings['address'] ?? '')) ?>">
            </div>
            <div class="col-lg-3">
              <label class="form-label">Phone</label>
              <input class="form-control" name="phone" value="<?= htmlspecialchars((string)($settings['phone'] ?? '')) ?>">
            </div>
            <div class="col-lg-3">
              <label class="form-label">Email</label>
              <input class="form-control" name="email" value="<?= htmlspecialchars((string)($settings['email'] ?? '')) ?>">
            </div>
          </div>

          <!-- =========================
               SECTION: About + Social
          ========================== -->
          <h6 class="text-uppercase text-muted mb-3">About + Social</h6>
          <div class="row g-3 mb-4">
            <div class="col-lg-8">
              <label class="form-label">About text</label>
              <textarea class="form-control" name="about_text" rows="5"><?= htmlspecialchars((string)($settings['about_text'] ?? '')) ?></textarea>
              <small class="text-muted">Nội dung dùng cho trang Giới thiệu hoặc các section giới thiệu sau này.</small>
            </div>
            <div class="col-lg-4">
              <label class="form-label">About image</label>
              <input class="form-control" type="file" name="about_image" accept="image/*">
              <div class="mt-2">
                <img src="<?= htmlspecialchars($aboutImg) ?>" alt="About image" class="img-fluid rounded border">
              </div>
            </div>

            <div class="col-lg-6">
              <label class="form-label">Facebook URL</label>
              <input class="form-control" name="facebook_url" value="<?= htmlspecialchars((string)($settings['facebook_url'] ?? '')) ?>" placeholder="https://facebook.com/...">
            </div>
          </div>

          <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit">Save</button>
            <a class="btn btn-outline-secondary" href="<?= ADMIN_URL ?>settings">Reset</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
