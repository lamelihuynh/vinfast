<div class="card h-100">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0">Xu hướng 6 tháng</h5>
            <span class="text-muted small">Đơn hàng và doanh thu cọc</span>
        </div>

        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead>
                    <tr>
                        <th>Tháng</th>
                        <th>Đơn hàng</th>
                        <th>Doanh thu cọc (VND)</th>
                        <th>Tỷ trọng</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($trend as $month): ?>
                        <?php
                        $orders = (int)($month['orders'] ?? 0);
                        $revenue = (float)($month['revenue'] ?? 0);
                        $orderPct = (int)round(($orders / max(1, $trendMaxOrders)) * 100);
                        ?>
                        <tr>
                            <td><?= htmlspecialchars((string)($month['label'] ?? '')) ?></td>
                            <td><?= htmlspecialchars((string)$orders) ?></td>
                            <td><?= htmlspecialchars(number_format($revenue, 0, ',', '.')) ?></td>
                            <td style="min-width:160px;">
                                <div class="progress" style="height:8px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?= htmlspecialchars((string)$orderPct) ?>%"></div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>