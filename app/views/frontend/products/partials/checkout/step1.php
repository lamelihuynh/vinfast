<section data-step-panel="1" class="space-y-4<?= $currentStep === 1 ? '' : ' hidden' ?>">
    <div>
        <h2 class="mb-1 text-[15px] font-bold text-slate-900">Bước 1 - Lựa chọn cấu hình</h2>
        <p class="m-0 text-[12px] leading-relaxed text-slate-500">Xin mời Quý khách chọn phiên bản, ngoại thất và nội thất xe trước khi qua bước nhập thông tin.</p>
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
                        data-switch-product
                        data-switch-product-quick
                        class="flex w-full items-center justify-between rounded-lg border px-3 py-2 text-left transition <?= $isVariantActive ? 'border-vfNavy bg-blue-50' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50' ?>">
                        <span class="text-[12px] font-semibold text-slate-800"><?= htmlspecialchars((string)($variantItem['name'] ?? 'VinFast')) ?></span>
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
                        <span class="text-[12px] font-semibold text-slate-800"><?= htmlspecialchars($vName) ?></span>
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
                    <label class="inline-flex cursor-pointer items-center justify-center rounded-full border-2 p-0.5 transition <?= $isChecked ? 'border-vfNavy shadow-[0_0_0_2px_rgba(20,100,244,0.22)]' : 'border-slate-200 hover:border-slate-300' ?>">
                        <input type="radio" name="color_code" value="<?= htmlspecialchars((string)$choice['code']) ?>" class="sr-only" data-color-radio data-color-name="<?= htmlspecialchars((string)$choice['name']) ?>" data-color-image="<?= htmlspecialchars((string)($choice['imageUrl'] ?? '')) ?>" <?= $isChecked ? 'checked' : '' ?>>
                        <span class="inline-block h-6 w-6 rounded-full border border-slate-300" <?= $dotStyle ?>></span>
                    </label>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="m-0 text-[12px] text-slate-500">Mẫu xe hiện chưa có danh sách màu cấu hình trong dữ liệu sản phẩm.</p>
        <?php endif; ?>
    </div>

    <?php if (!empty($interiorChoices)): ?>
        <div class="rounded-xl border border-slate-100 p-4">
            <div class="mb-2 flex items-center justify-between gap-2">
                <p class="m-0 text-[12px] font-semibold text-slate-800">Nội thất</p>
                <p class="m-0 text-[11px] text-slate-500" data-selected-interior><?= htmlspecialchars($selectedInteriorName) ?></p>
            </div>

            <div class="flex flex-wrap gap-2" role="radiogroup" aria-label="Màu nội thất">
                <?php foreach ($interiorChoices as $choice): ?>
                    <?php
                    $isChecked = strtoupper((string)$choice['code']) === strtoupper($selectedInteriorCode);
                    $dotStyle = '';
                    if (!empty($choice['hex']) && preg_match('/^#?[A-F0-9]{3,6}$/i', (string)$choice['hex'])) {
                        $dotStyle = 'style="background:' . htmlspecialchars('#' . ltrim((string)$choice['hex'], '#')) . '"';
                    }
                    ?>
                    <label class="inline-flex cursor-pointer items-center justify-center rounded-full border-2 p-0.5 transition <?= $isChecked ? 'border-vfNavy shadow-[0_0_0_2px_rgba(20,100,244,0.22)]' : 'border-slate-200 hover:border-slate-300' ?>">
                        <input type="radio" value="<?= htmlspecialchars((string)$choice['code']) ?>" class="sr-only" data-interior-radio data-interior-name="<?= htmlspecialchars((string)$choice['name']) ?>" <?= $isChecked ? 'checked' : '' ?>>
                        <span class="inline-block h-6 w-6 rounded-full border border-slate-300" <?= $dotStyle ?>></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
        <div class="space-y-2 border-b border-slate-200 pb-3">
            <div class="flex items-center justify-between gap-2 text-[12px]">
                <span class="text-slate-500">Dự toán trả góp</span>
                <a href="<?= BASE_URL ?>contact" class="font-semibold text-blue-600 no-underline hover:underline">Chi tiết</a>
            </div>
            <div class="flex items-center justify-between gap-2 text-[12px]">
                <span class="text-slate-500">Dự toán chi phí lăn bánh</span>
                <a href="<?= BASE_URL ?>contact" class="font-semibold text-blue-600 no-underline hover:underline">Chi tiết</a>
            </div>
        </div>

        <div class="mt-3 flex items-center justify-between gap-2">
            <span class="text-[12px] font-semibold text-slate-700">Giá xe kèm pin</span>
            <span class="text-[14px] font-bold text-slate-900" data-selected-variant-price><?= htmlspecialchars(number_format((float)($selectedVariant['price'] ?? 0), 0, ',', '.')) ?> VNĐ</span>
        </div>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
        <a href="<?= BASE_URL ?>products/detail/<?= (int)$productId ?>" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-[13px] font-semibold text-slate-700 no-underline transition hover:bg-slate-100">Quay lại chi tiết</a>
        <button type="button" data-step-next="2" class="inline-flex items-center justify-center rounded-lg border border-vfNavy bg-vfNavy px-4 py-2 text-[13px] font-semibold text-white transition hover:opacity-90">Tiếp tục nhập thông tin</button>
    </div>
</section>