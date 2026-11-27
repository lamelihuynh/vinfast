<?php

// PostgreSQL integration test. The catalog is a fixture: this script does not read/write the real MongoDB.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit(1);
}
require_once __DIR__ . '/../../app/core/Db.php';
require_once __DIR__ . '/../../app/core/Exceptions.php';
foreach (['models/Cart', 'services/CartService', 'services/CouponService', 'services/InventoryService', 'services/OrderService'] as $file) {
    require_once __DIR__ . '/../../app/' . $file . '.php';
}

final class Product
{
    public static function getByIds(array $ids, bool $includeInactive = false): array
    {
        $products = [];
        foreach ($ids as $id) {
            $products[$id] = ['name' => 'Phụ kiện kiểm thử', 'is_accessory' => true, 'is_active' => 1, 'variants' => [
                ['variantId' => $id . '-STD', 'name' => 'Tiêu chuẩn', 'isActive' => true, 'price' => 100000],
            ]];
        }
        return $products;
    }
}
final class ActivityLog
{
    public static function record(string $action, string $id, array $metadata = []): void {}
}

function expect(bool $ok, string $message): void
{
    if (!$ok) {
        throw new RuntimeException($message);
    }
}

if (Db::env('APP_ENV') !== 'bench' || Db::env('PG_DB') !== 'vinfast_t4_test') {
    throw new RuntimeException('Chỉ chạy smoke với APP_ENV=bench và PG_DB=vinfast_t4_test.');
}

$pdo = Db::pg();
expect($pdo->query('SELECT current_database()')->fetchColumn() === 'vinfast_t4_test', 'Sai database kiểm thử.');
$suffix = bin2hex(random_bytes(6));
$pidA = 't4a_' . $suffix;
$pidB = 't4b_' . $suffix;
$code = 'T4_' . $suffix;
$uids = [];
$insertUser = $pdo->prepare("INSERT INTO users (name, email, password) VALUES ('T4 smoke', ?, '!') RETURNING id");
$insertInv = $pdo->prepare('INSERT INTO inventory (product_id, variant_id, stock) VALUES (?, ?, 5)');
$coupon = $pdo->prepare("INSERT INTO coupons (code, discount_type, discount_value, usage_limit, expires_at)
    VALUES (?, 'percent', 10, 1, now() + interval '1 day')");

$scalar = static function (string $sql, array $args) use ($pdo): int {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($args);
    return (int)$stmt->fetchColumn();
};

try {
    for ($i = 0; $i < 2; $i++) {
        $insertUser->execute(["t4-{$suffix}-{$i}@example.invalid"]);
        $uids[] = (int)$insertUser->fetchColumn();
    }
    [$a, $b] = $uids;
    foreach ([$pidA, $pidB] as $id) {
        $insertInv->execute([$id, $id . '-STD']);
    }
    $coupon->execute([$code]);
    CartService::add($a, $pidA, $pidA . '-STD', 1);
    CartService::add($a, $pidB, $pidB . '-STD', 1);
    CartService::add($b, $pidA, $pidA . '-STD', 1);

    $state = static fn(): array => [
        $scalar('SELECT count(*) FROM orders WHERE user_id = ?', [$a]),
        $scalar('SELECT stock FROM inventory WHERE product_id = ?', [$pidA]),
        $scalar('SELECT stock FROM inventory WHERE product_id = ?', [$pidB]),
        $scalar('SELECT used_count FROM coupons WHERE code = ?', [$code]),
        $scalar('SELECT count(*) FROM coupon_redemptions WHERE user_id = ?', [$a]),
        Cart::countForUser($pdo, $a),
    ];
    $initial = [0, 5, 5, 0, 0, 2];
    expect($state() === $initial, 'Fixture không đúng.');
    $key = bin2hex(random_bytes(16));
    $quote = CartService::summary($a)['quote_version'];
    foreach (['after_order', 'after_items', 'after_inventory', 'after_cart_delete'] as $point) {
        putenv('FAIL_AT=' . $point);
        try {
            OrderService::placeFromCart($a, $key, $quote, $code, []);
            throw new LogicException('Thiếu lỗi mô phỏng.');
        } catch (RuntimeException $e) {
            expect($e->getMessage() === "FAIL_AT: {$point}", 'Sai lỗi mô phỏng: ' . $e->getMessage());
        } finally {
            putenv('FAIL_AT');
        }
        expect($state() === $initial, "Rollback thiếu tại {$point}.");
        echo "PASS rollback {$point}\n";
    }
    $pdo->prepare('UPDATE inventory SET stock = 0 WHERE product_id = ?')->execute([$pidB]);
    try {
        OrderService::placeFromCart($a, $key, $quote, $code, []);
        throw new LogicException('Đơn phải thất bại vì món thứ hai hết hàng.');
    } catch (OutOfStockException $e) {
        expect($state() === [0, 5, 0, 0, 0, 2], 'Hết hàng nhưng rollback không sạch.');
        echo "PASS thiếu hàng ở món thứ hai\n";
    } finally {
        $pdo->prepare('UPDATE inventory SET stock = 5 WHERE product_id = ?')->execute([$pidB]);
    }
    $id = OrderService::placeFromCart($a, $key, $quote, $code, []);
    expect($state() === [1, 4, 4, 1, 1, 0], 'Commit chưa ghi đủ đơn, kho, coupon và giỏ.');
    expect($scalar('SELECT count(*) FROM order_items WHERE order_id = ?', [$id]) === 2, 'Thiếu dòng đơn.');
    expect($scalar('SELECT total_amount FROM orders WHERE id = ?', [$id]) === 180000, 'Sai số tiền giảm.');
    expect(OrderService::placeFromCart($a, $key, $quote, $code, []) === $id, 'Submit lại không trả đúng đơn.');
    expect($state() === [1, 4, 4, 1, 1, 0], 'Submit lại có tác dụng phụ.');
    echo "PASS commit + giỏ trống + gửi lại cùng khóa\n";

    try {
        OrderService::placeFromCart($b, bin2hex(random_bytes(16)), CartService::summary($b)['quote_version'], $code, []);
        throw new LogicException('Không được cấp vượt hạn mức.');
    } catch (CouponException $e) {
        expect(Cart::countForUser($pdo, $b) === 1
            && $scalar('SELECT count(*) FROM orders WHERE user_id = ?', [$b]) === 0, 'Từ chối coupon làm thay đổi đơn hoặc giỏ.');
        echo "PASS từ chối mã hết lượt\n";
    }

    $pdo->prepare('UPDATE coupons SET usage_limit = 2 WHERE code = ?')->execute([$code]);
    CartService::add($a, $pidA, $pidA . '-STD', 1);
    try {
        OrderService::placeFromCart($a, bin2hex(random_bytes(16)), CartService::summary($a)['quote_version'], $code, []);
        throw new LogicException('Người dùng không được dùng lại cùng mã.');
    } catch (CouponException $e) {
        expect($state() === [1, 4, 4, 1, 1, 1], 'Dùng lại coupon làm đổi dữ liệu.');
        echo "PASS từ chối dùng lại mã\n";
    }

    $pdo->prepare("UPDATE coupons SET starts_at = now() - interval '2 days',
        expires_at = now() - interval '1 day' WHERE code = ?")->execute([$code]);
    try {
        OrderService::placeFromCart($b, bin2hex(random_bytes(16)), CartService::summary($b)['quote_version'], $code, []);
        throw new LogicException('Không được chấp nhận mã hết hạn.');
    } catch (CouponException $e) {
        expect($scalar('SELECT count(*) FROM orders WHERE user_id = ?', [$b]) === 0, 'Mã hết hạn vẫn tạo đơn.');
        echo "PASS từ chối mã hết hạn\n";
    }

    $insertInv->execute([$pidB, $pidB . '-ALT']);
    $vehicle = ['product_id' => $pidB, 'name' => 'Xe kiểm thử', 'is_active' => 1,
        'specs' => ['deposit_amount' => 15000000], 'variants' => [
            ['variantId' => $pidB . '-STD', 'name' => 'Tiêu chuẩn', 'isActive' => true],
            ['variantId' => $pidB . '-ALT', 'name' => 'Nâng cao', 'isActive' => true],
        ]];
    $depositKey = bin2hex(random_bytes(16));
    $deposit = OrderService::placeDeposit($b, $vehicle, $pidB . '-ALT', ['color' => []], $depositKey);
    expect(OrderService::placeDeposit($b, $vehicle, $pidB . '-ALT', ['color' => []], $depositKey) === $deposit,
        'Gửi lại đặt cọc tạo đơn khác.');
    expect($scalar('SELECT count(*) FROM order_items WHERE order_id = ? AND variant_id = ?', [$deposit, $pidB . '-ALT']) === 1
        && $scalar('SELECT stock FROM inventory WHERE product_id = ? AND variant_id = ?', [$pidB, $pidB . '-ALT']) === 4
        && $scalar('SELECT stock FROM inventory WHERE product_id = ? AND variant_id = ?', [$pidB, $pidB . '-STD']) === 4
        && $scalar('SELECT total_amount FROM orders WHERE id = ?', [$deposit]) === 15000000,
        'Đặt cọc không ghi đúng giá hoặc phiên bản.');
    echo "PASS đặt cọc đúng phiên bản + gửi lại\n";
} finally {
    foreach ($uids as $uid) {
        $pdo->prepare('DELETE FROM orders WHERE user_id = ?')->execute([$uid]);
        $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$uid]);
    }
    $pdo->prepare('DELETE FROM coupons WHERE code = ?')->execute([$code]);
    $pdo->prepare('DELETE FROM inventory WHERE product_id IN (?, ?)')->execute([$pidA, $pidB]);
}
