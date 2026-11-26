<?php

/**
 * database/seed/seed.php — Tạo schema PG (nếu chưa có) + seed dữ liệu cũ sang PG + Mongo.
 *
 *   php database/seed/seed.php           chỉ seed khi DB còn trống
 *   php database/seed/seed.php --force   xóa sạch PG (chạy lại 001/002/004) + dữ liệu Mongo rồi seed lại
 *
 *   php database/seed/seed.php --force --size=small|10k|100k
 *                                         seed cơ bản rồi sinh thêm dữ liệu benchmark (generate.php)
 *
 * Trong Docker:  docker compose run --rm seed php database/seed/seed.php --force
 * Xem database/seed/README.md cho kích thước dataset và thời gian chạy.
 *
 * Nguồn: database/seed/legacy_data.json — export 1 lần từ dump MySQL cũ
 * (schema.sql + products.sql + contacts-test_drive-data.sql + about-page-assets.sql).
 *
 * Tài khoản test (chỉ dùng cho môi trường local):
 *   admin@vinfast.vn  / Admin@123   (role admin)
 *   member@vinfast.vn / Member@123  (role member)
 */

require_once __DIR__ . '/../../config/bootstrap.php';

use MongoDB\BSON\UTCDateTime;

const SEED_ADMIN_PASSWORD = 'Admin@123';
const SEED_MEMBER_PASSWORD = 'Member@123';
const SEED_STOCK_PER_VARIANT = 10;

require_once __DIR__ . '/generate.php';

$force = in_array('--force', $argv ?? [], true);
$size = null;
foreach ($argv ?? [] as $arg) {
    if (strncmp($arg, '--size=', 7) === 0) {
        $size = substr($arg, 7);
        if (!isset(GEN_SIZES[$size])) {
            fwrite(STDERR, "seed: --size phải là một trong: " . implode(', ', array_keys(GEN_SIZES)) . PHP_EOL);
            exit(2);
        }
    }
}
$pdo = Db::pg();
$mongo = Db::mongo();

function out(string $msg): void
{
    fwrite(STDOUT, $msg . PHP_EOL);
}

function mongoDate(?string $value): UTCDateTime
{
    $ts = $value ? strtotime($value) : false;
    return new UTCDateTime((($ts !== false) ? $ts : time()) * 1000);
}

function applySchema(PDO $pdo): void
{
    foreach (['001_schema.sql', '002_indexes.sql', '004_coupons.sql'] as $file) {
        out("schema: {$file}");
        $pdo->exec((string)file_get_contents(ROOT . '/database/postgres/' . $file));
    }
}

// ---------------------------------------------------------------------------
// 1. Schema PG
// ---------------------------------------------------------------------------
$hasSchema = (bool)$pdo->query("SELECT to_regclass('public.users') IS NOT NULL")->fetchColumn();
if (!$hasSchema || $force) {
    applySchema($pdo);
} elseif ((int)$pdo->query('SELECT count(*) FROM users')->fetchColumn() > 0) {
    out('seed: PostgreSQL đã có dữ liệu, bỏ qua (dùng --force để seed lại).');
    if ($size !== null) {
        out("generate: bỏ qua --size={$size} vì DB đã có dữ liệu (dùng --force --size={$size}).");
    }
    exit(0);
}

if ($force) {
    foreach (['products', 'categories', 'userActivityLogs'] as $c) {
        $mongo->selectCollection($c)->deleteMany([]);
    }
    $mongo->selectCollection('counters')->updateMany([], ['$set' => ['seq' => 0]]);
}

$legacy = json_decode((string)file_get_contents(__DIR__ . '/legacy_data.json'), true, 512, JSON_THROW_ON_ERROR);

// ---------------------------------------------------------------------------
// 2. Mongo: categories + products (variant mặc định "{id}-STD", màu nằm trong variant)
// ---------------------------------------------------------------------------
$specAttributes = [
    ['key' => 'range', 'label' => 'Quãng đường', 'type' => 'string', 'required' => false, 'filterable' => false],
    ['key' => 'rangeKm', 'label' => 'Quãng đường (km)', 'type' => 'number', 'unit' => 'km', 'required' => false, 'filterable' => true],
    ['key' => 'power', 'label' => 'Công suất', 'type' => 'string', 'required' => false, 'filterable' => false],
    ['key' => 'battery', 'label' => 'Pin', 'type' => 'string', 'required' => false, 'filterable' => false],
    ['key' => 'max_speed', 'label' => 'Tốc độ tối đa', 'type' => 'string', 'required' => false, 'filterable' => false],
    ['key' => 'acceleration', 'label' => 'Tăng tốc', 'type' => 'string', 'required' => false, 'filterable' => false],
    ['key' => 'deposit_amount', 'label' => 'Tiền cọc', 'type' => 'number', 'unit' => 'VND', 'required' => false, 'filterable' => false],
];

$categoryDocs = [];
foreach ($legacy['categories'] as $cat) {
    $categoryDocs[] = [
        'categoryID' => (int)$cat['id'],
        'name' => (string)$cat['name'],
        'slug' => (string)$cat['slug'],
        'description' => null,
        'kind' => 'vehicle',
        'isActive' => true,
        'dynamicAttributes' => $specAttributes,
        'variantAttributes' => [],
        'createdAt' => new UTCDateTime(),
    ];
}
if ($categoryDocs) {
    $mongo->selectCollection('categories')->insertMany($categoryDocs);
}

$colorsByProduct = [];
foreach ($legacy['product_colors'] as $c) {
    $colorsByProduct[(int)$c['product_id']][] = $c;
}

$productDocs = [];
$productNames = [];
foreach ($legacy['products'] as $p) {
    $id = (int)$p['id'];
    $productNames[$id] = (string)$p['name'];
    $specs = is_array($p['specs'] ?? null) ? $p['specs'] : [];
    $rangeKm = Product::extractRangeKm($specs);
    if ($rangeKm > 0) {
        $specs['rangeKm'] = $rangeKm;
    }

    $colors = $colorsByProduct[$id] ?? [];
    usort($colors, static fn($a, $b) => [(int)$a['sort_order'], (int)$a['id']] <=> [(int)$b['sort_order'], (int)$b['id']]);
    $colorDocs = array_map(static function (array $c): array {
        $hex = trim((string)($c['color_hex'] ?? ''));
        return [
            'colorCode' => strtoupper((string)$c['color_code']),
            'colorName' => (string)$c['color_name'],
            'colorHex' => $hex !== '' ? $hex : null,
            'imagePath' => $c['image_path'] ?: null,
            'surcharge' => (int)$c['surcharge'],
            'sortOrder' => (int)$c['sort_order'],
            'isDefault' => (bool)$c['is_default'],
        ];
    }, $colors);

    $price = (int)$p['price'];
    $productDocs[] = [
        'productID' => (string)$id,
        'categoryID' => (int)$p['category_id'],
        'name' => (string)$p['name'],
        'slug' => (string)$p['slug'],
        'description' => $p['description'],
        'basePrice' => $price,
        'images' => array_values(is_array($p['images'] ?? null) ? $p['images'] : []),
        'isActive' => (bool)$p['is_active'],
        'attributes' => (object)$specs,
        'variants' => [[
            'variantId' => $id . '-STD',
            'name' => 'Tiêu chuẩn',
            'price' => $price,
            'isActive' => true,
            'attributes' => (object)[],
            'colors' => $colorDocs,
        ]],
        'avgRating' => 0.0,
        'totalReviews' => 0,
        'totalSold' => 0,
        'reviews' => [],
        'createdAt' => mongoDate($p['created_at'] ?? null),
        'updatedAt' => new UTCDateTime(),
    ];
}
if ($productDocs) {
    $mongo->selectCollection('products')->insertMany($productDocs);
}

$maxProduct = $productNames ? max(array_keys($productNames)) : 0;
$maxCategory = $legacy['categories'] ? max(array_map(static fn($c) => (int)$c['id'], $legacy['categories'])) : 0;
$mongo->selectCollection('counters')->updateOne(['_id' => 'productID'], ['$set' => ['seq' => $maxProduct]], ['upsert' => true]);
$mongo->selectCollection('counters')->updateOne(['_id' => 'categoryID'], ['$set' => ['seq' => $maxCategory]], ['upsert' => true]);
// Catalog phụ kiện (Phase 0.5): category kind=accessory, phiên bản thật, tồn kho theo phiên bản
$catalog = require __DIR__ . '/accessories.php';
foreach ($catalog['categories'] as $cat) {
    $mongo->selectCollection('categories')->insertOne($cat + ['isActive' => true, 'createdAt' => new UTCDateTime()]);
    $maxCategory = max($maxCategory, (int)$cat['categoryID']);
}
$mongo->selectCollection('counters')->updateOne(['_id' => 'categoryID'], ['$set' => ['seq' => $maxCategory]]);

mt_srand(20261120);
$accessoryCount = 0;
foreach ($catalog['products'] as $i => $p) {
    $variants = array_map(static fn(array $v): array => $v + ['stock' => mt_rand(0, 9) === 0 ? 0 : mt_rand(5, 60)], $p['variants']);
    $id = Product::createWithVariants([
        'category_id' => $p['cat'],
        'name' => $p['name'],
        'slug' => Product::slugify($p['name']),
        'description' => $p['desc'],
        'images' => $p['images'],
        'is_active' => true,
        'attributes' => $p['attrs'],
        'variants' => $variants,
        'created_at' => new UTCDateTime((time() - ($i + 1) * 86400) * 1000),
    ]);
    $accessoryCount += $id > 0 ? 1 : 0;
}
out(sprintf('mongo: %d categories, %d products (%d xe, %d phụ kiện)', count($categoryDocs) + count($catalog['categories']), count($productDocs) + $accessoryCount, count($productDocs), $accessoryCount));

// ---------------------------------------------------------------------------
// 3. PostgreSQL
// ---------------------------------------------------------------------------
$pdo->beginTransaction();

$insUser = $pdo->prepare('INSERT INTO users (id, name, email, password, role, created_at) VALUES (?, ?, ?, ?, ?, COALESCE(?::timestamptz, now()))');
foreach ($legacy['users'] as $u) {
    // Hash trong dump cũ là placeholder -> thay bằng mật khẩu test
    $insUser->execute([(int)$u['id'], $u['name'], $u['email'], password_hash(SEED_ADMIN_PASSWORD, PASSWORD_BCRYPT), $u['role'], $u['created_at'] ?? null]);
}
$memberId = (int)$pdo->query("INSERT INTO users (id, name, email, password, role) VALUES (2, 'Khách Hàng Test', 'member@vinfast.vn', '" . password_hash(SEED_MEMBER_PASSWORD, PASSWORD_BCRYPT) . "', 'member') RETURNING id")->fetchColumn();

$insSetting = $pdo->prepare('INSERT INTO site_settings (id, key, value) VALUES (?, ?, ?)');
foreach ($legacy['site_settings'] as $s) {
    $insSetting->execute([(int)$s['id'], $s['key'], $s['value']]);
}

$insContact = $pdo->prepare('INSERT INTO contacts (id, name, email, phone, message, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
foreach ($legacy['contacts'] as $c) {
    $insContact->execute([(int)$c['id'], $c['name'], $c['email'], $c['phone'], $c['message'], $c['status'], $c['created_at'], $c['updated_at']]);
}

$insTd = $pdo->prepare('INSERT INTO test_drives (id, name, email, phone, product_id, variant_id, product_name_snapshot, province, showroom, preferred_date, note, status, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
foreach ($legacy['test_drives'] as $t) {
    $pid = (int)$t['product_id'];
    $insTd->execute([(int)$t['id'], $t['name'], $t['email'], $t['phone'], (string)$pid, $pid . '-STD', $productNames[$pid] ?? '', $t['province'], $t['showroom'], $t['preferred_date'], $t['note'], $t['status'], $t['created_at'], $t['updated_at']]);
}

$insAsset = $pdo->prepare('INSERT INTO page_assets (id, page_type, asset_key, file_path, file_size, mime_type, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
foreach ($legacy['page_assets'] as $a) {
    $insAsset->execute([(int)$a['id'], $a['page_type'], $a['asset_key'], $a['file_path'], (int)($a['file_size'] ?? 0), $a['mime_type'], $a['created_at']]);
}

require_once __DIR__ . '/legacy_content.php';
$contentCounts = seedLegacyContent($pdo, $legacy);

$insInv = $pdo->prepare('INSERT INTO inventory (product_id, variant_id, stock) VALUES (?, ?, ?)');
foreach ($productDocs as $doc) {
    foreach ($doc['variants'] as $v) {
        $insInv->execute([$doc['productID'], $v['variantId'], SEED_STOCK_PER_VARIANT]);
    }
}

// Vài đơn đặt cọc mẫu để trang admin / lịch sử đơn có dữ liệu
$insOrder = $pdo->prepare("INSERT INTO orders (user_id, type, status, payment_status, subtotal_amount, total_amount, note, created_at)
    VALUES (?, 'deposit', ?, ?, ?, ?, ?::jsonb, now() - make_interval(days => ?)) RETURNING id");
$insItem = $pdo->prepare('INSERT INTO order_items (order_id, product_id, variant_id, product_name_snapshot, variant_name_snapshot, color_code, color_name_snapshot, unit_price, quantity)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)');
$samples = [[0, 'pending', 'pending_verify', 3], [3, 'confirmed', 'paid', 20], [6, 'done', 'paid', 45]];
foreach ($samples as [$idx, $status, $payStatus, $daysAgo]) {
    $doc = $productDocs[$idx] ?? null;
    if (!$doc) {
        continue;
    }
    $color = $doc['variants'][0]['colors'][0] ?? null;
    $deposit = (int)(((array)$doc['attributes'])['deposit_amount'] ?? 0);
    $note = json_encode([
        'full_name' => 'Khách Hàng Test', 'phone' => '0900000000', 'email' => 'member@vinfast.vn',
        'province' => 'TP. Hồ Chí Minh', 'showroom' => 'VinFast Landmark 81', 'pay_method' => 'card-intl',
        'color' => ['code' => $color['colorCode'] ?? '', 'name' => $color['colorName'] ?? '', 'surcharge' => $color['surcharge'] ?? 0],
        'variant_name' => 'Tiêu chuẩn',
        'deposit_amount' => $deposit, 'deposit_base_amount' => $deposit,
        'payment_status' => $payStatus,
    ], JSON_UNESCAPED_UNICODE);
    $insOrder->execute([$memberId, $status, $payStatus, $deposit, $deposit, $note, $daysAgo]);
    $orderId = (int)$insOrder->fetchColumn();
    $insItem->execute([$orderId, $doc['productID'], $doc['variants'][0]['variantId'], $doc['name'], 'Tiêu chuẩn', $color['colorCode'] ?? null, $color['colorName'] ?? null, $deposit]);
}

$pdo->query('SELECT sync_identity()');
$pdo->commit();

out(sprintf(
    'postgres: %d users, %d settings, %d contacts, %d test drives, %d page assets, %d inventory rows, %d orders',
    (int)$pdo->query('SELECT count(*) FROM users')->fetchColumn(),
    count($legacy['site_settings']),
    count($legacy['contacts']),
    count($legacy['test_drives']),
    count($legacy['page_assets']),
    (int)$pdo->query('SELECT count(*) FROM inventory')->fetchColumn(),
    (int)$pdo->query('SELECT count(*) FROM orders')->fetchColumn()
));
out('content: ' . implode(', ', array_map(static fn($k, $v) => "$v $k", array_keys($contentCounts), $contentCounts)));
out('seed: xong.');

// ---------------------------------------------------------------------------
// 4. Dữ liệu benchmark (--size=small|10k|100k) — thêm lên trên seed cơ bản
// ---------------------------------------------------------------------------
if ($size !== null) {
    generateBenchData($size, $pdo, $mongo, 'out');
}
