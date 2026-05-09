<?php

/**
 * app/views/admin/orders/partials/summary.php
 * Expects: $summary
 */
?>
<div class="row g-3 mb-4">
    <div class="col-6 col-xl">
        <div class="card border-0 text-white h-100" style="background:linear-gradient(135deg,#1464f4,#3b7cf8)">
            <div class="card-body py-3">
                <div class="small opacity-75">Tổng đơn hàng</div>
                <div class="h4 mb-0"><?= (int)($summary['all'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl">
        <div class="card border-0 text-white h-100" style="background:linear-gradient(135deg,#f59e0b,#d97706)">
            <div class="card-body py-3">
                <div class="small opacity-75">Chờ xử lý</div>
                <div class="h4 mb-0"><?= (int)($summary['pending'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl">
        <div class="card border-0 text-white h-100" style="background:linear-gradient(135deg,#0ea5e9,#0284c7)">
            <div class="card-body py-3">
                <div class="small opacity-75">Đã xác nhận</div>
                <div class="h4 mb-0"><?= (int)($summary['confirmed'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl">
        <div class="card border-0 text-white h-100" style="background:linear-gradient(135deg,#10b981,#059669)">
            <div class="card-body py-3">
                <div class="small opacity-75">Hoàn tất</div>
                <div class="h4 mb-0"><?= (int)($summary['done'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl">
        <div class="card border-0 text-white h-100" style="background:linear-gradient(135deg,#6b7280,#4b5563)">
            <div class="card-body py-3">
                <div class="small opacity-75">Đã hủy</div>
                <div class="h4 mb-0"><?= (int)($summary['cancelled'] ?? 0) ?></div>
            </div>
        </div>
    </div>
</div>