<?php
/**
 * app/views/admin/news/index.php
 * Owner  : Nhat Tan (Member 4)
 * Title  : News List
 *
 * Purpose: Search bar. Paginated table: thumbnail, title, author, date, edit/delete actions.
 *
 * Variables available (set by controller via View::render):
 *   $articles (array), $q (string), $pg (Pagination)
 *
 *
 * -----------------------------------------------------------------------
 * Rules:
 *  - Always escape output: <?= htmlspecialchars($var) ?>
 *  - Include CSRF in every form: <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
 *  - Include pagination partial where needed: include ROOT."/app/views/frontend/partials/pagination.php"
 */
?>
<?php
    $totalNews     = News::count();
    $publishedNews = News::count('', '', 'Hiển thị');
    $hiddenNews    = News::count('', '', 'Ẩn');

    $current_page = $pg->current ?? 1;
    $total_items  = $pg->total ?? $totalNews;
    $start_item   = ($current_page - 1) * 10 + 1;
    $end_item     = min($start_item + 9, $total_items);
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
    <div class="col-12">
        
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Quản lý tin tức</h2>
                <div class="text-sm text-gray-500 mt-1">Trang chủ / <span class="text-blue-600">Bài viết</span></div>
            </div>
            <a href="<?= ADMIN_URL ?>news/create" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-medium flex items-center gap-2 transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Thêm bài viết
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-blue-600 rounded-2xl p-6 text-white shadow-md relative overflow-hidden">
                <div class="relative z-10">
                    <div class="text-blue-100 text-sm font-medium mb-1">Tổng bài viết</div>
                    <div class="text-4xl font-bold"><?= number_format($totalNews) ?></div>
                </div>
            </div>

            <div class="bg-emerald-500 rounded-2xl p-6 text-white shadow-md relative overflow-hidden">
                <div class="relative z-10">
                    <div class="text-emerald-100 text-sm font-medium mb-1">Đã đăng</div>
                    <div class="text-4xl font-bold"><?= number_format($publishedNews) ?></div>
                </div>
            </div>

            <div class="bg-slate-600 rounded-2xl p-6 text-white shadow-md relative overflow-hidden">
                <div class="relative z-10">
                    <div class="text-slate-200 text-sm font-medium mb-1">Đã ẩn</div>
                    <div class="text-4xl font-bold"><?= number_format($hiddenNews) ?></div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            
            <div class="p-5 border-b border-gray-100 bg-gray-50/50">
                <form action="<?= ADMIN_URL ?>news" method="GET" class="flex gap-4 items-center">
                    <div class="relative w-full max-w-md">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input type="text" name="q" value="<?= htmlspecialchars($q ?? '') ?>" placeholder="Tìm tiêu đề..." class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    </div>
                    <button type="submit" class="bg-white border border-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">Tìm kiếm</button>
                    <?php if (!empty($q)): ?>
                        <a href="<?= ADMIN_URL ?>news" class="text-sm text-red-500 hover:underline">Xóa lọc</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                            <th class="px-6 py-4 font-semibold">Bài viết</th>
                            <th class="px-6 py-4 font-semibold">Danh mục</th>
                            <th class="px-6 py-4 font-semibold text-center">Lượt xem</th>
                            <th class="px-6 py-4 font-semibold">Trạng thái</th>
                            <th class="px-6 py-4 font-semibold">Ngày tạo</th>
                            <th class="px-6 py-4 font-semibold text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($articles)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                    Không tìm thấy bài viết nào phù hợp.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($articles as $art): ?>
                                <tr class="hover:bg-gray-50/50 transition group">
                                    
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <?php 
                                                $thumbnail = !empty($art['thumbnail']) ? $art['thumbnail'] : (!empty($art['img_link']) ? $art['img_link'] : '');
                                                // Tự động làm sạch đường dẫn trước khi nối BASE_URL
                                                $cleanPath = preg_replace('#^/?vinfast/#', '', ltrim($thumbnail, '/'));
                                                $imgUrl = !empty($cleanPath) ? BASE_URL . $cleanPath : 'https://via.placeholder.com/150x100?text=No+Image';
                                            ?>
                                            <img src="<?= htmlspecialchars($imgUrl) ?>" alt="Thumbnail" class="w-16 h-12 object-cover rounded-md border border-gray-200">
                                            
                                            <div class="max-w-xs">
                                                <div class="text-sm font-bold text-gray-900 line-clamp-1" title="<?= htmlspecialchars($art['title']) ?>">
                                                    <?= htmlspecialchars($art['title']) ?>
                                                </div>
                                                <div class="text-xs text-gray-500 line-clamp-1 mt-0.5">
                                                    <?= htmlspecialchars($art['slug']) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                            <?= htmlspecialchars($art['catalog'] ?? 'Chưa phân loại') ?>
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <span class="text-sm text-gray-600 font-medium"><?= number_format((int)($art['views'] ?? 0)) ?></span>
                                    </td>

                                    <td class="px-6 py-4">
                                        <?php if (($art['news_state'] ?? '') === 'Hiển thị'): ?>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-100">Đã đăng</span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">Bản nháp</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="px-6 py-4">
                                        <span class="text-sm text-gray-600">
                                            <?= date('d/m/Y', strtotime($art['created_at'])) ?>
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-3 transition-opacity">
                                            
                                            <a href="<?= ADMIN_URL ?>news/show/<?= $art['id'] ?>" class="text-gray-400 hover:text-blue-600 transition" title="Xem chi tiết">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            </a>

                                            <a href="<?= ADMIN_URL ?>news/edit/<?= $art['id'] ?>" class="text-gray-400 hover:text-amber-500 transition" title="Chỉnh sửa">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </a>

                                            <form action="<?= ADMIN_URL ?>news/delete/<?= $art['id'] ?>" method="POST" class="inline-block" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài viết này không? Hành động này không thể hoàn tác!');">
                                                <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                                                <button type="submit" class="text-gray-400 hover:text-red-600 transition cursor-pointer" title="Xóa bài viết">
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

            <?php if ($total_items > 0): ?>
            <div class="p-5 border-t border-gray-100 bg-white flex items-center justify-between">
                <div class="text-sm text-gray-500">
                    Hiển thị <span class="font-bold text-gray-900"><?= $start_item ?>-<?= $end_item ?></span> / <span class="font-bold text-gray-900"><?= $total_items ?></span> kết quả
                </div>
                
                <?php if (isset($pg) && $pg->pages > 1): ?>
                    <div class="flex space-x-1">
                        <?php if ($pg->current > 1): ?>
                            <a href="?page=<?= $pg->current - 1 ?>&q=<?= urlencode($q) ?>" class="px-3 py-1 border border-gray-200 rounded text-sm text-gray-600 hover:bg-gray-50">Trước</a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $pg->pages; $i++): ?>
                            <a href="?page=<?= $i ?>&q=<?= urlencode($q) ?>" class="px-3 py-1 border rounded text-sm <?= $i === $pg->current ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-200 text-gray-600 hover:bg-gray-50' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($pg->current < $pg->pages): ?>
                            <a href="?page=<?= $pg->current + 1 ?>&q=<?= urlencode($q) ?>" class="px-3 py-1 border border-gray-200 rounded text-sm text-gray-600 hover:bg-gray-50">Sau</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>
