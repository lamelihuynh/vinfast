<?php

/**
 * bench/t1/experiments/add_attribute.php — Thí nghiệm B: Chi phí thêm 1 thuộc tính mới & thời gian khóa bảng (T1-20)
 *
 * Chế độ worker nội bộ (Session 2 chạy SELECT liên tục đo thời gian bị chặn bởi AccessExclusiveLock):
 *   php bench/t1/experiments/add_attribute.php --probe-worker
 *
 * Chạy thí nghiệm:
 *   docker compose exec -e PG_DB=vinfast_bench -e MONGO_DB=vinfast_bench app \
 *     php bench/t1/experiments/add_attribute.php --size=10k --warmup=5 --runs=30
 *
 * Xuất:
 *   - bench/t1/results/raw/t1_add_attribute_<size>.csv
 *   (Cột `param` lưu thời gian tối đa câu SELECT đồng thời ở Session 2 bị chặn, tính bằng ms)
 */

require_once __DIR__ . '/../core/lib.php';

// =============================================================================
// Worker chế độ Session 2: chạy SELECT liên tục trên bench.products_sql để đo thời gian bị khóa
// =============================================================================
if (in_array('--probe-worker', $argv ?? [], true)) {
    $pdo2 = Db::pg();
    $stmt = $pdo2->prepare("SELECT base_price FROM bench.products_sql WHERE product_id = '165'");
    $stmt->execute();
    $stmt->fetchColumn();

    stream_set_blocking(STDIN, false);
    $measuring = false;
    $maxMs = 0.0;

    while (!feof(STDIN)) {
        $line = fgets(STDIN);
        if ($line !== false) {
            $cmd = trim($line);
            if ($cmd === 'START') {
                $measuring = true;
                $maxMs = 0.0;
                fwrite(STDOUT, "READY\n");
                fflush(STDOUT);
            } elseif ($cmd === 'STOP') {
                $measuring = false;
                fwrite(STDOUT, sprintf("%.4f\n", $maxMs));
                fflush(STDOUT);
            } elseif ($cmd === 'QUIT') {
                break;
            }
        }

        if ($measuring) {
            $t = hrtime(true);
            $stmt->execute();
            $stmt->fetchColumn();
            $dt = (hrtime(true) - $t) / 1e6;
            if ($dt > $maxMs) {
                $maxMs = $dt;
            }
            usleep(150);
        } else {
            usleep(1000);
        }
    }
    exit(0);
}

// =============================================================================
// Tiến trình đo chính (Session 1)
// =============================================================================
$args = bench_args(['size' => '10k', 'warmup' => 5, 'runs' => 30]);
bench_require_bench_db($args);

$sizeLabel = (string)$args['size'];
$warmup = max(0, (int)$args['warmup']);
$runs = max(1, (int)$args['runs']);
$targetCatId = 1;
$attrKey = 'bench_charging_time';
$colName = 'bench_charging_time_col';

$pdo = Db::pg();
$mongo = Db::mongo();
$catColl = $mongo->selectCollection('categories');
$prodColl = $mongo->selectCollection('products');

$totalSqlProducts = (int)$pdo->query('SELECT count(*) FROM bench.products_sql')->fetchColumn();
$cat1Count = (int)$pdo->query("SELECT count(*) FROM bench.products_sql WHERE category_id = {$targetCatId}")->fetchColumn();
if ($totalSqlProducts < 10) {
    throw new RuntimeException('Bảng bench.products_sql chưa có dữ liệu. Chạy load_bench.php trước.');
}

// Khởi chạy Session 2 probe worker
$descriptors = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];
$workerProc = proc_open([PHP_BINARY, __FILE__, '--probe-worker'], $descriptors, $pipes);
if (!is_resource($workerProc)) {
    throw new RuntimeException('Không khởi tạo được tiến trình probe Session 2.');
}

$probeStart = static function () use (&$pipes): void {
    fwrite($pipes[0], "START\n");
    fflush($pipes[0]);
    fgets($pipes[1]); // chờ READY
    usleep(500);      // đảm bảo vòng lặp SELECT ở Session 2 đã đang chạy
};

$probeStop = static function () use (&$pipes): float {
    fwrite($pipes[0], "STOP\n");
    fflush($pipes[0]);
    $out = fgets($pipes[1]);
    return $out !== false ? (float)trim($out) : 0.0;
};

// Hàm dọn sạch mọi dấu vết thử nghiệm
$cleanupAll = static function () use ($pdo, $catColl, $prodColl, $targetCatId, $attrKey, $colName): void {
    $catColl->updateOne(
        ['categoryID' => $targetCatId],
        ['$pull' => ['dynamicAttributes' => ['key' => $attrKey]]]
    );
    $prodColl->updateMany(
        ['categoryID' => $targetCatId],
        ['$unset' => ["attributes.{$attrKey}" => '']]
    );
    $pdo->prepare('DELETE FROM bench.category_attributes WHERE category_id = ? AND attr_key = ?')
        ->execute([$targetCatId, $attrKey]);
    $pdo->prepare('DELETE FROM bench.product_attributes WHERE attr_key = ?')
        ->execute([$attrKey]);
    $pdo->exec("UPDATE bench.products_sql SET attrs = attrs - '{$attrKey}' WHERE category_id = {$targetCatId} AND attrs ? '{$attrKey}'");
    $pdo->exec("ALTER TABLE bench.products_sql DROP COLUMN IF EXISTS {$colName}");
};

$cleanupAll();
$pdo->exec('VACUUM ANALYZE bench.products_sql');
$pdo->exec('VACUUM ANALYZE bench.product_attributes');

$attrDefMongo = [
    'key'        => $attrKey,
    'label'      => 'Thời gian sạc thử nghiệm',
    'type'       => 'number',
    'unit'       => 'giờ',
    'required'   => false,
    'filterable' => false,
];

$insCatAttrStmt = $pdo->prepare(
    "INSERT INTO bench.category_attributes (category_id, attr_key, label, attr_type, unit, is_required, is_filterable)
     VALUES (:cid, :k, 'Thời gian sạc thử nghiệm', 'number', 'giờ', FALSE, FALSE)"
);
$delCatAttrStmt = $pdo->prepare('DELETE FROM bench.category_attributes WHERE category_id = :cid AND attr_key = :k');
$insEavStmt = $pdo->prepare(
    "INSERT INTO bench.product_attributes (product_id, attr_key, val_type, value_text, value_num)
     SELECT product_id, :k, 'num', '0', 0 FROM bench.products_sql WHERE category_id = :cid"
);
$delEavStmt = $pdo->prepare('DELETE FROM bench.product_attributes WHERE attr_key = :k');

$variants = [
    'mongo_category_only' => [
        'db'      => 'mongo',
        'probe'   => false,
        'vacuum'  => null,
        'run'     => static function () use ($catColl, $targetCatId, $attrDefMongo): int {
            $res = $catColl->updateOne(
                ['categoryID' => $targetCatId],
                ['$push' => ['dynamicAttributes' => $attrDefMongo]]
            );
            return $res->getModifiedCount();
        },
        'undo'    => static function () use ($catColl, $targetCatId, $attrKey): void {
            $catColl->updateOne(
                ['categoryID' => $targetCatId],
                ['$pull' => ['dynamicAttributes' => ['key' => $attrKey]]]
            );
        },
    ],
    'mongo_backfill' => [
        'db'      => 'mongo',
        'probe'   => false,
        'vacuum'  => null,
        'run'     => static function () use ($catColl, $prodColl, $targetCatId, $attrDefMongo, $attrKey): int {
            $catColl->updateOne(
                ['categoryID' => $targetCatId],
                ['$push' => ['dynamicAttributes' => $attrDefMongo]]
            );
            $res = $prodColl->updateMany(
                ['categoryID' => $targetCatId],
                ['$set' => ["attributes.{$attrKey}" => 0]]
            );
            return $res->getModifiedCount();
        },
        'undo'    => static function () use ($catColl, $prodColl, $targetCatId, $attrKey): void {
            $catColl->updateOne(
                ['categoryID' => $targetCatId],
                ['$pull' => ['dynamicAttributes' => ['key' => $attrKey]]]
            );
            $prodColl->updateMany(
                ['categoryID' => $targetCatId],
                ['$unset' => ["attributes.{$attrKey}" => '']]
            );
        },
    ],
    'pg_eav' => [
        'db'      => 'postgres',
        'probe'   => true,
        'vacuum'  => 'bench.product_attributes',
        'run'     => static function () use ($pdo, $insCatAttrStmt, $insEavStmt, $targetCatId, $attrKey): int {
            $pdo->beginTransaction();
            $insCatAttrStmt->execute([':cid' => $targetCatId, ':k' => $attrKey]);
            $insEavStmt->execute([':cid' => $targetCatId, ':k' => $attrKey]);
            $affected = $insEavStmt->rowCount();
            $pdo->commit();
            return $affected;
        },
        'undo'    => static function () use ($delCatAttrStmt, $delEavStmt, $targetCatId, $attrKey): void {
            $delEavStmt->execute([':k' => $attrKey]);
            $delCatAttrStmt->execute([':cid' => $targetCatId, ':k' => $attrKey]);
        },
    ],
    'pg_jsonb' => [
        'db'      => 'postgres',
        'probe'   => true,
        'vacuum'  => 'bench.products_sql',
        'run'     => static function () use ($pdo, $targetCatId, $attrKey): int {
            return (int)$pdo->exec(
                "UPDATE bench.products_sql SET attrs = attrs || '{\"{$attrKey}\": 0}'::jsonb WHERE category_id = {$targetCatId}"
            );
        },
        'undo'    => static function () use ($pdo, $targetCatId, $attrKey): void {
            $pdo->exec(
                "UPDATE bench.products_sql SET attrs = attrs - '{$attrKey}' WHERE category_id = {$targetCatId}"
            );
        },
    ],
    'pg_alter_default' => [
        'db'      => 'postgres',
        'probe'   => true,
        'vacuum'  => null,
        'run'     => static function () use ($pdo, $colName, $totalSqlProducts): int {
            $pdo->exec("ALTER TABLE bench.products_sql ADD COLUMN {$colName} INTEGER DEFAULT 0");
            return $totalSqlProducts;
        },
        'undo'    => static function () use ($pdo, $colName): void {
            $pdo->exec("ALTER TABLE bench.products_sql DROP COLUMN {$colName}");
        },
    ],
    'pg_alter_backfill' => [
        'db'      => 'postgres',
        'probe'   => true,
        'vacuum'  => 'bench.products_sql',
        'run'     => static function () use ($pdo, $colName): int {
            // Migration chuẩn trong 1 transaction: AccessExclusiveLock giữ từ lúc ADD COLUMN tới lúc COMMIT UPDATE toàn bảng
            $pdo->beginTransaction();
            $pdo->exec("ALTER TABLE bench.products_sql ADD COLUMN {$colName} INTEGER");
            $affected = (int)$pdo->exec("UPDATE bench.products_sql SET {$colName} = 0");
            $pdo->commit();
            return $affected;
        },
        'undo'    => static function () use ($pdo, $colName): void {
            $pdo->exec("ALTER TABLE bench.products_sql DROP COLUMN {$colName}");
        },
    ],
];

$csvFile = "t1_add_attribute_{$sizeLabel}.csv";
bench_reset_csv($csvFile);

printf(
    "Experiment B (add_attribute) — dataset=%s (total=%d, cat1=%d), warmup=%d, runs=%d\n",
    $sizeLabel,
    $totalSqlProducts,
    $cat1Count,
    $warmup,
    $runs
);
echo str_repeat('-', 86) . PHP_EOL;
printf("%-20s | %10s | %10s | %10s | %14s | %10s\n", 'Variant', 'p50 (ms)', 'p95 (ms)', 'max (ms)', 'Blocked p50 ms', 'Rows Mod');
echo str_repeat('-', 86) . PHP_EOL;

try {
    foreach ($variants as $vName => $cfg) {
        $runFn = $cfg['run'];
        $undoFn = $cfg['undo'];
        $vacTable = $cfg['vacuum'];

        for ($w = 0; $w < $warmup; $w++) {
            $runFn();
            $undoFn();
        }
        if ($vacTable !== null) {
            $pdo->exec("VACUUM {$vacTable}");
        }

        $latencies = [];
        $blockedList = [];
        $lastAffected = 0;

        for ($r = 1; $r <= $runs; $r++) {
            if ($cfg['probe']) {
                $probeStart();
            }

            $affected = 0;
            $ms = bench_time(static function () use ($runFn, &$affected): void {
                $affected = $runFn();
            });

            $blockedMs = $cfg['probe'] ? $probeStop() : 0.0;
            $undoFn();

            if ($vacTable !== null && ($r % 5 === 0)) {
                $pdo->exec("VACUUM {$vacTable}");
            }

            $latencies[] = $ms;
            $blockedList[] = $blockedMs;
            $lastAffected = $affected;

            bench_csv($csvFile, BENCH_RAW_HEADER, [
                'add_attribute',
                $cfg['db'],
                $vName,
                $sizeLabel,
                sprintf('%.4f', $blockedMs),
                $r,
                sprintf('%.4f', $ms),
                $affected,
                0,
                bench_now_iso(),
            ]);
        }

        if ($vacTable !== null) {
            $pdo->exec("VACUUM ANALYZE {$vacTable}");
        }

        printf(
            "%-20s | %10.3f | %10.3f | %10.3f | %14.3f | %10d\n",
            $vName,
            bench_percentile($latencies, 50),
            bench_percentile($latencies, 95),
            max($latencies),
            bench_percentile($blockedList, 50),
            $lastAffected
        );
    }
} finally {
    $cleanupAll();
    fwrite($pipes[0], "QUIT\n");
    fclose($pipes[0]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($workerProc);
}

echo str_repeat('-', 86) . PHP_EOL;
printf("Saved CSV -> bench/t1/results/raw/%s\n", $csvFile);
