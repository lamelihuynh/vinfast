<?php
$mainImage = $mainImage ?? '';
$productName = $productName ?? '';
$imageUrls = $imageUrls ?? [];
?>
<div class="min-w-0">
	<div class="relative mb-3 flex items-center justify-center max-h-[500px] overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
		<img id="vfPdMainImage" src="<?= htmlspecialchars($mainImage) ?>" alt="<?= htmlspecialchars($productName) ?>" class="h-auto w-auto max-h-full max-w-full object-contain">
	</div>

	<div class="flex flex-wrap gap-2" id="vfPdThumbs">
		<?php foreach ($imageUrls as $idx => $img): ?>
			<button
				type="button"
				class="vf-pd-thumb h-14 w-16 overflow-hidden rounded-lg border-2 border-slate-200 bg-white p-0 transition <?= $idx === 0 ? 'is-active' : '' ?>"
				data-img="<?= htmlspecialchars($img) ?>"
				data-index="<?= (int)$idx ?>"
				aria-label="Ảnh <?= (int)$idx + 1 ?>">
				<img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($productName) ?> - Ảnh <?= (int)$idx + 1 ?>" class="h-full w-full object-cover">
			</button>
		<?php endforeach; ?>
	</div>
</div>