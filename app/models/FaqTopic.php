<?php

class FaqTopic {

    public static function all(): array {
        global $pdo;

        $stmt = $pdo->query("
            SELECT *
            FROM faq_topics
            ORDER BY sort_order ASC, created_at DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function allActive(): array {
        global $pdo;

        $stmt = $pdo->query("
            SELECT *
            FROM faq_topics
            WHERE is_active = 1
            ORDER BY sort_order ASC, created_at DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function getById(int $id): ?array {
        global $pdo;

        $stmt = $pdo->prepare("
            SELECT *
            FROM faq_topics
            WHERE id = ?
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
            ':active' => $isActive ? 1 : 0
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
            ':active' => $isActive ? 1 : 0
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