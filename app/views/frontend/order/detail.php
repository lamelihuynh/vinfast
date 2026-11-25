<?php

/**
 * app/views/frontend/order/detail.php
 * Title  : Order detail
 *
 * Variables (set by OrderController::detail):
 *   $order    (array)  order row from Order::getByIdForUser
 *   $items    (array)  snapshot rows of the order
 *   $products (array)  productID => product from Mongo; may lack products that were deleted
 */
$order = is_array($order ?? null) ? $order : [];
$items = is_array($items ?? null) ? $items : [];
$products = is_array($products ?? null) ? $products : [];

$fmtVnd = static function (float $value): string {
    return number_format($value, 0, ',', '.') . ' VNĐ';
};

$id = (int)($order['id'] ?? 0);
$orderCode = 'VF-' . strtoupper(dechex($id)) . '-' . strtoupper(substr(md5((string)$id), 0, 4));
$createdAt = !empty($order['created_at']) ? date('d/m/Y H:i', strtotime((string)$order['created_at'])) : '—';
$isPurchase = ($order['type'] ?? 'deposit') === 'purchase';

$statusMap = [
    'pending' => ['label' => 'Chờ xử lý', 'class' => 'bg-yellow-100 text-yellow-800'],
    'confirmed' => ['label' => 'Đã xác nhận', 'class' => 'bg-blue-100 text-blue-800'],
    'done' => ['label' => 'Hoàn tất', 'class' => 'bg-green-100 text-green-800'],
    'cancelled' => ['label' => 'Đã hủy', 'class' => 'bg-red-100 text-red-800'],
];
$paymentMap = [
    'unpaid' => ['label' => 'Chưa thanh toán', 'class' => 'bg-yellow-100 text-yellow-800'],
    'pending_verify' => ['label' => 'Chờ xác nhận', 'class' => 'bg-blue-100 text-blue-800'],
    'paid' => ['label' => $isPurchase ? 'Đã thanh toán' : 'Đã nhận cọc', 'class' => 'bg-green-100 text-green-800'],
    'failed' => ['label' => 'Thanh toán thất bại', 'class' => 'bg-red-100 text-red-800'],
    'refunded' => ['label' => 'Đã hoàn tiền', 'class' => 'bg-purple-100 text-purple-800'],
];
$statusUi = $statusMap[(string)($order['status'] ?? '')] ?? ['label' => (string)($order['status'] ?? ''), 'class' => 'bg-slate-100 text-slate-700'];
$paymentUi = $paymentMap[(string)($order['payment_status'] ?? '')] ?? $paymentMap['pending_verify'];

$subtotal = (float)($order['subtotal_amount'] ?? 0);
$discount = (float)($order['discount_amount'] ?? 0);
$total = (float)($order['total_amount'] ?? 0);
?>

<section class="min-h-screen bg-slate-50 py-6">
    <div class="mx-auto max-w-4xl px-4">
        <div class="mb-4">
            <a href="<?= BASE_URL ?>order" class="text-sm text-[#1a6fe0]">&larr; Đơn hàng của tôi</a>
        </div>

        <div class="mb-4 rounded-lg border border-slate-100 bg-white px-4 py-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <div class="text-sm text-slate-500">Mã đơn: <span class="font-mono text-slate-700"><?= htmlspecialchars($orderCode) ?></span></div>
                    <h1 class="text-xl font-bold text-slate-900"><?= $isPurchase ? 'Đơn mua hàng' : 'Đơn đặt cọc' ?></h1>
                    <div class="mt-1 text-xs text-slate-500">Ngày đặt: <?= htmlspecialchars($createdAt) ?></div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold <?= htmlspecialchars($statusUi['class']) ?>"><?= htmlspecialchars($statusUi['label']) ?></span>
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold <?= htmlspecialchars($paymentUi['class']) ?>"><?= htmlspecialchars($paymentUi['label']) ?></span>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto rounded-lg border border-slate-100 bg-white">
            <table class="w-full min-w-[560px] text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Sản phẩm</th>
                        <th class="px-4 py-3 text-right">Đơn giá</th>
                        <th class="px-4 py-3 text-right">SL</th>
                        <th class="px-4 py-3 text-right">Thành tiền</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($items as $item): ?>
                        <?php
                        $product = $products[(string)($item['product_id'] ?? '')] ?? null;
                        $linkable = $product !== null && !empty($product['is_active']);
                        $thumb = $product !== null ? ProductViewHelper::thumbUrl($product) : BASE_URL . 'public/images/products/default.png';
                        $variant = trim((string)($item['variant_name'] ?? ''));
                        $color = trim((string)($item['color_name'] ?? ''));
                        $meta = implode(' · ', array_filter([$variant, $color], static fn(string $v): bool => $v !== ''));
                        ?>
                        <tr>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <img src="<?= htmlspecialchars($thumb) ?>" alt="" class="h-12 w-12 flex-none rounded object-cover bg-slate-100" loading="lazy">
                                    <div class="min-w-0">
                                        <?php if ($linkable): ?>
                                            <a href="<?= BASE_URL ?>products/detail/<?= (int)$product['id'] ?>" class="font-semibold text-slate-900 hover:text-[#1a6fe0]"><?= htmlspecialchars((string)($item['product_name'] ?? '')) ?></a>
                                        <?php else: ?>
                                            <span class="font-semibold text-slate-900"><?= htmlspecialchars((string)($item['product_name'] ?? '')) ?></span>
                                            <span class="ml-1 text-xs text-slate-400">(ngừng kinh doanh)</span>
                                        <?php endif; ?>
                                        <?php if ($meta !== ''): ?>
                                            <div class="text-xs text-slate-500"><?= htmlspecialchars($meta) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap"><?= $fmtVnd((float)($item['unit_price'] ?? 0)) ?></td>
                            <td class="px-4 py-3 text-right"><?= (int)($item['quantity'] ?? 0) ?></td>
                            <td class="px-4 py-3 text-right whitespace-nowrap font-semibold"><?= $fmtVnd((float)($item['subtotal'] ?? 0)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="text-sm">
                    <tr>
                        <td colspan="3" class="px-4 pt-3 text-right text-slate-500">Tạm tính</td>
                        <td class="px-4 pt-3 text-right whitespace-nowrap"><?= $fmtVnd($subtotal) ?></td>
                    </tr>
                    <?php if ($discount > 0): ?>
                        <tr>
                            <td colspan="3" class="px-4 pt-1 text-right text-slate-500">Giảm giá</td>
                            <td class="px-4 pt-1 text-right whitespace-nowrap text-green-700">−<?= $fmtVnd($discount) ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td colspan="3" class="px-4 py-3 text-right font-semibold text-slate-900"><?= $isPurchase ? 'Tổng cộng' : 'Tiền cọc' ?></td>
                        <td class="px-4 py-3 text-right whitespace-nowrap text-base font-bold text-slate-900"><?= $fmtVnd($total) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</section>
