<div class="border-t border-slate-100 pt-2">
  <div class="mb-6 flex flex-wrap gap-x-8 gap-y-2 border-b border-slate-200" role="tablist" aria-label="Thông tin sản phẩm">
    <button type="button" class="vf-pd-tab-btn is-active border-0 border-b-2 border-transparent bg-transparent px-0 pb-3 text-[14px] font-medium text-slate-500 transition hover:text-vfNavy" data-tab="mota">Mô tả chi tiết</button>
    <button type="button" class="vf-pd-tab-btn border-0 border-b-2 border-transparent bg-transparent px-0 pb-3 text-[14px] font-medium text-slate-500 transition hover:text-vfNavy" data-tab="thongso">Thông số kỹ thuật</button>
    <button type="button" class="vf-pd-tab-btn border-0 border-b-2 border-transparent bg-transparent px-0 pb-3 text-[14px] font-medium text-slate-500 transition hover:text-vfNavy" data-tab="danhgia">Đánh giá (<?= (int)$reviewCount ?>)</button>
  </div>

  <div>
    <section class="vf-pd-pane is-active pb-8" data-pane="mota">
      <div class="space-y-4 text-[14px] leading-8 text-slate-600">
        <p><?= nl2br(htmlspecialchars($descriptionText)) ?></p>
        <p>Sở hữu hệ thống hỗ trợ lái nâng cao ADAS, kết nối thông minh qua ứng dụng VinFast, và chính sách bảo hành pin 10 năm, đây là người bạn đồng hành thông minh trên mọi cung đường.</p>
      </div>
      <a href="<?= BASE_URL ?>products/checkout/<?= (int)$productId ?>" class="mt-4 inline-flex items-center gap-2 text-[13px] font-semibold text-vfNavy no-underline transition hover:text-vfNavy/80">
        Đặt cọc ngay để nhận xe sớm nhất
        <i class="fa-solid fa-chevron-right"></i>
      </a>
    </section>

    <section class="vf-pd-pane pb-8" data-pane="thongso">
      <div class="grid grid-cols-1 overflow-hidden rounded-xl border border-slate-100 md:grid-cols-2">
        <?php foreach ($specRows as $idx => $row): ?>
          <div class="vf-pd-spec-row flex items-center justify-between gap-4 border-b border-slate-100 px-4 py-3 text-[13px] <?= ($idx % 2 === 0) ? 'is-even bg-slate-50' : '' ?> md:[&:nth-child(2n+1)]:border-r md:[&:nth-last-child(-n+2)]:border-b-0">
            <span class="text-slate-500"><?= htmlspecialchars($row['label']) ?></span>
            <strong class="text-right font-semibold text-slate-900"><?= htmlspecialchars($row['value']) ?></strong>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="vf-pd-pane pb-8" data-pane="danhgia">
      <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
        <?php foreach ($reviewItems as $rv): ?>
          <article class="rounded-xl border border-slate-200 bg-white p-4">
            <div class="mb-2 flex items-start justify-between gap-2">
              <div>
                <p class="m-0 text-[12px] font-bold text-slate-900"><?= htmlspecialchars($rv['name']) ?></p>
                <p class="m-0 text-[10px] text-slate-400"><?= htmlspecialchars($rv['date']) ?></p>
              </div>
              <div class="inline-flex gap-0.5 text-[11px] text-amber-500" aria-hidden="true">
                <?php for ($i = 0; $i < (int)$rv['stars']; $i++): ?>
                  <i class="fa-solid fa-star"></i>
                <?php endfor; ?>
              </div>
            </div>
            <p class="m-0 text-[12px] leading-6 text-slate-600"><?= htmlspecialchars($rv['body']) ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
</div>
