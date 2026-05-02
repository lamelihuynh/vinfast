<?php
/**
 * app/views/admin/news/show.php
 * Giao diện Xem chi tiết bài viết (Read-only) cho Admin
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

<style>
    .article-content p { margin-bottom: 1.5rem; line-height: 1.8; color: #374151; }
    .article-content figure { margin: 2rem 0; text-align: center; }
    .article-content img { border-radius: 0.75rem; max-width: 100%; height: auto; margin: 0 auto; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
    .article-content figcaption { margin-top: 0.75rem; font-size: 0.875rem; color: #6b7280; font-style: italic; }
</style>

<div class="row">
    <div class="col-12">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Chi tiết bài viết</h2>
            <a href="<?= ADMIN_URL ?>news" class="text-gray-500 hover:text-blue-600 transition font-medium">&larr; Quay lại danh sách</a>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            
            <div class="p-6 md:p-8 border-b border-gray-100 bg-gray-50/50">
                <div class="flex flex-wrap gap-3 mb-5">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                        Danh mục: <?= htmlspecialchars($article['catalog'] ?? 'Chưa phân loại') ?>
                    </span>
                    
                    <?php if (($article['news_state'] ?? '') === 'Hiển thị'): ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Trạng thái: Đã đăng</span>
                    <?php else: ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-200 text-slate-800">Trạng thái: Bản nháp / Đã ẩn</span>
                    <?php endif; ?>
                    
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800">
                        Lượt xem: <?= number_format($article['views'] ?? 0) ?>
                    </span>
                    
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                        Ngày tạo: <?= date('d/m/Y H:i', strtotime($article['created_at'])) ?>
                    </span>
                </div>
                
                <h1 class="text-3xl md:text-4xl font-extrabold font-sans text-gray-900 mb-3 leading-tight"><?= htmlspecialchars($article['title']) ?></h1>
                <p class="text-sm text-gray-500 font-mono bg-gray-100 p-2 rounded inline-block">Slug: <?= htmlspecialchars($article['slug']) ?></p>
            </div>

            <div class="p-6 md:p-10">
                <div class="article-content text-lg">
                    <?= $article['body'] ?>
                </div>
            </div>

            <?php if (!empty($article['tags'])): ?>
            <div class="p-6 md:p-8 border-t border-gray-100 bg-gray-50/30">
                <h3 class="text-sm font-bold text-gray-700 mb-3 uppercase tracking-wider">Từ khóa (Tags)</h3>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($article['tags'] as $tag): ?>
                        <span class="px-4 py-1.5 bg-white border border-gray-200 rounded-full text-sm text-gray-600 shadow-sm font-medium">
                            #<?= htmlspecialchars($tag) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
        
        <div class="mt-6 flex justify-end gap-4">
            <a href="<?= ADMIN_URL ?>news/edit/<?= $article['id'] ?>" class="bg-amber-500 hover:bg-amber-600 text-white px-8 py-3 rounded-xl font-bold shadow-md transition transform hover:-translate-y-0.5">
                Chỉnh sửa bài viết này
            </a>
        </div>
    </div>
</div>