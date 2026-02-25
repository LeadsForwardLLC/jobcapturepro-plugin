<?php

// Prevent direct access.
if (! defined('ABSPATH')) {
    exit;
}

?>

<section class="jcp-plugin-slider-section" aria-label="Project check-ins">
    <div class="jcp-plugin-slider">

        <button type="button" class="jcp-plugin-slider__btn jcp-plugin-slider__btn--prev" id="jcp-plugin-slider-prev" aria-label="Previous check-ins">
            <svg class="jcp-plugin-slider__chevron" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M15 18l-6-6 6-6"/>
            </svg>
        </button>

        <div class="jcp-plugin-slider__viewport">
            <div class="jcp-plugin-slider__track" id="jcp-plugin-slider-track">
                <?php foreach ($checkins as $checkin) :
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- plugin-controlled template output
                    echo JobCapturePro_Template::render_template('checkin-card', [
                        'checkin' => $checkin,
                    ]);
                endforeach; ?>
            </div>
        </div>

        <button type="button" class="jcp-plugin-slider__btn jcp-plugin-slider__btn--next" id="jcp-plugin-slider-next" aria-label="Next check-ins">
            <svg class="jcp-plugin-slider__chevron" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M9 18l6-6-6-6"/>
            </svg>
        </button>

    </div>
</section>
