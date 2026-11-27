<?php

/**
 * bench/t1/report/summarize.php — Tổng hợp toàn bộ CSV thô `bench/t1/results/raw/t1_*.csv`
 * thành `bench/t1/results/t1_summary_all.csv` (T1-22).
 *
 * Chạy:
 *   docker compose exec app php bench/t1/report/summarize.php
 *
 * Cột đầu ra:
 *   experiment,db,variant,dataset_size,param,n,p50_ms,p95_ms,mean_ms,min_ms,max_ms
 */

require_once __DIR__ . '/../core/lib.php';

const SUMMARY_HEADER = [
    'experiment',
    'db',
    'variant',
    'dataset_size',
    'param',
    'n',
    'p50_ms',
    'p95_ms',
    'mean_ms',
    'min_ms',
    'max_ms',
];

$rawDir = BENCH_T1_RESULTS_DIR . '/raw';
$files = glob($rawDir . '/t1_*.csv') ?: [];
natsort($files);

$groups = [];

foreach ($files as $file) {
    $base = basename($file);
    if ($base === 't1_summary_all.csv' || str_starts_with($base, 't1_reviews_size_')) {
        continue;
    }

    $fp = fopen($file, 'rb');
    if ($fp === false) {
        throw new RuntimeException("Không đọc được file: {$file}");
    }

    $header = fgetcsv($fp);
    if ($header !== BENCH_RAW_HEADER) {
        fclose($fp);
        continue;
    }

    while (($row = fgetcsv($fp)) !== false) {
        if (count($row) < 9) {
            continue;
        }
        [$exp, $db, $variant, $size, $param, $run, $latMs, $rowsRet, $bytesRet] = $row;

        // Xác định khóa gom nhóm theo bản chất cột `param` của từng thí nghiệm:
        // - reviews: `param` là mức số lượng review/SP (10, 1000, 10000) -> gom theo `param`
        // - add_attribute: `param` là thời gian câu SELECT ở session 2 bị chặn (ms) mỗi lượt -> gom theo biến thể, tính p50(blocked_ms)
        // - detail_read: `param` là productID ngẫu nhiên mỗi lượt -> gom theo biến thể, ghi trung bình rows & bytes
        $groupParamKey = ($exp === 'reviews') ? (string)$param : '';
        $gKey = implode('|', [$exp, $db, $variant, $size, $groupParamKey]);

        if (!isset($groups[$gKey])) {
            $groups[$gKey] = [
                'experiment'   => $exp,
                'db'           => $db,
                'variant'      => $variant,
                'dataset_size' => $size,
                'raw_params'   => [],
                'latencies'    => [],
                'rows'         => [],
                'bytes'        => [],
            ];
        }

        $groups[$gKey]['raw_params'][] = $param;
        $groups[$gKey]['latencies'][] = (float)$latMs;
        $groups[$gKey]['rows'][] = (int)$rowsRet;
        $groups[$gKey]['bytes'][] = (int)$bytesRet;
    }

    fclose($fp);
}

if (!$groups) {
    throw new RuntimeException('Không tìm thấy dữ liệu CSV thô nào trong bench/t1/results/raw/t1_*.csv');
}

// Sắp xếp thứ tự xuất dễ đọc: theo thứ tự thí nghiệm (detail_read -> add_attribute -> reviews), rồi size (10k -> 100k)
$expOrder = ['detail_read' => 1, 'add_attribute' => 2, 'reviews' => 3];
$sizeOrder = ['10k' => 1, '100k' => 2];

uksort($groups, static function (string $ka, string $kb) use ($groups, $expOrder, $sizeOrder): int {
    $a = $groups[$ka];
    $b = $groups[$kb];
    $ea = $expOrder[$a['experiment']] ?? 99;
    $eb = $expOrder[$b['experiment']] ?? 99;
    if ($ea !== $eb) {
        return $ea <=> $eb;
    }
    $sa = $sizeOrder[$a['dataset_size']] ?? 99;
    $sb = $sizeOrder[$b['dataset_size']] ?? 99;
    if ($sa !== $sb) {
        return $sa <=> $sb;
    }
    if ($a['experiment'] === 'reviews') {
        $pa = (int)($a['raw_params'][0] ?? 0);
        $pb = (int)($b['raw_params'][0] ?? 0);
        if ($pa !== $pb) {
            return $pa <=> $pb;
        }
    }
    return 0;
});

bench_reset_csv('t1_summary_all.csv', SUMMARY_HEADER, '');

printf(
    "%-14s | %-8s | %-20s | %-4s | %-20s | %3s | %9s | %9s | %9s | %9s\n",
    'experiment',
    'db',
    'variant',
    'size',
    'param',
    'n',
    'p50_ms',
    'p95_ms',
    'mean_ms',
    'max_ms'
);
echo str_repeat('-', 124) . PHP_EOL;

$rowCount = 0;
foreach ($groups as $g) {
    $lats = $g['latencies'];
    $n = count($lats);
    $p50 = bench_percentile($lats, 50);
    $p95 = bench_percentile($lats, 95);
    $mean = array_sum($lats) / $n;
    $min = min($lats);
    $max = max($lats);

    if ($g['experiment'] === 'reviews') {
        $summaryParam = (string)$g['raw_params'][0];
    } elseif ($g['experiment'] === 'add_attribute') {
        $blockedFloats = array_map('floatval', $g['raw_params']);
        $summaryParam = sprintf('%.4f', bench_percentile($blockedFloats, 50));
    } else {
        $avgRows = array_sum($g['rows']) / $n;
        $avgBytes = array_sum($g['bytes']) / $n;
        $summaryParam = sprintf('rows=%.1f;bytes=%.0f', $avgRows, $avgBytes);
    }

    bench_csv('t1_summary_all.csv', SUMMARY_HEADER, [
        $g['experiment'],
        $g['db'],
        $g['variant'],
        $g['dataset_size'],
        $summaryParam,
        $n,
        sprintf('%.4f', $p50),
        sprintf('%.4f', $p95),
        sprintf('%.4f', $mean),
        sprintf('%.4f', $min),
        sprintf('%.4f', $max),
    ], '');
    $rowCount++;

    printf(
        "%-14s | %-8s | %-20s | %-4s | %-20s | %3d | %9.4f | %9.4f | %9.4f | %9.4f\n",
        $g['experiment'],
        $g['db'],
        $g['variant'],
        $g['dataset_size'],
        $summaryParam,
        $n,
        $p50,
        $p95,
        $mean,
        $max
    );
}

echo str_repeat('-', 124) . PHP_EOL;
printf("Wrote %d summary rows -> bench/t1/results/t1_summary_all.csv\n", $rowCount);
