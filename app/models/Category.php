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

    /** Cache trong 1 request: categoryID => doc đã format */
    private static ?array $cache = null;

    private static function collection(): MongoDB\Collection
    {
        return Db::mongo()->selectCollection('categories');
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
            'dynamic_attributes' => $doc['dynamicAttributes'] ?? [],
            'variant_attributes' => $doc['variantAttributes'] ?? [],
        ];
    }

    public static function getAll()
    {
        $docs = self::collection()->find([], ['sort' => ['name' => 1]])->toArray();
        return array_map(static fn(array $doc): array => array_intersect_key(self::format($doc), array_flip(['id', 'name', 'slug', 'kind'])), $docs);
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

    public static function countBySlug($slug)
    {
        return (int)self::collection()->countDocuments(['slug' => (string)$slug]);
    }

    public static function create(string $name, string $slug, string $kind = 'vehicle'): int
    {
        $counter = Db::mongo()->selectCollection('counters')->findOneAndUpdate(
            ['_id' => 'categoryID'],
            ['$inc' => ['seq' => 1]],
            ['upsert' => true, 'returnDocument' => MongoDB\Operation\FindOneAndUpdate::RETURN_DOCUMENT_AFTER]
        );
        $id = (int)$counter['seq'];

        self::collection()->insertOne([
            'categoryID' => $id,
            'name' => $name,
            'slug' => $slug,
            'description' => null,
            'kind' => in_array($kind, self::KINDS, true) ? $kind : 'vehicle',
            'isActive' => true,
            'dynamicAttributes' => [],
            'variantAttributes' => [],
            'createdAt' => new MongoDB\BSON\UTCDateTime(),
        ]);

        self::$cache = null;
        return $id;
    }

    /** Giữ hành vi ON DELETE CASCADE cũ: xóa luôn sản phẩm thuộc category. */
    public static function delete(int $id): bool
    {
        $productIds = [];
        foreach (Db::mongo()->selectCollection('products')->find(['categoryID' => $id], ['projection' => ['productID' => 1]]) as $doc) {
            $productIds[] = (int)$doc['productID'];
        }
        foreach ($productIds as $productId) {
            Product::deleteById($productId);
        }

        return self::collection()->deleteOne(['categoryID' => $id])->getDeletedCount() > 0;
    }
}
