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

    public static function getPaginated(int $page = 1, int $perPage = PER_PAGE, ?string $status = null): array
    {
        global $pdo;
        $page = max(1, $page);
        $offset = max(0, ($page - 1) * $perPage);

        $sql = 'SELECT * FROM contacts';
        $params = [];
        if ($status !== null && $status !== '') {
            $sql .= ' WHERE status = :status';
            $params[':status'] = $status;
        }
        $sql .= ' ORDER BY FIELD(status, "unread", "read", "replied"), updated_at DESC, created_at DESC LIMIT :limit OFFSET :offset';

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function countAll(?string $status = null): int
    {
        global $pdo;
        $sql = 'SELECT COUNT(*) FROM contacts';
        if ($status !== null && $status !== '') {
            $stmt = $pdo->prepare($sql . ' WHERE status = ?');
            $stmt->execute([$status]);
            return (int)$stmt->fetchColumn();
        }
        return (int)$pdo->query($sql)->fetchColumn();
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
