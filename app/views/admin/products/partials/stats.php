<?php
$summary = isset($summary) && is_array($summary) ? $summary : [];
?>
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card border-0 text-white" style="background:linear-gradient(135deg,#1464f4,#3b7cf8)">
            <div class="card-body py-3">
                <div class="small opacity-75">Tổng sản phẩm</div>
                <div class="h4 mb-0"><?= (int)($summary['total'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 text-white" style="background:linear-gradient(135deg,#10b981,#059669)">
            <div class="card-body py-3">
                <div class="small opacity-75">Hiển thị</div>
                <div class="h4 mb-0"><?= (int)($summary['active'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 text-white" style="background:linear-gradient(135deg,#6b7280,#4b5563)">
            <div class="card-body py-3">
                <div class="small opacity-75">Đã ẩn</div>
                <div class="h4 mb-0"><?= (int)($summary['inactive'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 text-white" style="background:linear-gradient(135deg,#f59e0b,#d97706)">
            <div class="card-body py-3">
                <div class="small opacity-75">Danh mục</div>
                <div class="h4 mb-0"><?= (int)($summary['categories'] ?? 0) ?></div>
            </div>
        </div>
    </div>
</div>