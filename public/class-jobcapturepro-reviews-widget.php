<?php

/**
 * This file defines the Reviews Widget functionality for the plugin.
 */

class JobCaptureProReviewsWidget extends WP_Widget
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
            'jobcapturepro_reviews_widget',
            __('JobCapturePro Reviews Widget', 'jobcapturepro'),
            array(
                'description' => __('Display recent reviews for a company', 'jobcapturepro'),
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
        $company_id = !empty($instance['company_id']) ? $instance['company_id'] : null;
        $limit = !empty($instance['limit']) ? (int)$instance['limit'] : 5;

        // Check for checkin ID in URL parameter
        $checkin_id = isset($_GET['checkinId']) ? sanitize_text_field($_GET['checkinId']) : null;

        // Fetch reviews data
        $reviews = $this->fetch_reviews_data($company_id, $checkin_id, $limit);

        echo $args['before_widget'];

        if (!empty($title)) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

        // Display reviews or no reviews message
        if ($reviews && !empty($reviews)) {
            echo $this->render_reviews($reviews);
        } else {
            echo '<div class="jcp-no-reviews">';
            echo '<p>' . __('There are no recent reviews.', 'jobcapturepro') . '</p>';
            echo '</div>';
        }

        echo $args['after_widget'];
    }

    /**
     * Back-end widget form
     */
    public function form($instance)
    {
        $title = !empty($instance['title']) ? $instance['title'] : __('Recent Reviews', 'jobcapturepro');
        $company_id = !empty($instance['company_id']) ? $instance['company_id'] : '';
        $limit = !empty($instance['limit']) ? $instance['limit'] : 5;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('company_id')); ?>"><?php _e('Company ID (optional):'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('company_id')); ?>" name="<?php echo esc_attr($this->get_field_name('company_id')); ?>" type="text" value="<?php echo esc_attr($company_id); ?>">
            <small><?php _e('Leave empty to use URL parameter companyId', 'jobcapturepro'); ?></small>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('limit')); ?>"><?php _e('Number of reviews to display:'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('limit')); ?>" name="<?php echo esc_attr($this->get_field_name('limit')); ?>" type="number" min="1" max="20" value="<?php echo esc_attr($limit); ?>">
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
        $instance['company_id'] = (!empty($new_instance['company_id'])) ? sanitize_text_field($new_instance['company_id']) : '';
        $instance['limit'] = (!empty($new_instance['limit'])) ? (int)$new_instance['limit'] : 5;

        return $instance;
    }

    /**
     * Fetch reviews data from API
     */
    private function fetch_reviews_data($company_id = null, $checkin_id = null, $limit = 5)
    {
        // Check for company ID in URL parameter if not provided
        if (!$company_id && isset($_GET['companyId'])) {
            $company_id = sanitize_text_field($_GET['companyId']);
        }

        // Get the API Key from the plugin options
        $options = get_option('jobcapturepro_options');
        $apikey = trim($options['jobcapturepro_field_apikey']);
        
        if (empty($apikey)) {
            return null;
        }

        $url = $this->jcp_api_base_url . 'reviews';

        // Build query parameters
        $query_params = array();
        
        if ($company_id) {
            $query_params[] = "companyId=" . urlencode($company_id);
        }
        
        if ($checkin_id) {
            $query_params[] = "checkinId=" . urlencode($checkin_id);
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
        $response = json_decode($body, true);

        return $response;
    }

    /**
     * Render reviews HTML
     */
    private function render_reviews($reviews)
    {
        $output = '<div class="jcp-reviews-widget">';
        $output .= $this->get_reviews_styles();
        
        if (is_array($reviews) && !empty($reviews)) {
            $output .= '<div class="jcp-reviews-list">';
            
            foreach ($reviews as $review) {
                $output .= $this->render_single_review($review);
            }
            
            $output .= '</div>';
        }
        
        $output .= '</div>';
        
        return $output;
    }

    /**
     * Render a single review
     */
    private function render_single_review($review)
    {
        $output = '<div class="jcp-review-item">';
        
        // Review header with rating and author
        $output .= '<div class="jcp-review-header">';
        
        // Rating stars
        if (isset($review['rating']) && is_numeric($review['rating'])) {
            $rating = (float)$review['rating'];
            $output .= '<div class="jcp-review-rating">';
            $output .= $this->render_star_rating($rating);
            $output .= '</div>';
        }
        
        // Author name
        if (isset($review['author_name'])) {
            $output .= '<div class="jcp-review-author">';
            $output .= '<strong>' . esc_html($review['author_name']) . '</strong>';
            $output .= '</div>';
        }
        
        $output .= '</div>';
        
        // Review text
        if (isset($review['text']) && !empty($review['text'])) {
            $output .= '<div class="jcp-review-text">';
            $output .= '<p>' . esc_html($review['text']) . '</p>';
            $output .= '</div>';
        }
        
        // Review date
        if (isset($review['time'])) {
            $output .= '<div class="jcp-review-date">';
            $date = is_numeric($review['time']) ? date('M j, Y', $review['time']) : esc_html($review['time']);
            $output .= '<small>' . $date . '</small>';
            $output .= '</div>';
        }
        
        $output .= '</div>';
        
        return $output;
    }

    /**
     * Render star rating
     */
    private function render_star_rating($rating)
    {
        $output = '<div class="jcp-stars">';
        
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $rating) {
                $output .= '<span class="jcp-star jcp-star-filled">★</span>';
            } elseif ($i - 0.5 <= $rating) {
                $output .= '<span class="jcp-star jcp-star-half">☆</span>';
            } else {
                $output .= '<span class="jcp-star jcp-star-empty">☆</span>';
            }
        }
        
        $output .= '</div>';
        
        return $output;
    }

    /**
     * Get CSS styles for reviews
     */
    private function get_reviews_styles()
    {
        return '<style>
            .jcp-reviews-widget {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            }
            
            .jcp-reviews-list {
                display: flex;
                flex-direction: column;
                gap: 15px;
            }
            
            .jcp-review-item {
                background: #f9f9f9;
                padding: 15px;
                border-radius: 8px;
                border-left: 4px solid #0073aa;
            }
            
            .jcp-review-header {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 10px;
            }
            
            .jcp-review-rating {
                flex-shrink: 0;
            }
            
            .jcp-review-author {
                flex-grow: 1;
            }
            
            .jcp-stars {
                display: flex;
                gap: 2px;
            }
            
            .jcp-star {
                font-size: 16px;
                line-height: 1;
            }
            
            .jcp-star-filled {
                color: #ffa500;
            }
            
            .jcp-star-half {
                color: #ffa500;
            }
            
            .jcp-star-empty {
                color: #ddd;
            }
            
            .jcp-review-text {
                margin-bottom: 10px;
            }
            
            .jcp-review-text p {
                margin: 0;
                color: #333;
                line-height: 1.5;
            }
            
            .jcp-review-date {
                color: #666;
                font-size: 12px;
            }
            
            .jcp-no-reviews {
                text-align: center;
                padding: 20px;
                color: #666;
            }
            
            .jcp-no-reviews p {
                margin: 0;
                font-style: italic;
            }
        </style>';
    }
}
