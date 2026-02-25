document.addEventListener("DOMContentLoaded", function () {
    // Current page tracker
    let currentPage = 1;

    // Fetch guard — prevents duplicate concurrent requests
    let isFetching = false;

    // No more pages flag
    let hasMore = true;

    const sliderTrack = document.getElementById("jcp-plugin-slider-track");

    // Return early if slider track not found
    if (!sliderTrack) return;

    // Exposed so slider.js can trigger auto-fetch when near the end
    window.jcpSliderAutoFetch = function () {
        if (isFetching || !hasMore) return;
        loadNextCheckinsPage();
    };

    // Load next page of check-ins
    function loadNextCheckinsPage() {
        isFetching = true;

        // Build fetch URL with URLSearchParams using localized shortcode atts
        const params = new URLSearchParams();

        // page param
        params.set('page', String(currentPage + 1));

        // companyId if provided
        if (jobcaptureproLoadMoreData.companyId) {
            params.set('companyId', String(jobcaptureproLoadMoreData.companyId));
        }

        // Append any shortcode attributes passed via localization
        const scAtts = jobcaptureproLoadMoreData.scAtts || {};
        Object.entries(scAtts).forEach(([key, value]) => {
            if (value === null || value === undefined) return;
            if (value.trim() !== '') params.append(key, String(value));
        });

        // Get base API URL and ensure it ends with a slash
        let base = jobcaptureproLoadMoreData.baseApiUrl;
        if (!base.endsWith('/')) base += '/';

        // Construct full URL
        const url = new URL('checkins', base);
        url.search = params.toString();

        fetch(url.toString())
            .then(response => response.json())
            .then(data => {
                const checkins = data.checkins;

                // Append new check-ins to the slider track
                if (checkins && checkins.length > 0) {
                    checkins.forEach(function (checkin) {
                        sliderTrack.insertAdjacentHTML('beforeend', renderCheckinCard(checkin));
                    });

                    // Refresh slider — recount cards, recalculate widths, init new carousels/toggles
                    if (window.jcpSliderRefresh) {
                        window.jcpSliderRefresh();
                    }
                }

                if (data.hasNext) {
                    currentPage++;
                } else {
                    hasMore = false;
                }

                isFetching = false;
            })
            .catch(error => {
                console.error('Error fetching check-ins:', error);
                isFetching = false;
            });
    }

    // Render a single check-in card — mirrors checkin-card.php exactly
    function renderCheckinCard(checkin) {
        const addressParts = (checkin.address || '').split(',');
        const city         = (addressParts[1] || '').trim();
        const state        = (addressParts[2] || '').trim();
        const stateAbbr    = state.length > 2 ? state.substring(0, 2) : state;

        const timestamp = new Date(checkin.createdAt);
        const formattedDate = timestamp.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        const escape = (text) => {
            const div = document.createElement('div');
            div.textContent = String(text);
            return div.innerHTML;
        };

        const nl2br = (text) => escape(text).replace(/\n/g, '<br>');

        const imageUrls  = checkin.imageUrls && Array.isArray(checkin.imageUrls) ? checkin.imageUrls : [];
        const hasImages  = imageUrls.length > 0;
        const hasGallery = imageUrls.length > 1;

        // Gallery HTML
        let galleryHtml = '';
        if (hasImages) {
            const slidesHtml = imageUrls.map((url, i) => `
                <div class="jcp-plugin-card__slide${i === 0 ? ' is-active' : ''}">
                    <img src="${escape(url)}" alt="" width="400" height="260" loading="${i === 0 ? 'eager' : 'lazy'}">
                </div>`).join('');

            const navHtml = hasGallery ? `
                <button type="button" class="jcp-plugin-card__nav jcp-plugin-card__nav--prev" aria-label="Previous image">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
                </button>
                <button type="button" class="jcp-plugin-card__nav jcp-plugin-card__nav--next" aria-label="Next image">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                </button>
                <div class="jcp-plugin-card__dots">
                    ${imageUrls.map((_, i) => `
                        <button type="button"
                                class="jcp-plugin-card__dot${i === 0 ? ' is-active' : ''}"
                                aria-label="Image ${i + 1}"></button>
                    `).join('')}
                </div>` : '';

            galleryHtml = `
                <div class="jcp-plugin-card__gallery"${hasGallery ? ' data-carousel' : ''}>
                    <div class="jcp-plugin-card__gallery-inner">
                        ${slidesHtml}
                    </div>
                    ${navHtml}
                </div>`;
        }

        return `
        <article class="jcp-plugin-card">
            ${galleryHtml}
            <div class="jcp-plugin-card__body">
                <p class="jcp-plugin-card__description" data-desc-text>${nl2br(checkin.description || '')}</p>
                <button type="button" class="jcp-plugin-card__toggle" data-desc-toggle hidden aria-expanded="false">Read more</button>
                <div class="jcp-plugin-card__meta">
                    <span class="jcp-plugin-card__meta-item jcp-plugin-card__date">
                        <svg class="jcp-plugin-card__meta-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M8 2v4"/><path d="M16 2v4"/>
                            <rect x="3" y="4" width="18" height="18" rx="2"/>
                            <path d="M3 10h18"/>
                        </svg>
                        ${escape(formattedDate)}
                    </span>
                    <span class="jcp-plugin-card__meta-item jcp-plugin-card__location">
                        <svg class="jcp-plugin-card__meta-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M20 10c0 5-8 12-8 12s-8-7-8-12a8 8 0 1 1 16 0Z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        Near ${escape(city + ', ' + stateAbbr)}
                    </span>
                </div>
            </div>
        </article>`;
    }
});
