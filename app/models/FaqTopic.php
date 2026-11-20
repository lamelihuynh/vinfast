<?php

/**
 * FaqTopic — table faq_topics (PostgreSQL).
 * is_active (BOOLEAN) is returned as 0/1 and created_at as 'YYYY-MM-DD HH24:MI:SS'
 * so views keep the old MySQL shape.
 */
class FaqTopic {

    private const COLS = "t.id, t.name, t.slug, t.icon_svg, t.sort_order,
                t.is_active::int AS is_active,
                to_char(t.created_at, 'YYYY-MM-DD HH24:MI:SS') AS created_at";

    public static function all(): array {
        global $pdo;

        $stmt = $pdo->query("
            SELECT " . self::COLS . "
            FROM faq_topics t
            ORDER BY t.sort_order ASC, t.created_at DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function allActive(): array {
        global $pdo;

        $stmt = $pdo->query("
            SELECT " . self::COLS . "
            FROM faq_topics t
            WHERE t.is_active
            ORDER BY t.sort_order ASC, t.created_at DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function getById(int $id): ?array {
        global $pdo;

        $stmt = $pdo->prepare("
            SELECT " . self::COLS . "
            FROM faq_topics t
            WHERE t.id = ?
            LIMIT 1
        ");

        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function create(
        string $name,
        string $slug,
        string $iconSvg = '',
        int $sortOrder = 0,
        bool $isActive = true
    ): bool {

        global $pdo;

        $stmt = $pdo->prepare("
            INSERT INTO faq_topics
            (name, slug, icon_svg, sort_order, is_active)
            VALUES
            (:name, :slug, :icon, :sort_order, :active)
        ");

        return $stmt->execute([
            ':name' => trim($name),
            ':slug' => trim($slug),
            ':icon' => trim($iconSvg),
            ':sort_order' => $sortOrder,
            ':active' => $isActive ? 'true' : 'false'
        ]);
    }

    public static function update(
        int $id,
        string $name,
        string $slug,
        string $iconSvg = '',
        int $sortOrder = 0,
        bool $isActive = true
    ): bool {

        global $pdo;

        $stmt = $pdo->prepare("
            UPDATE faq_topics
            SET
                name = :name,
                slug = :slug,
                icon_svg = :icon,
                sort_order = :sort_order,
                is_active = :active
            WHERE id = :id
        ");

        return $stmt->execute([
            ':id' => $id,
            ':name' => trim($name),
            ':slug' => trim($slug),
            ':icon' => trim($iconSvg),
            ':sort_order' => $sortOrder,
            ':active' => $isActive ? 'true' : 'false'
        ]);
    }

    public static function delete(int $id): bool {
        global $pdo;

        $stmt = $pdo->prepare("
            DELETE FROM faq_topics
            WHERE id = ?
        ");

        return $stmt->execute([$id]);
    }
}
