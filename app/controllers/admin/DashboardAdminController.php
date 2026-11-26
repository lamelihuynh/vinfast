<?php

/**
 * app/controllers/admin/DashboardAdminController.php
 * Owner: All members (common)
 * Route: /admin/dashboard
 *
 * Renders the Srtdash admin dashboard with site-wide stats:
 * total users, products, unread contacts, and pending orders.
 * All queries live in DashboardService; this class only assembles and renders.
 * No auth check needed here — admin.php gate blocks non-admins globally.
 */
class DashboardAdminController
{
    public function index(): void
    {
        SEO::set('Admin Dashboard');

        $recentOrders = DashboardService::recentOrders(6);
        $recentContacts = DashboardService::recentContacts(5);
        $ordersByModel = DashboardService::ordersByModel(6);

        $dashboard = [
            'summary' => DashboardService::summary(),
            'trend' => DashboardService::monthlyTrend(6),
            'ordersByModel' => $ordersByModel,
            'recentOrders' => $recentOrders,
            'recentContacts' => $recentContacts,
            'activities' => DashboardService::activities($recentOrders, $recentContacts, 8),
            'modelStats' => DashboardService::modelStats(6, $ordersByModel),
            'topSelling' => DashboardService::topSelling(10, 30),
            'lowStock' => DashboardService::lowStock(5, 10),
            'searchKeywords' => DashboardService::searchKeywords(30, 10),
            'topViewed' => DashboardService::topViewedProducts(30, 10),
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
