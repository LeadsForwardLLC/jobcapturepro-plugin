<?php

/**
 * This file defines the template generation functionality for the plugin.
 */
class JobCaptureProTemplates
{
    /**
     * Helper function to check if a feature should be displayed
     * Features are controlled at the plugin code level, not via UI
     * 
     * @param string $feature_name The feature option name
     * @param bool $data_exists Whether the required data exists
     * @return bool Whether the feature should be displayed
     */
    private static function should_show_feature($feature_name, $data_exists = false)
    {
        // Feature toggles controlled at plugin code level
        // Set to false by default since backend features are not yet implemented
        $feature_toggles = array(
            'show_customer_reviews' => false,
            'show_star_ratings' => false,
            'show_verified_badges' => false,
            'show_company_stats' => false,
            'show_company_reviews' => false
        );

        $feature_enabled = isset($feature_toggles[$feature_name]) ? $feature_toggles[$feature_name] : false;
        return $feature_enabled && $data_exists;
    }

    /**
     * Helper method to render checkins with conditional logic
     * 
     * @param string|null $checkin_id The checkin ID if filtering for a specific checkin
     * @param array $checkins Array of checkin data
     * @param array $company_info Company data for features
     * @return string HTML output for the checkins
     */
    public static function render_checkins_conditionally($checkin_id, $checkins, $company_info = array())
    {
        // If a specific checkin_id was provided, render as a single checkin
        if ($checkin_id && count($checkins) === 1) {
            return JobCaptureProTemplates::render_single_checkin($checkins[0], $company_info);
        } else {
            // Otherwise render as a grid of multiple checkins
            return JobCaptureProTemplates::render_checkins_grid($checkins['checkins'], $company_info);
        }
    }

    /**
     * Helper method to render map with conditional logic based on checkin_id
     * 
     * @param string|null $checkin_id The checkin ID if filtering for a specific checkin
     * @param array $response_data The API response data containing locations and maps API key
     * @return string HTML output for the map
     */
    public static function render_map_conditionally($checkin_id, $response_data)
    {
        // Extract locations and maps API key from response
        $locations = isset($response_data['locations']) ? $response_data['locations'] : [];
        $maps_api_key = isset($response_data['googleMapsApiKey']['value']) ? $response_data['googleMapsApiKey']['value'] : '';

        // Render the map
        return JobCaptureProTemplates::render_map($locations, $maps_api_key);
    }


    /**
     * Renders combined components for job capture display
     *
     * This method processes and renders multiple UI components together, including
     * company information, map visualization, and check-in data for a specific job.
     *
     * @param array $company_info Contains company details and information
     * @param array $map_data Map-related data for location visualization
     * @param array $checkins Collection of check-in records
     * @param int $checkin_id Specific check-in identifier for single check-in display (see conditional rendering logic)
     * @return string HTML output for the combined components
     */
    public static function render_combined_components($company_info, $map_data, $checkins, $checkin_id)
    {
        $output = '<div class="jobcapturepro-combined-components">';

        // Render the company info section
        $output .= JobCaptureProTemplates::render_company_info($company_info);

        // Render map with conditional logic
        $output .= JobCaptureProTemplates::render_map_conditionally($checkin_id, $map_data);

        // Render checkins with conditional logic
        $output .= JobCaptureProTemplates::render_checkins_conditionally($checkin_id, $checkins, $company_info);

        $output .= '</div>';

        return $output;
    }

    /**
     * Generate HTML for a single checkin page matching screenshot style
     */
    public static function render_single_checkin($checkin, $company_info = array())
    {
        $output = '<div class="jobcapturepro-single-checkin">';

        // First content block (header and description)
        $output .= '<div class="jobcapturepro-single-content-block">';

        $output .= '<div class="jobcapturepro-flex-div">';

        $output .= '<div class="jobcapturepro-checkin-header">';
        $output .= '<div class="jobcapturepro-title-container">';

        // Only render hero image if actual checkin image exists
        if (!empty($checkin['imageUrls'][0])) {
            $hero_image = $checkin['imageUrls'][0];
            $output .= '<img class="jobcapturepro-hero-img" src="' . esc_url($hero_image) . '" alt="' . esc_attr($checkin['title'] ?? 'Job Image') . '">';
        }
        $output .= '<h1>' . esc_html($checkin['title'] ?? 'Roof Soft Wash in Venice, FL') . '</h1>';
        $output .= '</div>';
        // $output .= '<h1>' . esc_html($checkin['title'] ?? 'Roof Soft Wash in Venice, FL') . '</h1>';
        // Add the hero image below the title
        // Only render hero image and meta if image exists
        if (!empty($checkin['imageUrls'][0])) {
            $hero_image_full = !empty($checkin['imageUrls'][1]) ? $checkin['imageUrls'][1] : $hero_image;
            $output .= '<img class="jobcapturepro-hero-img" src="' . esc_url($hero_image_full) . '" alt="' . esc_attr($checkin['title'] ?? 'Job Image') . '">';
        }
        $output .= '<div class="jobcapturepro-checkin-meta">';

        // Use actual data or fallbacks
        $checkin_date = isset($checkin['createdAt']) ? date('F j, Y', strtotime($checkin['createdAt'])) : 'July 6, 2025';
        $tech_name = isset($checkin['assignedUser']['name']) ? $checkin['assignedUser']['name'] : 'Chris (Tech)';
        $location = isset($checkin['address']) ? $checkin['address'] : 'Venice, FL';

        $output .= '<span class="jobcapturepro-checkin-date"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M224 64C241.7 64 256 78.3 256 96L256 128L384 128L384 96C384 78.3 398.3 64 416 64C433.7 64 448 78.3 448 96L448 128L480 128C515.3 128 544 156.7 544 192L544 480C544 515.3 515.3 544 480 544L160 544C124.7 544 96 515.3 96 480L96 192C96 156.7 124.7 128 160 128L192 128L192 96C192 78.3 206.3 64 224 64zM160 304L160 336C160 344.8 167.2 352 176 352L208 352C216.8 352 224 344.8 224 336L224 304C224 295.2 216.8 288 208 288L176 288C167.2 288 160 295.2 160 304zM288 304L288 336C288 344.8 295.2 352 304 352L336 352C344.8 352 352 344.8 352 336L352 304C352 295.2 344.8 288 336 288L304 288C295.2 288 288 295.2 288 304zM432 288C423.2 288 416 295.2 416 304L416 336C416 344.8 423.2 352 432 352L464 352C472.8 352 480 344.8 480 336L480 304C480 295.2 472.8 288 464 288L432 288zM160 432L160 464C160 472.8 167.2 480 176 480L208 480C216.8 480 224 472.8 224 464L224 432C224 423.2 216.8 416 208 416L176 416C167.2 416 160 423.2 160 432zM304 416C295.2 416 288 423.2 288 432L288 464C288 472.8 295.2 480 304 480L336 480C344.8 480 352 472.8 352 464L352 432C352 423.2 344.8 416 336 416L304 416zM416 432L416 464C416 472.8 423.2 480 432 480L464 480C472.8 480 480 472.8 480 464L480 432C480 423.2 472.8 416 464 416L432 416C423.2 416 416 423.2 416 432z"></path></svg>' . esc_html($checkin_date) . '</span>';
        $output .= '<span class="jobcapturepro-checkin-tech"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M224 248a120 120 0 1 0 0-240 120 120 0 1 0 0 240zm-29.7 56C95.8 304 16 383.8 16 482.3 16 498.7 29.3 512 45.7 512l356.6 0c16.4 0 29.7-13.3 29.7-29.7 0-98.5-79.8-178.3-178.3-178.3l-59.4 0z"/></svg>' . esc_html($tech_name) . '</span>';
        $output .= '<span class="jobcapturepro-checkin-location"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M0 188.6C0 84.4 86 0 192 0S384 84.4 384 188.6c0 119.3-120.2 262.3-170.4 316.8-11.8 12.8-31.5 12.8-43.3 0-50.2-54.5-170.4-197.5-170.4-316.8zM192 256a64 64 0 1 0 0-128 64 64 0 1 0 0 128z"/></svg>' . esc_html($location) . '</span>';
        $output .= '</div>';
        $output .= '<div class="jobcapturepro-checkin-description">';

        // Use actual description or fallback
        $description = isset($checkin['description']) ? $checkin['description'] : 'Roof soft-washed to remove algae and restore curb appeal. This 2-story home was cleaned using a low-pressure rinse method safe for shingles and gutters. Job completed in under 2 hours.';
        $output .= '<p>' . esc_html($description) . '</p>';
        $output .= '</div>';
        $output .= '</div>'; // close first content block

        // Second content block (all other sections)
        $output .= '<div class="jobcapturepro-content-block">';

        // Review section - only show if customer review data exists
        $show_reviews = self::should_show_feature('show_customer_reviews', !empty($checkin['customer_review']));
        if ($show_reviews && !empty($checkin['customer_review'])) {
            $output .= '<div class="jobcapturepro-checkin-review">';
            $output .= '<h2 class="jobcapturepro-section-title">Review</h2>';
            $output .= '<div class="jobcapturepro-review-content">';
            $output .= '<p class="jobcapturepro-review-text">"' . esc_html($checkin['customer_review']['text']) . '"</p>';
            $output .= '<p class="jobcapturepro-review-author">– ' . esc_html($checkin['customer_review']['author']) . '</p>';
            $output .= '</div>';
        } else {
            // Fallback to hard-coded review if feature is enabled but no data
            $show_fallback_review = self::should_show_feature('show_customer_reviews', true);
            if ($show_fallback_review) {
                $output .= '<div class="jobcapturepro-checkin-review">';
                $output .= '<h2 class="jobcapturepro-section-title">Review</h2>';
                $output .= '<div class="jobcapturepro-review-content">';
                $output .= '<p class="jobcapturepro-review-text">"Looks brand new! Friendly, professional, fast. Highly recommend."</p>';
                $output .= '<p class="jobcapturepro-review-author">– Danielle P.</p>';
                $output .= '</div>';
            }
        }

        // Star ratings - only show if rating data exists or feature is enabled
        $show_ratings = self::should_show_feature('show_star_ratings', !empty($checkin['rating']));
        if ($show_ratings && !empty($checkin['rating'])) {
            $rating = min(5, max(1, (int)$checkin['rating'])); // Ensure 1-5 range
            $output .= '<div class="jobcapturepro-job-reviews">';
            for ($i = 1; $i <= 5; $i++) {
                $filled = $i <= $rating ? 'filled' : 'empty';
                $output .= '<span class="star-' . $filled . '">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C43.3 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z"></path></svg>
            </span>';
            }
            $output .= '</div>';
        } else {
            // Fallback to 5-star rating if feature is enabled but no data
            $show_fallback_rating = self::should_show_feature('show_star_ratings', true);
            if ($show_fallback_rating) {
                $output .= '<div class="jobcapturepro-job-reviews">
                <span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C43.3 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z"></path></svg>
                </span>
                <span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C43.3 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z"></path></svg>
                </span>
                <span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C43.3 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z"></path></svg>
                </span>
                <span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C43.3 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z"></path></svg>
                </span>
                <span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C43.3 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z"></path></svg>
                </span>
            </div>';
            }
        }

        // Verified badge - only show if job is verified
        $show_verified = self::should_show_feature('show_verified_badges', !empty($checkin['is_verified']) && $checkin['is_verified']);
        if ($show_verified || self::should_show_feature('show_verified_badges', true)) {
            $output .= '<div class="jobcapturepro-verified-badge"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M256 512a256 256 0 1 0 0-512 256 256 0 1 0 0 512zm84.4-299.3l-80 128c-4.2 6.7-11.4 10.9-19.3 11.3s-15.5-3.2-20.2-9.6l-48-64c-8-10.6-5.8-25.6 4.8-33.6s25.6-5.8 33.6 4.8l27 36 61.4-98.3c7-11.2 21.8-14.7 33.1-7.6s14.7 21.8 7.6 33.1z"/></svg> Verified Job Check-In</div>';
        }

        $output .= '<a href="#" class="get-quote-btn">Get a Quote Like This</a>';

        // Related check-ins - only show if related checkins exist
        $show_related = !empty($checkin['related_checkins']) && is_array($checkin['related_checkins']);
        if ($show_related) {
            $output .= '<div class="jobcapturepro-related-checkins">';
            $output .= '<h2 class="jobcapturepro-section-title">Related Check-ins</h2>';
            $output .= '<ul class="jobcapturepro-list">';
            foreach ($checkin['related_checkins'] as $related) {
                $output .= '<li><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M338.8-9.9c11.9 8.6 16.3 24.2 10.9 37.8L271.3 224 416 224c13.5 0 25.5 8.4 30.1 21.1s.7 26.9-9.6 35.5l-288 240c-11.3 9.4-27.4 9.9-39.3 1.3s-16.3-24.2-10.9-37.8L176.7 288 32 288c-13.5 0-25.5-8.4-30.1-21.1s-.7-26.9 9.6-35.5l288-240c11.3-9.4 27.4-9.9 39.3-1.3z"/></svg> ' . esc_html($related['title']) . '</li>';
            }
            $output .= '</ul>';
            $output .= '</div>';
        } else {
            // Fallback to hard-coded related checkins
            $output .= '<div class="jobcapturepro-related-checkins">';
            $output .= '<h2 class="jobcapturepro-section-title">Related Check-ins</h2>';
            $output .= '<ul class="jobcapturepro-list">';
            $output .= '<li><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M338.8-9.9c11.9 8.6 16.3 24.2 10.9 37.8L271.3 224 416 224c13.5 0 25.5 8.4 30.1 21.1s.7 26.9-9.6 35.5l-288 240c-11.3 9.4-27.4 9.9-39.3 1.3s-16.3-24.2-10.9-37.8L176.7 288 32 288c-13.5 0-25.5-8.4-30.1-21.1s-.7-26.9 9.6-35.5l288-240c11.3-9.4 27.4-9.9 39.3-1.3z"/></svg> Driveway Pressure Wash – Sarasota</li>';
            $output .= '<li><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free v5.15.4 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M562.1 383.9c-21.5-2.4-42.1-10.5-57.9-22.9-14.1-11.1-34.2-11.3-48.2 0-37.9 30.4-107.2 30.4-145.7-1.5-13.5-11.2-33-9.1-46.7 1.8-38 30.1-106.9 30-145.2-1.7-13.5-11.2-33.3-8.9-47.1 2-15.5 12.2-36 20.1-57.7 22.4-7.9.8-13.6 7.8-13.6 15.7v32.2c0 9.1 7.6 16.8 16.7 16 28.8-2.5 56.1-11.4 79.4-25.9 56.5 34.6 137 34.1 192 0 56.5 34.6 137 34.1 192 0 23.3 14.2 50.9 23.3 79.1 25.8 9.1.8 16.7-6.9 16.7-16v-31.6c.1-8-5.7-15.4-13.8-16.3zm0-144c-21.5-2.4-42.1-10.5-57.9-22.9-14.1-11.1-34.2-11.3-48.2 0-37.9 30.4-107.2 30.4-145.7-1.5-13.5-11.2-33-9.1-46.7 1.8-38 30.1-106.9 30-145.2-1.7-13.5-11.2-33.3-8.9-47.1 2-15.5 12.2-36 20.1-57.7 22.4-7.9.8-13.6 7.8-13.6 15.7v32.2c0 9.1 7.6 16.8 16.7 16 28.8-2.5 56.1-11.4 79.4-25.9 56.5 34.6 137 34.1 192 0 56.5 34.6 137 34.1 192 0 23.3 14.2 50.9 23.3 79.1 25.8 9.1.8 16.7-6.9 16.7-16v-31.6c.1-8-5.7-15.4-13.8-16.3zm0-144C540.6 93.4 520 85.4 504.2 73 490.1 61.9 470 61.7 456 73c-37.9 30.4-107.2 30.4-145.7-1.5-13.5-11.2-33-9.1-46.7 1.8-38 30.1-106.9 30-145.2-1.7-13.5-11.2-33.3-8.9-47.1 2-15.5 12.2-36 20.1-57.7 22.4-7.9.8-13.6 7.8-13.6 15.7v32.2c0 9.1 7.6 16.8 16.7 16 28.8-2.5 56.1-11.4 79.4-25.9 56.5 34.6 137 34.1 192 0 56.5 34.6 137 34.1 192 0 23.3 14.2 50.9 23.3 79.1 25.8 9.1.8 16.7-6.9 16.7-16v-31.6c.1-8-5.7-15.4-13.8-16.3z"/></svg> Pool Deck Cleaning – Nokomis</li>';
            $output .= '<li><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free v5.15.4 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M464 32H48C21.5 32 0 53.5 0 80v352c0 26.5 21.5 48 48 48h416c26.5 0 48-21.5 48-48V80c0-26.5-21.5-48-48-48zm-16 160H64v-84c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12v84z"/></svg> Window Cleaning – Lakewood Ranch</li>';
            $output .= '</ul>';
            $output .= '</div>';
        }
        $output .= '</div>';

        $output .= '</div>'; // close second content block

        $output .= '</div>'; // close jobcapturepro-flex-div

        // Testimonials & Services div
        $output .= '<div class="jobcapturepro-ts-div">';

        // Testimonials - only show if testimonials exist or pass company_info parameter
        $show_testimonials = !empty($company_info['testimonials']) && is_array($company_info['testimonials']);
        if ($show_testimonials) {
            $output .= '<div class="jobcapturepro-testimonials">';
            $output .= '<h2 class="jobcapturepro-section-title">What Homeowners Say</h2>';
            $output .= '<ul class="jobcapturepro-list">';
            foreach ($company_info['testimonials'] as $testimonial) {
                $output .= '<li>"' . esc_html($testimonial['text']) . '" – ' . esc_html($testimonial['author']) . '</li>';
            }
            $output .= '</ul>';
            $output .= '</div>';
        } else {
            // Fallback to hard-coded testimonials
            $output .= '<div class="jobcapturepro-testimonials">';
            $output .= '<h2 class="jobcapturepro-section-title">What Homeowners Say</h2>';
            $output .= '<ul class="jobcapturepro-list">';
            $output .= '<li>"Cleaned it like new in 2 hours." – Brian M.</li>';
            $output .= '<li>"Didn\'t even need to be home." – Linda R.</li>';
            $output .= '<li>"No upsells. Just results." – Mark D.</li>';
            $output .= '</ul>';
            $output .= '</div>';
        }

        // Service tags - use actual tags if available
        $show_service_tags = !empty($checkin['service_tags']) && is_array($checkin['service_tags']);
        if ($show_service_tags) {
            $output .= '<div class="jobcapturepro-service-tags">';
            $output .= '<h2 class="jobcapturepro-section-title">Service Tags</h2>';
            $output .= '<div class="jobcapturepro-tags-list">';
            foreach ($checkin['service_tags'] as $tag) {
                $output .= '<span class="job-tag">' . esc_html($tag) . '</span>';
            }
            $output .= '</div>';
            $output .= '</div>';
        } else {
            // Fallback to hard-coded service tags
            $output .= '<div class="jobcapturepro-service-tags">';
            $output .= '<h2 class="jobcapturepro-section-title">Nearby Service Tags</h2>';
            $output .= '<div class="jobcapturepro-tags-list">';
            $output .= '<span class="job-tag">Venice, FL</span>';
            $output .= '<span class="job-tag">Roof Cleaning</span>';
            $output .= '<span class="job-tag">Soft Wash</span>';
            $output .= '<span class="job-tag">Exterior Algae</span>';
            $output .= '</div>';
            $output .= '</div>';
        }

        $output .= '</div>'; // close Testimonials & Services div

        // FAQ div
        $output .= '<div class="jobcapturepro-faq-div">';

        // FAQs
        $output .= '<div class="jobcapturepro-faqs">';
        $output .= '<h2 class="jobcapturepro-section-title">FAQs</h2>';
        $output .= '<ul class="jobcapturepro-list">';
        $output .= '<li>► Do I need to be home?</li>';
        $output .= '<li>► How long does it take?</li>';
        $output .= '</ul>';
        $output .= '</div>';

        $output .= '</div>'; // close FAQ div

        $output .= '</div>'; // close jobcapturepro-single-checkin

        // Enqueue styles
        self::enqueue_single_checkin_styles();
        self::enqueue_checkins_grid_styles();

        return $output;
    }


    /**
     * Generate HTML for a single checkin card
     * 
     * @param array $checkin The checkin data
     * @return string HTML for a single checkin card
     */
    public static function render_checkin_card($checkin)
    {

        // Check for required fields
        if (empty($checkin['description']) || empty($checkin['address']) || empty($checkin['createdAt'])) {
            return '';
        }

        // Create clickable link with checkinId parameter
        $current_url = sanitize_text_field($_SERVER['REQUEST_URI']);
        $checkin_url = add_query_arg('checkinId', sanitize_text_field($checkin['id']), $current_url);

        $output = '<a href="' . esc_url($checkin_url) . '" class="jobcapturepro-checkin-card" style="text-decoration: none; color: inherit;">';

        // Images (if available)
        if (!empty($checkin['imageUrls']) && is_array($checkin['imageUrls'])) {
            $output .= self::render_images_gallery($checkin['imageUrls']);
        }

        // User info (if available)
        if (!empty($checkin['assignedUser'])) {
            $output .= '<div class="jobcapturepro-checkin-user">';

            // Profile image
            if (!empty($checkin['assignedUser']['profileImageUrl'])) {
                $output .= '<div class="jobcapturepro-user-image">
                    <img src="' . esc_url($checkin['assignedUser']['profileImageUrl']) . '" alt="User profile">
                </div>';
            }

            // User name
            if (!empty($checkin['assignedUser']['name'])) {
                $output .= '<div class="jobcapturepro-user-name">
                    <p>' . esc_html($checkin['assignedUser']['name']) . '</p>
                </div>';
            }

            // job Reviews
            $output .= '<div class="jobcapturepro-job-reviews">
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C43.3 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z"></path></svg>
                    </span>
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C43.3 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z"></path></svg>
                    </span>
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C43.3 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z"></path></svg>
                    </span>
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C43.3 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z"></path></svg>
                    </span>
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C43.3 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z"></path></svg>
                    </span>
                </div>';


            $output .= '</div>';
        }

        // Description
        $output .= '<div class="jobcapturepro-checkin-description">
            <p>' . nl2br(esc_html($checkin['description'])) . '</p>
        </div>';

        // Date - Simplified to only show the formatted date
        $output .= '<div class="jobcapturepro-checkin-date">';
        $timestamp = strtotime($checkin['createdAt']);
        $output .= '<p class="date-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M224 64C241.7 64 256 78.3 256 96L256 128L384 128L384 96C384 78.3 398.3 64 416 64C433.7 64 448 78.3 448 96L448 128L480 128C515.3 128 544 156.7 544 192L544 480C544 515.3 515.3 544 480 544L160 544C124.7 544 96 515.3 96 480L96 192C96 156.7 124.7 128 160 128L192 128L192 96C192 78.3 206.3 64 224 64zM160 304L160 336C160 344.8 167.2 352 176 352L208 352C216.8 352 224 344.8 224 336L224 304C224 295.2 216.8 288 208 288L176 288C167.2 288 160 295.2 160 304zM288 304L288 336C288 344.8 295.2 352 304 352L336 352C344.8 352 352 344.8 352 336L352 304C352 295.2 344.8 288 336 288L304 288C295.2 288 288 295.2 288 304zM432 288C423.2 288 416 295.2 416 304L416 336C416 344.8 423.2 352 432 352L464 352C472.8 352 480 344.8 480 336L480 304C480 295.2 472.8 288 464 288L432 288zM160 432L160 464C160 472.8 167.2 480 176 480L208 480C216.8 480 224 472.8 224 464L224 432C224 423.2 216.8 416 208 416L176 416C167.2 416 160 423.2 160 432zM304 416C295.2 416 288 423.2 288 432L288 464C288 472.8 295.2 480 304 480L336 480C344.8 480 352 472.8 352 464L352 432C352 423.2 344.8 416 336 416L304 416zM416 432L416 464C416 472.8 423.2 480 432 480L464 480C472.8 480 480 472.8 480 464L480 432C480 423.2 472.8 416 464 416L432 416C423.2 416 416 423.2 416 432z"/></svg>' . esc_html(date('F j, Y', $timestamp)) . '</p>'; // Format: "Month Day, Year" (e.g., "July 23, 2025")
        $output .= '</div>';

        // Address - Extract city and state only
        $output .= '<div class="jobcapturepro-checkin-address">';

        // Parse address (assuming format: "Street, City, State, ZIP, Country")
        $address_parts = explode(',', $checkin['address']);

        // Get city (2nd last part) and state (last part before country/ZIP)
        $city = trim($address_parts[1] ?? ''); // City
        $state = trim($address_parts[2] ?? ''); // State (full name)

        // Shorten state abbreviation if needed (e.g., "California" → "CA")
        $state_abbr = strlen($state) > 2 ? substr($state, 0, 2) : $state;

        // Display "City, ST" format (e.g., "Miami, FL")
        $output .= '<p><strong>Near</strong> ' . esc_html($city . ', ' . $state_abbr) . '</p>';

        $output .= '</div>';
        $output .= '</a>'; // Close clickable card
        return $output;
    }

    /**
     * Generate HTML for the checkins grid layout with items sorted by date (newest first)
     * 
     * @param array $checkins Array of checkin data
     * @param array $company_info Company data for stats
     * @return string HTML for all checkins in a responsive grid
     */
    public static function render_checkins_grid($checkins, $company_info = array())
    {
        // Sort checkins by date (newest first)
        usort($checkins, function ($a, $b) {
            // Compare timestamps (higher timestamp = more recent)
            return strtotime($b['createdAt']) - strtotime($a['createdAt']);
        });

        // Container with CSS Grid for responsive layout
        $output = '<div class="jobcapturepro-container">';

        // Unique ID for this grid
        $gridId = 'jobcapturepro-grid-' . wp_rand();

        // Grid container with data attribute to store the column count
        $output .= '<div class="jobcapturepro-checkins-grid ' . $gridId . '" data-column-count="3">';

        // Add each checkin to the grid in date-sorted order
        foreach ($checkins as $checkin) {
            $output .= self::render_checkin_card($checkin);
        }

        $output .= '</div>'; // Close grid
        $output .= '</div>'; // Close container

        // jcp stats section - only show if stats data is available or feature is enabled
        $show_stats = self::should_show_feature('show_company_stats', !empty($company_info['stats']));
        if ($show_stats && !empty($company_info['stats'])) {
            $output .= '<div class="jobcapturepro-stats-container">';

            if (!empty($company_info['stats']['jobs_this_month'])) {
                $output .= '<div class="jobcapturepro-stat-item">
                <div class="jobcapturepro-stat-number">' . esc_html($company_info['stats']['jobs_this_month']) . '</div>
                <div class="jobcapturepro-stat-label">Jobs Posted This Month</div>
            </div>';
            }

            if (!empty($company_info['stats']['average_rating'])) {
                $output .= '<div class="jobcapturepro-stat-item">
                <div class="jobcapturepro-stat-number">' . esc_html($company_info['stats']['average_rating']) . '</div>
                <div class="jobcapturepro-stat-label">Average Rating</div>
            </div>';
            }

            if (!empty($company_info['stats']['last_checkin'])) {
                $output .= '<div class="jobcapturepro-stat-item">
                <div class="jobcapturepro-stat-number">' . esc_html($company_info['stats']['last_checkin']) . '</div>
                <div class="jobcapturepro-stat-label">Last Job Check-In</div>
            </div>';
            }

            $output .= '</div>'; // Close jobcapturepro-stats-container
        } else {
            // Fallback to hard-coded stats if feature is enabled but no data
            $show_fallback_stats = self::should_show_feature('show_company_stats', true);
            if ($show_fallback_stats) {
                $output .= '<div class="jobcapturepro-stats-container">';

                $output .= '<div class="jobcapturepro-stat-item">
                <div class="jobcapturepro-stat-number">86</div>
                <div class="jobcapturepro-stat-label">Jobs Posted This Month</div>
            </div>';

                $output .= '<div class="jobcapturepro-stat-item">
                <div class="jobcapturepro-stat-number">96%</div>
                <div class="jobcapturepro-stat-label">Average 5-Star Rating</div>
            </div>';

                $output .= '<div class="jobcapturepro-stat-item">
                <div class="jobcapturepro-stat-number">12 mins ago</div>
                <div class="jobcapturepro-stat-label">Last Job Check-In</div>
            </div>';

                $output .= '</div>'; // Close jobcapturepro-stats-container
            }
        }

        // jcp CTA section

        $output .= '<div class="jobcapturepro-cta-container">';

        // cta Heading
        $output .= '<div class="jobcapturepro-cta">
                    <h2>Let Your Work Speak For Itself</h2>
                    <p>Capture check-ins like these with JobCapturePro. Set it and forget it.</p>
                    <a href="#" class="quote-btn">Get JobCapturePro</a>
                    </div>';

        $output .= '</div>'; // Close jobcapturepro-cta-container

        // Add JavaScript to maintain proper masonry layout
        $output .= '<script>
            document.addEventListener("DOMContentLoaded", function() {
                const grid = document.querySelector(".' . $gridId . '");
                if (!grid) return;
                
                // Function to detect column count from CSS
                function getColumnCount() {
                    const style = window.getComputedStyle(grid);
                    const columnCount = style.getPropertyValue("column-count");
                    return parseInt(columnCount) || 4; // Default to 4 if not set
                }
                
                // Force items to be added in correct order for visual masonry
                function rearrangeItems() {
                    const items = Array.from(grid.children);
                    
                    // First remove all items
                    items.forEach(item => grid.removeChild(item));
                    
                    // Calculate column count
                    const columnCount = getColumnCount();

                    // Update grid attribute with current column count
                    grid.setAttribute("data-column-count", columnCount);
                    
                    // Only keep items that fit evenly into columns
                    // This ensures the masonry layout works correctly
                    const itemsToKeep = Math.floor(items.length / columnCount) * columnCount;
                    const finalItems = items; //.slice(0, itemsToKeep); // TODO: just need a better algo for sorting the masonry grid

                    // Create "virtual" columns - these will help us rearrange items properly
                    const columns = Array.from({length: columnCount}, () => []);
                    
                    // Organize items by column (this ensures ordered reading left-to-right)
                    items.forEach((item, index) => {
                        const columnIndex = index % columnCount;
                        columns[columnIndex].push(item);
                    });
                    
                    // Add back to grid in column-first order
                    columns.forEach(column => {
                        column.forEach(item => {
                            grid.appendChild(item);
                        });
                    });
                }
                
                // Run on load
                rearrangeItems();
                
                // Also run when window is resized (column count may change)
                let previousColumnCount = getColumnCount();
                window.addEventListener("resize", function() {
                    const newColumnCount = getColumnCount();
                    if (newColumnCount !== previousColumnCount) {
                        previousColumnCount = newColumnCount;
                        rearrangeItems();
                    }
                });
            });
        </script>';


        // Enqueue styles and add dynamic selectors styles
        self::enqueue_checkins_grid_styles();
        $output .= self::get_dynamic_selectors_checkins_grid_styles($gridId);

        return $output;
    }

    /**
     * Generate CSS styles for the checkins grid
     * 
     * @param string $gridId The unique ID for the grid
     * @return string CSS styles for the checkins grid
     */
    private static function get_dynamic_selectors_checkins_grid_styles($gridId = null)
    {
        $gridSelector = $gridId ? '.' . $gridId : '.jobcapturepro-checkins-grid';

        return '<style>
            ' . $gridSelector . ' {
                /* Keep masonry-style layout with CSS columns */
                column-count: 3;
                column-gap: 20px;
                width: 100%;
            }

            /* Responsive design */
            @media (max-width: 1024px) {
                ' . $gridSelector . ' {
                    column-count: 3;
                }
            }
            
            @media (max-width: 768px) {
                ' . $gridSelector . ' {
                    column-count: 2;
                }
            }
            
            @media (max-width: 480px) {
                ' . $gridSelector . ' {
                    column-count: 1;
                }
            }
        </style>';
    }


    /**
     * Generate HTML for the address section
     */
    private static function render_address($address)
    {
        $output = '<div class="address" style="background: #f5f5f5; padding: 10px; border-radius: 4px; margin-bottom: 15px;">';
        $output .= '<p style="margin: 0;">' . esc_html($address['addressLine1']) . '<br>';
        if (isset($address['city']) && isset($address['region']) && isset($address['postalCode'])) {
            $output .= esc_html($address['city']) . ', ' . esc_html($address['region']) . ' ' . esc_html($address['postalCode']) . '<br>';
        }
        if (isset($address['countryCode'])) {
            $output .= esc_html($address['countryCode']);
        }
        $output .= '</p>';
        $output .= '</div>';
        return $output;
    }

    /**
     * Generate HTML for the images grid
     */
    private static function render_images_grid($imageUrls)
    {
        if (empty($imageUrls)) {
            return '';
        }

        $output = '<div class="images-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; margin-top: 15px;">';
        foreach ($imageUrls as $imageUrl) {
            $output .= '<img src="' . esc_url($imageUrl) . '" style="width: 100%; height: 200px; object-fit: cover; border-radius: 4px;">';
        }
        $output .= '</div>';
        return $output;
    }

    /**
     * Generate HTML for the images gallery with navigation arrows
     * 
     * @param array $imageUrls Array of image URLs
     * @return string HTML for the image gallery
     */
    private static function render_images_gallery($imageUrls)
    {
        if (empty($imageUrls)) {
            return '';
        }

        $imageCount = count($imageUrls);
        $showArrows = $imageCount > 1;
        $galleryId = 'gallery-' . wp_rand(); // Create unique ID for this gallery

        $output = '<div class="jobcapturepro-checkin-image" id="' . $galleryId . '">';

        // Add all images but only first is visible initially
        foreach ($imageUrls as $index => $imageUrl) {
            $activeClass = $index === 0 ? ' active' : '';
            $output .= '<div class="gallery-image' . $activeClass . '" data-index="' . intval($index) . '">
                <img src="' . esc_url($imageUrl) . '" alt="' . esc_attr('Checkin image ' . ($index + 1)) . '">
            </div>';
        }

        // Add navigation arrows if there are multiple images
        if ($showArrows) {
            $output .= '<div class="gallery-nav gallery-prev" onclick="jobcaptureproChangeImage(\'' . esc_js($galleryId) . '\', \'prev\')">&#10094;</div>';
            $output .= '<div class="gallery-nav gallery-next" onclick="jobcaptureproChangeImage(\'' . esc_js($galleryId) . '\', \'next\')">&#10095;</div>';
            $output .= '<div class="gallery-dots">';

            // Add indicator dots
            for ($i = 0; $i < $imageCount; $i++) {
                $activeClass = $i === 0 ? ' active' : '';
                $output .= '<span class="gallery-dot' . $activeClass . '" onclick="jobcaptureproShowImage(\'' . esc_js($galleryId) . '\', ' . intval($i) . ')"></span>';
            }

            $output .= '</div>';
        }

        $output .= '</div>';

        // Add JavaScript for gallery functionality if there are multiple images
        if ($showArrows) {
            $output .= self::get_gallery_script();
        }

        return $output;
    }

    /**
     * Generate JavaScript for image gallery functionality
     * 
     * @return string JavaScript for gallery functionality
     */
    private static function get_gallery_script()
    {
        // Check if script has already been added to avoid duplication
        static $scriptAdded = false;

        if ($scriptAdded) {
            return '';
        }

        $scriptAdded = true;

        return '<script>
            function jobcaptureproChangeImage(galleryId, direction) {
                const gallery = document.getElementById(galleryId);
                const images = gallery.querySelectorAll(".gallery-image");
                const dots = gallery.querySelectorAll(".gallery-dot");
                
                // Find current active image
                let currentIndex = 0;
                for (let i = 0; i < images.length; i++) {
                    if (images[i].classList.contains("active")) {
                        currentIndex = i;
                        break;
                    }
                }
                
                // Remove active class from current image and dot
                images[currentIndex].classList.remove("active");
                if (dots.length) dots[currentIndex].classList.remove("active");
                
                // Calculate new index
                let newIndex;
                if (direction === "next") {
                    newIndex = (currentIndex + 1) % images.length;
                } else {
                    newIndex = (currentIndex - 1 + images.length) % images.length;
                }
                
                // Add active class to new image and dot
                images[newIndex].classList.add("active");
                if (dots.length) dots[newIndex].classList.add("active");
            }
            
            function jobcaptureproShowImage(galleryId, index) {
                const gallery = document.getElementById(galleryId);
                const images = gallery.querySelectorAll(".gallery-image");
                const dots = gallery.querySelectorAll(".gallery-dot");
                
                // Remove active class from all images and dots
                for (let i = 0; i < images.length; i++) {
                    images[i].classList.remove("active");
                    if (dots.length) dots[i].classList.remove("active");
                }
                
                // Add active class to selected image and dot
                images[index].classList.add("active");
                if (dots.length) dots[index].classList.add("active");
            }
        </script>';
    }

    private static function determine_bounds($features)
    {
        // Calculate center point of 80% of checkins
        $totalPoints = count($features);

        // Sort points by distance from mean center to get the central 80%
        if ($totalPoints > 0) {
            // Find bounds of all points
            $minLat = $maxLat = $features[0]['geometry']['coordinates'][1];
            $minLng = $maxLng = $features[0]['geometry']['coordinates'][0];

            foreach ($features as $feature) {
                $lat = $feature['geometry']['coordinates'][1];
                $lng = $feature['geometry']['coordinates'][0];
                $minLat = min($minLat, $lat);
                $maxLat = max($maxLat, $lat);
                $minLng = min($minLng, $lng);
                $maxLng = max($maxLng, $lng);
            }

            // Add padding (approximately 1km)
            $padding = 0.01;
            $minLat -= $padding;
            $maxLat += $padding;
            $minLng -= $padding;
            $maxLng += $padding;
        } else {
            // Default center if no points
            $minLat = $maxLat = 0;
            $minLng = $maxLng = 0;
        }

        return array($minLat, $maxLat, $minLng, $maxLng);
    }

    /**
     * Generate HTML for a Google Maps map with multiple markers
     * 
     * @param array $locations The location data as defined by geopoints in RFC 7946
     * @return string HTML for a Google Maps map with multiple markers
     */
    public static function render_map($locations, $maps_api_key)
    {
        // Check for required fields
        if (empty($locations)) {
            return '';
        }

        // Ensure necessary scripts are loaded
        wp_enqueue_script('google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . $maps_api_key . '&libraries=marker', array(), null, array('strategy' => 'async'));
        wp_enqueue_script('markerclusterer', 'https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js', array('google-maps'), null, array('strategy' => 'async'));

        // Extract features array from the GeoJSON FeatureCollection
        $features = $locations['features'];

        // Determine the bounds for the map
        list($minLat, $maxLat, $minLng, $maxLng) = self::determine_bounds($features);

        // Calculate center point
        $centerLat = ($minLat + $maxLat) / 2;
        $centerLng = ($minLng + $maxLng) / 2;

        // Start building HTML output
        $output = '<div id="jobcapturepro-map" class="jobcapturepro-map"></div>';

        // Generate unique markers data with properties
        $markersData = array();
        foreach ($features as $index => $feature) {
            // Extract relevant data for the marker
            $lat = $feature['geometry']['coordinates'][1];
            $lng = $feature['geometry']['coordinates'][0];
            $checkinId = $feature['properties']['checkinId'] ?? null;

            // Skip if no checkinId
            if (!$checkinId) {
                continue;
            }

            // Build the marker data
            $markersData[] = array(
                'position' => array('lat' => $lat, 'lng' => $lng),
                'id' => $checkinId,
            );
        }

        wp_enqueue_script(
            'jobcapturepro-map',
            plugin_dir_url(dirname(__FILE__)) . 'assets/js/map.js',
            array('google-maps', 'markerclusterer'),
            '1.0.0',
            true
        );

        wp_localize_script(
            'jobcapturepro-map',
            'jobcaptureproMapData',
            array(
                'wpPluginApiBaseUrl' => JobCaptureProAPI::get_wp_plugin_api_base_url(),

                //
                'centerLat' => (float)$centerLat,
                'centerLng' => (float)$centerLng,
                'minLat' => (float)$minLat,
                'minLng' => (float)$minLng,
                'maxLat' => (float)$maxLat,
                'maxLng' => (float)$maxLng,
                'markersData' => $markersData,
            )
        );

        // Enqueue styles for the map
        self::enqueue_map_styles();
        $output .= self::get_dynamic_selectors_checkins_grid_styles();

        return $output;
    }

    /**
     * Generate HTML for company information
     * 
     * @param array $company_info Company data
     * @return string HTML for company info section
     */
    public static function render_company_info($company_info)
    {
        // Check for required fields
        if (empty($company_info['name']) || empty($company_info['address'])) {
            return '';
        }

        $output = '<div class="jobcapturepro-company-info">';

        // Company details (now comes first)
        $output .= '<div class="jobcapturepro-company-details">
            <h2 class="jobcapturepro-company-name">' . esc_html($company_info['name']) . '</h2>';

        // Intro text
        $output .= '<div class="jobcapturepro-company-info-text">
            <p>' . esc_html($company_info['address']) . '</p>
        </div>';

        $output .= '<div class="jobcapturepro-company-div-2">';

        // Company Reviews text
        $output .= '<div class="jobcapturepro-company-reviews-text">
            <p>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                    <path d="M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C43.3 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z"/>
                </svg>
                <span>No Reviews</span>
            </p>
        </div>';


        // Check if we have either phone or URL
        $has_phone = !empty($company_info['tn']);
        $has_url = !empty($company_info['url']);

        if ($has_phone) {
            $output .= '<p> <strong> &nbsp;.&nbsp; </strong><a href="tel:' . esc_attr(preg_replace('/[^0-9]/', '', $company_info['tn'])) . '">' . esc_html($company_info['phoneNumberString']) . '</a></p>';
        }

        if ($has_url) {
            $parsed_url = parse_url($company_info['url']);
            $host = $parsed_url['host'] ?? $company_info['url'];

            // Remove www. prefix if it exists
            $display_url = preg_replace('/^www\./i', '', $host);

            $output .= '<p> <strong> &nbsp;.&nbsp; </strong><a href="' . esc_url($company_info['url']) . '" target="_blank" rel="noopener noreferrer">' . esc_html($display_url) . '</a></p>';
        }

        // Show message if no contact info
        if (!$has_phone && !$has_url) {
            $output .= '<p class="jobcapturepro-no-contact-info">Contact No. and Website information not available</p>';
        }

        $output .= '</div>'; // Close jobcapturepro-company-div-2

        $output .= '<div class="jobcapturepro-company-description">
            <p>' . esc_html($company_info['description']) . '</p>
        </div>';
        $output .= '</div>'; // jobcapturepro-company-details

        // Quote btn and text

        $output .= '<div class="jobcapturepro-company-logo">
            <a href="' . esc_url($company_info['quoteUrl']) . '" class="quote-btn">Get a Quote</a>
            <p class="powered-by">Powered by <a href="https://jobcapturepro.com">JobCapturePro</a></p>
        </div>';


        // Logo (now comes after details)
        //if (!empty($company_info['logoUrl'])) {
        //    $output .= '<div class="jobcapturepro-company-logo">
        //        <img src="' . esc_url($company_info['logoUrl']) . '" alt="' . esc_attr($company_info['name']) . ' Logo">
        //    </div>';
        //}

        $output .= '</div>'; // Close jobcapturepro-company-info

        // Enqueue styles 
        self::enqueue_company_info_styles();

        return $output;
    }

    /**
     * Enqueue styles for the map
     * 
     * @return void
     */
    private static function enqueue_map_styles()
    {
        wp_enqueue_style(
            'jobcapturepro-map',
            plugin_dir_url(dirname(__FILE__)) . 'assets/css/map.css',
            array(),
            '1.0.0',
            'all'
        );
    }

    /**
     * Enqueue styles for company info
     * 
     * @return void
     */
    private static function enqueue_company_info_styles()
    {
        wp_enqueue_style(
            'jcp-company-info-styles',
            plugin_dir_url(dirname(__FILE__)) . '/assets/css/company-info.css',
            array(),
            '1.0.0',
            'all'
        );
    }

    /**
     * Enqueue styles for single checkin
     * 
     * @return void
     */
    private static function enqueue_single_checkin_styles()
    {
        wp_enqueue_style(
            'jcp-single-checkin',
            plugin_dir_url(dirname(__FILE__)) . '/assets/css/single-checkin.css',
            array(),
            '1.0.0',
            'all'
        );
    }

    /**
     * Enqueue checkins grid styles
     */
    private static function enqueue_checkins_grid_styles() {
        wp_enqueue_style(
            'jcp-checkins-grid',
            plugin_dir_url(dirname(__FILE__)) . '/assets/css/checkins-grid.css',
            array(),
            '1.0.0',
            'all'
        );
    }
}
