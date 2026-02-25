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
            // Otherwise render as a slider of multiple checkins
            return JobCaptureProTemplates::render_checkins_slider($checkins, $company_info);
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

        // Powered by footer
        $output .= JobCapturePro_Template::render_template('powered-by-footer');

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
     * Generate HTML for the checkins slider
     *
     * @param array $checkins Array of checkin data
     * @param array $company_info Company data for stats
     * @return string HTML for the checkins slider
     */
    public static function render_checkins_slider($checkins, $company_info = array())
    {
        // Enqueue styles and scripts
        self::enqueue_checkins_slider_styles();
        self::enqueue_checkins_slider_script($company_info);

        $checkins_slider_html = JobCapturePro_Template::render_template('checkins-slider', [
            'checkins' => $checkins,
            'company_info' => $company_info,
        ]);

        if (self::should_show_feature('show_company_stats', !empty($company_info['stats']))) {
            $checkins_slider_html .= JobCapturePro_Template::render_template('company-stats', [
                'company_info' => $company_info,
            ]);
        }

        return $checkins_slider_html;
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

        // Enqueue styles for the map
        self::enqueue_map_styles();

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
     * Enqueue checkins slider styles
     */
    private static function enqueue_checkins_slider_styles()
    {
        wp_enqueue_style(
            'jobcapturepro-inter-font',
            'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
            array(),
            null
        );

        wp_enqueue_style(
            'jobcapturepro-checkins-slider',
            JOBCAPTUREPRO_PLUGIN_URL . 'dist/css/checkins-slider.min.css',
            array('jobcapturepro-inter-font'),
            JOBCAPTUREPRO_VERSION,
            'all'
        );
    }


    /**
     * Enqueue the slider JavaScript
     */
    private static function enqueue_checkins_slider_script($company_info)
    {
        // Enqueue slider script
        wp_enqueue_script(
            'jobcapturepro-checkins-slider',
            JOBCAPTUREPRO_PLUGIN_URL . 'dist/js/checkins/slider.min.js',
            array(),
            JOBCAPTUREPRO_VERSION,
            true
        );

        // Enqueue checkins pagination script
        wp_enqueue_script(
            'jobcapturepro-checkins-pagination',
            JOBCAPTUREPRO_PLUGIN_URL . 'dist/js/checkins/checkins-pagination.min.js',
            array(),
            JOBCAPTUREPRO_VERSION,
            true
        );

        global $jcp_combined_sc_atts;

        // Pass data to the checkins pagination script
        wp_localize_script('jobcapturepro-checkins-pagination', 'jobcaptureproLoadMoreData', array(
            'companyId' => $company_info['id'] ?? null,
            'baseApiUrl' => JobCaptureProAPI::get_wp_plugin_api_base_url(),
            'scAtts' => $jcp_combined_sc_atts ?? array(),
        ));
    }
}
