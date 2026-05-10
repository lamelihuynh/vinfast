<?php

/**
 * app/models/PageAsset.php — Page Assets (Images, Videos)
 * Table: page_assets
 * Owner: Nhat Linh (Member 2)
 *
 * Used by: About page, FAQ page, admin controllers
 * Stores uploaded images/videos with metadata
 */
class PageAsset
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Lấy asset theo page_type và asset_key
     */
    public function getByKey(string $pageType, string $assetKey): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM page_assets WHERE page_type = ? AND asset_key = ? LIMIT 1'
        );
        $stmt->execute([$pageType, $assetKey]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Lấy tất cả assets theo page_type
     */
    public function getByPageType(string $pageType): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM page_assets WHERE page_type = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$pageType]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Lấy tất cả assets theo page_type và asset_key pattern (e.g., 'timeline_%')
     */
    public function getByPattern(string $pageType, string $pattern): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM page_assets WHERE page_type = ? AND asset_key LIKE ? ORDER BY asset_key ASC'
        );
        $stmt->execute([$pageType, $pattern]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }


    public function getTimelineData(): array{

        $assets = $this->getByPattern('about', 'timeline_%');

        $timeline = []; 
        foreach ($assets as $asset) {
            $parts = explode('_', $asset['asset_key']); 

            if (count($parts) >= 3){
                $year = $parts[1]; 
                $type = $parts[2];
                $timeline[$year]["imag_$type"] = $asset['$file_path'];
            }
        }
        return $timeline;
    }

    /**
     * Tạo/Upsert asset
     */
    public function save(string $pageType, string $assetKey, string $filePath, int $fileSize = 0, string $mimeType = null): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO page_assets (page_type, asset_key, file_path, file_size, mime_type) 
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE file_path = ?, file_size = ?, mime_type = ?, created_at = NOW()'
        );
        return $stmt->execute([$pageType, $assetKey, $filePath, $fileSize, $mimeType, $filePath, $fileSize, $mimeType]);
    }

    /**
     * Xóa asset
     */
    public function delete(string $pageType, string $assetKey): bool
    {
        // Lấy file path để xóa file
        $asset = $this->getByKey($pageType, $assetKey);
        if ($asset && file_exists(UPLOAD_PATH . $asset['file_path'])) {
            unlink(UPLOAD_PATH . $asset['file_path']);
        }

        $stmt = $this->pdo->prepare(
            'DELETE FROM page_assets WHERE page_type = ? AND asset_key = ?'
        );
        return $stmt->execute([$pageType, $assetKey]);
    }

    /**
     * Xóa tất cả assets của page_type
     */
    public function deleteByPageType(string $pageType): bool
    {
        $assets = $this->getByPageType($pageType);
        foreach ($assets as $asset) {
            if (file_exists(PUBLIC_PATH . $asset['file_path'])) {
                unlink(PUBLIC_PATH . $asset['file_path']);
            }
        }

        $stmt = $this->pdo->prepare('DELETE FROM page_assets WHERE page_type = ?');
        return $stmt->execute([$pageType]);
    }

    /**
     * Upload file + save to database
     * Returns: ['success' => bool, 'message' => string, 'asset' => array|null]
     */
    public function upload(string $pageType, string $assetKey, array $fileInput, string $uploadDir = 'about-page/'): array
    {
        if (!isset($fileInput['tmp_name']) || !isset($fileInput['name'])) {
            return ['success' => false, 'message' => 'Không có file được chọn'];
        }

        // Validate file size (images: 10MB, videos: 100MB)
        $isVideo = strpos($fileInput['type'], 'video') === 0;
        $maxSize = $isVideo ? 100 * 1024 * 1024 : 10 * 1024 * 1024;
        if ($fileInput['size'] > $maxSize) {
            $maxSizeMB = $maxSize / (1024 * 1024);
            return ['success' => false, 'message' => "File quá lớn (tối đa {$maxSizeMB}MB)"];
        }

        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4'];
        $mimeType = mime_content_type($fileInput['tmp_name']);
        if (!in_array($mimeType, $allowedMimes)) {
            return ['success' => false, 'message' => 'Định dạng file không được phép. Chỉ hỗ trợ: JPEG, PNG, GIF, WebP, MP4'];
        }

        // Tạo thư mục nếu chưa tồn tại
        $uploadPath = UPLOAD_PATH . $uploadDir;
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Tạo tên file unique
        $ext = pathinfo($fileInput['name'], PATHINFO_EXTENSION);
        $filename = $assetKey . '.' . $ext; 
        $filePath = $uploadPath . $filename;


        // Check duplicate 
        if (file_exists($filePath)) {
            unlink($filePath); 
        }       

        // Move file
        if (!move_uploaded_file($fileInput['tmp_name'], $filePath)) {
            return ['success' => false, 'message' => 'Lỗi khi tải file lên'];
        }

        // Save to database
        if (!$this->save($pageType, $assetKey, $uploadDir . $filename, $fileInput['size'], $mimeType)) {
            unlink($filePath);
            return ['success' => false, 'message' => 'Lỗi khi lưu vào database'];
        }

        $asset = $this->getByKey($pageType, $assetKey);
        return ['success' => true, 'message' => 'Upload thành công', 'asset' => $asset];
    }
}