<?php
/**
 * app/controllers/frontend/NewsController.php
 * Owner: Nhat Tan 
 * Routes: GET /news   GET /news/read/{slug}
 *
 *  * Article listing with keyword search (?q=) and pagination.
 * Detail page renders TinyMCE HTML body, shows approved comments,
 * and provides a comment form for logged-in members.
 * Frontend CSS: public/css/frontend/news.css
 */
class NewsController
{
    private const PER_PAGE = 6;
 
    public function index(): void
    {
        $q       = trim($_GET['q']       ?? '');
        $catalog = trim($_GET['catalog'] ?? '');
        $sort    = in_array($_GET['sort'] ?? '', ['latest', 'popular'], true)
                   ? $_GET['sort']
                   : 'latest';
        $page    = max(1, (int) ($_GET['page'] ?? 1));

        if (!in_array($catalog, News::CATALOGS, true)) {
            $catalog = '';
        }
 
        $pg = new Pagination(
            News::count($q, $catalog, 'Hiển thị'),
            $page,
            self::PER_PAGE
        );
 
        $articles = News::getAll($pg->current, self::PER_PAGE, $q, $catalog, $sort, 'Hiển thị');
 
        $pageLabel = $pg->current > 1 ? ' — Trang ' . $pg->current : '';
        SEO::set(
            'Tin tức VinFast' . $pageLabel,
            'Cập nhật tin tức, sự kiện và công nghệ xe điện mới nhất từ VinFast.'
        );
 
        View::render('frontend/news/index', [
            'articles' => $articles,
            'q'        => $q,
            'catalog'  => $catalog,
            'sort'     => $sort,
            'catalogs' => News::CATALOGS,
            'pg'       => $pg,
        ]);
    }
 
    public function read(string $slug = ''): void
    {
        $slug = trim($slug);
        if ($slug === '') {
            $this->notFound();
            return;
        }
 
        $article = News::getBySlug($slug, 'Hiển thị');
        if (!$article) {
            $this->notFound();
            return;
        }
 
        SEO::set($article['title'], '');
 
        View::render('frontend/news/detail', [
            'article'  => $article,
        ]);
    }
    
    private function notFound(): void
    {
        http_response_code(404);
        include ROOT . '/app/views/frontend/404.php';
    }
}
