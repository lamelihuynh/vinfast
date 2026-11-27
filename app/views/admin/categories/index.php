<?php
/**
 * app/views/admin/categories/index.php — Danh sách danh mục sản phẩm (T1-13)
 * Owner: T1 (Data Storage)
 */
$categories = is_array($categories ?? null) ? $categories : [];
$productCounts = is_array($productCounts ?? null) ? $productCounts : [];
?>

<style>
    .vf-cat-list .card {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }
    .vf-cat-list .vf-table th {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #64748b;
        background: #f8fafc;
        white-space: nowrap;
        padding: 0.75rem 0.85rem;
        border-bottom: 1px solid #e2e8f0;
    }
    .vf-cat-list .vf-table td {
        padding: 0.8rem 0.85rem;
        vertical-align: middle;
        font-size: 13.5px;
        border-color: #f1f5f9;
    }
    .vf-cat-list .vf-slug-pill {
        display: inline-block;
        padding: 2px 7px;
        border-radius: 4px;
        background: #f1f5f9;
        color: #475569;
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        font-size: 12px;
    }
    .vf-cat-list .vf-action-btn {
        padding: 6px 12px !important;
        min-height: 32px !important;
        font-size: 12.5px;
        font-weight: 600;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
    }
    .vf-cat-list .vf-icon-btn {
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
</style>

<div class="vf-cat-list">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark">
                <i class="fa-solid fa-sitemap me-2 text-primary"></i>Danh mục sản phẩm
            </h4>
            <small class="text-muted">
                Quản lý các nhóm ngành hàng và cấu hình bộ thông số riêng cho từng loại sản phẩm.
            </small>
        </div>
        <a href="<?= ADMIN_URL ?>categories/create" class="btn btn-primary fw-semibold" style="padding: 8px 16px; border-radius: 8px;">
            <i class="fa-solid fa-plus me-1"></i>Thêm danh mục
        </a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <?php if (!empty($categories)): ?>
                <div class="table-responsive">
                    <table class="table table-hover vf-table mb-0">
                        <thead>
                            <tr>
                                <th style="width:65px">Mã</th>
                                <th>Tên danh mục</th>
                                <th>Đường dẫn (Slug)</th>
                                <th>Nhóm hàng</th>
                                <th>Bộ thông số</th>
                                <th class="text-end">Số sản phẩm</th>
                                <th>Trạng thái</th>
                                <th class="text-end" style="width:155px">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                                <?php
                                $cid = (int)$cat['id'];
                                $pCount = (int)($productCounts[$cid] ?? 0);
                                $dynAttrs = is_array($cat['dynamic_attributes'] ?? null) ? $cat['dynamic_attributes'] : [];
                                $varAttrs = is_array($cat['variant_attributes'] ?? null) ? $cat['variant_attributes'] : [];
                                $isVehicle = ($cat['kind'] ?? 'vehicle') === 'vehicle';
                                ?>
                                <tr>
                                    <td class="fw-semibold text-muted">#<?= $cid ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars((string)$cat['name']) ?></div>
                                        <?php if (!empty($cat['description'])): ?>
                                            <small class="text-muted d-block text-truncate" style="max-width:280px"><?= htmlspecialchars((string)$cat['description']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="vf-slug-pill"><?= htmlspecialchars((string)$cat['slug']) ?></span></td>
                                    <td class="text-nowrap">
                                        <?php if ($isVehicle): ?>
                                            <span class="badge bg-info text-dark">Xe điện</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Phụ kiện</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            <span class="badge bg-light text-dark border">
                                                <?= count($dynAttrs) ?> thông số chung
                                            </span>
                                            <?php if (count($varAttrs) > 0 || !$isVehicle): ?>
                                                <span class="badge bg-light text-secondary border">
                                                    <?= count($varAttrs) ?> phân loại phiên bản
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="text-end fw-semibold text-dark"><?= number_format($pCount, 0, ',', '.') ?></td>
                                    <td class="text-nowrap">
                                        <?php if ((int)($cat['is_active'] ?? 1) === 1): ?>
                                            <span class="badge bg-success">Đang hiển thị</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Đã ẩn</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            <a href="<?= ADMIN_URL ?>categories/edit/<?= $cid ?>" class="btn btn-outline-primary vf-action-btn" title="Chỉnh sửa danh mục &amp; thông số">
                                                <i class="fa-solid fa-sliders"></i><span>Cấu hình</span>
                                            </a>
                                            <form method="POST" action="<?= ADMIN_URL ?>categories/delete/<?= $cid ?>" class="d-inline"
                                                  onsubmit="return confirm('Bạn có chắc muốn xóa danh mục «<?= htmlspecialchars(addslashes((string)$cat['name'])) ?>»?');">
                                                <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                                                <button type="submit" class="btn btn-outline-danger vf-icon-btn"
                                                    <?= $pCount > 0 ? 'title="Danh mục đang có sản phẩm sẽ không thể xóa"' : 'title="Xóa danh mục"' ?>>
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
                <div class="text-center py-5 text-muted">
                    Chưa có danh mục sản phẩm nào.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
