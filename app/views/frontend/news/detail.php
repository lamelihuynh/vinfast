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
    .article-body p {
        font-size: 1.125rem; 
        line-height: 1.8;
        color: #374151;
        margin-bottom: 1.5rem;
        text-align: justify;
    }

    .article-body figure {
        margin: 2.5rem 0;
        text-align: center;
    }
    .article-body figure img {
        width: 100%;
        max-height: 600px;
        object-fit: cover;
        border-radius: 0.5rem;
    }
    .article-body figcaption {
        margin-top: 0.75rem;
        font-size: 0.875rem;
        color: #6b7280;
        font-style: italic;
    }
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
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <?= date('d/m/Y', strtotime($article['created_at'])) ?>
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        <?= number_format($article['views'] ?? 0) ?> lượt xem
                    </span>
                </div>
            </header>

            <div class="article-body">
                <?= $article['body'] ?>
            </div>

            <?php if (!empty($article['tags'])): ?>
            <div class="mt-10 pt-6 border-t border-gray-100 flex flex-wrap gap-2 items-center">
                <span class="font-bold text-sm text-gray-700 mr-2">Từ khóa:</span>
                <?php foreach ($article['tags'] as $tag): ?>
                    <a href="<?= BASE_URL ?>news?q=<?= urlencode($tag) ?>" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-3 py-1 text-sm rounded transition">
                        #<?= htmlspecialchars($tag) ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </article>

        <aside class="space-y-10">
            
            <div>
                <h3 class="font-bold text-lg mb-6 border-b-2 border-primary inline-block pb-1">Tin tức mới nhất</h3>
                <div class="grid grid-cols-2 lg:grid-cols-1 gap-4">
                    <?php foreach ($featuredNews as $fn): ?>
                        <?php 
                            $thumbnail = $fn['thumbnail'] ?? '';
                            $cleanPath = preg_replace('#^/?vinfast/#', '', ltrim($thumbnail, '/'));
                            $imgUrl = !empty($cleanPath) ? BASE_URL . $cleanPath : 'https://via.placeholder.com/300x200?text=VinFast+News';
                        ?>
                        <a href="<?= BASE_URL ?>news/read/<?= $fn['slug'] ?>" class="group block">
                            <div class="overflow-hidden rounded-lg mb-2 relative aspect-[3/2]">
                                <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($fn['title']) ?>" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                            </div>
                            <h4 class="text-sm font-bold text-gray-800 line-clamp-3 group-hover:text-blue-600 transition leading-snug">
                                <?= htmlspecialchars($fn['title']) ?>
                            </h4>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="bg-gradient-to-br from-[#102339] to-[#1a3a5c] text-white p-8 rounded-2xl shadow-xl relative overflow-hidden">
                <svg class="absolute top-0 right-0 opacity-10 w-32 h-32 transform translate-x-8 -translate-y-8" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path></svg>
                
                <div class="relative z-10">
                    <h3 class="text-2xl font-black mb-3 leading-tight uppercase tracking-wide">Nhận thông tin<br><span class="text-secondary">VinFast</span></h3>
                    <p class="text-sm text-gray-300 mb-6">Đăng ký để không bỏ lỡ các chương trình ưu đãi và tin tức công nghệ xe điện mới nhất.</p>
                    
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
    document.getElementById('form-subscribe-detail').addEventListener('submit', function(e) {
        e.preventDefault();
        const emailInput = document.getElementById('email-detail');
        const alertBox = document.getElementById('alert-detail');
        const email = emailInput.value.trim();

        const regex = /^.+@.+\..+$/;
        
        if (!regex.test(email)) {
            alertBox.innerHTML = `
                <div class="bg-[#f8d7da] border border-[#f5c6cb] text-[#721c24] px-4 py-2 rounded relative mb-3 text-sm">
                    <strong>Lỗi!</strong> Định dạng email không hợp lệ. Vui lòng kiểm tra lại.
                </div>
            `;
            return;
        }

        let formData = new FormData();
        formData.append('email', email);

        fetch('<?= BASE_URL ?>news/subscribe', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alertBox.innerHTML = `
                    <div class="bg-[#d4edda] border border-[#c3e6cb] text-[#155724] px-4 py-2 rounded relative mb-3 text-sm">
                        <strong>Hoàn tất!</strong> Bạn đã đăng ký nhận tin thành công!
                    </div>
                `;
                emailInput.value = '';
            } else {
                alertBox.innerHTML = `<div class="bg-[#f8d7da] border border-[#f5c6cb] text-[#721c24] px-4 py-2 rounded relative mb-3 text-sm"><strong>Oh snap!</strong> ${data.message}</div>`;
            }
        })
        .catch(err => {
            alertBox.innerHTML = `<div class="bg-[#f8d7da] border border-[#f5c6cb] text-[#721c24] px-4 py-2 rounded relative mb-3 text-sm"><strong>Oh snap!</strong> Lỗi kết nối máy chủ!</div>`;
        });
    });
    </script>
</main>

