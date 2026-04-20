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
        <p class="m-0 font-semibold text-slate-900">15.000.000 VNĐ</p>
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