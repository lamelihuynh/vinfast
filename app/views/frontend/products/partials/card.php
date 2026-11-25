<?php
$p = $p ?? [];
$images = is_array($p['images'] ?? null) ? $p['images'] : [];
$imgUrl = ProductViewHelper::thumbUrl($p);

$specs = is_array($p['specs'] ?? null) ? $p['specs'] : [];
$rangeText = function_exists('extractRangeText')
  ? extractRangeText($specs)
  : (isset($specs['range']) && $specs['range'] !== '' ? (string)$specs['range'] : 'N/A');
$wishlistIds = is_array($wishlistIds ?? null) ? $wishlistIds : [];
$productIdStr = trim((string)($p['product_id'] ?? $p['id'] ?? ''));
$isWishlisted = $productIdStr !== '' && in_array($productIdStr, $wishlistIds, true);

$categoryText = (string)($p['category_name'] ?? ($specs['category'] ?? 'VinFast'));
if ($categoryText === '') {
  $categoryText = 'VinFast';
}

$powerText = (string)($specs['power'] ?? ($specs['motor_power'] ?? ($specs['max_power'] ?? 'N/A')));
if ($powerText === '') {
  $powerText = 'N/A';
}

// Mặt sau thẻ: xe dùng 4 thông số cố định; phụ kiện lấy theo thuộc tính động của category
$isAccessory = !empty($p['is_accessory']);
$priceText = number_format((float)($p['price'] ?? 0), 0, ',', '.') . ' VND';
if ($isAccessory) {
  $categoryText = (string)($p['category_label'] ?? 'Phụ kiện');
  $variantCount = count(array_filter((array)($p['variants'] ?? []), static fn($v) => !empty($v['isActive'])));
  $rangeText = $variantCount > 1 ? $variantCount . ' phiên bản' : 'Phụ kiện';
  $cardStats = [];
  $catDef = Category::allById()[(int)($p['category_id'] ?? 0)] ?? [];
  foreach ((array)($catDef['dynamic_attributes'] ?? []) as $def) {
    if (count($cardStats) >= 3 || ($def['type'] ?? '') === 'text') {
      continue;
    }
    $val = $specs[$def['key'] ?? ''] ?? null;
    if ($val === null || $val === '' || $val === []) {
      continue;
    }
    if (is_array($val)) {
      $val = count($val) > 3 ? implode(', ', array_slice($val, 0, 3)) . '…' : implode(', ', $val);
    } elseif (is_bool($val)) {
      $val = $val ? 'Có' : 'Không';
    } else {
      $val = trim($val . ' ' . ($def['unit'] ?? ''));
    }
    $cardStats[] = ['•', (string)($def['label'] ?? ''), (string)$val];
  }
  $cardStats[] = ['₫', 'Giá từ', $priceText];
} else {
  $cardStats = [
    ['⚡', 'Công suất', $powerText],
    ['🔋', 'Phạm vi', $rangeText],
    ['₫', 'Giá từ', $priceText],
    ['✓', 'Vận tốc tối đa', (string)($specs['max_speed'] ?? 'N/A')],
  ];
}
?>
<div class="flip-card bg-transparent reveal-on-scroll <?= $delayClass ?? '' ?>" tabindex="0">
  <div class="flip-card-inner">
    <!-- FRONT -->
    <div class="flip-card-face card-front bg-white rounded-md border border-gray-100 overflow-hidden hover:shadow-lg transition-all duration-300 group">
      <?php if ($productIdStr !== ''): ?>
        <button
          type="button"
          class="vf-wishlist-btn absolute top-3 left-3 z-10 inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-slate-400 shadow-sm border border-slate-200/80 hover:text-red-500 transition<?= $isWishlisted ? ' is-active' : '' ?>"
          data-wishlist-product="<?= htmlspecialchars($productIdStr) ?>"
          data-wishlist-variant=""
          data-wishlist-url="<?= BASE_URL ?>wishlist/toggle"
          aria-pressed="<?= $isWishlisted ? 'true' : 'false' ?>"
          aria-label="<?= $isWishlisted ? 'Bỏ khỏi yêu thích' : 'Thêm vào yêu thích' ?>">
          <i data-heart-icon class="<?= $isWishlisted ? 'fa-solid' : 'fa-regular' ?> fa-heart text-sm"></i>
        </button>
      <?php endif; ?>
      <a href="<?= BASE_URL ?>products/detail/<?= (int)($p['id'] ?? 0) ?>" class="block h-full">
        <div class="relative h-44 bg-slate-50 flex items-center justify-center p-2">
          <span class="absolute top-3 right-3 bg-white/80 backdrop-blur-sm text-slate-700 px-1.5 py-0.5 rounded border border-slate-200 text-[9px] font-semibold">
            <?= htmlspecialchars($rangeText) ?>
          </span>

          <img src="<?= htmlspecialchars($imgUrl) ?>" class="h-full w-full object-contain group-hover:scale-105 transition-transform duration-300">
        </div>

        <div class="p-4">
          <?php
          $name = (string)($p['name'] ?? '');
          if (function_exists('mb_strlen')) {
            $displayName = mb_strlen($name, 'UTF-8') > 40 ? mb_substr($name, 0, 40, 'UTF-8') . '...' : $name;
          } else {
            $displayName = strlen($name) > 40 ? substr($name, 0, 40) . '...' : $name;
          }
          ?>
          <h4 class="text-slate-900 mb-0.5 text-[14px] font-bold" title="<?= htmlspecialchars($name) ?>" style="max-width:100%;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($displayName) ?></h4>
          <p class="text-slate-400 mb-1.5 text-[11px]"><?= htmlspecialchars($categoryText) ?></p>
          <p class="text-vfGold mt-2 mb-2 text-[15px] font-bold"><?= $isAccessory && count((array)($p['variants'] ?? [])) > 1 ? 'Từ ' : '' ?><?= $priceText ?></p>
        </div>
      </a>
    </div>

    <!-- BACK -->
    <div class="flip-card-face flip-card-back bg-[#1a2240] rounded-md overflow-hidden flex flex-col p-4 text-white">
      <div class="mb-3">
        <p class="text-[#c8a22e] mb-0.5 text-[10px] font-bold tracking-wider"><?= htmlspecialchars(strtoupper($categoryText)) ?></p>
        <?php
        $backName = (string)($p['name'] ?? '');
        if (function_exists('mb_strlen')) {
          $displayBackName = mb_strlen($backName, 'UTF-8') > 48 ? mb_substr($backName, 0, 48, 'UTF-8') . '...' : $backName;
        } else {
          $displayBackName = strlen($backName) > 48 ? substr($backName, 0, 48) . '...' : $backName;
        }
        ?>
        <p class="text-[15px] font-extrabold" title="<?= htmlspecialchars($backName) ?>" style="max-width:100%;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($displayBackName) ?></p>
      </div>

      <div class="grid grid-cols-2 gap-2 mb-3 flex-1">
        <?php foreach ($cardStats as [$statIcon, $statLabel, $statValue]): ?>
        <div class="bg-white/10 rounded-lg p-2">
          <div class="flex items-center gap-1 text-white/60 mb-0.5"><span class="text-[11px]"><?= $statIcon ?></span><span class="text-[9px]"><?= htmlspecialchars($statLabel) ?></span></div>
          <p class="text-[11px] font-bold" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= htmlspecialchars($statValue) ?>"><?= htmlspecialchars($statValue) ?></p>
        </div>
        <?php endforeach; ?>
      </div>


      <div class="flex gap-2">
        <a href="<?= BASE_URL ?>products/detail/<?= (int)($p['id'] ?? 0) ?>" class="flex-1 flex items-center justify-center gap-1 bg-[#1464F4] text-white rounded-lg py-2 hover:bg-[#0d4ec9] transition text-[11px] font-bold">Xem chi tiết</a>
        <?php if ($productIdStr !== ''): ?>
          <button
            type="button"
            class="vf-wishlist-btn inline-flex items-center justify-center rounded-lg border border-white/25 bg-white/10 px-3 py-2 text-white/80 hover:bg-white/20 transition<?= $isWishlisted ? ' is-active' : '' ?>"
            data-wishlist-product="<?= htmlspecialchars($productIdStr) ?>"
            data-wishlist-variant=""
            data-wishlist-url="<?= BASE_URL ?>wishlist/toggle"
            aria-pressed="<?= $isWishlisted ? 'true' : 'false' ?>"
            aria-label="<?= $isWishlisted ? 'Bỏ khỏi yêu thích' : 'Thêm vào yêu thích' ?>">
            <i data-heart-icon class="<?= $isWishlisted ? 'fa-solid' : 'fa-regular' ?> fa-heart text-sm"></i>
          </button>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>