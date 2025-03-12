<?php

/**
 * This file defines the template generation functionality for the plugin.
 */
class JobCaptureProTemplates
{
    /**
     * Generate HTML for a single checkin card
     * 
     * @param array $checkin The checkin data
     * @return string HTML for a single checkin card
     */
    public static function render_checkin_card($checkin)
    {

        // Check for required fields
        if (empty($checkin['description']) || empty($checkin['address']) || empty($checkin['createdAt'])) {
            return '';
        }
        
        $output = '<div class="jcp-checkin-card">';

        // Images (if available)
        if (!empty($checkin['imageUrls']) && is_array($checkin['imageUrls'])) {
            $output .= self::render_images_gallery($checkin['imageUrls']);
        }

        // User info (if available)
        if (!empty($checkin['assignedUser'])) {
            $output .= '<div class="jcp-checkin-user">';
            
            // Profile image
            if (!empty($checkin['assignedUser']['profileImageUrl'])) {
                $output .= '<div class="jcp-user-image">
                    <img src="' . esc_url($checkin['assignedUser']['profileImageUrl']) . '" alt="User profile">
                </div>';
            }
            
            // User name
            if (!empty($checkin['assignedUser']['name'])) {
                $output .= '<div class="jcp-user-name">
                    <p>' . esc_html($checkin['assignedUser']['name']) . '</p>
                </div>';
            }
            
            $output .= '</div>';
        }

        // Description
        $output .= '<div class="jcp-checkin-description">
            <p>' . esc_html($checkin['description']) . '</p>
        </div>';

        // Date
        $output .= '<div class="jcp-checkin-date">';
        $timestamp = $checkin['createdAt'];
        $current_time = time();
        $time_diff = $current_time - $timestamp;
        $three_months = 3 * 30 * 24 * 60 * 60; // Approximate 3 months in seconds

        if ($time_diff < $three_months) {
            // Relative time for dates within 3 months
            if ($time_diff < 60 * 60) {
                // Less than an hour ago
                $relative_time = '1 hour ago';
            } elseif ($time_diff < 24 * 60 * 60) {
                // Hours ago
                $hours = floor($time_diff / (60 * 60));
                $relative_time = $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
            } elseif ($time_diff < 30 * 24 * 60 * 60) {
                // Days ago
                $days = floor($time_diff / (24 * 60 * 60));
                $relative_time = $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
            } else {
                // Months ago
                $months = floor($time_diff / (30 * 24 * 60 * 60));
                $relative_time = $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
            }
            $output .= '<p>' . esc_html($relative_time) . '</p>';
        } else {
            // Standard date format for older dates
            $output .= '<p>' . esc_html(date('F j, Y', $timestamp)) . '</p>';
        }
        $output .= '</div>';

        // Address
        $output .= '<div class="jcp-checkin-address">';
        $output .= '<p><strong>Near</strong> ' . esc_html($checkin['address']);
        $output .= '</p></div>';

        $output .= '</div>'; // Close card
        return $output;
    }

    /**
     * Generate HTML for the checkins grid layout with transposed order
     * 
     * @param array $checkins Array of checkin data
     * @return string HTML for all checkins in a responsive grid
     */
    public static function render_checkins_grid($checkins)
    {
        // Container with CSS Grid for responsive layout
        $output = '<div class="jcp-container">';

        // Add CSS for modern responsive grid
        $output .= self::get_checkins_grid_styles();

        // Grid container
        $output .= '<div class="jcp-checkins-grid">';

        // Calculate number of columns for transposition
        $columnCount = 4; // Default column count
        $totalCheckins = count($checkins);
        $rowCount = ceil($totalCheckins / $columnCount);

        // Create a transposed array
        $transposedOrder = [];

        for ($col = 0; $col < $columnCount; $col++) {
            for ($row = 0; $row < $rowCount; $row++) {
                $index = $row * $columnCount + $col;
                if ($index < $totalCheckins) {
                    $transposedOrder[] = $checkins[$index];
                }
            }
        }

        // Add each checkin to the grid in transposed order
        foreach ($transposedOrder as $checkin) {
            $output .= self::render_checkin_card($checkin);
        }

        $output .= '</div>'; // Close grid
        $output .= '</div>'; // Close container

        return $output;
    }

    /**
     * Generate CSS styles for the checkins grid
     * 
     * @return string CSS styles for the checkins grid
     */
    private static function get_checkins_grid_styles()
    {
        return '<style>
            .jcp-container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 20px;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            }
            
            .jcp-checkins-grid {
                /* Keep masonry-style layout with CSS columns */
                column-count: 4;
                column-gap: 20px;
                width: 100%;
            }
            
            .jcp-checkin-card {
                break-inside: avoid;
                margin-bottom: 20px;
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                overflow: hidden;
                transition: transform 0.2s ease, box-shadow 0.2s ease;
                display: block;
            }
            
            .jcp-checkin-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            }
            
            /* User info styles */
            .jcp-checkin-user {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 10px 15px;
                border-bottom: 1px solid #f0f0f0;
            }
            
            .jcp-user-image {
                flex: 0 0 40px;
                height: 40px;
                border-radius: 50%;
                overflow: hidden;
            }
            
            .jcp-user-image img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
            }
            
            .jcp-user-name {
                flex-grow: 1;
                text-align: right;
            }
            
            .jcp-user-name p {
                margin: 0;
                font-size: 1.05em;
                font-weight: 600;
                color: #333;
            }
            
            /* Image gallery styles */
            .jcp-checkin-image {
                position: relative;
                width: 100%;
                height: 200px;
                overflow: hidden;
            }
            
            .gallery-image {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                opacity: 0;
                transition: opacity 0.3s ease;
                display: none;
            }
            
            .gallery-image.active {
                opacity: 1;
                display: block;
            }
            
            .gallery-image img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
            }
            
            .gallery-nav {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                width: 40px;
                height: 40px;
                background-color: rgba(255, 255, 255, 0.5);
                color: rgba(0, 0, 0, 0.6);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                font-size: 18px;
                font-weight: bold;
                z-index: 2;
                transition: background-color 0.3s ease, color 0.3s ease;
            }
            
            .gallery-nav:hover {
                background-color: rgba(255, 255, 255, 0.8);
                color: rgba(0, 0, 0, 0.8);
            }
            
            .gallery-prev {
                left: 10px;
            }
            
            .gallery-next {
                right: 10px;
            }
            
            .gallery-dots {
                position: absolute;
                bottom: 10px;
                left: 50%;
                transform: translateX(-50%);
                display: flex;
                gap: 8px;
                z-index: 2;
            }
            
            .gallery-dot {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background-color: rgba(255, 255, 255, 0.5);
                cursor: pointer;
                transition: background-color 0.3s ease;
            }
            
            .gallery-dot.active,
            .gallery-dot:hover {
                background-color: rgba(255, 255, 255, 0.9);
            }
            
            .jcp-checkin-description,
            .jcp-checkin-date,
            .jcp-checkin-address {
                padding: 10px 15px;
            }
            
            .jcp-checkin-date {
                font-size: 0.9em;
                color: #666;
            }
            
            .jcp-checkin-address {
                font-size: 0.85em;
                background-color: #f8f8f8;
                border-top: 1px solid #eee;
            }
            
            /* Responsive design */
            @media (max-width: 1024px) {
                .jcp-checkins-grid {
                    column-count: 3;
                }
            }
            
            @media (max-width: 768px) {
                .jcp-checkins-grid {
                    column-count: 2;
                }
            }
            
            @media (max-width: 480px) {
                .jcp-checkins-grid {
                    column-count: 1;
                }
            }
        </style>';
    }

    /**
     * Generate HTML for the address section
     */
    private static function render_address($address)
    {
        $output = '<div class="address" style="background: #f5f5f5; padding: 10px; border-radius: 4px; margin-bottom: 15px;">';
        $output .= '<p style="margin: 0;">' . esc_html($address['addressLine1']) . '<br>';
        if (isset($address['city']) && isset($address['region']) && isset($address['postalCode'])) {
            $output .= esc_html($address['city']) . ', ' . esc_html($address['region']) . ' ' . esc_html($address['postalCode']) . '<br>';
        }
        if (isset($address['countryCode'])) {
            $output .= esc_html($address['countryCode']);
        }
        $output .= '</p>';
        $output .= '</div>';
        return $output;
    }

    /**
     * Generate HTML for the images grid
     */
    private static function render_images_grid($imageUrls)
    {
        if (empty($imageUrls)) {
            return '';
        }

        $output = '<div class="images-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; margin-top: 15px;">';
        foreach ($imageUrls as $imageUrl) {
            $output .= '<img src="' . esc_url($imageUrl) . '" style="width: 100%; height: 200px; object-fit: cover; border-radius: 4px;">';
        }
        $output .= '</div>';
        return $output;
    }

    /**
     * Generate HTML for the images gallery with navigation arrows
     * 
     * @param array $imageUrls Array of image URLs
     * @return string HTML for the image gallery
     */
    private static function render_images_gallery($imageUrls)
    {
        if (empty($imageUrls)) {
            return '';
        }

        $imageCount = count($imageUrls);
        $showArrows = $imageCount > 1;
        $galleryId = 'gallery-' . wp_rand(); // Create unique ID for this gallery
        
        $output = '<div class="jcp-checkin-image" id="' . $galleryId . '">';
        
        // Add all images but only first is visible initially
        foreach ($imageUrls as $index => $imageUrl) {
            $activeClass = $index === 0 ? ' active' : '';
            $output .= '<div class="gallery-image' . $activeClass . '" data-index="' . $index . '">
                <img src="' . esc_url($imageUrl) . '" alt="Checkin image ' . ($index + 1) . '">
            </div>';
        }
        
        // Add navigation arrows if there are multiple images
        if ($showArrows) {
            $output .= '<div class="gallery-nav gallery-prev" onclick="changeImage(\'' . $galleryId . '\', \'prev\')">&#10094;</div>';
            $output .= '<div class="gallery-nav gallery-next" onclick="changeImage(\'' . $galleryId . '\', \'next\')">&#10095;</div>';
            $output .= '<div class="gallery-dots">';
            
            // Add indicator dots
            for ($i = 0; $i < $imageCount; $i++) {
                $activeClass = $i === 0 ? ' active' : '';
                $output .= '<span class="gallery-dot' . $activeClass . '" onclick="showImage(\'' . $galleryId . '\', ' . $i . ')"></span>';
            }
            
            $output .= '</div>';
        }
        
        $output .= '</div>';
        
        // Add JavaScript for gallery functionality if there are multiple images
        if ($showArrows) {
            $output .= self::get_gallery_script();
        }
        
        return $output;
    }

    /**
     * Generate JavaScript for image gallery functionality
     * 
     * @return string JavaScript for gallery functionality
     */
    private static function get_gallery_script()
    {
        // Check if script has already been added to avoid duplication
        static $scriptAdded = false;
        
        if ($scriptAdded) {
            return '';
        }
        
        $scriptAdded = true;
        
        return '<script>
            function changeImage(galleryId, direction) {
                const gallery = document.getElementById(galleryId);
                const images = gallery.querySelectorAll(".gallery-image");
                const dots = gallery.querySelectorAll(".gallery-dot");
                
                // Find current active image
                let currentIndex = 0;
                for (let i = 0; i < images.length; i++) {
                    if (images[i].classList.contains("active")) {
                        currentIndex = i;
                        break;
                    }
                }
                
                // Remove active class from current image and dot
                images[currentIndex].classList.remove("active");
                if (dots.length) dots[currentIndex].classList.remove("active");
                
                // Calculate new index
                let newIndex;
                if (direction === "next") {
                    newIndex = (currentIndex + 1) % images.length;
                } else {
                    newIndex = (currentIndex - 1 + images.length) % images.length;
                }
                
                // Add active class to new image and dot
                images[newIndex].classList.add("active");
                if (dots.length) dots[newIndex].classList.add("active");
            }
            
            function showImage(galleryId, index) {
                const gallery = document.getElementById(galleryId);
                const images = gallery.querySelectorAll(".gallery-image");
                const dots = gallery.querySelectorAll(".gallery-dot");
                
                // Remove active class from all images and dots
                for (let i = 0; i < images.length; i++) {
                    images[i].classList.remove("active");
                    if (dots.length) dots[i].classList.remove("active");
                }
                
                // Add active class to selected image and dot
                images[index].classList.add("active");
                if (dots.length) dots[index].classList.add("active");
            }
        </script>';
    }
}