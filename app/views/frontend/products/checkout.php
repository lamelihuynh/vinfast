<?php
$product = isset($product) && is_array($product) ? $product : [];
$selectedColor = isset($selectedColor) && is_array($selectedColor) ? $selectedColor : [];
$provinces = isset($provinces) && is_array($provinces) ? $provinces : [];
$showrooms = isset($showrooms) && is_array($showrooms) ? $showrooms : [];
$formData = isset($formData) && is_array($formData) ? $formData : [];
$switchProducts = isset($switchProducts) && is_array($switchProducts) ? $switchProducts : [];

$productId = (int)($product['id'] ?? 0);
$productName = (string)($product['name'] ?? 'VinFast');
$productSlug = trim((string)($product['slug'] ?? ''));
$priceText = number_format((float)($product['price'] ?? 0), 0, ',', '.');

$extractProductFamily = static function (string $text): string {
    $value = strtolower(trim($text));
    if ($value === '') {
        return '';
    }

    if (preg_match('/(?:^|[-_])vf(?:-?mpv)?-?([3-9])(?:[-_]|$)/i', $value, $familyMatch)) {
        return 'vf' . $familyMatch[1];
    }

    $normalized = preg_replace('/[^a-z0-9]+/i', '-', $value);
    $normalized = trim((string)$normalized, '-');
    if ($normalized === '') {
        return '';
    }

    if (strpos($normalized, 'vinfast-') === 0) {
        $normalized = substr($normalized, 8);
    }

    $normalized = trim((string)$normalized, '-');
    if ($normalized === '') {
        return '';
    }

    $parts = explode('-', $normalized);
    $family = strtolower(trim((string)($parts[0] ?? '')));
    if (!preg_match('/^[a-z0-9]+$/', $family)) {
        return '';
    }

    return $family;
};

$productFamily = '';
if (!empty($product['slug'])) {
    $productFamily = $extractProductFamily((string)$product['slug']);
}
if ($productFamily === '' && !empty($product['name'])) {
    $productFamily = $extractProductFamily((string)$product['name']);
}

$colorCode = trim((string)($selectedColor['code'] ?? ''));
$colorName = trim((string)($selectedColor['name'] ?? ''));

$specs = is_array($product['specs'] ?? null) ? $product['specs'] : [];
$images = is_array($product['images'] ?? null) ? $product['images'] : [];
$rawExteriorColors = is_array($specs['exterior_colors'] ?? null) ? $specs['exterior_colors'] : [];
$rawInteriorColors = is_array($specs['interior_colors'] ?? null) ? $specs['interior_colors'] : [];
$rawVariants = is_array($specs['variants'] ?? null) ? $specs['variants'] : [];

$resolveImageUrl = static function (string $imgRel, string $preferredSlug = '') use ($productFamily): string {
    $imgRel = trim($imgRel);
    if ($imgRel === '') {
        return '';
    }

    $preferredSlug = strtolower(trim($preferredSlug));
    if ($preferredSlug === '') {
        $preferredSlug = '';
    }

    if (preg_match('/^https?:\/\//i', $imgRel)) {
        return $imgRel;
    }

    $imgRel = ltrim($imgRel, '/');

    if (strpos($imgRel, '/') !== false) {
        $fullPath = ROOT . '/public/images/' . $imgRel;
        if (is_file($fullPath)) {
            return BASE_URL . 'public/images/' . $imgRel;
        }

        $basename = basename($imgRel);
        $fixCandidates = [];
        if ($preferredSlug !== '') {
            $fixCandidates[] = 'uploads/products/' . $preferredSlug . '/' . $basename;
        }
        if ($productFamily !== '') {
            $fixCandidates[] = 'uploads/products/' . $productFamily . '/' . $basename;
        }

        foreach ($fixCandidates as $candidate) {
            $candidatePath = ROOT . '/public/images/' . $candidate;
            if (is_file($candidatePath)) {
                return BASE_URL . 'public/images/' . $candidate;
            }
        }

        return BASE_URL . 'public/images/' . $imgRel;
    }

    $basename = basename($imgRel);
    $candidates = [
        $preferredSlug !== '' ? 'uploads/products/' . $preferredSlug . '/' . $basename : '',
        $productFamily !== '' ? 'uploads/products/' . $productFamily . '/' . $basename : '',
        'uploads/products/' . $imgRel,
        'products/' . $imgRel,
        $imgRel,
    ];

    foreach ($candidates as $candidate) {
        if ($candidate === '') {
            continue;
        }
        $fullPath = ROOT . '/public/images/' . $candidate;
        if (is_file($fullPath)) {
            return BASE_URL . 'public/images/' . $candidate;
        }
    }

    if ($preferredSlug !== '') {
        $slugPriorityPath = ROOT . '/public/images/uploads/products/' . $preferredSlug . '/' . $basename;
        if (is_file($slugPriorityPath)) {
            $match = str_replace(ROOT . '/public/images/', '', $slugPriorityPath);
            $match = str_replace('\\', '/', $match);
            return BASE_URL . 'public/images/' . $match;
        }
    }

    if ($productFamily !== '') {
        $priorityPath = ROOT . '/public/images/uploads/products/' . $productFamily . '/' . $basename;
        if (is_file($priorityPath)) {
            $match = str_replace(ROOT . '/public/images/', '', $priorityPath);
            $match = str_replace('\\', '/', $match);
            return BASE_URL . 'public/images/' . $match;
        }
    }

    return BASE_URL . 'public/images/products/' . $imgRel;
};

$variantChoices = [];
foreach ($rawVariants as $row) {
    if (!is_array($row)) {
        continue;
    }

    $variantName = trim((string)($row['name'] ?? ''));
    $variantPrice = (float)($row['price'] ?? 0);
    if ($variantName === '') {
        continue;
    }

    if ($variantPrice <= 0) {
        $variantPrice = (float)($product['price'] ?? 0);
    }

    $variantChoices[] = [
        'name' => $variantName,
        'price' => $variantPrice,
    ];
}

if (empty($variantChoices)) {
    $variantChoices[] = [
        'name' => $productName,
        'price' => (float)($product['price'] ?? 0),
    ];
}
$colorChoices = [];
foreach ($rawExteriorColors as $row) {
    if (!is_array($row)) {
        continue;
    }
    $code = strtoupper(trim((string)($row['code'] ?? '')));
    $name = trim((string)($row['name'] ?? ''));
    $hex = trim((string)($row['hex'] ?? ''));

    if ($code === '' || $name === '') {
        continue;
    }

    $colorChoices[] = [
        'code' => $code,
        'name' => $name,
        'hex' => $hex,
        'image' => trim((string)($row['image'] ?? '')),
        'surcharge' => max(0, (int)($row['surcharge'] ?? 0)),
    ];
}

foreach ($colorChoices as &$colorChoice) {
    $colorImage = '';
    if (!empty($colorChoice['image'])) {
        $colorImage = $resolveImageUrl((string)$colorChoice['image'], $productSlug);
    }

    if ($colorImage === '') {
        $targetCode = strtoupper((string)($colorChoice['code'] ?? ''));
        foreach ($images as $imgRel) {
            if (!is_string($imgRel) || trim($imgRel) === '') {
                continue;
            }

            $imgPath = parse_url((string)$imgRel, PHP_URL_PATH);
            $imgFilename = is_string($imgPath) ? basename($imgPath) : basename((string)$imgRel);
            $imgBasename = strtoupper((string)pathinfo($imgFilename, PATHINFO_FILENAME));
            if ($imgBasename !== '' && $imgBasename === $targetCode) {
                $colorImage = $resolveImageUrl((string)$imgRel, $productSlug);
                break;
            }
        }
    }

    $colorChoice['imageUrl'] = $colorImage;
}
unset($colorChoice);

$interiorChoices = [];
foreach ($rawInteriorColors as $row) {
    if (!is_array($row)) {
        continue;
    }

    $code = strtoupper(trim((string)($row['code'] ?? $row['name'] ?? '')));
    $name = trim((string)($row['name'] ?? ''));
    $hex = trim((string)($row['hex'] ?? ''));
    if ($code === '' || $name === '') {
        continue;
    }

    $interiorChoices[] = [
        'code' => $code,
        'name' => $name,
        'hex' => $hex,
    ];
}

if ($colorCode === '' && !empty($colorChoices)) {
    $colorCode = (string)$colorChoices[0]['code'];
    $colorName = (string)$colorChoices[0]['name'];
}

$mainImage = BASE_URL . 'public/images/products/default.jpg';
if (!empty($images) && is_string($images[0])) {
    $resolved = $resolveImageUrl((string)$images[0], $productSlug);
    if ($resolved !== '') {
        $mainImage = $resolved;
    }
}

foreach ($colorChoices as $colorChoice) {
    if (strtoupper((string)$colorChoice['code']) !== strtoupper($colorCode)) {
        continue;
    }
    if (!empty($colorChoice['imageUrl'])) {
        $mainImage = (string)$colorChoice['imageUrl'];
    }
    break;
}

$powerText = trim((string)($specs['power'] ?? ($specs['motor_power'] ?? 'N/A')));
$rangeText = trim((string)($specs['range'] ?? ($specs['driving_range'] ?? 'N/A')));
$wheelbaseText = trim((string)($specs['wheelbase'] ?? ($specs['wheel_base'] ?? 'N/A')));

$switchProductsUi = [];
foreach ($switchProducts as $item) {
    if (!is_array($item)) {
        continue;
    }

    $modelKey = strtolower(trim((string)($item['model_key'] ?? '')));
    if ($modelKey === '') {
        $fallbackSeed = (string)($item['slug'] ?? $item['name'] ?? '');
        $modelKey = $extractProductFamily($fallbackSeed);
    }

    $switchProductsUi[] = [
        'id' => (int)($item['id'] ?? 0),
        'name' => (string)($item['name'] ?? 'VinFast'),
        'slug' => (string)($item['slug'] ?? ''),
        'modelKey' => $modelKey,
        'priceRaw' => (float)($item['price'] ?? 0),
        'priceText' => number_format((float)($item['price'] ?? 0), 0, ',', '.') . ' VNĐ',
        'imageUrl' => $resolveImageUrl((string)($item['image'] ?? ''), (string)($item['slug'] ?? '')),
        'isCurrent' => (bool)($item['is_current'] ?? false),
    ];
}

if (empty($switchProductsUi)) {
    $switchProductsUi[] = [
        'id' => $productId,
        'name' => $productName,
        'slug' => (string)($product['slug'] ?? ''),
        'modelKey' => $extractProductFamily((string)($product['slug'] ?? $productName)),
        'priceRaw' => (float)($product['price'] ?? 0),
        'priceText' => $priceText . ' VNĐ',
        'imageUrl' => $mainImage,
        'isCurrent' => true,
    ];
}

$modelGroups = [];
foreach ($switchProductsUi as $item) {
    $key = trim((string)($item['modelKey'] ?? ''));
    if ($key === '') {
        $key = 'product-' . (int)($item['id'] ?? 0);
    }

    if (!isset($modelGroups[$key])) {
        $modelGroups[$key] = [
            'key' => $key,
            'representative' => $item,
            'items' => [],
        ];
    }

    $modelGroups[$key]['items'][] = $item;
    if (!empty($item['isCurrent']) || (int)($item['id'] ?? 0) === $productId) {
        $modelGroups[$key]['representative'] = $item;
    }
}

$currentModelKey = '';
foreach ($modelGroups as $groupKey => $group) {
    foreach ($group['items'] as $modelItem) {
        if (!empty($modelItem['isCurrent']) || (int)($modelItem['id'] ?? 0) === $productId) {
            $currentModelKey = (string)$groupKey;
            break 2;
        }
    }
}

if ($currentModelKey === '' && !empty($modelGroups)) {
    $currentModelKey = (string)array_key_first($modelGroups);
}

$displayModels = [];
if ($currentModelKey !== '' && isset($modelGroups[$currentModelKey])) {
    $displayModels[] = $modelGroups[$currentModelKey]['representative'];
}
foreach ($modelGroups as $groupKey => $group) {
    if ($groupKey === $currentModelKey) {
        continue;
    }
    $displayModels[] = $group['representative'];
}

$variantSwitchChoices = [];
if ($currentModelKey !== '' && isset($modelGroups[$currentModelKey])) {
    foreach ($modelGroups[$currentModelKey]['items'] as $modelItem) {
        $variantSwitchChoices[] = [
            'productId' => (int)($modelItem['id'] ?? 0),
            'name' => (string)($modelItem['name'] ?? 'VinFast'),
            'price' => (float)($modelItem['priceRaw'] ?? 0),
            'priceText' => (string)($modelItem['priceText'] ?? '0 VNĐ'),
            'isCurrent' => !empty($modelItem['isCurrent']) || (int)($modelItem['id'] ?? 0) === $productId,
        ];
    }
}

$formData = array_merge([
    'owner_type' => 'ca-nhan',
    'full_name' => '',
    'phone' => '',
    'cccd' => '',
    'email' => '',
    'province' => '',
    'showroom' => '',
    'salesperson' => '',
    'voucher' => '',
    'pay_method' => 'card-intl',
    'agree_terms' => '',
    'variant_name' => '',
    'interior_code' => '',
    'step' => 1,
], $formData);

$selectedVariantName = trim((string)$formData['variant_name']);
if ($selectedVariantName === '') {
    $selectedVariantName = (string)($variantChoices[0]['name'] ?? $productName);
}

$depositAmount = max(0, (int)($specs['deposit_amount'] ?? 15000000));
$depositNonRefundable = !empty($specs['deposit_non_refundable']) ? 1 : 0;
$selectedColorSurcharge = max(0, (int)($selectedColor['surcharge'] ?? 0));

$selectedVariant = $variantChoices[0];
foreach ($variantChoices as $variantRow) {
    if (strcasecmp((string)($variantRow['name'] ?? ''), $selectedVariantName) === 0) {
        $selectedVariant = $variantRow;
        break;
    }
}

$selectedInteriorCode = strtoupper(trim((string)$formData['interior_code']));
$selectedInteriorName = '';
if (empty($interiorChoices)) {
    $selectedInteriorCode = '';
} elseif ($selectedInteriorCode === '') {
    $selectedInteriorCode = (string)$interiorChoices[0]['code'];
}

foreach ($interiorChoices as $interiorRow) {
    if (strtoupper((string)$interiorRow['code']) !== $selectedInteriorCode) {
        continue;
    }
    $selectedInteriorName = (string)$interiorRow['name'];
    break;
}

if ($selectedInteriorName === '' && !empty($interiorChoices)) {
    $selectedInteriorName = (string)$interiorChoices[0]['name'];
}

$currentStep = max(1, min(3, (int)$formData['step']));
$checkoutFormId = 'vfCheckoutForm';

$checkoutJsVersion = AssetHelper::getVersion('public/js/frontend/checkout.js');
$scripts = '<script src="' . BASE_URL . 'public/js/frontend/checkout.js?v=' . htmlspecialchars($checkoutJsVersion) . '"></script>';
?>

<section class="min-h-screen bg-slate-50 py-8">
    <div class="mx-auto w-full max-w-6xl px-4 lg:px-6">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-start" id="vfCheckoutRoot" data-step="<?= (int)$currentStep ?>" data-showrooms='<?= htmlspecialchars(json_encode($showrooms, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>' data-current-color='<?= htmlspecialchars($colorName !== '' ? $colorName : $colorCode) ?>' data-deposit-amount="<?= (int)$depositAmount ?>" data-deposit-non-refundable="<?= (int)$depositNonRefundable ?>" data-color-surcharge="<?= (int)$selectedColorSurcharge ?>">
            <div class="min-w-0 flex-1">
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="grid grid-cols-1 lg:grid-cols-[132px_1fr]">
                        <div class="border-b border-slate-100 bg-white p-3 lg:border-b-0 lg:border-r">
                            <p class="mb-2 text-[10px] font-semibold uppercase tracking-[0.8px] text-slate-500">Dòng xe</p>
                            <div class="space-y-2">
                                <?php foreach ($displayModels as $modelItem): ?>
                                    <?php
                                    $modelId = (int)($modelItem['id'] ?? 0);
                                    if ($modelId <= 0) {
                                        continue;
                                    }
                                    $isCurrentModel = $modelId === $productId;
                                    ?>
                                    <button
                                        type="submit"
                                        name="switch_product_id"
                                        value="<?= $modelId ?>"
                                        form="<?= $checkoutFormId ?>"
                                        data-switch-product
                                        class="w-full rounded-lg border px-2 py-2 text-left transition <?= $isCurrentModel ? 'border-blue-300 bg-blue-50' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50' ?>">
                                        <img
                                            src="<?= htmlspecialchars($modelItem['imageUrl'] !== '' ? $modelItem['imageUrl'] : (BASE_URL . 'public/images/products/default.jpg')) ?>"
                                            alt="<?= htmlspecialchars((string)$modelItem['name']) ?>"
                                            class="mb-1 h-10 w-full rounded object-cover">
                                        <span class="block truncate text-[11px] font-semibold <?= $isCurrentModel ? 'text-vfNavy' : 'text-slate-700' ?>"><?= htmlspecialchars((string)$modelItem['name']) ?></span>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="bg-slate-50 p-6 lg:p-8">
                            <img id="vfCheckoutMainImage" src="<?= htmlspecialchars($mainImage) ?>" alt="<?= htmlspecialchars($productName) ?>" class="mx-auto h-auto max-h-[320px] w-full max-w-2xl object-contain">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 divide-y divide-slate-100 border-t border-slate-100 sm:grid-cols-3 sm:divide-x sm:divide-y-0">
                        <div class="px-4 py-4 text-center">
                            <p class="mb-1 text-[10px] text-slate-400">CÔNG SUẤT TỐI ĐA</p>
                            <p class="m-0 text-[18px] font-bold text-slate-900"><?= htmlspecialchars($powerText) ?></p>
                        </div>
                        <div class="px-4 py-4 text-center">
                            <p class="mb-1 text-[10px] text-slate-400">QUÃNG ĐƯỜNG 1 LẦN SẠC</p>
                            <p class="m-0 text-[18px] font-bold text-slate-900"><?= htmlspecialchars($rangeText) ?></p>
                        </div>
                        <div class="px-4 py-4 text-center">
                            <p class="mb-1 text-[10px] text-slate-400">CHIỀU DÀI CƠ SỞ</p>
                            <p class="m-0 text-[18px] font-bold text-slate-900"><?= htmlspecialchars($wheelbaseText) ?></p>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 px-4 py-3 text-center text-[10px] text-slate-400">
                        Các thông tin sản phẩm có thể thay đổi mà không cần báo trước.
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-[400px] lg:flex-shrink-0">
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-4 pt-4">
                        <div class="flex items-center gap-2 pb-3">
                            <?php for ($step = 1; $step <= 3; $step++): ?>
                                <button type="button" data-step-tab="<?= (int)$step ?>" class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-[11px] font-semibold transition <?= $currentStep === $step ? 'bg-slate-100 text-vfNavy' : 'text-slate-400 hover:text-slate-600' ?>">
                                    <span class="grid h-5 w-5 place-items-center rounded-full text-[10px] <?= $currentStep >= $step ? 'bg-vfNavy text-white' : 'border border-slate-300 text-slate-400' ?>"><?= (int)$step ?></span>
                                    <span class="hidden sm:inline"><?= htmlspecialchars($step === 1 ? 'Lựa chọn' : ($step === 2 ? 'Thông tin' : 'Đặt cọc')) ?></span>
                                </button>
                                <?php if ($step < 3): ?>
                                    <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="p-4">
                        <form id="<?= $checkoutFormId ?>" method="post" action="<?= BASE_URL ?>products/checkout/<?= (int)$productId ?>" novalidate>
                            <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                            <input type="hidden" name="step" value="<?= (int)$currentStep ?>" data-step-input>

                            <?php include ROOT . '/app/views/frontend/products/partials/checkout/step1.php'; ?>
                            <?php include ROOT . '/app/views/frontend/products/partials/checkout/step2.php'; ?>
                            <?php include ROOT . '/app/views/frontend/products/partials/checkout/step3.php'; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>