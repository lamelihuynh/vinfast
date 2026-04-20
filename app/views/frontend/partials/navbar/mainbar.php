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
      <button type="button" id="vfLangToggleMain" class="hidden md:inline-flex items-center gap-1 text-slate-600 hover:text-vfNavy text-[12px]">
        <i class="fa-solid fa-globe"></i><span class="vf-lang-toggle__label">VI</span>
      </button>

      <div class="relative" style="z-index: 1099;">
        <button type="button" id="vfNotificationTrigger" class="relative w-8 h-8 rounded-full border border-slate-200 text-slate-600 hover:text-vfNavy transition" onclick="document.getElementById('vfNotificationDropdown').classList.toggle('hidden')">
          <i class="fa-regular fa-bell"></i>
          <span class="absolute -top-1 -right-1 w-[15px] h-[15px] rounded-full bg-red-500 text-white text-[9px] font-bold inline-flex items-center justify-center">1</span>
        </button>
        <div id="vfNotificationDropdown" class="hidden absolute right-0 top-full mt-2 rounded-xl border border-slate-200 bg-white shadow-2xl p-4 z-[9999]" style="width: 360px; min-width: 360px;">
          <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-slate-900">Thông báo</h3>
            <button type="button" class="text-slate-400 hover:text-slate-600 text-lg leading-none" onclick="document.getElementById('vfNotificationDropdown').classList.add('hidden')">
              <i class="fa-solid fa-times"></i>
            </button>
          </div>
          <div class="space-y-2 max-h-96 overflow-y-auto">
            <div class="p-3 rounded-lg bg-slate-50 border border-slate-200 hover:bg-slate-100 transition cursor-pointer">
              <p class="text-sm font-medium text-slate-900">Cập nhật sản phẩm mới</p>
              <p class="text-xs text-slate-500 mt-1">VinFast vừa ra mắt dòng xe mới</p>
              <p class="text-[10px] text-slate-400 mt-2">2 phút trước</p>
            </div>
          </div>
          <div class="mt-3 pt-3 border-t border-slate-200">
            <a href="<?= BASE_URL ?>user/notifications" class="text-center block text-xs font-semibold text-vfNavy hover:text-vfNavy/85">Xem tất cả thông báo</a>
          </div>
        </div>
      </div>

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
        <a href="<?= BASE_URL ?>auth/register" class="hidden md:inline-flex px-3 py-1.5 rounded-md bg-vfNavy text-[#FFB81C] text-[12px] font-bold border-2 border-[#FFB81C] hover:bg-[#FFB81C] hover:text-vfNavy transition-all duration-200">Đăng ký</a>
      <?php endif; ?>

      <button id="vfMobileToggle" type="button" class="md:hidden text-slate-700 text-xl">
        <i class="fa-solid fa-bars"></i>
      </button>
    </div>
  </div>
</div>
