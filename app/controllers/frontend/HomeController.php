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
        $featured = Product::getAll(1, 12);
        $latest   = News::getLatest(3);

        $leadProduct = null;
        if (!empty($settings['featured_product_id'])) {
            $leadProduct = Product::getById($settings['featured_product_id']);
            
            // If a specific color is selected in settings, try to find its corresponding image
            if ($leadProduct && !empty($settings['featured_color'])) {
                $targetColorHex = strtoupper(trim($settings['featured_color']));
                $colors = $leadProduct['exterior_colors'] ?? [];
                
                foreach ($colors as $c) {
                    $colorHex = strtoupper(trim((string)($c['hex'] ?? '')));
                    if ($colorHex === $targetColorHex && !empty($c['image'])) {
                        // Put the color-specific image at the beginning of the images array
                        // so that ProductViewHelper::thumbUrl picks it up.
                        array_unshift($leadProduct['images'], $c['image']);
                        break;
                    }
                }
            }
        }
        if (!$leadProduct && !empty($featured)) {
            $leadProduct = $featured[0];
        }

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
            'leadProduct' => $leadProduct,
        ]);
    }
}
