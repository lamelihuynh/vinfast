<?php

/**
 * app/controllers/frontend/OrderController.php
 * Owner: migration
 * Routes: /order/checkout/{id}, /order/confirmation
 */
class OrderController
{
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
        $exteriorColors = is_array($product['exterior_colors'] ?? null) ? $product['exterior_colors'] : [];
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
            $url = BASE_URL . 'order/checkout/' . $productId;
            if ($colorCode !== '') {
                $url .= '?color=' . urlencode($colorCode);
            }
            return $url;
        };

        $locations = [];
        $locationsPath = ROOT . '/config/locations.php';
        if (file_exists($locationsPath)) {
            $locations = include $locationsPath;
        }

        $provinces = is_array($locations['provinces'] ?? null) ? $locations['provinces'] : [];
        $showrooms = is_array($locations['showrooms'] ?? null) ? $locations['showrooms'] : [];

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
                    header('Location: ' . BASE_URL . 'order/checkout/' . $switchProductId);
                    exit;
                }
            } elseif ($switchProductId > 0 && $switchProductId === $id) {
                unset($_SESSION['checkout_old']);
                header('Location: ' . BASE_URL . 'order/checkout/' . $id);
                exit;
            }

            $formData = [
                'full_name' => $this->cleanText($_POST['full_name'] ?? ''),
                'phone' => $this->cleanText($_POST['phone'] ?? ''),
                'cccd' => $this->cleanText($_POST['cccd'] ?? ''),
                'email' => $this->cleanText($_POST['email'] ?? ''),
                'province' => $this->cleanText($_POST['province'] ?? ''),
                'showroom' => $this->cleanText($_POST['showroom'] ?? ''),
                'salesperson' => $this->cleanText($_POST['salesperson'] ?? ''),
                'voucher' => $this->cleanText($_POST['voucher'] ?? ''),
                'pay_method' => $this->cleanText($_POST['pay_method'] ?? 'card-intl'),
                'agree_terms' => $this->cleanText($_POST['agree_terms'] ?? ''),
                'variant_name' => $this->cleanText($_POST['variant_name'] ?? ''),
                'interior_code' => strtoupper($this->cleanText($_POST['interior_code'] ?? '')),
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
                'deposit_amount' => $depositAmount,
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
                'depositAmount' => $depositAmount,
                'depositBaseAmount' => $depositAmount,
                'depositNonRefundable' => $depositNonRefundable,
                'paymentStatus' => 'pending_verify',
                'orderDate' => date('d/m/Y H:i'),
            ];

            header('Location: ' . BASE_URL . 'order/confirmation');
            exit;
        }

        $switchProducts = Product::getSwitchProducts($categoryId, 12);
        // mark current product in list
        foreach ($switchProducts as &$sp) {
            if (isset($sp['id']) && (int)$sp['id'] === $id) {
                $sp['is_current'] = true;
            }
        }
        unset($sp);

        SEO::set('Đặt cọc ' . ($product['name'] ?? 'VinFast'));
        View::render('frontend/order/checkout', [
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
        View::render('frontend/order/confirmation', [
            'order' => $order,
        ]);
    }

    private function cleanText(mixed $value): string
    {
        return trim(strip_tags((string)$value));
    }

    public function index(): void
    {
        if (!Auth::check()) {
            $_SESSION['flash'] = 'Vui lòng đăng nhập để xem đơn hàng.';
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 12;

        $raw = Order::getByUserId((int)Auth::id(), $page, $perPage);
        $orders = [];
        foreach ($raw as $r) {
            $note = [];
            if (is_string($r['note'] ?? '')) {
                $decoded = json_decode((string)$r['note'], true);
                if (is_array($decoded)) {
                    $note = $decoded;
                }
            } elseif (is_array($r['note'] ?? null)) {
                $note = $r['note'];
            }

            $paymentStatus = Order::getPaymentStatusFromNote($r['note'] ?? null);
            $orders[] = [
                'orderId' => 'VF-' . strtoupper(dechex((int)$r['id'])) . '-' . strtoupper(substr(md5((string)$r['id']), 0, 4)),
                'orderDbId' => (int)$r['id'],
                'carName' => (string)($r['product_name'] ?? '—'),
                'orderDate' => !empty($r['created_at']) ? date('d/m/Y H:i', strtotime($r['created_at'])) : '',
                'customerName' => (string)($note['full_name'] ?? ($r['user_name'] ?? '')),
                'email' => (string)($note['email'] ?? ($r['email'] ?? '')),
                'phone' => (string)($note['phone'] ?? ''),
                'depositAmount' => (float)($note['deposit_amount'] ?? 0),
                'paymentStatus' => $paymentStatus,
            ];
        }

        SEO::set('Đơn hàng của tôi');
        View::render('frontend/order/index', ['orders' => $orders]);
    }
}
