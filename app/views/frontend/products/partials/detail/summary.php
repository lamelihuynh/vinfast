<div class="min-w-0">
	<div class="mb-2 flex items-start justify-between gap-3">
		<div>
			<p class="mb-1 text-[11px] font-semibold uppercase tracking-[1px] text-amber-600"><?= htmlspecialchars(strtoupper($categoryText)) ?></p>
			<h1 class="m-0 text-2xl font-bold leading-tight text-slate-900 lg:text-[28px]"><?= htmlspecialchars($productName) ?></h1>
		</div>
		<button type="button" class="grid h-9 w-9 place-items-center rounded-full border border-slate-200 bg-white text-slate-400 transition hover:border-blue-200 hover:text-blue-600" aria-label="Chia sẻ">
			<i class="fa-solid fa-share-nodes"></i>
		</button>
	</div>

	<div class="mb-4 flex items-center gap-2">
		<div class="inline-flex gap-0.5 text-[12px] text-amber-500" aria-hidden="true">
			<?php for ($i = 0; $i < 5; $i++): ?>
				<i class="fa-solid fa-star"></i>
			<?php endfor; ?>
		</div>
		<span class="text-[12px] text-slate-500">(<?= (int)$reviewCount ?> đánh giá)</span>
	</div>

	<div class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
		<?php foreach ($stats as $s): ?>
			<div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-3 text-center">
				<div class="mb-1 text-amber-600"><i class="<?= htmlspecialchars($s['icon']) ?>"></i></div>
				<p class="m-0 text-[17px] font-bold text-slate-900"><?= htmlspecialchars($s['val']) ?></p>
				<p class="mt-0.5 text-[11px] text-slate-500"><?= htmlspecialchars($s['label']) ?></p>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="mb-5 rounded-xl border border-blue-100 bg-blue-50/60 p-4">
		<p class="mb-1 text-[11px] text-slate-600">Giá tham khảo (kèm pin)</p>
		<p class="m-0 text-[30px] font-extrabold leading-none text-slate-900">
			<?= htmlspecialchars($priceText) ?>
			<span class="text-[14px] font-medium">VNĐ</span>
		</p>
		<p class="mt-1 text-[10px] text-slate-500">Đã bao gồm VAT · Giá có thể thay đổi tùy phiên bản</p>
	</div>

	<div class="mb-6 rounded-xl border border-slate-100 bg-slate-50 p-4">
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

	<div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
		<a href="<?= BASE_URL ?>products/checkout/<?= (int)$productId ?>" class="inline-flex items-center justify-center rounded-lg border-2 border-vfNavy bg-vfNavy px-4 py-3 text-center text-[13px] font-bold tracking-[0.3px] text-white no-underline transition hover:border-vfNavy/90 hover:bg-vfNavy/90">ĐẶT CỌC NGAY</a>
		<a href="<?= BASE_URL ?>contact" class="inline-flex items-center justify-center rounded-lg border-2 border-vfNavy bg-white px-4 py-3 text-center text-[13px] font-bold tracking-[0.3px] text-vfNavy no-underline transition hover:bg-vfNavy hover:text-white">DỰ TOÁN LĂN BÁNH</a>
	</div>
</div>
