<?php

// Prevent direct access.
if (! defined('ABSPATH')) {
    exit;
}

?>

<section class="jcp-plugin-slider-section jcp:pb-4 jcp:mt-6 jcp:font-['Inter','SF_Pro_Text',-apple-system,BlinkMacSystemFont,'Segoe_UI',sans-serif]" aria-label="Project check-ins">
    <div class="jcp-plugin-slider jcp:relative jcp:px-10 jcp:md:px-14">

        <div class="swiper">
            <div class="swiper-wrapper" id="jcp-plugin-slider-track">
                <?php foreach ($checkins as $checkin) :
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- plugin-controlled template output
                    echo JobCapturePro_Template::render_template('checkin-card', [
                        'checkin' => $checkin,
                    ]);
                endforeach; ?>
            </div>
        </div>

        <button type="button" class="jcp-plugin-slider__btn jcp-plugin-slider__btn--prev jcp:left-0" id="jcp-plugin-slider-prev" aria-label="Previous check-ins">
            <?php echo jcp_icon('chevron-left', 18, 'jcp:block jcp:md:hidden'); ?>
            <?php echo jcp_icon('chevron-left', 24, 'jcp:hidden jcp:md:block'); ?>
        </button>

        <button type="button" class="jcp-plugin-slider__btn jcp-plugin-slider__btn--next jcp:right-0" id="jcp-plugin-slider-next" aria-label="Next check-ins">
            <?php echo jcp_icon('chevron-right', 18, 'jcp:block jcp:md:hidden'); ?>
            <?php echo jcp_icon('chevron-right', 24, 'jcp:hidden jcp:md:block'); ?>
        </button>

    </div>
</section>
