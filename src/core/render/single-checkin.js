import { lucideSvg, Calendar, MapPin, User, Star, CircleCheck, Zap } from '../icons.js';
import { escapeHtml, formatCheckinDate } from '../utils.js';

/**
 * Single check-in detail view; mirrors templates/single-checkin.php,
 * including its demo fallback content and feature toggles.
 * Toggles mirror JobCaptureProTemplates::should_show_feature() — controlled
 * at code level, all off until the backend features exist.
 */
const FEATURE_TOGGLES = {
    show_customer_reviews: false,
    show_star_ratings: false,
    show_verified_badges: false,
    show_company_stats: false,
    show_company_reviews: false,
};

function shouldShowFeature(featureName, dataExists) {
    return !!FEATURE_TOGGLES[featureName] && !!dataExists;
}

function listItem(html) {
    return `<li class="jcp:py-1 jcp:text-[0.9rem] jcp:text-[#333] jcp:flex jcp:items-center jcp:gap-1">${html}</li>`;
}

export function renderSingleCheckin(checkin, companyInfo = {}) {
    const rating = checkin.rating ? Math.min(5, Math.max(1, parseInt(checkin.rating, 10))) : 0;

    const checkinDate = formatCheckinDate(checkin.jobCompletedDate || checkin.createdAt) || 'July 6, 2025';
    const techName = checkin.assignedUser?.name ?? 'Chris (Tech)';
    const location = checkin.address ?? 'Venice, FL';
    const description = checkin.description ?? 'Roof soft-washed to remove algae and restore curb appeal. This 2-story home was cleaned using a low-pressure rinse method safe for shingles and gutters. Job completed in under 2 hours.';
    const title = checkin.title ?? 'Roof Soft Wash in Venice, FL';

    const relatedCheckins = Array.isArray(checkin.related_checkins) ? checkin.related_checkins : [];
    const testimonials = Array.isArray(companyInfo?.testimonials) ? companyInfo.testimonials : [];
    const serviceTags = Array.isArray(checkin.service_tags) ? checkin.service_tags : [];

    const showReviews = shouldShowFeature('show_customer_reviews', !!checkin.customer_review);
    const showFallbackReview = shouldShowFeature('show_customer_reviews', true);
    const showRatings = shouldShowFeature('show_star_ratings', !!checkin.rating);
    const showFallbackRating = shouldShowFeature('show_star_ratings', true);
    const showVerified = shouldShowFeature('show_verified_badges', !!checkin.is_verified);
    const showVerifiedFallback = shouldShowFeature('show_verified_badges', true);

    // Header images
    const heroImage = checkin.imageUrls?.[0];
    const heroThumbHtml = heroImage
        ? `<img class="jcp-hero-img jcp:w-20 jcp:h-20 jcp:object-cover jcp:rounded-full" src="${escapeHtml(heroImage)}" alt="${escapeHtml(title)}">`
        : '';
    const heroFullHtml = heroImage
        ? `<img class="jcp-hero-img jcp:my-4 jcp:shadow-[0_2px_8px_rgba(0,0,0,0.1)] jcp:w-full jcp:h-auto jcp:object-cover jcp:border-b jcp:border-[#eee] jcp:rounded-xl jcp:mb-6" src="${escapeHtml(checkin.imageUrls?.[1] || heroImage)}" alt="${escapeHtml(title)}">`
        : '';

    // Review section
    let reviewHtml = '';
    if (showReviews && checkin.customer_review) {
        reviewHtml = `
                <div class="jcp-checkin-review">
                    <h2 class="jcp-section-title">Review</h2>
                    <div class="jcp-review-content jcp:mb-4 jcp:bg-[#fef9c3] jcp:border-l-4 jcp:border-[#facc15] jcp:px-4 jcp:py-3 jcp:rounded-lg jcp:leading-[1.5]">
                        <p class="jcp-review-text jcp:italic jcp:m-0 jcp:mb-1">"${escapeHtml(checkin.customer_review.text)}"</p>
                        <p class="jcp-review-author jcp:font-bold jcp:text-right jcp:m-0">&ndash; ${escapeHtml(checkin.customer_review.author)}</p>
                    </div>
                </div>`;
    } else if (showFallbackReview) {
        reviewHtml = `
                <div class="jcp-checkin-review">
                    <h2 class="jcp-section-title">Review</h2>
                    <div class="jcp-review-content jcp:mb-4 jcp:bg-[#fef9c3] jcp:border-l-4 jcp:border-[#facc15] jcp:px-4 jcp:py-3 jcp:rounded-lg jcp:leading-[1.5]">
                        <p class="jcp-review-text jcp:italic jcp:m-0 jcp:mb-1">"Looks brand new! Friendly, professional, fast. Highly recommend."</p>
                        <p class="jcp-review-author jcp:font-bold jcp:text-right jcp:m-0">&ndash; Danielle P.</p>
                    </div>
                </div>`;
    }

    // Ratings section
    const starIcon = lucideSvg(Star, 18, 'jcp:fill-[#facc15] jcp:text-[#facc15]');
    let ratingsHtml = '';
    if (showRatings && checkin.rating) {
        const stars = Array.from({ length: 5 }, (_, i) =>
            `<span class="jcp-star-${i + 1 <= rating ? 'filled' : 'empty'}">${starIcon}</span>`).join('');
        ratingsHtml = `<div class="jcp-job-reviews jcp:flex jcp:gap-0.5 jcp:my-1 jcp:justify-end">${stars}</div>`;
    } else if (showFallbackRating) {
        const stars = Array.from({ length: 5 }, () => `<span>${starIcon}</span>`).join('');
        ratingsHtml = `<div class="jcp-job-reviews jcp:flex jcp:gap-0.5 jcp:my-1 jcp:justify-end">${stars}</div>`;
    }

    const verifiedHtml = (showVerified || showVerifiedFallback) ? `
                <div class="jcp-verified-badge jcp:bg-[#e6f4ea] jcp:text-[#1e7f3e] jcp:px-3 jcp:py-1.5 jcp:text-[0.85rem] jcp:rounded-full jcp:font-medium jcp:inline-flex jcp:items-center jcp:gap-1.5 jcp:w-full jcp:mt-5 jcp:mb-4">
                    ${lucideSvg(CircleCheck, 18, 'jcp:text-[#1e7f3e] jcp:shrink-0')} Verified Job Check-In</div>` : '';

    // Related check-ins
    const zapIcon = lucideSvg(Zap, 14, 'jcp:text-[#999] jcp:shrink-0');
    const relatedItems = relatedCheckins.length > 0
        ? relatedCheckins.map((related) => listItem(`${zapIcon} ${escapeHtml(related.title)}`)).join('')
        : [
            `${zapIcon} Driveway Pressure Wash &ndash; Sarasota`,
            `${zapIcon} Pool Deck Cleaning &ndash; Nokomis`,
            `${zapIcon} Window Cleaning &ndash; Lakewood Ranch`,
        ].map(listItem).join('');

    // Testimonials
    const testimonialItems = testimonials.length > 0
        ? testimonials.map((t) => `<li class="jcp:py-1 jcp:text-[0.9rem] jcp:text-[#333]">"${escapeHtml(t.text)}" &ndash; ${escapeHtml(t.author)}</li>`).join('')
        : `<li class="jcp:py-1 jcp:text-[0.9rem] jcp:text-[#333]">"Cleaned it like new in 2 hours." &ndash; Brian M.</li>
           <li class="jcp:py-1 jcp:text-[0.9rem] jcp:text-[#333]">"Didn't even need to be home." &ndash; Linda R.</li>
           <li class="jcp:py-1 jcp:text-[0.9rem] jcp:text-[#333]">"No upsells. Just results." &ndash; Mark D.</li>`;

    // Service tags
    const tagsTitle = serviceTags.length > 0 ? 'Service Tags' : 'Nearby Service Tags';
    const tagItems = serviceTags.length > 0
        ? serviceTags.map((tag) => `<span class="jcp-tag">${escapeHtml(tag)}</span>`).join('')
        : `<span class="jcp-tag">Venice, FL</span>
           <span class="jcp-tag">Roof Cleaning</span>
           <span class="jcp-tag">Soft Wash</span>
           <span class="jcp-tag">Exterior Algae</span>`;

    return `
    <div class="jcp-single-checkin jcp:mx-auto jcp:p-5 jcp:text-[#333] jcp:leading-[1.5] jcp:bg-[#f9f9f9]">
        <div class="jcp-single-content-block">
            <div class="jcp-flex-div jcp:grid jcp:grid-cols-[1fr_360px] jcp:gap-8 jcp:max-w-[1200px] jcp:mx-20 jcp:px-8 jcp:items-start jcp:border-b jcp:border-[#eee] jcp:pb-12">
                <div class="jcp-checkin-header jcp:mb-5 jcp:bg-white jcp:rounded-2xl jcp:p-8 jcp:shadow-[0_6px_20px_rgba(0,0,0,0.05)]">
                    <div class="jcp-title-container jcp:flex jcp:items-center jcp:gap-4">
                        ${heroThumbHtml}
                        <h1 class="jcp:text-2xl jcp:m-0 jcp:mb-1 jcp:text-[#222]">${escapeHtml(title)}</h1>
                    </div>

                    ${heroFullHtml}

                    <div class="jcp-checkin-meta jcp:flex jcp:gap-4 jcp:text-sm jcp:text-[#666] jcp:mb-4 jcp:justify-between">
                        <span class="jcp-checkin-date jcp:flex jcp:items-center">
                            ${lucideSvg(Calendar, 16, 'jcp:mr-1.5 jcp:text-[#999] jcp:shrink-0')}${escapeHtml(checkinDate)}
                        </span>
                        <span class="jcp-checkin-tech">
                            ${lucideSvg(User, 16, 'jcp:mr-1.5 jcp:text-[#999] jcp:shrink-0')}${escapeHtml(techName)}
                        </span>
                        <span class="jcp-checkin-location">
                            ${lucideSvg(MapPin, 16, 'jcp:mr-1.5 jcp:text-[#999] jcp:shrink-0')}${escapeHtml(location)}
                        </span>
                    </div>

                    <div class="jcp-checkin-description jcp:text-[15px] jcp:my-4">
                        <p>${escapeHtml(description)}</p>
                    </div>
                </div>

                <div class="jcp-content-block jcp:rounded-2xl jcp:p-6 jcp:shadow-[0_6px_20px_rgba(0,0,0,0.05)] jcp:flex jcp:flex-col jcp:gap-5 jcp:bg-white">
                    ${reviewHtml}
                    ${ratingsHtml}
                    ${verifiedHtml}

                    <a href="#" class="jcp-quote-btn">Get a Quote Like This</a>

                    <div class="jcp-related-checkins jcp:mt-8 jcp:border-t jcp:border-[#eee] jcp:pt-5">
                        <h2 class="jcp-section-title">Related Check-ins</h2>
                        <ul class="jcp-list">
                            ${relatedItems}
                        </ul>
                    </div>
                </div>
            </div>

            <div class="jcp-ts-div jcp:grid jcp:grid-cols-[1fr_360px] jcp:gap-8 jcp:max-w-[1200px] jcp:mx-20 jcp:p-8 jcp:items-start">
                <div class="jcp-testimonials">
                    <h2 class="jcp-section-title">What Homeowners Say</h2>
                    <ul class="jcp-list">
                        ${testimonialItems}
                    </ul>
                </div>

                <div class="jcp-service-tags">
                    <h2 class="jcp-section-title">${tagsTitle}</h2>
                    <div class="jcp-tags-list jcp:flex jcp:flex-wrap jcp:gap-2">
                        ${tagItems}
                    </div>
                </div>
            </div>

            <div class="jcp-faq-div jcp:grid jcp:grid-cols-[1fr_360px] jcp:gap-8 jcp:max-w-[1200px] jcp:mx-0 jcp:p-8 jcp:pb-12 jcp:items-start jcp:border-t jcp:border-[#eee]">
                <div class="jcp-faqs">
                    <h2 class="jcp-section-title">FAQs</h2>
                    <ul class="jcp-list">
                        <li class="jcp:py-1 jcp:text-[0.9rem] jcp:text-[#333]">&#9658; Do I need to be home?</li>
                        <li class="jcp:py-1 jcp:text-[0.9rem] jcp:text-[#333]">&#9658; How long does it take?</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>`;
}
