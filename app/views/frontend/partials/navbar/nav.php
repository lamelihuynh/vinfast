<nav class="bg-vfNavy hidden md:block">
  <div class="w-full px-4 lg:px-6 xl:px-8 flex items-center text-[12px] font-semibold tracking-[0.4px]">
    <a href="<?= BASE_URL ?>" class="px-5 py-3 border-b-2 <?= vf_is_active('') ? 'text-vfGold border-vfGold' : 'border-transparent text-white/85 hover:text-vfGold' ?> transition">TRANG CHỦ</a>
      <a href="<?= BASE_URL ?>products" class="inline-flex items-center gap-1 px-5 py-3 border-b-2 <?= vf_is_active('products') || vf_is_active('product') ? 'text-vfGold border-vfGold' : 'border-transparent text-white/85 hover:text-vfGold' ?> transition">
        SẢN PHẨM
      </a>
    <a href="<?= BASE_URL ?>about" class="px-5 py-3 border-b-2 <?= vf_is_active('about') ? 'text-vfGold border-vfGold' : 'border-transparent text-white/85 hover:text-vfGold' ?> transition">GIỚI THIỆU</a>
    <a href="<?= BASE_URL ?>news" class="px-5 py-3 border-b-2 <?= vf_is_active('news') ? 'text-vfGold border-vfGold' : 'border-transparent text-white/85 hover:text-vfGold' ?> transition">TIN TỨC</a>
    <a href="<?= BASE_URL ?>contact" class="px-5 py-3 border-b-2 <?= vf_is_active('contact') ? 'text-vfGold border-vfGold' : 'border-transparent text-white/85 hover:text-vfGold' ?> transition">LIÊN HỆ</a>
    <a href="<?= BASE_URL ?>faqs" class="px-5 py-3 border-b-2 <?= vf_is_active('contact') ? 'text-vfGold border-vfGold' : 'border-transparent text-white/85 hover:text-vfGold' ?> transition">CÂU HỎI THƯỜNG GẶP</a>

    <div class="ml-auto flex items-center gap-2 pl-4">
      <a class="px-4 py-2 text-[11px] font-semibold bg-vfGold text-white rounded-md hover:bg-[#b8921e] transition" href="<?= BASE_URL ?>contact">Lái thử xe</a>
    </div>
  </div>
</nav>