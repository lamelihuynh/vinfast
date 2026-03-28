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

function vf_is_active(string $path): bool {
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
    <?php include ROOT . '/app/views/frontend/partials/navbar/utility.php'; ?>
    <?php include ROOT . '/app/views/frontend/partials/navbar/mainbar.php'; ?>
    <?php include ROOT . '/app/views/frontend/partials/navbar/nav.php'; ?>
    <?php include ROOT . '/app/views/frontend/partials/navbar/mobile.php'; ?>
</header>

<div id="vfHeaderSpacer" class="h-[122px] md:h-[130px]"></div>
