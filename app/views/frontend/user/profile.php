<?php

/**
 * app/views/frontend/user/profile.php
 * Owner  : All members (common)
 * Title  : My Profile
 */

$u = isset($user) && is_array($user) ? $user : [];
$avatarFallback = 'data:image/svg+xml;utf8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 96 96"><rect width="96" height="96" rx="24" fill="#1a2240"/><circle cx="48" cy="38" r="18" fill="#e2e8f0"/><path d="M20 80c5-14 16-22 28-22s23 8 28 22" fill="#e2e8f0"/></svg>');
$avatarUrl = !empty($u['avatar']) ? BASE_URL . ltrim((string)$u['avatar'], '/') : $avatarFallback;
$createdAt = !empty($u['created_at']) ? date('F Y', strtotime((string)$u['created_at'])) : 'N/A';
$status = empty($u['is_locked']) ? 'Hoạt động' : 'Bị khóa';
?>

<section class="min-h-screen bg-[#F5F6F8] py-6">
    <div class="mx-auto w-full max-w-6xl px-4 lg:px-6">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#1a2240] via-[#233060] to-[#1a2240] px-6 py-10 text-white shadow-lg lg:px-10">
            <div class="absolute -right-16 -top-16 h-56 w-56 rounded-full bg-[#c8a22e]/10"></div>
            <div class="absolute -bottom-12 left-10 h-40 w-40 rounded-full bg-white/5"></div>
            <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-[0.28em] text-white/55">Tài khoản</p>
                    <h1 class="mb-3 text-3xl font-extrabold tracking-[-0.03em] lg:text-4xl">Hồ sơ cá nhân</h1>
                    <p class="mb-0 max-w-xl text-sm leading-6 text-white/70">Cập nhật thông tin người dùng, ảnh đại diện và mật khẩu trong cùng một không gian quản lý thống nhất.</p>
                </div>

                <div class="flex items-center gap-4 rounded-2xl border border-white/10 bg-white/10 px-4 py-3 backdrop-blur">
                    <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="User Avatar" class="h-16 w-16 rounded-2xl object-cover ring-2 ring-white/25">
                    <div>
                        <div class="text-lg font-bold"><?= htmlspecialchars((string)($u['name'] ?? 'Chưa cập nhật tên')) ?></div>
                        <div class="text-sm text-white/65"><?= htmlspecialchars((string)($u['email'] ?? '')) ?></div>
                        <div class="mt-1 text-xs uppercase tracking-[0.18em] text-white/45"><?= htmlspecialchars($status) ?> · <?= htmlspecialchars($createdAt) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($_SESSION['flash']) || !empty($_SESSION['errors'])): ?>
            <div class="mt-6 space-y-3">
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

        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-[320px_1fr]">
            <aside class="space-y-4">
                <?php include ROOT . '/app/views/frontend/user/partials/profile-card.php'; ?>
                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <?php $active = 'profile';
                    include ROOT . '/app/views/frontend/user/partials/sidebar.php'; ?>
                </div>
            </aside>

            <main class="space-y-6">
                <section class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h2 class="text-lg font-bold text-slate-900">Cập nhật hồ sơ</h2>
                        <p class="mt-1 text-sm text-slate-500">Tên, email và ảnh đại diện của bạn.</p>
                    </div>
                    <div class="px-6 py-6">
                        <form id="formUpdateProfile" action="<?= BASE_URL ?>user/saveProfile" method="POST" enctype="multipart/form-data" class="space-y-5">
                            <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                            <div class="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label for="name" class="mb-2 block text-sm font-medium text-slate-700">Tên người dùng</label>
                                    <input type="text" id="name" name="name" value="<?= htmlspecialchars((string)($u['name'] ?? '')) ?>" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#1a2240] focus:ring-2 focus:ring-[#1a2240]/10">
                                </div>
                                <div>
                                    <label for="email" class="mb-2 block text-sm font-medium text-slate-700">Email</label>
                                    <input type="email" id="email" name="email" value="<?= htmlspecialchars((string)($u['email'] ?? '')) ?>" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#1a2240] focus:ring-2 focus:ring-[#1a2240]/10">
                                </div>
                            </div>

                            <div>
                                <label for="avatar" class="mb-2 block text-sm font-medium text-slate-700">Ảnh đại diện</label>
                                <input type="file" id="avatar" name="avatar" accept="image/*" class="w-full rounded-xl border border-dashed border-slate-300 bg-white px-4 py-3 text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-700">
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="inline-flex items-center rounded-xl bg-[#1a2240] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#233060]">Lưu hồ sơ</button>
                            </div>
                        </form>
                    </div>
                </section>

                <section class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h2 class="text-lg font-bold text-slate-900">Đổi mật khẩu</h2>
                        <p class="mt-1 text-sm text-slate-500">Đặt mật khẩu mạnh để bảo vệ tài khoản.</p>
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

                            <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-500">Mật khẩu mới nên có tối thiểu 8 ký tự và gồm cả chữ hoa lẫn số.</div>

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
    });
</script>