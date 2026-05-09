<?php

/**
 * app/views/admin/orders/partials/filters.php
 * Expects: $status, $q
 */
$status = trim((string)($status ?? 'all'));
$q = trim((string)($q ?? ''));
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <h5 class="mb-0">Quản lý đơn hàng</h5>
</div>

<form method="get" action="<?= ADMIN_URL ?>orders" class="row g-2">
    <div class="col-md-6">
        <input
            type="text"
            name="q"
            class="form-control"
            placeholder="Tìm theo khách hàng, email, tên xe hoặc mã đơn"
            value="<?= htmlspecialchars($q) ?>">
    </div>
    <div class="col-md-2">
        <select name="status" class="form-select">
            <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>Tất cả trạng thái</option>
            <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
            <option value="confirmed" <?= $status === 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
            <option value="done" <?= $status === 'done' ? 'selected' : '' ?>>Hoàn tất</option>
            <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
        </select>
    </div>
    <div class="col-md-2 d-grid">
        <button type="submit" class="btn btn-primary">Lọc đơn</button>
    </div>
</form>