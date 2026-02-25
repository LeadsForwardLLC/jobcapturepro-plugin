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

<article class="jcp-plugin-card">

    <?php if ($has_images) : ?>
    <div class="jcp-plugin-card__gallery"<?php if ($has_gallery) : ?> data-carousel<?php endif; ?>>
        <div class="jcp-plugin-card__gallery-inner">
            <?php foreach ($image_urls as $i => $url) : ?>
                <div class="jcp-plugin-card__slide<?php echo $i === 0 ? ' is-active' : ''; ?>">
                    <img src="<?php echo esc_url($url); ?>"
                         alt=""
                         width="400"
                         height="260"
                         loading="<?php echo $i === 0 ? 'eager' : 'lazy'; ?>">
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($has_gallery) : ?>
            <button type="button" class="jcp-plugin-card__nav jcp-plugin-card__nav--prev" aria-label="Previous image">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 18l-6-6 6-6"/>
                </svg>
            </button>
            <button type="button" class="jcp-plugin-card__nav jcp-plugin-card__nav--next" aria-label="Next image">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 18l6-6-6-6"/>
                </svg>
            </button>
            <div class="jcp-plugin-card__dots">
                <?php foreach ($image_urls as $i => $url) : ?>
                    <button type="button"
                            class="jcp-plugin-card__dot<?php echo $i === 0 ? ' is-active' : ''; ?>"
                            aria-label="<?php echo esc_attr('Image ' . ($i + 1)); ?>"></button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="jcp-plugin-card__body">
        <p class="jcp-plugin-card__description" data-desc-text><?php echo nl2br(esc_html($checkin['description'] ?? '')); ?></p>
        <button type="button" class="jcp-plugin-card__toggle" data-desc-toggle hidden aria-expanded="false">Read more</button>
        <div class="jcp-plugin-card__meta">
            <span class="jcp-plugin-card__meta-item jcp-plugin-card__date">
                <svg class="jcp-plugin-card__meta-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M8 2v4"/><path d="M16 2v4"/>
                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                    <path d="M3 10h18"/>
                </svg>
                <?php echo esc_html(gmdate('F j, Y', $timestamp)); ?>
            </span>
            <span class="jcp-plugin-card__meta-item jcp-plugin-card__location">
                <svg class="jcp-plugin-card__meta-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M20 10c0 5-8 12-8 12s-8-7-8-12a8 8 0 1 1 16 0Z"/>
                    <circle cx="12" cy="10" r="3"/>
                </svg>
                Near <?php echo esc_html($city . ', ' . $state_abbr); ?>
            </span>
        </div>
    </div>

</article>
