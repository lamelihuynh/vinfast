/**
 * public/js/frontend/home.js — Homepage JS (Natural Storytelling Version)
 */
document.addEventListener('DOMContentLoaded', () => {
    const header = document.querySelector('#vfHeader');

    if (typeof Swiper === 'function') {
        // 1. Hero Slider
        new Swiper('.vfHomeHero', {
            loop: true,
            effect: 'fade',
            fadeEffect: { crossFade: true },
            autoplay: { delay: 6000, disableOnInteraction: false },
            pagination: { el: '.vfHomeHeroPagination', clickable: true },
            navigation: { nextEl: '.vfHomeHeroNext', prevEl: '.vfHomeHeroPrev' }
        });

        // 2. Featured Vehicles Slider (Auto-play + Navigation)
        new Swiper('.vfFeaturedSwiper', {
            slidesPerView: 1,
            spaceBetween: 25,
            loop: true,
            autoplay: { delay: 5000, disableOnInteraction: false },
            navigation: {
                nextEl: '.vfFeaturedNext',
                prevEl: '.vfFeaturedPrev',
            },
            pagination: { el: '.swiper-pagination', clickable: true },
            breakpoints: {
                640: { slidesPerView: 2 },
                1024: { slidesPerView: 3 }
            }
        });
    }

    // 3. reveal-on-scroll Observer
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.snap-section, .reveal-on-scroll, .fade-in-scale, .typewriter-text').forEach((el) => {
        revealObserver.observe(el);
    });

    // 5. Header Dynamic Background (on window scroll)
    window.addEventListener('scroll', () => {
        if (header) {
            if (window.scrollY > 50) {
                header.classList.add('bg-vfNavy/95', 'backdrop-blur-md', 'shadow-xl');
            } else {
                header.classList.remove('bg-vfNavy/95', 'backdrop-blur-md', 'shadow-xl');
            }
        }
    });
});
