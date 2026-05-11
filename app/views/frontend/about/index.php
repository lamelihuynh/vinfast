<?php
/**
 * app/views/frontend/about/index.php
 * Owner  : Nhat Linh (Member 2)
 * Title  : About VinFast
 *
 * Purpose: Official VinFast About page - matches vinfastauto.com/vn_vi/ve-chung-toi
 */

$heroPath = '/public/images/uploads/about-page/hero_image.jpg';
$version_heroPath = filemtime($_SERVER['DOCUMENT_ROOT'] . $heroPath); 
$total = count($timeline ?? []);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>

<style>
  /* ─── DESKTOP: Horizontal accordion (1024px+) ─────────────────────── */
  @media (min-width: 1024px) {
    .accordion {
    padding-top: 0;
      display: flex;
      height: 560px;
      gap: 5px;
    }
    .card {
      flex: 1;
      display: flex;
      overflow: hidden;
      transition: flex 0.6s cubic-bezier(0.4, 0, 0.2, 1);
      cursor: pointer;  
        flex-direction: row;

    }
    .accordion:has(.card:hover) .card { flex: 0.85; }
    .card:hover                        { flex: 2.8 !important; }

    .card__img-wrap {
      position: relative;
      flex: 1;
      min-width: 0;
      overflow: hidden;
      height: 100%;

    }
    .card__img-wrap img {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    /* Chữ dọc khi thu nhỏ */
    .card__label-v {
      display: block;
      position: absolute;
      left: 50%;
      top: 50%;
      transform: translateX(-50%) ;
      white-space: nowrap;
      color: #fff;
      font-size: 15px;
      letter-spacing: 0.2em;
      text-transform: uppercase;
      font-family: 'Lora', serif;
      font-weight: 500;
      text-shadow: 0 1px 6px rgba(0,0,0,0.7);
      transition: opacity 0.25s ease;
      pointer-events: none;
    }
    .card:hover .card__label-v { opacity: 0; }

    /* Panel text bên phải */
    .card__text {
      width: 0;
      flex-shrink: 0;
      overflow: hidden;
      background: #ffffff;
      transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .card:hover .card__text { width: 360px; }

    .card__text-inner {
      width: 360px;
      height: 100%;
      padding: 36px 28px;
      display: flex;
      flex-direction: column;
      overflow-y: auto;
      scrollbar-width: thin;
      scrollbar-color: #888 transparent;
    }
    .card__text-inner::-webkit-scrollbar { width: 6px; }
    .card__text-inner::-webkit-scrollbar-thumb {
      background: #bbb;
      border-radius: 10px;
    }
    .card__text-inner::-webkit-scrollbar-thumb:hover {
      background: #888; 
    }


    .card__text-body {
      opacity: 0;
      transform: translateX(-10px);
      transition: opacity 0.35s ease 0.3s, transform 0.35s ease 0.3s;
    }
    .card:hover .card__text-body { opacity: 1; transform: translateX(0); }

    /* Ẩn các layout khác */
    .card__mobile-content { display: none; }
  }

  /* ─── TABLET: 2×2 grid (640px – 1023px) ───────────────────────────── */
  @media (min-width: 640px) and (max-width: 1023px) {
    .accordion { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }

    .card { display: flex; flex-direction: column; overflow: hidden; cursor: pointer; }

    .card__img-wrap {
      position: relative;
      width: 100%;
      height: 280px;
      overflow: hidden;
    }
    .card__img-wrap img {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.5s ease;
    }
    .card:hover .card__img-wrap img { transform: scale(1.03); }

    .card__label-v { display: none; }

    /* Text luôn hiển thị dưới ảnh */
    .card__text { display: none; }
    .card__mobile-content {
      background: #111;
      padding: 20px 22px 24px;
      flex: 1;
    }
    .card__text-body { opacity: 1; transform: none; }
  }

  /* ─── MOBILE: Vertical stack (<640px) ─────────────────────────────── */
  @media (max-width: 639px) {
    .accordion { display: flex; flex-direction: column; gap: 4px; }

    .card { display: flex; flex-direction: column; overflow: hidden; cursor: pointer; }

    .card__img-wrap {
      position: relative;
      width: 100%;
      height: 240px;
      overflow: hidden;
    }
    .card__img-wrap img {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.5s ease;
    }
    .card:hover .card__img-wrap img { transform: scale(1.03); }

    .card__label-v { display: none; }
    .card__text { display: none; }

    .card__mobile-content {
      background: #111;
      padding: 18px 20px 22px;
    }
    .card__text-body { opacity: 1; transform: none; }
  }

  /* Gradient overlay chung */
  .img-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.45), transparent);
  }


/** Time line*/


    /* ── Background & section ── */
  .timeline-section {
    background-color: #d4c9a8;
    padding: 72px 0 80px;
    overflow: hidden;
  }
 
  /* ── Carousel track ── */
  .tl-viewport {
    overflow: hidden;
    position: relative;
  }
  .tl-track {
    display: flex;
    transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    will-change: transform;
    align-items: center; /* vertical center so side cards look right */
  }
 
  /* ── Slide ── */
  .tl-slide {
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 28px;
    transition: opacity 0.5s ease, transform 0.5s ease;
    opacity: 0.35;
    transform: scale(0.88);
    pointer-events: none;
    user-select: none;
  }
  .tl-slide.active {
    opacity: 1;
    transform: scale(1);
    pointer-events: auto;
  }
 
 /* ────────────────────────────────────────────────────────────
     PHOTO COMPOSITION  (Desktop base: ≥ 1024px)
 
     DUAL layout:
       Wrap  : 420 × 490px
       Main  : 355 × 450px — top-right, 4px black border
       Sec   : 175 × 218px — overlaps main at bottom-left, z on top
 
     SINGLE layout:
       Wrap  : 420 × 490px
       Main  : 320 × 430px — centred, 4px black border
  ──────────────────────────────────────────────────────────── */
  .tl-photo-wrap {
    position: relative;
    width: 420px;
    height: 490px;
    flex-shrink: 0;
  }

    /* ── dual: main sits top-right ── */
  .tl-photo-wrap.dual .tl-photo-main {
    position: absolute;
    top: 30px; right: 0;
    width: 355px;
    height: 450px;
    border: 4px solid #1a1a1a;
    overflow: hidden;
    z-index: 1;
  }

  /* ── dual: secondary overlaps bottom-left of main ── */
  .tl-photo-wrap.dual .tl-photo-secondary {
    position: absolute;
    /* aligns so its right edge is ~120px into main's left side */
    left: 0;
    top: 200px;           /* vertically centre-ish */
    width: 175px;
    height: 218px;
    overflow: hidden;
    z-index: 2;           /* on top of main */
  }
 
  .tl-photo-main img {
    width: 100%; height: 100%;
    object-fit: cover;
    display: block;
  }
 /* ── single: one image centred ── */
  .tl-photo-wrap.single .tl-photo-main {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    width: 550px;
    height: 420px;
    border: 4px solid #1a1a1a;
    overflow: hidden;
    z-index: 1;
  }
 
  .tl-photo-main img,
  .tl-photo-secondary img {
    width: 100%; height: 100%;
    object-fit: cover;
    display: block;
  }
 
  /* ── Text below (active only) ── */
  .tl-text {
    text-align: center;
    max-width: 360px;
    opacity: 0;
    transform: translateY(10px);
    transition: opacity 0.4s ease 0.2s, transform 0.4s ease 0.2s;
  }
  .tl-slide.active .tl-text {
    opacity: 1;
    transform: translateY(0);
  }
 
  /* ── Nav arrows ── */
  .tl-btn {
    width: 37px; height: 37px;
    border-radius: 50%;
    background: #fff;
    border: 1px solid rgba(0,0,0,0.15);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: background 0.2s ease, box-shadow 0.2s ease;
  }
  .tl-btn:hover { background: #f5f0e8; box-shadow: 0 2px 8px rgba(0,0,0,0.12); }
  .tl-btn:disabled { opacity: 0.35; cursor: default; }

  /* ── RESPONSIVE ── */
 
  /* Tablet: 640–1023px */
  @media (min-width: 640px) and (max-width: 1023px) {
    .tl-photo-wrap                       { width: 340px; height: 400px; }
    .tl-photo-wrap.dual .tl-photo-main   { top: 24px; width: 285px; height: 365px; }
    .tl-photo-wrap.dual .tl-photo-secondary { left: 0; top: 164px; width: 140px; height: 175px; }
    .tl-photo-wrap.single .tl-photo-main { width: 258px; height: 350px; }
    .tl-slide { gap: 20px; }
    .tl-text  { max-width: 300px; }
  }
 
  /* Mobile: <640px */
  @media (max-width: 639px) {
    .tl-photo-wrap                       { width: 260px; height: 310px; }
    .tl-photo-wrap.dual .tl-photo-main   { top: 18px; width: 216px; height: 278px; }
    .tl-photo-wrap.dual .tl-photo-secondary { left: 0; top: 124px; width: 108px; height: 135px; }
    .tl-photo-wrap.single .tl-photo-main { width: 200px; height: 270px; }
    .tl-slide { gap: 16px; }
    .tl-text  { max-width: 240px; font-size: 13px; }
  }


  /* ── Section wrapper ─────────────────────────────── */
  .aw-section {
    background: #f5f1e8;
    padding: 52px 0 0;
    overflow: hidden;
  }
 
  /* ── Header row ──────────────────────────────────── */
  .aw-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    padding: 0 32px 32px;
  }
 
  /* ── Nav buttons ─────────────────────────────────── */
  .aw-nav {
    display: flex;
    gap: 8px;
    flex-shrink: 0;
  }
  .aw-btn {
    width: 37px; height: 37px;
    border-radius: 50%;
    background: #fff;
    border: 1px solid rgba(0,0,0,.15);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    transition: background .2s, box-shadow .2s;
    flex-shrink: 0;
  }
  .aw-btn:hover  { background: #ede9df; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
  .aw-btn:disabled { opacity: .3; cursor: default; }
 
  /* ── Track + viewport ────────────────────────────── */
  .aw-viewport {
    overflow: hidden;
    border-top: 1px solid rgba(0,0,0,.12);
  }
  .aw-track {
    display: flex;
    transition: transform .55s cubic-bezier(.4,0,.2,1);
    will-change: transform;
  }
 
  /* ── Award card ──────────────────────────────────── */
  .aw-card {
    /* width set by JS */
    flex-shrink: 0;
    min-height: 164px;
    border-right: 1px solid rgba(0,0,0,.12);
    position: relative;
    overflow: hidden;
    cursor: default;
  }
  .aw-card:last-child { border-right: none; }
 
  /* Logo face (default visible) */
  .aw-face-logo,
  .aw-face-text {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 24px;
    transition: opacity .45s ease;
  }
  .aw-face-logo  { opacity: 1; }
  .aw-face-text  { opacity: 0; background: #eee8d5; }
 
  .aw-card:hover .aw-face-logo { opacity: 0; }
  .aw-card:hover .aw-face-text { opacity: 1; }
 
  /* Logo image — grayscale, contained */
  .aw-logo-img {
    width: 100%;
    height: 90px;
    object-fit: contain;
    filter: grayscale(1);
    display: block;
  }
 
  /* Text face */
  .aw-award-name {
    font-family: 'DM Sans', sans-serif;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .06em;
    color: #1a1a1a;
    line-height: 1.45;
    margin-bottom: 10px;
  }
  .aw-award-desc {
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    font-weight: 300;
    color: #444;
    line-height: 1.65;
  }
 
  /* Touch devices: always show text, no hover trick */
  @media (hover: none) {
    .aw-face-logo  { opacity: 0; }
    .aw-face-text  { opacity: 1; }
  }
</style>

 



<!-- <nav class="bg-white py-3 border-bottom" style="border-color: #e5e7eb;">
    <div class="mx-auto" style="max-width: 1200px; padding: 0 20px;">
        <div style="font-size: 14px; color: #666;">
            <a href="<?= BASE_URL ?>" style="color: #0066cc; text-decoration: none;">Trang chủ</a>
            <span style="margin: 0 8px;">/</span>
            <span>Hỏi & Đáp</span>
        </div>
    </div>
</nav> -->

<!-- ════════════════════════════════════════════════════════
     1. HERO SECTION 
════════════════════════════════════════════════════════ -->
</head>
<body>
<div id="aboutus" style="flex-grow: 1; z-index: 0;">
    <div class="relative z-0 h-full bg-[#fcf9f2]"> 
        
        <div class="w-full h-[60vh] md:h-[calc(100vh-80px)] max-h-[1000px] overflow-hidden relative">
            
            <div class="absolute inset-0 w-full h-full">
                <img 
                    alt="Hero Image" 
                    loading="lazy" 
                    class="absolute inset-0 w-full h-full object-cover object-center" 
                    src="<?= BASE_URL . $heroPath . '?v=' . time() ?>"
                    style="position: absolute; height: 100%; width: 100%; inset: 0px; object-fit: cover; object-position: center center; color: transparent;"
                >
                <div class="absolute inset-0 w-full h-full bg-black/30 pointer-events-none"></div>
            </div>

            <div class="absolute left-1/2 right-1/2 top-0 flex h-full w-full -translate-x-1/2 flex-col  items-center  justify-center px-6 text-center text-white sm:w-[800px]">
                <div class="font-medium text-[12px] md:text-[16px] uppercase text-white antialiased tracking-widest mb-1">
                    VỀ CHÚNG TÔI
                </div>
                <h2 class ="text-[24px] leading-[32x] md:text-[36px] md:leading-[42px] font-thin text-white text-center antialiased drop-shadow-[0_0_20px_rgba(255,255,255,0.8)]" style="font-family: 'Lora', serif; font-weight: 400;">
                    VinFast - Dẫn Đầu Kỷ Nguyên Xe Điện, Từ 2017
                </h2>
            </div>

        </div>

    </div>

    <section class="bg-[rgb(224, 211, 211)] pt-[48px]">
        <h3 class="text-[24px] leading-[32px] md:text-[36px] md:leading-[43px] px-[15%] pb-[60px] antialiased text-center sm:pb-[120px]"  style="font-family: 'Lora', serif; font-weight: 400;" >

        <span class="before:content-[''] before:float-left before:h-[42px] before:w-[15%]"></span>
        <span class="before:content-[''] before:float-right before:h-[42px] before:w-[15%]"></span>
        
        <?php echo $aboutText; ?>

        </h3>
        

        
    </section>
    
    <div class="bg-[rgb(224, 211, 211)] min-h-screen flex items-start pt-10">

        <div class="accordion w-full max-w-[1400px] mx-auto">

        <!-- ── Card 1 ── -->
        <div class="card">
            <div class="card__img-wrap">
            <img src="https://cdn.abercrombiekent.com/images/bsiop5ln/production/961cb889752cc9f3be22533c1a2463e33852dfd3-2048x2560.jpg?w=1440&q=75&fit=max&auto=format" alt="Travel Your Way">
            <div class="img-overlay"></div>
            <span class="card__label-v">Tầm Nhìn</span>
            </div>

            <!-- Desktop text panel -->
            <div class="card__text">
                <div class="card__text-inner">
                    <div class="card__text-body">
                    <p class="text-[10px] tracking-[0.22em] uppercase text-black/35 mb-3" style="font-family:'Helvetica Neue',Arial,sans-serif">Vision</p>
                    <h2 class="text-[22px] text-black font-light italic leading-snug mb-4" style="font-family:Georgia,serif">Tầm Nhìn</h2>
                    <div class="w-6 h-px bg-black/25 mb-4"></div>
                    <p class="text-[25px] leading-[1.8] text-black/60" style="font-family:'Lora',serif;font-weight:400">
                        Với khát vọng đưa thương hiệu Việt vươn tầm quốc tế, VinFast hướng tới tầm nhìn trở thành hãng xe điện thông minh hàng đầu thế giới, dẫn dắt cuộc cách mạng di chuyển xanh toàn cầu. Bằng việc lấy khách hàng làm trọng tâm và không ngừng sáng tạo, chúng tôi cam kết kiến tạo một hệ sinh thái xe điện đẳng cấp, tích hợp công nghệ trí tuệ nhân tạo tiên tiến, mang đến trải nghiệm an toàn, bền vững và thông minh cho mọi người trên hành trình hướng tới một tương lai xanh.
                    </p>

                    </div>
                </div>
            </div>

            <!-- Tablet & Mobile content -->
            <div class="card__mobile-content">
            <div class="card__text-body">
                <p class="text-[10px] tracking-[0.2em] uppercase text-white/35 mb-2" style="font-family:'Helvetica Neue',Arial,sans-serif">Philosophy</p>
                <h2 class="text-[19px] text-white font-light italic leading-snug mb-3" style="font-family:Georgia,serif">Travel Your Way</h2>
                <div class="w-5 h-px bg-white/25 mb-3"></div>
                <p class="text-[13px] leading-[1.75] text-white/55" style="font-family:'Helvetica Neue',Arial,sans-serif;font-weight:300">For six decades we have been refining the art of bespoke travel, showing every guest the world in a fresh light.</p>
            </div>
            </div>
        </div>

        <!-- ── Card 2 ── -->
        <div class="card">
            <div class="card__img-wrap">
            <img src="https://cdn.abercrombiekent.com/images/bsiop5ln/production/2f2c73cb34a30abbc46f15260e808c4f48f5cf86-2048x2560.jpg?w=1440&q=75&fit=max&auto=format" alt="With You All The Way">
            <div class="img-overlay"></div>
            <span class="card__label-v">Sứ Mệnh</span>
            </div>

            <div class="card__text">
            <div class="card__text-inner">
                <div class="card__text-body">
                <p class="text-[10px] tracking-[0.22em] uppercase text-black/35 mb-3" style="font-family:'Helvetica Neue',Arial,sans-serif">Mission</p>
                <h2 class="text-[22px] text-black font-light italic leading-snug mb-4" style="font-family:Georgia,serif">Sứ Mệnh</h2>
                <div class="w-6 h-px bg-black/25 mb-4"></div>
                <p class="text-[25px] leading-[1.8] text-black/60" style="font-family:'Lora',serif;font-weight:400">
                    Sứ mệnh của VinFast là 'Vì một cuộc sống tốt đẹp hơn cho mọi người'. chúng tôi không chỉ sản xuất những chiếc xe, mà còn tiên phong thúc đẩy lối sống bền vững thông qua các giải pháp di chuyển thông minh, an toàn và thân thiện với môi trường. Bằng tinh thần quyết liệt, tốc độ và cam kết không ngừng đổi mới, VinFast nỗ lực phá bỏ mọi rào cản để xe điện trở nên dễ tiếp cận hơn cho mọi khách hàng, từ đó chung tay kiến tạo một tương lai xanh và bền vững cho các thế hệ mai sau.
                </p>
                </div>
            </div>
            </div>

            <div class="card__mobile-content">
            <div class="card__text-body">
                <p class="text-[10px] tracking-[0.2em] uppercase text-white/35 mb-2" style="font-family:'Helvetica Neue',Arial,sans-serif">Support</p>
                <h2 class="text-[19px] text-white font-light italic leading-snug mb-3" style="font-family:Georgia,serif">Sứ</h2>
                <div class="w-5 h-px bg-white/25 mb-3"></div>
                <p class="text-[13px] leading-[1.75] text-white/55" style="font-family:'Helvetica Neue',Arial,sans-serif;font-weight:300">From the moment you begin planning to the day you return home, our team is by your side around the clock.</p>
            </div>
            </div>
        </div>

        <!-- ── Card 3 ── -->
        <div class="card">
            <div class="card__img-wrap">
            <img src="https://cdn.abercrombiekent.com/images/bsiop5ln/production/70932a47c5cc8f9c64b593cf498af5e5b2fa9537-2048x2560.jpg?w=1440&q=75&fit=max&auto=format" alt="Travel Thoughtfully">
            <div class="img-overlay"></div>
            <span class="card__label-v">Triết Lý Thương Hiệu</span>
            </div>

            <div class="card__text">
            <div class="card__text-inner">
                <div class="card__text-body">
                <p class="text-[10px] tracking-[0.22em] uppercase text-black/35 mb-3" style="font-family:'Helvetica Neue',Arial,sans-serif">Brand Philosophy</p>
                <h2 class="text-[22px] text-black font-light italic leading-snug mb-4" style="font-family:Georgia,serif">Triết Lý Thương Hiệu</h2>
                <div class="w-6 h-px bg-black/25 mb-4"></div>
                <p class="text-[25px] leading-[1.8] text-black/60" style="font-family:'Lora', serif;font-weight:400">
                    Triết lý thương hiệu của VinFast được xây dựng trên ba trụ cột chính: 'Đặt khách hàng làm trọng tâm', 'Đổi mới không ngừng' và 'Cam kết chất lượng đẳng cấp'. Chúng tôi tin rằng công nghệ tiên tiến nhất chỉ thực sự có giá trị khi nó phục vụ cuộc sống và mang lại lợi ích cho cộng đồng. Với VinFast, mỗi chiếc xe không chỉ là phương tiện di chuyển, mà là một không gian sống thông minh, nơi mọi giới hạn về trải nghiệm đều được phá bỏ để mang lại sự hài lòng tối đa và niềm tự hào cho chủ sở hữu.
                </p>
                </div>
            </div>
            </div>

            <div class="card__mobile-content">
            <div class="card__text-body">
                <p class="text-[10px] tracking-[0.2em] uppercase text-white/35 mb-2" style="font-family:'Helvetica Neue',Arial,sans-serif">Sustainability</p>
                <h2 class="text-[19px] text-white font-light italic leading-snug mb-3" style="font-family:Georgia,serif">Travel Thoughtfully</h2>
                <div class="w-5 h-px bg-white/25 mb-3"></div>
                <p class="text-[13px] leading-[1.75] text-white/55" style="font-family:'Helvetica Neue',Arial,sans-serif;font-weight:300">Travel done responsibly is a force for good — protecting wildlife, preserving culture, and empowering communities across 30+ countries.</p>
            </div>
            </div>
        </div>

        <!-- ── Card 4 ── -->
    </div> 


</div> 
<!-- ════════ TIMELINE SECTION ════════ -->
<section class="timeline-section">
 
  <!-- Title -->
  <h3 class="text-center text-[28px] md:text-[34px] font-normal text-[#2c2416] mb-10 md:mb-14 px-6"
      style="font-family:'Lora',serif;">
    Lịch sử hình thành và phát triển
  </h3>
 
  <!-- Carousel row: arrow + viewport + arrow -->
  <div class="flex items-center justify-center gap-4 md:gap-6 px-4">
 
    <!-- Prev -->
    <button class="tl-btn" id="tl-prev" aria-label="Previous" disabled>
      <svg width="12" height="12" viewBox="0 0 48 48" fill="none">
        <path d="M5.8 24H41.8" stroke="#000" stroke-width="4" stroke-linecap="round"/>
        <path d="M17.8 36L5.8 24L17.8 12" stroke="#000" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </button>
 
    <!-- Viewport -->
    <div class="tl-viewport flex-1 max-w-[1500px]" id="tl-viewport">
      <div class="tl-track" id="tl-track">
 
        <?php foreach ($timeline as $i => $item): ?>
        <div class="tl-slide <?= $i === 0 ? 'active' : '' ?>"
             data-index="<?= $i ?>">
 
          <!-- Photo composition -->
          <div class="tl-photo-wrap <?= $item['img_secondary'] ? 'dual' : 'single' ?>">
 
            <!-- Main image -->
            <div class="tl-photo-main">
              <img src="<?= htmlspecialchars($item['img_main']) ?>"
                   alt="<?= htmlspecialchars($item['year']) ?>"
                   loading="lazy">
            </div>
 
            <!-- Secondary image (optional) -->
            <?php if ($item['img_secondary']): ?>
            <div class="tl-photo-secondary">
              <img src="<?= htmlspecialchars($item['img_secondary']) ?>"
                   alt="<?= htmlspecialchars($item['year']) ?> secondary"
                   loading="lazy">
            </div>
            <?php endif; ?>
 
          </div>
 
          <!-- Text (year + description) -->
          <div class="tl-text">
            <p class="text-[20px] font-bold text-[#2c2416] mb-2 tracking-wide"
               style="font-family:'Lora',serif;">
              <?= htmlspecialchars($item['year']) ?>
            </p>
            <p class="text-[22px] leading-[1.75] text-[#4a3f2f]"
               style="font-family:'Helvetica Neue',Arial,sans-serif; font-weight:300;">
              <?= htmlspecialchars($item['description']) ?>
            </p>
          </div>
 
        </div>
        <?php endforeach; ?>
 
      </div><!-- /track -->
    </div><!-- /viewport -->
 
    <!-- Next -->
    <button class="tl-btn" id="tl-next" aria-label="Next">
      <svg width="12" height="12" viewBox="0 0 48 48" fill="none">
        <path d="M42 24H6" stroke="#000" stroke-width="4" stroke-linecap="round"/>
        <path d="M30 12L42 24L30 36" stroke="#000" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </button>
 
  </div><!-- /flex row -->
 
  <!-- Dot indicators -->
  <div class="flex justify-center gap-2 mt-8" id="tl-dots">
    <?php for ($i = 0; $i < $total; $i++): ?>
    <button
      class="tl-dot w-2 h-2 rounded-full transition-all duration-300 <?= $i === 0 ? 'bg-[#2c2416] w-5' : 'bg-[#2c2416]/30' ?>"
      data-dot="<?= $i ?>"
      aria-label="Go to slide <?= $i + 1 ?>">
    </button>
    <?php endfor; ?>
  </div>
 
</section>



<section class="aw-section" id="awards">
  <!-- Header -->
   <div class="aw-header" >
      <div>
        <p class="text-[11px] tracking-[.18em] uppercase text-black/50 mb-1" style="font-family: 'DM Sans, sans-serif'; font-weight: 600px;">
                Giải thưởng của chúng tôi
        </p>
        <h2 class="text-[24px] md:text-[30px] font-normal text-[#1a1a1a] leading-tight" style="font-family:'Lora',serif;">
                Được công nhận toàn cầu về chất lượng

        </h2>
      </div>

      <div class="aw-nav">
      <button class="aw-btn" id="aw-prev" aria-label="Trước" disabled>
        <svg width="12" height="12" viewBox="0 0 48 48" fill="none">
          <path d="M5.8 24H41.8" stroke="#000" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M17.8 36L5.8 24L17.8 12" stroke="#000" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>

      <button class="aw-btn" id="aw-next" aria-label="Tiếp">
        <svg width="12" height="12" viewBox="0 0 48 48" fill="none">
          <path d="M42 24H6" stroke="#000" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M30 12L42 24L30 36" stroke="#000" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>


      </div>
   </div>

  <!-- Carousel -->
  <div class="aw-viewport" id="aw-viewport">
    <div class="aw-track" id="aw-track">
 
      <?php foreach ($awards as $i => $aw): ?>
      <div class="aw-card">
 
        <!-- Logo face -->
        <div class="aw-face-logo">
          <img class="aw-logo-img"
               src="<?= BASE_URL . 'public/images/uploads/'.htmlspecialchars($aw['image_path']) ?>"
               alt="<?= htmlspecialchars($aw['title']) ?>"
               loading="lazy">
        </div>
 
        <!-- Text face (on hover) -->
        <div class="aw-face-text">

            <p class="aw-award-name">
              <?= (int)$aw['award_year'] ?>
            </p>

            <p class="aw-award-desc">
              <?= htmlspecialchars($aw['title']) ?>
            </p>

            <?php if (!empty($award['description'])): ?>
              <p class="aw-award-desc">
                <?= nl2br(htmlspecialchars($award['description'])) ?>
              </p>
            <?php endif; ?>

          </div>
 
      </div>
      <?php endforeach; ?>
 
    </div>
  </div>


</section>

</body>



<script>
(function () {
  const total    = <?= $total ?>;
  let   current  = 0;
 
  const track    = document.getElementById('tl-track');
  const viewport = document.getElementById('tl-viewport');
  const slides   = Array.from(track.querySelectorAll('.tl-slide'));
  const btnPrev  = document.getElementById('tl-prev');
  const btnNext  = document.getElementById('tl-next');
  const dots     = Array.from(document.querySelectorAll('.tl-dot'));
 
  /* ── Calculate slide width based on viewport ─────────────── */
  function slideWidth() {
    const vw = viewport.offsetWidth;
    if (vw >= 1024) return 545;
    if (vw >= 640)  return 420;
    return Math.min(vw * 0.82, 310);
  }
 
  function gap() { return window.innerWidth >= 640 ? 48 : 32; }
 
  /* ── Render: set widths, translate track, update states ──── */
  function render(animate = true) {
    const sw = slideWidth();
    const g  = gap();
 
    // Set each slide width + margin so (sw + g) step matches real layout
    slides.forEach(s => {
      s.style.width       = sw + 'px';
      s.style.marginRight = g  + 'px';
    });
 
    // Centre the active slide inside the viewport
    const vpW    = viewport.offsetWidth;
    const offset = (vpW - sw) / 2 - current * (sw + g);
    track.style.transition = animate
      ? 'transform 0.6s cubic-bezier(0.4, 0, 0.2, 1)'
      : 'none';
    track.style.transform  = `translateX(${offset}px)`;
 
    // Active classes
    slides.forEach((s, i) => {
      s.classList.toggle('active', i === current);
    });
 
    // Buttons
    btnPrev.disabled = current === 0;
    btnNext.disabled = current === total - 1;
 
    // Dots
    dots.forEach((d, i) => {
      d.classList.toggle('bg-[#2c2416]',    i === current);
      d.classList.toggle('w-5',             i === current);
      d.classList.toggle('bg-[#2c2416]/30', i !== current);
      d.classList.remove(...(i === current ? ['bg-[#2c2416]/30'] : ['bg-[#2c2416]', 'w-5']));
    });
  }
 
  /* ── Navigation ───────────────────────────────────────────── */
  btnPrev.addEventListener('click', () => { if (current > 0)         { current--; render(); } });
  btnNext.addEventListener('click', () => { if (current < total - 1) { current++; render(); } });
 
  dots.forEach(d => {
    d.addEventListener('click', () => {
      current = parseInt(d.dataset.dot);
      render();
    });
  });
 
  /* ── Touch swipe ──────────────────────────────────────────── */
  let touchStartX = 0;
  viewport.addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; }, { passive: true });
  viewport.addEventListener('touchend',   e => {
    const diff = touchStartX - e.changedTouches[0].clientX;
    if (Math.abs(diff) > 40) {
      if (diff > 0 && current < total - 1) { current++; render(); }
      if (diff < 0 && current > 0)         { current--; render(); }
    }
  });
 
  /* ── Keyboard ─────────────────────────────────────────────── */
  document.addEventListener('keydown', e => {
    if (e.key === 'ArrowRight' && current < total - 1) { current++; render(); }
    if (e.key === 'ArrowLeft'  && current > 0)         { current--; render(); }
  });
 
  /* ── Resize ───────────────────────────────────────────────── */
  window.addEventListener('resize', () => render(false));
 
  /* ── Init ─────────────────────────────────────────────────── */
  render(false);
})();


(function () {
  const track    = document.getElementById('aw-track');
  const viewport = document.getElementById('aw-viewport');
  const btnPrev  = document.getElementById('aw-prev');
  const btnNext  = document.getElementById('aw-next');
  const cards    = Array.from(track.querySelectorAll('.aw-card'));
  const total    = cards.length;
  let   offset   = 0;   // how many cards we've slid
 
  /* ── How many cards fit based on viewport width ── */
  function visible() {
    const vw = viewport.offsetWidth;
    if (vw >= 1280) return 5;
    if (vw >= 1024) return 4;
    if (vw >= 768)  return 3;
    if (vw >= 500)  return 2;
    return 1;
  }
 
  /* ── Set card widths and apply translate ── */
  function render(animate) {
    const vis = visible();
    const cw  = viewport.offsetWidth / vis;
 
    cards.forEach(c => { c.style.width = cw + 'px'; });
 
    track.style.transition = animate
      ? 'transform .55s cubic-bezier(.4,0,.2,1)'
      : 'none';
    track.style.transform  = `translateX(${-offset * cw}px)`;
 
    const maxOffset = Math.max(0, total - vis);
    btnPrev.disabled = offset <= 0;
    btnNext.disabled = offset >= maxOffset;
  }
 
  /* ── Navigation ── */
  btnPrev.addEventListener('click', () => {
    if (offset > 0) { offset--; render(true); }
  });
  btnNext.addEventListener('click', () => {
    const maxOffset = Math.max(0, total - visible());
    if (offset < maxOffset) { offset++; render(true); }
  });
 
  /* ── Touch swipe ── */
  let touchStartX = 0;
  viewport.addEventListener('touchstart', e => {
    touchStartX = e.touches[0].clientX;
  }, { passive: true });
  viewport.addEventListener('touchend', e => {
    const diff = touchStartX - e.changedTouches[0].clientX;
    if (Math.abs(diff) > 40) {
      const maxOffset = Math.max(0, total - visible());
      if (diff > 0 && offset < maxOffset) { offset++; render(true); }
      if (diff < 0 && offset > 0)         { offset--; render(true); }
    }
  });
 
  /* ── Keyboard ── */
  document.addEventListener('keydown', e => {
    const maxOffset = Math.max(0, total - visible());
    if (e.key === 'ArrowRight' && offset < maxOffset) { offset++; render(true); }
    if (e.key === 'ArrowLeft'  && offset > 0)         { offset--; render(true); }
  });
 
  /* ── Resize ── */
  let resizeTimer;
  window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
      // Clamp offset after resize
      const maxOffset = Math.max(0, total - visible());
      if (offset > maxOffset) offset = maxOffset;
      render(false);
    }, 100);
  });
 
  /* ── Init ── */
  render(false);
})();
</script>
</html>

