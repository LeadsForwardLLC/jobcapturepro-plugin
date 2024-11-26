<?php

/**
 * This file defines the shortcode behavior for the plugin.
 */

class JobCaptureProShortcodes {

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
	public function __construct( $plugin_name, $version, $jcp_api_base_url ) {

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
        $url = $this->jcp_api_base_url . "checkin/" . $checkin_id;
        
        // Set the API request headers
        $args = array(
            'timeout' => 15,
            'headers' => array(
                'API_KEY' => $apikey
            )
        );

        // Make the API request and return the response body
        $request = wp_remote_get($url, $args);
        $body = wp_remote_retrieve_body( $request );
        if( is_wp_error( $request ) ) {
            return;
        } else {
            return $body;
        }
    }

    /**
     * Shortcode to display all checkins.
     */
    public function get_all_checkins($atts)
    {
        // Get the API Key from the plugin options
        $options = get_option('jobcapturepro_options');
        $apikey = trim($options[ 'jobcapturepro_field_apikey' ]);

        $url = $this->jcp_api_base_url . "checkin";
        
        // Set the API request headers
        $args = array(
            'timeout' => 15,
            'headers' => array(
                'API_KEY' => $apikey
            )
        );

        // Make the API request and return the response body
        $request = wp_remote_get($url, $args);
        $body = wp_remote_retrieve_body( $request );
        if( is_wp_error( $request ) ) {
            return;
        } else {
            return $body;
        }
    }
}
