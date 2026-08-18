import { renderCheckinCard } from './render/checkin-card.js';

/**
 * Infinite-scroll pagination for the check-ins slider.
 * Fetches subsequent /checkins pages directly from the JobCapturePro API
 * and appends rendered cards to the slider track.
 */
export function createCheckinsPagination({ track, companyId, baseApiUrl, apiKey, scAtts = {}, onAppended }) {
    let currentPage = 1;
    let isFetching = false;
    let hasMore = true;

    function loadNextCheckinsPage() {
        isFetching = true;

        const params = new URLSearchParams();
        params.set('page', String(currentPage + 1));

        if (companyId) {
            params.set('companyId', String(companyId));
        }

        // Default params for checkins (can be overridden by shortcode atts below)
        params.set('minImages', '1');
        params.set('strict', 'false');
        params.set('sortBy', 'jobCompletedDate');
        params.set('sortOrder', 'DESC');

        // Append any shortcode attributes passed through
        Object.entries(scAtts).forEach(([key, value]) => {
            if (value === null || value === undefined) return;
            if (String(value).trim() !== '') params.set(key, String(value));
        });

        let base = baseApiUrl;
        if (!base.endsWith('/')) base += '/';

        const url = new URL('checkins', base);
        url.search = params.toString();

        fetch(url.toString(), {
                headers: { 'Authorization': `Bearer ${apiKey}` }
            })
            .then((response) => response.json())
            .then((data) => {
                const checkins = data.checkins;

                if (checkins && checkins.length > 0) {
                    checkins.forEach((checkin) => {
                        track.insertAdjacentHTML('beforeend', renderCheckinCard(checkin));
                    });

                    if (typeof onAppended === 'function') onAppended();
                }

                if (data.hasNext) {
                    currentPage++;
                } else {
                    hasMore = false;
                }

                isFetching = false;
            })
            .catch((error) => {
                console.error('Error fetching check-ins:', error);
                isFetching = false;
            });
    }

    return {
        // Guarded trigger — safe to call repeatedly (e.g. from Swiper reachEnd)
        autoFetch() {
            if (isFetching || !hasMore) return;
            loadNextCheckinsPage();
        },
    };
}
