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

            // Start building the HTML output with modern styling
            $output = '<div class="checkin-container" style="max-width: 1200px; margin: 0 auto; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Oxygen-Sans, Ubuntu, Cantarell, \'Helvetica Neue\', sans-serif;">';

            // Validate required fields
            if (isset($checkin['company']) && isset($checkin['createdAt']) && 
            isset($checkin['address']) && is_array($checkin['address'])) {

            $address = $checkin['address'];
            if (isset($address['addressLine1']) && isset($address['city']) && 
                isset($address['region']) && isset($address['postalCode']) && 
                isset($address['countryCode'])) {

                $output .= '<div class="checkin-card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin: 20px 0; padding: 20px;">';
                
                // Company and Date Header
                $date = date('F j, Y', strtotime($checkin['createdAt']));
                $output .= '<h3 style="margin: 0 0 10px; color: #1a1a1a;">' . esc_html($checkin['company']) . '</h3>';
                $output .= '<p style="color: #666; margin: 0 0 15px;">' . esc_html($date) . '</p>';
                
                // Description
                if (!empty($checkin['description'])) {
                $output .= '<p style="margin: 0 0 15px;">' . esc_html($checkin['description']) . '</p>';
                }
                
                // Address
                $output .= '<div class="address" style="background: #f5f5f5; padding: 10px; border-radius: 4px; margin-bottom: 15px;">';
                $output .= '<p style="margin: 0;">' . esc_html($address['addressLine1']) . '<br>';
                $output .= esc_html($address['city']) . ', ' . esc_html($address['region']) . ' ' . esc_html($address['postalCode']) . '<br>';
                $output .= esc_html($address['countryCode']) . '</p>';
                $output .= '</div>';
                
                // Images
                if (!empty($checkin['imageUrls'])) {
                $output .= '<div class="images-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; margin-top: 15px;">';
                foreach ($checkin['imageUrls'] as $imageUrl) {
                    $output .= '<img src="' . esc_url($imageUrl) . '" style="width: 100%; height: 200px; object-fit: cover; border-radius: 4px;">';
                }
                $output .= '</div>';
                }
                
                $output .= '</div>';
            }
            }

            $output .= '</div>';
            return $output;
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
        $body = wp_remote_retrieve_body( $request );

        if( is_wp_error( $request ) ) {
            return;
        } else {
            // Decode the JSON response
            $checkins = json_decode($body, true);
    
            // Start building the HTML output with modern styling
            $output = '<div class="checkins-container" style="max-width: 1200px; margin: 0 auto; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Oxygen-Sans, Ubuntu, Cantarell, \'Helvetica Neue\', sans-serif;">';

            // Log checkins to browser console for debugging
            $output .= '<script>console.log(' . json_encode($checkins) . ');</script>';

            // Create table header
            $output .= '<table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <thead>
                <tr style="background-color: #f5f5f5;">
                    <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Image</th>
                    <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Description</th>
                    <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Date</th>
                    <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Address</th>
                </tr>
            </thead>
            <tbody>';

            foreach ($checkins as $checkin) {
                $image_cell = '';
                if (!empty($checkin['imageUrls']) && is_array($checkin['imageUrls'])) {
                    $image_cell = '<img src="' . esc_url($checkin['imageUrls'][0]) . '" style="width: 100px; height: 100px; object-fit: cover;">';
                }

                // Add table row for each checkin
                $output .= '<tr>
                    <td style="padding: 12px; border: 1px solid #ddd;">' . $image_cell . '</td>
                    <td style="padding: 12px; border: 1px solid #ddd;">' . esc_html($checkin['description']) . '</td>
                    <td style="padding: 12px; border: 1px solid #ddd;">' . esc_html(date('F j, Y', $checkin['createdAt']['_seconds'])) . '</td>
                    <td style="padding: 12px; border: 1px solid #ddd;">' . esc_html($checkin['address']['addressLine1']) . '</td>
                </tr>';
            }

            // Close the table
            $output .= '</tbody></table>';
    
            return $output;    
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

            // Convert JSON to PHP array
            $locations = json_decode($body, true);

            // Extract features array from the GeoJSON FeatureCollection
            $features = $locations['features'];
            
            // Calculate center point of 80% of checkins
            $totalPoints = count($features);
            $pointsToUse = (int)($totalPoints * 0.8);
            
            // Sort points by distance from mean center to get the central 80%
            if ($totalPoints > 0) {
                // First calculate the mean center
                $sumLat = 0;
                $sumLng = 0;
                foreach ($features as $feature) {
                    $sumLat += $feature['geometry']['coordinates'][1];
                    $sumLng += $feature['geometry']['coordinates'][0];
                }
                $meanLat = $sumLat / $totalPoints;
                $meanLng = $sumLng / $totalPoints;
                
                // Calculate distance of each point from mean
                $distanceFromMean = [];
                foreach ($features as $index => $feature) {
                    $lat = $feature['geometry']['coordinates'][1];
                    $lng = $feature['geometry']['coordinates'][0];
                    $distance = sqrt(pow($lat - $meanLat, 2) + pow($lng - $meanLng, 2));
                    $distanceFromMean[$index] = $distance;
                }
                
                // Sort points by distance
                asort($distanceFromMean);
                
                // Keep only the closest 80%
                $centralPoints = array_slice($distanceFromMean, 0, $pointsToUse, true);
                
                // Find bounds of these central points
                $minLat = $maxLat = $features[array_key_first($centralPoints)]['geometry']['coordinates'][1];
                $minLng = $maxLng = $features[array_key_first($centralPoints)]['geometry']['coordinates'][0];
                
                foreach ($centralPoints as $index => $distance) {
                    $lat = $features[$index]['geometry']['coordinates'][1];
                    $lng = $features[$index]['geometry']['coordinates'][0];
                    $minLat = min($minLat, $lat);
                    $maxLat = max($maxLat, $lat);
                    $minLng = min($minLng, $lng);
                    $maxLng = max($maxLng, $lng);
                }
                
                // Calculate center of the 80% points
                $centerLat = ($minLat + $maxLat) / 2;
                $centerLng = ($minLng + $maxLng) / 2;
            } else {
                // Default center if no points
                $centerLat = 0;
                $centerLng = 0;
                $minLat = $maxLat = 0;
                $minLng = $maxLng = 0;
            }

            // Start building HTML output
            $output = '<div id="heatmap" style="height: 500px; width: 100%;"></div>';

            $output .= '<script>
            function initHeatMap() {
                const map = new google.maps.Map(document.getElementById("heatmap"), {
                    mapTypeId: "roadmap"
                });
                
                // Define bounds for the map
                const bounds = new google.maps.LatLngBounds(
                    new google.maps.LatLng(' . $minLat . ', ' . $minLng . '),
                    new google.maps.LatLng(' . $maxLat . ', ' . $maxLng . ')
                );
                
                // Fit the map to these bounds
                map.fitBounds(bounds);

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
