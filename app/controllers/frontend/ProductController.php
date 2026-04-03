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
        $pp = (int)($_GET['pp'] ?? 6);
        if (!in_array($pp, $allowedPerPage, true)) {
            $pp = 6;
        }
        $page  = max(1, (int)($_GET['page'] ?? 1));

        $categories = Category::getAll();

        $filters = [];

        if ($q !== '') {
            $filters['search'] = $q;
        }

        if ($cat > 0) {
            $filters['category_id'] = $cat;
        }

        // Convert price range to VND values
        if ($price !== 'all') {
            switch ($price) {
                case 'under300':
                    $filters['price_max'] = 300 * 1000000;
                    break;
                case '300-500':
                    $filters['price_min'] = 300 * 1000000;
                    $filters['price_max'] = 500 * 1000000;
                    break;
                case '500-1000':
                    $filters['price_min'] = 500 * 1000000;
                    $filters['price_max'] = 1000 * 1000000;
                    break;
                case 'over1000':
                    $filters['price_min'] = 1000 * 1000000;
                    break;
            }
        }

        $filters['sort'] = $sort;

        $products = Product::filterAll($filters);

        if ($range !== 'all') {
            $products = array_values(array_filter($products, function (array $p) use ($range): bool {
                $km = Product::extractRangeKm($p['specs'] ?? []);
                if ($range === 'lt200') return $km > 0 && $km < 200;
                if ($range === '200-400') return $km >= 200 && $km <= 400;
                if ($range === 'gt400') return $km > 400;
                return true;
            }));
        }

        $total = count($products);
        $pg = new Pagination($total, $page, $pp);
        $products = array_slice($products, $pg->offset(), $pg->limit());

        $query = [];
        if ($q !== '') $query['q'] = $q;
        if ($cat > 0) $query['cat'] = $cat;
        if ($sort !== 'default') $query['sort'] = $sort;
        if ($pp !== 6) $query['pp'] = $pp;
        if ($price !== 'all') $query['price'] = $price;
        if ($range !== 'all') $query['range'] = $range;
        $baseQuery = http_build_query($query);
        $pageUrl = BASE_URL . 'products?' . ($baseQuery !== '' ? $baseQuery . '&' : '') . 'page=';

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
}
