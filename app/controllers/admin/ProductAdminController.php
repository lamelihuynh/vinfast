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
        $allowedStatus = ['all', 'active', 'inactive'];
        if (!in_array($status, $allowedStatus, true)) {
            $status = 'all';
        }

        $page = max(1, (int)($_GET['page'] ?? 1));

        $filters = [
            'search' => $q,
            'category_id' => $cat > 0 ? $cat : null,
            'status' => $status,
        ];

        $summaryFilters = [
            'search' => $q,
            'category_id' => $cat > 0 ? $cat : null,
            'status' => 'all',
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
                $this->redirectIndex();
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
            $this->redirectIndex();
        }

        $payload = $this->collectPayload();
        $errors = $this->validatePayload($payload, $id);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $payload + ['id' => $id];
            $this->redirectForm($id);
        }

        $existingImages = $this->collectExistingImages($_POST['existing_images'] ?? []);

        try {
            $imageSubdir = $this->resolveImageFamilyFromPayload($payload, is_array($existing) ? $existing : []);
            $newImages = $this->uploadNewImages($_FILES['images'] ?? null, $imageSubdir);
        } catch (RuntimeException $e) {
            $_SESSION['errors'] = [$e->getMessage()];
            $_SESSION['old'] = $payload + ['id' => $id, 'existing_images' => $existingImages];
            $this->redirectForm($id);
        }

        $allImages = array_values(array_unique(array_merge($existingImages, $newImages)));
        $mainImage = $this->resolveMainImage($allImages, $newImages);
        $allImages = $this->prioritizeMainImage($allImages, $mainImage);

        if ($isEdit) {
            $this->persistUpdate($id, is_array($existing) ? $existing : [], $payload, $allImages);
            $_SESSION['flash'] = 'Product updated successfully.';
            $this->redirectIndex();
        }

        $this->persistCreate($payload, $allImages);
        $_SESSION['flash'] = 'Product created successfully.';
        $this->redirectIndex();
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
            $this->redirectIndex();
        }

        $next = ((int)($product['is_active'] ?? 0) === 1) ? 0 : 1;
        Product::setActive($id, $next);
        $_SESSION['flash'] = $next === 1 ? 'Product is now visible.' : 'Product is now hidden.';
        $this->redirectIndex();
    }

    public function delete($id = 0): void
    {
        Auth::verifyCsrf();
        $id = (int)$id;
        $product = Product::getByIdAdmin($id);
        if (!$product) {
            $_SESSION['errors'] = ['Product not found.'];
            $this->redirectIndex();
        }

        if (Product::hasOrders($id)) {
            Product::setActive($id, 0);
            $_SESSION['errors'] = ['Product has related orders, so it was hidden instead of deleted.'];
            $this->redirectIndex();
        }

        $images = is_array($product['images'] ?? null) ? $product['images'] : [];
        Product::deleteById($id);

        foreach ($images as $img) {
            $this->deleteUploadedImage((string)$img);
        }

        $_SESSION['flash'] = 'Product deleted successfully.';
        $this->redirectIndex();
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
        if ($payload['name'] === '') {
            $errors[] = 'Product name is required.';
        }
        if ($payload['slug'] === '') {
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

            if (preg_match('/\b([A-Za-z0-9_-]+\.(?:webp|jpg|jpeg|png))\b/i', $line, $mImage)) {
                $image = trim((string)$mImage[1]);
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
                        $image = $token;
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
                $row['image'] = ltrim($image, '/');
            }
            if ($hex !== '') {
                $row['hex'] = $hex;
            }

            $out[strtoupper($code)] = $row;
        }

        return array_values($out);
    }

    private function collectExistingImages($existingImagesRaw): array
    {
        $out = [];
        $source = is_array($existingImagesRaw) ? $existingImagesRaw : [];

        foreach ($source as $img) {
            if (!is_string($img)) {
                continue;
            }
            $img = trim($img);
            if ($img === '' || preg_match('/^https?:\/\//i', $img)) {
                continue;
            }
            $out[] = ltrim($img, '/');
        }

        return array_values(array_unique($out));
    }

    private function resolveMainImage(array $allImages, array $newImages): string
    {
        $postedMain = trim((string)($_POST['main_image'] ?? ''));
        $postedMain = ltrim($postedMain, '/');
        if ($postedMain !== '' && in_array($postedMain, $allImages, true)) {
            return $postedMain;
        }

        $postedMainNewIndex = isset($_POST['main_new_index']) ? (int)$_POST['main_new_index'] : -1;
        if ($postedMainNewIndex >= 0 && isset($newImages[$postedMainNewIndex])) {
            $candidate = (string)$newImages[$postedMainNewIndex];
            if ($candidate !== '' && in_array($candidate, $allImages, true)) {
                return $candidate;
            }
        }

        return isset($allImages[0]) ? (string)$allImages[0] : '';
    }

    private function prioritizeMainImage(array $allImages, string $mainImage): array
    {
        $mainImage = ltrim(trim($mainImage), '/');
        if ($mainImage === '' || !in_array($mainImage, $allImages, true)) {
            return $allImages;
        }

        return array_values(array_unique(array_merge([$mainImage], $allImages)));
    }

    private function uploadNewImages($images, string $subdir): array
    {
        if (!is_array($images) || !isset($images['name']) || !is_array($images['name'])) {
            return [];
        }

        $uploaded = [];
        $fileCount = count($images['name']);

        for ($i = 0; $i < $fileCount; $i++) {
            $single = [
                'name' => $images['name'][$i] ?? '',
                'type' => $images['type'][$i] ?? '',
                'tmp_name' => $images['tmp_name'][$i] ?? '',
                'error' => $images['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                'size' => $images['size'][$i] ?? 0,
            ];

            if (($single['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $subPath = trim($subdir, '/');
            $subPath = $subPath !== '' ? 'products/' . $subPath : 'products';
            $rel = Upload::image($single, $subPath, true);
            $uploaded[] = 'uploads/' . ltrim($rel, '/');
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
        $candidates = [];

        if (!empty($existing['slug'])) {
            $candidates[] = (string)$existing['slug'];
        }
        if (!empty($payload['slug'])) {
            $candidates[] = (string)$payload['slug'];
        }
        if (!empty($existing['name'])) {
            $candidates[] = (string)$existing['name'];
        }
        if (!empty($payload['name'])) {
            $candidates[] = (string)$payload['name'];
        }

        foreach ($candidates as $candidate) {
            $family = $this->extractImageFamily($candidate);
            if ($family !== '') {
                return $family;
            }
        }

        return '';
    }

    private function extractImageFamily(string $text): string
    {
        $text = strtolower(trim($text));
        if ($text === '') {
            return '';
        }

        if (preg_match('/(?:^|[-_])vf(?:-?mpv)?-?([3-9])(?:[-_]|$)/i', $text, $match)) {
            return 'vf' . $match[1];
        }

        $normalized = preg_replace('/[^a-z0-9]+/i', '-', $text);
        $normalized = trim((string)$normalized, '-');
        if ($normalized === '') {
            return '';
        }

        if (strpos($normalized, 'vinfast-') === 0) {
            $normalized = substr($normalized, 8);
        }

        $normalized = trim((string)$normalized, '-');
        if ($normalized === '') {
            return '';
        }

        $parts = explode('-', $normalized);
        $family = strtolower(trim((string)($parts[0] ?? '')));
        if (!preg_match('/^[a-z0-9]+$/', $family)) {
            return '';
        }

        return $family;
    }

    private function enrichExteriorColorImages(array $rows, string $family): array
    {
        $out = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $code = strtoupper(trim((string)($row['code'] ?? '')));
            if ($code !== '' && trim((string)($row['image'] ?? '')) === '') {
                $resolved = $this->resolveColorImageInFamily($family, $code);
                if ($resolved !== '') {
                    $row['image'] = $resolved;
                }
            }

            $out[] = $row;
        }

        return $out;
    }

    private function resolveColorImageInFamily(string $family, string $code): string
    {
        $family = trim($family);
        $code = strtolower(trim($code));
        if ($family === '' || $code === '') {
            return '';
        }

        $dir = ROOT . '/public/images/uploads/products/' . $family;
        if (!is_dir($dir)) {
            return '';
        }

        $extensions = ['webp', 'jpg', 'jpeg', 'png'];
        foreach ($extensions as $ext) {
            $candidate = $dir . '/' . $code . '.' . $ext;
            if (is_file($candidate)) {
                return 'uploads/products/' . $family . '/' . $code . '.' . $ext;
            }
        }

        $matches = glob($dir . '/' . $code . '.*') ?: [];
        if (!empty($matches)) {
            $match = (string)$matches[0];
            $relative = str_replace(ROOT . '/public/images/', '', str_replace('\\', '/', $match));
            return $relative;
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
        $slug = trim((string)$slug, '-');
        return $slug;
    }

    private function redirectForm(int $id = 0): void
    {
        $url = ADMIN_URL . 'products/form' . ($id > 0 ? '/' . $id : '');
        header('Location: ' . $url);
        exit;
    }

    private function redirectIndex(): void
    {
        header('Location: ' . ADMIN_URL . 'products');
        exit;
    }
}
