<?php

/** The cart row is the common lock for edits and checkout for one customer. */
final class Cart
{
    public static function lock(PDO $pdo, int $userId, bool $create = false): ?int
    {
        if ($create) {
            $pdo->prepare('INSERT INTO carts (user_id) VALUES (?) ON CONFLICT (user_id) DO NOTHING')
                ->execute([$userId]);
        }
        $stmt = $pdo->prepare('SELECT id FROM carts WHERE user_id = ? FOR UPDATE');
        $stmt->execute([$userId]);
        $id = $stmt->fetchColumn();
        return $id === false ? null : (int)$id;
    }

    public static function items(PDO $pdo, int $userId): array
    {
        $stmt = $pdo->prepare('SELECT i.* FROM cart_items i JOIN carts c ON c.id = i.cart_id
            WHERE c.user_id = ? ORDER BY i.id');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function add(PDO $pdo, int $cartId, string $product, string $variant, int $qty, int $price): void
    {
        $pdo->prepare('INSERT INTO cart_items (cart_id, product_id, variant_id, quantity, unit_price)
            VALUES (?, ?, ?, ?, ?)
            ON CONFLICT (cart_id, product_id, variant_id) DO UPDATE
            SET quantity = LEAST(cart_items.quantity + EXCLUDED.quantity, 99), unit_price = EXCLUDED.unit_price')
            ->execute([$cartId, $product, $variant, $qty, $price]);
    }

    public static function edit(PDO $pdo, int $cartId, int $itemId, ?int $quantity): ?string
    {
        if ($quantity === null) {
            $stmt = $pdo->prepare('DELETE FROM cart_items WHERE cart_id = ? AND id = ? RETURNING product_id');
            $stmt->execute([$cartId, $itemId]);
        } else {
            $stmt = $pdo->prepare('UPDATE cart_items SET quantity = ? WHERE cart_id = ? AND id = ? RETURNING product_id');
            $stmt->execute([$quantity, $cartId, $itemId]);
        }
        $id = $stmt->fetchColumn();
        return $id === false ? null : (string)$id;
    }

    public static function clear(PDO $pdo, int $cartId): void
    {
        $pdo->prepare('DELETE FROM cart_items WHERE cart_id = ?')->execute([$cartId]);
    }

    public static function countForUser(PDO $pdo, int $userId): int
    {
        $stmt = $pdo->prepare('SELECT COALESCE(SUM(i.quantity), 0) FROM cart_items i
            JOIN carts c ON c.id = i.cart_id WHERE c.user_id = ?');
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    public static function fingerprint(array $items): string
    {
        return hash('sha256', json_encode($items, JSON_THROW_ON_ERROR));
    }
}
