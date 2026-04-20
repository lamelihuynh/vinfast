<?php
/**
 * app/controllers/admin/SettingsAdminController.php
 * Owner: Tang Vu 
 * Routes: /admin/settings   POST /admin/settings/save , .... 
 *
 *  * Manage site-wide settings: logo upload, banner images (1-3),
 * contact address/phone/email, about page text and image, tagline.
 */
class SettingsAdminController {
    public function index(): void
    {
        SEO::set('Site settings');
        $settings = SiteSetting::all();

        View::render('admin/settings/index', [
            'settings' => $settings,
        ], 'admin');
    }

    public function save(): void
    {
        Auth::verifyCsrf();

        // -----------------------------
        // 1) Text fields (editable content)
        // -----------------------------
        $payload = [
            'tagline' => trim((string)($_POST['tagline'] ?? '')),
            'address' => trim((string)($_POST['address'] ?? '')),
            'phone' => trim((string)($_POST['phone'] ?? '')),
            'email' => trim((string)($_POST['email'] ?? '')),
            'facebook_url' => trim((string)($_POST['facebook_url'] ?? '')),
            'about_text' => trim((string)($_POST['about_text'] ?? '')),
        ];

        // Basic validation (admin side)
        $v = new Validator($payload);
        $v->maxLen('tagline', 255)->maxLen('address', 255)->maxLen('phone', 20)->maxLen('email', 150)->maxLen('facebook_url', 255)->maxLen('about_text', 5000);
        if ($v->fails()) {
            $_SESSION['errors'] = array_values($v->errors());
            header('Location: ' . ADMIN_URL . 'settings');
            exit;
        }

        // -----------------------------
        // 2) Upload fields
        // Store paths as web-relative: public/images/uploads/...
        // -----------------------------
        $fileMap = [
            'logo' => 'logo',
            'banner_1' => 'banner_1',
            'banner_2' => 'banner_2',
            'banner_3' => 'banner_3',
            'about_image' => 'about_image',
        ];

        foreach ($fileMap as $inputName => $settingKey) {
            if (!empty($_FILES[$inputName]) && ($_FILES[$inputName]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                try {
                    $rel = Upload::image($_FILES[$inputName], 'site', true);
                    $payload[$settingKey] = 'public/images/uploads/' . $rel;
                } catch (Throwable $e) {
                    $_SESSION['errors'] = [$e->getMessage()];
                    header('Location: ' . ADMIN_URL . 'settings');
                    exit;
                }
            }
        }

        SiteSetting::setMany($payload);

        $_SESSION['flash'] = 'Đã lưu cấu hình website.';
        header('Location: ' . ADMIN_URL . 'settings');
        exit;
    }
}
