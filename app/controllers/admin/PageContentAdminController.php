<?php

/**
 * app/controllers/admin/PageContentAdminController.php
 * Owner: Nhat Linh (Member 2)
 * Routes: 
 *   GET  /admin/page-content/about          — Display About page content editor
 *   POST /admin/page-content/about/save     — Save About content (text, video, images)
 *   GET  /admin/page-content/faq            — Display FAQ page content editor
 *   POST /admin/page-content/faq/save       — Save FAQ content
 *   POST /admin/page-content/upload         — Handle asset upload (AJAX/Form)
 *   POST /admin/page-content/delete-asset   — Delete page asset
 *
 * Purpose: Manage page content and assets (images, videos) stored on server
 */
class PageContentAdminController
{
    private $pageAsset;
    private $siteSetting;

    private $aboutAward;

    public function __construct()
    {
        $this->pageAsset = new PageAsset();
        $this->siteSetting = new SiteSetting();
        $this->aboutAward = new AboutAward();

    }

    /**
     * GET /admin/page-content/about
     * About page content editor
     */
    public function about(): void
    {
        Auth::requireAdmin();
        SEO::set('Quản lý nội dung trang About');

        // Get current settings
        $aboutText = $this->siteSetting->get('about_intro_text', '');
        $aboutImage = $this->siteSetting->get('about_intro_image', '');
        $heroVideo = $this->siteSetting->get('about_hero_video', '');

        $visionTitle = $this->siteSetting->get('about_vision_title', '');
        $visionText = $this->siteSetting->get('about_vision_text', '');
        $visionImage = $this->siteSetting->get('about_vision_image', '');

        $missionTitle = $this->siteSetting->get('about_mission_title', '');
        $missionText = $this->siteSetting->get('about_mission_text', '');
        $missionImage = $this->siteSetting->get('about_mission_image', '');

        $philosophyTitle = $this->siteSetting->get('about_philosophy_title', '');
        $philosophyText = $this->siteSetting->get('about_philosophy_text', '');
        $philosophyImage = $this->siteSetting->get('about_philosophy_image', '');
        $aboutHeroImage = $this->siteSetting->get('about_intro_image', ''); 
        // Get timeline images
        $years = [2026, 2025,2024, 2023, 2022, 2021, 2020, 2019, 2018, 2017];

        $timelineAssets = $this->pageAsset->getByPattern('about', 'timeline_%');
        $timelineImages = [];

        foreach ($timelineAssets as $asset) {
            $key = str_replace('timeline_', '', $asset['asset_key']); // còn lại: 2023_main hoặc 2023_secondary
            
            $parts = explode('_', $key); // Tách thành ['2023', 'main']
            
            if (count($parts) === 2) {
                $year = $parts[0];
                $type = $parts[1]; // main hoặc secondary
                
                $timelineImages[$year][$type] = $asset['file_path'];
            }
        }
        $timelineTexts = [];
        foreach ($years as $year) {
            $timelineTexts[$year] = $this->siteSetting->get("about_timeline_text_{$year}", '');
        }

        // Get award images
        $awardAssets = $this->pageAsset->getByPattern('about', 'award_%');
        $awardImages = [];
        foreach ($awardAssets as $asset) {
            $year = str_replace('award_', '', $asset['asset_key']);
            $awardImages[$year] = $asset['file_path'];
        }

        $awards  = $this->aboutAward->all(); 
        View::render('admin/pages/about-edit', [
            'aboutText' => $aboutText,
            'aboutImage' => $aboutImage,
            'heroVideo' => $heroVideo,
            'timelineImages' => $timelineImages,
            'timelineTexts' => $timelineTexts,
            'awardImages' => $awardImages,
            'awards'=> $awards,
            'visionTitle' => $visionTitle,
            'visionText' => $visionText, 
            'missionTitle' => $missionTitle,
            'missionText' => $missionText, 
            'philosophyTitle' => $philosophyTitle,
            'philosophyText' => $philosophyText, 
            'visionImage' => $visionImage,
            'missionImage' => $missionImage,
            'philosophyImage' => $philosophyImage,
            'heroImage' => $aboutHeroImage,
        ], 'admin');
    }

    /**
     * POST /admin/page-content/about/save
     * Save About page content
     */
    public function aboutSave(): void
    {
        Auth::requireAdmin();
        // Auth::verifyCsrf();

        if (isset($_POST['vision_title'])) {
            $this->siteSetting->set(
                'about_vision_title',
                trim($_POST['vision_title'] ?? '')
            );
        }

        if (isset($_POST['vision_text'])) {
            $this->siteSetting->set(
                'about_vision_text',
                trim($_POST['vision_text'] ?? '')
            );
        }

        if (isset($_POST['mission_title'])) {
            $this->siteSetting->set(
                'about_mission_title',
                trim($_POST['mission_title'] ?? '')
            );
        }

        if (isset($_POST['mission_text'])) {
            $this->siteSetting->set(
                'about_mission_text',
                trim($_POST['mission_text'] ?? '')
            );
        }

        if (isset($_POST['philosophy_title'])) {
            $this->siteSetting->set(
                'about_philosophy_title',
                trim($_POST['philosophy_title'] ?? '')
            );
        }
        if (isset($_POST['philosophy_text'])) {
            $this->siteSetting->set(
                'about_philosophy_text',
                trim($_POST['philosophy_text'] ?? '')
            );
        }
        
        if (
            isset($_FILES['vision_image']) &&
            $_FILES['vision_image']['size'] > 0
        ) {

            $upload = $this->pageAsset->upload(
                'about',
                'vision_image',
                $_FILES['vision_image']
            );

            if ($upload['success']) {

                $this->siteSetting->set(
                    'about_vision_image',
                    $upload['asset']['file_path']
                );
            }
        }


        if (
            isset($_FILES['mission_image']) &&
            $_FILES['mission_image']['size'] > 0
        ) {

            $upload = $this->pageAsset->upload(
                'about',
                'mission_image',
                $_FILES['mission_image']
            );

            if ($upload['success']) {

                $this->siteSetting->set(
                    'about_mission_image',
                    $upload['asset']['file_path']
                );
            }
        }


        if (
            isset($_FILES['philosophy_image']) &&
            $_FILES['philosophy_image']['size'] > 0
        ) {

            $upload = $this->pageAsset->upload(
                'about',
                'philosophy_image',
                $_FILES['philosophy_image']
            );

            if ($upload['success']) {

                $this->siteSetting->set(
                    'about_philosophy_image',
                    $upload['asset']['file_path']
                );
            }
        }



        // Save text content ONLY if submitted
        if (isset($_POST['intro_text'])) {
            $introText = trim((string)$_POST['intro_text']);
            $this->siteSetting->set('about_intro_text', $introText);
        }

        // Handle intro image upload
        if (isset($_FILES['intro_image']) && $_FILES['intro_image']['size'] > 0) {
            $result = $this->pageAsset->upload('about', 'intro_image', $_FILES['intro_image']);
            if ($result['success']) {
                $this->siteSetting->set('about_intro_image', $result['asset']['file_path']);
            }
        }

        // Handle descripton timeline each year 
        if (isset($_POST['timeline_desc']) && is_array($_POST['timeline_desc'])){
            foreach ($_POST['timeline_desc'] as $year => $description) {
                $year = (int)$year;
                $cleanDesc = trim ((string)$description); 

                $this->siteSetting->set("about_timeline_text_{$year}", $cleanDesc);
            }
        }

        // Handle hero video upload
        if (isset($_FILES['hero_image']) && $_FILES['hero_image']['size'] > 0) {
            $result = $this->pageAsset->upload('about', 'hero_image', $_FILES['hero_image']);
            if ($result['success']) {
                $this->siteSetting->set('about_hero_image', $result['asset']['file_path']);
            }
        }

        $_SESSION['success'] = 'Cập nhật nội dung trang About thành công!';
        header('Location: ' . ADMIN_URL . 'page-content/about');
        exit;
    }

    /**
     * GET /admin/page-content/faq
     * FAQ page content editor
     */
    public function faq(): void
    {
        Auth::requireAdmin();
        SEO::set('Quản lý nội dung trang FAQ');

        $faqIntro = $this->siteSetting->get('faq_intro_text', '');
        $faqImage = $this->siteSetting->get('faq_intro_image', '');

        View::render('admin/pages/faq-edit', [
            'faqIntro' => $faqIntro,
            'faqImage' => $faqImage,
        ], 'admin');
    }

    /**
     * POST /admin/page-content/faq/save
     * Save FAQ page content
     */
    public function faqSave(): void
    {
        Auth::requireAdmin();
        Auth::verifyCsrf();

        // Save text content ONLY if submitted
        if (isset($_POST['intro_text'])) {
            $introText = trim((string)$_POST['intro_text']);
            $this->siteSetting->set('faq_intro_text', $introText);
        }

        if (isset($_FILES['intro_image']) && $_FILES['intro_image']['size'] > 0) {
            $result = $this->pageAsset->upload('faq', 'intro_image', $_FILES['intro_image']);
            if ($result['success']) {
                $this->siteSetting->set('faq_intro_image', $result['asset']['file_path']);
            }
        }

        $_SESSION['success'] = 'Cập nhật nội dung trang FAQ thành công!';
        header('Location: ' . ADMIN_URL . 'page-content/faq');
        exit;
    }

    /**
     * Handle AJAX/Form uploads for timeline and award images
     * POST /admin/page-content/upload?page=about&type=timeline&year=2023
     */
    public function upload(): void
    {
        Auth::requireAdmin();

        $page = $_GET['page'] ?? '';
        $assetKey = $_GET['key'] ?? '';

        if (!$assetKey){
            $type = $_GET['type'] ?? ''; 
            $year = $_GET['year'] ?? ''; 
            if ($type && $year){
                $assetKey = $type . '_'. $year;
            }
        }



        if (!$page || !$assetKey) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing parameters']);
            exit;
        }

        if (!isset($_FILES['file'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No file provided']);
            exit;
        }

        $result = $this->pageAsset->upload($page, $assetKey, $_FILES['file']);

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    /**
     * Delete asset
     * POST /admin/page-content/delete-asset?page=about&key=timeline_2023_main
     */
    /**
 * POST /admin/page-content/delete-asset?page=about&key=timeline_2023_main
 */
public function deleteAsset(): void
{
    Auth::requireAdmin();
    // Auth::verifyCsrf(); // Bật nếu bạn có gửi kèm token

    $page = $_GET['page'] ?? '';
    $assetKey = $_GET['key'] ?? '';

    header('Content-Type: application/json');

    if (!$page || !$assetKey) {
        echo json_encode(['success' => false, 'message' => 'Thiếu thông tin xóa.']);
        exit;
    }

    // Model PageAsset của bạn đã có hàm delete: 
    // Nó vừa xóa file trong thư mục public vừa xóa bản ghi DB
    $result = $this->pageAsset->delete($page, $assetKey);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Đã xóa ảnh thành công.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không thể xóa ảnh hoặc ảnh không tồn tại.']);
    }
    exit;
}


public function addAward(): void
{
    Auth::requireAdmin();

    $title = trim($_POST['title'] ?? '');
    $year = (int)($_POST['award_year'] ?? 0);

    if (!$title || !$year || !isset($_FILES['image'])) {
        $_SESSION['error'] = 'Thiếu dữ liệu';
        header('Location: ' . ADMIN_URL . 'page-content/about');
        exit;
    }

    $assetKey = 'award_' . uniqid();

    $result = $this->pageAsset->upload(
        'about',
        $assetKey,
        $_FILES['image']
    );

    if (!$result['success']) {
        $_SESSION['error'] = $result['message'];
        header('Location: ' . ADMIN_URL . 'page-content/about');
        exit;
    }

    $this->aboutAward->create(
        $title,
        $year,
        $result['asset']['file_path']
    );

    $_SESSION['success'] = 'Thêm giải thưởng thành công';

    header('Location: ' . ADMIN_URL . 'page-content/about#awards-pane');
    exit;
}

public function aboutdeleteAward(): void
{
    Auth::requireAdmin();

    $id = (int)($_GET['id'] ?? 0);

    header('Content-Type: application/json');

    if (!$id) {
        echo json_encode([
            'success' => false,
            'message' => 'ID không hợp lệ'
        ]);
        exit;
    }

    $result = $this->aboutAward->delete($id);

    echo json_encode([
        'success' => $result
    ]);

    exit;
}
}