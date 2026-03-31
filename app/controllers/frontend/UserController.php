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
        if (!empty($_FILES['avatar']['name'])) {
            try {
                $avatar = Upload::image($_FILES['avatar'], 'avatars');
            } catch (RuntimeException $e) {
                $_SESSION['errors'] = ['avatar' => $e->getMessage()];
                header('Location: ' . BASE_URL . 'user/profile');
                exit;
            }
        }
        $this->user->update(Auth::id(), [
            'name'   => strip_tags($_POST['name']),
            'email'  => $_POST['email'],
            'avatar' => $avatar,
        ]);
        $_SESSION['flash'] = 'Profile updated.';
        header('Location: ' . BASE_URL . 'user/profile');
        exit;
    }

    public function changePassword(): void
    {
        Auth::verifyCsrf();
        $v = (new Validator($_POST))->required('current')->minLen('new_password', 8);
        if ($v->fails()) {
            $_SESSION['errors'] = $v->errors();
            header('Location: ' . BASE_URL . 'user/profile');
            exit;
        }
        $u = $this->user->findById(Auth::id());
        if (!password_verify($_POST['current'], $u['password'])) {
            $_SESSION['errors'] = ['current' => 'Current password is incorrect.'];
            header('Location: ' . BASE_URL . 'user/profile');
            exit;
        }
        $this->user->changePassword(Auth::id(), $_POST['new_password']);
        $_SESSION['flash'] = 'Password changed successfully.';
        header('Location: ' . BASE_URL . 'user/profile');
        exit;
    }

    public function orders(): void
    {
        SEO::set('My Orders');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        $total = Order::countByUserId(Auth::id());
        $pg = new Pagination($total, $page, $perPage);
        $orders = Order::getByUserId(Auth::id(), $pg->current, $pg->perPage);

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
