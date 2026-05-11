<?php
/**
 * app/controllers/admin/NewsAdminController.php
 * Owner: Nhat Tan 
 * Routes: POST /admin/news/save   POST /admin/news/delete/{id} , ... more CRUD api
 *
 *  * Full CRUD for news articles.
 * Includes SEO fields: meta_title, meta_description.
 * Slug is auto-generated from the title.
 */
class NewsAdminController
{
    private const PER_PAGE = 10;
    public function __construct() {}

    public function index(): void
    {
        $q       = trim($_GET['q'] ?? '');
        $catalog = trim($_GET['catalog'] ?? '');
        $sort    = $_GET['sort'] ?? 'time'; 
        $state   = trim($_GET['state'] ?? '');
        $page    = max(1, (int) ($_GET['page'] ?? 1));

        $totalItems = News::count($q, $catalog, $state);
        $pg = new Pagination($totalItems, $page, self::PER_PAGE);

        $articles = News::getAll($pg->current, self::PER_PAGE, $q, $catalog, $sort, $state);

        View::render('admin/news/index', [
            'articles' => $articles,
            'q'        => $q,
            'catalog'  => $catalog,
            'state'    => $state,
            'sort'     => $sort,
            'pg'       => $pg,
            'catalogs' => News::CATALOGS,
            'states'   => News::STATES
        ], 'admin');
    }

    public function create(): void
    {
        View::render('admin/news/form', [
            'article'  => null,
            'catalogs' => News::CATALOGS,
            'states'   => News::STATES
        ], 'admin');
    }

    public function show(int $id): void
    {
        $basicInfo = News::getById($id);
        if (!$basicInfo) {
            $_SESSION['errors'] = ['Bài viết không tồn tại.'];
            header('Location: ' . ADMIN_URL . 'news');
            exit;
        }

        $article = News::getBySlug($basicInfo['slug'], '', false);
        View::render('admin/news/show', ['article' => $article], 'admin');
    }

    public function edit(int $id): void
    {
        $basicInfo = News::getById($id);
        if (!$basicInfo) {
            $_SESSION['errors'] = ['Bài viết không tồn tại.'];
            header('Location: ' . ADMIN_URL . 'news');
            exit;
        }

        $article = News::getBySlug($basicInfo['slug'], '', false); 

        View::render('admin/news/form', [
            'article'  => $article,
            'isEdit'   => true,
            'catalogs' => News::CATALOGS,
            'states'   => News::STATES
        ], 'admin');
    }

    public function delete(int $id): void
    {
        Auth::verifyCsrf();

        if (News::delete($id)) {
            $targetDir = ROOT . "/public/images/news/{$id}";
            $this->deleteDirectory($targetDir);
            $_SESSION['flash'] = 'Đã xóa bài viết và thư mục hình ảnh.';
        }
        header('Location: ' . ADMIN_URL . 'news');
        exit;
    }

    private function deleteDirectory($dir) {
        if (!is_dir($dir)) return false;
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->deleteDirectory("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    private function createSlug(string $str): string
    {
        $unicode = array(
            'a'=>'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ', 'd'=>'đ',
            'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ', 'i'=>'í|ì|ỉ|ĩ|ị',
            'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ', 'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y'=>'ý|ỳ|ỷ|ỹ|ỵ',
            'A'=>'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ', 'D'=>'Đ',
            'E'=>'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ', 'I'=>'Í|Ì|Ỉ|Ĩ|Ị',
            'O'=>'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ', 'U'=>'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'Y'=>'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
        );
        foreach($unicode as $nonUnicode => $uni) {
            $str = preg_replace("/($uni)/i", $nonUnicode, $str);
        }
        $str = strtolower($str);
        $str = preg_replace('/[^a-z0-9]+/i', '-', $str);
        return trim($str, '-');
    }

    public function save_builder(): void
    {
        ob_start(); 

        try {
            if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['_csrf'] ?? '')) {
                ob_end_clean();
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Lỗi bảo mật: Token CSRF không hợp lệ.']);
                exit;
            }

            $id      = (int) ($_POST['id'] ?? 0);
            $title   = trim($_POST['title'] ?? '');
            $catalog = $_POST['catalog'] ?? 'Tin tức';
            $state   = $_POST['news_state'] ?? 'Hiển thị';

            $slug = $this->createSlug($title);
            $originalSlug = $slug;
            $count = 1;
            while (News::isSlugExists($slug, $id)) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }

            if ($id === 0) {
                $id = News::create([
                    'title' => $title, 'slug' => $slug, 'body' => '', 
                    'catalog' => $catalog, 'news_state' => $state
                ]);
            }

            $uploadDir = ROOT . "/public/images/news/{$id}/";
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }

            $existingFiles = glob($uploadDir . '*.*');
            $maxIndex = 0;
            if ($existingFiles) {
                foreach ($existingFiles as $f) {
                    $basename = pathinfo($f, PATHINFO_FILENAME);
                    if (is_numeric($basename) && (int)$basename > $maxIndex) {
                        $maxIndex = (int)$basename;
                    }
                }
            }
            $nextImgIndex = $maxIndex + 1; 

            $blocks = $_POST['blocks'] ?? [];
            $finalBodyParts = [];
            $imagesData = [];

            $oldThumbnail = trim($_POST['old_thumbnail'] ?? '');
            $thumbDbPath = '';

            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['thumbnail']['tmp_name'];
                $originalName = basename($_FILES['thumbnail']['name']); 
                $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                
                if ($oldThumbnail !== '') {
                    $oldThumbPathAbs = ROOT . '/' . ltrim(str_replace('\\', '/', $oldThumbnail), '/');
                    if (file_exists($oldThumbPathAbs)) {
                        $oldBasename = pathinfo($oldThumbPathAbs, PATHINFO_FILENAME);
                        if (strpos(strtolower($oldBasename), 'thumbnail') === 0) {
                            $oldExt = strtolower(pathinfo($oldThumbPathAbs, PATHINFO_EXTENSION));
                            rename($oldThumbPathAbs, $uploadDir . $nextImgIndex . '.' . $oldExt);
                            $nextImgIndex++;
                        }
                    }
                }

                $newThumbName = 'thumbnail.' . $fileExtension;
                $destPath = $uploadDir . $newThumbName;
                if (@move_uploaded_file($fileTmpPath, $destPath)) {
                    $thumbDbPath = "public/images/news/{$id}/{$newThumbName}";
                }

            } elseif ($oldThumbnail !== '') {
                $oldThumbPathAbs = ROOT . '/' . ltrim(str_replace('\\', '/', $oldThumbnail), '/');
                if (file_exists($oldThumbPathAbs)) {
                    $oldBasename = pathinfo($oldThumbPathAbs, PATHINFO_FILENAME);
                    
                    if (strpos(strtolower($oldBasename), 'thumbnail') !== 0) {
                        $ext = strtolower(pathinfo($oldThumbPathAbs, PATHINFO_EXTENSION));
                        $newThumbName = 'thumbnail.' . $ext;
                        copy($oldThumbPathAbs, $uploadDir . $newThumbName);
                        $thumbDbPath = "public/images/news/{$id}/{$newThumbName}";
                    } else {
                        $thumbDbPath = "public/images/news/{$id}/" . basename($oldThumbnail);
                    }
                } else {
                    $thumbDbPath = "public/images/news/{$id}/" . basename($oldThumbnail);
                }
            }

            if ($thumbDbPath !== '') {
                $imagesData[] = [
                    'img_link' => $thumbDbPath, 
                    'img_des'  => 'Thumbnail'
                ];
            }

            foreach ($blocks as $index => $block) {
                $type = $block['type'];

                if ($type === 'text') {
                    $content = trim($block['content'] ?? '');
                    if ($content !== '') {
                        $finalBodyParts[] = "<p class='mb-4 text-justify leading-relaxed'>" . nl2br(htmlspecialchars($content)) . "</p>";
                    }
                } 
                elseif ($type === 'image') {
                    $desc = htmlspecialchars(trim($block['desc'] ?? ''));
                    $webPath = '';
                    $dbPath  = ''; 

                    if (isset($_FILES['block_files']['tmp_name'][$index]) && is_uploaded_file($_FILES['block_files']['tmp_name'][$index])) {
                        
                        $fileTmpPath = $_FILES['block_files']['tmp_name'][$index];
                        $originalName = basename($_FILES['block_files']['name'][$index]); 
                        
                        // Đánh số từ $nextImgIndex
                        $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                        $newFileName = $nextImgIndex . '.' . $fileExtension;
                        $nextImgIndex++; 
                        
                        $destPath = $uploadDir . $newFileName;
                        if (@move_uploaded_file($fileTmpPath, $destPath)) {
                            $webPath = "public/images/news/{$id}/{$newFileName}";
                            $dbPath  = "public/images/news/{$id}/{$newFileName}";
                        }
                    } 
                    else {
                        $oldLink = trim($block['old_link'] ?? '');
                        if ($oldLink !== '') {
                            // Chuyển đường dẫn thành chuẩn Web /
                            $fileName = basename(str_replace('\\', '/', $oldLink));
                            $webPath = "public/images/news/{$id}/{$fileName}";
                            $dbPath  = "public/images/news/{$id}/{$fileName}";
                        }
                    }

                    if ($webPath !== '' && $dbPath !== '') {
                        $figureHtml = "<figure class='my-6 text-center'>";
                        $figureHtml .= "<img src='" . BASE_URL . "{$webPath}' alt='{$desc}' class='w-full max-h-[600px] object-cover rounded-lg shadow-sm'>";
                        if ($desc !== '') $figureHtml .= "<figcaption class='mt-2 text-sm text-gray-500 italic'>{$desc}</figcaption>";
                        $figureHtml .= "</figure>";

                        $finalBodyParts[] = $figureHtml;
                        
                        $imagesData[] = [
                            'img_link' => $dbPath, 
                            'img_des'  => $desc
                        ];
                    }
                }
            }

            $finalBody = implode("\n\n", $finalBodyParts);

            News::update($id, [
                'title'      => $title,
                'slug'       => $slug,
                'body'       => $finalBody,
                'catalog'    => $catalog,
                'news_state' => $state
            ], [], $imagesData);

            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success']);
            exit;

        } catch (Throwable $e) {
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Lỗi PHP: ' . $e->getMessage()]);
            exit;
        }
    }
}