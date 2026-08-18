import { escapeHtml } from '../utils.js';

// Mirrors JobCaptureProShortcodes::render_error_message() (without the WP_DEBUG block)
export function renderErrorMessage(message) {
    return `<div class="jobcapturepro-error" role="alert">
                <p>${escapeHtml(message)}</p>
            </div>`;
}

// Mirrors JobCaptureProShortcodes::render_no_checkins_message()
export function renderNoCheckinsMessage() {
    return '<p class="jobcapturepro-no-checkins">There are no job check-ins to display</p>';
}
