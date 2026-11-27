<?php

/**
 * app/views/frontend/products/accessory.php — Chi tiết phụ kiện (Phase 0.5)
 *
 * Biến: $product (Product::formatProduct), $category (Category::allById()[id]),
 *       $inventory (InventoryService::byProduct), $related (sản phẩm cùng category)
 * Nút "Thêm vào giỏ" để sẵn form POST cart/add — T4 (Transaction) hiện thực CartController.
 */
$product = $product ?? [];
$category = $category ?? [];
$inventory = $inventory ?? [];
$related = $related ?? [];

$specs = is_array($product['specs'] ?? null) ? $product['specs'] : [];
$variantDefs = (array)($category['variant_attributes'] ?? []);
$attrDefs = (array)($category['dynamic_attributes'] ?? []);

$variants = [];
foreach ((array)($product['variants'] ?? []) as $v) {
    if (empty($v['isActive'])) {
        continue;
    }
    $vid = (string)$v['variantId'];
    $variants[] = [
        'id' => $vid,
        'name' => (string)$v['name'],
        'price' => (float)$v['price'],
        'attributes' => (array)($v['attributes'] ?? []),
        'available' => (int)($inventory[$vid]['available'] ?? 0),
    ];
}

// Giá trị có thể chọn cho mỗi thuộc tính phiên bản (giữ thứ tự xuất hiện)
$choices = [];
foreach ($variantDefs as $def) {
    $key = (string)$def['key'];
    $vals = [];
    foreach ($variants as $v) {
        $val = (string)($v['attributes'][$key] ?? '');
        if ($val !== '' && !in_array($val, $vals, true)) {
            $vals[] = $val;
        }
    }
    if ($vals) {
        $choices[] = ['key' => $key, 'label' => (string)$def['label'], 'values' => $vals];
    }
}

$formatAttr = static function (array $def, $val): string {
    if (is_array($val)) {
        return implode(', ', $val);
    }
    if (is_bool($val)) {
        return $val ? 'Có' : 'Không';
    }
    return trim($val . ' ' . ($def['unit'] ?? ''));
};

$imgUrl = ProductViewHelper::thumbUrl($product);
$defaultVariant = $variants[0] ?? null;
foreach ($variants as $v) {
    if ($v['available'] > 0) {
        $defaultVariant = $v;
        break;
    }
}
$money = static fn(float $n): string => number_format($n, 0, ',', '.') . ' VND';
$isWishlisted = !empty($isWishlisted);
$productsCssVersion = AssetHelper::getVersion('public/css/frontend/products.css');
$productsJsVersion = AssetHelper::getVersion('public/js/frontend/products.js');
?>

<link rel="stylesheet" href="<?= BASE_URL ?>public/css/frontend/products.css?v=<?= htmlspecialchars($productsCssVersion) ?>">

<section class="bg-slate-50 min-h-screen py-6">
  <div class="max-w-6xl mx-auto px-4">
    <nav class="text-[12px] text-slate-500 mb-4">
      <a href="<?= BASE_URL ?>products?type=accessory" class="hover:text-vfNavy">Phụ kiện</a>
      <span class="mx-1">/</span>
      <a href="<?= BASE_URL ?>products?cat=<?= (int)($product['category_id'] ?? 0) ?>" class="hover:text-vfNavy"><?= htmlspecialchars((string)($product['category_label'] ?? '')) ?></a>
      <span class="mx-1">/</span>
      <span class="text-slate-700"><?= htmlspecialchars((string)$product['name']) ?></span>
    </nav>

    <div class="grid md:grid-cols-2 gap-6 bg-white rounded-lg border border-slate-100 p-4 md:p-6">
      <div class="bg-slate-50 rounded-lg flex items-center justify-center p-4 min-h-[260px]">
        <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars((string)$product['name']) ?>" class="max-h-[360px] w-full object-contain">
      </div>

      <div>
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0 flex-1">
            <p class="text-[11px] font-bold tracking-wider text-vfGold uppercase"><?= htmlspecialchars((string)($product['category_label'] ?? '')) ?></p>
            <h1 class="text-2xl font-bold text-slate-900 mt-1"><?= htmlspecialchars((string)$product['name']) ?></h1>
          </div>
          <button
            type="button"
            id="vfPdWishlistBtn"
            class="vf-pd-wishlist inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-400 shadow-sm transition hover:border-red-200 hover:text-red-500<?= $isWishlisted ? ' is-active' : '' ?>"
            data-wishlist-product="<?= htmlspecialchars((string)$product['product_id']) ?>"
            data-wishlist-variant=""
            data-wishlist-url="<?= BASE_URL ?>wishlist/toggle"
            aria-pressed="<?= $isWishlisted ? 'true' : 'false' ?>"
            aria-label="<?= $isWishlisted ? 'Bỏ khỏi yêu thích' : 'Thêm vào yêu thích' ?>">
            <i data-heart-icon class="<?= $isWishlisted ? 'fa-solid' : 'fa-regular' ?> fa-heart text-base"></i>
          </button>
        </div>
        <p id="accPrice" class="text-vfNavy text-2xl font-extrabold mt-3"><?= $money((float)($defaultVariant['price'] ?? $product['price'] ?? 0)) ?></p>

        <form id="accForm" method="post" action="<?= BASE_URL ?>cart/add" class="mt-5 space-y-4">
          <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
          <input type="hidden" name="product_id" value="<?= htmlspecialchars((string)$product['product_id']) ?>">
          <input type="hidden" id="accVariantId" name="variant_id" value="<?= htmlspecialchars((string)($defaultVariant['id'] ?? '')) ?>">

          <?php foreach ($choices as $choice): ?>
            <div>
              <p class="text-[12px] font-semibold text-slate-600 mb-1.5"><?= htmlspecialchars($choice['label']) ?></p>
              <div class="flex flex-wrap gap-2">
                <?php foreach ($choice['values'] as $val): ?>
                  <?php $isSel = ($defaultVariant['attributes'][$choice['key']] ?? '') === $val; ?>
                  <button type="button" class="js-acc-opt px-3 py-1.5 rounded-md border text-[12px] transition <?= $isSel ? 'border-vfNavy bg-vfNavy text-white' : 'border-slate-200 text-slate-700 hover:border-vfNavy' ?>"
                    data-key="<?= htmlspecialchars($choice['key']) ?>" data-value="<?= htmlspecialchars($val) ?>"><?= htmlspecialchars($val) ?></button>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>

          <?php if (!$choices && count($variants) > 1): ?>
            <div>
              <p class="text-[12px] font-semibold text-slate-600 mb-1.5">Phiên bản</p>
              <select id="accVariantSelect" class="border border-slate-200 rounded-md px-3 py-2 text-sm w-full">
                <?php foreach ($variants as $v): ?>
                  <option value="<?= htmlspecialchars($v['id']) ?>" <?= $v['id'] === ($defaultVariant['id'] ?? '') ? 'selected' : '' ?>><?= htmlspecialchars($v['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          <?php endif; ?>

          <p id="accStock" class="text-[13px] font-semibold"></p>

          <div class="flex items-center gap-3">
            <input type="number" name="quantity" value="1" min="1" max="99" class="w-20 border border-slate-200 rounded-md px-3 py-2 text-sm">
            <button id="accAddBtn" type="submit" disabled title="Giỏ hàng đang được phát triển (Topic 4)"
              class="flex-1 bg-vfNavy text-white rounded-md py-2.5 text-sm font-bold disabled:opacity-50 disabled:cursor-not-allowed">
              <i class="fa-solid fa-cart-plus mr-1"></i> Thêm vào giỏ
            </button>
          </div>
          <p class="text-[11px] text-slate-400">Giỏ hàng và thanh toán phụ kiện sẽ mở trong bản cập nhật tới.</p>
        </form>
      </div>
    </div>

    <div class="grid md:grid-cols-3 gap-6 mt-6">
      <div class="md:col-span-2 bg-white rounded-lg border border-slate-100 p-5">
        <h2 class="text-lg font-bold text-slate-900 mb-2">Mô tả</h2>
        <p class="text-slate-600 text-sm leading-relaxed whitespace-pre-line"><?= htmlspecialchars((string)($product['description'] ?? '')) ?></p>
      </div>
      <div class="bg-white rounded-lg border border-slate-100 p-5">
        <h2 class="text-lg font-bold text-slate-900 mb-2">Thông số</h2>
        <dl class="text-sm divide-y divide-slate-100">
          <?php foreach ($attrDefs as $def): ?>
            <?php $val = $specs[$def['key'] ?? ''] ?? null; if ($val === null || $val === '' || $val === []) continue; ?>
            <div class="py-2 flex justify-between gap-3">
              <dt class="text-slate-500"><?= htmlspecialchars((string)$def['label']) ?></dt>
              <dd class="text-slate-800 font-medium text-right"><?= htmlspecialchars($formatAttr($def, $val)) ?></dd>
            </div>
          <?php endforeach; ?>
        </dl>
      </div>
    </div>

    <div id="danhgia" class="bg-white rounded-lg border border-slate-100 p-5 mt-6">
      <h2 class="text-lg font-bold text-slate-900 mb-4">Đánh giá sản phẩm (<?= (int)($product['total_reviews'] ?? 0) ?>)</h2>
      <?php include ROOT . '/app/views/frontend/products/partials/detail/review-section.php'; ?>
    </div>

    <?php $related = array_values(array_filter($related, static fn($r) => (int)$r['id'] !== (int)$product['id'])); ?>
    <?php if ($related): ?>
      <h2 class="text-lg font-bold text-slate-900 mt-8 mb-3">Cùng danh mục</h2>
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <?php foreach (array_slice($related, 0, 4) as $r): ?>
          <a href="<?= BASE_URL ?>products/detail/<?= (int)$r['id'] ?>" class="bg-white rounded-lg border border-slate-100 p-3 hover:shadow-md transition">
            <img src="<?= htmlspecialchars(ProductViewHelper::thumbUrl($r)) ?>" class="h-28 w-full object-contain" alt="">
            <p class="text-[13px] font-bold text-slate-900 mt-2 truncate"><?= htmlspecialchars((string)$r['name']) ?></p>
            <p class="text-[12px] text-vfGold font-bold"><?= $money((float)$r['price']) ?></p>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<script>
(function () {
  var variants = <?= json_encode($variants, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
  var keys = <?= json_encode(array_column($choices, 'key')) ?>;
  var selected = <?= json_encode((object)($defaultVariant['attributes'] ?? [])) ?>;
  var priceEl = document.getElementById('accPrice');
  var stockEl = document.getElementById('accStock');
  var idInput = document.getElementById('accVariantId');
  var fmt = function (n) { return Math.round(n).toLocaleString('vi-VN') + ' VND'; };

  function current() {
    if (!keys.length) {
      var sel = document.getElementById('accVariantSelect');
      var id = sel ? sel.value : idInput.value;
      return variants.find(function (v) { return v.id === id; }) || variants[0];
    }
    return variants.find(function (v) {
      return keys.every(function (k) { return String(v.attributes[k] || '') === String(selected[k] || ''); });
    });
  }

  function render() {
    var v = current();
    idInput.value = v ? v.id : '';
    priceEl.textContent = v ? fmt(v.price) : 'Không có phiên bản này';
    if (!v) { stockEl.textContent = ''; return; }
    stockEl.textContent = v.available > 0 ? ('Còn ' + v.available + ' sản phẩm') : 'Hết hàng';
    stockEl.className = 'text-[13px] font-semibold ' + (v.available > 0 ? 'text-emerald-600' : 'text-red-500');
    document.querySelectorAll('.js-acc-opt').forEach(function (b) {
      var on = String(selected[b.dataset.key] || '') === b.dataset.value;
      b.className = 'js-acc-opt px-3 py-1.5 rounded-md border text-[12px] transition ' + (on ? 'border-vfNavy bg-vfNavy text-white' : 'border-slate-200 text-slate-700 hover:border-vfNavy');
    });
  }

  document.querySelectorAll('.js-acc-opt').forEach(function (b) {
    b.addEventListener('click', function () {
      selected[b.dataset.key] = b.dataset.value;
      // Nếu tổ hợp không tồn tại, nhảy sang phiên bản đầu tiên có giá trị vừa chọn
      if (!current()) {
        var alt = variants.find(function (v) { return String(v.attributes[b.dataset.key] || '') === b.dataset.value; });
        if (alt) { keys.forEach(function (k) { selected[k] = alt.attributes[k]; }); }
      }
      render();
    });
  });
  var sel = document.getElementById('accVariantSelect');
  if (sel) sel.addEventListener('change', render);
  render();
})();
</script>
<script src="<?= BASE_URL ?>public/js/frontend/products.js?v=<?= htmlspecialchars($productsJsVersion) ?>"></script>
