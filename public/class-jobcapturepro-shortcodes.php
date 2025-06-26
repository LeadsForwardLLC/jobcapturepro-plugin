<?php

/**
 * This file defines the shortcode behavior for the plugin.
 */

class JobCaptureProShortcodes
{

    /**
     * Helper method to fetch data from API with common logic
     * 
     * @param string $endpoint The API endpoint (e.g., 'checkins', 'map')
     * @param array $atts Shortcode attributes
     * @return array|null Returns array with checkin_id, company_id, and API response data, or null on error
     */
    private function fetch_api_data($endpoint, $atts)
    {
        // Check if checkinid attribute was provided, if not check URL parameter
        $checkin_id = isset($atts['checkinid']) ? $atts['checkinid'] : null;
        
        // If no attribute provided, check for URL parameter
        if (!$checkin_id && isset($_GET['checkinId'])) {
            $checkin_id = sanitize_text_field($_GET['checkinId']);
        }

        // Check if companyid attribute was provided, if not check URL parameter
        $company_id = isset($atts['companyid']) ? sanitize_text_field($atts['companyid']) : null;
        
        // If no attribute provided, check for URL parameter
        if (!$company_id && isset($_GET['companyId'])) {
            $company_id = sanitize_text_field($_GET['companyId']);
        }

        // Get the API Key from the plugin options
        $options = get_option('jobcapturepro_options');
        $apikey = trim($options['jobcapturepro_field_apikey']);
        $url = $this->jcp_api_base_url . $endpoint;

        // Add company_id and checkin_id as query parameters if provided
        $query_params = array();
        
        if ($company_id) {
            $query_params[] = "companyId=" . urlencode($company_id);
        }
        
        if ($checkin_id) {
            $query_params[] = "checkinId=" . urlencode($checkin_id);
        }
        
        if (!empty($query_params)) {
            $url .= "?" . implode("&", $query_params);
        }

        // Set the API request headers
        $args = array(
            'timeout' => 15,
            'headers' => array(
                'API_KEY' => $apikey
            )
        );

        // Make the API request
        $request = wp_remote_get($url, $args);
        $body = wp_remote_retrieve_body($request);
        
        if (is_wp_error($request)) {
            return null;
        }

        return array(
            'checkin_id' => $checkin_id,
            'company_id' => $company_id,
            'data' => json_decode($body, true)
        );
    }

    /**
     * The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     */
    private $version;

    /**
     * The base URL for our backend API.
     */
    private $jcp_api_base_url;

    /**
     * Initialize the class and set its properties.
     */
    public function __construct($plugin_name, $version, $jcp_api_base_url)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->jcp_api_base_url = $jcp_api_base_url;
    }

    /**
     * Shortcode to display a specific checkin.
     */
    public function get_checkin($atts)
    {
        // Check if checkinid attribute was provided, if not check URL parameter
        $checkin_id = isset($atts['checkinid']) ? $atts['checkinid'] : null;
        
        // If no attribute provided, check for URL parameter
        if (!$checkin_id && isset($_GET['checkinid'])) {
            $checkin_id = sanitize_text_field($_GET['checkinid']);
        }

        if (!$checkin_id) {
            return 'No checkin ID provided';
        }

        // Fetch specific checkin using the direct endpoint
        $result = $this->fetch_api_data("checkins/" . $checkin_id, array());
        if (!$result) {
            return 'Error fetching checkin data';
        }

        $checkin = $result['data'];
        return JobCaptureProTemplates::render_checkins_grid([$checkin]);
    }

    /**
     * Shortcode to display all checkins.
     */
    public function get_all_checkins($atts)
    {
        $result = $this->fetch_api_data('checkins', $atts);
        if (!$result) {
            return 'Error fetching checkins data';
        }

        $checkin_id = $result['checkin_id'];
        $checkins = $result['data'];

        return JobCaptureProTemplates::render_checkins_conditionally($checkin_id, $checkins);
    }

    /**
     * Shortcode to display a heatmap
     */
    public function get_heatmap($atts)
    {
        $result = $this->fetch_api_data('map', $atts);
        if (!$result) {
            return 'Error fetching map data';
        }

        // Extract locations and maps API key from response
        $response_data = $result['data'];
        $locations = isset($response_data['locations']) ? $response_data['locations'] : [];
        $maps_api_key = isset($response_data['googleMapsApiKey']['value']) ? $response_data['googleMapsApiKey']['value'] : '';

        return JobCaptureProTemplates::render_heatmap($locations, $maps_api_key);
    }

    /**
     * Shortcode to display a map with multiple markers
     */
    public function get_multimap($atts)
    {
        $result = $this->fetch_api_data('map', $atts);
        if (!$result) {
            return 'Error fetching map data';
        }

        $checkin_id = $result['checkin_id'];
        
        return JobCaptureProTemplates::render_map_conditionally($checkin_id, $result['data']);
    }

    /**
     * Shortcode to display combined components (checkins grid + multimap)
     */
    public function get_combined_components($atts)
    {
        // Fetch checkins data
        $checkins_result = $this->fetch_api_data('checkins', $atts);
        if (!$checkins_result) {
            return 'Error fetching checkins data';
        }

        // Fetch map data
        $map_result = $this->fetch_api_data('map', $atts);
        if (!$map_result) {
            return 'Error fetching map data';
        }

        $checkin_id = $checkins_result['checkin_id'];
        $checkins = $checkins_result['data'];
        
        // Extract map data
        $map_data = $map_result['data'];

        return JobCaptureProTemplates::render_combined_components(
            $checkins,
            $map_data,
            $checkin_id
        );
    }

}
