/**
 * Shared helpers for rendering check-in content.
 * Escaping mirrors WordPress esc_html() so client-rendered markup
 * matches the server-rendered PHP templates.
 */

export function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (ch) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    }[ch]));
}

export function nl2br(text) {
    return escapeHtml(text).replace(/\n/g, '<br>');
}

// UTC formatter matching PHP's gmdate('F j, Y') used in checkin-card.php,
// so cards appended client-side render the same date as server-rendered ones.
const DATE_FORMAT = new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    timeZone: 'UTC',
});

export function formatCheckinDate(timestamp) {
    if (!timestamp) return null;
    const date = new Date(timestamp);
    if (isNaN(date.getTime())) return null;
    return DATE_FORMAT.format(date);
}

// "123 Main St, Venice, FL 34285" → "Venice, FL" (mirrors checkin-card.php)
export function cityStateFromAddress(address) {
    const parts = String(address || '').split(',');
    const city = (parts[1] || '').trim();
    const state = (parts[2] || '').trim();
    const stateAbbr = state.length > 2 ? state.substring(0, 2) : state;
    return `${city}, ${stateAbbr}`;
}
