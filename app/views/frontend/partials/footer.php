<?php

/**
 * app/views/frontend/partials/footer.php — Customer Footer
 * Owner: Tang Vu (Member 1) — initial build, others can extend
 *
 * Contact info is loaded from SiteSetting when available.
 */
?>
<?php
// Footer dùng SiteSetting để admin chỉnh nhanh.
$settings = SiteSetting::all();
$logoUrl = SiteSetting::imageUrl($settings['logo'] ?? '', 'public/images/logo/logo_footer.png');
$address = (string)($settings['address'] ?? '');
$phone = (string)($settings['phone'] ?? '');
$email = (string)($settings['email'] ?? '');
$facebook = (string)($settings['facebook_url'] ?? '');
?>

<!-- =========================================================
     FOOTER (Tailwind)
     - Contact info đọc từ site_settings
========================================================== -->
<footer class="border-t border-slate-200 bg-slate-950 text-slate-200">
  <div class="mx-auto max-w-6xl px-4 py-10">
    <div class="grid gap-8 md:grid-cols-12">
      <!-- ===== Brand ===== -->
      <div class="md:col-span-5">
        <img src="<?= htmlspecialchars($logoUrl) ?>" alt="VinFast" class="h-10 w-auto">
        <p class="mt-3 text-sm text-slate-400">
          Vietnam's leading electric vehicle manufacturer.
        </p>
      </div>

      <!-- ===== Quick links ===== -->
      <div class="md:col-span-3">
        <h3 class="text-sm font-semibold tracking-wide text-white">Quick links</h3>
        <ul class="mt-4 space-y-2 text-sm">
          <li><a href="<?= BASE_URL ?>products" class="text-slate-400 hover:text-white">Vehicles</a></li>
          <li><a href="<?= BASE_URL ?>news" class="text-slate-400 hover:text-white">News</a></li>
          <li><a href="<?= BASE_URL ?>about" class="text-slate-400 hover:text-white">About</a></li>
          <li><a href="<?= BASE_URL ?>faq" class="text-slate-400 hover:text-white">FAQ</a></li>
          <li><a href="<?= BASE_URL ?>contact" class="text-slate-400 hover:text-white">Contact</a></li>
        </ul>
      </div>

      <!-- ===== Contact ===== -->
      <div class="md:col-span-4">
        <h3 class="text-sm font-semibold tracking-wide text-white">Contact</h3>
        <div class="mt-4 space-y-2 text-sm text-slate-400">
          <p class="flex gap-2">
            <i class="fa fa-location-dot mt-0.5 text-slate-500"></i>
            <span><?= htmlspecialchars($address ?: 'Chưa cập nhật địa chỉ') ?></span>
          </p>
          <p class="flex gap-2">
            <i class="fa fa-phone mt-0.5 text-slate-500"></i>
            <span><?= htmlspecialchars($phone ?: 'Chưa cập nhật SĐT') ?></span>
          </p>
          <p class="flex gap-2">
            <i class="fa fa-envelope mt-0.5 text-slate-500"></i>
            <span><?= htmlspecialchars($email ?: 'Chưa cập nhật email') ?></span>
          </p>
          <?php if ($facebook !== ''): ?>
            <p class="flex gap-2">
              <i class="fa-brands fa-facebook mt-0.5 text-slate-500"></i>
              <a class="hover:text-white" href="<?= htmlspecialchars($facebook) ?>" target="_blank" rel="noreferrer">Facebook</a>
            </p>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="mt-10 border-t border-white/10 pt-6 text-center text-xs text-slate-500">
      &copy; <?= date('Y') ?> VinFast. All rights reserved.
    </div>
  </div>
</footer>