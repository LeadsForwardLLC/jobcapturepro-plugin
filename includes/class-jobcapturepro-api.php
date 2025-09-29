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
        register_rest_route($this->namespace, '/checkin/(?P<id>[a-zA-Z0-9-]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_checkin_proxy'),
            'permission_callback' => '__return_true', // Public access
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        ));
    }

    /**
     * Proxy endpoint - WordPress hits real API and returns data
     */
    public function get_checkin_proxy($request)
    {
        $checkin_id = $request->get_param('id');

        // Get API key from WordPress options
        $options = get_option('jobcapturepro_options');
        $apikey = trim($options['jobcapturepro_field_apikey']);

        // Build URL to real API
        $url = $this->jcp_api_base_url . "checkins/" . $checkin_id;

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
