<?php

/**
 * Template functionality for JobCapturePro plugin.
 *
 * @package JobCapturePro
 * @since   1.0.0
 */

// Prevent direct access.
if (! defined('ABSPATH')) {
    exit;
}

/**
 * This file defines the template generation functionality for the plugin.
 *
 * @since 1.0.0
 */
class JobCaptureProTemplates
{
    /**
     * Helper function to safely sanitize template variables
     * 
     * @param mixed $data The data to sanitize
     * @param string $context The context for sanitization (html, attr, url, js, etc.)
     * @return mixed Sanitized data
     */
    public static function sanitize_template_data($data, $context = 'html')
    {
        if (is_null($data)) {
            return '';
        }

        if (is_array($data)) {
            return array_map(function ($item) use ($context) {
                return self::sanitize_template_data($item, $context);
            }, $data);
        }

        switch ($context) {
            case 'attr':
                return esc_attr($data);
            case 'url':
                return esc_url($data);
            case 'js':
                return esc_js($data);
            case 'textarea':
                return esc_textarea($data);
            case 'html':
            default:
                return esc_html($data);
        }
    }

    /**
     * Validate template data structure
     * 
     * @param mixed $data The data to validate
     * @param array $required_fields Required fields for the data
     * @param string $context Context for error logging
     * @return bool True if valid, false otherwise
     */
    public static function validate_template_data($data, $required_fields = array(), $context = 'template')
    {
        if (!is_array($data)) {
            // error_log("JobCapturePro Template Error: Invalid data type for {$context}, expected array, got " . gettype($data));
            return false;
        }

        foreach ($required_fields as $field) {
            if (!isset($data[$field])) {
                // error_log("JobCapturePro Template Error: Missing required field '{$field}' in {$context}");
                return false;
            }
        }

        return true;
    }

    /**
     * Render fallback content when template rendering fails
     * 
     * @param string $error_message The error message
     * @param string $context The context where the error occurred
     * @return string HTML fallback content
     */
    public static function render_template_fallback($error_message, $context = '')
    {
        if (defined('WP_DEBUG') && WP_DEBUG && current_user_can('manage_options')) {
            return sprintf(
                '<div class="jobcapturepro-template-error" style="border: 1px solid #ccc; padding: 10px; margin: 10px 0; background: #f9f9f9;">
                    <strong>Template Error:</strong> %s
                    %s
                </div>',
                esc_html($error_message),
                !empty($context) ? '<br><small>Context: ' . esc_html($context) . '</small>' : ''
            );
        }

        return '<div class="jobcapturepro-unavailable">' .
            esc_html__('Content is temporarily unavailable. Please try again later.', 'jobcapturepro') .
            '</div>';
    }

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
    public static function render_map_conditionally($map_data, $company_info = array())
    {
        // Extract locations and maps API key from response
        $locations = isset($map_data['locations']) ? $map_data['locations'] : [];
        $maps_api_key = isset($map_data['googleMapsApiKey']['value']) ? $map_data['googleMapsApiKey']['value'] : '';

        // Render the map
        return JobCaptureProTemplates::render_map($locations, $maps_api_key, $company_info);
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
        $output .= JobCaptureProTemplates::render_map_conditionally($map_data, $company_info);

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
        return JobCapturePro_Template::render_template('single-checkin', [
            'checkin' => $checkin,
            'company_info' => $company_info,
            'should_show_feature' => function ($feature_name, $data_exists = false) {
                return self::should_show_feature($feature_name, $data_exists);
            }
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
        // Check for required fields - return empty if missing
        if (empty($checkin['description']) || empty($checkin['address']) || empty($checkin['createdAt'])) {
            return;
        }

        //
        return JobCapturePro_Template::render_template('checkin-card', [
            'checkin' => $checkin,
        ]);
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
        // Generate unique ID for this grid instance
        $gridId = 'jobcapturepro-checkins-grid-' . uniqid();

        // Enqueue styles
        self::enqueue_checkins_grid_styles();

        // Add dynamic selectors styles
        self::add_inline_dynamic_styles_to_checkins_grid_stylesheet($gridId);

        // Enqueue scripts
        self::enqueue_checkins_grid_script($gridId, $company_info);
        self::enqueue_gallery_script();

        $checkins_grid_html .= JobCapturePro_Template::render_template('checkins-grid', [
            'checkins' => $checkins,
            'company_info' => $company_info,
            'gridId' => $gridId,
        ]);

        if (self::should_show_feature('show_company_stats', !empty($company_info['stats']))) {
            $checkins_grid_html .= JobCapturePro_Template::render_template('company-stats', [
                'company_info' => $company_info,
            ]);
        }

        //
        return $checkins_grid_html;
    }

    /**
     * Generate CSS styles for the checkins grid
     * 
     * @param string $gridId The unique ID for the grid
     * @return string CSS styles for the checkins grid
     */
    private static function add_inline_dynamic_styles_to_checkins_grid_stylesheet($gridId = null)
    {
        $gridSelector = $gridId ? '.' . sanitize_html_class($gridId) : '.jobcapturepro-checkins-grid';

        $dynamic_css = '
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
        ';

        wp_add_inline_style('jobcapturepro-checkins-grid', $dynamic_css);
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
        if (empty($imageUrls) || !is_array($imageUrls)) {
            return '';
        }

        $imageCount = count($imageUrls);
        $showArrows = $imageCount > 1;
        $galleryId = 'gallery-' . wp_rand();


        if ($showArrows) {
            // Enqueue gallery script only if there are multiple images
            self::enqueue_gallery_script();
        }

        return JobCapturePro_Template::render_template('image-gallery', [
            'imageUrls' => $imageUrls,
            'imageCount' => $imageCount,
            'showArrows' => $showArrows,
            'galleryId' => $galleryId
        ]);
    }

    /**
     * Determine map bounds based on checkin locations
     * 
     * @param array $features Array of GeoJSON features
     * @return array Array with minLat, maxLat, minLng, maxLng
     */
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

    /*e
     * Generate HTML for a Google Maps map with multiple markers
     * 
     * @param array $locations The location data as defined by geopoints in RFC 7946
     * @return string HTML for a Google Maps map with multiple markers
     */
    public static function render_map($locations, $maps_api_key, $company_info = array())
    {
        // Check for required fields
        if (empty($locations)) {
            return '';
        }

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
            JOBCAPTUREPRO_PLUGIN_URL . 'dist/js/map.min.js',
            array(),
            JOBCAPTUREPRO_VERSION,
            true
        );

        wp_localize_script(
            'jobcapturepro-map',
            'jobcaptureproMapData',
            array(
                // 
                'googleMapsApiKey' => esc_js($maps_api_key),

                // 
                'baseApiUrl' => JobCaptureProAPI::get_wp_plugin_api_base_url(),

                // Company information
                'companyInfo' => $company_info,

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

        // Enqueue styles for the map & checkins cards inside the map
        self::enqueue_map_styles();
        self::enqueue_checkins_grid_styles();
        self::add_inline_dynamic_styles_to_checkins_grid_stylesheet();

        // Enqueue the gallery script
        self::enqueue_gallery_script();

        //
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
        return JobCapturePro_Template::render_template('company-info', ["company_info" => $company_info]);
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
            JOBCAPTUREPRO_PLUGIN_URL . 'dist/css/map.min.css',
            array(),
            JOBCAPTUREPRO_VERSION,
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
            'jobcapturepro-company-info-styles',
            JOBCAPTUREPRO_PLUGIN_URL . 'dist/css/company-info.min.css',
            array(),
            JOBCAPTUREPRO_VERSION,
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
            'jobcapturepro-single-checkin',
            JOBCAPTUREPRO_PLUGIN_URL . 'dist/css/single-checkin.min.css',
            array(),
            JOBCAPTUREPRO_VERSION,
            'all'
        );
    }

    /**
     * Enqueue checkins grid styles
     */
    private static function enqueue_checkins_grid_styles()
    {
        wp_enqueue_style(
            'jobcapturepro-checkins-grid',
            JOBCAPTUREPRO_PLUGIN_URL . 'dist/css/checkins-grid.min.css',
            array(),
            JOBCAPTUREPRO_VERSION,
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
            JOBCAPTUREPRO_PLUGIN_URL . 'dist/js/gallery.min.js',
            array(),
            JOBCAPTUREPRO_VERSION,
            true
        );
    }

    /**
     * Enqueue the masonry grid JavaScript
     */
    private static function enqueue_checkins_grid_script($gridId, $company_info)
    {
        // Enqueue masonry grid script
        wp_enqueue_script(
            'jobcapturepro-checkins-grid',
            JOBCAPTUREPRO_PLUGIN_URL . 'dist/js/checkins/masonry-grid.min.js',
            array(),
            JOBCAPTUREPRO_VERSION,
            true
        );

        // Pass grid ID to JavaScript
        wp_localize_script('jobcapturepro-checkins-grid', 'jobcaptureproGridData', array(
            'gridId' => $gridId
        ));


        // Enqueue load more script
        wp_enqueue_script(
            'jobcapturepro-checkins-load-more',
            JOBCAPTUREPRO_PLUGIN_URL . 'dist/js/checkins/load-more.min.js',
            array(),
            JOBCAPTUREPRO_VERSION,
            true
        );

        global $jcp_combined_sc_atts;

        // Pass company id to the load more script 
        wp_localize_script('jobcapturepro-checkins-load-more', 'jobcaptureproLoadMoreData', array(
            'companyId' => $company_info['id'] ?? null,
            'baseApiUrl' => JobCaptureProAPI::get_wp_plugin_api_base_url(),
            'scAtts' => $jcp_combined_sc_atts ?? array(),
        ));
    }
}
