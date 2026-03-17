import { MarkerClusterer } from "@googlemaps/markerclusterer";
import { setOptions, importLibrary } from "@googlemaps/js-api-loader";
import { createElement, Star, Calendar, MapPin, ChevronLeft, ChevronRight } from 'lucide';

function lucideSvg(icon, size, cssClass) {
  const el = createElement(icon, { width: size, height: size });
  if (cssClass) el.setAttribute('class', cssClass);
  el.setAttribute('aria-hidden', 'true');
  return el.outerHTML;
}

function buildCardSkeleton() {
  return `
        <article class="jcp-plugin-card jcp:min-w-0 jcp:bg-white jcp:overflow-hidden jcp:max-w-[350px] jcp:animate-pulse">
            <div class="jcp-plugin-card__gallery">
                <div class="jcp:w-full jcp:h-full jcp:bg-gray-300 jcp:animate-pulse"></div>
                <div class="jcp:absolute jcp:left-2 jcp:top-1/2 jcp:-translate-y-1/2 jcp:w-9 jcp:h-9 jcp:bg-gray-400 jcp:rounded-full"></div>
                <div class="jcp:absolute jcp:right-2 jcp:top-1/2 jcp:-translate-y-1/2 jcp:w-9 jcp:h-9 jcp:bg-gray-400 jcp:rounded-full"></div>
                <div class="jcp:absolute jcp:bottom-2 jcp:left-1/2 jcp:-translate-x-1/2 jcp:flex jcp:gap-1.5">
                    <span class="jcp:w-2 jcp:h-2 jcp:bg-gray-400 jcp:rounded-full"></span>
                    <span class="jcp:w-2 jcp:h-2 jcp:bg-gray-400 jcp:rounded-full"></span>
                    <span class="jcp:w-2 jcp:h-2 jcp:bg-gray-400 jcp:rounded-full"></span>
                </div>
            </div>
            <div class="jcp-plugin-card__body jcp:pt-4 jcp:px-1">
                <div class="jcp:h-4 jcp:bg-gray-300 jcp:rounded jcp:w-full jcp:mb-2"></div>
                <div class="jcp:h-4 jcp:bg-gray-300 jcp:rounded jcp:w-3/4 jcp:mb-4"></div>
                <hr class="jcp:border-0 jcp:border-t jcp:border-[#e5e7eb] jcp:my-4">
                <div class="jcp:flex jcp:justify-between jcp:items-center">
                    <div class="jcp:flex jcp:items-center jcp:gap-2">
                        <div class="jcp:w-4 jcp:h-4 jcp:bg-gray-300 jcp:rounded"></div>
                        <div class="jcp:h-3 jcp:bg-gray-300 jcp:rounded jcp:w-20"></div>
                    </div>
                    <div class="jcp:flex jcp:items-center jcp:gap-2">
                        <div class="jcp:w-4 jcp:h-4 jcp:bg-gray-300 jcp:rounded"></div>
                        <div class="jcp:h-3 jcp:bg-gray-300 jcp:rounded jcp:w-24"></div>
                    </div>
                </div>
            </div>
        </article>`;
}

function buildCard(data) {
  const addressParts = (data.address || '').split(',');
  const city = (addressParts[1] || '').trim();
  const state = (addressParts[2] || '').trim();
  const stateAbbr = state.length > 2 ? state.substring(0, 2) : state;
  const locationDisplay = `${city}, ${stateAbbr}`;

  const imageUrls = data.imageUrls || [];
  const hasImages = imageUrls.length > 0;
  const hasGallery = imageUrls.length > 1;

  const timestamp = data.jobCompletedDate || data.createdAt;
  const dateDisplay = timestamp
    ? new Date(timestamp).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
    : 'Date not available';

  return `
        <article class="jcp-plugin-card jcp:min-w-0 jcp:bg-white jcp:overflow-hidden jcp:max-w-[350px]">
            ${hasImages ? `
                <div class="jcp-plugin-card__gallery"${hasGallery ? ' data-carousel' : ''}>
                    <div class="jcp-plugin-card__gallery-inner">
                        ${imageUrls.map((url, i) => `
                            <div class="jcp-plugin-card__slide ${i === 0 ? 'jcp:opacity-100 jcp:visible' : 'jcp:opacity-0 jcp:invisible'}">
                                <img src="${url}" alt="" loading="${i === 0 ? 'eager' : 'lazy'}">
                            </div>
                        `).join('')}
                    </div>
                    ${hasGallery ? `
                        <button type="button" class="jcp-plugin-card__nav jcp-plugin-card__nav--prev jcp:left-2" aria-label="Previous image">
                            ${lucideSvg(ChevronLeft, 20)}
                        </button>
                        <button type="button" class="jcp-plugin-card__nav jcp-plugin-card__nav--next jcp:right-2" aria-label="Next image">
                            ${lucideSvg(ChevronRight, 20)}
                        </button>
                        <div class="jcp-plugin-card__dots">
                            ${imageUrls.map((_, i) => `
                                <button type="button" class="jcp-plugin-card__dot" aria-label="Image ${i + 1}"></button>
                            `).join('')}
                        </div>
                    ` : ''}
                </div>
            ` : ''}
            <div class="jcp-plugin-card__body jcp:pt-4 jcp:px-1">
                ${data.description ? `
                    <p class="jcp-plugin-card__description">${data.description}</p>
                ` : ''}
                <hr class="jcp:border-0 jcp:border-t jcp:border-[#e5e7eb] jcp:my-4">
                <div class="jcp-plugin-card__meta">
                    <span class="jcp-plugin-card__meta-item jcp-plugin-card__date">
                        ${lucideSvg(Calendar, 14, 'jcp-plugin-card__meta-icon')}
                        ${dateDisplay}
                    </span>
                    ${data.address ? `
                        <span class="jcp-plugin-card__meta-item jcp-plugin-card__location">
                            ${lucideSvg(MapPin, 14, 'jcp-plugin-card__meta-icon')}
                            Near ${locationDisplay}
                        </span>
                    ` : ''}
                </div>
            </div>
        </article>`;
}

function bindCarousel(infoWindow) {
  google.maps.event.addListenerOnce(infoWindow, 'domready', () => {
    // Google Maps puts info window content inside .gm-style-iw-d
    const iwContainers = document.querySelectorAll('.gm-style-iw-d');
    iwContainers.forEach(container => {
      container.querySelectorAll('.jcp-plugin-card__gallery[data-carousel]').forEach(gallery => {
        if (gallery.dataset.carouselBound) return;
        gallery.dataset.carouselBound = '1';

        const slides = gallery.querySelectorAll('.jcp-plugin-card__slide');
        const prevBtn = gallery.querySelector('.jcp-plugin-card__nav--prev');
        const nextBtn = gallery.querySelector('.jcp-plugin-card__nav--next');
        const dots = gallery.querySelectorAll('.jcp-plugin-card__dot');
        const total = slides.length;
        let active = 0;

        function setActive(i) {
          if (i < 0) i = total - 1;
          if (i >= total) i = 0;
          active = i;
          slides.forEach((slide, idx) => {
            if (idx === active) {
              slide.classList.add('jcp:opacity-100', 'jcp:visible');
              slide.classList.remove('jcp:opacity-0', 'jcp:invisible');
            } else {
              slide.classList.add('jcp:opacity-0', 'jcp:invisible');
              slide.classList.remove('jcp:opacity-100', 'jcp:visible');
            }
          });
          dots.forEach((dot, idx) => {
            if (idx === active) {
              dot.classList.add('jcp:bg-white', 'jcp:scale-[1.2]');
              dot.classList.remove('jcp:bg-white/60');
            } else {
              dot.classList.add('jcp:bg-white/60');
              dot.classList.remove('jcp:bg-white', 'jcp:scale-[1.2]');
            }
          });
        }

        if (prevBtn) prevBtn.addEventListener('click', () => setActive(active - 1));
        if (nextBtn) nextBtn.addEventListener('click', () => setActive(active + 1));
        dots.forEach((dot, idx) => dot.addEventListener('click', () => setActive(idx)));
      });
    });
  });
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

    // Base API URL (ensure trailing slash for URL construction)
    const baseApiUrl = jobcaptureproMapData.baseApiUrl.endsWith('/')
      ? jobcaptureproMapData.baseApiUrl
      : jobcaptureproMapData.baseApiUrl + '/';

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

    function attachInfoWindow(marker, checkinId) {
      const infoWindow = new google.maps.InfoWindow({
        content: buildCardSkeleton()
      });

      marker.addListener("gmp-click", () => {
        if (currentInfoWindow) currentInfoWindow.close();
        infoWindow.open(map, marker);
        currentInfoWindow = infoWindow;

        fetch(new URL(`checkin/${checkinId}`, baseApiUrl).toString())
          .then(response => response.json())
          .then(data => {
            infoWindow.setContent(buildCard(data));
            bindCarousel(infoWindow);
          })
          .catch(error => console.error("Error fetching marker data:", error));
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
    if (jobcaptureproMapData.hasNext) {
      const fetchPage = async (page) => {
        const params = new URLSearchParams({ page, pageSize: jobcaptureproMapData.pageSize });
        if (jobcaptureproMapData.companyId) {
          params.set('companyId', jobcaptureproMapData.companyId);
        }

        const url = new URL('map', baseApiUrl);
        params.forEach((value, key) => url.searchParams.set(key, value));
        const response = await fetch(url.toString());
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
        for (let page = 2; page <= jobcaptureproMapData.totalPages; page++) {
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

window.addEventListener("DOMContentLoaded", async () => {
  initJobCaptureProMap();
});
