<?php

// Prevent direct access.
if (! defined('ABSPATH')) {
    exit;
}

?>

<section class="jcp-plugin-slider-section jcp:pb-4 jcp:mt-6 jcp:font-['Inter','SF_Pro_Text',-apple-system,BlinkMacSystemFont,'Segoe_UI',sans-serif]" aria-label="Project check-ins">
    <div class="jcp-plugin-slider jcp:relative jcp:px-14">

        <button type="button" class="jcp-plugin-slider__btn jcp-plugin-slider__btn--prev jcp:left-0" id="jcp-plugin-slider-prev" aria-label="Previous check-ins">
            <?php echo jcp_icon('chevron-left', 24, 'jcp:block'); ?>
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
            <?php echo jcp_icon('chevron-right', 24, 'jcp:block'); ?>
        </button>

    </div>
</section>
