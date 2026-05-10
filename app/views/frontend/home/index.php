<?php
/**
 * app/views/frontend/home/index.php
 * Final Refinements:
 * - Flush header (no gap)
 * - Full car image in spotlight (object-contain)
 * - Improved icons for marquee
 */

// -------- Hero assets --------
$tagline = !empty($settings['tagline']) ? (string)$settings['tagline'] : "Kiến tạo\ntương lai xanh";
$subTagline = !empty($settings['sub_tagline']) ? (string)$settings['sub_tagline'] : 'Khám phá bộ sưu tập xe điện thông minh, sang trọng và hướng đến một tương lai bền vững cùng VinFast.';
$banners = [
    SiteSetting::imageUrl($settings['banner_1'] ?? '', 'public/images/banners/banner_background.png'),
    SiteSetting::imageUrl($settings['banner_2'] ?? '', 'public/images/banners/banner_02.png'),
    SiteSetting::imageUrl($settings['banner_3'] ?? '', 'public/images/banners/banner_03.png'),
];

if (!function_exists('vf_news_thumb')) {
    function vf_news_thumb(array $news): string
    {
        $thumb = trim((string)($news['thumbnail'] ?? ''));
        if ($thumb === '') return BASE_URL . 'public/images/banners/banner_background.png';
        if (preg_match('~^https?://~i', $thumb)) return $thumb;
        $thumb = str_replace('\\', '/', $thumb);
        return BASE_URL . ltrim($thumb, '/');
    }
}

if (!function_exists('vf_product_price')) {
    function vf_product_price(array $p): string
    {
        return number_format((float)($p['price'] ?? 0), 0, ',', '.') . ' VNĐ';
    }
}

if (!function_exists('vf_product_range')) {
    function vf_product_range(array $p): string
    {
        $km = Product::extractRangeKm((array)($p['specs'] ?? []));
        return $km > 0 ? (string)$km . ' km' : '--';
    }
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>public/css/frontend/homepage_effects.css">

<style>
    /* Force header to be flush against the top */
    #vfHeader { top: 0 !important; margin: 0 !important; }
    #vfHeaderSpacer { display: none !important; }
    
    .vfHomeHero .swiper-pagination-bullet { width: 24px; height: 3px; border-radius: 999px; background: rgba(255, 255, 255, .3); transition: all 0.3s; }
    .vfHomeHero .swiper-pagination-bullet-active { width: 40px; background: #FFB81C; }
    
    main + footer { display: block !important; }
    
    .text-refined-sm { font-size: 0.875rem; line-height: 1.5; }
    .text-refined-xs { font-size: 0.75rem; }

    /* Spotlight image container to prevent cropping */
    .spotlight-img-container {
        background: radial-gradient(circle at center, rgba(255,184,28,0.05) 0%, transparent 70%);
    }
</style>

<div class="snap-container">
    <!-- HERO -->
    <section class="snap-section !p-0 relative bg-slate-900 min-h-screen">
        <div class="swiper vfHomeHero h-full w-full">
            <div class="swiper-wrapper h-full">
                <?php foreach ($banners as $src): ?>
                    <div class="swiper-slide">
                        <div class="relative h-screen w-full">
                            <img src="<?= htmlspecialchars($src) ?>" alt="VinFast banner" class="h-full w-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-r from-vfNavy/80 via-vfNavy/20 to-transparent"></div>
                            <div class="absolute inset-0 bg-gradient-to-t from-vfNavy/40 to-transparent"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="pointer-events-none absolute inset-0 z-10">
                <div class="mx-auto flex h-full max-w-6xl items-center px-4">
                    <div class="pointer-events-auto max-w-xl">
                        <p class="reveal-on-scroll inline-flex items-center gap-2 rounded-full bg-[#FFB81C] px-3 py-1 text-[9px] font-black text-vfNavy uppercase tracking-[0.2em]">
                            <span class="h-1 w-1 rounded-full bg-vfNavy animate-pulse"></span> VINFAST GLOBAL
                        </p>
                        <h1 class="reveal-on-scroll reveal-delay-1 mt-4 text-4xl font-black leading-[1.1] text-white sm:text-6xl lg:text-7xl">
                            <?= str_replace("\n", " ", $tagline) ?>
                        </h1>
                        <p class="reveal-on-scroll reveal-delay-2 mt-6 max-w-md text-base leading-relaxed text-white sm:text-lg break-words font-medium">
                            <?= nl2br(htmlspecialchars($subTagline)) ?>
                        </p>
                        <div class="reveal-on-scroll reveal-delay-3 mt-10 flex flex-wrap gap-4">
                            <a href="<?= BASE_URL ?>products" class="group relative overflow-hidden rounded-full bg-[#FFB81C] px-8 py-3.5 text-xs font-black text-vfNavy hover:bg-white transition-all duration-500 shadow-xl">
                                <span class="relative z-10 uppercase tracking-widest">Khám phá</span>
                            </a>
                            <a href="<?= BASE_URL ?>contact?tab=test-drive" class="inline-flex items-center justify-center rounded-full border-2 border-white/20 bg-white/5 backdrop-blur-md px-8 py-3.5 text-xs font-black text-white hover:bg-white hover:text-vfNavy transition-all duration-500">
                                <i class="fa-solid fa-car-side mr-2"></i> LÁI THỬ
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="absolute inset-x-0 bottom-16 z-20">
                <div class="mx-auto flex max-w-7xl items-center justify-between px-6">
                    <div class="vfHomeHeroPagination ml-4"></div>
                    <div class="flex gap-4 mr-4">
                        <button class="vfHomeHeroPrev inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/20 bg-white/5 backdrop-blur-md text-white hover:bg-[#FFB81C] hover:text-vfNavy transition-all"><i class="fa-solid fa-chevron-left"></i></button>
                        <button class="vfHomeHeroNext inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/20 bg-white/5 backdrop-blur-md text-white hover:bg-[#FFB81C] hover:text-vfNavy transition-all"><i class="fa-solid fa-chevron-right"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Strip -->
    <div class="relative z-30 -mt-12 mx-auto max-w-4xl px-4">
        <div class="grid grid-cols-2 sm:grid-cols-4 rounded-2xl bg-white p-6 shadow-xl border border-slate-100">
            <div class="text-center reveal-on-scroll px-2">
                <p class="text-2xl font-black text-vfNavy"><?= htmlspecialchars((string)($settings['stat1_val'] ?? '14+')) ?></p>
                <p class="mt-0.5 text-[9px] font-bold uppercase tracking-widest text-slate-400"><?= htmlspecialchars((string)($settings['stat1_lbl'] ?? 'Quốc gia')) ?></p>
            </div>
            <div class="text-center reveal-on-scroll reveal-delay-1 border-l border-slate-100 px-2">
                <p class="text-2xl font-black text-vfNavy"><?= htmlspecialchars((string)($settings['stat2_val'] ?? '150k+')) ?></p>
                <p class="mt-0.5 text-[9px] font-bold uppercase tracking-widest text-slate-400"><?= htmlspecialchars((string)($settings['stat2_lbl'] ?? 'Khách hàng')) ?></p>
            </div>
            <div class="text-center reveal-on-scroll reveal-delay-2 border-l border-slate-100 px-2">
                <p class="text-2xl font-black text-vfNavy"><?= htmlspecialchars((string)($settings['stat3_val'] ?? '8 mẫu')) ?></p>
                <p class="mt-0.5 text-[9px] font-bold uppercase tracking-widest text-slate-400"><?= htmlspecialchars((string)($settings['stat3_lbl'] ?? 'Xe hiện có')) ?></p>
            </div>
            <div class="text-center reveal-on-scroll reveal-delay-3 border-l border-slate-100 px-2">
                <p class="text-2xl font-black text-vfNavy"><?= htmlspecialchars((string)($settings['stat4_val'] ?? '500+')) ?></p>
                <p class="mt-0.5 text-[9px] font-bold uppercase tracking-widest text-slate-400"><?= htmlspecialchars((string)($settings['stat4_lbl'] ?? 'Showroom')) ?></p>
            </div>
        </div>
    </div>

    <!-- DANH SÁCH XE -->
    <section class="snap-section bg-white py-20">
        <div class="mx-auto max-w-6xl px-4">
            <div class="flex flex-col md:flex-row items-center justify-between mb-10 gap-4">
                <div class="text-center md:text-left">
                    <h2 class="reveal-on-scroll text-3xl font-black text-vfNavy uppercase tracking-tight typewriter-text">BỘ SƯU TẬP XE ĐIỆN</h2>
                    <p class="reveal-on-scroll reveal-delay-1 mt-2 text-slate-400 text-sm">Tinh hoa công nghệ Việt vươn tầm thế giới</p>
                </div>
                <div class="flex gap-2">
                    <button class="vfFeaturedPrev h-10 w-10 rounded-full border border-slate-200 flex items-center justify-center hover:bg-vfNavy hover:text-white transition-all"><i class="fa-solid fa-arrow-left text-xs"></i></button>
                    <button class="vfFeaturedNext h-10 w-10 rounded-full border border-slate-200 flex items-center justify-center hover:bg-vfNavy hover:text-white transition-all"><i class="fa-solid fa-arrow-right text-xs"></i></button>
                </div>
            </div>
            
            <div class="swiper vfFeaturedSwiper overflow-visible">
                <div class="swiper-wrapper">
                    <?php if (!empty($featured)): ?>
                        <?php foreach ($featured as $index => $p): ?>
                            <div class="swiper-slide">
                                <article class="vehicle-card-hover group relative overflow-hidden rounded-[2rem] bg-white p-3 shadow-lg border-2 border-slate-100 transition-all duration-500 mx-1.5">
                                    <a href="<?= BASE_URL ?>products/detail/<?= (int)($p['id'] ?? 0) ?>" class="block">
                                        <div class="aspect-[16/11] bg-slate-50 overflow-hidden rounded-[1.5rem] relative">
                                            <img src="<?= htmlspecialchars(ProductViewHelper::thumbUrl((array)$p)) ?>" alt="<?= htmlspecialchars((string)($p['name'] ?? '')) ?>" class="h-full w-full object-cover transition-transform duration-1000 group-hover:scale-110">
                                        </div>
                                        <div class="p-5">
                                            <h3 class="text-xl font-black text-vfNavy group-hover:text-[#1a4a82] transition-colors"><?= htmlspecialchars((string)($p['name'] ?? '')) ?></h3>
                                            <div class="mt-3 flex items-center gap-4 text-[10px] font-bold text-slate-400">
                                                <span class="flex items-center gap-1.5"><i class="fa-solid fa-route text-[#FFB81C]"></i> <?= htmlspecialchars(vf_product_range((array)$p)) ?></span>
                                                <span class="flex items-center gap-1.5"><i class="fa-solid fa-bolt text-[#FFB81C]"></i> <?= htmlspecialchars($p['type'] ?? 'Xe điện') ?></span>
                                            </div>
                                            <div class="mt-6 flex items-center justify-between border-t border-slate-50 pt-4">
                                                <div class="flex flex-col">
                                                    <span class="text-[9px] uppercase font-bold text-slate-300 tracking-wider">Giá niêm yết</span>
                                                    <span class="text-lg font-black text-vfNavy"><?= htmlspecialchars(vf_product_price((array)$p)) ?></span>
                                                </div>
                                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-vfNavy text-white shadow-md transition-all group-hover:bg-[#FFB81C] group-hover:text-vfNavy">
                                                    <i class="fa-solid fa-arrow-right text-xs"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </a>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- PRODUCT SPOTLIGHT (Full Image View) -->
    <section class="snap-section bg-vfNavy py-24 text-white overflow-hidden">
        <div class="mx-auto grid max-w-6xl gap-16 px-4 lg:grid-cols-2 lg:items-center">
            <!-- Left Side: Content -->
            <div class="reveal-on-scroll order-2 lg:order-1">
                <div class="inline-flex items-center gap-3 mb-6">
                    <span class="h-1 w-12 bg-[#FFB81C] rounded-full"></span>
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-[#FFB81C]">THE NEXT-GEN EV</p>
                </div>
                <h3 class="mt-4 text-5xl font-black text-white leading-tight reveal-on-scroll"><?= htmlspecialchars((string)(($leadProduct['name'] ?? 'VinFast VF 9'))) ?></h3>
                <p class="mt-6 text-base text-white/60 leading-relaxed max-w-md"><?= htmlspecialchars($leadProduct['short_desc'] ?? 'Đẳng cấp xe điện thông minh toàn cầu, kiến tạo hành trình di chuyển xanh.') ?></p>
                
                <div class="mt-10 grid grid-cols-2 gap-8">
                    <div class="reveal-on-scroll reveal-delay-2 flex flex-col gap-2 group">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/5 border border-white/10 text-[#FFB81C] mb-2"><i class="fa-solid fa-battery-full text-xl"></i></div>
                        <p class="text-[9px] text-white/40 uppercase font-black tracking-widest">Phạm vi tối đa</p>
                        <p class="font-black text-white text-2xl"><?= htmlspecialchars(vf_product_range((array)($leadProduct ?? []))) ?></p>
                    </div>
                    <div class="reveal-on-scroll reveal-delay-3 flex flex-col gap-2 group">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/5 border border-white/10 text-[#FFB81C] mb-2"><i class="fa-solid fa-tag text-xl"></i></div>
                        <p class="text-[9px] text-white/40 uppercase font-black tracking-widest">Giá niêm yết từ</p>
                        <p class="font-black text-white text-2xl"><?= htmlspecialchars(vf_product_price((array)($leadProduct ?? []))) ?></p>
                    </div>
                </div>

                <div class="mt-12 flex gap-4">
                    <div class="rounded-2xl bg-white/5 backdrop-blur-md p-4 border border-white/10 flex-1 text-center hover:bg-white/10 transition-colors">
                        <p class="text-[8px] uppercase text-white/40 font-black">Gia tốc</p>
                        <p class="text-lg font-black text-[#FFB81C]">4.8s</p>
                    </div>
                    <div class="rounded-2xl bg-white/5 backdrop-blur-md p-4 border border-white/10 flex-1 text-center hover:bg-white/10 transition-colors">
                        <p class="text-[8px] uppercase text-white/40 font-black">Công suất</p>
                        <p class="text-lg font-black text-[#FFB81C]">402hp</p>
                    </div>
                    <div class="rounded-2xl bg-white/5 backdrop-blur-md p-4 border border-white/10 flex-1 text-center hover:bg-white/10 transition-colors">
                        <p class="text-[8px] uppercase text-white/40 font-black">Chống nước</p>
                        <p class="text-lg font-black text-[#FFB81C]">IP67</p>
                    </div>
                </div>

                <div class="reveal-on-scroll reveal-delay-4 mt-12 flex gap-4">
                    <a href="<?= BASE_URL ?>products" class="px-8 py-4 rounded-full bg-[#FFB81C] text-vfNavy text-xs font-black uppercase tracking-widest hover:bg-white transition-all shadow-lg">XEM CHI TIẾT</a>
                    <a href="<?= BASE_URL ?>contact?tab=test-drive" class="px-8 py-4 rounded-full border-2 border-white/20 text-white text-xs font-black uppercase tracking-widest hover:bg-white/10 transition-all">LÁI THỬ</a>
                </div>
            </div>

            <!-- Right Side: Full Car Image (using object-contain to prevent cutting) -->
            <div class="reveal-on-scroll order-1 lg:order-2 flex justify-center">
                <div class="spotlight-img-container relative w-full aspect-[4/3] rounded-[3rem] overflow-hidden group">
                    <img src="<?= htmlspecialchars(ProductViewHelper::thumbUrl((array)($leadProduct ?? []))) ?>" alt="Full vehicle" class="h-full w-full object-contain transition-transform duration-1000 group-hover:scale-110">
                </div>
            </div>
        </div>
    </section>

    <!-- BRAND STORY (Company Intro) -->
    <section class="snap-section bg-white py-28 relative overflow-hidden">
        <div class="mx-auto max-w-6xl px-4 lg:grid lg:grid-cols-2 lg:gap-20 lg:items-center">
            <div class="reveal-on-scroll relative mb-12 lg:mb-0">
                <div class="aspect-square rounded-[3rem] overflow-hidden shadow-2xl border-4 border-slate-50">
                    <img src="<?= htmlspecialchars($banners[0]) ?>" class="h-full w-full object-cover fade-in-scale">
                </div>
                <div class="absolute -bottom-8 -right-8 w-60 aspect-[4/3] rounded-[2rem] overflow-hidden border-8 border-white shadow-2xl hidden sm:block">
                    <img src="<?= htmlspecialchars($banners[1]) ?>" class="h-full w-full object-cover">
                </div>
            </div>
            
            <div class="reveal-on-scroll">
                <div class="inline-flex items-center gap-3 mb-6">
                    <div class="h-1 w-8 bg-[#FFB81C] rounded-full"></div>
                    <span class="text-[10px] font-black uppercase tracking-[0.3em] text-vfNavy">HÀNH TRÌNH TIÊN PHONG</span>
                </div>
                
                <h3 class="text-4xl font-black text-vfNavy leading-tight mb-8 reveal-on-scroll" style="font-family: 'Lora', serif;">
                    VinFast - Dẫn Đầu Kỷ Nguyên Xe Điện
                </h3>
                
                <div class="space-y-6 text-base text-slate-500 leading-relaxed">
                    <p class="font-bold text-vfNavy/80">
                        VinFast là công ty thành viên thuộc tập đoàn Vingroup, một trong những Tập đoàn Kinh tế tư nhân đa ngành lớn nhất Châu Á.
                    </p>
                    <p>
                        Với triết lý <span class="text-vfNavy font-black italic">“Đặt khách hàng làm trọng tâm”</span>, VinFast không ngừng sáng tạo để tạo ra các sản phẩm đẳng cấp và xuất sắc cho mọi người.
                    </p>
                    <p>
                        Chúng tôi kiến tạo tương lai di chuyển thông minh và bền vững, đưa thương hiệu Việt vươn tầm quốc tế.
                    </p>
                </div>
                
                <div class="mt-12">
                    <a href="<?= BASE_URL ?>about" class="fade-in-scale inline-flex items-center gap-4 text-vfNavy font-black uppercase tracking-widest group text-xs">
                        TÌM HIỂU THÊM 
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-vfNavy text-white transition-all group-hover:translate-x-2 group-hover:bg-[#FFB81C] group-hover:text-vfNavy">
                            <i class="fa-solid fa-arrow-right"></i>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- TẬN HƯỞNG GIÁ TRỊ VƯỢT TRỘI -->
    <section class="snap-section bg-[#f8fafc] py-20 overflow-hidden">
        <div class="mx-auto max-w-6xl px-4 mb-12 text-center">
            <h2 class="reveal-on-scroll text-3xl font-black text-vfNavy uppercase tracking-tight typewriter-text">GIÁ TRỊ VƯỢT TRỘI</h2>
            <p class="reveal-on-scroll reveal-delay-1 mt-4 text-slate-400 text-sm max-w-xl mx-auto">Hệ sinh thái thông minh đồng hành cùng bạn trên mọi nẻo đường</p>
        </div>

        <div class="marquee-container py-8">
            <div class="marquee-content">
                <?php 
                $marquee_items = [
                    ['icon' => 'award', 'title' => 'An toàn 5 sao', 'desc' => 'Đạt chuẩn an toàn quốc tế ASEAN NCAP & EURO NCAP.'],
                    ['icon' => 'leaf', 'title' => 'Tương lai xanh', 'desc' => 'Kiến tạo môi trường di chuyển không phát thải.'],
                    ['icon' => 'shield-heart', 'title' => 'Bảo hành 10 năm', 'desc' => 'Cam kết chất lượng dài hạn nhất thị trường Việt.'],
                    ['icon' => 'mobile-screen-button', 'title' => 'VinFast App', 'desc' => 'Điều khiển xe và đặt lịch sạc ngay trên điện thoại.'],
                    ['icon' => 'charging-station', 'title' => 'Trạm sạc 63 tỉnh', 'desc' => 'Hệ thống trạm sạc lớn nhất phủ sóng toàn quốc.'],
                    ['icon' => 'robot', 'title' => 'Trợ lý ảo Vivi', 'desc' => 'Giao tiếp thông minh, hỗ trợ rảnh tay tuyệt đối.'],
                    ['icon' => 'truck-fast', 'title' => 'Cứu hộ 24/7', 'desc' => 'Dịch vụ cứu hộ và sửa chữa lưu động nhanh chóng.'],
                    ['icon' => 'bolt', 'title' => 'Hiệu suất cao', 'desc' => 'Vận hành mạnh mẽ từ động cơ điện thế hệ mới.'],
                ];
                $all_items = array_merge($marquee_items, $marquee_items);
                foreach ($all_items as $index => $item): ?>
                <div class="marquee-item group">
                    <div class="marquee-card rounded-[2.5rem] bg-white p-8 text-center shadow-md border-2 border-slate-100 group-hover:border-[#FFB81C] transition-all duration-500 group-hover:-translate-y-4">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-50 text-xl text-vfNavy group-hover:bg-[#FFB81C] transition-all">
                            <i class="fa-solid fa-<?= $item['icon'] ?>"></i>
                        </div>
                        <h4 class="mt-6 text-lg font-black text-vfNavy group-hover:text-vfNavy transition-colors uppercase tracking-tight"><?= $item['title'] ?></h4>
                        <p class="mt-4 text-base text-slate-500 leading-relaxed font-medium"><?= $item['desc'] ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- TIN TỨC MỚI NHẤT -->
    <section class="snap-section bg-white py-20">
        <div class="mx-auto max-w-6xl px-4">
            <div class="reveal-on-scroll mb-12 flex items-end justify-between">
                <div>
                    <h2 class="text-3xl font-black text-vfNavy typewriter-text uppercase tracking-tight">TIN TỨC MỚI NHẤT</h2>
                    <p class="text-slate-400 text-sm mt-1">Cập nhật hơi thở công nghệ từ VinFast</p>
                </div>
                <a href="<?= BASE_URL ?>news" class="group flex items-center gap-2 text-[10px] font-black text-vfNavy hover:text-[#FFB81C] transition-colors border-b border-vfNavy/10 pb-1">
                    XEM TẤT CẢ <i class="fa-solid fa-arrow-right text-[8px] group-hover:translate-x-1 transition-transform"></i>
                </a>
            </div>
            
            <div class="grid gap-8 md:grid-cols-3">
                <?php if (!empty($latest)): ?>
                    <?php foreach (array_slice($latest, 0, 3) as $index => $n): ?>
                        <article class="vehicle-card-hover reveal-on-scroll reveal-delay-<?= $index ?> group overflow-hidden rounded-[2.5rem] bg-white shadow-lg border border-slate-100 transition-all duration-700">
                            <a href="<?= BASE_URL ?>news/read/<?= htmlspecialchars((string)($n['slug'] ?? '')) ?>" class="block">
                                <div class="overflow-hidden aspect-[16/10] relative">
                                    <img src="<?= htmlspecialchars(vf_news_thumb((array)$n)) ?>" alt="<?= htmlspecialchars((string)($n['title'] ?? '')) ?>" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110">
                                </div>
                                <div class="p-7">
                                    <p class="text-[9px] font-black text-[#FFB81C] uppercase tracking-[0.2em] mb-3">Tin tức &bull; <?= date('d/m/Y', strtotime($n['created_at'] ?? 'now')) ?></p>
                                    <h3 class="line-clamp-2 text-lg font-black text-vfNavy group-hover:text-[#FFB81C] transition-colors leading-snug h-14 overflow-hidden"><?= htmlspecialchars((string)($n['title'] ?? '')) ?></h3>
                                    <div class="mt-6 flex items-center gap-2 text-[10px] font-bold text-slate-300">
                                        Xem chi tiết <i class="fa-solid fa-arrow-right-long transition-transform group-hover:translate-x-2"></i>
                                    </div>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>
<!-- Scripts are loaded via HomeController -->