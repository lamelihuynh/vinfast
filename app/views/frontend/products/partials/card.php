<?php
$p = $p ?? [];
$images = is_array($p['images'] ?? null) ? $p['images'] : [];
$imgUrl = ProductViewHelper::thumbUrl($p);

$specs = is_array($p['specs'] ?? null) ? $p['specs'] : [];
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
<div class="flip-card bg-transparent" tabindex="0">
  <div class="flip-card-inner">
    <!-- FRONT -->
    <div class="flip-card-face card-front bg-white rounded-md border border-gray-100 overflow-hidden hover:shadow-lg transition-all duration-300 group">
      <a href="<?= BASE_URL ?>products/detail/<?= (int)($p['id'] ?? 0) ?>" class="block h-full">
        <div class="relative h-44 bg-slate-50 flex items-center justify-center p-2">
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
    </div>

    <!-- BACK -->
    <div class="flip-card-face flip-card-back bg-[#1a2240] rounded-md overflow-hidden flex flex-col p-4 text-white">
      <div class="mb-3">
        <p class="text-[#c8a22e] mb-0.5 text-[10px] font-bold tracking-wider"><?= htmlspecialchars(strtoupper($categoryText)) ?></p>
        <p class="text-[15px] font-extrabold"><?= htmlspecialchars($p['name'] ?? '') ?></p>
      </div>

      <div class="grid grid-cols-2 gap-2 mb-3 flex-1">
        <div class="bg-white/10 rounded-lg p-2">
          <div class="flex items-center gap-1 text-white/60 mb-0.5"><span class="text-[11px]">⚡</span><span class="text-[9px]">Công suất</span></div>
          <p class="text-[11px] font-bold"><?= htmlspecialchars($powerText) ?></p>
        </div>
        <div class="bg-white/10 rounded-lg p-2">
          <div class="flex items-center gap-1 text-white/60 mb-0.5"><span class="text-[11px]">🔋</span><span class="text-[9px]">Phạm vi</span></div>
          <p class="text-[11px] font-bold"><?= htmlspecialchars($rangeText) ?></p>
        </div>
        <div class="bg-white/10 rounded-lg p-2">
          <div class="flex items-center gap-1 text-white/60 mb-0.5"><span class="text-[11px]">₫</span><span class="text-[9px]">Giá từ</span></div>
          <p class="text-[11px] font-bold"><?= number_format((float)($p['price'] ?? 0), 0, ',', '.') ?> VND</p>
        </div>
        <div class="bg-white/10 rounded-lg p-2">
          <div class="flex items-center gap-1 text-white/60 mb-0.5"><span class="text-[11px]">✓</span><span class="text-[9px]">Trạng thái</span></div>
          <p class="text-[11px] font-bold">Còn kinh doanh</p>
        </div>
      </div>


      <div class="flex gap-2">
        <a href="<?= BASE_URL ?>products/detail/<?= (int)($p['id'] ?? 0) ?>" class="flex-1 flex items-center justify-center gap-1 bg-[#1464F4] text-white rounded-lg py-2 hover:bg-[#0d4ec9] transition text-[11px] font-bold">Xem chi tiết</a>
      </div>
    </div>
  </div>
</div>