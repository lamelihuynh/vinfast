<?php
/**
 * app/views/frontend/user/profile.php
 * Owner  : All members (common)
 * Title  : My Profile
 *
 * Purpose: Two-section layout: (1) Edit name, email, avatar upload — POSTs to /user/saveProfile. (2) Change password — POSTs to /user/changePassword. Both need CSRF token.
 *
 * Variables available (set by controller via View::render):
 *   $user (array)
 *
  Assets    : (none)
 *
 * TODO: Replace the placeholder below with the actual HTML implementation.
 * -----------------------------------------------------------------------
 * Rules:
 *  - Always escape output: <?= htmlspecialchars($var) ?>
 *  - Include CSRF in every form: <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
 *  - Include pagination partial where needed: include ROOT."/app/views/frontend/partials/pagination.php"
 */
?>
<!-- TODO: Implement My Profile -->
<?php
  $u = $user ?? [];
  $avatarUrl = !empty($u['avatar']) ? BASE_URL . ltrim($u['avatar'], '/') : BASE_URL . 'public/images/avatars/default/default.jpg';
  $createdAt = !empty($u['created_at']) ? date('F Y', strtotime($u['created_at'])) : 'N/A';
  $status    = empty($u['is_locked']) ? 'Hoạt động' : 'Bị khóa';
?>

<div class="main-content-inner" id="main-content">
    
    <div class="row mt-4">
        <div class="col-12" id="alertContainer">
            <?php if (!empty($_SESSION['flash'])): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <strong>Well done!</strong> <?= htmlspecialchars($_SESSION['flash']) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['errors'])): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <strong>Oh snap!</strong> Vui lòng kiểm tra lại thông tin bên dưới:
                    <ul class="mb-0 mt-2">
                    <?php foreach ($_SESSION['errors'] as $error): ?>
                        <li>- <?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                    </ul>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['errors']); ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body d-flex flex-column align-items-center justify-content-center text-center text-white p-4" style="background: linear-gradient(135deg, #8914fe, #8063f5); border-radius: 0.375rem;">
                    <div class="mb-3">
                        <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="User Avatar" style="width: 100px; height: 100px; border-radius: 50%; border: 3px solid rgba(255,255,255,0.3); object-fit: cover;">
                    </div>
                    <h3 class="mb-1"><?= htmlspecialchars($u['name'] ?? 'Chưa cập nhật tên') ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h4 class="header-title mb-0 font-bold">Thông tin người dùng</h4>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="fa-solid fa-user me-2 text-muted"></i> Tên</span>
                            <strong><?= htmlspecialchars($u['name'] ?? '') ?></strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="fa-solid fa-envelope me-2 text-muted"></i> Email</span>
                            <strong><?= htmlspecialchars($u['email'] ?? '') ?></strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="fa-solid fa-calendar me-2 text-muted"></i> Tham gia</span>
                            <strong><?= $createdAt ?></strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="fa-solid fa-check me-2 text-muted"></i> Trạng thái</span>
                            <strong class="<?= empty($u['is_locked']) ? 'text-success' : 'text-danger' ?>"><?= $status ?></strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="header-title mb-0 font-bold">Cập nhật hồ sơ</h4>
                </div>
                <div class="card-body">
                    <form id="formUpdateProfile" action="<?= BASE_URL ?>user/saveProfile" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Tên người dùng</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?= htmlspecialchars($u['name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="text" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($u['email'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label for="avatar" class="form-label">Ảnh đại diện (Tùy chọn)</label>
                                <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Lưu Hồ Sơ</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="header-title mb-0 font-bold">Đổi Mật Khẩu</h4>
                </div>
                <div class="card-body">
                    <form id="formChangePassword" action="<?= BASE_URL ?>user/changePassword" method="POST">
                        <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="current" class="form-label">Mật khẩu hiện tại</label>
                                <input type="password" class="form-control" id="current" name="current">
                            </div>
                            <div class="col-md-6">
                                <label for="new_password" class="form-label">Mật khẩu mới</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                                <div class="text-muted" style="font-size: 0.8rem; margin-top: 5px;">* Tối thiểu 8 ký tự, gồm 1 chữ in hoa và 1 chữ số.</div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-warning">Đổi Mật Khẩu</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const existingAlerts = document.querySelectorAll('#alertContainer .alert');
    existingAlerts.forEach(alert => {
        setTimeout(() => {
            alert.classList.remove('show'); 
            setTimeout(() => alert.remove(), 150); 
        }, 5000);
    });

    function showCustomAlert(type, title, message) {
        const container = document.getElementById('alertContainer');
        const alertId = 'alert-' + Date.now(); 
        
        container.innerHTML = `
            <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show shadow-sm" role="alert">
                <strong>${title}</strong> <br>${message.replace(/\n/g, '<br>')}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close" onclick="this.parentElement.remove()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;
        container.scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(() => {
            const currentAlert = document.getElementById(alertId);
            if (currentAlert) {
                currentAlert.classList.remove('show');
                setTimeout(() => currentAlert.remove(), 150);
            }
        }, 5000);
    }

    const formProfile = document.getElementById('formUpdateProfile');
    formProfile.addEventListener('submit', function(e) {
        e.preventDefault();
        let isValid = true;
        let errors = [];

        const nameInput = document.getElementById('name');
        if (nameInput.value.trim().length < 2) {
            isValid = false;
            errors.push("- Tên người dùng phải có tối thiểu 2 ký tự.");
            nameInput.classList.add('is-invalid');
        } else {
            nameInput.classList.remove('is-invalid');
        }

        const emailInput = document.getElementById('email');
        const emailRegex = /^.+@.+\..+$/;
        if (!emailRegex.test(emailInput.value.trim())) {
            isValid = false;
            errors.push("- Định dạng email không hợp lệ (ví dụ: ten@mien.com).");
            emailInput.classList.add('is-invalid');
        } else {
            emailInput.classList.remove('is-invalid');
        }

        const avatarInput = document.getElementById('avatar');
        if (avatarInput.files.length > 0) {
            const file = avatarInput.files[0];
            if (!file.type.startsWith('image/')) {
                isValid = false;
                errors.push("- Vui lòng chỉ chọn các tệp định dạng hình ảnh (JPG, PNG, WEBP...).");
                avatarInput.classList.add('is-invalid');
            } else {
                avatarInput.classList.remove('is-invalid');
            }
        }

        if (!isValid) {
            showCustomAlert('danger', 'Oh snap!', errors.join('\n'));
        } else {
            showCustomAlert('success', 'Well done!', 'Dữ liệu hợp lệ! Đang lưu hồ sơ...');
            setTimeout(() => formProfile.submit(), 1000);
        }
    });

    const formPassword = document.getElementById('formChangePassword');
    formPassword.addEventListener('submit', function(e) {
        e.preventDefault();
        let isValid = true;
        let errors = [];

        const currentPwd = document.getElementById('current');
        if (currentPwd.value.trim() === '') {
            isValid = false;
            errors.push("- Vui lòng nhập mật khẩu hiện tại.");
            currentPwd.classList.add('is-invalid');
        } else {
            currentPwd.classList.remove('is-invalid');
        }

        const newPwd = document.getElementById('new_password');
        const pwdRegex = /^(?=.*[A-Z])(?=.*\d).{8,}$/;
        if (!pwdRegex.test(newPwd.value)) {
            isValid = false;
            errors.push("- Mật khẩu mới phải dài hơn 8 ký tự, có ít nhất 1 chữ cái in hoa và 1 chữ số.");
            newPwd.classList.add('is-invalid');
        } else {
            newPwd.classList.remove('is-invalid');
        }

        if (!isValid) {
            showCustomAlert('danger', 'Oh snap!', errors.join('\n'));
        } else {
            showCustomAlert('success', 'Well done!', 'Dữ liệu hợp lệ! Đang đổi mật khẩu...');
            setTimeout(() => formPassword.submit(), 1000);
        }
    });
});
</script>
