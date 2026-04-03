<?php

/**
 * app/views/frontend/auth/register.php
 * Owner  : All members (common)
 * Title  : Register
 *
 * Purpose: Name, email, password, confirm-password. CSRF token. Matching password check in validate.js. Link to /auth/login.
 *
 * Variables available (set by controller via View::render):
 *   None
 *
  Assets    : public/js/frontend/validate.js
 *
 * TODO: Replace the placeholder below with the actual HTML implementation.
 * -----------------------------------------------------------------------
 * Rules:
 *  - Always escape output: <?= htmlspecialchars($var) ?>
 *  - Include CSRF in every form: <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
 *  - Include pagination partial where needed: include ROOT."/app/views/frontend/partials/pagination.php"
 */
?>
<?php $old = is_array($old ?? null) ? $old : []; ?>
<section class="min-h-screen flex">
  <div class="hidden lg:flex w-[38%] flex-col justify-between p-12 relative overflow-hidden bg-gradient-to-br from-slate-900 via-[#233060] to-slate-900">
    <div class="absolute w-[380px] h-[380px] rounded-full bg-yellow-500/15 -top-20 -right-14"></div>
    <div class="absolute w-[220px] h-[220px] rounded-full bg-blue-500/10 bottom-20 -left-12"></div>

    <div class="relative z-10">
      <img src="<?= BASE_URL ?>public/images/logo/vinfast-logo.png" alt="VinFast" class="h-11 w-auto object-contain">
    </div>

    <div class="relative z-10 space-y-5">
      <div>
        <h2 class="text-white text-3xl font-extrabold leading-tight mb-3">Tham gia VinFast Family</h2>
        <p class="text-white/70 text-sm leading-7">Tạo tài khoản để nhận ưu đãi độc quyền và sử dụng dịch vụ hậu mãi thông minh của VinFast.</p>
      </div>
      <div class="space-y-3">
        <div class="flex items-center gap-3 text-white/80 text-sm"><span class="w-8 h-8 rounded-full bg-yellow-500/20 inline-flex items-center justify-center">🎁</span><span>Tích điểm ưu đãi theo giao dịch</span></div>
        <div class="flex items-center gap-3 text-white/80 text-sm"><span class="w-8 h-8 rounded-full bg-yellow-500/20 inline-flex items-center justify-center">🚗</span><span>Đặt cọc xe nhanh chóng</span></div>
        <div class="flex items-center gap-3 text-white/80 text-sm"><span class="w-8 h-8 rounded-full bg-yellow-500/20 inline-flex items-center justify-center">🔧</span><span>Quản lý lịch bảo dưỡng</span></div>
      </div>
    </div>

    <small class="relative z-10 text-white/40 text-xs">© 2024 VinFast Trading and Service Company Limited</small>
  </div>

  <div class="flex-1 overflow-y-auto flex items-center justify-center p-6 lg:p-10">
    <div class="w-full max-w-[440px]">
      <a href="<?= BASE_URL ?>auth/login" class="inline-block mb-6 text-sm text-slate-500 hover:text-slate-800">&larr; Quay lại đăng nhập</a>

      <div class="lg:hidden mb-6 text-center">
        <img src="<?= BASE_URL ?>public/images/logo/vinfast-logo.png" alt="VinFast" class="h-9 w-auto object-contain mx-auto">
      </div>

      <h1 class="text-2xl font-extrabold text-slate-900 mb-1">Tạo tài khoản</h1>
      <p class="text-sm text-slate-500 mb-6">Đã có tài khoản? <a href="<?= BASE_URL ?>auth/login" class="text-blue-600 font-semibold hover:underline">Đăng nhập</a></p>

      <form method="POST" action="<?= BASE_URL ?>auth/register" class="needs-validation space-y-4" novalidate>
        <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">

        <div>
          <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Họ và tên <span class="text-red-500">*</span></label>
          <input
            type="text"
            class="form-control !rounded-xl !border-slate-200 !py-3 !px-4"
            id="name"
            name="name"
            placeholder="Họ và tên"
            value="<?= htmlspecialchars((string)($old['name'] ?? '')) ?>"
            required>
          <div class="invalid-feedback">Họ và tên là bắt buộc.</div>
        </div>

        <div>
          <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email <span class="text-red-500">*</span></label>
          <input
            type="email"
            class="form-control !rounded-xl !border-slate-200 !py-3 !px-4"
            id="email"
            name="email"
            placeholder="Email"
            value="<?= htmlspecialchars((string)($old['email'] ?? '')) ?>"
            required>
          <div class="invalid-feedback">Vui lòng nhập email hợp lệ.</div>
        </div>

        <div>
          <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Mật khẩu <span class="text-red-500">*</span></label>
          <div class="relative">
            <input
              type="password"
              class="form-control !rounded-xl !border-slate-200 !py-3 !px-4 !pr-11"
              id="password"
              name="password"
              minlength="8"
              placeholder="Mật khẩu"
              required>
            <button
              type="button"
              class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
              data-target="password"
              aria-label="Hiện mật khẩu"
              aria-pressed="false">
              <svg class="eye-open w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z" />
                <circle cx="12" cy="12" r="3" />
              </svg>
              <svg class="eye-closed w-5 h-5 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.58 10.58A3 3 0 0 0 13.42 13.42M9.88 5.08A10.5 10.5 0 0 1 12 4.88c6 0 9.75 7.12 9.75 7.12a18.41 18.41 0 0 1-3.64 4.58M6.34 6.34A18.65 18.65 0 0 0 2.25 12s3.75 6.75 9.75 6.75a10.7 10.7 0 0 0 4.12-.8" />
              </svg>
            </button>
          </div>
          <div class="password-require mt-2">
            <p class="descx text-xs font-medium text-slate-500 mb-1">Mật khẩu bao gồm</p>
            <ul class="below-desc space-y-1 text-xs">
              <li id="character" class="text-slate-400 transition-colors duration-200"><span>Ít nhất 8 ký tự</span></li>
              <li id="uppercase" class="text-slate-400 transition-colors duration-200"><span>Chữ hoa &amp; chữ thường</span></li>
              <li id="special" class="text-slate-400 transition-colors duration-200"><span>Ít nhất 1 số</span></li>
            </ul>
          </div>
          <div class="invalid-feedback">Mật khẩu tối thiểu 8 ký tự.</div>
        </div>

        <div>
          <label for="confirm_password" class="block text-sm font-medium text-slate-700 mb-1">Xác nhận mật khẩu <span class="text-red-500">*</span></label>
          <div class="relative">
            <input
              type="password"
              class="form-control !rounded-xl !border-slate-200 !py-3 !px-4 !pr-11"
              id="confirm_password"
              name="confirm_password"
              placeholder="Nhập lại mật khẩu"
              required>
            <button
              type="button"
              class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
              data-target="confirm_password"
              aria-label="Hiện mật khẩu"
              aria-pressed="false">
              <svg class="eye-open w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z" />
                <circle cx="12" cy="12" r="3" />
              </svg>
              <svg class="eye-closed w-5 h-5 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.58 10.58A3 3 0 0 0 13.42 13.42M9.88 5.08A10.5 10.5 0 0 1 12 4.88c6 0 9.75 7.12 9.75 7.12a18.41 18.41 0 0 1-3.64 4.58M6.34 6.34A18.65 18.65 0 0 0 2.25 12s3.75 6.75 9.75 6.75a10.7 10.7 0 0 0 4.12-.8" />
              </svg>
            </button>
          </div>
          <div class="invalid-feedback">Vui lòng xác nhận mật khẩu.</div>
        </div>

        <div class="form-check py-1">
          <input class="form-check-input" type="checkbox" id="terms" required>
          <label class="form-check-label text-sm text-slate-600" for="terms">Tôi đồng ý với các điều khoản sử dụng và chính sách bảo mật của VinFast.</label>
          <div class="invalid-feedback">Bạn cần đồng ý điều khoản để tiếp tục.</div>
        </div>

        <button type="submit" class="w-full rounded-xl bg-[#1464F4] hover:bg-blue-700 text-white font-bold py-3 text-sm tracking-wide">ĐĂNG KÝ</button>
      </form>
    </div>
  </div>
</section>