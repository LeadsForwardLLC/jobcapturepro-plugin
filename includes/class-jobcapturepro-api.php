<?php

/**
 * Simple proxy API for JobCapturePro plugin
 */
class JobCaptureProAPI
{
    private $namespace = 'jobcapturepro/v1';
    private $jcp_api_base_url;

    public function __construct($jcp_api_base_url)
    {
        $this->jcp_api_base_url = $jcp_api_base_url;
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    /**
     * Register single proxy endpoint
     */
    public function register_routes()
    {
        // Single proxy endpoint for checkin data
        register_rest_route($this->namespace, '/checkin/(?P<id>[a-zA-Z0-9\-_]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_checkin_proxy'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => array($this, 'sanitize_checkin_id'),
                    'validate_callback' => array($this, 'validate_checkin_id'),
                    'description' => 'The checkin ID to retrieve'
                )
            )
        ));
    }

    /**
     * Check permissions for API access
     * 
     * @param WP_REST_Request $request The REST request
     * @return bool True if access is allowed
     */
    public function check_permissions($request)
    {
        // For now, allow public access but we could add authentication here
        // Rate limiting could also be implemented here
        return true;
    }

    /**
     * Sanitize checkin ID parameter
     * 
     * @param string $value The value to sanitize
     * @param WP_REST_Request $request The REST request
     * @param string $param The parameter name
     * @return string|WP_Error Sanitized value or error
     */
    public function sanitize_checkin_id($value, $request, $param)
    {
        $sanitized_id = JobCaptureProAdmin::sanitize_id_parameter($value, 'checkin');
        
        if ($sanitized_id === null) {
            return new WP_Error('invalid_checkin_id', 'Invalid checkin ID format', array('status' => 400));
        }
        
        return $sanitized_id;
    }

    /**
     * Validate checkin ID parameter
     * 
     * @param string $value The value to validate
     * @param WP_REST_Request $request The REST request
     * @param string $param The parameter name
     * @return bool|WP_Error True if valid, error otherwise
     */
    public function validate_checkin_id($value, $request, $param)
    {
        if (empty($value)) {
            return new WP_Error('empty_checkin_id', 'Checkin ID cannot be empty', array('status' => 400));
        }

        if (strlen($value) > 100) {
            return new WP_Error('checkin_id_too_long', 'Checkin ID is too long', array('status' => 400));
        }

        if (!preg_match('/^[a-zA-Z0-9\-_]+$/', $value)) {
            return new WP_Error('invalid_checkin_id_format', 'Checkin ID contains invalid characters', array('status' => 400));
        }

        return true;
    }

    /**
     * Proxy endpoint - WordPress hits real API and returns data
     */
    public function get_checkin_proxy($request)
    {
        $checkin_id = $request->get_param('id');

        // Get API key using enhanced sanitization
        $apikey = JobCaptureProAdmin::get_sanitized_api_key();
        
        if (!$apikey) {
            return new WP_Error('missing_api_key', 'API key not configured or invalid', array('status' => 500));
        }

        // Build URL to real API - the checkin_id is already sanitized by the sanitize callback
        $url = $this->jcp_api_base_url . "checkins/" . urlencode($checkin_id);

        // Make request to real API
        $args = array(
            'timeout' => 15,
            'headers' => array(
                'API_KEY' => $apikey
            )
        );

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            return new WP_Error('api_error', 'Failed to fetch checkin data', array('status' => 500));
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!$data) {
            return new WP_Error('no_data', 'No checkin found', array('status' => 404));
        }

        // Return the data with CORS headers
        $response = rest_ensure_response($data);

        return $response;
    }

    /**
     * Get the WordPress API base URL for frontend use
     */
    public static function get_wp_plugin_api_base_url()
    {
        return get_rest_url(null, 'jobcapturepro/v1');
    }
}
