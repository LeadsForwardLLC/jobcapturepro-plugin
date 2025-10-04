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
        // Enqueue styles
        self::enqueue_single_checkin_styles();
        self::enqueue_checkins_grid_styles();

        // 
        return Template::render_template('single-checkin', [
            'checkin' => $checkin,
            'company_info' => $company_info,
            'show_reviews' => self::should_show_feature('show_customer_reviews', !empty($checkin['customer_review'])),
            'show_fallback_review' => self::should_show_feature('show_customer_reviews', true),
            'show_ratings' => self::should_show_feature('show_star_ratings', !empty($checkin['rating'])),
            'show_fallback_rating' => self::should_show_feature('show_star_ratings', true),
            'show_verified' => self::should_show_feature('show_verified_badges', !empty($checkin['is_verified']) && $checkin['is_verified']),
            'show_verified_fallback' => self::should_show_feature('show_verified_badges', true)
        ]);
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
            self::enqueue_gallery_script();
        }

        return $output;
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

        // Enqueue styles 
        self::enqueue_company_info_styles();

        // 
        return Template::render_template('company-info', ["company_info" => $company_info]);
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
    private static function enqueue_checkins_grid_styles()
    {
        wp_enqueue_style(
            'jcp-checkins-grid',
            plugin_dir_url(dirname(__FILE__)) . '/assets/css/checkins-grid.css',
            array(),
            '1.0.0',
            'all'
        );
    }


    /**
     * Enqueue JavaScript for image gallery functionality
     * 
     */
    private static function enqueue_gallery_script()
    {
        // Check if script has already been added to avoid duplication
        static $scriptAdded = false;

        if ($scriptAdded) {
            return;
        }

        $scriptAdded = true;

        wp_enqueue_script(
            'jobcapturepro-gallery',
            plugin_dir_url(dirname(__FILE__)) . 'assets/js/gallery.js',
            array(),
            '1.0.0',
            true
        );
    }
}
