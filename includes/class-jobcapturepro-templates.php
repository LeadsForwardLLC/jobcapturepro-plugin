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
            return JobCaptureProTemplates::render_checkins_grid($checkins, $company_info);
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

        // Allow conditional rendering based on checkin_id - currently not used but can be extended
        if ($checkin_id) {
            return JobCaptureProTemplates::render_multimap($locations, $maps_api_key);
        } else {
            return JobCaptureProTemplates::render_multimap($locations, $maps_api_key);
        }
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

        $output = '<div class="jcp-combined-components">';

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
     * Generate CSS styles for a single checkin page
     * 
     * @return string CSS styles for a single checkin page
     */
    private static function get_single_checkin_styles()
    {
        return '<style>
            .jcp-checkins-page {
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            }

            .jcp-checkin-description {
                background: #f5f5f5;
                padding: 15px;
                border-radius: 8px;
                margin-bottom: 20px;
            }
        </style>';
    }

    /**
     * Generate HTML for a single checkin page matching screenshot style
     */
    public static function render_single_checkin($checkin, $company_info = array())
    {
        $output = '<div class="jcp-single-checkin">';

        // Add CSS styles
        $output .= '<style>
        .jcp-single-checkin {
            margin: 0 auto;
            padding: 20px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            color: #333;
            line-height: 1.5;
            background: #f9f9f9;
        }
        
        .jcp-flex-div{
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 2rem;
            max-width: 1200px;
            margin: 2rem 5rem auto;
            padding: 0 2rem;
            align-items: start;
            border-bottom: 1px solid #eee;
            padding-bottom: 3rem;
        }

        .jcp-ts-div{
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 2rem;
            max-width: 1200px;
            margin: 2rem 5rem auto;
            padding: 2rem 2rem;
            align-items: start;
        }

        .jcp-faq-div{
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 2rem;
            max-width: 1200px;
            margin: 2rem 0rem auto;
            padding: 2rem 0;
            align-items: start;
            border-top: 1px solid #eee;
            padding-bottom: 3rem;
        }

        .jcp-checkin-header{
            margin-bottom: 20px;
            background: #fff;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
        }
        .jcp-content-block {
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            background: #fff;
        }
        
        .jcp-hero-img {
            margin: 15px 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            height: auto;
            object-fit: cover;
            border-bottom: 1px solid #eee;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }
        
        .jcp-checkin-header h1 {
            font-size: 24px;
            margin: 0 0 5px 0;
            color: #222;
        }
        
        .jcp-checkin-meta {
            display: flex;
            gap: 15px;
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
            justify-content: space-between;
        }
        
        .jcp-checkin-description {
            font-size: 15px;
            margin: 15px 0;
        }
        
        .jcp-section-title {
            margin: 0 0 15px 0;
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 1rem;
            color: #222;
        }
        
        .jcp-review-content {
           margin-bottom: 15px;
            background: #fef9c3;
            border-left: 4px solid #facc15;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            line-height: 1.5;
        }
        
        .jcp-review-text {
            font-style: italic;
            margin: 0 0 5px 0;
        }
        
        .jcp-review-author {
            font-weight: bold;
            text-align: right;
            margin: 0;
        }
        
        .jcp-verified-badge {
            background: #f9f9f9;
            background-color: #e6f4ea;
            color: #1e7f3e;
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
            border-radius: 999px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            width: 100%;
            margin-top: 20px;
            margin-bottom: 15px;
        }
        
        .jcp-verified-badge svg {
            width: 18px;
            height: 18px;
            fill: #1e7f3e;
        }

        .jcp-cta-link {
            font-size: 15px;
            margin: 15px 0 0 0;
        }
        
        .jcp-cta-link strong {
            color: #e74c3c;
        }
        
        .jcp-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        
        .jcp-list li {
            padding: 5px 0;
            font-size: 15px;
        }
        
        .jcp-tags-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .jcp-tags-table td {
            padding: 8px;
            border: 1px solid #e0e0e0;
            background: #f8f9fa;
        }

        .jcp-tag-list{
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .job-tag{
            background-color: #e9e9e9;
            color: #000;
            padding: 0.3rem 0.6rem;
            font-size: 0.8rem;
            border-radius: 999px;
            display: inline-block;
            margin: 6px;
        }

        .jcp-title-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }

         .jcp-title-container .jcp-hero-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 50px;
        }

        .jcp-job-reviews {
            display: flex;
            gap: 2px;
            margin: 5px 0;
            justify-content: end;
        }

        .jcp-job-reviews svg{
            width: 18px;
            height: 18px;
            fill: #facc15;
        }

        .get-quote-btn {
            background-color: #ff503e;
            color: #fff;
            padding: 0.75rem 1.25rem;
            border-radius: 999px;
            font-weight: 600;
            font-size: 1rem;
            display: inline-block;
            text-align: center;
            transition: background-color 0.2s ease;
            width: 100%;
        }

        .jcp-related-checkins{
            margin-top: 2rem;
            border-top: 1px solid #eee;
            padding-top: 1.25rem;
        }

        .jcp-list li {
            padding: 5px 0;
            font-size: 0.9rem;
            color: #333;
        }

        .jcp-list svg {
            width: 14px;
            height: 14px;
            fill: #999;
            margin-right: 5px;
        }

        .jcp-checkin-date {
            display: flex;
            align-items: center;
        }

         .jcp-checkin-meta span {
           font-size: 0.95rem;
            color: #666;
        }

        .jcp-checkin-meta svg {
            width: 16px;
            height: 16px;
            fill: #999;
            margin-right: 0.4rem;
        }

         /* Hide specific elements on single check-in page */
        .jcp-company-details,
        .jcp-heatmap,
        .jcp-gallery-filters,
        .jcp-company-info {
            display: none !important;
        }
        
        @media (max-width: 600px) {
            .jcp-single-checkin {
                padding: 15px;
            }
            
            .jcp-content-block {
                padding: 15px;
            }
            
            .jcp-checkin-meta {
                flex-direction: column;
                gap: 5px;
            }

            .jcp-flex-div, .jcp-ts-div, .jcp-faq-div {
                display: contents;
        }

        .jcp-section-title {
                margin-top: 2rem;
        }
    }
    </style>';

        // First content block (header and description)
        $output .= '<div class="jcp-single-content-block">';

        $output .= '<div class="jcp-flex-div">';

        $output .= '<div class="jcp-checkin-header">';
        $output .= '<div class="jcp-title-container">';

        // Use actual checkin image or fallback
        $hero_image = !empty($checkin['imageUrls'][0]) ? $checkin['imageUrls'][0] : 'https://procleaneverything.com/wp-content/uploads/2021/01/Nate-with-Truck-Header-Forward-1.jpeg';
        $output .= '<img class="jcp-hero-img" src="' . esc_url($hero_image) . '" alt="' . esc_attr($checkin['title'] ?? 'Job Image') . '">';
        $output .= '<h1>' . esc_html($checkin['title'] ?? 'Roof Soft Wash in Venice, FL') . '</h1>';
        $output .= '</div>';
        // $output .= '<h1>' . esc_html($checkin['title'] ?? 'Roof Soft Wash in Venice, FL') . '</h1>';
        // Add the hero image below the title
        $hero_image_full = !empty($checkin['imageUrls'][1]) ? $checkin['imageUrls'][1] : $hero_image;
        $output .= '<img class="jcp-hero-img" src="' . esc_url($hero_image_full) . '" alt="' . esc_attr($checkin['title'] ?? 'Job Image') . '">';
        $output .= '<div class="jcp-checkin-meta">';

        // Use actual data or fallbacks
        $checkin_date = isset($checkin['createdAt']) ? date('F j, Y', $checkin['createdAt']) : 'July 6, 2025';
        $tech_name = isset($checkin['assignedUser']['name']) ? $checkin['assignedUser']['name'] : 'Chris (Tech)';
        $location = isset($checkin['address']) ? $checkin['address'] : 'Venice, FL';

        $output .= '<span class="jcp-checkin-date"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M224 64C241.7 64 256 78.3 256 96L256 128L384 128L384 96C384 78.3 398.3 64 416 64C433.7 64 448 78.3 448 96L448 128L480 128C515.3 128 544 156.7 544 192L544 480C544 515.3 515.3 544 480 544L160 544C124.7 544 96 515.3 96 480L96 192C96 156.7 124.7 128 160 128L192 128L192 96C192 78.3 206.3 64 224 64zM160 304L160 336C160 344.8 167.2 352 176 352L208 352C216.8 352 224 344.8 224 336L224 304C224 295.2 216.8 288 208 288L176 288C167.2 288 160 295.2 160 304zM288 304L288 336C288 344.8 295.2 352 304 352L336 352C344.8 352 352 344.8 352 336L352 304C352 295.2 344.8 288 336 288L304 288C295.2 288 288 295.2 288 304zM432 288C423.2 288 416 295.2 416 304L416 336C416 344.8 423.2 352 432 352L464 352C472.8 352 480 344.8 480 336L480 304C480 295.2 472.8 288 464 288L432 288zM160 432L160 464C160 472.8 167.2 480 176 480L208 480C216.8 480 224 472.8 224 464L224 432C224 423.2 216.8 416 208 416L176 416C167.2 416 160 423.2 160 432zM304 416C295.2 416 288 423.2 288 432L288 464C288 472.8 295.2 480 304 480L336 480C344.8 480 352 472.8 352 464L352 432C352 423.2 344.8 416 336 416L304 416zM416 432L416 464C416 472.8 423.2 480 432 480L464 480C472.8 480 480 472.8 480 464L480 432C480 423.2 472.8 416 464 416L432 416C423.2 416 416 423.2 416 432z"></path></svg>' . esc_html($checkin_date) . '</span>';
        $output .= '<span class="jcp-checkin-tech"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M224 248a120 120 0 1 0 0-240 120 120 0 1 0 0 240zm-29.7 56C95.8 304 16 383.8 16 482.3 16 498.7 29.3 512 45.7 512l356.6 0c16.4 0 29.7-13.3 29.7-29.7 0-98.5-79.8-178.3-178.3-178.3l-59.4 0z"/></svg>' . esc_html($tech_name) . '</span>';
        $output .= '<span class="jcp-checkin-location"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M0 188.6C0 84.4 86 0 192 0S384 84.4 384 188.6c0 119.3-120.2 262.3-170.4 316.8-11.8 12.8-31.5 12.8-43.3 0-50.2-54.5-170.4-197.5-170.4-316.8zM192 256a64 64 0 1 0 0-128 64 64 0 1 0 0 128z"/></svg>' . esc_html($location) . '</span>';
        $output .= '</div>';
        $output .= '<div class="jcp-checkin-description">';

        // Use actual description or fallback
        $description = isset($checkin['description']) ? $checkin['description'] : 'Roof soft-washed to remove algae and restore curb appeal. This 2-story home was cleaned using a low-pressure rinse method safe for shingles and gutters. Job completed in under 2 hours.';
        $output .= '<p>' . esc_html($description) . '</p>';
        $output .= '</div>';
        $output .= '</div>'; // close first content block

        // Second content block (all other sections)
        $output .= '<div class="jcp-content-block">';

        // Review section - only show if customer review data exists
        $show_reviews = self::should_show_feature('show_customer_reviews', !empty($checkin['customer_review']));
        if ($show_reviews && !empty($checkin['customer_review'])) {
            $output .= '<div class="jcp-checkin-review">';
            $output .= '<h2 class="jcp-section-title">Review</h2>';
            $output .= '<div class="jcp-review-content">';
            $output .= '<p class="jcp-review-text">"' . esc_html($checkin['customer_review']['text']) . '"</p>';
            $output .= '<p class="jcp-review-author">– ' . esc_html($checkin['customer_review']['author']) . '</p>';
            $output .= '</div>';
        } else {
            // Fallback to hard-coded review if feature is enabled but no data
            $show_fallback_review = self::should_show_feature('show_customer_reviews', true);
            if ($show_fallback_review) {
                $output .= '<div class="jcp-checkin-review">';
                $output .= '<h2 class="jcp-section-title">Review</h2>';
                $output .= '<div class="jcp-review-content">';
                $output .= '<p class="jcp-review-text">"Looks brand new! Friendly, professional, fast. Highly recommend."</p>';
                $output .= '<p class="jcp-review-author">– Danielle P.</p>';
                $output .= '</div>';
            }
        }

        // Star ratings - only show if rating data exists or feature is enabled
        $show_ratings = self::should_show_feature('show_star_ratings', !empty($checkin['rating']));
        if ($show_ratings && !empty($checkin['rating'])) {
            $rating = min(5, max(1, (int)$checkin['rating'])); // Ensure 1-5 range
            $output .= '<div class="jcp-job-reviews">';
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
                $output .= '<div class="jcp-job-reviews">
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
            $output .= '<div class="jcp-verified-badge"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M256 512a256 256 0 1 0 0-512 256 256 0 1 0 0 512zm84.4-299.3l-80 128c-4.2 6.7-11.4 10.9-19.3 11.3s-15.5-3.2-20.2-9.6l-48-64c-8-10.6-5.8-25.6 4.8-33.6s25.6-5.8 33.6 4.8l27 36 61.4-98.3c7-11.2 21.8-14.7 33.1-7.6s14.7 21.8 7.6 33.1z"/></svg> Verified Job Check-In</div>';
        }

        $output .= '<a href="#" class="get-quote-btn">Get a Quote Like This</a>';

        // Related check-ins - only show if related checkins exist
        $show_related = !empty($checkin['related_checkins']) && is_array($checkin['related_checkins']);
        if ($show_related) {
            $output .= '<div class="jcp-related-checkins">';
            $output .= '<h2 class="jcp-section-title">Related Check-ins</h2>';
            $output .= '<ul class="jcp-list">';
            foreach ($checkin['related_checkins'] as $related) {
                $output .= '<li><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M338.8-9.9c11.9 8.6 16.3 24.2 10.9 37.8L271.3 224 416 224c13.5 0 25.5 8.4 30.1 21.1s.7 26.9-9.6 35.5l-288 240c-11.3 9.4-27.4 9.9-39.3 1.3s-16.3-24.2-10.9-37.8L176.7 288 32 288c-13.5 0-25.5-8.4-30.1-21.1s-.7-26.9 9.6-35.5l288-240c11.3-9.4 27.4-9.9 39.3-1.3z"/></svg> ' . esc_html($related['title']) . '</li>';
            }
            $output .= '</ul>';
            $output .= '</div>';
        } else {
            // Fallback to hard-coded related checkins
            $output .= '<div class="jcp-related-checkins">';
            $output .= '<h2 class="jcp-section-title">Related Check-ins</h2>';
            $output .= '<ul class="jcp-list">';
            $output .= '<li><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M338.8-9.9c11.9 8.6 16.3 24.2 10.9 37.8L271.3 224 416 224c13.5 0 25.5 8.4 30.1 21.1s.7 26.9-9.6 35.5l-288 240c-11.3 9.4-27.4 9.9-39.3 1.3s-16.3-24.2-10.9-37.8L176.7 288 32 288c-13.5 0-25.5-8.4-30.1-21.1s-.7-26.9 9.6-35.5l288-240c11.3-9.4 27.4-9.9 39.3-1.3z"/></svg> Driveway Pressure Wash – Sarasota</li>';
            $output .= '<li><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free v5.15.4 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M562.1 383.9c-21.5-2.4-42.1-10.5-57.9-22.9-14.1-11.1-34.2-11.3-48.2 0-37.9 30.4-107.2 30.4-145.7-1.5-13.5-11.2-33-9.1-46.7 1.8-38 30.1-106.9 30-145.2-1.7-13.5-11.2-33.3-8.9-47.1 2-15.5 12.2-36 20.1-57.7 22.4-7.9.8-13.6 7.8-13.6 15.7v32.2c0 9.1 7.6 16.8 16.7 16 28.8-2.5 56.1-11.4 79.4-25.9 56.5 34.6 137 34.1 192 0 56.5 34.6 137 34.1 192 0 23.3 14.2 50.9 23.3 79.1 25.8 9.1.8 16.7-6.9 16.7-16v-31.6c.1-8-5.7-15.4-13.8-16.3zm0-144c-21.5-2.4-42.1-10.5-57.9-22.9-14.1-11.1-34.2-11.3-48.2 0-37.9 30.4-107.2 30.4-145.7-1.5-13.5-11.2-33-9.1-46.7 1.8-38 30.1-106.9 30-145.2-1.7-13.5-11.2-33.3-8.9-47.1 2-15.5 12.2-36 20.1-57.7 22.4-7.9.8-13.6 7.8-13.6 15.7v32.2c0 9.1 7.6 16.8 16.7 16 28.8-2.5 56.1-11.4 79.4-25.9 56.5 34.6 137 34.1 192 0 56.5 34.6 137 34.1 192 0 23.3 14.2 50.9 23.3 79.1 25.8 9.1.8 16.7-6.9 16.7-16v-31.6c.1-8-5.7-15.4-13.8-16.3zm0-144C540.6 93.4 520 85.4 504.2 73 490.1 61.9 470 61.7 456 73c-37.9 30.4-107.2 30.4-145.7-1.5-13.5-11.2-33-9.1-46.7 1.8-38 30.1-106.9 30-145.2-1.7-13.5-11.2-33.3-8.9-47.1 2-15.5 12.2-36 20.1-57.7 22.4-7.9.8-13.6 7.8-13.6 15.7v32.2c0 9.1 7.6 16.8 16.7 16 28.8-2.5 56.1-11.4 79.4-25.9 56.5 34.6 137 34.1 192 0 56.5 34.6 137 34.1 192 0 23.3 14.2 50.9 23.3 79.1 25.8 9.1.8 16.7-6.9 16.7-16v-31.6c.1-8-5.7-15.4-13.8-16.3z"/></svg> Pool Deck Cleaning – Nokomis</li>';
            $output .= '<li><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free v5.15.4 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M464 32H48C21.5 32 0 53.5 0 80v352c0 26.5 21.5 48 48 48h416c26.5 0 48-21.5 48-48V80c0-26.5-21.5-48-48-48zm-16 160H64v-84c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12v84z"/></svg> Window Cleaning – Lakewood Ranch</li>';
            $output .= '</ul>';
            $output .= '</div>';
        }
        $output .= '</div>';

        $output .= '</div>'; // close second content block

        $output .= '</div>'; // close jcp-flex-div

        // Testimonials & Services div
        $output .= '<div class="jcp-ts-div">';

        // Testimonials - only show if testimonials exist or pass company_info parameter
        $show_testimonials = !empty($company_info['testimonials']) && is_array($company_info['testimonials']);
        if ($show_testimonials) {
            $output .= '<div class="jcp-testimonials">';
            $output .= '<h2 class="jcp-section-title">What Homeowners Say</h2>';
            $output .= '<ul class="jcp-list">';
            foreach ($company_info['testimonials'] as $testimonial) {
                $output .= '<li>"' . esc_html($testimonial['text']) . '" – ' . esc_html($testimonial['author']) . '</li>';
            }
            $output .= '</ul>';
            $output .= '</div>';
        } else {
            // Fallback to hard-coded testimonials
            $output .= '<div class="jcp-testimonials">';
            $output .= '<h2 class="jcp-section-title">What Homeowners Say</h2>';
            $output .= '<ul class="jcp-list">';
            $output .= '<li>"Cleaned it like new in 2 hours." – Brian M.</li>';
            $output .= '<li>"Didn\'t even need to be home." – Linda R.</li>';
            $output .= '<li>"No upsells. Just results." – Mark D.</li>';
            $output .= '</ul>';
            $output .= '</div>';
        }

        // Service tags - use actual tags if available
        $show_service_tags = !empty($checkin['service_tags']) && is_array($checkin['service_tags']);
        if ($show_service_tags) {
            $output .= '<div class="jcp-service-tags">';
            $output .= '<h2 class="jcp-section-title">Service Tags</h2>';
            $output .= '<div class="jcp-tags-list">';
            foreach ($checkin['service_tags'] as $tag) {
                $output .= '<span class="job-tag">' . esc_html($tag) . '</span>';
            }
            $output .= '</div>';
            $output .= '</div>';
        } else {
            // Fallback to hard-coded service tags
            $output .= '<div class="jcp-service-tags">';
            $output .= '<h2 class="jcp-section-title">Nearby Service Tags</h2>';
            $output .= '<div class="jcp-tags-list">';
            $output .= '<span class="job-tag">Venice, FL</span>';
            $output .= '<span class="job-tag">Roof Cleaning</span>';
            $output .= '<span class="job-tag">Soft Wash</span>';
            $output .= '<span class="job-tag">Exterior Algae</span>';
            $output .= '</div>';
            $output .= '</div>';
        }

        $output .= '</div>'; // close Testimonials & Services div

        // FAQ div
        $output .= '<div class="jcp-faq-div">';

        // FAQs
        $output .= '<div class="jcp-faqs">';
        $output .= '<h2 class="jcp-section-title">FAQs</h2>';
        $output .= '<ul class="jcp-list">';
        $output .= '<li>► Do I need to be home?</li>';
        $output .= '<li>► How long does it take?</li>';
        $output .= '</ul>';
        $output .= '</div>';

        $output .= '</div>'; // close FAQ div

        $output .= '</div>'; // close jcp-single-checkin

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
        $current_url = $_SERVER['REQUEST_URI'];
        $checkin_url = add_query_arg('checkinId', $checkin['id'], $current_url);

        $output = '<a href="' . esc_url($checkin_url) . '" class="jcp-checkin-card" style="text-decoration: none; color: inherit;">';

        // Images (if available)
        if (!empty($checkin['imageUrls']) && is_array($checkin['imageUrls'])) {
            $output .= self::render_images_gallery($checkin['imageUrls']);
        }

        // User info (if available)
        if (!empty($checkin['assignedUser'])) {
            $output .= '<div class="jcp-checkin-user">';

            // Profile image
            if (!empty($checkin['assignedUser']['profileImageUrl'])) {
                $output .= '<div class="jcp-user-image">
                    <img src="' . esc_url($checkin['assignedUser']['profileImageUrl']) . '" alt="User profile">
                </div>';
            }

            // User name
            if (!empty($checkin['assignedUser']['name'])) {
                $output .= '<div class="jcp-user-name">
                    <p>' . esc_html($checkin['assignedUser']['name']) . '</p>
                </div>';
            }

            // job Reviews
            $output .= '<div class="jcp-job-reviews">
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
        $output .= '<div class="jcp-checkin-description">
            <p>' . nl2br(esc_html($checkin['description'])) . '</p>
        </div>';

        // Date - Simplified to only show the formatted date
        $output .= '<div class="jcp-checkin-date">';
        $timestamp = $checkin['createdAt'];
        $output .= '<p class="date-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M224 64C241.7 64 256 78.3 256 96L256 128L384 128L384 96C384 78.3 398.3 64 416 64C433.7 64 448 78.3 448 96L448 128L480 128C515.3 128 544 156.7 544 192L544 480C544 515.3 515.3 544 480 544L160 544C124.7 544 96 515.3 96 480L96 192C96 156.7 124.7 128 160 128L192 128L192 96C192 78.3 206.3 64 224 64zM160 304L160 336C160 344.8 167.2 352 176 352L208 352C216.8 352 224 344.8 224 336L224 304C224 295.2 216.8 288 208 288L176 288C167.2 288 160 295.2 160 304zM288 304L288 336C288 344.8 295.2 352 304 352L336 352C344.8 352 352 344.8 352 336L352 304C352 295.2 344.8 288 336 288L304 288C295.2 288 288 295.2 288 304zM432 288C423.2 288 416 295.2 416 304L416 336C416 344.8 423.2 352 432 352L464 352C472.8 352 480 344.8 480 336L480 304C480 295.2 472.8 288 464 288L432 288zM160 432L160 464C160 472.8 167.2 480 176 480L208 480C216.8 480 224 472.8 224 464L224 432C224 423.2 216.8 416 208 416L176 416C167.2 416 160 423.2 160 432zM304 416C295.2 416 288 423.2 288 432L288 464C288 472.8 295.2 480 304 480L336 480C344.8 480 352 472.8 352 464L352 432C352 423.2 344.8 416 336 416L304 416zM416 432L416 464C416 472.8 423.2 480 432 480L464 480C472.8 480 480 472.8 480 464L480 432C480 423.2 472.8 416 464 416L432 416C423.2 416 416 423.2 416 432z"/></svg>' . esc_html(date('F j, Y', $timestamp)) . '</p>'; // Format: "Month Day, Year" (e.g., "July 23, 2025")
        $output .= '</div>';

        // Address - Extract city and state only
        $output .= '<div class="jcp-checkin-address">';

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
            return $b['createdAt'] - $a['createdAt'];
        });

        // Container with CSS Grid for responsive layout
        $output = '<div class="jcp-container">';

        // Unique ID for this grid
        $gridId = 'jcp-grid-' . wp_rand();

        // Add CSS for modern responsive grid
        $output .= self::get_checkins_grid_styles($gridId);

        // Grid container with data attribute to store the column count
        $output .= '<div class="jcp-checkins-grid ' . $gridId . '" data-column-count="3">';

        // Add each checkin to the grid in date-sorted order
        foreach ($checkins as $checkin) {
            $output .= self::render_checkin_card($checkin);
        }

        $output .= '</div>'; // Close grid
        $output .= '</div>'; // Close container

        // jcp stats section - only show if stats data is available or feature is enabled
        $show_stats = self::should_show_feature('show_company_stats', !empty($company_info['stats']));
        if ($show_stats && !empty($company_info['stats'])) {
            $output .= '<div class="jcp-stats-container">';

            if (!empty($company_info['stats']['jobs_this_month'])) {
                $output .= '<div class="jcp-stat-item">
                <div class="jcp-stat-number">' . esc_html($company_info['stats']['jobs_this_month']) . '</div>
                <div class="jcp-stat-label">Jobs Posted This Month</div>
            </div>';
            }

            if (!empty($company_info['stats']['average_rating'])) {
                $output .= '<div class="jcp-stat-item">
                <div class="jcp-stat-number">' . esc_html($company_info['stats']['average_rating']) . '</div>
                <div class="jcp-stat-label">Average Rating</div>
            </div>';
            }

            if (!empty($company_info['stats']['last_checkin'])) {
                $output .= '<div class="jcp-stat-item">
                <div class="jcp-stat-number">' . esc_html($company_info['stats']['last_checkin']) . '</div>
                <div class="jcp-stat-label">Last Job Check-In</div>
            </div>';
            }

            $output .= '</div>'; // Close jcp-stats-container
        } else {
            // Fallback to hard-coded stats if feature is enabled but no data
            $show_fallback_stats = self::should_show_feature('show_company_stats', true);
            if ($show_fallback_stats) {
                $output .= '<div class="jcp-stats-container">';

                $output .= '<div class="jcp-stat-item">
                <div class="jcp-stat-number">86</div>
                <div class="jcp-stat-label">Jobs Posted This Month</div>
            </div>';

                $output .= '<div class="jcp-stat-item">
                <div class="jcp-stat-number">96%</div>
                <div class="jcp-stat-label">Average 5-Star Rating</div>
            </div>';

                $output .= '<div class="jcp-stat-item">
                <div class="jcp-stat-number">12 mins ago</div>
                <div class="jcp-stat-label">Last Job Check-In</div>
            </div>';

                $output .= '</div>'; // Close jcp-stats-container
            }
        }

        // jcp CTA section

        $output .= '<div class="jcp-cta-container">';

        // cta Heading
        $output .= '<div class="jcp-cta">
                    <h2>Let Your Work Speak For Itself</h2>
                    <p>Capture check-ins like these with JobCapturePro. Set it and forget it.</p>
                    <a href="#" class="quote-btn">Get JobCapturePro</a>
                    </div>';

        $output .= '</div>'; // Close jcp-cta-container

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

        return $output;
    }

    /**
     * Generate CSS styles for the checkins grid
     * 
     * @param string $gridId The unique ID for the grid
     * @return string CSS styles for the checkins grid
     */
    private static function get_checkins_grid_styles($gridId = null)
    {
        $gridSelector = $gridId ? '.' . $gridId : '.jcp-checkins-grid';

        return '<style>
            .jcp-container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 20px;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            }
            
            ' . $gridSelector . ' {
                /* Keep masonry-style layout with CSS columns */
                column-count: 3;
                column-gap: 20px;
                width: 100%;
            }

            .jcp-checkins-grid {
                gap: 2rem;
                padding: 0rem 0rem 2rem 0rem;
                margin: 0 auto;
            }
                        
            .jcp-checkin-card {
                break-inside: avoid;
                margin-bottom: 2rem;
                background: #fff;
                border-radius: 12px;
                box-shadow: rgba(0, 0, 0, 0.11) 0px 1px 10px 0px;
                overflow: hidden;
                transition: transform 0.2s ease, box-shadow 0.2s ease;
                display: block;
            }

            .jcp-checkin-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            }
            
            /* User info styles */
            .jcp-checkin-user {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 10px 15px;
                border-bottom: 1px solid #f0f0f0;
            }
            
            .jcp-user-image {
                flex: 0 0 40px;
                height: 40px;
                border-radius: 50%;
                overflow: hidden;
            }
            
            .jcp-user-image img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
            }
            
            .jcp-user-name {
                flex-grow: 1;
                text-align: left;
                margin-left: 0.75rem;
            }
            
            .jcp-user-name p {
                margin: 0;
                font-size: 1.05em;
                font-weight: 600;
                color: #333;
            }
            
            /* Image gallery styles */
            .jcp-checkin-image {
                position: relative;
                width: 100%;
                height: 215px;
                overflow: hidden;
                border-radius: 12px;
                margin-bottom: 1.5rem;
            }
            
            .gallery-image {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                opacity: 0;
                transition: opacity 0.3s ease;
                display: none;
            }
            
            .gallery-image.active {
                opacity: 1;
                display: block;
            }
            
            .gallery-image img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
            }
            
            .gallery-nav {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                width: 40px;
                height: 40px;
                background-color: rgba(255, 255, 255, 0.5);
                color: rgba(0, 0, 0, 0.6);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                font-size: 18px;
                font-weight: bold;
                z-index: 2;
                transition: background-color 0.3s ease, color 0.3s ease;
            }
            
            .gallery-nav:hover {
                background-color: rgba(255, 255, 255, 0.8);
                color: rgba(0, 0, 0, 0.8);
            }
            
            .gallery-prev {
                left: 10px;
            }
            
            .gallery-next {
                right: 10px;
            }
            
            .gallery-dots {
                position: absolute;
                bottom: 10px;
                left: 50%;
                transform: translateX(-50%);
                display: flex;
                gap: 8px;
                z-index: 2;
            }
            
            .gallery-dot {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background-color: rgba(255, 255, 255, 0.5);
                cursor: pointer;
                transition: background-color 0.3s ease;
            }
            
            .gallery-dot.active,
            .gallery-dot:hover {
                background-color: rgba(255, 255, 255, 0.9);
            }
            
            .jcp-checkin-description,
            .jcp-checkin-date,
            .jcp-checkin-address {
                padding: 10px 15px;
            }
            
            .jcp-checkin-date {
                font-size: 0.9em;
                color: #666;
                float: left;
            }

            .date-icon{
                display: flex;
                align-items: center;
            }
                        
            .jcp-checkin-address {
                font-size: 0.85em;
                /* background-color: #f8f8f8; */
                /* border-top: 1px solid #eee; */
                text-align: right;
            }
            
            .jcp-checkin-description {
                border-bottom: 1px solid #f0f0f0;
            }

            /*.jcp-checkin-date, .jcp-checkin-address {
                padding: 0 15px;
            }*/

            .jcp-job-reviews {
                display: flex;
                gap: 2px; /* Adjust the space between stars */
                margin: 5px 0; /* Add some vertical spacing */
            }

            .jcp-job-reviews svg {
                width: 18px; /* Adjust star size */
                height: 18px;
                fill: #facc15; /* Star color */
            }

            .date-icon svg{
                width: 16px;
                margin-right: 0.4rem;
                fill: #999;
            }

            .jcp-stats-section {
                background-color: #fff;
                padding: 30px 20px;
                margin: 30px 0;
                text-align: center;
                border-radius: 12px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
            .jcp-stats-container {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                max-width: 1000px;
                margin: 0 auto;
                gap: 0rem;
                background: #fff;
                padding: 2rem 5rem;
        }
        
        .jcp-stat-item {
            flex: 1;
            min-width: 200px;
            padding: 15px;
            text-align: center;
        }
        
        .jcp-stat-number {
            font-size: 2.2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
            line-height: 1;
        }
        
       .jcp-stat-label {
            font-weight: 500;
            font-size: 0.95rem;
            color: #555;
        }

        .jcp-cta-container{
            background-color: #111;
            color: #fff;
            padding: 3rem 2rem;
            text-align: center;
        }

         .jcp-cta-container h2 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .jcp-stats-container {
                flex-direction: column;
                gap: 25px;
            }
            
            .jcp-stat-item {
                min-width: 100%;
            }
            
            .jcp-stat-number {
                font-size: 2rem;
            }
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

         .jcp-single-checkin {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            color: #333;
            line-height: 1.6;
        }
        
        .jcp-checkin-header h1 {
            font-size: 28px;
            margin-bottom: 10px;
            color: #222;
        }
        
        .jcp-checkin-meta {
            display: flex;
            gap: 15px;
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
        }
        
        .jcp-checkin-description {
            margin: 0px 0;
            font-size: 16px;
        }
        
        .jcp-divider {
            height: 1px;
            background-color: #eee;
            margin: 30px 0;
        }
        
        .jcp-checkin-review h2,
        .jcp-related-checkins h2,
        .jcp-testimonials h2,
        .jcp-faqs h2,
        .jcp-service-tags h2 {
            font-size: 20px;
            margin-bottom: 15px;
            color: #222;
        }
        
        .jcp-review-content {
            background: #f8f8f8;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .jcp-review-text {
            font-style: italic;
            margin-bottom: 5px;
        }
        
        .jcp-review-author {
            font-weight: bold;
            text-align: right;
        }
        
        .jcp-verified-badge {
            color: #e74c3c;
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .jcp-cta-link {
            font-size: 16px;
            margin: 15px 0;
        }
        
        .jcp-cta-link strong {
            color: #e74c3c;
        }
        
        .jcp-related-list,
        .jcp-testimonial-list,
        .jcp-faq-list {
            list-style-type: none;
            padding-left: 0;
        }
        
        .jcp-related-list li,
        .jcp-testimonial-list li,
        .jcp-faq-list li {
            padding: 8px 0;
            font-size: 15px;
        }
        
        .jcp-tags-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .jcp-tags-table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        
        @media (max-width: 600px) {
            .jcp-single-checkin {
                padding: 15px;
            }
            
            .jcp-checkin-header h1 {
                font-size: 24px;
            }
            
            .jcp-checkin-meta {
                flex-direction: column;
                gap: 5px;
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

        $output = '<div class="jcp-checkin-image" id="' . $galleryId . '">';

        // Add all images but only first is visible initially
        foreach ($imageUrls as $index => $imageUrl) {
            $activeClass = $index === 0 ? ' active' : '';
            $output .= '<div class="gallery-image' . $activeClass . '" data-index="' . $index . '">
                <img src="' . esc_url($imageUrl) . '" alt="Checkin image ' . ($index + 1) . '">
            </div>';
        }

        // Add navigation arrows if there are multiple images
        if ($showArrows) {
            $output .= '<div class="gallery-nav gallery-prev" onclick="changeImage(\'' . $galleryId . '\', \'prev\')">&#10094;</div>';
            $output .= '<div class="gallery-nav gallery-next" onclick="changeImage(\'' . $galleryId . '\', \'next\')">&#10095;</div>';
            $output .= '<div class="gallery-dots">';

            // Add indicator dots
            for ($i = 0; $i < $imageCount; $i++) {
                $activeClass = $i === 0 ? ' active' : '';
                $output .= '<span class="gallery-dot' . $activeClass . '" onclick="showImage(\'' . $galleryId . '\', ' . $i . ')"></span>';
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
            function changeImage(galleryId, direction) {
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
            
            function showImage(galleryId, index) {
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
     * Generate HTML for a Google Maps heatmap
     * 
     * @param array $locations The location data as defined by geopoints in RFC 7946
     * @return string HTML for a Google Maps heatmap
     */
    public static function render_heatmap($locations, $maps_api_key)
    {
        // Check for required fields
        if (empty($locations)) {
            return '';
        }

        // Get the API Key from the plugin options
        $options = get_option('jobcapturepro_options');

        // Ensure necessary scripts are loaded
        wp_enqueue_script('google-maps', 'https://maps.googleapis.com/maps/api/js?libraries=visualization&key=' . $maps_api_key, array(), null, array('strategy' => 'async'));

        // Extract features array from the GeoJSON FeatureCollection
        $features = $locations['features'];

        // Determine the bounds for the map
        list($minLat, $maxLat, $minLng, $maxLng) = self::determine_bounds($features);

        // Start building HTML output
        $output = '<div id="heatmap" class="jcp-heatmap"></div>';

        // Add filter buttons UI
        $output .= '<div class="jcp-gallery-filters">';
        $output .= '<button class="jcp-filter-btn active" data-filter="all">All Jobs</button>';
        $output .= '<button class="jcp-filter-btn" data-filter="pressure-washing">Pressure Washing</button>';
        $output .= '<button class="jcp-filter-btn" data-filter="roof-cleaning">Roof Cleaning</button>';
        $output .= '<button class="jcp-filter-btn" data-filter="window-cleaning">Window Cleaning</button>';
        $output .= '<button class="jcp-filter-btn" data-filter="5-star">5-Star Jobs</button>';
        $output .= '</div>';

        // Add CSS for modern responsive grid
        $output .= self::get_heatmap_styles();

        $output .= '<script>
        function initHeatMap() {
            const map = new google.maps.Map(document.getElementById("heatmap"), {
                mapTypeId: "roadmap"
            });
            
            // Define bounds for the map
            const bounds = new google.maps.LatLngBounds(
                new google.maps.LatLng(' . $minLat . ', ' . $minLng . '),
                new google.maps.LatLng(' . $maxLat . ', ' . $maxLng . ')
            );
            
            // Fit the map to these bounds
            map.fitBounds(bounds);

            const heatmapData = [' .
            implode(',', array_map(function ($point) {
                return 'new google.maps.LatLng(' . $point['geometry']['coordinates'][1] . ',' . $point['geometry']['coordinates'][0] . ')';
            }, $features)) .
            '];

           

            new google.maps.visualization.HeatmapLayer({
                data: heatmapData,
                map: map
            });
        }
        window.addEventListener(\'load\', initHeatMap);
        </script>';

        return $output;
    }

    /**
     * Generate CSS styles for the heatmap
     * 
     * @return string CSS styles for the heatmap
     */
    private static function get_heatmap_styles()
    {
        return '<style>
            .jcp-heatmap {
                height: 500px;
                width: 80%;
                border-radius: 12px;
                overflow: hidden;
                margin-left: auto;
                margin-right: auto;
                margin-top: 2rem;
            }
            .jcp-gallery-filters {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                justify-content: center;
                padding: 3rem;
            }
                    
            .jcp-filter-btn {
                text-transform: capitalize;
                color: #000;
                background: #e5e5e5;
                border: none;
                padding: 0.6rem 1.2rem;
                border-radius: 999px;
                font-size: 0.95rem;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.2s ease;
            }
                    
            .jcp-filter-btn:hover {
                background: #000;
                color: #fff;
            }
                    
            .jcp-filter-btn.active {
                background: #000;
                color: white;
                border-color: #000;
            }
        </style>';
    }


    /**
     * Generate HTML for a Google Maps multimap directly from check-ins data
     * 
     * @param array $checkins Array of check-in data with full details
     * @param string $maps_api_key Google Maps API key
     * @return string HTML for a Google Maps multimap
     */
    public static function render_multimap_from_checkins_data($checkins, $maps_api_key = '')
    {
        if (empty($checkins)) {
            return 'No check-in data available';
        }

        // Get the API Key from the plugin options if not provided
        if (empty($maps_api_key)) {
            $options = get_option('jobcapturepro_options');
            if (is_array($options) && isset($options['jobcapturepro_field_apikey'])) {
                $maps_api_key = trim($options['jobcapturepro_field_apikey']);
            }
        }

        // Convert check-ins to GeoJSON features
        $features = array();
        foreach ($checkins as $checkin) {
            if (!empty($checkin['location']['latitude']) && !empty($checkin['location']['longitude'])) {
                $features[] = array(
                    'type' => 'Feature',
                    'geometry' => array(
                        'type' => 'Point',
                        'coordinates' => array($checkin['location']['longitude'], $checkin['location']['latitude'])
                    ),
                    'properties' => array(
                        'id' => $checkin['id'],
                        'title' => 'Check-in', // or use $checkin['title'] if available
                        'description' => $checkin['description'] ?? '',
                        'address' => $checkin['address'] ?? '',
                        'createdAt' => $checkin['createdAt'] ?? time(),
                        'imageUrls' => $checkin['imageUrls'] ?? array(),
                        'assignedUser' => $checkin['assignedUser'] ?? array()
                    )
                );
            }
        }

        // Create locations array in the expected format
        $locations = array(
            'type' => 'FeatureCollection',
            'features' => $features
        );

        return self::render_multimap($locations, $maps_api_key);
    }


    /**
     * Generate HTML for a Google Maps map with multiple markers
     * 
     * @param array $locations The location data as defined by geopoints in RFC 7946
     * @return string HTML for a Google Maps map with multiple markers
     */
    public static function render_multimap($locations, $maps_api_key)
    {
        // Check for required fields
        if (empty($locations)) {
            return '';
        }

        // Check if API key is valid
        if (empty($maps_api_key)) {
            return '<div class="jcp-error">Google Maps API key is not configured. Please check your plugin settings.</div>';
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
        $output = '<div id="multimap" class="jcp-multimap"></div>';

        // Add overlay container for check-in cards
        $output .= '<div id="jcp-checkin-overlay" style="display: none;">
            <div class="jcp-checkin-overlay-content">
                <button class="jcp-close-overlay" onclick="closeCheckinOverlay()">&times;</button>
                <div id="jcp-checkin-card-content"></div>
            </div>
        </div>';

        // Add CSS for modern responsive map and overlay
        $output .= self::get_multimap_styles();

        // Add check-in card styles from the grid
        $output .= self::get_checkins_grid_styles();

        // Generate unique markers data with full check-in properties
        $markersData = array();
        foreach ($features as $index => $feature) {
            // Extract relevant data for the marker
            $lat = $feature['geometry']['coordinates'][1];
            $lng = $feature['geometry']['coordinates'][0];

            // Get properties from feature if available - try different property names
            $title = !empty($feature['properties']['title']) ?
                esc_js($feature['properties']['title']) : (!empty($feature['properties']['name']) ? esc_js($feature['properties']['name']) : 'Location ' . ($index + 1));

            $description = !empty($feature['properties']['description']) ?
                esc_js($feature['properties']['description']) : (!empty($feature['properties']['desc']) ? esc_js($feature['properties']['desc']) : '');

            $address = !empty($feature['properties']['address']) ?
                esc_js($feature['properties']['address']) : (!empty($feature['properties']['location']) ? esc_js($feature['properties']['location']) : '');

            $date = !empty($feature['properties']['createdAt']) ?
                date('F j, Y', $feature['properties']['createdAt']) : (!empty($feature['properties']['created_at']) ? date('F j, Y', $feature['properties']['created_at']) : '');

            // Get full check-in data for overlay - try different property names
            $checkin_id = !empty($feature['properties']['id']) ? $feature['properties']['id'] : (!empty($feature['properties']['checkinId']) ? $feature['properties']['checkinId'] : '');

            $image_urls = !empty($feature['properties']['imageUrls']) ? json_encode($feature['properties']['imageUrls']) : (!empty($feature['properties']['images']) ? json_encode($feature['properties']['images']) : '[]');

            $assigned_user = !empty($feature['properties']['assignedUser']) ? json_encode($feature['properties']['assignedUser']) : (!empty($feature['properties']['user']) ? json_encode($feature['properties']['user']) : '{}');

            $created_at = !empty($feature['properties']['createdAt']) ? $feature['properties']['createdAt'] : (!empty($feature['properties']['created_at']) ? $feature['properties']['created_at'] : time());

            // Build the marker data with full check-in information
            $markersData[] = "{
                position: { lat: {$lat}, lng: {$lng} },
                title: " . json_encode($title) . ",
                description: " . json_encode($description) . ",
                address: " . json_encode($address) . ",
                date: " . json_encode($date) . ",
                checkin_id: " . json_encode($checkin_id) . ",
                image_urls: {$image_urls},
                assigned_user: {$assigned_user},
                created_at: {$created_at}
            }";
        }


        $output .= '<script>
            async function initMultiMap() {
                try {
                    // Request needed libraries.
                    const { Map } = await google.maps.importLibrary("maps");
                    const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");
                    
                    // Create the map
                    const map = new Map(document.getElementById("multimap"), {
                        center: { lat: ' . $centerLat . ', lng: ' . $centerLng . ' },
                        zoom: 10,
                        mapId: "f4a15cb6cd4f8d61", // You should replace this with your actual Map ID
                    });

                    // Define bounds for the map
                    const bounds = new google.maps.LatLngBounds(
                        new google.maps.LatLng(' . $minLat . ', ' . $minLng . '),
                        new google.maps.LatLng(' . $maxLat . ', ' . $maxLng . ')
                    );
                    
                    // Fit the map to these bounds
                    map.fitBounds(bounds);

                    // Markers data
                    const markersData = [' . implode(',', $markersData) . '];

                    // Create markers array for clustering
                    const markers = [];

                    // Create markers
                    markersData.forEach((markerData, index) => {
                        const marker = new AdvancedMarkerElement({
                            map: map,
                            position: markerData.position,
                            title: markerData.title
                        });

                        // Add marker to array for clustering
                        markers.push(marker);

                        // Add click listener to show check-in overlay
                        marker.addListener("click", () => {
                            showCheckinOverlay(markerData);
                        });
                    });
                    
                    // After creating all markers, add clustering (only if there are multiple markers)
                    if (markers.length > 1) {
                        const markerCluster = new markerClusterer.MarkerClusterer({ 
                            map: map, 
                            markers: markers 
                        });
                        
                        // Add click listener to cluster to handle individual marker clicks
                        markerCluster.addListener("click", (event) => {
                            // Find the marker that was clicked
                            const clickedMarker = event.marker;
                            const markerIndex = markers.indexOf(clickedMarker);
                            if (markerIndex !== -1) {
                                const markerData = markersData[markerIndex];
                                showCheckinOverlay(markerData);
                            }
                        });
                    }

                } catch (error) {
                    console.error("Error initializing map:", error);
                }
            }

            // Initialize when page loads
            if (typeof google !== "undefined" && google.maps) {
                initMultiMap();
            } else {
                window.addEventListener("load", initMultiMap);
            }

            // Add click-outside-to-close functionality for overlay
            let overlayJustOpened = false;
            
            document.addEventListener("click", function(e) {
                const overlay = document.getElementById("jcp-checkin-overlay");
                const content = document.getElementById("jcp-checkin-card-content");
                
                if (overlay && overlay.style.display === "block") {
                    // Skip if overlay just opened (prevent immediate closing)
                    if (overlayJustOpened) {
                        overlayJustOpened = false;
                        return;
                    }
                    
                    // Check if click is outside the popup content
                    if (content && !content.contains(e.target)) {
                        closeCheckinOverlay();
                    }
                }
            });

            // Function to show check-in overlay
            function showCheckinOverlay(markerData) {
                const overlay = document.getElementById("jcp-checkin-overlay");
                const content = document.getElementById("jcp-checkin-card-content");
                
                if (!overlay || !content) {
                    console.error("Overlay elements not found!");
                    return;
                }
                
                // Generate check-in card HTML
                const checkinCard = generateCheckinCard(markerData);
                content.innerHTML = checkinCard;
                
                // Show overlay
                overlay.style.display = "block";
                overlayJustOpened = true;
            }

            // Function to close check-in overlay
            function closeCheckinOverlay() {
                const overlay = document.getElementById("jcp-checkin-overlay");
                overlay.style.display = "none";
            }


            // Function to generate check-in card HTML
            function generateCheckinCard(markerData) {
                const images = markerData.image_urls || [];
                const user = markerData.assigned_user || {};
                const date = new Date(markerData.created_at * 1000).toLocaleDateString("en-US", { 
                    year: "numeric", 
                    month: "long", 
                    day: "numeric" 
                });
                
                // Parse address to get city and state
                const addressParts = markerData.address ? markerData.address.split(",") : [];
                const city = addressParts[1] ? addressParts[1].trim() : "";
                const state = addressParts[2] ? addressParts[2].trim() : "";
                const stateAbbr = state.length > 2 ? state.substring(0, 2) : state;
                const location = city && stateAbbr ? city + ", " + stateAbbr : markerData.address || "";

                let cardHtml = "<div class=\\"jcp-checkin-card\\" style=\\"text-decoration: none; color: inherit; margin: 0;\\">";
                
                // Images gallery with navigation arrows (matching render_images_gallery)
                if (images.length > 0) {
                    const imageCount = images.length;
                    const showArrows = imageCount > 1;
                    const galleryId = "gallery-" + Math.random().toString(36).substr(2, 9);
                    
                    cardHtml += "<div class=\\"jcp-checkin-image\\" id=\\"" + galleryId + "\\">";
                    
                    // Add all images but only first is visible initially
                    images.forEach((imageUrl, index) => {
                        const activeClass = index === 0 ? " active" : "";
                        cardHtml += "<div class=\\"gallery-image" + activeClass + "\\" data-index=\\"" + index + "\\">";
                        cardHtml += "<img src=\\"" + imageUrl + "\\" alt=\\"Checkin image " + (index + 1) + "\\">";
                        cardHtml += "</div>";
                    });
                    
                    // Add navigation arrows if there are multiple images
                    if (showArrows) {
                        cardHtml += "<div class=\\"gallery-nav gallery-prev\\" onclick=\\"changeImage(&quot;" + galleryId + "&quot;, &quot;prev&quot;)\\">&#10094;</div>";
                        cardHtml += "<div class=\\"gallery-nav gallery-next\\" onclick=\\"changeImage(&quot;" + galleryId + "&quot;, &quot;next&quot;)\\">&#10095;</div>";
                        cardHtml += "<div class=\\"gallery-dots\\">";
                        
                        // Add indicator dots
                        for (let i = 0; i < imageCount; i++) {
                            const activeClass = i === 0 ? " active" : "";
                            cardHtml += "<span class=\\"gallery-dot" + activeClass + "\\" onclick=\\"showImage(&quot;" + galleryId + "&quot;, " + i + ")\\"></span>";
                        }
                        
                        cardHtml += "</div>";
                    }
                    
                    cardHtml += "</div>";
                }

                // User info (matching render_checkin_card structure)
                if (user.name) {
                    cardHtml += "<div class=\\"jcp-checkin-user\\">";
                    
                    // Profile image
                    if (user.profileImageUrl) {
                        cardHtml += "<div class=\\"jcp-user-image\\">";
                        cardHtml += "<img src=\\"" + user.profileImageUrl + "\\" alt=\\"User profile\\">";
                        cardHtml += "</div>";
                    }
                    
                    // User name
                    cardHtml += "<div class=\\"jcp-user-name\\">";
                    cardHtml += "<p>" + user.name + "</p>";
                    cardHtml += "</div>";
                    
                    // Job Reviews (5 stars)
                    cardHtml += "<div class=\\"jcp-job-reviews\\">";
                    for (let i = 0; i < 5; i++) {
                        cardHtml += "<span>";
                        cardHtml += "<svg xmlns=\\"http://www.w3.org/2000/svg\\" viewBox=\\"0 0 640 640\\"><path d=\\"M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C43.3 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z\\"></path></svg>";
                        cardHtml += "</span>";
                    }
                    cardHtml += "</div>";
                    
                    cardHtml += "</div>";
                }

                // Description (matching render_checkin_card structure)
                cardHtml += "<div class=\\"jcp-checkin-description\\">";
                cardHtml += "<p>" + (markerData.description || "").replace(/\\n/g, "<br>") + "</p>";
                cardHtml += "</div>";

                // Date (matching render_checkin_card structure)
                cardHtml += "<div class=\\"jcp-checkin-date\\">";
                cardHtml += "<p class=\\"date-icon\\">";
                cardHtml += "<svg xmlns=\\"http://www.w3.org/2000/svg\\" viewBox=\\"0 0 640 640\\"><path d=\\"M224 64C241.7 64 256 78.3 256 96L256 128L384 128L384 96C384 78.3 398.3 64 416 64C433.7 64 448 78.3 448 96L448 128L480 128C515.3 128 544 156.7 544 192L544 480C544 515.3 515.3 544 480 544L160 544C124.7 544 96 515.3 96 480L96 192C96 156.7 124.7 128 160 128L192 128L192 96C192 78.3 206.3 64 224 64zM160 304L160 336C160 344.8 167.2 352 176 352L208 352C216.8 352 224 344.8 224 336L224 304C224 295.2 216.8 288 208 288L176 288C167.2 288 160 295.2 160 304zM288 304L288 336C288 344.8 295.2 352 304 352L336 352C344.8 352 352 344.8 352 336L352 304C352 295.2 344.8 288 336 288L304 288C295.2 288 288 295.2 288 304zM432 288C423.2 288 416 295.2 416 304L416 336C416 344.8 423.2 352 432 352L464 352C472.8 352 480 344.8 480 336L480 304C480 295.2 472.8 288 464 288L432 288zM160 432L160 464C160 472.8 167.2 480 176 480L208 480C216.8 480 224 472.8 224 464L224 432C224 423.2 216.8 416 208 416L176 416C167.2 416 160 423.2 160 432zM304 416C295.2 416 288 423.2 288 432L288 464C288 472.8 295.2 480 304 480L336 480C344.8 480 352 472.8 352 464L352 432C352 423.2 344.8 416 336 416L304 416zM416 432L416 464C416 472.8 423.2 480 432 480L464 480C472.8 480 480 472.8 480 464L480 432C480 423.2 472.8 416 464 416L432 416C423.2 416 416 423.2 416 432z\\"></path></svg>";
                cardHtml += date;
                cardHtml += "</p>";
                cardHtml += "</div>";

                // Address (matching render_checkin_card structure)
                cardHtml += "<div class=\\"jcp-checkin-address\\">";
                cardHtml += "<p><strong>Near</strong> " + location + "</p>";
                cardHtml += "</div>";

                cardHtml += "</div>";
                return cardHtml;
            }

            // Gallery functionality functions (matching get_gallery_script)
            function changeImage(galleryId, direction) {
                const gallery = document.getElementById(galleryId);
                if (!gallery) return;
                
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
            
            function showImage(galleryId, index) {
                const gallery = document.getElementById(galleryId);
                if (!gallery) return;
                
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


        return $output;
    }

    /**
     * Generate CSS styles for the multi markers map
     * 
     * @return string CSS styles for the multi markers map
     */
    private static function get_multimap_styles()
    {
        return '<style>
            .jcp-multimap {
                height: 500px;
                width: 100%;
                border-radius: 12px;
                overflow: hidden;
                margin-left: auto;
                margin-right: auto;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
            
            /* Check-in overlay styles - Google Maps popup style */
            .jcp-checkin-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: transparent;
                display: none;
                z-index: 10000;
                pointer-events: none;
            }
            
            .jcp-checkin-overlay-content {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: white;
                border-radius: 8px;
                max-width: 400px;
                width: 90%;
                max-height: 80vh;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
                border: 1px solid #e0e0e0;
                pointer-events: auto;
            }
            
            /* Google Maps popup arrow */
            .jcp-checkin-overlay-content::before {
                content: "";
                position: absolute;
                bottom: -10px;
                left: 50%;
                transform: translateX(-50%);
                width: 0;
                height: 0;
                border-left: 10px solid transparent;
                border-right: 10px solid transparent;
                border-top: 10px solid white;
            }
            
            .jcp-checkin-overlay-content::after {
                content: "";
                position: absolute;
                bottom: -11px;
                left: 50%;
                transform: translateX(-50%);
                width: 0;
                height: 0;
                border-left: 11px solid transparent;
                border-right: 11px solid transparent;
                border-top: 11px solid #e0e0e0;
            }
            
            .jcp-close-overlay {
                position: absolute;
                top: 10px;
                right: 15px;
                background: none;
                border: none;
                font-size: 24px;
                cursor: pointer;
                color: #666;
                z-index: 10001;
                width: 30px;
                height: 30px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                transition: background-color 0.2s ease;
            }
            
            .jcp-close-overlay:hover {
                background-color: #f0f0f0;
                color: #333;
            }
            
            /* Overlay check-in card styles */
            .jcp-checkin-overlay .jcp-checkin-card {
                margin: 0;
                box-shadow: none;
                border-radius: 12px;
                overflow: visible;
            }
            
            .jcp-checkin-overlay .jcp-checkin-image {
                border-radius: 12px 12px 0 0;
                margin-bottom: 0;
            }
            
            .jcp-checkin-overlay .jcp-checkin-user {
                padding: 15px;
                border-bottom: 1px solid #f0f0f0;
            }
            
            .jcp-checkin-overlay .jcp-checkin-description {
                padding: 15px;
                border-bottom: 1px solid #f0f0f0;
            }
            
            .jcp-checkin-overlay .jcp-checkin-date,
            .jcp-checkin-overlay .jcp-checkin-address {
                padding: 10px 15px;
            }
            
            .jcp-checkin-overlay .jcp-checkin-address {
                border-bottom: none;
            }

            /* Responsive design */
            @media (max-width: 1024px) {
                .jcp-multimap {
                    width: 90%;
                }
            }
            
            @media (max-width: 768px) {
                .jcp-multimap {
                    width: 95%;
                }
                
                .jcp-checkin-overlay-content {
                    max-width: 350px;
                    margin: 10px;
                }
            }
            
            @media (max-width: 480px) {
                .jcp-multimap {
                    width: 100%;
                    height: 400px;
                }
                
                .jcp-checkin-overlay {
                    padding: 10px;
                }
                
                .jcp-checkin-overlay-content {
                    max-width: 100%;
                    max-height: 90vh;
                }
            }
        </style>';
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

        $output = '<div class="jcp-company-info">';

        // Company details (now comes first)
        $output .= '<div class="jcp-company-details">
            <h2 class="jcp-company-name">' . esc_html($company_info['name']) . '</h2>';

        // Intro text
        $output .= '<div class="jcp-company-into-text">
            <p>Sarasota’s #1 for exterior cleaning. Trusted by 200+ homeowners.</p>
        </div>';

        $output .= '<div class="jcp-company-div-2">';

        // Company Reviews text
        $output .= '<div class="jcp-company-reviews-text">
            <p><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C43.3 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z"/></svg> 4.9 (212 reviews)</p>
        </div>';

        // Address
        // $output .= '<div class="jcp-company-address">
        //   <p>' . nl2br(esc_html($company_info['address'])) . '</p>
        //</div>';


        // Check if we have either phone or URL
        $has_phone = !empty($company_info['tn']);
        $has_url = !empty($company_info['url']);

        if ($has_phone) {
            $output .= '<p> <strong> &nbsp;.&nbsp; </strong><a href="tel:' . esc_attr(preg_replace('/[^0-9]/', '', $company_info['tn'])) . '">' . esc_html($company_info['tn']) . '</a></p>';
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
            $output .= '<p class="jcp-no-contact-info">Contact No. and Website information not available</p>';
        }

        $output .= '</div></div>'; // Close jcp-company-contact and jcp-company-details

        // Quote btn and text

        $output .= '<div class="jcp-company-logo">
                <a href="#" class="quote-btn">Get a Quote</a>
                <p class="powered-by">Powered by <b>JobCapturePro</b></p>
            </div>';


        // Logo (now comes after details)
        //if (!empty($company_info['logoUrl'])) {
        //    $output .= '<div class="jcp-company-logo">
        //        <img src="' . esc_url($company_info['logoUrl']) . '" alt="' . esc_attr($company_info['name']) . ' Logo">
        //    </div>';
        //}

        $output .= '</div>'; // Close jcp-company-info

        // Add CSS
        $output .= self::get_company_info_styles();

        return $output;
    }

    /**
     * Generate CSS styles for the company info section
     * 
     * @return string CSS styles for the company info section
     */
    private static function get_company_info_styles()
    {
        return '<style>
            .jcp-company-info {
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                padding: 2.5rem 2rem;
                background-color: #fff;
                border-bottom: 1px solid #eee;
                gap: 1.5rem;
            }

            .jcp-company-into-text p {
                margin: 0;
                font-size: 1rem;
                color: #444;
            }

            .jcp-company-div-2 p {
                margin: 0;
                font-size: 1rem;
                color: #444;
                display: flex;
                align-items: center;
            }

            .jcp-company-div-2 a {
                color: #111;
            }

            .jcp-company-div-2 a:hover {
                color: #e2353c;
            }

            .jcp-company-logo img {
                max-width: 200px;
                height: auto;
                object-fit: contain;
            }

            .jcp-company-details {
                flex: 1;
                min-width: 250px;
            }

            .jcp-company-name {
                color: #333;
                margin: 0;
                font-size: 1.8rem;
                font-weight: 700;
                margin-bottom: 0.6rem;
            }

            .jcp-company-address p,
            .jcp-company-contact p {
                margin: 8px 0;
                font-size: 16px;
                color: #555;
            }

            .jcp-company-contact a {
                color: #0066cc;
                text-decoration: none;
            }

            .jcp-company-contact a:hover {
                text-decoration: underline;
            }

            .jcp-no-contact-info {
                color: #999;
                font-style: italic;
            }

            .jcp-company-reviews-text p svg{
                width : 18px;
            }

            .jcp-company-div-2{
                display: flex;
            }

            .jcp-company-logo{
                text-align: end;
            }

            .quote-btn {
                background-color: #ff503e;
                color: #fff;
                font-weight: bold;
                padding: 0.7rem 1.5rem;
                font-size: 1rem;
                border-radius: 999px;
                transition: background-color 0.2s ease;
            }

            .powered-by {
                font-size: 0.75rem;
                color: #888;
            }

            .powered-by b {
               color: #000;
            }

            @media (max-width: 600px) {
                .jcp-company-info {
                    flex-direction: column;
                    text-align: center;
                    gap: 20px;
                }
                
                .jcp-company-logo img {
                    max-width: 150px;
                }

                .jcp-company-div-2{
                    display: block;
                }
            }
                
        </style>';
    }
}
