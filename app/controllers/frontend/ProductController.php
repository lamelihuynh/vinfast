<?php
/**
 * app/controllers/frontend/ProductController.php
 * Owner: Hai Nam
 * Routes: GET /products   GET /products/detail/{id}
 */
class ProductController
{
    public function index(): void
    {
        $q     = trim($_GET['q'] ?? '');
        $cat   = (int)($_GET['cat'] ?? 0);
        $sort  = trim($_GET['sort'] ?? 'default');
        $price = trim($_GET['price'] ?? 'all');
        $range = trim($_GET['range'] ?? 'all');
        $allowedPerPage = [6, 12, 15];
        $pp = (int)($_GET['pp'] ?? 12);
        if (!in_array($pp, $allowedPerPage, true)) {    
            $pp = 12;
        }
        $page  = max(1, (int)($_GET['page'] ?? 1));

        $categories = Category::getAll();

        // Fetch a large active set then apply combined filters in PHP
        $all = Product::getAll(1, 1000);

        if ($q !== '') {
            $needle = strtolower($q);
            $all = array_values(array_filter($all, function (array $p) use ($needle): bool {
                $name = strtolower((string)($p['name'] ?? ''));
                return strpos($name, $needle) !== false;
            }));
        }

        if ($cat > 0) {
            $all = array_values(array_filter($all, function (array $p) use ($cat): bool {
                return (int)($p['category_id'] ?? 0) === $cat;
            }));
        }

        if ($price !== 'all') {
            $all = array_values(array_filter($all, function (array $p) use ($price): bool {
                $million = ((float)($p['price'] ?? 0)) / 1000000;
                if ($price === 'under300') return $million < 300;
                if ($price === '300-500') return $million >= 300 && $million <= 500;
                if ($price === '500-1000') return $million > 500 && $million <= 1000;
                if ($price === 'over1000') return $million > 1000;
                return true;
            }));
        }

        if ($range !== 'all') {
            $all = array_values(array_filter($all, function (array $p) use ($range): bool {
                $km = $this->extractRangeKm($p['specs'] ?? []);
                if ($range === 'lt200') return $km > 0 && $km < 200;
                if ($range === '200-400') return $km >= 200 && $km <= 400;
                if ($range === 'gt400') return $km > 400;
                return true;
            }));
        }

        usort($all, function (array $a, array $b) use ($sort): int {
            $pa = (float)($a['price'] ?? 0);
            $pb = (float)($b['price'] ?? 0);
            if ($sort === 'price_asc') return $pa <=> $pb;
            if ($sort === 'price_desc') return $pb <=> $pa;
            if ($sort === 'newest') {
                $ta = strtotime((string)($a['created_at'] ?? '')) ?: 0;
                $tb = strtotime((string)($b['created_at'] ?? '')) ?: 0;
                return $tb <=> $ta;
            }
            return 0;
        });

        $total = count($all);
        $pg = new Pagination((int)$total, $page, $pp);
        $products = array_slice($all, $pg->offset(), $pg->limit());

        $query = [];
        if ($q !== '') $query[] = 'q=' . urlencode($q);
        if ($cat > 0) $query[] = 'cat=' . $cat;
        if ($sort !== 'default') $query[] = 'sort=' . urlencode($sort);
        if ($pp !== 12) $query[] = 'pp=' . $pp;
        if ($price !== 'all') $query[] = 'price=' . urlencode($price);
        if ($range !== 'all') $query[] = 'range=' . urlencode($range);
        $pageUrl = BASE_URL . 'products?' . (empty($query) ? '' : implode('&', $query) . '&') . 'page=';

        SEO::set('Vehicles', 'Danh sách xe VinFast', 'Vinfast, xe điện, products');

        View::render('frontend/products/index', [
            'products'   => $products,
            'categories' => $categories,
            'q'          => $q,
            'cat'        => $cat,
            'sort'       => $sort,
            'pp'         => $pp,
            'price'      => $price,
            'range'      => $range,
            'pg'         => $pg,
            'pageUrl'    => $pageUrl,
            'total'      => $total,
        ]);
    }

    public function detail($id = 0): void
    {
        $id = (int)$id;
        if ($id <= 0) {
            http_response_code(404);
            include ROOT . '/app/views/frontend/404.php';
            return;
        }
        $product = Product::getById($id);
        if (!$product) {
            http_response_code(404);
            include ROOT . '/app/views/frontend/404.php';
            return;
        }
        SEO::set($product['name'] ?? 'Product Detail', 'Chi tiết xe VinFast', 'vinfast, chi tiết xe');
        View::render('frontend/products/detail', [
            'product' => $product
        ]);
    }

    private function extractRangeKm(array $specs): float
    {
        if (!isset($specs['range'])) {
            return 0;
        }
        if (preg_match('/(\d+(?:\.\d+)?)/', (string)$specs['range'], $m)) {
            return (float)$m[1];
        }
        return 0;
    }
}
