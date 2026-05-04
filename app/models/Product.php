<?php

/**
 * app/models/Product.php — Vehicle / Product Model
 * Table: products
 * Owner: Hai Nam (Member 3)
 * Used by: ProductController (frontend), ProductAdminController (admin)
 *
 * Columns: id, category_id, name, slug, description,
 *          specs(JSON), price, images(JSON), is_active, created_at
 */
class Product
{
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
        ]);
    }
    private static function formatProduct($product)
    {
        if (!$product) return null;
        $product['specs'] = json_decode($product['specs'] ?? '', true) ?? [];
        $product['images'] = json_decode($product['images'] ?? '', true) ?? [];
        return $product;
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
    public static function countAll()
    {
        global $pdo;
        $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1");
        return $stmt->fetchColumn();
    }
    public static function getByCategory($categoryId, $page = 1, $perPage = 10)
    {
        global $pdo;
        $page = max(1, $page);
        $offset = max(0, (int)($page - 1) * $perPage);
        $sql = "SELECT * FROM products 
            WHERE is_active = 1 AND category_id = :id 
            ORDER BY created_at DESC 
            LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return array_map([self::class, 'formatProduct'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    public static function search($keyword, $page = 1, $perPage = 10)
    {
        global $pdo;
        $page = max(1, $page);
        $offset = max(0, (int)($page - 1) * $perPage);
        $sql = "SELECT * FROM products 
            WHERE is_active = 1 AND name LIKE :keyword 
            ORDER BY created_at DESC 
            LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':keyword', "%$keyword%", PDO::PARAM_STR);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return array_map([self::class, 'formatProduct'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    public static function countByCategory($categoryId)
    {
        global $pdo;
        $query = "SELECT COUNT(*) as cnt FROM products WHERE is_active = 1 AND category_id = ?";

        $stmt = $pdo->prepare($query);
        $stmt->execute([$categoryId]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['cnt'] : 0;
    }
    public static function filter(array $filters = [], int $page = 1, int $perPage = 10): array
    {
        global $pdo;
        $page = max(1, $page);
        $offset = max(0, (int)($page - 1) * $perPage);

        $items = self::filterAll($filters);

        return array_slice($items, $offset, $perPage);
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

        $sql = "SELECT p.*, c.name AS category_name
                FROM products p
                JOIN categories c ON c.id = p.category_id
                WHERE {$whereSql}
                ORDER BY p.created_at DESC
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
