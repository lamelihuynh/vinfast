<?php

/**
 * database/seed/legacy_content.php — Nội dung website lấy từ DB production cũ (legacy_data.json):
 * giải thưởng (about), FAQ, tin tức + ảnh + tag, bình luận, email nhận tin.
 *
 * Được seed.php gọi trong cùng transaction PG. Các bảng rỗng trong dump vẫn được xử lý
 * (comments, news_tags, email) để seed lại từ dump mới không phải sửa code.
 *
 * @return array<string,int> số dòng đã chèn theo bảng
 */
function seedLegacyContent(PDO $pdo, array $legacy): array
{
    $bool = static fn($v): string => !empty($v) && $v !== '0' ? 'true' : 'false';
    $count = [];

    $stmt = $pdo->prepare('INSERT INTO about_awards (id, title, award_year, image_path, created_at) VALUES (?, ?, ?, ?, COALESCE(CAST(? AS timestamptz), now()))');
    foreach ($legacy['about_awards'] ?? [] as $r) {
        $stmt->execute([(int)$r['id'], $r['title'], (int)$r['award_year'], $r['image_path'], $r['created_at'] ?? null]);
    }
    $count['about_awards'] = count($legacy['about_awards'] ?? []);

    $stmt = $pdo->prepare('INSERT INTO faq_topics (id, name, slug, icon_svg, sort_order, is_active, created_at) VALUES (?, ?, ?, ?, ?, CAST(? AS boolean), COALESCE(CAST(? AS timestamptz), now()))');
    foreach ($legacy['faq_topics'] ?? [] as $r) {
        $stmt->execute([(int)$r['id'], $r['name'], $r['slug'], $r['icon_svg'], (int)$r['sort_order'], $bool($r['is_active']), $r['created_at'] ?? null]);
    }
    $count['faq_topics'] = count($legacy['faq_topics'] ?? []);

    $stmt = $pdo->prepare('INSERT INTO faqs (id, topic_id, question, answer, sort_order, is_active, created_at) VALUES (?, ?, ?, ?, ?, CAST(? AS boolean), COALESCE(CAST(? AS timestamptz), now()))');
    foreach ($legacy['faqs'] ?? [] as $r) {
        $stmt->execute([(int)$r['id'], (int)$r['topic_id'], $r['question'], $r['answer'], (int)$r['sort_order'], $bool($r['is_active']), $r['created_at'] ?? null]);
    }
    $count['faqs'] = count($legacy['faqs'] ?? []);

    $catalogs = ['Công ty', 'Ô tô điện', 'Xe máy điện'];
    $stmt = $pdo->prepare('INSERT INTO news (id, title, slug, body, catalog, views, news_state, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, COALESCE(CAST(? AS timestamptz), now()))');
    foreach ($legacy['news'] ?? [] as $r) {
        $stmt->execute([
            (int)$r['id'], $r['title'], $r['slug'], $r['body'],
            in_array($r['catalog'] ?? null, $catalogs, true) ? $r['catalog'] : null,
            max(0, (int)($r['views'] ?? 0)),
            ($r['news_state'] ?? '') === 'Ẩn' ? 'Ẩn' : 'Hiển thị',
            $r['created_at'] ?? null,
        ]);
    }
    $count['news'] = count($legacy['news'] ?? []);

    $stmt = $pdo->prepare('INSERT INTO news_tags (news_id, tags) VALUES (?, ?) ON CONFLICT DO NOTHING');
    foreach ($legacy['news_tags'] ?? [] as $r) {
        $stmt->execute([(int)$r['news_id'], $r['tags']]);
    }
    $count['news_tags'] = count($legacy['news_tags'] ?? []);

    // Dump Windows có đường dẫn dạng public\images\news\2\1.png -> chuẩn hóa dấu /
    $stmt = $pdo->prepare('INSERT INTO news_img_info (news_id, img_link, img_des) VALUES (?, ?, ?) ON CONFLICT DO NOTHING');
    foreach ($legacy['news_img_info'] ?? [] as $r) {
        $stmt->execute([(int)$r['news_id'], str_replace('\\', '/', (string)$r['img_link']), (string)($r['img_des'] ?? '')]);
    }
    $count['news_img_info'] = count($legacy['news_img_info'] ?? []);

    // Bình luận chỉ giữ dòng có user tồn tại (tài khoản thật không được seed)
    $stmt = $pdo->prepare('INSERT INTO comments (id, user_id, news_id, rating, body, helpful_count, is_approved, created_at)
        SELECT ?, ?, ?, ?, ?, ?, CAST(? AS boolean), COALESCE(CAST(? AS timestamptz), now()) WHERE EXISTS (SELECT 1 FROM users WHERE id = ?)');
    $count['comments'] = 0;
    foreach ($legacy['comments'] ?? [] as $r) {
        $stmt->execute([(int)$r['id'], (int)$r['user_id'], $r['news_id'], max(0, min(5, (int)$r['rating'])), $r['body'], (int)$r['helpful_count'], $bool($r['is_approved']), $r['created_at'] ?? null, (int)$r['user_id']]);
        $count['comments'] += $stmt->rowCount();
    }

    $stmt = $pdo->prepare('INSERT INTO email_subscriptions (email) VALUES (?) ON CONFLICT ((lower(email))) DO NOTHING');
    foreach ($legacy['email'] ?? [] as $r) {
        $stmt->execute([trim((string)$r['email'])]);
    }
    $count['email_subscriptions'] = count($legacy['email'] ?? []);

    return $count;
}
