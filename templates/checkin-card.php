<?php

// Prevent direct access.
if (! defined('ABSPATH')) {
    exit;
}

$address_parts = explode(',', $checkin['address'] ?? '');
$city          = trim($address_parts[1] ?? '');
$state         = trim($address_parts[2] ?? '');
$state_abbr    = strlen($state) > 2 ? substr($state, 0, 2) : $state;
$timestamp     = strtotime($checkin['createdAt'] ?? '');
$image_urls    = $checkin['imageUrls'] ?? [];
$has_images    = ! empty($image_urls) && is_array($image_urls);
$has_gallery   = $has_images && count($image_urls) > 1;

?>

<article class="jcp-plugin-card jcp:group jcp:flex-[0_0_var(--card-width,300px)] jcp:min-w-0 jcp:bg-white jcp:rounded-xl jcp:overflow-hidden jcp:transition-transform jcp:duration-200 jcp:hover:-translate-y-0.5">

    <?php if ($has_images) : ?>
    <div class="jcp-plugin-card__gallery jcp:relative jcp:w-full jcp:aspect-400/260 jcp:overflow-hidden jcp:bg-[#f3f4f6]"<?php if ($has_gallery) : ?> data-carousel<?php endif; ?>>
        <div class="jcp-plugin-card__gallery-inner jcp:relative jcp:w-full jcp:h-full">
            <?php foreach ($image_urls as $i => $url) : ?>
                <div class="jcp-plugin-card__slide <?php echo $i === 0 ? 'jcp:opacity-100 jcp:visible' : 'jcp:opacity-0 jcp:invisible'; ?>">
                    <img src="<?php echo esc_url($url); ?>"
                         alt=""
                         width="400"
                         height="260"
                         class="jcp:w-full jcp:h-full jcp:object-cover jcp:block"
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
            <div class="jcp-plugin-card__dots jcp:absolute jcp:bottom-2 jcp:left-1/2 jcp:-translate-x-1/2 jcp:flex jcp:gap-1.5 jcp:z-2">
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
        <p class="jcp-plugin-card__description jcp:text-[17px] jcp:leading-[1.6] jcp:text-[#111827] jcp:m-0 jcp:line-clamp-4 jcp:overflow-hidden" data-desc-text><?php echo nl2br(esc_html($checkin['description'] ?? '')); ?></p>
        <button type="button" class="jcp-plugin-card__toggle jcp:border-0 jcp:bg-transparent jcp:text-accent jcp:text-sm jcp:font-semibold jcp:p-0 jcp:cursor-pointer jcp:hover:underline" data-desc-toggle hidden aria-expanded="false">Read more</button>
        <hr class="jcp:border-0 jcp:border-t jcp:border-[#e5e7eb] jcp:my-4">
        <div class="jcp-plugin-card__meta jcp:flex jcp:flex-nowrap jcp:items-center jcp:justify-between jcp:gap-2 jcp:text-xs jcp:md:text-sm jcp:text-[#6b7280]">
            <span class="jcp-plugin-card__meta-item jcp-plugin-card__date">
                <?php echo jcp_icon('calendar', 14, 'jcp-plugin-card__meta-icon'); ?>
                <?php echo esc_html(gmdate('F j, Y', $timestamp)); ?>
            </span>
            <span class="jcp-plugin-card__meta-item jcp-plugin-card__location jcp:font-semibold jcp:text-[#111827]">
                <?php echo jcp_icon('map-pin', 14, 'jcp-plugin-card__meta-icon'); ?>
                Near <?php echo esc_html($city . ', ' . $state_abbr); ?>
            </span>
        </div>
    </div>

</article>
