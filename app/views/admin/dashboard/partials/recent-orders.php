<?php
$statusLabel = static function (string $status): string {
    $map = [
        'pending' => 'Chờ xử lý',
        'confirmed' => 'Đã xác nhận',
        'done' => 'Hoàn tất',
        'cancelled' => 'Đã hủy',
    ];
    return $map[$status] ?? ucfirst($status);
};
?>
<div class="card h-100">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0">Đơn hàng gần đây</h5>
            <a class="small" href="<?= htmlspecialchars(ADMIN_URL . 'orders') ?>">Xem tất cả</a>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Sản phẩm</th>
                        <th class="text-right">Cọc</th>
                        <th class="text-center">Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentOrders)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Chưa có đơn hàng.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentOrders as $order): ?>
                            <?php $status = (string)($order['status'] ?? 'pending'); ?>
                            <tr>
                                <td><?= htmlspecialchars((string)($order['code'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string)($order['customer'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string)($order['product'] ?? '')) ?></td>
                                <td class="text-right"><?= htmlspecialchars(number_format((float)($order['deposit'] ?? 0), 0, ',', '.')) ?></td>
                                <td class="text-center"><span class="badge badge-pill badge-<?= htmlspecialchars($status) ?>"><?= htmlspecialchars($statusLabel($status)) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>