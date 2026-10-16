/**
 * JobCapturePro embeddable widget: <jcp-widget>.
 *
 * Framework-agnostic equivalent of the WordPress shortcodes, for
 * non-WordPress sites (Wix, Squarespace, plain HTML, ...):
 *
 *   <script src="https://.../jcp-embed.min.js" async></script>
 *   <jcp-widget component="combined" companyid="..." api-key="..."></jcp-widget>
 *
 * component: combined | checkins | map | company-info | checkin
 * All other attributes are forwarded to the API as query params with
 * dashes stripped, mirroring WP shortcode atts (companyid / company-id,
 * checkinid / checkin-id, pagesize, state, ...).
 */

import cssText from '../css/tailwind.css?inline-css';

import { createApiClient } from '../core/api-client.js';
import { renderErrorMessage, renderNoCheckinsMessage } from '../core/render/messages.js';
import { renderCheckinCard, renderCardSkeleton } from '../core/render/checkin-card.js';
import { renderCheckinsSlider } from '../core/render/checkins-slider.js';
import { renderCompanyInfo } from '../core/render/company-info.js';
import { renderSingleCheckin } from '../core/render/single-checkin.js';
import { renderPoweredByFooter } from '../core/render/footer.js';
import { renderMapContainer, buildMapConfig } from '../core/map-data.js';
import { initCheckinsSlider } from '../core/slider.js';
import { createCheckinsPagination } from '../core/pagination.js';
import { initCheckinsMap } from '../core/map.js';
import { initCardCarousels, initDescriptionToggles } from '../core/card-interactions.js';

const DEFAULT_API_BASE = 'https://production--jobcapturepro.us-east4.hosted.app/api/';

// The widget renders into the light DOM (matching how the WP plugin
// behaves), so its stylesheet is injected once per document.
function injectStylesOnce() {
    if (document.getElementById('jcp-embed-styles')) return;
    const style = document.createElement('style');
    style.id = 'jcp-embed-styles';
    style.textContent = cssText;
    document.head.appendChild(style);
}

// Attributes that configure the element itself and are never forwarded
// to the API as query params.
const RESERVED_ATTRIBUTES = new Set([
    'component', 'api-key', 'api-base',
    'class', 'style', 'id', 'slot', 'part', 'tabindex', 'hidden',
]);

class JcpWidget extends HTMLElement {

    async connectedCallback() {
        // connectedCallback re-fires when the element is moved in the DOM
        if (this._jcpInitialized) return;
        this._jcpInitialized = true;

        injectStylesOnce();
        this.style.display = 'block';

        const apiKey = this.getAttribute('api-key');
        if (!apiKey) {
            this.innerHTML = renderErrorMessage('JobCapturePro: the api-key attribute is required.');
            return;
        }

        const baseApiUrl = this.getAttribute('api-base') || DEFAULT_API_BASE;
        const component = this.getAttribute('component') || 'combined';

        this._api = createApiClient({ baseUrl: baseApiUrl, apiKey });
        this._baseApiUrl = baseApiUrl;
        this._apiKey = apiKey;

        this.innerHTML = `<div class="jcp:flex jcp:justify-center jcp:py-8">${renderCardSkeleton()}</div>`;

        try {
            switch (component) {
                case 'combined':
                    await this._renderCombined();
                    break;
                case 'checkins':
                    await this._renderCheckins();
                    break;
                case 'checkin':
                    await this._renderCheckin();
                    break;
                case 'map':
                    await this._renderMap();
                    break;
                case 'company-info':
                    await this._renderCompanyInfo();
                    break;
                default:
                    this.innerHTML = renderErrorMessage(`JobCapturePro: unknown component "${component}".`);
            }
        } catch (error) {
            console.error('JobCapturePro widget error:', error);
            this.innerHTML = renderErrorMessage('Content is temporarily unavailable. Please try again later.');
        }
    }

    // Non-reserved attributes → API query atts, dashes stripped so
    // company-id / companyid both mirror the WP shortcode att names.
    _collectAtts() {
        const atts = {};
        for (const { name, value } of this.attributes) {
            if (RESERVED_ATTRIBUTES.has(name)) continue;
            if (name.startsWith('data-') || name.startsWith('aria-') || name.startsWith('on')) continue;
            atts[name.replace(/-/g, '')] = value;
        }
        return atts;
    }

    // Mirrors the company lookup in get_map()/get_combined_components():
    // direct endpoint when a company id is given, default company otherwise.
    async _fetchCompanyInfo(atts) {
        if (atts.companyid) {
            return await this._api.get(`companies/${encodeURIComponent(atts.companyid)}`, {});
        }
        return await this._api.get('companies', atts);
    }

    // Slider + infinite pagination wiring shared by combined/checkins paths
    _initSliderWithPagination({ companyId, scAtts }) {
        let sliderControl = null;

        const pagination = createCheckinsPagination({
            track: this.querySelector('.swiper-wrapper'),
            companyId,
            baseApiUrl: this._baseApiUrl,
            apiKey: this._apiKey,
            scAtts,
            onAppended: () => {
                if (sliderControl) sliderControl.refresh();
            },
        });

        sliderControl = initCheckinsSlider(this, {
            onReachEnd: () => pagination.autoFetch(),
        });
    }

    _initMapIfPresent(mapData, companyInfo, scAtts) {
        const mapEl = this.querySelector('.jcp-map');
        if (!mapEl) return;
        initCheckinsMap(mapEl, buildMapConfig(mapData, companyInfo, {
            baseApiUrl: this._baseApiUrl,
            apiKey: this._apiKey,
            scAtts,
        }));
    }

    // Mirrors get_combined_components(), except that combined deliberately
    // renders NO company-info header (product decision, Sep 2026). WP's
    // combined still calls render_company_info(), but on live sites the
    // header collapses to '' because the default `companies` endpoint
    // returns a paginated list without top-level name/address — the
    // header-less rendering is the intended behavior.
    async _renderCombined() {
        const atts = this._collectAtts();
        if (!atts.pagesize) atts.pagesize = '25';

        const companyInfo = await this._fetchCompanyInfo(atts);
        if (!companyInfo) {
            this.innerHTML = renderErrorMessage('Company information is not available at this time.');
            return;
        }

        const checkinsData = await this._api.get('checkins', atts);
        if (!checkinsData) {
            this.innerHTML = renderErrorMessage('Unable to load checkins data at this time. Please try again later.');
            return;
        }
        const checkins = Array.isArray(checkinsData.checkins) ? checkinsData.checkins : [];

        const mapData = await this._api.get('map', { ...atts, pageSize: 100 });
        if (!mapData) {
            this.innerHTML = renderErrorMessage('Unable to load map data at this time. Please try again later.');
            return;
        }

        if (checkins.length === 0 && (mapData.total ?? 0) === 0) {
            this.innerHTML = renderNoCheckinsMessage();
            return;
        }

        const hasMap = (mapData.locations?.features ?? []).length > 0;

        // Mirrors render_checkins_conditionally(): a single check-in
        // requested by id renders the detail view instead of the slider
        const singleView = atts.checkinid && checkins.length === 1;
        const checkinsHtml = singleView
            ? renderSingleCheckin(checkins[0], companyInfo)
            : renderCheckinsSlider(checkins.map(renderCheckinCard).join(''));

        this.innerHTML = `<div class="jcp-combined-components">`
            + (hasMap ? renderMapContainer() : '')
            + checkinsHtml
            + renderPoweredByFooter()
            + `</div>`;

        if (hasMap) this._initMapIfPresent(mapData, companyInfo, atts);
        if (!singleView) {
            this._initSliderWithPagination({ companyId: companyInfo?.id ?? null, scAtts: atts });
        }
    }

    // Mirrors get_all_checkins()
    async _renderCheckins() {
        const atts = this._collectAtts();
        if (!atts.pagesize) atts.pagesize = '25';

        const data = await this._api.get('checkins', atts);
        if (!data) {
            this.innerHTML = renderErrorMessage('Unable to load checkins at this time. Please try again later.');
            return;
        }

        const checkins = Array.isArray(data.checkins) ? data.checkins
            : (Array.isArray(data) ? data : []);

        if (checkins.length === 0) {
            this.innerHTML = renderNoCheckinsMessage();
            return;
        }

        if (atts.checkinid && checkins.length === 1) {
            this.innerHTML = renderSingleCheckin(checkins[0]);
            initCardCarousels(this);
            initDescriptionToggles(this);
            return;
        }

        this.innerHTML = renderCheckinsSlider(checkins.map(renderCheckinCard).join(''));
        this._initSliderWithPagination({ companyId: atts.companyid ?? null, scAtts: atts });
    }

    // Mirrors get_checkin(): a one-card slider for a specific check-in
    async _renderCheckin() {
        const atts = this._collectAtts();

        if (!atts.checkinid) {
            this.innerHTML = renderErrorMessage('No valid checkin ID provided.');
            return;
        }

        const checkin = await this._api.get(`checkins/${encodeURIComponent(atts.checkinid)}`, {});
        if (!checkin) {
            this.innerHTML = renderErrorMessage('Unable to load checkin data at this time. Please try again later.');
            return;
        }

        if (Object.keys(checkin).length === 0) {
            this.innerHTML = renderNoCheckinsMessage();
            return;
        }

        this.innerHTML = renderCheckinsSlider(renderCheckinCard(checkin));
        // No pagination for a deliberately single check-in
        initCheckinsSlider(this);
    }

    // Mirrors get_map()
    async _renderMap() {
        const atts = this._collectAtts();

        const companyInfo = await this._fetchCompanyInfo(atts);

        const mapData = await this._api.get('map', { ...atts, pageSize: 100 });
        if (!mapData) {
            this.innerHTML = renderErrorMessage('Unable to load map data at this time. Please try again later.');
            return;
        }

        if ((mapData.total ?? 0) === 0) {
            this.innerHTML = renderNoCheckinsMessage();
            return;
        }

        if ((mapData.locations?.features ?? []).length === 0) {
            this.innerHTML = '';
            return;
        }

        this.innerHTML = renderMapContainer();
        this._initMapIfPresent(mapData, companyInfo, atts);
    }

    // Mirrors get_company_info()
    async _renderCompanyInfo() {
        const atts = this._collectAtts();

        if (!atts.companyid) {
            this.innerHTML = renderErrorMessage('No valid company ID provided.');
            return;
        }

        const companyData = await this._api.get(`companies/${encodeURIComponent(atts.companyid)}`, {});
        if (!companyData) {
            this.innerHTML = renderErrorMessage('Unable to load company information at this time. Please try again later.');
            return;
        }

        this.innerHTML = renderCompanyInfo(companyData);
    }
}

if (!customElements.get('jcp-widget')) {
    customElements.define('jcp-widget', JcpWidget);
}
