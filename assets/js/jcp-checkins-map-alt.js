(() => {
    const ROOT_SELECTOR = '.jcp-alt-map';

    const parseJsonAttr = (el, attr) => {
        const raw = el.getAttribute(attr);
        if (!raw) return null;
        try {
            return JSON.parse(raw);
        } catch (error) {
            console.error('JobCapturePro alt map: invalid JSON', error);
            return null;
        }
    };

    const loadScriptOnce = (src, id) => {
        return new Promise((resolve, reject) => {
            const existing = document.querySelector(`script[data-jcp-alt="${id}"]`);
            if (existing) {
                if (existing.dataset.loaded === 'true') {
                    resolve();
                } else {
                    existing.addEventListener('load', resolve, { once: true });
                    existing.addEventListener('error', reject, { once: true });
                }
                return;
            }

            const script = document.createElement('script');
            script.src = src;
            script.async = true;
            script.defer = true;
            script.dataset.jcpAlt = id;
            script.onload = () => {
                script.dataset.loaded = 'true';
                resolve();
            };
            script.onerror = reject;
            document.head.appendChild(script);
        });
    };

    const loadGoogleMaps = (apiKey) => {
        if (window.google && window.google.maps) {
            return Promise.resolve();
        }
        if (!apiKey) {
            return Promise.reject(new Error('Missing Google Maps API key'));
        }
        const src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(apiKey)}&libraries=marker`;
        return loadScriptOnce(src, 'google-maps');
    };

    const loadMarkerClusterer = () => {
        if (window.MarkerClusterer) {
            return Promise.resolve();
        }
        return loadScriptOnce('https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js', 'marker-clusterer');
    };

    const getCompanyName = (checkin) => {
        if (checkin.companyName) return String(checkin.companyName);
        if (checkin.company && checkin.company.name) return String(checkin.company.name);
        return '';
    };

    const getServiceTags = (checkin) => {
        if (Array.isArray(checkin.service_tags)) return checkin.service_tags;
        if (Array.isArray(checkin.serviceTags)) return checkin.serviceTags;
        return [];
    };

    const formatLocation = (address) => {
        if (!address || typeof address !== 'string') return '';
        const parts = address.split(',');
        const city = (parts[1] || '').trim();
        const state = (parts[2] || '').trim();
        const stateAbbr = state.length > 2 ? state.substring(0, 2) : state;
        if (!city && !stateAbbr) return '';
        return `${city}${city && stateAbbr ? ', ' : ''}${stateAbbr}`;
    };

    const formatDate = (isoDate) => {
        if (!isoDate) return '';
        const date = new Date(isoDate);
        if (Number.isNaN(date.getTime())) return '';
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    };

    const truncateText = (text, maxLength) => {
        if (!text || typeof text !== 'string') return '';
        if (text.length <= maxLength) return text;
        return `${text.substring(0, maxLength).trim()}…`;
    };

    const buildCard = (checkin) => {
        const card = document.createElement('div');
        card.className = 'jcp-alt-checkin-card';
        card.dataset.checkinId = checkin.id || '';
        card.tabIndex = 0;
        card.setAttribute('role', 'button');

        const imageWrapper = document.createElement('div');
        imageWrapper.className = 'jcp-alt-card-image';

        const imageUrl = Array.isArray(checkin.imageUrls) && checkin.imageUrls.length > 0
            ? checkin.imageUrls[0]
            : '';

        if (imageUrl) {
            const img = document.createElement('img');
            img.src = imageUrl;
            img.alt = 'Check-in photo';
            img.loading = 'lazy';
            imageWrapper.appendChild(img);
        } else {
            imageWrapper.classList.add('is-empty');
            const fallback = document.createElement('span');
            fallback.textContent = 'Photo coming soon';
            imageWrapper.appendChild(fallback);
        }

        const content = document.createElement('div');
        content.className = 'jcp-alt-card-content';

        const title = document.createElement('h3');
        title.className = 'jcp-alt-card-title';
        const headline = checkin.title || checkin.jobTitle || checkin.description || '';
        title.textContent = truncateText(headline, 80);

        const meta = document.createElement('div');
        meta.className = 'jcp-alt-card-meta';

        const companyName = getCompanyName(checkin);
        if (companyName) {
            const company = document.createElement('span');
            company.className = 'jcp-alt-card-company';
            company.textContent = companyName;
            meta.appendChild(company);
        }

        const location = formatLocation(checkin.address);
        if (location) {
            const locationEl = document.createElement('span');
            locationEl.className = 'jcp-alt-card-location';
            locationEl.textContent = location;
            meta.appendChild(locationEl);
        }

        const date = formatDate(checkin.createdAt);
        if (date) {
            const dateEl = document.createElement('span');
            dateEl.className = 'jcp-alt-card-date';
            dateEl.textContent = date;
            meta.appendChild(dateEl);
        }

        const tags = getServiceTags(checkin);
        const tagsWrap = document.createElement('div');
        tagsWrap.className = 'jcp-alt-card-tags';
        tags.slice(0, 6).forEach((tag) => {
            const tagEl = document.createElement('span');
            tagEl.className = 'jcp-alt-card-tag';
            tagEl.textContent = tag;
            tagsWrap.appendChild(tagEl);
        });

        content.appendChild(title);
        if (meta.children.length) content.appendChild(meta);
        if (tagsWrap.children.length) content.appendChild(tagsWrap);

        card.appendChild(imageWrapper);
        card.appendChild(content);

        return card;
    };

    const initAltLayout = async (root) => {
        const mapData = parseJsonAttr(root, 'data-jcp-alt-map');
        const checkins = parseJsonAttr(root, 'data-jcp-alt-checkins');

        if (!mapData || !checkins || !Array.isArray(checkins)) return;

        root.innerHTML = '';

        // Alternate layout: map rendered above cards instead of the default grid layout.
        const mapEl = document.createElement('div');
        mapEl.className = 'jcp-alt-map-canvas';

        const slider = document.createElement('div');
        slider.className = 'jcp-alt-slider';

        const prevButton = document.createElement('button');
        prevButton.type = 'button';
        prevButton.className = 'jcp-alt-slider-btn jcp-alt-slider-prev';
        prevButton.setAttribute('aria-label', 'Previous check-ins');
        prevButton.innerHTML = '&#10094;';

        const nextButton = document.createElement('button');
        nextButton.type = 'button';
        nextButton.className = 'jcp-alt-slider-btn jcp-alt-slider-next';
        nextButton.setAttribute('aria-label', 'Next check-ins');
        nextButton.innerHTML = '&#10095;';

        const cardsViewport = document.createElement('div');
        cardsViewport.className = 'jcp-alt-cards-viewport';

        const cardsWrap = document.createElement('div');
        cardsWrap.className = 'jcp-alt-cards';

        cardsViewport.appendChild(cardsWrap);
        slider.appendChild(prevButton);
        slider.appendChild(cardsViewport);
        slider.appendChild(nextButton);

        root.appendChild(mapEl);
        root.appendChild(slider);

        const cardIndex = new Map();
        const markerIndex = new Map();
        let activeCard = null;

        checkins.forEach((checkin) => {
            if (!checkin || !checkin.id) return;
            const card = buildCard(checkin);
            cardsWrap.appendChild(card);
            cardIndex.set(String(checkin.id), card);
        });

        const focusCard = (checkinId, shouldScroll = true) => {
            const card = cardIndex.get(String(checkinId));
            if (!card) return;

            if (activeCard) activeCard.classList.remove('is-active');
            card.classList.add('is-active');
            activeCard = card;

            if (shouldScroll) {
                card.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'start' });
            }
        };

        await loadGoogleMaps(mapData.googleMapsApiKey || '');
        await loadMarkerClusterer();

        const locations = mapData.locations || {};
        const features = Array.isArray(locations.features) ? locations.features : [];

        const bounds = new google.maps.LatLngBounds();

        const map = new google.maps.Map(mapEl, {
            center: { lat: 38.0, lng: -97.0 },
            zoom: 4,
            mapId: 'f4a15cb6cd4f8d61',
            gestureHandling: 'greedy'
        });

        const markers = [];

        features.forEach((feature) => {
            if (!feature || !feature.geometry || !feature.properties) return;
            const coords = feature.geometry.coordinates || [];
            const checkinId = feature.properties.checkinId;
            if (!checkinId || coords.length < 2) return;

            const position = { lat: coords[1], lng: coords[0] };
            bounds.extend(position);

            const marker = new google.maps.Marker({
                position,
                map
            });

            marker.addListener('click', () => {
                focusCard(checkinId);
            });

            markers.push(marker);
            markerIndex.set(String(checkinId), marker);
        });

        if (markers.length > 1 && window.MarkerClusterer) {
            new MarkerClusterer({ map, markers });
        }

        if (!bounds.isEmpty()) {
            map.fitBounds(bounds);
        }

        cardsWrap.addEventListener('click', (event) => {
            const card = event.target.closest('.jcp-alt-checkin-card');
            if (!card || !cardsWrap.contains(card)) return;

            const checkinId = card.dataset.checkinId;
            focusCard(checkinId, false);

            const marker = markerIndex.get(String(checkinId));
            if (marker) {
                map.panTo(marker.getPosition());
                map.setZoom(Math.max(map.getZoom(), 12));
            }
        });

        cardsWrap.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter' && event.key !== ' ') return;
            const card = event.target.closest('.jcp-alt-checkin-card');
            if (!card) return;
            event.preventDefault();
            card.click();
        });

        const updateButtonState = () => {
            const maxScroll = cardsViewport.scrollWidth - cardsViewport.clientWidth;
            const canScroll = maxScroll > 1;
            prevButton.disabled = !canScroll || cardsViewport.scrollLeft <= 0;
            nextButton.disabled = !canScroll || cardsViewport.scrollLeft >= maxScroll - 1;
            slider.classList.toggle('is-scrollable', canScroll);
            cardsViewport.classList.add('is-clean');
        };

        const scrollByCard = (direction) => {
            const card = cardsWrap.querySelector('.jcp-alt-checkin-card');
            const cardWidth = card ? card.getBoundingClientRect().width : cardsViewport.clientWidth / 2;
            cardsViewport.scrollBy({ left: direction * (cardWidth + 18), behavior: 'smooth' });
        };

        prevButton.addEventListener('click', () => scrollByCard(-1));
        nextButton.addEventListener('click', () => scrollByCard(1));
        cardsViewport.addEventListener('scroll', updateButtonState, { passive: true });
        window.addEventListener('resize', updateButtonState);
        updateButtonState();
    };

    const initAll = () => {
        const roots = document.querySelectorAll(ROOT_SELECTOR);
        roots.forEach((root) => {
            initAltLayout(root);
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();
