<?php
$orders = is_array($orders ?? null) ? $orders : [];

$fmtVnd = static function (float $value): string {
    return number_format($value, 0, ',', '.') . ' VNĐ';
};

$paymentMap = [
    'unpaid' => ['label' => 'Chưa thanh toán', 'class' => 'bg-yellow-100 text-yellow-800'],
    'pending_verify' => ['label' => 'Chờ xác nhận', 'class' => 'bg-blue-100 text-blue-800'],
    'paid' => ['label' => 'Đã nhận cọc', 'class' => 'bg-green-100 text-green-800'],
    'failed' => ['label' => 'Thanh toán thất bại', 'class' => 'bg-red-100 text-red-800'],
    'refunded' => ['label' => 'Đã hoàn tiền', 'class' => 'bg-purple-100 text-purple-800'],
];

?>

<section class="min-h-screen bg-slate-50 py-6">
    <div class="mx-auto max-w-4xl px-4">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-900">Đơn hàng của bạn</h1>
            <p class="text-sm text-slate-500">Quản lý các yêu cầu đặt cọc và trạng thái đơn hàng.</p>
        </div>

        <?php if (empty($orders)): ?>
            <div class="rounded-lg border border-slate-100 bg-white p-6 text-center">
                <p class="text-slate-600 mb-4">Hiện tại bạn chưa có đơn hàng nào.</p>
                <div class="flex items-center justify-center gap-3">
                    <a href="<?= BASE_URL ?>products" class="inline-block rounded-md bg-[#1a2240] px-4 py-2 text-white">Xem sản phẩm</a>
                    <a href="<?= BASE_URL ?>" class="inline-block rounded-md border border-slate-200 px-4 py-2">Về trang chủ</a>
                </div>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($orders as $o): ?>
                    <?php
                    $oid = htmlspecialchars((string)($o['orderId'] ?? 'VF-0000'));
                    $name = htmlspecialchars((string)($o['carName'] ?? '—'));
                    $date = htmlspecialchars((string)($o['orderDate'] ?? '—'));
                    $customerName = htmlspecialchars((string)($o['customerName'] ?? ''));
                    $customerEmail = htmlspecialchars((string)($o['email'] ?? ''));
                    $customerPhone = htmlspecialchars((string)($o['phone'] ?? ''));
                    $province = htmlspecialchars((string)($o['province'] ?? ''));
                    $showroom = htmlspecialchars((string)($o['showroom'] ?? ''));
                    $paymentStatusRaw = (string)($o['paymentStatus'] ?? 'pending_verify');
                    $paymentUi = $paymentMap[$paymentStatusRaw] ?? $paymentMap['pending_verify'];
                    $amount = (float)($o['depositAmount'] ?? 0);
                    ?>
                    <div class="rounded-lg border border-slate-100 bg-white px-4 py-3">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="text-sm text-slate-500">Mã đơn: <span class="font-mono text-slate-700"><?= $oid ?></span></div>
                                <div class="text-sm font-semibold text-slate-900"><?= $name ?></div>
                                <div class="mt-1 space-y-0.5 text-xs text-slate-500">
                                    <?php if ($customerName !== ''): ?><div><?= $customerName ?></div><?php endif; ?>
                                    <?php if ($customerPhone !== ''): ?><div><?= $customerPhone ?></div><?php endif; ?>
                                    <?php if ($customerEmail !== ''): ?><div><?= $customerEmail ?></div><?php endif; ?>
                                    <?php if ($province !== ''): ?><div><?= $province ?><?php if ($showroom !== ''): ?> · <?= $showroom ?><?php endif; ?></div><?php endif; ?>
                                </div>
                                <div class="text-xs text-slate-500 mt-1"><?= $date ?></div>
                            </div>

                            <div class="text-right">
                                <div class="text-xs text-slate-500">Tiền cọc</div>
                                <div class="text-sm font-semibold text-slate-900"><?= $amount > 0 ? $fmtVnd($amount) : 'Chưa cập nhật' ?></div>
                                <span class="mt-2 inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold <?= htmlspecialchars($paymentUi['class']) ?>">
                                    <?= htmlspecialchars($paymentUi['label']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center justify-end">
                            <a href="<?= BASE_URL ?>order/confirmation" class="text-xs text-[#1a6fe0] inline-block">Xem chi tiết</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>