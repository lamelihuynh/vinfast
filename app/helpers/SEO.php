<?php
/**
 * app/helpers/SEO.php — Meta Tag Helper
 * Owner: All members (common)
 *
 * Set per-page SEO in the controller:
 *   SEO::set("Page title", "description", "keywords");
 *
 * Echo in the <head> partial:
 *   <?= SEO::titleTag() ?>
 *   <?= SEO::metaTags() ?>
 */
class SEO {
    private static string $t = '', $d = '', $k = '';
    public static function set(string $t, string $d = '', string $k = ''): void {
        self::$t = htmlspecialchars($t, ENT_QUOTES, 'UTF-8');
        self::$d = htmlspecialchars($d, ENT_QUOTES, 'UTF-8');
        self::$k = htmlspecialchars($k, ENT_QUOTES, 'UTF-8');
    }
    public static function titleTag(): string {
        return '<title>' . (self::$t ? self::$t . ' | ' . APP_NAME : APP_NAME) . '</title>';
    }
    public static function metaTags(): string {
        $out = '';
        if (self::$d) $out .= '<meta name="description" content="' . self::$d . '">' . PHP_EOL;
        if (self::$k) $out .= '<meta name="keywords"    content="' . self::$k . '">' . PHP_EOL;
        return $out;
    }
}
