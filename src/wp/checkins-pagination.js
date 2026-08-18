import { createCheckinsPagination } from '../core/pagination.js';

/**
 * WordPress entry: reads the config localized by wp_localize_script
 * (jobcaptureproPaginationData) and wires infinite-scroll pagination
 * to the slider track rendered by templates/checkins-slider.php.
 */
document.addEventListener("DOMContentLoaded", () => {
    const track = document.getElementById("jcp-plugin-slider-track");
    if (!track || typeof jobcaptureproPaginationData === 'undefined') return;

    const pagination = createCheckinsPagination({
        track,
        companyId: jobcaptureproPaginationData.companyId,
        baseApiUrl: jobcaptureproPaginationData.baseApiUrl,
        apiKey: jobcaptureproPaginationData.apiKey,
        scAtts: jobcaptureproPaginationData.scAtts || {},
        onAppended: () => {
            if (window.jcpSliderRefresh) {
                window.jcpSliderRefresh();
            }
        },
    });

    // Exposed so the slider bundle can trigger auto-fetch when near the end
    window.jcpSliderAutoFetch = () => pagination.autoFetch();
});
