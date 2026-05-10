<?php
/**
 * app/views/admin/pages/faq-edit.php
 * Owner: Nhat Linh (Member 2)
 * Purpose: Manage FAQ page content (intro text and image)
 */

$csrf = Auth::CsrfToken();
?>

<div class="container-fluid p-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3">Quản Lý Nội Dung Trang FAQ</h1>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>Nội Dung Trang FAQ</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= ADMIN_URL ?>page-content/faq/save" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                
                <!-- Intro Text -->
                <div class="mb-4">
                    <label class="form-label"><strong>Văn Bản Giới Thiệu</strong></label>
                    <textarea class="form-control" name="intro_text" rows="8" placeholder="Nhập nội dung giới thiệu trang FAQ..."><?= htmlspecialchars($faqIntro) ?></textarea>
                    <small class="text-muted d-block mt-2">Nội dung này sẽ hiển thị ở đầu trang FAQ</small>
                </div>

                <!-- Intro Image -->
                <div class="mb-4">
                    <label class="form-label"><strong>Hình Ảnh</strong></label>
                    
                    <?php if (!empty($faqImage)): ?>
                        <div class="mb-3">
                            <div style="max-width: 400px; border-radius: 8px; overflow: hidden;">
                                <img src="<?= htmlspecialchars($faqImage) ?>" alt="FAQ Intro" style="width: 100%; height: auto; display: block;">
                            </div>
                            <small class="text-muted d-block mt-2">Ảnh hiện tại: <code><?= htmlspecialchars($faqImage) ?></code></small>
                        </div>
                    <?php else: ?>
                        <div class="mb-3">
                            <div style="max-width: 400px; height: 200px; background: #f0f0f0; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <span class="text-muted">Chưa có ảnh</span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <input type="file" class="form-control" name="intro_image" accept="image/*">
                    <small class="text-muted d-block mt-2">Để trống nếu không muốn thay đổi ảnh</small>
                </div>

                <!-- Submit -->
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Lưu Nội Dung FAQ
                    </button>
                    <a href="<?= ADMIN_URL ?>faq" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay Lại
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Preview -->
    <div class="card mt-4">
        <div class="card-header">
            <h5>Xem Trước</h5>
        </div>
        <div class="card-body bg-light">
            <h3>Nội Dung Giới Thiệu</h3>
            <p><?= nl2br(htmlspecialchars($faqIntro)) ?: '<em class="text-muted">Chưa có nội dung</em>' ?></p>
            
            <?php if (!empty($faqImage)): ?>
                <div style="margin-top: 20px;">
                    <img src="<?= htmlspecialchars($faqImage) ?>" alt="FAQ Preview" style="max-width: 100%; height: auto; border-radius: 8px;">
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>