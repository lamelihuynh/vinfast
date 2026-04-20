<?php
/**
 * app/views/frontend/contact/index.php
 * Owner  : Tang Vu 
 * Title  : Contact
 *
 * Purpose: Contact form: name, email, phone, message. POSTs to /contact/send. Company info (address, phone, email) from $settings.
 *
 * Variables available (set by controller via View::render):
 *   $settings (array)
 *
 *
 * -----------------------------------------------------------------------
 * Rules:
 *  - Always escape output: <?= htmlspecialchars($var) ?>
 *  - Include CSRF in every form: <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
 *  - Include pagination partial where needed: include ROOT."/app/views/frontend/partials/pagination.php"
 */
?>
<?php
// -----------------------------
// Data chuẩn hoá (để dễ chỉnh sửa sau này trong admin)
// -----------------------------
$address = (string)($settings['address'] ?? '');
$phone   = (string)($settings['phone'] ?? '');
$email   = (string)($settings['email'] ?? '');

// CTA links (có thể đưa vào SiteSetting sau)
$mapUrl = 'https://maps.google.com/?q=' . urlencode($address ?: 'VinFast');
?>

<!-- =========================================================
     CONTACT PAGE (Tailwind)
     - Form POST /contact/send (CSRF)
     - Block contact info lấy từ SiteSetting
========================================================== -->
<section class="bg-white">
  <div class="mx-auto max-w-6xl px-4 py-10 sm:py-14">
    <!-- ===== Heading ===== -->
    <div class="mb-10">
      <p class="text-sm font-semibold tracking-wide text-vfGold">LIÊN HỆ</p>
      <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">
        Gửi yêu cầu hỗ trợ
      </h1>
      <p class="mt-3 max-w-2xl text-slate-600">
        Bạn có câu hỏi về sản phẩm, chính sách hoặc dịch vụ? Hãy để lại thông tin, VinFast sẽ liên hệ sớm.
      </p>
    </div>

    <div class="grid gap-8 lg:grid-cols-12">
      <!-- ===== Form ===== -->
      <div class="lg:col-span-7">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
          <form action="<?= BASE_URL ?>contact/send" method="post" class="space-y-5">
            <!-- CSRF -->
            <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">

            <!-- Name + Email -->
            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <label class="mb-2 block text-sm font-medium text-slate-700" for="name">Họ và tên</label>
                <input id="name" name="name" required
                  class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 outline-none ring-vfGold/30 focus:ring-4"
                  placeholder="VD: Tang Vu" />
              </div>
              <div>
                <label class="mb-2 block text-sm font-medium text-slate-700" for="email">Email</label>
                <input id="email" name="email" type="email" required
                  class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 outline-none ring-vfGold/30 focus:ring-4"
                  placeholder="vd: tangvu@gmail.com" />
              </div>
            </div>

            <!-- Phone -->
            <div>
              <label class="mb-2 block text-sm font-medium text-slate-700" for="phone">Số điện thoại (tuỳ chọn)</label>
              <input id="phone" name="phone"
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 outline-none ring-vfGold/30 focus:ring-4"
                placeholder="VD: 09xxxxxxxx" />
            </div>

            <!-- Message -->
            <div>
              <label class="mb-2 block text-sm font-medium text-slate-700" for="message">Nội dung</label>
              <textarea id="message" name="message" rows="5" required
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 outline-none ring-vfGold/30 focus:ring-4"
                placeholder="Bạn cần hỗ trợ vấn đề gì?"></textarea>
            </div>

            <!-- Submit -->
            <div class="flex flex-wrap items-center gap-3">
              <button type="submit"
                class="inline-flex items-center justify-center rounded-xl bg-vfNavy px-5 py-3 text-sm font-semibold text-white hover:bg-slate-900">
                Gửi liên hệ
              </button>
              <p class="text-sm text-slate-500">
                Bằng việc gửi, bạn đồng ý để VinFast liên hệ lại qua email/điện thoại.
              </p>
            </div>
          </form>
        </div>
      </div>

      <!-- ===== Info ===== -->
      <aside class="lg:col-span-5">
        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6">
          <h2 class="text-lg font-semibold text-slate-900">Thông tin công ty</h2>
          <p class="mt-2 text-sm text-slate-600">
            Các thông tin bên dưới lấy từ bảng `site_settings` để admin chỉnh sửa nhanh.
          </p>

          <div class="mt-6 space-y-4 text-sm">
            <div class="rounded-xl bg-white p-4">
              <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Địa chỉ</p>
              <p class="mt-1 text-slate-900"><?= htmlspecialchars($address ?: 'Chưa cập nhật') ?></p>
              <a class="mt-2 inline-block text-vfNavy hover:underline" href="<?= htmlspecialchars($mapUrl) ?>" target="_blank" rel="noreferrer">
                Xem trên bản đồ
              </a>
            </div>

            <div class="rounded-xl bg-white p-4">
              <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Điện thoại</p>
              <p class="mt-1 text-slate-900"><?= htmlspecialchars($phone ?: 'Chưa cập nhật') ?></p>
            </div>

            <div class="rounded-xl bg-white p-4">
              <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Email</p>
              <p class="mt-1 text-slate-900"><?= htmlspecialchars($email ?: 'Chưa cập nhật') ?></p>
            </div>
          </div>
        </div>
      </aside>
    </div>
  </div>
</section>