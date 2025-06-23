<?php

/**
 * This file defines the shortcode behavior for the plugin.
 */

class JobCaptureProShortcodes
{

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

        // Get the API Key from the plugin options
        $options = get_option('jobcapturepro_options');
        $apikey = trim(sanitize_text_field($options['jobcapturepro_field_apikey']));

        // Set the API endpoint URL
        $url = $this->jcp_api_base_url . "checkins/" . $checkin_id;

        // Set the API request headers
        $args = array(
            'timeout' => 15,
            'headers' => array(
                'API_KEY' => $apikey
            )
        );

        // Make the API request and return the response body
        $request = wp_remote_get($url, $args);
        $body = wp_remote_retrieve_body($request);
        if (is_wp_error($request)) {
            return;
        } else {
            // Decode the JSON response
            $checkin = json_decode($body, true);

            return JobCaptureProTemplates::render_checkins_grid([$checkin]);

        }
    }

    /**
     * Shortcode to display all checkins.
     */
    public function get_all_checkins($atts)
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
        $url = $this->jcp_api_base_url . "checkins";

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

        // Make the API request and return the response body
        $request = wp_remote_get($url, $args);
        $body = wp_remote_retrieve_body($request);

        if (is_wp_error($request)) {
            return;
        } else {
            // Decode the JSON response
            $checkins = json_decode($body, true);

            return JobCaptureProTemplates::render_checkins_grid($checkins);

        }
    }

    /**
     * Shortcode to display a heatmap
     */
    public function get_map($atts) // TODO: rename to get_heatmap
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

        $url = $this->jcp_api_base_url . "map";

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

        // Make the API request and return the response body
        $request = wp_remote_get($url, $args);
        $body = wp_remote_retrieve_body($request);
        if (is_wp_error($request)) {
            return;
        } else {
            // Assume the response body is a JSON array of locations as defined by geopoints in RFC 7946

            
            // Decode JSON response
            $response_data = json_decode($body, true);
            
            // Extract locations and maps API key from response
            $locations = isset($response_data['locations']) ? $response_data['locations'] : [];
            $maps_api_key = isset($response_data['googleMapsApiKey']['value']) ? $response_data['googleMapsApiKey']['value'] : '';

            return JobCaptureProTemplates::render_heatmap($locations, $maps_api_key);
        }
    }


    /**
     * Shortcode to display a map with multiple markers
     */
    public function get_multimap($atts)
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

        $url = $this->jcp_api_base_url . "map";

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

        // Make the API request and return the response body
        $request = wp_remote_get($url, $args);
        $body = wp_remote_retrieve_body($request);
        if (is_wp_error($request)) {
            return;
        } else {
                    // Assume the response body is a JSON array of locations as defined by geopoints in RFC 7946

            
            // Decode JSON response
            $response_data = json_decode($body, true);
            
            // Extract locations and maps API key from response
            $locations = isset($response_data['locations']) ? $response_data['locations'] : [];
            $maps_api_key = isset($response_data['googleMapsApiKey']['value']) ? $response_data['googleMapsApiKey']['value'] : '';

        }


        return JobCaptureProTemplates::render_multimap($locations, $maps_api_key);
    }

}
