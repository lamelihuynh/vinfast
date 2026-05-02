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
  $avatarUrl = !empty($u['avatar']) ? BASE_URL . ltrim($u['avatar'], '/') : BASE_URL . 'public/images/author/avatar.png';
  $createdAt = !empty($u['created_at']) ? date('F Y', strtotime($u['created_at'])) : 'N/A';
  $status    = empty($u['is_locked']) ? 'Hoạt động' : 'Bị khóa';
?>

<div class="main-content-inner" id="main-content">
    
    <?php if (!empty($_SESSION['flash']) || !empty($_SESSION['errors'])): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="alert-items">
                        <?php if (!empty($_SESSION['flash'])): ?>
                            <div class="alert alert-success" role="alert">
                                <strong>Thành công!</strong> <?= htmlspecialchars($_SESSION['flash']) ?>
                            </div>
                            <?php unset($_SESSION['flash']); ?>
                        <?php endif; ?>

                        <?php if (!empty($_SESSION['errors'])): ?>
                            <div class="alert alert-danger" role="alert">
                                <strong>Lỗi!</strong> Vui lòng kiểm tra lại thông tin bên dưới:
                                <ul class="mb-0 mt-2">
                                <?php foreach ($_SESSION['errors'] as $error): ?>
                                    <li>- <?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php unset($_SESSION['errors']); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center text-white p-4" style="background: linear-gradient(135deg, #8914fe, #8063f5); border-radius: 0.375rem;">
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
                                       value="<?= htmlspecialchars($u['name'] ?? '') ?>" 
                                       pattern="^[\x20-\x7E]+$" 
                                       title="Tên người dùng chỉ được chứa các ký tự chữ cái không dấu, số và ký tự cơ bản."
                                       required>
                                <div class="invalid-feedback">Tên chỉ được chứa các ký tự chữ không dấu và số.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($u['email'] ?? '') ?>" 
                                       pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                                       title="Vui lòng nhập đúng định dạng email: ten@mien.com"
                                       required>
                                <div class="invalid-feedback">Định dạng email không hợp lệ.</div>
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
                                <input type="password" class="form-control" id="current" name="current" required>
                            </div>
                            <div class="col-md-6">
                                <label for="new_password" class="form-label">Mật khẩu mới</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" 
                                       pattern="^(?=.*[A-Z])(?=.*\d).{8,}$" 
                                       title="Mật khẩu phải dài hơn 8 ký tự, có ít nhất 1 chữ cái in hoa và 1 chữ số."
                                       required>
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
    var forms = document.querySelectorAll('#formUpdateProfile, #formChangePassword');
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
});
</script>
