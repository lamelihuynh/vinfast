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

$userData = null;
if (Auth::check()) {
  $userData = (new User())->findById((int)Auth::id());
}
?>

<section class="min-h-screen bg-[#F5F6F8] py-6">
  <div class="mx-auto w-full max-w-6xl px-4 lg:px-6">
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#1a2240] via-[#233060] to-[#1a2240] px-6 py-10 text-white shadow-lg lg:px-10">
      <div class="absolute -right-16 -top-16 h-56 w-56 rounded-full bg-[#c8a22e]/10"></div>
      <div class="absolute -bottom-12 left-10 h-40 w-40 rounded-full bg-white/5"></div>
      <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
        <div class="max-w-2xl">
          <p class="mb-2 text-xs font-semibold uppercase tracking-[0.28em] text-white/55">Tài khoản</p>
          <h1 class="mb-3 text-3xl font-extrabold tracking-[-0.03em] lg:text-4xl">Đơn hàng của tôi</h1>
          <p class="mb-0 max-w-xl text-sm leading-6 text-white/70">Theo dõi lịch sử đặt cọc và trạng thái xử lý trong cùng một không gian quản lý với hồ sơ cá nhân.</p>
        </div>

        <div class="flex items-center gap-4 rounded-2xl border border-white/10 bg-white/10 px-4 py-3 backdrop-blur">
          <?php $avatarFallback = 'data:image/svg+xml;utf8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 96 96"><rect width="96" height="96" rx="24" fill="#1a2240"/><circle cx="48" cy="38" r="18" fill="#e2e8f0"/><path d="M20 80c5-14 16-22 28-22s23 8 28 22" fill="#e2e8f0"/></svg>'); ?>
          <img src="<?= htmlspecialchars((string)($userData['avatar'] ?? $avatarFallback)) ?>" alt="User Avatar" class="h-16 w-16 rounded-2xl object-cover ring-2 ring-white/25">
          <div>
            <div class="text-lg font-bold"><?= htmlspecialchars((string)($userData['name'] ?? 'Người dùng')) ?></div>
            <div class="text-sm text-white/65"><?= htmlspecialchars((string)($userData['email'] ?? '')) ?></div>
            <div class="mt-1 text-xs uppercase tracking-[0.18em] text-white/45">Quản lý đơn đặt cọc</div>
          </div>
        </div>
      </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-[320px_1fr]">
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
              <p class="mt-1 text-sm text-slate-500">Trạng thái đơn hàng sẽ được cập nhật theo xử lý phía quản trị.</p>
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