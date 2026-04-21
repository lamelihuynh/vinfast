<section data-step-panel="2" class="space-y-4<?= $currentStep === 2 ? '' : ' hidden' ?>">
    <div>
        <h2 class="mb-1 text-[15px] font-bold text-slate-900">Bước 2 - Thông tin khách hàng</h2>
        <p class="m-0 text-[12px] leading-relaxed text-slate-500">Tiếp theo, Quý khách hãy cung cấp thông tin chủ xe và lựa chọn showroom nhận xe.</p>
    </div>

    <div class="grid gap-4 rounded-xl border border-slate-100 p-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <p class="mb-2 text-[12px] font-semibold text-slate-800">Chủ sở hữu xe</p>
            <div class="flex flex-wrap gap-4">
                <?php foreach (['ca-nhan' => 'Cá nhân', 'doanh-nghiep' => 'Doanh nghiệp'] as $value => $label): ?>
                    <label class="inline-flex cursor-pointer items-center gap-2 text-[12px] text-slate-700">
                        <input type="radio" name="owner_type" value="<?= htmlspecialchars($value) ?>" class="h-4 w-4 border-slate-300 text-blue-600" <?= $formData['owner_type'] === $value ? 'checked' : '' ?>>
                        <span><?= htmlspecialchars($label) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="sm:col-span-2">
            <label class="mb-1 block text-[12px] font-semibold text-slate-700">Họ và tên <span class="text-red-500">*</span></label>
            <input type="text" name="full_name" maxlength="80" value="<?= htmlspecialchars((string)$formData['full_name']) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-[13px] text-slate-700 outline-none transition focus:border-blue-500" placeholder="Nguyễn Hải Nam" required>
        </div>

        <div>
            <label class="mb-1 block text-[12px] font-semibold text-slate-700">Số điện thoại <span class="text-red-500">*</span></label>
            <input type="tel" name="phone" value="<?= htmlspecialchars((string)$formData['phone']) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-[13px] text-slate-700 outline-none transition focus:border-blue-500" placeholder="09xxxxxxxx" required>
        </div>

        <div>
            <label class="mb-1 block text-[12px] font-semibold text-slate-700">Email <span class="text-red-500">*</span></label>
            <input type="email" name="email" value="<?= htmlspecialchars((string)$formData['email']) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-[13px] text-slate-700 outline-none transition focus:border-blue-500" placeholder="example@gmail.com" required>
        </div>

        <div>
            <label class="mb-1 block text-[12px] font-semibold text-slate-700">Số CCCD</label>
            <input type="text" name="cccd" value="<?= htmlspecialchars((string)$formData['cccd']) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-[13px] text-slate-700 outline-none transition focus:border-blue-500" placeholder="Nhập số CCCD">
        </div>

        <div>
            <label class="mb-1 block text-[12px] font-semibold text-slate-700">Mã ưu đãi</label>
            <input type="text" name="voucher" value="<?= htmlspecialchars((string)$formData['voucher']) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-[13px] text-slate-700 outline-none transition focus:border-blue-500" placeholder="Nhập mã E-voucher">
        </div>

        <div>
            <label class="mb-1 block text-[12px] font-semibold text-slate-700">Tỉnh thành <span class="text-red-500">*</span></label>
            <select name="province" data-province-select class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-[13px] text-slate-700 outline-none transition focus:border-blue-500" required>
                <option value="">Chọn tỉnh thành</option>
                <?php foreach ($provinces as $province): ?>
                    <option value="<?= htmlspecialchars((string)$province) ?>" <?= $formData['province'] === $province ? 'selected' : '' ?>><?= htmlspecialchars((string)$province) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="mb-1 block text-[12px] font-semibold text-slate-700">Showroom nhận xe <span class="text-red-500">*</span></label>
            <select name="showroom" data-showroom-select data-selected="<?= htmlspecialchars((string)$formData['showroom']) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-[13px] text-slate-700 outline-none transition focus:border-blue-500" required>
                <option value="">Chọn showroom</option>
            </select>
        </div>

        <div class="sm:col-span-2">
            <label class="mb-1 block text-[12px] font-semibold text-slate-700">Tư vấn bán hàng</label>
            <input type="text" name="salesperson" value="<?= htmlspecialchars((string)$formData['salesperson']) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-[13px] text-slate-700 outline-none transition focus:border-blue-500" placeholder="Nhập tên tư vấn (nếu có)">
        </div>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
        <button type="button" data-step-prev="1" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-[13px] font-semibold text-slate-700 transition hover:bg-slate-100">Quay lại</button>
        <button type="button" data-step-next="3" class="inline-flex items-center justify-center rounded-lg border border-vfNavy bg-vfNavy px-4 py-2 text-[13px] font-semibold text-white transition hover:opacity-90">Xem xác nhận đặt cọc</button>
    </div>
</section>