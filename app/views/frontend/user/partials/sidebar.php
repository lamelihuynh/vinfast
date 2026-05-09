<?php
$active = $active ?? '';
?>
<nav class="space-y-3">
    <div class="px-1 text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Tài khoản</div>

    <a href="<?= BASE_URL ?>user/profile" class="group flex items-center justify-between rounded-xl border px-3 py-2.5 transition <?= $active === 'profile' ? 'border-blue-200 bg-blue-50 text-blue-700' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' ?>">
        <span class="flex items-center gap-3">
            <i class="fa-solid fa-user text-sm"></i>
            <span class="text-sm font-semibold">Thông tin cá nhân</span>
        </span>
        <i class="fa-solid fa-chevron-right text-[11px] <?= $active === 'profile' ? 'text-blue-500' : 'text-slate-400 group-hover:text-slate-500' ?>"></i>
    </a>

    <a href="<?= BASE_URL ?>user/orders" class="group flex items-center justify-between rounded-xl border px-3 py-2.5 transition <?= $active === 'orders' ? 'border-blue-200 bg-blue-50 text-blue-700' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' ?>">
        <span class="flex items-center gap-3">
            <i class="fa-solid fa-box text-sm"></i>
            <span class="text-sm font-semibold">Lịch sử đơn hàng</span>
        </span>
        <i class="fa-solid fa-chevron-right text-[11px] <?= $active === 'orders' ? 'text-blue-500' : 'text-slate-400 group-hover:text-slate-500' ?>"></i>
    </a>

    <div class="pt-2">
        <a href="<?= BASE_URL ?>auth/logout" class="flex items-center gap-3 rounded-xl border border-red-100 bg-red-50 px-3 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-100">
            <i class="fa-solid fa-right-from-bracket text-sm"></i>
            <span>Đăng xuất</span>
        </a>
    </div>
</nav>