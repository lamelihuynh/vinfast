<?php

/**
 * app/controllers/frontend/HomeController.php
 * Owner: Tang Vu 
 * Routes: GET /
 *
 *  * Homepage with Swiper hero banners, featured vehicle grid, and latest news preview.
 * Banner images and tagline are loaded from SiteSetting.
 * Swiper.js carousel is initialised in public/js/frontend/home.js.
 */
class HomeController
{
    /**
     * GET /
     * Route mapping:
     * - / (empty url) => index.php maps to controller "home" method "index"
     */
    public function index(): void
    {
        // -----------------------------
        // 1) Load "editable" site settings
        // -----------------------------
        $settings = SiteSetting::all();

        // -----------------------------
        // 2) Query homepage data blocks
        // -----------------------------
        $featured = Product::getAll(1, 6);
        $latest   = News::getLatest(3);

        // -----------------------------
        // 3) SEO
        // -----------------------------
        $title = (($settings['meta_home_title'] ?? '') !== '') ? (string)$settings['meta_home_title'] : 'Trang chủ';
        $desc  = (($settings['meta_home_description'] ?? '') !== '') ? (string)$settings['meta_home_description'] : 'Khám phá xe điện VinFast, tin tức mới nhất và dịch vụ hỗ trợ.';
        SEO::set($title, $desc);

        // -----------------------------
        // 4) Page scripts (inject into layout)
        // -----------------------------
        $scripts = '';
        $scripts .= '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">';
        $scripts .= '<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>';
        $scripts .= '<script src="' . BASE_URL . 'public/js/frontend/home.js' . AssetHelper::getVersionQuery('public/js/frontend/home.js') . '"></script>';

        View::render('frontend/home/index', [
            'settings' => $settings,
            'featured' => $featured,
            'latest' => $latest,
            'scripts' => $scripts,
        ]);
    }
}
