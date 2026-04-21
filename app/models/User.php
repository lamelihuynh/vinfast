<?php

/**
 * app/models/User.php — User Model
 * Table: users
 * Owner: All members (common)
 * Shared by: AuthController, UserController, UserAdminController
 *
 * Columns: id, name, email, password, role(member|admin),
 *          avatar, is_locked, created_at
 */
class User
{
    public function findById(int $id): ?array
    {
        global $pdo;
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        global $pdo;
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function create(array $data): bool
    {
        global $pdo;
        $stmt = $pdo->prepare(
            'INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)'
        );

        return $stmt->execute([
            (string)$data['name'],
            (string)$data['email'],
            (string)$data['password'],
            (string)($data['role'] ?? 'member'),
        ]);
    }

    public function update(int $id, array $data): bool
    {
        global $pdo;

        $fields = [];
        $params = [];

        if (array_key_exists('name', $data)) {
            $fields[] = 'name = ?';
            $params[] = (string)$data['name'];
        }
        if (array_key_exists('email', $data)) {
            $fields[] = 'email = ?';
            $params[] = (string)$data['email'];
        }
        if (array_key_exists('avatar', $data) && $data['avatar'] !== null && $data['avatar'] !== '') {
            $fields[] = 'avatar = ?';
            $params[] = (string)$data['avatar'];
        }

        if (empty($fields)) {
            return true;
        }

        $params[] = $id;
        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function changePassword(int $id, string $newPassword): bool
    {
        global $pdo;
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
        return $stmt->execute([$hash, $id]);
    }
}
