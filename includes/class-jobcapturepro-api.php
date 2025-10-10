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
     * Get API key from WordPress options
     */
    private function get_api_key()
    {
        $options = get_option('jobcapturepro_options');
        return trim($options['jobcapturepro_field_apikey']);
    }

    /**
     * Get standard request arguments for API calls
     */
    private function get_request_args($apikey)
    {
        return array(
            'timeout' => 15,
            'headers' => array(
                'API_KEY' => $apikey
            )
        );
    }

    /**
     * Make API request and handle response
     */
    private function make_api_request($url, $error_context = 'data')
    {
        $apikey = $this->get_api_key();
        $args = $this->get_request_args($apikey);

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            return new WP_Error('api_error', "Failed to fetch {$error_context} data", array('status' => 500));
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!$data) {
            return new WP_Error('no_data', "No {$error_context} found", array('status' => 404));
        }

        return rest_ensure_response($data);
    }

    /**
     * Register API routes
     */
    public function register_routes()
    {
        // Register single checkin route
        register_rest_route($this->namespace, '/checkin/(?P<id>[a-zA-Z0-9-]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_checkin_proxy'),
            'permission_callback' => '__return_true',
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        ));

        // Register checkins route
        register_rest_router($this->namespace, '/checkins', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_checkins'),
            'permission_callback' => '__return_true',
            'args' => array(
                'page' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 1,
                    'sanitize_callback' => 'absint'
                ),
            )
        ));
    }

    /**
     * Single checkin endpoint
     */
    public function get_checkin($request)
    {
        // Get Checkin ID
        $checkin_id = $request->get_param('id');

        // Build URL to real API
        $url = $this->jcp_api_base_url . "checkins/" . $checkin_id;

        // Make the request
        return $this->make_api_request($url, 'checkin');
    }

    /**
     * All checkins endpoint
     */
    public function get_checkins($request)
    {
        // Get page number
        $page = $request->get_param('page') ?: 1;

        // Build URL to real API
        $url = $this->jcp_api_base_url . "checkins?page=" . $page;

        // Make the request
        return $this->make_api_request($url, 'checkins');
    }

    /**
     * Get the WordPress API base URL for frontend use
     */
    public static function get_wp_plugin_api_base_url()
    {
        return get_rest_url(null, 'jobcapturepro/v1');
    }
}
