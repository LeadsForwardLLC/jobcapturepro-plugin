<?php

/**
 * This file defines the Nearby Checkins Widget functionality for the plugin.
 */

class JobCaptureProNearbyCheckinsWidget extends WP_Widget
{
    /**
     * The base URL for our backend API.
     */
    private $jcp_api_base_url;

    /**
     * Initialize the widget
     */
    public function __construct()
    {
        parent::__construct(
            'jobcapturepro_nearby_checkins_widget',
            __('JobCapturePro Nearby Checkins Widget', 'jobcapturepro'),
            array(
                'description' => __('Display nearby checkins based on the city of the current checkin', 'jobcapturepro'),
                'customize_selective_refresh' => true,
            )
        );
        
        // Set the API base URL
        $this->jcp_api_base_url = 'https://jcp-api--travel-app-eor5yc.us-central1.hosted.app/api/';
    }

    /**
     * Front-end display of widget
     */
    public function widget($args, $instance)
    {
        // Get widget settings
        $title = apply_filters('widget_title', $instance['title']);
        $limit = !empty($instance['limit']) ? (int)$instance['limit'] : 10;
        $exclude_current = !empty($instance['exclude_current']) ? (bool)$instance['exclude_current'] : true;

        // Check for checkin ID in URL parameter
        $checkin_id = isset($_GET['checkinId']) ? sanitize_text_field($_GET['checkinId']) : null;

        if (!$checkin_id) {
            // No checkin ID provided, don't show the widget
            return;
        }

        // Get the city from the current checkin
        $city = $this->get_checkin_city($checkin_id);
        
        if (!$city) {
            // Could not determine city, don't show widget
            return;
        }

        // Fetch nearby checkins data
        $checkins = $this->fetch_nearby_checkins($city, $limit, $exclude_current ? $checkin_id : null);

        echo $args['before_widget'];

        if (!empty($title)) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

        // Display nearby checkins or no checkins message
        if ($checkins && !empty($checkins)) {
            echo JobCaptureProTemplates::render_nearby_checkins($checkins, $city);
        } else {
            echo '<div class="jcp-no-nearby-checkins">';
            echo '<p>' . __('No nearby checkins found.', 'jobcapturepro') . '</p>';
            echo '</div>';
        }

        echo $args['after_widget'];
    }

    /**
     * Back-end widget form
     */
    public function form($instance)
    {
        $title = !empty($instance['title']) ? $instance['title'] : __('Nearby Checkins', 'jobcapturepro');
        $limit = !empty($instance['limit']) ? $instance['limit'] : 10;
        $exclude_current = !empty($instance['exclude_current']) ? $instance['exclude_current'] : true;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('limit')); ?>"><?php _e('Number of checkins to display:'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('limit')); ?>" name="<?php echo esc_attr($this->get_field_name('limit')); ?>" type="number" min="1" max="50" value="<?php echo esc_attr($limit); ?>">
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($exclude_current); ?> id="<?php echo esc_attr($this->get_field_id('exclude_current')); ?>" name="<?php echo esc_attr($this->get_field_name('exclude_current')); ?>">
            <label for="<?php echo esc_attr($this->get_field_id('exclude_current')); ?>"><?php _e('Exclude current checkin from results', 'jobcapturepro'); ?></label>
        </p>
        <?php
    }

    /**
     * Sanitize widget form values as they are saved
     */
    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['limit'] = (!empty($new_instance['limit'])) ? (int)$new_instance['limit'] : 10;
        $instance['exclude_current'] = (!empty($new_instance['exclude_current'])) ? (bool)$new_instance['exclude_current'] : false;

        return $instance;
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
