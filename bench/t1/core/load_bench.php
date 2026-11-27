<?php

/**
 * bench/t1/core/load_bench.php — Nạp toàn bộ dữ liệu catalog từ MongoDB sang schema chuẩn hóa `bench.*` của PG (T1-18)
 *
 * Chạy:
 *   docker compose exec -e PG_DB=vinfast_bench -e MONGO_DB=vinfast_bench app php bench/t1/core/load_bench.php
 */

require_once __DIR__ . '/lib.php';

$args = bench_args();
bench_require_bench_db($args);

$pdo = Db::pg();
$mongo = Db::mongo();
$t0 = hrtime(true);

/**
 * Nạp mảng dòng vào bảng PG bằng COPY text format (nhanh gấp nhiều lần INSERT từng dòng).
 * @param array<int, array<int, mixed>> $rows
 */
function copy_batch(PDO $pdo, string $table, array $columns, array &$rows): int
{
    $count = count($rows);
    if ($count === 0) {
        return 0;
    }

    $lines = [];
    foreach ($rows as $r) {
        $cells = [];
        foreach ($r as $v) {
            if ($v === null) {
                $cells[] = '\N';
            } elseif (is_bool($v)) {
                $cells[] = $v ? 't' : 'f';
            } else {
                $cells[] = strtr((string)$v, [
                    "\\" => "\\\\",
                    "\t" => "\\t",
                    "\n" => "\\n",
                    "\r" => "\\r",
                ]);
            }
        }
        $lines[] = implode("\t", $cells);
    }

    $pdo->pgsqlCopyFromArray($table, $lines, "\t", "\\\\N", implode(',', $columns));
    $rows = [];
    return $count;
}

function bson_to_pg_ts($val): ?string
{
    if ($val instanceof MongoDB\BSON\UTCDateTime) {
        return $val->toDateTime()->format('Y-m-d H:i:sP');
    }
    if (is_string($val) && $val !== '') {
        return $val;
    }
    return null;
}

$pdo->beginTransaction();
$pdo->exec('TRUNCATE bench.category_attributes, bench.variant_colors, bench.product_attributes, bench.product_variants, bench.products_sql RESTART IDENTITY CASCADE');

// 1. Nạp định nghĩa thuộc tính từ categories.dynamicAttributes
$catAttrRows = [];
foreach ($mongo->selectCollection('categories')->find() as $cat) {
    $cid = (int)($cat['categoryID'] ?? 0);
    if ($cid <= 0) {
        continue;
    }
    foreach ((array)($cat['dynamicAttributes'] ?? []) as $def) {
        if (!is_array($def) || empty($def['key'])) {
            continue;
        }
        $opts = isset($def['options']) && is_array($def['options'])
            ? json_encode(array_values($def['options']), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : null;
        $catAttrRows[] = [
            $cid,
            (string)$def['key'],
            (string)($def['label'] ?? $def['key']),
            (string)($def['type'] ?? 'string'),
            isset($def['unit']) && $def['unit'] !== '' ? (string)$def['unit'] : null,
            !empty($def['required']),
            !empty($def['filterable']),
            $opts,
        ];
    }
}
$nCatAttrs = copy_batch(
    $pdo,
    'bench.category_attributes',
    ['category_id', 'attr_key', 'label', 'attr_type', 'unit', 'is_required', 'is_filterable', 'options'],
    $catAttrRows
);

// 2. Đọc products từ Mongo theo lô 1000 document và COPY sang 4 bảng chuẩn hóa
$cursor = $mongo->selectCollection('products')->find([], ['batchSize' => 1000]);

$prodRows = [];
$varRows = [];
$colorRows = [];
$attrRows = [];

$nProducts = 0;
$nVariants = 0;
$nColors = 0;
$nAttrs = 0;

$flushAll = static function () use ($pdo, &$prodRows, &$varRows, &$colorRows, &$attrRows, &$nProducts, &$nVariants, &$nColors, &$nAttrs): void {
    $nProducts += copy_batch(
        $pdo,
        'bench.products_sql',
        ['product_id', 'category_id', 'name', 'slug', 'description', 'base_price', 'images', 'attrs', 'is_active', 'avg_rating', 'total_reviews', 'total_sold', 'created_at', 'updated_at'],
        $prodRows
    );
    $nVariants += copy_batch(
        $pdo,
        'bench.product_variants',
        ['variant_id', 'product_id', 'name', 'price', 'is_active', 'attributes', 'sort_order'],
        $varRows
    );
    $nColors += copy_batch(
        $pdo,
        'bench.variant_colors',
        ['variant_id', 'code', 'name', 'hex', 'image_path', 'surcharge', 'sort_order', 'is_default'],
        $colorRows
    );
    $nAttrs += copy_batch(
        $pdo,
        'bench.product_attributes',
        ['product_id', 'attr_key', 'val_type', 'value_text', 'value_num'],
        $attrRows
    );
};

foreach ($cursor as $doc) {
    $pid = (string)($doc['productID'] ?? '');
    if ($pid === '') {
        continue;
    }

    $attrs = is_array($doc['attributes'] ?? null) ? $doc['attributes'] : [];
    $images = is_array($doc['images'] ?? null) ? array_values($doc['images']) : [];
    $createdAt = bson_to_pg_ts($doc['createdAt'] ?? null) ?? date('Y-m-d H:i:sP');
    $updatedAt = bson_to_pg_ts($doc['updatedAt'] ?? null);

    $prodRows[] = [
        $pid,
        (int)($doc['categoryID'] ?? 0),
        (string)($doc['name'] ?? ''),
        (string)($doc['slug'] ?? ''),
        isset($doc['description']) ? (string)$doc['description'] : null,
        (int)round((float)($doc['basePrice'] ?? 0)),
        json_encode($images, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        json_encode((object)$attrs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        !empty($doc['isActive']),
        round((float)($doc['avgRating'] ?? 0), 2),
        (int)($doc['totalReviews'] ?? 0),
        (int)($doc['totalSold'] ?? 0),
        $createdAt,
        $updatedAt,
    ];

    foreach ($attrs as $aKey => $aVal) {
        $aKeyStr = trim((string)$aKey);
        if ($aKeyStr === '' || $aVal === null) {
            continue;
        }
        if (is_bool($aVal)) {
            $attrRows[] = [$pid, $aKeyStr, 'bool', $aVal ? '1' : '0', null];
        } elseif (is_int($aVal) || is_float($aVal)) {
            $attrRows[] = [$pid, $aKeyStr, 'num', (string)$aVal, (float)$aVal];
        } elseif (is_array($aVal)) {
            $attrRows[] = [
                $pid,
                $aKeyStr,
                'list',
                json_encode(array_values($aVal), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                null,
            ];
        } else {
            $attrRows[] = [$pid, $aKeyStr, 'str', (string)$aVal, null];
        }
    }

    foreach (array_values((array)($doc['variants'] ?? [])) as $vIdx => $v) {
        if (!is_array($v) || empty($v['variantId'])) {
            continue;
        }
        $vid = (string)$v['variantId'];
        $vAttrs = is_array($v['attributes'] ?? null) ? $v['attributes'] : [];
        $varRows[] = [
            $vid,
            $pid,
            (string)($v['name'] ?? ''),
            (int)round((float)($v['price'] ?? 0)),
            !empty($v['isActive']),
            json_encode((object)$vAttrs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $vIdx,
        ];

        $seenColors = [];
        foreach (array_values((array)($v['colors'] ?? [])) as $cIdx => $c) {
            if (!is_array($c)) {
                continue;
            }
            $code = strtoupper(trim((string)($c['colorCode'] ?? '')));
            if ($code === '' || isset($seenColors[$code])) {
                continue;
            }
            $seenColors[$code] = true;
            $colorRows[] = [
                $vid,
                $code,
                (string)($c['colorName'] ?? ''),
                isset($c['colorHex']) && $c['colorHex'] !== '' ? (string)$c['colorHex'] : null,
                isset($c['imagePath']) && $c['imagePath'] !== '' ? (string)$c['imagePath'] : null,
                max(0, (int)round((float)($c['surcharge'] ?? 0))),
                isset($c['sortOrder']) ? (int)$c['sortOrder'] : $cIdx,
                !empty($c['isDefault']),
            ];
        }
    }

    if (count($prodRows) >= 1000) {
        $flushAll();
    }
}

$flushAll();
$pdo->commit();

foreach (['category_attributes', 'products_sql', 'product_variants', 'variant_colors', 'product_attributes'] as $tbl) {
    $pdo->exec("ANALYZE bench.{$tbl}");
}

$elapsed = (hrtime(true) - $t0) / 1e6;
printf(
    "load_bench done in %.1f ms: products=%d, variants=%d, colors=%d, eav_attrs=%d, cat_attrs=%d\n",
    $elapsed,
    $nProducts,
    $nVariants,
    $nColors,
    $nAttrs,
    $nCatAttrs
);
