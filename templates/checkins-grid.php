<?php

// Prevent direct access.
if (! defined('ABSPATH')) {
    exit;
}

?>


<!-- Checkins Grid -->
<div class="jobcapturepro-container jcp:mt-8 jcp:mx-auto">
    <div id="checkins-grid" class="jobcapturepro-checkins-grid <?php echo esc_attr($gridId); ?>" data-column-count="3">
        <?php foreach ($checkins as $checkin): ?>
            <?php echo wp_kses_post(JobCapturePro_Template::render_template('checkin-card', [
                'checkin' => $checkin,
            ]));
            ?>
        <?php endforeach; ?>
    </div>

    <div class="jcp:flex jcp:justify-center jcp:mt-6">
        <button id="load-more-checkins-btn" class="jcp:flex jcp:items-center jcp:gap-3 jcp:text-center jcp:no-underline jcp:bg-accent jcp:text-white jcp:font-bold jcp:px-6 jcp:py-3 jcp:text-base jcp:rounded-full jcp:transition-colors jcp:duration-200 jcp:ease-in-out jcp:hover:bg-red-600 jcp:cursor-pointer jcp:border-0">
            <span>Load More</span>
            <svg class="jcp:hidden jcp:animate-spin jcp:h-4 jcp:w-4 jcp:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="jcp:opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="jcp:opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </button>
    </div>
</div>