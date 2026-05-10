<?php
$currentStep = $currentStep ?? 2;
$formData = $formData ?? [];
$provinces = $provinces ?? [];
?>
<section data-step-panel="2" class="space-y-4<?= $currentStep === 2 ? '' : ' hidden' ?>">
    <div>
        <h2 class="mb-1 text-[15px] font-bold text-slate-900">Bước 2 - Thông tin khách hàng</h2>
        <p class="m-0 text-[12px] leading-relaxed text-slate-500">Tiếp theo, Quý khách hãy cung cấp thông tin chủ xe và lựa chọn showroom nhận xe.</p>
    </div>

    <div class="grid gap-4 rounded-xl border border-slate-100 p-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label class="mb-1 block text-[12px] font-semibold text-slate-700">Họ và tên <span class="text-red-500">*</span></label>
            <input type="text" name="full_name" maxlength="80" value="<?= htmlspecialchars((string)$formData['full_name']) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-[13px] text-slate-700 outline-none transition focus:border-blue-500" placeholder="Họ và tên" required>
        </div>

        <div class="sm:col-span-2">
            <label class="mb-1 block text-[12px] font-semibold text-slate-700">Số điện thoại <span class="text-red-500">*</span></label>
            <input type="tel" name="phone"
                maxlength="10"
                pattern="[0-9]{10}"
                oninput="this.value = this.value.replace(/[^0-9]/g, '').substring(0, 10)"
                value="<?= htmlspecialchars((string)$formData['phone']) ?>"
                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-[13px] text-slate-700 outline-none transition focus:border-blue-500"
                placeholder="09xxxxxxxx" required>
            <p data-error="phone" class="mt-1 hidden text-[12px] text-red-600"></p>
        </div>

        <div class="sm:col-span-2">
            <label class="mb-1 block text-[12px] font-semibold text-slate-700">Email <span class="text-red-500">*</span></label>
            <input type="email" name="email"
                value="<?= htmlspecialchars((string)($formData['email'] ?? '')) ?>"
                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-[13px] text-slate-700 outline-none transition focus:border-blue-500"
                placeholder="example@gmail.com" required>
            <p data-error="email" class="mt-1 hidden text-[12px] text-red-600"></p>
        </div>

        <div class="sm:col-span-2">
            <label class="mb-1 block text-[12px] font-semibold text-slate-700">Số CCCD <span class="text-red-500">*</span></label>
            <input type="text" name="cccd"
                maxlength="12"
                pattern="[0-9]{12}"
                oninput="this.value = this.value.replace(/[^0-9]/g, '').substring(0, 12)"
                value="<?= htmlspecialchars((string)($formData['cccd'] ?? '')) ?>"
                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-[13px] text-slate-700 outline-none transition focus:border-blue-500"
                placeholder="Nhập 12 số CCCD" required>
            <p data-error="cccd" class="mt-1 hidden text-[12px] text-red-600"></p>
        </div>

        <div class="sm:col-span-2">
            <label class="mb-1 block text-[12px] font-semibold text-slate-700">Tỉnh thành <span class="text-red-500">*</span></label>
            <select name="province" data-province-select class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-[13px] text-slate-700 outline-none transition focus:border-blue-500" required>
                <option value="">Chọn tỉnh thành</option>
                <?php foreach ($provinces as $province): ?>
                    <option value="<?= htmlspecialchars((string)$province) ?>" <?= $formData['province'] === $province ? 'selected' : '' ?>><?= htmlspecialchars((string)$province) ?></option>
                <?php endforeach; ?>
            </select>
            <p data-error="province" class="mt-1 hidden text-[12px] text-red-600"></p>
        </div>

        <div class="sm:col-span-2">
            <label class="mb-1 block text-[12px] font-semibold text-slate-700">Showroom nhận xe <span class="text-red-500">*</span></label>
            <select name="showroom" data-showroom-select data-selected="<?= htmlspecialchars((string)$formData['showroom']) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-[13px] text-slate-700 outline-none transition focus:border-blue-500" required>
                <option value="">Chọn showroom</option>
            </select>
            <p data-error="showroom" class="mt-1 hidden text-[12px] text-red-600"></p>
        </div>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
        <button type="button" data-step-prev="1" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-[13px] font-semibold text-slate-700 transition hover:bg-slate-100">Quay lại</button>
        <button type="button" data-step-next="3" class="inline-flex items-center justify-center rounded-lg border border-vfNavy bg-vfNavy px-4 py-2 text-[13px] font-semibold text-white transition hover:opacity-90">Xem xác nhận đặt cọc</button>
    </div>
</section>