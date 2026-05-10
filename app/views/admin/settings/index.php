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
?>

<div class="row mt-4">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h4 class="header-title mb-0 text-uppercase"><i class="ti-settings"></i> Cấu hình hệ thống (Site Settings)</h4>
            <a class="btn btn-flat btn-outline-success px-4" href="<?= BASE_URL ?>" target="_blank" rel="noreferrer">
                <i class="ti-world"></i> Xem trang chủ
            </a>
        </div>

        <form action="<?= ADMIN_URL ?>settings/save" method="post" enctype="multipart/form-data">
            <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">

            <!-- KHU VỰC BRANDING & BANNERS -->
            <div class="card shadow-sm border-0 mb-4 mt-4">
                <div class="card-body">
                    <h4 class="header-title text-primary"><i class="ti-bookmark-alt"></i> Tagline & Logo</h4>
                    <p class="text-muted font-14 mb-4">Quản lý nhận diện thương hiệu chính yếu của website.</p>
                    
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="col-form-label font-weight-bold text-dark text-uppercase d-block border-bottom pb-1 mb-2" style="font-size: 0.9rem;">Slogan (Tiêu đề lớn)</label>
                            <textarea class="form-control mb-3" name="tagline" rows="2" style="font-size: 1.1rem; font-weight: bold;"><?= htmlspecialchars((string)($settings['tagline'] ?? "Kiến tạo\ntương lai xanh")) ?></textarea>
                            
                            <label class="col-form-label font-weight-bold text-dark text-uppercase d-block border-bottom pb-1 mb-2" style="font-size: 0.9rem;">Sub-Tagline (Mô tả phụ)</label>
                            <textarea class="form-control" name="sub_tagline" rows="2"><?= htmlspecialchars((string)($settings['sub_tagline'] ?? 'Khám phá bộ sưu tập xe điện thông minh, sang trọng và hướng đến một tương lai bền vững cùng VinFast.')) ?></textarea>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="col-form-label font-weight-bold text-dark text-uppercase d-block border-bottom pb-1 mb-2" style="font-size: 0.9rem;">Logo trang web</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="logoUpload" name="logo" accept="image/*">
                                <label class="custom-file-label" for="logoUpload">Chọn ảnh logo mới...</label>
                            </div>
                            <div class="mt-3 p-3 bg-light rounded text-center">
                                <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo preview" style="max-height:50px;">
                            </div>
                            <small class="form-text text-muted mt-2"><i class="ti-info-alt"></i> Tải lên để thay thế logo hiện tại. Hỗ trợ định dạng PNG/JPG trong suốt.</small>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h4 class="header-title text-primary"><i class="ti-gallery"></i> Homepage Banners</h4>
                    <p class="text-muted font-14 mb-4">Thay đổi các hình ảnh slide lớn trên trang chủ. Nên sử dụng ảnh có độ phân giải cao và tỷ lệ 16:9.</p>

                    <div class="row">
                        <div class="col-lg-4 form-group">
                            <label class="col-form-label font-weight-bold text-dark text-uppercase d-block border-bottom pb-1 mb-2" style="font-size: 0.9rem;"><i class="ti-image mr-1"></i> Banner 1</label>
                            <div class="custom-file mb-3">
                                <input type="file" class="custom-file-input" id="b1" name="banner_1" accept="image/*">
                                <label class="custom-file-label" for="b1">Chọn ảnh...</label>
                            </div>
                            <div class="image-preview-box rounded border p-1 bg-light">
                                <img src="<?= htmlspecialchars($banner1) ?>" alt="Banner 1" class="img-fluid rounded">
                            </div>
                        </div>
                        <div class="col-lg-4 form-group">
                            <label class="col-form-label font-weight-bold text-dark text-uppercase d-block border-bottom pb-1 mb-2" style="font-size: 0.9rem;"><i class="fa fa-image mr-1"></i> Banner 2</label>
                            <div class="custom-file mb-3">
                                <input type="file" class="custom-file-input" id="b2" name="banner_2" accept="image/*">
                                <label class="custom-file-label" for="b2">Chọn ảnh...</label>
                            </div>
                            <div class="image-preview-box rounded border p-1 bg-light">
                                <img src="<?= htmlspecialchars($banner2) ?>" alt="Banner 2" class="img-fluid rounded">
                            </div>
                        </div>
                        <div class="col-lg-4 form-group">
                            <label class="col-form-label font-weight-bold text-dark text-uppercase d-block border-bottom pb-1 mb-2" style="font-size: 0.9rem;"><i class="fa fa-image mr-1"></i> Banner 3</label>
                            <div class="custom-file mb-3">
                                <input type="file" class="custom-file-input" id="b3" name="banner_3" accept="image/*">
                                <label class="custom-file-label" for="b3">Chọn ảnh...</label>
                            </div>
                            <div class="image-preview-box rounded border p-1 bg-light">
                                <img src="<?= htmlspecialchars($banner3) ?>" alt="Banner 3" class="img-fluid rounded">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KHU VỰC CONTACT INFO -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h4 class="header-title text-success"><i class="ti-location-pin"></i> Thông tin liên hệ</h4>
                    <p class="text-muted font-14 mb-4">Thông tin này sẽ hiển thị ở cuối trang (Footer) và trang Liên hệ.</p>

                    <div class="row">
                        <div class="col-lg-12 form-group">
                            <label class="col-form-label font-weight-bold text-dark text-uppercase d-block border-bottom pb-1 mb-2" style="font-size: 0.9rem;">Địa chỉ trụ sở / Showroom chính</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="ti-map-alt"></i></span>
                                </div>
                                <input type="text" class="form-control form-control-lg" name="address" value="<?= htmlspecialchars((string)($settings['address'] ?? 'Số 1 Đường abc, Quận 1, TP.HCM')) ?>">
                            </div>
                        </div>
                        <div class="col-lg-4 form-group">
                            <label class="col-form-label font-weight-bold text-dark text-uppercase d-block border-bottom pb-1 mb-2" style="font-size: 0.9rem;">Số điện thoại Hotline</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="ti-headphone-alt"></i></span>
                                </div>
                                <input type="text" class="form-control form-control-lg" name="phone" value="<?= htmlspecialchars((string)($settings['phone'] ?? '1900 23 23 89')) ?>">
                            </div>
                        </div>
                        <div class="col-lg-4 form-group">
                            <label class="col-form-label font-weight-bold text-dark text-uppercase d-block border-bottom pb-1 mb-2" style="font-size: 0.9rem;">Email hỗ trợ khách hàng</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="ti-email"></i></span>
                                </div>
                                <input type="email" class="form-control form-control-lg" name="email" value="<?= htmlspecialchars((string)($settings['email'] ?? 'support@vinfast.vn')) ?>">
                            </div>
                        </div>
                        <div class="col-lg-4 form-group">
                            <label class="col-form-label font-weight-bold text-dark text-uppercase d-block border-bottom pb-1 mb-2" style="font-size: 0.9rem;">Facebook Fanpage URL</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="ti-facebook"></i></span>
                                </div>
                                <input type="url" class="form-control form-control-lg" name="facebook_url" value="<?= htmlspecialchars((string)($settings['facebook_url'] ?? 'https://facebook.com/VinFastAuto')) ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KHU VỰC SẢN PHẨM NỔI BẬT -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h4 class="header-title text-danger"><i class="ti-car"></i> Dòng xe nổi bật (Homepage Featured Car)</h4>
                    <p class="text-muted font-14 mb-4">Chọn một dòng xe để hiển thị nổi bật trên trang chủ.</p>
                    
                    <div class="row">
                        <div class="col-lg-6 form-group">
                            <label class="col-form-label font-weight-bold text-dark text-uppercase d-block border-bottom pb-1 mb-2" style="font-size: 0.9rem;">Chọn dòng xe</label>
                            <select class="form-control form-control-lg custom-select" name="featured_product_id">
                                <option value="">-- Không chọn / Tự động --</option>
                                <?php foreach ($products ?? [] as $product): ?>
                                    <option value="<?= $product['id'] ?>" <?= ($settings['featured_product_id'] ?? '') == $product['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($product['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KHU VỰC HOMEPAGE STATS -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h4 class="header-title text-warning"><i class="ti-bar-chart"></i> Thống kê nổi bật (Homepage Small Stats)</h4>
                    <p class="text-muted font-14 mb-4">Quản lý 4 con số thống kê nhỏ xuất hiện dưới banner trang chủ.</p>
                    
                    <div class="row">
                        <!-- Stat 1 -->
                        <div class="col-md-3 form-group border-right border-light">
                            <label class="col-form-label font-weight-bold text-dark text-uppercase d-block border-bottom pb-1 mb-2" style="font-size: 0.9rem;">Thống kê 1</label>
                            <div class="mb-2">
                                <small class="text-muted font-weight-bold text-uppercase">Giá trị (Ví dụ: 14+)</small>
                                <input type="text" class="form-control" name="stat1_val" value="<?= htmlspecialchars((string)($settings['stat1_val'] ?? '14+')) ?>">
                            </div>
                            <div>
                                <small class="text-muted font-weight-bold text-uppercase">Tiêu đề (Ví dụ: Quốc gia)</small>
                                <input type="text" class="form-control" name="stat1_lbl" value="<?= htmlspecialchars((string)($settings['stat1_lbl'] ?? 'Quốc gia hiện diện')) ?>">
                            </div>
                        </div>

                        <!-- Stat 2 -->
                        <div class="col-md-3 form-group border-right border-light">
                            <label class="col-form-label font-weight-bold text-dark text-uppercase d-block border-bottom pb-1 mb-2" style="font-size: 0.9rem;">Thống kê 2</label>
                            <div class="mb-2">
                                <small class="text-muted font-weight-bold text-uppercase">Giá trị</small>
                                <input type="text" class="form-control" name="stat2_val" value="<?= htmlspecialchars((string)($settings['stat2_val'] ?? '150,000+')) ?>">
                            </div>
                            <div>
                                <small class="text-muted font-weight-bold text-uppercase">Tiêu đề</small>
                                <input type="text" class="form-control" name="stat2_lbl" value="<?= htmlspecialchars((string)($settings['stat2_lbl'] ?? 'Khách hàng tin dùng')) ?>">
                            </div>
                        </div>

                        <!-- Stat 3 -->
                        <div class="col-md-3 form-group border-right border-light">
                            <label class="col-form-label font-weight-bold text-dark text-uppercase d-block border-bottom pb-1 mb-2" style="font-size: 0.9rem;">Thống kê 3</label>
                            <div class="mb-2">
                                <small class="text-muted font-weight-bold text-uppercase">Giá trị</small>
                                <input type="text" class="form-control" name="stat3_val" value="<?= htmlspecialchars((string)($settings['stat3_val'] ?? '8 mẫu')) ?>">
                            </div>
                            <div>
                                <small class="text-muted font-weight-bold text-uppercase">Tiêu đề</small>
                                <input type="text" class="form-control" name="stat3_lbl" value="<?= htmlspecialchars((string)($settings['stat3_lbl'] ?? 'Xe hiện có')) ?>">
                            </div>
                        </div>

                        <!-- Stat 4 -->
                        <div class="col-md-3 form-group">
                            <label class="col-form-label font-weight-bold text-dark text-uppercase d-block border-bottom pb-1 mb-2" style="font-size: 0.9rem;">Thống kê 4</label>
                            <div class="mb-2">
                                <small class="text-muted font-weight-bold text-uppercase">Giá trị</small>
                                <input type="text" class="form-control" name="stat4_val" value="<?= htmlspecialchars((string)($settings['stat4_val'] ?? '500+')) ?>">
                            </div>
                            <div>
                                <small class="text-muted font-weight-bold text-uppercase">Tiêu đề</small>
                                <input type="text" class="form-control" name="stat4_lbl" value="<?= htmlspecialchars((string)($settings['stat4_lbl'] ?? 'Showroom toàn cầu')) ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="card shadow-sm border-0 mb-5">
                <div class="card-body d-flex gap-3 justify-content-end bg-light rounded">
                    <a class="btn btn-flat btn-outline-secondary btn-lg px-4" href="<?= ADMIN_URL ?>settings">
                        <i class="ti-reload"></i> Huỷ thay đổi
                    </a>
                    <button class="btn btn-flat btn-primary btn-lg px-5" type="submit">
                        <i class="ti-save"></i> LƯU CẤU HÌNH
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Thêm chút CSS phụ trợ nhỏ gọn để hiển thị tên file khi chọn file mới -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll('.custom-file-input').forEach(function(input) {
            input.addEventListener('change', function(e) {
                if(e.target.files && e.target.files[0]) {
                    var file = e.target.files[0];
                    var fileName = file.name;
                    
                    // Cập nhật tên file trên label
                    var nextSibling = e.target.nextElementSibling;
                    if (nextSibling) {
                        nextSibling.innerText = fileName;
                    }

                    // Tự động load ảnh lên image preview (nếu có)
                    // Tìm container cha gần nhất
                    var parent = e.target.closest('.form-group');
                    if(parent) {
                        var imgPreview = parent.querySelector('img');
                        if(imgPreview) {
                            var reader = new FileReader();
                            reader.onload = function(evt) {
                                imgPreview.src = evt.target.result;
                                imgPreview.style.opacity = '0.5';
                                setTimeout(function(){ imgPreview.style.opacity = '1'; }, 300);
                            }
                            reader.readAsDataURL(file);
                        }
                    }
                }
            });
        });
    });
</script>