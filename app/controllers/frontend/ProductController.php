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

        $filterableMap = [];
        if ($cat > 0) {
            $selectedCategory = Category::allById()[$cat] ?? null;
            if ($selectedCategory) {
                foreach ((array)($selectedCategory['dynamic_attributes'] ?? []) as $def) {
                    $attrKey = (string)($def['key'] ?? '');
                    if (
                        !empty($def['filterable'])
                        && $attrKey !== ''
                        && preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $attrKey) === 1
                    ) {
                        $filterableMap[$attrKey] = $def;
                    }
                }
            }
        }

        $selectedAttrs = [];
        $typedAttrs = [];
        if ($cat > 0 && !empty($_GET['attr']) && is_array($_GET['attr'])) {
            foreach ($_GET['attr'] as $rawKey => $rawVal) {
                if (!is_string($rawKey) || !isset($filterableMap[$rawKey]) || !is_scalar($rawVal)) {
                    continue;
                }
                $val = trim((string)$rawVal);
                if ($val === '' || $val === 'all') {
                    continue;
                }
                $def = $filterableMap[$rawKey];
                $attrType = (string)($def['type'] ?? 'string');
                $casted = null;
                if ($attrType === 'enum' || $attrType === 'list') {
                    $options = array_map('strval', (array)($def['options'] ?? []));
                    if (!empty($options) && !in_array($val, $options, true)) {
                        continue;
                    }
                    $casted = $val;
                } elseif ($attrType === 'boolean') {
                    if ($val === '1' || strtolower($val) === 'true') {
                        $casted = true;
                        $val = '1';
                    } elseif ($val === '0' || strtolower($val) === 'false') {
                        $casted = false;
                        $val = '0';
                    } else {
                        continue;
                    }
                } elseif ($attrType === 'number') {
                    if (!is_numeric($val)) {
                        continue;
                    }
                    $num = (float)$val;
                    $casted = ((float)(int)$num === $num) ? (int)$num : $num;
                } else {
                    $casted = $val;
                }
                $typedAttrs[$rawKey] = $casted;
                $selectedAttrs[$rawKey] = $val;
            }
        }
        if (!empty($typedAttrs)) {
            $filters['attrs'] = $typedAttrs;
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

        $wishlistIds = Auth::check() ? Wishlist::productIdsOf((int)Auth::id()) : [];

        $query = [];
        if ($q !== '') $query['q'] = $q;
        if ($cat > 0) $query['cat'] = $cat;
        if ($sort !== 'default') $query['sort'] = $sort;
        if ($pp !== 6) $query['pp'] = $pp;
        if ($price !== 'all') $query['price'] = $price;
        if ($range !== 'all') $query['range'] = $range;
        if ($type !== 'all' && $cat <= 0) $query['type'] = $type;
        if (!empty($selectedAttrs)) $query['attr'] = $selectedAttrs;
        $baseQuery = http_build_query($query);
        $pageUrl = BASE_URL . 'products?' . ($baseQuery !== '' ? $baseQuery . '&' : '') . 'page=';

        SEO::set(
            $type === 'accessory' ? 'Phụ kiện' : ($type === 'vehicle' ? 'Xe điện' : 'Sản phẩm'),
            'Danh sách xe điện và phụ kiện VinFast',
            'Vinfast, xe điện, phụ kiện, pin, sạc, products'
        );

        View::render('frontend/products/index', [
            'products'        => $products,
            'categories'      => $categories,
            'q'               => $q,
            'cat'             => $cat,
            'sort'            => $sort,
            'pp'              => $pp,
            'price'           => $price,
            'range'           => $range,
            'type'            => $type,
            'pg'              => $pg,
            'pageUrl'         => $pageUrl,
            'total'           => $total,
            'filterableAttrs' => array_values($filterableMap),
            'selectedAttrs'   => $selectedAttrs,
            'wishlistIds'     => $wishlistIds,
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
        $isWishlisted = Auth::check()
            ? Wishlist::has((int)Auth::id(), (string)$product['product_id'], null)
            : false;

        if (!empty($product['is_accessory'])) {
            SEO::set($product['name'], 'Phụ kiện VinFast: ' . ($product['category_label'] ?? ''), 'vinfast, phụ kiện, ' . ($product['category_label'] ?? ''));
            View::render('frontend/products/accessory', [
                'product'      => $product,
                'category'     => Category::allById()[(int)$product['category_id']] ?? [],
                'inventory'    => InventoryService::byProduct((string)$product['product_id']),
                'related'      => Product::getByCategory((int)$product['category_id'], 1, 5),
                'isWishlisted' => $isWishlisted,
            ]);
            return;
        }

        $activeVariants = array_values(array_filter(
            is_array($product['variants'] ?? null) ? $product['variants'] : [],
            static fn($v): bool => is_array($v) && !empty($v['isActive']) && !empty($v['variantId'])
        ));

        $requestedVariantId = trim((string)($_GET['variant'] ?? ''));
        $selectedVariant = $activeVariants[0] ?? null;
        if ($requestedVariantId !== '') {
            foreach ($activeVariants as $v) {
                if ((string)$v['variantId'] === $requestedVariantId) {
                    $selectedVariant = $v;
                    break;
                }
            }
        }
        $selectedVariantId = (string)($selectedVariant['variantId'] ?? ($product['default_variant_id'] ?? ''));

        SEO::set($product['name'] ?? 'Product Detail', 'Chi tiết xe VinFast', 'vinfast, chi tiết xe');
        View::render('frontend/products/detail', [
            'product'           => $product,
            'activeVariants'    => $activeVariants,
            'selectedVariantId' => $selectedVariantId,
            'isWishlisted'      => $isWishlisted,
        ]);
    }

}
