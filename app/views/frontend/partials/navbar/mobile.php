<!-- Mobile Panel (Drawer from right) -->
<div id="vfMobilePanel" class="fixed top-0 right-0 h-screen w-[88vw] max-w-[340px] bg-vfNavy z-[1300] translate-x-full transition-transform duration-300">
  <div class="px-4 py-4 border-b border-white/10 flex items-center justify-between text-white">
    <strong>Menu</strong>
    <button type="button" id="vfMobileClose" class="text-xl"><i class="fa-solid fa-xmark"></i></button>
  </div>

  <div class="px-4 py-3 space-y-2 overflow-y-auto h-[calc(100vh-72px)]">
    <form action="<?= BASE_URL ?>products" method="get" class="flex border border-white/20 rounded-md overflow-hidden mb-2">
      <input type="text" name="q" placeholder="Tìm kiếm xe, dịch vụ..." class="w-full px-3 py-2.5 bg-white/10 text-white placeholder:text-white/40 text-sm outline-none">
      <button type="submit" class="w-11 bg-vfGold text-white"><i class="fa-solid fa-magnifying-glass"></i></button>
    </form>

    <a class="block rounded-md border border-white/10 px-3 py-2.5 text-white/85 text-sm" href="<?= BASE_URL ?>">TRANG CHỦ</a>
    <a class="block rounded-md border border-white/10 px-3 py-2.5 text-white/85 text-sm" href="<?= BASE_URL ?>products">SẢN PHẨM</a>
    <a class="block rounded-md border border-white/10 px-3 py-2.5 text-white/85 text-sm" href="<?= BASE_URL ?>about">GIỚI THIỆU</a>
    <a class="block rounded-md border border-white/10 px-3 py-2.5 text-white/85 text-sm" href="<?= BASE_URL ?>news">TIN TỨC</a>
    <a class="block rounded-md border border-white/10 px-3 py-2.5 text-white/85 text-sm" href="<?= BASE_URL ?>faq">FAQ</a>
    <a class="block rounded-md border border-white/10 px-3 py-2.5 text-white/85 text-sm" href="<?= BASE_URL ?>contact">LIÊN HỆ</a>

    <div class="pt-3 border-t border-white/10">
      <p class="text-white/40 text-[10px] tracking-[1px] mb-2 uppercase">Dịch vụ nhanh</p>
      <div class="grid grid-cols-2 gap-2">
        <a class="rounded-md border border-white/10 px-3 py-2 text-white/80 text-[12px]" href="<?= BASE_URL ?>products">Dự toán lăn bánh</a>
        <a class="rounded-md border border-white/10 px-3 py-2 text-white/80 text-[12px]" href="<?= BASE_URL ?>products/checkout/1">Đặt cọc xe</a>
        <a class="rounded-md border border-white/10 px-3 py-2 text-white/80 text-[12px]" href="<?= BASE_URL ?>contact">Chính sách</a>
        <a class="rounded-md border border-white/10 px-3 py-2 text-white/80 text-[12px]" href="<?= BASE_URL ?>contact">Hotline hỗ trợ</a>
      </div>
    </div>

    <div class="pt-2 flex gap-2">
      <a class="flex-1 text-center rounded-md border border-vfGold text-vfGold py-2 text-[12px] font-semibold" href="<?= BASE_URL ?>products">Dự toán chi phí</a>
      <a class="flex-1 text-center rounded-md bg-vfGold text-white py-2 text-[12px] font-semibold" href="<?= BASE_URL ?>contact">Lái thử xe</a>
    </div>

    <?php if (Auth::check()): ?>
      <a class="block rounded-md border border-white/10 px-3 py-2.5 text-white/85 text-sm" href="<?= BASE_URL ?>user/profile">Tài khoản</a>
    <?php else: ?>
      <div class="pt-1 flex gap-2">
        <a class="flex-1 text-center rounded-md border border-white/20 text-white/85 py-2 text-[12px]" href="<?= BASE_URL ?>auth/login">Đăng nhập</a>
        <a class="flex-1 text-center rounded-md bg-white text-vfNavy py-2 text-[12px] font-semibold" href="<?= BASE_URL ?>auth/register">Đăng ký</a>
      </div>
    <?php endif; ?>
  </div>
</div>

<div id="vfMobileOverlay" class="fixed inset-0 bg-black/45 z-[1250] opacity-0 pointer-events-none transition-opacity duration-200"></div>
