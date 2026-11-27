<?php

/**
 * bench/t1/tools/check_refs.php — Kiểm tra tham chiếu logic chéo giữa PostgreSQL và MongoDB (T1-23)
 *
 * Chạy:
 *   docker compose exec app php bench/t1/tools/check_refs.php
 *
 * Kiểm tra 2 chiều:
 *   1. PG -> Mongo: DISTINCT (product_id, variant_id) trong inventory, cart_items,
 *      wishlist_items, order_items, test_drives không còn tồn tại bên Mongo products.
 *   2. Mongo -> PG: phiên bản (productID, variantId) bên Mongo chưa có dòng trong PG inventory.
 *
 * Mã thoát:
 *   - exit(1): có lỗi nghiêm trọng ở inventory, cart_items hoặc thiếu dòng tồn kho cho variant Mongo.
 *   - exit(0): sạch hoàn toàn, hoặc chỉ có cảnh báo (WARN) ở order_items / test_drives / wishlist_items.
 */

require_once __DIR__ . '/../../../config/bootstrap.php';

$pdo = Db::pg();
$mongo = Db::mongo();

// 1. Đọc tập productID và cặp (productID, variantId) từ Mongo theo lô (projection tối giản)
$mongoProducts = []; // productID => true
$mongoVariants = []; // "productID|variantId" => true

$cursor = $mongo->selectCollection('products')->find(
    [],
    [
        'projection' => ['_id' => 0, 'productID' => 1, 'variants.variantId' => 1],
        'batchSize' => 1000,
    ]
);

foreach ($cursor as $doc) {
    $pid = trim((string) ($doc['productID'] ?? ''));
    if ($pid === '') {
        continue;
    }
    $mongoProducts[$pid] = true;
    foreach ((array) ($doc['variants'] ?? []) as $v) {
        $vid = trim((string) ($v['variantId'] ?? ''));
        if ($vid !== '') {
            $mongoVariants[$pid . '|' . $vid] = true;
        }
    }
}

// 2. Chiều PG -> Mongo trên 5 bảng có tham chiếu logic
// severity = 'ERROR' sẽ làm tiến trình trả exit(1); 'WARN' chỉ cảnh báo.
$pgTables = [
    'inventory' => ['nullable_variant' => false, 'severity' => 'ERROR'],
    'cart_items' => ['nullable_variant' => false, 'severity' => 'ERROR'],
    'wishlist_items' => ['nullable_variant' => true, 'severity' => 'WARN'],
    'order_items' => ['nullable_variant' => false, 'severity' => 'WARN'],
    'test_drives' => ['nullable_variant' => true, 'severity' => 'WARN'],
];

$reportRows = [];
$details = [];
$hasFatalError = false;
$pgInventoryPairs = [];

foreach ($pgTables as $table => $cfg) {
    $totalRows = (int) $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
    $stmt = $pdo->query("SELECT DISTINCT product_id, variant_id FROM {$table} ORDER BY product_id, variant_id");

    $distinctPairs = 0;
    $orphanPairs = [];

    foreach ($stmt as $row) {
        $distinctPairs++;
        $pid = trim((string) ($row['product_id'] ?? ''));
        $vid = $row['variant_id'] !== null ? trim((string) $row['variant_id']) : null;

        if ($table === 'inventory' && $pid !== '' && $vid !== null && $vid !== '') {
            $pgInventoryPairs[$pid . '|' . $vid] = true;
        }

        if ($pid === '' || !isset($mongoProducts[$pid])) {
            $orphanPairs[] = $pid . ' / ' . ($vid ?? 'NULL') . ' (missing product)';
            continue;
        }

        if ($vid === null || $vid === '') {
            if (!$cfg['nullable_variant']) {
                $orphanPairs[] = $pid . ' / NULL (empty variant_id)';
            }
            continue;
        }

        if (!isset($mongoVariants[$pid . '|' . $vid])) {
            $orphanPairs[] = $pid . ' / ' . $vid . ' (missing variant)';
        }
    }

    $orphanCount = count($orphanPairs);
    $status = 'OK';
    if ($orphanCount > 0) {
        $status = $cfg['severity'];
        if ($cfg['severity'] === 'ERROR') {
            $hasFatalError = true;
        }
        $details["PG {$table} -> Mongo"] = $orphanPairs;
    }

    $reportRows[] = [
        'direction' => "PG {$table} -> Mongo",
        'rows' => $totalRows,
        'distinct' => $distinctPairs,
        'missing' => $orphanCount,
        'status' => $status,
    ];
}

// 3. Chiều ngược lại: Mongo variants -> PG inventory
$missingInInventory = [];
foreach ($mongoVariants as $pairKey => $_) {
    if (!isset($pgInventoryPairs[$pairKey])) {
        [$pid, $vid] = explode('|', $pairKey, 2);
        $missingInInventory[] = "{$pid} / {$vid}";
    }
}

$missingInvCount = count($missingInInventory);
if ($missingInvCount > 0) {
    $hasFatalError = true;
    $details['Mongo variants -> PG inventory'] = $missingInInventory;
}

$reportRows[] = [
    'direction' => 'Mongo variants -> PG inventory',
    'rows' => count($mongoProducts),
    'distinct' => count($mongoVariants),
    'missing' => $missingInvCount,
    'status' => $missingInvCount > 0 ? 'ERROR' : 'OK',
];

// 4. In bảng kết quả
printf(
    "Cross-DB Reference Check (PG=%s, Mongo=%s: %d products, %d variants)\n",
    Db::env('PG_DB', 'vinfast'),
    Db::env('MONGO_DB', 'vinfast'),
    count($mongoProducts),
    count($mongoVariants)
);
echo str_repeat('-', 82) . PHP_EOL;
printf("%-32s | %10s | %12s | %10s | %-6s\n", 'Check', 'Total Rows', 'Distinct SKU', 'Orphans', 'Status');
echo str_repeat('-', 82) . PHP_EOL;

foreach ($reportRows as $r) {
    printf(
        "%-32s | %10d | %12d | %10d | %-6s\n",
        $r['direction'],
        $r['rows'],
        $r['distinct'],
        $r['missing'],
        $r['status']
    );
}
echo str_repeat('-', 82) . PHP_EOL;

if ($details) {
    foreach ($details as $label => $items) {
        $sample = array_slice($items, 0, 5);
        $more = count($items) - count($sample);
        echo sprintf(
            "  * %s (%d): %s%s\n",
            $label,
            count($items),
            implode(', ', $sample),
            $more > 0 ? " ... (+{$more} more)" : ''
        );
    }
}

if ($hasFatalError) {
    fwrite(STDERR, "FAILED: Phát hiện lỗi tham chiếu nghiêm trọng (inventory / cart_items).\n");
    exit(1);
}

echo "PASSED: Không có lỗi tham chiếu nghiêm trọng.\n";
exit(0);
