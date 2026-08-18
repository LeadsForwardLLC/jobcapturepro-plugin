/**
 * Minimal JobCapturePro API client for browser use.
 * Mirrors JobCaptureProShortcodes::fetch_api_data(): per-endpoint default
 * query params merged under caller atts, Bearer auth, null on any failure.
 */

// Default query parameters per endpoint prefix (atts can override them)
const ENDPOINT_DEFAULTS = {
    checkins: { minImages: '1', strict: 'false', sortBy: 'jobCompletedDate', sortOrder: 'DESC' },
};

export function createApiClient({ baseUrl, apiKey }) {
    const base = baseUrl.endsWith('/') ? baseUrl : baseUrl + '/';

    /**
     * GET an endpoint with atts as query params. Returns parsed JSON
     * data, or null on any error (network, HTTP, or API-level).
     */
    async function get(endpoint, atts = {}) {
        let merged = { ...atts };
        for (const [prefix, defaults] of Object.entries(ENDPOINT_DEFAULTS)) {
            if (endpoint.startsWith(prefix)) {
                merged = { ...defaults, ...atts };
                break;
            }
        }

        const url = new URL(endpoint, base);
        Object.entries(merged).forEach(([key, value]) => {
            if (value === null || value === undefined) return;
            if (String(value).trim() === '') return;
            url.searchParams.set(key, String(value));
        });

        try {
            const response = await fetch(url.toString(), {
                headers: { 'Authorization': `Bearer ${apiKey}` },
            });

            if (!response.ok) {
                console.error(`JobCapturePro API error: HTTP ${response.status} for ${endpoint}`);
                return null;
            }

            const data = await response.json();

            if (data && data.error) {
                console.error(`JobCapturePro API error: ${data.error}`);
                return null;
            }

            return data;
        } catch (error) {
            console.error('JobCapturePro API request failed:', error);
            return null;
        }
    }

    return { get, base, apiKey };
}
