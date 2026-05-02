<?php
/**
 * app/views/admin/news/form.php
 * Owner  : Nhat Tan
 * Title  : News Article Editor
 *
 * Purpose: Title input (also feeds slug). Body textarea with TinyMCE WYSIWYG. Thumbnail upload. SEO section: meta_title, meta_description. Hidden id for edit. CSRF. POSTs to /admin/news/save.
 *
 * Variables available (set by controller via View::render):
 *   $article (array|null)
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
    $isEdit = !empty($article['id']);
    $formTitle = $isEdit ? 'Chỉnh sửa bài viết' : 'Thêm bài viết mới';
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
            <h2 class="text-2xl font-bold text-gray-800"><?= $formTitle ?></h2>
            <a href="<?= ADMIN_URL ?>news" class="text-gray-500 hover:text-blue-600 transition">&larr; Quay lại danh sách</a>
        </div>
        
        <form id="newsBuilderForm" class="flex flex-col lg:flex-row gap-6">
            <input type="hidden" id="csrfToken" name="_csrf" value="<?= Auth::csrfToken() ?>">
            <input type="hidden" id="newsId" name="id" value="<?= $article['id'] ?? 0 ?>">
            
            <div class="flex-1 space-y-6">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <label class="block font-bold text-gray-700 mb-2">Tiêu đề bài viết <span class="text-red-500">*</span></label>
                    <input type="text" id="newsTitle" name="title" required 
                           value="<?= htmlspecialchars($article['title'] ?? '') ?>" 
                           placeholder="Nhập tiêu đề..." 
                           class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500">
                </div>

                <div id="blocksContainer" class="space-y-4">
                    </div>

                <div class="flex gap-4 p-4 border-2 border-dashed border-gray-300 rounded-xl bg-gray-50 justify-center">
                    <button type="button" onclick="addTextBlock()" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded shadow-sm hover:bg-gray-100 font-medium transition">+ Thêm Đoạn Văn</button>
                    <button type="button" onclick="addImageBlock()" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded shadow-sm hover:bg-gray-100 font-medium transition">+ Thêm Hình Ảnh</button>
                </div>
            </div>

            <div class="w-full lg:w-1/3 space-y-6">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <h3 class="font-bold text-gray-800 mb-4 border-b pb-2">Cài đặt</h3>
                    
                    <div class="mb-4">
                        <label class="block text-sm text-gray-600 mb-1">Danh mục</label>
                        <select id="newsCatalog" name="catalog" class="w-full border rounded p-2">
                            <?php foreach (News::CATALOGS as $cat): ?>
                                <option value="<?= $cat ?>" <?= (($article['catalog'] ?? '') === $cat) ? 'selected' : '' ?>>
                                    <?= $cat ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm text-gray-600 mb-1">Trạng thái</label>
                        <select id="newsState" name="news_state" class="w-full border rounded p-2">
                            <?php foreach (News::STATES as $state): ?>
                                <option value="<?= $state ?>" <?= (($article['news_state'] ?? 'Hiển thị') === $state) ? 'selected' : '' ?>>
                                    <?= $state === 'Hiển thị' ? 'Hiển thị (Đăng ngay)' : 'Bản nháp (Ẩn)' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition shadow-md">
                        Lưu Bài Viết
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    const container = document.getElementById('blocksContainer');
    const rawBody = <?= json_encode($article['body'] ?? '') ?>;
    const existingImages = <?= json_encode($article['images'] ?? []) ?>;

    /**
     * BỘ GIẢI MÃ (PARSER): Tự động chia Block khi load trang Edit
     */
    function initBlocks() {
        if (!rawBody) {
            addTextBlock(); 
            return;
        }

        if (rawBody.includes('<p') || rawBody.includes('<figure')) {
            const segments = rawBody.split(/\n\n/);
            segments.forEach(segment => {
                const trimmed = segment.trim();
                if (trimmed.startsWith('<figure')) {
                    const imgData = existingImages.shift();
                    if (imgData) addImageBlock(imgData.img_link, imgData.img_des);
                } else if (trimmed.startsWith('<p')) {
                    const textContent = trimmed.replace(/<p[^>]*>/g, "").replace(/<\/p>/g, "").replace(/<br\s*\/?>/g, "\n");
                    addTextBlock(textContent);
                }
            });
        } else {
            const paragraphs = rawBody.split(/\n\s*\n/);
            paragraphs.forEach(p => {
                if (p.trim()) addTextBlock(p.trim());
            });
            existingImages.forEach(img => addImageBlock(img.img_link, img.img_des));
        }
    }

    function addTextBlock(content = "") {
        const blockHTML = `
            <div class="block-item bg-white p-5 rounded-xl shadow-sm border border-l-4 border-l-blue-500 relative group" data-type="text">
                <div class="flex justify-between items-center mb-3">
                    <span class="font-bold text-sm text-blue-600 uppercase tracking-wider">Đoạn văn</span>
                    <div class="space-x-2">
                        <button type="button" onclick="moveUp(this)" class="text-gray-400 hover:text-gray-800">▲</button>
                        <button type="button" onclick="moveDown(this)" class="text-gray-400 hover:text-gray-800">▼</button>
                        <button type="button" onclick="this.closest('.block-item').remove()" class="text-red-400 hover:text-red-600 ml-2">✕ Xóa</button>
                    </div>
                </div>
                <textarea class="block-content w-full border border-gray-200 rounded p-3 min-h-[100px] focus:ring-1 focus:ring-blue-500" placeholder="Nhập nội dung đoạn văn...">${content}</textarea>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', blockHTML);
    }

    function addImageBlock(url = "", desc = "") {
        const imgPreview = url ? `<img src="${url}" class="h-32 object-cover mb-3 rounded-lg border border-gray-200">` : '';
        
        const blockHTML = `
            <div class="block-item bg-white p-5 rounded-xl shadow-sm border border-l-4 border-l-emerald-500 relative group" data-type="image">
                <div class="flex justify-between items-center mb-3">
                    <span class="font-bold text-sm text-emerald-600 uppercase tracking-wider">Hình ảnh</span>
                    <div class="space-x-2">
                        <button type="button" onclick="moveUp(this)" class="text-gray-400 hover:text-gray-800">▲</button>
                        <button type="button" onclick="moveDown(this)" class="text-gray-400 hover:text-gray-800">▼</button>
                        <button type="button" onclick="this.closest('.block-item').remove()" class="text-red-400 hover:text-red-600 ml-2">✕ Xóa</button>
                    </div>
                </div>
                ${imgPreview}
                <input type="file" accept="image/*" class="block-file mb-3 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                <input type="hidden" class="block-old-link" value="${url}">
                <input type="text" class="block-desc w-full border border-gray-200 rounded p-2 text-sm focus:ring-1 focus:ring-emerald-500" value="${desc}" placeholder="Nhập mô tả hình ảnh (không bắt buộc)...">
            </div>
        `;
        container.insertAdjacentHTML('beforeend', blockHTML);
    }

    function moveUp(btn) {
        const block = btn.closest('.block-item');
        if (block.previousElementSibling) block.parentNode.insertBefore(block, block.previousElementSibling);
    }
    function moveDown(btn) {
        const block = btn.closest('.block-item');
        if (block.nextElementSibling) block.parentNode.insertBefore(block.nextElementSibling, block);
    }

    document.getElementById('newsBuilderForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        let formData = new FormData();
        formData.append('_csrf', document.getElementById('csrfToken').value);
        formData.append('id', document.getElementById('newsId').value);
        formData.append('title', document.getElementById('newsTitle').value);
        formData.append('catalog', document.getElementById('newsCatalog').value);
        formData.append('news_state', document.getElementById('newsState').value);
        
        const blocks = document.querySelectorAll('.block-item');
        blocks.forEach((block, index) => {
            const type = block.dataset.type;
            formData.append(`blocks[${index}][type]`, type);

            if (type === 'text') {
                formData.append(`blocks[${index}][content]`, block.querySelector('.block-content').value);
            } else if (type === 'image') {
                formData.append(`blocks[${index}][desc]`, block.querySelector('.block-desc').value);
                formData.append(`blocks[${index}][old_link]`, block.querySelector('.block-old-link').value); 
                
                const fileInput = block.querySelector('.block-file');
                if (fileInput.files.length > 0) {
                    formData.append(`block_files[${index}]`, fileInput.files[0]);
                }
            }
        });

        try {
            const response = await fetch('/vinfast/admin/news/save_builder', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            if(result.status === 'success') {
                alert('Lưu bài viết thành công!');
                window.location.href = '/vinfast/admin/news';
            } else {
                alert('Lỗi: ' + result.message);
            }
        } catch (error) {
            console.error(error);
            alert('Lỗi kết nối máy chủ!');
        }
    });
    initBlocks();
</script>
