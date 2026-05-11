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
                if (entry.target.classList.contains('letter-reveal')) {
                    revealLetters(entry.target);
                }
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.snap-section, .reveal-on-scroll, .fade-in-scale, .typewriter-text, .letter-reveal').forEach((el) => {
        revealObserver.observe(el);
    });

    // 4. Letter Reveal Helper (Word-safe: no mid-word line breaks)
    function revealLetters(element) {
        if (element.dataset.revealed) return;
        const text = element.textContent.trim();
        element.innerHTML = '';
        element.style.opacity = '1';
        
        const words = text.split(/\s+/);
        const allCharSpans = [];
        let charIdx = 0;

        words.forEach((word, wordIdx) => {
            // Word wrapper: keeps all letters of a word on the same line
            const wordSpan = document.createElement('span');
            wordSpan.style.display = 'inline-block';
            wordSpan.style.whiteSpace = 'nowrap';
            // Override CSS ".letter-reveal span { opacity:0 }" on the wrapper
            wordSpan.style.opacity = '1';
            wordSpan.style.transform = 'none';

            [...word].forEach((char) => {
                const span = document.createElement('span');
                span.innerText = char;
                span.style.display = 'inline-block';
                span.style.opacity = '0';
                span.style.transform = 'translateY(15px)';
                span.style.transition = 'all 0.5s cubic-bezier(0.22, 1, 0.36, 1)';
                span.style.transitionDelay = `${charIdx * 0.03}s`;
                wordSpan.appendChild(span);
                allCharSpans.push(span);
                charIdx++;
            });

            element.appendChild(wordSpan);

            // Normal space between words (browser can break lines here)
            if (wordIdx < words.length - 1) {
                element.appendChild(document.createTextNode(' '));
            }
        });

        // Trigger animations AFTER everything is in the DOM
        // Double-rAF ensures the browser paints opacity:0 first
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                allCharSpans.forEach(span => {
                    span.style.opacity = '1';
                    span.style.transform = 'translateY(0)';
                });
            });
        });

        element.dataset.revealed = 'true';
    }

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
