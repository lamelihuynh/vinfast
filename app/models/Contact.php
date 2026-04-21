<?php

/**
 * app/models/Contact.php — Customer Contact Message Model
 * Table: contacts
 * Owner: Tang Vu (Member 1)
 * Used by: ContactController (frontend submit), ContactAdminController (admin view)
 *
 * Columns: id, name, email, phone, message,
 *          status(unread|read|replied), created_at
 */
class Contact
{

    public static function create(string $name, string $email, string $phone, string $message): bool
    {
        global $pdo;
        $stmt = $pdo->prepare(
            'INSERT INTO contacts (name, email, phone, message, status) VALUES (?, ?, ?, ?, "unread")'
        );
        return $stmt->execute([$name, $email, $phone !== '' ? $phone : null, $message]);
    }

    public static function getPaginated(int $page = 1, int $perPage = PER_PAGE): array
    {
        global $pdo;
        $page = max(1, $page);
        $offset = max(0, ($page - 1) * $perPage);

        $stmt = $pdo->prepare('SELECT * FROM contacts ORDER BY created_at DESC LIMIT :limit OFFSET :offset');
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function countAll(): int
    {
        global $pdo;
        return (int)$pdo->query('SELECT COUNT(*) FROM contacts')->fetchColumn();
    }

    public static function setStatus(int $id, string $status): bool
    {
        global $pdo;
        $allowed = ['unread', 'read', 'replied'];
        if (!in_array($status, $allowed, true)) return false;
        $stmt = $pdo->prepare('UPDATE contacts SET status = ? WHERE id = ?');
        return $stmt->execute([$status, $id]);
    }

    public static function deleteById(int $id): bool
    {
        global $pdo;
        $stmt = $pdo->prepare('DELETE FROM contacts WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
