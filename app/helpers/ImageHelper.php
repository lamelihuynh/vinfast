<?php

/**
 * app/helpers/ImageHelper.php — Image Processing & Resolution Helper
 * Owner: Hai Nam (Member 3)
 *
 * Consolidates image handling logic shared between ProductAdminController
 * and CheckoutViewHelper to eliminate code duplication.
 */
class ImageHelper
{
    /**
     * Extract product family from text (e.g., "VF-8" → "vf8", "vf3" → "vf3")
     * Used for organizing product images in subdirectories.
     */
    public static function extractFamily(string $text): string
    {
        $text = strtolower(trim($text));
        if (!$text) {
            return '';
        }

        // Match VF-3, VF-8, VF-MPV-9, etc.
        if (preg_match('/(?:^|[-_])vf(?:-?mpv)?-?([3-9])(?:[-_]|$)/i', $text, $match)) {
            return 'vf' . $match[1];
        }

        // Normalize to first segment
        $normalized = preg_replace('/[^a-z0-9-]/i', '-', $text);
        $normalized = trim(str_replace('vinfast-', '', $normalized), '-');

        if (!$normalized) {
            return '';
        }

        $family = strtolower(explode('-', $normalized)[0]);
        return preg_match('/^[a-z0-9]+$/', $family) ? $family : '';
    }

    /**
     * Find and resolve the best image URL from multiple sources.
     * Priority: direct path → preferred slug → product family → fallback
     */
    public static function resolveImageUrl(
        string $imgRel,
        string $productFamily = '',
        string $preferredSlug = '',
        array $additionalSearchImages = []
    ): string {
        $imgRel = trim($imgRel);
        if ($imgRel === '') {
            return '';
        }

        // Already absolute URL
        if (preg_match('/^https?:\/\//i', $imgRel)) {
            return $imgRel;
        }

        $imgRel = ltrim($imgRel, '/');

        // Direct path with subdirectory
        if (strpos($imgRel, '/') !== false) {
            $fullPath = ROOT . '/public/images/' . $imgRel;
            if (is_file($fullPath)) {
                return BASE_URL . 'public/images/' . $imgRel;
            }

            // Try fixing path with product family
            $basename = basename($imgRel);
            $fixCandidates = [];
            if ($preferredSlug !== '') {
                $fixCandidates[] = 'uploads/products/' . $preferredSlug . '/' . $basename;
            }
            if ($productFamily !== '') {
                $fixCandidates[] = 'uploads/products/' . $productFamily . '/' . $basename;
            }

            foreach ($fixCandidates as $candidate) {
                $candidatePath = ROOT . '/public/images/' . $candidate;
                if (is_file($candidatePath)) {
                    return BASE_URL . 'public/images/' . $candidate;
                }
            }

            return BASE_URL . 'public/images/' . $imgRel;
        }

        // Filename only - search in multiple locations
        $basename = basename($imgRel);
        $searchDirs = [];

        if ($preferredSlug !== '') {
            $searchDirs[] = 'uploads/products/' . $preferredSlug;
        }
        if ($productFamily !== '') {
            $searchDirs[] = 'uploads/products/' . $productFamily;
        }
        $searchDirs[] = 'uploads/products';
        $searchDirs[] = 'products';

        foreach ($searchDirs as $dir) {
            $candidate = $dir . '/' . $basename;
            $fullPath = ROOT . '/public/images/' . $candidate;
            if (is_file($fullPath)) {
                return BASE_URL . 'public/images/' . $candidate;
            }
        }

        // Try matching against additional image names
        foreach ($additionalSearchImages as $searchImg) {
            if (!is_string($searchImg) || trim($searchImg) === '') {
                continue;
            }

            $searchBasename = basename((string)parse_url($searchImg, PHP_URL_PATH) ?: $searchImg);
            if (strtoupper((string)pathinfo($searchBasename, PATHINFO_FILENAME)) === strtoupper((string)pathinfo($basename, PATHINFO_FILENAME))) {
                return self::resolveImageUrl($searchImg, $productFamily, $preferredSlug, []);
            }
        }

        return BASE_URL . 'public/images/products/' . $imgRel;
    }

    /**
     * Find color image file in product family directory.
     * Searches for {CODE}.{extension} in family subdirs.
     */
    public static function findColorImageInFamily(string $family, string $code): string
    {
        $family = trim($family);
        $code = strtolower(trim($code));
        if ($family === '' || $code === '') {
            return '';
        }

        $dirs = [
            ROOT . '/public/images/uploads/products/' . $family,
        ];

        // Add fallback family if different
        $familyFallback = self::extractFamily($family);
        if ($familyFallback !== '' && $familyFallback !== $family) {
            $dirs[] = ROOT . '/public/images/uploads/products/' . $familyFallback;
        }

        $extensions = ['webp', 'jpg', 'jpeg', 'png'];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            // Try exact extensions
            foreach ($extensions as $ext) {
                $candidate = $dir . '/' . $code . '.' . $ext;
                if (is_file($candidate)) {
                    $relative = str_replace(ROOT . '/public/images/', '', str_replace('\\', '/', $candidate));
                    return $relative;
                }
            }

            // Try glob pattern
            $matches = glob($dir . '/' . $code . '.*', GLOB_NOSORT) ?: [];
            if (!empty($matches)) {
                $match = (string)$matches[0];
                $relative = str_replace(ROOT . '/public/images/', '', str_replace('\\', '/', $match));
                return $relative;
            }
        }

        return '';
    }

    /**
     * Normalize relative image path (remove upload:// prefix, fix slashes, etc.)
     */
    public static function normalizePath(string $path): string
    {
        $path = trim(str_replace('\\', '/', $path));
        if (!$path) {
            return '';
        }

        // Extract relative path after /public/images/
        if (preg_match('~(?:^|[A-Za-z]:)?(?:.*/)?public/images/(.+)$~i', $path, $m)) {
            return ltrim($m[1], '/');
        }

        return ltrim($path, '/');
    }
}
