<?php
$priceOptions = [
    'all' => 'Tất cả',
    'under300' => 'Dưới 300 triệu',
    '300-500' => '300-500 triệu',
    '500-1000' => '500 triệu - 1 tỷ',
    'over1000' => 'Trên 1 tỷ',
];

$rangeOptions = [
    'all' => 'Tất cả',
    'lt200' => 'Dưới 200km',
    '200-400' => '200-400km',
    'gt400' => 'Trên 400km',
];
$categories = $categories ?? [];
$selectedCategoryName = '';
foreach ($categories as $c) {
    if ((int)$c['id'] === $cat) {
        $selectedCategoryName = (string)$c['name'];
        break;
    }
}
$price = $price ?? 'all';
$range = $range ?? 'all';
$sort = $sort ?? 'default';
$q = $q ?? '';
?>

<aside id="productsSidebar" class="products-sidebar bg-white border border-slate-100 rounded-lg flex-shrink-0 fixed lg:static z-50 lg:z-auto transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0 self-start">
  <div class="p-3 border-b border-slate-100 flex items-center justify-between lg:sticky lg:top-0 bg-white z-10">
    <div class="flex items-center gap-2">
      <i class="fa-solid fa-sliders text-slate-600 text-sm"></i>
      <span class="text-[14px] font-bold text-slate-900">Bộ lọc</span>
      <?php if ($activeFilters > 0): ?>
        <span class="bg-vfGold text-white rounded-full w-5 h-5 inline-flex items-center justify-center text-[10px] font-bold"><?= (int)$activeFilters ?></span>
      <?php endif; ?>
    </div>
    <div class="flex items-center gap-2">
      <button id="closeProductsFilter" class="lg:hidden p-1 rounded-lg hover:bg-slate-100 text-slate-500">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
  </div>

  <form id="productsFilterForm" method="get" action="<?= BASE_URL ?>products" class="p-4 space-y-3">
    <input type="hidden" id="filterCatInput" name="cat" value="<?= (int)$cat ?>">
    <input type="hidden" id="filterSortInput" name="sort" value="<?= htmlspecialchars($sort) ?>">
  <input type="hidden" id="filterPerPageInput" name="pp" value="<?= (int)($pp ?? 12) ?>">
    <?php if ($activeFilters > 0): ?>
        <div class="space-y-2">
          <p class="text-slate-500 text-[10px] font-semibold tracking-wide">BỘ LỌC ĐANG ÁP DỤNG</p>
          <div class="flex flex-wrap gap-1.5">
            <?php if ($cat > 0 && $selectedCategoryName !== ''): ?>
              <span class="inline-flex items-center gap-1 bg-vfNavy/10 text-vfNavy px-2 py-1 rounded-md text-[10px]"><?= htmlspecialchars($selectedCategoryName) ?></span>
            <?php endif; ?>
            <?php if ($price !== 'all'): ?>
              <span class="inline-flex items-center gap-1 bg-vfNavy/10 text-vfNavy px-2 py-1 rounded-md text-[10px]"><?= htmlspecialchars($priceOptions[$price] ?? 'Mức giá') ?></span>
            <?php endif; ?>
            <?php if ($range !== 'all'): ?>
              <span class="inline-flex items-center gap-1 bg-vfNavy/10 text-vfNavy px-2 py-1 rounded-md text-[10px]"><?= htmlspecialchars($rangeOptions[$range] ?? 'Phạm vi') ?></span>
            <?php endif; ?>
               <a
              href="<?= BASE_URL ?>products"
              class="inline-flex items-center gap-1 text-[10px] font-semibold text-slate-500 hover:text-slate-700 transition"
            >
              <i class="fa-solid fa-xmark text-[9px]"></i>
              Xóa
            </a>
          </div>
        </div>
    <?php endif; ?>
    <div>
      <label class="block text-[11px] uppercase tracking-wide text-slate-500 font-semibold mb-1">Tìm kiếm</label>
      <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Tìm theo tên xe..." class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm outline-none focus:border-vfNavy">
    </div>

    <div>
      <button type="button" data-filter-toggle data-target="filterCategoryBody" class="w-full flex items-center justify-between py-1.5 px-1">
        <span class="text-[11px] uppercase tracking-wide text-slate-500 font-semibold">DANH MỤC</span>
        <i data-filter-icon class="fa-solid fa-chevron-down text-[11px] text-slate-400"></i>
      </button>
      <div id="filterCategoryBody" class="mt-1">
        <ul class="space-y-1">
          <li>
            <button type="button" data-cat-value="0" class="js-cat-btn w-full text-left py-1.5 px-3 rounded-lg transition text-[12px] <?= $cat === 0 ? 'font-bold text-vfNavy bg-vfNavy/10' : 'font-normal text-slate-700 hover:bg-slate-50' ?>">Tất cả danh mục</button>
          </li>
          <?php foreach ($categories as $c): ?>
            <?php $isActiveCat = $cat === (int)$c['id']; ?>
            <li>
              <button type="button" data-cat-value="<?= (int)$c['id'] ?>" class="js-cat-btn w-full text-left py-1.5 px-3 rounded-lg transition text-[12px] <?= $isActiveCat ? 'font-bold text-vfNavy bg-vfNavy/10' : 'font-normal text-slate-700 hover:bg-slate-50' ?>"><?= htmlspecialchars($c['name']) ?></button>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>

    <div>
      <button type="button" data-filter-toggle data-target="filterPriceBody" class="w-full flex items-center justify-between py-1.5 px-1">
        <span class="text-[11px] uppercase tracking-wide text-slate-500 font-semibold">MỨC GIÁ</span>
        <i data-filter-icon class="fa-solid fa-chevron-down text-[11px] text-slate-400"></i>
      </button>
      <div id="filterPriceBody" class="mt-1 space-y-1">
        <?php foreach ($priceOptions as $value => $label): ?>
          <label class="flex items-center gap-2.5 py-1.5 px-2 rounded-lg cursor-pointer hover:bg-slate-50 transition">
            <input type="radio" name="price" value="<?= $value ?>" <?= $price === $value ? 'checked' : '' ?> class="accent-vfNavy w-[14px] h-[14px]">
            <span class="text-slate-600 text-[12px]"><?= $label ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>

    <div>
      <button type="button" data-filter-toggle data-target="filterRangeBody" class="w-full flex items-center justify-between py-1.5 px-1">
        <span class="text-[11px] uppercase tracking-wide text-slate-500 font-semibold">PHẠM VI</span>
        <i data-filter-icon class="fa-solid fa-chevron-down text-[11px] text-slate-400"></i>
      </button>
      <div id="filterRangeBody" class="mt-1 space-y-1">
        <?php foreach ($rangeOptions as $value => $label): ?>
          <label class="flex items-center gap-2.5 py-1.5 px-2 rounded-lg cursor-pointer hover:bg-slate-50 transition">
            <input type="radio" name="range" value="<?= $value ?>" <?= $range === $value ? 'checked' : '' ?> class="accent-vfNavy w-[14px] h-[14px]">
            <span class="text-slate-600 text-[12px]"><?= $label ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>
  </form>
</aside>
