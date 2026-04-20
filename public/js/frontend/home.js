/**
 * public/js/frontend/home.js — Homepage JS
 * Owner: Tang Vu
 *
 * Purpose:
 * - Init Swiper hero banner
 *
 * Note:
 * - Swiper assets are injected by HomeController into $scripts (CDN)
 */
document.addEventListener("DOMContentLoaded", () => {
  const el = document.querySelector(".vfHomeHero");
  if (!el || typeof window.Swiper !== "function") return;

  // Hero slider
  // - loop + autoplay for a "premium" feel
  // - pagination bullets + prev/next buttons
  // - pause on hover for desktop users
  const swiper = new window.Swiper(el, {
    loop: true,
    speed: 650,
    autoplay: {
      delay: 4500,
      disableOnInteraction: false,
    },
    pagination: {
      el: ".vfHomeHeroPagination",
      clickable: true,
    },
    navigation: {
      nextEl: ".vfHomeHeroNext",
      prevEl: ".vfHomeHeroPrev",
    },
  });

  // Pause autoplay on hover (desktop)
  const stop = () => swiper.autoplay && swiper.autoplay.stop();
  const start = () => swiper.autoplay && swiper.autoplay.start();
  el.addEventListener("mouseenter", stop);
  el.addEventListener("mouseleave", start);
});

