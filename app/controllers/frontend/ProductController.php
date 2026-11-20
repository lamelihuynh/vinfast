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
        $type  = trim($_GET['type'] ?? 'all');
        if (!in_array($type, ['all', 'vehicle', 'accessory'], true)) {
            $type = 'all';
        }
        $allowedPerPage = [6, 12, 15];
        $pp = (int)($_GET['pp'] ?? 6);
        if (!in_array($pp, $allowedPerPage, true)) {
            $pp = 6;
        }
        $page  = max(1, (int)($_GET['page'] ?? 1));

        $categories = Category::getAll();
        // Đang xem 1 category -> loại sản phẩm theo category đó
        foreach ($categories as $c) {
            if ($cat > 0 && (int)$c['id'] === $cat) {
                $type = (string)$c['kind'];
            }
        }

        $filters = [];
        if ($type !== 'all') {
            $filters['kind'] = $type;
        }

        if ($q !== '') {
            $filters['search'] = $q;
        }

        if ($cat > 0) {
            $filters['category_id'] = $cat;
        }

        if ($range !== 'all' && $type !== 'accessory') {
            $filters['range'] = $range;
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
                // Mức giá cho phụ kiện
                case 'acc_under1':
                    $filters['price_max'] = 1000000;
                    break;
                case 'acc_1-5':
                    $filters['price_min'] = 1000000;
                    $filters['price_max'] = 5000000;
                    break;
                case 'acc_5-20':
                    $filters['price_min'] = 5000000;
                    $filters['price_max'] = 20000000;
                    break;
                case 'acc_over20':
                    $filters['price_min'] = 20000000;
                    break;
            }
        }

        $filters['sort'] = $sort;

        $total = Product::countFiltered($filters);
        $pg = new Pagination($total, $page, $pp);
        $products = Product::filterPaginated($filters, $pg->current, $pg->perPage);

        if ($q !== '' && $pg->current === 1) {
            ActivityLog::record('search', null, ['keyword' => $q, 'customData' => ['results' => $total, 'type' => $type, 'cat' => $cat]]);
        }

        $query = [];
        if ($q !== '') $query['q'] = $q;
        if ($cat > 0) $query['cat'] = $cat;
        if ($sort !== 'default') $query['sort'] = $sort;
        if ($pp !== 6) $query['pp'] = $pp;
        if ($price !== 'all') $query['price'] = $price;
        if ($range !== 'all') $query['range'] = $range;
        if ($type !== 'all' && $cat <= 0) $query['type'] = $type;
        $baseQuery = http_build_query($query);
        $pageUrl = BASE_URL . 'products?' . ($baseQuery !== '' ? $baseQuery . '&' : '') . 'page=';

        SEO::set(
            $type === 'accessory' ? 'Phụ kiện' : ($type === 'vehicle' ? 'Xe điện' : 'Sản phẩm'),
            'Danh sách xe điện và phụ kiện VinFast',
            'Vinfast, xe điện, phụ kiện, pin, sạc, products'
        );

        View::render('frontend/products/index', [
            'products'   => $products,
            'categories' => $categories,
            'q'          => $q,
            'cat'        => $cat,
            'sort'       => $sort,
            'pp'         => $pp,
            'price'      => $price,
            'range'      => $range,
            'type'       => $type,
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

        ActivityLog::record('view_product', (string)$product['product_id'], ['customData' => ['kind' => $product['kind'] ?? 'vehicle']]);

        if (!empty($product['is_accessory'])) {
            SEO::set($product['name'], 'Phụ kiện VinFast: ' . ($product['category_label'] ?? ''), 'vinfast, phụ kiện, ' . ($product['category_label'] ?? ''));
            View::render('frontend/products/accessory', [
                'product' => $product,
                'category' => Category::allById()[(int)$product['category_id']] ?? [],
                'inventory' => InventoryService::byProduct((string)$product['product_id']),
                'related' => Product::getByCategory((int)$product['category_id'], 1, 5),
            ]);
            return;
        }

        SEO::set($product['name'] ?? 'Product Detail', 'Chi tiết xe VinFast', 'vinfast, chi tiết xe');
        View::render('frontend/products/detail', [
            'product' => $product
        ]);
    }

}
