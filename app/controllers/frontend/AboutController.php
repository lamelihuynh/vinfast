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

$prependBase = function($path) {
        if (empty($path)) return null;
        // Nếu path lưu trong DB là 'about-page/file.jpg'
        // Ta nối thành: BASE_URL + 'public/images/uploads/' + path
        return BASE_URL . 'public/images/uploads/' . ltrim($path, '/');
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
        $aboutImage = $settings['about_intro_image'] ?? '';
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

            'visionPath' => $visionPath, 
            'missionPath' => $missionPath, 
            'philosophyPath' => $philosophyPath, 

        ]);
    }
}