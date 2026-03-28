<?php
$q = $q ?? '';
$cat = (int)($cat ?? 0);
$sort = $sort ?? 'default';
$pp = (int)($pp ?? 12);
$price = $price ?? 'all';
$range = $range ?? 'all';
$products = $products ?? [];
$categories = $categories ?? [];
$total = (int)($total ?? count($products));

$activeFilters = 0;
if ($cat > 0) $activeFilters++;
if ($price !== 'all') $activeFilters++;
if ($range !== 'all') $activeFilters++;

function selectedValue(string $value, string $current): string {
    return $value === $current ? 'selected' : '';
}

function extractRangeText(array $specs): string {
    if (isset($specs['range'])) return (string)$specs['range'];
    return 'N/A';
}

function isProductNew(array $product): bool {
    if (empty($product['created_at'])) return false;
    $created = strtotime($product['created_at']);
    if (!$created) return false;
    return (time() - $created) <= 60 * 24 * 3600;
}

$productsJsPath = ROOT . '/public/js/frontend/products.js';
$productsJsVersion = file_exists($productsJsPath)
  ? (string) filemtime($productsJsPath)
  : (string) time();
?>

<link rel="stylesheet" href="<?= BASE_URL ?>public/css/frontend/products.css">

<section class="products-page bg-slate-50 min-h-screen py-5">
  <div class="w-full px-3 lg:px-5 xl:px-6">
    <div id="productsOverlay" class="fixed inset-0 z-40 bg-black/45 hidden lg:hidden"></div>

    <button id="openProductsFilter" class="lg:hidden mb-3 inline-flex items-center gap-2 px-4 py-2 rounded-md border border-slate-200 bg-white text-slate-700">
      <i class="fa-solid fa-sliders"></i>
      Bộ lọc
      <?php if ($activeFilters > 0): ?>
        <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-vfGold text-white text-[11px] font-bold\"><?= $activeFilters ?></span>
      <?php endif; ?>
    </button>

    <div class="products-wrap">
      <?php include ROOT . '/app/views/frontend/products/partials/filter.php'; ?>

      <div class="products-content flex-1 min-w-0">
        <div class="products-toolbar mb-4 flex flex-wrap items-center justify-between gap-3">
          <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-slate-900">Sản phẩm</h1>
            <p class="text-sm text-slate-500"><?= $total ?> sản phẩm</p>
          </div>

          <div class="flex items-center gap-2 sm:gap-3">
            <span class="text-slate-500 hidden sm:inline text-[12px]">Hiển thị:</span>
            <select id="productsPerPageSelect" class="border border-slate-200 rounded-md px-2 sm:px-3 py-1.5 text-slate-700 outline-none focus:border-vfNavy bg-white text-[12px]" onchange="var f=document.getElementById('productsFilterForm');var p=document.getElementById('filterPerPageInput');if(p){p.value=this.value;}if(f){f.submit();}">
              <option value="6" <?= $pp === 6 ? 'selected' : '' ?>>6</option>
              <option value="12" <?= $pp === 12 ? 'selected' : '' ?>>12</option>
              <option value="15" <?= $pp === 15 ? 'selected' : '' ?>>15</option>
            </select>

            <span class="text-slate-500 hidden sm:inline text-[12px]">Sắp xếp:</span>
            <select id="productsSortSelect" class="border border-slate-200 rounded-md px-2 sm:px-3 py-1.5 text-slate-700 outline-none focus:border-vfNavy bg-white text-[12px]">
              <option value="default" <?= selectedValue('default', $sort) ?>>Mặc định</option>
              <option value="price_asc" <?= selectedValue('price_asc', $sort) ?>>Giá tăng dần</option>
              <option value="price_desc" <?= selectedValue('price_desc', $sort) ?>>Giá giảm dần</option>
              <option value="newest" <?= selectedValue('newest', $sort) ?>>Mới nhất</option>
            </select>
          </div>
        </div>

        <div class="products-grid-wrap">
          <?php if (empty($products)): ?>
            <div class="text-center py-20">
              <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-sliders text-slate-400 text-xl"></i>
              </div>
              <p class="text-slate-500 text-[14px]">Không tìm thấy sản phẩm phù hợp</p>
              <a href="<?= BASE_URL ?>products" class="mt-3 inline-block text-vfNavy hover:text-vfBlue transition text-[12px] font-semibold">
                Xóa bộ lọc
              </a>
            </div>
          <?php else: ?>
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-5 mb-8">
              <?php foreach ($products as $p): ?>
                <?php include ROOT . '/app/views/frontend/products/partials/card.php'; ?>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <?php include ROOT . '/app/views/frontend/products/partials/pagination.php'; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<script src="<?= BASE_URL ?>public/js/frontend/products.js?v=<?= htmlspecialchars($productsJsVersion) ?>"></script>
