import { MarkerClusterer } from "@googlemaps/markerclusterer";
import { setOptions, importLibrary } from "@googlemaps/js-api-loader";
import { createElement, Star, Calendar } from 'lucide';

function lucideSvg(icon, size, cssClass) {
    const el = createElement(icon, { width: size, height: size });
    if (cssClass) el.setAttribute('class', cssClass);
    el.setAttribute('aria-hidden', 'true');
    return el.outerHTML;
}

setOptions({
    key: jobcaptureproMapData.googleMapsApiKey,
});


async function initJobCaptureProMap() {
    try {
        // Request needed libraries.
        const { Map } = await importLibrary("maps");
        const { AdvancedMarkerElement } = await importLibrary("marker");

        // Variable to track currently open info window
        let currentInfoWindow = null;

        // Create the map
        const isMobile = window.innerWidth < 768;
        const map = new Map(document.getElementById("jcp-map"), {
            center: { lat: parseFloat(jobcaptureproMapData.centerLat), lng: parseFloat(jobcaptureproMapData.centerLng) },
            zoom: isMobile ? 2 : 10,
            mapId: "f4a15cb6cd4f8d61",
            gestureHandling: 'greedy',
        });

        if (!isMobile) {
            // Define bounds for the map
            const bounds = new google.maps.LatLngBounds(
                new google.maps.LatLng(parseFloat(jobcaptureproMapData.minLat), parseFloat(jobcaptureproMapData.minLng)),
                new google.maps.LatLng(parseFloat(jobcaptureproMapData.maxLat), parseFloat(jobcaptureproMapData.maxLng))
            );

            // Fit the map to these bounds
            map.fitBounds(bounds);
        }

        // Markers data
        const markersData = jobcaptureproMapData.markersData;

        // Create markers array for clustering
        const markers = [];

        // Company custom marker image
        let customMarkerImg = null;
        if (jobcaptureproMapData.companyInfo && jobcaptureproMapData.companyInfo[0] && jobcaptureproMapData.companyInfo[0].customMarker) {
            customMarkerImg = document.createElement('img');
            customMarkerImg.src = jobcaptureproMapData.companyInfo[0].customMarker;
        }

        // Create markers
        markersData.forEach((markerData, index) => {
            const marker = new AdvancedMarkerElement({
                map: map,
                position: markerData.position,
                id: markerData.id,
                ...(customMarkerImg && { content: customMarkerImg.cloneNode(true) })
            });

            // Add marker to array for clustering
            markers.push(marker);

            // Add info window if there\'s additional content
            const infoWindow = new google.maps.InfoWindow({
                content: `
                    <div class="jcp-info-window jcp:p-[5px] jcp:max-w-[250px] jcp:font-sans jcp:pt-0 !jcp:max-w-[350px] jcp:animate-pulse">
                        <!-- Image Gallery Skeleton -->
                        <div class="jcp-checkin-image !jcp:h-[160px] !jcp:mb-2 jcp:relative">
                            <div class="jcp:w-full jcp:h-full jcp:bg-gray-300 jcp:rounded-t-lg jcp:animate-pulse"></div>
                            <div class="jcp:absolute jcp:left-2 jcp:top-1/2 jcp:transform jcp:-translate-y-1/2 jcp:w-8 jcp:h-8 jcp:bg-gray-400 jcp:rounded"></div>
                            <div class="jcp:absolute jcp:right-2 jcp:top-1/2 jcp:transform jcp:-translate-y-1/2 jcp:w-8 jcp:h-8 jcp:bg-gray-400 jcp:rounded"></div>
                            <div class="jcp:absolute jcp:bottom-2 jcp:left-1/2 jcp:transform jcp:-translate-x-1/2 jcp:flex jcp:space-x-2">
                                <span class="jcp:w-2 jcp:h-2 jcp:bg-gray-400 jcp:rounded-full"></span>
                                <span class="jcp:w-2 jcp:h-2 jcp:bg-gray-400 jcp:rounded-full"></span>
                                <span class="jcp:w-2 jcp:h-2 jcp:bg-gray-400 jcp:rounded-full"></span>
                            </div>
                        </div>
                        
                        <!-- User Section Skeleton -->
                        <div class="jcp-checkin-user jcp:flex jcp:items-center jcp:justify-between jcp:p-4">
                            <div class="jcp:flex jcp:justify-between jcp:items-center jcp:gap-2">
                                <div class="jcp-user-image">
                                    <div class="jcp:w-12 jcp:h-12 jcp:bg-gray-300 jcp:rounded-full"></div>
                                </div>
                                
                                <div class="jcp:flex-1">
                                    <div class="jcp:h-4 jcp:bg-gray-300 jcp:rounded jcp:w-24"></div>
                                </div>
                            </div>
                            
                            <div class="jcp-job-reviews jcp:flex jcp:space-x-1">
                                <div class="jcp:w-3 jcp:h-3 jcp:bg-gray-300 jcp:rounded"></div>
                                <div class="jcp:w-3 jcp:h-3 jcp:bg-gray-300 jcp:rounded"></div>
                                <div class="jcp:w-3 jcp:h-3 jcp:bg-gray-300 jcp:rounded"></div>
                                <div class="jcp:w-3 jcp:h-3 jcp:bg-gray-300 jcp:rounded"></div>
                                <div class="jcp:w-3 jcp:h-3 jcp:bg-gray-300 jcp:rounded"></div>
                            </div>
                        </div>
                        
                        <!-- Description Section Skeleton -->
                        <div class="jcp-checkin-description jcp:px-4 jcp:pb-2">
                            <div class="jcp:h-3 jcp:bg-gray-300 jcp:rounded jcp:w-full jcp:mb-2"></div>
                            <div class="jcp:h-3 jcp:bg-gray-300 jcp:rounded jcp:w-3/4"></div>
                        </div>
                        
                        <!-- Date Section Skeleton -->
                        <div class="jcp:flex jcp:justify-between jcp:items-center">
                            <div class="jcp-checkin-date">
                                <div class="jcp:flex jcp:items-center jcp:space-x-2">
                                    <div class="jcp:w-4 jcp:h-4 jcp:bg-gray-300 jcp:rounded"></div>
                                    <div class="jcp:h-3 jcp:bg-gray-300 jcp:rounded jcp:w-20"></div>
                                </div>
                            </div>
                            
                            <!-- Address Section Skeleton -->
                            <div class="jcp-checkin-address">
                                <div class="jcp:h-3 jcp:bg-gray-300 jcp:rounded jcp:w-32"></div>
                            </div>
                        </div>
                    </div>
                `
            });

            marker.addListener("gmp-click", () => {
                // Close any currently open info window
                if (currentInfoWindow) {
                    currentInfoWindow.close();
                }

                // Open this marker's info window
                infoWindow.open(map, marker);
                currentInfoWindow = infoWindow;

                // Fetch content and display it in the info window
                fetch(`${jobcaptureproMapData.baseApiUrl}/checkin/${markerData.id}`)
                    .then(response => response.json())
                    .then(data => {
                        const addressParts = data.address.split(',');
                        const city = (addressParts[1] || '').trim();
                        const state = (addressParts[2] || '').trim();
                        const stateAbbr = state.length > 2 ? state.substring(0, 2) : state;
                        const locationDisplay = `${city}, ${stateAbbr}`;

                        infoWindow.setContent(`
                            <div class="jcp-info-window jcp:p-[5px] jcp:max-w-[350px] jcp:font-sans jcp:pt-0">
                                ${data.imageUrls && data.imageUrls.length > 0 ? `
                                    <div class="jcp-checkin-image !jcp:h-[160px] !jcp:mb-2" id="gallery-${data.id}">
                                        ${data.imageUrls.map((imageUrl, index) => `
                                            <div class="gallery-image ${index === 0 ? 'active' : ''}" data-index="${index}">
                                                <img src="${imageUrl}" alt="Checkin image ${index + 1}" style="height: auto;">
                                            </div>
                                        `).join('')}
                                        
                                        ${data.imageUrls.length > 1 ? `
                                            <div class="gallery-nav gallery-prev" onclick="jobcaptureproChangeImage(event, 'gallery-${data.id}', 'prev')">❮</div>
                                            <div class="gallery-nav gallery-next" onclick="jobcaptureproChangeImage(event, 'gallery-${data.id}', 'next')">❯</div>
                                            <div class="gallery-dots">
                                                ${data.imageUrls.map((_, index) => `
                                                    <span class="gallery-dot ${index === 0 ? 'active' : ''}" onclick="jobcaptureproShowImage(event, 'gallery-${data.id}', ${index})"></span>
                                                `).join('')}
                                            </div>
                                        ` : ''}
                                    </div>
                                ` : ''}
                                
                                ${data.assignedUser ? `
                                    <div class="jcp-checkin-user">
                                        ${data.assignedUser.profileImageUrl ? `
                                            <div class="jcp-user-image">
                                                <img src="${data.assignedUser.profileImageUrl}" alt="User profile" style="width: 50px; height: 50px; border-radius: 50%;">
                                            </div>
                                        ` : ''}

                                        <div class="jcp-user-name">
                                            <p class="jcp:my-[5px] jcp:text-sm">
                                                <strong>${data.assignedUser.name || 'Unknown User'}</strong>
                                            </p>
                                        </div>

                                        <div class="jcp-job-reviews">
                                            ${Array.from({ length: 5 }, () => `<span>${lucideSvg(Star, 16, 'jcp:fill-[#facc15] jcp:text-[#facc15]')}</span>`).join('')}
                                        </div>
                                    </div>
                                ` : ''}
                                
                                ${data.description ? `
                                    <div class="jcp-checkin-description">
                                        <p class="jcp:my-[5px] jcp:text-sm">${data.description}</p>
                                    </div>
                                ` : ''}
                                
                                <div class="jcp-checkin-date">
                                    <p class="date-icon jcp:my-[5px] jcp:text-xs jcp:text-[#666]">
                                        ${lucideSvg(Calendar, 12)}
                                        ${data.createdAt ? new Date(data.createdAt).toLocaleDateString('en-US',
                            {
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric'
                            }) : 'Date not available'}
                                    </p>
                                </div>
                                
                                ${data.address ? `
                                    <div class="jcp-checkin-address">
                                        <p class="jcp:my-[5px] jcp:text-xs jcp:text-[#666]"><strong>Near</strong> ${locationDisplay}</p>
                                    </div>
                                ` : ''}
                            </div>
                        `)
                    }).catch(error => {
                        console.error("Error fetching marker data:", error);
                    });

            });
        });

        // After creating all markers, add clustering (only if there are multiple markers)
        if (markers.length > 1) {
            new MarkerClusterer({
                map: map,
                markers: markers
            });
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

window.addEventListener("DOMContentLoaded", async () => {
    initJobCaptureProMap();
});