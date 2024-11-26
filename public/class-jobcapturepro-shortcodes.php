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
	 * Initialize the class and set its properties.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

    /**
     * Shortcode to display a specific checkin.
     */
    public function get_checkin($atts)
    {

        // Get the API Key from the plugin options
        $options = get_option('jobcapturepro_options');
        $apikey = trim($options[ 'jobcapturepro_field_apikey' ]);

        // TODO: store the API link as a class constant and parameterize the checkin ID
        $url = "https://jcp-api--travel-app-eor5yc.us-central1.hosted.app/api/checkin/GZYpwDkw1CUuvFggavAE";
        
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

        // TODO: store the API link as a class constant
        $url = "https://jcp-api--travel-app-eor5yc.us-central1.hosted.app/api/checkin";
        
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
