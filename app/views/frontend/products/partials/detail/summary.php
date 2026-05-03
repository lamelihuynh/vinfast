<?php
$priceText = isset($priceText) ? (string)$priceText : '0';
$productName = isset($productName) ? (string)$productName : 'VinFast';
$featureList = isset($featureList) && is_array($featureList) ? $featureList : [];
$colorCode = isset($colorCode) ? (string)$colorCode : '';
$colorName = isset($colorName) ? (string)$colorName : '';
$selectedColorSurcharge = isset($selectedColorSurcharge) ? (int)$selectedColorSurcharge : 0;
$defaultColor = isset($defaultColor) && is_array($defaultColor) ? $defaultColor : [];
$productId = isset($productId) ? (int)$productId : 0;
?>
<div class="min-w-0">
	<div class="mb-2 flex items-start justify-between gap-3">
		<div>
			<p class="mb-1 text-[11px] font-semibold uppercase tracking-[1px] text-amber-600"><?= htmlspecialchars(strtoupper($categoryText)) ?></p>
			<h1 class="m-0 text-2xl font-bold leading-tight text-slate-900 lg:text-[28px]"><?= htmlspecialchars($productName) ?></h1>
		</div>
	</div>


	<div class="mb-4 rounded-xl border border-blue-100 bg-blue-50/60 p-4">
		<p class="mb-1 text-[11px] text-slate-600">Giá tham khảo (kèm pin)</p>
		<p class="m-0 text-[30px] font-extrabold leading-none text-slate-900">
			<?= htmlspecialchars($priceText) ?>
			<span class="text-[14px] font-medium">VNĐ</span>
		</p>
		<p class="mt-1 text-[10px] text-slate-500">Đã bao gồm VAT · Giá có thể thay đổi tùy phiên bản</p>
	</div>

	<div class="mb-4 rounded-xl border border-slate-100 bg-white p-4">
		<div class="mb-2 flex items-center justify-between gap-2">
			<div class="text-[13px] font-bold text-slate-900">Chọn màu ngoại thất</div>
			<?php if (!empty($colorOptions)): ?>
				<span class="text-[11px] text-slate-500">Chọn một màu để xem ảnh tương ứng</span>
			<?php else: ?>
				<p class="m-0 text-[11px] text-amber-600">Chưa cấu hình màu cho mẫu xe này</p>
			<?php endif; ?>
		</div>

		<?php if (!empty($colorOptions)): ?>
			<div class="flex flex-wrap items-center gap-2" id="vfPdColorWrap">
				<?php foreach ($colorOptions as $idx => $color): ?>
					<?php
					$hexColor = trim((string)($color['hex'] ?? ''));
					$colorStyle = $hexColor !== '' ? 'style="--vf-color-chip:' . htmlspecialchars($hexColor) . '"' : '';
					?>
					<button
						type="button"
						class="vf-pd-color-btn inline-grid h-9 w-9 place-items-center rounded-full border-2 border-slate-200 p-0 text-slate-700 transition hover:border-blue-300 <?= (strcasecmp((string)$color['code'], (string)$colorCode) === 0) ? 'is-active' : '' ?>"
						data-color-code="<?= htmlspecialchars((string)$color['code']) ?>"
						data-color-name="<?= htmlspecialchars((string)$color['name']) ?>"
						data-color-image="<?= htmlspecialchars((string)($color['image'] ?? '')) ?>"
						data-color-surcharge="<?= (int)($color['surcharge'] ?? 0) ?>"
						aria-label="<?= htmlspecialchars((string)$color['name'] . ' (' . (string)$color['code'] . ')') ?>"
						<?= $colorStyle ?>>
						<span class="vf-pd-color-dot" aria-hidden="true"></span>
					</button>
				<?php endforeach; ?>
			</div>
		<?php else: ?>
			<div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-[12px] text-amber-700">
				Vui lòng cấu hình màu tại Admin Product để bật tính năng chọn màu và đổi ảnh theo màu trên trang chi tiết.
			</div>
		<?php endif; ?>
	</div>

	<div class="mb-6 rounded-xl border border-slate-100 bg-slate-50 p-4">
		<div class="grid grid-cols-1 gap-3">
			<a id="vfPdCheckoutBtn" href="<?= BASE_URL ?>products/checkout/<?= (int)$productId ?>" class="vf-pd-checkout-link inline-flex items-center justify-center rounded-lg border-2 border-vfNavy bg-vfNavy px-4 py-3 text-center text-[13px] font-bold tracking-[0.3px] text-white no-underline transition hover:border-vfNavy/90 hover:bg-vfNavy/90">ĐẶT CỌC NGAY</a>
		</div>
	</div>

	<div class="mb-2 rounded-xl border border-slate-100 bg-slate-50 p-4">
		<h4 class="mb-3 text-[13px] font-bold text-slate-900">Điểm nổi bật</h4>
		<ul class="m-0 flex list-none flex-col gap-2 p-0">
			<?php foreach ($featureList as $f): ?>
				<li class="flex items-start gap-2 text-[13px] leading-5 text-slate-600">
					<i class="fa-solid fa-circle-check mt-0.5 text-green-500"></i>
					<span><?= htmlspecialchars($f) ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<div class="vf-pd-mobile-cta md:hidden">
		<div class="vf-pd-mobile-cta-inner">
			<div class="vf-pd-mobile-cta-meta">
				<p class="vf-pd-mobile-cta-price"><?= htmlspecialchars($priceText) ?> VNĐ</p>
			</div>
			<a href="<?= BASE_URL ?>products/checkout/<?= (int)$productId ?>" class="vf-pd-checkout-link vf-pd-mobile-cta-btn w-full">Đặt cọc ngay</a>
		</div>
	</div>
</div>