<?php

/**
 * bench/t1/experiments/detail_read.php — Thí nghiệm A: Đọc trọn 1 sản phẩm (`mongo_findone` vs `sql_join4` vs `sql_json_agg`, T1-19)
 *
 * Chạy:
 *   docker compose exec -e PG_DB=vinfast_bench -e MONGO_DB=vinfast_bench app \
 *     php bench/t1/experiments/detail_read.php --size=10k --warmup=5 --runs=30
 *
 * Xuất:
 *   - bench/t1/results/raw/t1_detail_read_<size>.csv
 *   - bench/t1/results/plans/t1_detail_explain_<size>.txt
 */

require_once __DIR__ . '/../core/lib.php';

$args = bench_args(['size' => '10k', 'warmup' => 5, 'runs' => 30]);
bench_require_bench_db($args);

$sizeLabel = (string)$args['size'];
$warmup = max(0, (int)$args['warmup']);
$runs = max(1, (int)$args['runs']);
$totalPicks = max(35, $warmup + $runs);

$pdo = Db::pg();
$mongo = Db::mongo();
$productsColl = $mongo->selectCollection('products');

// 1. Lấy danh sách toàn bộ product_id đã nạp rồi chọn ngẫu nhiên cố định theo mt_srand(42)
$allIds = $pdo->query('SELECT product_id FROM bench.products_sql ORDER BY CAST(product_id AS bigint) ASC')
    ->fetchAll(PDO::FETCH_COLUMN);
if (count($allIds) < 10) {
    throw new RuntimeException('Bảng bench.products_sql chưa có dữ liệu. Chạy load_bench.php trước.');
}

mt_srand(42);
$sampleIds = [];
$nAll = count($allIds);
for ($i = 0; $i < $totalPicks; $i++) {
    $sampleIds[] = (string)$allIds[mt_rand(0, $nAll - 1)];
}

// ---------------------------------------------------------------------------
// Chuẩn hóa dữ liệu đọc về cùng cấu trúc để đối chiếu tính đúng đắn (assert 5 ID)
// ---------------------------------------------------------------------------

function decode_eav_value(string $type, ?string $text, $num)
{
    if ($type === 'bool') {
        return $text === '1' || $text === 't' || $text === 'true';
    }
    if ($type === 'num') {
        $f = (float)$num;
        return ((float)(int)$f === $f) ? (int)$f : round($f, 4);
    }
    if ($type === 'list') {
        $arr = $text !== null ? json_decode($text, true) : [];
        return is_array($arr) ? array_values($arr) : [];
    }
    return (string)$text;
}

function canonicalize_product(array $p): array
{
    $attrs = [];
    foreach (($p['attributes'] ?? []) as $k => $v) {
        if ($v === null) {
            continue;
        }
        if (is_float($v)) {
            $v = ((float)(int)$v === $v) ? (int)$v : round($v, 4);
        }
        $attrs[(string)$k] = $v;
    }
    ksort($attrs);

    $variants = [];
    foreach (($p['variants'] ?? []) as $v) {
        $vAttrs = [];
        foreach (($v['attributes'] ?? []) as $vk => $vv) {
            if ($vv !== null) {
                $vAttrs[(string)$vk] = is_float($vv) && ((float)(int)$vv === $vv) ? (int)$vv : $vv;
            }
        }
        ksort($vAttrs);

        $colors = [];
        foreach (($v['colors'] ?? []) as $c) {
            $colors[] = [
                'code'      => strtoupper((string)($c['code'] ?? $c['colorCode'] ?? '')),
                'name'      => (string)($c['name'] ?? $c['colorName'] ?? ''),
                'hex'       => isset($c['hex']) ? ($c['hex'] ?: null) : (($c['colorHex'] ?? null) ?: null),
                'surcharge' => (int)round((float)($c['surcharge'] ?? 0)),
                'isDefault' => !empty($c['isDefault']),
            ];
        }
        usort($colors, static fn($a, $b) => strcmp($a['code'], $b['code']));

        $variants[] = [
            'variantId'  => (string)$v['variantId'],
            'name'       => (string)$v['name'],
            'price'      => (int)round((float)$v['price']),
            'isActive'   => !empty($v['isActive']),
            'attributes' => $vAttrs,
            'colors'     => $colors,
        ];
    }
    usort($variants, static fn($a, $b) => strcmp($a['variantId'], $b['variantId']));

    return [
        'product_id'  => (string)$p['product_id'],
        'category_id' => (int)$p['category_id'],
        'name'        => (string)$p['name'],
        'slug'        => (string)$p['slug'],
        'base_price'  => (int)round((float)$p['base_price']),
        'is_active'   => !empty($p['is_active']),
        'images'      => array_values((array)($p['images'] ?? [])),
        'attributes'  => $attrs,
        'variants'    => $variants,
    ];
}

// ---------------------------------------------------------------------------
// 3 biến thể đọc trọn 1 sản phẩm
// ---------------------------------------------------------------------------

$sqlJoin4 = <<<'SQL'
SELECT p.product_id, p.category_id, p.name, p.slug, p.description, p.base_price,
       p.images, p.is_active, p.avg_rating, p.total_reviews, p.total_sold,
       v.variant_id, v.name AS variant_name, v.price AS variant_price,
       v.is_active AS variant_active, v.attributes AS variant_attrs, v.sort_order AS variant_sort,
       c.code AS color_code, c.name AS color_name, c.hex AS color_hex,
       c.image_path AS color_image, c.surcharge AS color_surcharge,
       c.sort_order AS color_sort, c.is_default AS color_default,
       a.attr_key, a.val_type, a.value_text, a.value_num
  FROM bench.products_sql p
  JOIN bench.product_variants v ON v.product_id = p.product_id
  LEFT JOIN bench.variant_colors c ON c.variant_id = v.variant_id
  LEFT JOIN bench.product_attributes a ON a.product_id = p.product_id
 WHERE p.product_id = :pid
 ORDER BY v.sort_order, c.sort_order, c.code, a.attr_key
SQL;

$sqlJsonAgg = <<<'SQL'
SELECT p.product_id, p.category_id, p.name, p.slug, p.description, p.base_price,
       p.images, p.is_active, p.avg_rating, p.total_reviews, p.total_sold,
       COALESCE((
         SELECT jsonb_agg(
           jsonb_build_object(
             'variantId', v.variant_id,
             'name', v.name,
             'price', v.price,
             'isActive', v.is_active,
             'attributes', v.attributes,
             'colors', COALESCE((
               SELECT jsonb_agg(
                 jsonb_build_object(
                   'colorCode', c.code,
                   'colorName', c.name,
                   'colorHex', c.hex,
                   'imagePath', c.image_path,
                   'surcharge', c.surcharge,
                   'sortOrder', c.sort_order,
                   'isDefault', c.is_default
                 ) ORDER BY c.sort_order, c.code
               )
               FROM bench.variant_colors c
               WHERE c.variant_id = v.variant_id
             ), '[]'::jsonb)
           ) ORDER BY v.sort_order, v.variant_id
         )
         FROM bench.product_variants v
         WHERE v.product_id = p.product_id
       ), '[]'::jsonb) AS variants_json,
       COALESCE((
         SELECT jsonb_agg(
           jsonb_build_object(
             'key', a.attr_key,
             'type', a.val_type,
             'text', a.value_text,
             'num', a.value_num
           ) ORDER BY a.attr_key
         )
         FROM bench.product_attributes a
         WHERE a.product_id = p.product_id
       ), '[]'::jsonb) AS attrs_json
  FROM bench.products_sql p
 WHERE p.product_id = :pid
SQL;

$stmtJoin4 = $pdo->prepare($sqlJoin4);
$stmtJsonAgg = $pdo->prepare($sqlJsonAgg);

// Mỗi hàm đọc trả `raw` = dữ liệu thô nhận từ DB; byte được tính bằng bench_payload_bytes()
// sau khi bấm giờ xong, để latency chỉ gồm truy vấn + ghép mảng, không gồm json_encode.

/** @return array{data: array, rows: int, raw: mixed} */
$readMongo = static function (string $pid) use ($productsColl): array {
    $doc = $productsColl->findOne(['productID' => $pid]);
    if (!$doc) {
        return ['data' => [], 'rows' => 0, 'raw' => null];
    }
    $out = [
        'product_id'  => (string)$doc['productID'],
        'category_id' => (int)$doc['categoryID'],
        'name'        => (string)$doc['name'],
        'slug'        => (string)$doc['slug'],
        'description' => $doc['description'] ?? null,
        'base_price'  => (int)round((float)$doc['basePrice']),
        'is_active'   => !empty($doc['isActive']),
        'images'      => is_array($doc['images'] ?? null) ? array_values($doc['images']) : [],
        'attributes'  => is_array($doc['attributes'] ?? null) ? $doc['attributes'] : [],
        'variants'    => is_array($doc['variants'] ?? null) ? $doc['variants'] : [],
    ];
    return ['data' => $out, 'rows' => 1, 'raw' => $doc];
};

/** @return array{data: array, rows: int, raw: mixed} */
$readSqlJoin4 = static function (string $pid) use ($stmtJoin4): array {
    $stmtJoin4->execute([':pid' => $pid]);
    $rows = $stmtJoin4->fetchAll(PDO::FETCH_ASSOC);
    $rowCount = count($rows);
    if ($rowCount === 0) {
        return ['data' => [], 'rows' => 0, 'raw' => null];
    }

    $first = $rows[0];
    $variantsMap = [];
    $colorsSeen = [];
    $attrsMap = [];

    foreach ($rows as $r) {
        $vid = (string)$r['variant_id'];
        if (!isset($variantsMap[$vid])) {
            $vAttrs = json_decode((string)($r['variant_attrs'] ?? '{}'), true);
            $variantsMap[$vid] = [
                'variantId'  => $vid,
                'name'       => (string)$r['variant_name'],
                'price'      => (int)$r['variant_price'],
                'isActive'   => (bool)$r['variant_active'],
                'attributes' => is_array($vAttrs) ? $vAttrs : [],
                'colors'     => [],
            ];
        }

        if ($r['color_code'] !== null) {
            $ckey = $vid . '|' . $r['color_code'];
            if (!isset($colorsSeen[$ckey])) {
                $colorsSeen[$ckey] = true;
                $variantsMap[$vid]['colors'][] = [
                    'colorCode' => (string)$r['color_code'],
                    'colorName' => (string)$r['color_name'],
                    'colorHex'  => $r['color_hex'],
                    'imagePath' => $r['color_image'],
                    'surcharge' => (int)$r['color_surcharge'],
                    'sortOrder' => (int)$r['color_sort'],
                    'isDefault' => (bool)$r['color_default'],
                ];
            }
        }

        if ($r['attr_key'] !== null && !array_key_exists((string)$r['attr_key'], $attrsMap)) {
            $attrsMap[(string)$r['attr_key']] = decode_eav_value(
                (string)$r['val_type'],
                $r['value_text'],
                $r['value_num']
            );
        }
    }

    $images = json_decode((string)($first['images'] ?? '[]'), true);
    $out = [
        'product_id'  => (string)$first['product_id'],
        'category_id' => (int)$first['category_id'],
        'name'        => (string)$first['name'],
        'slug'        => (string)$first['slug'],
        'description' => $first['description'],
        'base_price'  => (int)$first['base_price'],
        'is_active'   => (bool)$first['is_active'],
        'images'      => is_array($images) ? array_values($images) : [],
        'attributes'  => $attrsMap,
        'variants'    => array_values($variantsMap),
    ];

    return ['data' => $out, 'rows' => $rowCount, 'raw' => $rows];
};

/** @return array{data: array, rows: int, raw: mixed} */
$readSqlJsonAgg = static function (string $pid) use ($stmtJsonAgg): array {
    $stmtJsonAgg->execute([':pid' => $pid]);
    $row = $stmtJsonAgg->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return ['data' => [], 'rows' => 0, 'raw' => null];
    }

    $variants = json_decode((string)($row['variants_json'] ?? '[]'), true);
    $rawAttrs = json_decode((string)($row['attrs_json'] ?? '[]'), true);
    $attrsMap = [];
    if (is_array($rawAttrs)) {
        foreach ($rawAttrs as $a) {
            if (is_array($a) && isset($a['key'])) {
                $attrsMap[(string)$a['key']] = decode_eav_value(
                    (string)($a['type'] ?? 'str'),
                    isset($a['text']) ? (string)$a['text'] : null,
                    $a['num'] ?? null
                );
            }
        }
    }

    $images = json_decode((string)($row['images'] ?? '[]'), true);
    $out = [
        'product_id'  => (string)$row['product_id'],
        'category_id' => (int)$row['category_id'],
        'name'        => (string)$row['name'],
        'slug'        => (string)$row['slug'],
        'description' => $row['description'],
        'base_price'  => (int)$row['base_price'],
        'is_active'   => (bool)$row['is_active'],
        'images'      => is_array($images) ? array_values($images) : [],
        'attributes'  => $attrsMap,
        'variants'    => is_array($variants) ? $variants : [],
    ];

    return ['data' => $out, 'rows' => 1, 'raw' => $row];
};

// ---------------------------------------------------------------------------
// 2. Assert 3 biến thể trả cùng dữ liệu cho 5 ID đầu tiên
// ---------------------------------------------------------------------------
for ($i = 0; $i < 5; $i++) {
    $pid = $sampleIds[$i];
    $cMongo = canonicalize_product($readMongo($pid)['data']);
    $cJoin4 = canonicalize_product($readSqlJoin4($pid)['data']);
    $cJson  = canonicalize_product($readSqlJsonAgg($pid)['data']);

    if ($cMongo !== $cJoin4) {
        throw new RuntimeException("Mismatch giữa mongo_findone và sql_join4 tại productID={$pid}");
    }
    if ($cMongo !== $cJson) {
        throw new RuntimeException("Mismatch giữa mongo_findone và sql_json_agg tại productID={$pid}");
    }
}
echo "Assertion OK: 3 biến thể trả dữ liệu khớp 100% trên 5 ID kiểm tra.\n";

// ---------------------------------------------------------------------------
// 3. Đo hiệu năng (warmup + runs) và ghi CSV
// ---------------------------------------------------------------------------
$variants = [
    'mongo_findone' => ['db' => 'mongo',    'fn' => $readMongo],
    'sql_join4'     => ['db' => 'postgres', 'fn' => $readSqlJoin4],
    'sql_json_agg'  => ['db' => 'postgres', 'fn' => $readSqlJsonAgg],
];

$csvFile = "t1_detail_read_{$sizeLabel}.csv";
bench_reset_csv($csvFile);

printf("Experiment A (detail_read) — dataset=%s, warmup=%d, runs=%d\n", $sizeLabel, $warmup, $runs);
echo str_repeat('-', 78) . PHP_EOL;
printf("%-16s | %9s | %9s | %9s | %9s | %10s\n", 'Variant', 'p50 (ms)', 'p95 (ms)', 'max (ms)', 'Avg Rows', 'Avg Bytes');
echo str_repeat('-', 78) . PHP_EOL;

foreach ($variants as $vName => $meta) {
    $fn = $meta['fn'];
    for ($w = 0; $w < $warmup; $w++) {
        $fn($sampleIds[$w]);
    }

    $latencies = [];
    $rowsSum = 0;
    $bytesSum = 0;

    for ($r = 1; $r <= $runs; $r++) {
        $pid = $sampleIds[$warmup + $r - 1];
        $res = null;
        $ms = bench_time(static function () use ($fn, $pid, &$res): void {
            $res = $fn($pid);
        });
        $bytes = $res['raw'] !== null ? bench_payload_bytes($res['raw']) : 0;
        $latencies[] = $ms;
        $rowsSum += $res['rows'];
        $bytesSum += $bytes;

        bench_csv($csvFile, BENCH_RAW_HEADER, [
            'detail_read',
            $meta['db'],
            $vName,
            $sizeLabel,
            $pid,
            $r,
            sprintf('%.4f', $ms),
            $res['rows'],
            $bytes,
            bench_now_iso(),
        ]);
    }

    printf(
        "%-16s | %9.3f | %9.3f | %9.3f | %9.1f | %10.0f\n",
        $vName,
        bench_percentile($latencies, 50),
        bench_percentile($latencies, 95),
        max($latencies),
        $rowsSum / $runs,
        $bytesSum / $runs
    );
}
echo str_repeat('-', 78) . PHP_EOL;

// ---------------------------------------------------------------------------
// 4. Lưu EXPLAIN thực tế cho 1 sản phẩm đại diện (ưu tiên xe nhiều phiên bản có màu)
// ---------------------------------------------------------------------------
$explainPid = (string)($pdo->query(
    "SELECT v.product_id FROM bench.product_variants v
     JOIN bench.variant_colors c ON c.variant_id = v.variant_id
     GROUP BY v.product_id HAVING count(DISTINCT v.variant_id) >= 2
     ORDER BY CAST(v.product_id AS bigint) ASC LIMIT 1"
)->fetchColumn() ?: $sampleIds[0]);

$explainLines = [];
$explainLines[] = "=== T1 Experiment A EXPLAIN (dataset={$sizeLabel}, productID={$explainPid}) ===";
$explainLines[] = '';

// Mongo explain("executionStats")
$mongoExplain = $mongo->command([
    'explain' => [
        'find'   => 'products',
        'filter' => ['productID' => $explainPid],
        'limit'  => 1,
    ],
    'verbosity' => 'executionStats',
])->toArray()[0] ?? [];

$explainLines[] = "--- 1. MongoDB findOne({productID: \"{$explainPid}\"}) executionStats ---";
$explainLines[] = json_encode($mongoExplain['executionStats'] ?? $mongoExplain, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$explainLines[] = '';

// PG EXPLAIN (ANALYZE, BUFFERS) cho sql_join4
$explainLines[] = "--- 2. PostgreSQL sql_join4 EXPLAIN (ANALYZE, BUFFERS) ---";
$stExp1 = $pdo->prepare('EXPLAIN (ANALYZE, BUFFERS) ' . $sqlJoin4);
$stExp1->execute([':pid' => $explainPid]);
foreach ($stExp1->fetchAll(PDO::FETCH_COLUMN) as $line) {
    $explainLines[] = (string)$line;
}
$explainLines[] = '';

// PG EXPLAIN (ANALYZE, BUFFERS) cho sql_json_agg
$explainLines[] = "--- 3. PostgreSQL sql_json_agg EXPLAIN (ANALYZE, BUFFERS) ---";
$stExp2 = $pdo->prepare('EXPLAIN (ANALYZE, BUFFERS) ' . $sqlJsonAgg);
$stExp2->execute([':pid' => $explainPid]);
foreach ($stExp2->fetchAll(PDO::FETCH_COLUMN) as $line) {
    $explainLines[] = (string)$line;
}

$explainPath = bench_plan_path("t1_detail_explain_{$sizeLabel}.txt");
file_put_contents($explainPath, implode(PHP_EOL, $explainLines) . PHP_EOL);
printf("Saved CSV -> bench/t1/results/raw/%s & EXPLAIN -> bench/t1/results/plans/t1_detail_explain_%s.txt\n", $csvFile, $sizeLabel);
