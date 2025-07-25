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
        // Check if companyid attribute was provided
        $company_id = isset($atts['companyid']) ? sanitize_text_field($atts['companyid']) : null;

        if ($company_id) {
            // Fetch specific company information using the direct endpoint)
            $company_info = $this->fetch_api_data("companies/" . $company_id, array())['data'];
        } else {
            // If no company ID provided, fetch default company info
            $company_info = $this->fetch_api_data('companies', $atts)['data'];
        }

        if (!$company_info) {
            return 'No company info found';
        }

        // Fetch checkins data
        $checkins_result = $this->fetch_api_data('checkins', $atts);
        if (!$checkins_result) {
            return 'Error fetching checkins data';
        }

        $checkin_id = $checkins_result['checkin_id'];
        $checkins = $checkins_result['data'];

        // Fetch map data
        $map_result = $this->fetch_api_data('map', $atts);
        if (!$map_result) {
            return 'Error fetching map data';
        }

        // Extract map data
        $map_data = $map_result['data'];

        return JobCaptureProTemplates::render_combined_components(
            $company_info,
            $map_data,
            $checkins,
            $checkin_id
        );
    }

    /**
    * Shortcode to display company information
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
     * Shortcode to display reviews
     */
    public function get_reviews($atts)
    {
        // Extract attributes
        $atts = shortcode_atts(array(
            'companyid' => '',
            'limit' => 5,
            'title' => 'Recent Reviews'
        ), $atts, 'jcp_reviews');

        $company_id = sanitize_text_field($atts['companyid']);
        $limit = (int)$atts['limit'];
        $title = sanitize_text_field($atts['title']);

        // Check for checkin ID in URL parameter
        $checkin_id = isset($_GET['checkinId']) ? sanitize_text_field($_GET['checkinId']) : null;

        // Check for company ID in URL parameter if not provided in shortcode
        if (!$company_id && isset($_GET['companyId'])) {
            $company_id = sanitize_text_field($_GET['companyId']);
        }

        // Fetch reviews data
        $reviews = $this->fetch_reviews_data($company_id, $checkin_id, $limit);

        // Generate output
        $output = '<div class="jcp-reviews-shortcode">';
        
        if (!empty($title)) {
            $output .= '<h3>' . esc_html($title) . '</h3>';
        }

        if ($reviews && !empty($reviews)) {
            $output .= JobCaptureProTemplates::render_reviews($reviews);
        } else {
            $output .= '<div class="jcp-no-reviews">';
            $output .= '<p>There are no recent reviews.</p>';
            $output .= '</div>';
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Fetch reviews data from API
     */
    private function fetch_reviews_data($company_id = null, $checkin_id = null, $limit = 5)
    {
        // Get the API Key from the plugin options
        $options = get_option('jobcapturepro_options');
        $apikey = trim($options['jobcapturepro_field_apikey']);
        
        if (empty($apikey)) {
            return null;
        }

        $url = $this->jcp_api_base_url . 'reviews';

        // Build query parameters
        $query_params = array();
        
        if ($company_id) {
            $query_params[] = "companyId=" . urlencode($company_id);
        }
        
        if ($checkin_id) {
            $query_params[] = "checkinId=" . urlencode($checkin_id);
        }
        
        if ($limit) {
            $query_params[] = "limit=" . urlencode($limit);
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
        
        if (is_wp_error($request)) {
            return null;
        }

        $body = wp_remote_retrieve_body($request);
        $response = json_decode($body, true);

        return $response;
    }

    /**
     * Shortcode to display nearby checkins
     */
    public function get_nearby_checkins($atts)
    {
        // Extract attributes
        $atts = shortcode_atts(array(
            'checkinid' => '',
            'limit' => 10,
            'title' => 'Nearby Checkins',
            'exclude_current' => 'true'
        ), $atts, 'jcp_nearby_checkins');

        $provided_checkin_id = sanitize_text_field($atts['checkinid']);
        $limit = (int)$atts['limit'];
        $title = sanitize_text_field($atts['title']);
        $exclude_current = $atts['exclude_current'] === 'true';

        // Check for checkin ID - use provided or URL parameter
        $checkin_id = $provided_checkin_id;
        if (!$checkin_id && isset($_GET['checkinId'])) {
            $checkin_id = sanitize_text_field($_GET['checkinId']);
        }

        if (!$checkin_id) {
            return '<div class="jcp-no-nearby-checkins"><p>No checkin ID provided to find nearby checkins.</p></div>';
        }

        // Get the city from the current checkin
        $city = $this->get_checkin_city($checkin_id);
        
        if (!$city) {
            return '<div class="jcp-no-nearby-checkins"><p>Could not determine city for nearby checkins.</p></div>';
        }

        // Fetch nearby checkins data
        $checkins = $this->fetch_nearby_checkins($city, $limit, $exclude_current ? $checkin_id : null);

        // Generate output
        $output = '<div class="jcp-nearby-checkins-shortcode">';
        
        if (!empty($title)) {
            $output .= '<h3>' . esc_html($title) . '</h3>';
        }

        if ($checkins && !empty($checkins)) {
            $output .= JobCaptureProTemplates::render_nearby_checkins($checkins, $city);
        } else {
            $output .= '<div class="jcp-no-nearby-checkins">';
            $output .= '<p>No nearby checkins found.</p>';
            $output .= '</div>';
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Get the city from a specific checkin
     */
    private function get_checkin_city($checkin_id)
    {
        // Get the API Key from the plugin options
        $options = get_option('jobcapturepro_options');
        $apikey = trim($options['jobcapturepro_field_apikey']);
        
        if (empty($apikey)) {
            return null;
        }

        $url = $this->jcp_api_base_url . 'checkins/' . urlencode($checkin_id);

        // Set the API request headers
        $args = array(
            'timeout' => 15,
            'headers' => array(
                'API_KEY' => $apikey
            )
        );

        // Make the API request
        $request = wp_remote_get($url, $args);
        
        if (is_wp_error($request)) {
            return null;
        }

        $body = wp_remote_retrieve_body($request);
        $checkin = json_decode($body, true);

        // Extract city from the checkin data
        if (isset($checkin['city'])) {
            return $checkin['city'];
        }
        
        // If city is not directly available, try to extract from address
        if (isset($checkin['address'])) {
            // Try to parse city from address string
            // This is a simple approach - you might need more sophisticated parsing
            $address_parts = explode(',', $checkin['address']);
            if (count($address_parts) >= 2) {
                // Assume city is the second-to-last part (before state/province)
                $city_part = trim($address_parts[count($address_parts) - 2]);
                return $city_part;
            }
        }

        return null;
    }

    /**
     * Fetch nearby checkins from API
     */
    private function fetch_nearby_checkins($city, $limit = 10, $exclude_checkin_id = null)
    {
        // Get the API Key from the plugin options
        $options = get_option('jobcapturepro_options');
        $apikey = trim($options['jobcapturepro_field_apikey']);
        
        if (empty($apikey)) {
            return null;
        }

        $url = $this->jcp_api_base_url . 'checkins';

        // Build query parameters
        $query_params = array();
        
        if ($city) {
            $query_params[] = "city=" . urlencode($city);
        }
        
        if ($limit) {
            $query_params[] = "limit=" . urlencode($limit);
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
        
        if (is_wp_error($request)) {
            return null;
        }

        $body = wp_remote_retrieve_body($request);
        $checkins = json_decode($body, true);

        // Filter out the current checkin if requested
        if ($exclude_checkin_id && is_array($checkins)) {
            $checkins = array_filter($checkins, function($checkin) use ($exclude_checkin_id) {
                return isset($checkin['id']) && $checkin['id'] != $exclude_checkin_id;
            });
        }

        return $checkins;
    }
}
