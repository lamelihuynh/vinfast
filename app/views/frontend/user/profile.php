<?php

/**
 * app/views/frontend/user/profile.php
 * Owner  : All members (common)
 * Title  : My Profile
 */

$u = isset($user) && is_array($user) ? $user : [];
$avatarFallback = 'data:image/svg+xml;utf8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 96 96"><rect width="96" height="96" rx="24" fill="#1a2240"/><circle cx="48" cy="38" r="18" fill="#e2e8f0"/><path d="M20 80c5-14 16-22 28-22s23 8 28 22" fill="#e2e8f0"/></svg>');
$avatarUrl = !empty($u['avatar']) ? BASE_URL . ltrim((string)$u['avatar'], '/') : $avatarFallback;
$hasNotice = !empty($_SESSION['flash']) || !empty($_SESSION['errors']);
?>

<section class="min-h-screen bg-[#F5F6F8] pt-10 pb-6">
    <div class="mx-auto w-full max-w-6xl px-4 lg:px-6">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-[320px_1fr]">
            <aside class="space-y-4">
                <?php include ROOT . '/app/views/frontend/user/partials/profile-card.php'; ?>
                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <?php $active = 'profile';
                    include ROOT . '/app/views/frontend/user/partials/sidebar.php'; ?>
                </div>
            </aside>

            <main class="space-y-6">
                <?php if ($hasNotice): ?>
                    <div class="space-y-3">
                        <?php if (!empty($_SESSION['flash'])): ?>
                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                                <?= htmlspecialchars((string)$_SESSION['flash']) ?>
                            </div>
                            <?php unset($_SESSION['flash']); ?>
                        <?php endif; ?>
                        <?php if (!empty($_SESSION['errors'])): ?>
                            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                                <div class="font-semibold">Vui lòng kiểm tra lại thông tin:</div>
                                <ul class="mt-2 list-disc pl-5 space-y-1">
                                    <?php foreach ((array)$_SESSION['errors'] as $error): ?>
                                        <li><?= htmlspecialchars((string)$error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php unset($_SESSION['errors']); ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <section class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Thông tin cá nhân</h2>
                            <p class="mt-1 text-sm text-slate-500">Cập nhật hồ sơ theo phong cách thẻ quản lý hiện đại.</p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">Hồ sơ chính</span>
                    </div>

                    <div class="px-6 py-6">
                        <form id="formUpdateProfile" action="<?= BASE_URL ?>user/saveProfile" method="POST" enctype="multipart/form-data" class="space-y-6">
                            <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">

                            <div class="grid gap-6 lg:grid-cols-[140px_1fr]">
                                <div class="flex flex-col items-center">
                                    <img id="profileAvatarPreview" src="<?= htmlspecialchars($avatarUrl) ?>" alt="Avatar" class="h-28 w-28 rounded-full object-cover ring-2 ring-slate-200">
                                    <div class="mt-2 text-center text-xs text-slate-500">Ảnh hiện tại</div>
                                </div>

                                <div class="grid gap-5 md:grid-cols-2">
                                    <div>
                                        <label for="name" class="mb-2 block text-sm font-medium text-slate-700">Họ và tên</label>
                                        <input type="text" id="name" name="name" value="<?= htmlspecialchars((string)($u['name'] ?? '')) ?>" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#1a2240] focus:ring-2 focus:ring-[#1a2240]/10">
                                    </div>
                                    <div>
                                        <label for="email" class="mb-2 block text-sm font-medium text-slate-700">Email</label>
                                        <input type="email" id="email" name="email" value="<?= htmlspecialchars((string)($u['email'] ?? '')) ?>" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#1a2240] focus:ring-2 focus:ring-[#1a2240]/10">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label for="avatar" class="mb-2 block text-sm font-medium text-slate-700">Ảnh đại diện mới</label>
                                        <input type="file" id="avatar" name="avatar" accept="image/*" class="w-full rounded-xl border border-dashed border-slate-300 bg-white px-4 py-3 text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-700">
                                        <p class="mt-2 text-xs text-slate-400">Hỗ trợ JPG, PNG, WebP. Khuyến nghị ảnh vuông để hiển thị đẹp hơn.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm text-slate-500">
                                Mẹo: dùng tên thật và email chính để nhận thông báo về đơn hàng, bảo dưỡng và chương trình ưu đãi.
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="inline-flex items-center rounded-xl bg-[#1a2240] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#233060]">Lưu hồ sơ</button>
                            </div>
                        </form>
                    </div>
                </section>

                <section class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h2 class="text-lg font-bold text-slate-900">Bảo mật tài khoản</h2>
                        <p class="mt-1 text-sm text-slate-500">Đặt mật khẩu mạnh và cập nhật định kỳ để bảo vệ tài khoản.</p>
                    </div>
                    <div class="px-6 py-6">
                        <form id="formChangePassword" action="<?= BASE_URL ?>user/changePassword" method="POST" class="space-y-5">
                            <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                            <div class="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label for="current" class="mb-2 block text-sm font-medium text-slate-700">Mật khẩu hiện tại</label>
                                    <input type="password" id="current" name="current" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#1a2240] focus:ring-2 focus:ring-[#1a2240]/10">
                                </div>
                                <div>
                                    <label for="new_password" class="mb-2 block text-sm font-medium text-slate-700">Mật khẩu mới</label>
                                    <input type="password" id="new_password" name="new_password" minlength="8" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#1a2240] focus:ring-2 focus:ring-[#1a2240]/10">
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500">
                                <div class="font-semibold text-slate-700 mb-1">Yêu cầu mật khẩu</div>
                                <ul class="list-disc pl-5 space-y-1">
                                    <li>Tối thiểu 8 ký tự</li>
                                    <li>Nên có chữ hoa, chữ thường và số</li>
                                    <li>Không dùng lại mật khẩu cũ</li>
                                </ul>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="inline-flex items-center rounded-xl border border-amber-300 bg-amber-400 px-5 py-3 text-sm font-semibold text-slate-900 transition hover:bg-amber-300">Đổi mật khẩu</button>
                            </div>
                        </form>
                    </div>
                </section>
            </main>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var forms = document.querySelectorAll('#formUpdateProfile, #formChangePassword');
        Array.prototype.slice.call(forms).forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });

        var avatarInput = document.getElementById('avatar');
        var avatarPreview = document.getElementById('profileAvatarPreview');
        if (avatarInput && avatarPreview) {
            avatarInput.addEventListener('change', function(event) {
                var file = event.target.files && event.target.files[0] ? event.target.files[0] : null;
                if (!file || !file.type || file.type.indexOf('image/') !== 0) {
                    return;
                }

                var reader = new FileReader();
                reader.onload = function(e) {
                    if (e.target && typeof e.target.result === 'string') {
                        avatarPreview.setAttribute('src', e.target.result);
                    }
                };
                reader.readAsDataURL(file);
            });
        }
    });
</script>