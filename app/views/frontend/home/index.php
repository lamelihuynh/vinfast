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
$tagline = (string)($settings['tagline'] ?? 'Khám phá bộ sưu tập xe điện thông minh, sang trọng và hướng đến một tương lai bền vững cùng VinFast.');
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
                <div class="pointer-events-auto max-w-xl">
                    <p class="inline-flex rounded-full bg-[#FFB81C] px-3 py-1 text-xs font-semibold text-[#0b233f]">VINFAST</p>
                    <h1 class="mt-3 text-4xl font-bold leading-tight text-white sm:text-5xl">Kiến tạo<br>tương lai xanh</h1>
                    <p class="mt-3 text-sm text-white/80 sm:text-base"><?= htmlspecialchars($tagline) ?></p>
                    <div class="mt-6 flex gap-3">
                        <a href="<?= BASE_URL ?>products" class="rounded-md bg-[#FFB81C] px-5 py-2.5 text-sm font-semibold text-[#0b233f]">XEM NGAY</a>
                        <a href="<?= BASE_URL ?>contact" class="rounded-md border border-white/40 px-5 py-2.5 text-sm font-semibold text-white">TƯ VẤN</a>
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
                <p class="text-xl font-bold">14+</p>
                <p class="text-[11px] uppercase text-white/60">Quốc gia hiện diện</p>
            </div>

            <div class="py-4 ">
                <p class="text-xl font-bold">150,000+</p>
                <p class="text-[11px] uppercase text-white/60">Khách hàng tin dùng</p>
            </div>

            <div class="py-4 ">
                <p class="text-xl font-bold">8 mẫu</p>
                <p class="text-[11px] uppercase text-white/60">Xe hiện có</p>
            </div>

            <div class="py-4">
                <p class="text-xl font-bold">500+</p>
                <p class="text-[11px] uppercase text-white/60">Showroom toàn cầu</p>
            </div>

        </div>
    </div>
</section>

<!-- DONG XE NOI BAT -->
<section class="bg-white py-12">
    <div class="mx-auto max-w-6xl px-4">
        <h2 class="text-center text-2xl font-bold text-[#0b233f]">DÒNG XE NỔI BẬT</h2>
        <p class="mt-2 text-center text-sm text-slate-500">Khám phá những mẫu xe điện đang được quan tâm nhất</p>
        <div class="mt-8 grid gap-5 md:grid-cols-3">
            <?php if (empty($featured)): ?>
                <p class="col-span-3 rounded-lg border border-slate-200 p-4 text-sm text-slate-500">Chưa có dữ liệu sản phẩm.</p>
            <?php else: ?>
                <?php foreach (array_slice($featured, 0, 3) as $p): ?>
                    <article class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                        <a href="<?= BASE_URL ?>products/detail/<?= (int)($p['id'] ?? 0) ?>" class="block">
                            <div class="aspect-[16/10] bg-slate-100">
                                <img src="<?= htmlspecialchars(ProductViewHelper::thumbUrl((array)$p)) ?>" alt="<?= htmlspecialchars((string)($p['name'] ?? '')) ?>" class="h-full w-full object-cover">
                            </div>
                            <div class="p-4">
                                <h3 class="font-semibold text-[#0b233f]"><?= htmlspecialchars((string)($p['name'] ?? '')) ?></h3>
                                <p class="mt-1 text-xs text-slate-500">Quãng đường: <?= htmlspecialchars(vf_product_range((array)$p)) ?></p>
                                <div class="mt-3 flex items-center justify-between">
                                    <span class="text-sm font-bold text-[#0b233f]"><?= htmlspecialchars(vf_product_price((array)$p)) ?></span>
                                    <span class="rounded bg-[#0b233f] px-3 py-1 text-[11px] font-semibold text-white">XEM XE</span>
                                </div>
                            </div>
                        </a>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- TAI SAO CHON VINFAST -->
<section class="bg-[#f8fafc] py-12">
    <div class="mx-auto max-w-6xl px-4">
        <h2 class="text-center text-2xl font-bold text-[#0b233f]">TẠI SAO CHỌN XE ĐIỆN VINFAST</h2>
        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg border border-slate-200 bg-white p-4 text-center"><i class="fa-solid fa-bolt text-xl text-[#0b233f]"></i>
                <p class="mt-3 text-sm font-semibold">Hiệu suất tối ưu</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-4 text-center"><i class="fa-solid fa-shield-halved text-xl text-[#0b233f]"></i>
                <p class="mt-3 text-sm font-semibold">An toàn vượt trội</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-4 text-center"><i class="fa-solid fa-screwdriver-wrench text-xl text-[#0b233f]"></i>
                <p class="mt-3 text-sm font-semibold">Dịch vụ toàn diện</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-4 text-center"><i class="fa-solid fa-leaf text-xl text-[#0b233f]"></i>
                <p class="mt-3 text-sm font-semibold">Hướng đến bền vững</p>
            </div>
        </div>
    </div>
</section>

<!-- PRODUCT SPOTLIGHT -->
<section class="bg-[#0a2342] py-12 text-white">
    <div class="mx-auto grid max-w-6xl gap-6 px-4 lg:grid-cols-12 lg:items-center">
        <div class="lg:col-span-7">
            <div class="overflow-hidden rounded-xl border border-white/10">
                <img src="<?= htmlspecialchars(ProductViewHelper::thumbUrl((array)($leadProduct ?? []))) ?>" alt="Lead vehicle" class="h-full w-full object-cover">
            </div>
        </div>
        <div class="lg:col-span-5">
            <p class="text-xs uppercase tracking-widest text-[#FFB81C]">DÒNG XE NỔI BẬT</p>
            <h3 class="mt-2 text-2xl font-bold"><?= htmlspecialchars((string)(($leadProduct['name'] ?? 'VinFast VF Series'))) ?></h3>
            <div class="mt-4 space-y-2 text-sm text-white/80">
                <p>Quãng đường: <span class="font-semibold text-white"><?= htmlspecialchars(vf_product_range((array)($leadProduct ?? []))) ?></span></p>
                <p>Giá từ: <span class="font-semibold text-white"><?= htmlspecialchars(vf_product_price((array)($leadProduct ?? []))) ?></span></p>
            </div>
            <a href="<?= BASE_URL ?>products" class="mt-6 inline-flex rounded-md bg-[#FFB81C] px-4 py-2 text-sm font-semibold text-[#0b233f]">XEM CHI TIẾT</a>
        </div>
    </div>
</section>

<!-- SO SANH XE -->
<section class="bg-white py-12">
    <div class="mx-auto max-w-6xl px-4">
        <h2 class="text-center text-2xl font-bold text-[#0b233f]">CÙNG SO SÁNH CÁC XE VINFAST</h2>
        <div class="mt-8 overflow-x-auto rounded-lg border border-slate-200">
            <table class="w-full min-w-[680px] text-sm">
                <thead class="bg-[#0b233f] text-white">
                    <tr>
                        <th class="px-4 py-3 text-left">Thông số</th>
                        <th class="px-4 py-3 text-left"><?= htmlspecialchars((string)($compareA['name'] ?? 'Mẫu A')) ?></th>
                        <th class="px-4 py-3 text-left"><?= htmlspecialchars((string)($compareB['name'] ?? 'Mẫu B')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-t">
                        <td class="px-4 py-3 font-medium">Giá</td>
                        <td class="px-4 py-3"><?= htmlspecialchars(vf_product_price((array)($compareA ?? []))) ?></td>
                        <td class="px-4 py-3"><?= htmlspecialchars(vf_product_price((array)($compareB ?? []))) ?></td>
                    </tr>
                    <tr class="border-t bg-slate-50">
                        <td class="px-4 py-3 font-medium">Quãng đường</td>
                        <td class="px-4 py-3"><?= htmlspecialchars(vf_product_range((array)($compareA ?? []))) ?></td>
                        <td class="px-4 py-3"><?= htmlspecialchars(vf_product_range((array)($compareB ?? []))) ?></td>
                    </tr>
                    <tr class="border-t">
                        <td class="px-4 py-3 font-medium">Sạc nhanh</td>
                        <td class="px-4 py-3">24 phút (10-70%)</td>
                        <td class="px-4 py-3">30 phút (10-70%)</td>
                    </tr>
                    <tr class="border-t bg-slate-50">
                        <td class="px-4 py-3 font-medium">Bảo hành</td>
                        <td class="px-4 py-3">10 năm</td>
                        <td class="px-4 py-3">10 năm</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- BRAND STORY -->
<section class="bg-[#f8fafc] py-12">
    <div class="mx-auto grid max-w-6xl gap-8 px-4 lg:grid-cols-12">
        <div class="grid grid-cols-2 gap-3 lg:col-span-5">
            <img src="<?= htmlspecialchars($banners[0]) ?>" alt="Story 1" class="h-36 w-full rounded-lg object-cover sm:h-44">
            <img src="<?= htmlspecialchars($banners[1]) ?>" alt="Story 2" class="h-36 w-full rounded-lg object-cover sm:h-44">
            <img src="<?= htmlspecialchars($banners[2]) ?>" alt="Story 3" class="col-span-2 h-40 w-full rounded-lg object-cover sm:h-52">
        </div>
        <div class="lg:col-span-7">
            <p class="text-xs font-semibold uppercase tracking-widest text-[#0b233f]">VINFAST - TIÊN PHONG VÌ MỘT TƯƠNG LAI XANH</p>
            <h3 class="mt-2 text-2xl font-bold text-[#0b233f]">Vì một Việt Nam xanh hơn mỗi ngày</h3>
            <p class="mt-3 text-sm leading-7 text-slate-600">
                VinFast tập trung phát triển hệ sinh thái xe điện toàn diện, từ ô tô, xe máy đến hạ tầng sạc,
                giúp hành trình di chuyển trở nên thông minh, an toàn và thân thiện với môi trường.
            </p>
            <div class="mt-4 grid gap-2 text-sm text-slate-700 sm:grid-cols-2">
                <p><i class="fa-solid fa-check text-[#0b233f]"></i> Hệ thống trạm sạc phủ rộng</p>
                <p><i class="fa-solid fa-check text-[#0b233f]"></i> Công nghệ thông minh</p>
                <p><i class="fa-solid fa-check text-[#0b233f]"></i> Chính sách bảo hành dài hạn</p>
                <p><i class="fa-solid fa-check text-[#0b233f]"></i> Dịch vụ hậu mãi toàn quốc</p>
            </div>
        </div>
    </div>
</section>

<!-- TESTIMONIAL -->
<section class="bg-white py-12">
    <div class="mx-auto max-w-6xl px-4">
        <h2 class="text-center text-2xl font-bold text-[#0b233f]">KHÁCH HÀNG NÓI VỀ CHÚNG TÔI</h2>
        <div class="mt-8 grid gap-4 md:grid-cols-3">
            <article class="rounded-lg border border-slate-200 p-5">
                <p class="text-sm text-slate-600">“Xe vận hành êm, sạc nhanh và tiết kiệm hơn kỳ vọng.”</p>
                <p class="mt-4 text-sm font-semibold text-[#0b233f]">Nguyễn M.</p>
            </article>
            <article class="rounded-lg border border-slate-200 p-5">
                <p class="text-sm text-slate-600">“Dịch vụ hậu mãi tốt, đội ngũ tư vấn hỗ trợ rất nhanh.”</p>
                <p class="mt-4 text-sm font-semibold text-[#0b233f]">Trần H.</p>
            </article>
            <article class="rounded-lg border border-slate-200 p-5">
                <p class="text-sm text-slate-600">“Thiết kế đẹp, nhiều công nghệ an toàn cho gia đình.”</p>
                <p class="mt-4 text-sm font-semibold text-[#0b233f]">Lê K.</p>
            </article>
        </div>
    </div>
</section>

<!-- TIN TUC -->
<section class="bg-[#f8fafc] py-12">
    <div class="mx-auto max-w-6xl px-4">
        <div class="mb-6 flex items-end justify-between">
            <h2 class="text-2xl font-bold text-[#0b233f]">TIN TỨC MỚI NHẤT</h2>
            <a href="<?= BASE_URL ?>news" class="text-sm font-semibold text-[#0b233f] hover:underline">Xem tất cả</a>
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
                                <h3 class="mt-2 line-clamp-2 text-sm font-semibold text-[#0b233f]"><?= htmlspecialchars((string)($n['title'] ?? '')) ?></h3>
                            </div>
                        </a>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>