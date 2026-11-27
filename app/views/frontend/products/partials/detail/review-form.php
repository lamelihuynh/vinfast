<?php
/**
 * app/views/frontend/products/partials/detail/review-form.php — Form gửi đánh giá sản phẩm (T1-16)
 */
$reviewProductId = isset($product['product_id']) && $product['product_id'] !== ''
    ? (string)$product['product_id']
    : (string)($product['id'] ?? 0);
$reviewOld = is_array($_SESSION['review_old'] ?? null) ? $_SESSION['review_old'] : [];
if (($reviewOld['product_id'] ?? '') === $reviewProductId) {
    unset($_SESSION['review_old']);
} else {
    $reviewOld = [];
}
$oldRating = max(1, min(5, (int)($reviewOld['rating'] ?? 5)));
$oldBody = (string)($reviewOld['body'] ?? '');
$loginRedirectUrl = BASE_URL . 'auth/login?redirect=' . urlencode('/products/detail/' . (int)($product['id'] ?? 0) . '#danhgia');
?>
<div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4 md:p-5">
    <h3 class="m-0 mb-3 text-[15px] font-bold text-slate-900">Viết đánh giá của bạn</h3>

    <?php if (Auth::check()): ?>
        <form method="post" action="<?= BASE_URL ?>review/post" class="space-y-4 vf-review-form">
            <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
            <input type="hidden" name="product_id" value="<?= htmlspecialchars($reviewProductId) ?>">
            <input type="hidden" name="rating" value="<?= $oldRating ?>" class="js-review-rating-input">

            <div>
                <label class="mb-1.5 block text-[12.5px] font-semibold text-slate-700">Mức độ hài lòng</label>
                <div class="inline-flex items-center gap-1.5" role="radiogroup" aria-label="Chọn số sao đánh giá">
                    <?php for ($star = 1; $star <= 5; $star++): ?>
                        <button
                            type="button"
                            class="js-review-star border-0 bg-transparent p-1 text-xl transition cursor-pointer <?= $star <= $oldRating ? 'text-amber-400' : 'text-slate-300' ?>"
                            data-star="<?= $star ?>"
                            aria-label="<?= $star ?> sao">
                            <i class="<?= $star <= $oldRating ? 'fa-solid' : 'fa-regular' ?> fa-star"></i>
                        </button>
                    <?php endfor; ?>
                    <span class="ml-2 text-[12.5px] font-medium text-slate-600 js-review-rating-label"><?= $oldRating ?>/5 sao</span>
                </div>
            </div>

            <div>
                <label for="vfReviewBody_<?= htmlspecialchars($reviewProductId) ?>" class="mb-1.5 block text-[12.5px] font-semibold text-slate-700">
                    Nhận xét chi tiết <span class="text-red-500">*</span>
                </label>
                <textarea
                    id="vfReviewBody_<?= htmlspecialchars($reviewProductId) ?>"
                    name="body"
                    rows="4"
                    minlength="10"
                    maxlength="2000"
                    required
                    placeholder="Chia sẻ trải nghiệm thực tế của bạn về sản phẩm (tối thiểu 10 ký tự)..."
                    class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-[13.5px] text-slate-800 focus:border-vfNavy focus:outline-none"><?= htmlspecialchars($oldBody) ?></textarea>
                <p class="mb-0 mt-1 text-[11.5px] text-slate-400">Từ 10 đến 2.000 ký tự. Đánh giá sẽ hiển thị sau khi quản trị viên kiểm duyệt.</p>
            </div>

            <div>
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-vfNavy px-5 py-2.5 text-[13px] font-bold text-white transition hover:bg-vfNavy/90 cursor-pointer border-0">
                    <i class="fa-solid fa-paper-plane text-xs"></i>
                    Gửi đánh giá
                </button>
            </div>
        </form>
    <?php else: ?>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="m-0 text-[13.5px] text-slate-600">Vui lòng đăng nhập tài khoản để gửi đánh giá cho sản phẩm này.</p>
            <a href="<?= htmlspecialchars($loginRedirectUrl) ?>"
               class="inline-flex items-center gap-2 rounded-lg border border-vfNavy bg-white px-4 py-2 text-[13px] font-semibold text-vfNavy no-underline transition hover:bg-vfNavy hover:text-white">
                <i class="fa-solid fa-right-to-bracket text-xs"></i>
                Đăng nhập ngay
            </a>
        </div>
    <?php endif; ?>
</div>
