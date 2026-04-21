<?php

/**
 * app/controllers/frontend/ContactController.php
 * Owner: Tang Vu 
 * Routes: GET /contact  POST /contact/send
 *
 *  * Contact form page.
 * Displays company contact info from SiteSetting.
 * POST saves the message to the contacts table for admin review.
 */
class ContactController
{
    public function index(): void
    {
        $settings = SiteSetting::all();
        SEO::set('Liên hệ', 'Gửi liên hệ và nhận hỗ trợ từ VinFast.');

        View::render('frontend/contact/index', [
            'settings' => $settings,
        ]);
    }

    public function send(): void
    {
        // -----------------------------
        // 0) Security: CSRF
        // -----------------------------
        Auth::verifyCsrf();

        // -----------------------------
        // 1) Validate input
        // -----------------------------
        $v = new Validator($_POST);
        $v->required('name')->maxLen('name', 100);
        $v->required('email')->email('email')->maxLen('email', 150);
        $v->maxLen('phone', 20);
        $v->required('message')->minLen('message', 10)->maxLen('message', 2000);

        if ($v->fails()) {
            $_SESSION['errors'] = array_values($v->errors());
            header('Location: ' . BASE_URL . 'contact');
            exit;
        }

        // -----------------------------
        // 2) Persist message
        // -----------------------------
        $name = trim((string)($_POST['name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $phone = trim((string)($_POST['phone'] ?? ''));
        $message = trim((string)($_POST['message'] ?? ''));

        Contact::create($name, $email, $phone, $message);

        // -----------------------------
        // 3) Flash + redirect
        // -----------------------------
        $_SESSION['flash'] = 'Cảm ơn bạn! Tin nhắn đã được gửi.';
        header('Location: ' . BASE_URL . 'contact');
        exit;
    }
}
