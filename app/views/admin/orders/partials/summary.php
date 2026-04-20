<?php

/**
 * app/views/admin/orders/partials/summary.php
 * Expects: $summary
 */
?>
<div class="row g-2 mt-2">
    <div class="col-6 col-lg-2">
        <div class="border rounded p-2 h-100">
            <div class="small text-muted">Tất cả</div>
            <div class="h6 mb-0"><?= (int)($summary['all'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="border rounded p-2 h-100">
            <div class="small text-muted">Chờ xử lý</div>
            <div class="h6 mb-0"><?= (int)($summary['pending'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="border rounded p-2 h-100">
            <div class="small text-muted">Đã xác nhận</div>
            <div class="h6 mb-0"><?= (int)($summary['confirmed'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="border rounded p-2 h-100">
            <div class="small text-muted">Hoàn tất</div>
            <div class="h6 mb-0"><?= (int)($summary['done'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="border rounded p-2 h-100">
            <div class="small text-muted">Đã hủy</div>
            <div class="h6 mb-0"><?= (int)($summary['cancelled'] ?? 0) ?></div>
        </div>
    </div>
</div>