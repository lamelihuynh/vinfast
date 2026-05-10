<?php

/**
 * app/controllers/frontend/UserController.php
 * Owner: All members (common)
 * Routes: /user/profile  /user/orders  /user/saveProfile  /user/changePassword
 *
 * Profile management for logged-in members.
 * Auth::requireLogin() is called in the constructor — guests are redirected.
 */
class UserController
{
    private User  $user;
    private Order $order;

    public function __construct()
    {
        Auth::requireLogin();
        $this->user  = new User();
        $this->order = new Order();
    }

    public function profile(): void
    {
        SEO::set('My Profile');
        $userData = $this->user->findById(Auth::id());
        View::render('frontend/user/profile', ['user' => $userData]);
    }

    public function saveProfile(): void
    {
        Auth::verifyCsrf();
        $v = (new Validator($_POST))->required('name')->required('email')->email('email');
        if ($v->fails()) {
            $_SESSION['errors'] = $v->errors();
            header('Location: ' . BASE_URL . 'user/profile');
            exit;
        }

        $avatar = null;

        if (!empty($_FILES['avatar']['tmp_name']) && is_uploaded_file($_FILES['avatar']['tmp_name'])) {
            $mime = mime_content_type($_FILES['avatar']['tmp_name']);
            if (strpos($mime, 'image/') !== 0) {
                $_SESSION['errors'] = ['avatar' => 'File tải lên phải là định dạng hình ảnh.'];
                header('Location: ' . BASE_URL . 'user/profile');
                exit;
            }

            $userId = Auth::id();
            $uploadDir = ROOT . "/public/images/avatars/{$userId}/";

            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            } else {
                $files = glob($uploadDir . '*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        @unlink($file);
                    }
                }
            }

            $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            if (empty($ext)) $ext = 'jpg';
            $fileName = 'avatar.' . $ext;

            $destPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $destPath)) {
                $avatar = "public/images/avatars/{$userId}/{$fileName}";
            }
        }
        $this->user->update(Auth::id(), [
            'name'   => strip_tags($_POST['name']),
            'email'  => $_POST['email'],
            'avatar' => $avatar,
        ]);

        $updatedUser = $this->user->findById(Auth::id());
        if (is_array($updatedUser)) {
            $_SESSION['uname'] = (string)($updatedUser['name'] ?? $_SESSION['uname'] ?? '');
            $_SESSION['uavatar'] = (string)($updatedUser['avatar'] ?? $_SESSION['uavatar'] ?? '');
        }

        $_SESSION['flash'] = 'Cập nhật hồ sơ thành công.';
        header('Location: ' . BASE_URL . 'user/profile');
        exit;
    }

    public function changePassword(): void
    {
        Auth::verifyCsrf();
        $v = (new Validator($_POST))
            ->required('current')
            ->required('new_password')
            ->minLen('new_password', 8);
        if ($v->fails()) {
            $_SESSION['errors'] = $v->errors();
            header('Location: ' . BASE_URL . 'user/profile');
            exit;
        }

        $newPassword = (string)($_POST['new_password'] ?? '');
        if (!preg_match('/[A-Z]/', $newPassword) || !preg_match('/[a-z]/', $newPassword) || !preg_match('/\d/', $newPassword)) {
            $_SESSION['errors'] = ['new_password' => 'Mật khẩu phải có chữ hoa, chữ thường và ít nhất 1 chữ số.'];
            header('Location: ' . BASE_URL . 'user/profile');
            exit;
        }

        $u = $this->user->findById(Auth::id());
        if (!password_verify($_POST['current'], $u['password'])) {
            $_SESSION['errors'] = ['current' => 'Mật khẩu hiện tại không chính xác.'];
            header('Location: ' . BASE_URL . 'user/profile');
            exit;
        }

        if (password_verify($newPassword, (string)$u['password'])) {
            $_SESSION['errors'] = ['new_password' => 'Mật khẩu mới không được giống mật khẩu hiện tại.'];
            header('Location: ' . BASE_URL . 'user/profile');
            exit;
        }

        $this->user->changePassword(Auth::id(), $_POST['new_password']);
        $_SESSION['flash'] = 'Đổi mật khẩu thành công.';
        header('Location: ' . BASE_URL . 'user/profile');
        exit;
    }

    public function orders(): void
    {
        SEO::set('My Orders');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 5;
        $total = Order::countByUserId(Auth::id());
        $pg = new Pagination($total, $page, $perPage);
        $rawOrders = Order::getByUserId(Auth::id(), $pg->current, $pg->perPage);
        $orders = [];
        foreach ($rawOrders as $row) {
            $note = [];
            if (is_string($row['note'] ?? '')) {
                $decoded = json_decode((string)$row['note'], true);
                if (is_array($decoded)) {
                    $note = $decoded;
                }
            } elseif (is_array($row['note'] ?? null)) {
                $note = $row['note'];
            }

            $paymentStatus = Order::getPaymentStatusFromNote($row['note'] ?? null);
            $orders[] = [
                'orderId' => 'VF-' . strtoupper(dechex((int)$row['id'])) . '-' . strtoupper(substr(md5((string)$row['id']), 0, 4)),
                'orderDbId' => (int)$row['id'],
                'carName' => (string)($row['product_name'] ?? '—'),
                'orderDate' => !empty($row['created_at']) ? date('d/m/Y H:i', strtotime((string)$row['created_at'])) : '',
                'customerName' => (string)($note['full_name'] ?? ($row['user_name'] ?? '')),
                'email' => (string)($note['email'] ?? ($row['email'] ?? '')),
                'phone' => (string)($note['phone'] ?? ''),
                'province' => (string)($note['province'] ?? ''),
                'showroom' => (string)($note['showroom'] ?? ''),
                'depositAmount' => (float)($note['deposit_amount'] ?? 0),
                'paymentStatus' => $paymentStatus,
            ];
        }

        $query = $_GET;
        unset($query['page']);
        $baseQuery = http_build_query($query);
        $pageUrl = BASE_URL . 'user/orders?' . ($baseQuery !== '' ? $baseQuery . '&' : '') . 'page=';

        View::render('frontend/user/orders', [
            'orders' => $orders,
            'pg' => $pg,
            'pageUrl' => $pageUrl,
        ]);
    }
}