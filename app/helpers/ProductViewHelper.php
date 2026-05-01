<?php

/**
 * app/helpers/ProductViewHelper.php — Product display helpers
 * Owner: Hai Nam
 */
class ProductViewHelper
{
    public static function thumbUrl(array $product): string
    {
        $images = is_array($product['images'] ?? null) ? $product['images'] : [];
        $imgRel = !empty($images[0]) && is_string($images[0]) ? (string)$images[0] : '';

        return self::resolveImageUrl($imgRel, $product);
    }

    public static function resolveImageUrl(string $imgRel, array $product = []): string
    {
        $imgRel = trim($imgRel);
        if ($imgRel === '') {
            return BASE_URL . 'public/images/products/default.png';
        }

        if (preg_match('/^https?:\/\//i', $imgRel)) {
            return $imgRel;
        }

        $imgRel = str_replace('\\', '/', $imgRel);
        if (preg_match('~(?:^|[A-Za-z]:)?(?:.*/)?public/images/(.+)$~i', $imgRel, $match)) {
            $imgRel = (string)$match[1];
        }

        $imgRel = ltrim($imgRel, '/');

        if (strpos($imgRel, '/') !== false) {
            $directPath = ROOT . '/public/images/' . $imgRel;
            if (is_file($directPath)) {
                return BASE_URL . 'public/images/' . $imgRel;
            }
        }

        $candidates = [
            'uploads/products/' . $imgRel,
            'products/' . $imgRel,
            $imgRel,
        ];

        foreach ($candidates as $candidate) {
            $fullPath = ROOT . '/public/images/' . $candidate;
            if (is_file($fullPath)) {
                return BASE_URL . 'public/images/' . $candidate;
            }
        }

        $basename = basename($imgRel);
        $family = self::extractImageFamily((string)($product['slug'] ?? ''));
        if ($family === '') {
            $family = self::extractImageFamily((string)($product['name'] ?? ''));
        }

        if ($family !== '') {
            $familyPath = ROOT . '/public/images/uploads/products/' . $family . '/' . $basename;
            if (is_file($familyPath)) {
                $match = str_replace(ROOT . '/public/images/', '', str_replace('\\', '/', $familyPath));
                return BASE_URL . 'public/images/' . $match;
            }
        }

        $searchPatterns = [
            ROOT . '/public/images/uploads/products/*/' . $basename,
            ROOT . '/public/images/uploads/products/*/*/' . $basename,
        ];

        foreach ($searchPatterns as $pattern) {
            $matches = glob($pattern) ?: [];
            if (!empty($matches)) {
                $match = str_replace(ROOT . '/public/images/', '', str_replace('\\', '/', (string)$matches[0]));
                return BASE_URL . 'public/images/' . $match;
            }
        }

        return BASE_URL . 'public/images/products/default.jpg';
    }

    private static function extractImageFamily(string $text): string
    {
        $text = strtolower(trim($text));
        if ($text === '') {
            return '';
        }

        if (preg_match('/(?:^|[-_])vf(?:-?mpv)?-?([3-9])(?:[-_]|$)/i', $text, $match)) {
            return 'vf' . $match[1];
        }

        $normalized = preg_replace('/[^a-z0-9]+/i', '-', $text);
        $normalized = trim((string)$normalized, '-');
        if ($normalized === '') {
            return '';
        }

        if (strpos($normalized, 'vinfast-') === 0) {
            $normalized = substr($normalized, 8);
        }

        $normalized = trim((string)$normalized, '-');
        if ($normalized === '') {
            return '';
        }

        $parts = explode('-', $normalized);
        $family = strtolower(trim((string)($parts[0] ?? '')));
        if (!preg_match('/^[a-z0-9]+$/', $family)) {
            return '';
        }

        return $family;
    }
}
