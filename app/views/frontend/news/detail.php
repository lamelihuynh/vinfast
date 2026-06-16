<?php
/**
 * app/views/frontend/news/detail.php
 * Owner  : Nhat Tan 
 * Title  : Article Reader
 *
 * Purpose: Full article: thumbnail, title, author, date, body (echo $article["body"] — sanitise TinyMCE output with appropriate method). Comment list. Comment form (members only, CSRF token required).
 *
 * Variables available (set by controller via View::render):
 *   $article (array), $comments (array)
 *
  Assets    : public/css/frontend/news.css  |  public/js/frontend/comments.js
 *
 * TODO: Replace the placeholder below with the actual HTML implementation.
 * -----------------------------------------------------------------------
 * Rules:
 *  - Always escape output: <?= htmlspecialchars($var) ?>
 *  - Include CSRF in every form: <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
 *  - Include pagination partial where needed: include ROOT."/app/views/frontend/partials/pagination.php"
 */
?>
<!-- TODO: Implement Article Reader -->
<?php
$featuredNews = News::getAll(1, 4, '', '', 'latest', 'Hiển thị');

$reviews = $comments ?? [];
$reviewStats = [
    'avg' => 0,
    'total' => count($reviews),
    'counts' => [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0]
];
if ($reviewStats['total'] > 0) {
    $sum = 0;
    foreach ($reviews as $r) {
        $r_rating = (int)$r['rating'];
        $sum += $r_rating;
        if (isset($reviewStats['counts'][$r_rating])) {
            $reviewStats['counts'][$r_rating]++;
        }
    }
    $reviewStats['avg'] = round($sum / $reviewStats['total'], 1);
}
$sort = $_GET['sort'] ?? 'newest';
?>

<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: { colors: { primary: '#102339', secondary: '#c8a059' } }
        }
    }
</script>

<style>
    .article-body {
        overflow-wrap: anywhere; 
        word-wrap: break-word;
    }
    .article-body p { 
        font-size: 1.125rem; 
        line-height: 1.8; 
        color: #374151; 
        margin-bottom: 1.5rem; 
        text-align: left; 
        overflow-wrap: anywhere; 
        word-wrap: break-word;
    }
    .article-body figure { margin: 2.5rem 0; text-align: center; }
    .article-body figure img { width: 100%; max-height: 600px; object-fit: cover; border-radius: 0.5rem; }
    .article-body figcaption { margin-top: 0.75rem; font-size: 0.875rem; color: #6b7280; font-style: italic; }
</style>

<section class="bg-[#102339] py-8 px-4 w-full">
    <div class="max-w-7xl mx-5">
        <nav class="flex items-center space-x-2 text-sm text-gray-400">
            <a href="<?= BASE_URL ?>" class="hover:text-white transition">Trang chủ</a>
            <span>&rang;</span>
            <a href="<?= BASE_URL ?>news" class="hover:text-white transition">Tin tức</a>
            <span>&rang;</span>
            <span class="text-gray-300 font-medium line-clamp-1"><?= htmlspecialchars($article['catalog']) ?></span>
        </nav>
    </div>
</section>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
        
        <article class="lg:col-span-2">
            
            <header class="mb-8 border-b border-gray-200 pb-6">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 leading-snug mb-4 uppercase">
                    <?= htmlspecialchars($article['title']) ?>
                </h1>
                <div class="flex flex-wrap items-center text-sm text-gray-500 gap-4">
                    <span class="font-bold text-blue-700 bg-blue-50 px-3 py-1 rounded-full uppercase tracking-wider text-xs">
                        <?= htmlspecialchars($article['catalog'] ?? 'Tin tức') ?>
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <?= date('d/m/Y', strtotime($article['created_at'])) ?>
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        <?= number_format($article['views'] ?? 0) ?> lượt xem
                    </span>
                </div>
            </header>

            <div class="article-body">
                <?= $article['body'] ?>
            </div>

            <div class="bg-[#1c2438] rounded-2xl p-6 md:p-8 flex flex-col md:flex-row items-center justify-between gap-8 mb-8 mt-12 text-white">
                <div class="flex flex-col items-center text-center min-w-[120px]">
                  <div class="text-5xl font-black mb-1"><?= $reviewStats['avg'] ?: '0' ?></div>
                  <div class="text-amber-500 text-sm mb-1">
                    <?php 
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= floor($reviewStats['avg'])) echo '<i class="fa-solid fa-star"></i>';
                        elseif ($i == ceil($reviewStats['avg']) && $reviewStats['avg'] > floor($reviewStats['avg'])) echo '<i class="fa-solid fa-star-half-stroke"></i>';
                        else echo '<i class="fa-regular fa-star text-slate-500"></i>';
                    }
                    ?>
                  </div>
                  <div class="text-xs text-slate-400"><?= $reviewStats['total'] ?> bình luận</div>
                </div>

                <div class="flex-1 w-full space-y-2">
                  <?php for ($i = 5; $i >= 1; $i--): 
                    $count = $reviewStats['counts'][$i];
                    $percent = $reviewStats['total'] > 0 ? ($count / $reviewStats['total']) * 100 : 0;
                  ?>
                  <div class="flex items-center gap-3 text-sm text-slate-300">
                    <span class="w-3"><?= $i ?></span>
                    <i class="fa-solid fa-star text-amber-500 text-xs"></i>
                    <div class="flex-1 h-2 bg-slate-700 rounded-full overflow-hidden">
                      <div class="h-full bg-slate-400 rounded-full" style="width: <?= $percent ?>%"></div>
                    </div>
                    <span class="w-4 text-right"><?= $count ?></span>
                  </div>
                  <?php endfor; ?>
                </div>

                <div class="flex flex-col items-center min-w-[150px]">
                  <button type="button" id="btnToggleReviewForm" class="w-full bg-[#c8a059] hover:bg-yellow-600 text-[#102339] font-bold py-3 px-6 rounded-lg transition mb-2">
                    Viết bình luận
                  </button>
                  <p class="text-[10px] text-slate-400 text-center">Chỉ thành viên có thể bình luận</p>
                </div>
            </div>

            <div id="reviewFormContainer" class="hidden bg-white border border-slate-200 rounded-2xl p-6 mb-8 shadow-sm relative">
                <button type="button" id="btnCloseReviewForm" class="absolute top-4 right-4 text-slate-400 hover:text-slate-700">
                  <i class="fa-solid fa-xmark text-xl"></i>
                </button>
                
                <h3 class="text-lg font-bold text-slate-800 mb-6">Viết bình luận cho bài viết này</h3>
                
                <form id="formSubmitReview" action="<?= BASE_URL ?>comment/post" method="POST">
                  <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                  <input type="hidden" name="news_id" value="<?= $article['id'] ?>">
                  <input type="hidden" name="rating" id="inputRating" value="0">

                  <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Đánh giá <span class="text-red-500">*</span></label>
                    <div class="flex gap-2 text-2xl text-slate-300 cursor-pointer" id="starSelector">
                      <i class="fa-solid fa-star hover-star" data-val="1"></i>
                      <i class="fa-solid fa-star hover-star" data-val="2"></i>
                      <i class="fa-solid fa-star hover-star" data-val="3"></i>
                      <i class="fa-solid fa-star hover-star" data-val="4"></i>
                      <i class="fa-solid fa-star hover-star" data-val="5"></i>
                    </div>
                    <p id="starError" class="text-xs text-red-500 mt-1 hidden">Vui lòng chọn số sao.</p>
                  </div>

                  <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Nội dung <span class="text-red-500">*</span> <span class="text-xs text-slate-400 font-normal">(10 - 300 ký tự)</span></label>
                    <textarea name="body" rows="4" placeholder="Chia sẻ suy nghĩ của bạn về bài viết này..." class="w-full border border-slate-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none resize-none"></textarea>
                  </div>

                  <div class="flex gap-4">
                    <button type="button" id="btnCancelReview" class="flex-1 bg-white border border-slate-300 text-slate-700 font-medium py-3 rounded-lg hover:bg-slate-50 transition">Huỷ</button>
                    <button type="submit" class="flex-1 bg-[#8892a0] hover:bg-[#6c7889] text-white font-medium py-3 rounded-lg transition">Gửi bình luận</button>
                  </div>
                </form>
            </div>

            <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-6 border-b border-slate-100 pb-4">
                <div class="flex items-center gap-2 overflow-x-auto w-full md:w-auto pb-2 md:pb-0 scrollbar-hide" id="commentFilters">
                  <span class="text-sm text-slate-500 mr-2 whitespace-nowrap">Lọc:</span>
                  <button data-filter="all" class="filter-btn bg-[#102339] text-white px-4 py-1.5 rounded-full text-xs font-medium whitespace-nowrap transition">
                      Tất cả (<?= $reviewStats['total'] ?>)
                  </button>
                  <?php for($i=5; $i>=1; $i--): if($reviewStats['counts'][$i] > 0): ?>
                    <button data-filter="<?= $i ?>" class="filter-btn border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-1.5 rounded-full text-xs font-medium whitespace-nowrap flex items-center gap-1 transition">
                        <?= $i ?> <i class="fa-solid fa-star text-amber-500"></i> (<?= $reviewStats['counts'][$i] ?>)
                    </button>
                  <?php endif; endfor; ?>
                </div>
            </div>

            <div class="space-y-6" id="commentsList">
                <?php if (empty($reviews)): ?>
                    <p class="text-center text-slate-400 py-10 italic">Chưa có bình luận nào cho bài viết này.</p>
                <?php else: ?>
                    <?php foreach ($reviews as $rv): ?>
                      <article class="comment-item border-b border-slate-100 pb-6 last:border-0" data-rating="<?= (int)$rv['rating'] ?>">
                        <div class="flex items-start justify-between mb-3">
                          <div class="flex items-center gap-3">
                            <?php 
                                $avatarPath = !empty($rv['author_avatar']) ? ltrim($rv['author_avatar'], '/') : 'public/images/avatars/default/default.jpg';
                                $avatarUrl = BASE_URL . $avatarPath;
                            ?>
                            <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Avatar" class="w-10 h-10 rounded-full object-cover border border-slate-200 shadow-sm flex-shrink-0">
                            
                            <div>
                              <p class="m-0 text-sm font-bold text-slate-900"><?= htmlspecialchars($rv['author_name'] ?? $rv['user_name'] ?? 'User') ?></p>
                              <p class="m-0 text-[11px] text-slate-400"><?= date('d/m/Y', strtotime($rv['created_at'])) ?></p>
                            </div>
                          </div>
                          <div class="text-xs text-amber-500 flex gap-0.5" aria-hidden="true">
                            <?php for ($i = 0; $i < (int)$rv['rating']; $i++): ?>
                              <i class="fa-solid fa-star"></i>
                            <?php endfor; ?>
                            <?php for ($i = (int)$rv['rating']; $i < 5; $i++): ?>
                              <i class="fa-regular fa-star text-slate-300"></i>
                            <?php endfor; ?>
                          </div>
                        </div>
                        
                        <p class="text-sm leading-relaxed text-slate-700 mb-4 text-left" style="overflow-wrap: anywhere; word-wrap: break-word;">
                          <?= nl2br(htmlspecialchars($rv['body'])) ?>
                        </p>

                        <div class="flex items-center justify-between">
                          <p class="text-[11px] text-slate-400">Đánh giá có hữu ích không?</p>
                          <button type="button" class="btn-helpful flex items-center gap-1.5 text-xs text-slate-500 hover:text-blue-600 transition" data-id="<?= $rv['id'] ?>">
                            <i class="fa-regular fa-thumbs-up"></i>
                            Hữu ích (<span class="helpful-count"><?= (int)$rv['helpful_count'] ?></span>)
                          </button>
                        </div>
                      </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </article>

        <aside class="space-y-10">
            <div>
                <h3 class="font-bold text-lg mb-6 border-b-2 border-primary inline-block pb-1">Tin tức mới nhất</h3>
                <div class="grid grid-cols-2 lg:grid-cols-1 gap-4">
                    <?php foreach ($featuredNews as $fn): ?>
                        <?php 
                            $thumbUrl = '';
                            $newsId = $fn['id'];
                            $extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                            
                            foreach ($extensions as $ext) {
                                if (file_exists(ROOT . "/public/images/news/{$newsId}/thumbnail.{$ext}")) {
                                    $thumbUrl = BASE_URL . "public/images/news/{$newsId}/thumbnail.{$ext}";
                                    break;
                                }
                            }
                            
                            if ($thumbUrl === '') {
                                foreach ($extensions as $ext) {
                                    if (file_exists(ROOT . "/public/images/news/{$newsId}/1.{$ext}")) {
                                        $thumbUrl = BASE_URL . "public/images/news/{$newsId}/1.{$ext}";
                                        break;
                                    }
                                }
                            }
                            
                            if ($thumbUrl === '') {
                                $dbImage = !empty($fn['img_link']) ? str_replace('\\', '/', $fn['img_link']) : '';
                                $cleanPath = preg_replace('#^/?vinfast/#', '', ltrim($dbImage, '/'));
                                $thumbUrl = !empty($cleanPath) ? BASE_URL . $cleanPath : 'https://via.placeholder.com/300x200?text=VinFast+News';
                            }
                        ?>
                        <a href="<?= BASE_URL ?>news/read/<?= $fn['slug'] ?>" class="group block">
                            <div class="overflow-hidden rounded-lg mb-2 relative aspect-[3/2]">
                                <img src="<?= htmlspecialchars($thumbUrl) ?>" alt="<?= htmlspecialchars($fn['title']) ?>" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                            </div>
                            <h4 class="text-sm font-bold text-gray-800 line-clamp-3 group-hover:text-blue-600 transition leading-snug">
                                <?= htmlspecialchars($fn['title']) ?>
                            </h4>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="bg-gradient-to-br from-[#102339] to-[#1a3a5c] text-white p-8 rounded-2xl shadow-xl relative overflow-hidden">
                <div class="relative z-10">
                    <h3 class="text-2xl font-black mb-3 leading-tight uppercase tracking-wide">Nhận thông tin<br><span class="text-secondary">VinFast</span></h3>
                    <p class="text-sm text-gray-300 mb-6">Đăng ký để không bỏ lỡ tin tức công nghệ xe điện mới nhất.</p>
                    
                    <div id="alert-detail"></div>
                    
                    <form id="form-subscribe-detail" class="space-y-3">
                        <input type="text" id="email-detail" placeholder="Nhập email của bạn..." class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-3 text-sm text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-secondary focus:border-transparent">
                        <button type="submit" class="w-full bg-secondary hover:bg-yellow-600 text-white font-bold py-3 rounded-lg transition shadow-lg tracking-wider">
                            ĐĂNG KÝ NGAY
                        </button>
                    </form>
                </div>
            </div>
        </aside>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const subscribeForm = document.getElementById('form-subscribe-detail');
    if (subscribeForm) {
        subscribeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const emailInput = document.getElementById('email-detail');
            const alertBox = document.getElementById('alert-detail');
            const email = emailInput.value.trim();
            const regex = /^.+@.+\..+$/;
            
            if (!regex.test(email)) {
                alertBox.innerHTML = `<div id="temp-alert-news" class="bg-[#f8d7da] border border-[#f5c6cb] text-[#721c24] px-4 py-2 rounded relative mb-3 text-sm transition-opacity duration-500"><strong>Oh snap!</strong> Định dạng email không hợp lệ. Vui lòng kiểm tra lại.</div>`;
                setTimeout(() => { const n = document.getElementById('temp-alert-news'); if (n) { n.style.opacity = '0'; setTimeout(() => n.remove(), 500); } }, 3000);
                return;
            }

            let formData = new FormData();
            formData.append('email', email);

            fetch('<?= BASE_URL ?>news/subscribe', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alertBox.innerHTML = `<div id="temp-alert-news" class="bg-[#d4edda] border border-[#c3e6cb] text-[#155724] px-4 py-2 rounded relative mb-3 text-sm transition-opacity duration-500"><strong>Hoàn thành!</strong> Bạn đã đăng ký nhận tin thành công!</div>`;
                    emailInput.value = '';
                } else {
                    alertBox.innerHTML = `<div id="temp-alert-news" class="bg-[#f8d7da] border border-[#f5c6cb] text-[#721c24] px-4 py-2 rounded relative mb-3 text-sm transition-opacity duration-500"><strong>Lỗi!</strong> ${data.message}</div>`;
                }
                setTimeout(() => { const n = document.getElementById('temp-alert-news'); if (n) { n.style.opacity = '0'; setTimeout(() => n.remove(), 500); } }, 3000);
            })
            .catch(err => {
                alertBox.innerHTML = `<div id="temp-alert-news" class="bg-[#f8d7da] border border-[#f5c6cb] text-[#721c24] px-4 py-2 rounded relative mb-3 text-sm transition-opacity duration-500"><strong>Lỗi!</strong> Lỗi kết nối máy chủ!</div>`;
                setTimeout(() => { const n = document.getElementById('temp-alert-news'); if (n) { n.style.opacity = '0'; setTimeout(() => n.remove(), 500); } }, 3000);
            });
        });
    }

    const btnToggleForm = document.getElementById('btnToggleReviewForm');
    const formContainer = document.getElementById('reviewFormContainer');
    const btnCloseForm = document.getElementById('btnCloseReviewForm');
    const btnCancel = document.getElementById('btnCancelReview');
    
    function toggleForm() {
        if (formContainer) {
            formContainer.classList.toggle('hidden');
            if (!formContainer.classList.contains('hidden')) formContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    if (btnToggleForm) btnToggleForm.addEventListener('click', toggleForm);
    if (btnCloseForm) btnCloseForm.addEventListener('click', toggleForm);
    if (btnCancel) btnCancel.addEventListener('click', toggleForm);

    const stars = document.querySelectorAll('#starSelector .hover-star');
    const inputRating = document.getElementById('inputRating');
    const starError = document.getElementById('starError');
    
    if (inputRating && parseInt(inputRating.value) > 0) updateStarsUI(parseInt(inputRating.value), false);

    if (stars.length > 0) {
        stars.forEach(star => {
            star.addEventListener('mouseover', function() { updateStarsUI(parseInt(this.getAttribute('data-val')), true); });
            star.addEventListener('mouseout', function() { updateStarsUI(parseInt(inputRating.value), false); });
            star.addEventListener('click', function() {
                const val = parseInt(this.getAttribute('data-val'));
                inputRating.value = val;
                if (starError) starError.classList.add('hidden');
                updateStarsUI(val, false);
                const submitBtn = document.querySelector('#formSubmitReview button[type="submit"]');
                if (submitBtn) {
                    submitBtn.classList.remove('bg-[#8892a0]', 'hover:bg-[#6c7889]');
                    submitBtn.classList.add('bg-[#c8a059]', 'hover:bg-yellow-600');
                }
            });
        });
    }

    function updateStarsUI(val, isHover) {
        stars.forEach(s => {
            if (parseInt(s.getAttribute('data-val')) <= val) {
                s.classList.remove('text-slate-300'); s.classList.add('text-amber-500');
            } else {
                s.classList.remove('text-amber-500'); s.classList.add('text-slate-300');
            }
        });
    }
    
    const reviewForm = document.getElementById('formSubmitReview');
    function showCommentAlert(type, message) {
        let alertBox = document.getElementById('commentAlert');
        if (!alertBox) {
            alertBox = document.createElement('div');
            alertBox.id = 'commentAlert';
            reviewForm.parentNode.insertBefore(alertBox, reviewForm);
        }
        const isSuccess = type === 'success';
        const uniqueId = 'cmt-alert-' + Date.now();
        
        alertBox.innerHTML = `
            <div id="${uniqueId}" class="${isSuccess ? 'bg-[#d4edda] border-[#c3e6cb] text-[#155724]' : 'bg-[#f8d7da] border-[#f5c6cb] text-[#721c24]'} border px-4 py-2 rounded relative mb-3 text-sm transition-opacity duration-500">
                <strong>${isSuccess ? 'Hoàn thành!' : 'Lỗi!'}</strong> ${message}
            </div>
        `;
        alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(() => { const n = document.getElementById(uniqueId); if (n) { n.style.opacity = '0'; setTimeout(() => n.remove(), 500); } }, 3000);
    }

    if (reviewForm) {
        reviewForm.addEventListener('submit', function(e) {
            e.preventDefault(); 
            let isValid = true;
            
            if (!inputRating || parseInt(inputRating.value) === 0) {
                if (starError) starError.classList.remove('hidden');
                showCommentAlert('error', 'Vui lòng chọn số sao đánh giá (từ 1 đến 5 sao).');
                isValid = false;
            }

            const bodyElement = reviewForm.querySelector('textarea[name="body"]');
            if (isValid && bodyElement) {
                const charCount = bodyElement.value.trim().length;
                if (charCount > 300) {
                    showCommentAlert('error', `Bình luận quá dài. Vui lòng viết tối đa 300 ký tự (Bạn đang viết ${charCount} ký tự).`);
                    isValid = false;
                } else if (charCount < 10) {
                    showCommentAlert('error', `Bình luận quá ngắn, vui lòng viết chi tiết hơn (tối thiểu 10 ký tự, bạn đang có ${charCount} ký tự).`);
                    isValid = false;
                }
            }

            if (!isValid) return; 

            const submitBtn = reviewForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerText;
            submitBtn.innerText = 'Đang gửi...'; submitBtn.disabled = true;

            let formData = new FormData(reviewForm);
            fetch(reviewForm.action, { method: 'POST', body: formData, redirect: 'follow' })
            .then(res => {
                if (res.url && res.url.includes('login')) { showCommentAlert('error', 'Bạn cần đăng nhập để có thể gửi bình luận.'); return; }
                if (res.ok) {
                    showCommentAlert('success', 'Đánh giá của bạn đã được gửi và đang chờ quản trị viên duyệt.');
                    if (bodyElement) bodyElement.value = '';
                    inputRating.value = 0; updateStarsUI(0, false);
                    submitBtn.classList.remove('bg-[#c8a059]', 'hover:bg-yellow-600'); submitBtn.classList.add('bg-[#8892a0]', 'hover:bg-[#6c7889]');
                    setTimeout(() => toggleForm(), 3000);
                } else { showCommentAlert('error', 'Có lỗi xảy ra từ hệ thống, vui lòng thử lại sau.'); }
            })
            .catch(err => { showCommentAlert('error', 'Lỗi kết nối máy chủ!'); })
            .finally(() => { submitBtn.innerText = originalText; submitBtn.disabled = false; });
        });
    }

    const filterBtns = document.querySelectorAll('.filter-btn');
    const commentItems = document.querySelectorAll('.comment-item');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => {
                b.classList.remove('bg-[#102339]', 'text-white');
                b.classList.add('border', 'border-slate-200', 'text-slate-600', 'hover:bg-slate-50');
            });
            this.classList.remove('border', 'border-slate-200', 'text-slate-600', 'hover:bg-slate-50');
            this.classList.add('bg-[#102339]', 'text-white');

            const filterValue = this.getAttribute('data-filter');
            commentItems.forEach(item => {
                if (filterValue === 'all' || item.getAttribute('data-rating') === filterValue) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });

    const helpfulBtns = document.querySelectorAll('.btn-helpful');
    const csrfToken = document.querySelector('input[name="_csrf"]').value;

    helpfulBtns.forEach(btn => {
        const commentId = btn.getAttribute('data-id');
        const icon = btn.querySelector('i');
        const countSpan = btn.querySelector('.helpful-count');

        let votedComments = JSON.parse(localStorage.getItem('voted_comments') || '[]');
        if (votedComments.includes(commentId)) {
            btn.classList.add('text-blue-600', 'font-bold');
            icon.classList.remove('fa-regular');
            icon.classList.add('fa-solid');
        }

        btn.addEventListener('click', function() {
            let currentCount = parseInt(countSpan.innerText);
            let isVoting = !votedComments.includes(commentId);

            if (isVoting) {
                currentCount++;
                votedComments.push(commentId);
                btn.classList.add('text-blue-600', 'font-bold');
                icon.classList.remove('fa-regular');
                icon.classList.add('fa-solid');
            } else {
                currentCount--;
                if (currentCount < 0) currentCount = 0; 
                votedComments = votedComments.filter(id => id !== commentId);
                btn.classList.remove('text-blue-600', 'font-bold');
                icon.classList.remove('fa-solid');
                icon.classList.add('fa-regular');
            }

            countSpan.innerText = currentCount;
            localStorage.setItem('voted_comments', JSON.stringify(votedComments));

            let formData = new FormData();
            formData.append('_csrf', csrfToken);
            formData.append('comment_id', commentId);
            formData.append('action', isVoting ? 'like' : 'unlike');

            fetch('<?= BASE_URL ?>comment/vote_helpful', {
                method: 'POST',
                body: formData
            }).catch(err => console.log('Lỗi vote'));
        });
    });
});
</script>
</main>