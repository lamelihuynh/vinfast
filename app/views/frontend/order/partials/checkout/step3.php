<?php
$currentStep = isset($currentStep) ? (int)$currentStep : 3;
$productName = isset($productName) ? (string)$productName : '';
$colorCode = isset($colorCode) ? (string)$colorCode : '';
$colorName = isset($colorName) ? (string)$colorName : '';
$selectedInteriorName = isset($selectedInteriorName) ? (string)$selectedInteriorName : '';
$selectedVariant = isset($selectedVariant) && is_array($selectedVariant) ? $selectedVariant : [];
$depositAmount = isset($depositAmount) ? (int)$depositAmount : 15000000;
$selectedColorSurcharge = isset($selectedColorSurcharge) ? (int)$selectedColorSurcharge : 0;
$formData = isset($formData) && is_array($formData) ? $formData : [];
// Server-side validation errors (from controller) - read and clear session
$errors = is_array($_SESSION['errors'] ?? null) ? $_SESSION['errors'] : [];
if (isset($_SESSION['errors'])) {
    unset($_SESSION['errors']);
}
?>
<section data-step-panel="3" class="space-y-4<?= $currentStep === 3 ? '' : ' hidden' ?>">
    <div>
        <h2 class="mb-1 text-[15px] font-bold text-slate-900">Bước 3 - Xác nhận và thanh toán</h2>
        <p class="m-0 text-[12px] text-slate-500">Kiểm tra thông tin trước khi gửi yêu cầu đặt cọc.</p>
    </div>

    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">

        <p class="mb-2 text-[11px] font-semibold uppercase tracking-[0.8px] text-slate-500">
            Thông tin đơn hàng
        </p>

        <div class="space-y-2 text-[13px]">
            <div class="flex items-start justify-between gap-3">
                <span class="text-slate-500 min-w-[120px]">Sản phẩm</span>
                <span class="min-w-0 flex-1 break-words text-right font-semibold text-slate-800"><?= htmlspecialchars($productName) ?></span>
            </div>

            <div class="flex items-start justify-between gap-3">
                <span class="text-slate-500 min-w-[120px]">Phiên bản</span>
                <span class="min-w-0 flex-1 break-words text-right font-semibold text-slate-800" data-summary-variant>
                    <?= htmlspecialchars((string)($selectedVariant['name'] ?? $productName)) ?>
                </span>
            </div>

            <div class="flex items-start justify-between gap-3">
                <span class="text-slate-500 min-w-[120px]">Màu ngoại thất</span>
                <span class="min-w-0 flex-1 break-words text-right font-semibold text-slate-800" data-summary-color>
                    <?= htmlspecialchars($colorName !== '' ? $colorName : $colorCode) ?>
                </span>
            </div>

            <div class="flex items-start justify-between gap-3">
                <span class="text-slate-500 min-w-[120px]">Giá xe</span>
                <span class="min-w-0 flex-1 break-words text-right font-semibold text-slate-800" data-summary-price>
                    <?= htmlspecialchars(number_format((float)($selectedVariant['price'] ?? 0), 0, ',', '.')) ?> VNĐ
                </span>
            </div>

            <div class="flex items-start justify-between gap-3">
                <span class="text-slate-500 min-w-[120px]">Đặt cọc</span>
                <span class="min-w-0 flex-1 break-words text-right font-semibold text-slate-800" data-summary-deposit>
                    <?= htmlspecialchars(number_format((float)($depositAmount ?? 15000000), 0, ',', '.')) ?> VNĐ
                </span>
            </div>

            <div class="hidden flex items-start justify-between gap-3" data-summary-color-surcharge-wrap>
                <span class="text-slate-500 min-w-[120px]">Phụ thu màu</span>
                <span class="min-w-0 flex-1 break-words text-right font-semibold text-slate-800" data-summary-color-surcharge>
                    <?= htmlspecialchars(number_format((float)($selectedColorSurcharge ?? 0), 0, ',', '.')) ?> VNĐ
                </span>
            </div>
        </div>

        <p class="mt-4 mb-2 text-[11px] font-semibold uppercase tracking-[0.8px] text-slate-500">
            Thông tin chủ xe
        </p>

        <div class="space-y-2 text-[13px]">
            <div class="flex items-start justify-between gap-3">
                <span class="text-slate-500 min-w-[120px]">Họ tên</span>
                <span class="min-w-0 flex-1 break-words text-right font-semibold text-slate-800" data-summary-name>
                    <?= htmlspecialchars($formData['full_name'] ?? '') ?>
                </span>
            </div>

            <div class="flex items-start justify-between gap-3">
                <span class="text-slate-500 min-w-[120px]">SĐT</span>
                <span class="min-w-0 flex-1 break-words text-right font-semibold text-slate-800" data-summary-phone>
                    <?= htmlspecialchars($formData['phone'] ?? '') ?>
                </span>
            </div>

            <div class="flex items-start justify-between gap-3">
                <span class="text-slate-500 min-w-[120px]">CCCD</span>
                <span class="min-w-0 flex-1 break-words text-right font-semibold text-slate-800" data-summary-cccd>
                    <?= htmlspecialchars($formData['cccd'] ?? '') ?>
                </span>
            </div>

            <div class="flex items-start justify-between gap-3">
                <span class="text-slate-500 min-w-[120px]">Email</span>
                <span class="min-w-0 flex-1 break-words text-right font-semibold text-slate-800" data-summary-email>
                    <?= htmlspecialchars($formData['email'] ?? '') ?>
                </span>
            </div>

            <div class="flex items-start justify-between gap-3">
                <span class="text-slate-500 min-w-[120px]">Tỉnh thành</span>
                <span class="min-w-0 flex-1 break-words text-right font-semibold text-slate-800" data-summary-province>
                    <?= htmlspecialchars($formData['province'] ?? '') ?>
                </span>
            </div>

            <div class="flex items-start justify-between gap-3">
                <span class="text-slate-500 min-w-[120px]">Showroom</span>
                <span class="min-w-0 flex-1 break-words text-right font-semibold text-slate-800" data-summary-showroom>
                    <?= htmlspecialchars($formData['showroom'] ?? '') ?>
                </span>
            </div>
        </div>

    </div>

    <p class="text-[11px] text-slate-500" data-deposit-hint>Tiền đặt cọc có thể không được hoàn lại.</p>

    <div>
        <p class="mb-2 text-[12px] font-semibold text-slate-800">Hình thức thanh toán</p>
        <div class="space-y-2">
            <?php foreach (['card-intl' => 'Thẻ thanh toán quốc tế', 'card-domestic' => 'Thẻ ATM nội địa / Internet Banking', 'transfer' => 'Chuyển khoản ngân hàng'] as $value => $label): ?>
                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-[12px] text-slate-700 transition hover:border-slate-300">
                    <input type="radio" name="pay_method" value="<?= htmlspecialchars($value) ?>" class="h-4 w-4 border-slate-300 text-blue-600" <?= $formData['pay_method'] === $value ? 'checked' : '' ?>>
                    <span><?= htmlspecialchars($label) ?></span>
                </label>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="rounded-lg border border-slate-200 p-3">
        <label class="flex cursor-pointer items-start gap-2 text-[12px] leading-5 text-slate-700">
            <input type="checkbox" name="agree_terms" value="1" class="mt-0.5 h-4 w-4 border-slate-300 text-blue-600" <?= (string)$formData['agree_terms'] === '1' ? 'checked' : '' ?>>
            <span>Tôi xác nhận thông tin cung cấp là chính xác và đồng ý với điều khoản đặt cọc của VinFast.</span>
        </label>
        <?php if (!empty($errors['agree_terms'])): ?>
            <p data-error="agree_terms" class="mt-2 text-[12px] text-red-600"><?= htmlspecialchars((string)$errors['agree_terms']) ?></p>
        <?php else: ?>
            <p data-error="agree_terms" class="mt-2 hidden text-[12px] text-red-600"></p>
        <?php endif; ?>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
        <button type="button" data-step-prev="2" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-[13px] font-semibold text-slate-700 transition hover:bg-slate-100">Quay lại</button>
        <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-vfNavy bg-vfNavy px-4 py-2 text-[13px] font-semibold text-white transition hover:opacity-90">Thanh toán đặt cọc</button>
    </div>
</section>