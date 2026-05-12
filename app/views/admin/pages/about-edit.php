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
        

        <!-- Intro Tab -->
        <!-- <div class="tab-pane fade" id="intro-pane" role="tabpanel">
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
        </div> -->
        
        <div class="tab-pane fade" id="intro-pane" role="tabpanel">

            <form
                method="POST"
                action="<?= ADMIN_URL ?>page-content/about/save"
                enctype="multipart/form-data"
                id="introForm"
            >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($csrf) ?>"
                >

                <!-- ================= INTRO ================= -->

                <div class="card mb-4">

                    <div class="card-header">
                        <h5>Giới Thiệu Chung</h5>
                    </div>

                    <div class="card-body">

                        <div class="mb-3">
                            <label class="form-label">
                                Nội dung
                            </label>

                            <textarea
                                class="form-control"
                                name="intro_text"
                                rows="5"
                            ><?= htmlspecialchars($aboutText) ?></textarea>
                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                Ảnh giới thiệu
                            </label>

                            <?php if (!empty($aboutImage)): ?>

                                <div class="mb-2">
                                    <img
                                        src="<?= UPLOAD_URL . htmlspecialchars($aboutImage).'?v=' .time() ?>"
                                        style="max-width:300px;border-radius:8px;"
                                    >
                                </div>

                            <?php endif; ?>

                            <input
                                type="file"
                                class="form-control"
                                name="intro_image"
                            >

                        </div>

                    </div>
                </div>

                <!-- ================= VISION ================= -->

                <div class="card mb-4">

                    <div class="card-header">
                        <h5>Tầm Nhìn</h5>
                    </div>

                    <div class="card-body">

                        <div class="mb-3">
                            <label>Tiêu đề</label>

                            <input
                                type="text"
                                class="form-control"
                                name="vision_title"
                                value="<?= htmlspecialchars($visionTitle ?? '') ?>"
                            >
                        </div>

                        <div class="mb-3">
                            <label>Nội dung</label>

                            <textarea
                                class="form-control"
                                name="vision_text"
                                rows="6"
                            ><?= htmlspecialchars($visionText ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">

                            <label>Ảnh</label>

                            <?php if (!empty($visionImage)): ?>

                                <div class="mb-2">
                                    <img
                                        src="<?= UPLOAD_URL . htmlspecialchars($visionImage). '?v=' .time() ?>"
                                        style="max-width:300px;border-radius:8px;"
                                    >
                                </div>

                            <?php endif; ?>

                            <input
                                type="file"
                                class="form-control"
                                name="vision_image"
                            >

                        </div>

                    </div>
                </div>

                <!-- ================= MISSION ================= -->

                <div class="card mb-4">

                    <div class="card-header">
                        <h5>Sứ Mệnh</h5>
                    </div>

                    <div class="card-body">

                        <div class="mb-3">
                            <label>Tiêu đề</label>

                            <input
                                type="text"
                                class="form-control"
                                name="mission_title"
                                value="<?= htmlspecialchars($missionTitle ?? '') ?>"
                            >
                        </div>

                        <div class="mb-3">
                            <label>Nội dung</label>

                            <textarea
                                class="form-control"
                                name="mission_text"
                                rows="6"
                            ><?= htmlspecialchars($missionText ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">

                            <label>Ảnh</label>

                            <?php if (!empty($missionImage)): ?>

                                <div class="mb-2">
                                    <img
                                        src="<?= UPLOAD_URL . htmlspecialchars($missionImage).'?v=' .time() ?>"
                                        style="max-width:300px;border-radius:8px;"
                                    >
                                </div>

                            <?php endif; ?>

                            <input
                                type="file"
                                class="form-control"
                                name="mission_image"
                            >

                        </div>

                    </div>
                </div>

                <!-- ================= PHILOSOPHY ================= -->

                <div class="card mb-4">

                    <div class="card-header">
                        <h5>Triết Lý Thương Hiệu</h5>
                    </div>

                    <div class="card-body">

                        <div class="mb-3">
                            <label>Tiêu đề</label>

                            <input
                                type="text"
                                class="form-control"
                                name="philosophy_title"
                                value="<?= htmlspecialchars($philosophyTitle ?? '') ?>"
                            >
                        </div>

                        <div class="mb-3">
                            <label>Nội dung</label>

                            <textarea
                                class="form-control"
                                name="philosophy_text"
                                rows="6"
                            ><?= htmlspecialchars($philosophyText ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">

                            <label>Ảnh</label>

                            <?php if (!empty($philosophyImage)): ?>

                                <div class="mb-2">
                                    <img
                                        src="<?= UPLOAD_URL . htmlspecialchars($philosophyImage).'?v='.time() ?>"
                                        style="max-width:300px;border-radius:8px;"
                                    >
                                </div>

                            <?php endif; ?>

                            <input
                                type="file"
                                class="form-control"
                                name="philosophy_image"
                            >

                        </div>

                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Lưu toàn bộ nội dung
                </button>

            </form>
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
                                                            <img src="<?= UPLOAD_URL. htmlspecialchars($timelineImages[$year]['main']).'?v='.time() ?>" class="img-thumbnail" style="height: 100px; object-fit: cover;">
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
                                                            <img src="<?= UPLOAD_URL.htmlspecialchars($timelineImages[$year]['secondary']).'?v='.time() ?>" class="img-thumbnail" style="height: 100px; object-fit: cover;">
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
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Giải Thưởng</h5>

            <button class="btn btn-primary btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#awardModal">
                <i class="fas fa-plus"></i>
                Thêm giải thưởng
            </button>
        </div>

        <div class="card-body">
            <div class="row">

                <?php foreach ($awards as $award): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">

                            <img src="<?= UPLOAD_URL . htmlspecialchars($award['image_path']).'?v=' .time() ?>"
                                 class="card-img-top"
                                 style="height:220px; object-fit:cover;">

                            <div class="card-body">
                                <h6><?= htmlspecialchars($award['title']) ?></h6>

                                <small class="text-muted">
                                    Năm <?= (int)$award['award_year'] ?>
                                </small>
                            </div>

                            <div class="card-footer">
                                <button
                                    class="btn btn-sm btn-danger btn-delete-award"
                                    data-id="<?= $award['id'] ?>">
                                    <i class="fas fa-trash"></i>
                                    Xóa
                                </button>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>
    </div>
        </div>

        <div class="modal fade" id="awardModal" tabindex="-1">
            <div class="modal-dialog">
                <form method="POST"
                    action="<?= ADMIN_URL ?>page-content/addaward"
                    enctype="multipart/form-data"
                    class="modal-content">

                    <input type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars($csrf) ?>">

                    <div class="modal-header">
                        <h5 class="modal-title">Thêm giải thưởng</h5>
                    </div>

                    <div class="modal-body">

                        <div class="mb-3">
                            <label>Tên giải thưởng</label>

                            <input type="text"
                                name="title"
                                class="form-control"
                                required>
                        </div>

                        <div class="mb-3">
                            <label>Năm</label>

                            <input type="number"
                                name="award_year"
                                class="form-control"
                                required>
                        </div>

                        <div class="mb-3">
                            <label>Ảnh</label>

                            <input type="file"
                                name="image"
                                class="form-control"
                                accept="image/*"
                                required>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-primary">
                            Lưu
                        </button>
                    </div>

                </form>
            </div>
        </div>




            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.btn-delete-award');

    if (!btn) return;

    const id = btn.dataset.id;

    if (!confirm('Xóa giải thưởng này?')) return;

    fetch(`<?= ADMIN_URL ?>page-content/about/deleteaward?id=${id}`, {
        method: 'POST'
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message);
        }
    });
});

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
// 1. Tự động mở tab dựa trên Hash trên URL HOẶC SessionStorage khi tải trang
window.addEventListener('DOMContentLoaded', () => {
    // Lấy tab từ Hash hoặc từ SessionStorage
    const targetHash = window.location.hash || sessionStorage.getItem('activeTab');
    
    if (targetHash) {
        const tabBtn = document.querySelector(`[data-bs-target="${targetHash}"]`);
        if (tabBtn) {
            const tab = new bootstrap.Tab(tabBtn);
            tab.show();
        }
    }

    // 2. Cập nhật Hash vào URL và SessionStorage mỗi khi người dùng nhấn chuyển Tab
    const tabButtons = document.querySelectorAll('button[data-bs-toggle="tab"]');
    tabButtons.forEach(btn => {
        btn.addEventListener('shown.bs.tab', (e) => {
            const hash = e.target.getAttribute('data-bs-target');
            window.location.hash = hash;
            sessionStorage.setItem('activeTab', hash); // Lưu lại để dùng khi reload
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

    // ===== TEXT =====
    const introText =
        document.querySelector('[name="intro_text"]')?.value.trim();

    const visionText =
        document.querySelector('[name="vision_text"]')?.value.trim();

    const missionText =
        document.querySelector('[name="mission_text"]')?.value.trim();

    const philosophyText =
        document.querySelector('[name="philosophy_text"]')?.value.trim();

    // ===== IMAGE =====
    const introImage =
        document.querySelector('[name="intro_image"]');

    const visionImage =
        document.querySelector('[name="vision_image"]');

    const missionImage =
        document.querySelector('[name="mission_image"]');

    const philosophyImage =
        document.querySelector('[name="philosophy_image"]');

    const hasAnyText =
        introText ||
        visionText ||
        missionText ||
        philosophyText;

    const hasAnyImage =
        (introImage?.files?.length > 0) ||
        (visionImage?.files?.length > 0) ||
        (missionImage?.files?.length > 0) ||
        (philosophyImage?.files?.length > 0);

    // ===== VALIDATE EMPTY =====
    if (!hasAnyText && !hasAnyImage) {

        e.preventDefault();

        alert('Vui lòng nhập ít nhất một nội dung hoặc chọn ảnh!');

        return false;
    }

    // ===== VALIDATE IMAGE SIZE =====
    const maxSize = 10 * 1024 * 1024; // 10MB

    const imageInputs = [
        introImage,
        visionImage,
        missionImage,
        philosophyImage
    ];

    for (const input of imageInputs) {

        if (
            input &&
            input.files &&
            input.files.length > 0
        ) {

            if (input.files[0].size > maxSize) {

                e.preventDefault();

                alert(
                    `Ảnh "${input.name}" vượt quá 10MB`
                );

                return false;
            }
        }
    }
});

// Cập nhật action của các form để gắn thêm hash đang active trước khi submit
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function() {
        const activeTabBtn = document.querySelector('.nav-link.active');
        if (activeTabBtn) {
            const hash = activeTabBtn.getAttribute('data-bs-target');
            // Gắn hash vào cuối URL action nếu chưa có
            let actionUrl = this.getAttribute('action');
            if (actionUrl && !actionUrl.includes('#')) {
                this.setAttribute('action', actionUrl + hash);
            }
        }
    });
});
</script>