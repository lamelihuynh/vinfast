<?php

/**
 * app/helpers/Upload.php — Image Upload Handler
 * Owner: All members (common)
 *
 * Validates via finfo (real MIME, not browser header), enforces 2 MB limit,
 * saves with a random filename. Returns relative path to store in DB.
 *
 * Usage: $path = Upload::image($_FILES["photo"], "products");
 *        Upload::delete($oldPath);
 */
class Upload
{
    public static function image(array $file, string $sub = '', bool $keepOriginalName = false): string
    {
        if ($file['error'] !== UPLOAD_ERR_OK)
            throw new RuntimeException('Upload error: ' . $file['error']);
        if ($file['size'] > MAX_FILE_SIZE)
            throw new RuntimeException('File exceeds 2 MB limit.');
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!in_array($mime, ALLOWED_MIME, true))
            throw new RuntimeException('Only JPG, PNG, WebP allowed.');
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        $name = uniqid('vf_', true) . '.' . $ext;
        if ($keepOriginalName) {
            $base = strtolower((string)pathinfo((string)$file['name'], PATHINFO_FILENAME));
            $base = preg_replace('/[^a-z0-9_-]+/', '-', $base);
            $base = trim((string)$base, '-_');
            if ($base === '') {
                $base = 'img';
            }
            $name = $base . '.' . $ext;
        }

        $dir  = rtrim(UPLOAD_PATH . $sub, '/') . '/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        if ($keepOriginalName && file_exists($dir . $name)) {
            $baseName = (string)pathinfo($name, PATHINFO_FILENAME);
            if (preg_match('/^[a-z0-9]{3,5}$/i', $baseName)) {
                @unlink($dir . $name);
            } else {
                $name = pathinfo($name, PATHINFO_FILENAME) . '-' . uniqid() . '.' . $ext;
            }
        }

        if (!move_uploaded_file($file['tmp_name'], $dir . $name))
            throw new RuntimeException('Failed to save file.');
        return ($sub ? $sub . '/' : '') . $name;
    }
    public static function delete(string $rel): void
    {
        $f = UPLOAD_PATH . ltrim($rel, '/');
        if (file_exists($f)) unlink($f);
    }
}
