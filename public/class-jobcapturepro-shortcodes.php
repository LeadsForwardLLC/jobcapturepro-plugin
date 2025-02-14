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


    /**
     * Shortcode to display a map
     */
    public function get_map($atts)
    {
        // Get the API Key from the plugin options
        $options = get_option('jobcapturepro_options');
        $apikey = trim($options[ 'jobcapturepro_field_apikey' ]);

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
        $body = wp_remote_retrieve_body( $request );
        if( is_wp_error( $request ) ) {
            return;
        } else {

            // Get the API Key from the plugin options
            $options = get_option('jobcapturepro_options');
            $mapsApikey = trim($options[ 'jobcapturepro_field_gmaps_apikey' ]);

            // Ensure necessary scripts are loaded
            wp_enqueue_script('google-maps', 'https://maps.googleapis.com/maps/api/js?libraries=visualization&key=' . $mapsApikey, array(), null, array('strategy' => 'async'));

            // Assume the response body is a JSON array of locations as defined by geopoints in RFC 7946

            // For testing, uncomment this to override API response with test data            
            /*
            $body = '{
                "type": "FeatureCollection",
                "features": [
                    {
                        "type": "Feature",
                        "geometry": {
                            "type": "Point",
                            "coordinates": [
                                -74.9334345,
                                39.918751
                            ]
                        },
                        "properties": {
                            "description": "Pump unclogging",
                            "address": "Church Rd E"
                        }
                    }
                ]
            }';
            */

            // Convert JSON to PHP array
            $locations = json_decode($body, true);

            // Extract features array from the GeoJSON FeatureCollection
            $features = $locations['features'];


            // Start building HTML output
            $output = '<div id="heatmap" style="height: 500px; width: 100%;"></div>';

            $output .= '<script>
            function initHeatMap() {
                const map = new google.maps.Map(document.getElementById("heatmap"), {
                    zoom: 13,
                    center: {lat: ' . $features[0]['geometry']['coordinates'][1] . ', lng: ' . $features[0]['geometry']['coordinates'][0] . '},
                    mapTypeId: "roadmap"
                });

                const heatmapData = [' . 
                    implode(',', array_map(function($point) {
                        return 'new google.maps.LatLng(' . $point['geometry']['coordinates'][1] . ',' . $point['geometry']['coordinates'][0] . ')';
                    }, $features)) . 
                '];

                new google.maps.visualization.HeatmapLayer({
                    data: heatmapData,
                    map: map
                });
            }
            window.addEventListener(\'load\', initHeatMap);
            </script>';

            return $output;
        }
    }

}
