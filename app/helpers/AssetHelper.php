<?php
class AssetHelper
{
    public static function getVersion(string $relativePath): string
    {
        $fullPath = ROOT . '/' . ltrim($relativePath, '/');

        if (file_exists($fullPath)) {
            return (string)filemtime($fullPath);
        }
        return (string)time();
    }
    public static function getVersionQuery(string $relativePath): string
    {
        return '?v=' . self::getVersion($relativePath);
    }
    public static function exists(string $relativePath): bool
    {
        $fullPath = ROOT . '/' . ltrim($relativePath, '/');
        return file_exists($fullPath);
    }
    public static function url(string $assetPath, string $basePath = ''): string
    {
        if ($basePath === '') {
            $basePath = BASE_URL . 'public/';
        }

        $relativePath = 'public/' . ltrim($assetPath, '/');
        $version = self::getVersion($relativePath);

        return $basePath . ltrim($assetPath, '/') . '?v=' . $version;
    }
    public static function getMultiVersion(array $assetPaths): string
    {
        $versions = [];

        foreach ($assetPaths as $path) {
            $versions[] = self::getVersion($path);
        }
        // Create a simple hash of all versions
        return md5(implode('|', $versions));
    }
}
