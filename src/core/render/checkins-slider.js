import { lucideSvg, ChevronLeft, ChevronRight } from '../icons.js';

/**
 * Slider shell markup; mirrors templates/checkins-slider.php.
 * The WP template carries ids (jcp-plugin-slider-track etc.) for backwards
 * compatibility; this client-rendered version relies on classes only, which
 * is what the scoped slider/pagination modules select on.
 */
export function renderCheckinsSlider(cardsHtml) {
    return `
    <section class="jcp-plugin-slider-section jcp:pb-4 jcp:mt-6 jcp:font-['Inter','SF_Pro_Text',-apple-system,BlinkMacSystemFont,'Segoe_UI',sans-serif]" aria-label="Project check-ins">
        <div class="jcp-plugin-slider jcp:relative jcp:px-10 jcp:md:px-14">

            <div class="swiper">
                <div class="swiper-wrapper">
                    ${cardsHtml}
                </div>
            </div>

            <button type="button" class="jcp-plugin-slider__btn jcp-plugin-slider__btn--prev jcp:left-0" aria-label="Previous check-ins">
                ${lucideSvg(ChevronLeft, 18, 'jcp:block jcp:md:hidden')}
                ${lucideSvg(ChevronLeft, 24, 'jcp:hidden jcp:md:block')}
            </button>

            <button type="button" class="jcp-plugin-slider__btn jcp-plugin-slider__btn--next jcp:right-0" aria-label="Next check-ins">
                ${lucideSvg(ChevronRight, 18, 'jcp:block jcp:md:hidden')}
                ${lucideSvg(ChevronRight, 24, 'jcp:hidden jcp:md:block')}
            </button>

        </div>
    </section>`;
}
