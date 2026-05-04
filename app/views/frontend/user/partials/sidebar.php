<?php
$active = $active ?? '';
?>
<nav class="space-y-2">
    <a href="<?= BASE_URL ?>user/profile" class="flex items-center gap-3 px-3 py-2 rounded-lg <?= $active === 'profile' ? 'bg-slate-100 font-semibold' : 'text-slate-700' ?>">
        <i class="fa-solid fa-user text-sm"></i>
        <span>Thông tin</span>
    </a>
    <a href="<?= BASE_URL ?>user/orders" class="flex items-center gap-3 px-3 py-2 rounded-lg <?= $active === 'orders' ? 'bg-slate-100 font-semibold' : 'text-slate-700' ?>">
        <i class="fa-solid fa-box text-sm"></i>
        <span>Đơn hàng</span>
    </a>
    <a href="<?= BASE_URL ?>auth/logout" class="flex items-center gap-3 px-3 py-2 rounded-lg text-red-600">
        <i class="fa-solid fa-right-from-bracket text-sm"></i>
        <span>Đăng xuất</span>
    </a>
</nav>