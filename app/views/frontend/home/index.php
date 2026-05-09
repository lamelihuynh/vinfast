<?php

/**
 * app/views/frontend/home/index.php
 * Owner  : Tang Vu 
 * Title  : Homepage
 *
 * Purpose: Hero: Swiper.js carousel (banners from SiteSetting). Featured vehicles grid (6 items). Latest news cards (3 items). CTA section.
 *
 * Variables available (set by controller via View::render):
 *   $settings (array), $featured (array of products), $latest (array of articles)
 *
 *
 * TODO: Replace the placeholder below with the actual HTML implementation.
 * -----------------------------------------------------------------------
 * Rules:
 *  - Always escape output: <?= htmlspecialchars($var) ?>
 *  - Include CSRF in every form: <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
 *  - Include pagination partial where needed: include ROOT."/app/views/frontend/partials/pagination.php"
 */
?>
<?php
// =========================================================
// HOMEPAGE (Tailwind + Swiper) - phiên bản bám sát layout mẫu
// =========================================================

// -------- Hero assets --------
$tagline = !empty($settings['tagline']) ? (string)$settings['tagline'] : "Kiến tạo\ntương lai xanh";
$subTagline = !empty($settings['sub_tagline']) ? (string)$settings['sub_tagline'] : 'Khám phá bộ sưu tập xe điện thông minh, sang trọng và hướng đến một tương lai bền vững cùng VinFast.';
$banners = [
    SiteSetting::imageUrl($settings['banner_1'] ?? '', 'public/images/banners/banner_background.png'),
    SiteSetting::imageUrl($settings['banner_2'] ?? '', 'public/images/banners/banner_02.png'),
    SiteSetting::imageUrl($settings['banner_3'] ?? '', 'public/images/banners/banner_03.png'),
];

// -------- Reuse data --------
$leadProduct = $featured[0] ?? null;
$compareA = $featured[0] ?? null;
$compareB = $featured[1] ?? null;

function vf_news_thumb(array $news): string
{
    $thumb = trim((string)($news['thumbnail'] ?? ''));
    if ($thumb === '') return BASE_URL . 'public/images/banners/banner_background.png';
    if (preg_match('~^https?://~i', $thumb)) return $thumb;
    return BASE_URL . ltrim($thumb, '/');
}

function vf_product_price(array $p): string
{
    return number_format((float)($p['price'] ?? 0), 0, ',', '.') . ' VNĐ';
}

function vf_product_range(array $p): string
{
    $km = Product::extractRangeKm((array)($p['specs'] ?? []));
    return $km > 0 ? (string)$km . ' km' : '--';
}
?>

<style>
    .vfHomeHero .swiper-pagination-bullet {
        width: 28px;
        height: 4px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .45);
    }

    .vfHomeHero .swiper-pagination-bullet-active {
        background: #fff;
    }
</style>

<!-- HERO -->
<section class="relative bg-slate-900">
    <div class="swiper vfHomeHero">
        <div class="swiper-wrapper">
            <?php foreach ($banners as $src): ?>
                <div class="swiper-slide">
                    <div class="relative h-[420px] sm:h-[520px] lg:h-[600px]">
                        <img src="<?= htmlspecialchars($src) ?>" alt="VinFast banner" class="h-full w-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-r from-[#071a33]/85 via-[#0a2a4b]/50 to-transparent"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="pointer-events-none absolute inset-0 z-10">
            <div class="mx-auto flex h-full max-w-6xl items-center px-4">
                <div class="pointer-events-auto max-w-lg">
                    <p class="inline-flex rounded-full bg-[#FFB81C] px-3 py-1 text-xs font-semibold text-vfNavy">VINFAST</p>
                    <h1 class="mt-3 text-4xl font-bold leading-tight text-white sm:text-5xl break-words"><?= nl2br(htmlspecialchars($tagline)) ?></h1>
                    <p class="mt-4 max-w-md text-sm leading-relaxed text-white/90 sm:text-base break-words"><?= nl2br(htmlspecialchars($subTagline)) ?></p>
                    <div class="mt-8 flex flex-col items-start gap-4">
                        <div class="flex flex-wrap gap-4">
                            <a href="<?= BASE_URL ?>products" class="group relative overflow-hidden rounded-full bg-gradient-to-r from-[#FFB81C] to-[#f59e0b] px-6 py-3 text-sm font-bold text-vfNavy hover:text-white shadow-[0_4px_15px_rgba(255,184,28,0.4)] transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_6px_20px_rgba(255,184,28,0.6)]">
                                <span class="relative z-10">XEM NGAY</span>
                            </a>
                            <a href="<?= BASE_URL ?>contact" class="rounded-full border-2 border-white/40 bg-white/5 backdrop-blur-sm px-6 py-3 text-sm font-bold text-white transition-all duration-300 hover:-translate-y-1 hover:bg-[rgba(255,255,255,0.15)] hover:text-white/90">
                                TƯ VẤN
                            </a>
                        </div>

                        <a href="<?= BASE_URL ?>contact?tab=test-drive" class="inline-flex items-center justify-center rounded-full border-2 border-[#FFB81C] px-6 py-3 text-sm font-bold text-[#FFB81C] transition-all duration-300 hover:-translate-y-1 hover:bg-[#FFB81C] hover:text-vfNavy hover:shadow-[0_4px_15px_rgba(255,184,28,0.4)]">
                            <i class="fa-solid fa-car-side mr-2"></i> ĐĂNG KÝ LÁI THỬ
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="absolute inset-x-0 bottom-4 z-20">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4">
                <div class="vfHomeHeroPagination"></div>
                <div class="flex gap-2">
                    <button class="vfHomeHeroPrev inline-flex h-9 w-9 items-center justify-center rounded-full bg-white/20 text-white"><i class="fa-solid fa-chevron-left text-xs"></i></button>
                    <button class="vfHomeHeroNext inline-flex h-9 w-9 items-center justify-center rounded-full bg-white/20 text-white"><i class="fa-solid fa-chevron-right text-xs"></i></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Small stat strip giống mẫu -->

    <div class="bg-[#071f3f]">
        <div class="mx-auto max-w-6xl grid grid-cols-2 sm:grid-cols-4 text-center text-white">

            <div class="py-4 ">
                <p class="text-xl font-bold"><?= htmlspecialchars((string)($settings['stat1_val'] ?? '14+')) ?></p>
                <p class="text-[11px] uppercase text-white/60"><?= htmlspecialchars((string)($settings['stat1_lbl'] ?? 'Quốc gia hiện diện')) ?></p>
            </div>

            <div class="py-4 ">
                <p class="text-xl font-bold"><?= htmlspecialchars((string)($settings['stat2_val'] ?? '150,000+')) ?></p>
                <p class="text-[11px] uppercase text-white/60"><?= htmlspecialchars((string)($settings['stat2_lbl'] ?? 'Khách hàng tin dùng')) ?></p>
            </div>

            <div class="py-4 ">
                <p class="text-xl font-bold"><?= htmlspecialchars((string)($settings['stat3_val'] ?? '8 mẫu')) ?></p>
                <p class="text-[11px] uppercase text-white/60"><?= htmlspecialchars((string)($settings['stat3_lbl'] ?? 'Xe hiện có')) ?></p>
            </div>

            <div class="py-4">
                <p class="text-xl font-bold"><?= htmlspecialchars((string)($settings['stat4_val'] ?? '500+')) ?></p>
                <p class="text-[11px] uppercase text-white/60"><?= htmlspecialchars((string)($settings['stat4_lbl'] ?? 'Showroom toàn cầu')) ?></p>
            </div>

        </div>
    </div>
</section>

<!-- DONG XE NOI BAT -->
<section class="bg-white py-12">
    <div class="mx-auto max-w-6xl px-4">
        <h2 class="text-center text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-vfNavy to-[#1a4a82] uppercase tracking-wide drop-shadow-sm">DÒNG XE NỔI BẬT</h2>
        <p class="mt-3 text-center text-base text-slate-500">Khám phá những mẫu xe điện đang được quan tâm nhất</p>
        <div class="mt-8 grid gap-5 md:grid-cols-3">
            <?php if (empty($featured)): ?>
                <p class="col-span-3 rounded-lg border border-slate-200 p-4 text-sm text-slate-500">Chưa có dữ liệu sản phẩm.</p>
            <?php else: ?>
                <?php foreach (array_slice($featured, 0, 3) as $p): ?>
                    <article class="group overflow-hidden rounded-2xl bg-white shadow-md ring-1 ring-slate-100 transition-all duration-300 hover:-translate-y-2 hover:shadow-xl">
                        <a href="<?= BASE_URL ?>products/detail/<?= (int)($p['id'] ?? 0) ?>" class="block">
                            <div class="aspect-[16/10] bg-slate-100 overflow-hidden">
                                <img src="<?= htmlspecialchars(ProductViewHelper::thumbUrl((array)$p)) ?>" alt="<?= htmlspecialchars((string)($p['name'] ?? '')) ?>" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110">
                            </div>
                            <div class="p-5">
                                <h3 class="text-lg font-bold text-vfNavy transition-colors group-hover:text-[#1a4a82]"><?= htmlspecialchars((string)($p['name'] ?? '')) ?></h3>
                                <p class="mt-2 text-sm text-slate-500 flex items-center gap-2">
                                    <i class="fa-solid fa-route text-[#FFB81C]"></i> Quãng đường: <span class="font-semibold text-slate-700"><?= htmlspecialchars(vf_product_range((array)$p)) ?></span>
                                </p>
                                <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4">
                                    <span class="text-base font-extrabold text-vfNavy"><?= htmlspecialchars(vf_product_price((array)$p)) ?></span>
                                    <span class="rounded-full bg-gradient-to-r from-vfNavy to-[#1a4a82] px-4 py-1.5 text-xs font-bold text-white shadow-md transition-transform group-hover:scale-105">XEM XE</span>
                                </div>
                            </div>
                        </a>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- PRODUCT SPOTLIGHT -->
<section class="bg-[#0a2342] py-12 text-white">
    <div class="mx-auto grid max-w-6xl gap-6 px-4 lg:grid-cols-12 lg:items-center">
        <div class="lg:col-span-7">
            <div class="overflow-hidden rounded-xl border border-white/10 bg-white">
                <img src="<?= htmlspecialchars(ProductViewHelper::thumbUrl((array)($leadProduct ?? []))) ?>" alt="Lead vehicle" class="h-full w-full object-cover">
            </div>
        </div>
        <div class="lg:col-span-5 flex flex-col justify-center">
            <div class="inline-flex items-center gap-2">
                <span class="h-1 w-8 bg-[#FFB81C] rounded-full"></span>
                <p class="text-sm font-bold uppercase tracking-widest text-[#FFB81C]">DÒNG XE NỔI BẬT</p>
            </div>
            <h3 class="mt-4 text-4xl font-extrabold text-white drop-shadow-md"><?= htmlspecialchars((string)(($leadProduct['name'] ?? 'VinFast VF Series'))) ?></h3>
            <div class="mt-6 space-y-4 text-base text-white/90">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-[#FFB81C]"><i class="fa-solid fa-battery-full"></i></div>
                    <p>Quãng đường: <span class="font-bold text-white"><?= htmlspecialchars(vf_product_range((array)($leadProduct ?? []))) ?></span></p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-[#FFB81C]"><i class="fa-solid fa-tag"></i></div>
                    <p>Giá từ: <span class="font-bold text-white"><?= htmlspecialchars(vf_product_price((array)($leadProduct ?? []))) ?></span></p>
                </div>
            </div>
            <div class="mt-8">
                <a href="<?= BASE_URL ?>products" class="inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-r from-[#FFB81C] to-[#f59e0b] px-8 py-3.5 text-sm font-bold text-vfNavy hover:text-white shadow-[0_4px_15px_rgba(255,184,28,0.4)] transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_6px_20px_rgba(255,184,28,0.6)]">
                    XEM CHI TIẾT <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- TAI SAO CHON VINFAST -->
<section class="bg-[#f8fafc] py-12">
    <div class="mx-auto max-w-6xl px-4">
        <h2 class="text-center text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-vfNavy to-[#1a4a82] uppercase tracking-wide drop-shadow-sm">TẠI SAO CHỌN XE ĐIỆN VINFAST</h2>
        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="group rounded-2xl bg-white p-6 text-center shadow-sm ring-1 ring-slate-100 transition-all duration-300 hover:-translate-y-2 hover:shadow-xl">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-[#f8fafc] text-2xl text-vfNavy transition-colors group-hover:bg-[#FFB81C] group-hover:text-white">
                    <i class="fa-solid fa-bolt"></i>
                </div>
                <p class="mt-4 text-base font-bold text-vfNavy">Hiệu suất tối ưu</p>
                <p class="mt-2 text-sm text-slate-500">Vận hành mạnh mẽ, êm ái và không tiếng ồn.</p>
            </div>
            <div class="group rounded-2xl bg-white p-6 text-center shadow-sm ring-1 ring-slate-100 transition-all duration-300 hover:-translate-y-2 hover:shadow-xl">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-[#f8fafc] text-2xl text-vfNavy transition-colors group-hover:bg-[#FFB81C] group-hover:text-white">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <p class="mt-4 text-base font-bold text-vfNavy">An toàn vượt trội</p>
                <p class="mt-2 text-sm text-slate-500">Đạt chuẩn an toàn quốc tế, công nghệ hỗ trợ lái tiên tiến.</p>
            </div>
            <div class="group rounded-2xl bg-white p-6 text-center shadow-sm ring-1 ring-slate-100 transition-all duration-300 hover:-translate-y-2 hover:shadow-xl">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-[#f8fafc] text-2xl text-vfNavy transition-colors group-hover:bg-[#FFB81C] group-hover:text-white">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                </div>
                <p class="mt-4 text-base font-bold text-vfNavy">Dịch vụ toàn diện</p>
                <p class="mt-2 text-sm text-slate-500">Bảo hành 10 năm, xưởng dịch vụ phủ khắp toàn quốc.</p>
            </div>
            <div class="group rounded-2xl bg-white p-6 text-center shadow-sm ring-1 ring-slate-100 transition-all duration-300 hover:-translate-y-2 hover:shadow-xl">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-[#f8fafc] text-2xl text-vfNavy transition-colors group-hover:bg-[#FFB81C] group-hover:text-white">
                    <i class="fa-solid fa-leaf"></i>
                </div>
                <p class="mt-4 text-base font-bold text-vfNavy">Hướng đến bền vững</p>
                <p class="mt-2 text-sm text-slate-500">Không phát thải, góp phần kiến tạo tương lai xanh.</p>
            </div>
        </div>
    </div>
</section>


<!-- HỆ SINH THÁI THÔNG MINH -->
<section class="bg-white py-16">
    <div class="mx-auto max-w-6xl px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-vfNavy to-[#1a4a82] uppercase tracking-wide drop-shadow-sm">Hệ Sinh Thái Thông Minh</h2>
            <p class="mt-4 text-slate-500 max-w-2xl mx-auto">Trải nghiệm vượt trên một phương tiện di chuyển với hệ sinh thái công nghệ, dịch vụ đẳng cấp thế giới được tích hợp hoàn hảo.</p>
        </div>
        <div class="grid gap-8 md:grid-cols-3">
            <div class="group relative overflow-hidden rounded-2xl bg-slate-50 p-8 transition-all duration-300 hover:-translate-y-2 hover:shadow-xl hover:bg-white ring-1 ring-slate-100">
                <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-vfNavy to-[#1a4a82] text-white shadow-lg transition-transform group-hover:scale-110 group-hover:-rotate-3">
                    <i class="fa-solid fa-mobile-screen-button text-2xl"></i>
                </div>
                <h3 class="mb-3 text-xl font-bold text-vfNavy">Ứng dụng VinFast</h3>
                <p class="text-sm text-slate-600 leading-relaxed">Điều khiển xe từ xa, theo dõi tình trạng pin, định vị xe và quản lý lịch sử bảo dưỡng dễ dàng chỉ với một thao tác vuốt trên điện thoại di động.</p>
            </div>
            <div class="group relative overflow-hidden rounded-2xl bg-slate-50 p-8 transition-all duration-300 hover:-translate-y-2 hover:shadow-xl hover:bg-white ring-1 ring-slate-100">
                <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-vfNavy to-[#1a4a82] text-white shadow-lg transition-transform group-hover:scale-110 group-hover:rotate-3">
                    <i class="fa-solid fa-charging-station text-2xl"></i>
                </div>
                <h3 class="mb-3 text-xl font-bold text-vfNavy">Hạ tầng trạm sạc</h3>
                <p class="text-sm text-slate-600 leading-relaxed">Hệ thống trạm sạc công cộng phủ sóng khắp 63 tỉnh thành, dọc các tuyến quốc lộ và cao tốc, mang lại sự an tâm tuyệt đối trên mọi hành trình.</p>
            </div>
            <div class="group relative overflow-hidden rounded-2xl bg-slate-50 p-8 transition-all duration-300 hover:-translate-y-2 hover:shadow-xl hover:bg-white ring-1 ring-slate-100">
                <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-vfNavy to-[#1a4a82] text-white shadow-lg transition-transform group-hover:scale-110 group-hover:-rotate-3">
                    <i class="fa-solid fa-robot text-2xl"></i>
                </div>
                <h3 class="mb-3 text-xl font-bold text-vfNavy">Trợ lý ảo thông minh</h3>
                <p class="text-sm text-slate-600 leading-relaxed">Tương tác bằng giọng nói tiếng Việt đa vùng miền. Hỗ trợ điều hướng, gọi điện, giải trí và kiểm soát các tính năng xe một cách hoàn toàn rảnh tay.</p>
            </div>
        </div>
    </div>
</section>

<!-- BRAND STORY -->
<section class="bg-[#f8fafc] py-20 relative overflow-hidden">
    <!-- Decorative background element -->
    <div class="absolute -right-40 -top-40 h-96 w-96 rounded-full bg-gradient-to-br from-[#1a4a82]/5 to-vfNavy/5 blur-3xl pointer-events-none"></div>
    <div class="absolute -left-40 -bottom-40 h-96 w-96 rounded-full bg-gradient-to-tr from-[#FFB81C]/5 to-transparent blur-3xl pointer-events-none"></div>

    <div class="mx-auto grid max-w-6xl gap-12 px-4 lg:grid-cols-12 lg:items-center relative z-10">
        <!-- Image Grid -->
        <div class="grid grid-cols-2 gap-4 lg:col-span-5">
            <div class="group relative overflow-hidden rounded-2xl shadow-sm transition-all duration-500 hover:-translate-y-2 hover:shadow-2xl hover:shadow-vfNavy/20">
                <img src="<?= htmlspecialchars($banners[0]) ?>" alt="Story 1" class="h-40 w-full object-cover transition-transform duration-700 group-hover:scale-110 sm:h-48">
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
            </div>
            <div class="group relative mt-8 overflow-hidden rounded-2xl shadow-sm transition-all duration-500 hover:-translate-y-2 hover:shadow-2xl hover:shadow-vfNavy/20">
                <img src="<?= htmlspecialchars($banners[1]) ?>" alt="Story 2" class="h-40 w-full object-cover transition-transform duration-700 group-hover:scale-110 sm:h-48">
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
            </div>
            <div class="group relative col-span-2 overflow-hidden rounded-2xl shadow-sm transition-all duration-500 hover:-translate-y-2 hover:shadow-2xl hover:shadow-[#FFB81C]/20">
                <img src="<?= htmlspecialchars($banners[2]) ?>" alt="Story 3" class="h-48 w-full object-cover transition-transform duration-700 group-hover:scale-110 sm:h-64">
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
            </div>
        </div>

        <!-- Content -->
        <div class="lg:col-span-7 lg:pl-8">
            <div class="inline-flex items-center gap-3 rounded-full bg-white px-4 py-1.5 shadow-sm border border-slate-100 mb-6">
                <span class="flex h-2 w-2 rounded-full bg-[#FFB81C] animate-pulse"></span>
                <span class="text-[11px] font-bold uppercase tracking-widest text-vfNavy">CÂU CHUYỆN VINFAST</span>
            </div>

            <h3 class="text-3xl font-extrabold text-vfNavy sm:text-4xl leading-tight">
                Tiên phong vì một <br> <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#1a4a82] to-vfNavy">Việt Nam xanh hơn</span> mỗi ngày
            </h3>

            <p class="mt-6 text-base leading-relaxed text-slate-600">
                <?= nl2br(htmlspecialchars(!empty($settings['about_text']) ? $settings['about_text'] : 'Không chỉ là phương tiện di chuyển, VinFast mang đến một lối sống mới. Chúng tôi cam kết phát triển hệ sinh thái xe điện toàn diện, kiến tạo chuẩn mực đẳng cấp toàn cầu và đóng góp trực tiếp vào mục tiêu giảm phát thải, bảo vệ môi trường sống.')) ?>
            </p>

            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                <div class="flex items-start gap-4 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-100 transition hover:shadow-md">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#f8fafc] text-vfNavy">
                        <i class="fa-solid fa-earth-americas text-lg"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-vfNavy">Tầm nhìn toàn cầu</h4>
                        <p class="mt-1 text-xs text-slate-500 leading-snug">Vươn tầm quốc tế, khẳng định trí tuệ Việt.</p>
                    </div>
                </div>

                <div class="flex items-start gap-4 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-100 transition hover:shadow-md">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#f8fafc] text-vfNavy">
                        <i class="fa-solid fa-handshake-angle text-lg"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-vfNavy">Tận tâm phục vụ</h4>
                        <p class="mt-1 text-xs text-slate-500 leading-snug">Dịch vụ xuất sắc từ trái tim.</p>
                    </div>
                </div>
            </div>

            <div class="mt-10">
                <a href="<?= BASE_URL ?>about" class="inline-flex items-center justify-center gap-2 rounded-full border-2 border-vfNavy bg-transparent px-8 py-3 text-sm font-bold text-vfNavy transition-all duration-300 hover:bg-vfNavy hover:text-white hover:shadow-lg">
                    TÌM HIỂU THÊM VỀ CHÚNG TÔI <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- TESTIMONIAL ĐÃ ĐƯỢC XÓA THEO YÊU CẦU -->

<!-- TIN TUC -->
<section class="bg-[#f8fafc] py-12">
    <div class="mx-auto max-w-6xl px-4">
        <div class="mb-6 flex items-end justify-between">
            <h2 class="text-2xl font-bold text-vfNavy">TIN TỨC MỚI NHẤT</h2>
            <a href="<?= BASE_URL ?>news" class="text-sm font-semibold text-vfNavy hover:underline">Xem tất cả</a>
        </div>
        <div class="grid gap-5 md:grid-cols-3">
            <?php if (empty($latest)): ?>
                <p class="col-span-3 rounded-lg border border-slate-200 bg-white p-4 text-sm text-slate-500">Chưa có bài viết.</p>
            <?php else: ?>
                <?php foreach ($latest as $n): ?>
                    <article class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                        <a href="<?= BASE_URL ?>news/read/<?= htmlspecialchars((string)($n['slug'] ?? '')) ?>" class="block">
                            <img src="<?= htmlspecialchars(vf_news_thumb((array)$n)) ?>" alt="<?= htmlspecialchars((string)($n['title'] ?? '')) ?>" class="aspect-[16/10] w-full object-cover">
                            <div class="p-4">
                                <p class="text-[11px] uppercase text-slate-500"><?= htmlspecialchars((string)($n['created_at'] ?? '')) ?></p>
                                <h3 class="mt-2 line-clamp-2 text-sm font-semibold text-vfNavy"><?= htmlspecialchars((string)($n['title'] ?? '')) ?></h3>
                            </div>
                        </a>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>