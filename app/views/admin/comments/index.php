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
    <div class="col-lg-12 ">
        <div id="alertContainer" class="mb-4">
            <?php if (!empty($_SESSION['flash'])): ?>
                <div class="alert alert-success fade show shadow-sm srt-alert" role="alert">
                    <strong>Well done!</strong> <?= htmlspecialchars($_SESSION['flash']) ?>
                </div>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>
            <?php if (!empty($_SESSION['errors'])): ?>
                <div class="alert alert-danger fade show shadow-sm srt-alert" role="alert">
                    <strong>Oh snap!</strong> <?= htmlspecialchars($_SESSION['errors'][0]) ?>
                </div>
                <?php unset($_SESSION['errors']); ?>
            <?php endif; ?>
        </div>

        <div class="row mb-4">
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="card text-white bg-primary h-100 shadow-sm" style="border: none;">
                    <div class="card-body p-4">
                        <div class="text-white-50 text-uppercase font-weight-bold mb-2" style="font-size: 13px;">Tổng số bình luận</div>
                        <h2 class="font-weight-bold text-white mb-0" style="font-size: 2.5rem;"><?= (int)$counts['total'] ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="card text-white h-100 shadow-sm" style="background-color: #f59e0b !important; border: none;">
                    <div class="card-body p-4">
                        <div class="text-white-50 text-uppercase font-weight-bold mb-2" style="font-size: 13px;">Chờ duyệt</div>
                        <h2 class="font-weight-bold text-white mb-0" style="font-size: 2.5rem;"><?= (int)$counts['pending'] ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white h-100 shadow-sm" style="background-color: #10b981 !important; border: none;">
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

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <input type="text" id="searchComment" class="form-control" placeholder="Tìm kiếm nội dung đánh giá..." style="max-width: 300px;">
                    <select id="sortComment" class="form-control" style="max-width: 250px; cursor: pointer;">
                        <option value="time_desc">Sắp xếp: Mới nhất</option>
                        <option value="id_asc">Sắp xếp: ID Bài đăng tăng dần</option>
                        <option value="rating">Sắp xếp: Đánh giá phản hồi</option>
                    </select>
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
                            <tbody id="commentTbody">
                                <?php if (empty($comments)): ?>
                                    <tr id="defaultNoDataRow">
                                        <td colspan="8" class="text-center text-muted py-4 font-italic">Chưa có đánh giá nào.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($comments as $cmt): ?>
                                        <tr class="comment-row" 
                                            data-news-id="<?= $cmt['news_id'] ?? 0 ?>" 
                                            data-time="<?= strtotime($cmt['created_at']) ?>"
                                            data-rating="<?= (int)$cmt['rating'] ?>">
                                            
                                            <td class="align-middle">
                                                <input type="checkbox" class="custom-cb comment-cb" value="<?= $cmt['id'] ?>">
                                            </td>

                                            <td class="text-left align-middle" style="max-width: 180px;">
                                                <div class="d-flex align-items-center">
                                                    <?php 
                                                        $avatarPath = !empty($cmt['author_avatar']) ? ltrim($cmt['author_avatar'], '/') : 'public/images/avatars/default/default.jpg';
                                                        $avatarUrl = BASE_URL . $avatarPath;
                                                    ?>
                                                    <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Avatar" class="mr-3" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 1px solid #e2e8f0; flex-shrink: 0;">
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
                                                    <div class="comment-body-text text-muted small mb-2" style="white-space: normal; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.5; word-break: break-all;">
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

                                            <td class="align-middle badge-status-td">
                                                <?php if ($cmt['is_approved']): ?>
                                                    <span class="badge-status" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 500;">
                                                        Đã duyệt
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge-status" style="background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 500;">
                                                        Chờ duyệt
                                                    </span>
                                                <?php endif; ?>
                                            </td>

                                            <td class="align-middle"><?= date('d/m/Y', strtotime($cmt['created_at'])) ?></td>

                                            <td class="align-middle">
                                                <ul class="d-flex justify-content-center align-items-center list-unstyled mb-0 gap-3">
                                                    <?php if (!$cmt['is_approved']): ?>
                                                        <li class="mr-2 approve-btn-container">
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

                <div class="d-flex justify-content-between align-items-center mt-4" id="paginationWrapper">
                    <div class="small text-muted" id="paginationInfo"></div>
                    <ul class="pagination pagination-sm mb-0" id="paginationButtons"></ul>
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
            </div>
            <div class="modal-body text-justify text-secondary" id="modalContent" style="line-height: 1.8; max-height: 60vh; overflow-y: auto; word-wrap: break-word; word-break: break-word;"></div>
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
            <div id="${alertId}" class="alert ${alertClass} fade show shadow-sm" role="alert">
                <strong>${title}</strong> ${message}
            </div>
        `;
        window.scrollTo({top: 0, behavior: 'smooth'});

        setTimeout(() => {
            const el = document.getElementById(alertId);
            if(el) {
                el.classList.remove('show');
                setTimeout(() => el.remove(), 200);
            }
        }, 3000);
    }

    document.querySelectorAll('.srt-alert').forEach(el => {
        setTimeout(() => {
            el.classList.remove('show');
            setTimeout(() => el.remove(), 200);
        }, 3000);
    });

    let currentPage = 1;
    const itemsPerPage = 8;
    let allRows = Array.from(document.querySelectorAll('.comment-row'));
    let noDataRow = document.getElementById('defaultNoDataRow');

    function updateView() {
        const keyword = document.getElementById('searchComment').value.toLowerCase();
        const sortVal = document.getElementById('sortComment').value;
        const tbody = document.getElementById('commentTbody');

        let matchingRows = allRows.filter(row => {
            const text = row.querySelector('.comment-body-text').innerText.toLowerCase();
            return text.includes(keyword);
        });

        matchingRows.sort((a, b) => {
            if (sortVal === 'time_desc') return b.dataset.time - a.dataset.time;
            if (sortVal === 'id_asc') return a.dataset.newsId - b.dataset.newsId;
            if (sortVal === 'rating') return a.dataset.rating - b.dataset.rating;
            return 0;
        });

        const totalItems = matchingRows.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage) || 1;
        if (currentPage > totalPages) currentPage = totalPages;
        
        const start = (currentPage - 1) * itemsPerPage;
        const end = start + itemsPerPage;

        if (totalItems === 0) {
            if (!noDataRow) {
                noDataRow = document.createElement('tr');
                noDataRow.id = 'defaultNoDataRow';
                noDataRow.innerHTML = '<td colspan="8" class="text-center text-muted py-4 font-italic">Không tìm thấy kết quả phù hợp.</td>';
            }
            tbody.appendChild(noDataRow);
            noDataRow.style.display = '';
        } else {
            if (noDataRow && noDataRow.parentNode) noDataRow.remove();
        }

        matchingRows.forEach((row, index) => {
            tbody.appendChild(row); 
            if (index >= start && index < end) {
                row.style.display = ''; 
            } else {
                row.style.display = 'none'; 
            }
        });

        const selectAllCb = document.getElementById('selectAll');
        if (selectAllCb) selectAllCb.checked = false;

        const paginationInfo = document.getElementById('paginationInfo');
        const paginationButtons = document.getElementById('paginationButtons');

        if (paginationInfo && paginationButtons) {
            const currentStart = totalItems === 0 ? 0 : start + 1;
            const currentEnd = Math.min(end, totalItems);
            
            paginationInfo.innerHTML = `Hiển thị <strong>${currentStart} - ${currentEnd}</strong> trên <strong>${totalItems}</strong> mục`;
            
            let html = '';
            html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${currentPage - 1}">Trước</a></li>`;
            for (let i = 1; i <= totalPages; i++) {
                html += `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
            }
            html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${currentPage + 1}">Sau</a></li>`;
            
            paginationButtons.innerHTML = html;
        }
    }

    document.getElementById('paginationButtons').addEventListener('click', function(e) {
        e.preventDefault();
        if(e.target.tagName === 'A') {
            const page = parseInt(e.target.getAttribute('data-page'));
            if(!isNaN(page)) {
                currentPage = page;
                updateView();
            }
        }
    });

    document.getElementById('searchComment').addEventListener('input', function() {
        currentPage = 1;
        updateView();
    });

    document.getElementById('sortComment').addEventListener('change', function() {
        currentPage = 1;
        updateView();
    });

    const selectAllCb = document.getElementById('selectAll');
    if (selectAllCb) {
        selectAllCb.addEventListener('change', function() {
            document.querySelectorAll('.comment-cb').forEach(cb => {
                if(cb.closest('tr').style.display !== 'none') cb.checked = this.checked;
            });
        });
    }

    const csrfToken = document.getElementById('globalCsrf').value;

    async function handleBulkAction(actionPath, confirmMsg, successMsg) {
        const checkedBoxes = Array.from(document.querySelectorAll('.comment-cb:checked'));
        if (checkedBoxes.length === 0) {
            showCustomAlert('danger', 'Lỗi!', 'Vui lòng chọn ít nhất 1 bình luận.');
            return;
        }

        if (!confirm(confirmMsg + ` (${checkedBoxes.length} mục)?`)) return;

        for (let cb of checkedBoxes) {
            let formData = new FormData();
            formData.append('_csrf', csrfToken);
            try {
                const res = await fetch(`<?= ADMIN_URL ?>comment/${actionPath}/${cb.value}`, { method: 'POST', body: formData });
                if(res.ok) {
                    const tr = cb.closest('tr');
                    if (actionPath === 'delete') {
                        allRows = allRows.filter(r => r !== tr);
                        tr.remove();
                    } else if (actionPath === 'approve') {
                        const badge = tr.querySelector('.badge-status');
                        badge.style = "background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 500;";
                        badge.innerText = 'Đã duyệt';
                        tr.querySelector('.approve-btn-container')?.remove();
                    }
                }
            } catch (e) { console.error('Lỗi ID:', cb.value); }
        }

        showCustomAlert('success', 'Thành công!', successMsg);
        updateView(); 
    }

    document.getElementById('btnApproveSelected').addEventListener('click', () => {
        handleBulkAction('approve', 'DUYỆT tất cả bình luận', 'Đã duyệt các bình luận thành công!');
    });

    document.getElementById('btnDeleteSelected').addEventListener('click', () => {
        handleBulkAction('delete', 'XÓA VĨNH VIỄN tất cả bình luận', 'Đã xóa các bình luận thành công!');
    });

    document.getElementById('commentTbody').addEventListener('submit', function(e) {
        if(e.target && e.target.classList.contains('ajax-action-form')) {
            e.preventDefault();
            const form = e.target;
            const confirmMsg = form.getAttribute('data-confirm');
            if (confirmMsg && !confirm(confirmMsg)) return;

            fetch(form.action, { method: 'POST', body: new FormData(form) })
            .then(res => {
                if(res.ok) {
                    const tr = form.closest('tr');
                    const isApprove = form.action.includes('approve');
                    if (isApprove) {
                        const badge = tr.querySelector('.badge-status');
                        badge.style = "background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 500;";
                        badge.innerText = 'Đã duyệt';
                        form.parentElement.remove();
                        showCustomAlert('success', 'Thành công!', 'Đã duyệt bình luận hiển thị lên trang chủ.');
                    } else {
                        allRows = allRows.filter(r => r !== tr);
                        tr.remove();
                        showCustomAlert('success', 'Thành công!', 'Đã xóa bình luận.');
                    }
                    updateView(); 
                }
            })
            .catch(() => showCustomAlert('danger', 'Oh snap!', 'Lỗi kết nối.'));
        }
    });

    updateView();
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