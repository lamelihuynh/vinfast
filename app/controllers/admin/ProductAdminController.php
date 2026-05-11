<?php

require_once __DIR__ . '/../../helpers/ImageHelper.php';

/**
 * app/controllers/admin/ProductAdminController.php
 * Owner: Hai Nam 
 * Routes: /admin/products
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
        $old = is_array($_SESSION['old'] ?? null) ? $_SESSION['old'] : [];
        unset($_SESSION['old']);

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
            'old' => $old,
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
            $exteriorColors = is_array($p['exterior_colors'] ?? null) ? $p['exterior_colors'] : [];

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
                'exterior_colors' => $exteriorColors,
                'images' => array_values($localImages),
            ];
        }, $products);
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
            header('Location: ' . ADMIN_URL . 'products');
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
            header('Location: ' . ADMIN_URL . 'products');
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
        $productId = (int)Product::create(...$this->buildProductMutationArgs($payload, $allImages));
        if ($productId > 0) {
            Product::syncExteriorColors($productId, $this->buildExteriorColorRows($payload));
        }
    }

    private function persistUpdate(int $id, array $existing, array $payload, array $allImages): void
    {
        $oldImages = is_array($existing['images'] ?? null) ? $existing['images'] : [];
        $removedImages = array_values(array_diff($oldImages, $allImages));

        Product::updateById($id, ...$this->buildProductMutationArgs($payload, $allImages));
        Product::syncExteriorColors($id, $this->buildExteriorColorRows($payload));

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

    private function collectPayload(): array
    {
        return [
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'name' => $this->cleanText($_POST['name'] ?? ''),
            'slug' => $this->slugify((string)($_POST['slug'] ?? ''), $this->cleanText($_POST['name'] ?? '')),
            'price' => (float)($_POST['price'] ?? 0),
            'description' => $this->cleanText($_POST['description'] ?? ''),
            'range' => $this->cleanText($_POST['range'] ?? ''),
            'power' => $this->cleanText($_POST['power'] ?? ''),
            'acceleration' => $this->cleanText($_POST['acceleration'] ?? ''),
            'max_speed' => $this->cleanText($_POST['max_speed'] ?? ''),
            'battery' => $this->cleanText($_POST['battery'] ?? ''),
            'deposit_amount' => max(0, (int)($_POST['deposit_amount'] ?? 15000000)),
            'modal_default_image' => $this->cleanText($_POST['modal_default_image'] ?? ''),
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
        if ($this->strLength((string)$payload['name']) > 120) {
            $errors[] = 'Product name must be at most 120 characters.';
        }
        if ($this->strLength((string)$payload['description']) > 2000) {
            $errors[] = 'Description must be at most 2000 characters.';
        }
        foreach (['range', 'power', 'acceleration', 'max_speed', 'battery'] as $field) {
            if ($this->strLength((string)($payload[$field] ?? '')) > 120) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' must be at most 120 characters.';
            }
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

    private function strLength(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
    }

    private function cleanText(mixed $value): string
    {
        return trim(strip_tags((string)$value));
    }

    private function buildExteriorColorRows(array $payload): array
    {
        $defaultImage = ltrim(trim((string)($payload['modal_default_image'] ?? '')), '/');
        $codes = is_array($_POST['color_code'] ?? null) ? $_POST['color_code'] : [];
        $names = is_array($_POST['color_name'] ?? null) ? $_POST['color_name'] : [];
        $hexes = is_array($_POST['color_hex'] ?? null) ? $_POST['color_hex'] : [];
        $surcharges = is_array($_POST['color_surcharge'] ?? null) ? $_POST['color_surcharge'] : [];
        $images = is_array($_POST['existing_images'] ?? null) ? $_POST['existing_images'] : [];

        $rows = [];
        $rowCount = max(count($codes), count($names), count($hexes), count($surcharges), count($images));
        for ($i = 0; $i < $rowCount; $i++) {
            $code = strtoupper(trim((string)($codes[$i] ?? '')));
            $name = trim((string)($names[$i] ?? ''));
            if ($code === '' || $name === '') {
                continue;
            }

            $image = ltrim(trim((string)($images[$i] ?? '')), '/');
            $row = [
                'code' => $code,
                'name' => $name,
                'sortOrder' => $i,
            ];

            if ($image !== '') {
                $row['image'] = $image;
            }
            $hex = strtoupper(trim((string)($hexes[$i] ?? '')));
            if ($hex !== '') {
                $row['hex'] = str_starts_with($hex, '#') ? $hex : '#' . $hex;
            }

            $surcharge = max(0, (int)preg_replace('/\D+/', '', (string)($surcharges[$i] ?? '')));
            if ($surcharge > 0) {
                $row['surcharge'] = $surcharge;
            }

            $row['isDefault'] = $defaultImage !== '' && $image !== '' && $image === $defaultImage;
            $rows[] = $row;
        }

        if (!empty($rows) && $defaultImage !== '') {
            $hasDefault = false;
            foreach ($rows as $row) {
                if (!empty($row['isDefault'])) {
                    $hasDefault = true;
                    break;
                }
            }

            if (!$hasDefault) {
                $rows[0]['isDefault'] = true;
            }
        }

        return $rows;
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

    /**
     * Determine the upload subdirectory for a product's images.
     * Priority:
     *  1. Slug from the current payload (e.g. "vinfast-vf3-eco")
     *  2. Slug of the existing DB record (for edits with unchanged slug)
     *  3. First image path that already contains an "uploads/products/…" subdir
     *  4. Raw slug as-is (fallback)
     */
    private function resolveImageFamilyFromPayload(array $payload, array $existing): string
    {
        // 1. Use the slug being saved (trimmed, lowercased)
        $slug = strtolower(trim((string)($payload['slug'] ?? '')));
        if ($slug !== '') {
            return $slug;
        }

        // 2. Fall back to existing record's slug
        $existingSlug = strtolower(trim((string)($existing['slug'] ?? '')));
        if ($existingSlug !== '') {
            return $existingSlug;
        }

        // 3. Try to extract subdir from an existing image path
        //    e.g. "uploads/products/vinfast-vf3-eco/ce18.webp" → "vinfast-vf3-eco"
        $existingImages = is_array($existing['images'] ?? null) ? $existing['images'] : [];
        foreach ($existingImages as $img) {
            if (!is_string($img)) {
                continue;
            }
            if (preg_match('~uploads/products/([^/]+)/~i', $img, $m)) {
                return strtolower($m[1]);
            }
        }

        // 4. Last resort: derive from product name via ImageHelper
        $name = (string)($payload['name'] ?? '');
        $family = ImageHelper::extractFamily($name);
        return $family !== '' ? 'vinfast-' . $family : 'products';
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
