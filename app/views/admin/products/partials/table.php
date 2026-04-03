<div class="card-body">
    <div class="pb-3 mb-3">
        <h5 class="header-title mb-2"><i class="fa-solid fa-chart-line me-2"></i>Bảng Quản Lý Sản Phẩm</h5>
        <p class="text-muted mb-0">Theo dõi sản phẩm theo danh mục, giá bán và trạng thái hiển thị</p>
    </div>
    <div class="single-table">
        <div class="table-responsive">
            <table class="table table-hover progress-table text-center align-middle">
                <thead class="text-uppercase bg-light">
                    <tr style="font-weight:600">
                        <th scope="col">Sản phẩm</th>
                        <th scope="col">Danh mục</th>
                        <th scope="col">Giá</th>
                        <th scope="col">Trạng thái</th>
                        <th scope="col">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fa-solid fa-inbox" style="font-size:3em;opacity:0.3"></i>
                                    <p class="mt-3">Chưa có sản phẩm nào.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                            <?php
                            $basePrice = (float)($p['price'] ?? 0);
                            $minPrice = $basePrice;
                            $maxPrice = $basePrice;

                            $isActive = (int)($p['is_active'] ?? 0) === 1;
                            $statusClass = $isActive ? 'bg-success' : 'bg-secondary';
                            $statusText = $isActive ? 'Hiển thị' : 'Đã ẩn';
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2 justify-content-center">
                                        <?php $thumbUrl = ProductViewHelper::thumbUrl($p); ?>
                                        <img src="<?= htmlspecialchars($thumbUrl) ?>" alt="<?= htmlspecialchars((string)$p['name']) ?>" style="width:35px;height:35px;object-fit:cover;border-radius:4px;">
                                        <div class="text-start">
                                            <small class="d-block fw-semibold"><?= htmlspecialchars(substr((string)$p['name'], 0, 20)) ?></small>
                                            <small class="text-muted"><?= htmlspecialchars((string)($p['slug'] ?? 'no-slug')) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary text-white"><?= htmlspecialchars((string)($p['category_name'] ?? 'Chưa phân loại')) ?></span>
                                </td>
                                <td>
                                    <?php if ($maxPrice > $minPrice): ?>
                                        <small class="fw-semibold"><?= number_format($minPrice, 0, ',', '.') ?> - <?= number_format($maxPrice, 0, ',', '.') ?> VND</small>
                                    <?php else: ?>
                                        <small class="fw-semibold"><?= number_format($minPrice, 0, ',', '.') ?> VND</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" action="<?= ADMIN_URL ?>products/toggle/<?= (int)$p['id'] ?>" class="d-inline">
                                        <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                                        <button type="submit" class="btn btn-link p-0 text-decoration-none">
                                            <span class="status-p <?= $statusClass ?> text-white" style="padding:6px 12px;border-radius:4px;display:inline-block">
                                                <?php if ($isActive): ?>
                                                    <i class="fa-solid fa-check me-1"></i><?= $statusText ?>
                                                <?php else: ?>
                                                    <i class="fa-solid fa-ban me-1"></i><?= $statusText ?>
                                                <?php endif; ?>
                                            </span>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <ul class="d-flex justify-content-center gap-2 list-unstyled">
                                        <li>
                                            <button type="button" class="btn btn-link text-secondary p-0 btn-edit-product" data-id="<?= (int)$p['id'] ?>" title="Chỉnh sửa">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                        </li>
                                        <li>
                                            <form method="POST" action="<?= ADMIN_URL ?>products/delete/<?= (int)$p['id'] ?>" class="d-inline">
                                                <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                                                <button type="submit" class="btn btn-link text-danger p-0" data-confirm="Bạn có chắc muốn xóa sản phẩm này?" title="Xóa">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>