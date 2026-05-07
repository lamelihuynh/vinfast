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

    $safeBody = json_encode(
        (string)($article['body'] ?? ''), 
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_INVALID_UTF8_SUBSTITUTE
    ) ?: '""';

    $safeImages = json_encode(
        array_values($article['images'] ?? []), 
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_INVALID_UTF8_SUBSTITUTE
    ) ?: '[]';
?>

<script src="https://cdn.tailwindcss.com"></script>

<style>
    .collapse { visibility: visible !important; }
    .collapse:not(.show):not(.in) { display: none !important; }
</style>

<div class="row">
    <div class="col-12">
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-2xl font-bold text-gray-800"><?= $formTitle ?></h2>
            <a href="<?= ADMIN_URL ?>news" class="text-gray-500 hover:text-blue-600 transition">&larr; Quay lại danh sách</a>
        </div>
        
        <div id="alertContainer" class="mb-4"></div>
        
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
    
    const rawBody = <?= $safeBody ?>;
    const existingImages = <?= $safeImages ?>;

    function showCustomAlert(type, title, message) {
        const alertContainer = document.getElementById('alertContainer');
        const formattedMessage = message.replace(/\n/g, '<br>');
        
        alertContainer.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <strong>${title}</strong> ${formattedMessage}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;
        alertContainer.scrollIntoView({ behavior: 'smooth', block: 'end' });
    }

    function decodeHTMLEntities(text) {
        const textArea = document.createElement('textarea');
        textArea.innerHTML = text;
        return textArea.value;
    }

    /**
     * PARSER 
     */
    function initBlocks() {
        if (!rawBody) {
            addTextBlock(); 
            return;
        }

        const parser = new DOMParser();
        const doc = parser.parseFromString(rawBody, 'text/html');
        const nodes = doc.body.childNodes;
        let hasBlocks = false;

        nodes.forEach(node => {
            if (node.nodeType === Node.ELEMENT_NODE) {
                if (node.tagName.toLowerCase() === 'p') {
                    let textContent = node.innerHTML.replace(/<br\s*\/?>\r?\n/gi, "\n").replace(/<br\s*\/?>/gi, "\n");
                    addTextBlock(decodeHTMLEntities(textContent.trim()));
                    hasBlocks = true;
                } else if (node.tagName.toLowerCase() === 'figure') {
                    const imgData = existingImages.shift();
                    if (imgData) {
                        addImageBlock(imgData.img_link, imgData.img_des);
                    } else {
                        const img = node.querySelector('img');
                        const caption = node.querySelector('figcaption');
                        addImageBlock(img ? img.getAttribute('src') : '', caption ? caption.textContent : '');
                    }
                    hasBlocks = true;
                }
            }
        });

        if (!hasBlocks) {
            let textContent = rawBody.replace(/<br\s*\/?>\r?\n/gi, "\n").replace(/<br\s*\/?>/gi, "\n");
            addTextBlock(decodeHTMLEntities(textContent.trim()));
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
                <textarea class="block-content w-full border border-gray-200 rounded p-3 min-h-[100px] focus:ring-1 focus:ring-blue-500 transition" placeholder="Nhập nội dung đoạn văn (Tối thiểu 10 ký tự)..."></textarea>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', blockHTML);
        container.lastElementChild.querySelector('.block-content').value = content;
    }

    function addImageBlock(url = "", desc = "") {
        let displayUrl = '';
        let fileNameDisplay = '';
        
        if (url) {
            const safeUrl = String(url).replace(/\\\\/g, '/').replace(/\\/g, '/');
            displayUrl = safeUrl.startsWith('http') ? safeUrl : '<?= BASE_URL ?>' + safeUrl;
            const fileName = safeUrl.split('/').pop();
            
            fileNameDisplay = `
                <div class="text-sm text-gray-600 mb-2 font-medium flex items-center bg-gray-50 p-2 rounded border border-gray-200">
                    <svg class="w-4 h-4 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg> 
                    Đang sử dụng hình: <span class="text-emerald-700 font-bold ml-1">${fileName}</span>
                </div>
            `;
        }

        const imgPreview = displayUrl ? `<img src="${displayUrl}" class="h-40 w-auto object-contain mb-3 rounded-lg border border-gray-200 shadow-sm">` : '';
        
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
                ${fileNameDisplay}
                ${imgPreview}
                <div class="mb-3">
                    <label class="block text-xs text-gray-500 font-medium mb-1">${url ? 'Tải ảnh mới lên (để thay thế ảnh hiện tại)' : 'Chọn ảnh để tải lên'}</label>
                    <input type="file" accept="image/*" class="block-file block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer transition">
                </div>
                <input type="hidden" class="block-old-link" value="${url}">
                <input type="text" class="block-desc w-full border border-gray-200 rounded p-2 text-sm focus:ring-1 focus:ring-emerald-500" placeholder="Nhập mô tả hình ảnh (không bắt buộc)...">
            </div>
        `;
        container.insertAdjacentHTML('beforeend', blockHTML);
        container.lastElementChild.querySelector('.block-desc').value = desc || "";
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
        
        document.getElementById('alertContainer').innerHTML = '';
        
        let isValid = true;
        let errorMessages = [];
        let textBlockIndex = 0;
        let imageBlockIndex = 0;
        let firstErrorElement = null;

        const blocks = document.querySelectorAll('.block-item');
        
        blocks.forEach((block) => {
            const type = block.dataset.type;

            if (type === 'text') {
                textBlockIndex++;
                const contentNode = block.querySelector('.block-content');
                const textLength = contentNode.value.trim().length; 
                
                contentNode.classList.remove('border-red-500', 'ring-1', 'ring-red-500');

                if (textLength < 10) {
                    isValid = false;
                    contentNode.classList.add('border-red-500', 'ring-1', 'ring-red-500');
                    errorMessages.push(`- Đoạn văn thứ ${textBlockIndex}: Nội dung phải có ít nhất 10 ký tự (Đang có: ${textLength}).`);
                    if (!firstErrorElement) firstErrorElement = contentNode;
                }
            } 
            else if (type === 'image') {
                imageBlockIndex++;
                const fileInput = block.querySelector('.block-file');
                fileInput.classList.remove('border-red-500', 'border');

                if (fileInput.files.length > 0) {
                    const file = fileInput.files[0];
                    if (!file.type.startsWith('image/')) {
                        isValid = false;
                        fileInput.classList.add('border-red-500', 'border', 'rounded');
                        errorMessages.push(`- Hình ảnh thứ ${imageBlockIndex}: File tải lên không hợp lệ. Chỉ chấp nhận các định dạng hình ảnh.`);
                        if (!firstErrorElement) firstErrorElement = fileInput;
                    }
                }
            }
        });

        if (!isValid) {
            showCustomAlert('danger', 'Oh snap!', "Vui lòng sửa các lỗi sau đây:\n\n" + errorMessages.join("\n"));
            if (firstErrorElement) {
                setTimeout(() => {
                    firstErrorElement.focus();
                    firstErrorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 300);
            }
            return;
        }

        let formData = new FormData();
        formData.append('_csrf', document.getElementById('csrfToken').value);
        formData.append('id', document.getElementById('newsId').value);
        formData.append('title', document.getElementById('newsTitle').value);
        formData.append('catalog', document.getElementById('newsCatalog').value);
        formData.append('news_state', document.getElementById('newsState').value);
        
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
                showCustomAlert('success', 'Well done!', 'Lưu bài viết thành công! Đang chuyển hướng...');
                setTimeout(() => {
                    window.location.href = '/vinfast/admin/news';
                }, 1500);
            } else {
                showCustomAlert('danger', 'Oh snap!', 'Lỗi từ máy chủ: ' + result.message);
            }
        } catch (error) {
            console.error(error);
            showCustomAlert('danger', 'Oh snap!', 'Lỗi kết nối máy chủ!');
        }
    });
    
    initBlocks();
</script>
