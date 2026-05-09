<?php
/**
 * app/views/admin/users/index.php
 * Owner  : All members (common)
 * Title  : User Management
 */
?>
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h4 class="header-title mb-0"><i class="ti-user me-2"></i>Quản lý Tài khoản</h4>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body border-bottom pb-3">
        <form method="GET" action="<?= ADMIN_URL ?>users" class="row g-2 align-items-end mb-2">
            <div class="col-md-4">
                <label class="form-label">Tìm kiếm</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" name="q" class="form-control border-start-0" placeholder="Tên hoặc email..." value="<?= htmlspecialchars((string)($q ?? '')) ?>">
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Phân quyền</label>
                <select name="role" class="form-select">
                    <option value="" <?= ($role ?? '') === '' ? 'selected' : '' ?>>Tất cả quyền</option>
                    <option value="admin" <?= ($role ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="member" <?= ($role ?? '') === 'member' ? 'selected' : '' ?>>Member</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <button class="btn btn-primary w-100 btn-sm" type="submit">
                    <i class="fa-solid fa-filter me-1"></i>Lọc
                </button>
            </div>
            <div class="col-6 col-md-2">
                <a href="<?= ADMIN_URL ?>users" class="btn btn-outline-secondary w-100 btn-sm">
                    <i class="fa-solid fa-rotate-left me-1"></i>Đặt lại
                </a>
            </div>
        </form>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase">
                    <tr>
                        <th scope="col" style="width: 80px;">Avatar</th>
                        <th scope="col">Tài khoản</th>
                        <th scope="col">Phân quyền</th>
                        <th scope="col" style="width: 170px;">Ngày tạo</th>
                        <th scope="col" style="width: 150px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fa-solid fa-users" style="font-size:3em;opacity:0.3"></i>
                                    <p class="mt-3">Chưa có người dùng nào.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($u['avatar'])): ?>
                                        <img src="<?= BASE_URL . htmlspecialchars($u['avatar']) ?>" alt="Avatar" class="rounded-circle" style="width:40px;height:40px;object-fit:cover;">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width:40px;height:40px;font-size:18px;">
                                            <?= strtoupper(substr(trim((string)$u['name']), 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars((string)($u['name'] ?? '')) ?></div>
                                    <div class="text-muted small"><?= htmlspecialchars((string)($u['email'] ?? '')) ?></div>
                                </td>
                                <td>
                                    <?php if ($u['role'] === 'admin'): ?>
                                        <span class="status-p bg-primary text-white" style="padding:6px 12px;border-radius:4px;display:inline-block"><i class="fa-solid fa-shield-halved me-1"></i>Admin</span>
                                    <?php else: ?>
                                        <span class="status-p bg-secondary text-white" style="padding:6px 12px;border-radius:4px;display:inline-block"><i class="fa-solid fa-user me-1"></i>Member</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small">
                                    <?= htmlspecialchars((string)($u['created_at'] ?? '')) ?>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-start gap-2">
                                        <?php if ((int)$u['id'] !== (int)Auth::id()): ?>
                                            <form action="<?= ADMIN_URL ?>users/updateRole/<?= (int)$u['id'] ?>" method="post" onsubmit="return confirm('Bạn có chắc chắn muốn thay đổi quyền của tài khoản này?');">
                                                <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                                                <?php if ($u['role'] === 'admin'): ?>
                                                    <input type="hidden" name="role" value="member">
                                                    <button class="btn btn-link text-warning p-0 text-decoration-none" type="submit" title="Hạ quyền xuống Member">
                                                        <i class="fa-solid fa-arrow-down me-1"></i>Hạ quyền
                                                    </button>
                                                <?php else: ?>
                                                    <input type="hidden" name="role" value="admin">
                                                    <button class="btn btn-link text-success p-0 text-decoration-none" type="submit" title="Nâng quyền lên Admin">
                                                        <i class="fa-solid fa-arrow-up me-1"></i>Nâng quyền
                                                    </button>
                                                <?php endif; ?>
                                            </form>
                                            
                                            <form action="<?= ADMIN_URL ?>users/delete/<?= (int)$u['id'] ?>" method="post" onsubmit="return confirm('Bạn có chắc chắn muốn xoá tài khoản này?');">
                                                <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                                                <button class="btn btn-link text-danger p-0" type="submit" title="Xóa">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted small">Tài khoản của bạn</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php
        $itemName = 'tài khoản';
        if (!empty($pg) && ($pg->pages ?? 1) > 1) {
            include ROOT . '/app/views/admin/partials/pagination.php';
        }
        ?>
    </div>
</div>
