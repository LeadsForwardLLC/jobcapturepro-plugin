<?php
// Rating processing
if (!empty($checkin['rating'])) {
    $rating = min(5, max(1, (int)$checkin['rating'])); // Ensure 1-5 range
}

// Process data variables
$checkin_date = isset($checkin['createdAt']) ? date('F j, Y', strtotime($checkin['createdAt'])) : 'July 6, 2025';
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

<div class="jobcapturepro-single-checkin">
    <div class="jobcapturepro-single-content-block">
        <div class="jobcapturepro-flex-div">
            <div class="jobcapturepro-checkin-header">
                <div class="jobcapturepro-title-container">
                    <?php if (!empty($checkin['imageUrls'][0])): ?>
                        <?php $hero_image = $checkin['imageUrls'][0]; ?>
                        <img class="jobcapturepro-hero-img" src="<?php echo esc_url($hero_image); ?>" alt="<?php echo esc_attr($checkin['title'] ?? 'Job Image'); ?>">
                    <?php endif; ?>
                    <h1><?php echo esc_html($checkin['title'] ?? 'Roof Soft Wash in Venice, FL'); ?></h1>
                </div>

                <?php if (!empty($checkin['imageUrls'][0])): ?>
                    <?php $hero_image_full = !empty($checkin['imageUrls'][1]) ? $checkin['imageUrls'][1] : $hero_image; ?>
                    <img class="jobcapturepro-hero-img" src="<?php echo esc_url($hero_image_full); ?>" alt="<?php echo esc_attr($checkin['title'] ?? 'Job Image'); ?>">
                <?php endif; ?>

                <div class="jobcapturepro-checkin-meta">
                    <span class="jobcapturepro-checkin-date"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                            <path d="M224 64C241.7 64 256 78.3 256 96L256 128L384 128L384 96C384 78.3 398.3 64 416 64C433.7 64 448 78.3 448 96L448 128L480 128C515.3 128 544 156.7 544 192L544 480C544 515.3 515.3 544 480 544L160 544C124.7 544 96 515.3 96 480L96 192C96 156.7 124.7 128 160 128L192 128L192 96C192 78.3 206.3 64 224 64zM160 304L160 336C160 344.8 167.2 352 176 352L208 352C216.8 352 224 344.8 224 336L224 304C224 295.2 216.8 288 208 288L176 288C167.2 288 160 295.2 160 304zM288 304L288 336C288 344.8 295.2 352 304 352L336 352C344.8 352 352 344.8 352 336L352 304C352 295.2 344.8 288 336 288L304 288C295.2 288 288 295.2 288 304zM432 288C423.2 288 416 295.2 416 304L416 336C416 344.8 423.2 352 432 352L464 352C472.8 352 480 344.8 480 336L480 304C480 295.2 472.8 288 464 288L432 288zM160 432L160 464C160 472.8 167.2 480 176 480L208 480C216.8 480 224 472.8 224 464L224 432C224 423.2 216.8 416 208 416L176 416C167.2 416 160 423.2 160 432zM304 416C295.2 416 288 423.2 288 432L288 464C288 472.8 295.2 480 304 480L336 480C344.8 480 352 472.8 352 464L352 432C352 423.2 344.8 416 336 416L304 416zM416 432L416 464C416 472.8 423.2 480 432 480L464 480C472.8 480 480 472.8 480 464L480 432C480 423.2 472.8 416 464 416L432 416C423.2 416 416 423.2 416 432z"></path>
                        </svg><?php echo esc_html($checkin_date); ?></span>
                    <span class="jobcapturepro-checkin-tech"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                            <path d="M224 248a120 120 0 1 0 0-240 120 120 0 1 0 0 240zm-29.7 56C95.8 304 16 383.8 16 482.3 16 498.7 29.3 512 45.7 512l356.6 0c16.4 0 29.7-13.3 29.7-29.7 0-98.5-79.8-178.3-178.3-178.3l-59.4 0z" />
                        </svg><?php echo esc_html($tech_name); ?></span>
                    <span class="jobcapturepro-checkin-location"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                            <path d="M0 188.6C0 84.4 86 0 192 0S384 84.4 384 188.6c0 119.3-120.2 262.3-170.4 316.8-11.8 12.8-31.5 12.8-43.3 0-50.2-54.5-170.4-197.5-170.4-316.8zM192 256a64 64 0 1 0 0-128 64 64 0 1 0 0 128z" />
                        </svg><?php echo esc_html($location); ?></span>
                </div>

                <div class="jobcapturepro-checkin-description">
                    <p><?php echo esc_html($description); ?></p>
                </div>
            </div>

            <div class="jobcapturepro-content-block">
                <?php if ($show_reviews && !empty($checkin['customer_review'])): ?>
                    <div class="jobcapturepro-checkin-review">
                        <h2 class="jobcapturepro-section-title">Review</h2>
                        <div class="jobcapturepro-review-content">
                            <p class="jobcapturepro-review-text">"<?php echo esc_html($checkin['customer_review']['text']); ?>"</p>
                            <p class="jobcapturepro-review-author">– <?php echo esc_html($checkin['customer_review']['author']); ?></p>
                        </div>
                    </div>
                <?php elseif ($show_fallback_review): ?>
                    <div class="jobcapturepro-checkin-review">
                        <h2 class="jobcapturepro-section-title">Review</h2>
                        <div class="jobcapturepro-review-content">
                            <p class="jobcapturepro-review-text">"Looks brand new! Friendly, professional, fast. Highly recommend."</p>
                            <p class="jobcapturepro-review-author">– Danielle P.</p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($show_ratings && !empty($checkin['rating'])): ?>
                    <div class="jobcapturepro-job-reviews">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?php $filled = $i <= $rating ? 'filled' : 'empty'; ?>
                            <span class="star-<?php echo esc_attr($filled); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                    <path d="M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C43.3 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z"></path>
                                </svg>
                            </span>
                        <?php endfor; ?>
                    </div>
                <?php elseif ($show_fallback_rating): ?>
                    <div class="jobcapturepro-job-reviews">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                <path d="M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C43.3 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z"></path>
                            </svg>
                        </span>
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                <path d="M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C43.3 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z"></path>
                            </svg>
                        </span>
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                <path d="M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C43.3 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z"></path>
                            </svg>
                        </span>
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                <path d="M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C43.3 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z"></path>
                            </svg>
                        </span>
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                <path d="M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C433 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z"></path>
                            </svg>
                        </span>
                    </div>
                <?php endif; ?>

                <?php if ($show_verified || $show_verified_fallback): ?>
                    <div class="jobcapturepro-verified-badge"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                            <path d="M256 512a256 256 0 1 0 0-512 256 256 0 1 0 0 512zm84.4-299.3l-80 128c-4.2 6.7-11.4 10.9-19.3 11.3s-15.5-3.2-20.2-9.6l-48-64c-8-10.6-5.8-25.6 4.8-33.6s25.6-5.8 33.6 4.8l27 36 61.4-98.3c7-11.2 21.8-14.7 33.1-7.6s14.7 21.8 7.6 33.1z" />
                        </svg> Verified Job Check-In</div>
                <?php endif; ?>

                <a href="#" class="get-quote-btn">Get a Quote Like This</a>

                <?php if ($show_related): ?>
                    <div class="jobcapturepro-related-checkins">
                        <h2 class="jobcapturepro-section-title">Related Check-ins</h2>
                        <ul class="jobcapturepro-list">
                            <?php foreach ($checkin['related_checkins'] as $related): ?>
                                <li><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                        <path d="M338.8-9.9c11.9 8.6 16.3 24.2 10.9 37.8L271.3 224 416 224c13.5 0 25.5 8.4 30.1 21.1s.7 26.9-9.6 35.5l-288 240c-11.3 9.4-27.4 9.9-39.3 1.3s-16.3-24.2-10.9-37.8L176.7 288 32 288c-13.5 0-25.5-8.4-30.1-21.1s-.7-26.9 9.6-35.5l288-240c11.3-9.4 27.4-9.9 39.3-1.3z" />
                                    </svg> <?php echo esc_html($related['title']); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php else: ?>
                    <div class="jobcapturepro-related-checkins">
                        <h2 class="jobcapturepro-section-title">Related Check-ins</h2>
                        <ul class="jobcapturepro-list">
                            <li><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                    <path d="M338.8-9.9c11.9 8.6 16.3 24.2 10.9 37.8L271.3 224 416 224c13.5 0 25.5 8.4 30.1 21.1s.7 26.9-9.6 35.5l-288 240c-11.3 9.4-27.4 9.9-39.3 1.3s-16.3-24.2-10.9-37.8L176.7 288 32 288c-13.5 0-25.5-8.4-30.1-21.1s-.7-26.9 9.6-35.5l288-240c11.3-9.4 27.4-9.9 39.3-1.3z" />
                                </svg> Driveway Pressure Wash – Sarasota</li>
                            <li><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free v5.15.4 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                    <path d="M562.1 383.9c-21.5-2.4-42.1-10.5-57.9-22.9-14.1-11.1-34.2-11.3-48.2 0-37.9 30.4-107.2 30.4-145.7-1.5-13.5-11.2-33-9.1-46.7 1.8-38 30.1-106.9 30-145.2-1.7-13.5-11.2-33.3-8.9-47.1 2-15.5 12.2-36 20.1-57.7 22.4-7.9.8-13.6 7.8-13.6 15.7v32.2c0 9.1 7.6 16.8 16.7 16 28.8-2.5 56.1-11.4 79.4-25.9 56.5 34.6 137 34.1 192 0 56.5 34.6 137 34.1 192 0 23.3 14.2 50.9 23.3 79.1 25.8 9.1.8 16.7-6.9 16.7-16v-31.6c.1-8-5.7-15.4-13.8-16.3zm0-144c-21.5-2.4-42.1-10.5-57.9-22.9-14.1-11.1-34.2-11.3-48.2 0-37.9 30.4-107.2 30.4-145.7-1.5-13.5-11.2-33-9.1-46.7 1.8-38 30.1-106.9 30-145.2-1.7-13.5-11.2-33.3-8.9-47.1 2-15.5 12.2-36 20.1-57.7 22.4-7.9.8-13.6 7.8-13.6 15.7v32.2c0 9.1 7.6 16.8 16.7 16 28.8-2.5 56.1-11.4 79.4-25.9 56.5 34.6 137 34.1 192 0 56.5 34.6 137 34.1 192 0 23.3 14.2 50.9 23.3 79.1 25.8 9.1.8 16.7-6.9 16.7-16v-31.6c.1-8-5.7-15.4-13.8-16.3zm0-144C540.6 93.4 520 85.4 504.2 73 490.1 61.9 470 61.7 456 73c-37.9 30.4-107.2 30.4-145.7-1.5-13.5-11.2-33-9.1-46.7 1.8-38 30.1-106.9 30-145.2-1.7-13.5-11.2-33.3-8.9-47.1 2-15.5 12.2-36 20.1-57.7 22.4-7.9.8-13.6 7.8-13.6 15.7v32.2c0 9.1 7.6 16.8 16.7 16 28.8-2.5 56.1-11.4 79.4-25.9 56.5 34.6 137 34.1 192 0 56.5 34.6 137 34.1 192 0 23.3 14.2 50.9 23.3 79.1 25.8 9.1.8 16.7-6.9 16.7-16v-31.6c.1-8-5.7-15.4-13.8-16.3z" />
                                </svg> Pool Deck Cleaning – Nokomis</li>
                            <li><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free v5.15.4 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                    <path d="M464 32H48C21.5 32 0 53.5 0 80v352c0 26.5 21.5 48 48 48h416c26.5 0 48-21.5 48-48V80c0-26.5-21.5-48-48-48zm-16 160H64v-84c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12v84z" />
                                </svg> Window Cleaning – Lakewood Ranch</li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="jobcapturepro-ts-div">
            <?php if ($show_testimonials): ?>
                <div class="jobcapturepro-testimonials">
                    <h2 class="jobcapturepro-section-title">What Homeowners Say</h2>
                    <ul class="jobcapturepro-list">
                        <?php foreach ($company_info['testimonials'] as $testimonial): ?>
                            <li>"<?php echo esc_html($testimonial['text']); ?>" – <?php echo esc_html($testimonial['author']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php else: ?>
                <div class="jobcapturepro-testimonials">
                    <h2 class="jobcapturepro-section-title">What Homeowners Say</h2>
                    <ul class="jobcapturepro-list">
                        <li>"Cleaned it like new in 2 hours." – Brian M.</li>
                        <li>"Didn't even need to be home." – Linda R.</li>
                        <li>"No upsells. Just results." – Mark D.</li>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($show_service_tags): ?>
                <div class="jobcapturepro-service-tags">
                    <h2 class="jobcapturepro-section-title">Service Tags</h2>
                    <div class="jobcapturepro-tags-list">
                        <?php foreach ($checkin['service_tags'] as $tag): ?>
                            <span class="job-tag"><?php echo esc_html($tag); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="jobcapturepro-service-tags">
                    <h2 class="jobcapturepro-section-title">Nearby Service Tags</h2>
                    <div class="jobcapturepro-tags-list">
                        <span class="job-tag">Venice, FL</span>
                        <span class="job-tag">Roof Cleaning</span>
                        <span class="job-tag">Soft Wash</span>
                        <span class="job-tag">Exterior Algae</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="jobcapturepro-faq-div">
            <div class="jobcapturepro-faqs">
                <h2 class="jobcapturepro-section-title">FAQs</h2>
                <ul class="jobcapturepro-list">
                    <li>► Do I need to be home?</li>
                    <li>► How long does it take?</li>
                </ul>
            </div>
        </div>
    </div>
</div>