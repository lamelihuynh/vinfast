<?php
$product = is_array($product ?? null) ? $product : [];
$productId = (int)($product['id'] ?? 0);
$productName = (string)($product['name'] ?? 'VinFast');
$descriptionText = trim((string)($product['description'] ?? ''));

if ($descriptionText === '') {
    $descriptionText = 'Mẫu xe điện VinFast được tối ưu cho nhu cầu di chuyển hiện đại, kết hợp thiết kế thời thượng, vận hành êm ái và hệ sinh thái thông minh.';
}

$specs = is_array($product['specs'] ?? null) ? $product['specs'] : [];
$images = is_array($product['images'] ?? null) ? $product['images'] : [];

$imageUrls = [];
foreach ($images as $imgRel) {
    if (!is_string($imgRel) || trim($imgRel) === '') {
        continue;
    }

    $imgRel = trim($imgRel);
    if (preg_match('/^https?:\/\//i', $imgRel)) {
        $imageUrls[] = $imgRel;
    } else {
        $imageUrls[] = BASE_URL . 'public/images/' . ltrim($imgRel, '/');
    }
}

if (empty($imageUrls)) {
    $imageUrls[] = BASE_URL . 'public/images/products/default.jpg';
}

$mainImage = $imageUrls[0];

$categoryText = (string)($specs['category'] ?? ($specs['segment'] ?? 'Xe điện VinFast'));
$powerText = (string)($specs['power'] ?? ($specs['motor_power'] ?? ($specs['max_power'] ?? 'N/A')));
$rangeText = (string)($specs['range'] ?? ($specs['driving_range'] ?? 'N/A'));
$accelText = (string)($specs['acceleration'] ?? ($specs['zero_to_hundred'] ?? ($specs['0_100'] ?? 'N/A')));

$stats = [
    ['icon' => 'fa-solid fa-bolt', 'val' => $powerText, 'label' => 'Công suất'],
    ['icon' => 'fa-solid fa-battery-three-quarters', 'val' => $rangeText, 'label' => 'Phạm vi'],
    ['icon' => 'fa-solid fa-gauge-high', 'val' => $accelText, 'label' => '0-100 km/h'],
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

$reviewCount = (int)($specs['review_count'] ?? 48);

$reviewItems = [
    ['name' => 'Nguyễn Minh Khang', 'date' => '12/03/2026', 'stars' => 5, 'body' => 'Xe tăng tốc tốt, vận hành mượt và tiết kiệm chi phí so với xe xăng trước đây.'],
    ['name' => 'Trần Bảo Anh', 'date' => '01/03/2026', 'stars' => 5, 'body' => 'Không gian nội thất rộng rãi, nhiều công nghệ an toàn và hỗ trợ lái rất hữu ích.'],
    ['name' => 'Lê Đức Nam', 'date' => '21/02/2026', 'stars' => 4, 'body' => 'Phù hợp đi phố và đi tỉnh, trạm sạc ngày càng nhiều nên yên tâm sử dụng.'],
    ['name' => 'Phạm Thu Hà', 'date' => '16/02/2026', 'stars' => 5, 'body' => 'Thiết kế đẹp, màn hình dễ dùng, dịch vụ hậu mãi tốt và hỗ trợ nhanh.'],
];

$priceText = number_format((float)($product['price'] ?? 0), 0, ',', '.');

$productsJsVersion = AssetHelper::getVersion('public/js/frontend/products.js');
$productsCssVersion = AssetHelper::getVersion('public/css/frontend/products.css');
?>

<link rel="stylesheet" href="<?= BASE_URL ?>public/css/frontend/products.css?v=<?= htmlspecialchars($productsCssVersion) ?>">

<section id="vfProductDetail" class="min-h-screen bg-white">
    <div class="mx-auto w-full max-w-[1200px] px-4 py-6 lg:px-6">
        <nav class="mb-6 flex items-center gap-2 text-[12px] text-slate-400" aria-label="Breadcrumb">
            <a href="<?= BASE_URL ?>products" class="text-slate-500 no-underline hover:text-blue-600">Sản phẩm</a>
            <i class="fa-solid fa-chevron-right text-[10px]"></i>
            <span class="text-slate-700"><?= htmlspecialchars($productName) ?></span>
        </nav>

        <div class="vf-pd-top-layout mb-10 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-[58%_42%]">
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