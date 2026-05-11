<?php

/**
 * app/views/admin/orders/partials/detail-modal.php
 */
?>
<div class="modal fade" id="orderDetailModal" tabindex="-1" aria-labelledby="orderDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderDetailModalLabel">Chi tiết đơn hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="small text-muted">Mã đơn</div>
                        <div class="fw-semibold" data-detail="orderCode">--</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Ngày tạo</div>
                        <div data-detail="createdAt">--</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Khách hàng</div>
                        <div class="fw-semibold" data-detail="customerName">--</div>
                        <div class="small text-muted" data-detail="email">--</div>
                        <div class="small text-muted" data-detail="phone">--</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Sản phẩm</div>
                        <div class="fw-semibold" data-detail="productName">--</div>
                        <div class="small text-muted" data-detail="price">--</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Loại đơn</div>
                        <div data-detail="type">--</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Trạng thái</div>
                        <span class="badge bg-secondary text-white" data-detail="statusBadge">--</span>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Tỉnh/Thành</div>
                        <div data-detail="province">--</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Showroom</div>
                        <div data-detail="showroom">--</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Thanh toán</div>
                        <div data-detail="payMethod">--</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Trạng thái thanh toán</div>
                        <span class="badge bg-secondary text-white" data-detail="paymentStatusBadge">--</span>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Tiền cọc</div>
                        <div class="fw-semibold" data-detail="depositAmount">--</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <form id="orderDetailStatusForm" method="post" action="<?= ADMIN_URL ?>orders/setstatus/0" class="d-flex gap-2 w-100 mb-2">
                    <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                    <input type="hidden" name="redirect" value="index">
                    <select name="status" class="form-select" id="orderDetailStatusSelect">
                        <option value="pending">Chờ xử lý</option>
                        <option value="confirmed">Đã xác nhận</option>
                        <option value="done">Hoàn tất</option>
                        <option value="cancelled">Đã hủy</option>
                    </select>
                    <button type="submit" class="btn btn-primary">Lưu trạng thái</button>
                </form>
                <form id="orderDetailPaymentForm" method="post" action="<?= ADMIN_URL ?>orders/setpayment/0" class="d-flex gap-2 w-100">
                    <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                    <input type="hidden" name="redirect" value="index">
                    <select name="payment_status" class="form-select" id="orderDetailPaymentSelect">
                        <option value="unpaid">Chưa thanh toán</option>
                        <option value="pending_verify">Chờ xác nhận thanh toán</option>
                        <option value="paid">Đã nhận cọc</option>
                        <option value="failed">Thanh toán thất bại</option>
                        <option value="refunded">Đã hoàn tiền</option>
                    </select>
                    <button type="submit" class="btn btn-success">Lưu thanh toán</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        var modal = document.getElementById('orderDetailModal');
        if (!modal) {
            return;
        }

        var statusClassMap = {
            pending: 'bg-warning text-dark',
            confirmed: 'bg-info text-dark',
            done: 'bg-success text-white',
            cancelled: 'bg-danger text-white'
        };

        var paymentClassMap = {
            unpaid: 'bg-secondary text-white',
            pending_verify: 'bg-primary text-white',
            paid: 'bg-success text-white',
            failed: 'bg-danger text-white',
            refunded: 'bg-info text-dark'
        };

        var emptyValue = '--';

        modal.addEventListener('show.bs.modal', function(event) {
            var trigger = event.relatedTarget;
            if (!trigger) {
                return;
            }

            var raw = trigger.getAttribute('data-order') || '{}';
            var order = {};
            try {
                order = JSON.parse(raw);
            } catch (e) {
                order = {};
            }

            var setText = function(key, value) {
                var el = modal.querySelector('[data-detail="' + key + '"]');
                if (!el) {
                    return;
                }
                var text = value && String(value).trim() !== '' ? String(value) : emptyValue;
                el.textContent = text;
                try {
                    el.setAttribute('title', text);
                } catch (e) {}
            };

            setText('orderCode', order.orderCode || '');
            setText('createdAt', order.createdAt || '');
            setText('customerName', order.customerName || order.userName || '');
            setText('email', order.email || '');
            setText('phone', order.phone || '');
            setText('productName', order.productName || '');
            setText('price', order.price || '');
            setText('type', order.type || '');
            setText('ownerType', order.ownerType || '');
            setText('province', order.province || '');
            setText('showroom', order.showroom || '');
            setText('payMethod', order.payMethod || '');
            setText('depositAmount', order.depositAmount || '');

            var badgeEl = modal.querySelector('[data-detail="statusBadge"]');
            if (badgeEl) {
                badgeEl.className = 'badge ' + (statusClassMap[order.statusRaw] || 'bg-secondary text-white');
                badgeEl.textContent = order.status || emptyValue;
            }

            var paymentBadgeEl = modal.querySelector('[data-detail="paymentStatusBadge"]');
            if (paymentBadgeEl) {
                paymentBadgeEl.className = 'badge ' + (paymentClassMap[order.paymentStatusRaw] || 'bg-secondary text-white');
                paymentBadgeEl.textContent = order.paymentStatus || emptyValue;
            }

            var statusSelect = document.getElementById('orderDetailStatusSelect');
            if (statusSelect) {
                statusSelect.value = order.statusRaw || 'pending';
            }

            var paymentSelect = document.getElementById('orderDetailPaymentSelect');
            if (paymentSelect) {
                paymentSelect.value = order.paymentStatusRaw || 'pending_verify';
            }

            var form = document.getElementById('orderDetailStatusForm');
            if (form) {
                var id = Number(order.id || 0);
                if (id > 0) {
                    form.setAttribute('action', '<?= ADMIN_URL ?>orders/setstatus/' + id);
                }
            }

            var paymentForm = document.getElementById('orderDetailPaymentForm');
            if (paymentForm) {
                var paymentId = Number(order.id || 0);
                if (paymentId > 0) {
                    paymentForm.setAttribute('action', '<?= ADMIN_URL ?>orders/setpayment/' + paymentId);
                }
            }
        });
    })();
</script>