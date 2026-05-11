<?php
/**
 * app/controllers/frontend/ContactController.php
 * Owner: Tang Vu 
 * Routes: GET /contact  POST /contact/send  POST /contact/testdrive
 *
 * Contact form page + Test drive registration form.
 * Displays company contact info from SiteSetting.
 * POST saves the message to the contacts table or test_drives table for admin review.
 */
class ContactController {

    private const PROVINCES = [
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

    private const SHOWROOMS = [
        'Hà Nội' => ['VinFast Hà Nội - Tố Hữu', 'VinFast Hà Nội - Phạm Hùng', 'VinFast Hà Nội - Phú Thượng'],
        'Hồ Chí Minh' => ['VinFast HCM - Quận 7', 'VinFast HCM - Bình Thạnh', 'VinFast HCM - Thủ Đức'],
        'Đà Nẵng' => ['VinFast Đà Nẵng - Nguyễn Văn Linh'],
        'Hải Phòng' => ['VinFast Hải Phòng - Lê Hồng Phong'],
    ];

    public function index(): void
    {
        // Handle pending submissions after login
        if (Auth::check()) {
            if (isset($_SESSION['pending_contact_msg'])) {
                $data = $_SESSION['pending_contact_msg'];
                unset($_SESSION['pending_contact_msg']);
                $this->processSend($data);
            }
            if (isset($_SESSION['pending_test_drive'])) {
                $data = $_SESSION['pending_test_drive'];
                unset($_SESSION['pending_test_drive']);
                $this->processTestDrive($data);
            }
        }

        $settings = SiteSetting::all();
        $tab = trim((string)($_GET['tab'] ?? 'contact'));
        if (!in_array($tab, ['contact', 'test-drive'], true)) {
            $tab = 'contact';
        }

        $products = Product::getAll(1, 100);

        SEO::set('Liên hệ', 'Gửi liên hệ và nhận hỗ trợ từ VinFast.');

        View::render('frontend/contact/index', [
            'settings' => $settings,
            'tab' => $tab,
            'products' => $products,
            'provinces' => self::PROVINCES,
            'showrooms' => self::SHOWROOMS,
        ]);
    }

    public function send(): void
    {
        Auth::verifyCsrf();

        if (!Auth::check()) {
            $_SESSION['pending_contact_msg'] = $_POST;
            header('Location: ' . BASE_URL . 'auth/login?return_to=contact');
            exit;
        }

        $this->processSend($_POST);
    }

    private function processSend(array $data): void
    {
        $v = new Validator($data);
        $v->required('name')->maxLen('name', 100);
        $v->required('email')->email('email')->maxLen('email', 150);
        $v->maxLen('phone', 20);
        $v->required('message')->minLen('message', 10)->maxLen('message', 2000);

        if ($v->fails()) {
            $_SESSION['errors'] = array_values($v->errors());
            header('Location: ' . BASE_URL . 'contact');
            exit;
        }

        $name = strip_tags(trim((string)($data['name'] ?? '')));
        $email = strip_tags(trim((string)($data['email'] ?? '')));
        $phone = strip_tags(trim((string)($data['phone'] ?? '')));
        $message = strip_tags(trim((string)($data['message'] ?? '')));

        Contact::create($name, $email, $phone, $message);

        $_SESSION['flash'] = 'Cảm ơn bạn! Tin nhắn đã được gửi.';
        header('Location: ' . BASE_URL . 'contact');
        exit;
    }

    public function testdrive(): void
    {
        Auth::verifyCsrf();

        if (!Auth::check()) {
            $_SESSION['pending_test_drive'] = $_POST;
            header('Location: ' . BASE_URL . 'auth/login?return_to=contact?tab=test-drive');
            exit;
        }

        $this->processTestDrive($_POST);
    }

    private function processTestDrive(array $data): void
    {
        $v = new Validator($data);
        $v->required('name')->maxLen('name', 100);
        $v->required('email')->email('email')->maxLen('email', 150);
        $v->required('phone')->maxLen('phone', 20);
        $v->required('product_id');
        $v->required('province');
        $v->required('showroom');
        $v->required('preferred_date');
        $v->maxLen('note', 2000);

        if ($v->fails()) {
            $_SESSION['errors'] = array_values($v->errors());
            header('Location: ' . BASE_URL . 'contact?tab=test-drive');
            exit;
        }

        $name = strip_tags(trim((string)($data['name'] ?? '')));
        $email = strip_tags(trim((string)($data['email'] ?? '')));
        $phone = strip_tags(trim((string)($data['phone'] ?? '')));
        $productId = (int)($data['product_id'] ?? 0);
        $province = strip_tags(trim((string)($data['province'] ?? '')));
        $showroom = strip_tags(trim((string)($data['showroom'] ?? '')));
        $preferredDate = strip_tags(trim((string)($data['preferred_date'] ?? '')));
        $note = strip_tags(trim((string)($data['note'] ?? '')));

        $today = date('Y-m-d');
        if ($preferredDate < $today) {
            $_SESSION['errors'] = ['Thời gian đăng ký không hợp lệ.'];
            header('Location: ' . BASE_URL . 'contact?tab=test-drive');
            exit;
        }

        if (!in_array($province, self::PROVINCES, true)) {
            $_SESSION['errors'] = ['Tỉnh/Thành phố không hợp lệ.'];
            header('Location: ' . BASE_URL . 'contact?tab=test-drive');
            exit;
        }

        $provinceShowrooms = self::SHOWROOMS[$province] ?? [];
        if (empty($provinceShowrooms) || !in_array($showroom, $provinceShowrooms, true)) {
            $_SESSION['errors'] = ['Vui lòng chọn showroom hợp lệ theo tỉnh thành.'];
            header('Location: ' . BASE_URL . 'contact?tab=test-drive');
            exit;
        }

        if ($productId <= 0) {
            $_SESSION['errors'] = ['Vui lòng chọn dòng xe bạn quan tâm.'];
            header('Location: ' . BASE_URL . 'contact?tab=test-drive');
            exit;
        }

        $product = Product::getById($productId);
        if (!$product) {
            $_SESSION['errors'] = ['Dòng xe không tồn tại.'];
            header('Location: ' . BASE_URL . 'contact?tab=test-drive');
            exit;
        }

        TestDrive::create(
            $name,
            $email,
            $phone,
            $productId,
            $province,
            $showroom,
            $preferredDate,
            $note
        );

        $_SESSION['flash'] = 'Cảm ơn bạn! Đăng ký lái thử đã được gửi. VinFast sẽ liên hệ sớm để xác nhận lịch hẹn.';
        header('Location: ' . BASE_URL . 'contact?tab=test-drive');
        exit;
    }
}
