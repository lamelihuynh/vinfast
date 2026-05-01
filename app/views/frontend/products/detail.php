<?php
$product = isset($product) && is_array($product) ? $product : [];
$productId = (int)($product['id'] ?? 0);
$productName = (string)($product['name'] ?? 'VinFast');
$descriptionText = trim((string)($product['description'] ?? ''));

if ($descriptionText === '') {
    $descriptionText = 'Mẫu xe điện VinFast được tối ưu cho nhu cầu di chuyển hiện đại, kết hợp thiết kế thời thượng, vận hành êm ái và hệ sinh thái thông minh.';
}

$specs = is_array($product['specs'] ?? null) ? $product['specs'] : [];
$images = is_array($product['images'] ?? null) ? $product['images'] : [];
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

$resolveImageUrl = function (string $imgRel) use ($productFamily): string {
    $imgRel = trim($imgRel);
    if ($imgRel === '') {
        return '';
    }

    if (preg_match('/^https?:\/\//i', $imgRel)) {
        return $imgRel;
    }

    $imgRel = ltrim($imgRel, '/');

    if (strpos($imgRel, '/') !== false) {
        return BASE_URL . 'public/images/' . $imgRel;
    }

    $candidates = [
        'uploads/products/' . $imgRel,
        'products/' . $imgRel,
        $imgRel,
    ];

    foreach ($candidates as $candidate) {
        $fullPath = ROOT . '/public/images/' . $candidate;
        if (is_file($fullPath)) {
            return BASE_URL . 'public/images/' . $candidate;
        }
    }

    $basename = basename($imgRel);

    if ($productFamily !== '') {
        $priorityPath = ROOT . '/public/images/uploads/products/' . $productFamily . '/' . $basename;
        if (is_file($priorityPath)) {
            $match = str_replace(ROOT . '/public/images/', '', $priorityPath);
            $match = str_replace('\\', '/', $match);
            return BASE_URL . 'public/images/' . $match;
        }
    }

    $searchPatterns = [
        ROOT . '/public/images/uploads/products/*/' . $basename,
        ROOT . '/public/images/uploads/products/*/*/' . $basename,
    ];

    foreach ($searchPatterns as $pattern) {
        $matches = glob($pattern) ?: [];
        if (!empty($matches)) {
            $match = str_replace(ROOT . '/public/images/', '', (string)$matches[0]);
            $match = str_replace('\\', '/', $match);
            return BASE_URL . 'public/images/' . $match;
        }
    }

    return BASE_URL . 'public/images/products/' . $imgRel;
};

$imageUrls = [];
foreach ($images as $imgRel) {
    if (!is_string($imgRel) || trim($imgRel) === '') {
        continue;
    }

    $imageUrls[] = $resolveImageUrl((string)$imgRel);
}

$exteriorColorsRaw = is_array($specs['exterior_colors'] ?? null) ? $specs['exterior_colors'] : [];
$colorOptions = [];
foreach ($exteriorColorsRaw as $row) {
    if (!is_array($row)) {
        continue;
    }

    $code = strtoupper(trim((string)($row['code'] ?? '')));
    $name = trim((string)($row['name'] ?? ''));
    $hex = strtoupper(trim((string)($row['hex'] ?? '')));
    $img = trim((string)($row['image'] ?? ''));

    if ($code === '' || $name === '') {
        continue;
    }

    $imageUrl = '';
    if ($img !== '') {
        $imageUrl = $resolveImageUrl($img);
    }

    if ($imageUrl === '' && $productFamily !== '') {
        $codeImageCandidates = [
            'uploads/products/' . $productFamily . '/' . strtolower($code) . '.webp',
            'uploads/products/' . $productFamily . '/' . strtolower($code) . '.jpg',
            'uploads/products/' . $productFamily . '/' . strtolower($code) . '.jpeg',
            'uploads/products/' . $productFamily . '/' . strtolower($code) . '.png',
        ];

        foreach ($codeImageCandidates as $candidate) {
            if (is_file(ROOT . '/public/images/' . $candidate)) {
                $imageUrl = BASE_URL . 'public/images/' . $candidate;
                break;
            }
        }
    }

    $colorOptions[$code] = [
        'code' => $code,
        'name' => $name,
        'hex' => preg_match('/^#?[A-F0-9]{3,6}$/i', $hex) ? ('#' . ltrim($hex, '#')) : '',
        'image' => $imageUrl,
        'surcharge' => max(0, (int)($row['surcharge'] ?? 0)),
    ];
}

$colorOptions = array_values($colorOptions);

foreach ($colorOptions as &$colorOption) {
    if (!empty($colorOption['image'])) {
        continue;
    }

    $targetCode = strtoupper((string)$colorOption['code']);
    foreach ($imageUrls as $galleryUrl) {
        $path = parse_url((string)$galleryUrl, PHP_URL_PATH);
        $filename = is_string($path) ? basename($path) : '';
        $basename = strtoupper((string)pathinfo($filename, PATHINFO_FILENAME));
        if ($basename !== '' && $basename === $targetCode) {
            $colorOption['image'] = $galleryUrl;
            break;
        }
    }
}
unset($colorOption);

foreach ($colorOptions as $c) {
    if (($c['image'] ?? '') !== '' && !in_array($c['image'], $imageUrls, true)) {
        $imageUrls[] = $c['image'];
    }
}

if (empty($imageUrls)) {
    $imageUrls[] = BASE_URL . 'public/images/products/default.jpg';
}

$mainImage = $imageUrls[0];

$defaultColor = null;
if (!empty($colorOptions)) {
    $defaultColor = $colorOptions[0];
    if (!empty($defaultColor['image'])) {
        $mainImage = $defaultColor['image'];
    }
}

$categoryText = (string)($specs['category'] ?? ($specs['segment'] ?? 'Xe điện VinFast'));
$powerText = (string)($specs['power'] ?? ($specs['motor_power'] ?? ($specs['max_power'] ?? 'N/A')));
$rangeText = (string)($specs['range'] ?? ($specs['driving_range'] ?? 'N/A'));
$batteryText = (string)($specs['battery'] ?? 'N/A');
$stats = [
    ['icon' => 'fa-solid fa-bolt', 'val' => $powerText, 'label' => 'Công suất'],
    ['icon' => 'fa-solid fa-battery-three-quarters', 'val' => $batteryText, 'label' => 'Pin'],
    ['icon' => 'fa-solid fa-battery-three-quarters', 'val' => $rangeText, 'label' => 'Phạm vi'],
];

$featureList = [];
if (isset($specs['features']) && is_array($specs['features'])) {
    foreach ($specs['features'] as $f) {
        if (is_string($f) && trim($f) !== '') {
            $featureList[] = trim($f);
        }
    }
}

if (empty($featureList)) {
    $featureList = [
        'Bảo hành 10 năm hoặc 200,000km',
        'Hệ thống hỗ trợ lái ADAS tiên tiến',
        'Màn hình giải trí trung tâm kích thước lớn',
        'Hỗ trợ cứu hộ 24/7 toàn quốc',
        'Hệ sinh thái ứng dụng VinFast thông minh',
    ];
}

$excludeKeys = ['features', 'variants', 'exterior_colors', 'interior_colors', 'images'];
$specRows = [];

foreach ($specs as $k => $v) {
    if (in_array((string)$k, $excludeKeys, true)) {
        continue;
    }
    if (is_array($v) || is_object($v)) {
        continue;
    }

    $label = trim((string)$k);
    $value = trim((string)$v);

    if ($label === '' || $value === '') {
        continue;
    }

    $specRows[] = [
        'label' => ucfirst(str_replace('_', ' ', $label)),
        'value' => $value
    ];
}

if (empty($specRows)) {
    $specRows = [
        ['label' => 'Loại pin', 'value' => 'LFP (Lithium Iron Phosphate)'],
        ['label' => 'Dung lượng pin', 'value' => '75.3 kWh'],
        ['label' => 'Hệ dẫn động', 'value' => 'AWD (4 bánh)'],
        ['label' => 'Sạc nhanh DC', 'value' => '150 kW'],
        ['label' => 'Số chỗ ngồi', 'value' => '5 chỗ'],
    ];
}


$priceText = number_format((float)($product['price'] ?? 0), 0, ',', '.');

$productsJsVersion = AssetHelper::getVersion('public/js/frontend/products.js');
$productsCssVersion = AssetHelper::getVersion('public/css/frontend/products.css');
?>

<link rel="stylesheet" href="<?= BASE_URL ?>public/css/frontend/products.css?v=<?= htmlspecialchars($productsCssVersion) ?>">

<section id="vfProductDetail" class="min-h-screen bg-white">
    <div class="mx-auto w-full max-w-[1200px] px-4 pb-6 pt-3 lg:px-6 lg:pt-4">
        <nav class="mb-4 flex items-center gap-2 text-[12px] text-slate-400" aria-label="Breadcrumb">
            <a href="<?= BASE_URL ?>products" class="text-slate-500 no-underline hover:text-blue-600">Sản phẩm</a>
            <i class="fa-solid fa-chevron-right text-[10px]"></i>
            <span class="text-slate-700"><?= htmlspecialchars($productName) ?></span>
        </nav>

        <div class="vf-pd-top-layout mb-8 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-[58%_42%]">
            <div class="vf-pd-col-gallery min-w-0">
                <?php include ROOT . '/app/views/frontend/products/partials/detail/gallery.php'; ?>
            </div>
            <div class="vf-pd-col-summary min-w-0">
                <?php include ROOT . '/app/views/frontend/products/partials/detail/summary.php'; ?>
            </div>
        </div>

        <?php include ROOT . '/app/views/frontend/products/partials/detail/tabs.php'; ?>
    </div>
</section>

<script src="<?= BASE_URL ?>public/js/frontend/products.js?v=<?= htmlspecialchars($productsJsVersion) ?>"></script>