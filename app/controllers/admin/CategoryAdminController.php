<?php

/**
 * app/controllers/admin/CategoryAdminController.php — Quản lý danh mục & thuộc tính động (T1-13)
 * Owner: T1 (Data Storage)
 *
 * Routes:
 *   GET  /admin/categories                 -> index()
 *   GET  /admin/categories/create          -> create()
 *   GET  /admin/categories/edit/{id}       -> edit($id)
 *   POST /admin/categories/save            -> save()
 *   POST /admin/categories/delete/{id}     -> delete($id)
 *   POST /admin/categories/attribute-save  -> attributeSave()
 *   POST /admin/categories/attribute-delete-> attributeDelete()
 */
class CategoryAdminController
{
    public function index(): void
    {
        $categories = Category::getAdminAll();
        $productCounts = Category::productCountsByCategory();

        SEO::set('Quản lý danh mục & thuộc tính động');
        View::render('admin/categories/index', [
            'categories'    => $categories,
            'productCounts' => $productCounts,
        ], 'admin');
    }

    public function create(): void
    {
        SEO::set('Thêm danh mục mới');
        View::render('admin/categories/form', [
            'category'     => null,
            'productCount' => 0,
        ], 'admin');
    }

    public function edit($id = 0): void
    {
        $id = (int)$id;
        $category = $id > 0 ? Category::getById($id) : null;
        if (!$category) {
            $_SESSION['errors'] = ['Không tìm thấy danh mục.'];
            header('Location: ' . ADMIN_URL . 'categories');
            exit;
        }

        SEO::set('Cấu hình danh mục: ' . $category['name']);
        View::render('admin/categories/form', [
            'category'     => $category,
            'productCount' => Category::countProducts($id),
        ], 'admin');
    }

    public function save(): void
    {
        $this->requirePostAndCsrf();

        $id = (int)($_POST['id'] ?? 0);
        $isEdit = $id > 0;

        $name = $this->cleanText($_POST['name'] ?? '');
        $slug = $this->slugify((string)($_POST['slug'] ?? ''));
        if ($slug === '' && $name !== '') {
            $slug = Product::slugify($name);
        }
        $kind = trim((string)($_POST['kind'] ?? 'vehicle'));
        if (!in_array($kind, Category::KINDS, true)) {
            $kind = 'vehicle';
        }
        $description = $this->cleanText($_POST['description'] ?? '');
        $isActive = isset($_POST['is_active']);

        $errors = [];
        if ($name === '') {
            $errors[] = 'Vui lòng nhập tên danh mục.';
        } elseif (mb_strlen($name, 'UTF-8') > 100) {
            $errors[] = 'Tên danh mục tối đa 100 ký tự.';
        }
        if ($slug === '' || preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) !== 1) {
            $errors[] = 'Slug không hợp lệ (chỉ gồm chữ thường a-z, số 0-9 và dấu gạch ngang).';
        } elseif (Category::countBySlug($slug, $isEdit ? $id : null) > 0) {
            $errors[] = 'Slug danh mục đã tồn tại.';
        }

        if ($errors) {
            $_SESSION['errors'] = $errors;
            header('Location: ' . ADMIN_URL . ($isEdit ? ('categories/edit/' . $id) : 'categories/create'));
            exit;
        }

        try {
            if ($isEdit) {
                if (!Category::getById($id)) {
                    $_SESSION['errors'] = ['Không tìm thấy danh mục cần sửa.'];
                    header('Location: ' . ADMIN_URL . 'categories');
                    exit;
                }
                Category::update($id, $name, $slug, $kind, $description, $isActive);
                $_SESSION['flash'] = 'Đã cập nhật thông tin danh mục.';
                header('Location: ' . ADMIN_URL . 'categories/edit/' . $id);
                exit;
            }

            $newId = Category::create($name, $slug, $kind, $description, $isActive);
            $_SESSION['flash'] = 'Đã tạo danh mục mới. Bạn có thể thiết lập bộ thông số sản phẩm ở khung bên phải.';
            header('Location: ' . ADMIN_URL . 'categories/edit/' . $newId);
            exit;
        } catch (InvalidArgumentException $e) {
            $_SESSION['errors'] = [$e->getMessage()];
            header('Location: ' . ADMIN_URL . ($isEdit ? ('categories/edit/' . $id) : 'categories/create'));
            exit;
        }
    }

    public function delete($id = 0): void
    {
        $this->requirePostAndCsrf();

        $id = (int)$id;
        $category = $id > 0 ? Category::getById($id) : null;
        if (!$category) {
            $_SESSION['errors'] = ['Không tìm thấy danh mục.'];
            header('Location: ' . ADMIN_URL . 'categories');
            exit;
        }

        $productCount = Category::countProducts($id);
        if ($productCount > 0 || !Category::delete($id)) {
            $_SESSION['errors'] = [
                sprintf('Không thể xóa danh mục "%s" vì đang có %d sản phẩm thuộc danh mục này.', $category['name'], $productCount),
            ];
            header('Location: ' . ADMIN_URL . 'categories');
            exit;
        }

        $_SESSION['flash'] = 'Đã xóa danh mục thành công.';
        header('Location: ' . ADMIN_URL . 'categories');
        exit;
    }

    public function attributeSave(): void
    {
        $this->requirePostAndCsrf();

        $categoryId = (int)($_POST['category_id'] ?? 0);
        $category = $categoryId > 0 ? Category::getById($categoryId) : null;
        if (!$category) {
            $_SESSION['errors'] = ['Không tìm thấy danh mục.'];
            header('Location: ' . ADMIN_URL . 'categories');
            exit;
        }

        $scope = trim((string)($_POST['scope'] ?? 'dynamic'));
        if (!in_array($scope, Category::ATTR_SCOPES, true)) {
            $scope = 'dynamic';
        }
        $mode = trim((string)($_POST['mode'] ?? 'add')) === 'edit' ? 'edit' : 'add';

        $def = [
            'key'        => trim((string)($_POST['key'] ?? '')),
            'label'      => $this->cleanText($_POST['label'] ?? ''),
            'type'       => trim((string)($_POST['type'] ?? 'string')),
            'unit'       => $this->cleanText($_POST['unit'] ?? ''),
            'options'    => (string)($_POST['options'] ?? ''),
            'required'   => isset($_POST['required']),
            'filterable' => isset($_POST['filterable']),
        ];

        try {
            $displayLabel = $def['label'] !== '' ? $def['label'] : $def['key'];
            if ($mode === 'edit') {
                $ok = Category::updateAttribute($categoryId, $scope, $def);
                if ($ok) {
                    $_SESSION['flash'] = sprintf('Đã cập nhật thông số "%s".', $displayLabel);
                } else {
                    $_SESSION['errors'] = [sprintf('Không tìm thấy thông số "%s" để cập nhật.', $displayLabel)];
                }
            } else {
                $ok = Category::addAttribute($categoryId, $scope, $def);
                if ($ok) {
                    $_SESSION['flash'] = sprintf('Đã thêm thông số "%s" vào danh mục "%s".', $displayLabel, $category['name']);
                } else {
                    $_SESSION['errors'] = [sprintf('Mã định danh "%s" đã tồn tại trong danh mục này.', $def['key'])];
                }
            }
        } catch (InvalidArgumentException $e) {
            $_SESSION['errors'] = [$e->getMessage()];
        }

        header('Location: ' . ADMIN_URL . 'categories/edit/' . $categoryId);
        exit;
    }

    public function attributeDelete(): void
    {
        $this->requirePostAndCsrf();

        $categoryId = (int)($_POST['category_id'] ?? 0);
        $scope = trim((string)($_POST['scope'] ?? 'dynamic'));
        $key = trim((string)($_POST['key'] ?? ''));

        if ($categoryId <= 0 || !Category::getById($categoryId)) {
            $_SESSION['errors'] = ['Không tìm thấy danh mục.'];
            header('Location: ' . ADMIN_URL . 'categories');
            exit;
        }

        try {
            if (Category::removeAttribute($categoryId, $scope, $key)) {
                $_SESSION['flash'] = sprintf('Đã gỡ thông số "%s" khỏi biểu mẫu nhập liệu (dữ liệu đã lưu trên các sản phẩm cũ vẫn được giữ nguyên).', $key);
            } else {
                $_SESSION['errors'] = ['Không tìm thấy thông số cần gỡ.'];
            }
        } catch (InvalidArgumentException $e) {
            $_SESSION['errors'] = [$e->getMessage()];
        }

        header('Location: ' . ADMIN_URL . 'categories/edit/' . $categoryId);
        exit;
    }

    private function requirePostAndCsrf(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            header('Location: ' . ADMIN_URL . 'categories');
            exit;
        }
        Auth::verifyCsrf();
    }

    private function cleanText(mixed $value): string
    {
        return trim(strip_tags((string)$value));
    }

    private function slugify(string $value): string
    {
        $slug = strtolower(trim($value));
        $slug = (string)preg_replace('/[^a-z0-9]+/', '-', $slug);
        return trim($slug, '-');
    }
}
