<?php
$maxOrders = 1;
foreach ($modelStats as $item) {
    $maxOrders = max($maxOrders, (int)($item['orders'] ?? 0));
}
?>
<div class="card h-100">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0">Hiệu suất dòng xe</h5>
            <a class="small" href="<?= htmlspecialchars(ADMIN_URL . 'products') ?>">Xem sản phẩm</a>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th>Dòng xe</th>
                        <th>Phân khúc</th>
                        <th class="text-right">Giá</th>
                        <th class="text-right">Đơn</th>
                        <th>Tỷ lệ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($modelStats)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Chưa có dữ liệu sản phẩm.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($modelStats as $item): ?>
                            <?php $percent = (int)round(((int)($item['orders'] ?? 0) / max(1, $maxOrders)) * 100); ?>
                            <tr>
                                <td>
                                    <span class="badge mr-1" style="background: <?= htmlspecialchars((string)($item['color'] ?? '#1464F4')) ?>;">&nbsp;</span>
                                    <?= htmlspecialchars((string)($item['name'] ?? '')) ?>
                                </td>
                                <td><?= htmlspecialchars((string)($item['category'] ?? 'EV')) ?></td>
                                <td class="text-right"><?= htmlspecialchars(number_format((float)($item['price'] ?? 0), 0, ',', '.')) ?></td>
                                <td class="text-right"><?= htmlspecialchars((string)((int)($item['orders'] ?? 0))) ?></td>
                                <td>
                                    <div class="progress" style="height:8px;">
                                        <div class="progress-bar" role="progressbar" style="width: <?= htmlspecialchars((string)$percent) ?>%; background: <?= htmlspecialchars((string)($item['color'] ?? '#1464F4')) ?>;"></div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>