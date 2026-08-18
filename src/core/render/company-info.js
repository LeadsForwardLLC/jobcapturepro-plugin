import { lucideSvg, Star } from '../icons.js';
import { escapeHtml } from '../utils.js';

/**
 * Company info header; mirrors templates/company-info.php.
 * Returns '' when name or address is missing, matching
 * JobCaptureProTemplates::render_company_info().
 */
export function renderCompanyInfo(companyInfo) {
    if (!companyInfo || !companyInfo.name || !companyInfo.address) {
        return '';
    }

    const hasPhone = !!companyInfo.tn;
    const hasUrl = !!companyInfo.url;

    let displayUrl = '';
    if (hasUrl) {
        let host;
        try {
            host = new URL(companyInfo.url).host || companyInfo.url;
        } catch {
            host = companyInfo.url;
        }
        displayUrl = host.replace(/^www\./i, '');
    }

    const phoneHtml = hasPhone ? `
                <p class="jcp:m-0 jcp:text-base jcp:text-[#444] jcp:flex jcp:items-center"><strong>&nbsp;&middot;&nbsp;</strong><a class="jcp:text-[#111] jcp:no-underline jcp:hover:underline" href="tel:${escapeHtml(String(companyInfo.tn).replace(/[^0-9]/g, ''))}">${escapeHtml(companyInfo.phoneNumberString)}</a></p>` : '';

    const urlHtml = hasUrl ? `
                <p class="jcp:m-0 jcp:text-base jcp:text-[#444] jcp:flex jcp:items-center"><strong>&nbsp;&middot;&nbsp;</strong><a class="jcp:text-[#111] jcp:no-underline jcp:hover:underline" href="${escapeHtml(companyInfo.url)}" target="_blank" rel="noopener noreferrer">${escapeHtml(displayUrl)}</a></p>` : '';

    const noContactHtml = (!hasPhone && !hasUrl)
        ? '<p class="jcp-no-contact-info jcp:m-0 jcp:text-[#999] jcp:italic">Contact No. and Website information not available</p>'
        : '';

    const logoHtml = companyInfo.logoUrl ? `
            <img class="jcp-company-logo__img jcp:max-w-40 jcp:md:max-w-[200px] jcp:h-auto jcp:object-contain" src="${escapeHtml(companyInfo.logoUrl)}" alt="${escapeHtml(companyInfo.name)} logo">` : '';

    return `
    <div class="jcp-company-info jcp:flex jcp:flex-col jcp:md:flex-row jcp:items-start jcp:md:items-center jcp:px-4 jcp:md:px-8 jcp:py-8 jcp:md:py-10 jcp:bg-white jcp:border-b jcp:border-[#eee] jcp:gap-6">
        <div class="jcp-company-details jcp:flex-1 jcp:w-full jcp:min-w-0">
            <h2 class="jcp-company-name jcp:text-[#333] jcp:m-0 jcp:text-2xl jcp:md:text-[1.8rem] jcp:font-bold jcp:mb-2">${escapeHtml(companyInfo.name)}</h2>

            <div class="jcp-company-info-text jcp:mb-2">
                <p class="jcp:m-0 jcp:text-base jcp:text-[#444]">${escapeHtml(companyInfo.address)}</p>
            </div>

            <div class="jcp-company-div-2 jcp:flex jcp:flex-wrap jcp:items-center jcp:gap-x-1 jcp:gap-y-1">
                <div class="jcp-company-reviews-text">
                    <p class="jcp:flex jcp:items-center jcp:gap-1.5 jcp:m-0">
                        ${lucideSvg(Star, 14)}
                        <span class="jcp:text-sm jcp:text-[#444]">No Reviews</span>
                    </p>
                </div>
                ${phoneHtml}
                ${urlHtml}
                ${noContactHtml}
            </div>

            <div class="jcp-company-description jcp:mt-2">
                <p class="jcp:m-0 jcp:text-base jcp:text-[#444]">${escapeHtml(companyInfo.description || '')}</p>
            </div>
        </div>

        <div class="jcp-company-actions jcp:flex jcp:flex-row jcp:md:flex-col jcp:items-center jcp:justify-between jcp:md:items-end jcp:md:justify-start jcp:gap-3 jcp:w-full jcp:md:w-auto jcp:shrink-0">
            ${logoHtml}
            <div class="jcp-company-cta jcp:flex jcp:flex-col jcp:items-end jcp:gap-2">
                <a href="${escapeHtml(companyInfo.quoteUrl || '')}" class="jcp-quote-btn">Get a Quote</a>
                <p class="jcp:text-xs jcp:text-gray-400 jcp:m-0">Powered by <a href="https://jobcapturepro.com" class="jcp:font-bold jcp:no-underline jcp:text-black jcp:hover:underline">JobCapturePro</a></p>
            </div>
        </div>
    </div>`;
}
