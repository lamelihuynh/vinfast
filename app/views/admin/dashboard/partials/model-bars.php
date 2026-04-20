<div class="card h-100">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0">Đơn theo dòng xe</h5>
            <a class="small" href="<?= htmlspecialchars(ADMIN_URL . 'orders') ?>">Xem đơn hàng</a>
        </div>

        <div>
            <?php if (empty($ordersByModel)): ?>
                <p class="text-muted mb-0">Chưa có dữ liệu đơn hàng.</p>
            <?php else: ?>
                <?php foreach ($ordersByModel as $model): ?>
                    <?php
                    $orders = (int)($model['orders'] ?? 0);
                    $width = (int)round(($orders / max(1, $maxModelOrders)) * 100);
                    ?>
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-1 small">
                            <span><?= htmlspecialchars((string)($model['name'] ?? 'Unknown')) ?></span>
                            <strong><?= htmlspecialchars((string)$orders) ?></strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?= htmlspecialchars((string)$width) ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>