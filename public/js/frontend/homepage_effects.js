document.addEventListener('DOMContentLoaded', () => {
    const snapContainer = document.querySelector('.snap-container');
    const sections = document.querySelectorAll('.snap-section');
    const header = document.querySelector('#vfHeader');
    
    // 1. Initialize Hero Swiper
    if (typeof Swiper !== 'undefined') {
        new Swiper('.vfHomeHero', {
            loop: true,
            autoplay: { delay: 5000, disableOnInteraction: false },
            pagination: { el: '.vfHomeHeroPagination', clickable: true },
            navigation: { nextEl: '.vfHomeHeroNext', prevEl: '.vfHomeHeroPrev' }
        });

        // Initialize Featured Swiper (NEW)
        new Swiper('.vfFeaturedSwiper', {
            slidesPerView: 1,
            spaceBetween: 20,
            loop: true,
            pagination: { el: '.swiper-pagination', clickable: true },
            breakpoints: {
                640: { slidesPerView: 2 },
                1024: { slidesPerView: 3 }
            }
        });
    }

    // 2. Letter Reveal Logic
    function revealLetters(element) {
        if (element.dataset.revealed) return;
        const text = element.textContent.trim();
        element.innerHTML = '';
        element.style.opacity = '1';
        
        [...text].forEach((char, i) => {
            const span = document.createElement('span');
            span.innerText = char === ' ' ? '\u00A0' : char;
            span.style.display = 'inline-block';
            span.style.opacity = '0';
            span.style.transform = 'translateY(10px)';
            span.style.transition = 'all 0.4s cubic-bezier(0.22, 1, 0.36, 1)';
            span.style.transitionDelay = `${i * 0.03}s`;
            element.appendChild(span);
            
            requestAnimationFrame(() => {
                span.style.opacity = '1';
                span.style.transform = 'translateY(0)';
            });
        });
        element.dataset.revealed = 'true';
    }

    // 3. Scroll Reveal Observer
    const observerOptions = {
        threshold: 0.2
    };

    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                if (entry.target.classList.contains('letter-reveal')) {
                    revealLetters(entry.target);
                }
            }
        });
    }, observerOptions);

    document.querySelectorAll('.reveal-on-scroll, .fade-in-scale, .typewriter-text, .letter-reveal').forEach(el => {
        revealObserver.observe(el);
    });

    // 4. Snap Scrolling Navigation Dots
    const navContainer = document.querySelector('.snap-nav');
    if (navContainer && sections.length > 0) {
        sections.forEach((_, i) => {
            const dot = document.createElement('div');
            dot.classList.add('snap-dot');
            if (i === 0) dot.classList.add('active');
            dot.addEventListener('click', () => {
                sections[i].scrollIntoView({ behavior: 'smooth' });
            });
            navContainer.appendChild(dot);
        });

        if (snapContainer) {
            snapContainer.addEventListener('scroll', () => {
                const scrollPos = snapContainer.scrollTop + (window.innerHeight / 2);
                sections.forEach((section, i) => {
                    if (scrollPos >= section.offsetTop && scrollPos < (section.offsetTop + section.offsetHeight)) {
                        document.querySelectorAll('.snap-dot').forEach(d => d.classList.remove('active'));
                        document.querySelectorAll('.snap-dot')[i].classList.add('active');
                    }
                });

                // Header Transparency
                if (header) {
                    if (snapContainer.scrollTop > 50) {
                        header.classList.add('bg-vfNavy/90', 'backdrop-blur-md', 'shadow-lg');
                    } else {
                        header.classList.remove('bg-vfNavy/90', 'backdrop-blur-md', 'shadow-lg');
                    }
                }
            });
        }
    }

    // 5. Scroll to top on logo click
    const logo = document.querySelector('#vfHeader img');
    if (logo && document.body.classList.contains('is-homepage-snap')) {
        logo.style.cursor = 'pointer';
        logo.addEventListener('click', (e) => {
            if (snapContainer) {
                e.preventDefault();
                snapContainer.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    }
});
