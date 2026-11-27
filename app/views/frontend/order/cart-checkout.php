<?php
$money = static fn(int $amount): string => number_format($amount, 0, ',', '.') . ' ₫';
$escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
?>
<section class="min-h-screen bg-slate-50 py-8">
    <div class="mx-auto max-w-4xl px-4">
        <a href="<?= BASE_URL ?>cart" class="text-sm text-blue-700 underline">Quay về giỏ hàng</a>
        <h1 class="my-4 text-2xl font-bold text-vfNavy">Đặt mua phụ kiện</h1>
        <form method="post" action="<?= BASE_URL ?>order/cartcheckout" class="grid gap-4 md:grid-cols-2">
            <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
            <input type="hidden" name="checkout_token" value="<?= $escape($key) ?>">
            <input type="hidden" name="quote_version" value="<?= $escape($cart['quote_version']) ?>">
            <div class="space-y-3 rounded-xl border bg-white p-4">
                <h2 class="font-semibold">Thông tin người nhận</h2>
                <label class="block">Họ tên
                    <input class="mt-1 w-full rounded border p-2" name="full_name" autocomplete="name" value="<?= $escape($form['full_name']) ?>" maxlength="100" required>
                </label>
                <label class="block">Số điện thoại
                    <input class="mt-1 w-full rounded border p-2" name="phone" type="tel" autocomplete="tel" value="<?= $escape($form['phone']) ?>" maxlength="15" required>
                </label>
                <label class="block">Địa chỉ nhận hàng
                    <textarea class="mt-1 w-full rounded border p-2" name="address" autocomplete="street-address" maxlength="200" required><?= $escape($form['address']) ?></textarea>
                </label>
                <p class="text-sm text-slate-700">Thanh toán chuyển khoản sau khi đặt đơn. Trang này chưa thu tiền.</p>
                <label class="flex items-start gap-2 text-sm">
                    <input class="mt-1" type="checkbox" name="agree_terms" value="1" <?= $form['agreed'] ? 'checked' : '' ?> required>
                    Tôi xác nhận thông tin nhận hàng và số lượng trên đơn.
                </label>
            </div>
            <div class="space-y-3 rounded-xl border bg-white p-4">
                <h2 class="font-semibold">Tóm tắt đơn hàng</h2>
                <ul class="space-y-2 border-b pb-3 text-sm">
                    <?php foreach ($cart['items'] as $item): ?>
                        <li><?= $escape($item['product_name']) ?> · <?= $escape($item['variant_name']) ?>
                            × <?= $item['quantity'] ?> — <?= $money($item['unit_price'] * $item['quantity']) ?></li>
                    <?php endforeach; ?>
                </ul>
                <p>Tạm tính: <strong><?= $money($cart['subtotal']) ?></strong></p>
                <label class="block text-sm" for="coupon-code">Mã giảm giá</label>
                <div class="flex gap-2">
                    <input class="min-w-0 flex-1 rounded border p-2" id="coupon-code" name="coupon_code" value="<?= $escape($form['coupon_code']) ?>" maxlength="40">
                    <button class="rounded border px-3" type="submit" name="intent" value="preview" formnovalidate>Xem trước</button>
                </div>
                <p class="text-sm text-slate-600">Xem trước không giữ lượt. Mã được kiểm tra lại khi đặt hàng.</p>
                <?php if ($preview): ?>
                    <p>Giảm dự kiến: <?= $money($discount) ?></p>
                    <p class="font-bold">Tổng dự kiến: <?= $money($cart['subtotal'] - $discount) ?></p>
                <?php endif; ?>
                <button class="w-full rounded-lg bg-vfNavy px-4 py-2 font-semibold text-white" type="submit" name="intent" value="place">Đặt hàng</button>
            </div>
        </form>
    </div>
</section>
