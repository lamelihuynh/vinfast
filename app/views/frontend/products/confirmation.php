<?php
$order = isset($order) && is_array($order) ? $order : [];

$orderId = (string)($order['orderId'] ?? 'VF-0000');
$carName = (string)($order['carName'] ?? 'VinFast');
$variantName = (string)($order['variantName'] ?? '');
$carPrice = (float)($order['carPrice'] ?? 0);
$exteriorColor = (string)($order['exteriorColor'] ?? '');
$colorSurcharge = (float)($order['colorSurcharge'] ?? 0);
$interiorColor = (string)($order['interiorColor'] ?? '');
$customerName = (string)($order['customerName'] ?? '');
$phone = (string)($order['phone'] ?? '');
$email = (string)($order['email'] ?? '');
$cccd = (string)($order['cccd'] ?? '');
$province = (string)($order['province'] ?? '');
$showroom = (string)($order['showroom'] ?? '');
$payMethod = (string)($order['payMethod'] ?? 'transfer');
$depositAmount = (float)($order['depositAmount'] ?? 15000000);
$paymentStatus = (string)($order['paymentStatus'] ?? 'pending_verify');
$orderDate = (string)($order['orderDate'] ?? date('d/m/Y H:i'));

$fmtVnd = static function (float $value): string {
    return number_format($value, 0, ',', '.') . ' VNĐ';
};

$fmtPayMethod = static function (string $method): string {
    if ($method === 'card-intl') {
        return 'Thẻ thanh toán quốc tế';
    }
    if ($method === 'card-domestic') {
        return 'Thẻ ATM / Internet Banking';
    }
    return 'Chuyển khoản ngân hàng';
};

$fmtPaymentStatus = static function (string $status): array {
    $map = [
        'unpaid' => ['label' => 'Chưa thanh toán', 'bg' => '#FEF3C7', 'color' => '#B45309'],
        'pending_verify' => ['label' => 'Chờ xác nhận thanh toán', 'bg' => '#DBEAFE', 'color' => '#1D4ED8'],
        'paid' => ['label' => 'Đã nhận cọc', 'bg' => '#DCFCE7', 'color' => '#16A34A'],
        'failed' => ['label' => 'Thanh toán thất bại', 'bg' => '#FEE2E2', 'color' => '#B91C1C'],
        'refunded' => ['label' => 'Đã hoàn tiền', 'bg' => '#F3E8FF', 'color' => '#7E22CE'],
    ];

    return $map[$status] ?? $map['pending_verify'];
};

$paymentUi = $fmtPaymentStatus($paymentStatus);

$nextSteps = [
    'Email xác nhận đã được gửi đến ' . ($email !== '' ? $email : 'hộp thư của bạn'),
    'Tư vấn viên sẽ liên hệ trong vòng 24h làm việc',
    'Hẹn lịch ký hợp đồng và nhận xe tại showroom',
    'Nhận xe và tận hưởng trải nghiệm VinFast',
];

$scripts = '';
?>

<section class="min-h-screen bg-[#F5F6F8]" style="font-family: Inter, sans-serif;">
    <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1a2240 0%, #233060 50%, #1a2240 100%); padding-top: 56px; padding-bottom: 80px;">
        <div class="absolute rounded-full opacity-10" style="width: 400px; height: 400px; background: #c8a22e; top: -120px; right: -80px;"></div>
        <div class="absolute rounded-full opacity-5" style="width: 250px; height: 250px; background: #1a6fe0; bottom: -60px; left: 10%;"></div>

        <div class="mx-auto max-w-2xl px-6 text-center relative z-10">
            <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-full shadow-lg" style="background: rgba(255,255,255,0.12); border: 2px solid rgba(200,162,46,0.5);">
                <div class="flex h-14 w-14 items-center justify-center rounded-full" style="background: #c8a22e;">
                    <i class="fa-solid fa-check text-[32px] text-white"></i>
                </div>
            </div>

            <h1 class="mb-2 text-white" style="font-size: 26px; font-weight: 800; letter-spacing: -0.3px;">Đã Ghi Nhận Yêu Cầu Đặt Cọc</h1>
            <p class="mb-6 text-white/70" style="font-size: 14px;">Yêu cầu của Quý khách đã được tạo. Chúng tôi sẽ xác nhận thanh toán và cập nhật trạng thái đơn sớm nhất.</p>

            <div class="inline-flex items-center gap-3 rounded-xl px-5 py-3" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15);">
                <span class="text-white/60" style="font-size: 11px; font-weight: 600; letter-spacing: 1px;">MÃ ĐẶT CỌC</span>
                <span class="font-mono text-white" style="font-size: 16px; font-weight: 800;"><?= htmlspecialchars($orderId) ?></span>
                <button type="button" data-copy-order class="ml-1 text-white/80 transition hover:text-white" title="Sao chép mã đặt cọc">
                    <i class="fa-regular fa-copy"></i>
                </button>
            </div>

            <?php if ($payMethod === 'transfer'): ?>
                <div class="mt-4 inline-flex items-center gap-2 rounded-lg px-4 py-2.5" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);">
                    <i class="fa-regular fa-clock text-[13px] text-yellow-400"></i>
                    <span class="text-white/70" style="font-size: 11px;">Vui lòng hoàn thành chuyển khoản trong </span>
                    <span class="font-mono" data-countdown="1800" style="font-size: 12px; font-weight: 700; color: #60a5fa;">30:00</span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="mx-auto -mt-10 max-w-6xl px-4 pb-16 relative z-10 lg:px-6">
        <div class="mb-5 flex flex-wrap items-center gap-4 rounded-xl border border-gray-100 bg-white px-6 py-4 shadow-sm">
            <div class="flex items-center gap-2">
                <span class="grid h-6 w-6 place-items-center rounded-full" style="background: #1a2240; color: white;"><i class="fa-solid fa-check text-[12px]"></i></span>
                <span class="text-[12px] font-semibold text-[#1a2240]">Lựa chọn xe</span>
            </div>
            <i class="fa-solid fa-chevron-right text-[13px] text-gray-300"></i>
            <div class="flex items-center gap-2">
                <span class="grid h-6 w-6 place-items-center rounded-full" style="background: #1a2240; color: white;"><i class="fa-solid fa-check text-[12px]"></i></span>
                <span class="text-[12px] font-semibold text-[#1a2240]">Nhập thông tin</span>
            </div>
            <i class="fa-solid fa-chevron-right text-[13px] text-gray-300"></i>
            <div class="flex items-center gap-2">
                <span class="grid h-6 w-6 place-items-center rounded-full" style="background: #1a2240; color: white;"><i class="fa-solid fa-check text-[12px]"></i></span>
                <span class="text-[12px] font-semibold text-[#1a2240]">Đặt cọc xe</span>
            </div>
            <div class="ml-auto">
                <span class="rounded-full px-3 py-1 text-white" style="background: #22c55e; font-size: 11px; font-weight: 700;">✓ Hoàn tất</span>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div class="space-y-5">
                <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                    <div class="flex items-center gap-2 border-b border-gray-100 px-5 py-3" style="background: #FAFBFC;">
                        <i class="fa-solid fa-car text-[14px] text-[#1a2240]"></i>
                        <h3 class="text-[#1a2240]" style="font-size: 12px; font-weight: 700; letter-spacing: 0.5px;">THÔNG TIN XE ĐẶT CỌC</h3>
                    </div>
                    <div class="space-y-3 px-5 py-4">
                        <?php foreach (
                            [
                                ['label' => 'Dòng xe', 'value' => $carName],
                                ['label' => 'Phiên bản', 'value' => $variantName !== '' ? $variantName : '—'],
                                ['label' => 'Ngoại thất', 'value' => $exteriorColor !== '' ? $exteriorColor : '—'],
                                $colorSurcharge > 0 ? ['label' => 'Phụ thu màu', 'value' => $fmtVnd($colorSurcharge), 'highlight' => true] : null,
                                ['label' => 'Giá xe (kèm pin)', 'value' => $fmtVnd($carPrice), 'highlight' => true],
                            ] as $row
                        ): ?>
                            <?php if ($row === null) {
                                continue;
                            } ?>
                            <div class="flex items-start justify-between gap-3">
                                <span class="text-gray-500" style="font-size: 12px;"><?= htmlspecialchars($row['label']) ?></span>
                                <span style="font-size: 12px; font-weight: <?= !empty($row['highlight']) ? 700 : 500 ?>; color: <?= !empty($row['highlight']) ? '#1a2240' : '#374151' ?>; text-align: right; max-width: 55%;"><?= htmlspecialchars((string)$row['value']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                    <div class="flex items-center gap-2 border-b border-gray-100 px-5 py-3" style="background: #FAFBFC;">
                        <i class="fa-solid fa-credit-card text-[14px] text-[#1a2240]"></i>
                        <h3 class="text-[#1a2240]" style="font-size: 12px; font-weight: 700; letter-spacing: 0.5px;">THÔNG TIN THANH TOÁN</h3>
                    </div>
                    <div class="space-y-3 px-5 py-4">
                        <?php foreach (
                            [
                                ['label' => 'Hình thức', 'value' => $fmtPayMethod($payMethod)],
                                ['label' => 'Số tiền đặt cọc', 'value' => $fmtVnd($depositAmount), 'highlight' => true],
                                ['label' => 'Trạng thái', 'value' => $paymentUi['label'], 'status' => true],
                                ['label' => 'Ngày đặt cọc', 'value' => $orderDate],
                            ] as $row
                        ): ?>
                            <div class="flex items-start justify-between gap-3">
                                <span class="text-gray-500" style="font-size: 12px;"><?= htmlspecialchars($row['label']) ?></span>
                                <span style="font-size: 12px; font-weight: <?= !empty($row['highlight']) ? 700 : 500 ?>; color: <?= !empty($row['highlight']) ? '#1a6fe0' : '#374151' ?>; text-align: right; max-width: 55%;">
                                    <?php if (!empty($row['status'])): ?>
                                        <span class="rounded-full px-2 py-0.5" style="background: <?= htmlspecialchars($paymentUi['bg']) ?>; color: <?= htmlspecialchars($paymentUi['color']) ?>; font-size: 11px; font-weight: 600;"><?= htmlspecialchars((string)$row['value']) ?></span>
                                    <?php else: ?>
                                        <?= htmlspecialchars((string)$row['value']) ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="space-y-5">
                <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                    <div class="flex items-center gap-2 border-b border-gray-100 px-5 py-3" style="background: #FAFBFC;">
                        <i class="fa-solid fa-user text-[14px] text-[#1a2240]"></i>
                        <h3 class="text-[#1a2240]" style="font-size: 12px; font-weight: 700; letter-spacing: 0.5px;">THÔNG TIN CHỦ XE</h3>
                    </div>
                    <div class="space-y-3 px-5 py-4">
                        <?php foreach (
                            [
                                ['icon' => 'fa-user', 'label' => 'Họ và tên', 'value' => $customerName],
                                ['icon' => 'fa-phone', 'label' => 'Điện thoại', 'value' => $phone],
                                ['icon' => 'fa-envelope', 'label' => 'Email', 'value' => $email],
                                ['icon' => 'fa-id-card', 'label' => 'Số CCCD', 'value' => $cccd !== '' ? $cccd : '—'],
                            ] as $row
                        ): ?>
                            <div class="flex items-start gap-2.5">
                                <span class="mt-0.5 flex-shrink-0 text-gray-400"><i class="fa-solid <?= htmlspecialchars($row['icon']) ?> text-[12px]"></i></span>
                                <div class="flex-1 flex justify-between gap-3">
                                    <span class="text-gray-500" style="font-size: 12px;"><?= htmlspecialchars($row['label']) ?></span>
                                    <span class="text-right text-gray-800" style="font-size: 12px; font-weight: 500;"><?= htmlspecialchars((string)$row['value']) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                    <div class="flex items-center gap-2 border-b border-gray-100 px-5 py-3" style="background: #FAFBFC;">
                        <i class="fa-solid fa-map-location-dot text-[14px] text-[#1a2240]"></i>
                        <h3 class="text-[#1a2240]" style="font-size: 12px; font-weight: 700; letter-spacing: 0.5px;">SHOWROOM NHẬN XE</h3>
                    </div>
                    <div class="px-5 py-4">
                        <p class="text-[#1a2240]" style="font-size: 13px; font-weight: 600;"><?= htmlspecialchars($showroom !== '' ? $showroom : '—') ?></p>
                        <p class="mt-1 text-gray-500" style="font-size: 11px;"><?= htmlspecialchars($province !== '' ? $province : '—') ?></p>
                        <a href="<?= BASE_URL ?>contact" class="mt-3 inline-flex items-center gap-1 text-[#1a6fe0] hover:underline" style="font-size: 11px; font-weight: 500;">
                            <i class="fa-solid fa-map-pin text-[11px]"></i> Xem bản đồ
                        </a>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-[#c8a22e]/30" style="background: linear-gradient(135deg, #1a2240 0%, #233060 100%);">
                    <div class="px-5 py-4">
                        <h3 class="mb-3 text-white" style="font-size: 12px; font-weight: 700; letter-spacing: 0.5px;">CÁC BƯỚC TIẾP THEO</h3>
                        <?php foreach ($nextSteps as $stepText): ?>
                            <div class="mb-3 flex items-start gap-3 last:mb-0">
                                <div class="mt-0.5 flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full" style="background: rgba(200,162,46,0.2); color: #c8a22e;">
                                    <i class="fa-solid fa-check text-[10px]"></i>
                                </div>
                                <p class="leading-relaxed text-white/75" style="font-size: 11px;"><?= htmlspecialchars($stepText) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="flex items-center gap-4 rounded-xl border px-5 py-4" style="background: #F0FDF4; border-color: #BBF7D0;">
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full" style="background: #22c55e; color: white;">
                        <i class="fa-solid fa-phone text-[16px]"></i>
                    </div>
                    <div>
                        <p class="text-green-800" style="font-size: 11px; font-weight: 600;">Cần hỗ trợ? Gọi ngay Hotline</p>
                        <p class="text-green-700" style="font-size: 16px; font-weight: 800;">1900 23 23 89</p>
                        <p class="text-green-600" style="font-size: 10px;">Thứ 2 – Chủ nhật · 7:00 – 22:00</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 flex flex-col gap-3 sm:flex-row">
            <button type="button" class="flex-1 rounded-xl border-2 border-[#1a2240] py-3 text-[#1a2240] transition hover:bg-[#1a2240] hover:text-white" style="font-size: 13px; font-weight: 700;" data-print-confirm>
                <i class="fa-solid fa-download mr-2"></i>
                TẢI XÁC NHẬN (PDF)
            </button>
            <button type="button" class="flex-1 rounded-xl border-2 border-gray-300 py-3 text-gray-600 transition hover:border-[#1a6fe0] hover:text-[#1a6fe0]" style="font-size: 13px; font-weight: 700;" data-share-confirm>
                <i class="fa-solid fa-share-nodes mr-2"></i>
                <span data-share-label>CHIA SẺ</span>
            </button>
            <a href="<?= BASE_URL ?>" class="flex-1 rounded-xl bg-[#1a2240] py-3 text-center text-white transition hover:bg-[#233060]" style="font-size: 13px; font-weight: 700;">
                <i class="fa-solid fa-house mr-2"></i>
                VỀ TRANG CHỦ
            </a>
        </div>

        <div class="mt-8 text-center">
            <p class="mb-2 text-gray-500" style="font-size: 12px;">Khám phá thêm các dòng xe VinFast</p>
            <a href="<?= BASE_URL ?>products" class="inline-flex items-center gap-1 text-[#1a6fe0] hover:underline" style="font-size: 13px; font-weight: 600;">
                Xem tất cả sản phẩm <i class="fa-solid fa-chevron-right text-[14px]"></i>
            </a>
        </div>
    </div>
</section>

<script>
    (function() {
        var copyBtn = document.querySelector('[data-copy-order]');
        if (copyBtn) {
            copyBtn.addEventListener('click', function() {
                var text = <?= json_encode($orderId) ?>;
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).catch(function() {});
                }
            });
        }

        var countdown = document.querySelector('[data-countdown]');
        if (countdown) {
            var seconds = Number(countdown.getAttribute('data-countdown') || '0');
            var timer = setInterval(function() {
                if (seconds <= 0) {
                    clearInterval(timer);
                    countdown.textContent = '00:00';
                    return;
                }
                seconds -= 1;
                var m = String(Math.floor(seconds / 60)).padStart(2, '0');
                var s = String(seconds % 60).padStart(2, '0');
                countdown.textContent = m + ':' + s;
            }, 1000);
        }

        var shareBtn = document.querySelector('[data-share-confirm]');
        var shareLabel = document.querySelector('[data-share-label]');
        if (shareBtn && shareLabel) {
            shareBtn.addEventListener('click', function() {
                shareLabel.textContent = 'ĐÃ CHIA SẺ!';
                setTimeout(function() {
                    shareLabel.textContent = 'CHIA SẺ';
                }, 2000);
            });
        }

        var printBtn = document.querySelector('[data-print-confirm]');
        if (printBtn) {
            printBtn.addEventListener('click', function() {
                try {
                    window.print();
                } catch (e) {}
            });
        }
    })();
</script>