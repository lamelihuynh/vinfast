<?php
/**
 * app/controllers/admin/FaqAdminController.php
 * Owner: Nhat Linh 
 * Routes: POST /admin/faq/save   POST /admin/faq/delete/{id}
 *
 *  * CRUD management for FAQ entries.
 * Admin sets question, answer, sort order, and active status.
 */
/**
 * app/controllers/admin/FaqAdminController.php
 * Owner: Nhat Linh 
 * Routes: GET /admin/faq  GET /admin/faq/create  POST /admin/faq/save  
 *         GET /admin/faq/edit/{id}  POST /admin/faq/delete/{id}
 *
 *  * CRUD management for FAQ entries.
 * Admin sets question, answer, sort order, and active status.
 */
class FaqAdminController {
    /** 
     * GET admin/faq
     * List all FAQs with pagination
    */
    public function index(): void {
        // $page = max(1, (int) ($_GET['page'] ?? 1))  ; 

        // $total = Faq::countAll(); // number

        // $faqs = Faq::getForAdmin($page, PER_PAGE); // array [FAQ]

        // $pg = new Pagination($total, $page, PER_PAGE); 

        $faqs = Faq::getAllForAdminDatatable(); 

        SEO::set('Quản lý FAQ'); 


        View::render('admin/faq/index' ,[
            'faqs' => $faqs,
            // 'pg' => $pg
        ], 'admin');

    }

    /**
     * GET /admin/faq/create
     * Show form to create new FAQ
     */

    public function create(): void {
        SEO::set('Tạo FAQ mới'); 
        $topics = FaqTopic::all();

        View::render('admin/faq/form' , [
            'faq' => null,
            'topics' => $topics,
            'action' => 'create', 
        ], 'admin');
    }


    /**
     * GET /admin/faq/edit/{id}
     * Show form to edit FAQ
     */
    public function edit (int $id): void {
        $faq = Faq::getById($id); 
        $topics = FaqTopic::all();
        if (!$faq ) {
            $_SESSION['error'] = ['FAQ Không tồn tại.']; 
            header('Location: '.ADMIN_URL.'faq'); 
            exit; 
        }

        SEO::set('Chỉnh sửa FAQ'); 

        View::render('admin/faq/form',[
            'faq' => $faq, 
            'topics' => $topics,
            'action' => 'edit', 
        ],'admin');
    }

    /**
     * POST /admin/faq/save
     * Save FAQ (create or update)
     */
    public function save(): void {
        // Verify CSRF and method
        if ($_SERVER['REQUEST_METHOD'] != 'POST'){
            http_response_code(405); 
            exit ('Method Not Allowed');
        }

        Auth::verifyCsrf($_POST['_csrf']); 

        // Validate input

        $id = isset($_POST['id']) ? (int)$_POST['id']  : 0; 
        $question = trim($_POST['question'] ?? ''); 
        $answer = trim($_POST['answer'] ?? ''); 
        $sortOrder = (int) ($_POST['sort_order'] ?? 0); 
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $topicId = (int)($_POST['topic_id'] ?? 0);

        $errors = []; 

        if ($question === '') {
            $errors['question'] = 'Câu hỏi không được để trống'; 
        }
        if ($answer === '') {
            $errors['question'] = 'Câu trả lời không được để trống'; 
        }

        if (!empty($errors)){
            $_SESSION['errors'] = $errors;

            if ($id > 0){
                header('Location: '.ADMIN_URL.'faq/edit/'.$id);
            }
            else {
                header('Location'.ADMIN_URL.'faq/create'); 
            }
            exit; 
        }
        if ($topicId <= 0) {
           $errors['topic_id'] = 'Vui lòng chọn chủ đề';
        }
        // Save 
        try {
            if ($id > 0){
                // Update existing 
                Faq::update($id, $topicId, $question, $answer, $sortOrder, $isActive === 1);
                $_SESSION['flash'] = 'Cập nhật FAQ thành công.'; 
            } else {
                Faq::create($topicId, $question, $answer, $sortOrder, $isActive === 1);
                $_SESSION['flash'] = 'Tạo FAQ mới thành công.'; 
            }
        } catch (Throwable $e){
            $_SESSION['errors'] = ['Có lỗi xảy ra: '.$e->getMessage()]; 
        }

        header('Location: '.ADMIN_URL.'faq');
        exit;
}

    /**
     * POST /admin/faq/delete/{id}
     * Delete FAQ
     */
    public function delete($id): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
            http_response_code(405); 
            exit ('Method Not Allowed'); 
        }

        Auth::verifyCsrf($_POST['_csrf']); 

        
        try {
            $faq = Faq::getById($id);
            if (!$faq){
                $_SESSION['errors'] = ['FAQ không tồn tại.'];
            } else {
                Faq::delete($id); 
                $_SESSION['flash'] = 'Xoá FAQ thành công.';
            }

        } catch (Throwable $e){
            $_SESSION['errors'] = ['Có lỗi xảy ra: '.$e->getMessage()];
        }
        header('Location: '.ADMIN_URL.'faq'); 
        exit; 
    }

}