<?php

final class CouponService
{
    /** A preview is read-only. During checkout, lock the coupon until commit. */
    public static function quote(PDO $pdo, string $code, int $userId, int $subtotal): array
    {
        if ($code === '') {
            return [null, 0];
        }
        if (strlen($code) > 40) {
            throw new CouponException('Mã giảm giá không hợp lệ.');
        }
        $stmt = $pdo->prepare('SELECT *, clock_timestamp() BETWEEN starts_at AND expires_at AS valid_now
            FROM coupons WHERE upper(code) = upper(?)' . ($pdo->inTransaction() ? ' FOR UPDATE' : ''));
        $stmt->execute([$code]);
        $coupon = $stmt->fetch();
        if (!$coupon || !$coupon['is_active'] || !$coupon['valid_now']) {
            throw new CouponException('Mã giảm giá không tồn tại, chưa có hiệu lực hoặc đã hết hạn.');
        }
        if ($coupon['usage_limit'] !== null && (int)$coupon['used_count'] >= (int)$coupon['usage_limit']) {
            throw new CouponException('Mã giảm giá đã hết lượt.');
        }
        if ($subtotal < (int)$coupon['min_order_amount']) {
            throw new CouponException('Đơn hàng chưa đạt giá trị tối thiểu của mã.');
        }
        $used = $pdo->prepare('SELECT 1 FROM coupon_redemptions WHERE coupon_id = ? AND user_id = ?');
        $used->execute([$coupon['id'], $userId]);
        if ($used->fetchColumn() !== false) {
            throw new CouponException('Bạn đã sử dụng mã giảm giá này.');
        }
        $value = (int)$coupon['discount_value'];
        $discount = $coupon['discount_type'] === 'percent' ? intdiv($subtotal * $value + 50, 100) : $value;
        return [(int)$coupon['id'], min($subtotal, $discount, (int)($coupon['max_discount'] ?? $discount))];
    }

    public static function redeem(PDO $pdo, int $couponId, int $userId, int $orderId, int $discount): void
    {
        $stmt = $pdo->prepare('UPDATE coupons SET used_count = used_count + 1
            WHERE id = ? AND is_active AND clock_timestamp() BETWEEN starts_at AND expires_at
              AND (usage_limit IS NULL OR used_count < usage_limit)');
        $stmt->execute([$couponId]);
        if ($stmt->rowCount() !== 1) {
            throw new CouponException('Mã giảm giá đã hết lượt hoặc không còn hiệu lực.');
        }
        $stmt = $pdo->prepare('INSERT INTO coupon_redemptions (coupon_id, user_id, order_id, discount_amount)
            VALUES (?, ?, ?, ?) ON CONFLICT (coupon_id, user_id) DO NOTHING');
        $stmt->execute([$couponId, $userId, $orderId, $discount]);
        if ($stmt->rowCount() !== 1) {
            throw new CouponException('Bạn đã sử dụng mã giảm giá này.');
        }
    }
}
