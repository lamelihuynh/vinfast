<div class="min-w-0">
	<div class="relative mb-3 aspect-[7/4] lg:aspect-[16/9] overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
		<img id="vfPdMainImage" src="<?= htmlspecialchars($mainImage) ?>" alt="<?= htmlspecialchars($productName) ?>" class="h-full w-full object-cover">
		<button type="button" id="vfPdWishlistBtn" class="vf-pd-wishlist absolute right-3 top-3 grid h-9 w-9 place-items-center rounded-full border border-white/70 bg-white text-slate-400 shadow-md transition hover:text-red-500" aria-label="Yêu thích" aria-pressed="false">
			<i class="fa-regular fa-heart" data-heart-icon></i>
		</button>
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