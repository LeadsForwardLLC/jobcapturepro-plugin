<?php

$maxImagesToDisplay = 5;

?>

<div class="jobcapturepro-checkin-image" id="<?php echo esc_attr($galleryId); ?>">
    <!-- Add all images but only first is visible initially -->
    <?php foreach ($imageUrls as $index => $imageUrl): ?>
        <?php if ($index >= $maxImagesToDisplay) break; ?>
        <?php $activeClass = $index === 0 ? ' active' : ''; ?>
        <div class="gallery-image<?php echo esc_attr($activeClass); ?>" data-index="<?php echo intval($index); ?>">
            <img src="<?php echo esc_url($imageUrl); ?>" alt="<?php echo esc_attr('Checkin image ' . ($index + 1)); ?>">
        </div>
    <?php endforeach; ?>

    <!-- Add navigation arrows if there are multiple images -->
    <?php if ($showArrows): ?>
        <div class="gallery-nav gallery-prev" onclick="jobcaptureproChangeImage(event, '<?php echo esc_js($galleryId); ?>', 'prev')">&#10094;</div>
        <div class="gallery-nav gallery-next" onclick="jobcaptureproChangeImage(event, '<?php echo esc_js($galleryId); ?>', 'next')">&#10095;</div>
        <div class="gallery-dots">
            <!-- Add indicator dots -->
            <?php for ($i = 0; $i < $imageCount; $i++): ?>
                <?php if ($i >= $maxImagesToDisplay) break; ?>
                <?php $activeClass = $i === 0 ? ' active' : ''; ?>
                <span class="gallery-dot<?php echo esc_attr($activeClass); ?>" onclick="jobcaptureproShowImage(event, '<?php echo esc_js($galleryId); ?>', <?php echo intval($i); ?>)"></span>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>