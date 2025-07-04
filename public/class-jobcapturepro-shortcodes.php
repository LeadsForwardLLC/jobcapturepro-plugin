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
        // Check if checkinid attribute was provided
        $checkin_id = isset($atts['checkinid']) ? $atts['checkinid'] : null;

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

            return JobCaptureProTemplates::render_checkin_card($checkin);

        }
    }

    /**
     * Shortcode to display all checkins.
     */
    public function get_all_checkins($atts)
    {
        // Get the API Key from the plugin options
        $options = get_option('jobcapturepro_options');
        $apikey = trim($options['jobcapturepro_field_apikey']);

        $url = $this->jcp_api_base_url . "checkins";

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
    * Shortcode to display company information (Abdul)
    */
    public function get_company_info($atts)
    {
    // Check if companyid attribute was provided
    $company_id = isset($atts['companyid']) ? sanitize_text_field($atts['companyid']) : null;

    if (!$company_id) {
        return 'No company ID provided';
    }

    // Get the API Key from the plugin options
    $options = get_option('jobcapturepro_options');
    $apikey = trim($options['jobcapturepro_field_apikey']);

    // Set the API endpoint URL
    $url = $this->jcp_api_base_url . "companies/" . $company_id;

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
        return 'Error fetching company information';
    } else {
        // Decode the JSON response
        $company_data = json_decode($body, true);

        // Prepare company info array with expected structure
        $company_info = [
            'address' => isset($company_data['address']) ? $company_data['address'] : '',
            'name' => isset($company_data['name']) ? $company_data['name'] : '',
            'url' => isset($company_data['url']) ? $company_data['url'] : '',
            'tn' => isset($company_data['tn']) ? $company_data['tn'] : '',
            'logoUrl' => isset($company_data['logoUrl']) ? $company_data['logoUrl'] : ''
        ];

        return JobCaptureProTemplates::render_company_info($company_info);
        }
    }

    /**
     * Shortcode to display a heatmap
     */
    public function get_map($atts) // TODO: rename to get_heatmap
    {
        // Get the API Key from the plugin options
        $options = get_option('jobcapturepro_options');
        $apikey = trim($options['jobcapturepro_field_apikey']);

        $url = $this->jcp_api_base_url . "map";

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
        // Get the API Key from the plugin options
        $options = get_option('jobcapturepro_options');
        $apikey = trim($options['jobcapturepro_field_apikey']);

        $url = $this->jcp_api_base_url . "map";
        $mapsApiKeyUrl = $this->jcp_api_base_url . "config";

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