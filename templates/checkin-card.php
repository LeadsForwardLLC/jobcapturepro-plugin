<?php

// Prevent direct access.
if (! defined('ABSPATH')) {
    exit;
}

$address_parts = explode(',', $checkin['address'] ?? '');
$city          = trim($address_parts[1] ?? '');
$state         = trim($address_parts[2] ?? '');
$state_abbr    = strlen($state) > 2 ? substr($state, 0, 2) : $state;
$timestamp     = strtotime($checkin['jobCompletedDate'] ?? $checkin['createdAt'] ?? '');
$image_urls    = $checkin['imageUrls'] ?? [];
$has_images    = ! empty($image_urls) && is_array($image_urls);
$has_gallery   = $has_images && count($image_urls) > 1;

?>

<article class="jcp-plugin-card jcp:group jcp:bg-white jcp:rounded-xl jcp:overflow-hidden jcp:transition-transform jcp:duration-200 jcp:hover:-translate-y-0.5">

    <?php if ($has_images) : ?>
    <div class="jcp-plugin-card__gallery"<?php if ($has_gallery) : ?> data-carousel<?php endif; ?>>
        <div class="jcp-plugin-card__gallery-inner">
            <?php foreach ($image_urls as $i => $url) : ?>
                <div class="jcp-plugin-card__slide <?php echo $i === 0 ? 'jcp:opacity-100 jcp:visible' : 'jcp:opacity-0 jcp:invisible'; ?>">
                    <img src="<?php echo esc_url($url); ?>"
                         alt=""
                         width="400"
                         height="260"
                         loading="<?php echo $i === 0 ? 'eager' : 'lazy'; ?>">
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($has_gallery) : ?>
            <button type="button" class="jcp-plugin-card__nav jcp-plugin-card__nav--prev jcp:left-2" aria-label="Previous image">
                <?php echo jcp_icon('chevron-left', 20); ?>
            </button>
            <button type="button" class="jcp-plugin-card__nav jcp-plugin-card__nav--next jcp:right-2" aria-label="Next image">
                <?php echo jcp_icon('chevron-right', 20); ?>
            </button>
            <div class="jcp-plugin-card__dots">
                <?php foreach ($image_urls as $i => $url) : ?>
                    <button type="button"
                            class="jcp-plugin-card__dot"
                            aria-label="<?php echo esc_attr('Image ' . ($i + 1)); ?>"></button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="jcp-plugin-card__body jcp:p-6 jcp:border-2 jcp:border-[#e5e7eb] jcp:border-t-0 jcp:rounded-b-xl">
        <p class="jcp-plugin-card__description jcp:line-clamp-4 jcp:overflow-hidden" data-desc-text><?php echo nl2br(esc_html($checkin['description'] ?? '')); ?></p>
        <button type="button" class="jcp-plugin-card__toggle jcp:border-0 jcp:bg-transparent jcp:text-accent jcp:text-sm jcp:font-semibold jcp:p-0 jcp:cursor-pointer jcp:hover:underline" data-desc-toggle hidden aria-expanded="false">Read more</button>
        <hr class="jcp:border-0 jcp:border-t jcp:border-[#e5e7eb] jcp:my-4">
        <div class="jcp-plugin-card__meta">
            <span class="jcp-plugin-card__meta-item jcp-plugin-card__date">
                <?php echo jcp_icon('calendar', 14, 'jcp-plugin-card__meta-icon'); ?>
                <?php echo esc_html(gmdate('F j, Y', $timestamp)); ?>
            </span>
            <span class="jcp-plugin-card__meta-item jcp-plugin-card__location">
                <?php echo jcp_icon('map-pin', 14, 'jcp-plugin-card__meta-icon'); ?>
                Near <?php echo esc_html($city . ', ' . $state_abbr); ?>
            </span>
        </div>
    </div>

</article>
