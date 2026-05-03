<?php

/**
 * app/controllers/frontend/ProductController.php
 * Owner: Hai Nam
 * Routes: GET /products   GET /products/detail/{id}
 */
class ProductController
{
    public function index(): void
    {
        $q     = trim($_GET['q'] ?? '');
        $cat   = (int)($_GET['cat'] ?? 0);
        $sort  = trim($_GET['sort'] ?? 'default');
        $price = trim($_GET['price'] ?? 'all');
        $range = trim($_GET['range'] ?? 'all');
        $allowedPerPage = [6, 12, 15];
        $pp = (int)($_GET['pp'] ?? 6);
        if (!in_array($pp, $allowedPerPage, true)) {
            $pp = 6;
        }
        $page  = max(1, (int)($_GET['page'] ?? 1));

        $categories = Category::getAll();

        $filters = [];

        if ($q !== '') {
            $filters['search'] = $q;
        }

        if ($cat > 0) {
            $filters['category_id'] = $cat;
        }

        // Convert price range to VND values
        if ($price !== 'all') {
            switch ($price) {
                case 'under300':
                    $filters['price_max'] = 300 * 1000000;
                    break;
                case '300-500':
                    $filters['price_min'] = 300 * 1000000;
                    $filters['price_max'] = 500 * 1000000;
                    break;
                case '500-1000':
                    $filters['price_min'] = 500 * 1000000;
                    $filters['price_max'] = 1000 * 1000000;
                    break;
                case 'over1000':
                    $filters['price_min'] = 1000 * 1000000;
                    break;
            }
        }

        $filters['sort'] = $sort;

        $products = Product::filterAll($filters);

        if ($range !== 'all') {
            $products = array_values(array_filter($products, function (array $p) use ($range): bool {
                $km = Product::extractRangeKm($p['specs'] ?? []);
                if ($range === 'lt200') return $km > 0 && $km < 200;
                if ($range === '200-400') return $km >= 200 && $km <= 400;
                if ($range === 'gt400') return $km > 400;
                return true;
            }));
        }

        $total = count($products);
        $pg = new Pagination($total, $page, $pp);
        $products = array_slice($products, $pg->offset(), $pg->limit());

        $query = [];
        if ($q !== '') $query['q'] = $q;
        if ($cat > 0) $query['cat'] = $cat;
        if ($sort !== 'default') $query['sort'] = $sort;
        if ($pp !== 6) $query['pp'] = $pp;
        if ($price !== 'all') $query['price'] = $price;
        if ($range !== 'all') $query['range'] = $range;
        $baseQuery = http_build_query($query);
        $pageUrl = BASE_URL . 'products?' . ($baseQuery !== '' ? $baseQuery . '&' : '') . 'page=';

        SEO::set('Vehicles', 'Danh sách xe VinFast', 'Vinfast, xe điện, products');

        View::render('frontend/products/index', [
            'products'   => $products,
            'categories' => $categories,
            'q'          => $q,
            'cat'        => $cat,
            'sort'       => $sort,
            'pp'         => $pp,
            'price'      => $price,
            'range'      => $range,
            'pg'         => $pg,
            'pageUrl'    => $pageUrl,
            'total'      => $total,
        ]);
    }

    public function detail($id = 0): void
    {
        $id = (int)$id;
        if ($id <= 0) {
            http_response_code(404);
            include ROOT . '/app/views/frontend/404.php';
            return;
        }
        $product = Product::getById($id);
        if (!$product) {
            http_response_code(404);
            include ROOT . '/app/views/frontend/404.php';
            return;
        }

        SEO::set($product['name'] ?? 'Product Detail', 'Chi tiết xe VinFast', 'vinfast, chi tiết xe');
        View::render('frontend/products/detail', [
            'product' => $product
        ]);
    }

    public function checkout($id = 0): void
    {
        $id = (int)$id;
        if ($id <= 0) {
            http_response_code(404);
            include ROOT . '/app/views/frontend/404.php';
            return;
        }

        $product = Product::getById($id);
        if (!$product) {
            http_response_code(404);
            include ROOT . '/app/views/frontend/404.php';
            return;
        }

        $categoryId = (int)($product['category_id'] ?? 0);

        $specs = is_array($product['specs'] ?? null) ? $product['specs'] : [];
        $exteriorColors = is_array($specs['exterior_colors'] ?? null) ? $specs['exterior_colors'] : [];
        $selectedCode = strtoupper(trim((string)($_POST['color_code'] ?? $_GET['color'] ?? '')));

        $selectedColor = null;
        foreach ($exteriorColors as $row) {
            if (!is_array($row)) {
                continue;
            }

            $code = strtoupper(trim((string)($row['code'] ?? '')));
            if ($code === '' || $code !== $selectedCode) {
                continue;
            }

            $selectedColor = [
                'code' => $code,
                'name' => trim((string)($row['name'] ?? '')),
            ];
            break;
        }

        if ($selectedColor === null && !empty($exteriorColors) && is_array($exteriorColors[0])) {
            $selectedColor = [
                'code' => strtoupper(trim((string)($exteriorColors[0]['code'] ?? ''))),
                'name' => trim((string)($exteriorColors[0]['name'] ?? '')),
                'surcharge' => max(0, (int)($exteriorColors[0]['surcharge'] ?? 0)),
            ];
        }

        if ($selectedColor !== null) {
            $selectedCodeForMatch = strtoupper(trim((string)($selectedColor['code'] ?? '')));
            foreach ($exteriorColors as $row) {
                if (!is_array($row)) {
                    continue;
                }

                $code = strtoupper(trim((string)($row['code'] ?? '')));
                if ($code !== '' && $code === $selectedCodeForMatch) {
                    $selectedColor['surcharge'] = max(0, (int)($row['surcharge'] ?? 0));
                    break;
                }
            }
        }

        $depositAmount = max(0, (int)($specs['deposit_amount'] ?? 15000000));
        $depositNonRefundable = !empty($specs['deposit_non_refundable']) ? 1 : 0;

        $selectedColorCode = strtoupper(trim((string)($selectedColor['code'] ?? '')));
        $buildCheckoutUrl = static function (int $productId, string $colorCode = ''): string {
            $url = BASE_URL . 'products/checkout/' . $productId;
            if ($colorCode !== '') {
                $url .= '?color=' . urlencode($colorCode);
            }
            return $url;
        };

        $provinces = [
            'Hà Nội',
            'Hồ Chí Minh',
            'Đà Nẵng',
            'Hải Phòng',
            'Cần Thơ',
            'An Giang',
            'Bà Rịa - Vũng Tàu',
            'Bắc Giang',
            'Bắc Ninh',
            'Bình Định',
            'Bình Dương',
            'Bình Phước',
            'Bình Thuận',
            'Cà Mau',
            'Đắk Lắk',
            'Đồng Nai',
            'Đồng Tháp',
            'Gia Lai',
            'Hà Nam',
            'Hà Tĩnh',
            'Hải Dương',
            'Hậu Giang',
            'Khánh Hòa',
            'Kiên Giang',
            'Lâm Đồng',
            'Long An',
            'Nam Định',
            'Nghệ An',
            'Ninh Bình',
            'Phú Thọ',
            'Quảng Bình',
            'Quảng Nam',
            'Quảng Ninh',
            'Quảng Trị',
            'Sóc Trăng',
            'Thanh Hóa',
            'Thừa Thiên Huế',
            'Tiền Giang',
            'Vĩnh Long',
            'Vĩnh Phúc',
        ];

        $showrooms = [
            'Hà Nội' => ['VinFast Hà Nội - Tố Hữu', 'VinFast Hà Nội - Phạm Hùng', 'VinFast Hà Nội - Phú Thượng'],
            'Hồ Chí Minh' => ['VinFast HCM - Quận 7', 'VinFast HCM - Bình Thạnh', 'VinFast HCM - Thủ Đức'],
            'Đà Nẵng' => ['VinFast Đà Nẵng - Nguyễn Văn Linh'],
            'Hải Phòng' => ['VinFast Hải Phòng - Lê Hồng Phong'],
            'Cần Thơ' => ['VinFast Cần Thơ - Nguyễn Văn Cừ'],
            'An Giang' => ['VinFast An Giang - Châu Đốc'],
            'Bà Rịa - Vũng Tàu' => ['VinFast Vũng Tàu - Lê Hồng Phong'],
            'Bắc Giang' => ['VinFast Bắc Giang - Nguyễn Văn Cừ'],
            'Bắc Ninh' => ['VinFast Bắc Ninh - Lý Thái Tổ'],
            'Bình Định' => ['VinFast Bình Định - Nguyễn Thái Học'],
            'Bình Dương' => ['VinFast Bình Dương - Thủ Dầu Một'],
            'Bình Phước' => ['VinFast Bình Phước - Đồng Xoài'],
            'Bình Thuận' => ['VinFast Bình Thuận - Phan Thiết'],
            'Cà Mau' => ['VinFast Cà Mau - Nguyễn Hữu Thọ'],
            'Đắk Lắk' => ['VinFast Đắk Lắk - Buôn Ma Thuột'],
            'Đồng Nai' => ['VinFast Đồng Nai - Biên Hòa'],
            'Đồng Tháp' => ['VinFast Đồng Tháp - Cao Lãnh'],
            'Gia Lai' => ['VinFast Gia Lai - Pleiku'],
            'Hà Nam' => ['VinFast Hà Nam - Phủ Lý'],
            'Hà Tĩnh' => ['VinFast Hà Tĩnh - TP Hà Tĩnh'],
            'Hải Dương' => ['VinFast Hải Dương - Nguyễn Lương Bằng'],
            'Hậu Giang' => ['VinFast Hậu Giang - Vị Thanh'],
            'Khánh Hòa' => ['VinFast Khánh Hòa - Nha Trang'],
            'Kiên Giang' => ['VinFast Kiên Giang - Rạch Giá'],
            'Lâm Đồng' => ['VinFast Lâm Đồng - Đà Lạt'],
            'Long An' => ['VinFast Long An - Tân An'],
            'Nam Định' => ['VinFast Nam Định - Trần Hưng Đạo'],
            'Nghệ An' => ['VinFast Nghệ An - Vinh'],
            'Ninh Bình' => ['VinFast Ninh Bình - Trần Hưng Đạo'],
            'Phú Thọ' => ['VinFast Phú Thọ - Việt Trì'],
            'Quảng Bình' => ['VinFast Quảng Bình - Đồng Hới'],
            'Quảng Nam' => ['VinFast Quảng Nam - Tam Kỳ'],
            'Quảng Ninh' => ['VinFast Quảng Ninh - Hạ Long'],
            'Quảng Trị' => ['VinFast Quảng Trị - Đông Hà'],
            'Sóc Trăng' => ['VinFast Sóc Trăng - Sóc Trăng'],
            'Thanh Hóa' => ['VinFast Thanh Hóa - Thanh Hóa'],
            'Thừa Thiên Huế' => ['VinFast Thừa Thiên Huế - Huế'],
            'Tiền Giang' => ['VinFast Tiền Giang - Mỹ Tho'],
            'Vĩnh Long' => ['VinFast Vĩnh Long - Vĩnh Long'],
            'Vĩnh Phúc' => ['VinFast Vĩnh Phúc - Vĩnh Yên'],
        ];

        $defaultName = '';
        $defaultEmail = '';
        if (Auth::check()) {
            $user = (new User())->findById((int)Auth::id());
            if (is_array($user)) {
                $defaultName = trim((string)($user['name'] ?? ''));
                $defaultEmail = trim((string)($user['email'] ?? ''));
            }
        }

        $checkoutOld = is_array($_SESSION['checkout_old'] ?? null) ? $_SESSION['checkout_old'] : [];
        // Keep session until form is fully submitted
        // Don't unset here - session will be cleared after successful payment on step 3

        $formData = [
            'owner_type' => (string)($checkoutOld['owner_type'] ?? 'ca-nhan'),
            'full_name' => (string)($checkoutOld['full_name'] ?? $defaultName),
            'phone' => (string)($checkoutOld['phone'] ?? ''),
            'cccd' => (string)($checkoutOld['cccd'] ?? ''),
            'email' => (string)($checkoutOld['email'] ?? $defaultEmail),
            'province' => (string)($checkoutOld['province'] ?? ''),
            'showroom' => (string)($checkoutOld['showroom'] ?? ''),
            'salesperson' => (string)($checkoutOld['salesperson'] ?? ''),
            'voucher' => (string)($checkoutOld['voucher'] ?? ''),
            'pay_method' => (string)($checkoutOld['pay_method'] ?? 'card-intl'),
            'agree_terms' => (string)($checkoutOld['agree_terms'] ?? ''),
            'variant_name' => (string)($checkoutOld['variant_name'] ?? ''),
            'interior_code' => (string)($checkoutOld['interior_code'] ?? ''),
            'step' => (int)($checkoutOld['step'] ?? 1),
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::verifyCsrf();

            $switchProductId = (int)($_POST['switch_product_id'] ?? 0);
            if ($switchProductId > 0 && $switchProductId !== $id) {
                $switchProduct = Product::getById($switchProductId);
                if ($switchProduct && ($categoryId <= 0 || (int)($switchProduct['category_id'] ?? 0) === $categoryId)) {
                    unset($_SESSION['checkout_old']);
                    header('Location: ' . BASE_URL . 'products/checkout/' . $switchProductId);
                    exit;
                }
            } elseif ($switchProductId > 0 && $switchProductId === $id) {
                // clicking the currently active product should not submit the form —
                // redirect to the same page (GET) to avoid triggering validation.
                unset($_SESSION['checkout_old']);
                header('Location: ' . BASE_URL . 'products/checkout/' . $id);
                exit;
            }

            $formData = [
                'owner_type' => (string)($_POST['owner_type'] ?? 'ca-nhan'),
                'full_name' => trim((string)($_POST['full_name'] ?? '')),
                'phone' => trim((string)($_POST['phone'] ?? '')),
                'cccd' => trim((string)($_POST['cccd'] ?? '')),
                'email' => trim((string)($_POST['email'] ?? '')),
                'province' => trim((string)($_POST['province'] ?? '')),
                'showroom' => trim((string)($_POST['showroom'] ?? '')),
                'salesperson' => trim((string)($_POST['salesperson'] ?? '')),
                'voucher' => trim((string)($_POST['voucher'] ?? '')),
                'pay_method' => trim((string)($_POST['pay_method'] ?? 'card-intl')),
                'agree_terms' => trim((string)($_POST['agree_terms'] ?? '')),
                'variant_name' => trim((string)($_POST['variant_name'] ?? '')),
                'interior_code' => strtoupper(trim((string)($_POST['interior_code'] ?? ''))),
                'step' => (int)($_POST['step'] ?? 3),
            ];

            if (!Auth::check()) {
                $_SESSION['flash'] = 'Vui lòng đăng nhập để hoàn tất đặt cọc.';
                $_SESSION['checkout_old'] = $formData;
                header('Location: ' . BASE_URL . 'auth/login');
                exit;
            }

            $v = (new Validator($_POST))
                ->required('full_name')
                ->required('phone')
                ->required('email')
                ->email('email')
                ->required('province')
                ->required('showroom');

            if ($v->fails()) {
                $_SESSION['errors'] = $v->errors();
                $_SESSION['checkout_old'] = $formData;
                header('Location: ' . $buildCheckoutUrl($id, $selectedColorCode));
                exit;
            }

            if (!preg_match('/^[0-9+\s.-]{9,15}$/', $formData['phone'])) {
                $_SESSION['errors'] = ['phone' => 'Số điện thoại không hợp lệ.'];
                $_SESSION['checkout_old'] = $formData;
                header('Location: ' . $buildCheckoutUrl($id, $selectedColorCode));
                exit;
            }

            if (!in_array($formData['province'], $provinces, true)) {
                $_SESSION['errors'] = ['province' => 'Tỉnh thành không hợp lệ.'];
                $_SESSION['checkout_old'] = $formData;
                header('Location: ' . $buildCheckoutUrl($id, $selectedColorCode));
                exit;
            }

            $provinceShowrooms = $showrooms[$formData['province']] ?? [];
            if (empty($provinceShowrooms) || !in_array($formData['showroom'], $provinceShowrooms, true)) {
                $_SESSION['errors'] = ['showroom' => 'Vui lòng chọn showroom hợp lệ theo tỉnh thành.'];
                $_SESSION['checkout_old'] = $formData;
                header('Location: ' . $buildCheckoutUrl($id, $selectedColorCode));
                exit;
            }

            $allowedOwnerTypes = ['ca-nhan', 'doanh-nghiep'];
            if (!in_array($formData['owner_type'], $allowedOwnerTypes, true)) {
                $formData['owner_type'] = 'ca-nhan';
            }

            $allowedPayMethods = ['card-intl', 'card-domestic', 'transfer'];
            if (!in_array($formData['pay_method'], $allowedPayMethods, true)) {
                $formData['pay_method'] = 'card-intl';
            }

            if ($formData['agree_terms'] !== '1') {
                $_SESSION['errors'] = ['agree_terms' => 'Vui lòng đồng ý điều khoản trước khi thanh toán đặt cọc.'];
                $_SESSION['checkout_old'] = $formData;
                header('Location: ' . $buildCheckoutUrl($id, $selectedColorCode));
                exit;
            }

            $notePayload = [
                'owner_type' => $formData['owner_type'],
                'full_name' => $formData['full_name'],
                'phone' => $formData['phone'],
                'email' => $formData['email'],
                'cccd' => $formData['cccd'],
                'province' => $formData['province'],
                'showroom' => $formData['showroom'],
                'salesperson' => $formData['salesperson'],
                'voucher' => $formData['voucher'],
                'pay_method' => $formData['pay_method'],
                'color' => [
                    'code' => (string)($selectedColor['code'] ?? ''),
                    'name' => (string)($selectedColor['name'] ?? ''),
                    'surcharge' => max(0, (int)($selectedColor['surcharge'] ?? 0)),
                ],
                'variant_name' => (string)$formData['variant_name'],
                'interior_code' => (string)$formData['interior_code'],
                'deposit_amount' => $depositAmount + max(0, (int)($selectedColor['surcharge'] ?? 0)),
                'deposit_base_amount' => $depositAmount,
                'deposit_non_refundable' => $depositNonRefundable,
                'payment_status' => 'pending_verify',
                'payment_verified_at' => null,
            ];

            $noteJson = json_encode($notePayload, JSON_UNESCAPED_UNICODE);
            if ($noteJson === false) {
                $noteJson = null;
            }

            $created = Order::create((int)Auth::id(), $id, 'deposit', $noteJson);
            if (!$created) {
                $_SESSION['errors'] = ['system' => 'Không thể tạo đơn đặt cọc lúc này, vui lòng thử lại sau.'];
                $_SESSION['checkout_old'] = $formData;
                header('Location: ' . $buildCheckoutUrl($id, $selectedColorCode));
                exit;
            }

            $confirmationOrderId = 'VF-' . strtoupper(dechex((int)$created)) . '-' . strtoupper(substr(md5((string)microtime(true)), 0, 4));
            $_SESSION['checkout_confirmation'] = [
                'orderId' => $confirmationOrderId,
                'orderDbId' => (int)$created,
                'carName' => (string)($product['name'] ?? ''),
                'variantName' => (string)$formData['variant_name'],
                'carPrice' => (float)($product['price'] ?? 0),
                'exteriorColor' => (string)($selectedColor['name'] ?? $selectedColorCode),
                'colorSurcharge' => max(0, (int)($selectedColor['surcharge'] ?? 0)),
                'interiorColor' => (string)$formData['interior_code'],
                'customerName' => (string)$formData['full_name'],
                'phone' => (string)$formData['phone'],
                'email' => (string)$formData['email'],
                'cccd' => (string)$formData['cccd'],
                'province' => (string)$formData['province'],
                'showroom' => (string)$formData['showroom'],
                'payMethod' => (string)$formData['pay_method'],
                'depositAmount' => $depositAmount + max(0, (int)($selectedColor['surcharge'] ?? 0)),
                'depositBaseAmount' => $depositAmount,
                'depositNonRefundable' => $depositNonRefundable,
                'paymentStatus' => 'pending_verify',
                'orderDate' => date('d/m/Y H:i'),
            ];

            header('Location: ' . BASE_URL . 'products/confirmation');
            exit;
        }

        $switchProductsRaw = $categoryId > 0
            ? Product::getByCategory($categoryId, 1, 30)
            : Product::getAll(1, 30);

        if (empty($switchProductsRaw)) {
            $switchProductsRaw = Product::getAll(1, 30);
        }

        $extractModelKey = static function (string $text): string {
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

        $switchProducts = [];
        foreach ($switchProductsRaw as $switchItem) {
            if (!is_array($switchItem)) {
                continue;
            }

            $switchId = (int)($switchItem['id'] ?? 0);
            if ($switchId <= 0) {
                continue;
            }

            $switchImages = is_array($switchItem['images'] ?? null) ? $switchItem['images'] : [];
            $switchSlug = (string)($switchItem['slug'] ?? '');
            $switchName = (string)($switchItem['name'] ?? 'VinFast');
            $switchProducts[] = [
                'id' => $switchId,
                'name' => $switchName,
                'slug' => $switchSlug,
                'model_key' => $extractModelKey($switchSlug !== '' ? $switchSlug : $switchName),
                'price' => (float)($switchItem['price'] ?? 0),
                'image' => is_string($switchImages[0] ?? null) ? (string)$switchImages[0] : '',
                'is_current' => $switchId === $id,
            ];

            if (count($switchProducts) >= 12) {
                break;
            }
        }

        SEO::set('Dat coc ' . ($product['name'] ?? 'VinFast'));
        View::render('frontend/products/checkout', [
            'product' => $product,
            'selectedColor' => $selectedColor,
            'provinces' => $provinces,
            'showrooms' => $showrooms,
            'formData' => $formData,
            'switchProducts' => $switchProducts,
        ]);
    }

    public function confirmation(): void
    {
        $order = is_array($_SESSION['checkout_confirmation'] ?? null) ? $_SESSION['checkout_confirmation'] : [];
        if (empty($order)) {
            header('Location: ' . BASE_URL . 'products');
            exit;
        }

        unset($_SESSION['checkout_confirmation']);
        SEO::set('Đặt cọc thành công', 'Xác nhận đặt cọc VinFast', 'vinfast, đặt cọc thành công');
        View::render('frontend/products/confirmation', [
            'order' => $order,
        ]);
    }
}
