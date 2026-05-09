<?php
/**
 * app/views/frontend/contact/index.php
 * Owner  : Tang Vu 
 * Title  : Contact
 *
 * Purpose: Dual-tab contact page: Contact form & Test-drive registration form.
 *          POST contact -> /contact/send | POST test drive -> /contact/testdrive
 *
 * Variables available:
 *   $settings (array), $tab (string), $products (array), $provinces (array), $showrooms (array)
 */
?>
<?php
$address = (string)($settings['address'] ?? '');
$phone   = (string)($settings['phone'] ?? '');
$email   = (string)($settings['email'] ?? '');
$mapUrl  = 'https://maps.google.com/?q=' . urlencode($address ?: 'VinFast');

$isContactTab = $tab === 'contact';
$isTestDriveTab = $tab === 'test-drive';
?>

<style>
  /* ===== Contact Page Tab Styles ===== */
  .vf-contact-tabs {
    display: flex;
    border-bottom: 2px solid #e2e8f0;
    margin-bottom: 1.5rem;
  }
  .vf-contact-tab {
    position: relative;
    padding: 0.875rem 1.5rem;
    font-size: 0.9375rem;
    font-weight: 600;
    color: #64748b;
    cursor: pointer;
    border: none;
    background: transparent;
    transition: color 0.3s ease;
  }
  .vf-contact-tab:hover {
    color: #0b233f;
  }
  .vf-contact-tab.active {
    color: #0b233f;
  }
  .vf-contact-tab::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: -2px;
    width: 100%;
    height: 3px;
    background: #FFB81C;
    border-radius: 3px 3px 0 0;
    transform: scaleX(0);
    transform-origin: center;
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }
  .vf-contact-tab.active::after {
    transform: scaleX(1);
  }

  .vf-tab-panel {
    opacity: 0;
    transform: translateY(12px);
    transition: opacity 0.35s ease, transform 0.35s ease;
    display: none;
  }
  .vf-tab-panel.active {
    display: block;
    opacity: 1;
    transform: translateY(0);
  }

  .vf-contact-header {
    background: linear-gradient(135deg, #0b233f 0%, #1a3a5f 100%);
    color: #fff;
    padding: 1.75rem 1.5rem;
    border-radius: 1rem 1rem 0 0;
    text-align: center;
  }
  .vf-contact-header h2 {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
  }
  .vf-contact-header p {
    font-size: 0.875rem;
    color: rgba(255,255,255,0.75);
    margin: 0;
  }

  .vf-contact-box {
    border-radius: 1rem;
    border: 1px solid #e2e8f0;
    background: #fff;
    box-shadow: 0 4px 24px rgba(0,0,0,0.04);
    overflow: hidden;
  }
  .vf-contact-body {
    padding: 1.5rem;
  }

  .vf-form-label {
    display: block;
    margin-bottom: 0.375rem;
    font-size: 0.8125rem;
    font-weight: 600;
    color: #334155;
  }
  .vf-form-input,
  .vf-form-select,
  .vf-form-textarea {
    width: 100%;
    border-radius: 0.75rem;
    border: 1px solid #e2e8f0;
    background: #fff;
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
    color: #0f172a;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
  }
  .vf-form-input:focus,
  .vf-form-select:focus,
  .vf-form-textarea:focus {
    border-color: #FFB81C;
    box-shadow: 0 0 0 3px rgba(255,184,28,0.18);
  }
  .vf-btn-submit {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    border-radius: 0.75rem;
    background: #0b233f;
    color: #fff;
    padding: 0.875rem 1.75rem;
    font-size: 0.875rem;
    font-weight: 700;
    border: none;
    cursor: pointer;
    transition: background 0.2s, transform 0.15s;
  }
  .vf-btn-submit:hover {
    background: #06182d;
  }
  .vf-btn-submit:active {
    transform: scale(0.98);
  }
  .vf-btn-submit--gold {
    background: linear-gradient(135deg, #FFB81C 0%, #f59e0b 100%);
    color: #0b233f;
    box-shadow: 0 4px 15px rgba(255, 184, 28, 0.4);
  }
  .vf-btn-submit--gold:hover {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    box-shadow: 0 6px 20px rgba(255, 184, 28, 0.6);
    transform: translateY(-2px);
  }
</style>

<section class="bg-white">
  <div class="mx-auto max-w-6xl px-4 py-10 sm:py-14">
    <!-- ===== Heading ===== -->
    <div class="mb-12 text-center">
      <div class="inline-flex items-center justify-center gap-2 mb-3">
        <span class="h-1 w-8 bg-[#FFB81C] rounded-full"></span>
        <p class="text-sm font-bold tracking-widest text-[#FFB81C] uppercase">LIÊN HỆ VỚI CHÚNG TÔI</p>
        <span class="h-1 w-8 bg-[#FFB81C] rounded-full"></span>
      </div>
      <h1 class="mt-2 text-4xl font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-vfNavy to-[#1a4a82] sm:text-5xl drop-shadow-sm">
        Gửi yêu cầu hỗ trợ
      </h1>
      <p class="mt-4 max-w-2xl mx-auto text-base text-slate-600">
        Bạn có câu hỏi về sản phẩm, chính sách hoặc dịch vụ? Hãy để lại thông tin, VinFast sẽ liên hệ sớm.
      </p>
    </div>

    <div class="grid gap-8 lg:grid-cols-12">
      <!-- ===== Form Box ===== -->
      <div class="lg:col-span-7">
        <div class="vf-contact-box">
          <div class="vf-contact-header">
            <h2><?= $isContactTab ? 'Gửi yêu cầu hỗ trợ' : 'Đăng ký lái thử xe' ?></h2>
            <p><?= $isContactTab ? 'Chúng tôi sẽ phản hồi trong vòng 24 giờ.' : 'Trải nghiệm cảm giác lái xe điện VinFast.' ?></p>
          </div>

          <!-- Tabs -->
          <div class="vf-contact-tabs px-4 pt-2">
            <a href="<?= BASE_URL ?>contact?tab=contact" class="vf-contact-tab <?= $isContactTab ? 'active' : '' ?>">
              <i class="fa-regular fa-envelope mr-1"></i> Liên hệ
            </a>
            <a href="<?= BASE_URL ?>contact?tab=test-drive" class="vf-contact-tab <?= $isTestDriveTab ? 'active' : '' ?>">
              <i class="fa-solid fa-car mr-1"></i> Đăng ký lái thử
            </a>
          </div>

          <div class="vf-contact-body">
            <!-- ===== Contact Form ===== -->
            <div class="vf-tab-panel <?= $isContactTab ? 'active' : '' ?>" id="panel-contact">
              <form action="<?= BASE_URL ?>contact/send" method="post" class="space-y-5">
                <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">

                <div class="grid gap-4 sm:grid-cols-2">
                  <div>
                    <label class="vf-form-label" for="c_name">Họ và tên <span class="text-red-500">*</span></label>
                    <input id="c_name" name="name" required class="vf-form-input" placeholder="VD: Nguyễn Văn A" />
                  </div>
                  <div>
                    <label class="vf-form-label" for="c_email">Email <span class="text-red-500">*</span></label>
                    <input id="c_email" name="email" type="email" required class="vf-form-input" placeholder="vd: example@gmail.com" />
                  </div>
                </div>

                <div>
                  <label class="vf-form-label" for="c_phone">Số điện thoại (tuỳ chọn)</label>
                  <input id="c_phone" name="phone" class="vf-form-input" placeholder="VD: 09xxxxxxxx" />
                </div>

                <div>
                  <label class="vf-form-label" for="c_message">Nội dung <span class="text-red-500">*</span></label>
                  <textarea id="c_message" name="message" rows="5" required class="vf-form-textarea" placeholder="Bạn cần hỗ trợ vấn đề gì?"></textarea>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                  <button type="submit" class="vf-btn-submit">
                    <i class="fa-solid fa-paper-plane"></i> Gửi liên hệ
                  </button>
                </div>
              </form>
            </div>

            <!-- ===== Test Drive Form ===== -->
            <div class="vf-tab-panel <?= $isTestDriveTab ? 'active' : '' ?>" id="panel-testdrive">
              <form action="<?= BASE_URL ?>contact/testdrive" method="post" class="space-y-5">
                <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">

                <div class="grid gap-4 sm:grid-cols-2">
                  <div>
                    <label class="vf-form-label" for="td_name">Họ và tên <span class="text-red-500">*</span></label>
                    <input id="td_name" name="name" required class="vf-form-input" placeholder="VD: Nguyễn Văn A" />
                  </div>
                  <div>
                    <label class="vf-form-label" for="td_email">Email <span class="text-red-500">*</span></label>
                    <input id="td_email" name="email" type="email" required class="vf-form-input" placeholder="vd: example@gmail.com" />
                  </div>
                </div>

                <div>
                  <label class="vf-form-label" for="td_phone">Số điện thoại <span class="text-red-500">*</span></label>
                  <input id="td_phone" name="phone" required class="vf-form-input" placeholder="VD: 09xxxxxxxx" />
                </div>

                <div>
                  <label class="vf-form-label" for="td_product">Dòng xe quan tâm <span class="text-red-500">*</span></label>
                  <select id="td_product" name="product_id" required class="vf-form-select">
                    <option value="">-- Chọn dòng xe --</option>
                    <?php foreach ($products as $p): ?>
                      <option value="<?= (int)($p['id'] ?? 0) ?>"><?= htmlspecialchars((string)($p['name'] ?? '')) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                  <div>
                    <label class="vf-form-label" for="td_province">Tỉnh / Thành phố <span class="text-red-500">*</span></label>
                    <select id="td_province" name="province" required class="vf-form-select">
                      <option value="">-- Chọn tỉnh thành --</option>
                      <?php foreach ($provinces as $pv): ?>
                        <option value="<?= htmlspecialchars($pv) ?>"><?= htmlspecialchars($pv) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div>
                    <label class="vf-form-label" for="td_showroom">Showroom <span class="text-red-500">*</span></label>
                    <select id="td_showroom" name="showroom" required class="vf-form-select" disabled>
                      <option value="">-- Chọn showroom --</option>
                    </select>
                  </div>
                </div>

                <div>
                  <label class="vf-form-label" for="td_date">Ngày mong muốn lái thử <span class="text-red-500">*</span></label>
                  <input id="td_date" name="preferred_date" type="date" required class="vf-form-input" />
                </div>

                <div>
                  <label class="vf-form-label" for="td_note">Ghi chú thêm</label>
                  <textarea id="td_note" name="note" rows="3" class="vf-form-textarea" placeholder="Yêu cầu đặc biệt hoặc thời gian cụ thể trong ngày..."></textarea>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                  <button type="submit" class="vf-btn-submit vf-btn-submit--gold">
                    <i class="fa-solid fa-car-side"></i> Đăng ký lái thử
                  </button>
                </div>
              </form>
            </div>
          </div>
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
            <div class="group rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100 transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
              <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#f8fafc] text-vfNavy transition-colors group-hover:bg-vfNavy group-hover:text-white">
                  <i class="fa-solid fa-location-dot"></i>
                </div>
                <div>
                  <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Địa chỉ</p>
                  <p class="mt-1 font-medium text-slate-900"><?= htmlspecialchars($address ?: 'Chưa cập nhật') ?></p>
                </div>
              </div>
              <a class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-vfNavy hover:text-[#FFB81C] transition-colors" href="<?= htmlspecialchars($mapUrl) ?>" target="_blank" rel="noreferrer">
                Xem trên bản đồ <i class="fa-solid fa-arrow-right-long"></i>
              </a>
            </div>

            <div class="group rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100 transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
              <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#f8fafc] text-vfNavy transition-colors group-hover:bg-vfNavy group-hover:text-white">
                  <i class="fa-solid fa-phone"></i>
                </div>
                <div>
                  <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Điện thoại</p>
                  <p class="mt-1 font-medium text-slate-900"><?= htmlspecialchars($phone ?: 'Chưa cập nhật') ?></p>
                </div>
              </div>
            </div>

            <div class="group rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100 transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
              <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#f8fafc] text-vfNavy transition-colors group-hover:bg-vfNavy group-hover:text-white">
                  <i class="fa-solid fa-envelope"></i>
                </div>
                <div>
                  <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Email</p>
                  <p class="mt-1 font-medium text-slate-900"><?= htmlspecialchars($email ?: 'Chưa cập nhật') ?></p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </aside>
    </div>
  </div>
</section>

<script>
  (function() {
    const provinceSelect = document.getElementById('td_province');
    const showroomSelect = document.getElementById('td_showroom');
    const showroomsData = <?= json_encode($showrooms, JSON_UNESCAPED_UNICODE) ?>;

    function updateShowrooms() {
      const province = provinceSelect.value;
      showroomSelect.innerHTML = '<option value="">-- Chọn showroom --</option>';
      showroomSelect.disabled = true;

      if (showroomsData[province]) {
        showroomsData[province].forEach(function(s) {
          const opt = document.createElement('option');
          opt.value = s;
          opt.textContent = s;
          showroomSelect.appendChild(opt);
        });
        showroomSelect.disabled = false;
      }
    }

    if (provinceSelect) {
      provinceSelect.addEventListener('change', updateShowrooms);
      // Init if pre-selected
      if (provinceSelect.value) updateShowrooms();
    }
  })();
</script>

