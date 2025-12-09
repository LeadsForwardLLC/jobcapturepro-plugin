<?php

// Prevent direct access.
if (! defined('ABSPATH')) {
    exit;
}

$maxImagesToDisplay = 5;

?>

<div class="jobcapturepro-checkin-image">
    <!-- Add all images but only first is visible initially -->
    <?php foreach ($imageUrls as $index => $imageUrl): ?>
        <?php if ($index >= $maxImagesToDisplay) break; ?>
        <?php $activeClass = $index === 0 ? 'active' : ''; ?>
        <div class="gallery-image <?php echo esc_attr($activeClass); ?>">
            <img src="<?php echo esc_url($imageUrl); ?>" alt="<?php echo esc_attr('Checkin image ' . ($index + 1)); ?>">
        </div>
    <?php endforeach; ?>

    <!-- Add navigation arrows if there are multiple images -->
    <?php if ($showArrows): ?>
        <div class="gallery-nav gallery-prev">&#10094;</div>
        <div class="gallery-nav gallery-next">&#10095;</div>
        <div class=" gallery-dots">
            <!-- Add indicator dots -->
            <?php for ($i = 0; $i < $imageCount; $i++): ?>
                <?php if ($i >= $maxImagesToDisplay) break; ?>
                <?php $activeClass = $i === 0 ? 'active' : ''; ?>
                <span class="gallery-dot <?php echo esc_attr($activeClass); ?>"></span>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>