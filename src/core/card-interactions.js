/**
 * Card-level interactivity: image carousels and "Read more" toggles.
 * All functions are scoped to a root element so multiple widget
 * instances (or the WP page-level document) can coexist.
 */

export function bindCarousel(gallery) {
    if (gallery.dataset.carouselBound) return;
    gallery.dataset.carouselBound = '1';

    const slides = gallery.querySelectorAll('.jcp-plugin-card__slide');
    const prevBtn = gallery.querySelector('.jcp-plugin-card__nav--prev');
    const nextBtn = gallery.querySelector('.jcp-plugin-card__nav--next');
    const dots = gallery.querySelectorAll('.jcp-plugin-card__dot');
    const total = slides.length;
    let active = 0;

    function setActive(i) {
        if (i < 0) i = total - 1;
        if (i >= total) i = 0;
        active = i;
        slides.forEach((slide, idx) => {
            if (idx === active) {
                slide.classList.add('jcp:opacity-100', 'jcp:visible');
                slide.classList.remove('jcp:opacity-0', 'jcp:invisible');
            } else {
                slide.classList.add('jcp:opacity-0', 'jcp:invisible');
                slide.classList.remove('jcp:opacity-100', 'jcp:visible');
            }
        });
        dots.forEach((dot, idx) => {
            if (idx === active) {
                dot.classList.add('jcp:bg-white', 'jcp:scale-[1.2]');
                dot.classList.remove('jcp:bg-white/60');
            } else {
                dot.classList.add('jcp:bg-white/60');
                dot.classList.remove('jcp:bg-white', 'jcp:scale-[1.2]');
            }
        });
    }

    if (prevBtn) prevBtn.addEventListener('click', () => setActive(active - 1));
    if (nextBtn) nextBtn.addEventListener('click', () => setActive(active + 1));
    dots.forEach((dot, idx) => dot.addEventListener('click', () => setActive(idx)));
}

export function initCardCarousels(root) {
    root.querySelectorAll('.jcp-plugin-card__gallery[data-carousel]').forEach(bindCarousel);
}

export function initDescriptionToggles(root) {
    root.querySelectorAll('.jcp-plugin-card').forEach((card) => {
        const text = card.querySelector('[data-desc-text]');
        const toggle = card.querySelector('[data-desc-toggle]');
        if (!text || !toggle) return;
        if (!toggle.dataset.bound) {
            toggle.addEventListener('click', () => {
                const expanded = text.classList.contains('jcp:line-clamp-none');
                if (expanded) {
                    text.classList.remove('jcp:line-clamp-none', 'jcp:overflow-visible');
                    text.classList.add('jcp:line-clamp-4', 'jcp:overflow-hidden');
                    toggle.setAttribute('aria-expanded', 'false');
                    toggle.textContent = 'Read more';
                } else {
                    text.classList.add('jcp:line-clamp-none', 'jcp:overflow-visible');
                    text.classList.remove('jcp:line-clamp-4', 'jcp:overflow-hidden');
                    toggle.setAttribute('aria-expanded', 'true');
                    toggle.textContent = 'Show less';
                }
            });
            toggle.dataset.bound = '1';
        }
        if (text.classList.contains('jcp:line-clamp-none')) {
            toggle.hidden = false;
            return;
        }
        requestAnimationFrame(() => {
            toggle.hidden = text.scrollHeight <= text.clientHeight + 2;
        });
    });
}
