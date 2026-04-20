<?php

/**
 * app/views/frontend/partials/navbar.php — Customer Navbar
 * Owner: All members (common)
 *
 * Sticky Bootstrap 5 navbar. Shows login/register for guests,
 * user dropdown + cart icon for members.
 * Admin link appears only when role === "admin".
 */

$userName = trim(Auth::name());
$initials = '';
if ($userName !== '') {
    $parts = preg_split('/\s+/', $userName);
    $parts = array_values(array_filter($parts));
    $lastTwo = array_slice($parts, -2);
    foreach ($lastTwo as $p) {
        $initials .= strtoupper(substr($p, 0, 1));
    }
}

$isLoggedIn = Auth::check();

$defaultCheckoutUrl = BASE_URL . 'products/checkout/94';

$navCategories = Category::getAll();
$navProductGroups = [];
foreach ($navCategories as $navCategory) {
    if (!is_array($navCategory)) {
        continue;
    }

    $categoryId = (int)($navCategory['id'] ?? 0);
    $categoryName = trim((string)($navCategory['name'] ?? ''));
    if ($categoryId <= 0 || $categoryName === '') {
        continue;
    }

    $categoryProducts = Product::getByCategory($categoryId, 1, 6);
    if (empty($categoryProducts)) {
        continue;
    }

    $navItems = [];
    foreach ($categoryProducts as $navProduct) {
        if (!is_array($navProduct)) {
            continue;
        }

        $navItems[] = [
            'id' => (int)($navProduct['id'] ?? 0),
            'name' => (string)($navProduct['name'] ?? ''),
            'slug' => (string)($navProduct['slug'] ?? ''),
            'rangeText' => Product::extractRangeKm((array)($navProduct['specs'] ?? [])) > 0 ? (string)Product::extractRangeKm((array)($navProduct['specs'] ?? [])) . 'km' : '',
            'priceText' => number_format((float)($navProduct['price'] ?? 0), 0, ',', '.') . ' VNĐ',
        ];
    }

    if (empty($navItems)) {
        continue;
    }

    $navProductGroups[] = [
        'title' => $categoryName,
        'slug' => (string)($navCategory['slug'] ?? ''),
        'items' => $navItems,
    ];
}

if (empty($navProductGroups)) {
    $navProductGroups[] = [
        'title' => 'Sản phẩm',
        'slug' => 'products',
        'items' => [],
    ];
}

$navFeaturedProduct = null;
foreach ($navProductGroups as $navGroup) {
    if (!empty($navGroup['items'][0])) {
        $navFeaturedProduct = $navGroup['items'][0];
        break;
    }
}

if ($navFeaturedProduct === null) {
    $fallbackFeatured = Product::getAll(1, 1);
    if (!empty($fallbackFeatured) && is_array($fallbackFeatured[0] ?? null)) {
        $navFeaturedProduct = [
            'name' => (string)($fallbackFeatured[0]['name'] ?? 'VinFast'),
            'slug' => (string)($fallbackFeatured[0]['slug'] ?? ''),
            'rangeText' => Product::extractRangeKm((array)($fallbackFeatured[0]['specs'] ?? [])) > 0 ? (string)Product::extractRangeKm((array)($fallbackFeatured[0]['specs'] ?? [])) . 'km' : '',
        ];
    }
}

function vf_is_active(string $path): bool
{
    $uri = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/', '/');
    $base = trim(parse_url(BASE_URL, PHP_URL_PATH) ?? '/', '/');
    if ($base !== '' && strpos($uri, $base) === 0) {
        $uri = trim(substr($uri, strlen($base)), '/');
    }
    $path = trim($path, '/');
    if ($path === '') return $uri === '';
    return strpos($uri, $path) === 0;
}
?>
<header id="vfHeader" class="fixed top-0 left-0 right-0 z-[1200] w-full transition-all duration-300" style="font-family: Inter, Segoe UI, Roboto, sans-serif;">
    <?php include ROOT . '/app/views/frontend/partials/navbar/mainbar.php'; ?>
    <?php include ROOT . '/app/views/frontend/partials/navbar/nav.php'; ?>
    <?php include ROOT . '/app/views/frontend/partials/navbar/mobile.php'; ?>
</header>

<div id="vfHeaderSpacer" class="h-[86px] md:h-[94px]"></div>