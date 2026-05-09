<?php

/**
 * app/views/frontend/user/orders.php
 * Owner  : All members (common)
 * Title  : My Orders
 *
 * Purpose: Orders page in the shared profile shell.
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
?>

<section class="min-h-screen bg-[#F5F6F8] pt-10 pb-6">
  <div class="mx-auto w-full max-w-6xl px-4 lg:px-6">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[320px_1fr]">
      <aside class="space-y-4">
        <?php include ROOT . '/app/views/frontend/user/partials/profile-card.php'; ?>
        <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
          <?php $active = 'orders';
          include ROOT . '/app/views/frontend/user/partials/sidebar.php'; ?>
        </div>
      </aside>

      <main class="space-y-4">
        <div class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
          <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between gap-3">
            <div>
              <h2 class="text-lg font-bold text-slate-900">Lịch sử đơn hàng</h2>
            </div>
            <a href="<?= BASE_URL ?>products" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">Tiếp tục đặt xe</a>
          </div>
          <div class="px-6 py-6">
            <?php include ROOT . '/app/views/frontend/user/partials/orders-list.php'; ?>
          </div>
        </div>

        <?php include ROOT . '/app/views/frontend/partials/pagination.php'; ?>
      </main>
    </div>
  </div>
</section>