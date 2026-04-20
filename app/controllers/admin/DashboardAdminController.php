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
            'products' => $this->countRows('products', 'is_active = 1'),
            'orders_total' => $this->countRows('orders'),
            'orders_pending' => $this->countRows('orders', "status = 'pending'"),
            'orders_done' => $this->countRows('orders', "status = 'done'"),
            'contacts_total' => $this->countRows('contacts'),
            'contacts_unread' => $this->countRows('contacts', "status = 'unread'"),
            'news_total' => $this->countRows('news'),
            'comments_total' => $this->countRows('comments'),
            'comments_pending' => $this->countRows('comments', 'is_approved = 0'),
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
            $sql = "SELECT DATE_FORMAT(o.created_at, '%Y-%m') AS ym,
						   COUNT(*) AS orders,
						   COALESCE(SUM(CASE WHEN o.status <> 'cancelled' THEN p.price * 0.10 ELSE 0 END), 0) AS revenue
					FROM orders o
					JOIN products p ON p.id = o.product_id
					WHERE o.created_at >= DATE_SUB(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL :months MONTH)
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
            $sql = 'SELECT p.name, COUNT(*) AS total
					FROM orders o
					JOIN products p ON p.id = o.product_id
					GROUP BY o.product_id, p.name
					ORDER BY total DESC, p.name ASC
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
            $sql = 'SELECT p.id,
						   p.name,
						   p.price,
						   p.specs,
						   COUNT(o.id) AS orders
					FROM products p
					LEFT JOIN orders o ON o.product_id = p.id
					WHERE p.is_active = 1
					GROUP BY p.id, p.name, p.price, p.specs
					ORDER BY orders DESC, p.created_at DESC
					LIMIT :limit';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':limit', max(3, $limit), PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $items = [];
            foreach ($rows as $i => $row) {
                $specs = json_decode((string)($row['specs'] ?? ''), true);
                if (!is_array($specs)) {
                    $specs = [];
                }

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
            $sql = 'SELECT o.id,
						   o.status,
						   o.type,
						   o.created_at,
						   o.note,
						   p.name AS product_name,
						   p.price,
						   u.name AS user_name
					FROM orders o
					JOIN products p ON p.id = o.product_id
					JOIN users u ON u.id = o.user_id
					ORDER BY o.created_at DESC
					LIMIT :limit';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':limit', max(3, $limit), PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(static function (array $row): array {
                $orderId = (int)($row['id'] ?? 0);
                return [
                    'id' => $orderId,
                    'code' => 'VF-ORD-' . str_pad((string)$orderId, 4, '0', STR_PAD_LEFT),
                    'customer' => (string)($row['user_name'] ?? 'Khach hang'),
                    'product' => (string)($row['product_name'] ?? ''),
                    'deposit' => (float)($row['price'] ?? 0) * 0.10,
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
            $sql = 'SELECT id, name, email, status, created_at, message
					FROM contacts
					ORDER BY created_at DESC
					LIMIT :limit';
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

    private function sumDepositRevenue(): float
    {
        global $pdo;

        try {
            $sql = "SELECT COALESCE(SUM(CASE WHEN o.status <> 'cancelled' THEN p.price * 0.10 ELSE 0 END), 0) AS revenue
					FROM orders o
					JOIN products p ON p.id = o.product_id";
            $stmt = $pdo->query($sql);
            return (float)$stmt->fetchColumn();
        } catch (Throwable $e) {
            return 0;
        }
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
