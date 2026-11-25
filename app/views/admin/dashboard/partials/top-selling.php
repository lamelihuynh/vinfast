<div class="card h-100">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0">Bán chạy 30 ngày</h5>
            <span class="text-muted small">Theo số lượng, không tính đơn hủy</span>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Sản phẩm</th>
                        <th class="text-right">SL</th>
                        <th class="text-right">Doanh thu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($topSelling)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Chưa có đơn trong 30 ngày.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($topSelling as $i => $item): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars((string)($item['name'] ?? '')) ?></td>
                                <td class="text-right"><?= (int)($item['qty'] ?? 0) ?></td>
                                <td class="text-right text-nowrap"><?= htmlspecialchars(number_format((float)($item['revenue'] ?? 0), 0, ',', '.')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
