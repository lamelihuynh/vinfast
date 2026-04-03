<?php
$images = is_array($p['images'] ?? null) ? $p['images'] : [];
$imgRel = !empty($images[0]) ? ltrim($images[0], '/') : 'products/default.jpg';
$imgUrl = BASE_URL . 'public/images/' . $imgRel;

$specs = is_array($p['specs'] ?? null) ? $p['specs'] : [];
$isNew = isProductNew($p);
$rangeText = extractRangeText($specs);

$categoryText = (string)($p['category_name'] ?? ($specs['category'] ?? 'VinFast'));
if ($categoryText === '') {
    $categoryText = 'VinFast';
}

$powerText = (string)($specs['power'] ?? ($specs['motor_power'] ?? ($specs['max_power'] ?? 'N/A')));
if ($powerText === '') {
    $powerText = 'N/A';
}
?>
<a href="<?= BASE_URL ?>products/detail/<?= (int)($p['id'] ?? 0) ?>" class="bg-white rounded-md border border-gray-100 overflow-hidden hover:shadow-lg transition-all duration-300 group">
  <div class="relative h-44 bg-slate-50 flex items-center justify-center p-2">
    <?php if ($isNew): ?>
      <span class="absolute top-3 left-3 bg-emerald-500 text-white px-2 py-0.5 rounded-md text-[9px] font-bold">MỚI</span>
    <?php endif; ?>

    <span class="absolute top-3 right-3 bg-white/80 backdrop-blur-sm text-slate-700 px-1.5 py-0.5 rounded border border-slate-200 text-[9px] font-semibold">
      <?= htmlspecialchars($rangeText) ?>
    </span>

    <img src="<?= htmlspecialchars($imgUrl) ?>" class="h-full w-full object-contain group-hover:scale-105 transition-transform duration-300">
  </div>

  <div class="p-4">
    <h4 class="text-slate-900 mb-0.5 text-[14px] font-bold"><?= htmlspecialchars($p['name'] ?? '') ?></h4>
    <p class="text-slate-400 mb-1.5 text-[11px]"><?= htmlspecialchars($categoryText) ?></p>
    <p class="text-vfGold mt-2 mb-2 text-[15px] font-bold"><?= number_format((float)($p['price'] ?? 0), 0, ',', '.') ?> VND</p>

    <div class="flex items-center justify-between">
      <span class="flex items-center gap-1 text-emerald-500 text-[10px]">
        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full inline-block"></span>
        Còn kinh doanh
      </span>
      <span class="text-slate-400 text-[10px]">⚡ <?= htmlspecialchars($powerText) ?></span>
    </div>
  </div>
</a>
