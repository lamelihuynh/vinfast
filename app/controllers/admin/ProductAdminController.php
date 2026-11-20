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
        $kind = trim((string)($_GET['kind'] ?? 'all'));
        if (!in_array($kind, Category::KINDS, true)) {
            $kind = 'all';
        }
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
            'kind' => $kind !== 'all' ? $kind : null,
        ];

        $summaryFilters = [
            'search' => $q,
            'category_id' => $cat > 0 ? $cat : null,
            'status' => 'all',
            'price_min' => $price_min,
            'price_max' => $price_max,
            'kind' => $kind !== 'all' ? $kind : null,
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
            'kind' => $kind !== 'all' ? $kind : null,
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
            'categoryDefs' => $this->categoryDefsForModal(),
            'kind' => $kind,
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

            $row = [
                'id' => (int)($p['id'] ?? 0),
                'kind' => (string)($p['kind'] ?? 'vehicle'),
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

            if (($p['kind'] ?? 'vehicle') === 'accessory') {
                $row['specs'] = $specs;
                $row['variants'] = $this->mapVariantsForModal($p);
            }

            return $row;
        }, $products);
    }

    /** Phiên bản phụ kiện + tồn kho PG, cùng dạng với old state để JS dùng chung. */
    private function mapVariantsForModal(array $product): array
    {
        $variants = is_array($product['variants'] ?? null) ? $product['variants'] : [];
        $inventory = InventoryService::byProduct((string)($product['id'] ?? ''));

        $rows = [];
        foreach ($variants as $v) {
            if (!is_array($v)) {
                continue;
            }
            $variantId = (string)($v['variantId'] ?? '');
            $inv = $inventory[$variantId] ?? null;
            $rows[] = [
                'variant_id' => $variantId,
                'name' => (string)($v['name'] ?? ''),
                'price' => (float)($v['price'] ?? 0),
                'is_active' => !empty($v['isActive']) ? 1 : 0,
                'attributes' => is_array($v['attributes'] ?? null) ? $v['attributes'] : [],
                'stock' => $inv !== null ? (string)$inv['stock'] : '',
                'stock_orig' => $inv !== null ? (string)$inv['stock'] : '',
                'reserved' => (int)($inv['reserved'] ?? 0),
            ];
        }
        return $rows;
    }

    /** Định nghĩa category cho modal: id => {id, name, kind, dynamic_attributes[], variant_attributes[]}. */
    private function categoryDefsForModal(): array
    {
        $defs = [];
        foreach (Category::allById() as $id => $c) {
            $defs[(string)$id] = [
                'id' => (int)$id,
                'name' => (string)$c['name'],
                'kind' => (string)$c['kind'],
                'dynamic_attributes' => array_values($this->normalizeAttrDefs($c['dynamic_attributes'] ?? [])),
                'variant_attributes' => array_values($this->normalizeAttrDefs($c['variant_attributes'] ?? [])),
            ];
        }
        return $defs;
    }

    /** @return array<string,array{key:string,label:string,type:string,unit:string,required:bool,options:string[]}> key => def */
    private function normalizeAttrDefs($defs): array
    {
        $out = [];
        foreach ((array)$defs as $d) {
            if (!is_array($d)) {
                continue;
            }
            $key = trim((string)($d['key'] ?? ''));
            if ($key === '' || !preg_match('/^[A-Za-z0-9_]+$/', $key)) {
                continue;
            }
            $type = (string)($d['type'] ?? 'string');
            if (!in_array($type, ['string', 'number', 'boolean', 'enum', 'text', 'list'], true)) {
                $type = 'string';
            }
            $out[$key] = [
                'key' => $key,
                'label' => (string)($d['label'] ?? $key),
                'type' => $type,
                'unit' => (string)($d['unit'] ?? ''),
                'required' => !empty($d['required']),
                'options' => array_values(array_map('strval', (array)($d['options'] ?? []))),
            ];
        }
        return $out;
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

        $categoryId = (int)($_POST['category_id'] ?? 0);
        if ($categoryId > 0 && Category::kindOf($categoryId) === 'accessory') {
            $this->saveAccessory($id, is_array($existing) ? $existing : [], $categoryId);
            return;
        }

        $payload = $this->collectPayload();
        $errors = $this->validatePayload($payload, $id);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $payload + ['id' => $id];
            header('Location: ' . ADMIN_URL . 'products');
            exit;
        }

        $allImages = $this->collectImages($payload, is_array($existing) ? $existing : [], $payload + ['id' => $id]);

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

    private function postedExistingImages(): array
    {
        return array_values(array_unique(array_filter(
            array_map('trim', array_map('strval', (array)($_POST['existing_images'] ?? []))),
            static fn(string $img): bool => $img !== '' && !preg_match('/^https?:\/\//i', $img)
        )));
    }

    /** existing_images[] + ảnh upload mới, ảnh chính đưa lên đầu. Lỗi upload -> redirect kèm old state. */
    private function collectImages(array $payload, array $existing, array $oldState): array
    {
        $existingImages = $this->postedExistingImages();
        $newImages = [];

        try {
            $imageSubdir = $this->resolveImageFamilyFromPayload($payload, $existing);
            $newImages = $this->uploadNewImages($_FILES['images'] ?? null, $imageSubdir);
        } catch (RuntimeException $e) {
            $_SESSION['errors'] = [$e->getMessage()];
            $_SESSION['old'] = $oldState + ['existing_images' => $existingImages];
            header('Location: ' . ADMIN_URL . 'products');
            exit;
        }

        $allImages = array_values(array_unique(array_merge($existingImages, $newImages)));
        $mainImage = $this->resolveMainImage($allImages, $newImages);
        return $this->prioritizeMainImage($allImages, $mainImage);
    }

    // ------------------------------------------------------------------
    // Phụ kiện (category.kind = 'accessory'): thuộc tính động + phiên bản
    // ------------------------------------------------------------------

    private function saveAccessory(int $id, array $existing, int $categoryId): void
    {
        $isEdit = $id > 0;
        $category = Category::allById()[$categoryId] ?? [];
        $attrDefs = $this->normalizeAttrDefs($category['dynamic_attributes'] ?? []);
        $variantDefs = $this->normalizeAttrDefs($category['variant_attributes'] ?? []);

        $errors = [];
        $name = $this->cleanText($_POST['name'] ?? '');
        $slug = $this->slugify((string)($_POST['slug'] ?? ''));
        if ($slug === '' && $name !== '') {
            $slug = Product::slugify($name);
        }
        $description = $this->cleanText($_POST['description'] ?? '');

        if ($name === '') {
            $errors[] = 'Vui lòng nhập tên sản phẩm.';
        } elseif ($this->strLength($name) > 120) {
            $errors[] = 'Tên sản phẩm tối đa 120 ký tự.';
        }
        if ($this->strLength($description) > 2000) {
            $errors[] = 'Mô tả tối đa 2000 ký tự.';
        }
        if ($slug === '') {
            $errors[] = 'Slug không hợp lệ.';
        } elseif (Product::slugExists($slug, $isEdit ? $id : null)) {
            $errors[] = 'Slug đã tồn tại.';
        }

        $postedAttrs = is_array($_POST['attrs'] ?? null) ? $_POST['attrs'] : [];
        $attributes = $this->castAttributes($attrDefs, $postedAttrs, $errors, '');
        $variants = $this->collectAccessoryVariants($variantDefs, $errors);

        $payload = [
            'category_id' => $categoryId,
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];
        // Old state cùng dạng với mapProductsForModal() để modal mở lại đúng dữ liệu đã nhập
        $oldState = $payload + [
            'id' => $id,
            'kind' => 'accessory',
            'specs' => $attributes,
            'variants' => array_map(static fn(array $v): array => [
                'variant_id' => $v['variant_id'],
                'name' => $v['name'],
                'price' => $v['price_input'],
                'is_active' => $v['is_active'],
                'attributes' => $v['attributes'],
                'stock' => $v['stock_input'],
                'stock_orig' => $v['stock_orig'],
                'reserved' => $v['reserved'],
            ], $variants),
        ];

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $oldState + ['existing_images' => $this->postedExistingImages()];
            header('Location: ' . ADMIN_URL . 'products');
            exit;
        }

        $allImages = $this->collectImages($payload, $existing, $oldState);

        $data = $payload + [
            'images' => $allImages,
            'attributes' => $attributes,
            'variants' => array_map(static fn(array $v): array => [
                'variant_id' => $v['variant_id'],
                'name' => $v['name'],
                'price' => $v['price'],
                'is_active' => $v['is_active'],
                'attributes' => $v['attributes'],
                'stock' => $v['stock'],
            ], $variants),
        ];

        if ($isEdit) {
            $ok = Product::updateWithVariants($id, $data);
            if ($ok) {
                $oldImages = is_array($existing['images'] ?? null) ? $existing['images'] : [];
                foreach (array_diff($oldImages, $allImages) as $img) {
                    $this->deleteUploadedImage((string)$img);
                }
            }
        } else {
            $ok = Product::createWithVariants($data) > 0;
        }

        if ($ok) {
            $_SESSION['flash'] = $isEdit ? 'Sản phẩm đã được cập nhật thành công.' : 'Sản phẩm đã được tạo thành công.';
        } else {
            $_SESSION['errors'] = ['Không thể lưu sản phẩm phụ kiện. Vui lòng thử lại.'];
            $_SESSION['old'] = $oldState + ['existing_images' => $this->postedExistingImages()];
        }
        header('Location: ' . ADMIN_URL . 'products');
        exit;
    }

    /**
     * Đọc variants[i][variant_id|name|price|stock|stock_orig|reserved|is_active|attrs][...] từ form.
     * stock = null (giữ nguyên tồn kho PG) khi admin không đổi so với lúc mở form,
     * tránh ghi đè số tồn vừa bị đơn hàng khác trừ.
     */
    private function collectAccessoryVariants(array $variantDefs, array &$errors): array
    {
        $rows = is_array($_POST['variants'] ?? null) ? array_values($_POST['variants']) : [];
        $variants = [];

        foreach ($rows as $i => $row) {
            if (!is_array($row)) {
                continue;
            }
            $label = 'Phiên bản #' . ($i + 1);
            $attrs = $this->castAttributes($variantDefs, is_array($row['attrs'] ?? null) ? $row['attrs'] : [], $errors, $label . ' - ');

            $name = $this->cleanText($row['name'] ?? '');
            if ($name === '' && $attrs) {
                $name = implode(' / ', array_map(static fn($v): string => is_bool($v) ? ($v ? 'Có' : 'Không') : (is_array($v) ? implode(', ', $v) : (string)$v), $attrs));
            }
            if ($name === '') {
                $errors[] = $label . ': vui lòng nhập tên phiên bản.';
            } elseif ($this->strLength($name) > 120) {
                $errors[] = $label . ': tên tối đa 120 ký tự.';
            }

            $priceRaw = trim(str_replace([',', ' '], '', is_scalar($row['price'] ?? null) ? (string)$row['price'] : ''));
            if ($priceRaw === '' || !is_numeric($priceRaw) || (float)$priceRaw < 0) {
                $errors[] = $label . ': giá phải là số lớn hơn hoặc bằng 0.';
            }

            $stockRaw = trim(is_scalar($row['stock'] ?? null) ? (string)$row['stock'] : '');
            $stockOrig = trim(is_scalar($row['stock_orig'] ?? null) ? (string)$row['stock_orig'] : '');
            $stock = null;
            if ($stockRaw !== '') {
                if (!ctype_digit($stockRaw)) {
                    $errors[] = $label . ': tồn kho phải là số nguyên không âm.';
                } elseif ($stockOrig === '' || (int)$stockRaw !== (int)$stockOrig) {
                    $stock = (int)$stockRaw;
                }
            }

            $variantId = trim(is_scalar($row['variant_id'] ?? null) ? (string)$row['variant_id'] : '');
            if (!preg_match('/^[A-Za-z0-9_-]{1,80}$/', $variantId)) {
                $variantId = '';
            }

            $variants[] = [
                'variant_id' => $variantId,
                'name' => $name,
                'price' => is_numeric($priceRaw) ? max(0, (float)$priceRaw) : 0.0,
                'price_input' => $priceRaw,
                'is_active' => isset($row['is_active']) ? 1 : 0,
                'attributes' => $attrs,
                'stock' => $stock,
                'stock_input' => $stockRaw,
                'stock_orig' => $stockOrig,
                'reserved' => max(0, (int)($row['reserved'] ?? 0)),
            ];
        }

        if (!$variants) {
            $errors[] = 'Sản phẩm phụ kiện cần ít nhất 1 phiên bản.';
        }
        return $variants;
    }

    /**
     * Ép kiểu giá trị form theo định nghĩa thuộc tính của category.
     * number -> int|float, boolean -> bool, list -> string[], string/text/enum -> string. Giá trị rỗng bị bỏ.
     */
    private function castAttributes(array $defs, array $posted, array &$errors, string $errorPrefix): array
    {
        $out = [];
        foreach ($defs as $key => $def) {
            $raw = $posted[$key] ?? null;
            $label = $errorPrefix . $def['label'];
            $options = $def['options'];

            if ($def['type'] === 'boolean') {
                $out[$key] = is_scalar($raw) && !in_array((string)$raw, ['', '0'], true);
                continue;
            }

            if ($def['type'] === 'list') {
                $values = [];
                foreach ((array)$raw as $item) {
                    $item = $this->cleanText(is_scalar($item) ? $item : '');
                    if ($item === '' || in_array($item, $values, true)) {
                        continue;
                    }
                    if ($options && !in_array($item, $options, true)) {
                        $errors[] = $label . ': giá trị "' . $item . '" không hợp lệ.';
                        continue;
                    }
                    $values[] = $item;
                }
                if ($values) {
                    $out[$key] = $values;
                }
            } else {
                $value = $this->cleanText(is_scalar($raw) ? $raw : '');
                if ($value !== '') {
                    if ($def['type'] === 'number') {
                        $number = str_replace([',', ' '], '', $value);
                        if (!is_numeric($number)) {
                            $errors[] = $label . ' phải là số.';
                            continue;
                        }
                        $out[$key] = preg_match('/^-?\d+$/', $number) ? (int)$number : (float)$number;
                    } elseif ($def['type'] === 'enum' && $options && !in_array($value, $options, true)) {
                        $errors[] = $label . ': giá trị "' . $value . '" không hợp lệ.';
                        continue;
                    } else {
                        $max = $def['type'] === 'text' ? 2000 : 255;
                        if ($this->strLength($value) > $max) {
                            $errors[] = $label . ' tối đa ' . $max . ' ký tự.';
                            continue;
                        }
                        $out[$key] = $value;
                    }
                }
            }

            if ($def['required'] && !array_key_exists($key, $out)) {
                $errors[] = $label . ' là bắt buộc.';
            }
        }
        return $out;
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
