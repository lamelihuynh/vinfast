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

        // Đặt cọc chỉ áp dụng cho xe; phụ kiện mua qua giỏ hàng (T4)
        if (!empty($product['is_accessory'])) {
            header('Location: ' . BASE_URL . 'products/detail/' . (int)$product['id']);
            exit;
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

        if (($checkoutOld['product_id'] ?? null) !== $id) {
            $checkoutOld = [];
        }
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
            'variant_id' => (string)($checkoutOld['variant_id'] ?? ''),
            'interior_code' => (string)($checkoutOld['interior_code'] ?? ''),
            'step' => (int)($checkoutOld['step'] ?? 1),
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::verifyCsrf();

            if (isset($_GET['save_checkout_step2'])) {
                $draft = json_decode((string)file_get_contents('php://input'), true);
                if (!is_array($draft)) {
                    http_response_code(400);
                    return;
                }
                $fields = ['owner_type', 'full_name', 'phone', 'email', 'cccd', 'province',
                    'showroom', 'salesperson', 'pay_method', 'agree_terms', 'variant_name', 'variant_id', 'interior_code', 'checkout_token'];
                $old = [];
                foreach ($fields as $field) {
                    $old[$field] = $this->cleanText($draft[$field] ?? '');
                }
                $_SESSION['checkout_old'] = $old + ['step' => 3, 'product_id' => $id];
                http_response_code(204);
                return;
            }

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
                'product_id' => $id,
                'checkout_token' => $this->cleanText($_POST['checkout_token'] ?? ''),
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
                'variant_id' => $this->cleanText($_POST['variant_id'] ?? ''),
                'interior_code' => strtoupper($this->cleanText($_POST['interior_code'] ?? '')),
                'step' => (int)($_POST['step'] ?? 3),
            ];

            if (!Auth::check()) {
                $_SESSION['flash'] = 'Vui lòng đăng nhập để hoàn tất đặt cọc.';
                $_SESSION['checkout_old'] = $formData;
                $_SESSION['return_to'] = 'order/checkout/' . $id;
                Auth::requireLogin();
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

            try {
                $variantId = $formData['variant_id'];
                if ($variantId === '') {
                    $variantId = (string)$product['default_variant_id'];
                }
                $created = OrderService::placeDeposit((int)Auth::id(), $product, $variantId, $notePayload,
                    $formData['checkout_token']);
            } catch (OutOfStockException | InvalidArgumentException | ConcurrentUpdateException $e) {
                $msg = match (true) {
                    $e instanceof OutOfStockException => 'Phiên bản xe không đủ tồn kho.',
                    $e instanceof ConcurrentUpdateException => 'Đang có nhiều người đặt xe. Vui lòng thử lại.',
                    default => $e->getMessage(),
                };
                $_SESSION['errors'] = ['system' => $msg];
                $_SESSION['checkout_old'] = $formData;
                header('Location: ' . $buildCheckoutUrl($id, $selectedColorCode), true, 303);
                exit;
            } catch (Throwable $e) {
                error_log('Deposit checkout failed: ' . get_class($e));
                $_SESSION['errors'] = ['system' => 'Chưa xác nhận được đơn đặt cọc. Vui lòng thử lại.'];
                $_SESSION['checkout_old'] = $formData;
                header('Location: ' . $buildCheckoutUrl($id, $selectedColorCode), true, 303);
                exit;
            }

            // Read the persisted snapshot, including when this POST replays an earlier order.
            $saved = Order::getByIdForUser($created, (int)Auth::id());
            $savedNote = json_decode((string)($saved['note'] ?? ''), true) ?? [];
            $formData = array_replace($formData, $savedNote);
            $selectedColor = $savedNote['color'] ?? $selectedColor;
            unset($_SESSION['checkout_old']);

            $_SESSION['checkout_confirmation'] = [
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
                'depositAmount' => (int)($saved['total_amount'] ?? $depositAmount),
                'depositBaseAmount' => (int)($saved['subtotal_amount'] ?? $depositAmount),
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
            'checkoutToken' => preg_match('/\A[0-9a-f]{32}\z/', (string)($checkoutOld['checkout_token'] ?? ''))
                ? $checkoutOld['checkout_token'] : bin2hex(random_bytes(16)),
        ]);
    }

    public function cartcheckout(): void
    {
        if (!Auth::check()) {
            $_SESSION['return_to'] = 'order/cartcheckout';
            Auth::requireLogin();
        }
        $userId = (int)Auth::id();
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $form = ['full_name' => Auth::name(), 'phone' => '', 'address' => '', 'coupon_code' => ''];
        $discount = 0;
        $preview = false;
        $key = bin2hex(random_bytes(16));

        if ($method === 'POST') {
            Auth::verifyCsrf();
            foreach (array_keys($form) as $field) {
                $form[$field] = $this->cleanText($_POST[$field] ?? '');
            }
            $submittedKey = $this->cleanText($_POST['checkout_token'] ?? '');
            if (preg_match('/\A[0-9a-f]{32}\z/', $submittedKey)) {
                $key = $submittedKey;
            }

            try {
                if (($_POST['intent'] ?? '') === 'preview') {
                    $cart = CartService::summary($userId);
                    if (!$cart['ready']) {
                        throw new InvalidArgumentException('Giỏ hàng chưa sẵn sàng để thanh toán.');
                    }
                    $subtotal = $cart['subtotal'];
                    if ($subtotal > 999999999999999) {
                        throw new InvalidArgumentException('Giá trị đơn hàng vượt giới hạn.');
                    }
                    [, $discount] = CouponService::quote(Db::pg(), $form['coupon_code'], $userId, $subtotal);
                    $preview = $form['coupon_code'] !== '';
                } else {
                    if ($form['full_name'] === '' || strlen($form['full_name']) > 300
                        || !preg_match('/\A[0-9+\s.\-]{9,15}\z/', $form['phone'])
                        || $form['address'] === '' || strlen($form['address']) > 600
                        || ($_POST['agree_terms'] ?? '') !== '1') {
                        throw new InvalidArgumentException('Vui lòng nhập họ tên, số điện thoại, địa chỉ và đồng ý điều khoản.');
                    }
                    $note = array_diff_key($form, ['coupon_code' => true]);
                    $id = OrderService::placeFromCart($userId, $submittedKey, $this->cleanText($_POST['quote_version'] ?? ''),
                        $form['coupon_code'], $note + ['pay_method' => 'transfer']);
                    $_SESSION['flash'] = 'Đã tạo đơn phụ kiện. Đơn đang chờ thanh toán.';
                    header('Location: ' . BASE_URL . 'order/detail/' . $id, true, 303);
                    exit;
                }
            } catch (InvalidArgumentException | CouponException $e) {
                $_SESSION['errors'] = [$e->getMessage()];
            } catch (OutOfStockException $e) {
                $_SESSION['errors'] = ['Không đủ tồn kho cho đơn này. Giỏ hàng của bạn được giữ nguyên.'];
            } catch (ConcurrentUpdateException $e) {
                $_SESSION['errors'] = ['Có nhiều đơn đang được xử lý. Vui lòng thử lại.'];
            } catch (Throwable $e) {
                error_log('Cart checkout failed: ' . get_class($e));
                $_SESSION['errors'] = ['Chưa xác nhận được đơn hàng. Vui lòng thử lại.'];
            }
        } elseif ($method !== 'GET') {
            header('Allow: GET, POST');
            http_response_code(405);
            return;
        }

        $cart = $cart ?? CartService::summary($userId);
        if (!$cart['ready']) {
            $_SESSION['errors'] ??= ['Giỏ hàng trống hoặc có phụ kiện không còn bán.'];
            header('Location: ' . BASE_URL . 'cart', true, 303);
            exit;
        }
        $form['agreed'] = ($_POST['agree_terms'] ?? '') === '1';
        SEO::set('Đặt mua phụ kiện');
        View::render('frontend/order/cart-checkout', compact('cart', 'form', 'key', 'discount', 'preview'));
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
        return is_scalar($value) ? trim(strip_tags((string)$value)) : '';
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

        $result = Order::getUserOrderPage((int)Auth::id(), $page, $perPage);
        $pg = new Pagination($result['total'], $result['page'], $perPage);
        $pageUrl = BASE_URL . 'order?page=';

        SEO::set('Đơn hàng của tôi');
        View::render('frontend/order/index', [
            'orders' => $result['orders'],
            'pg' => $pg,
            'pageUrl' => $pageUrl,
        ]);
    }

    /**
     * Backend join: order + items from PG in one query, images/links from Mongo in one $in.
     * Item names and prices always come from the order snapshot, so renamed or hidden
     * products still show what the customer actually bought.
     */
    public function detail($id = 0): void
    {
        if (!Auth::check()) {
            $_SESSION['flash'] = 'Vui lòng đăng nhập để xem đơn hàng.';
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        $order = Order::getByIdForUser((int)$id, (int)Auth::id());
        if (!$order) {
            $_SESSION['flash'] = 'Không tìm thấy đơn hàng.';
            header('Location: ' . BASE_URL . 'order');
            exit;
        }

        $items = $order['items'];
        try {
            $products = Product::findManyByIds(array_column($items, 'product_id'));
        } catch (Throwable $e) {
            // PostgreSQL snapshots still render when catalog enrichment is unavailable.
            $products = [];
            error_log('Order detail: MongoDB catalog unavailable');
        }

        SEO::set('Đơn hàng #' . (int)$order['id']);
        View::render('frontend/order/detail', [
            'order' => $order,
            'items' => $items,
            'products' => $products,
        ]);
    }
}
