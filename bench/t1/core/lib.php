<?php

/**
 * bench/t1/core/lib.php — Khung đo thời gian, tính phân vị p50/p95 và ghi CSV cho Topic T1 (T1-24)
 * Owner: T1 (Data Storage)
 *
 * Công thức phân vị: Nearest-Rank (chỉ số 0-based = ceil((p / 100) * n) - 1 trên mảng đã sắp tăng).
 * Ví dụ với mảng [1..10], p=50 -> phần tử thứ ceil(5.0) = 5; với n=30, p=50 -> phần tử thứ 15, p=95 -> phần tử thứ 29.
 */

require_once __DIR__ . '/../../../config/bootstrap.php';

const BENCH_T1_RESULTS_DIR = ROOT . '/bench/t1/results';

const BENCH_RAW_HEADER = [
    'experiment',
    'db',
    'variant',
    'dataset_size',
    'param',
    'run',
    'latency_ms',
    'rows_returned',
    'payload_bytes',
    'ts',
];

/**
 * Đọc tham số dòng lệnh dạng `--key=value` hoặc `--flag`.
 * Mặc định: size='10k', warmup=5, runs=30.
 *
 * @param array<string, mixed> $defaults
 * @return array<string, mixed>
 */
function bench_args(array $defaults = []): array
{
    $args = array_merge([
        'size'   => '10k',
        'warmup' => 5,
        'runs'   => 30,
    ], $defaults);

    global $argv;
    foreach (array_slice($argv ?? [], 1) as $arg) {
        if (strncmp($arg, '--', 2) !== 0) {
            continue;
        }
        $eq = strpos($arg, '=');
        if ($eq === false) {
            $args[substr($arg, 2)] = true;
            continue;
        }
        $key = substr($arg, 2, $eq - 2);
        $val = substr($arg, $eq + 1);
        if ($key === 'warmup' || $key === 'runs' || $key === 'products') {
            $args[$key] = max(0, (int)$val);
        } else {
            $args[$key] = $val;
        }
    }

    return $args;
}

/**
 * Chốt an toàn: bắt buộc chạy thí nghiệm trên DB tách biệt (VD: `vinfast_bench`),
 * ngăn ghi/xóa nhầm lên DB dev `vinfast` của nhóm.
 */
function bench_require_bench_db(array $args = []): void
{
    if (!empty($args['allow-dev-db'])) {
        return;
    }
    $pgDb = Db::env('PG_DB', 'vinfast');
    $mongoDb = Db::env('MONGO_DB', 'vinfast');
    if ($pgDb === 'vinfast' || $mongoDb === 'vinfast') {
        fwrite(
            STDERR,
            "ABORT: Vui lòng chạy benchmark trên DB riêng (PG_DB=vinfast_bench MONGO_DB=vinfast_bench), hiện tại PG_DB={$pgDb}, MONGO_DB={$mongoDb}.\n"
        );
        exit(2);
    }
}

/** Đo thời gian thực thi $fn bằng đồng hồ đơn điệu độ phân giải nano giây, trả về mili giây (ms). */
function bench_time(callable $fn): float
{
    $t = hrtime(true);
    $fn();
    return (hrtime(true) - $t) / 1e6;
}

/**
 * Phân vị Nearest-Rank cho $p thuộc [0, 100].
 * @param float[]|int[] $xs
 */
function bench_percentile(array $xs, float $p): float
{
    $n = count($xs);
    if ($n === 0) {
        return 0.0;
    }
    sort($xs, SORT_NUMERIC);
    if ($p <= 0) {
        return (float)$xs[0];
    }
    if ($p >= 100) {
        return (float)$xs[$n - 1];
    }
    $idx = min($n - 1, max(0, (int)ceil(($p / 100.0) * $n) - 1));
    return (float)$xs[$idx];
}

function bench_results_path(string $file, string $subDir = 'raw'): string
{
    $dir = BENCH_T1_RESULTS_DIR . ($subDir !== '' ? '/' . trim($subDir, '/') : '');
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    return $dir . '/' . basename($file);
}

function bench_plan_path(string $file): string
{
    return bench_results_path($file, 'plans');
}

/** Khởi tạo mới file CSV kết quả (ghi đè file cũ của cùng lần cấu hình để không bị nhân đôi dòng). */
function bench_reset_csv(string $file, array $header = BENCH_RAW_HEADER, string $subDir = 'raw'): void
{
    $path = bench_results_path($file, $subDir);
    $fp = fopen($path, 'wb');
    if ($fp === false) {
        throw new RuntimeException("Không mở được file CSV: {$path}");
    }
    fputcsv($fp, $header);
    fclose($fp);
}

/** Ghi 1 dòng kết quả vào `bench/t1/results/[subDir]/<file>`, tự chèn header nếu file mới tạo. */
function bench_csv(string $file, array $header, array $row, string $subDir = 'raw'): void
{
    $path = bench_results_path($file, $subDir);
    $needHeader = !file_exists($path) || filesize($path) === 0;
    $fp = fopen($path, 'ab');
    if ($fp === false) {
        throw new RuntimeException("Không mở được file CSV: {$path}");
    }
    if ($needHeader && $header !== []) {
        fputcsv($fp, $header);
    }
    fputcsv($fp, $row);
    fclose($fp);
}

function bench_now_iso(): string
{
    return (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y-m-d\TH:i:s\Z');
}
