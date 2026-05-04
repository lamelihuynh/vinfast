<?php
$ordersList = is_iterable($orders ?? []) ? $orders : [];
$fmtVnd = static function (float $v) {
    return number_format($v, 0, ',', '.') . ' VNĐ';
};
?>
<div class="space-y-3">
    <?php if (empty($ordersList)): ?>
        <div class="rounded-lg border border-slate-100 bg-white p-6 text-center">
            <p class="text-slate-600">Bạn chưa có đơn hàng nào.</p>
        </div>
    <?php else: ?>
        <?php foreach ($ordersList as $o):
            $oid = htmlspecialchars((string)($o['orderId'] ?? ('VF-ORD-' . str_pad((string)($o['id'] ?? 0), 6, '0', STR_PAD_LEFT))));
            $name = htmlspecialchars((string)($o['carName'] ?? $o['product_name'] ?? '—'));
            $date = htmlspecialchars((string)($o['orderDate'] ?? $o['created_at'] ?? '—'));
            $amount = (float)($o['depositAmount'] ?? $o['deposit_amount'] ?? 0);
            $paymentStatus = htmlspecialchars((string)($o['paymentStatus'] ?? $o['payment_status'] ?? 'pending_verify'));
            $statusClass = 'bg-slate-100 text-slate-700';
            $statusLabel = $paymentStatus;
            // map small set
            if ($paymentStatus === 'paid') {
                $statusClass = 'bg-green-100 text-green-800';
                $statusLabel = 'Đã nhận cọc';
            } elseif ($paymentStatus === 'pending_verify') {
                $statusClass = 'bg-blue-100 text-blue-800';
                $statusLabel = 'Chờ xác nhận';
            } elseif ($paymentStatus === 'unpaid') {
                $statusClass = 'bg-yellow-100 text-yellow-800';
                $statusLabel = 'Chưa thanh toán';
            }
        ?>
            <div class="rounded-lg border border-slate-100 bg-white p-4 flex items-center justify-between">
                <div>
                    <div class="text-sm text-slate-500">Mã: <span class="font-mono text-slate-700"><?= $oid ?></span></div>
                    <div class="text-base font-semibold text-slate-900"><?= $name ?></div>
                    <div class="text-xs text-slate-500"><?= $date ?></div>
                </div>
                <div class="text-right">
                    <div class="text-sm font-semibold text-slate-900"><?= $amount > 0 ? $fmtVnd($amount) : '--' ?></div>
                    <div class="mt-2">
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold <?= $statusClass ?>"><?= htmlspecialchars($statusLabel) ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>