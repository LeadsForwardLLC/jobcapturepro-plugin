import { MarkerClusterer } from "@googlemaps/markerclusterer";
import { setOptions, importLibrary } from "@googlemaps/js-api-loader";

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
        const map = new Map(document.getElementById("jobcapturepro-map"), {
            center: { lat: parseFloat(jobcaptureproMapData.centerLat), lng: parseFloat(jobcaptureproMapData.centerLng) },
            zoom: 10,
            mapId: "f4a15cb6cd4f8d61",
            gestureHandling: 'greedy',
        });

        // Define bounds for the map
        const bounds = new google.maps.LatLngBounds(
            new google.maps.LatLng(parseFloat(jobcaptureproMapData.minLat), parseFloat(jobcaptureproMapData.minLng)),
            new google.maps.LatLng(parseFloat(jobcaptureproMapData.maxLat), parseFloat(jobcaptureproMapData.maxLng))
        );

        // Fit the map to these bounds
        map.fitBounds(bounds);

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
                    <div class="jobcapturepro-info-window jcp:pt-0 !jcp:max-w-[350px] jcp:animate-pulse">
                        <!-- Image Gallery Skeleton -->
                        <div class="jobcapturepro-checkin-image !jcp:h-[160px] !jcp:mb-2 jcp:relative">
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
                        <div class="jobcapturepro-checkin-user jcp:flex jcp:items-center jcp:justify-between jcp:p-4">
                            <div class="jcp:flex jcp:justify-between jcp:items-center jcp:gap-2">
                                <div class="jobcapturepro-user-image">
                                    <div class="jcp:w-12 jcp:h-12 jcp:bg-gray-300 jcp:rounded-full"></div>
                                </div>
                                
                                <div class="jcp:flex-1">
                                    <div class="jcp:h-4 jcp:bg-gray-300 jcp:rounded jcp:w-24"></div>
                                </div>
                            </div>
                            
                            <div class="jobcapturepro-job-reviews jcp:flex jcp:space-x-1">
                                <div class="jcp:w-3 jcp:h-3 jcp:bg-gray-300 jcp:rounded"></div>
                                <div class="jcp:w-3 jcp:h-3 jcp:bg-gray-300 jcp:rounded"></div>
                                <div class="jcp:w-3 jcp:h-3 jcp:bg-gray-300 jcp:rounded"></div>
                                <div class="jcp:w-3 jcp:h-3 jcp:bg-gray-300 jcp:rounded"></div>
                                <div class="jcp:w-3 jcp:h-3 jcp:bg-gray-300 jcp:rounded"></div>
                            </div>
                        </div>
                        
                        <!-- Description Section Skeleton -->
                        <div class="jobcapturepro-checkin-description jcp:px-4 jcp:pb-2">
                            <div class="jcp:h-3 jcp:bg-gray-300 jcp:rounded jcp:w-full jcp:mb-2"></div>
                            <div class="jcp:h-3 jcp:bg-gray-300 jcp:rounded jcp:w-3/4"></div>
                        </div>
                        
                        <!-- Date Section Skeleton -->
                        <div class="jcp:flex jcp:justify-between jcp:items-center">
                            <div class="jobcapturepro-checkin-date">
                                <div class="jcp:flex jcp:items-center jcp:space-x-2">
                                    <div class="jcp:w-4 jcp:h-4 jcp:bg-gray-300 jcp:rounded"></div>
                                    <div class="jcp:h-3 jcp:bg-gray-300 jcp:rounded jcp:w-20"></div>
                                </div>
                            </div>
                            
                            <!-- Address Section Skeleton -->
                            <div class="jobcapturepro-checkin-address">
                                <div class="jcp:h-3 jcp:bg-gray-300 jcp:rounded jcp:w-32"></div>
                            </div>
                        </div>
                    </div>
                `
            });

            marker.addListener("click", () => {
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
                            <div class="jobcapturepro-info-window jcp:pt-0" style="max-width: 350px;">
                                ${data.imageUrls && data.imageUrls.length > 0 ? `
                                    <div class="jobcapturepro-checkin-image !jcp:h-[160px] !jcp:mb-2" id="gallery-${data.id}">
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
                                    <div class="jobcapturepro-checkin-user">
                                        ${data.assignedUser.profileImageUrl ? `
                                            <div class="jobcapturepro-user-image">
                                                <img src="${data.assignedUser.profileImageUrl}" alt="User profile" style="width: 50px; height: 50px; border-radius: 50%;">
                                            </div>
                                        ` : ''}

                                        <div class="jobcapturepro-user-name">
                                            <p>
                                                <strong>${data.assignedUser.name || 'Unknown User'}</strong>
                                            </p>
                                        </div>

                                        <div class="jobcapturepro-job-reviews">
                                            <span>
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C43.3 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z"></path></svg>
                                            </span>
                                            <span>
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C43.3 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z"></path></svg>
                                            </span>
                                            <span>
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C43.3 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z"></path></svg>
                                            </span>
                                            <span>
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C43.3 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z"></path></svg>
                                            </span>
                                            <span>
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C43.3 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z"></path></svg>
                                            </span>
                                        </div>
                                    </div>
                                ` : ''}
                                
                                ${data.description ? `
                                    <div class="jobcapturepro-checkin-description">
                                        <p>${data.description}</p>
                                    </div>
                                ` : ''}
                                
                                <div class="jobcapturepro-checkin-date">
                                    <p class="date-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M224 64C241.7 64 256 78.3 256 96L256 128L384 128L384 96C384 78.3 398.3 64 416 64C433.7 64 448 78.3 448 96L448 128L480 128C515.3 128 544 156.7 544 192L544 480C544 515.3 515.3 544 480 544L160 544C124.7 544 96 515.3 96 480L96 192C96 156.7 124.7 128 160 128L192 128L192 96C192 78.3 206.3 64 224 64zM160 304L160 336C160 344.8 167.2 352 176 352L208 352C216.8 352 224 344.8 224 336L224 304C224 295.2 216.8 288 208 288L176 288C167.2 288 160 295.2 160 304zM288 304L288 336C288 344.8 295.2 352 304 352L336 352C344.8 352 352 344.8 352 336L352 304C352 295.2 344.8 288 336 288L304 288C295.2 288 288 295.2 288 304zM432 288C423.2 288 416 295.2 416 304L416 336C416 344.8 423.2 352 432 352L464 352C472.8 352 480 344.8 480 336L480 304C480 295.2 472.8 288 464 288L432 288zM160 432L160 464C160 472.8 167.2 480 176 480L208 480C216.8 480 224 472.8 224 464L224 432C224 423.2 216.8 416 208 416L176 416C167.2 416 160 423.2 160 432zM304 416C295.2 416 288 423.2 288 432L288 464C288 472.8 295.2 480 304 480L336 480C344.8 480 352 472.8 352 464L352 432C352 423.2 344.8 416 336 416L304 416zM416 432L416 464C416 472.8 423.2 480 432 480L464 480C472.8 480 480 472.8 480 464L480 432C480 423.2 472.8 416 464 416L432 416C423.2 416 416 423.2 416 432z"></path></svg>
                                        ${data.createdAt ? new Date(data.createdAt).toLocaleDateString('en-US',
                            {
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric'
                            }) : 'Date not available'}
                                    </p>
                                </div>
                                
                                ${data.address ? `
                                    <div class="jobcapturepro-checkin-address">
                                        <p><strong>Near</strong> ${locationDisplay}</p>
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