import { initCheckinsSlider } from '../core/slider.js';

/**
 * WordPress entry: boots Swiper on the slider markup rendered by
 * templates/checkins-slider.php. Coordination with the pagination
 * bundle happens via the window.jcpSliderAutoFetch / jcpSliderRefresh
 * globals since the two ship as separate scripts.
 */
(function () {
    const result = initCheckinsSlider(document, {
        onReachEnd: () => {
            if (typeof window.jcpSliderAutoFetch === 'function') {
                window.jcpSliderAutoFetch();
            }
        },
    });

    if (result) {
        // Exposed so pagination can refresh after appending new slides
        window.jcpSliderRefresh = result.refresh;
    }
})();
