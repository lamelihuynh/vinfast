<?php

/**
 * database/seed/accessories.php — Catalog phụ kiện mẫu (Phase 0.5)
 *
 * Dữ liệu minh họa (không phải giá/sản phẩm chính thức). Mỗi category tự khai báo:
 *   dynamicAttributes : thuộc tính cấp sản phẩm (products.attributes)
 *   variantAttributes : thuộc tính phân biệt phiên bản (variants[].attributes) — mỗi phiên bản có tồn kho riêng
 * Quy ước: thời trang tính MÀU × SIZE vào phiên bản (tồn kho khác nhau theo màu) — điều chỉnh quyết định 3.
 *
 * Dùng bởi seed.php (catalog small) và generate.php (sinh dữ liệu lớn theo cùng schema).
 */

$models = ['VF 3', 'VF 5', 'VF 6', 'VF 7', 'VF 8', 'VF 9', 'Evo', 'Feliz', 'Klara', 'Vento', 'Theon'];

$categories = [
    [
        'categoryID' => 3,
        'name' => 'Pin & Sạc',
        'slug' => 'pin-sac',
        'description' => 'Pin, bộ sạc và thiết bị sạc cho xe điện VinFast',
        'kind' => 'accessory',
        'dynamicAttributes' => [
            ['key' => 'capacity_kwh', 'label' => 'Dung lượng', 'type' => 'number', 'unit' => 'kWh', 'required' => false, 'filterable' => true],
            ['key' => 'power_kw', 'label' => 'Công suất sạc', 'type' => 'number', 'unit' => 'kW', 'required' => false, 'filterable' => true],
            ['key' => 'voltage', 'label' => 'Điện áp', 'type' => 'string', 'unit' => 'V', 'required' => false, 'filterable' => false],
            ['key' => 'connector', 'label' => 'Chuẩn kết nối', 'type' => 'enum', 'required' => false, 'filterable' => true, 'options' => ['Type 2', 'CCS2', 'Ổ cắm gia dụng', 'Chân pin xe máy']],
            ['key' => 'warranty_months', 'label' => 'Bảo hành', 'type' => 'number', 'unit' => 'tháng', 'required' => false, 'filterable' => false],
            ['key' => 'compatible_models', 'label' => 'Dòng xe tương thích', 'type' => 'list', 'required' => true, 'filterable' => true, 'options' => $models],
        ],
        'variantAttributes' => [
            ['key' => 'option', 'label' => 'Tùy chọn', 'type' => 'string', 'required' => true],
        ],
    ],
    [
        'categoryID' => 4,
        'name' => 'Thời trang',
        'slug' => 'thoi-trang',
        'description' => 'Áo, mũ bảo hiểm và đồ thời trang VinFast',
        'kind' => 'accessory',
        'dynamicAttributes' => [
            ['key' => 'material', 'label' => 'Chất liệu', 'type' => 'string', 'required' => false, 'filterable' => false],
            ['key' => 'gender', 'label' => 'Giới tính', 'type' => 'enum', 'required' => false, 'filterable' => true, 'options' => ['Nam', 'Nữ', 'Unisex']],
            ['key' => 'care', 'label' => 'Hướng dẫn bảo quản', 'type' => 'text', 'required' => false, 'filterable' => false],
        ],
        'variantAttributes' => [
            ['key' => 'color', 'label' => 'Màu', 'type' => 'string', 'required' => true],
            ['key' => 'size', 'label' => 'Size', 'type' => 'enum', 'required' => true, 'options' => ['S', 'M', 'L', 'XL', 'Free size']],
        ],
    ],
    [
        'categoryID' => 5,
        'name' => 'Phụ kiện xe',
        'slug' => 'phu-kien-xe',
        'description' => 'Thảm, camera, giá nóc và phụ kiện lắp trên xe',
        'kind' => 'accessory',
        'dynamicAttributes' => [
            ['key' => 'material', 'label' => 'Chất liệu', 'type' => 'string', 'required' => false, 'filterable' => false],
            ['key' => 'needs_install', 'label' => 'Cần lắp đặt tại xưởng', 'type' => 'boolean', 'required' => false, 'filterable' => true],
            ['key' => 'warranty_months', 'label' => 'Bảo hành', 'type' => 'number', 'unit' => 'tháng', 'required' => false, 'filterable' => false],
            ['key' => 'compatible_models', 'label' => 'Dòng xe tương thích', 'type' => 'list', 'required' => true, 'filterable' => true, 'options' => $models],
        ],
        'variantAttributes' => [
            ['key' => 'fit_model', 'label' => 'Dòng xe', 'type' => 'enum', 'required' => true, 'options' => $models],
            ['key' => 'color', 'label' => 'Màu', 'type' => 'string', 'required' => false],
        ],
    ],
];

/** Phiên bản theo tích Descartes màu × size, giá bằng nhau */
$apparel = static function (array $colors, array $sizes, int $price): array {
    $out = [];
    foreach ($colors as $c) {
        foreach ($sizes as $s) {
            $out[] = ['name' => "{$c} / {$s}", 'price' => $price, 'attributes' => ['color' => $c, 'size' => $s]];
        }
    }
    return $out;
};

/** Phiên bản theo dòng xe */
$byModel = static function (array $fitModels, int $price, ?string $color = null): array {
    return array_map(static fn(string $m): array => [
        'name' => $color ? "{$m} / {$color}" : $m,
        'price' => $price,
        'attributes' => array_filter(['fit_model' => $m, 'color' => $color]),
    ], $fitModels);
};

/** Phiên bản theo tùy chọn (dung lượng / công suất) */
$options = static function (array $pairs): array {
    $out = [];
    foreach ($pairs as $name => $price) {
        $out[] = ['name' => (string)$name, 'price' => $price, 'attributes' => ['option' => (string)$name]];
    }
    return $out;
};

$img = static fn(string $cat): array => ["accessories/{$cat}.svg"];

$products = [
    // ---------------- Pin & Sạc (3) ----------------
    ['cat' => 3, 'name' => 'Pin LFP xe máy điện 1.5 kWh', 'desc' => 'Pin LFP dùng cho xe máy điện VinFast dòng phổ thông, tuổi thọ cao.',
        'attrs' => ['capacity_kwh' => 1.5, 'voltage' => '48', 'connector' => 'Chân pin xe máy', 'warranty_months' => 36, 'compatible_models' => ['Evo', 'Feliz']],
        'variants' => $options(['1.5 kWh' => 8900000])],
    ['cat' => 3, 'name' => 'Pin LFP xe máy điện 3.5 kWh', 'desc' => 'Pin dung lượng lớn cho quãng đường dài.',
        'attrs' => ['capacity_kwh' => 3.5, 'voltage' => '72', 'connector' => 'Chân pin xe máy', 'warranty_months' => 36, 'compatible_models' => ['Klara', 'Vento', 'Theon']],
        'variants' => $options(['3.5 kWh' => 19500000])],
    ['cat' => 3, 'name' => 'Bộ sạc treo tường ô tô', 'desc' => 'Wallbox sạc AC tại nhà, hỗ trợ hẹn giờ qua ứng dụng.',
        'attrs' => ['power_kw' => 7.4, 'voltage' => '220-380', 'connector' => 'Type 2', 'warranty_months' => 24, 'compatible_models' => ['VF 3', 'VF 5', 'VF 6', 'VF 7', 'VF 8', 'VF 9']],
        'variants' => $options(['7.4 kW (1 pha)' => 13900000, '11 kW (3 pha)' => 18900000])],
    ['cat' => 3, 'name' => 'Bộ sạc di động ô tô', 'desc' => 'Sạc di động cắm ổ điện gia dụng, kèm túi đựng.',
        'attrs' => ['power_kw' => 2.2, 'voltage' => '220', 'connector' => 'Ổ cắm gia dụng', 'warranty_months' => 12, 'compatible_models' => ['VF 3', 'VF 5', 'VF 6', 'VF 7', 'VF 8', 'VF 9']],
        'variants' => $options(['Cáp 5 m' => 6500000, 'Cáp 8 m' => 7200000])],
    ['cat' => 3, 'name' => 'Sạc nhanh xe máy điện', 'desc' => 'Bộ sạc nhanh rút ngắn 40% thời gian sạc.',
        'attrs' => ['power_kw' => 0.6, 'voltage' => '220', 'connector' => 'Ổ cắm gia dụng', 'warranty_months' => 12, 'compatible_models' => ['Evo', 'Feliz', 'Klara', 'Vento', 'Theon']],
        'variants' => $options(['Tiêu chuẩn' => 1290000, 'Kèm giá treo' => 1490000])],
    ['cat' => 3, 'name' => 'Cáp sạc Type 2 dự phòng', 'desc' => 'Cáp sạc Type 2 - Type 2 dùng tại trạm AC công cộng.',
        'attrs' => ['power_kw' => 22, 'connector' => 'Type 2', 'warranty_months' => 12, 'compatible_models' => ['VF 5', 'VF 6', 'VF 7', 'VF 8', 'VF 9']],
        'variants' => $options(['5 m' => 3200000, '7.5 m' => 3900000])],
    ['cat' => 3, 'name' => 'Bộ chuyển đổi CCS2', 'desc' => 'Adapter sạc nhanh DC chuẩn CCS2.',
        'attrs' => ['power_kw' => 150, 'connector' => 'CCS2', 'warranty_months' => 12, 'compatible_models' => ['VF 6', 'VF 7', 'VF 8', 'VF 9']],
        'variants' => $options(['Tiêu chuẩn' => 4500000])],
    ['cat' => 3, 'name' => 'Ổ cắm sạc chuyên dụng', 'desc' => 'Ổ cắm 32A chịu tải cao cho sạc tại nhà, kèm aptomat.',
        'attrs' => ['power_kw' => 7.4, 'voltage' => '220', 'connector' => 'Ổ cắm gia dụng', 'warranty_months' => 24, 'compatible_models' => ['VF 3', 'VF 5', 'VF 6']],
        'variants' => $options(['Bộ ổ cắm' => 1850000, 'Bộ ổ cắm + lắp đặt' => 2650000])],

    // ---------------- Thời trang (4) ----------------
    ['cat' => 4, 'name' => 'Áo polo VinFast', 'desc' => 'Áo polo cotton thoáng mát, logo thêu ngực trái.',
        'attrs' => ['material' => 'Cotton 95%, Spandex 5%', 'gender' => 'Unisex', 'care' => 'Giặt máy nước lạnh, không sấy nóng.'],
        'variants' => $apparel(['Xanh navy', 'Trắng'], ['S', 'M', 'L', 'XL'], 450000)],
    ['cat' => 4, 'name' => 'Áo thun Electric Future', 'desc' => 'Áo thun in hình xe điện phong cách tối giản.',
        'attrs' => ['material' => 'Cotton 100%', 'gender' => 'Unisex', 'care' => 'Lộn trái khi giặt.'],
        'variants' => $apparel(['Đen', 'Trắng', 'Xanh dương'], ['S', 'M', 'L', 'XL'], 290000)],
    ['cat' => 4, 'name' => 'Áo khoác gió VinFast', 'desc' => 'Áo khoác chống nước nhẹ, có mũ trùm.',
        'attrs' => ['material' => 'Polyester chống thấm', 'gender' => 'Unisex', 'care' => 'Giặt tay, phơi nơi thoáng mát.'],
        'variants' => $apparel(['Đen', 'Xanh navy'], ['M', 'L', 'XL'], 790000)],
    ['cat' => 4, 'name' => 'Mũ bảo hiểm 3/4 VinFast', 'desc' => 'Mũ bảo hiểm 3/4 đạt chuẩn QCVN, kính chắn gió.',
        'attrs' => ['material' => 'Nhựa ABS, lót EPS', 'gender' => 'Unisex', 'care' => 'Lau bằng khăn ẩm.'],
        'variants' => $apparel(['Trắng', 'Đen nhám', 'Đỏ'], ['M', 'L'], 520000)],
    ['cat' => 4, 'name' => 'Mũ bảo hiểm nửa đầu', 'desc' => 'Mũ nửa đầu nhẹ cho di chuyển trong phố.',
        'attrs' => ['material' => 'Nhựa ABS', 'gender' => 'Unisex'],
        'variants' => $apparel(['Trắng', 'Xanh mint', 'Hồng'], ['Free size'], 250000)],
    ['cat' => 4, 'name' => 'Mũ lưỡi trai VinFast', 'desc' => 'Mũ lưỡi trai thêu logo, khóa điều chỉnh.',
        'attrs' => ['material' => 'Kaki', 'gender' => 'Unisex'],
        'variants' => $apparel(['Đen', 'Trắng', 'Be'], ['Free size'], 190000)],
    ['cat' => 4, 'name' => 'Áo polo nữ VinFast', 'desc' => 'Polo dáng ôm cho nữ.',
        'attrs' => ['material' => 'Cotton cá sấu', 'gender' => 'Nữ'],
        'variants' => $apparel(['Trắng', 'Hồng phấn'], ['S', 'M', 'L'], 450000)],
    ['cat' => 4, 'name' => 'Áo mưa bộ VinFast', 'desc' => 'Áo mưa bộ hai lớp, phản quang ban đêm.',
        'attrs' => ['material' => 'PVC phủ PU', 'gender' => 'Unisex'],
        'variants' => $apparel(['Xanh navy', 'Ghi'], ['M', 'L', 'XL'], 380000)],
    ['cat' => 4, 'name' => 'Túi tote VinFast', 'desc' => 'Túi vải canvas tái chế.',
        'attrs' => ['material' => 'Canvas tái chế', 'gender' => 'Unisex'],
        'variants' => $apparel(['Be', 'Đen'], ['Free size'], 220000)],

    // ---------------- Phụ kiện xe (5) ----------------
    ['cat' => 5, 'name' => 'Thảm lót sàn 5D', 'desc' => 'Thảm 5D ôm khít sàn xe, chống nước, dễ vệ sinh.',
        'attrs' => ['material' => 'Da PU + cao su', 'needs_install' => false, 'warranty_months' => 12, 'compatible_models' => ['VF 3', 'VF 5', 'VF 6', 'VF 7', 'VF 8']],
        'variants' => array_merge($byModel(['VF 3', 'VF 5', 'VF 6'], 1800000, 'Đen'), $byModel(['VF 7', 'VF 8'], 2300000, 'Đen'), $byModel(['VF 8'], 2300000, 'Be'))],
    ['cat' => 5, 'name' => 'Camera hành trình 4K', 'desc' => 'Camera trước sau, kết nối Wi-Fi, cảnh báo va chạm.',
        'attrs' => ['material' => 'Nhựa ABS', 'needs_install' => true, 'warranty_months' => 24, 'compatible_models' => ['VF 5', 'VF 6', 'VF 7', 'VF 8', 'VF 9']],
        'variants' => $byModel(['VF 5', 'VF 6', 'VF 7', 'VF 8', 'VF 9'], 3990000)],
    ['cat' => 5, 'name' => 'Giá nóc nhôm', 'desc' => 'Thanh giá nóc nhôm định hình, tải trọng 75 kg.',
        'attrs' => ['material' => 'Hợp kim nhôm', 'needs_install' => true, 'warranty_months' => 24, 'compatible_models' => ['VF 6', 'VF 7', 'VF 8', 'VF 9']],
        'variants' => array_merge($byModel(['VF 6', 'VF 7'], 4500000, 'Bạc'), $byModel(['VF 8', 'VF 9'], 5200000, 'Đen'))],
    ['cat' => 5, 'name' => 'Phim cách nhiệt cao cấp', 'desc' => 'Phim cách nhiệt cản 99% tia UV, dán trọn bộ kính.',
        'attrs' => ['material' => 'Phim gốm Nano', 'needs_install' => true, 'warranty_months' => 60, 'compatible_models' => ['VF 3', 'VF 5', 'VF 6', 'VF 7', 'VF 8', 'VF 9']],
        'variants' => array_merge($byModel(['VF 3', 'VF 5'], 5500000), $byModel(['VF 6', 'VF 7'], 7500000), $byModel(['VF 8', 'VF 9'], 9500000))],
    ['cat' => 5, 'name' => 'Bọc vô lăng da', 'desc' => 'Bọc vô lăng da khâu tay, chống trượt.',
        'attrs' => ['material' => 'Da Nappa', 'needs_install' => false, 'warranty_months' => 6, 'compatible_models' => ['VF 3', 'VF 5', 'VF 6', 'VF 7', 'VF 8', 'VF 9']],
        'variants' => array_merge($byModel(['VF 3', 'VF 5', 'VF 6'], 650000, 'Đen'), $byModel(['VF 7', 'VF 8', 'VF 9'], 650000, 'Nâu'))],
    ['cat' => 5, 'name' => 'Nước hoa ô tô VinFast', 'desc' => 'Sáp thơm gắn cửa gió, hương gỗ nhẹ.',
        'attrs' => ['material' => 'Sáp thơm', 'needs_install' => false, 'warranty_months' => 0, 'compatible_models' => ['VF 3', 'VF 5', 'VF 6', 'VF 7', 'VF 8', 'VF 9']],
        'variants' => [['name' => 'Gỗ đàn hương', 'price' => 350000, 'attributes' => ['fit_model' => 'VF 3']], ['name' => 'Cam bergamot', 'price' => 350000, 'attributes' => ['fit_model' => 'VF 3']]]],
    ['cat' => 5, 'name' => 'Lót cốp sau', 'desc' => 'Khay lót cốp chống tràn, viền cao 5 cm.',
        'attrs' => ['material' => 'TPE', 'needs_install' => false, 'warranty_months' => 12, 'compatible_models' => ['VF 5', 'VF 6', 'VF 7', 'VF 8', 'VF 9']],
        'variants' => $byModel(['VF 5', 'VF 6', 'VF 7', 'VF 8', 'VF 9'], 1200000, 'Đen')],
    ['cat' => 5, 'name' => 'Baga sau xe máy điện', 'desc' => 'Baga thép sơn tĩnh điện, lắp thêm thùng.',
        'attrs' => ['material' => 'Thép sơn tĩnh điện', 'needs_install' => true, 'warranty_months' => 12, 'compatible_models' => ['Evo', 'Feliz', 'Klara']],
        'variants' => $byModel(['Evo', 'Feliz', 'Klara'], 450000, 'Đen')],
    ['cat' => 5, 'name' => 'Thùng sau xe máy điện 30L', 'desc' => 'Thùng chứa đồ 30 lít, khóa chống trộm.',
        'attrs' => ['material' => 'Nhựa PP', 'needs_install' => true, 'warranty_months' => 12, 'compatible_models' => ['Evo', 'Feliz', 'Klara', 'Vento', 'Theon']],
        'variants' => [['name' => 'Đen', 'price' => 890000, 'attributes' => ['fit_model' => 'Evo', 'color' => 'Đen']], ['name' => 'Trắng', 'price' => 890000, 'attributes' => ['fit_model' => 'Evo', 'color' => 'Trắng']]]],
    ['cat' => 5, 'name' => 'Bạt phủ xe ô tô', 'desc' => 'Bạt phủ chống nắng mưa, lớp lót nỉ chống xước.',
        'attrs' => ['material' => 'Vải Oxford tráng bạc', 'needs_install' => false, 'warranty_months' => 6, 'compatible_models' => ['VF 3', 'VF 5', 'VF 6', 'VF 7', 'VF 8', 'VF 9']],
        'variants' => array_merge($byModel(['VF 3', 'VF 5'], 790000, 'Bạc'), $byModel(['VF 6', 'VF 7', 'VF 8', 'VF 9'], 990000, 'Bạc'))],
];

// Gán ảnh placeholder theo category
$slugByCat = array_column($categories, 'slug', 'categoryID');
foreach ($products as &$p) {
    $p['images'] = $img($slugByCat[$p['cat']]);
}
unset($p);

return ['categories' => $categories, 'products' => $products, 'models' => $models];
