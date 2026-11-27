<?php
/**
 * app/views/admin/comments/partials/reviews-table.php — Bảng kiểm duyệt đánh giá sản phẩm (T1-17)
 * Owner: T1 (Data Storage)
 */
$reviews = isset($reviews) && is_array($reviews) ? $reviews : [];
$productsMap = isset($productsMap) && is_array($productsMap) ? $productsMap : [];
$reviewStatus = isset($reviewStatus) ? (string)$reviewStatus : 'all';
$currentPage = isset($pg) && is_object($pg) ? (int)$pg->current : 1;
?>

<style>
    .vf-rev-icon-btn {
        width: 32px;
        height: 32px;
        padding: 0 !important;
        min-height: 32px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        font-size: 13px;
    }
</style>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 pb-3 border-bottom">
    <div>
        <h4 class="header-title mb-1">Danh sách đánh giá sản phẩm</h4>
        <small class="text-muted">Kiểm duyệt nhận xét từ khách hàng. Đánh giá sau khi duyệt sẽ hiển thị trên trang sản phẩm và tự động cập nhật điểm sao trung bình.</small>
    </div>

    <div class="btn-group btn-group-sm" role="group" aria-label="Lọc trạng thái đánh giá">
        <a href="<?= ADMIN_URL ?>comments?tab=reviews"
           class="btn <?= $reviewStatus === 'all' ? 'btn-primary' : 'btn-outline-secondary' ?>" style="padding: 6px 12px;">
            Tất cả (<?= (int)($reviewCounts['total'] ?? 0) ?>)
        </a>
        <a href="<?= ADMIN_URL ?>comments?tab=reviews&amp;status=pending"
           class="btn <?= $reviewStatus === 'pending' ? 'btn-warning text-dark fw-semibold' : 'btn-outline-secondary' ?>" style="padding: 6px 12px;">
            Chờ duyệt (<?= (int)($reviewCounts['pending'] ?? 0) ?>)
        </a>
        <a href="<?= ADMIN_URL ?>comments?tab=reviews&amp;status=approved"
           class="btn <?= $reviewStatus === 'approved' ? 'btn-success' : 'btn-outline-secondary' ?>" style="padding: 6px 12px;">
            Đã duyệt (<?= (int)($reviewCounts['approved'] ?? 0) ?>)
        </a>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-hover align-middle mb-0">
        <thead class="table-light text-uppercase small">
            <tr>
                <th style="width:70px" class="text-center">ID</th>
                <th style="min-width:180px">Khách hàng</th>
                <th style="min-width:200px">Sản phẩm</th>
                <th style="min-width:260px">Nội dung đánh giá</th>
                <th style="width:110px" class="text-center">Số sao</th>
                <th style="width:120px" class="text-center">Trạng thái</th>
                <th style="width:115px" class="text-center">Ngày gửi</th>
                <th style="width:150px" class="text-center">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($reviews)): ?>
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        <em>Chưa có đánh giá sản phẩm nào trong mục này.</em>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($reviews as $rev): ?>
                    <?php
                    $rid = (int)$rev['id'];
                    $pid = (string)($rev['product_id'] ?? '');
                    $pInfo = $productsMap[$pid] ?? null;
                    $pName = is_array($pInfo) && !empty($pInfo['name']) ? (string)$pInfo['name'] : ('Sản phẩm #' . $pid);
                    $isApproved = !empty($rev['is_approved']);
                    $stars = max(1, min(5, (int)($rev['rating'] ?? 5)));
                    $avatarPath = !empty($rev['author_avatar']) ? ltrim((string)$rev['author_avatar'], '/') : 'public/images/avatars/default/default.jpg';
                    $avatarUrl = BASE_URL . $avatarPath;
                    ?>
                    <tr>
                        <td class="text-center fw-semibold">#<?= $rid ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="" style="width:34px;height:34px;border-radius:50%;object-fit:cover;border:1px solid #e2e8f0;flex-shrink:0;">
                                <div class="min-w-0">
                                    <div class="fw-bold text-dark text-truncate" style="max-width:150px" title="<?= htmlspecialchars((string)($rev['author_name'] ?? '')) ?>">
                                        <?= htmlspecialchars((string)($rev['author_name'] ?? '')) ?>
                                    </div>
                                    <div class="text-muted small text-truncate" style="max-width:150px">
                                        <?= htmlspecialchars((string)($rev['author_email'] ?? '')) ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>products/detail/<?= (int)$pid ?>#danhgia" target="_blank" class="fw-semibold text-decoration-none">
                                <?= htmlspecialchars($pName) ?>
                            </a>
                            <div class="text-muted small">Mã SP: <code><?= htmlspecialchars($pid) ?></code></div>
                        </td>
                        <td>
                            <div class="small text-dark" style="white-space:pre-line;max-height:90px;overflow-y:auto;">
                                <?= htmlspecialchars((string)($rev['body'] ?? '')) ?>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="text-warning" style="font-size:12px">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fa-<?= $i <= $stars ? 'solid' : 'regular' ?> fa-star"></i>
                                <?php endfor; ?>
                            </span>
                            <div class="small text-muted"><?= $stars ?>/5</div>
                        </td>
                        <td class="text-center">
                            <?php if ($isApproved): ?>
                                <span class="badge bg-success">Đã duyệt</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Chờ duyệt</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center small text-muted">
                            <?= !empty($rev['created_at']) ? date('d/m/Y H:i', strtotime((string)$rev['created_at'])) : '—' ?>
                        </td>
                        <td class="text-center text-nowrap">
                            <div class="d-inline-flex align-items-center gap-1">
                                <?php if (!$isApproved): ?>
                                    <form method="POST" action="<?= ADMIN_URL ?>comments/review-approve/<?= $rid ?>" class="d-inline">
                                        <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                                        <input type="hidden" name="return_status" value="<?= htmlspecialchars($reviewStatus) ?>">
                                        <input type="hidden" name="return_page" value="<?= $currentPage ?>">
                                        <button type="submit" class="btn btn-outline-success vf-rev-icon-btn" title="Duyệt hiển thị">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="<?= ADMIN_URL ?>comments/review-reject/<?= $rid ?>" class="d-inline">
                                        <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                                        <input type="hidden" name="return_status" value="<?= htmlspecialchars($reviewStatus) ?>">
                                        <input type="hidden" name="return_page" value="<?= $currentPage ?>">
                                        <button type="submit" class="btn btn-outline-warning vf-rev-icon-btn" title="Ẩn / Bỏ duyệt">
                                            <i class="fa-solid fa-eye-slash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <form method="POST" action="<?= ADMIN_URL ?>comments/review-delete/<?= $rid ?>" class="d-inline"
                                      onsubmit="return confirm('Xóa vĩnh viễn đánh giá #<?= $rid ?>?');">
                                    <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                                    <input type="hidden" name="return_status" value="<?= htmlspecialchars($reviewStatus) ?>">
                                    <input type="hidden" name="return_page" value="<?= $currentPage ?>">
                                    <button type="submit" class="btn btn-outline-danger vf-rev-icon-btn" title="Xóa vĩnh viễn">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$itemName = 'đánh giá sản phẩm';
include ROOT . '/app/views/admin/partials/pagination.php';
?>
