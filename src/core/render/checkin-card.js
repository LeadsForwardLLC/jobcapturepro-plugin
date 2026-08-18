import { lucideSvg, Calendar, MapPin, ChevronLeft, ChevronRight } from '../icons.js';
import { escapeHtml, nl2br, formatCheckinDate, cityStateFromAddress } from '../utils.js';

/**
 * Consolidated check-in card renderers.
 *
 * renderCheckinCard()    — slider/grid card; mirrors templates/checkin-card.php exactly
 *                          so client-appended cards match server-rendered ones.
 * renderInfoWindowCard() — compact variant used inside Google Maps info windows.
 * renderCardSkeleton()   — loading placeholder.
 */

function buildGallery(imageUrls, { withDimensions = false } = {}) {
    const hasImages = imageUrls.length > 0;
    if (!hasImages) return '';

    const hasGallery = imageUrls.length > 1;
    const dimensionAttrs = withDimensions ? ' width="400" height="260"' : '';

    const slidesHtml = imageUrls.map((url, i) => `
                <div class="jcp-plugin-card__slide ${i === 0 ? 'jcp:opacity-100 jcp:visible' : 'jcp:opacity-0 jcp:invisible'}">
                    <img src="${escapeHtml(url)}" alt=""${dimensionAttrs} loading="${i === 0 ? 'eager' : 'lazy'}">
                </div>`).join('');

    const navHtml = hasGallery ? `
                <button type="button" class="jcp-plugin-card__nav jcp-plugin-card__nav--prev jcp:left-2" aria-label="Previous image">
                    ${lucideSvg(ChevronLeft, 20)}
                </button>
                <button type="button" class="jcp-plugin-card__nav jcp-plugin-card__nav--next jcp:right-2" aria-label="Next image">
                    ${lucideSvg(ChevronRight, 20)}
                </button>
                <div class="jcp-plugin-card__dots">
                    ${imageUrls.map((_, i) => `
                        <button type="button"
                                class="jcp-plugin-card__dot"
                                aria-label="Image ${i + 1}"></button>
                    `).join('')}
                </div>` : '';

    return `
            <div class="jcp-plugin-card__gallery"${hasGallery ? ' data-carousel' : ''}>
                <div class="jcp-plugin-card__gallery-inner">
                    ${slidesHtml}
                </div>
                ${navHtml}
            </div>`;
}

export function renderCheckinCard(checkin) {
    const imageUrls = Array.isArray(checkin.imageUrls) ? checkin.imageUrls : [];
    const formattedDate = formatCheckinDate(checkin.jobCompletedDate || checkin.createdAt) || '';

    return `
        <article class="jcp-plugin-card swiper-slide jcp:group jcp:bg-white jcp:rounded-2xl jcp:p-2 jcp:border jcp:border-[#e5e7eb] jcp:shadow-[0_1px_3px_rgba(0,0,0,0.04),0_4px_12px_-4px_rgba(0,0,0,0.06)] jcp:transition-transform jcp:duration-200 jcp:hover:-translate-y-0.5">
            ${buildGallery(imageUrls, { withDimensions: true })}
            <div class="jcp-plugin-card__body jcp:p-4">
                <p class="jcp-plugin-card__description jcp:line-clamp-4 jcp:overflow-hidden" data-desc-text>${nl2br(checkin.description || '')}</p>
                <button type="button" class="jcp-plugin-card__toggle jcp:border-0 jcp:bg-transparent jcp:text-accent jcp:text-sm jcp:font-semibold jcp:p-0 jcp:cursor-pointer jcp:hover:underline" data-desc-toggle hidden aria-expanded="false">Read more</button>
                <hr class="jcp:border-0 jcp:border-t jcp:border-[#e5e7eb] jcp:my-4">
                <div class="jcp-plugin-card__meta">
                    <span class="jcp-plugin-card__meta-item jcp-plugin-card__date">
                        ${lucideSvg(Calendar, 14, 'jcp-plugin-card__meta-icon')}
                        ${escapeHtml(formattedDate)}
                    </span>
                    <span class="jcp-plugin-card__meta-item jcp-plugin-card__location">
                        ${lucideSvg(MapPin, 14, 'jcp-plugin-card__meta-icon')}
                        Near ${escapeHtml(cityStateFromAddress(checkin.address))}
                    </span>
                </div>
            </div>
        </article>`;
}

export function renderInfoWindowCard(checkin) {
    const imageUrls = Array.isArray(checkin.imageUrls) ? checkin.imageUrls : [];
    const dateDisplay = formatCheckinDate(checkin.jobCompletedDate || checkin.createdAt) || 'Date not available';

    return `
        <article class="jcp-plugin-card jcp:min-w-0 jcp:bg-white jcp:overflow-hidden jcp:max-w-[350px]">
            ${buildGallery(imageUrls)}
            <div class="jcp-plugin-card__body jcp:pt-4 jcp:px-1">
                ${checkin.description ? `
                    <p class="jcp-plugin-card__description">${nl2br(checkin.description)}</p>
                ` : ''}
                <hr class="jcp:border-0 jcp:border-t jcp:border-[#e5e7eb] jcp:my-4">
                <div class="jcp-plugin-card__meta">
                    <span class="jcp-plugin-card__meta-item jcp-plugin-card__date">
                        ${lucideSvg(Calendar, 14, 'jcp-plugin-card__meta-icon')}
                        ${escapeHtml(dateDisplay)}
                    </span>
                    ${checkin.address ? `
                        <span class="jcp-plugin-card__meta-item jcp-plugin-card__location">
                            ${lucideSvg(MapPin, 14, 'jcp-plugin-card__meta-icon')}
                            Near ${escapeHtml(cityStateFromAddress(checkin.address))}
                        </span>
                    ` : ''}
                </div>
            </div>
        </article>`;
}

export function renderCardSkeleton() {
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
