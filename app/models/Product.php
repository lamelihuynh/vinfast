<?php

/**
 * app/models/Product.php — Vehicle / Product Model (MongoDB: products)
 * Owner: Hai Nam (Member 3)
 * Used by: ProductController (frontend), ProductAdminController (admin)
 *
 * Document: { productID:"165", categoryID, name, slug, description, basePrice, images[], isActive,
 *             attributes{}, variants[{variantId, name, price, isActive, attributes, colors[]}],
 *             avgRating, totalReviews, totalSold, reviews[], createdAt, updatedAt }
 *
 * formatProduct() là adapter Mongo doc -> mảng cũ (quyết định 8), nên view/controller không phải sửa:
 *   id, category_id, name, slug, description, specs, price, images, is_active, created_at,
 *   exterior_colors, category_name (admin) + khóa mới: product_id, kind ('vehicle'|'accessory'), is_accessory,
 *   category_label, variants, default_variant_id, avg_rating, total_sold
 *
 * Phụ kiện (category.kind = 'accessory'): phiên bản thật (size/màu, dung lượng...), mỗi phiên bản 1 dòng
 * tồn kho bên PG. Tạo/sửa qua createWithVariants() / updateWithVariants().
 *
 * Public query API (giữ nguyên):
 *   getById, getByIdAdmin, getAll, getByCategory
 *   filterPaginated, filterAll, countFiltered
 *   getSwitchProducts, extractRangeKm
 *   getAdminList, countAdminList, countAdminDistinctCategories
 *   create, updateById, setActive, deleteById, hasOrders, slugExists
 *   getExteriorColorsByProductId, syncExteriorColors
 *   createWithVariants, updateWithVariants (phụ kiện)
 *
 * Bộ lọc catalog nhận thêm 'kind' => 'vehicle' | 'accessory'.
 */
class Product
{
    private static function collection(): MongoDB\Collection
    {
        return Db::mongo()->selectCollection('products');
    }

    private static function regex(string $text): MongoDB\BSON\Regex
    {
        return new MongoDB\BSON\Regex(preg_quote($text, '/'), 'i');
    }

    private static function now(): MongoDB\BSON\UTCDateTime
    {
        return new MongoDB\BSON\UTCDateTime();
    }

    private static function defaultVariantId($productId): string
    {
        return (string)$productId . '-STD';
    }

    // ------------------------------------------------------------------
    // Adapter
    // ------------------------------------------------------------------

    private static function formatProduct($doc, array $categoryNames = [])
    {
        if (!$doc) {
            return null;
        }

        $attributes = is_array($doc['attributes'] ?? null) ? $doc['attributes'] : [];
        $variants = is_array($doc['variants'] ?? null) ? $doc['variants'] : [];
        $createdAt = $doc['createdAt'] ?? null;
        $createdAtStr = $createdAt instanceof MongoDB\BSON\UTCDateTime
            ? $createdAt->toDateTime()->setTimezone(new DateTimeZone(date_default_timezone_get()))->format('Y-m-d H:i:s')
            : '';

        $colors = [];
        foreach ($variants as $variant) {
            if (!empty($variant['colors'])) {
                $colors = $variant['colors'];
                break;
            }
        }

        $categoryId = (int)($doc['categoryID'] ?? 0);
        $category = Category::allById()[$categoryId] ?? null;
        $kind = $category['kind'] ?? 'vehicle';
        $product = [
            'id' => (int)($doc['productID'] ?? 0),
            'product_id' => (string)($doc['productID'] ?? ''),
            'category_id' => $categoryId,
            'kind' => $kind,
            'is_accessory' => $kind === 'accessory',
            'category_label' => (string)($category['name'] ?? ''),
            'name' => (string)($doc['name'] ?? ''),
            'slug' => (string)($doc['slug'] ?? ''),
            'description' => $doc['description'] ?? null,
            'specs' => $attributes,
            'price' => (float)($doc['basePrice'] ?? 0),
            'images' => array_values(is_array($doc['images'] ?? null) ? $doc['images'] : []),
            'is_active' => !empty($doc['isActive']) ? 1 : 0,
            'created_at' => $createdAtStr,
            'exterior_colors' => self::normalizeColorRows($colors),
            'variants' => $variants,
            'default_variant_id' => (string)($variants[0]['variantId'] ?? self::defaultVariantId($doc['productID'] ?? '')),
            'avg_rating' => (float)($doc['avgRating'] ?? 0),
            'total_reviews' => (int)($doc['totalReviews'] ?? 0),
            'total_sold' => (int)($doc['totalSold'] ?? 0),
        ];

        if (isset($categoryNames[$categoryId])) {
            $product['category_name'] = $categoryNames[$categoryId];
        }

        return $product;
    }

    private static function formatMany(iterable $cursor, array $categoryNames = []): array
    {
        $items = [];
        foreach ($cursor as $doc) {
            $items[] = self::formatProduct($doc, $categoryNames);
        }
        return $items;
    }

    private static function normalizeColorRows(array $rows): array
    {
        $normalized = [];

        foreach ($rows as $index => $row) {
            if (!is_array($row)) {
                continue;
            }

            $code = strtoupper(trim((string)($row['code'] ?? $row['colorCode'] ?? $row['color_code'] ?? '')));
            $name = trim((string)($row['name'] ?? $row['colorName'] ?? $row['color_name'] ?? ''));
            if ($code === '' || $name === '') {
                continue;
            }

            $hex = strtoupper(trim((string)($row['hex'] ?? $row['colorHex'] ?? $row['color_hex'] ?? '')));
            $image = trim((string)($row['image'] ?? $row['imagePath'] ?? $row['image_path'] ?? ''));
            $sortOrder = (int)($row['sortOrder'] ?? $row['sort_order'] ?? $index);

            $normalized[] = [
                'code' => $code,
                'name' => $name,
                'image' => $image,
                'hex' => $hex !== '' ? (strpos($hex, '#') === 0 ? $hex : '#' . $hex) : '',
                'surcharge' => max(0, (int)($row['surcharge'] ?? 0)),
                'isDefault' => !empty($row['isDefault'] ?? $row['is_default'] ?? false),
                'sortOrder' => $sortOrder,
            ];
        }

        usort($normalized, static function (array $left, array $right): int {
            $leftOrder = (int)($left['sortOrder'] ?? 0);
            $rightOrder = (int)($right['sortOrder'] ?? 0);
            if ($leftOrder === $rightOrder) {
                return strcasecmp((string)($left['code'] ?? ''), (string)($right['code'] ?? ''));
            }

            return $leftOrder <=> $rightOrder;
        });

        $hasDefault = false;
        foreach ($normalized as $row) {
            if (!empty($row['isDefault'])) {
                $hasDefault = true;
                break;
            }
        }

        if (!$hasDefault && !empty($normalized)) {
            $normalized[0]['isDefault'] = true;
        }

        return $normalized;
    }

    // ------------------------------------------------------------------
    // Filters
    // ------------------------------------------------------------------

    private static function isTextSearch(array $filters): bool
    {
        return trim((string)($filters['search'] ?? '')) !== ''
            && Db::env('SEARCH_MODE', 'regex') === 'text';
    }

    private static function buildCatalogFilters(array $filters = []): array
    {
        $query = ['isActive' => true];

        $search = trim((string)($filters['search'] ?? ''));
        if ($search !== '') {
            if (self::isTextSearch($filters)) {
                $query['$text'] = ['$search' => $search];
            } else {
                $query['name'] = self::regex($search);
            }
        }

        if (!empty($filters['category_id'])) {
            $query['categoryID'] = (int)$filters['category_id'];
        } elseif (!empty($filters['kind']) && in_array($filters['kind'], Category::KINDS, true)) {
            $query['categoryID'] = ['$in' => Category::idsByKind($filters['kind'])];
        }

        $price = [];
        if (!empty($filters['price_min'])) {
            $price['$gte'] = (float)$filters['price_min'];
        }
        if (!empty($filters['price_max'])) {
            $price['$lte'] = (float)$filters['price_max'];
        }
        if ($price) {
            $query['basePrice'] = $price;
        }

        if (!empty($filters['range']) && $filters['range'] !== 'all') {
            if ($filters['range'] === 'lt200') {
                $query['attributes.rangeKm'] = ['$gt' => 0, '$lt' => 200];
            } elseif ($filters['range'] === '200-400') {
                $query['attributes.rangeKm'] = ['$gte' => 200, '$lte' => 400];
            } elseif ($filters['range'] === 'gt400') {
                $query['attributes.rangeKm'] = ['$gt' => 400];
            }
        }

        // T2-12: lọc thuộc tính động đã được controller whitelist theo category
        if (!empty($filters['attrs']) && is_array($filters['attrs'])) {
            foreach ($filters['attrs'] as $attrKey => $attrVal) {
                if (!is_string($attrKey) || preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $attrKey) !== 1) {
                    continue;
                }
                if (is_bool($attrVal) || is_int($attrVal) || is_float($attrVal) || (is_string($attrVal) && $attrVal !== '')) {
                    $query['attributes.' . $attrKey] = $attrVal;
                }
            }
        }

        return $query;
    }

    private static function catalogSort(array $filters): array
    {
        $sort = $filters['sort'] ?? 'default';
        if ($sort === 'price_asc') {
            return ['basePrice' => 1, 'productID' => 1];
        }
        if ($sort === 'price_desc') {
            return ['basePrice' => -1, 'productID' => 1];
        }
        if (self::isTextSearch($filters)) {
            return ['score' => ['$meta' => 'textScore'], 'createdAt' => -1, 'productID' => 1];
        }
        return ['createdAt' => -1, 'productID' => 1];
    }

    private static function catalogFindOptions(array $filters, ?int $page = null, ?int $perPage = null): array
    {
        $options = [
            'sort' => self::catalogSort($filters),
        ];
        if (self::isTextSearch($filters) && ($filters['sort'] ?? 'default') === 'default') {
            $options['projection'] = ['score' => ['$meta' => 'textScore']];
        }
        if ($page !== null && $perPage !== null) {
            $page = max(1, $page);
            $perPage = max(1, $perPage);
            $options['skip'] = ($page - 1) * $perPage;
            $options['limit'] = $perPage;
        }
        return $options;
    }

    private static function buildAdminFilters(array $filters = []): array
    {
        $query = [];

        if (!empty($filters['search'])) {
            $search = (string)$filters['search'];
            $categoryIds = [];
            foreach (Category::namesById() as $id => $name) {
                if (mb_stripos($name, $search) !== false) {
                    $categoryIds[] = $id;
                }
            }
            $or = [['name' => self::regex($search)]];
            if ($categoryIds) {
                $or[] = ['categoryID' => ['$in' => $categoryIds]];
            }
            $query['$or'] = $or;
        }

        if (!empty($filters['category_id'])) {
            $query['categoryID'] = (int)$filters['category_id'];
        } elseif (!empty($filters['kind']) && in_array($filters['kind'], Category::KINDS, true)) {
            $query['categoryID'] = ['$in' => Category::idsByKind($filters['kind'])];
        }

        if (($filters['status'] ?? 'all') === 'active') {
            $query['isActive'] = true;
        } elseif (($filters['status'] ?? 'all') === 'inactive') {
            $query['isActive'] = false;
        }

        $price = [];
        if (isset($filters['price_min']) && $filters['price_min'] !== '' && is_numeric($filters['price_min'])) {
            $price['$gte'] = (float)$filters['price_min'];
        }
        if (isset($filters['price_max']) && $filters['price_max'] !== '' && is_numeric($filters['price_max'])) {
            $price['$lte'] = (float)$filters['price_max'];
        }
        if ($price) {
            $query['basePrice'] = $price;
        }

        return $query;
    }

    // ------------------------------------------------------------------
    // Reads
    // ------------------------------------------------------------------

    public static function getById($id)
    {
        return self::formatProduct(self::collection()->findOne(['productID' => (string)(int)$id, 'isActive' => true]));
    }

    /**
     * Many products in one $in round trip, keyed by productID (string), including inactive
     * ones so old orders still resolve. Missing ids are simply absent from the result.
     */
    public static function findManyByIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('strval', $ids), static fn(string $id): bool => $id !== '')));
        if ($ids === []) {
            return [];
        }

        $out = [];
        foreach (self::collection()->find(['productID' => ['$in' => $ids]]) as $doc) {
            $product = self::formatProduct($doc);
            $out[$product['product_id']] = $product;
        }

        return $out;
    }

    public static function getByIdAdmin($id)
    {
        return self::formatProduct(self::collection()->findOne(['productID' => (string)(int)$id]));
    }

    /**
     * Lấy nhiều sản phẩm bằng 1 query $in (dùng cho trang wishlist, tránh N+1).
     * @return array<string, array> Map theo product_id
     */
    public static function getByIds(array $ids, bool $includeInactive = true): array
    {
        $uniqueIds = [];
        foreach ($ids as $id) {
            $str = trim((string)$id);
            if ($str !== '' && !in_array($str, $uniqueIds, true)) {
                $uniqueIds[] = $str;
            }
        }
        if (empty($uniqueIds)) {
            return [];
        }

        $filter = ['productID' => ['$in' => $uniqueIds]];
        if (!$includeInactive) {
            $filter['isActive'] = true;
        }

        $byId = [];
        foreach (self::collection()->find($filter) as $doc) {
            $formatted = self::formatProduct($doc);
            if ($formatted !== null) {
                $byId[$formatted['product_id']] = $formatted;
            }
        }
        return $byId;
    }

    /** @param string|null $kind 'vehicle' | 'accessory' | null (tất cả) */
    public static function getAll($page = 1, $perPage = 10, ?string $kind = null)
    {
        $page = max(1, (int)$page);
        $perPage = max(1, (int)$perPage);
        $query = ['isActive' => true];
        if ($kind !== null) {
            $query['categoryID'] = ['$in' => Category::idsByKind($kind)];
        }
        return self::formatMany(self::collection()->find($query, [
            'sort' => ['createdAt' => -1, 'productID' => 1],
            'skip' => ($page - 1) * $perPage,
            'limit' => $perPage,
        ]));
    }

    /** Used by navbar.php to populate dropdown menu per category. */
    public static function getByCategory($categoryId, $page = 1, $perPage = 10): array
    {
        $page = max(1, (int)$page);
        $perPage = max(1, (int)$perPage);
        return self::formatMany(self::collection()->find(['isActive' => true, 'categoryID' => (int)$categoryId], [
            'sort' => ['createdAt' => -1, 'productID' => 1],
            'skip' => ($page - 1) * $perPage,
            'limit' => $perPage,
        ]));
    }

    public static function filterPaginated(array $filters = [], int $page = 1, int $perPage = 10): array
    {
        return self::formatMany(self::collection()->find(
            self::buildCatalogFilters($filters),
            self::catalogFindOptions($filters, $page, $perPage)
        ));
    }

    public static function filterAll(array $filters = []): array
    {
        return self::formatMany(self::collection()->find(
            self::buildCatalogFilters($filters),
            self::catalogFindOptions($filters)
        ));
    }

    public static function countFiltered(array $filters = []): int
    {
        return (int)self::collection()->countDocuments(self::buildCatalogFilters($filters));
    }

    public static function extractRangeKm(array $specs): float
    {
        if (isset($specs['rangeKm']) && is_numeric($specs['rangeKm'])) {
            return (float)$specs['rangeKm'];
        }
        if (!isset($specs['range'])) {
            return 0;
        }
        if (preg_match('/(\d+(?:\.\d+)?)/', (string)$specs['range'], $m)) {
            return (float)$m[1];
        }
        return 0;
    }

    private static function extractModelKey(string $text): string
    {
        $value = strtolower(trim($text));
        if ($value === '') {
            return '';
        }

        if (preg_match('/(?:^|[-_])vf(?:-?mpv)?-?([3-9])(?:[-_]|$)/i', $value, $familyMatch)) {
            return 'vf' . $familyMatch[1];
        }

        $normalized = preg_replace('/[^a-z0-9]+/i', '-', $value);
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

    public static function getSwitchProducts(int $categoryId = 0, int $limit = 12, int $rawLimit = 30): array
    {
        $switchProductsRaw = $categoryId > 0
            ? self::getByCategory($categoryId, 1, $rawLimit)
            : self::getAll(1, $rawLimit, 'vehicle');

        if (empty($switchProductsRaw)) {
            $switchProductsRaw = self::getAll(1, $rawLimit, 'vehicle');
        }

        $switchProducts = [];
        foreach ($switchProductsRaw as $switchItem) {
            if (!is_array($switchItem)) {
                continue;
            }

            $switchId = (int)($switchItem['id'] ?? 0);
            if ($switchId <= 0) {
                continue;
            }

            $switchImages = is_array($switchItem['images'] ?? null) ? $switchItem['images'] : [];
            $switchSlug = (string)($switchItem['slug'] ?? '');
            $switchName = (string)($switchItem['name'] ?? 'VinFast');
            $switchProducts[] = [
                'id' => $switchId,
                'name' => $switchName,
                'slug' => $switchSlug,
                'model_key' => self::extractModelKey($switchSlug !== '' ? $switchSlug : $switchName),
                'price' => (float)($switchItem['price'] ?? 0),
                'image' => is_string($switchImages[0] ?? null) ? (string)$switchImages[0] : '',
                'is_current' => false,
            ];

            if (count($switchProducts) >= $limit) {
                break;
            }
        }

        return $switchProducts;
    }

    public static function getAdminList(array $filters = [], int $page = 1, int $perPage = 10): array
    {
        $page = max(1, $page);
        $perPage = max(1, $perPage);

        $sortMap = [
            'price_asc' => ['basePrice' => 1],
            'price_desc' => ['basePrice' => -1],
            'name_asc' => ['name' => 1],
            'name_desc' => ['name' => -1],
        ];
        $sort = ($sortMap[$filters['sort'] ?? ''] ?? ['createdAt' => -1]) + ['productID' => 1];

        return self::formatMany(self::collection()->find(self::buildAdminFilters($filters), [
            'sort' => $sort,
            'skip' => ($page - 1) * $perPage,
            'limit' => $perPage,
        ]), Category::namesById());
    }

    public static function countAdminList(array $filters = []): int
    {
        return (int)self::collection()->countDocuments(self::buildAdminFilters($filters));
    }

    public static function countAdminDistinctCategories(array $filters = []): int
    {
        return count(self::collection()->distinct('categoryID', self::buildAdminFilters($filters)));
    }

    public static function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = ['slug' => $slug];
        if ($excludeId !== null) {
            $query['productID'] = ['$ne' => (string)$excludeId];
        }
        return self::collection()->countDocuments($query) > 0;
    }

    // ------------------------------------------------------------------
    // Colors (nằm trong variant đầu tiên)
    // ------------------------------------------------------------------

    public static function getExteriorColorsByProductId(int $productId): array
    {
        if ($productId <= 0) {
            return [];
        }
        $product = self::getByIdAdmin($productId);
        return $product['exterior_colors'] ?? [];
    }

    public static function syncExteriorColors(int $productId, array $rows): bool
    {
        if ($productId <= 0) {
            return false;
        }

        $colors = [];
        foreach (array_values($rows) as $index => $row) {
            if (!is_array($row)) {
                continue;
            }

            $code = strtoupper(trim((string)($row['code'] ?? '')));
            $name = trim((string)($row['name'] ?? ''));
            if ($code === '' || $name === '') {
                continue;
            }

            $hex = trim((string)($row['hex'] ?? ''));
            $image = trim((string)($row['image'] ?? ''));
            $colors[] = [
                'colorCode' => $code,
                'colorName' => $name,
                'colorHex' => $hex !== '' ? (strpos($hex, '#') === 0 ? $hex : '#' . $hex) : null,
                'imagePath' => $image !== '' ? ltrim($image, '/') : null,
                'surcharge' => max(0, (int)($row['surcharge'] ?? 0)),
                'sortOrder' => isset($row['sortOrder']) ? (int)$row['sortOrder'] : $index,
                'isDefault' => !empty($row['isDefault']),
            ];
        }

        try {
            self::collection()->updateOne(
                ['productID' => (string)$productId],
                ['$set' => ['variants.0.colors' => $colors, 'updatedAt' => self::now()]]
            );
            return true;
        } catch (Throwable $e) {
            error_log('Product::syncExteriorColors: ' . $e->getMessage());
            return false;
        }
    }

    // ------------------------------------------------------------------
    // Writes
    // ------------------------------------------------------------------

    public static function create($categoryId, $name, $slug, $description, $specs, $price, $images, $isActive)
    {
        $counter = Db::mongo()->selectCollection('counters')->findOneAndUpdate(
            ['_id' => 'productID'],
            ['$inc' => ['seq' => 1]],
            ['upsert' => true, 'returnDocument' => MongoDB\Operation\FindOneAndUpdate::RETURN_DOCUMENT_AFTER]
        );
        $id = (int)$counter['seq'];
        $variantId = self::defaultVariantId($id);

        self::collection()->insertOne([
            'productID' => (string)$id,
            'categoryID' => (int)$categoryId,
            'name' => (string)$name,
            'slug' => (string)$slug,
            'description' => $description,
            'basePrice' => (float)$price,
            'images' => array_values((array)$images),
            'isActive' => (bool)$isActive,
            'attributes' => self::attributesFromSpecs((array)$specs),
            'variants' => [[
                'variantId' => $variantId,
                'name' => 'Tiêu chuẩn',
                'price' => (float)$price,
                'isActive' => true,
                'attributes' => (object)[],
                'colors' => [],
            ]],
            'avgRating' => 0.0,
            'totalReviews' => 0,
            'totalSold' => 0,
            'reviews' => [],
            'createdAt' => self::now(),
            'updatedAt' => self::now(),
        ]);

        // Mỗi variant cần 1 dòng tồn kho bên PG
        InventoryService::ensureRows((string)$id, [$variantId => null]);

        return $id;
    }

    private static function attributesFromSpecs(array $specs): object
    {
        $rangeKm = self::extractRangeKm(array_diff_key($specs, ['rangeKm' => true]));
        if ($rangeKm > 0) {
            $specs['rangeKm'] = $rangeKm;
        } else {
            unset($specs['rangeKm']);
        }
        return (object)$specs;
    }

    public static function updateById(
        int $id,
        int $categoryId,
        string $name,
        string $slug,
        string $description,
        array $specs,
        float $price,
        array $images,
        int $isActive
    ): bool {
        $result = self::collection()->updateOne(['productID' => (string)$id], ['$set' => [
            'categoryID' => $categoryId,
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'attributes' => self::attributesFromSpecs($specs),
            'basePrice' => $price,
            'variants.0.price' => $price,
            'images' => array_values($images),
            'isActive' => (bool)$isActive,
            'updatedAt' => self::now(),
        ]]);

        return $result->getMatchedCount() > 0;
    }

    public static function setActive(int $id, int $isActive): bool
    {
        $result = self::collection()->updateOne(
            ['productID' => (string)$id],
            ['$set' => ['isActive' => (bool)$isActive, 'updatedAt' => self::now()]]
        );
        return $result->getMatchedCount() > 0;
    }

    public static function hasOrders(int $id): bool
    {
        $stmt = Db::pg()->prepare('SELECT EXISTS (SELECT 1 FROM order_items WHERE product_id = ?)');
        $stmt->execute([(string)$id]);
        return (bool)$stmt->fetchColumn();
    }

    public static function deleteById(int $id): bool
    {
        $deleted = self::collection()->deleteOne(['productID' => (string)$id])->getDeletedCount() > 0;

        // Dọn dữ liệu PG tham chiếu logic tới sản phẩm (không có FK chéo DB)
        $pdo = Db::pg();
        InventoryService::deleteProduct((string)$id);
        foreach (['cart_items', 'wishlist_items'] as $table) {
            $pdo->prepare("DELETE FROM {$table} WHERE product_id = ?")->execute([(string)$id]);
        }

        return $deleted;
    }

    // ------------------------------------------------------------------
    // Phụ kiện: sản phẩm nhiều phiên bản
    // ------------------------------------------------------------------

    private static function nextProductId(): int
    {
        $counter = Db::mongo()->selectCollection('counters')->findOneAndUpdate(
            ['_id' => 'productID'],
            ['$inc' => ['seq' => 1]],
            ['upsert' => true, 'returnDocument' => MongoDB\Operation\FindOneAndUpdate::RETURN_DOCUMENT_AFTER]
        );
        return (int)$counter['seq'];
    }

    /** "Áo polo VinFast" -> "ao-polo-vinfast" (bỏ dấu tiếng Việt) */
    public static function slugify(string $text): string
    {
        return strtolower(self::variantCode($text));
    }

    /** "Đỏ / M" -> "DO-M" (bỏ dấu, chỉ A-Z0-9) */
    private static function variantCode(string $text): string
    {
        $map = ['à'=>'a','á'=>'a','ạ'=>'a','ả'=>'a','ã'=>'a','â'=>'a','ầ'=>'a','ấ'=>'a','ậ'=>'a','ẩ'=>'a','ẫ'=>'a','ă'=>'a','ằ'=>'a','ắ'=>'a','ặ'=>'a','ẳ'=>'a','ẵ'=>'a',
            'è'=>'e','é'=>'e','ẹ'=>'e','ẻ'=>'e','ẽ'=>'e','ê'=>'e','ề'=>'e','ế'=>'e','ệ'=>'e','ể'=>'e','ễ'=>'e','ì'=>'i','í'=>'i','ị'=>'i','ỉ'=>'i','ĩ'=>'i',
            'ò'=>'o','ó'=>'o','ọ'=>'o','ỏ'=>'o','õ'=>'o','ô'=>'o','ồ'=>'o','ố'=>'o','ộ'=>'o','ổ'=>'o','ỗ'=>'o','ơ'=>'o','ờ'=>'o','ớ'=>'o','ợ'=>'o','ở'=>'o','ỡ'=>'o',
            'ù'=>'u','ú'=>'u','ụ'=>'u','ủ'=>'u','ũ'=>'u','ư'=>'u','ừ'=>'u','ứ'=>'u','ự'=>'u','ử'=>'u','ữ'=>'u','ỳ'=>'y','ý'=>'y','ỵ'=>'y','ỷ'=>'y','ỹ'=>'y','đ'=>'d'];
        $text = strtr(mb_strtolower($text, 'UTF-8'), $map);
        $code = strtoupper(trim((string)preg_replace('/[^a-z0-9]+/', '-', $text), '-'));
        return $code !== '' ? substr($code, 0, 40) : 'V';
    }

    /**
     * Chuẩn hóa danh sách phiên bản từ form/seed.
     * Mỗi phần tử: [variant_id?, name, price, is_active?, attributes?(array), stock?(int|null)]
     * variant_id cũ được giữ; phiên bản mới sinh "{productID}-{CODE}" (duy nhất trong sản phẩm).
     *
     * @return array{0: array, 1: array<string,int|null>} [variants cho Mongo, stockByVariant]
     */
    private static function buildVariants(int $productId, array $rows): array
    {
        $variants = [];
        $stock = [];
        $used = [];
        foreach (array_values($rows) as $i => $row) {
            $name = trim((string)($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $id = trim((string)($row['variant_id'] ?? ''));
            if ($id === '' || strpos($id, $productId . '-') !== 0) {
                $base = $productId . '-' . self::variantCode($name);
                $id = $base;
                for ($n = 2; isset($used[$id]); $n++) {
                    $id = $base . '-' . $n;
                }
            }
            if (isset($used[$id])) {
                continue;
            }
            $used[$id] = true;

            $attrs = [];
            foreach ((array)($row['attributes'] ?? []) as $k => $v) {
                if ($v === '' || $v === null) {
                    continue;
                }
                $attrs[(string)$k] = is_numeric($v) && !is_string($v) ? $v : (string)$v;
            }

            $variants[] = [
                'variantId' => $id,
                'name' => $name,
                'price' => max(0, (float)($row['price'] ?? 0)),
                'isActive' => !array_key_exists('is_active', $row) || !empty($row['is_active']),
                'attributes' => (object)$attrs,
                'colors' => [],
            ];
            $stock[$id] = array_key_exists('stock', $row) && $row['stock'] !== '' && $row['stock'] !== null ? max(0, (int)$row['stock']) : null;
        }
        return [$variants, $stock];
    }

    private static function basePriceOf(array $variants): float
    {
        $prices = array_map(static fn(array $v): float => (float)$v['price'], array_filter($variants, static fn(array $v): bool => $v['isActive']));
        if (!$prices) {
            $prices = array_map(static fn(array $v): float => (float)$v['price'], $variants);
        }
        return $prices ? min($prices) : 0.0;
    }

    /**
     * Tạo sản phẩm nhiều phiên bản (phụ kiện).
     * $data: category_id, name, slug, description, images[], is_active, attributes[], variants[] (xem buildVariants)
     * @return int productID mới, 0 nếu không có phiên bản hợp lệ
     */
    public static function createWithVariants(array $data): int
    {
        $id = self::nextProductId();
        [$variants, $stock] = self::buildVariants($id, (array)($data['variants'] ?? []));
        if (!$variants) {
            return 0;
        }

        self::collection()->insertOne([
            'productID' => (string)$id,
            'categoryID' => (int)$data['category_id'],
            'name' => (string)$data['name'],
            'slug' => (string)$data['slug'],
            'description' => $data['description'] ?? null,
            'basePrice' => self::basePriceOf($variants),
            'images' => array_values((array)($data['images'] ?? [])),
            'isActive' => !empty($data['is_active']),
            'attributes' => (object)(array)($data['attributes'] ?? []),
            'variants' => $variants,
            'avgRating' => 0.0,
            'totalReviews' => 0,
            'totalSold' => 0,
            'reviews' => [],
            'createdAt' => isset($data['created_at']) && $data['created_at'] instanceof MongoDB\BSON\UTCDateTime ? $data['created_at'] : self::now(),
            'updatedAt' => self::now(),
        ]);

        InventoryService::ensureRows((string)$id, $stock);
        return $id;
    }

    /**
     * Sửa sản phẩm nhiều phiên bản. Phiên bản bị bỏ khỏi danh sách sẽ bị xóa khỏi Mongo,
     * dòng tồn kho của nó bị xóa nếu không có hàng đang giữ (order_items vẫn giữ snapshot).
     */
    public static function updateWithVariants(int $id, array $data): bool
    {
        [$variants, $stock] = self::buildVariants($id, (array)($data['variants'] ?? []));
        if (!$variants) {
            return false;
        }

        $result = self::collection()->updateOne(['productID' => (string)$id], ['$set' => [
            'categoryID' => (int)$data['category_id'],
            'name' => (string)$data['name'],
            'slug' => (string)$data['slug'],
            'description' => $data['description'] ?? null,
            'basePrice' => self::basePriceOf($variants),
            'images' => array_values((array)($data['images'] ?? [])),
            'isActive' => !empty($data['is_active']),
            'attributes' => (object)(array)($data['attributes'] ?? []),
            'variants' => $variants,
            'updatedAt' => self::now(),
        ]]);
        if ($result->getMatchedCount() === 0) {
            return false;
        }

        InventoryService::ensureRows((string)$id, $stock);
        InventoryService::removeRowsExcept((string)$id, array_keys($stock));
        return true;
    }
}
