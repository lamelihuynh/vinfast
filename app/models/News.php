<?php

/**
 * app/models/News.php — News Article Model
 * Table: news
 * Owner: Nhat Tan (Member 4)
 * Used by: NewsController (frontend), NewsAdminController (admin)
 *
 * Columns: id, author_id, title, slug, body, thumbnail,
 *          meta_title, meta_description, created_at
 */
class News
{
    public const CATALOGS = ['Công ty', 'Ô tô điện', 'Xe máy điện'];
    public const STATES = ['Hiển thị', 'Ẩn'];

    public static function count(string $q = '', string $catalog = '', string $state = 'Hiển thị'): int
    {
        global $pdo;

        $conditions = [];
        $params = [];

        if ($q !== '') {
            $like = '%' . $q . '%';
            $conditions[] = '(n.title LIKE :q OR t.tags LIKE :q2)';
            $params[':q'] = $like;
            $params[':q2'] = $like;
        }

        if ($catalog !== '' && in_array($catalog, self::CATALOGS, true)) {
            $conditions[] = 'n.catalog = :catalog';
            $params[':catalog'] = $catalog;
        }

        if ($state !== '' && in_array($state, self::STATES, true)) {
            $conditions[] = 'n.news_state = :state';
            $params[':state'] = $state;
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $stmt = $pdo->prepare(
            "SELECT COUNT(DISTINCT n.id)
               FROM news n
          LEFT JOIN news_tags t ON t.news_id = n.id
             $where"
        );
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public static function getAll(
        int $page,
        int $perPage,
        string $q = '',
        string $catalog = '',
        string $sort = 'latest',
        string $state = 'Hiển thị'
    ): array {
        global $pdo;

        $offset = ($page - 1) * $perPage;
        $conditions = [];
        $params = [];

        if ($q !== '') {
            $like = '%' . $q . '%';
            $conditions[] = '(n.title LIKE :q OR t.tags LIKE :q2)';
            $params[':q'] = $like;
            $params[':q2'] = $like;
        }

        if ($catalog !== '' && in_array($catalog, self::CATALOGS, true)) {
            $conditions[] = 'n.catalog = :catalog';
            $params[':catalog'] = $catalog;
        }

        if ($state !== '' && in_array($state, self::STATES, true)) {
            $conditions[] = 'n.news_state = :state';
            $params[':state'] = $state;
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        if ($sort === 'id_asc') {
            $orderBy = 'n.id ASC';
        } elseif ($sort === 'popular') {
            $orderBy = 'n.views DESC';
        } elseif ($sort === 'state') {
            $orderBy = 'n.news_state DESC';
        } else {
            $orderBy = 'n.created_at DESC'; 
        }

        $stmt = $pdo->prepare(
            "SELECT DISTINCT n.id
               FROM news n
          LEFT JOIN news_tags t ON t.news_id = n.id
             $where
           ORDER BY $orderBy
              LIMIT :lim OFFSET :off"
        );
        $params[':lim'] = $perPage;
        $params[':off'] = $offset;

        foreach ($params as $key => $val) {
            $type = in_array($key, [':lim', ':off'], true) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($key, $val, $type);
        }
        $stmt->execute();
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($ids)) return [];

        $in = implode(',', array_fill(0, count($ids), '?'));
        $rows = $pdo->prepare(
            "SELECT n.id, n.title, n.slug, n.catalog, n.views, n.news_state, n.created_at, 
                    MIN(i.img_link) as thumbnail 
            FROM news n
        LEFT JOIN news_img_info i ON n.id = i.news_id
            WHERE n.id IN ($in)
        GROUP BY n.id
        ORDER BY $orderBy"
        );
        $rows->execute($ids);
        $articles = $rows->fetchAll(PDO::FETCH_ASSOC);

        $tags = self::fetchTagsByIds($ids);
        foreach ($articles as &$a) {
            $a['tags'] = $tags[$a['id']] ?? [];
        }
        unset($a);

        return $articles;
    }

    public static function getBySlug(string $slug, string $state = 'Hiển thị', bool $incrementView = true): ?array
    {
        global $pdo;

        $where = 'WHERE slug = :slug';
        $params = [':slug' => $slug];

        if ($state !== '' && in_array($state, self::STATES, true)) {
            $where .= ' AND news_state = :state';
            $params[':state'] = $state;
        }

        $stmt = $pdo->prepare("SELECT id, title, slug, body, catalog, news_state, views, created_at FROM news $where LIMIT 1");
        $stmt->execute($params);
        $article = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$article) return null;

        $id = (int) $article['id'];

        if ($incrementView) {
            $pdo->prepare('UPDATE news SET views = views + 1 WHERE id = :id')->execute([':id' => $id]);
            $article['views'] = $article['views'] + 1;
        }

        $article['tags'] = self::fetchTagsByIds([$id])[$id] ?? [];
        $article['images'] = self::fetchImagesByIds([$id])[$id] ?? [];

        return $article;
    }

    public static function getLatest(int $limit = 3, string $state = 'Hiển thị'): array
    {
        global $pdo;
        $limit = max(1, min(12, $limit));

        $where = '';
        if ($state !== '' && in_array($state, self::STATES, true)) {
            $where = 'WHERE news_state = :state';
        }

        $stmt = $pdo->prepare(
            "SELECT n.*, MIN(i.img_link) as thumbnail
               FROM news n
          LEFT JOIN news_img_info i ON n.id = i.news_id
             $where
           GROUP BY n.id
           ORDER BY n.created_at DESC
              LIMIT :limit"
        );
        if ($where !== '') $stmt->bindValue(':state', $state, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function getById(int $id): ?array
    {
        global $pdo;
        $stmt = $pdo->prepare('SELECT id, title, slug, news_state FROM news WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function create(array $data, array $tags = [], array $images = []): int
    {
        global $pdo;

        $state = in_array($data['news_state'] ?? '', self::STATES, true) ? $data['news_state'] : 'Hiển thị';

        $stmt = $pdo->prepare(
            'INSERT INTO news (title, slug, body, catalog, news_state) VALUES (:title, :slug, :body, :catalog, :news_state)'
        );
        $stmt->execute([
            ':title' => $data['title'], ':slug' => $data['slug'], ':body' => $data['body'],
            ':catalog' => $data['catalog'] ?? null, ':news_state' => $state,
        ]);

        $id = (int) $pdo->lastInsertId();
        if ($id === 0) return 0;

        self::syncTags($id, $tags);
        self::syncImages($id, $images);

        return $id;
    }

    public static function isSlugExists(string $slug, int $excludeId = 0): bool
    {
        global $pdo;
        $sql = "SELECT COUNT(*) FROM news WHERE slug = :slug";
        $params = [':slug' => $slug];
        if ($excludeId > 0) {
            $sql .= " AND id != :id";
            $params[':id'] = $excludeId;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function update(int $id, array $data, array $tags = [], array $images = []): bool
    {
        global $pdo;
        $state = in_array($data['news_state'] ?? '', self::STATES, true) ? $data['news_state'] : 'Hiển thị';

        $stmt = $pdo->prepare(
            'UPDATE news SET title = :title, slug = :slug, body = :body, catalog = :catalog, news_state = :news_state WHERE id = :id'
        );
        $ok = $stmt->execute([
            ':title' => $data['title'], ':slug' => $data['slug'], ':body' => $data['body'],
            ':catalog' => $data['catalog'] ?? null, ':news_state' => $state, ':id' => $id,
        ]);

        if ($ok) {
            self::syncTags($id, $tags);
            self::syncImages($id, $images); 
        }
        return $ok;
    }

    public static function delete(int $id): bool
    {
        global $pdo;
        self::syncTags($id, []);
        
        $pdo->prepare('DELETE FROM news_img_info WHERE news_id = :id')->execute([':id' => $id]);
        
        $stmt = $pdo->prepare('DELETE FROM news WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    private static function fetchTagsByIds(array $ids): array
    {
        global $pdo;
        if (empty($ids)) return [];
        $in = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("SELECT news_id, tags FROM news_tags WHERE news_id IN ($in)");
        $stmt->execute($ids);
        $result = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $result[$row['news_id']][] = $row['tags'];
        }
        return $result;
    }

    private static function fetchImagesByIds(array $ids): array
    {
        global $pdo;
        if (empty($ids)) return [];
        $in = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("SELECT news_id, img_link, img_des FROM news_img_info WHERE news_id IN ($in)");
        $stmt->execute($ids);
        $result = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $result[$row['news_id']][] = [
                'img_link' => $row['img_link'],
                'img_des' => $row['img_des'],
            ];
        }
        return $result;
    }

    private static function syncTags(int $newsId, array $tags): void
    {
        global $pdo;
        $pdo->prepare('DELETE FROM news_tags WHERE news_id = :id')->execute([':id' => $newsId]);
        if (empty($tags)) return;
        $stmt = $pdo->prepare('INSERT IGNORE INTO news_tags (news_id, tags) VALUES (:news_id, :tag)');
        foreach (array_unique($tags) as $tag) {
            $tag = trim((string) $tag);
            if ($tag !== '') $stmt->execute([':news_id' => $newsId, ':tag' => $tag]);
        }
    }

    private static function syncImages(int $newsId, array $images): void
    {
        global $pdo;
        $stmt = $pdo->prepare('SELECT img_link FROM news_img_info WHERE news_id = :id');
        $stmt->execute([':id' => $newsId]);
        $existingRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $existingMap = [];
        foreach ($existingRows as $row) {
            $fileName = basename(str_replace('\\', '/', $row['img_link']));
            $existingMap[$fileName] = $row['img_link'];
        }

        $stmtInsert = $pdo->prepare('INSERT INTO news_img_info (news_id, img_link, img_des) VALUES (:news_id, :img_link, :img_des)');
        
        $stmtUpdate = $pdo->prepare('UPDATE news_img_info SET img_des = :img_des WHERE news_id = :news_id AND img_link = :old_link');

        foreach ($images as $img) {
            $link = trim((string) ($img['img_link'] ?? ''));
            $des = trim((string) ($img['img_des'] ?? ''));

            if ($link !== '') {
                $fileName = basename(str_replace('\\', '/', $link));

                if (isset($existingMap[$fileName])) {
                    $stmtUpdate->execute([
                        ':img_des'  => $des,
                        ':news_id'  => $newsId,
                        ':old_link' => $existingMap[$fileName]
                    ]);
                } else {
                    $stmtInsert->execute([
                        ':news_id'  => $newsId,
                        ':img_link' => $link,
                        ':img_des'  => $des
                    ]);
                }
            }
        }
    }
}