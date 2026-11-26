<?php

/**
 * app/services/DashboardService.php — Data blocks for /admin/dashboard (T3)
 *
 * One public method per dashboard block so each can be timed on its own
 * (bench/t3/dashboard_timing.php) without going through Apache.
 * Every block degrades to an empty value on error: one failing block must not take down the page.
 */
class DashboardService
{
    private const PALETTE = ['#1464F4', '#f59e0b', '#10b981', '#ef4444', '#8b5cf6', '#06b6d4'];

    /**
     * Header and stat-card numbers. All PG counts in one round trip; the product
     * count stays in Mongo, where products live.
     */
    public static function summary(): array
    {
        $out = [
            'users' => 0, 'products' => 0,
            'orders_total' => 0, 'orders_pending' => 0, 'orders_done' => 0,
            'contacts_total' => 0, 'contacts_unread' => 0,
            'news_total' => 0, 'comments_total' => 0, 'comments_pending' => 0,
            'deposit_revenue' => 0.0,
        ];

        try {
            $row = Db::pg()->query(
                "SELECT COUNT(*)                                                           AS orders_total,
                        COUNT(*) FILTER (WHERE status = 'pending')                         AS orders_pending,
                        COUNT(*) FILTER (WHERE status = 'done')                            AS orders_done,
                        COALESCE(SUM(total_amount) FILTER (WHERE status <> 'cancelled'), 0) AS deposit_revenue,
                        (SELECT COUNT(*) FROM users)                                       AS users,
                        (SELECT COUNT(*) FROM contacts)                                    AS contacts_total,
                        (SELECT COUNT(*) FROM contacts WHERE status = 'unread')            AS contacts_unread,
                        (SELECT COUNT(*) FROM news)                                        AS news_total,
                        (SELECT COUNT(*) FROM comments)                                    AS comments_total,
                        (SELECT COUNT(*) FROM comments WHERE NOT is_approved)              AS comments_pending
                 FROM orders"
            )->fetch(PDO::FETCH_ASSOC) ?: [];

            foreach ($row as $key => $value) {
                $out[$key] = $key === 'deposit_revenue' ? (float)$value : (int)$value;
            }
        } catch (Throwable $e) {
            error_log('DashboardService::summary: ' . $e->getMessage());
        }

        try {
            $out['products'] = Product::countFiltered();
        } catch (Throwable $e) {
            error_log('DashboardService::summary products: ' . $e->getMessage());
        }

        return $out;
    }

    /**
     * Orders and revenue for the last $months months, oldest first.
     * generate_series yields every month, so months without orders still come back as 0.
     * @return list<array{label:string, orders:int, revenue:float}>
     */
    public static function monthlyTrend(int $months = 6): array
    {
        $months = max(3, min(12, $months));

        try {
            $stmt = Db::pg()->prepare(
                "WITH months AS (
                    SELECT generate_series(date_trunc('month', now()) - make_interval(months => CAST(:back AS int)),
                                           date_trunc('month', now()), interval '1 month') AS m
                 )
                 SELECT EXTRACT(MONTH FROM months.m)::int AS month_no,
                        COUNT(o.id)                       AS orders,
                        COALESCE(SUM(o.total_amount) FILTER (WHERE o.status <> 'cancelled'), 0) AS revenue
                 FROM months
                 LEFT JOIN orders o ON o.created_at >= months.m AND o.created_at < months.m + interval '1 month'
                 GROUP BY months.m
                 ORDER BY months.m"
            );
            $stmt->bindValue(':back', $months - 1, PDO::PARAM_INT);
            $stmt->execute();

            return array_map(static fn(array $r): array => [
                'label' => 'T' . (int)$r['month_no'],
                'orders' => (int)$r['orders'],
                'revenue' => (float)$r['revenue'],
            ], $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (Throwable $e) {
            error_log('DashboardService::monthlyTrend: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Products ranked by number of distinct orders, named from their newest order snapshot
     * (MIN() would pick the alphabetically first, possibly outdated, name).
     * Two hash GROUP BYs instead of COUNT(DISTINCT), which forces a sort of every item
     * under the en_US collation; the name is then fetched for the top N only.
     * Ties break on product_id, not name, since names are not known before the LIMIT.
     * @return list<array{product_id:string, name:string, orders:int}>
     */
    private static function topOrderedProducts(int $limit): array
    {
        $stmt = Db::pg()->prepare(
            'WITH per_order AS (
                 SELECT product_id, order_id FROM order_items GROUP BY product_id, order_id
             ), top AS (
                 SELECT product_id, COUNT(*) AS orders
                 FROM per_order
                 GROUP BY product_id
                 ORDER BY orders DESC, product_id
                 LIMIT :limit
             )
             SELECT t.product_id, n.name, t.orders
             FROM top t
             CROSS JOIN LATERAL (
                 SELECT oi.product_name_snapshot AS name FROM order_items oi
                 WHERE oi.product_id = t.product_id ORDER BY oi.id DESC LIMIT 1
             ) n
             ORDER BY t.orders DESC, t.product_id'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(static fn(array $r): array => [
            'product_id' => (string)$r['product_id'],
            'name' => (string)$r['name'],
            'orders' => (int)$r['orders'],
        ], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /** @return list<array{product_id:string, name:string, orders:int}> */
    public static function ordersByModel(int $limit = 6): array
    {
        try {
            return self::topOrderedProducts(max(3, $limit));
        } catch (Throwable $e) {
            error_log('DashboardService::ordersByModel: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Top products by orders with price/category/range from Mongo.
     * Reads only the top N from PG and those N documents from Mongo, instead of
     * loading the whole catalog into PHP to sort and keep 6 rows.
     * Like before, hidden products are skipped and the list is topped up with
     * the newest active products (0 orders) when fewer than N have orders.
     * Pass ordersByModel()'s rows as $top to skip re-running the same ranking query.
     */
    public static function modelStats(int $limit = 6, ?array $top = null): array
    {
        $limit = max(3, $limit);

        try {
            $top ??= self::topOrderedProducts($limit);
            $products = Product::findManyByIds(array_column($top, 'product_id'));

            $rows = [];
            // Walk the PG order: the Mongo result is keyed, not ranked.
            foreach ($top as $t) {
                $p = $products[$t['product_id']] ?? null;
                if ($p !== null && !empty($p['is_active'])) {
                    $rows[$t['product_id']] = $p + ['orders' => $t['orders']];
                }
            }

            if (count($rows) < $limit) {
                // $limit docs suffice: at most count($rows) of them are already listed.
                foreach (Product::filterPaginated([], 1, $limit) as $p) {
                    if (count($rows) >= $limit) {
                        break;
                    }
                    $rows[$p['product_id']] ??= $p + ['orders' => 0];
                }
            }

            $items = [];
            foreach (array_values($rows) as $i => $row) {
                $specs = is_array($row['specs'] ?? null) ? $row['specs'] : [];
                $items[] = [
                    'name' => (string)($row['name'] ?? ''),
                    'price' => (float)($row['price'] ?? 0),
                    'category' => (string)($specs['category'] ?? 'EV'),
                    'range' => (string)($specs['range'] ?? 'N/A'),
                    'orders' => (int)$row['orders'],
                    'color' => self::PALETTE[$i % count(self::PALETTE)],
                ];
            }

            return $items;
        } catch (Throwable $e) {
            error_log('DashboardService::modelStats: ' . $e->getMessage());
            return [];
        }
    }

    public static function recentOrders(int $limit = 6): array
    {
        try {
            $stmt = Db::pg()->prepare(
                "SELECT o.id, o.status,
                        to_char(o.created_at, 'YYYY-MM-DD HH24:MI:SS') AS created_at,
                        o.total_amount, p.product_name, u.name AS user_name
                 FROM orders o
                 LEFT JOIN LATERAL (
                     SELECT oi.product_name_snapshot AS product_name
                     FROM order_items oi WHERE oi.order_id = o.id ORDER BY oi.id LIMIT 1
                 ) p ON TRUE
                 JOIN users u ON u.id = o.user_id
                 ORDER BY o.created_at DESC, o.id DESC
                 LIMIT :limit"
            );
            $stmt->bindValue(':limit', max(3, $limit), PDO::PARAM_INT);
            $stmt->execute();

            return array_map(static fn(array $row): array => [
                'id' => (int)$row['id'],
                'code' => 'VF-ORD-' . str_pad((string)$row['id'], 4, '0', STR_PAD_LEFT),
                'customer' => (string)($row['user_name'] ?? 'Khach hang'),
                'product' => (string)($row['product_name'] ?? ''),
                'deposit' => (float)($row['total_amount'] ?? 0),
                'status' => (string)($row['status'] ?? 'pending'),
                'created_at' => (string)($row['created_at'] ?? ''),
            ], $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (Throwable $e) {
            error_log('DashboardService::recentOrders: ' . $e->getMessage());
            return [];
        }
    }

    public static function recentContacts(int $limit = 5): array
    {
        try {
            $stmt = Db::pg()->prepare(
                "SELECT id, name, email, status, message,
                        to_char(created_at, 'YYYY-MM-DD HH24:MI:SS') AS created_at
                 FROM contacts
                 ORDER BY created_at DESC, id DESC
                 LIMIT :limit"
            );
            $stmt->bindValue(':limit', max(3, $limit), PDO::PARAM_INT);
            $stmt->execute();

            return array_map(static fn(array $row): array => [
                'id' => (int)$row['id'],
                'name' => (string)($row['name'] ?? ''),
                'email' => (string)($row['email'] ?? ''),
                'status' => (string)($row['status'] ?? 'unread'),
                'message' => (string)($row['message'] ?? ''),
                'created_at' => (string)($row['created_at'] ?? ''),
            ], $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (Throwable $e) {
            error_log('DashboardService::recentContacts: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Activity feed built from blocks the page already loaded, so it costs no query
     * (the old version re-ran the recent orders and contacts queries).
     */
    public static function activities(array $orders, array $contacts, int $limit = 8): array
    {
        $items = [];
        foreach ($orders as $order) {
            $items[] = [
                'type' => 'order',
                'text' => 'Đơn mới: ' . ($order['customer'] ?? '') . ' - ' . ($order['product'] ?? ''),
                'status' => (string)($order['status'] ?? 'pending'),
                'time' => (string)($order['created_at'] ?? ''),
            ];
        }
        foreach ($contacts as $contact) {
            $items[] = [
                'type' => 'contact',
                'text' => 'Liên hệ: ' . ($contact['name'] ?? '') . ' gửi yêu cầu mới',
                'status' => (string)($contact['status'] ?? 'unread'),
                'time' => (string)($contact['created_at'] ?? ''),
            ];
        }

        usort($items, static fn(array $a, array $b): int => strcmp($b['time'], $a['time']));

        return array_slice($items, 0, max(5, $limit));
    }

    /**
     * Best sellers by quantity over the last $days days, cancelled orders excluded.
     * @return list<array{product_id:string, name:string, qty:int, revenue:float}>
     */
    public static function topSelling(int $limit = 10, int $days = 30): array
    {
        try {
            $stmt = Db::pg()->prepare(
                "SELECT oi.product_id,
                        (ARRAY_AGG(oi.product_name_snapshot ORDER BY oi.id DESC))[1] AS name,
                        SUM(oi.quantity) AS qty,
                        SUM(oi.subtotal) AS revenue
                 FROM order_items oi
                 JOIN orders o ON o.id = oi.order_id
                 WHERE o.status <> 'cancelled'
                   AND o.created_at >= now() - make_interval(days => CAST(:days AS int))
                 GROUP BY oi.product_id
                 ORDER BY qty DESC, oi.product_id
                 LIMIT :limit"
            );
            $stmt->bindValue(':days', max(1, $days), PDO::PARAM_INT);
            $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
            $stmt->execute();

            return array_map(static fn(array $r): array => [
                'product_id' => (string)$r['product_id'],
                'name' => (string)$r['name'],
                'qty' => (int)$r['qty'],
                'revenue' => (float)$r['revenue'],
            ], $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (Throwable $e) {
            error_log('DashboardService::topSelling: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Variants whose sellable stock (stock - reserved) is at or below $threshold,
     * named from Mongo in one $in.
     * TODO(T5): move the query into InventoryService::lowStock() once T5 agrees (inventory is T5's file).
     * @return list<array{product_id:string, variant_id:string, product_name:string, variant_name:string, stock:int, reserved:int, available:int}>
     */
    public static function lowStock(int $threshold = 5, int $limit = 10): array
    {
        try {
            $stmt = Db::pg()->prepare(
                'SELECT product_id, variant_id, stock, reserved, stock - reserved AS available
                 FROM inventory
                 WHERE stock - reserved <= :threshold
                 ORDER BY available, product_id, variant_id
                 LIMIT :limit'
            );
            $stmt->bindValue(':threshold', $threshold, PDO::PARAM_INT);
            $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $products = Product::findManyByIds(array_column($rows, 'product_id'));

            return array_map(static function (array $r) use ($products): array {
                $p = $products[(string)$r['product_id']] ?? null;
                $variantName = '';
                foreach ((array)($p['variants'] ?? []) as $v) {
                    if ((string)($v['variantId'] ?? '') === (string)$r['variant_id']) {
                        $variantName = (string)($v['name'] ?? '');
                        break;
                    }
                }

                return [
                    'product_id' => (string)$r['product_id'],
                    'variant_id' => (string)$r['variant_id'],
                    'product_name' => (string)($p['name'] ?? ('#' . $r['product_id'])),
                    'variant_name' => $variantName !== '' ? $variantName : (string)$r['variant_id'],
                    'stock' => (int)$r['stock'],
                    'reserved' => (int)$r['reserved'],
                    'available' => (int)$r['available'],
                ];
            }, $rows);
        } catch (Throwable $e) {
            error_log('DashboardService::lowStock: ' . $e->getMessage());
            return [];
        }
    }

    public static function searchKeywords(int $days = 30, int $limit = 10): array
    {
        try {
            return ActivityLog::topKeywords($days, $limit);
        } catch (Throwable $e) {
            error_log('DashboardService::searchKeywords: ' . $e->getMessage());
            return [];
        }
    }

    public static function topViewedProducts(int $days = 30, int $limit = 10): array
    {
        try {
            return ActivityLog::topViewedProducts($days, $limit);
        } catch (Throwable $e) {
            error_log('DashboardService::topViewedProducts: ' . $e->getMessage());
            return [];
        }
    }
}
