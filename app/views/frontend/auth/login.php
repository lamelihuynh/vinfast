<?php

/**
 * app/views/frontend/auth/login.php
 * Owner  : All members (common)
 * Title  : Login
 *
 * Purpose: Bootstrap card, centred. Email + password inputs. CSRF hidden input. Client-side validation (validate.js). Link to /auth/register.
 *
 * Variables available (set by controller via View::render):
 *   None (flash/errors from session)
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
  <div class="hidden lg:flex w-[42%] flex-col justify-between p-12 relative overflow-hidden bg-gradient-to-br from-slate-900 via-[#233060] to-slate-900">
    <div class="absolute w-[420px] h-[420px] rounded-full bg-yellow-500/15 -top-24 -right-20"></div>
    <div class="absolute w-[260px] h-[260px] rounded-full bg-blue-500/10 bottom-16 -left-16"></div>

    <div class="relative z-10">
      <img src="<?= BASE_URL ?>public/images/logo/vinfast-logo.png" alt="VinFast" class="h-11 w-auto object-contain">
    </div>

    <div class="relative z-10 space-y-5">
      <div>
        <h2 class="text-white text-3xl font-extrabold leading-tight mb-3">Chào mừng trở lại!</h2>
        <p class="text-white/70 text-sm leading-7">Đăng nhập để quản lý đơn hàng, theo dõi lịch sử giao dịch và sử dụng dịch vụ VinFast nhanh hơn.</p>
      </div>
      <div class="space-y-3">
        <div class="flex items-center gap-3 text-white/80 text-sm"><span class="w-8 h-8 rounded-full bg-yellow-500/20 inline-flex items-center justify-center">🚗</span><span>Quản lý thông tin xe đã đặt</span></div>
        <div class="flex items-center gap-3 text-white/80 text-sm"><span class="w-8 h-8 rounded-full bg-yellow-500/20 inline-flex items-center justify-center">📋</span><span>Theo dõi đơn đặt cọc realtime</span></div>
        <div class="flex items-center gap-3 text-white/80 text-sm"><span class="w-8 h-8 rounded-full bg-yellow-500/20 inline-flex items-center justify-center">🎁</span><span>Nhận ưu đãi và chương trình riêng</span></div>
      </div>
    </div>

    <small class="relative z-10 text-white/40 text-xs">© 2024 VinFast Trading and Service Company Limited</small>
  </div>

  <div class="flex-1 flex items-center justify-center p-6 lg:p-12">
    <div class="w-full max-w-[420px]">
      <a href="<?= BASE_URL ?>" class="inline-block mb-6 text-sm text-slate-500 hover:text-slate-800">&larr; Quay lại trang chủ</a>

      <div class="lg:hidden mb-6 text-center">
        <img src="<?= BASE_URL ?>public/images/logo/vinfast-logo.png" alt="VinFast" class="h-9 w-auto object-contain mx-auto">
      </div>

      <h1 class="text-2xl font-extrabold text-slate-900 mb-1">Đăng nhập</h1>
      <p class="text-sm text-slate-500 mb-6">Chưa có tài khoản? <a href="<?= BASE_URL ?>auth/register" class="text-blue-600 font-semibold hover:underline">Đăng ký ngay</a></p>

      <form method="POST" action="<?= BASE_URL ?>auth/login" class="needs-validation space-y-4" novalidate>
        <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">

        <div>
          <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email <span class="text-red-500">*</span></label>
          <input
            type="email"
            class="form-control !rounded-xl !border-slate-200 !py-3 !px-4"
            id="email"
            name="email"
            value="<?= htmlspecialchars((string)($old['email'] ?? '')) ?>"
            placeholder="ten@example.com"
            required>
          <div class="invalid-feedback">Vui lòng nhập email hợp lệ.</div>
        </div>

        <div>
          <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Mật khẩu <span class="text-red-500">*</span></label>
          <input
            type="password"
            class="form-control !rounded-xl !border-slate-200 !py-3 !px-4"
            id="password"
            name="password"
            placeholder="••••••••"
            required>
          <div class="invalid-feedback">Mật khẩu là bắt buộc.</div>
        </div>

        <div class="flex items-center justify-between">
          <label class="inline-flex items-center gap-2 text-sm text-slate-600">
            <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
            Ghi nhớ đăng nhập
          </label>
          <span class="text-xs text-slate-400">Quên mật khẩu?</span>
        </div>

        <button type="submit" class="w-full rounded-xl bg-[#1464F4] hover:bg-blue-700 text-white font-bold py-3 text-sm tracking-wide">ĐĂNG NHẬP</button>
      </form>
    </div>
  </div>
</section>