import { initCheckinsMap } from '../core/map.js';

/**
 * WordPress entry: reads the config localized by wp_localize_script
 * (jobcaptureproMapData) and boots the map on the #jcp-map container
 * rendered by JobCaptureProTemplates::render_map().
 */
window.addEventListener("DOMContentLoaded", () => {
    const mapEl = document.getElementById("jcp-map");
    if (!mapEl || typeof jobcaptureproMapData === 'undefined') return;
    initCheckinsMap(mapEl, jobcaptureproMapData);
});
