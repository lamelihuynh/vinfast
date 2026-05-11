<div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-uppercase">
                <tr>
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
                                    <img src="<?= htmlspecialchars($thumbUrl) ?>" alt="<?= htmlspecialchars((string)$p['name']) ?>" class="rounded" style="width:35px;height:35px;object-fit:cover;">
                                    <div class="text-start">
                                        <?php
                                        $rawName = (string)($p['name'] ?? '');
                                        if (function_exists('mb_strlen')) {
                                            $displayName = mb_strlen($rawName, 'UTF-8') > 30 ? mb_substr($rawName, 0, 30, 'UTF-8') . '...' : $rawName;
                                        } else {
                                            $displayName = strlen($rawName) > 30 ? substr($rawName, 0, 30) . '...' : $rawName;
                                        }
                                        ?>
                                        <small class="d-block fw-semibold"><?= htmlspecialchars($displayName) ?></small>
                                        <small class="text-muted"><?= htmlspecialchars((string)($p['slug'] ?? 'no-slug')) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge text-black badge-primary"><?= htmlspecialchars((string)($p['category_name'] ?? 'Chưa phân loại')) ?></span>
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
                                        <button type="button" class="btn btn-link text-danger p-0 btn-delete-product" data-id="<?= (int)$p['id'] ?>" data-name="<?= htmlspecialchars((string)$p['name']) ?>" title="Xóa">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
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