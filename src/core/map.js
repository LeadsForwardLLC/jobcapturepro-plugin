import { MarkerClusterer } from "@googlemaps/markerclusterer";
import { setOptions, importLibrary } from "@googlemaps/js-api-loader";
import { renderInfoWindowCard, renderCardSkeleton } from './render/checkin-card.js';
import { bindCarousel } from './card-interactions.js';

function bindInfoWindowCarousels(infoWindow) {
    google.maps.event.addListenerOnce(infoWindow, 'domready', () => {
        // Google Maps puts info window content inside .gm-style-iw-d
        document.querySelectorAll('.gm-style-iw-d').forEach((container) => {
            container.querySelectorAll('.jcp-plugin-card__gallery[data-carousel]').forEach(bindCarousel);
        });
    });
}

/**
 * Initialize the Google Map with clustered check-in markers.
 * `config` matches the shape localized by WP as jobcaptureproMapData
 * (see also buildMapConfig() in map-data.js for the embed path).
 */
export async function initCheckinsMap(mapEl, config) {
    try {
        setOptions({
            key: config.googleMapsApiKey,
        });

        const fetchOptions = {
            headers: { 'Authorization': `Bearer ${config.apiKey}` }
        };

        // Request needed libraries.
        const { Map } = await importLibrary("maps");
        const { AdvancedMarkerElement } = await importLibrary("marker");

        // Variable to track currently open info window
        let currentInfoWindow = null;

        // Create the map
        const isMobile = window.innerWidth < 768;
        const map = new Map(mapEl, {
            center: { lat: parseFloat(config.centerLat), lng: parseFloat(config.centerLng) },
            zoom: isMobile ? 2 : 10,
            mapId: "f4a15cb6cd4f8d61",
            gestureHandling: 'greedy',
        });

        if (!isMobile) {
            const bounds = new google.maps.LatLngBounds(
                new google.maps.LatLng(parseFloat(config.minLat), parseFloat(config.minLng)),
                new google.maps.LatLng(parseFloat(config.maxLat), parseFloat(config.maxLng))
            );
            map.fitBounds(bounds);
        }

        // Base API URL (ensure trailing slash for URL construction)
        const baseApiUrl = config.baseApiUrl.endsWith('/')
            ? config.baseApiUrl
            : config.baseApiUrl + '/';

        const markersData = config.markersData;
        const markers = [];

        // Company custom marker image
        let customMarkerImg = null;
        if (config.companyInfo && config.companyInfo[0] && config.companyInfo[0].customMarker) {
            customMarkerImg = document.createElement('img');
            customMarkerImg.src = config.companyInfo[0].customMarker;
        }

        function attachInfoWindow(marker, checkinId) {
            const infoWindow = new google.maps.InfoWindow({
                content: renderCardSkeleton()
            });

            marker.addListener("gmp-click", () => {
                if (currentInfoWindow) currentInfoWindow.close();
                infoWindow.open(map, marker);
                currentInfoWindow = infoWindow;

                fetch(new URL(`checkins/${checkinId}`, baseApiUrl).toString(), fetchOptions)
                    .then((response) => response.json())
                    .then((data) => {
                        infoWindow.setContent(renderInfoWindowCard(data));
                        bindInfoWindowCarousels(infoWindow);
                    })
                    .catch((error) => console.error("Error fetching marker data:", error));
            });
        }

        // Create markers
        markersData.forEach((markerData) => {
            const marker = new AdvancedMarkerElement({
                map: map,
                position: markerData.position,
                id: markerData.id,
                ...(customMarkerImg && { content: customMarkerImg.cloneNode(true) })
            });

            markers.push(marker);
            attachInfoWindow(marker, markerData.id);
        });

        // After creating all markers, add clustering (only if there are multiple markers)
        let clusterer = null;
        if (markers.length > 1) {
            clusterer = new MarkerClusterer({
                map: map,
                markers: markers
            });
        }

        // Progressively fetch remaining map pages if there are more
        if (config.hasNext) {
            const fetchPage = async (page) => {
                const params = new URLSearchParams({ page, pageSize: config.pageSize });
                if (config.companyId) {
                    params.set('companyId', config.companyId);
                }

                // Forward shortcode atts (state, etc.) so paginated results match the initial filter
                const scAtts = config.scAtts || {};
                Object.entries(scAtts).forEach(([key, value]) => {
                    if (value === null || value === undefined) return;
                    if (String(value).trim() !== '') params.set(key, String(value));
                });

                const url = new URL('map', baseApiUrl);
                params.forEach((value, key) => url.searchParams.set(key, value));
                const response = await fetch(url.toString(), fetchOptions);
                const data = await response.json();
                const features = data?.locations?.features ?? [];
                const newMarkers = [];

                features.forEach((feature) => {
                    const lat = feature.geometry.coordinates[1];
                    const lng = feature.geometry.coordinates[0];
                    const checkinId = feature.properties?.checkinId;
                    if (!checkinId) return;

                    const marker = new AdvancedMarkerElement({
                        map: map,
                        position: { lat, lng },
                        id: checkinId,
                        ...(customMarkerImg && { content: customMarkerImg.cloneNode(true) })
                    });

                    attachInfoWindow(marker, checkinId);
                    newMarkers.push(marker);
                });

                if (newMarkers.length > 0) {
                    if (clusterer) {
                        clusterer.addMarkers(newMarkers);
                    } else {
                        clusterer = new MarkerClusterer({ map, markers: [...markers, ...newMarkers] });
                    }
                }
            };

            (async () => {
                for (let page = 2; page <= config.totalPages; page++) {
                    await fetchPage(page);
                }
            })();
        }

        // Add map click listener to close any open info window
        map.addListener("click", () => {
            if (currentInfoWindow) {
                currentInfoWindow.close();
                currentInfoWindow = null;
            }
        });
    } catch (error) {
        console.error("Error initializing map:", error);
    }
}
