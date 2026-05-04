<?php
$userData = $userData ?? null;
if (empty($userData) && Auth::check()) {
    $userData = (new User())->findById((int)Auth::id());
}

$name = htmlspecialchars((string)($userData['name'] ?? 'Người dùng'));
$email = htmlspecialchars((string)($userData['email'] ?? ''));
$avatarFallback = 'data:image/svg+xml;utf8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 96 96"><rect width="96" height="96" rx="24" fill="#1a2240"/><circle cx="48" cy="38" r="18" fill="#e2e8f0"/><path d="M20 80c5-14 16-22 28-22s23 8 28 22" fill="#e2e8f0"/></svg>');
$avatar = htmlspecialchars((string)($userData['avatar'] ?? $avatarFallback));
?>
<div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
    <div class="flex items-center gap-4">
        <img src="<?= $avatar ?>" alt="avatar" class="h-16 w-16 rounded-2xl object-cover ring-2 ring-slate-100">
        <div class="min-w-0">
            <h3 class="truncate text-base font-bold text-slate-900"><?= $name ?></h3>
            <p class="truncate text-sm text-slate-500"><?= $email ?></p>
            <div class="mt-2 inline-flex rounded-full bg-slate-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Hồ sơ</div>
        </div>
    </div>
</div>