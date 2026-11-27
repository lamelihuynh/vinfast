<?php
/**
 * app/views/frontend/products/partials/detail/review-list.php — Danh sách các thẻ đánh giá (T1-16)
 * Dùng chung cho:
 *   1. 5 bài đánh giá nhúng sẵn trong Mongo `products.reviews[]` khi tải trang lần đầu.
 *   2. Các trang tiếp theo tải qua AJAX từ PostgreSQL (`ReviewController::list`).
 */
$reviews = isset($reviews) && is_array($reviews) ? $reviews : [];

foreach ($reviews as $rev):
    if (!is_array($rev)) {
        continue;
    }
    $revId = (int)($rev['id'] ?? 0);
    $author = trim((string)($rev['author_name'] ?? 'Khách hàng'));
    if ($author === '') {
        $author = 'Khách hàng';
    }
    $initial = function_exists('mb_substr')
        ? mb_strtoupper(mb_substr($author, 0, 1, 'UTF-8'), 'UTF-8')
        : strtoupper(substr($author, 0, 1));
    $rating = max(1, min(5, (int)($rev['rating'] ?? 5)));
    $bodyText = (string)($rev['body'] ?? '');
    $createdRaw = (string)($rev['created_at_raw'] ?? $rev['created_iso'] ?? $rev['created_at'] ?? '');
    $createdTs = !empty($rev['created_at']) ? strtotime((string)$rev['created_at']) : false;
    $dateDisplay = $createdTs !== false ? date('d/m/Y H:i', $createdTs) : (string)($rev['created_at'] ?? '');
?>
<article
    class="vf-review-item rounded-xl border border-slate-100 bg-white p-4 shadow-sm"
    data-review-id="<?= $revId ?>"
    data-created-at="<?= htmlspecialchars($createdRaw) ?>">
    <div class="flex items-start justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-50 text-sm font-bold text-vfNavy">
                <?= htmlspecialchars($initial) ?>
            </div>
            <div class="min-w-0">
                <p class="m-0 truncate text-[14px] font-semibold text-slate-900"><?= htmlspecialchars($author) ?></p>
                <div class="mt-0.5 flex items-center gap-1 text-[12px] text-amber-400" aria-label="<?= $rating ?> trên 5 sao">
                    <?php for ($s = 1; $s <= 5; $s++): ?>
                        <i class="<?= $s <= $rating ? 'fa-solid' : 'fa-regular' ?> fa-star"></i>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
        <?php if ($dateDisplay !== ''): ?>
            <time class="shrink-0 text-[12px] text-slate-400"><?= htmlspecialchars($dateDisplay) ?></time>
        <?php endif; ?>
    </div>
    <p class="mb-0 mt-3 text-[13.5px] leading-relaxed text-slate-600 whitespace-pre-line"><?= htmlspecialchars($bodyText) ?></p>
</article>
<?php endforeach; ?>
