<div class="bg-white/95 backdrop-blur-md border-b border-slate-100">
  <div class="w-full px-4 lg:px-6 xl:px-8 py-3 flex items-center gap-4">
    <a href="<?= BASE_URL ?>" class="flex items-center flex-shrink-0">
      <img src="<?= BASE_URL ?>public/images/logo/vinfast-logo.png" alt="VinFast" class="h-9 w-auto">
    </a>

    <form action="<?= BASE_URL ?>products" method="get" class="hidden md:flex flex-1 max-w-xl mx-auto border border-slate-200 rounded-md overflow-hidden focus-within:ring-2 focus-within:ring-vfNavy/20 focus-within:border-vfNavy transition">
      <input type="text" name="q" placeholder="Tìm kiếm xe, dịch vụ..." class="w-full px-4 py-2 text-[13px] text-slate-700 outline-none">
      <button type="submit" class="px-4 bg-[#0B233F] text-white hover:bg-[#0a1f35] transition flex items-center justify-center"><i class="fa-solid fa-magnifying-glass text-[14px]"></i></button>
    </form>

     <div class="ml-auto flex items-center gap-3">

      <?php if ($isLoggedIn): ?>
        <div class="relative" id="vfUserMenu">
          <button type="button" id="vfUserTrigger" aria-expanded="false" class="inline-flex items-center gap-2 text-slate-700 hover:text-vfNavy text-sm">
            <span class="w-8 h-8 rounded-full bg-gradient-to-br from-vfNavy to-blue-500 text-white text-[11px] font-bold inline-flex items-center justify-center"><?= htmlspecialchars($initials !== '' ? $initials : 'U') ?></span>
            <span class="hidden md:inline"><?= htmlspecialchars(Auth::name()) ?></span>
            <i class="fa-solid fa-chevron-down text-[11px]"></i>
          </button>
          <div id="vfUserDropdown" class="hidden absolute right-0 mt-2 w-56 rounded-xl border border-slate-200 bg-white shadow-xl p-1">
            <div class="flex items-center gap-2 px-2 py-2 border-b border-slate-100 mb-1">
              <span class="w-8 h-8 rounded-full bg-gradient-to-br from-vfNavy to-blue-500 text-white text-[11px] font-bold inline-flex items-center justify-center"><?= htmlspecialchars($initials !== '' ? $initials : 'U') ?></span>
              <div class="min-w-0">
                <p class="text-[13px] text-slate-900 font-semibold truncate"><?= htmlspecialchars(Auth::name()) ?></p>
                <p class="text-[11px] text-slate-400">Thanh vien VinFast</p>
              </div>
            </div>
            <a class="flex items-center gap-2 px-2 py-2 rounded-lg text-[13px] text-slate-700 hover:bg-slate-50" href="<?= BASE_URL ?>user/profile"><i class="fa-regular fa-user w-4"></i>Thông tin tài khoản</a>
            <a class="flex items-center gap-2 px-2 py-2 rounded-lg text-[13px] text-slate-700 hover:bg-slate-50" href="<?= BASE_URL ?>user/orders"><i class="fa-regular fa-clock w-4"></i>Lịch sử đơn hàng</a>
            <a class="flex items-center gap-2 px-2 py-2 rounded-lg text-[13px] text-slate-700 hover:bg-slate-50" href="<?= BASE_URL ?>contact"><i class="fa-solid fa-screwdriver-wrench w-4"></i>Bảo dưỡng - Sửa chữa</a>
            <a class="flex items-center gap-2 px-2 py-2 rounded-lg text-[13px] text-slate-700 hover:bg-slate-50" href="<?= BASE_URL ?>products"><i class="fa-solid fa-car w-4"></i>Xe của tôi</a>
            <?php if (Auth::isAdmin()): ?>
              <a class="flex items-center gap-2 px-2 py-2 rounded-lg text-[13px] text-slate-700 hover:bg-slate-50" href="<?= ADMIN_URL ?>dashboard"><i class="fa-solid fa-shield-halved w-4"></i>Quản trị viên</a>
            <?php endif; ?>
            <a class="flex items-center gap-2 px-2 py-2 rounded-lg text-[13px] text-red-500 hover:bg-red-50" href="<?= BASE_URL ?>auth/logout"><i class="fa-solid fa-right-from-bracket w-4"></i>Đăng xuất</a>
          </div>
        </div>

      <?php else: ?>
        <a href="<?= BASE_URL ?>auth/login" class="hidden md:inline-flex px-3 py-1.5 rounded-md border border-vfNavy text-vfNavy text-[12px] font-semibold hover:bg-slate-50">Đăng nhập</a>
        <a href="<?= BASE_URL ?>auth/register" class="hidden md:inline-flex px-3 py-1.5 rounded-md bg-[#FFB81C] text-vfNavy text-[12px] font-bold border-2 border-[#FFB81C] hover:bg-vfNavy hover:text-[#FFB81C] transition-all duration-200">Đăng ký</a>
      <?php endif; ?>

      <button id="vfMobileToggle" type="button" class="md:hidden text-slate-700 text-xl">
        <i class="fa-solid fa-bars"></i>
      </button>
    </div>
  </div>  
</div>
