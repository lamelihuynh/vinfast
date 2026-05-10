<nav class="bg-vfNavy hidden md:block" style="background-color:#0B233F;">
  <div class="w-full px-4 lg:px-6 xl:px-8 flex items-center text-[12px] font-semibold tracking-[0.4px]">
    <a href="<?= BASE_URL ?>"
      class="px-5 py-3 border-b-2 <?= vf_is_active('') ? 'text-[#FFB81C] border-[#FFB81C] hover:text-[#FFB81C]' : 'border-transparent text-white/85 hover:text-[#FFB81C]' ?> transition">TRANG
      CHỦ</a>
      <a href="<?= BASE_URL ?>products"
        class="inline-flex items-center gap-1 px-5 py-3 border-b-2 <?= vf_is_active('products') || vf_is_active('product') ? 'text-[#FFB81C] border-[#FFB81C] hover:text-[#FFB81C]' : 'border-transparent text-white/85 hover:text-[#FFB81C]' ?> transition">
        SẢN PHẨM
      </a>
    <a href="<?= BASE_URL ?>about"
      class="px-5 py-3 border-b-2 <?= vf_is_active('about') ? 'text-[#FFB81C] border-[#FFB81C] hover:text-[#FFB81C]' : 'border-transparent text-white/85 hover:text-[#FFB81C]' ?> transition">GIỚI
      THIỆU</a>
    <a href="<?= BASE_URL ?>news"
      class="px-5 py-3 border-b-2 <?= vf_is_active('news') ? 'text-[#FFB81C] border-[#FFB81C] hover:text-[#FFB81C]' : 'border-transparent text-white/85 hover:text-[#FFB81C]' ?> transition">TIN
      TỨC</a>
    <a href="<?= BASE_URL ?>contact"
      class="px-5 py-3 border-b-2 <?= vf_is_active('contact') ? 'text-[#FFB81C] border-[#FFB81C] hover:text-[#FFB81C]' : 'border-transparent text-white/85 hover:text-[#FFB81C]' ?> transition">LIÊN
      HỆ</a>
    <a href="<?= BASE_URL ?>faqs"
      class="px-5 py-3 border-b-2 <?= vf_is_active('faqs') ? 'text-[#FFB81C] border-[#FFB81C] hover:text-[#FFB81C]' : 'border-transparent text-white/85 hover:text-[#FFB81C]' ?> transition">CÂU HỎI THƯỜNG GẶP
      </a>

    <div class="ml-auto flex items-center gap-2 pl-4">
      <a class="px-4 py-2 text-[11px] font-bold rounded-md bg-[#FFB81C] text-vfNavy border-2 border-[#FFB81C] hover:bg-vfNavy hover:text-[#FFB81C] transition-all duration-200"
       href="<?= BASE_URL ?>contact?tab=test-drive">Lái thử xe</a>
    </div>
  </div>
</nav>