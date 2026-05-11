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
                <div class="small opacity-75">Chưa thanh toán</div>
                <div class="h4 mb-0"><?= (int)($summary['unpaid'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl">
        <div class="card border-0 text-white h-100" style="background:linear-gradient(135deg,#0ea5e9,#0284c7)">
            <div class="card-body py-3">
                <div class="small opacity-75">Chờ xác nhận thanh toán</div>
                <div class="h4 mb-0"><?= (int)($summary['pending_verify'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl">
        <div class="card border-0 text-white h-100" style="background:linear-gradient(135deg,#10b981,#059669)">
            <div class="card-body py-3">
                <div class="small opacity-75">Đã nhận cọc</div>
                <div class="h4 mb-0"><?= (int)($summary['paid'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl">
        <div class="card border-0 text-white h-100" style="background:linear-gradient(135deg,#6b7280,#4b5563)">
            <div class="card-body py-3">
                <div class="small opacity-75">Thanh toán thất bại</div>
                <div class="h4 mb-0"><?= (int)($summary['failed'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl">
        <div class="card border-0 text-white h-100" style="background:linear-gradient(135deg,#7c3aed,#6d28d9)">
            <div class="card-body py-3">
                <div class="small opacity-75">Đã hoàn tiền</div>
                <div class="h4 mb-0"><?= (int)($summary['refunded'] ?? 0) ?></div>
            </div>
        </div>
    </div>
</div>