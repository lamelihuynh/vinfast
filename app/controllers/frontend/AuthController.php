<?php

/**
 * app/controllers/frontend/AuthController.php
 * Owner: All members (common)
 * Routes: /auth/login  /auth/register  /auth/logout
 *
 * Handles user registration and login.
 * On success, redirects members to /user/profile,
 * admins are redirected to /admin/dashboard (through admin.php).
 */
class AuthController
{
    private User $user;

    public function __construct()
    {
        $this->user = new User();
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->loginPost();
            return;
        }

        if (Auth::check()) {
            $this->redirectByRole();
        }

        SEO::set('Login');
        View::render('frontend/auth/login', [
            'old' => $this->pullOld(),
        ], 'auth');
    }

    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->registerPost();
            return;
        }

        if (Auth::check()) {
            $this->redirectByRole();
        }

        SEO::set('Register');
        View::render('frontend/auth/register', [
            'old' => $this->pullOld(),
        ], 'auth');
    }

    public function logout(): void
    {
        Auth::logout();
        session_start();
        $_SESSION['flash'] = 'Ban da dang xuat thanh cong.';
        header('Location: ' . BASE_URL . 'auth/login');
        exit;
    }

    private function loginPost(): void
    {
        Auth::verifyCsrf();

        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $remember = isset($_POST['remember']) && (string)$_POST['remember'] === '1';

        $v = (new Validator($_POST))
            ->required('email')->email('email')
            ->required('password');

        if ($v->fails()) {
            $_SESSION['errors'] = $v->errors();
            $_SESSION['old'] = ['email' => $email];
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        $user = $this->user->findByEmail($email);
        if (!$user || !password_verify($password, (string)$user['password'])) {
            $_SESSION['errors'] = ['Email hoặc mật khẩu không đúng.'];
            $_SESSION['old'] = ['email' => $email];
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        if ((int)($user['is_locked'] ?? 0) === 1) {
            $_SESSION['errors'] = ['Tài khoản cua bạn da bi khoa.'];
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        Auth::login($user, $remember);
        $this->redirectByRole();
    }

    private function registerPost(): void
    {
        Auth::verifyCsrf();

        $name = trim((string)($_POST['name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');

        $v = (new Validator($_POST))
            ->required('name')->maxLen('name', 100)
            ->required('email')->email('email')
            ->required('password')->minLen('password', 8);

        $errors = $v->errors();

        if ($password !== $confirm) {
            $errors['confirm_password'] = 'Mật khẩu xác nhận không khớp.';
        }

        if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/\d/', $password)) {
            $errors['password_policy'] = 'Mật khẩu phải có chữ hoa, chữ thường và ít nhất 1 chữ số.';
        }

        if ($this->user->findByEmail($email)) {
            $errors['email_exists'] = 'Email đã tồn tại.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = array_values($errors);
            $_SESSION['old'] = [
                'name' => $name,
                'email' => $email,
            ];
            header('Location: ' . BASE_URL . 'auth/register');
            exit;
        }

        $ok = $this->user->create([
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'role' => 'member',
        ]);

        if (!$ok) {
            $_SESSION['errors'] = ['Không thể tạo tài khoản. Vui lòng thử lại.'];
            $_SESSION['old'] = ['name' => $name, 'email' => $email];
            header('Location: ' . BASE_URL . 'auth/register');
            exit;
        }

        $_SESSION['flash'] = 'Đăng ký thành công. Vui lòng đăng nhập.';
        header('Location: ' . BASE_URL . 'auth/login');
        exit;
    }

    private function redirectByRole(): void
    {
        if (Auth::isAdmin()) {
            header('Location: ' . ADMIN_URL . 'dashboard');
            exit;
        }

        header('Location: ' . BASE_URL . 'user/profile');
        exit;
    }

    private function pullOld(): array
    {
        $old = is_array($_SESSION['old'] ?? null) ? $_SESSION['old'] : [];
        unset($_SESSION['old']);
        return $old;
    }
}
