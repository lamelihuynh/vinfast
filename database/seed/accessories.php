<?php

/**
 * database/seed/accessories.php — Catalog phụ kiện (dữ liệu thật)
 *
 * Nguồn: shop.vinfastauto.com (/vn_en/<SKU>.html), lấy ngày 2026-11-20. Giá là giá niêm yết lúc lấy, đã làm tròn
 * theo số trên shop; giá có thể đã thay đổi. 'sku' = mã sản phẩm trên shop (xem trang gốc để đối chiếu).
 * 3 sản phẩm Pin & Sạc ghi "Giá tham khảo" lấy từ nhà bán lẻ bên thứ ba / bài báo (shop chính hãng không bán riêng
 * pin và sạc xe máy) — độ tin cậy thấp, chỉ dùng làm dữ liệu mẫu.
 * Ảnh: public/images/accessories/<slug-danh-muc>/*.webp (tải từ trang sản phẩm, đổi sang WebP rộng 800px) — ảnh thuộc
 * VinFast / nhà bán lẻ, chỉ dùng cho môi trường học tập/local.
 * Tồn kho: seed.php sinh ngẫu nhiên (cố định seed), không phải tồn kho thật.
 *
 * Mỗi category tự khai báo:
 *   dynamicAttributes : thuộc tính cấp sản phẩm (products.attributes)
 *   variantAttributes : thuộc tính phân biệt phiên bản (variants[].attributes) — mỗi phiên bản có tồn kho riêng
 * Quy ước: thời trang tính MÀU × SIZE vào phiên bản.
 *
 * Dùng bởi seed.php (catalog small) và generate.php (sinh dữ liệu lớn theo cùng schema).
 */

$models = ['VF 3', 'VF 5', 'VF 6', 'VF 7', 'VF 8', 'VF 9', 'Evo', 'Evo Grand', 'Feliz', 'Klara', 'Vento', 'Theon'];
$cars = ['VF 3', 'VF 5', 'VF 6', 'VF 7', 'VF 8', 'VF 9'];
$scooters = ['Evo', 'Evo Grand', 'Feliz', 'Klara', 'Vento', 'Theon'];

$sku = ['key' => 'sku', 'label' => 'Mã sản phẩm', 'type' => 'string', 'required' => false, 'filterable' => false];

$categories = [
    [
        'categoryID' => 3,
        'name' => 'Pin & Sạc',
        'slug' => 'pin-sac',
        'description' => 'Pin, bộ sạc và thiết bị sạc cho xe điện VinFast',
        'kind' => 'accessory',
        'dynamicAttributes' => [
            $sku,
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
        'description' => 'Áo, mũ bảo hiểm, áo mưa và đồ thời trang VinFast',
        'kind' => 'accessory',
        'dynamicAttributes' => [
            $sku,
            ['key' => 'material', 'label' => 'Chất liệu', 'type' => 'string', 'required' => false, 'filterable' => false],
            ['key' => 'gender', 'label' => 'Giới tính', 'type' => 'enum', 'required' => false, 'filterable' => true, 'options' => ['Nam', 'Nữ', 'Unisex']],
            ['key' => 'care', 'label' => 'Hướng dẫn bảo quản', 'type' => 'text', 'required' => false, 'filterable' => false],
        ],
        'variantAttributes' => [
            ['key' => 'color', 'label' => 'Màu', 'type' => 'string', 'required' => true],
            ['key' => 'size', 'label' => 'Size', 'type' => 'enum', 'required' => true, 'options' => ['S', 'M', 'L', 'XL', '2XL', 'Free size']],
        ],
    ],
    [
        'categoryID' => 5,
        'name' => 'Phụ kiện xe',
        'slug' => 'phu-kien-xe',
        'description' => 'Thảm, camera, giá nóc và phụ kiện lắp trên xe',
        'kind' => 'accessory',
        'dynamicAttributes' => [
            $sku,
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

/** Phiên bản theo tùy chọn */
$options = static function (array $pairs): array {
    $out = [];
    foreach ($pairs as $name => $price) {
        $out[] = ['name' => (string)$name, 'price' => $price, 'attributes' => ['option' => (string)$name]];
    }
    return $out;
};

/** Ảnh: 'pin-sac', ['bo-sac-...-1', ...] -> accessories/pin-sac/bo-sac-...-1.webp */
$img = static fn(string $cat, array $files): array => array_map(static fn(string $f): string => "accessories/{$cat}/{$f}.webp", $files);

$apparelSizes = ['M', 'L', 'XL', '2XL'];
$free = ['Free size'];
$ref = 'Giá tham khảo từ nhà bán lẻ bên thứ ba, không phải giá niêm yết chính hãng.';

$products = [
    // ---------------- Pin & Sạc (8) ----------------
    ['cat' => 3, 'name' => 'Bộ sạc treo tường AC 7,4 kW',
        'desc' => 'Bộ sạc AC treo tường 1 pha cho ô tô điện VinFast, nhiều chế độ bảo vệ, có thể tự lắp theo hướng dẫn. Nhiệt độ hoạt động -40 đến +60°C. VF 3 cần bộ chuyển đổi.',
        'attrs' => ['sku' => 'CHG00009020', 'power_kw' => 7.4, 'voltage' => '230 (1 pha)', 'warranty_months' => 24, 'compatible_models' => $cars],
        'variants' => $options(['Tiêu chuẩn' => 10800000]),
        'images' => $img('pin-sac', ['bo-sac-treo-tuong-ac-7-4-kw-1', 'bo-sac-treo-tuong-ac-7-4-kw-2'])],
    ['cat' => 3, 'name' => 'Bộ sạc treo tường AC 11 kW',
        'desc' => 'Bộ sạc AC treo tường 3 pha 11 kW cho ô tô điện VinFast, bảo hành tận nơi. VF 3 cần bộ chuyển đổi.',
        'attrs' => ['sku' => 'EEP30000011', 'power_kw' => 11, 'voltage' => '400 (3 pha)', 'warranty_months' => 24, 'compatible_models' => $cars],
        'variants' => $options(['Tiêu chuẩn' => 11781818]),
        'images' => $img('pin-sac', ['bo-sac-treo-tuong-ac-11-kw-1', 'bo-sac-treo-tuong-ac-11-kw-2'])],
    ['cat' => 3, 'name' => 'Bộ sạc di động ô tô điện 2,2 kW',
        'desc' => 'Bộ sạc AC di động 2,2 kW cắm ổ điện gia dụng (phích E/F), súng sạc Type 2 đạt IP54. Sạc 10%–70% khoảng 11 giờ. VF 3 cần bộ chuyển đổi.',
        'attrs' => ['sku' => 'ACS10000039', 'power_kw' => 2.2, 'voltage' => '230', 'connector' => 'Type 2', 'compatible_models' => $cars],
        'variants' => $options(['Tiêu chuẩn' => 3927273]),
        'images' => $img('pin-sac', ['bo-sac-di-dong-o-to-dien-2-2-kw-1', 'bo-sac-di-dong-o-to-dien-2-2-kw-2'])],
    ['cat' => 3, 'name' => 'Bộ sạc tại nhà 3 kW cho VF 3',
        'desc' => 'Bộ sạc tại nhà dành riêng cho VF 3, cổng CCS2, đạt IP41/IK08. Sạc 10%–70% dưới 5 giờ. Bán qua showroom.',
        'attrs' => ['sku' => 'EEP69072176AB', 'power_kw' => 3, 'connector' => 'CCS2', 'compatible_models' => ['VF 3']],
        'variants' => $options(['Tiêu chuẩn' => 8836365]),
        'images' => $img('pin-sac', ['home-charger-3kw-sac-tai-nha-vf-3-1'])],
    ['cat' => 3, 'name' => 'Pin LFP 72V 48Ah cho xe máy điện',
        'desc' => 'Pin LFP 72V 48Ah dùng cho Evo200, Feliz S, Klara S2, Vento S, Theon S; quãng đường công bố khoảng 160 km. ' . $ref,
        'attrs' => ['capacity_kwh' => 3.46, 'voltage' => '72', 'connector' => 'Chân pin xe máy', 'compatible_models' => ['Evo', 'Feliz', 'Klara', 'Vento', 'Theon']],
        'variants' => $options(['Tiêu chuẩn' => 11500000, 'Kèm bảo hành thêm 1 năm' => 13500000]),
        'images' => $img('pin-sac', ['pin-lfp-72v-48ah-cho-evo200-feliz-s-klara-s2-vento-s-theon-s-1'])],
    ['cat' => 3, 'name' => 'Pin LFP 72V 45Ah cho Feliz',
        'desc' => 'Pin thay thế LFP 72V 45Ah (3.240 Wh) cho Feliz / Feliz S, sạc 5–6 giờ, nặng 18–20 kg, quãng đường công bố 110–120 km. ' . $ref,
        'attrs' => ['capacity_kwh' => 3.24, 'voltage' => '72', 'connector' => 'Chân pin xe máy', 'warranty_months' => 36, 'compatible_models' => ['Feliz']],
        'variants' => $options(['Tiêu chuẩn' => 9500000]),
        'images' => $img('pin-sac', ['pin-72v-45ah-lfp-xe-may-dien-vinfast-feliz-1'])],
    ['cat' => 3, 'name' => 'Pin LFP xe máy điện VinFast (thay mới)',
        'desc' => 'Giá tham khảo thay pin LFP mới tại đại lý cho Evo200, Feliz S, Klara S2, Vento S, Theon S (theo bài viết FPT Shop 2025). Tuổi thọ công bố 800–1000 chu kỳ.',
        'attrs' => ['voltage' => '72', 'connector' => 'Chân pin xe máy', 'compatible_models' => ['Evo', 'Feliz', 'Klara', 'Vento', 'Theon']],
        'variants' => $options(['Thay tại đại lý' => 8659000]),
        'images' => ['accessories/pin-sac.svg']],
    ['cat' => 3, 'name' => 'Bộ sạc di động 3,5 kW',
        'desc' => 'Bộ sạc di động 3,5 kW cho ô tô điện. ' . $ref,
        'attrs' => ['power_kw' => 3.5, 'compatible_models' => ['VF 5', 'VF 6', 'VF 7', 'VF 8', 'VF 9']],
        'variants' => $options(['Tiêu chuẩn' => 6000000]),
        'images' => $img('pin-sac', ['bo-sac-di-dong-3-5-kw-1'])],

    // ---------------- Thời trang (21) ----------------
    ['cat' => 4, 'name' => 'Áo polo VinFast màu trắng',
        'desc' => 'Áo polo cotton trắng, logo VINFAST đen ở ngực, họa tiết in chuyển nhiệt.',
        'attrs' => ['sku' => 'VINFASTPOLOWHITE', 'material' => '97% cotton, 3% spandex', 'gender' => 'Unisex'],
        'variants' => $apparel(['Trắng'], $apparelSizes, 384999),
        'images' => $img('thoi-trang', ['ao-polo-vinfast-mau-trang-1'])],
    ['cat' => 4, 'name' => 'Áo polo VinFast Boundless Together màu đen',
        'desc' => 'Áo polo đen thuộc bộ sưu tập Boundless Together.',
        'attrs' => ['sku' => 'VINFASTPOLOBLACK', 'gender' => 'Unisex'],
        'variants' => $apparel(['Đen'], $apparelSizes, 384999),
        'images' => $img('thoi-trang', ['ao-polo-vinfast-boundless-together-mau-den-1'])],
    ['cat' => 4, 'name' => 'Áo polo nam VinFast',
        'desc' => 'Áo polo nam VinFast.',
        'attrs' => ['sku' => 'MALEPOLOSHIRT', 'gender' => 'Nam'],
        'variants' => $apparel(['Theo mẫu'], $apparelSizes, 350000),
        'images' => $img('thoi-trang', ['ao-polo-nam-vinfast-1'])],
    ['cat' => 4, 'name' => 'Áo polo nữ VinFast',
        'desc' => 'Áo polo nữ VinFast.',
        'attrs' => ['sku' => 'FEMALEPOLOSHIRT', 'gender' => 'Nữ'],
        'variants' => $apparel(['Theo mẫu'], ['S', 'M', 'L', 'XL'], 300000),
        'images' => $img('thoi-trang', ['ao-polo-nu-vinfast-1'])],
    ['cat' => 4, 'name' => 'Áo polo VF 5 thêu hoa',
        'desc' => 'Áo polo thêu hoa theo chủ đề VF 5.',
        'attrs' => ['sku' => 'VF5PLUSEMBROIDEREDPOLO', 'gender' => 'Unisex'],
        'variants' => $apparel(['Theo mẫu'], $apparelSizes, 533001),
        'images' => $img('thoi-trang', ['ao-polo-vinfast-vf-5-theu-hoa-1'])],
    ['cat' => 4, 'name' => 'Áo thun VF 5 thêu hoa',
        'desc' => 'Áo thun thêu hoa theo chủ đề VF 5.',
        'attrs' => ['sku' => 'VF5PLUSEMBROIDEREDTSHIRT', 'gender' => 'Unisex'],
        'variants' => $apparel(['Theo mẫu'], $apparelSizes, 478000),
        'images' => $img('thoi-trang', ['ao-thun-vinfast-vf-5-theu-hoa-1'])],
    ['cat' => 4, 'name' => 'Áo thun VF 7',
        'desc' => 'Áo thun đen theo chủ đề VF 7.',
        'attrs' => ['sku' => 'VF7TSHIRT', 'gender' => 'Unisex'],
        'variants' => $apparel(['Đen'], $apparelSizes, 647801),
        'images' => $img('thoi-trang', ['ao-thun-vf-7-1'])],
    ['cat' => 4, 'name' => 'Áo thun VF 7 Special',
        'desc' => 'Áo thun phiên bản đặc biệt VF 7.',
        'attrs' => ['sku' => 'VF7SPECIALTSHIRT', 'gender' => 'Unisex'],
        'variants' => $apparel(['Theo mẫu'], $apparelSizes, 647801),
        'images' => $img('thoi-trang', ['ao-thun-vf-7-special-1'])],
    ['cat' => 4, 'name' => 'Mũ lưỡi trai VinFast',
        'desc' => 'Mũ lưỡi trai logo VinFast.',
        'attrs' => ['sku' => 'ACS30000028', 'gender' => 'Unisex'],
        'variants' => $apparel(['Theo mẫu'], $free, 400341),
        'images' => $img('thoi-trang', ['mu-luoi-trai-vinfast-1'])],
    ['cat' => 4, 'name' => 'Mũ lưỡi trai VF 7',
        'desc' => 'Mũ lưỡi trai theo chủ đề VF 7.',
        'attrs' => ['sku' => 'ACS30000104', 'gender' => 'Unisex'],
        'variants' => $apparel(['Theo mẫu'], $free, 345494),
        'images' => $img('thoi-trang', ['mu-luoi-trai-vf-7-1'])],
    ['cat' => 4, 'name' => 'Mũ lưỡi trai VF 5 thêu hoa',
        'desc' => 'Mũ lưỡi trai thêu hoa theo chủ đề VF 5.',
        'attrs' => ['sku' => 'VF5FLORALEMBROIDEREDCAP', 'gender' => 'Unisex'],
        'variants' => $apparel(['Theo mẫu'], $free, 330999),
        'images' => $img('thoi-trang', ['mu-luoi-trai-vf-5-theu-hoa-1'])],
    ['cat' => 4, 'name' => 'Mũ lưỡi trai VinFast – Mãnh liệt tinh thần Việt Nam',
        'desc' => 'Mũ lưỡi trai thuộc bộ sưu tập "Mãnh liệt tinh thần Việt Nam".',
        'attrs' => ['sku' => 'ACS10000239', 'gender' => 'Unisex'],
        'variants' => $apparel(['Theo mẫu'], $free, 816228),
        'images' => $img('thoi-trang', ['mu-luoi-trai-vinfast-manh-liet-tinh-than-viet-nam-1'])],
    ['cat' => 4, 'name' => 'Túi tote thương hiệu VinFast',
        'desc' => 'Túi tote in thương hiệu VinFast.',
        'attrs' => ['sku' => 'VINFASTTOTEBAG', 'gender' => 'Unisex'],
        'variants' => $apparel(['Theo mẫu'], $free, 378000),
        'images' => $img('thoi-trang', ['tui-tote-thuong-hieu-vinfast-1'])],
    ['cat' => 4, 'name' => 'Túi tote VinFast Boundless Together',
        'desc' => 'Túi tote thời trang thuộc bộ sưu tập Boundless Together.',
        'attrs' => ['sku' => 'ACS40000108', 'gender' => 'Unisex'],
        'variants' => $apparel(['Theo mẫu'], $free, 378000),
        'images' => $img('thoi-trang', ['tui-tote-thoi-trang-vinfast-boundless-together-1'])],
    ['cat' => 4, 'name' => 'Túi tote VinFast – Mãnh liệt tinh thần Việt Nam',
        'desc' => 'Túi tote thuộc bộ sưu tập "Mãnh liệt tinh thần Việt Nam".',
        'attrs' => ['sku' => 'ACS10000241', 'gender' => 'Unisex'],
        'variants' => $apparel(['Theo mẫu'], $free, 583021),
        'images' => $img('thoi-trang', ['tui-tote-vinfast-manh-liet-tinh-than-viet-nam-1'])],
    ['cat' => 4, 'name' => 'Khăn bandana VinFast – Việt Nam',
        'desc' => 'Khăn bandana VinFast chủ đề Việt Nam.',
        'attrs' => ['sku' => 'ACS10000240', 'gender' => 'Unisex'],
        'variants' => $apparel(['Theo mẫu'], $free, 332537),
        'images' => $img('thoi-trang', ['khan-bandana-vinfast-viet-nam-1'])],
    ['cat' => 4, 'name' => 'Khăn lụa cao cấp VinFast',
        'desc' => 'Khăn lụa cao cấp VinFast.',
        'attrs' => ['sku' => 'VINFASTSILKSCARF', 'material' => 'Lụa', 'gender' => 'Nữ'],
        'variants' => $apparel(['Theo mẫu'], $free, 3489999),
        'images' => $img('thoi-trang', ['khan-lua-cao-cap-vinfast-1'])],
    ['cat' => 4, 'name' => 'Mũ bảo hiểm VinFast',
        'desc' => 'Mũ bảo hiểm đi hằng ngày, kính chắn tháo rời, có lỗ thông gió. Vỏ ABS, lót EPS, nặng 660 ± 30 g, đạt QCVN 2:2021/BKHCN.',
        'attrs' => ['sku' => 'VINFASTHELMET', 'material' => 'Vỏ ABS, lót EPS', 'gender' => 'Unisex', 'care' => 'Lau bằng khăn ẩm, không dùng dung môi.'],
        'variants' => $apparel(['Đen nhám', 'Trắng bóng', 'Xanh olive bóng', 'Xám bóng'], ['L', 'XL'], 459000),
        'images' => $img('thoi-trang', ['mu-bao-hiem-vinfast-1', 'mu-bao-hiem-vinfast-2'])],
    ['cat' => 4, 'name' => 'Mũ bảo hiểm siêu nhẹ an toàn',
        'desc' => 'Mũ bảo hiểm đúc liền khối siêu nhẹ (dưới 350 g), vỏ PC lót EPS, 11 màu theo màu xe máy điện VinFast.',
        'attrs' => ['sku' => 'UltraLightSafetyHelmet', 'material' => 'Vỏ PC, lót EPS', 'gender' => 'Unisex'],
        'variants' => $apparel(['Theo màu xe'], $free, 472001),
        'images' => $img('thoi-trang', ['mu-bao-hiem-sieu-nhe-an-toan-1', 'mu-bao-hiem-sieu-nhe-an-toan-2'])],
    ['cat' => 4, 'name' => 'Áo mưa cánh dơi hai mũ',
        'desc' => 'Áo mưa cánh dơi cho 2 người, dùng khi đi xe máy điện.',
        'attrs' => ['sku' => 'ACS40000014', 'gender' => 'Unisex'],
        'variants' => $apparel(['Theo mẫu'], $free, 200001),
        'images' => $img('thoi-trang', ['ao-mua-canh-doi-hai-mu-1'])],
    ['cat' => 4, 'name' => 'Áo mưa cánh dơi một mũ',
        'desc' => 'Áo mưa cánh dơi cho 1 người, dùng khi đi xe máy điện.',
        'attrs' => ['sku' => 'ACS40000013', 'gender' => 'Unisex'],
        'variants' => $apparel(['Theo mẫu'], $free, 180000),
        'images' => $img('thoi-trang', ['ao-mua-canh-doi-mot-mu-1'])],

    // ---------------- Phụ kiện xe (15) ----------------
    ['cat' => 5, 'name' => 'Bộ quây bảo vệ xe Evo Grand',
        'desc' => 'Bộ quây 5 chi tiết bằng ống thép mạ crom, lắp vào điểm bắt vít nguyên bản. Giá chưa gồm công lắp.',
        'attrs' => ['sku' => 'ACS40000053', 'material' => 'Ống thép mạ crom', 'needs_install' => true, 'compatible_models' => ['Evo Grand']],
        'variants' => $byModel(['Evo Grand'], 1412557),
        'images' => $img('phu-kien-xe', ['bo-quay-cao-cap-bao-ve-xe-vinfast-evo-grand-1'])],
    ['cat' => 5, 'name' => 'Baga sau cao cấp Evo Grand',
        'desc' => 'Baga sau cao cấp cho Evo Grand.',
        'attrs' => ['sku' => 'ACS40000057', 'needs_install' => true, 'compatible_models' => ['Evo Grand']],
        'variants' => $byModel(['Evo Grand'], 592282),
        'images' => $img('phu-kien-xe', ['baga-sau-cao-cap-xe-vinfast-evo-grand-1'])],
    ['cat' => 5, 'name' => 'Baga trước cao cấp Evo Grand',
        'desc' => 'Baga trước cao cấp cho Evo Grand.',
        'attrs' => ['sku' => 'ACS40000055', 'needs_install' => true, 'compatible_models' => ['Evo Grand']],
        'variants' => $byModel(['Evo Grand'], 518111),
        'images' => $img('phu-kien-xe', ['baga-truoc-cao-cap-xe-vinfast-evo-grand-1'])],
    ['cat' => 5, 'name' => 'Thảm sàn 3D VF 7',
        'desc' => 'Thảm sàn đúc TPE ôm khít sàn VF 7, chống nước, chống trượt. Đạt kiểm định QUATEST 3 và RoHS.',
        'attrs' => ['sku' => 'ACS10000040', 'material' => 'TPE', 'needs_install' => false, 'warranty_months' => 24, 'compatible_models' => ['VF 7']],
        'variants' => $byModel(['VF 7'], 1966195),
        'images' => $img('phu-kien-xe', ['3d-floor-mat-vf-7-tham-san-3d-1'])],
    ['cat' => 5, 'name' => 'Thảm cốp 3D VF 7',
        'desc' => 'Thảm lót cốp đúc khuôn cho VF 7.',
        'attrs' => ['sku' => 'ACS10000041', 'needs_install' => false, 'compatible_models' => ['VF 7']],
        'variants' => $byModel(['VF 7'], 891000),
        'images' => $img('phu-kien-xe', ['3d-trunk-mat-vf-7-tham-cop-3d-1'])],
    ['cat' => 5, 'name' => 'Thảm sàn nhựa 2D VF 8',
        'desc' => 'Bộ thảm sàn nhựa 2D cho VF 8.',
        'attrs' => ['sku' => 'ACS10000007', 'material' => 'Nhựa', 'needs_install' => false, 'compatible_models' => ['VF 8']],
        'variants' => $byModel(['VF 8'], 1900215),
        'images' => $img('phu-kien-xe', ['2d-plastic-floor-mat-vf-8-1'])],
    ['cat' => 5, 'name' => 'Thảm cốp 3D VF 8',
        'desc' => 'Thảm lót cốp đúc khuôn cho VF 8.',
        'attrs' => ['sku' => 'ACS10000010', 'needs_install' => false, 'compatible_models' => ['VF 8']],
        'variants' => $byModel(['VF 8'], 1251000),
        'images' => $img('phu-kien-xe', ['3d-trunk-mat-vf-8-1'])],
    ['cat' => 5, 'name' => 'Thảm sàn 3D VF 9',
        'desc' => 'Bộ thảm sàn đúc 3D cho VF 9.',
        'attrs' => ['sku' => 'FLOORMATS3DVF9', 'needs_install' => false, 'compatible_models' => ['VF 9']],
        'variants' => $byModel(['VF 9'], 3008674),
        'images' => $img('phu-kien-xe', ['3d-floor-mat-vf-9-1'])],
    ['cat' => 5, 'name' => 'Thanh ngang giá nóc VF 8',
        'desc' => 'Thanh ngang giá nóc cho VF 8.',
        'attrs' => ['sku' => 'ACS30000142', 'needs_install' => true, 'compatible_models' => ['VF 8']],
        'variants' => $byModel(['VF 8'], 18360000),
        'images' => $img('phu-kien-xe', ['thanh-ngang-gia-noc-vinfast-vf-8-1'])],
    ['cat' => 5, 'name' => 'Cốp nóc phi thuyền VF 8',
        'desc' => 'Cốp nóc dạng phi thuyền lắp trên giá nóc VF 8.',
        'attrs' => ['sku' => 'ACS30000141', 'needs_install' => true, 'compatible_models' => ['VF 8']],
        'variants' => $byModel(['VF 8'], 6390000),
        'images' => $img('phu-kien-xe', ['cop-noc-phi-thuyen-o-to-vinfast-vf-8-1'])],
    ['cat' => 5, 'name' => 'Bộ thanh ngang giá nóc VF 3',
        'desc' => 'Bộ thanh ngang giá nóc cho VF 3.',
        'attrs' => ['sku' => 'VF3ROOFRACKCROSSBAR', 'needs_install' => true, 'compatible_models' => ['VF 3']],
        'variants' => $byModel(['VF 3'], 1436000),
        'images' => $img('phu-kien-xe', ['bo-thanh-ngang-gia-noc-vf-3-1'])],
    ['cat' => 5, 'name' => 'Camera lùi VF 3',
        'desc' => 'Camera lùi cho VF 3.',
        'attrs' => ['sku' => 'EEP73090003AB', 'needs_install' => true, 'compatible_models' => ['VF 3']],
        'variants' => $byModel(['VF 3'], 2157977),
        'images' => $img('phu-kien-xe', ['camera-lui-vf-3-1'])],
    ['cat' => 5, 'name' => 'Bọc ghế da VF 3',
        'desc' => 'Bộ bọc ghế da nhân tạo cho VF 3.',
        'attrs' => ['sku' => 'VHC69011000AA', 'material' => 'Da nhân tạo', 'needs_install' => true, 'compatible_models' => ['VF 3']],
        'variants' => $byModel(['VF 3'], 6713000),
        'images' => $img('phu-kien-xe', ['boc-ghe-da-vf-3-1'])],
    ['cat' => 5, 'name' => 'Tấm che pin cao áp VF 8',
        'desc' => 'Tấm che bảo vệ pin cao áp gầm xe VF 8.',
        'attrs' => ['sku' => 'EEP31001000', 'needs_install' => true, 'compatible_models' => ['VF 8']],
        'variants' => $byModel(['VF 8'], 8140001),
        'images' => $img('phu-kien-xe', ['vf-8-tam-che-pin-cao-ap-1'])],
    ['cat' => 5, 'name' => 'Rèm che nắng cửa VF 8',
        'desc' => 'Bộ rèm che nắng kính cửa bên cho VF 8.',
        'attrs' => ['sku' => 'ACS10000014', 'needs_install' => false, 'compatible_models' => ['VF 8']],
        'variants' => $byModel(['VF 8'], 495000),
        'images' => $img('phu-kien-xe', ['door-sun-shade-vf-8-1'])],
];

return ['categories' => $categories, 'products' => $products, 'models' => $models];
