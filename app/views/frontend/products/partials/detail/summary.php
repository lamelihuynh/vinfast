<?php
$priceText = isset($priceText) ? (string)$priceText : '0';
$productName = isset($productName) ? (string)$productName : 'VinFast';
$featureList = isset($featureList) && is_array($featureList) ? $featureList : [];
$colorCode = isset($colorCode) ? (string)$colorCode : '';
$colorName = isset($colorName) ? (string)$colorName : '';
$selectedColorSurcharge = isset($selectedColorSurcharge) ? (int)$selectedColorSurcharge : 0;
$defaultColor = isset($defaultColor) && is_array($defaultColor) ? $defaultColor : [];
$productId = isset($productId) ? (int)$productId : 0;
$productIdStr = (string)(($product['product_id'] ?? '') !== '' ? $product['product_id'] : $productId);
$isWishlisted = !empty($isWishlisted);
$jsVariants = isset($jsVariants) && is_array($jsVariants) ? $jsVariants : [];
$selectedVariantId = isset($selectedVariantId) ? (string)$selectedVariantId : '';

$checkoutBasePath = '/order/checkout/' . $productId;
$checkoutQuery = array_filter([
	'variant_id' => $selectedVariantId !== '' ? $selectedVariantId : null,
	'color_code' => $colorCode !== '' ? $colorCode : null,
	'color'      => $colorCode !== '' ? $colorCode : null,
]);
$checkoutTargetPath = $checkoutBasePath . ($checkoutQuery ? ('?' . http_build_query($checkoutQuery)) : '');
$checkoutUrl = Auth::check()
	? BASE_URL . ltrim($checkoutTargetPath, '/')
	: BASE_URL . 'auth/login?redirect=' . urlencode($checkoutTargetPath);
?>
<div class="min-w-0">
	<div class="mb-2 flex items-start justify-between gap-3">
		<div class="min-w-0 flex-1">
			<?php
			$catText = (string)($categoryText ?? '');
			$pName = (string)($productName ?? '');
			if (function_exists('mb_strlen')) {
				$displayH1 = mb_strlen($pName, 'UTF-8') > 80 ? mb_substr($pName, 0, 80, 'UTF-8') . '...' : $pName;
			} else {
				$displayH1 = strlen($pName) > 80 ? substr($pName, 0, 80) . '...' : $pName;
			}
			?>
			<p class="mb-1 text-[11px] font-semibold uppercase tracking-[1px] text-amber-600 reveal-on-scroll"><?= htmlspecialchars(strtoupper($catText)) ?></p>
			<h1 class="m-0 text-2xl font-bold leading-tight text-slate-900 lg:text-[28px] reveal-on-scroll reveal-delay-1 letter-reveal" title="<?= htmlspecialchars($pName) ?>" style="max-width:100%;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($displayH1) ?></h1>
		</div>
		<?php if ($productId > 0): ?>
			<button
				type="button"
				id="vfPdWishlistBtn"
				class="vf-pd-wishlist inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-400 shadow-sm transition hover:border-red-200 hover:text-red-500<?= $isWishlisted ? ' is-active' : '' ?>"
				data-wishlist-product="<?= htmlspecialchars($productIdStr) ?>"
				data-wishlist-variant=""
				data-wishlist-url="<?= BASE_URL ?>wishlist/toggle"
				aria-pressed="<?= $isWishlisted ? 'true' : 'false' ?>"
				aria-label="<?= $isWishlisted ? 'Bỏ khỏi yêu thích' : 'Thêm vào yêu thích' ?>">
				<i data-heart-icon class="<?= $isWishlisted ? 'fa-solid' : 'fa-regular' ?> fa-heart text-base"></i>
			</button>
		<?php endif; ?>
	</div>


	<div class="mb-4 rounded-xl border border-blue-100 bg-blue-50/60 p-4">
		<p class="mb-1 text-[11px] text-slate-600">Giá tham khảo (kèm pin)</p>
		<p class="m-0 text-[30px] font-extrabold leading-none text-slate-900">
			<span id="vfPdPriceValue"><?= htmlspecialchars($priceText) ?></span>
			<span class="text-[14px] font-medium">VNĐ</span>
		</p>
		<p class="mt-1 text-[10px] text-slate-500">Đã bao gồm VAT · Giá có thể thay đổi tùy phiên bản và màu sắc</p>
	</div>

	<?php if (count($jsVariants) >= 2): ?>
		<div class="mb-4 rounded-xl border border-slate-100 bg-white p-4" id="vfPdVariantSection">
			<div class="mb-2 flex items-center justify-between gap-2">
				<div class="text-[13px] font-bold text-slate-900">Chọn phiên bản</div>
				<span class="text-[11px] text-slate-500" id="vfPdSelectedVariantLabel">
					<?php
					$curVarName = '';
					foreach ($jsVariants as $vItem) {
						if ($vItem['variantId'] === $selectedVariantId) {
							$curVarName = (string)$vItem['name'];
							break;
						}
					}
					echo htmlspecialchars($curVarName);
					?>
				</span>
			</div>
			<div class="grid grid-cols-1 gap-2 sm:grid-cols-2" id="vfPdVariantWrap" role="group" aria-label="Chọn phiên bản xe">
				<?php foreach ($jsVariants as $vItem): ?>
					<?php
					$vId = (string)$vItem['variantId'];
					$vName = (string)$vItem['name'];
					$vPrice = (float)$vItem['price'];
					$isVarActive = ($vId === $selectedVariantId);
					?>
					<button
						type="button"
						class="vf-pd-variant-btn flex items-center justify-between gap-2 rounded-lg border px-3 py-2.5 text-left transition <?= $isVarActive ? 'is-active border-vfNavy bg-blue-50/70 text-vfNavy' : 'border-slate-200 bg-white text-slate-700 hover:border-blue-300' ?>"
						data-variant-id="<?= htmlspecialchars($vId) ?>"
						data-variant-name="<?= htmlspecialchars($vName) ?>"
						data-price="<?= htmlspecialchars((string)$vPrice) ?>"
						aria-pressed="<?= $isVarActive ? 'true' : 'false' ?>">
						<span class="text-[13px] font-semibold truncate"><?= htmlspecialchars($vName) ?></span>
						<span class="text-[12px] font-bold shrink-0"><?= htmlspecialchars(number_format($vPrice, 0, ',', '.')) ?> đ</span>
					</button>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<div class="mb-4 rounded-xl border border-slate-100 bg-white p-4">
		<div class="mb-2 flex items-center justify-between gap-2">
			<div class="text-[13px] font-bold text-slate-900">Chọn màu ngoại thất</div>
			<span class="text-[11px] text-slate-500" id="vfPdSelectedColorLabel">
				<?php if ($colorName !== ''): ?>
					<?= htmlspecialchars($colorName) ?><?= $selectedColorSurcharge > 0 ? (' (+' . number_format($selectedColorSurcharge, 0, ',', '.') . ' đ)') : '' ?>
				<?php elseif (!empty($colorOptions)): ?>
					Chọn một màu để xem ảnh tương ứng
				<?php else: ?>
					Chưa cấu hình màu cho phiên bản này
				<?php endif; ?>
			</span>
		</div>

		<div id="vfPdColorGroups">
			<?php foreach ($jsVariants as $vItem): ?>
				<?php
				$vId = (string)$vItem['variantId'];
				$vColors = is_array($vItem['colors'] ?? null) ? $vItem['colors'] : [];
				$isCurrentGroup = ($vId === $selectedVariantId);
				?>
				<div
					class="vf-pd-color-group <?= $isCurrentGroup ? '' : 'hidden' ?>"
					data-variant-colors="<?= htmlspecialchars($vId) ?>">
					<?php if (!empty($vColors)): ?>
						<div class="flex flex-wrap items-center gap-2">
							<?php foreach ($vColors as $cIdx => $color): ?>
								<?php
								$hexColor = trim((string)($color['hex'] ?? ''));
								$colorStyle = $hexColor !== '' ? 'style="--vf-color-chip:' . htmlspecialchars($hexColor) . '"' : '';
								$isBtnActive = $isCurrentGroup
									? (strcasecmp((string)$color['code'], $colorCode) === 0)
									: (!empty($color['isDefault']) || $cIdx === 0);
								?>
								<button
									type="button"
									class="vf-pd-color-btn inline-grid h-9 w-9 place-items-center rounded-full border-2 border-slate-200 p-0 text-slate-700 transition hover:border-blue-300 <?= $isBtnActive ? 'is-active' : '' ?>"
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
			<?php endforeach; ?>
		</div>
	</div>

	<div class="mb-6 rounded-xl border border-slate-100 bg-slate-50 p-4">
		<form
			id="vfPdDepositForm"
			method="get"
			action="<?= htmlspecialchars(BASE_URL . 'order/checkout/' . $productId) ?>"
			data-checkout-path="<?= htmlspecialchars($checkoutBasePath) ?>"
			data-is-auth="<?= Auth::check() ? '1' : '0' ?>"
			data-login-url="<?= htmlspecialchars(BASE_URL . 'auth/login') ?>"
			class="grid grid-cols-1 gap-3 m-0">
			<input type="hidden" name="variant_id" id="vfPdVariantInput" value="<?= htmlspecialchars($selectedVariantId) ?>">
			<input type="hidden" name="color_code" id="vfPdColorInput" value="<?= htmlspecialchars($colorCode) ?>">
			<input type="hidden" name="color" id="vfPdColorQueryInput" value="<?= htmlspecialchars($colorCode) ?>">
			<button
				type="submit"
				id="vfPdCheckoutBtn"
				class="inline-flex w-full cursor-pointer items-center justify-center rounded-lg border-2 border-vfNavy bg-vfNavy px-4 py-3 text-center text-[13px] font-bold tracking-[0.3px] text-white no-underline transition hover:border-vfNavy/90 hover:bg-vfNavy/90">
				ĐẶT CỌC NGAY
			</button>
		</form>
	</div>

	<!-- Removed static 'Điểm nổi bật' feature list per request -->

	<div class="vf-pd-mobile-cta md:hidden">
		<div class="vf-pd-mobile-cta-inner">
			<div class="vf-pd-mobile-cta-meta">
				<p class="vf-pd-mobile-cta-price"><span id="vfPdMobilePriceValue"><?= htmlspecialchars($priceText) ?></span> VNĐ</p>
			</div>
			<a href="<?= htmlspecialchars($checkoutUrl) ?>" data-checkout-base="<?= htmlspecialchars(BASE_URL . 'order/checkout/' . $productId) ?>" class="vf-pd-checkout-link vf-pd-mobile-cta-btn w-full">Đặt cọc ngay</a>
		</div>
	</div>
</div>