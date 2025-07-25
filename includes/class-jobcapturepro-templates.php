<?php

/**
 * This file defines the template generation functionality for the plugin.
 */
class JobCaptureProTemplates
{

    /**
     * Helper method to render checkins with conditional logic
     * 
     * @param string|null $checkin_id The checkin ID if filtering for a specific checkin
     * @param array $checkins Array of checkin data
     * @return string HTML output for the checkins
     */
    public static function render_checkins_conditionally($checkin_id, $checkins)
    {
        // If a specific checkin_id was provided, render as a single checkin
        if ($checkin_id && count($checkins) === 1) {
            return JobCaptureProTemplates::render_single_checkin($checkins[0]);
        } else {
            // Otherwise render as a grid of multiple checkins
            return JobCaptureProTemplates::render_checkins_grid($checkins);
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
        $output .= JobCaptureProTemplates::render_checkins_conditionally($checkin_id, $checkins);

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
     * Generate HTML for a single checkin page
     * 
     * @param array $checkin The checkin data
     * @return string HTML for a single checkin page
     */
    public static function render_single_checkin($checkin)
    {
        $output = '<div class="jcp-checkin-page">';

        $output .= self::get_single_checkin_styles();
        
        // Description
        $output .= '<div class="jcp-checkin-description">
            <p>' . esc_html($checkin['description']) . '</p>
        </div>';

        $output .= '</div>';

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
            
            $output .= '</div>';
        }

        // Description
        $output .= '<div class="jcp-checkin-description">
            <p>' . nl2br(esc_html($checkin['description'])) . '</p>
        </div>';

        // Date
        $output .= '<div class="jcp-checkin-date">';
        $timestamp = $checkin['createdAt'];
        $current_time = time();
        $time_diff = $current_time - $timestamp;
        $three_months = 3 * 30 * 24 * 60 * 60; // Approximate 3 months in seconds

        if ($time_diff < $three_months) {
            // Relative time for dates within 3 months
            if ($time_diff < 60 * 60) {
                // Less than an hour ago
                $relative_time = '1 hour ago';
            } elseif ($time_diff < 24 * 60 * 60) {
                // Hours ago
                $hours = floor($time_diff / (60 * 60));
                $relative_time = $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
            } elseif ($time_diff < 30 * 24 * 60 * 60) {
                // Days ago
                $days = floor($time_diff / (24 * 60 * 60));
                $relative_time = $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
            } else {
                // Months ago
                $months = floor($time_diff / (30 * 24 * 60 * 60));
                $relative_time = $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
            }
            $output .= '<p>' . esc_html($relative_time) . '</p>';
        } else {
            // Standard date format for older dates
            $output .= '<p>' . esc_html(date('F j, Y', $timestamp)) . '</p>';
        }
        $output .= '</div>';

        // Address
        $output .= '<div class="jcp-checkin-address">';
        $output .= '<p><strong>Near</strong> ' . esc_html($checkin['address']);
        $output .= '</p></div>';

        $output .= '</a>'; // Close clickable card
        return $output;
    }

    /**
     * Generate HTML for the checkins grid layout with items sorted by date (newest first)
     * 
     * @param array $checkins Array of checkin data
     * @return string HTML for all checkins in a responsive grid
     */
    public static function render_checkins_grid($checkins)
    {
        // Sort checkins by date (newest first)
        usort($checkins, function($a, $b) {
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
        $output .= '<div class="jcp-checkins-grid ' . $gridId . '" data-column-count="4">';
        
        // Add each checkin to the grid in date-sorted order
        foreach ($checkins as $checkin) {
            $output .= self::render_checkin_card($checkin);
        }

        $output .= '</div>'; // Close grid
        $output .= '</div>'; // Close container

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
                padding: 20px;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            }
            
            ' . $gridSelector . ' {
                /* Keep masonry-style layout with CSS columns */
                column-count: 4;
                column-gap: 20px;
                width: 100%;
            }
                        
            .jcp-checkin-card {
                break-inside: avoid;
                margin-bottom: 20px;
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
                text-align: right;
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
                height: 200px;
                overflow: hidden;
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
            }
                        
            .jcp-checkin-address {
                font-size: 0.85em;
                /* background-color: #f8f8f8; */
                /* border-top: 1px solid #eee; */
            }
            
            .jcp-checkin-description {
                border-bottom: 1px solid #f0f0f0;
            }

            .jcp-checkin-date, .jcp-checkin-address {
                padding: 0 15px;
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

    private static function determine_bounds($features) {
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
            }
        </style>';
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

        // Add CSS for modern responsive map
        $output .= self::get_multimap_styles();

        // Generate unique markers data with properties
        $markersData = array();
        foreach ($features as $index => $feature) {
            // Extract relevant data for the marker
            $lat = $feature['geometry']['coordinates'][1];
            $lng = $feature['geometry']['coordinates'][0];
            
            // Get properties from feature if available
            $title = !empty($feature['properties']['title']) ? 
                esc_js($feature['properties']['title']) : 'Location ' . ($index + 1);
            
            $description = !empty($feature['properties']['description']) ? 
                esc_js($feature['properties']['description']) : '';
                
            $address = !empty($feature['properties']['address']) ? 
                esc_js($feature['properties']['address']) : '';
                
            $date = !empty($feature['properties']['createdAt']) ? 
                date('F j, Y', $feature['properties']['createdAt']) : '';
            
            // Build the marker data
            $markersData[] = "{
                position: { lat: {$lat}, lng: {$lng} },
                title: '{$title}',
                description: '{$description}',
                address: '{$address}',
                date: '{$date}'
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

                        // Add info window if there\'s additional content
                        if (markerData.description || markerData.address || markerData.date) {
                            const infoWindow = new google.maps.InfoWindow({
                                content: `
                                    <div style="max-width: 200px;">
                                        <h4>${markerData.title}</h4>
                                        ${markerData.description ? `<p>${markerData.description}</p>` : ""}
                                        ${markerData.address ? `<p><strong>Address:</strong> ${markerData.address}</p>` : ""}
                                        ${markerData.date ? `<p><strong>Date:</strong> ${markerData.date}</p>` : ""}
                                    </div>
                                `
                            });

                            marker.addListener("click", () => {
                                infoWindow.open(map, marker);
                            });
                        }
                    });
                    
                    // After creating all markers, add clustering (only if there are multiple markers)
                    if (markers.length > 1) {
                        const markerCluster = new markerClusterer.MarkerClusterer({ 
                            map: map, 
                            markers: markers 
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
            
            .jcp-info-window {
                padding: 5px;
                max-width: 250px;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            }
            
            .jcp-info-window h3 {
                margin-top: 0;
                margin-bottom: 8px;
                font-size: 16px;
                font-weight: 600;
            }
            
            .jcp-info-window p {
                margin: 5px 0;
                font-size: 14px;
            }
            
            .jcp-info-window .jcp-address,
            .jcp-info-window .jcp-date {
                font-size: 12px;
                color: #666;
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
            }
            
            @media (max-width: 480px) {
                .jcp-multimap {
                    width: 100%;
                    height: 400px;
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

        $output = '<div class="jcp-company-info jcp-container">';
        
        // Company details (now comes first)
        $output .= '<div class="jcp-company-details">
            <h2 class="jcp-company-name">' . esc_html($company_info['name']) . '</h2>';
            
        // Address
        $output .= '<div class="jcp-company-address">
            <p>' . nl2br(esc_html($company_info['address'])) . '</p>
        </div>';
        
        // Contact info section
        $output .= '<div class="jcp-company-contact">';
        
        // Check if we have either phone or URL
        $has_phone = !empty($company_info['tn']);
        $has_url = !empty($company_info['url']);
        
        if ($has_phone) {
            $output .= '<p><strong>Phone:</strong> <a href="tel:' . esc_attr(preg_replace('/[^0-9]/', '', $company_info['tn'])) . '">' . esc_html($company_info['tn']) . '</a></p>';
        }
        
        if ($has_url) {
            $parsed_url = parse_url($company_info['url']);
            $display_url = $parsed_url['host'] ?? $company_info['url'];
            $output .= '<p><strong>Website:</strong> <a href="' . esc_url($company_info['url']) . '" target="_blank" rel="noopener noreferrer">' . esc_html($display_url) . '</a></p>';
        }
        
        // Show message if no contact info
        if (!$has_phone && !$has_url) {
            $output .= '<p class="jcp-no-contact-info">Contact No. and Website information not available</p>';
        }
        
        $output .= '</div></div>'; // Close jcp-company-contact and jcp-company-details
        
        // Logo (now comes after details)
        if (!empty($company_info['logoUrl'])) {
            $output .= '<div class="jcp-company-logo">
                <img src="' . esc_url($company_info['logoUrl']) . '" alt="' . esc_attr($company_info['name']) . ' Logo">
            </div>';
        }
        
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
                flex-wrap: wrap;
                align-items: center;
                gap: 30px;
                padding: 25px !important;
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
                margin-top: 0;
                margin-bottom: 15px;
                color: #333;
                font-size: 28px;
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

            @media (max-width: 600px) {
                .jcp-company-info {
                    flex-direction: column;
                    text-align: center;
                    gap: 20px;
                }
                
                .jcp-company-logo img {
                    max-width: 150px;
                }
            }
        </style>';
    }   

    /**
     * Render reviews HTML
     */
    public static function render_reviews($reviews)
    {
        $output = '<div class="jcp-reviews-container">';
        $output .= JobCaptureProTemplates::get_reviews_styles();
        
        if (is_array($reviews) && !empty($reviews)) {
            $output .= '<div class="jcp-reviews-list">';
            
            foreach ($reviews as $review) {
                $output .= JobCaptureProTemplates::render_single_review($review);
            }
            
            $output .= '</div>';
        }
        
        $output .= '</div>';
        
        return $output;
    }

    /**
     * Render a single review
     */
    public static function render_single_review($review)
    {
        $output = '<div class="jcp-review-item">';
        
        // Review header with rating and author
        $output .= '<div class="jcp-review-header">';
        
        // Rating stars
        if (isset($review['rating']) && is_numeric($review['rating'])) {
            $rating = (float)$review['rating'];
            $output .= '<div class="jcp-review-rating">';
            $output .= JobCaptureProTemplates::render_star_rating($rating);
            $output .= '</div>';
        }
        
        // Author name
        if (isset($review['author_name'])) {
            $output .= '<div class="jcp-review-author">';
            $output .= '<strong>' . esc_html($review['author_name']) . '</strong>';
            $output .= '</div>';
        }
        
        $output .= '</div>';
        
        // Review text
        if (isset($review['text']) && !empty($review['text'])) {
            $output .= '<div class="jcp-review-text">';
            $output .= '<p>' . esc_html($review['text']) . '</p>';
            $output .= '</div>';
        }
        
        // Review date
        if (isset($review['time'])) {
            $output .= '<div class="jcp-review-date">';
            $date = is_numeric($review['time']) ? date('M j, Y', $review['time']) : esc_html($review['time']);
            $output .= '<small>' . $date . '</small>';
            $output .= '</div>';
        }
        
        $output .= '</div>';
        
        return $output;
    }

    /**
     * Render star rating
     */
    public static function render_star_rating($rating)
    {
        $output = '<div class="jcp-stars">';
        
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $rating) {
                $output .= '<span class="jcp-star jcp-star-filled">★</span>';
            } elseif ($i - 0.5 <= $rating) {
                $output .= '<span class="jcp-star jcp-star-half">☆</span>';
            } else {
                $output .= '<span class="jcp-star jcp-star-empty">☆</span>';
            }
        }
        
        $output .= '</div>';
        
        return $output;
    }

    /**
     * Get CSS styles for reviews
     */
    public static function get_reviews_styles()
    {
        return '<style>
            .jcp-reviews-container {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
                margin: 20px 0;
            }
            
            .jcp-reviews-list {
                display: flex;
                flex-direction: column;
                gap: 15px;
            }
            
            .jcp-review-item {
                background: #f9f9f9;
                padding: 15px;
                border-radius: 8px;
                border-left: 4px solid #0073aa;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            
            .jcp-review-header {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 10px;
            }
            
            .jcp-review-rating {
                flex-shrink: 0;
            }
            
            .jcp-review-author {
                flex-grow: 1;
            }
            
            .jcp-stars {
                display: flex;
                gap: 2px;
            }
            
            .jcp-star {
                font-size: 16px;
                line-height: 1;
            }
            
            .jcp-star-filled {
                color: #ffa500;
            }
            
            .jcp-star-half {
                color: #ffa500;
            }
            
            .jcp-star-empty {
                color: #ddd;
            }
            
            .jcp-review-text {
                margin-bottom: 10px;
            }
            
            .jcp-review-text p {
                margin: 0;
                color: #333;
                line-height: 1.5;
            }
            
            .jcp-review-date {
                color: #666;
                font-size: 12px;
            }
            
            .jcp-no-reviews {
                text-align: center;
                padding: 20px;
                color: #666;
                background: #f9f9f9;
                border-radius: 8px;
            }
            
            .jcp-no-reviews p {
                margin: 0;
                font-style: italic;
            }
            
            .jcp-reviews-shortcode {
                margin: 20px 0;
            }
            
            .jcp-reviews-shortcode h3 {
                margin-top: 0;
                margin-bottom: 15px;
                color: #333;
            }
            
            @media (max-width: 768px) {
                .jcp-review-header {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 5px;
                }
                
                .jcp-review-item {
                    padding: 12px;
                }
            }
        </style>';
    }

    /**
     * Render nearby checkins HTML
     */
    public static function render_nearby_checkins($checkins, $city)
    {
        $output = '<div class="jcp-nearby-checkins-container">';
        $output .= JobCaptureProTemplates::get_nearby_checkins_styles();
        
        if (is_array($checkins) && !empty($checkins)) {
            $output .= '<div class="jcp-nearby-checkins-list">';
            
            foreach ($checkins as $checkin) {
                $output .= JobCaptureProTemplates::render_nearby_checkin_item($checkin);
            }
            
            $output .= '</div>';
        }
        
        $output .= '</div>';
        
        return $output;
    }

    /**
     * Render a single nearby checkin item
     */
    public static function render_nearby_checkin_item($checkin)
    {
        // Create clickable link with checkinId parameter
        $current_url = $_SERVER['REQUEST_URI'];
        $checkin_url = add_query_arg('checkinId', $checkin['id'], $current_url);
        
        $output = '<a href="' . esc_url($checkin_url) . '" class="jcp-nearby-checkin-item" style="text-decoration: none; color: inherit;">';
        
        // Checkin image (if available)
        if (!empty($checkin['imageUrls']) && is_array($checkin['imageUrls'])) {
            $output .= '<div class="jcp-nearby-checkin-image">';
            $output .= '<img src="' . esc_url($checkin['imageUrls'][0]) . '" alt="Checkin image">';
            $output .= '</div>';
        }
        
        // Checkin content
        $output .= '<div class="jcp-nearby-checkin-content">';
        
        // Description
        if (!empty($checkin['description'])) {
            $output .= '<div class="jcp-nearby-checkin-description">';
            $output .= '<p>' . esc_html(wp_trim_words($checkin['description'], 20)) . '</p>';
            $output .= '</div>';
        }
        
        // Address
        if (!empty($checkin['address'])) {
            $output .= '<div class="jcp-nearby-checkin-address">';
            $output .= '<p><small>' . esc_html($checkin['address']) . '</small></p>';
            $output .= '</div>';
        }
        
        // Date
        if (!empty($checkin['createdAt'])) {
            $output .= '<div class="jcp-nearby-checkin-date">';
            $timestamp = $checkin['createdAt'];
            $current_time = time();
            $time_diff = $current_time - $timestamp;
            
            if ($time_diff < 60 * 60 * 24 * 7) { // Within a week
                $days = floor($time_diff / (60 * 60 * 24));
                if ($days == 0) {
                    $relative_time = 'Today';
                } elseif ($days == 1) {
                    $relative_time = 'Yesterday';
                } else {
                    $relative_time = $days . ' days ago';
                }
                $output .= '<p><small>' . esc_html($relative_time) . '</small></p>';
            } else {
                $output .= '<p><small>' . esc_html(date('M j, Y', $timestamp)) . '</small></p>';
            }
            $output .= '</div>';
        }
        
        $output .= '</div>'; // Close content
        $output .= '</a>';
        
        return $output;
    }

    /**
     * Get CSS styles for nearby checkins
     */
    public static function get_nearby_checkins_styles()
    {
        return '<style>
            .jcp-nearby-checkins-container {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
                margin: 20px 0;
            }
            
            .jcp-nearby-checkins-list {
                display: flex;
                flex-direction: column;
                gap: 12px;
            }
            
            .jcp-nearby-checkin-item {
                display: flex;
                background: #fff;
                border: 1px solid #e0e0e0;
                border-radius: 8px;
                overflow: hidden;
                transition: transform 0.2s ease, box-shadow 0.2s ease;
                cursor: pointer;
            }
            
            .jcp-nearby-checkin-item:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                border-color: #0073aa;
            }
            
            .jcp-nearby-checkin-image {
                flex-shrink: 0;
                width: 80px;
                height: 80px;
                overflow: hidden;
            }
            
            .jcp-nearby-checkin-image img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
            }
            
            .jcp-nearby-checkin-content {
                flex: 1;
                padding: 12px;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            }
            
            .jcp-nearby-checkin-description {
                margin-bottom: 8px;
            }
            
            .jcp-nearby-checkin-description p {
                margin: 0;
                font-size: 14px;
                color: #333;
                line-height: 1.4;
            }
            
            .jcp-nearby-checkin-address {
                margin-bottom: 4px;
            }
            
            .jcp-nearby-checkin-address p {
                margin: 0;
                color: #666;
                font-size: 12px;
            }
            
            .jcp-nearby-checkin-date p {
                margin: 0;
                color: #999;
                font-size: 11px;
            }
            
            .jcp-no-nearby-checkins {
                text-align: center;
                padding: 20px;
                color: #666;
                background: #f9f9f9;
                border-radius: 8px;
            }
            
            .jcp-no-nearby-checkins p {
                margin: 0;
                font-style: italic;
            }
            
            @media (max-width: 480px) {
                .jcp-nearby-checkin-item {
                    flex-direction: column;
                }
                
                .jcp-nearby-checkin-image {
                    width: 100%;
                    height: 120px;
                }
            }
        </style>';
    }
}
