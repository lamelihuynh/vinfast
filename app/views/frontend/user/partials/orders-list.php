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
            $rawName = (string)($o['carName'] ?? $o['product_name'] ?? '—');
            $date = htmlspecialchars((string)($o['orderDate'] ?? $o['created_at'] ?? '—'));
            $rawCustomerName = (string)($o['customerName'] ?? '');
            $rawCustomerEmail = (string)($o['email'] ?? '');
            $customerPhone = htmlspecialchars((string)($o['phone'] ?? ''));
            $rawProvince = (string)($o['province'] ?? '');
            $rawShowroom = (string)($o['showroom'] ?? '');
            $amount = (float)($o['depositAmount'] ?? $o['deposit_amount'] ?? 0);

            // prepare truncated display values (multibyte-safe)
            if (function_exists('mb_strlen')) {
                $displayName = mb_strlen($rawName, 'UTF-8') > 60 ? mb_substr($rawName, 0, 60, 'UTF-8') . '...' : $rawName;
                $displayCustomerName = mb_strlen($rawCustomerName, 'UTF-8') > 32 ? mb_substr($rawCustomerName, 0, 32, 'UTF-8') . '...' : $rawCustomerName;
                $displayCustomerEmail = mb_strlen($rawCustomerEmail, 'UTF-8') > 36 ? mb_substr($rawCustomerEmail, 0, 36, 'UTF-8') . '...' : $rawCustomerEmail;
                $combinedPlace = trim($rawProvince . ($rawProvince !== '' && $rawShowroom !== '' ? ' · ' : '') . $rawShowroom);
                $displayPlace = mb_strlen($combinedPlace, 'UTF-8') > 50 ? mb_substr($combinedPlace, 0, 50, 'UTF-8') . '...' : $combinedPlace;
            } else {
                $displayName = strlen($rawName) > 60 ? substr($rawName, 0, 60) . '...' : $rawName;
                $displayCustomerName = strlen($rawCustomerName) > 32 ? substr($rawCustomerName, 0, 32) . '...' : $rawCustomerName;
                $displayCustomerEmail = strlen($rawCustomerEmail) > 36 ? substr($rawCustomerEmail, 0, 36) . '...' : $rawCustomerEmail;
                $combinedPlace = trim($rawProvince . ($rawProvince !== '' && $rawShowroom !== '' ? ' · ' : '') . $rawShowroom);
                $displayPlace = strlen($combinedPlace) > 50 ? substr($combinedPlace, 0, 50) . '...' : $combinedPlace;
            }

            $name = htmlspecialchars($displayName);
            $nameTitle = htmlspecialchars($rawName);
            $customerName = htmlspecialchars($displayCustomerName);
            $customerNameTitle = htmlspecialchars($rawCustomerName);
            $customerEmail = htmlspecialchars($displayCustomerEmail);
            $customerEmailTitle = htmlspecialchars($rawCustomerEmail);
            $placeEsc = htmlspecialchars($displayPlace);
            $placeTitle = htmlspecialchars($combinedPlace);
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
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-3">
                    <div class="text-sm text-slate-500">Mã đơn: <span class="font-mono font-semibold text-slate-700"><?= $oid ?></span></div>
                    <div class="text-xs text-slate-500">Ngày tạo: <?= $date ?></div>
                </div>

                <div class="mt-3 grid gap-4 md:grid-cols-2">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Khách hàng</div>
                        <div class="mt-1 text-sm font-semibold text-slate-900" <?= $rawCustomerName !== '' ? ' title="' . $customerNameTitle . '"' : '' ?>><?= $rawCustomerName !== '' ? $customerName : 'Chưa cập nhật' ?></div>
                        <?php if ($rawCustomerEmail !== ''): ?><div class="text-xs text-slate-500" title="<?= $customerEmailTitle ?>"><?= $customerEmail ?></div><?php endif; ?>
                        <?php if ($customerPhone !== ''): ?><div class="text-xs text-slate-500"><?= $customerPhone ?></div><?php endif; ?>
                        <?php if ($rawProvince !== '' || $rawShowroom !== ''): ?>
                            <?php if ($combinedPlace !== ''): ?>
                                <div class="mt-1 text-xs text-slate-500" title="<?= $placeTitle ?>"><?= $placeEsc ?></div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <div class="md:text-right">
                        <div class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Sản phẩm</div>
                        <div class="mt-1 text-sm font-semibold text-slate-900" title="<?= $nameTitle ?>" style="max-width:100%;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= $name ?></div>
                        <div class="mt-2 text-xs text-slate-500">Tiền cọc</div>
                        <div class="text-sm font-semibold text-slate-900"><?= $amount > 0 ? $fmtVnd($amount) : 'Chưa cập nhật' ?></div>
                        <div class="mt-2">
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold <?= $statusClass ?>"><?= htmlspecialchars($statusLabel) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>