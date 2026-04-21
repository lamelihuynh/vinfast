<?php
$users = (int)($summary['users'] ?? 0);
$products = (int)($summary['products'] ?? 0);
$ordersTotal = (int)($summary['orders_total'] ?? 0);
$unreadContacts = (int)($summary['contacts_unread'] ?? 0);
?>

<div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1">Tổng quan hệ thống</h4>
    </div>
    <div class="d-flex flex-wrap gap-2 mt-3 mt-md-0">
        <span class="badge badge-light text-dark border"><i class="ti-user mr-1"></i> <?= htmlspecialchars((string)$users) ?> users</span>
        <span class="badge badge-light text-dark border"><i class="ti-package mr-1"></i> <?= htmlspecialchars((string)$products) ?> products</span>
        <span class="badge badge-light text-dark border"><i class="ti-receipt mr-1"></i> <?= htmlspecialchars((string)$ordersTotal) ?> orders</span>
        <span class="badge badge-light text-dark border"><i class="ti-email mr-1"></i> <?= htmlspecialchars((string)$unreadContacts) ?> unread contacts</span>
    </div>
</div>