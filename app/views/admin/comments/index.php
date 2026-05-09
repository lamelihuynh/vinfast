<?php
/**
 * app/views/admin/comments/index.php
 * Owner  : Nhat Tan 
 * Title  : Comment Moderation
 *
 * Purpose: Paginated table: user, source (article or product link), comment excerpt, status badge (approved/pending), approve/delete actions. CSRF on both actions.
 *
 * Variables available (set by controller via View::render):
 *   $comments (array), $pg (Pagination)
 *
 *
 * -----------------------------------------------------------------------
 * Rules:
 *  - Always escape output: <?= htmlspecialchars($var) ?>
 *  - Include CSRF in every form: <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
 *  - Include pagination partial where needed: include ROOT."/app/views/frontend/partials/pagination.php"
 */
?>
<style>
    .custom-cb { width: 18px; height: 18px; cursor: pointer; }
</style>

<div class="row">
    <div class="col-lg-12 mt-5">
        
        <div class="mb-4">
            <h4 class="header-title mb-1">Quản lý bình luận</h4>
            <div class="text-muted small">Trang chủ / Tin tức / <span class="text-primary font-weight-bold">Bình luận</span></div>
        </div>

        <div id="alertContainer" class="mb-4">
            <?php if (!empty($_SESSION['flash'])): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm srt-alert" role="alert">
                    <strong>Well done!</strong> <?= htmlspecialchars($_SESSION['flash']) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>
            <?php if (!empty($_SESSION['errors'])): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm srt-alert" role="alert">
                    <strong>Oh snap!</strong> <?= htmlspecialchars($_SESSION['errors'][0]) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['errors']); ?>
            <?php endif; ?>
        </div>

        <div class="row mb-4">
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="card text-white bg-primary h-100 shadow-sm" style="border-radius: 8px; border: none;">
                    <div class="card-body p-4">
                        <div class="text-white-50 text-uppercase font-weight-bold mb-2" style="font-size: 13px;">Tổng số bình luận</div>
                        <h2 class="font-weight-bold text-white mb-0" style="font-size: 2.5rem;"><?= (int)$counts['total'] ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="card text-white h-100 shadow-sm" style="background-color: #f59e0b !important; border-radius: 8px; border: none;">
                    <div class="card-body p-4">
                        <div class="text-white-50 text-uppercase font-weight-bold mb-2" style="font-size: 13px;">Chờ duyệt</div>
                        <h2 class="font-weight-bold text-white mb-0" style="font-size: 2.5rem;"><?= (int)$counts['pending'] ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white h-100 shadow-sm" style="background-color: #10b981 !important; border-radius: 8px; border: none;">
                    <div class="card-body p-4">
                        <div class="text-white-50 text-uppercase font-weight-bold mb-2" style="font-size: 13px;">Đã duyệt</div>
                        <h2 class="font-weight-bold text-white mb-0" style="font-size: 2.5rem;"><?= (int)$counts['approved'] ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm" style="border-radius: 8px; border: none;">
            <div class="card-body">
                
                <div class="d-flex justify-content-between align-items-center mb-4 border-b pb-3">
                    <h4 class="header-title mb-0">Danh sách đánh giá</h4>
                    <div class="d-flex gap-2">
                        <button type="button" id="btnApproveSelected" class="btn btn-success btn-sm d-flex align-items-center gap-1 shadow-sm">
                            <i class="ti-check"></i> Duyệt đã chọn
                        </button>
                        <button type="button" id="btnDeleteSelected" class="btn btn-danger btn-sm d-flex align-items-center gap-1 shadow-sm ml-2">
                            <i class="ti-trash"></i> Xóa đã chọn
                        </button>
                    </div>
                </div>

                <input type="hidden" id="globalCsrf" value="<?= Auth::csrfToken() ?>">

                <div class="single-table">
                    <div class="table-responsive">
                        <table class="table table-bordered text-center align-middle">
                            <thead class="text-uppercase bg-light">
                                <tr>
                                    <th scope="col" style="width: 40px;">
                                        <input type="checkbox" id="selectAll" class="custom-cb">
                                    </th>
                                    <th scope="col" class="text-left" style="min-width: 180px;">Tác giả</th>
                                    <th scope="col" style="min-width: 160px;">Bài đăng</th>
                                    <th scope="col" class="text-left" style="min-width: 250px;">Nội dung</th>
                                    <th scope="col" style="min-width: 100px;">Đánh giá</th>
                                    <th scope="col" style="min-width: 120px;">Trạng thái</th>
                                    <th scope="col" style="min-width: 100px;">Ngày</th>
                                    <th scope="col" style="min-width: 100px;">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($comments)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4 font-italic">Chưa có đánh giá nào.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($comments as $cmt): ?>
                                        <tr>
                                            <td class="align-middle">
                                                <input type="checkbox" class="custom-cb comment-cb" value="<?= $cmt['id'] ?>">
                                            </td>

                                            <td class="text-left align-middle" style="max-width: 180px;">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary text-white d-flex align-items-center justify-content-center font-weight-bold mr-3" style="width: 35px; height: 35px; border-radius: 50%; font-size: 14px; flex-shrink: 0;">
                                                        <?= mb_substr(htmlspecialchars($cmt['author_name']), 0, 1) ?>
                                                    </div>
                                                    <div style="min-width: 0; flex: 1;">
                                                        <div class="font-weight-bold text-dark text-truncate" title="<?= htmlspecialchars($cmt['author_name']) ?>">
                                                            <?= htmlspecialchars($cmt['author_name']) ?>
                                                        </div>
                                                        <div class="text-muted small text-truncate">
                                                            <?= htmlspecialchars($cmt['author_email']) ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="align-middle" style="max-width: 160px;">
                                                <div class="font-weight-bold text-primary mb-1">ID: <?= htmlspecialchars($cmt['news_id'] ?? 'N/A') ?></div>
                                                <div class="text-truncate text-muted small" title="<?= htmlspecialchars($cmt['news_title'] ?? 'Không rõ') ?>">
                                                    <?= htmlspecialchars($cmt['news_title'] ?? 'Không rõ') ?>
                                                </div>
                                            </td>

                                            <td class="text-left align-middle" style="max-width: 250px;">
                                                <div class="d-flex flex-column align-items-start">
                                                    <div class="text-muted small mb-2" style="white-space: normal; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.5;">
                                                        <?= htmlspecialchars($cmt['body']) ?>
                                                    </div>
                                                    <button type="button" onclick="openModal(`<?= htmlspecialchars(addslashes(nl2br($cmt['body']))) ?>`)" class="btn btn-xs btn-outline-primary" style="font-size: 11px; padding: 2px 8px;">
                                                        Xem chi tiết
                                                    </button>
                                                </div>
                                            </td>

                                            <td class="align-middle">
                                                <div class="text-warning" style="font-size: 12px;">
                                                    <?php for ($i = 0; $i < (int)$cmt['rating']; $i++): ?><i class="fa fa-star"></i><?php endfor; ?>
                                                    <?php for ($i = (int)$cmt['rating']; $i < 5; $i++): ?><i class="fa fa-star text-light"></i><?php endfor; ?>
                                                </div>
                                            </td>

                                            <td class="align-middle">
                                                <?php if ($cmt['is_approved']): ?>
                                                    <span style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 500;">
                                                        Đã duyệt
                                                    </span>
                                                <?php else: ?>
                                                    <span style="background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 500;">
                                                        Chờ duyệt
                                                    </span>
                                                <?php endif; ?>
                                            </td>

                                            <td class="align-middle"><?= date('d/m/Y', strtotime($cmt['created_at'])) ?></td>

                                            <td class="align-middle">
                                                <ul class="d-flex justify-content-center align-items-center list-unstyled mb-0 gap-3">
                                                    <?php if (!$cmt['is_approved']): ?>
                                                        <li class="mr-2">
                                                            <form action="<?= ADMIN_URL ?>comment/approve/<?= $cmt['id'] ?>" method="POST" class="d-inline ajax-action-form" title="Duyệt bình luận">
                                                                <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                                                                <button type="submit" class="text-success border-0 bg-transparent p-0" style="cursor: pointer;">
                                                                    <i class="ti-check" style="font-size: 18px;"></i>
                                                                </button>
                                                            </form>
                                                        </li>
                                                    <?php endif; ?>
                                                    
                                                    <li>
                                                        <form action="<?= ADMIN_URL ?>comment/delete/<?= $cmt['id'] ?>" method="POST" class="d-inline ajax-action-form" data-confirm="Bạn có chắc chắn muốn XÓA VĨNH VIỄN đánh giá này?" title="Xóa bình luận">
                                                            <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                                                            <button type="submit" class="text-danger border-0 bg-transparent p-0" style="cursor: pointer;">
                                                                <i class="ti-trash" style="font-size: 18px;"></i>
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 8px; border: none;">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold">Chi tiết đánh giá</h5>
                <button type="button" class="close" data-dismiss="modal" onclick="closeModal()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-justify text-secondary" id="modalContent" style="line-height: 1.8; max-height: 60vh; overflow-y: auto;"></div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="closeModal()">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function showCustomAlert(type, title, message) {
        const container = document.getElementById('alertContainer');
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertId = 'alert-' + Date.now();
        
        container.innerHTML = `
            <div id="${alertId}" class="alert ${alertClass} alert-dismissible fade show shadow-sm" role="alert">
                <strong>${title}</strong> ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close" onclick="this.parentElement.remove()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;
        window.scrollTo({top: 0, behavior: 'smooth'});

        setTimeout(() => {
            const el = document.getElementById(alertId);
            if(el) {
                el.classList.remove('show');
                setTimeout(() => el.remove(), 200);
            }
        }, 5000);
    }

    document.querySelectorAll('.srt-alert').forEach(el => {
        setTimeout(() => {
            el.classList.remove('show');
            setTimeout(() => el.remove(), 200);
        }, 5000);
    });

    const selectAllCb = document.getElementById('selectAll');
    const commentCbs = document.querySelectorAll('.comment-cb');

    if (selectAllCb) {
        selectAllCb.addEventListener('change', function() {
            commentCbs.forEach(cb => cb.checked = this.checked);
        });
    }

    commentCbs.forEach(cb => {
        cb.addEventListener('change', function() {
            if (!this.checked) selectAllCb.checked = false;
            else if (document.querySelectorAll('.comment-cb:checked').length === commentCbs.length) selectAllCb.checked = true;
        });
    });

    const csrfToken = document.getElementById('globalCsrf').value;

    async function handleBulkAction(actionPath, confirmMsg, successMsg) {
        const selectedIds = Array.from(document.querySelectorAll('.comment-cb:checked')).map(cb => cb.value);
        
        if (selectedIds.length === 0) {
            showCustomAlert('danger', 'Lỗi!', 'Vui lòng chọn ít nhất 1 bình luận để thực hiện thao tác.');
            return;
        }

        if (!confirm(confirmMsg + ` (${selectedIds.length} mục)?`)) return;

        for (let id of selectedIds) {
            let formData = new FormData();
            formData.append('_csrf', csrfToken);
            try {
                await fetch(`<?= ADMIN_URL ?>comment/${actionPath}/${id}`, {
                    method: 'POST', body: formData
                });
            } catch (e) { console.error('Lỗi khi thao tác ID:', id); }
        }

        showCustomAlert('success', 'Well done!', successMsg);
        setTimeout(() => location.reload(), 1500);
    }

    document.getElementById('btnApproveSelected').addEventListener('click', () => {
        handleBulkAction('approve', 'Bạn có chắc chắn muốn DUYỆT tất cả bình luận đã chọn', 'Đã duyệt các bình luận thành công!');
    });

    document.getElementById('btnDeleteSelected').addEventListener('click', () => {
        handleBulkAction('delete', 'Bạn có chắc chắn muốn XÓA VĨNH VIỄN tất cả bình luận đã chọn', 'Đã xóa các bình luận thành công!');
    });

    document.querySelectorAll('.ajax-action-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const confirmMsg = this.getAttribute('data-confirm');
            if (confirmMsg && !confirm(confirmMsg)) return;

            let formData = new FormData(this);
            fetch(this.action, { method: 'POST', body: formData })
            .then(() => {
                const isApprove = this.action.includes('approve');
                showCustomAlert('success', 'Thành công!', isApprove ? 'Đã duyệt bình luận hiển thị lên trang chủ.' : 'Đã xóa bình luận.');
                setTimeout(() => location.reload(), 1000);
            })
            .catch(() => showCustomAlert('danger', 'Oh snap!', 'Lỗi kết nối đến máy chủ.'));
        });
    });
});

function openModal(content) {
    document.getElementById('modalContent').innerHTML = content;
    if (typeof $ !== 'undefined') $('#detailModal').modal('show');
    else {
        const m = document.getElementById('detailModal');
        m.classList.add('show'); m.style.display = 'block'; m.style.backgroundColor = 'rgba(0,0,0,0.5)';
    }
}
function closeModal() {
    if (typeof $ !== 'undefined') $('#detailModal').modal('hide');
    else {
        const m = document.getElementById('detailModal');
        m.classList.remove('show'); m.style.display = 'none';
    }
}
</script>