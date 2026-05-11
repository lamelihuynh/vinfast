<div class="card-body border-bottom pb-3">
    <?php $cats = isset($cats) && is_array($cats) ? $cats : []; ?>
    <div class="pb-3 mb-3  d-flex align-items-center justify-content-between">
        <h5 class="header-title mb-0">Bảng Quản Lý Sản Phẩm</h5>
        <button type="button" id="openCreateProductBtn" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#productModal">
            <i class="fa-solid fa-plus me-2"></i>Thêm Sản Phẩm
        </button>
    </div>
    <form method="GET" action="<?= ADMIN_URL ?>products" class="mb-2">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label small">Tìm kiếm</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" name="q" class="form-control form-control-sm border-start-0" placeholder="Tên sản phẩm hoặc danh mục..." value="<?= htmlspecialchars((string)$q) ?>">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small">Danh mục</label>
                <select name="cat" class="form-select form-select-sm">
                    <option value="0">Tất cả</option>
                    <?php foreach ($cats as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= (int)$cat === (int)$c['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string)$c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-4 col-md-2">
                <label class="form-label small">Trạng thái</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>Tất cả</option>
                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Hiển thị</option>
                    <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Đã ẩn</option>
                </select>
            </div>
            <div class="col-4 col-md-2">
                <label class="form-label small">Sắp xếp</label>
                <select name="sort" class="form-select form-select-sm">
                    <option value="default" <?= (isset($sort) && $sort === 'default') ? 'selected' : '' ?>>Mặc định</option>
                    <option value="price_desc" <?= (isset($sort) && $sort === 'price_desc') ? 'selected' : '' ?>>Giá: cao → thấp</option>
                    <option value="price_asc" <?= (isset($sort) && $sort === 'price_asc') ? 'selected' : '' ?>>Giá: thấp → cao</option>
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-2 justify-content-md-end">
                <a href="<?= ADMIN_URL ?>products" class="btn btn-outline-secondary btn-sm">Đặt lại</a>
                <button class="btn btn-primary btn-sm" type="submit"><i class="fa-solid fa-filter me-1"></i>Lọc</button>
            </div>
        </div>

    </form>
</div>