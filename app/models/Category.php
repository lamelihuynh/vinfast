<?php

/**
 * app/models/Category.php — Vehicle Category Model (MongoDB: categories)
 * Owner: Hai Nam (Member 3)
 *
 * Document: { categoryID:int, name, slug, description, kind, isActive, dynamicAttributes[], variantAttributes[] }
 *   kind = 'vehicle'   : xe, luồng đặt cọc / lái thử
 *   kind = 'accessory' : phụ kiện (pin, sạc, thời trang...), mua theo số lượng, có phiên bản thật
 * API trả về mảng như bản MySQL cũ: id, name, slug (+ kind)
 */
class Category
{
    public const KINDS = ['vehicle', 'accessory'];
    public const ATTR_TYPES = ['string', 'number', 'boolean', 'enum', 'text', 'list'];
    public const ATTR_SCOPES = ['dynamic', 'variant'];

    /** Cache trong 1 request: categoryID => doc đã format */
    private static ?array $cache = null;

    private static function collection(): MongoDB\Collection
    {
        return Db::mongo()->selectCollection('categories');
    }

    private static function throwWriteException(MongoDB\Driver\Exception\BulkWriteException $e): never
    {
        $errs = $e->getWriteResult()->getWriteErrors();
        $code = $errs ? $errs[0]->getCode() : (int)$e->getCode();
        error_log(sprintf('Category MongoDB write error (%d): %s', $code, $e->getMessage()));

        $message = $code === 11000
            ? 'Slug hoặc mã danh mục đã tồn tại.'
            : 'Dữ liệu danh mục hoặc thuộc tính không hợp lệ.';

        throw new InvalidArgumentException($message, $code, $e);
    }

    private static function format(?array $doc): ?array
    {
        if (!$doc) {
            return null;
        }

        return [
            'id' => (int)$doc['categoryID'],
            'name' => (string)($doc['name'] ?? ''),
            'slug' => (string)($doc['slug'] ?? ''),
            'description' => $doc['description'] ?? null,
            'kind' => in_array($doc['kind'] ?? '', self::KINDS, true) ? $doc['kind'] : 'vehicle',
            'is_active' => (!array_key_exists('isActive', $doc) || !empty($doc['isActive'])) ? 1 : 0,
            'dynamic_attributes' => is_array($doc['dynamicAttributes'] ?? null) ? array_values($doc['dynamicAttributes']) : [],
            'variant_attributes' => is_array($doc['variantAttributes'] ?? null) ? array_values($doc['variantAttributes']) : [],
        ];
    }

    public static function getAll()
    {
        $docs = self::collection()->find([], ['sort' => ['name' => 1]])->toArray();
        return array_map(static fn(array $doc): array => array_intersect_key(self::format($doc), array_flip(['id', 'name', 'slug', 'kind'])), $docs);
    }

    /** Danh sách đầy đủ phục vụ trang quản trị /admin/categories (sắp theo categoryID). */
    public static function getAdminAll(): array
    {
        $docs = self::collection()->find([], ['sort' => ['categoryID' => 1]])->toArray();
        return array_values(array_filter(array_map([self::class, 'format'], $docs)));
    }

    /** Đếm số sản phẩm của từng category bằng 1 lệnh aggregate duy nhất. */
    public static function productCountsByCategory(): array
    {
        $counts = [];
        $cursor = Db::mongo()->selectCollection('products')->aggregate([
            ['$group' => ['_id' => '$categoryID', 'n' => ['$sum' => 1]]],
        ]);
        foreach ($cursor as $row) {
            $counts[(int)($row['_id'] ?? 0)] = (int)($row['n'] ?? 0);
        }
        return $counts;
    }

    public static function countProducts(int $id): int
    {
        return (int)Db::mongo()->selectCollection('products')->countDocuments(['categoryID' => $id]);
    }

    /** Mọi category (đầy đủ thuộc tính động), key = categoryID. Cache trong request. */
    public static function allById(): array
    {
        if (self::$cache === null) {
            self::$cache = [];
            foreach (self::collection()->find([], ['sort' => ['name' => 1]]) as $doc) {
                $cat = self::format($doc);
                self::$cache[$cat['id']] = $cat;
            }
        }
        return self::$cache;
    }

    /** Map categoryID => name, dùng cho danh sách sản phẩm admin. */
    public static function namesById(): array
    {
        return array_map(static fn(array $c): string => $c['name'], self::allById());
    }

    public static function kindOf(int $categoryId): string
    {
        return self::allById()[$categoryId]['kind'] ?? 'vehicle';
    }

    /** @return int[] categoryID thuộc 1 kind */
    public static function idsByKind(string $kind): array
    {
        return array_keys(array_filter(self::allById(), static fn(array $c): bool => $c['kind'] === $kind));
    }

    public static function getById($id)
    {
        return self::format(self::collection()->findOne(['categoryID' => (int)$id]));
    }

    public static function countBySlug($slug, ?int $excludeId = null)
    {
        $query = ['slug' => (string)$slug];
        if ($excludeId !== null) {
            $query['categoryID'] = ['$ne' => $excludeId];
        }
        return (int)self::collection()->countDocuments($query);
    }

    public static function create(
        string $name,
        string $slug,
        string $kind = 'vehicle',
        ?string $description = null,
        bool $isActive = true
    ): int {
        $counter = Db::mongo()->selectCollection('counters')->findOneAndUpdate(
            ['_id' => 'categoryID'],
            ['$inc' => ['seq' => 1]],
            ['upsert' => true, 'returnDocument' => MongoDB\Operation\FindOneAndUpdate::RETURN_DOCUMENT_AFTER]
        );
        $id = (int)$counter['seq'];
        $desc = $description !== null && trim($description) !== '' ? trim($description) : null;

        try {
            self::collection()->insertOne([
                'categoryID' => $id,
                'name' => trim($name),
                'slug' => trim($slug),
                'description' => $desc,
                'kind' => in_array($kind, self::KINDS, true) ? $kind : 'vehicle',
                'isActive' => $isActive,
                'dynamicAttributes' => [],
                'variantAttributes' => [],
                'createdAt' => new MongoDB\BSON\UTCDateTime(),
                'updatedAt' => new MongoDB\BSON\UTCDateTime(),
            ]);
        } catch (MongoDB\Driver\Exception\BulkWriteException $e) {
            self::throwWriteException($e);
        }

        self::$cache = null;
        return $id;
    }

    public static function update(
        int $id,
        string $name,
        string $slug,
        string $kind = 'vehicle',
        ?string $description = null,
        bool $isActive = true
    ): bool {
        $desc = $description !== null && trim($description) !== '' ? trim($description) : null;

        try {
            $res = self::collection()->updateOne(
                ['categoryID' => $id],
                ['$set' => [
                    'name' => trim($name),
                    'slug' => trim($slug),
                    'description' => $desc,
                    'kind' => in_array($kind, self::KINDS, true) ? $kind : 'vehicle',
                    'isActive' => $isActive,
                    'updatedAt' => new MongoDB\BSON\UTCDateTime(),
                ]]
            );
        } catch (MongoDB\Driver\Exception\BulkWriteException $e) {
            self::throwWriteException($e);
        }

        self::$cache = null;
        return $res->getMatchedCount() > 0;
    }

    private static function scopeField(string $scope): string
    {
        if ($scope === 'dynamic') {
            return 'dynamicAttributes';
        }
        if ($scope === 'variant') {
            return 'variantAttributes';
        }
        throw new InvalidArgumentException('Phạm vi thuộc tính không hợp lệ (chỉ nhận dynamic hoặc variant).');
    }

    /**
     * Chuẩn hóa và kiểm tra 1 định nghĩa thuộc tính khớp validator `001_collections.js`.
     * @param array<string,mixed> $def
     */
    private static function normalizeAttributeDef(array $def, string $scope): array
    {
        $key = trim((string)($def['key'] ?? ''));
        if ($key === '' || preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $key) !== 1) {
            throw new InvalidArgumentException('Mã định danh phải bắt đầu bằng chữ cái và chỉ gồm chữ không dấu (a-z), chữ số (0-9) hoặc dấu gạch dưới (_).');
        }

        $label = trim((string)($def['label'] ?? ''));
        if ($label === '') {
            throw new InvalidArgumentException('Vui lòng nhập tên hiển thị của thông số.');
        }

        $type = trim((string)($def['type'] ?? 'string'));
        if (!in_array($type, self::ATTR_TYPES, true)) {
            throw new InvalidArgumentException('Định dạng nhập liệu không hợp lệ.');
        }

        $unitRaw = isset($def['unit']) ? trim((string)$def['unit']) : '';
        $optionsRaw = $def['options'] ?? [];
        if (is_string($optionsRaw)) {
            $optionsRaw = preg_split('/[\r\n,]+/', $optionsRaw) ?: [];
        }
        $options = [];
        foreach ((array)$optionsRaw as $opt) {
            $optStr = trim((string)$opt);
            if ($optStr !== '' && !in_array($optStr, $options, true)) {
                $options[] = $optStr;
            }
        }
        if ($type === 'enum' && empty($options)) {
            throw new InvalidArgumentException('Định dạng danh sách chọn cần nhập ít nhất 1 lựa chọn.');
        }

        $normalized = [
            'key' => $key,
            'label' => $label,
            'type' => $type,
            'unit' => $unitRaw !== '' ? $unitRaw : null,
            'required' => !empty($def['required']),
        ];

        if ($scope === 'dynamic') {
            $normalized['filterable'] = !empty($def['filterable']);
        }
        if ($options || in_array($type, ['enum', 'list'], true)) {
            $normalized['options'] = $options;
        }

        return $normalized;
    }

    /**
     * Thêm thuộc tính mới vào `dynamicAttributes` hoặc `variantAttributes` (T1-13).
     * Chỉ chèn khi `key` chưa tồn tại trong mảng đó (`modifiedCount = 0` nếu trùng key).
     * Thao tác hoàn toàn trên collection `categories`, không chạm tới `products`.
     */
    public static function addAttribute(int $id, string $scope, array $def): bool
    {
        $field = self::scopeField($scope);
        $item = self::normalizeAttributeDef($def, $scope);

        try {
            $res = self::collection()->updateOne(
                ['categoryID' => $id, "{$field}.key" => ['$ne' => $item['key']]],
                [
                    '$push' => [$field => $item],
                    '$set' => ['updatedAt' => new MongoDB\BSON\UTCDateTime()],
                ]
            );
        } catch (MongoDB\Driver\Exception\BulkWriteException $e) {
            self::throwWriteException($e);
        }

        self::$cache = null;
        return $res->getModifiedCount() > 0;
    }

    /**
     * Sửa định nghĩa một thuộc tính có sẵn trong mảng bằng `arrayFilters` (T1-13).
     */
    public static function updateAttribute(int $id, string $scope, array $def): bool
    {
        $field = self::scopeField($scope);
        $item = self::normalizeAttributeDef($def, $scope);

        try {
            $res = self::collection()->updateOne(
                ['categoryID' => $id, "{$field}.key" => $item['key']],
                [
                    '$set' => [
                        "{$field}.$[elem]" => $item,
                        'updatedAt' => new MongoDB\BSON\UTCDateTime(),
                    ],
                ],
                ['arrayFilters' => [['elem.key' => $item['key']]]]
            );
        } catch (MongoDB\Driver\Exception\BulkWriteException $e) {
            self::throwWriteException($e);
        }

        self::$cache = null;
        return $res->getMatchedCount() > 0;
    }

    /**
     * Xóa thuộc tính khỏi định nghĩa category bằng `$pull` (T1-13).
     * Không xóa dữ liệu lịch sử trong `products.attributes`, chỉ ẩn khỏi form nhập.
     */
    public static function removeAttribute(int $id, string $scope, string $key): bool
    {
        $field = self::scopeField($scope);
        $key = trim($key);
        if ($key === '') {
            return false;
        }

        // Lọc theo key để danh mục không có thuộc tính này không khớp: nếu không, `$set updatedAt`
        // luôn làm modifiedCount = 1 và controller báo "đã gỡ" dù không gỡ gì.
        $res = self::collection()->updateOne(
            ['categoryID' => $id, "{$field}.key" => $key],
            [
                '$pull' => [$field => ['key' => $key]],
                '$set' => ['updatedAt' => new MongoDB\BSON\UTCDateTime()],
            ]
        );

        self::$cache = null;
        return $res->getModifiedCount() > 0;
    }

    /**
     * Chặn xóa danh mục nếu vẫn còn sản phẩm thuộc danh mục đó (T1-13: thay hành vi CASCADE cũ).
     */
    public static function delete(int $id): bool
    {
        if (self::countProducts($id) > 0) {
            return false;
        }

        $deleted = self::collection()->deleteOne(['categoryID' => $id])->getDeletedCount() > 0;
        self::$cache = null;
        return $deleted;
    }
}
