<?php

/**
 * app/controllers/admin/DashboardAdminController.php
 * Owner: All members (common)
 * Route: /admin/dashboard
 *
 * Renders the Srtdash admin dashboard with site-wide stats:
 * total users, products, unread contacts, and pending orders.
 * No auth check needed here — admin.php gate blocks non-admins globally.
 */
class DashboardAdminController
{
    public function index(): void
    {
        SEO::set('Admin Dashboard');

        $dashboard = [
            'summary' => $this->buildSummary(),
            'trend' => $this->buildMonthlyTrend(6),
            'ordersByModel' => $this->buildOrdersByModel(6),
            'recentOrders' => $this->buildRecentOrders(6),
            'recentContacts' => $this->buildRecentContacts(5),
            'activities' => $this->buildActivities(8),
            'modelStats' => $this->buildModelStats(6),
            'quickActions' => $this->buildQuickActions(),
            'mock' => [
                'contacts' => false,
                'activities' => false,
            ],
        ];

        if (empty($dashboard['recentContacts'])) {
            $dashboard['recentContacts'] = $this->mockRecentContacts();
            $dashboard['mock']['contacts'] = true;
        }

        if (empty($dashboard['activities'])) {
            $dashboard['activities'] = $this->mockActivities();
            $dashboard['mock']['activities'] = true;
        }

        View::render('admin/dashboard/index', [
            'dashboard' => $dashboard,
        ], 'admin');
    }

    private function buildSummary(): array
    {
        return [
            'users' => $this->countRows('users'),
            'products' => $this->countActiveProducts(),
            'orders_total' => $this->countRows('orders'),
            'orders_pending' => $this->countRows('orders', "status = 'pending'"),
            'orders_done' => $this->countRows('orders', "status = 'done'"),
            'contacts_total' => $this->countRows('contacts'),
            'contacts_unread' => $this->countRows('contacts', "status = 'unread'"),
            'news_total' => $this->countRows('news'),
            'comments_total' => $this->countRows('comments'),
            'comments_pending' => $this->countRows('comments', 'NOT is_approved'),
            'deposit_revenue' => $this->sumDepositRevenue(),
        ];
    }

    private function buildMonthlyTrend(int $months = 6): array
    {
        global $pdo;

        $months = max(3, min(12, $months));
        $items = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $dt = new DateTimeImmutable('first day of this month');
            $current = $dt->modify("-{$i} month");
            $key = $current->format('Y-m');
            $items[$key] = [
                'label' => 'T' . $current->format('n'),
                'orders' => 0,
                'revenue' => 0,
            ];
        }

        try {
            // Doanh thu = total_amount của đơn chưa hủy (đơn cọc: tiền cọc; đơn mua: tổng tiền)
            $sql = "SELECT to_char(o.created_at, 'YYYY-MM') AS ym,
                           COUNT(*) AS orders,
                           COALESCE(SUM(o.total_amount) FILTER (WHERE o.status <> 'cancelled'), 0) AS revenue
                    FROM orders o
                    WHERE o.created_at >= date_trunc('month', now()) - make_interval(months => :months)
                    GROUP BY ym
                    ORDER BY ym ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':months', $months - 1, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                $key = (string)($row['ym'] ?? '');
                if (!isset($items[$key])) {
                    continue;
                }

                $items[$key]['orders'] = (int)($row['orders'] ?? 0);
                $items[$key]['revenue'] = (float)($row['revenue'] ?? 0);
            }
        } catch (Throwable $e) {
            // Keep zero values so chart still renders.
        }

        return array_values($items);
    }

    private function buildOrdersByModel(int $limit = 6): array
    {
        global $pdo;

        try {
            $sql = 'SELECT MIN(oi.product_name_snapshot) AS name, COUNT(DISTINCT oi.order_id) AS total
                    FROM order_items oi
                    GROUP BY oi.product_id
                    ORDER BY total DESC, name ASC
                    LIMIT :limit';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':limit', max(3, $limit), PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(static function (array $row): array {
                return [
                    'name' => (string)($row['name'] ?? ''),
                    'orders' => (int)($row['total'] ?? 0),
                ];
            }, $rows);
        } catch (Throwable $e) {
            return [];
        }
    }

    private function buildModelStats(int $limit = 6): array
    {
        global $pdo;

        $palette = ['#1464F4', '#f59e0b', '#10b981', '#ef4444', '#8b5cf6', '#06b6d4'];

        try {
            // Sản phẩm ở MongoDB, số đơn ở PostgreSQL -> ghép ở backend
            $orderCounts = [];
            $stmt = $pdo->query('SELECT product_id, COUNT(DISTINCT order_id) AS orders FROM order_items GROUP BY product_id');
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $orderCounts[(string)$row['product_id']] = (int)$row['orders'];
            }

            $rows = [];
            foreach (Product::filterAll() as $product) {
                $product['orders'] = $orderCounts[(string)$product['id']] ?? 0;
                $rows[] = $product;
            }
            usort($rows, static fn(array $x, array $y): int => [$y['orders'], $y['created_at']] <=> [$x['orders'], $x['created_at']]);
            $rows = array_slice($rows, 0, max(3, $limit));

            $items = [];
            foreach ($rows as $i => $row) {
                $specs = is_array($row['specs'] ?? null) ? $row['specs'] : [];

                $items[] = [
                    'name' => (string)($row['name'] ?? ''),
                    'price' => (float)($row['price'] ?? 0),
                    'category' => (string)($specs['category'] ?? 'EV'),
                    'range' => (string)($specs['range'] ?? 'N/A'),
                    'orders' => (int)($row['orders'] ?? 0),
                    'color' => $palette[$i % count($palette)],
                ];
            }

            return $items;
        } catch (Throwable $e) {
            return [];
        }
    }

    private function buildRecentOrders(int $limit = 6): array
    {
        global $pdo;

        try {
            $sql = "SELECT o.id,
                           o.status,
                           o.type,
                           to_char(o.created_at, 'YYYY-MM-DD HH24:MI:SS') AS created_at,
                           o.note::text AS note,
                           o.total_amount,
                           p.product_name,
                           u.name AS user_name
                    FROM orders o
                    LEFT JOIN LATERAL (
                        SELECT oi.product_name_snapshot AS product_name
                        FROM order_items oi WHERE oi.order_id = o.id ORDER BY oi.id LIMIT 1
                    ) p ON TRUE
                    JOIN users u ON u.id = o.user_id
                    ORDER BY o.created_at DESC
                    LIMIT :limit";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':limit', max(3, $limit), PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(function (array $row): array {
                $orderId = (int)($row['id'] ?? 0);
                return [
                    'id' => $orderId,
                    'code' => 'VF-ORD-' . str_pad((string)$orderId, 4, '0', STR_PAD_LEFT),
                    'customer' => (string)($row['user_name'] ?? 'Khach hang'),
                    'product' => (string)($row['product_name'] ?? ''),
                    'deposit' => (float)($row['total_amount'] ?? 0) ?: $this->extractDepositFromNote($row['note'] ?? null),
                    'status' => (string)($row['status'] ?? 'pending'),
                    'created_at' => (string)($row['created_at'] ?? ''),
                ];
            }, $rows);
        } catch (Throwable $e) {
            return [];
        }
    }

    private function buildRecentContacts(int $limit = 5): array
    {
        global $pdo;

        try {
            $sql = "SELECT id, name, email, status, to_char(created_at, 'YYYY-MM-DD HH24:MI:SS') AS created_at, message
					FROM contacts
					ORDER BY contacts.created_at DESC
					LIMIT :limit";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':limit', max(3, $limit), PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(static function (array $row): array {
                return [
                    'id' => (int)($row['id'] ?? 0),
                    'name' => (string)($row['name'] ?? ''),
                    'email' => (string)($row['email'] ?? ''),
                    'status' => (string)($row['status'] ?? 'unread'),
                    'message' => (string)($row['message'] ?? ''),
                    'created_at' => (string)($row['created_at'] ?? ''),
                ];
            }, $rows);
        } catch (Throwable $e) {
            return [];
        }
    }

    private function buildActivities(int $limit = 8): array
    {
        $items = [];

        foreach ($this->buildRecentOrders($limit) as $order) {
            $items[] = [
                'type' => 'order',
                'text' => 'Đơn mới: ' . ($order['customer'] ?? '') . ' - ' . ($order['product'] ?? ''),
                'status' => (string)($order['status'] ?? 'pending'),
                'time' => (string)($order['created_at'] ?? ''),
            ];
        }

        foreach ($this->buildRecentContacts($limit) as $contact) {
            $items[] = [
                'type' => 'contact',
                'text' => 'Liên hệ: ' . ($contact['name'] ?? '') . ' gửi yêu cầu mới',
                'status' => (string)($contact['status'] ?? 'unread'),
                'time' => (string)($contact['created_at'] ?? ''),
            ];
        }

        usort($items, static function (array $a, array $b): int {
            return strtotime((string)($b['time'] ?? '')) <=> strtotime((string)($a['time'] ?? ''));
        });

        return array_slice($items, 0, max(5, $limit));
    }

    private function buildQuickActions(): array
    {
        return [
            ['label' => 'Quản lý người dùng', 'url' => ADMIN_URL . 'users', 'icon' => 'ti-user'],
            ['label' => 'Quản lý sản phẩm', 'url' => ADMIN_URL . 'products', 'icon' => 'ti-package'],
            ['label' => 'Quản lý đơn hàng', 'url' => ADMIN_URL . 'orders', 'icon' => 'ti-receipt'],
            ['label' => 'Liên hệ khách hàng', 'url' => ADMIN_URL . 'contacts', 'icon' => 'ti-email'],
            ['label' => 'Tin tức', 'url' => ADMIN_URL . 'news', 'icon' => 'ti-write'],
            ['label' => 'Bình luận', 'url' => ADMIN_URL . 'comments', 'icon' => 'ti-comment'],
        ];
    }

    private function countRows(string $table, string $where = '1=1'): int
    {
        global $pdo;

        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM {$table} WHERE {$where}");
            return (int)$stmt->fetchColumn();
        } catch (Throwable $e) {
            return 0;
        }
    }

    private function countActiveProducts(): int
    {
        try {
            return Product::countFiltered();
        } catch (Throwable $e) {
            return 0;
        }
    }

    private function sumDepositRevenue(): float
    {
        global $pdo;

        try {
            $sql = "SELECT COALESCE(SUM(total_amount) FILTER (WHERE status <> 'cancelled'), 0) FROM orders";
            $stmt = $pdo->query($sql);
            return (float)$stmt->fetchColumn();
        } catch (Throwable $e) {
            return 0;
        }
    }

    private function extractDepositFromNote($note): float
    {
        $raw = trim((string)$note);
        if ($raw === '') {
            return 0;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return 0;
        }

        if (isset($decoded['deposit_base_amount'])) {
            return max(0, (float)$decoded['deposit_base_amount']);
        }

        return max(0, (float)($decoded['deposit_amount'] ?? 0));
    }

    private function mockRecentContacts(): array
    {
        return [
            ['id' => 1, 'name' => 'Nguyen Van A', 'email' => 'a@example.com', 'status' => 'unread', 'message' => 'Xin tu van VF 8', 'created_at' => date('Y-m-d H:i:s', strtotime('-20 minutes'))],
            ['id' => 2, 'name' => 'Tran Thi B', 'email' => 'b@example.com', 'status' => 'read', 'message' => 'Dat lich lai thu VF 7', 'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour'))],
            ['id' => 3, 'name' => 'Le Van C', 'email' => 'c@example.com', 'status' => 'replied', 'message' => 'Hoi ve uu dai mua xe', 'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours'))],
        ];
    }

    private function mockActivities(): array
    {
        return [
            ['type' => 'order', 'text' => 'Đơn hàng mới cho dòng xe VF 8', 'status' => 'pending', 'time' => date('Y-m-d H:i:s', strtotime('-10 minutes'))],
            ['type' => 'contact', 'text' => 'Liên hệ mới từ khách hàng tiềm năng', 'status' => 'unread', 'time' => date('Y-m-d H:i:s', strtotime('-35 minutes'))],
            ['type' => 'order', 'text' => 'Đơn hàng đã xác nhận thành công', 'status' => 'confirmed', 'time' => date('Y-m-d H:i:s', strtotime('-70 minutes'))],
            ['type' => 'comment', 'text' => 'Bình luận mới cần duyệt', 'status' => 'pending', 'time' => date('Y-m-d H:i:s', strtotime('-2 hours'))],
        ];
    }
}
