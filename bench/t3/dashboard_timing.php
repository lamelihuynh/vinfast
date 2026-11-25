<?php

/**
 * bench/t3/dashboard_timing.php — T3-11..15: time each dashboard block, old vs new
 *
 *   docker compose run --rm -e PG_DB=vinfast_bench -e MONGO_DB=vinfast_bench \
 *     seed php bench/t3/dashboard_timing.php [--runs=10]
 *
 * "old" blocks are the pre-T3-12/13 controller code, copied here verbatim so both
 * versions run against the same data. Prints median/p95 ms and how many documents
 * each block pulled from Mongo (counted from the driver's command replies).
 */

require_once __DIR__ . '/../../config/bootstrap.php';

$runs = 10;
foreach ($argv as $arg) {
    if (preg_match('/^--runs=(\d+)$/', $arg, $m)) {
        $runs = max(1, (int)$m[1]);
    }
}

/** Counts documents returned by find/aggregate/getMore, per collection. */
final class MongoDocCounter implements MongoDB\Driver\Monitoring\CommandSubscriber
{
    public array $docs = [];
    private array $collByRequest = [];

    public function commandStarted(MongoDB\Driver\Monitoring\CommandStartedEvent $event): void
    {
        $cmd = $event->getCommand();
        $name = $event->getCommandName();
        $coll = match ($name) {
            'find', 'aggregate', 'count' => (string)($cmd->{$name} ?? ''),
            'getMore' => (string)($cmd->collection ?? ''),
            default => '',
        };
        $this->collByRequest[$event->getRequestId()] = $coll;
    }

    public function commandSucceeded(MongoDB\Driver\Monitoring\CommandSucceededEvent $event): void
    {
        $coll = $this->collByRequest[$event->getRequestId()] ?? '';
        unset($this->collByRequest[$event->getRequestId()]);
        $reply = $event->getReply();
        $batch = $reply->cursor->firstBatch ?? $reply->cursor->nextBatch ?? null;
        if ($coll !== '' && is_array($batch)) {
            $this->docs[$coll] = ($this->docs[$coll] ?? 0) + count($batch);
        }
    }

    public function commandFailed(MongoDB\Driver\Monitoring\CommandFailedEvent $event): void
    {
        unset($this->collByRequest[$event->getRequestId()]);
    }
}

$counter = new MongoDocCounter();
MongoDB\Driver\Monitoring\addSubscriber($counter);

// ---------------------------------------------------------------- old blocks
$old = [
    'summary' => static function (): array {
        $pdo = Db::pg();
        $count = static fn(string $t, string $w = '1=1'): int => (int)$pdo->query("SELECT COUNT(*) FROM {$t} WHERE {$w}")->fetchColumn();
        return [
            'users' => $count('users'),
            'products' => Product::countFiltered(),
            'orders_total' => $count('orders'),
            'orders_pending' => $count('orders', "status = 'pending'"),
            'orders_done' => $count('orders', "status = 'done'"),
            'contacts_total' => $count('contacts'),
            'contacts_unread' => $count('contacts', "status = 'unread'"),
            'news_total' => $count('news'),
            'comments_total' => $count('comments'),
            'comments_pending' => $count('comments', 'NOT is_approved'),
            'deposit_revenue' => (float)$pdo->query("SELECT COALESCE(SUM(total_amount) FILTER (WHERE status <> 'cancelled'), 0) FROM orders")->fetchColumn(),
        ];
    },
    'monthlyTrend' => static function (): array {
        $months = 6;
        $items = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $current = (new DateTimeImmutable('first day of this month'))->modify("-{$i} month");
            $items[$current->format('Y-m')] = ['label' => 'T' . $current->format('n'), 'orders' => 0, 'revenue' => 0];
        }
        $stmt = Db::pg()->prepare("SELECT to_char(o.created_at, 'YYYY-MM') AS ym, COUNT(*) AS orders,
                   COALESCE(SUM(o.total_amount) FILTER (WHERE o.status <> 'cancelled'), 0) AS revenue
            FROM orders o WHERE o.created_at >= date_trunc('month', now()) - make_interval(months => :months)
            GROUP BY ym ORDER BY ym ASC");
        $stmt->bindValue(':months', $months - 1, PDO::PARAM_INT);
        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if (isset($items[$row['ym']])) {
                $items[$row['ym']]['orders'] = (int)$row['orders'];
                $items[$row['ym']]['revenue'] = (float)$row['revenue'];
            }
        }
        return array_values($items);
    },
    'modelStats' => static function (): array {
        $orderCounts = [];
        foreach (Db::pg()->query('SELECT product_id, COUNT(DISTINCT order_id) AS orders FROM order_items GROUP BY product_id') as $row) {
            $orderCounts[(string)$row['product_id']] = (int)$row['orders'];
        }
        $rows = [];
        foreach (Product::filterAll() as $product) {
            $product['orders'] = $orderCounts[(string)$product['id']] ?? 0;
            $rows[] = $product;
        }
        usort($rows, static fn(array $x, array $y): int => [$y['orders'], $y['created_at']] <=> [$x['orders'], $x['created_at']]);
        return array_map(static fn(array $r): array => ['name' => $r['name'], 'orders' => $r['orders']], array_slice($rows, 0, 6));
    },
];

// ---------------------------------------------------------------- new blocks
$recent = [DashboardService::recentOrders(6), DashboardService::recentContacts(5)];
$new = [
    'summary' => static fn() => DashboardService::summary(),
    'monthlyTrend' => static fn() => DashboardService::monthlyTrend(6),
    'ordersByModel' => static fn() => DashboardService::ordersByModel(6),
    'modelStats' => static fn() => DashboardService::modelStats(6),
    'recentOrders' => static fn() => DashboardService::recentOrders(6),
    'recentContacts' => static fn() => DashboardService::recentContacts(5),
    'activities' => static fn() => DashboardService::activities($recent[0], $recent[1], 8),
    'topSelling' => static fn() => DashboardService::topSelling(10, 30),
    'lowStock' => static fn() => DashboardService::lowStock(5, 10),
    'searchKeywords' => static fn() => DashboardService::searchKeywords(30, 10),
    'topViewedProducts' => static fn() => DashboardService::topViewedProducts(30, 10),
];

Category::allById(); // warm the per-process category cache so it is not charged to the first block

function measure(string $label, callable $fn, int $runs, MongoDocCounter $counter): array
{
    $fn(); // warm-up, not recorded
    $counter->docs = [];
    $result = $fn();
    $docs = $counter->docs;

    $ms = [];
    for ($i = 0; $i < $runs; $i++) {
        $t = hrtime(true);
        $fn();
        $ms[] = (hrtime(true) - $t) / 1e6;
    }
    sort($ms);
    $p = static fn(float $q): float => $ms[min(count($ms) - 1, (int)ceil($q * count($ms)) - 1)];

    printf("%-28s p50 %8.2f ms  p95 %8.2f ms  rows %3d  mongo docs %s\n",
        $label, $p(0.5), $p(0.95), count($result),
        $docs ? implode(', ', array_map(static fn($c, $n) => "{$c}={$n}", array_keys($docs), $docs)) : '0');

    return $result;
}

printf("PG %s / Mongo %s, runs=%d\n\n", Db::env('PG_DB', 'vinfast'), Db::env('MONGO_DB', 'vinfast'), $runs);

$results = [];
foreach ($old as $name => $fn) {
    $results["old.{$name}"] = measure("old  {$name}", $fn, $runs, $counter);
}
echo "\n";
foreach ($new as $name => $fn) {
    $results["new.{$name}"] = measure("new  {$name}", $fn, $runs, $counter);
}

// ---------------------------------------------------------------- correctness
echo "\nCorrectness\n";
$oldSummary = $results['old.summary'];
$newSummary = $results['new.summary'];
printf("  summary identical:      %s\n", $oldSummary == $newSummary ? 'yes' : 'NO ' . json_encode(array_diff_assoc($newSummary, $oldSummary)));
$strip = static fn(array $rows): array => array_map(static fn($r) => [$r['label'], $r['orders'], $r['revenue']], $rows);
printf("  monthlyTrend identical: %s\n", $strip($results['old.monthlyTrend']) == $strip($results['new.monthlyTrend']) ? 'yes' : 'NO');
$oldCounts = array_column($results['old.modelStats'], 'orders');
$newCounts = array_column($results['new.modelStats'], 'orders');
printf("  modelStats order counts old [%s] new [%s] (names may differ only on ties)\n", implode(',', $oldCounts), implode(',', $newCounts));
foreach ($results['old.modelStats'] as $i => $r) {
    printf("    %d. %-40s %4d | %-40s %4d\n", $i + 1, mb_strimwidth($r['name'], 0, 40), $r['orders'],
        mb_strimwidth($results['new.modelStats'][$i]['name'] ?? '-', 0, 40), $results['new.modelStats'][$i]['orders'] ?? 0);
}
