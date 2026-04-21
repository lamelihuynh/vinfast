<?php

/**
 * app/models/SiteSetting.php — Site Settings Key-Value Store
 * Table: site_settings
 * Owner: Tang Vu (Member 1)
 * Used by: SettingsAdminController (admin), HomeController (reads banners/logo)
 *
 * Keys used: logo, banner_1..3, address, phone, email, about_text,
 *            about_image, facebook_url, tagline
 */
class SiteSetting
{

    /**
     * Lấy toàn bộ settings dưới dạng assoc array: ['key' => 'value', ...]
     */
    public static function all(): array
    {
        global $pdo;
        $stmt = $pdo->query('SELECT `key`, value FROM site_settings');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $out = [];
        foreach ($rows as $r) {
            $k = (string)($r['key'] ?? '');
            if ($k === '') continue;
            $out[$k] = (string)($r['value'] ?? '');
        }
        return $out;
    }

    /**
     * Lấy value theo key. Nếu không tồn tại trả về $default.
     */
    public static function get(string $key, string $default = ''): string
    {
        global $pdo;
        $stmt = $pdo->prepare('SELECT value FROM site_settings WHERE `key` = ? LIMIT 1');
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        if ($val === false || $val === null) return $default;
        return (string)$val;
    }

    /**
     * Upsert 1 key/value.
     */
    public static function set(string $key, ?string $value): bool
    {
        global $pdo;
        $key = trim($key);
        if ($key === '') return false;

        $stmt = $pdo->prepare(
            'INSERT INTO site_settings (`key`, value) VALUES (:k, :v)
             ON DUPLICATE KEY UPDATE value = VALUES(value)'
        );
        return $stmt->execute([
            ':k' => $key,
            ':v' => $value,
        ]);
    }

    /**
     * Upsert nhiều key/value cùng lúc.
     */
    public static function setMany(array $kv): bool
    {
        global $pdo;
        if (empty($kv)) return true;

        $pdo->beginTransaction();
        try {
            foreach ($kv as $k => $v) {
                self::set((string)$k, $v === null ? null : (string)$v);
            }
            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Chuẩn hoá đường dẫn ảnh để render ra URL.
     *
     * - Nếu value là URL tuyệt đối (http/https) thì giữ nguyên.
     * - Nếu value là path tương đối, tự cộng BASE_URL.
     * - Nếu rỗng, trả fallback.
     */
    public static function imageUrl(?string $value, string $fallbackRelative): string
    {
        $value = trim((string)$value);
        if ($value === '') {
            return BASE_URL . ltrim($fallbackRelative, '/');
        }

        if (preg_match('~^https?://~i', $value)) {
            return $value;
        }

        return BASE_URL . ltrim($value, '/');
    }
}
