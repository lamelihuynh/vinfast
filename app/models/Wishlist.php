<?php

/**
 * app/models/Wishlist.php — Wishlist Model (PostgreSQL: wishlists, wishlist_items)
 * Owner: T2 (Indexing)
 *
 * Mọi thao tác thêm/bỏ đều idempotent nhờ UNIQUE constraints:
 *   wishlists(user_id, name) -> uq_wishlist_user_name
 *   wishlist_items(wishlist_id, product_id, variant_id) -> uq_wishlist_item (NULLS NOT DISTINCT)
 */
class Wishlist
{
    public const DEFAULT_NAME = 'Yêu thích';

    public static function ensureWishlistId(int $userId): int
    {
        $stmt = Db::pg()->prepare(
            'INSERT INTO wishlists (user_id, name) VALUES (:u, :n)
             ON CONFLICT (user_id, name) DO UPDATE SET name = EXCLUDED.name
             RETURNING id'
        );
        $stmt->bindValue(':u', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':n', self::DEFAULT_NAME, PDO::PARAM_STR);
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    public static function findWishlistId(int $userId): ?int
    {
        $stmt = Db::pg()->prepare(
            'SELECT id FROM wishlists WHERE user_id = :u AND name = :n LIMIT 1'
        );
        $stmt->bindValue(':u', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':n', self::DEFAULT_NAME, PDO::PARAM_STR);
        $stmt->execute();
        $id = $stmt->fetchColumn();

        return $id !== false ? (int)$id : null;
    }

    public static function set(int $userId, string $productId, ?string $variantId, bool $on): bool
    {
        $productId = trim($productId);
        $variantId = ($variantId === null || trim($variantId) === '') ? null : trim($variantId);
        $pdo = Db::pg();

        if ($on) {
            $wid = self::ensureWishlistId($userId);
            $stmt = $pdo->prepare(
                'INSERT INTO wishlist_items (wishlist_id, product_id, variant_id)
                 VALUES (:w, :p, :v)
                 ON CONFLICT ON CONSTRAINT uq_wishlist_item DO NOTHING'
            );
            $stmt->bindValue(':w', $wid, PDO::PARAM_INT);
            $stmt->bindValue(':p', $productId, PDO::PARAM_STR);
            if ($variantId === null) {
                $stmt->bindValue(':v', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':v', $variantId, PDO::PARAM_STR);
            }
            $stmt->execute();
            return true;
        }

        $wid = self::findWishlistId($userId);
        if ($wid === null) {
            return false;
        }

        // Tách nhánh IS NULL vs = :v để Postgres dùng trọn index uq_wishlist_item
        if ($variantId === null) {
            $stmt = $pdo->prepare(
                'DELETE FROM wishlist_items WHERE wishlist_id = :w AND product_id = :p AND variant_id IS NULL'
            );
            $stmt->bindValue(':w', $wid, PDO::PARAM_INT);
            $stmt->bindValue(':p', $productId, PDO::PARAM_STR);
        } else {
            $stmt = $pdo->prepare(
                'DELETE FROM wishlist_items WHERE wishlist_id = :w AND product_id = :p AND variant_id = :v'
            );
            $stmt->bindValue(':w', $wid, PDO::PARAM_INT);
            $stmt->bindValue(':p', $productId, PDO::PARAM_STR);
            $stmt->bindValue(':v', $variantId, PDO::PARAM_STR);
        }
        $stmt->execute();

        return false;
    }

    public static function has(int $userId, string $productId, ?string $variantId = null): bool
    {
        $wid = self::findWishlistId($userId);
        if ($wid === null) {
            return false;
        }

        $productId = trim($productId);
        $variantId = ($variantId === null || trim($variantId) === '') ? null : trim($variantId);
        $pdo = Db::pg();

        // Tránh IS NOT DISTINCT FROM vì không đưa được cột variant_id vào Index Cond
        if ($variantId === null) {
            $stmt = $pdo->prepare(
                'SELECT 1 FROM wishlist_items WHERE wishlist_id = :w AND product_id = :p AND variant_id IS NULL LIMIT 1'
            );
            $stmt->bindValue(':w', $wid, PDO::PARAM_INT);
            $stmt->bindValue(':p', $productId, PDO::PARAM_STR);
        } else {
            $stmt = $pdo->prepare(
                'SELECT 1 FROM wishlist_items WHERE wishlist_id = :w AND product_id = :p AND variant_id = :v LIMIT 1'
            );
            $stmt->bindValue(':w', $wid, PDO::PARAM_INT);
            $stmt->bindValue(':p', $productId, PDO::PARAM_STR);
            $stmt->bindValue(':v', $variantId, PDO::PARAM_STR);
        }
        $stmt->execute();

        return $stmt->fetchColumn() !== false;
    }

    /** @return string[] Danh sách product_id đã thích (1 query cho cả trang /products) */
    public static function productIdsOf(int $userId): array
    {
        $stmt = Db::pg()->prepare(
            'SELECT DISTINCT wi.product_id
               FROM wishlists w
               JOIN wishlist_items wi ON wi.wishlist_id = w.id
              WHERE w.user_id = :u AND w.name = :n'
        );
        $stmt->bindValue(':u', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':n', self::DEFAULT_NAME, PDO::PARAM_STR);
        $stmt->execute();

        return array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []);
    }

    /** @return array<int, array{id:int, product_id:string, variant_id:?string, created_at:string}> */
    public static function listOf(int $userId): array
    {
        $stmt = Db::pg()->prepare(
            "SELECT wi.id, wi.product_id, wi.variant_id,
                    to_char(wi.created_at, 'YYYY-MM-DD HH24:MI:SS') AS created_at
               FROM wishlists w
               JOIN wishlist_items wi ON wi.wishlist_id = w.id
              WHERE w.user_id = :u AND w.name = :n
              ORDER BY wi.created_at DESC, wi.id DESC"
        );
        $stmt->bindValue(':u', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':n', self::DEFAULT_NAME, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
