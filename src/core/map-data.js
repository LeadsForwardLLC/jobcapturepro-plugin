/**
 * Client-side port of the map data preparation done in PHP by
 * JobCaptureProTemplates::render_map() / determine_bounds(), so the
 * embed can build the same map config the WP plugin localizes.
 */

// Mirrors JobCaptureProTemplates::determine_bounds()
export function determineBounds(features) {
    let minLat = 0, maxLat = 0, minLng = 0, maxLng = 0;

    if (features.length > 0) {
        minLat = maxLat = features[0].geometry.coordinates[1];
        minLng = maxLng = features[0].geometry.coordinates[0];

        features.forEach((feature) => {
            const lat = feature.geometry.coordinates[1];
            const lng = feature.geometry.coordinates[0];
            minLat = Math.min(minLat, lat);
            maxLat = Math.max(maxLat, lat);
            minLng = Math.min(minLng, lng);
            maxLng = Math.max(maxLng, lng);
        });

        // Add padding (approximately 1km)
        const padding = 0.01;
        minLat -= padding;
        maxLat += padding;
        minLng -= padding;
        maxLng += padding;
    }

    return { minLat, maxLat, minLng, maxLng };
}

// Container markup matching the PHP render_map() output (minus the
// page-unique id, which only the WP template needs).
export function renderMapContainer() {
    return '<div class="jcp-map jcp:h-[500px] jcp:max-[480px]:h-[400px] jcp:w-full jcp:rounded-xl jcp:overflow-hidden jcp:mx-auto"></div>';
}

/**
 * Build the config object consumed by initCheckinsMap() from a raw
 * /map API response — the same shape the WP plugin passes via
 * wp_localize_script as jobcaptureproMapData.
 */
export function buildMapConfig(mapData, companyInfo, { baseApiUrl, apiKey, scAtts = {} }) {
    const features = mapData.locations?.features ?? [];
    const { minLat, maxLat, minLng, maxLng } = determineBounds(features);

    const markersData = [];
    features.forEach((feature) => {
        const checkinId = feature.properties?.checkinId;
        if (!checkinId) return;
        markersData.push({
            position: {
                lat: feature.geometry.coordinates[1],
                lng: feature.geometry.coordinates[0],
            },
            id: checkinId,
        });
    });

    return {
        googleMapsApiKey: mapData.googleMapsApiKey?.value ?? '',
        baseApiUrl,
        apiKey,
        companyInfo,
        centerLat: (minLat + maxLat) / 2,
        centerLng: (minLng + maxLng) / 2,
        minLat,
        minLng,
        maxLat,
        maxLng,
        markersData,
        hasNext: mapData.hasNext ?? false,
        totalPages: mapData.totalPages ?? 1,
        pageSize: mapData.pageSize ?? 100,
        companyId: companyInfo?.id ?? null,
        scAtts,
    };
}
