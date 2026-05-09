<div class="card-body border-bottom pb-3">
    <div class="pb-3 mb-3  d-flex align-items-center justify-content-between">
        <h5 class="header-title mb-0">Bảng Quản Lý Sản Phẩm</h5>
        <button type="button" id="openCreateProductBtn" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#productModal">
            <i class="fa-solid fa-plus me-2"></i>Thêm Sản Phẩm
        </button>
    </div>
    <form method="GET" action="<?= ADMIN_URL ?>products" class="row g-2 align-items-end mb-2">
        <div class="col-md-4">
            <label class="form-label">Tìm kiếm</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input type="text" name="q" class="form-control border-start-0" placeholder="Tên sản phẩm hoặc danh mục..." value="<?= htmlspecialchars((string)$q) ?>">
            </div>
        </div>

        <div class="col-md-3">
            <label class="form-label">Danh mục</label>
            <select name="cat" class="form-select">
                <option value="0">Tất cả danh mục</option>
                <?php foreach ($cats as $c): ?>
                    <option value="<?= (int)$c['id'] ?>" <?= (int)$cat === (int)$c['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string)$c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label">Trạng thái</label>
            <select name="status" class="form-select">
                <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>Tất cả trạng thái</option>
                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Hiển thị</option>
                <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Đã ẩn</option>
            </select>
        </div>

        <div class="col-6 col-md-1">
            <button class="btn btn-primary w-100 btn-sm" type="submit">
                <i class="fa-solid fa-filter me-1"></i>Lọc
            </button>
        </div>

        <div class="col-6 col-md-2">
            <a href="<?= ADMIN_URL ?>products" class="btn btn-outline-secondary w-100 btn-sm">
                <i class="fa-solid fa-rotate-left me-1"></i>Đặt lại
            </a>
        </div>
    </form>
</div>