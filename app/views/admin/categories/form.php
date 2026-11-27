<?php
/**
 * app/views/admin/categories/form.php — Tạo / Sửa danh mục & Cấu hình thông số sản phẩm (T1-13)
 * Owner: T1 (Data Storage)
 */
$category = is_array($category ?? null) ? $category : null;
$isEdit = $category !== null;
$productCount = (int)($productCount ?? 0);
$currentKind = (string)($category['kind'] ?? 'vehicle');

$dynAttrs = $isEdit && is_array($category['dynamic_attributes'] ?? null) ? $category['dynamic_attributes'] : [];
$varAttrs = $isEdit && is_array($category['variant_attributes'] ?? null) ? $category['variant_attributes'] : [];

$typeModalLabels = [
    'string'  => 'Văn bản ngắn (1 dòng)',
    'number'  => 'Con số (có thể kèm đơn vị)',
    'boolean' => 'Lựa chọn Có / Không',
    'enum'    => 'Danh sách chọn 1 giá trị',
    'list'    => 'Danh sách chọn nhiều giá trị',
    'text'    => 'Mô tả dài (nhiều dòng)',
];

$typeShortLabels = [
    'string'  => 'Chữ ngắn',
    'number'  => 'Con số',
    'boolean' => 'Có / Không',
    'enum'    => 'Chọn 1 mục',
    'list'    => 'Chọn nhiều mục',
    'text'    => 'Đoạn văn',
];
?>

<style>
    .vf-cat-admin .card {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }
    .vf-cat-admin .form-label {
        font-size: 13.5px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 0.4rem;
    }
    .vf-cat-admin .form-control,
    .vf-cat-admin .form-select {
        font-size: 14px;
        line-height: 1.5;
        min-height: 42px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        color: #0f172a;
    }
    .vf-cat-admin .form-select {
        padding: 0.55rem 2.25rem 0.55rem 0.8rem;
    }
    .vf-cat-admin textarea.form-control {
        min-height: auto;
    }
    .vf-cat-admin .form-control:focus,
    .vf-cat-admin .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }
    .vf-cat-admin .form-control[readonly] {
        background-color: #f1f5f9;
        color: #64748b;
        cursor: not-allowed;
    }
    .vf-cat-admin .vf-switch-row {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding-left: 0;
        min-height: 28px;
    }
    .vf-cat-admin .vf-switch-row .form-check-input {
        float: none;
        margin: 0;
        width: 2.25em;
        height: 1.2em;
        cursor: pointer;
        flex-shrink: 0;
    }
    .vf-cat-admin .vf-switch-row .form-check-label {
        margin-bottom: 0;
        font-size: 13.5px;
        font-weight: 500;
        color: #334155;
        cursor: pointer;
    }
    .vf-cat-admin .vf-code-pill {
        display: inline-block;
        padding: 1px 6px;
        border-radius: 4px;
        background: #f1f5f9;
        color: #475569;
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        font-size: 11.5px;
    }
    .vf-cat-admin .vf-attr-table th {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #64748b;
        background: #f8fafc;
        white-space: nowrap;
        padding: 0.65rem 0.75rem;
        border-bottom: 1px solid #e2e8f0;
    }
    .vf-cat-admin .vf-attr-table td {
        padding: 0.7rem 0.75rem;
        vertical-align: middle;
        font-size: 13.5px;
        border-color: #f1f5f9;
    }
    .vf-cat-admin .vf-icon-btn {
        width: 32px;
        height: 32px;
        padding: 0 !important;
        min-height: 32px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        font-size: 13px;
    }
    .vf-cat-admin .vf-header-btn {
        padding: 7px 14px !important;
        font-size: 13px;
        font-weight: 600;
        border-radius: 7px;
        white-space: nowrap;
    }
</style>

<div class="vf-cat-admin">
    <!-- Thanh tiêu đề trang đồng bộ chuẩn Admin -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark">
                <?= $isEdit ? ('Cấu hình danh mục: ' . htmlspecialchars((string)$category['name'])) : 'Thêm danh mục sản phẩm mới' ?>
            </h4>
            <small class="text-muted">
                Danh mục sản phẩm / <?= $isEdit ? htmlspecialchars((string)$category['name']) : 'Tạo mới' ?>
            </small>
        </div>
        <a href="<?= ADMIN_URL ?>categories" class="btn btn-outline-secondary vf-header-btn">
            <i class="fa-solid fa-arrow-left me-1"></i>Quay lại danh sách
        </a>
    </div>

    <div class="row g-4">
        <!-- Cột trái: Thông tin chung của Danh mục -->
        <div class="<?= $isEdit ? 'col-xl-4 col-lg-5' : 'col-xl-6 col-lg-8 mx-auto' ?>">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title fw-bold text-dark mb-3" style="font-size: 16px;">
                        <i class="fa-solid fa-folder-open text-primary me-2"></i>Thông tin chung
                    </h5>

                    <form method="POST" action="<?= ADMIN_URL ?>categories/save" id="categoryBasicForm">
                        <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id" value="<?= (int)$category['id'] ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label" for="cat_name">Tên danh mục <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="cat_name" class="form-control" maxlength="100" required
                                   placeholder="VD: Ô tô điện, Phụ kiện sạc..."
                                   value="<?= htmlspecialchars((string)($category['name'] ?? '')) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="cat_slug">Đường dẫn tĩnh (Slug) <span class="text-danger">*</span></label>
                            <input type="text" name="slug" id="cat_slug" class="form-control" maxlength="100" required
                                   pattern="^[a-z0-9]+(?:-[a-z0-9]+)*$"
                                   placeholder="VD: o-to-dien"
                                   value="<?= htmlspecialchars((string)($category['slug'] ?? '')) ?>">
                            <small class="text-muted d-block mt-1">Dùng trên địa chỉ web, viết thường không dấu nối bằng dấu gạch ngang.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="cat_kind">Nhóm mặt hàng <span class="text-danger">*</span></label>
                            <select name="kind" id="cat_kind" class="form-select">
                                <option value="vehicle" <?= $currentKind === 'vehicle' ? 'selected' : '' ?>>
                                    Xe điện (Đặt cọc &amp; Đăng ký lái thử)
                                </option>
                                <option value="accessory" <?= $currentKind === 'accessory' ? 'selected' : '' ?>>
                                    Phụ kiện (Mua trực tiếp theo số lượng)
                                </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="cat_desc">Mô tả ngắn</label>
                            <textarea name="description" id="cat_desc" rows="3" class="form-control" maxlength="500"
                                      placeholder="Giới thiệu ngắn gọn về nhóm sản phẩm này..."><?= htmlspecialchars((string)($category['description'] ?? '')) ?></textarea>
                        </div>

                        <div class="form-check form-switch vf-switch-row mb-4">
                            <input type="checkbox" class="form-check-input" role="switch" name="is_active" id="cat_is_active"
                                <?= ((int)($category['is_active'] ?? 1) === 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="cat_is_active">Hiển thị danh mục trên website</label>
                        </div>

                        <?php if ($isEdit): ?>
                            <div class="d-flex align-items-center justify-content-between rounded-3 bg-light border px-3 py-2 small mb-3">
                                <span class="text-muted">Số sản phẩm hiện có:</span>
                                <strong class="text-dark"><?= number_format($productCount, 0, ',', '.') ?> sản phẩm</strong>
                            </div>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-primary w-100 fw-semibold" style="border-radius: 8px;">
                            <i class="fa-solid fa-floppy-disk me-1"></i><?= $isEdit ? 'Lưu thay đổi' : 'Tạo danh mục' ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <?php if ($isEdit): ?>
            <!-- Cột phải: Cấu hình bộ thông số của Danh mục -->
            <div class="col-xl-8 col-lg-7">
                <!-- 1. Thông số kỹ thuật chung (Cấp sản phẩm) -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                            <div>
                                <h5 class="card-title fw-bold text-dark mb-1" style="font-size: 16px;">
                                    <i class="fa-solid fa-list-check text-primary me-2"></i>Thông số kỹ thuật chung
                                </h5>
                                <small class="text-muted d-block">
                                    Các thông số này sẽ tự động hiển thị thành ô nhập liệu khi quản trị viên thêm hoặc sửa sản phẩm thuộc danh mục.
                                </small>
                            </div>
                            <button type="button" class="btn btn-primary vf-header-btn js-open-attr-modal"
                                    data-mode="add" data-scope="dynamic">
                                <i class="fa-solid fa-plus me-1"></i>Thêm thông số
                            </button>
                        </div>

                        <?php if (!empty($dynAttrs)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover vf-attr-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Tên thông số</th>
                                            <th>Định dạng</th>
                                            <th>Đơn vị</th>
                                            <th>Thiết lập</th>
                                            <th class="text-end" style="width:92px">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($dynAttrs as $a): ?>
                                            <?php
                                            $aKey = (string)($a['key'] ?? '');
                                            $aType = (string)($a['type'] ?? 'string');
                                            $aUnit = trim((string)($a['unit'] ?? ''));
                                            $opts = is_array($a['options'] ?? null) ? $a['options'] : [];
                                            $attrJson = htmlspecialchars(json_encode([
                                                'scope'      => 'dynamic',
                                                'key'        => $aKey,
                                                'label'      => (string)($a['label'] ?? ''),
                                                'type'       => $aType,
                                                'unit'       => $aUnit,
                                                'required'   => !empty($a['required']),
                                                'filterable' => !empty($a['filterable']),
                                                'options'    => implode(', ', $opts),
                                            ], JSON_UNESCAPED_UNICODE), ENT_QUOTES);
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold text-dark"><?= htmlspecialchars((string)$a['label']) ?></div>
                                                    <div class="mt-1 d-flex flex-wrap align-items-center gap-2">
                                                        <span class="vf-code-pill" title="Mã định danh hệ thống"><?= htmlspecialchars($aKey) ?></span>
                                                        <?php if ($opts): ?>
                                                            <small class="text-muted">Lựa chọn: <?= htmlspecialchars(implode(', ', $opts)) ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td class="text-nowrap">
                                                    <span class="badge bg-light text-dark border fw-medium">
                                                        <?= htmlspecialchars($typeShortLabels[$aType] ?? $aType) ?>
                                                    </span>
                                                </td>
                                                <td class="text-nowrap text-muted">
                                                    <?= $aUnit !== '' ? htmlspecialchars($aUnit) : '—' ?>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-wrap gap-1">
                                                        <?php if (!empty($a['required'])): ?>
                                                            <span class="badge bg-danger">Bắt buộc</span>
                                                        <?php endif; ?>
                                                        <?php if (!empty($a['filterable'])): ?>
                                                            <span class="badge bg-primary">Dùng lọc SP</span>
                                                        <?php endif; ?>
                                                        <?php if (empty($a['required']) && empty($a['filterable'])): ?>
                                                            <span class="text-muted small">Không bắt buộc</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td class="text-end text-nowrap">
                                                    <div class="d-inline-flex align-items-center gap-1">
                                                        <button type="button" class="btn btn-outline-secondary vf-icon-btn js-open-attr-modal"
                                                                data-mode="edit" data-attr="<?= $attrJson ?>" title="Chỉnh sửa thông số">
                                                            <i class="fa-solid fa-pen"></i>
                                                        </button>
                                                        <form method="POST" action="<?= ADMIN_URL ?>categories/attribute-delete" class="d-inline"
                                                              onsubmit="return confirm('Gỡ thông số «<?= htmlspecialchars(addslashes((string)$a['label'])) ?>» khỏi biểu mẫu nhập sản phẩm? (Dữ liệu đã lưu trên các sản phẩm cũ vẫn được giữ nguyên)');">
                                                            <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                                                            <input type="hidden" name="category_id" value="<?= (int)$category['id'] ?>">
                                                            <input type="hidden" name="scope" value="dynamic">
                                                            <input type="hidden" name="key" value="<?= htmlspecialchars($aKey) ?>">
                                                            <button type="submit" class="btn btn-outline-danger vf-icon-btn" title="Gỡ thông số">
                                                                <i class="fa-solid fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="rounded-3 border bg-light p-4 text-center text-muted small">
                                Danh mục này chưa có thông số kỹ thuật riêng. Bấm <strong>Thêm thông số</strong> để bắt đầu cấu hình.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 2. Phân loại phiên bản bán hàng (Chỉ dùng cho Phụ kiện; tự ẩn gọn khi là Xe điện không có dữ liệu) -->
                <div class="card <?= ($currentKind === 'vehicle' && empty($varAttrs)) ? 'd-none' : '' ?>" id="variantAttrsCard">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                            <div>
                                <h5 class="card-title fw-bold text-dark mb-1" style="font-size: 16px;">
                                    <i class="fa-solid fa-layer-group text-primary me-2"></i>Thuộc tính chia phiên bản (Màu sắc / Kích cỡ)
                                </h5>
                                <small class="text-muted d-block">
                                    Áp dụng cho nhóm Phụ kiện để chia các lựa chọn mua hàng có giá bán và số lượng tồn kho riêng.
                                </small>
                            </div>
                            <button type="button" class="btn btn-outline-primary vf-header-btn js-open-attr-modal"
                                    data-mode="add" data-scope="variant">
                                <i class="fa-solid fa-plus me-1"></i>Thêm phân loại
                            </button>
                        </div>

                        <?php if (!empty($varAttrs)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover vf-attr-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Tên phân loại</th>
                                            <th>Định dạng</th>
                                            <th>Đơn vị</th>
                                            <th>Thiết lập</th>
                                            <th class="text-end" style="width:92px">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($varAttrs as $a): ?>
                                            <?php
                                            $aKey = (string)($a['key'] ?? '');
                                            $aType = (string)($a['type'] ?? 'string');
                                            $aUnit = trim((string)($a['unit'] ?? ''));
                                            $opts = is_array($a['options'] ?? null) ? $a['options'] : [];
                                            $attrJson = htmlspecialchars(json_encode([
                                                'scope'      => 'variant',
                                                'key'        => $aKey,
                                                'label'      => (string)($a['label'] ?? ''),
                                                'type'       => $aType,
                                                'unit'       => $aUnit,
                                                'required'   => !empty($a['required']),
                                                'filterable' => false,
                                                'options'    => implode(', ', $opts),
                                            ], JSON_UNESCAPED_UNICODE), ENT_QUOTES);
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold text-dark"><?= htmlspecialchars((string)$a['label']) ?></div>
                                                    <div class="mt-1 d-flex flex-wrap align-items-center gap-2">
                                                        <span class="vf-code-pill"><?= htmlspecialchars($aKey) ?></span>
                                                        <?php if ($opts): ?>
                                                            <small class="text-muted">Lựa chọn: <?= htmlspecialchars(implode(', ', $opts)) ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td class="text-nowrap">
                                                    <span class="badge bg-light text-dark border fw-medium">
                                                        <?= htmlspecialchars($typeShortLabels[$aType] ?? $aType) ?>
                                                    </span>
                                                </td>
                                                <td class="text-nowrap text-muted">
                                                    <?= $aUnit !== '' ? htmlspecialchars($aUnit) : '—' ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($a['required'])): ?>
                                                        <span class="badge bg-danger">Bắt buộc</span>
                                                    <?php else: ?>
                                                        <span class="text-muted small">Không bắt buộc</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end text-nowrap">
                                                    <div class="d-inline-flex align-items-center gap-1">
                                                        <button type="button" class="btn btn-outline-secondary vf-icon-btn js-open-attr-modal"
                                                                data-mode="edit" data-attr="<?= $attrJson ?>" title="Chỉnh sửa phân loại">
                                                            <i class="fa-solid fa-pen"></i>
                                                        </button>
                                                        <form method="POST" action="<?= ADMIN_URL ?>categories/attribute-delete" class="d-inline"
                                                              onsubmit="return confirm('Gỡ phân loại phiên bản «<?= htmlspecialchars(addslashes((string)$a['label'])) ?>»?');">
                                                            <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                                                            <input type="hidden" name="category_id" value="<?= (int)$category['id'] ?>">
                                                            <input type="hidden" name="scope" value="variant">
                                                            <input type="hidden" name="key" value="<?= htmlspecialchars($aKey) ?>">
                                                            <button type="submit" class="btn btn-outline-danger vf-icon-btn" title="Gỡ phân loại">
                                                                <i class="fa-solid fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="rounded-3 border bg-light p-4 text-center text-muted small">
                                Chưa khai báo thuộc tính chia phiên bản (ví dụ: Màu sắc, Kích cỡ S/M/L).
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Ghi chú nhẹ cho danh mục Xe điện -->
                <div class="rounded-3 border bg-white px-3 py-2.5 small text-muted <?= ($currentKind === 'vehicle' && empty($varAttrs)) ? '' : 'd-none' ?>" id="vehicleVariantNote">
                    <i class="fa-solid fa-circle-info text-primary me-1"></i>
                    Đối với nhóm <strong>Xe điện</strong>, phiên bản xe (Tiêu chuẩn / Eco / Plus) và bảng màu sơn ngoại thất được quản lý trực tiếp ngay trên từng mẫu xe.
                </div>
            </div>

            <!-- Modal Thêm / Sửa Thông số -->
            <div class="modal fade" id="attrModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="border-radius: 12px;">
                        <form method="POST" action="<?= ADMIN_URL ?>categories/attribute-save" id="attrModalForm">
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold" id="attrModalTitle">Thêm thông số mới</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                                <input type="hidden" name="category_id" value="<?= (int)$category['id'] ?>">
                                <input type="hidden" name="mode" id="attr_mode" value="add">
                                <input type="hidden" name="scope" id="attr_scope" value="dynamic">

                                <div class="row g-3">
                                    <div class="col-md-7">
                                        <label class="form-label" for="attr_label">Tên hiển thị <span class="text-danger">*</span></label>
                                        <input type="text" name="label" id="attr_label" class="form-control" required
                                               maxlength="100" placeholder="VD: Thời gian sạc nhanh">
                                    </div>

                                    <div class="col-md-5">
                                        <label class="form-label" for="attr_key">Mã định danh <span class="text-danger">*</span></label>
                                        <input type="text" name="key" id="attr_key" class="form-control" required
                                               pattern="^[a-zA-Z][a-zA-Z0-9_]*$" placeholder="VD: charging_time">
                                        <small class="text-muted d-block mt-1" id="attrKeyHelp">Tự gợi ý không dấu (a-z, 0-9, _).</small>
                                    </div>

                                    <div class="col-md-7">
                                        <label class="form-label" for="attr_type">Định dạng nhập liệu <span class="text-danger">*</span></label>
                                        <select name="type" id="attr_type" class="form-select">
                                            <?php foreach ($typeModalLabels as $tKey => $tLabel): ?>
                                                <option value="<?= $tKey ?>"><?= htmlspecialchars($tLabel) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-5">
                                        <label class="form-label" for="attr_unit">Đơn vị tính</label>
                                        <input type="text" name="unit" id="attr_unit" class="form-control"
                                               maxlength="30" placeholder="VD: km, phút, kWh">
                                    </div>

                                    <div class="col-12 d-none" id="attrOptionsGroup">
                                        <label class="form-label" for="attr_options">Danh sách lựa chọn</label>
                                        <textarea name="options" id="attr_options" rows="2" class="form-control"
                                                  placeholder="Nhập các lựa chọn cách nhau bằng dấu phẩy. VD: Đen, Trắng, Xám"></textarea>
                                        <small class="text-muted d-block mt-1">Người nhập sẽ chọn trực tiếp từ danh sách này.</small>
                                    </div>

                                    <div class="col-12 pt-1">
                                        <div class="d-flex flex-wrap gap-4">
                                            <div class="form-check form-switch vf-switch-row">
                                                <input type="checkbox" class="form-check-input" role="switch" name="required" id="attr_required" value="1">
                                                <label class="form-check-label" for="attr_required">Bắt buộc phải nhập</label>
                                            </div>

                                            <div class="form-check form-switch vf-switch-row" id="attrFilterableGroup">
                                                <input type="checkbox" class="form-check-input" role="switch" name="filterable" id="attr_filterable" value="1">
                                                <label class="form-check-label" for="attr_filterable">Dùng làm tiêu chí lọc trên trang sản phẩm</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light vf-header-btn" data-bs-dismiss="modal">Hủy</button>
                                <button type="submit" class="btn btn-primary vf-header-btn" id="attrModalSubmit">Lưu thông số</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var nameInput = document.getElementById('cat_name');
    var slugInput = document.getElementById('cat_slug');
    var kindSelect = document.getElementById('cat_kind');
    var variantCard = document.getElementById('variantAttrsCard');
    var vehicleNote = document.getElementById('vehicleVariantNote');
    var hasVarAttrs = <?= !empty($varAttrs) ? 'true' : 'false' ?>;
    var isEdit = <?= $isEdit ? 'true' : 'false' ?>;
    var slugTouched = isEdit;

    function slugify(text, sep) {
        return (text || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[đĐ]/g, 'd')
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, sep)
            .replace(new RegExp('^' + sep + '+|' + sep + '+$', 'g'), '');
    }

    if (slugInput) {
        slugInput.addEventListener('input', function () {
            slugTouched = slugInput.value.trim() !== '';
        });
    }
    if (nameInput && slugInput) {
        nameInput.addEventListener('input', function () {
            if (!slugTouched) {
                slugInput.value = slugify(nameInput.value, '-');
            }
        });
    }

    if (kindSelect && variantCard && vehicleNote) {
        kindSelect.addEventListener('change', function () {
            var hideVariantCard = (kindSelect.value === 'vehicle' && !hasVarAttrs);
            variantCard.classList.toggle('d-none', hideVariantCard);
            vehicleNote.classList.toggle('d-none', !hideVariantCard);
        });
    }

    var modalEl = document.getElementById('attrModal');
    if (!modalEl || typeof bootstrap === 'undefined') return;
    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);

    var titleEl = document.getElementById('attrModalTitle');
    var modeInput = document.getElementById('attr_mode');
    var scopeInput = document.getElementById('attr_scope');
    var keyInput = document.getElementById('attr_key');
    var keyHelp = document.getElementById('attrKeyHelp');
    var labelInput = document.getElementById('attr_label');
    var typeSelect = document.getElementById('attr_type');
    var unitInput = document.getElementById('attr_unit');
    var optionsInput = document.getElementById('attr_options');
    var reqCheck = document.getElementById('attr_required');
    var filterCheck = document.getElementById('attr_filterable');
    var optionsGroup = document.getElementById('attrOptionsGroup');
    var filterGroup = document.getElementById('attrFilterableGroup');
    var keyManuallyEdited = false;

    function syncVisibility() {
        var t = typeSelect.value;
        optionsGroup.classList.toggle('d-none', t !== 'enum' && t !== 'list');
        filterGroup.classList.toggle('d-none', scopeInput.value !== 'dynamic');
    }

    typeSelect.addEventListener('change', syncVisibility);

    keyInput.addEventListener('input', function () {
        if (!keyInput.readOnly) {
            keyManuallyEdited = keyInput.value.trim() !== '';
        }
    });

    labelInput.addEventListener('input', function () {
        if (!keyInput.readOnly && !keyManuallyEdited) {
            var suggested = slugify(labelInput.value, '_');
            if (suggested && /^[0-9]/.test(suggested)) {
                suggested = 'attr_' + suggested;
            }
            keyInput.value = suggested;
        }
    });

    document.querySelectorAll('.js-open-attr-modal').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var mode = btn.getAttribute('data-mode') || 'add';
            modeInput.value = mode;

            if (mode === 'edit') {
                var data = {};
                try { data = JSON.parse(btn.getAttribute('data-attr') || '{}'); } catch (e) {}
                scopeInput.value = data.scope || 'dynamic';
                titleEl.textContent = 'Chỉnh sửa thông số: ' + (data.label || data.key || '');
                keyInput.value = data.key || '';
                keyInput.readOnly = true;
                keyManuallyEdited = true;
                if (keyHelp) keyHelp.textContent = 'Mã định danh được giữ cố định để bảo toàn dữ liệu cũ.';
                labelInput.value = data.label || '';
                typeSelect.value = data.type || 'string';
                unitInput.value = data.unit || '';
                optionsInput.value = data.options || '';
                reqCheck.checked = !!data.required;
                filterCheck.checked = !!data.filterable;
            } else {
                var scope = btn.getAttribute('data-scope') || 'dynamic';
                scopeInput.value = scope;
                titleEl.textContent = scope === 'variant'
                    ? 'Thêm phân loại phiên bản'
                    : 'Thêm thông số kỹ thuật mới';
                keyInput.value = '';
                keyInput.readOnly = false;
                keyManuallyEdited = false;
                if (keyHelp) keyHelp.textContent = 'Tự gợi ý không dấu (a-z, 0-9, _).';
                labelInput.value = '';
                typeSelect.value = 'string';
                unitInput.value = '';
                optionsInput.value = '';
                reqCheck.checked = false;
                filterCheck.checked = false;
            }

            syncVisibility();
            modal.show();
        });
    });
});
</script>
