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
<script src="https://cdn.tailwindcss.com"></script>


<style>
    .collapse {
        visibility: visible !important;
    }
    
    .collapse:not(.show):not(.in) {
        display: none !important;
    }
</style>

<div class="row">
    <div class="col-12 w-full">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Quản lý bình luận</h2>
            <div class="text-sm text-gray-500 mt-1">Trang chủ / Sản phẩm / <span class="text-blue-600 font-medium">Bình luận</span></div>
        </div>

        <?php if (!empty($_SESSION['flash'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm">
                <?= htmlspecialchars($_SESSION['flash']) ?>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-blue-600 text-white p-6 rounded-xl shadow-md">
                <div class="text-sm font-medium opacity-80 mb-1">Tổng số bình luận</div>
                <div class="text-4xl font-black"><?= (int)$counts['total'] ?></div>
            </div>
            <div class="bg-amber-500 text-white p-6 rounded-xl shadow-md">
                <div class="text-sm font-medium opacity-80 mb-1">Chờ duyệt</div>
                <div class="text-4xl font-black"><?= (int)$counts['pending'] ?></div>
            </div>
            <div class="bg-emerald-500 text-white p-6 rounded-xl shadow-md">
                <div class="text-sm font-medium opacity-80 mb-1">Đã duyệt</div>
                <div class="text-4xl font-black"><?= (int)$counts['approved'] ?></div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="overflow-x-auto w-full rounded-2xl">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap">Tác giả</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap">Sản phẩm</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Nội dung</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap">Đánh giá</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap">Trạng thái</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap">Ngày</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <?php if (empty($comments)): ?>
                            <tr><td colspan="7" class="px-6 py-10 text-center text-gray-500 italic">Chưa có đánh giá nào.</td></tr>
                        <?php else: ?>
                            <?php foreach ($comments as $cmt): ?>
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm uppercase shadow-sm">
                                                <?= mb_substr(htmlspecialchars($cmt['author_name']), 0, 1) ?>
                                            </div>
                                            <div>
                                                <div class="text-sm font-bold text-gray-900"><?= htmlspecialchars($cmt['author_name']) ?></div>
                                                <div class="text-xs text-gray-400"><?= htmlspecialchars($cmt['author_email']) ?></div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-600 truncate max-w-[150px]" title="<?= htmlspecialchars($cmt['product_name'] ?? 'Không rõ') ?>">
                                            <?= htmlspecialchars($cmt['product_name'] ?? 'Không rõ') ?>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 min-w-[300px]">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="text-sm text-gray-600 line-clamp-2 w-full leading-relaxed">
                                                <?= htmlspecialchars($cmt['body']) ?>
                                            </div>
                                            <button onclick="openModal(`<?= htmlspecialchars(addslashes(nl2br($cmt['body']))) ?>`)" class="text-blue-500 hover:text-blue-700 text-xs font-medium whitespace-nowrap bg-blue-50 hover:bg-blue-100 px-2 py-1 rounded transition">
                                                Xem chi tiết
                                            </button>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex text-[11px]">
                                            <?php for ($i = 0; $i < (int)$cmt['rating']; $i++): ?>
                                                <i class="fa-solid fa-star text-amber-400 mr-0.5"></i>
                                            <?php endfor; ?>
                                            <?php for ($i = (int)$cmt['rating']; $i < 5; $i++): ?>
                                                <i class="fa-solid fa-star text-gray-200 mr-0.5"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <?php if ($cmt['is_approved']): ?>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-100 text-emerald-700">Đã duyệt</span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-amber-100 text-amber-700">Chờ duyệt</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500"><?= date('d/m/Y', strtotime($cmt['created_at'])) ?></div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end gap-3">
                                            <?php if (!$cmt['is_approved']): ?>
                                                <form action="<?= ADMIN_URL ?>comment/approve/<?= $cmt['id'] ?>" method="POST" title="Duyệt bình luận">
                                                    <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                                                    <button type="submit" class="text-emerald-500 hover:text-emerald-700 transition transform hover:scale-110">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <form action="<?= ADMIN_URL ?>comment/delete/<?= $cmt['id'] ?>" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn XÓA VĨNH VIỄN đánh giá này?');" title="Xóa bình luận">
                                                <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                                                <button type="submit" class="text-gray-400 hover:text-red-600 transition transform hover:scale-110">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
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
        </div>
    </div>
</div>

<div id="detailModal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900 bg-opacity-60 backdrop-blur-sm transition-opacity">
    <div class="bg-white rounded-2xl shadow-2xl w-11/12 max-w-lg overflow-hidden transform transition-all">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">Chi tiết đánh giá</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-700 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="p-6">
            <div id="modalContent" class="text-gray-700 text-sm leading-loose text-justify overflow-y-auto max-h-[60vh]">
                </div>
        </div>
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
            <button onclick="closeModal()" class="bg-[#102339] hover:bg-[#1a3a5c] text-white px-6 py-2 rounded-lg text-sm font-medium transition shadow-sm">
                Đóng
            </button>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('detailModal');
    const modalContent = document.getElementById('modalContent');

    function openModal(content) {
        modalContent.innerHTML = content;
        modal.classList.remove('hidden');
    }

    function closeModal() {
        modal.classList.add('hidden');
    }

    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });
</script>
