<?php

// Prevent direct access.
if (! defined('ABSPATH')) {
    exit;
}

?>

<section class="jcp-plugin-slider-section jcp:pb-4 jcp:mt-6 jcp:font-['Inter','SF_Pro_Text',-apple-system,BlinkMacSystemFont,'Segoe_UI',sans-serif]" aria-label="Project check-ins">
    <div class="jcp-plugin-slider jcp:relative jcp:px-14">

        <button type="button" class="jcp-plugin-slider__btn jcp-plugin-slider__btn--prev jcp:left-0" id="jcp-plugin-slider-prev" aria-label="Previous check-ins">
            <svg class="jcp:block" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M15 18l-6-6 6-6"/>
            </svg>
        </button>

        <div class="jcp-plugin-slider__viewport jcp:w-full jcp:min-w-0 jcp:overflow-hidden">
            <div class="jcp-plugin-slider__track jcp:flex jcp:gap-6 jcp:transition-transform jcp:duration-350 jcp:ease-[ease] jcp:will-change-transform" id="jcp-plugin-slider-track">
                <?php foreach ($checkins as $checkin) :
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- plugin-controlled template output
                    echo JobCapturePro_Template::render_template('checkin-card', [
                        'checkin' => $checkin,
                    ]);
                endforeach; ?>
            </div>
        </div>

        <button type="button" class="jcp-plugin-slider__btn jcp-plugin-slider__btn--next jcp:right-0" id="jcp-plugin-slider-next" aria-label="Next check-ins">
            <svg class="jcp:block" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M9 18l6-6-6-6"/>
            </svg>
        </button>

    </div>
</section>
