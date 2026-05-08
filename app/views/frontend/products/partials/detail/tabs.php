<?php
$descriptionText = isset($descriptionText) ? (string)$descriptionText : (string)($product['description'] ?? '');
$productId = isset($productId) ? (int)$productId : (int)($product['id'] ?? 0);
$specRows = isset($specRows) && is_array($specRows) ? $specRows : [];
?>
<div class="border-t border-slate-100 pt-2">
  <div class="mb-6 flex flex-wrap gap-x-8 gap-y-2 border-b border-slate-200" role="tablist" aria-label="Thông tin sản phẩm">
    <button type="button" class="vf-pd-tab-btn is-active border-0 border-b-2 border-transparent bg-transparent px-0 pb-3 text-[14px] font-medium text-slate-500 transition hover:text-vfNavy" data-tab="mota">Mô tả chi tiết</button>
    <button type="button" class="vf-pd-tab-btn border-0 border-b-2 border-transparent bg-transparent px-0 pb-3 text-[14px] font-medium text-slate-500 transition hover:text-vfNavy" data-tab="thongso">Thông số kỹ thuật</button>
  </div>

  <div>
    <section class="vf-pd-pane is-active pb-8" data-pane="mota">
      <div class="space-y-4 text-[14px] leading-8 text-slate-600">
        <p><?= nl2br(htmlspecialchars($descriptionText)) ?></p>
      </div>
      <a href="<?= BASE_URL ?>order/checkout/<?= (int)$productId ?><?= !empty($defaultColor['code']) ? ('?color=' . urlencode((string)$defaultColor['code'])) : '' ?>" data-checkout-base="<?= BASE_URL ?>order/checkout/<?= (int)$productId ?>" class="vf-pd-checkout-link mt-4 inline-flex items-center gap-2 text-[13px] font-semibold text-vfNavy no-underline transition hover:text-vfNavy/80">
        Đặt cọc ngay để nhận xe sớm nhất
        <i class="fa-solid fa-chevron-right"></i>
      </a>
    </section>

    <section class="vf-pd-pane pb-8" data-pane="thongso">
      <div class="grid grid-cols-1 overflow-hidden rounded-xl border border-slate-100 md:grid-cols-2">
        <?php $rowIndex = 0; ?>
        <?php foreach ($specRows as $row): ?>
          <?php if (!is_array($row)) continue; ?>
          <div class="vf-pd-spec-row flex items-center justify-between gap-4 border-b border-slate-100 px-4 py-3 text-[13px] <?= ($rowIndex % 2 === 0) ? 'is-even bg-slate-50' : '' ?> md:[&:nth-child(2n+1)]:border-r md:[&:nth-last-child(-n+2)]:border-b-0">
            <span class="text-slate-500"><?= htmlspecialchars((string)($row['label'] ?? '')) ?></span>
            <strong class="text-right font-semibold text-slate-900"><?= htmlspecialchars((string)($row['value'] ?? '')) ?></strong>
          </div>
          <?php $rowIndex++; ?>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
</div>