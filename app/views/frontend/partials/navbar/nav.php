<nav class="bg-vfNavy hidden md:block" style="background-color:#0B233F;">
  <div class="w-full px-4 lg:px-6 xl:px-8 flex items-center text-[12px] font-semibold tracking-[0.4px]">
    <a href="<?= BASE_URL ?>"
      class="px-5 py-3 border-b-2 <?= vf_is_active('') ? 'text-[#FFB81C] border-[#FFB81C] hover:text-[#FFB81C]' : 'border-transparent text-white/85 hover:text-[#FFB81C]' ?> transition">TRANG
      CHỦ</a>

    <div class="relative group" id="vfProductsMegaRoot">
      <a href="<?= BASE_URL ?>products"
        class="inline-flex items-center gap-1 px-5 py-3 border-b-2 <?= vf_is_active('products') || vf_is_active('product') ? 'text-[#FFB81C] border-[#FFB81C] hover:text-[#FFB81C]' : 'border-transparent text-white/85 hover:text-[#FFB81C]' ?> transition">
        SẢN PHẨM <i class="fa-solid fa-chevron-down text-[11px]"></i>
      </a>
      <div id="vfProductsMega"
        class="absolute left-0 top-full w-[780px] max-w-[85vw] bg-white rounded-b-2xl border border-slate-200 shadow-2xl hidden group-hover:flex overflow-hidden">
        <div class="flex-1 p-6 grid grid-cols-2 gap-6">
          <div>
            <p class="text-vfNavy text-[10px] font-bold tracking-[1px] mb-3 pb-2 border-b border-slate-100">SUV DIỆN</p>
            <div class="space-y-1">
              <a class="vf-mega-item" href="<?= BASE_URL ?>products?q=VF+3"><span>VF 3</span><small>Mini Electric SUV •
                  210km</small><em class="vf-badge vf-badge--hot">HOT</em></a>
              <a class="vf-mega-item" href="<?= BASE_URL ?>products?q=VF+5"><span>VF 5 Plus</span><small>SUV Hang A •
                  326km</small></a>
              <a class="vf-mega-item" href="<?= BASE_URL ?>products?q=VF+6"><span>VF 6</span><small>SUV Hang B •
                  399km</small><em class="vf-badge vf-badge--new">Mới</em></a>
              <a class="vf-mega-item" href="<?= BASE_URL ?>products?q=VF+7"><span>VF 7</span><small>SUV Hang C •
                  431km</small></a>
              <a class="vf-mega-item" href="<?= BASE_URL ?>products?q=VF+8"><span>VF 8</span><small>SUV Hang D •
                  471km</small><em class="vf-badge vf-badge--best">BEST</em></a>
              <a class="vf-mega-item" href="<?= BASE_URL ?>products?q=VF+9"><span>VF 9</span><small>SUV Hang E •
                  594km</small></a>
            </div>
          </div>
          <div>
            <p class="text-vfNavy text-[10px] font-bold tracking-[1px] mb-3 pb-2 border-b border-slate-100">XE MÁY ĐIỆN
            </p>
            <div class="space-y-1">
              <a class="vf-mega-item" href="<?= BASE_URL ?>products?q=Evo"><span>Evo 200 Lite</span><small>Phổ thông •
                  80km</small></a>
              <a class="vf-mega-item" href="<?= BASE_URL ?>products?q=Feliz"><span>Feliz S</span><small>Trung cấp •
                  95km</small></a>
              <a class="vf-mega-item" href="<?= BASE_URL ?>products?q=Klara"><span>Klara S</span><small>Cao cấp •
                  120km</small></a>
              <a class="vf-mega-item" href="<?= BASE_URL ?>products?q=Vento"><span>Vento</span><small>The thao •
                  100km</small><em class="vf-badge vf-badge--new">MỚI</em></a>
            </div>
          </div>

          <div class="col-span-2 pt-4 border-t border-slate-100 flex flex-wrap gap-2">
            <a class="px-4 py-2 rounded-md bg-vfNavy text-white text-[11px] font-semibold hover:bg-vfNavy/85 transition"
              href="<?= BASE_URL ?>products">Xem tất cả sản phẩm</a>
            <a class="px-4 py-2 rounded-md border border-vfNavy text-vfNavy text-[11px] font-semibold hover:bg-slate-50 transition"
              href="<?= BASE_URL ?>products?sort=price_desc">Dự toán lăn bánh</a>
            <a class="px-4 py-2 rounded-md border-2 border-[#FFB81C] text-[#FFB81C] text-[11px] font-semibold hover:bg-[#FFB81C] hover:text-vfNavy transition"
              href="<?= BASE_URL ?>products/checkout/94">Đặt cọc xe</a>
          </div>
        </div>
        <div class="w-64 bg-vfNavy p-5 flex flex-col">
          <p class="text-[#FFB81C] text-[9px] font-bold tracking-[1px] mb-1">XE NỔI BẬT</p>
          <p class="text-white text-lg font-bold mb-1">VF 9</p>
          <p class="text-white/60 text-[11px] mb-3">Flagship SUV Thuần Điện</p>
          <img src="<?= BASE_URL ?>public/images/products/vf9.webp" alt="VF 9"
            class="h-28 w-full object-cover rounded-md border border-white/20 mb-3">
          <p class="text-white/70 text-[10px] leading-4 mb-3">Không gian 7 cho sang trọng, phạm vi 594km, đỉnh cao của
            dòng xe VinFast.</p>
          <a class="mt-auto text-center rounded-md bg-[#FFB81C] hover:bg-[#FFA500] transition text-vfNavy text-[11px] font-semibold py-2"
            href="<?= BASE_URL ?>products?q=VF+9">Khám phá ngay</a>
        </div>
      </div>
    </div>

    <a href="<?= BASE_URL ?>about"
      class="px-5 py-3 border-b-2 <?= vf_is_active('about') ? 'text-[#FFB81C] border-[#FFB81C] hover:text-[#FFB81C]' : 'border-transparent text-white/85 hover:text-[#FFB81C]' ?> transition">GIỚI
      THIỆU</a>
    <a href="<?= BASE_URL ?>news"
      class="px-5 py-3 border-b-2 <?= vf_is_active('news') ? 'text-[#FFB81C] border-[#FFB81C] hover:text-[#FFB81C]' : 'border-transparent text-white/85 hover:text-[#FFB81C]' ?> transition">TIN
      TỨC</a>
    <a href="<?= BASE_URL ?>contact"
      class="px-5 py-3 border-b-2 <?= vf_is_active('contact') ? 'text-[#FFB81C] border-[#FFB81C] hover:text-[#FFB81C]' : 'border-transparent text-white/85 hover:text-[#FFB81C]' ?> transition">LIÊN
      HỆ</a>

    <div class="ml-auto flex items-center gap-2 pl-4">
      <a class="px-4 py-2 text-[11px] font-semibold border-2 border-[#FFB81C] text-[#FFB81C] rounded-md hover:bg-[#FFB81C] hover:text-vfNavy transition"
        href="<?= BASE_URL ?>products/checkout/94">Đặt cọc</a>
      <a class="px-4 py-2 text-[11px] font-bold rounded-md bg-[#FFB81C] text-vfNavy border-2 border-[#FFB81C] hover:bg-vfNavy hover:text-[#FFB81C] transition-all duration-200"
       href="<?= BASE_URL ?>contact?tab=test-drive">Lái thử xe</a>
    </div>
  </div>
</nav>