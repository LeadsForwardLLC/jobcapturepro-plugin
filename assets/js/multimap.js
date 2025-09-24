
async function initMultiMap() {
    try {
        // Request needed libraries.
        const { Map } = await google.maps.importLibrary("maps");
        const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");

        // Variable to track currently open info window
        let currentInfoWindow = null;

        // Create the map
        const map = new Map(document.getElementById("multimap"), {
            center: { lat: parseFloat(jcpMultimapData.centerLat), lng: parseFloat(jcpMultimapData.centerLng) },
            zoom: 10,
            mapId: "f4a15cb6cd4f8d61",
        });

        // Define bounds for the map
        const bounds = new google.maps.LatLngBounds(
            new google.maps.LatLng(parseFloat(jcpMultimapData.minLat), parseFloat(jcpMultimapData.minLng)),
            new google.maps.LatLng(parseFloat(jcpMultimapData.maxLat), parseFloat(jcpMultimapData.maxLng))
        );

        // Fit the map to these bounds
        map.fitBounds(bounds);

        // Markers data
        const markersData = jcpMultimapData.markersData;

        // Create markers array for clustering
        const markers = [];

        // Create markers
        markersData.forEach((markerData, index) => {
            const marker = new AdvancedMarkerElement({
                map: map,
                position: markerData.position,
            });

            // Add marker to array for clustering
            markers.push(marker);

            // Add info window if there\'s additional content
            const infoWindow = new google.maps.InfoWindow({
                content: `
                    <div class>
                        <p>Loading details...</p>
                    </div>
                `
            });

            marker.addListener("click", () => {
                // Close any currently open info window
                if(currentInfoWindow) {
                    currentInfoWindow.close();
                }

                // Open this info window
                infoWindow.open(map, marker);
                currentInfoWindow = infoWindow;
            });
        });

        // After creating all markers, add clustering (only if there are multiple markers)
        if (markers.length > 1) {
            const markerCluster = new markerClusterer.MarkerClusterer({
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

// Initialize when page loads
if (typeof google !== "undefined" && google.maps) {
    initMultiMap();
} else {
    window.addEventListener("load", initMultiMap);
}