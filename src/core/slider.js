import Swiper from 'swiper';
import { Navigation, Autoplay } from 'swiper/modules';
import { initCardCarousels, initDescriptionToggles } from './card-interactions.js';

/**
 * Initialize the check-ins Swiper slider within a root element.
 * Returns { swiper, refresh } or null when no slider markup is present.
 */
export function initCheckinsSlider(root, { onReachEnd } = {}) {
    const sliderEl = root.querySelector('.jcp-plugin-slider .swiper');
    if (!sliderEl) return null;

    const sliderWrap = root.querySelector('.jcp-plugin-slider');
    const swiper = new Swiper(sliderEl, {
        modules: [Navigation, Autoplay],
        slidesPerView: 1,
        spaceBetween: 24,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
            pauseOnMouseEnter: true,
        },
        breakpoints: {
            640: { slidesPerView: 2 },
            1280: { slidesPerView: 3 },
        },
        navigation: {
            nextEl: sliderWrap.querySelector('.jcp-plugin-slider__btn--next'),
            prevEl: sliderWrap.querySelector('.jcp-plugin-slider__btn--prev'),
        },
        on: {
            reachEnd: () => {
                if (typeof onReachEnd === 'function') onReachEnd();
            },
        },
    });

    // Recount cards, recalculate widths, init new carousels/toggles
    const refresh = () => {
        swiper.update();
        initCardCarousels(root);
        initDescriptionToggles(root);
    };

    initCardCarousels(root);
    initDescriptionToggles(root);

    return { swiper, refresh };
}
