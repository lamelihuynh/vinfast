<?php
/**
 * app/views/frontend/products/partials/detail/review-section.php — Khối hiển thị tóm tắt + 5 review nhúng + nút Xem thêm + Form (T1-16)
 * Đọc hoàn toàn từ mảng `$product` của Mongo ở lần tải trang đầu tiên (0 query sang bảng PG `product_reviews`).
 */
$product = isset($product) && is_array($product) ? $product : [];
$revProductIdStr = (string)(($product['product_id'] ?? '') !== '' ? $product['product_id'] : ($product['id'] ?? 0));
$avgRating = round((float)($product['avg_rating'] ?? 0), 2);
$totalReviews = max(0, (int)($product['total_reviews'] ?? 0));
$embeddedReviews = is_array($product['reviews'] ?? null) ? $product['reviews'] : [];
$shownCount = count($embeddedReviews);
$roundedStars = (int)round($avgRating);
?>
<div class="space-y-6">
    <!-- Tổng quan điểm đánh giá -->
    <div class="flex flex-wrap items-center gap-6 rounded-xl border border-slate-100 bg-slate-50 p-4 md:p-5">
        <div class="flex items-baseline gap-2">
            <span class="text-4xl font-extrabold text-slate-900"><?= number_format($avgRating, 1, ',', '.') ?></span>
            <span class="text-sm font-medium text-slate-500">/ 5</span>
        </div>
        <div>
            <div class="flex items-center gap-1 text-base text-amber-400" aria-hidden="true">
                <?php for ($s = 1; $s <= 5; $s++): ?>
                    <i class="<?= $s <= $roundedStars ? 'fa-solid' : 'fa-regular' ?> fa-star"></i>
                <?php endfor; ?>
            </div>
            <p class="m-0 mt-1 text-[13px] text-slate-600">
                <?= $totalReviews > 0
                    ? ('Dựa trên <strong>' . number_format($totalReviews, 0, ',', '.') . '</strong> đánh giá đã duyệt')
                    : 'Chưa có đánh giá nào được duyệt' ?>
            </p>
        </div>
    </div>

    <!-- Danh sách đánh giá (5 bài đầu từ Mongo, các trang sau nối thêm từ PG) -->
    <?php if ($shownCount > 0): ?>
        <div id="vfReviewList" class="space-y-3">
            <?php
            $reviews = $embeddedReviews;
            include ROOT . '/app/views/frontend/products/partials/detail/review-list.php';
            ?>
        </div>

        <?php if ($totalReviews > $shownCount): ?>
            <div class="text-center">
                <button
                    type="button"
                    id="vfLoadMoreReviewsBtn"
                    data-url="<?= htmlspecialchars(BASE_URL . 'review/list/' . $revProductIdStr) ?>"
                    data-next-page="2"
                    data-shown="<?= $shownCount ?>"
                    data-total="<?= $totalReviews ?>"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-[13px] font-semibold text-slate-700 transition hover:border-vfNavy hover:text-vfNavy cursor-pointer">
                    <span>Xem thêm đánh giá (còn <?= number_format($totalReviews - $shownCount, 0, ',', '.') ?>)</span>
                    <i class="fa-solid fa-chevron-down text-xs"></i>
                </button>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="rounded-xl border border-dashed border-slate-200 bg-white p-6 text-center text-[13.5px] text-slate-500">
            Sản phẩm này hiện chưa có đánh giá nào. Hãy là người đầu tiên chia sẻ cảm nhận của bạn!
        </div>
    <?php endif; ?>

    <!-- Form gửi đánh giá mới -->
    <?php include ROOT . '/app/views/frontend/products/partials/detail/review-form.php'; ?>
</div>
