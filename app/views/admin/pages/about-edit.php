<?php
/**
 * app/views/admin/pages/about-edit.php
 * Owner: Nhat Linh (Member 2)
 * Purpose: Manage About page content (hero image, intro text/image, timeline, awards)
 */

$csrf = Auth::CsrfToken();
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<div class="container-fluid p-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3">Quản Lý Nội Dung Trang About</h1>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="hero-tab" data-bs-toggle="tab" data-bs-target="#hero-pane" type="button" role="tab">
                Ảnh Hero Đầu Trang
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="intro-tab" data-bs-toggle="tab" data-bs-target="#intro-pane" type="button" role="tab">
                Phần Giới Thiệu
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="timeline-tab" data-bs-toggle="tab" data-bs-target="#timeline-pane" type="button" role="tab">
                Lịch Sử (Timeline)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="awards-tab" data-bs-toggle="tab" data-bs-target="#awards-pane" type="button" role="tab">
                Giải Thưởng
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Hero & Image Tab-->
        <div class="tab-pane fade show active" id="hero-pane" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <h5>Hero Background Image</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($heroImage)): ?>
                        <div class="mb-3">
                            <label class="form-label">Hình Ảnh Hiện Tại:</label>
                            <p class="text-muted"><code><?= htmlspecialchars($heroImage) ?></code></p>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?= ADMIN_URL ?>page-content/about/save" enctype="multipart/form-data" id="heroForm">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Chọn hình ảnh mới (PNG, tối đa 5MB):</label>
                            <input type="file" class="form-control" name="hero_image" accept="image/png, image/jpeg, image/jpg" id="heroImageInput">
                            <small class="text-muted">Để trống nếu không muốn thay đổi</small>
                        </div>

                        <button type="submit" class="btn btn-primary" id="heroVideoBtn">
                            <i class="fas fa-save"></i> Lưu Hình Ảnh
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Intro Tab -->
        <div class="tab-pane fade" id="intro-pane" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <h5>Nội Dung Phần Giới Thiệu</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= ADMIN_URL ?>page-content/about/save" enctype="multipart/form-data" id="introForm">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Nội Dung Văn Bản:</label>
                            <textarea class="form-control" name="intro_text" rows="6" id="introTextInput"><?= htmlspecialchars($aboutText) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Hình Ảnh:</label>
                            <?php if (!empty($aboutImage)): ?>
                                <div class="mb-2">
                                    <img src="<?= htmlspecialchars($aboutImage) ?>" alt="Intro" style="max-width: 300px; border-radius: 8px;">
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" name="intro_image" accept="image/*" id="introImageInput">
                            <small class="text-muted">Để trống nếu không muốn thay đổi</small>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Lưu Nội Dung
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Timeline Tab -->
        <div class="tab-pane fade" id="timeline-pane" role="tabpanel">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Ảnh Lịch Sử Thương Hiệu</h5>
                    <button type="submit" form="timelineTextForm" class="btn btn-primary btn-sm">
                        <i class="fas fa-save"></i> Lưu tất cả mô tả
                    </button>
                </div>
                                
                <div class="card-body">
                    <p class="text-muted">Lưu ý: Ảnh được tự động lưu khi tải lên. Văn bản mô tả cần nhấn "Lưu tất cả mô tả".</p>                    
                    <form id="timelineTextForm" method="POST" action="<?= ADMIN_URL ?>page-content/about/save">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                        <div class="row">
                            <?php foreach ([2026, 2025, 2024, 2023, 2022, 2021, 2020, 2019, 2018, 2017] as $year): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title">Năm <?= $year ?></h6>

                                            <div class="mb-3">
                                                <label class="form-label small">Mô tả lịch sử năm <?= $year ?>: </label>
                                                <textarea class="form-control form-control-sm" name="timeline_desc[<?= $year ?>]" rows="3"><?= htmlspecialchars($timelineTexts[$year] ?? '') ?></textarea>

                                                </textarea>
                                            </div>
                                            
                                            <div class="row">
                                            <!-- Ảnh chính (Main) -->
                                                <div class="col-6">
                                                    <label class="small d-block mb-1">Ảnh chính:</label>
                                                    <div id="preview-<?= $year ?>-main" class="mb-2">
            
                                                        <?php if (isset($timelineImages[$year]['main'])): ?>
                                                            <img src="<?= UPLOAD_URL. htmlspecialchars($timelineImages[$year]['main']) ?>" class="img-thumbnail" style="height: 100px; object-fit: cover;">
                                                        <?php else: ?>
                                                            <div class="bg-light d-flex align-items-center justify-content-center border" style="height: 100px;">
                                                                <small class="text-muted">Trống</small>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <input type="file" class="timeline-upload" data-year="<?= $year ?>" data-type="main" style="display:none;" id="file-<?= $year ?>-main">

                                                    <div class="btn-group w-100" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary flex-grow-1 btn-upload-trigger" data-target="file-<?= $year ?>-main">
                                                            <i class="fas fa-camera"></i> Tải ảnh
                                                        </button>
                                                        <?php if (isset($timelineImages[$year]['main'])): ?>
                                                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-timeline" data-year="<?= $year ?>" data-type="main">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                            <!-- Ảnh phụ (Secondary) -->
                                                <div class="col-6">
                                                    <label class="small d-block mb-1">Ảnh phụ:</label>
                                                    <div id="preview-<?= $year ?>-secondary" class="mb-2">
                                                        <?php if (isset($timelineImages[$year]['secondary'])): ?>
                                                            <img src="<?= UPLOAD_URL.htmlspecialchars($timelineImages[$year]['secondary']) ?>" class="img-thumbnail" style="height: 100px; object-fit: cover;">
                                                        <?php else: ?>
                                                            <div class="bg-light d-flex align-items-center justify-content-center border" style="height: 100px;">
                                                                <small class="text-muted">Trống</small>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <input type="file" class="timeline-upload" data-year="<?= $year ?>" data-type="secondary" style="display:none;" id="file-<?= $year ?>-secondary">
                                                    <div class="btn-group w-100" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary flex-grow-1 btn-upload-trigger" data-target="file-<?= $year ?>-secondary">
                                                            <i class="fas fa-camera"></i> Tải ảnh
                                                        </button>
                                                        <?php if (isset($timelineImages[$year]['secondary'])): ?>
                                                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-timeline" data-year="<?= $year ?>" data-type="secondary">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Awards Tab -->
        <div class="tab-pane fade" id="awards-pane" role="tabpanel">
            <div class="card">
                    <h5>Ảnh Giải Thưởng</h5>
                </div>
                    <p class="text-muted">Tải lên ảnh giải thưởng cho từng năm (2017-202)</p>
                    
                    <div class="row">
                        <?php foreach ([2023, 2022, 2021, 2020, 2019, 2018] as $year): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title">Năm <?= $year ?></h6>
                                        
                                        <?php if (isset($awardImages[$year])): ?>
                                            <img src="<?= htmlspecialchars($awardImages[$year]) ?>" alt="Award <?= $year ?>" style="max-width: 100%; height: auto; border-radius: 4px; margin-bottom: 10px;">
                                            <br>
                                            <small class="text-muted"><?= htmlspecialchars($awardImages[$year]) ?></small>
                                        <?php else: ?>
                                            <div style="width: 100%; height: 150px; background: #f0f0f0; border-radius: 4px; display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                                                <span class="text-muted">Chưa có ảnh</span>
                                            </div>
                                        <?php endif; ?>

                                        <div class="mt-2">
                                            <input type="file" class="form-control form-control-sm award-upload" accept="image/*" 
                                                   data-year="<?= $year ?>" style="display: none;">
                                            <button type="button" class="btn btn-sm btn-outline-primary award-upload-btn" data-year="<?= $year ?>">
                                                <i class="fas fa-upload"></i> <?= isset($awardImages[$year]) ? 'Thay' : 'Tải' ?> Ảnh
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('click', function(e) {
    if (e.target.closest('.btn-upload-trigger') || e.target.closest('.timeline-upload-btn') || e.target.closest('.award-upload-btn')) {
        e.preventDefault();
        e.stopPropagation();

        const btn = e.target.closest('button');
        const targetId = btn.getAttribute('data-target');
        const year = btn.getAttribute('data-year');
        
        if (targetId) {
            document.getElementById(targetId).click();
        } else if (btn.classList.contains('timeline-upload-btn')) {
             document.querySelector(`.timeline-upload[data-year="${year}"]`).click();
        } else if (btn.classList.contains('award-upload-btn')) {
             document.querySelector(`.award-upload[data-year="${year}"]`).click();
        }
    }
});


document.addEventListener('click', function(e) {
    const deleteBtn = e.target.closest('.btn-delete-timeline');

    if (deleteBtn){
        e.preventDefault();
        const year = deleteBtn.getAttribute('data-year');
        const type = deleteBtn.getAttribute('data-type'); // main hoặc secondary
        const assetKey = `timeline_${year}_${type}`;

        if (confirm(`Bạn có chắc chắn muốn xóa ảnh ${type} của năm ${year}?`)) {
            deleteTimelineImage(assetKey, deleteBtn);
        }
    }
});

function deleteTimelineImage(assetKey, btnElement){
    const originalHtml = btnElement.innerHTML; 
    btnElement.disabled = true; 
    btnElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch(`<?= ADMIN_URL ?>page-content/delete-asset?page=about&key=${assetKey}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Thành công thì reload để cập nhật giao diện
            location.reload();
        } else {
            alert('Lỗi: ' + data.message);
            btnElement.disabled = false;
            btnElement.innerHTML = originalHtml;
        }
    })
    .catch(err => {
        console.error('Delete Error:', err);
        alert('Có lỗi xảy ra khi kết nối server.');
        btnElement.disabled = false;
        btnElement.innerHTML = originalHtml;
    });
}




// Xử lý thay đổi file cho Timeline
document.querySelectorAll('.timeline-upload').forEach(input => {
    input.addEventListener('change', function() {
        const year = this.getAttribute('data-year');
        const type = this.getAttribute('data-type') || 'main'; 
        // Thống nhất key: timeline_2023_main
        const assetKey = `timeline_${year}_${type}`;
        uploadFileAjax('about', assetKey, this.files[0], this);
    });
});

// Xử lý thay đổi file cho Award
document.querySelectorAll('.award-upload').forEach(input => {
    input.addEventListener('change', function() {
        const year = this.getAttribute('data-year');
        const assetKey = `award_${year}`;
        uploadFileAjax('about', assetKey, this.files[0], this);
    });
});

// Hàm Upload AJAX dùng chung duy nhất
function uploadFileAjax(page, assetKey, file, inputElement) {
    if (!file) return;

    const formData = new FormData();
    formData.append('file', file);

    // Tìm nút bấm tương ứng để hiện loading
    const container = inputElement.closest('.card-body');
    const btn = container.querySelector('button');
    const originalHtml = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    // Lưu active tab trước khi reload
    const activeTab = document.querySelector('.nav-link.active');
    if (activeTab) {
        sessionStorage.setItem('activeTab', activeTab.getAttribute('id'));
    }

    // Luôn gửi qua tham số 'key' để đồng bộ với Controller mới
    fetch(`<?= ADMIN_URL ?>page-content/upload?page=${page}&key=${assetKey}`, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Reload nhưng sẽ restore tab sau
            location.reload(); 
        } else {
            alert('Lỗi: ' + data.message);
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    })
    .catch(err => {
        console.error(err);
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
}

// 1. Tự động mở tab dựa trên Hash trên URL khi tải trang
window.addEventListener('DOMContentLoaded', () => {
    const hash = window.location.hash;
    if (hash) {
        const tabBtn = document.querySelector(`[data-bs-target="${hash}"]`);
        if (tabBtn) {
            const tab = new bootstrap.Tab(tabBtn);
            tab.show();
        }
    }

    // 2. Cập nhật Hash vào URL mỗi khi người dùng nhấn chuyển Tab
    const tabButtons = document.querySelectorAll('button[data-bs-toggle="tab"]');
    tabButtons.forEach(btn => {
        btn.addEventListener('shown.bs.tab', (e) => {
            window.location.hash = e.target.getAttribute('data-bs-target');
        });
    });
});


// Validation cho form Hero Video
document.getElementById('heroForm').addEventListener('submit', function(e) {
    const videoInput = document.getElementById('heroVideoInput');
    
    // Nếu không chọn file, không submit
    if (!videoInput.files || videoInput.files.length === 0) {
        e.preventDefault();
        alert('Vui lòng chọn file video trước khi lưu!');
        return false;
    }
    
    // Kiểm tra kích thước file
    const maxSize = 100 * 1024 * 1024; // 100MB
    if (videoInput.files[0].size > maxSize) {
        e.preventDefault();
        alert('File video quá lớn! Tối đa 100MB');
        return false;
    }
    
    // Kiểm tra định dạng file
    if (videoInput.files[0].type !== 'video/mp4') {
        e.preventDefault();
        alert('Chỉ hỗ trợ file MP4!');
        return false;
    }
});

// Validation cho form Intro
document.getElementById('introForm')?.addEventListener('submit', function(e) {
    const introTextInput = document.getElementById('introTextInput');
    const introImageInput = document.getElementById('introImageInput');
    
    // Ít nhất phải có text hoặc image
    const hasText = introTextInput && introTextInput.value.trim().length > 0;
    const hasImage = introImageInput && introImageInput.files && introImageInput.files.length > 0;
    
    if (!hasText && !hasImage) {
        e.preventDefault();
        alert('Vui lòng nhập nội dung hoặc chọn hình ảnh!');
        return false;
    }
    
    // Kiểm tra kích thước ảnh nếu có
    if (hasImage) {
        const maxSize = 10 * 1024 * 1024; // 10MB
        if (introImageInput.files[0].size > maxSize) {
            e.preventDefault();
            alert('File ảnh quá lớn! Tối đa 10MB');
            return false;
        }
    }
});
</script>