<?php
/**
 * app/controllers/frontend/AboutController.php
 * Owner: Nhat Linh (Member 2)
 * Routes: GET /about
 *
 * About / company introduction page.
 * Content managed via admin panel (PageContentAdminController)
 * Stored in SiteSetting and PageAsset tables
 */
class AboutController {
    
    /**
     * GET /about
     * Displays company about page with editable content from database
     */
    public function index(): void
    {
        // Load settings and assets
        $settings = SiteSetting::all();
        $pageAsset = new PageAsset();


        $years = ['2017', '2018', '2019', '2020', '2021', '2022', '2023', '2024', '2025', '2026'];
        $timelineAssets = $pageAsset->getByPattern('about', 'timeline_%');
        $assetMap =[];
        foreach ($timelineAssets as $asset) {
            $key = str_replace('timeline_', '', $asset['asset_key']);
            $assetMap[$key] = $asset['file_path'];
        }

$normalizeUploadPath = function ($path): ?string {
    $path = trim((string)$path);
    if ($path === '') return null;
    if (preg_match('~^https?://~i', $path)) return $path;

    $path = ltrim(str_replace('\\', '/', $path), '/');
    foreach (['public/images/uploads/', 'images/uploads/', 'uploads/'] as $prefix) {
        if (str_starts_with($path, $prefix)) {
            $path = substr($path, strlen($prefix));
            break;
        }
    }

    return $path !== '' ? $path : null;
};

$prependBase = function ($path) use ($normalizeUploadPath) {
    $path = $normalizeUploadPath($path);
    if ($path === null) return null;
    if (preg_match('~^https?://~i', $path)) return $path;
    return BASE_URL . 'public/images/uploads/' . $path;
};


        $timeline = [];
        foreach ($years as $year) {
        $timeline[] = [
            'year'          => $year,
            'description'   => $settings["about_timeline_text_{$year}"] ?? '',
            'img_main'      => $prependBase($assetMap["{$year}_main"] ?? null),
            'img_secondary' => $prependBase($assetMap["{$year}_secondary"] ?? null),
        ];
        }

        // // Helper function to prepend BASE_URL if path starts with /

        
        $visionTitle = $settings['about_vision_title'] ?? '';
        $visionText = $settings['about_vision_text'] ?? '';
        $missionTitle = $settings['about_mission_title'] ?? '';
        $missionText = $settings['about_mission_text'] ?? '';
        $philosophyTitle = $settings['about_philosophy_title'] ?? '';
        $philosophyText = $settings['about_philosophy_text'] ?? '';

        $visionPath = $settings['about_vision_image'];
        $missionPath = $settings['about_mission_image'];
        $philosophyPath = $settings['about_philosophy_image'];
        

        // Extract about intro content
        $aboutText = $settings['about_intro_text'] ?? '';
        $aboutImage = $normalizeUploadPath($settings['about_intro_image'] ?? '');
        $heroVideo = $settings['about_hero_video'] ?? '';

        

        // Get award images
        $awardAssets = $pageAsset->getByPattern('about', 'award_%');
        $awardImages = [];
        foreach ($awardAssets as $asset) {
            $year = str_replace('award_', '', $asset['asset_key']);
            $awardImages[$year] = $asset['file_path'];
        }

        $aboutAward = new AboutAward(); 
        $awards = $aboutAward->all(); 
        // SEO
        $title = $settings['meta_about_title'] ?? 'Giới thiệu VinFast';
        $desc = $settings['meta_about_description'] ?? 'Tìm hiểu về VinFast - công ty sản xuất ô tô điện hàng đầu tại Việt Nam';
        SEO::set($title, $desc);


        // Render view
        View::render('frontend/about/index', [
            'aboutText' => $aboutText,
            'aboutImage' => $aboutImage,
            'videoUrl' => $prependBase($heroVideo),
            'timeline' => $timeline,
            'awardImages' => array_map($prependBase, $awardImages),
            'awards' => $awards,
            'settings' => $settings,
            
            'visionTitle' => $visionTitle, 
            'visionText' => $visionText, 
            'missionTitle' => $missionTitle, 
            'missionText' => $missionText,             
            'philosophyTitle' => $philosophyTitle, 
            'philosophyText' => $visionText, 

            'visionPath' => $normalizeUploadPath($visionPath),
            'missionPath' => $normalizeUploadPath($missionPath),
            'philosophyPath' => $normalizeUploadPath($philosophyPath),

        ]);
    }
}