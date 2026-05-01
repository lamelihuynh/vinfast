<?php
$priceText = isset($priceText) ? (string)$priceText : '0';
$productName = isset($productName) ? (string)$productName : 'VinFast';
$colorCode = isset($colorCode) ? (string)$colorCode : '';
$colorName = isset($colorName) ? (string)$colorName : '';
$selectedColorSurcharge = isset($selectedColorSurcharge) ? (int)$selectedColorSurcharge : 0;
$depositAmount = isset($depositAmount) ? (int)$depositAmount : 15000000;
$depositNonRefundable = !empty($depositNonRefundable);
?>
<div class="mb-5 grid gap-4 rounded-xl border border-slate-100 bg-slate-50 p-4 text-[14px] text-slate-700 sm:grid-cols-2">
    <div>
        <p class="m-0 text-[11px] uppercase tracking-[0.8px] text-slate-500">Sản phẩm</p>
        <p class="m-0 font-semibold text-slate-900"><?= htmlspecialchars($productName) ?></p>
    </div>
    <div>
        <p class="m-0 text-[11px] uppercase tracking-[0.8px] text-slate-500">Giá tham khảo</p>
        <p class="m-0 font-semibold text-slate-900"><?= htmlspecialchars($priceText) ?> VNĐ</p>
    </div>
    <div>
        <p class="m-0 text-[11px] uppercase tracking-[0.8px] text-slate-500">Số tiền đặt cọc</p>
        <p class="m-0 font-semibold text-slate-900" data-summary-deposit><?= htmlspecialchars(number_format((float)($depositAmount ?? 15000000), 0, ',', '.')) ?> VNĐ</p>
        <p class="m-0 text-[11px] text-slate-500">Tiền đặt cọc có thể không được hoàn lại.</p>
    </div>
    <div data-summary-color-surcharge-wrap class="hidden">
        <p class="m-0 text-[11px] uppercase tracking-[0.8px] text-slate-500">Phụ thu màu</p>
        <p class="m-0 font-semibold text-slate-900" data-summary-color-surcharge><?= htmlspecialchars(number_format((float)($selectedColorSurcharge ?? 0), 0, ',', '.')) ?> VNĐ</p>
    </div>
    <div>
        <p class="m-0 text-[11px] uppercase tracking-[0.8px] text-slate-500">Màu đã chọn</p>
        <?php if ($colorCode !== '' || $colorName !== ''): ?>
            <p class="m-0 font-semibold text-slate-900">
                <?= htmlspecialchars($colorName !== '' ? $colorName : $colorCode) ?>
                <?php if ($colorCode !== '' && strcasecmp($colorName, $colorCode) !== 0): ?>
                    <span class="text-slate-500">(<?= htmlspecialchars($colorCode) ?>)</span>
                <?php endif; ?>
            </p>
        <?php else: ?>
            <p class="m-0 text-slate-500">Chưa chọn màu. Bạn có thể chọn tại bước 1 hoặc quay lại trang chi tiết.</p>
        <?php endif; ?>
    </div>
</div>