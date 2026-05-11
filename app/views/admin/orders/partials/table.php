<?php

/**
 * app/views/admin/orders/partials/table.php
 * Expects: $orders, $typeMap, $labelByStatus, $badgeByStatus, $extractPhone, $extractOrderDetail
 */
$orders = $orders ?? [];
$typeMap = $typeMap ?? [];
$labelByStatus = $labelByStatus ?? function ($v) {
    return $v;
};
$badgeByStatus = $badgeByStatus ?? function ($v) {
    return $v;
};
$extractPhone = $extractPhone ?? function ($v) {
    return '';
};
$extractOrderDetail = $extractOrderDetail ?? function ($v) {
    return [];
};
?>
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Mã đơn</th>
                                <th>Khách hàng</th>
                                <th>Sản phẩm</th>
                                <th>Loại</th>
                                <th>Thanh toán</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Chi tiết</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">Không có đơn hàng phù hợp.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($orders as $order): ?>
                                    <?php
                                    $id = (int)($order['id'] ?? 0);
                                    $orderCode = 'VF-ORD-' . str_pad((string)$id, 6, '0', STR_PAD_LEFT);
                                    $orderStatus = trim((string)($order['status'] ?? 'pending'));
                                    $phone = $extractPhone($order['note'] ?? '');
                                    $createdAt = trim((string)($order['created_at'] ?? ''));
                                    $detailPayload = $extractOrderDetail($order);
                                    $paymentStatusRaw = (string)($detailPayload['paymentStatusRaw'] ?? 'pending_verify');
                                    $paymentStatusLabel = (string)($detailPayload['paymentStatus'] ?? 'Chờ xác nhận thanh toán');
                                    $paymentStatusBadge = (string)($detailPayload['paymentStatusBadge'] ?? 'badge-primary');
                                    $detailJson = json_encode($detailPayload, JSON_UNESCAPED_UNICODE);
                                    if ($detailJson === false) {
                                        $detailJson = '{}';
                                    }
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><?= htmlspecialchars($orderCode) ?></div>
                                            <div class="small text-muted">#<?= $id ?></div>
                                        </td>
                                        <td>
                                            <?php
                                            $custName = (string)($detailPayload['customerName'] ?? $order['user_name'] ?? '');
                                            $custEmail = (string)($detailPayload['email'] ?? '');
                                            $custPhone = (string)($detailPayload['phone'] ?? '');
                                            if (function_exists('mb_strlen')) {
                                                $displayCustName = mb_strlen($custName, 'UTF-8') > 28 ? mb_substr($custName, 0, 28, 'UTF-8') . '...' : $custName;
                                            } else {
                                                $displayCustName = strlen($custName) > 28 ? substr($custName, 0, 28) . '...' : $custName;
                                            }
                                            ?>
                                            <div class="fw-semibold" title="<?= htmlspecialchars($custName) ?>" style="max-width:220px;display:inline-block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($displayCustName) ?></div>
                                            <div class="small text-muted" title="<?= htmlspecialchars($custEmail) ?>" style="max-width:220px;display:inline-block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($custEmail) ?></div>
                                            <?php if ($custPhone !== ''): ?>
                                                <div class="small text-muted"><?= htmlspecialchars($custPhone) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $prodName = (string)($order['product_name'] ?? '');
                                            if (function_exists('mb_strlen')) {
                                                $displayProdName = mb_strlen($prodName, 'UTF-8') > 32 ? mb_substr($prodName, 0, 32, 'UTF-8') . '...' : $prodName;
                                            } else {
                                                $displayProdName = strlen($prodName) > 32 ? substr($prodName, 0, 32) . '...' : $prodName;
                                            }
                                            ?>
                                            <div class="fw-semibold" title="<?= htmlspecialchars($prodName) ?>" style="max-width:260px;display:inline-block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($displayProdName) ?></div>
                                            <div class="small text-muted"><?= number_format((float)($order['price'] ?? 0), 0, ',', '.') ?> VND</div>
                                        </td>
                                        <td>
                                            <span class="badge badge-light text-black border">
                                                <?= htmlspecialchars($typeMap[(string)($order['type'] ?? '')] ?? (string)($order['type'] ?? '')) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge text-black <?= htmlspecialchars($paymentStatusBadge) ?>">
                                                <?= htmlspecialchars($paymentStatusLabel) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge text-black <?= htmlspecialchars($badgeByStatus($orderStatus)) ?>">
                                                <?= htmlspecialchars($labelByStatus($orderStatus)) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div><?= htmlspecialchars($createdAt !== '' ? date('d/m/Y H:i', strtotime($createdAt)) : '--') ?></div>
                                        </td>
                                        <td>
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-secondary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#orderDetailModal"
                                                data-order='<?= htmlspecialchars($detailJson, ENT_QUOTES, 'UTF-8') ?>'>
                                                Xem / Cập nhật
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php
            $itemName = 'đơn hàng';
            include ROOT . '/app/views/admin/partials/pagination.php';
            ?>
        </div>
    </div>
</div>