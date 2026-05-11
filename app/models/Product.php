<?php

/**
 * app/models/Product.php — Vehicle / Product Model
 * Table: products
 * Owner: Hai Nam (Member 3)
 * Used by: ProductController (frontend), ProductAdminController (admin)
 *
 * Columns: id, category_id, name, slug, description,
 *          specs(JSON), price, images(JSON), is_active, created_at
 *
 * Public query API:
 *   getById, getByIdAdmin, getAll, getByCategory
 *   filterPaginated, filterAll, countFiltered
 *   getSwitchProducts, extractRangeKm
 *   getAdminList, countAdminList, countAdminDistinctCategories
 *   create, updateById, setActive, deleteById, hasOrders, slugExists
 */
class Product
{
    private static ?bool $hasColorTable = null;

    private static function bindNamedParams(PDOStatement $stmt, array $params): void
    {
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
    }

    private static function buildCatalogFilters(array $filters = []): array
    {
        $where = ['is_active = 1'];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = 'name LIKE :search';
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['category_id'])) {
            $where[] = 'category_id = :cat_id';
            $params[':cat_id'] = (int)$filters['category_id'];
        }

        if (!empty($filters['price_min'])) {
            $where[] = 'price >= :price_min';
            $params[':price_min'] = (float)$filters['price_min'];
        }

        if (!empty($filters['price_max'])) {
            $where[] = 'price <= :price_max';
            $params[':price_max'] = (float)$filters['price_max'];
        }

        if (!empty($filters['range']) && $filters['range'] !== 'all') {
            $rangeExpr = "CAST(REGEXP_SUBSTR(JSON_UNQUOTE(JSON_EXTRACT(specs, '$.range')), '[0-9]+(\\.[0-9]+)?') AS DECIMAL(10,2))";

            if ($filters['range'] === 'lt200') {
                $where[] = "($rangeExpr > 0 AND $rangeExpr < 200)";
            } elseif ($filters['range'] === '200-400') {
                $where[] = "($rangeExpr >= 200 AND $rangeExpr <= 400)";
            } elseif ($filters['range'] === 'gt400') {
                $where[] = "$rangeExpr > 400";
            }
        }

        return [implode(' AND ', $where), $params];
    }

    private static function buildAdminFilters(array $filters = []): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = '(p.name LIKE :search_name OR c.name LIKE :search_category)';
            $searchTerm = '%' . $filters['search'] . '%';
            $params[':search_name'] = $searchTerm;
            $params[':search_category'] = $searchTerm;
        }

        if (!empty($filters['category_id'])) {
            $where[] = 'p.category_id = :category_id';
            $params[':category_id'] = (int)$filters['category_id'];
        }

        if (($filters['status'] ?? 'all') === 'active') {
            $where[] = 'p.is_active = 1';
        } elseif (($filters['status'] ?? 'all') === 'inactive') {
            $where[] = 'p.is_active = 0';
        }

        // Price range filter for admin list
        if (isset($filters['price_min']) && $filters['price_min'] !== null && $filters['price_min'] !== '') {
            if (is_numeric($filters['price_min'])) {
                $where[] = 'p.price >= :price_min';
                $params[':price_min'] = (float)$filters['price_min'];
            }
        }

        if (isset($filters['price_max']) && $filters['price_max'] !== null && $filters['price_max'] !== '') {
            if (is_numeric($filters['price_max'])) {
                $where[] = 'p.price <= :price_max';
                $params[':price_max'] = (float)$filters['price_max'];
            }
        }

        return [implode(' AND ', $where), $params];
    }

    public static function create($categoryId, $name, $slug, $description, $specs, $price, $images, $isActive)
    {
        global $pdo;
        $stmt = $pdo->prepare("
            INSERT INTO products (category_id, name, slug, description, specs, price, images, is_active) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $categoryId,
            $name,
            $slug,
            $description,
            json_encode($specs),
            $price,
            json_encode($images),
            $isActive
        ]) ? (int)$pdo->lastInsertId() : 0;
    }
    private static function formatProduct($product)
    {
        if (!$product) return null;
        $product['specs'] = json_decode($product['specs'] ?? '', true) ?? [];
        $product['images'] = json_decode($product['images'] ?? '', true) ?? [];

        $productId = (int)($product['id'] ?? 0);
        if ($productId > 0) {
            $product['exterior_colors'] = self::getExteriorColorsByProductId($productId);
        } else {
            $product['exterior_colors'] = [];
        }

        return $product;
    }

    private static function colorTableExists(): bool
    {
        if (self::$hasColorTable !== null) {
            return self::$hasColorTable;
        }

        global $pdo;

        try {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :table_name');
            $stmt->execute([':table_name' => 'product_colors']);
            self::$hasColorTable = (int)$stmt->fetchColumn() > 0;
        } catch (Throwable $e) {
            self::$hasColorTable = false;
        }

        return self::$hasColorTable;
    }

    private static function normalizeColorRows(array $rows): array
    {
        $normalized = [];

        foreach ($rows as $index => $row) {
            if (!is_array($row)) {
                continue;
            }

            $code = strtoupper(trim((string)($row['code'] ?? $row['color_code'] ?? '')));
            $name = trim((string)($row['name'] ?? $row['color_name'] ?? ''));
            if ($code === '' || $name === '') {
                continue;
            }

            $hex = strtoupper(trim((string)($row['hex'] ?? $row['color_hex'] ?? '')));
            $image = trim((string)($row['image'] ?? $row['image_path'] ?? ''));
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

    public static function getExteriorColorsByProductId(int $productId): array
    {
        if ($productId > 0 && self::colorTableExists()) {
            global $pdo;

            try {
                $stmt = $pdo->prepare('SELECT color_code AS code, color_name AS name, color_hex AS hex, image_path AS image, surcharge, sort_order, is_default FROM product_colors WHERE product_id = ? ORDER BY sort_order ASC, id ASC');
                $stmt->execute([$productId]);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($rows)) {
                    return self::normalizeColorRows($rows);
                }
            } catch (Throwable $e) {
                // Fall through to empty result below.
            }
        }

        return [];
    }

    public static function syncExteriorColors(int $productId, array $rows): bool
    {
        global $pdo;

        if ($productId <= 0) {
            return false;
        }

        if (!self::colorTableExists()) {
            return true;
        }

        try {
            $pdo->beginTransaction();

            $delete = $pdo->prepare('DELETE FROM product_colors WHERE product_id = ?');
            $delete->execute([$productId]);

            if (!empty($rows)) {
                $insert = $pdo->prepare('INSERT INTO product_colors (product_id, color_code, color_name, color_hex, image_path, surcharge, sort_order, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
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
                    $hex = $hex !== '' ? (strpos($hex, '#') === 0 ? $hex : '#' . $hex) : null;
                    $image = trim((string)($row['image'] ?? ''));
                    $image = $image !== '' ? ltrim($image, '/') : null;
                    $surcharge = max(0, (int)($row['surcharge'] ?? 0));
                    $sortOrder = isset($row['sortOrder']) ? (int)$row['sortOrder'] : $index;
                    $isDefault = !empty($row['isDefault']) ? 1 : 0;

                    $insert->execute([
                        $productId,
                        $code,
                        $name,
                        $hex,
                        $image,
                        $surcharge,
                        $sortOrder,
                        $isDefault,
                    ]);
                }
            }

            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return false;
        }
    }
    public static function getById($id)
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM products WHERE is_active = 1 AND id = ? LIMIT 0,1");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        return self::formatProduct($product);
    }
    public static function getByIdAdmin($id)
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? LIMIT 0,1");
        $stmt->execute([(int)$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        return self::formatProduct($product);
    }
    public static function getAll($page = 1, $perPage = 10)
    {
        global $pdo;
        $page = max(1, $page);
        $offset = max(0, (int)($page - 1) * $perPage);
        $stmt = $pdo->prepare("SELECT * FROM products WHERE is_active = 1 ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return array_map([self::class, 'formatProduct'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /** Used by navbar.php to populate dropdown menu per category. */
    public static function getByCategory($categoryId, $page = 1, $perPage = 10): array
    {
        global $pdo;
        $page = max(1, $page);
        $offset = max(0, (int)($page - 1) * $perPage);
        $sql = "SELECT * FROM products
            WHERE is_active = 1 AND category_id = :id
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', (int)$categoryId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return array_map([self::class, 'formatProduct'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function filterPaginated(array $filters = [], int $page = 1, int $perPage = 10): array
    {
        global $pdo;

        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = max(0, (int)($page - 1) * $perPage);

        [$whereClause, $params] = self::buildCatalogFilters($filters);

        $sort = $filters['sort'] ?? 'default';
        $orderBy = 'created_at DESC';
        if ($sort === 'price_asc') {
            $orderBy = 'price ASC';
        } elseif ($sort === 'price_desc') {
            $orderBy = 'price DESC';
        }

        $sql = "SELECT * FROM products WHERE $whereClause ORDER BY $orderBy LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        self::bindNamedParams($stmt, $params);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return array_map([self::class, 'formatProduct'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function filterAll(array $filters = []): array
    {
        global $pdo;

        [$whereClause, $params] = self::buildCatalogFilters($filters);

        $sql = "SELECT * FROM products WHERE $whereClause ORDER BY ";

        $sort = $filters['sort'] ?? 'default';
        if ($sort === 'price_asc') {
            $sql .= "price ASC ";
        } elseif ($sort === 'price_desc') {
            $sql .= "price DESC ";
        } elseif ($sort === 'newest') {
            $sql .= "created_at DESC ";
        } else {
            $sql .= "created_at DESC ";
        }

        $stmt = $pdo->prepare($sql);
        self::bindNamedParams($stmt, $params);
        $stmt->execute();

        return array_map([self::class, 'formatProduct'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function countFiltered(array $filters = []): int
    {
        global $pdo;

        [$whereClause, $params] = self::buildCatalogFilters($filters);
        $sql = "SELECT COUNT(*) FROM products WHERE $whereClause";

        $stmt = $pdo->prepare($sql);
        self::bindNamedParams($stmt, $params);
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    public static function extractRangeKm(array $specs): float
    {
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
            : self::getAll(1, $rawLimit);

        if (empty($switchProductsRaw)) {
            $switchProductsRaw = self::getAll(1, $rawLimit);
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
        global $pdo;

        $page = max(1, $page);
        $offset = max(0, (int)($page - 1) * $perPage);

        [$whereSql, $params] = self::buildAdminFilters($filters);

        $orderBy = 'p.created_at DESC';
        $sort = $filters['sort'] ?? '';
        if ($sort === 'price_asc') {
            $orderBy = 'p.price ASC';
        } elseif ($sort === 'price_desc') {
            $orderBy = 'p.price DESC';
        } elseif ($sort === 'name_asc') {
            $orderBy = 'p.name ASC';
        } elseif ($sort === 'name_desc') {
            $orderBy = 'p.name DESC';
        }

        $sql = "SELECT p.*, c.name AS category_name
                FROM products p
                JOIN categories c ON c.id = p.category_id
                WHERE {$whereSql}
                ORDER BY {$orderBy}
                LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        self::bindNamedParams($stmt, $params);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return array_map([self::class, 'formatProduct'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function countAdminList(array $filters = []): int
    {
        global $pdo;

        [$whereSql, $params] = self::buildAdminFilters($filters);

        $sql = "SELECT COUNT(*)
                FROM products p
                JOIN categories c ON c.id = p.category_id
                WHERE {$whereSql}";

        $stmt = $pdo->prepare($sql);
        self::bindNamedParams($stmt, $params);
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    public static function countAdminDistinctCategories(array $filters = []): int
    {
        global $pdo;

        [$whereSql, $params] = self::buildAdminFilters($filters);
        $sql = "SELECT COUNT(DISTINCT p.category_id)
                FROM products p
                JOIN categories c ON c.id = p.category_id
                WHERE {$whereSql}";

        $stmt = $pdo->prepare($sql);
        self::bindNamedParams($stmt, $params);
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    public static function slugExists(string $slug, ?int $excludeId = null): bool
    {
        global $pdo;
        $sql = 'SELECT COUNT(*) FROM products WHERE slug = :slug';
        if ($excludeId !== null) {
            $sql .= ' AND id <> :id';
        }

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
        if ($excludeId !== null) {
            $stmt->bindValue(':id', $excludeId, PDO::PARAM_INT);
        }
        $stmt->execute();

        return (int)$stmt->fetchColumn() > 0;
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
        global $pdo;
        $stmt = $pdo->prepare(
            'UPDATE products
             SET category_id = ?, name = ?, slug = ?, description = ?, specs = ?, price = ?, images = ?, is_active = ?
             WHERE id = ?'
        );

        return $stmt->execute([
            $categoryId,
            $name,
            $slug,
            $description,
            json_encode($specs),
            $price,
            json_encode($images),
            $isActive,
            $id,
        ]);
    }

    public static function setActive(int $id, int $isActive): bool
    {
        global $pdo;
        $stmt = $pdo->prepare('UPDATE products SET is_active = ? WHERE id = ?');
        return $stmt->execute([$isActive ? 1 : 0, $id]);
    }

    public static function hasOrders(int $id): bool
    {
        global $pdo;
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM orders WHERE product_id = ?');
        $stmt->execute([$id]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public static function deleteById(int $id): bool
    {
        global $pdo;
        $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
