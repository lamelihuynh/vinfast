<?php
$cardItems = [
    [
        'label' => 'Đơn hàng',
        'value' => (int)($summary['orders_total'] ?? 0),
        'sub' => (int)($summary['orders_pending'] ?? 0) . ' chờ xử lý',
        'icon' => 'ti-receipt',
        'class' => 'bg-primary',
    ],
    [
        'label' => 'Doanh thu cọc',
        'value' => number_format((float)($summary['deposit_revenue'] ?? 0), 0, ',', '.') . ' VND',
        'sub' => 'Tổng tiền cọc của đơn chưa hủy',
        'icon' => 'ti-wallet',
        'class' => 'bg-warning',
    ],
    [
        'label' => 'Liên hệ mới',
        'value' => (int)($summary['contacts_unread'] ?? 0),
        'sub' => (int)($summary['contacts_total'] ?? 0) . ' tổng liên hệ',
        'icon' => 'ti-email',
        'class' => 'bg-danger',
    ],
    [
        'label' => 'Bình luận cho duyệt',
        'value' => (int)($summary['comments_pending'] ?? 0),
        'sub' => (int)($summary['comments_total'] ?? 0) . ' tổng bình luận',
        'icon' => 'ti-comment-alt',
        'class' => 'bg-success',
    ],
];
?>

<div class="row">
    <?php foreach ($cardItems as $item): ?>
        <div class="col-xl-3 col-md-6 col-12 mb-4">
            <div class="card border-0 text-white <?= htmlspecialchars((string)$item['class']) ?>">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="small opacity-75"><?= htmlspecialchars((string)$item['label']) ?></div>
                        <i class="<?= htmlspecialchars($item['icon']) ?>"></i>
                    </div>
                    <div class="h5 mb-1 text-white"><?= htmlspecialchars((string)$item['value']) ?></div>
                    <div class="small opacity-75 text-white"><?= htmlspecialchars((string)$item['sub']) ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>