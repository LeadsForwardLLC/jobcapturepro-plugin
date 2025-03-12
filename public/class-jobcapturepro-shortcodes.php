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
     * Shortcode to display a map
     */
    public function get_map($atts)
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

            // Get the API Key from the plugin options
            $options = get_option('jobcapturepro_options');
            $mapsApikey = trim($options['jobcapturepro_field_gmaps_apikey']);

            // Ensure necessary scripts are loaded
            wp_enqueue_script('google-maps', 'https://maps.googleapis.com/maps/api/js?libraries=visualization&key=' . $mapsApikey, array(), null, array('strategy' => 'async'));

            // Assume the response body is a JSON array of locations as defined by geopoints in RFC 7946

            // Convert JSON to PHP array
            $locations = json_decode($body, true);

            // Extract features array from the GeoJSON FeatureCollection
            $features = $locations['features'];

            // Calculate center point of 80% of checkins
            $totalPoints = count($features);
            $pointsToUse = (int) ($totalPoints * 0.8);

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
                implode(',', array_map(function ($point) {
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
