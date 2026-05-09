<?php

/**
 * app/controllers/admin/ProductAdminController.php
 * Owner: Hai Nam 
 * Routes: /admin/products   /admin/products/form   /admin/products/form/{id}
 *         POST /admin/products/save   POST /admin/products/delete/{id}
 *
 *  * Full CRUD for vehicle listings.
 * Image upload uses Dropzone.js (multi-image).
 * Specs are stored as JSON key-value pairs.
 */
class ProductAdminController
{
    private const PER_PAGE = 8;

    public function index(): void
    {
        $q = trim((string)($_GET['q'] ?? ''));
        $cat = (int)($_GET['cat'] ?? 0);
        $status = trim((string)($_GET['status'] ?? 'all'));
        $priceMinRaw = trim((string)($_GET['price_min'] ?? ''));
        $priceMaxRaw = trim((string)($_GET['price_max'] ?? ''));
        $price_min = $priceMinRaw === '' ? null : (float)str_replace([',', ' '], '', $priceMinRaw);
        $price_max = $priceMaxRaw === '' ? null : (float)str_replace([',', ' '], '', $priceMaxRaw);
        $sort = trim((string)($_GET['sort'] ?? 'default'));
        $allowedStatus = ['all', 'active', 'inactive'];
        if (!in_array($status, $allowedStatus, true)) {
            $status = 'all';
        }

        $page = max(1, (int)($_GET['page'] ?? 1));

        $filters = [
            'search' => $q,
            'category_id' => $cat > 0 ? $cat : null,
            'status' => $status,
            'price_min' => $price_min,
            'price_max' => $price_max,
            'sort' => $sort,
        ];

        $summaryFilters = [
            'search' => $q,
            'category_id' => $cat > 0 ? $cat : null,
            'status' => 'all',
            'price_min' => $price_min,
            'price_max' => $price_max,
        ];
        $summary = [
            'total' => Product::countAdminList($summaryFilters),
            'active' => Product::countAdminList(array_merge($summaryFilters, ['status' => 'active'])),
            'inactive' => Product::countAdminList(array_merge($summaryFilters, ['status' => 'inactive'])),
            'categories' => Product::countAdminDistinctCategories($summaryFilters),
        ];

        $total = Product::countAdminList($filters);
        $pg = new Pagination($total, $page, self::PER_PAGE);
        $products = Product::getAdminList($filters, $pg->current, $pg->perPage);
        $productsForModal = $this->mapProductsForModal($products);
        $cats = Category::getAll();

        $query = array_filter([
            'q' => $q !== '' ? $q : null,
            'cat' => $cat > 0 ? $cat : null,
            'status' => $status !== 'all' ? $status : null,
            'price_min' => $priceMinRaw !== '' ? $priceMinRaw : null,
            'price_max' => $priceMaxRaw !== '' ? $priceMaxRaw : null,
            'sort' => $sort !== 'default' ? $sort : null,
        ], static function ($value): bool {
            return $value !== null;
        });
        $baseQuery = http_build_query($query);
        $pageUrl = ADMIN_URL . 'products?' . ($baseQuery !== '' ? $baseQuery . '&' : '') . 'page=';

        SEO::set('Admin Products');
        View::render('admin/products/index', [
            'products' => $products,
            'productsForModal' => $productsForModal,
            'cats' => $cats,
            'q' => $q,
            'cat' => $cat,
            'status' => $status,
            'price_min' => $priceMinRaw,
            'price_max' => $priceMaxRaw,
            'sort' => $sort,
            'summary' => $summary,
            'pg' => $pg,
            'pageUrl' => $pageUrl,
        ], 'admin');
    }

    private function mapProductsForModal(array $products): array
    {
        return array_map(function (array $p): array {
            $specs = is_array($p['specs'] ?? null) ? $p['specs'] : [];
            $images = is_array($p['images'] ?? null) ? $p['images'] : [];

            $localImages = [];
            foreach ($images as $img) {
                if (!is_string($img) || trim($img) === '') {
                    continue;
                }
                if (preg_match('/^https?:\/\//i', $img)) {
                    continue;
                }
                $localImages[] = ltrim($img, '/');
            }

            return [
                'id' => (int)($p['id'] ?? 0),
                'category_id' => (int)($p['category_id'] ?? 0),
                'name' => (string)($p['name'] ?? ''),
                'slug' => (string)($p['slug'] ?? ''),
                'price' => (float)($p['price'] ?? 0),
                'description' => (string)($p['description'] ?? ''),
                'is_active' => (int)($p['is_active'] ?? 0),
                'range' => (string)($specs['range'] ?? ''),
                'power' => (string)($specs['power'] ?? ''),
                'acceleration' => (string)($specs['acceleration'] ?? ''),
                'max_speed' => (string)($specs['max_speed'] ?? ''),
                'battery' => (string)($specs['battery'] ?? ''),
                'deposit_amount' => max(0, (int)($specs['deposit_amount'] ?? 15000000)),
                'deposit_non_refundable' => !empty($specs['deposit_non_refundable']) ? 1 : 0,
                'exterior_colors' => is_array($specs['exterior_colors'] ?? null) ? $specs['exterior_colors'] : [],
                'images' => array_values($localImages),
                'family' => $this->extractImageFamily((string)($p['slug'] ?? '')),
            ];
        }, $products);
    }

    public function form($id = 0): void
    {
        $id = (int)$id;
        $product = null;
        if ($id > 0) {
            $product = Product::getByIdAdmin($id);
            if (!$product) {
                $_SESSION['errors'] = ['Product not found.'];
                header('Location: ' . ADMIN_URL . 'products');
                exit;
            }
        }

        $old = $_SESSION['old'] ?? null;
        unset($_SESSION['old']);

        $cats = Category::getAll();
        SEO::set($id > 0 ? 'Edit Product' : 'Create Product');
        View::render('admin/products/form', [
            'product' => $product,
            'cats' => $cats,
            'old' => is_array($old) ? $old : [],
        ], 'admin');
    }

    public function save(): void
    {
        Auth::verifyCsrf();

        $id = (int)($_POST['id'] ?? 0);
        $isEdit = $id > 0;
        $existing = $isEdit ? Product::getByIdAdmin($id) : null;

        if ($isEdit && !$existing) {
            $_SESSION['errors'] = ['Product not found.'];
            header('Location: ' . ADMIN_URL . 'products');
            exit;
        }

        $payload = $this->collectPayload();
        $errors = $this->validatePayload($payload, $id);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $payload + ['id' => $id];
            header('Location: ' . ADMIN_URL . 'products/form' . ($id > 0 ? '/' . $id : ''));
            exit;
        }

        $existingImages = array_values(array_unique(array_filter(
            array_map('trim', (array)($_POST['existing_images'] ?? [])),
            static fn($img) => is_string($img) && $img && !preg_match('/^https?:\/\//i', $img)
        )));
        $newImages = [];

        try {
            $imageSubdir = $this->resolveImageFamilyFromPayload($payload, is_array($existing) ? $existing : []);
            $newImages = $this->uploadNewImages($_FILES['images'] ?? null, $imageSubdir);
        } catch (RuntimeException $e) {
            $_SESSION['errors'] = [$e->getMessage()];
            $_SESSION['old'] = $payload + ['id' => $id, 'existing_images' => $existingImages];
            header('Location: ' . ADMIN_URL . 'products/form' . ($id > 0 ? '/' . $id : ''));
            exit;
        }

        $allImages = array_values(array_unique(array_merge($existingImages, $newImages)));
        $mainImage = $this->resolveMainImage($allImages, $newImages);
        $allImages = $this->prioritizeMainImage($allImages, $mainImage);

        if ($isEdit) {
            $this->persistUpdate($id, is_array($existing) ? $existing : [], $payload, $allImages);
            $_SESSION['flash'] = 'Sản phẩm đã được cập nhật thành công.';
        } else {
            $this->persistCreate($payload, $allImages);
            $_SESSION['flash'] = 'Sản phẩm đã được tạo thành công.';
        }
        header('Location: ' . ADMIN_URL . 'products');
        exit;
    }

    private function persistCreate(array $payload, array $allImages): void
    {
        Product::create(...$this->buildProductMutationArgs($payload, $allImages));
    }

    private function persistUpdate(int $id, array $existing, array $payload, array $allImages): void
    {
        $oldImages = is_array($existing['images'] ?? null) ? $existing['images'] : [];
        $removedImages = array_values(array_diff($oldImages, $allImages));

        Product::updateById($id, ...$this->buildProductMutationArgs($payload, $allImages));

        foreach ($removedImages as $img) {
            $this->deleteUploadedImage((string)$img);
        }
    }

    public function toggle($id = 0): void
    {
        Auth::verifyCsrf();
        $id = (int)$id;
        $product = Product::getByIdAdmin($id);
        if (!$product) {
            $_SESSION['errors'] = ['Product not found.'];
            header('Location: ' . ADMIN_URL . 'products');
            exit;
        }

        $next = ((int)($product['is_active'] ?? 0) === 1) ? 0 : 1;
        Product::setActive($id, $next);
        $_SESSION['flash'] = $next === 1 ? 'Sản phẩm hiện đang được hiển thị.' : 'Sản phẩm hiện đang bị ẩn.';
        header('Location: ' . ADMIN_URL . 'products');
        exit;
    }

    public function delete($id = 0): void
    {
        Auth::verifyCsrf();
        $id = (int)$id;
        $product = Product::getByIdAdmin($id);
        if (!$product) {
            $_SESSION['errors'] = ['Product not found.'];
            header('Location: ' . ADMIN_URL . 'products');
            exit;
        }

        if (Product::hasOrders($id)) {
            Product::setActive($id, 0);
            $_SESSION['errors'] = ['Không thể xóa sản phẩm vì đã có đơn hàng liên quan. Sản phẩm đã bị ẩn khỏi danh mục.'];
            header('Location: ' . ADMIN_URL . 'products');
            exit;
        }

        $images = is_array($product['images'] ?? null) ? $product['images'] : [];
        Product::deleteById($id);

        foreach ($images as $img) {
            $this->deleteUploadedImage((string)$img);
        }

        $_SESSION['flash'] = 'Sản phẩm đã được xóa thành công.';
        header('Location: ' . ADMIN_URL . 'products');
        exit;
    }

    public function createcategory(): void
    {
        Auth::verifyCsrf();
        header('Content-Type: application/json; charset=utf-8');

        $name = trim((string)($_POST['name'] ?? ''));
        if ($name === '') {
            http_response_code(422);
            echo json_encode([
                'ok' => false,
                'message' => 'Tên danh mục là bắt buộc.',
            ]);
            exit;
        }

        $slugInput = trim((string)($_POST['slug'] ?? ''));
        $slugBase = $this->slugify($slugInput, $name);
        if ($slugBase === '') {
            http_response_code(422);
            echo json_encode([
                'ok' => false,
                'message' => 'Không thể tạo slug hợp lệ cho danh mục.',
            ]);
            exit;
        }

        $slug = $slugBase;
        $suffix = 1;
        while (Category::countBySlug($slug) > 0) {
            $slug = $slugBase . '-' . $suffix;
            $suffix++;
            if ($suffix > 999) {
                break;
            }
        }

        try {
            $id = Category::create($name, $slug);
            echo json_encode([
                'ok' => true,
                'message' => 'Đã thêm danh mục thành công.',
                'category' => [
                    'id' => $id,
                    'name' => $name,
                    'slug' => $slug,
                ],
            ]);
            exit;
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'ok' => false,
                'message' => 'Không thể thêm danh mục lúc này.',
            ]);
            exit;
        }
    }

    public function deletecategory(): void
    {
        Auth::verifyCsrf();
        header('Content-Type: application/json; charset=utf-8');

        $id = (int)($_POST['id'] ?? $_POST['category_id'] ?? 0);
        if ($id <= 0) {
            http_response_code(422);
            echo json_encode([
                'ok' => false,
                'message' => 'Danh mục không hợp lệ.',
            ]);
            exit;
        }

        $category = Category::getById($id);
        if (!$category) {
            http_response_code(404);
            echo json_encode([
                'ok' => false,
                'message' => 'Danh mục không tồn tại.',
            ]);
            exit;
        }

        // Prevent deleting when products are assigned
        $productCount = Product::countByCategory($id);
        if ($productCount > 0) {
            http_response_code(409);
            echo json_encode([
                'ok' => false,
                'message' => 'Không thể xóa danh mục vì đang có sản phẩm liên quan.',
            ]);
            exit;
        }

        try {
            $deleted = Category::delete($id);
            if ($deleted) {
                echo json_encode([
                    'ok' => true,
                    'message' => 'Đã xóa danh mục.',
                    'id' => $id,
                ]);
                exit;
            }
            http_response_code(500);
            echo json_encode([
                'ok' => false,
                'message' => 'Không thể xóa danh mục lúc này.',
            ]);
            exit;
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'ok' => false,
                'message' => 'Lỗi máy chủ khi xóa danh mục.',
            ]);
            exit;
        }
    }

    private function collectPayload(): array
    {
        return [
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'name' => trim((string)($_POST['name'] ?? '')),
            'slug' => $this->slugify((string)($_POST['slug'] ?? ''), (string)($_POST['name'] ?? '')),
            'price' => (float)($_POST['price'] ?? 0),
            'description' => trim((string)($_POST['description'] ?? '')),
            'range' => trim((string)($_POST['range'] ?? '')),
            'power' => trim((string)($_POST['power'] ?? '')),
            'acceleration' => trim((string)($_POST['acceleration'] ?? '')),
            'max_speed' => trim((string)($_POST['max_speed'] ?? '')),
            'battery' => trim((string)($_POST['battery'] ?? '')),
            'deposit_amount' => max(0, (int)($_POST['deposit_amount'] ?? 15000000)),
            'deposit_non_refundable' => isset($_POST['deposit_non_refundable']) ? 1 : 0,
            'exterior_colors_raw' => trim((string)($_POST['exterior_colors_raw'] ?? '')),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];
    }

    private function validatePayload(array $payload, int $id = 0): array
    {
        $errors = [];
        if ($payload['category_id'] <= 0 || !Category::getById($payload['category_id'])) {
            $errors[] = 'Please select a valid category.';
        }
        if (!$payload['name']) {
            $errors[] = 'Product name is required.';
        }
        if (!$payload['slug']) {
            $errors[] = 'Slug is required.';
        }
        if ($payload['price'] <= 0) {
            $errors[] = 'Price must be greater than 0.';
        }
        if (Product::slugExists($payload['slug'], $id > 0 ? $id : null)) {
            $errors[] = 'Slug already exists.';
        }
        return $errors;
    }

    private function buildSpecs(array $payload): array
    {
        $specs = [
            'range' => $payload['range'],
            'power' => $payload['power'],
            'acceleration' => $payload['acceleration'],
            'max_speed' => $payload['max_speed'],
            'battery' => $payload['battery'],
            'deposit_amount' => max(0, (int)($payload['deposit_amount'] ?? 15000000)),
            'deposit_non_refundable' => 1,
        ];

        $exteriorColors = $this->parseExteriorColors($payload['exterior_colors_raw'] ?? '');
        $family = $this->resolveImageFamilyFromPayload($payload);
        if ($family !== '' && !empty($exteriorColors)) {
            $exteriorColors = $this->enrichExteriorColorImages($exteriorColors, $family);
        }

        if (!empty($exteriorColors)) {
            $specs['exterior_colors'] = $exteriorColors;
        }

        return array_filter($specs, static function ($v): bool {
            if (is_string($v)) {
                return trim($v) !== '';
            }

            if (is_array($v)) {
                return !empty($v);
            }

            if (is_int($v) || is_float($v) || is_bool($v)) {
                return true;
            }

            return false;
        });
    }

    private function parseExteriorColors(string $raw): array
    {
        if ($raw === '') {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];
        $out = [];

        foreach ($lines as $line) {
            $line = trim((string)$line);
            if ($line === '') {
                continue;
            }

            $line = preg_replace('/^\s*[-*]\s+/u', '', $line);
            $line = preg_replace('/^\s*\d+[\.)]\s+/u', '', $line);
            $line = preg_replace('/^t[êe]n\s*m[àa]u\s*:\s*/iu', '', $line);
            $line = trim((string)$line);
            if ($line === '') {
                continue;
            }

            $code = '';
            $name = '';
            $image = '';
            $hex = '';
            $surcharge = 0;

            if (preg_match('/(?:^|[\s|])((?:[A-Za-z]:)?[^|]+\.(?:webp|jpg|jpeg|png))\b/i', $line, $mImage)) {
                $image = $this->normalizeColorImagePath((string)$mImage[1]);
                $basename = pathinfo($image, PATHINFO_FILENAME);
                if (is_string($basename) && $basename !== '') {
                    $code = strtoupper($basename);
                }
            }

            if (preg_match('/#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})\b/', $line, $mHex)) {
                $hex = '#' . strtoupper((string)$mHex[1]);
            }

            $parts = array_values(array_filter(array_map('trim', explode('|', $line)), static function ($v): bool {
                return $v !== '';
            }));

            if (count($parts) >= 2) {
                $first = strtoupper((string)$parts[0]);
                $second = strtoupper((string)$parts[1]);

                if (preg_match('/^(?!WEBP$|JPG$|JPEG$|PNG$)[A-Z0-9]{3,5}$/', $first)) {
                    $code = $first;
                    $name = (string)$parts[1];
                } elseif (preg_match('/^(?!WEBP$|JPG$|JPEG$|PNG$)[A-Z0-9]{3,5}$/', $second)) {
                    $name = (string)$parts[0];
                    $code = $second;
                }

                for ($i = 2; $i < count($parts); $i++) {
                    $token = trim((string)$parts[$i]);
                    if ($token === '') {
                        continue;
                    }

                    if ($hex === '' && preg_match('/^#?[A-Fa-f0-9]{3,6}$/', $token)) {
                        $hex = '#' . strtoupper(ltrim($token, '#'));
                        continue;
                    }

                    if ($image === '' && preg_match('/(?:^|\/)[A-Za-z0-9._-]+\.(?:webp|jpg|jpeg|png)$/i', $token)) {
                        $image = $this->normalizeColorImagePath($token);
                        continue;
                    }

                    if ($surcharge <= 0 && preg_match('/^[0-9][0-9.,]*$/', $token)) {
                        $surcharge = max(0, (int)preg_replace('/\D+/', '', $token));
                    }
                }
            }

            if ($name === '') {
                $name = $line;
                if ($image !== '') {
                    $name = trim(str_ireplace($image, '', $name));
                }
                if ($code !== '') {
                    $name = trim(preg_replace('/\b' . preg_quote($code, '/') . '\b/i', '', $name));
                }
                $name = trim((string)$name, " \t\n\r\0\x0B:-");
            }

            if ($code === '' && preg_match('/\b([A-Za-z0-9]{3,5})\b/', $line, $mCode)) {
                $candidateCode = strtoupper((string)$mCode[1]);
                if (!preg_match('/^(WEBP|JPG|JPEG|PNG)$/', $candidateCode) && preg_match('/^[A-Z0-9]{3,5}$/', $candidateCode)) {
                    $code = $candidateCode;
                }
            }

            if ($code === '' || $name === '') {
                continue;
            }

            $row = [
                'code' => strtoupper($code),
                'name' => $name,
            ];

            if ($image !== '') {
                $row['image'] = $this->normalizeColorImagePath($image);
            }
            if ($hex !== '') {
                $row['hex'] = $hex;
            }
            if ($surcharge > 0) {
                $row['surcharge'] = $surcharge;
            }

            $out[strtoupper($code)] = $row;
        }

        return array_values($out);
    }

    private function normalizeColorImagePath(string $path): string
    {
        $path = trim(str_replace('\\', '/', $path));
        if (!$path) {
            return '';
        }
        return preg_match('~(?:^|[A-Za-z]:)?(?:.*/)?public/images/(.+)$~i', $path, $m)
            ? ltrim($m[1], '/')
            : ltrim($path, '/');
    }

    private function resolveMainImage(array $allImages, array $newImages): string
    {
        $postedMain = ltrim(trim((string)($_POST['main_image'] ?? '')), '/');
        if ($postedMain && in_array($postedMain, $allImages, true)) {
            return $postedMain;
        }

        $mainNewIndex = (int)($_POST['main_new_index'] ?? -1);
        if ($mainNewIndex >= 0 && isset($newImages[$mainNewIndex])) {
            $candidate = (string)$newImages[$mainNewIndex];
            if ($candidate && in_array($candidate, $allImages, true)) {
                return $candidate;
            }
        }

        return $allImages[0] ?? '';
    }

    private function prioritizeMainImage(array $allImages, string $mainImage): array
    {
        $mainImage = ltrim(trim($mainImage), '/');
        return (!$mainImage || !in_array($mainImage, $allImages, true))
            ? $allImages
            : array_values(array_unique(array_merge([$mainImage], $allImages)));
    }

    private function uploadNewImages($images, string $subdir): array
    {
        if (!is_array($images) || !isset($images['name']) || !is_array($images['name'])) {
            return [];
        }

        $uploaded = [];
        $subPath = trim($subdir, '/') ?: 'products';
        $subPath = $subPath !== 'products' ? 'products/' . $subPath : 'products';

        foreach ($images['name'] as $i => $name) {
            if (($images['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $uploaded[] = 'uploads/' . ltrim(
                Upload::image([
                    'name' => $name,
                    'type' => $images['type'][$i] ?? '',
                    'tmp_name' => $images['tmp_name'][$i] ?? '',
                    'error' => $images['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                    'size' => $images['size'][$i] ?? 0,
                ], $subPath, true),
                '/'
            );
        }
        return $uploaded;
    }

    private function buildProductMutationArgs(array $payload, array $allImages): array
    {
        return [
            (int)$payload['category_id'],
            (string)$payload['name'],
            (string)$payload['slug'],
            (string)$payload['description'],
            $this->buildSpecs($payload),
            (float)$payload['price'],
            $allImages,
            (int)$payload['is_active'],
        ];
    }

    private function resolveImageFamilyFromPayload(array $payload, array $existing = []): string
    {
        $candidates = array_filter([
            strtolower(trim((string)($existing['slug'] ?? ''))),
            strtolower(trim((string)($payload['slug'] ?? ''))),
        ]);

        foreach ($candidates as $slug) {
            if (preg_match('/^[a-z0-9-]+$/', $slug)) {
                return $slug;
            }
        }

        foreach ([$existing['name'] ?? null, $payload['name'] ?? null] as $candidate) {
            if ($candidate && ($family = $this->extractImageFamily((string)$candidate))) {
                return $family;
            }
        }

        return '';
    }

    private function extractImageFamily(string $text): string
    {
        $text = strtolower(trim($text));
        if (!$text) {
            return '';
        }

        if (preg_match('/(?:^|[-_])vf(?:-?mpv)?-?([3-9])(?:[-_]|$)/i', $text, $match)) {
            return 'vf' . $match[1];
        }

        $normalized = preg_replace('/[^a-z0-9-]/i', '-', $text);
        $normalized = trim(str_replace('vinfast-', '', $normalized), '-');

        if (!$normalized) {
            return '';
        }

        $family = strtolower(explode('-', $normalized)[0]);
        return preg_match('/^[a-z0-9]+$/', $family) ? $family : '';
    }

    private function enrichExteriorColorImages(array $rows, string $family): array
    {
        return array_map(function (array $row) use ($family): array {
            if (!is_array($row) || ($code = strtoupper(trim((string)($row['code'] ?? '')))) === '') {
                return $row;
            }
            if (trim((string)($row['image'] ?? '')) === '' && ($resolved = $this->resolveColorImageInFamily($family, $code))) {
                $row['image'] = $resolved;
            }
            return $row;
        }, $rows);
    }

    private function resolveColorImageInFamily(string $family, string $code): string
    {
        $family = trim($family);
        $code = strtolower(trim($code));
        if ($family === '' || $code === '') {
            return '';
        }

        $dirs = [];
        $dirs[] = ROOT . '/public/images/uploads/products/' . $family;

        $familyFallback = $this->extractImageFamily($family);
        if ($familyFallback !== '' && $familyFallback !== $family) {
            $dirs[] = ROOT . '/public/images/uploads/products/' . $familyFallback;
        }

        $extensions = ['webp', 'jpg', 'jpeg', 'png'];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            foreach ($extensions as $ext) {
                $candidate = $dir . '/' . $code . '.' . $ext;
                if (is_file($candidate)) {
                    $relative = str_replace(ROOT . '/public/images/', '', str_replace('\\', '/', $candidate));
                    return $relative;
                }
            }

            $matches = glob($dir . '/' . $code . '.*') ?: [];
            if (!empty($matches)) {
                $match = (string)$matches[0];
                $relative = str_replace(ROOT . '/public/images/', '', str_replace('\\', '/', $match));
                return $relative;
            }
        }

        return '';
    }

    private function deleteUploadedImage(string $relative): void
    {
        $relative = ltrim($relative, '/');
        if (strpos($relative, 'uploads/') !== 0) {
            return;
        }

        Upload::delete(substr($relative, strlen('uploads/')));
    }

    private function slugify(string $inputSlug, string $fallbackName = ''): string
    {
        $raw = trim($inputSlug !== '' ? $inputSlug : $fallbackName);
        $raw = strtolower($raw);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $raw);
        return trim($slug, '-');
    }
}
