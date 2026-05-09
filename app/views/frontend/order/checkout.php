<?php
$product = isset($product) && is_array($product) ? $product : [];
$selectedColor = isset($selectedColor) && is_array($selectedColor) ? $selectedColor : [];
$provinces = isset($provinces) && is_array($provinces) ? $provinces : [];
$showrooms = isset($showrooms) && is_array($showrooms) ? $showrooms : [];
$formData = isset($formData) && is_array($formData) ? $formData : [];
$switchProducts = isset($switchProducts) && is_array($switchProducts) ? $switchProducts : [];

$checkout = CheckoutViewHelper::prepare($product, $selectedColor, $provinces, $showrooms, $formData, $switchProducts);
extract($checkout, EXTR_SKIP);

$checkoutJsVersion = AssetHelper::getVersion('public/js/frontend/checkout.js');
$scripts = '<script src="' . BASE_URL . 'public/js/frontend/checkout.js?v=' . htmlspecialchars($checkoutJsVersion) . '"></script>';
?>

<section class="min-h-screen bg-slate-50 py-8">
    <div class="mx-auto w-full max-w-6xl px-4 lg:px-6">
        <div class="flex flex-col gap-6 items-start lg:flex-row" id="vfCheckoutRoot"
            data-step="<?= (int)$currentStep ?>"
            data-deposit-amount="<?= (int)$depositAmount ?>"
            data-deposit-non-refundable="<?= (int)$depositNonRefundable ?>"
            data-color-surcharge="<?= (int)$selectedColorSurcharge ?>"
            data-showrooms='<?= htmlspecialchars(json_encode($showrooms), ENT_QUOTES) ?>'>

            <div class="min-w-0 flex-1">
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="grid grid-cols-1 lg:grid-cols-[132px_1fr]">
                        <div class="border-b bg-white p-3 lg:border-b-0 lg:border-r">
                            <p class="mb-2 text-[10px] font-semibold uppercase text-slate-500">Dòng xe</p>
                            <div class="space-y-2">
                                <?php foreach ($displayModels as $modelItem): ?>
                                    <?php
                                    $modelId = (int)($modelItem['id'] ?? 0);
                                    if ($modelId <= 0) {
                                        continue;
                                    }
                                    $isCurrentModel = $modelId === $productId;
                                    ?>
                                    <button type="submit"
                                        name="switch_product_id"
                                        value="<?= $modelId ?>"
                                        form="<?= $checkoutFormId ?>"
                                        formnovalidate
                                        class="w-full rounded-lg border px-2 py-2 text-left <?= $isCurrentModel ? 'border-blue-300 bg-blue-50' : 'border-slate-200 hover:bg-slate-50' ?>">
                                        <img src="<?= htmlspecialchars($modelItem['imageUrl'] ?: BASE_URL . 'public/images/products/default.jpg') ?>"
                                            class="mb-1 h-10 w-full rounded object-cover">
                                        <span class="block truncate text-[11px] font-semibold">
                                            <?= htmlspecialchars($modelItem['name']) ?>
                                        </span>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="bg-slate-50 p-6 lg:p-8">
                            <img id="vfCheckoutMainImage"
                                src="<?= htmlspecialchars($mainImage) ?>"
                                class="mx-auto max-h-[280px] w-full max-w-xl object-contain">

                            <div class="mt-6 grid grid-cols-3 divide-x rounded-xl border bg-white py-4 text-center shadow-sm">
                                <div>
                                    <p class="text-[10px] text-slate-400">CÔNG SUẤT</p>
                                    <p class="font-bold"><?= htmlspecialchars($powerText) ?></p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-slate-400">QUÃNG ĐƯỜNG</p>
                                    <p class="font-bold"><?= htmlspecialchars($rangeText) ?></p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-slate-400">WHEELBASE</p>
                                    <p class="font-bold"><?= htmlspecialchars($wheelbaseText) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-[380px] lg:flex-shrink-0">
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b px-4 pt-4">
                        <div class="flex items-center gap-2 pb-3">
                            <?php for ($step = 1; $step <= 3; $step++): ?>
                                <span data-step-tab="<?= $step ?>"
                                    class="pointer-events-none px-2 py-1 text-[11px] font-semibold <?= $currentStep === $step ? 'bg-slate-100 text-vfNavy' : 'text-slate-400' ?>">
                                    <span class="inline-grid h-5 w-5 place-items-center rounded-full text-[10px] <?= $currentStep >= $step ? 'bg-vfNavy text-white' : 'border text-slate-400' ?>">
                                        <?= $step ?>
                                    </span>
                                    <span class="hidden sm:inline">
                                        <?= $step === 1 ? 'Lựa chọn' : ($step === 2 ? 'Thông tin' : 'Đặt cọc') ?>
                                    </span>
                                </span>
                                <?php if ($step < 3): ?>
                                    <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="p-4">
                        <form id="<?= $checkoutFormId ?>" method="post" action="<?= BASE_URL ?>order/checkout/<?= $productId ?>">
                            <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                            <input type="hidden" name="step" value="<?= (int)$currentStep ?>" data-step-input>

                            <?php include ROOT . '/app/views/frontend/order/partials/checkout/step1.php'; ?>
                            <?php include ROOT . '/app/views/frontend/order/partials/checkout/step2.php'; ?>
                            <?php include ROOT . '/app/views/frontend/order/partials/checkout/step3.php'; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>