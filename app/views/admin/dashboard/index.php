<?php

/**
 * app/views/admin/dashboard/index.php
 * Owner  : All members (common)
 * Title  : Dashboard
 *
 * Purpose: Srtdash dashboard overview with statistics, charts and recent activities.
 */

$dashboard = is_array($dashboard ?? null) ? $dashboard : [];
$summary = is_array($dashboard['summary'] ?? null) ? $dashboard['summary'] : [];
$trend = is_array($dashboard['trend'] ?? null) ? $dashboard['trend'] : [];
$ordersByModel = is_array($dashboard['ordersByModel'] ?? null) ? $dashboard['ordersByModel'] : [];
$modelStats = is_array($dashboard['modelStats'] ?? null) ? $dashboard['modelStats'] : [];
$recentOrders = is_array($dashboard['recentOrders'] ?? null) ? $dashboard['recentOrders'] : [];
$recentContacts = is_array($dashboard['recentContacts'] ?? null) ? $dashboard['recentContacts'] : [];
$activities = is_array($dashboard['activities'] ?? null) ? $dashboard['activities'] : [];
$quickActions = is_array($dashboard['quickActions'] ?? null) ? $dashboard['quickActions'] : [];
$mockFlags = is_array($dashboard['mock'] ?? null) ? $dashboard['mock'] : ['contacts' => false, 'activities' => false];

$maxModelOrders = 1;
foreach ($ordersByModel as $m) {
    $maxModelOrders = max($maxModelOrders, (int)($m['orders'] ?? 0));
}

$trendMaxOrders = 1;
foreach ($trend as $month) {
    $trendMaxOrders = max($trendMaxOrders, (int)($month['orders'] ?? 0));
}
?>

<div>
    <?php include ROOT . '/app/views/admin/dashboard/partials/header.php'; ?>

    <div class="row">
        <div class="col-12">
            <?php include ROOT . '/app/views/admin/dashboard/partials/stat-cards.php'; ?>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-xl-8 col-12 mb-4 mb-xl-0">
            <?php include ROOT . '/app/views/admin/dashboard/partials/trend-chart.php'; ?>
        </div>
        <div class="col-xl-4 col-12">
            <?php include ROOT . '/app/views/admin/dashboard/partials/model-bars.php'; ?>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-xl-8 col-12 mb-4 mb-xl-0">
            <?php include ROOT . '/app/views/admin/dashboard/partials/recent-orders.php'; ?>
        </div>
        <div class="col-xl-4 col-12">
            <?php include ROOT . '/app/views/admin/dashboard/partials/activities.php'; ?>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-xl-7 col-12 mb-4 mb-xl-0">
            <?php include ROOT . '/app/views/admin/dashboard/partials/model-stats.php'; ?>
        </div>
    </div>
</div>