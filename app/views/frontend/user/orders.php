<?php

/**
 * app/views/frontend/user/orders.php
 * Owner  : All members (common)
 * Title  : My Orders
 *
 * Purpose: Table of the member's deposit/test-drive orders: product name, order type, status badge, date.
 *
 * Variables available (set by controller via View::render):
 *   $orders (array)
 *
  Assets    : (none)
 *
 * TODO: Replace the placeholder below with the actual HTML implementation.
 * -----------------------------------------------------------------------
 * Rules:
 *  - Always escape output: <?= htmlspecialchars($var) ?>
 *  - Include CSRF in every form: <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
 *  - Include pagination partial where needed: include ROOT."/app/views/frontend/partials/pagination.php"
 */
?>
<?php
$ordersRaw = $orders ?? [];
$orders = is_iterable($ordersRaw) ? $ordersRaw : [];

$statusMap = [
  'pending' => ['label' => 'Cho xu ly', 'class' => 'bg-yellow-100 text-yellow-800'],
  'confirmed' => ['label' => 'Da xac nhan', 'class' => 'bg-blue-100 text-blue-800'],
  'done' => ['label' => 'Hoan tat', 'class' => 'bg-green-100 text-green-800'],
  'cancelled' => ['label' => 'Da huy', 'class' => 'bg-red-100 text-red-800'],
];

$typeMap = [
  'deposit' => 'Dat coc',
  'test_drive' => 'Lai thu',
];

$paymentStatusMap = [
  'unpaid' => ['label' => 'Chua thanh toan', 'class' => 'bg-slate-100 text-slate-700'],
  'pending_verify' => ['label' => 'Cho xac nhan TT', 'class' => 'bg-blue-100 text-blue-800'],
  'paid' => ['label' => 'Da nhan coc', 'class' => 'bg-green-100 text-green-800'],
  'failed' => ['label' => 'TT that bai', 'class' => 'bg-red-100 text-red-800'],
  'refunded' => ['label' => 'Da hoan tien', 'class' => 'bg-purple-100 text-purple-800'],
];

$extractDepositAmount = static function ($note): float {
  $raw = trim((string)$note);
  if ($raw === '') {
    return 0;
  }

  $decoded = json_decode($raw, true);
  if (!is_array($decoded)) {
    return 0;
  }

  return (float)($decoded['deposit_amount'] ?? 0);
};

$extractPaymentStatus = static function ($note): string {
  $raw = trim((string)$note);
  if ($raw === '') {
    return 'pending_verify';
  }

  $decoded = json_decode($raw, true);
  if (!is_array($decoded)) {
    return 'pending_verify';
  }

  $status = trim((string)($decoded['payment_status'] ?? 'pending_verify'));
  return $status !== '' ? $status : 'pending_verify';
};
?>

<section class="py-6 bg-slate-50 min-h-[60vh]">
  <div class="container">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
      <div>
        <h1 class="text-2xl font-semibold text-slate-900 mb-1">Lich su don hang</h1>
        <p class="text-sm text-slate-500 mb-0">Trang thai don duoc cap nhat theo xu ly tai trang admin.</p>
      </div>
      <a href="<?= BASE_URL ?>products" class="inline-flex items-center rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-700 hover:bg-slate-100">Tiep tuc dat xe</a>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
          <thead class="bg-slate-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Ma don</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">San pham</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Loai</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Tien coc</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Thanh toan</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Trang thai</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Ngay tao</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php if (empty($orders)): ?>
              <tr>
                <td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500">Ban chua co don hang nao.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($orders as $order): ?>
                <?php
                $id = (int)($order['id'] ?? 0);
                $orderCode = 'VF-ORD-' . str_pad((string)$id, 6, '0', STR_PAD_LEFT);
                $status = trim((string)($order['status'] ?? 'pending'));
                $statusItem = $statusMap[$status] ?? ['label' => $status, 'class' => 'bg-slate-100 text-slate-700'];
                $depositAmount = $extractDepositAmount($order['note'] ?? null);
                $paymentStatus = $extractPaymentStatus($order['note'] ?? null);
                $paymentItem = $paymentStatusMap[$paymentStatus] ?? ['label' => $paymentStatus, 'class' => 'bg-slate-100 text-slate-700'];
                ?>
                <tr>
                  <td class="px-4 py-3 text-sm font-medium text-slate-900"><?= htmlspecialchars($orderCode) ?></td>
                  <td class="px-4 py-3 text-sm text-slate-700"><?= htmlspecialchars((string)($order['product_name'] ?? '')) ?></td>
                  <td class="px-4 py-3 text-sm text-slate-700"><?= htmlspecialchars($typeMap[(string)($order['type'] ?? '')] ?? (string)($order['type'] ?? '')) ?></td>
                  <td class="px-4 py-3 text-sm text-slate-700">
                    <?= $depositAmount > 0 ? htmlspecialchars(number_format($depositAmount, 0, ',', '.') . ' VND') : '--' ?>
                  </td>
                  <td class="px-4 py-3 text-sm">
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold <?= htmlspecialchars($paymentItem['class']) ?>">
                      <?= htmlspecialchars((string)$paymentItem['label']) ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-sm">
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold <?= htmlspecialchars($statusItem['class']) ?>">
                      <?= htmlspecialchars((string)$statusItem['label']) ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-sm text-slate-500">
                    <?= htmlspecialchars(!empty($order['created_at']) ? date('d/m/Y H:i', strtotime((string)$order['created_at'])) : '--') ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <?php include ROOT . '/app/views/frontend/partials/pagination.php'; ?>
  </div>
</section>