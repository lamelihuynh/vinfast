<?php
$orders = is_array($orders ?? null) ? $orders : [];

$fmtVnd = static function (float $value): string {
    return number_format($value, 0, ',', '.') . ' VNĐ';
};

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
                    <?php $oid = htmlspecialchars((string)($o['orderId'] ?? 'VF-0000')); ?>
                    <?php $name = htmlspecialchars((string)($o['carName'] ?? '—')); ?>
                    <?php $date = htmlspecialchars((string)($o['orderDate'] ?? '—')); ?>
                    <?php $amount = $fmtVnd((float)($o['depositAmount'] ?? 0)); ?>
                    <div class="flex items-center justify-between rounded-lg border border-slate-100 bg-white px-4 py-3">
                        <div>
                            <div class="text-sm text-slate-500">Mã đơn: <span class="font-mono text-slate-700"><?= $oid ?></span></div>
                            <div class="text-sm font-semibold text-slate-900"><?= $name ?></div>
                            <div class="text-xs text-slate-500"><?= $date ?></div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-semibold text-slate-900"><?= $amount ?></div>
                            <a href="<?= BASE_URL ?>order/confirmation" class="text-xs text-[#1a6fe0] mt-1 inline-block">Xem chi tiết</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>