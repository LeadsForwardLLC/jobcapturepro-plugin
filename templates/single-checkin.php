<?php

// Prevent direct access.
if (! defined('ABSPATH')) {
    exit;
}

// Rating processing
if (!empty($checkin['rating'])) {
    $rating = min(5, max(1, (int)$checkin['rating'])); // Ensure 1-5 range
}

// Process data variables
$checkin_date = isset($checkin['createdAt']) ? gmdate('F j, Y', strtotime($checkin['createdAt'])) : 'July 6, 2025';
$tech_name = isset($checkin['assignedUser']['name']) ? $checkin['assignedUser']['name'] : 'Chris (Tech)';
$location = isset($checkin['address']) ? $checkin['address'] : 'Venice, FL';
$description = isset($checkin['description']) ? $checkin['description'] : 'Roof soft-washed to remove algae and restore curb appeal. This 2-story home was cleaned using a low-pressure rinse method safe for shingles and gutters. Job completed in under 2 hours.';

// Feature flags (these need to be passed from the class)
$show_related = !empty($checkin['related_checkins']) && is_array($checkin['related_checkins']);
$show_testimonials = !empty($company_info['testimonials']) && is_array($company_info['testimonials']);
$show_service_tags = !empty($checkin['service_tags']) && is_array($checkin['service_tags']);

// Set feature flag variables using the static method directly
$show_reviews = $should_show_feature('show_customer_reviews', !empty($checkin['customer_review']));
$show_fallback_review = $should_show_feature('show_customer_reviews', true);
$show_ratings = $should_show_feature('show_star_ratings', !empty($checkin['rating']));
$show_fallback_rating = $should_show_feature('show_star_ratings', true);
$show_verified = $should_show_feature('show_verified_badges', !empty($checkin['is_verified']) && $checkin['is_verified']);
$show_verified_fallback = $should_show_feature('show_verified_badges', true);
?>

<div class="jcp-single-checkin jcp:mx-auto jcp:p-5 jcp:text-[#333] jcp:leading-[1.5] jcp:bg-[#f9f9f9]">
    <div class="jcp-single-content-block">
        <div class="jcp-flex-div jcp:grid jcp:grid-cols-[1fr_360px] jcp:gap-8 jcp:max-w-[1200px] jcp:mx-20 jcp:px-8 jcp:items-start jcp:border-b jcp:border-[#eee] jcp:pb-12">
            <div class="jcp-checkin-header jcp:mb-5 jcp:bg-white jcp:rounded-2xl jcp:p-8 jcp:shadow-[0_6px_20px_rgba(0,0,0,0.05)]">
                <div class="jcp-title-container jcp:flex jcp:items-center jcp:gap-4">
                    <?php if (!empty($checkin['imageUrls'][0])): ?>
                        <?php $hero_image = $checkin['imageUrls'][0]; ?>
                        <img class="jcp-hero-img jcp:w-20 jcp:h-20 jcp:object-cover jcp:rounded-full" src="<?php echo esc_url($hero_image); ?>" alt="<?php echo esc_attr($checkin['title'] ?? 'Job Image'); ?>">
                    <?php endif; ?>
                    <h1 class="jcp:text-2xl jcp:m-0 jcp:mb-1 jcp:text-[#222]"><?php echo esc_html($checkin['title'] ?? 'Roof Soft Wash in Venice, FL'); ?></h1>
                </div>

                <?php if (!empty($checkin['imageUrls'][0])): ?>
                    <?php $hero_image_full = !empty($checkin['imageUrls'][1]) ? $checkin['imageUrls'][1] : $hero_image; ?>
                    <img class="jcp-hero-img jcp:my-4 jcp:shadow-[0_2px_8px_rgba(0,0,0,0.1)] jcp:w-full jcp:h-auto jcp:object-cover jcp:border-b jcp:border-[#eee] jcp:rounded-xl jcp:mb-6" src="<?php echo esc_url($hero_image_full); ?>" alt="<?php echo esc_attr($checkin['title'] ?? 'Job Image'); ?>">
                <?php endif; ?>

                <div class="jcp-checkin-meta jcp:flex jcp:gap-4 jcp:text-sm jcp:text-[#666] jcp:mb-4 jcp:justify-between">
                    <span class="jcp-checkin-date jcp:flex jcp:items-center">
                        <?php echo jcp_icon('calendar', 16, 'jcp:mr-1.5 jcp:text-[#999] jcp:shrink-0'); ?><?php echo esc_html($checkin_date); ?>
                    </span>
                    <span class="jcp-checkin-tech">
                        <?php echo jcp_icon('user', 16, 'jcp:mr-1.5 jcp:text-[#999] jcp:shrink-0'); ?><?php echo esc_html($tech_name); ?>
                    </span>
                    <span class="jcp-checkin-location">
                        <?php echo jcp_icon('map-pin', 16, 'jcp:mr-1.5 jcp:text-[#999] jcp:shrink-0'); ?><?php echo esc_html($location); ?>
                    </span>
                </div>

                <div class="jcp-checkin-description jcp:text-[15px] jcp:my-4">
                    <p><?php echo esc_html($description); ?></p>
                </div>
            </div>

            <div class="jcp-content-block jcp:rounded-2xl jcp:p-6 jcp:shadow-[0_6px_20px_rgba(0,0,0,0.05)] jcp:flex jcp:flex-col jcp:gap-5 jcp:bg-white">
                <?php if ($show_reviews && !empty($checkin['customer_review'])): ?>
                    <div class="jcp-checkin-review">
                        <h2 class="jcp-section-title">Review</h2>
                        <div class="jcp-review-content jcp:mb-4 jcp:bg-[#fef9c3] jcp:border-l-4 jcp:border-[#facc15] jcp:px-4 jcp:py-3 jcp:rounded-lg jcp:leading-[1.5]">
                            <p class="jcp-review-text jcp:italic jcp:m-0 jcp:mb-1">"<?php echo esc_html($checkin['customer_review']['text']); ?>"</p>
                            <p class="jcp-review-author jcp:font-bold jcp:text-right jcp:m-0">– <?php echo esc_html($checkin['customer_review']['author']); ?></p>
                        </div>
                    </div>
                <?php elseif ($show_fallback_review): ?>
                    <div class="jcp-checkin-review">
                        <h2 class="jcp-section-title">Review</h2>
                        <div class="jcp-review-content jcp:mb-4 jcp:bg-[#fef9c3] jcp:border-l-4 jcp:border-[#facc15] jcp:px-4 jcp:py-3 jcp:rounded-lg jcp:leading-[1.5]">
                            <p class="jcp-review-text jcp:italic jcp:m-0 jcp:mb-1">"Looks brand new! Friendly, professional, fast. Highly recommend."</p>
                            <p class="jcp-review-author jcp:font-bold jcp:text-right jcp:m-0">– Danielle P.</p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($show_ratings && !empty($checkin['rating'])): ?>
                    <div class="jcp-job-reviews jcp:flex jcp:gap-0.5 jcp:my-1 jcp:justify-end">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?php $filled = $i <= $rating ? 'filled' : 'empty'; ?>
                            <span class="jcp-star-<?php echo esc_attr($filled); ?>">
                                <?php echo jcp_icon('star', 18, 'jcp:fill-[#facc15] jcp:text-[#facc15]'); ?>
                            </span>
                        <?php endfor; ?>
                    </div>
                <?php elseif ($show_fallback_rating): ?>
                    <div class="jcp-job-reviews jcp:flex jcp:gap-0.5 jcp:my-1 jcp:justify-end">
                        <?php for ($i = 0; $i < 5; $i++): ?>
                        <span><?php echo jcp_icon('star', 18, 'jcp:fill-[#facc15] jcp:text-[#facc15]'); ?></span>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>

                <?php if ($show_verified || $show_verified_fallback): ?>
                    <div class="jcp-verified-badge jcp:bg-[#e6f4ea] jcp:text-[#1e7f3e] jcp:px-3 jcp:py-1.5 jcp:text-[0.85rem] jcp:rounded-full jcp:font-medium jcp:inline-flex jcp:items-center jcp:gap-1.5 jcp:w-full jcp:mt-5 jcp:mb-4">
                        <?php echo jcp_icon('circle-check', 18, 'jcp:text-[#1e7f3e] jcp:shrink-0'); ?> Verified Job Check-In</div>
                <?php endif; ?>

                <a href="#" class="jcp-quote-btn">Get a Quote Like This</a>

                <?php if ($show_related): ?>
                    <div class="jcp-related-checkins jcp:mt-8 jcp:border-t jcp:border-[#eee] jcp:pt-5">
                        <h2 class="jcp-section-title">Related Check-ins</h2>
                        <ul class="jcp-list">
                            <?php foreach ($checkin['related_checkins'] as $related): ?>
                                <li class="jcp:py-1 jcp:text-[0.9rem] jcp:text-[#333] jcp:flex jcp:items-center jcp:gap-1">
                                    <?php echo jcp_icon('zap', 14, 'jcp:text-[#999] jcp:shrink-0'); ?>
                                    <?php echo esc_html($related['title']); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php else: ?>
                    <div class="jcp-related-checkins jcp:mt-8 jcp:border-t jcp:border-[#eee] jcp:pt-5">
                        <h2 class="jcp-section-title">Related Check-ins</h2>
                        <ul class="jcp-list">
                            <li class="jcp:py-1 jcp:text-[0.9rem] jcp:text-[#333] jcp:flex jcp:items-center jcp:gap-1"><?php echo jcp_icon('zap', 14, 'jcp:text-[#999] jcp:shrink-0'); ?> Driveway Pressure Wash – Sarasota</li>
                            <li class="jcp:py-1 jcp:text-[0.9rem] jcp:text-[#333] jcp:flex jcp:items-center jcp:gap-1"><?php echo jcp_icon('zap', 14, 'jcp:text-[#999] jcp:shrink-0'); ?> Pool Deck Cleaning – Nokomis</li>
                            <li class="jcp:py-1 jcp:text-[0.9rem] jcp:text-[#333] jcp:flex jcp:items-center jcp:gap-1"><?php echo jcp_icon('zap', 14, 'jcp:text-[#999] jcp:shrink-0'); ?> Window Cleaning – Lakewood Ranch</li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="jcp-ts-div jcp:grid jcp:grid-cols-[1fr_360px] jcp:gap-8 jcp:max-w-[1200px] jcp:mx-20 jcp:p-8 jcp:items-start">
            <?php if ($show_testimonials): ?>
                <div class="jcp-testimonials">
                    <h2 class="jcp-section-title">What Homeowners Say</h2>
                    <ul class="jcp-list">
                        <?php foreach ($company_info['testimonials'] as $testimonial): ?>
                            <li class="jcp:py-1 jcp:text-[0.9rem] jcp:text-[#333]">"<?php echo esc_html($testimonial['text']); ?>" – <?php echo esc_html($testimonial['author']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php else: ?>
                <div class="jcp-testimonials">
                    <h2 class="jcp-section-title">What Homeowners Say</h2>
                    <ul class="jcp-list">
                        <li class="jcp:py-1 jcp:text-[0.9rem] jcp:text-[#333]">"Cleaned it like new in 2 hours." – Brian M.</li>
                        <li class="jcp:py-1 jcp:text-[0.9rem] jcp:text-[#333]">"Didn't even need to be home." – Linda R.</li>
                        <li class="jcp:py-1 jcp:text-[0.9rem] jcp:text-[#333]">"No upsells. Just results." – Mark D.</li>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($show_service_tags): ?>
                <div class="jcp-service-tags">
                    <h2 class="jcp-section-title">Service Tags</h2>
                    <div class="jcp-tags-list jcp:flex jcp:flex-wrap jcp:gap-2">
                        <?php foreach ($checkin['service_tags'] as $tag): ?>
                            <span class="jcp-tag"><?php echo esc_html($tag); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="jcp-service-tags">
                    <h2 class="jcp-section-title">Nearby Service Tags</h2>
                    <div class="jcp-tags-list jcp:flex jcp:flex-wrap jcp:gap-2">
                        <span class="jcp-tag">Venice, FL</span>
                        <span class="jcp-tag">Roof Cleaning</span>
                        <span class="jcp-tag">Soft Wash</span>
                        <span class="jcp-tag">Exterior Algae</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="jcp-faq-div jcp:grid jcp:grid-cols-[1fr_360px] jcp:gap-8 jcp:max-w-[1200px] jcp:mx-0 jcp:p-8 jcp:pb-12 jcp:items-start jcp:border-t jcp:border-[#eee]">
            <div class="jcp-faqs">
                <h2 class="jcp-section-title">FAQs</h2>
                <ul class="jcp-list">
                    <li class="jcp:py-1 jcp:text-[0.9rem] jcp:text-[#333]">► Do I need to be home?</li>
                    <li class="jcp:py-1 jcp:text-[0.9rem] jcp:text-[#333]">► How long does it take?</li>
                </ul>
            </div>
        </div>
    </div>
</div>
