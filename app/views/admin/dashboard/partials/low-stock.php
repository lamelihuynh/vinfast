<div class="card h-100">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0">Sắp hết hàng</h5>
            <span class="text-muted small">Còn bán được ≤ 5</span>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th>Sản phẩm / phiên bản</th>
                        <th class="text-right">Tồn</th>
                        <th class="text-right">Giữ</th>
                        <th class="text-right">Còn</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($lowStock)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Không có phiên bản nào sắp hết.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($lowStock as $item): ?>
                            <?php $available = (int)($item['available'] ?? 0); ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars((string)($item['product_name'] ?? '')) ?>
                                    <div class="small text-muted"><?= htmlspecialchars((string)($item['variant_name'] ?? '')) ?></div>
                                </td>
                                <td class="text-right"><?= (int)($item['stock'] ?? 0) ?></td>
                                <td class="text-right"><?= (int)($item['reserved'] ?? 0) ?></td>
                                <td class="text-right"><span class="badge <?= $available <= 0 ? 'badge-danger' : 'badge-warning' ?>"><?= $available ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
