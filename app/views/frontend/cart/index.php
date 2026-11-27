<?php
$money = static fn(int $amount): string => number_format($amount, 0, ',', '.') . ' ₫';
$token = Auth::csrfToken();
?>
<section class="min-h-screen bg-slate-50 py-8">
    <div class="mx-auto max-w-5xl px-4">
        <h1 class="mb-4 text-2xl font-bold text-vfNavy">Giỏ hàng phụ kiện</h1>
        <?php if (!$cart['items']): ?>
            <p class="mb-4">Giỏ hàng của bạn đang trống.</p>
            <a class="text-blue-700 underline" href="<?= BASE_URL ?>products?type=accessory">Xem phụ kiện</a>
        <?php else: ?>
            <ul class="divide-y rounded-xl border bg-white">
                <?php foreach ($cart['items'] as $item): ?>
                    <li class="flex flex-wrap items-center justify-between gap-4 p-4">
                        <div>
                            <a class="font-semibold text-vfNavy" href="<?= BASE_URL ?>products/detail/<?= (int)$item['product_id'] ?>">
                                <?= htmlspecialchars($item['product_name']) ?>
                            </a>
                            <p class="text-sm text-slate-600"><?= htmlspecialchars($item['variant_name']) ?> · <?= $money($item['unit_price']) ?></p>
                            <?php if (!$item['available']): ?><p class="text-sm text-red-700">Phiên bản không còn bán</p><?php endif; ?>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <form method="post" action="<?= BASE_URL ?>cart/update" class="flex items-center gap-2">
                                <input type="hidden" name="_csrf" value="<?= $token ?>">
                                <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                <input aria-label="Số lượng" class="w-20 rounded border p-2" type="number" name="quantity"
                                       min="1" max="99" required value="<?= $item['quantity'] ?>">
                                <button class="rounded border px-3 py-2" type="submit">Cập nhật</button>
                            </form>
                            <form method="post" action="<?= BASE_URL ?>cart/remove">
                                <input type="hidden" name="_csrf" value="<?= $token ?>">
                                <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                <button class="rounded border border-red-200 px-3 py-2 text-red-700" type="submit">Xoá</button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="mt-4 flex flex-wrap items-center justify-between gap-4">
                <p class="font-bold">Tạm tính: <?= $money($cart['subtotal']) ?></p>
                <?php if ($cart['ready']): ?>
                    <a class="rounded-lg bg-vfNavy px-4 py-2 text-white" href="<?= BASE_URL ?>order/cartcheckout">Tiến hành đặt hàng</a>
                <?php else: ?>
                    <p class="text-sm text-red-700">Xoá các phiên bản không còn bán để tiếp tục.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
