<?php
/**
 * app/views/frontend/news/index.php
 * Owner  : Nhat Tan (Member 4)
 * Title  : News Listing
 *
 * Purpose: Search bar (GET ?q=). Article cards: thumbnail, title, date, excerpt, "Read More" link. Pagination.
 *
 * Variables available (set by controller via View::render):
 *   $articles (array), $q (string), $pg (Pagination)
 *
  Assets    : public/css/frontend/news.css
 *
 * TODO: Replace the placeholder below with the actual HTML implementation.
 * -----------------------------------------------------------------------
 * Rules:
 *  - Always escape output: <?= htmlspecialchars($var) ?>
 *  - Include CSRF in every form: <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
 *  - Include pagination partial where needed: include ROOT."/app/views/frontend/partials/pagination.php"
 */
?>
<!-- TODO: Implement News Listing -->
<?php
$host = 'localhost';
$db   = 'vinfast_db';
$user = 'root';
$pass = '';

$newsArray = [];
$tagsArray = [];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $stmtNews = $pdo->query("
        SELECT n.id, n.title, n.slug, n.catalog, n.created_at, n.body, i.img_link
        FROM news n
        LEFT JOIN news_img_info i ON n.id = i.news_id
        WHERE n.news_state NOT LIKE '%Ẩn%'
        GROUP BY n.id
        ORDER BY n.created_at DESC
    ");
    $rawNews = $stmtNews->fetchAll();

    foreach ($rawNews as $row) {
        $cleanBody = strip_tags($row['body']);
        $newsArray[] = [
            'id'      => $row['id'],
            'slug'    => $row['slug'], 
            'catalog' => $row['catalog'] ?? 'Tin tức',
            'date'    => explode(' ', $row['created_at'])[0],
            'title'   => $row['title'],
            'desc'    => mb_substr($cleanBody, 0, 150) . '...',
            'image'   => $row['img_link'] ?: 'https://via.placeholder.com/600x350/E5E7EB/9CA3AF?text=No+Image'
        ];
    }

    $stmtTags = $pdo->query("SELECT DISTINCT tags FROM news_tags LIMIT 15");
    $tagsArray = $stmtTags->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    error_log("Lỗi Database: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tin tức & Sự kiện</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { primary: '#102339', secondary: '#c8a059' } } }
        }
    </script>
</head>
<body class="bg-white text-gray-800 font-sans">

    <section class="bg-[#102339] py-5 px-4 sm:px-6 md:py-16 lg:px-12 w-full">
        <div class="max-w-7xl mx-5">
            <nav class="flex items-center space-x-2 text-sm sm:text-base mb-6">
                <a href="/vinfast" class="flex items-center text-gray-400 hover:text-white transition-colors">
                    <svg class="w-4 h-4 mr-1.5 pb-[2px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Trang chủ
                </a>
                <span class="text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </span>
                <span class="text-gray-500 font-medium">Tin tức & Sự kiện</span>
            </nav>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold text-white mb-3">Tin tức & Sự kiện</h1>
            <p class="text-gray-400 text-sm sm:text-base md:text-lg">Cập nhật những tin tức mới nhất từ VinFast toàn cầu</p>
        </div>
    </section>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 grid grid-cols-1 lg:grid-cols-3 gap-10">
        
        <div class="lg:col-span-2">
            <div class="flex flex-wrap gap-2 mb-8" id="filterMenu">
                <button data-filter="Tất cả" class="filter-btn bg-[#102339] text-white px-5 py-2 rounded-full text-sm font-medium transition">Tất cả</button>
                <button data-filter="Công ty" class="filter-btn bg-gray-100 text-gray-600 hover:bg-gray-200 px-5 py-2 rounded-full text-sm font-medium transition">Công ty</button>
                <button data-filter="Ô tô điện" class="filter-btn bg-gray-100 text-gray-600 hover:bg-gray-200 px-5 py-2 rounded-full text-sm font-medium transition">Ô tô điện</button>
                <button data-filter="Xe máy điện" class="filter-btn bg-gray-100 text-gray-600 hover:bg-gray-200 px-5 py-2 rounded-full text-sm font-medium transition">Xe máy điện</button>
            </div>

            <div id="news-grid" class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-10"></div>

            <div class="mt-12 flex justify-center gap-4">
                <button id="btn-prev" class="hidden border border-gray-300 text-gray-700 px-6 py-2.5 rounded-full text-sm font-medium hover:bg-gray-50 transition">&larr; Trang trước</button>
                <button id="btn-next" class="hidden border border-gray-300 text-gray-700 px-6 py-2.5 rounded-full text-sm font-medium hover:bg-gray-50 transition">Xem thêm tin tức &rarr;</button>
            </div>
        </div>

        <aside class="space-y-10">
            <div>
                <h3 class="font-bold text-lg mb-4">Tìm kiếm</h3>
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="Tìm kiếm từ khóa" class="w-full border border-gray-300 rounded-lg pl-4 pr-10 py-2.5 text-sm focus:outline-none focus:border-primary">
                    <svg class="w-4 h-4 text-gray-400 absolute right-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <div>
                <h3 class="font-bold text-lg mb-4">Danh mục</h3>
                <ul class="space-y-3 text-sm text-gray-600" id="sidebarMenu">
                    <li data-filter="Tất cả" class="sidebar-btn flex justify-between items-center cursor-pointer hover:text-primary transition">
                        <span class="flex items-center before:content-['•'] before:text-[#c8a059] before:mr-2 before:text-xl font-bold text-[#102339]">Tất cả</span> 
                        <span id="count-all" class="bg-[#102339] text-white text-xs rounded-full w-6 h-6 flex items-center justify-center">0</span>
                    </li>
                    <li data-filter="Công ty" class="sidebar-btn flex justify-between items-center pl-4 cursor-pointer hover:text-primary transition">
                        <span class="flex items-center before:content-['•'] before:text-transparent before:mr-2 before:text-xl">Công ty</span> 
                        <span id="count-company" class="bg-gray-100 text-xs rounded-full w-6 h-6 flex items-center justify-center">0</span>
                    </li>
                    <li data-filter="Ô tô điện" class="sidebar-btn flex justify-between items-center pl-4 cursor-pointer hover:text-primary transition">
                        <span class="flex items-center before:content-['•'] before:text-transparent before:mr-2 before:text-xl">Ô tô điện</span> 
                        <span id="count-oto" class="bg-gray-100 text-xs rounded-full w-6 h-6 flex items-center justify-center">0</span>
                    </li>
                    <li data-filter="Xe máy điện" class="sidebar-btn flex justify-between items-center pl-4 cursor-pointer hover:text-primary transition">
                        <span class="flex items-center before:content-['•'] before:text-transparent before:mr-2 before:text-xl">Xe máy điện</span> 
                        <span id="count-xemay" class="bg-gray-100 text-xs rounded-full w-6 h-6 flex items-center justify-center">0</span>
                    </li>
                </ul>
            </div>

            <div>
                <h3 class="font-bold text-lg mb-4">Tin tức nổi bật</h3>
                <div id="featured-news" class="grid grid-cols-2 gap-4"></div>
            </div>
            
            <div class="bg-[#102339] text-white p-6 rounded-xl shadow-lg">
                <h3 class="font-bold text-lg mb-2">Đăng ký nhận tin</h3>
                <p class="text-sm text-gray-300 mb-4 line-clamp-2">Nhận ngay tin tức mới nhất từ VinFast qua email của bạn.</p>
                
                <div id="alert-index"></div>
                
                <form id="form-subscribe-index" class="space-y-3">
                    <input type="text" id="email-index" placeholder="Email của bạn" class="w-full bg-[#1a3350] border border-[#2a4565] rounded px-4 py-2 text-sm text-white placeholder-gray-400 focus:outline-none focus:border-[#c8a059]">
                    <button type="submit" class="w-full bg-[#c8a059] hover:bg-yellow-600 text-white font-medium py-2 rounded transition">Đăng ký &rarr;</button>
                </form>
            </div>
        </aside>
    </main>

    <script>
        const newsDB = <?= json_encode($newsArray, JSON_UNESCAPED_UNICODE) ?>;
        const tagsDB = <?= json_encode($tagsArray, JSON_UNESCAPED_UNICODE) ?>;

        const ITEMS_PER_PAGE = 8;
        let currentPage = 1;
        
        let currentFilter = 'Tất cả';
        let currentSearchQuery = '';

        function getFilteredNews() {
            return newsDB.filter(news => {
                const matchFilter = (currentFilter === 'Tất cả' || news.catalog === currentFilter);
                const matchSearch = news.title.toLowerCase().includes(currentSearchQuery.toLowerCase());
                
                return matchFilter && matchSearch;
            });
        }

        function updateCategoryCounts() {
            document.getElementById('count-all').innerText = newsDB.length;
            document.getElementById('count-company').innerText = newsDB.filter(n => n.catalog === 'Công ty').length;
            document.getElementById('count-oto').innerText = newsDB.filter(n => n.catalog === 'Ô tô điện').length;
            document.getElementById('count-xemay').innerText = newsDB.filter(n => n.catalog === 'Xe máy điện').length;
        }

        function renderNews() {
            const grid = document.getElementById('news-grid');
            grid.innerHTML = ''; 
            
            const filteredData = getFilteredNews();

            if (filteredData.length === 0) {
                grid.innerHTML = '<p class="text-gray-500 col-span-2 py-10 text-center border-2 border-dashed border-gray-200 rounded-xl">Không tìm thấy tin tức nào phù hợp.</p>';
                updatePaginationButtons(0);
                return;
            }

            const start = (currentPage - 1) * ITEMS_PER_PAGE;
            const end = start + ITEMS_PER_PAGE;
            const paginatedNews = filteredData.slice(start, end);

            paginatedNews.forEach(news => {
                const parts = news.date.split('-');
                const formattedDate = `${parts[2]}.${parts[1]}.${parts[0]}`;
                
                const detailUrl = `<?= BASE_URL ?>news/read/${news.slug}`;

                grid.innerHTML += `
                    <article class="group flex flex-col h-full">
                        <a href="${detailUrl}" class="overflow-hidden rounded-xl mb-4 block cursor-pointer">
                            <img src="${news.image}" alt="${news.title}" class="w-full h-56 object-cover transform group-hover:scale-105 transition duration-500">
                        </a>
                        
                        <div class="flex items-center text-xs text-gray-500 mb-2 space-x-3">
                            <span class="text-blue-600 font-medium">${news.catalog}</span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                ${formattedDate}
                            </span>
                        </div>
                        
                        <h2 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2 transition">
                            <a href="${detailUrl}" class="hover:text-blue-600">${news.title}</a>
                        </h2>
                        
                        <p class="text-gray-600 text-sm line-clamp-3 mb-3 flex-grow">${news.desc}</p>
                        
                        <a href="${detailUrl}" class="text-sm font-bold text-gray-900 flex items-center gap-1 hover:text-blue-600 transition mt-auto pt-2">
                            Đọc thêm <span class="text-xs">&rang;</span>
                        </a>
                    </article>
                `;
            });

            updatePaginationButtons(filteredData.length);
        }

        function updatePaginationButtons(totalItems) {
            const totalPages = Math.ceil(totalItems / ITEMS_PER_PAGE);
            const btnPrev = document.getElementById('btn-prev');
            const btnNext = document.getElementById('btn-next');

            btnPrev.classList.toggle('hidden', currentPage <= 1);
            btnNext.classList.toggle('hidden', currentPage >= totalPages || totalItems === 0);
        }

        function setActiveFilterUI() {
            document.querySelectorAll('.filter-btn').forEach(btn => {
                if (btn.dataset.filter === currentFilter) {
                    btn.className = "filter-btn bg-[#102339] text-white px-5 py-2 rounded-full text-sm font-medium transition";
                } else {
                    btn.className = "filter-btn bg-gray-100 text-gray-600 hover:bg-gray-200 px-5 py-2 rounded-full text-sm font-medium transition";
                }
            });

            document.querySelectorAll('.sidebar-btn').forEach(btn => {
                const spanText = btn.querySelector('span:first-child');
                const spanBadge = btn.querySelector('span:last-child');
                
                if (btn.dataset.filter === currentFilter) {
                    spanText.className = "flex items-center before:content-['•'] before:text-[#c8a059] before:mr-2 before:text-xl font-bold text-[#102339]";
                    spanBadge.className = "bg-[#102339] text-white text-xs rounded-full w-6 h-6 flex items-center justify-center";
                } else {
                    spanText.className = "flex items-center before:content-['•'] before:text-transparent before:mr-2 before:text-xl font-normal text-gray-600";
                    spanBadge.className = "bg-gray-100 text-xs text-gray-600 rounded-full w-6 h-6 flex items-center justify-center";
                }
            });
        }

        function setupEventListeners() {
            document.getElementById('searchInput').addEventListener('input', (e) => {
                currentSearchQuery = e.target.value;
                currentPage = 1;
                renderNews();
            });

            const allFilterButtons = document.querySelectorAll('.filter-btn, .sidebar-btn');
            allFilterButtons.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    currentFilter = e.currentTarget.dataset.filter;
                    currentPage = 1; 
                    
                    setActiveFilterUI();
                    renderNews();
                });
            });

            document.getElementById('btn-next').addEventListener('click', () => {
                currentPage++;
                renderNews();
                window.scrollTo({ top: 0, behavior: 'smooth' }); 
            });

            document.getElementById('btn-prev').addEventListener('click', () => {
                currentPage--;
                renderNews();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }

        function renderSidebarExtras() {
            const featuredContainer = document.getElementById('featured-news');
            newsDB.slice(0, 4).forEach(news => {
                const detailUrl = `<?= BASE_URL ?>news/read/${news.slug}`;
                featuredContainer.innerHTML += `
                    <a href="${detailUrl}" class="group block cursor-pointer">
                        <img src="${news.image}" class="w-full h-20 object-cover rounded-lg mb-2">
                        <h4 class="text-xs font-bold leading-tight line-clamp-3 group-hover:text-blue-600 transition">${news.title}</h4>
                    </a>
                `;
            });

            const tagsContainer = document.getElementById('tags-container');
            if(tagsDB.length > 0) {
                tagsDB.forEach(tag => {
                    tagsContainer.innerHTML += `<button class="border border-gray-200 text-gray-500 text-xs px-3 py-1.5 rounded-full hover:border-gray-400 hover:text-gray-700 transition">${tag}</button>`;
                });
            }
        }

        updateCategoryCounts();
        setupEventListeners();
        renderSidebarExtras();
        renderNews();

    </script>

    <script>
    document.getElementById('form-subscribe-index').addEventListener('submit', function(e) {
        e.preventDefault();
        const emailInput = document.getElementById('email-index');
        const alertBox = document.getElementById('alert-index');
        const email = emailInput.value.trim();

        const regex = /^.+@.+\..+$/;
        
        if (!regex.test(email)) {
            alertBox.innerHTML = `
                <div class="bg-[#f8d7da] border border-[#f5c6cb] text-[#721c24] px-4 py-2 rounded relative mb-3 text-sm">
                    <strong>Lỗi!</strong> Định dạng email không hợp lệ. Vui lòng nhập định dạng có chứa '@' và dấu chấm '.'.
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
</body>
</html>
