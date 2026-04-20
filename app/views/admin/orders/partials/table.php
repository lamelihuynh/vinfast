<?php

/**
 * app/views/admin/orders/partials/table.php
 * Expects: $orders, $typeMap, $labelByStatus, $badgeByStatus, $extractPhone, $extractOrderDetail
 */
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
                                            <div class="fw-semibold"><?= htmlspecialchars((string)($order['user_name'] ?? '')) ?></div>
                                            <div class="small text-muted"><?= htmlspecialchars((string)($order['email'] ?? '')) ?></div>
                                            <?php if ($phone !== ''): ?>
                                                <div class="small text-muted"><?= htmlspecialchars($phone) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-semibold"><?= htmlspecialchars((string)($order['product_name'] ?? '')) ?></div>
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
        </div>
    </div>
</div>