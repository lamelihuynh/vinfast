<?php

class AboutAward
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function all(): array
    {
        $stmt = $this->pdo->query("
            SELECT a.id, a.title, a.award_year, a.image_path,
                   to_char(a.created_at, 'YYYY-MM-DD HH24:MI:SS') AS created_at
            FROM about_awards a
            ORDER BY a.award_year DESC, a.id DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(string $title, int $year, string $imagePath): bool
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO about_awards(title, award_year, image_path)
            VALUES (?, ?, ?)
        ");

        return $stmt->execute([$title, $year, $imagePath]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT image_path FROM about_awards WHERE id = ?
        ");
        $stmt->execute([$id]);

        $award = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($award && file_exists(UPLOAD_PATH . $award['image_path'])) {
            unlink(UPLOAD_PATH . $award['image_path']);
        }

        $stmt = $this->pdo->prepare("
            DELETE FROM about_awards WHERE id = ?
        ");

        return $stmt->execute([$id]);
    }
}
