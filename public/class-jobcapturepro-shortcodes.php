<?php

/**
 * Shortcode functionality for JobCapturePro plugin.
 *
 * @package JobCapturePro
 * @since   1.0.0
 */

// Prevent direct access.
if (! defined('ABSPATH')) {
    exit;
}

/**
 * This file defines the shortcode behavior for the plugin.
 *
 * @since 1.0.0
 */

class JobCaptureProShortcodes
{

    /**
     * Render a user-friendly error message
     * 
     * @param string $message The error message to display
     * @param string $error_code Optional error code for debugging
     * @param bool $show_debug Whether to show debug information
     * @return string HTML error message
     */
    private function render_error_message($message, $error_code = '', $show_debug = false)
    {
        $debug_info = '';
        if ($show_debug && defined('WP_DEBUG') && WP_DEBUG && current_user_can('manage_options')) {
            $debug_info = sprintf(
                '<small class="jobcapturepro-debug">Debug: %s</small>',
                esc_html($error_code)
            );
        }

        return sprintf(
            '<div class="jobcapturepro-error" role="alert">
                <p>%s</p>
                %s
            </div>',
            esc_html($message),
            $debug_info
        );
    }

    /**
     * Log API errors with context
     * 
     * @param string $message Error message
     * @param string $context Additional context
     * @param array $data Optional data to log
     */
    private function log_api_error($message, $context = '', $data = array())
    {
        $log_message = 'JobCapturePro API Error: ' . $message;

        if (!empty($context)) {
            $log_message .= ' | Context: ' . $context;
        }

        if (!empty($data)) {
            $log_message .= ' | Data: ' . wp_json_encode($data);
        }

        error_log($log_message);
    }

    /**
     * Helper method to fetch data from API with common logic
     * 
     * @param string $endpoint The API endpoint (e.g., 'checkins', 'map')
     * @param array $atts Shortcode attributes
     * @return array|null Returns array with checkin_id, company_id, and API response data, or null on error
     */
    private function fetch_api_data($endpoint, $atts)
    {
        // Sanitize and validate shortcode attributes
        $atts = shortcode_atts(array(
            'checkinid' => '',
            'companyid' => '',
        ), $atts, 'jobcapturepro');

        // Check if checkinid attribute was provided, if not check URL parameter
        $checkin_id = JobCaptureProAdmin::sanitize_id_parameter($atts['checkinid'], 'checkin');

        // If no attribute provided, check for URL parameter
        if (!$checkin_id && isset($_GET['checkinId'])) {
            $checkin_id = JobCaptureProAdmin::sanitize_id_parameter(
                sanitize_text_field(wp_unslash($_GET['checkinId'])),
                'checkin'
            );
        }

        // Check if companyid attribute was provided, if not check URL parameter
        $company_id = JobCaptureProAdmin::sanitize_id_parameter($atts['companyid'], 'company');

        // If no attribute provided, check for URL parameter
        if (!$company_id && isset($_GET['companyId'])) {
            $company_id = JobCaptureProAdmin::sanitize_id_parameter(
                sanitize_text_field(wp_unslash($_GET['companyId'])),
                'company'
            );
        }

        // Get the API Key using the enhanced sanitization method
        $apikey = JobCaptureProAdmin::get_sanitized_api_key();

        if (!$apikey) {
            error_log('JobCapturePro: Invalid or missing API key');
            return null;
        }

        // Sanitize the endpoint parameter
        $endpoint = sanitize_text_field($endpoint);
        if (empty($endpoint)) {
            error_log('JobCapturePro: Invalid endpoint provided');
            return null;
        }

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

        // Validate the final URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            error_log('JobCapturePro: Invalid API URL constructed: ' . $url);
            return null;
        }

        // Set the API request headers
        $args = array(
            'timeout' => 15,
            'headers' => array(
                'Authorization' => "Bearer $apikey",
                'User-Agent' => 'JobCapturePro-WordPress-Plugin/' . JOBCAPTUREPRO_VERSION
            ),
            'sslverify' => true
        );

        // Make the API request
        $request = wp_remote_get($url, $args);

        if (is_wp_error($request)) {
            $this->log_api_error(
                $request->get_error_message(),
                'wp_remote_get failed',
                array('url' => $url, 'endpoint' => $endpoint)
            );
            return null;
        }

        $response_code = wp_remote_retrieve_response_code($request);
        $response_message = wp_remote_retrieve_response_message($request);

        if ($response_code !== 200) {
            $this->log_api_error(
                "HTTP {$response_code}: {$response_message}",
                'API returned non-200 status',
                array(
                    'url' => $url,
                    'endpoint' => $endpoint,
                    'status_code' => $response_code,
                    'response_headers' => wp_remote_retrieve_headers($request)
                )
            );
            return null;
        }

        $body = wp_remote_retrieve_body($request);

        if (empty($body)) {
            $this->log_api_error(
                'Empty response body received from API',
                'API returned empty response',
                array('url' => $url, 'endpoint' => $endpoint)
            );
            return null;
        }

        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->log_api_error(
                'Invalid JSON in API response: ' . json_last_error_msg(),
                'JSON decode failed',
                array(
                    'url' => $url,
                    'endpoint' => $endpoint,
                    'response_snippet' => substr($body, 0, 500)
                )
            );
            return null;
        }

        // Check if the API returned an error in the response
        if (isset($data['error'])) {
            $this->log_api_error(
                'API returned error: ' . $data['error'],
                'API error response',
                array('url' => $url, 'endpoint' => $endpoint, 'error_data' => $data)
            );
            return null;
        }

        return array(
            'checkin_id' => $checkin_id,
            'company_id' => $company_id,
            'data' => $data
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
        // Sanitize and validate shortcode attributes
        $atts = shortcode_atts(array(
            'checkinid' => '',
        ), $atts, 'jobcapturepro_checkin');

        // Check if checkinid attribute was provided, if not check URL parameter
        $checkin_id = JobCaptureProAdmin::sanitize_id_parameter($atts['checkinid'], 'checkin');

        // If no attribute provided, check for URL parameter
        if (!$checkin_id && isset($_GET['checkinid'])) {
            $checkin_id = JobCaptureProAdmin::sanitize_id_parameter(
                sanitize_text_field(wp_unslash($_GET['checkinid'])),
                'checkin'
            );
        }

        if (!$checkin_id) {
            return '<div class="jobcapturepro-error">' . esc_html(__('No valid checkin ID provided.', 'job-capture-pro')) . '</div>';
        }

        // Fetch specific checkin using the direct endpoint
        $result = $this->fetch_api_data("checkins/" . $checkin_id, array());
        if (!$result) {
            return $this->render_error_message(
                __('Unable to load checkin data at this time. Please try again later.', 'job-capture-pro'),
                'api_fetch_failed'
            );
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
            return $this->render_error_message(
                __('Unable to load checkins at this time. Please try again later.', 'job-capture-pro'),
                'checkins_fetch_failed'
            );
        }

        $checkin_id = $result['checkin_id'];
        $checkins = $result['data'] ?? array();

        // Validate that we have valid data structure
        if (!is_array($checkins)) {
            $this->log_api_error(
                'Invalid checkins data structure received',
                'invalid_checkins_data',
                array('data_type' => gettype($checkins))
            );
            return $this->render_error_message(
                __('Invalid checkins data received. Please try again later.', 'job-capture-pro'),
                'invalid_checkins_structure'
            );
        }

        return JobCaptureProTemplates::render_checkins_conditionally($checkin_id, $checkins);
    }

    /**
     * Shortcode to display a map with multiple markers
     */
    public function get_map($atts)
    {
        // Sanitize and validate shortcode attributes
        $atts = shortcode_atts(array(
            'companyid' => '',
        ), $atts);

        // Check if companyid attribute was provided
        $company_id = JobCaptureProAdmin::sanitize_id_parameter($atts['companyid'], 'company');

        if ($company_id) {
            // Fetch specific company information using the direct endpoint
            $company_result = $this->fetch_api_data("companies/" . $company_id, array());
            $company_info = $company_result ? $company_result['data'] : null;
        } else {
            // If no company ID provided, fetch default company info
            $company_result = $this->fetch_api_data('companies', $atts);
            $company_info = $company_result ? $company_result['data'] : null;
        }

        // Fetch map data
        $map_result = $this->fetch_api_data('map', $atts);

        if (!$map_result) {
            return $this->render_error_message(
                __('Unable to load map data at this time. Please try again later.', 'job-capture-pro'),
                'map_fetch_failed'
            );
        }

        $map_data = $map_result['data'];

        // Validate map data structure
        if (!is_array($map_data)) {
            $this->log_api_error(
                'Invalid map data structure received',
                'invalid_map_data',
                array('data_type' => gettype($map_data))
            );
            return $this->render_error_message(
                __('Invalid map data received. Please try again later.', 'job-capture-pro'),
                'invalid_map_structure'
            );
        }

        return JobCaptureProTemplates::render_map_conditionally($map_data, $company_info);
    }

    /**
     * Shortcode to display combined components (checkins grid + multimap)
     */
    public function get_combined_components($atts)
    {
        // Sanitize and validate shortcode attributes
        $atts = shortcode_atts(array(
            'companyid' => '',
        ), $atts, 'jobcapturepro_combined');

        // Check if companyid attribute was provided
        $company_id = JobCaptureProAdmin::sanitize_id_parameter($atts['companyid'], 'company');

        if ($company_id) {
            // Fetch specific company information using the direct endpoint
            $company_result = $this->fetch_api_data("companies/" . $company_id, array());
            $company_info = $company_result ? $company_result['data'] : null;
        } else {
            // If no company ID provided, fetch default company info
            $company_result = $this->fetch_api_data('companies', $atts);
            $company_info = $company_result ? $company_result['data'] : null;
        }

        if (!$company_info) {
            return $this->render_error_message(
                __('Company information is not available at this time.', 'job-capture-pro'),
                'company_info_not_found'
            );
        }

        // Fetch checkins data
        $checkins_result = $this->fetch_api_data('checkins', $atts);
        if (!$checkins_result) {
            return $this->render_error_message(
                __('Unable to load checkins data at this time. Please try again later.', 'job-capture-pro'),
                'checkins_fetch_failed'
            );
        }

        $checkin_id = $checkins_result['checkin_id'];
        $checkins = $checkins_result['data']['checkins'] ?? array();

        // Validate checkins data structure
        if (!is_array($checkins)) {
            $this->log_api_error(
                'Invalid checkins data structure in combined components',
                'invalid_combined_checkins_data',
                array('data_type' => gettype($checkins))
            );
            return $this->render_error_message(
                __('Invalid checkins data received. Please try again later.', 'job-capture-pro'),
                'invalid_combined_checkins_structure'
            );
        }

        // Fetch map data
        $map_result = $this->fetch_api_data('map', $atts);
        if (!$map_result) {
            return $this->render_error_message(
                __('Unable to load map data at this time. Please try again later.', 'job-capture-pro'),
                'map_fetch_failed'
            );
        }

        // Extract and validate map data
        $map_data = $map_result['data'];

        if (!is_array($map_data)) {
            $this->log_api_error(
                'Invalid map data structure in combined components',
                'invalid_combined_map_data',
                array('data_type' => gettype($map_data))
            );
            return $this->render_error_message(
                __('Invalid map data received. Please try again later.', 'job-capture-pro'),
                'invalid_combined_map_structure'
            );
        }

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
        // Sanitize and validate shortcode attributes
        $atts = shortcode_atts(array(
            'companyid' => '',
        ), $atts, 'jobcapturepro_company_info');

        // Check if companyid attribute was provided
        $company_id = JobCaptureProAdmin::sanitize_id_parameter($atts['companyid'], 'company');

        if (!$company_id) {
            return $this->render_error_message(
                __('No valid company ID provided.', 'job-capture-pro'),
                'missing_company_id'
            );
        }

        // Get the API Key using enhanced sanitization
        $apikey = JobCaptureProAdmin::get_sanitized_api_key();

        if (!$apikey) {
            return $this->render_error_message(
                __('Plugin configuration error. Please contact the site administrator.', 'job-capture-pro'),
                'missing_api_key'
            );
        }

        // Set the API endpoint URL
        $url = $this->jcp_api_base_url . "companies/" . urlencode($company_id);

        // Validate the URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $this->log_api_error('Invalid company API URL constructed', 'url_validation', array('url' => $url));
            return $this->render_error_message(
                __('Unable to connect to company data service.', 'job-capture-pro'),
                'invalid_api_url'
            );
        }

        // Set the API request headers
        $args = array(
            'timeout' => 15,
            'headers' => array(
                'Authorization' => "Bearer $apikey",
                'User-Agent' => 'JobCapturePro-WordPress-Plugin/' . JOBCAPTUREPRO_VERSION
            ),
            'sslverify' => true
        );

        // Make the API request
        $request = wp_remote_get($url, $args);

        if (is_wp_error($request)) {
            $this->log_api_error(
                $request->get_error_message(),
                'company_api_request_failed',
                array('url' => $url, 'company_id' => $company_id)
            );
            return $this->render_error_message(
                __('Unable to load company information at this time. Please try again later.', 'job-capture-pro'),
                'api_request_failed'
            );
        }

        $response_code = wp_remote_retrieve_response_code($request);
        $body = wp_remote_retrieve_body($request);

        if ($response_code !== 200) {
            $this->log_api_error(
                "Company API returned HTTP {$response_code}",
                'company_api_http_error',
                array(
                    'url' => $url,
                    'company_id' => $company_id,
                    'status_code' => $response_code
                )
            );

            if ($response_code === 404) {
                return $this->render_error_message(
                    __('Company information not found.', 'job-capture-pro'),
                    'company_not_found'
                );
            }

            return $this->render_error_message(
                __('Unable to load company information at this time. Please try again later.', 'job-capture-pro'),
                'api_http_error'
            );
        }

        if (empty($body)) {
            $this->log_api_error(
                'Empty response from company API',
                'company_api_empty_response',
                array('url' => $url, 'company_id' => $company_id)
            );
            return $this->render_error_message(
                __('No company data available.', 'job-capture-pro'),
                'empty_response'
            );
        }

        // Decode the JSON response
        $company_data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->log_api_error(
                'Invalid JSON in company API response: ' . json_last_error_msg(),
                'company_api_json_error',
                array(
                    'url' => $url,
                    'company_id' => $company_id,
                    'response_snippet' => substr($body, 0, 200)
                )
            );
            return $this->render_error_message(
                __('Invalid company data received. Please try again later.', 'job-capture-pro'),
                'invalid_json'
            );
        }

        // Check for API error response
        if (isset($company_data['error'])) {
            $this->log_api_error(
                'Company API returned error: ' . $company_data['error'],
                'company_api_error_response',
                array('url' => $url, 'company_id' => $company_id, 'error_data' => $company_data)
            );
            return $this->render_error_message(
                __('Company information is currently unavailable.', 'job-capture-pro'),
                'api_error_response'
            );
        }

        // Prepare company info array with expected structure and proper sanitization
        $company_info = array(
            'address' => isset($company_data['address']) ? $company_data['address'] : '',
            'name' => isset($company_data['name']) ? sanitize_text_field($company_data['name']) : '',
            'url' => isset($company_data['url']) ? esc_url_raw($company_data['url']) : '',
            'phoneNumberString' => isset($company_data['phoneNumberString']) ? sanitize_text_field($company_data['phoneNumberString']) : '',
            'tn' => isset($company_data['tn']) ? sanitize_text_field($company_data['tn']) : '',
            'logoUrl' => isset($company_data['logoUrl']) ? esc_url_raw($company_data['logoUrl']) : '',
            'quoteUrl' => isset($company_data['quoteUrl']) ? esc_url_raw($company_data['quoteUrl']) : '',
            'description' => isset($company_data['description']) ? wp_kses_post($company_data['description']) : ''
        );

        return JobCaptureProTemplates::render_company_info($company_info);
    }
}
