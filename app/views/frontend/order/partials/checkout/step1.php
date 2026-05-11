<?php
$currentStep = isset($currentStep) ? (int)$currentStep : 1;
$productName = isset($productName) ? (string)$productName : 'VinFast';
$productId = isset($productId) ? (int)$productId : 0;
$selectedInteriorCode = isset($selectedInteriorCode) ? (string)$selectedInteriorCode : '';
$selectedInteriorName = isset($selectedInteriorName) ? (string)$selectedInteriorName : '';
$colorCode = isset($colorCode) ? (string)$colorCode : '';
$colorName = isset($colorName) ? (string)$colorName : '';
$selectedColorSurcharge = isset($selectedColorSurcharge) ? (int)$selectedColorSurcharge : 0;
$variantChoices = isset($variantChoices) && is_array($variantChoices) ? $variantChoices : [];
$variantSwitchChoices = isset($variantSwitchChoices) && is_array($variantSwitchChoices) ? $variantSwitchChoices : [];
$selectedVariant = isset($selectedVariant) && is_array($selectedVariant) ? $selectedVariant : [];
$selectedVariantName = isset($selectedVariantName) ? (string)$selectedVariantName : '';

$initialBasePrice = 0;
if (!empty($selectedVariant) && isset($selectedVariant['price'])) {
    $initialBasePrice = (float)$selectedVariant['price'];
} elseif (!empty($variantSwitchChoices)) {
    foreach ($variantSwitchChoices as $variantItem) {
        if (!empty($variantItem['isCurrent'])) {
            if (isset($variantItem['price'])) {
                $initialBasePrice = (float)$variantItem['price'];
            } else {
                $initialBasePrice = (float)preg_replace('/[^0-9]/', '', $variantItem['priceText'] ?? '0');
            }
            break;
        }
    }
} elseif (!empty($variantChoices)) {
    foreach ($variantChoices as $variantItem) {
        $vName = (string)($variantItem['name'] ?? '');
        if (strcasecmp($vName, (string)($selectedVariant['name'] ?? '')) === 0) {
            $initialBasePrice = (float)($variantItem['price'] ?? 0);
            break;
        }
    }
    if ($initialBasePrice === 0 && count($variantChoices) > 0) {
        $initialBasePrice = (float)($variantChoices[0]['price'] ?? 0);
    }
}

if ($initialBasePrice === 0 && !empty($variantSwitchChoices)) {
    if (isset($variantSwitchChoices[0]['price'])) {
        $initialBasePrice = (float)$variantSwitchChoices[0]['price'];
    } else {
        $initialBasePrice = (float)preg_replace('/[^0-9]/', '', $variantSwitchChoices[0]['priceText'] ?? '0');
    }
}

if ($initialBasePrice === 0 && isset($product) && is_array($product) && isset($product['price'])) {
    $initialBasePrice = (float)$product['price'];
}

$initialBasePriceText = number_format($initialBasePrice, 0, ',', '.') . ' VNĐ';
?>
<section data-step-panel="1" class="space-y-4<?= $currentStep === 1 ? '' : ' hidden' ?>">
    <div>
        <h2 class="mb-1 text-[15px] font-bold text-slate-900">Bước 1 - Lựa chọn cấu hình</h2>
        <p class="m-0 text-[12px] leading-relaxed text-slate-500">Xin mời Quý khách chọn phiên bản, ngoại thất xe trước khi qua bước nhập thông tin.</p>
    </div>

    <input type="hidden" name="variant_name" value="<?= htmlspecialchars((string)($selectedVariant['name'] ?? $productName)) ?>" data-variant-input>
    <input type="hidden" name="interior_code" value="<?= htmlspecialchars((string)$selectedInteriorCode) ?>" data-interior-input>

    <?php if (!empty($variantSwitchChoices) && count($variantSwitchChoices) > 1): ?>
        <div class="rounded-xl border border-slate-100 p-4">
            <p class="mb-2 text-[12px] font-semibold text-slate-800">Phiên bản</p>
            <div class="space-y-2">
                <?php foreach ($variantSwitchChoices as $variantItem): ?>
                    <?php
                    $variantProductId = (int)($variantItem['productId'] ?? 0);
                    $isVariantActive = !empty($variantItem['isCurrent']);
                    ?>
                    <button
                        type="submit"
                        name="switch_product_id"
                        value="<?= $variantProductId ?>"
                        formnovalidate
                        data-switch-product
                        data-switch-product-quick
                        class="flex w-full items-center justify-between rounded-lg border px-3 py-2 text-left transition <?= $isVariantActive ? 'border-vfNavy bg-blue-50' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50' ?>">
                        <?php $vsName = (string)($variantItem['name'] ?? 'VinFast'); ?>
                        <span class="text-[12px] font-semibold text-slate-800 block truncate max-w-[65%]" title="<?= htmlspecialchars($vsName) ?>"><?= htmlspecialchars($vsName) ?></span>
                        <span class="text-[12px] font-semibold text-vfNavy"><?= htmlspecialchars((string)($variantItem['priceText'] ?? '0 VNĐ')) ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    <?php elseif (!empty($variantChoices)): ?>
        <div class="rounded-xl border border-slate-100 p-4">
            <p class="mb-2 text-[12px] font-semibold text-slate-800">Phiên bản</p>
            <div class="space-y-2">
                <?php foreach ($variantChoices as $variantItem): ?>
                    <?php
                    $vName = (string)($variantItem['name'] ?? 'VinFast');
                    $vPrice = (float)($variantItem['price'] ?? 0);
                    $isVariantActive = strcasecmp($vName, (string)($selectedVariant['name'] ?? '')) === 0;
                    ?>
                    <button
                        type="button"
                        data-variant-btn
                        data-variant-name="<?= htmlspecialchars($vName) ?>"
                        data-variant-price="<?= htmlspecialchars((string)$vPrice) ?>"
                        class="flex w-full items-center justify-between rounded-lg border px-3 py-2 text-left transition <?= $isVariantActive ? 'border-vfNavy bg-blue-50' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50' ?>">
                        <span class="text-[12px] font-semibold text-slate-800 block truncate max-w-[65%]" title="<?= htmlspecialchars($vName) ?>"><?= htmlspecialchars($vName) ?></span>
                        <span class="text-[12px] font-semibold text-vfNavy"><?= htmlspecialchars(number_format($vPrice, 0, ',', '.')) ?> VNĐ</span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="rounded-xl border border-slate-100 p-4">
        <div class="mb-2 flex items-center justify-between gap-2">
            <p class="m-0 text-[12px] font-semibold text-slate-800">Ngoại thất</p>
            <p class="m-0 text-[11px] text-slate-500" data-selected-exterior><?= htmlspecialchars($colorName !== '' ? $colorName : $colorCode) ?></p>
        </div>

        <?php if (!empty($colorChoices)): ?>
            <div class="flex flex-wrap gap-2" role="radiogroup" aria-label="Màu ngoại thất">
                <?php foreach ($colorChoices as $choice): ?>
                    <?php
                    $isChecked = strtoupper((string)$choice['code']) === strtoupper($colorCode);
                    $dotStyle = '';
                    if (!empty($choice['hex']) && preg_match('/^#?[A-F0-9]{3,6}$/i', (string)$choice['hex'])) {
                        $dotStyle = 'style="background:' . htmlspecialchars('#' . ltrim((string)$choice['hex'], '#')) . '"';
                    }
                    ?>
                    <label
                        data-color-choice
                        class="vf-pd-color-btn inline-grid h-9 w-9 place-items-center rounded-full border-2 p-0 text-slate-700 transition hover:border-blue-300 <?= $isChecked ? 'is-active border-vfNavy shadow-[0_0_0_2px_rgba(20,100,244,0.22)]' : 'border-slate-200' ?>">
                        <input type="radio" name="color_code" value="<?= htmlspecialchars((string)$choice['code']) ?>" class="sr-only" data-color-radio data-color-name="<?= htmlspecialchars((string)$choice['name']) ?>" data-color-image="<?= htmlspecialchars((string)($choice['imageUrl'] ?? '')) ?>" data-color-surcharge="<?= (int)($choice['surcharge'] ?? 0) ?>" <?= $isChecked ? 'checked' : '' ?>>
                        <span class="vf-pd-color-dot inline-block h-6 w-6 rounded-full border border-slate-300" aria-hidden="true" <?= $dotStyle ?>></span>
                    </label>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="m-0 text-[12px] text-slate-500">Mẫu xe hiện chưa có danh sách màu cấu hình trong dữ liệu sản phẩm.</p>
        <?php endif; ?>
    </div>


    <div class="rounded-xl border border-slate-100 bg-white p-4">
        <button
            type="button"
            data-price-breakdown-toggle
            aria-expanded="false"
            class="flex w-full items-center justify-between gap-2 text-left">
            <div>
                <div class="text-[12px] font-semibold text-slate-700">Giá xe kèm pin</div>
                <span class="text-[14px] font-bold text-slate-900" data-price-total-estimate>0 VNĐ</span>
            </div>
            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition" data-price-breakdown-icon>
                <i class="fa-solid fa-chevron-down text-[10px]"></i>
            </span>
        </button>

        <div class="mt-3 hidden border-t border-slate-100 pt-3" data-price-breakdown-panel>
            <div class="space-y-2 text-[12px]">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-slate-600">Giá phiên bản</span>
                    <span class="font-semibold text-slate-800" data-selected-variant-price><?= htmlspecialchars($initialBasePriceText) ?></span>
                </div>
                <div class="flex items-center justify-between gap-2 hidden" data-selected-color-surcharge-wrap>
                    <span class="text-slate-600">Phụ thu màu</span>
                    <span class="font-semibold text-slate-800" data-summary-color-surcharge><?= htmlspecialchars(number_format((float)($selectedColorSurcharge ?? 0), 0, ',', '.')) ?> VNĐ</span>
                </div>
                <div class="mt-2 flex items-center justify-between gap-2 border-t border-slate-100 pt-2">
                    <span class="font-semibold text-slate-700">Tổng dự kiến</span>
                    <span class="text-[14px] font-bold text-slate-900" data-price-total-estimate>0 VNĐ</span>
                </div>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-between gap-3 border-t border-slate-100 pt-4">
        <a href="<?= BASE_URL ?>products/detail/<?= (int)$productId ?>" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-[13px] font-semibold hover:text-vfNavy no-underline transition hover:bg-slate-100">Quay lại</a>
        <button type="button" data-step-next="2" class="ml-auto inline-flex items-center justify-center rounded-lg border border-vfNavy bg-vfNavy px-4 py-2 text-[13px] font-semibold text-white transition hover:opacity-90">Tiếp tục nhập thông tin</button>
    </div>
</section>