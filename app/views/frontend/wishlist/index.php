<?php
$wishlistItems = is_array($wishlistItems ?? null) ? $wishlistItems : [];
$wishlistIds = is_array($wishlistIds ?? null) ? $wishlistIds : [];
$total = count($wishlistItems);

$productsCssVersion = AssetHelper::getVersion('public/css/frontend/products.css');
$productsJsVersion = AssetHelper::getVersion('public/js/frontend/products.js');

if (!function_exists('extractRangeText')) {
    function extractRangeText(array $specs): string
    {
        if (isset($specs['range']) && $specs['range'] !== '') {
            return (string)$specs['range'];
        }
        return 'N/A';
    }
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>public/css/frontend/products.css?v=<?= htmlspecialchars($productsCssVersion) ?>">
<link rel="stylesheet" href="<?= BASE_URL ?>public/css/frontend/homepage_effects.css">

<section class="products-page bg-slate-50 min-h-screen py-8">
  <div class="max-w-6xl mx-auto px-4 lg:px-6">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-4">
      <div>
        <p class="text-[11px] font-bold uppercase tracking-wider text-vfGold mb-1">Tài khoản của tôi</p>
        <h1 class="text-2xl font-bold text-slate-900">Sản phẩm yêu thích</h1>
      </div>
      <div class="flex items-center gap-3">
        <span class="text-sm text-slate-500"><?= $total ?> sản phẩm</span>
        <a href="<?= BASE_URL ?>products" class="inline-flex items-center gap-1.5 rounded-md border border-slate-200 bg-white px-3 py-1.5 text-[12px] font-semibold text-slate-700 hover:border-vfNavy hover:text-vfNavy transition">
          <i class="fa-solid fa-arrow-left text-[11px]"></i> Tiếp tục xem xe
        </a>
      </div>
    </div>

    <?php if (empty($wishlistItems)): ?>
      <div class="bg-white rounded-xl border border-slate-100 p-12 text-center shadow-sm">
        <div class="w-16 h-16 rounded-full bg-red-50 text-red-400 flex items-center justify-center mx-auto mb-4">
          <i class="fa-regular fa-heart text-2xl"></i>
        </div>
        <h2 class="text-lg font-bold text-slate-900 mb-1">Danh sách yêu thích đang trống</h2>
        <p class="text-sm text-slate-500 mb-5">Hãy bấm biểu tượng trái tim trên các mẫu xe hoặc phụ kiện bạn quan tâm để lưu lại tại đây.</p>
        <a href="<?= BASE_URL ?>products" class="inline-flex items-center gap-2 rounded-lg bg-vfNavy px-5 py-2.5 text-sm font-bold text-white hover:bg-vfNavy/90 transition">
          Khám phá sản phẩm
        </a>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
        <?php foreach ($wishlistItems as $cardIndex => $entry): ?>
          <?php
          $item = $entry['item'] ?? [];
          $p = $entry['product'] ?? null;
          $isDiscontinued = !empty($entry['is_discontinued']);
          $pid = (string)($item['product_id'] ?? '');
          $vid = (string)($item['variant_id'] ?? '');
          $delayClass = $cardIndex % 5 === 0 ? '' : 'reveal-delay-' . ($cardIndex % 5);
          ?>
          <div class="relative flex flex-col gap-2">
            <?php if ($p !== null): ?>
              <?php if ($isDiscontinued): ?>
                <span class="absolute top-3 left-14 z-20 rounded-full bg-slate-800/90 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white shadow-sm">Ngừng bán</span>
              <?php endif; ?>
              <?php include ROOT . '/app/views/frontend/products/partials/card.php'; ?>
            <?php else: ?>
              <div class="bg-white rounded-md border border-slate-200 p-5 flex-1 flex flex-col justify-between">
                <div>
                  <span class="inline-block rounded-full bg-slate-800 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white mb-3">Ngừng bán</span>
                  <h3 class="text-sm font-bold text-slate-900">Sản phẩm #<?= htmlspecialchars($pid) ?></h3>
                  <p class="text-xs text-slate-500 mt-1">Sản phẩm này hiện không còn trên hệ thống.</p>
                </div>
              </div>
            <?php endif; ?>

            <div class="flex items-center justify-between gap-2 rounded-md border border-slate-200 bg-white px-3 py-2">
              <span class="text-[11px] text-slate-500 truncate">
                <?= $vid !== '' ? 'Phiên bản: ' . htmlspecialchars($vid) : 'Lưu ngày ' . htmlspecialchars((string)($item['created_at'] ?? '')) ?>
              </span>
              <form method="post" action="<?= BASE_URL ?>wishlist/remove" class="m-0 shrink-0">
                <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                <input type="hidden" name="product_id" value="<?= htmlspecialchars($pid) ?>">
                <input type="hidden" name="variant_id" value="<?= htmlspecialchars($vid) ?>">
                <button type="submit" class="inline-flex items-center gap-1 rounded px-2.5 py-1 text-[11px] font-semibold text-red-600 hover:bg-red-50 transition">
                  <i class="fa-solid fa-trash-can text-[10px]"></i> Bỏ
                </button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<script src="<?= BASE_URL ?>public/js/frontend/products.js?v=<?= htmlspecialchars($productsJsVersion) ?>"></script>
<script src="<?= BASE_URL ?>public/js/frontend/homepage_effects.js"></script>
