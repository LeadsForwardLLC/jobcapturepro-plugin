<?php

// Prevent direct access.
if (! defined('ABSPATH')) {
    exit;
}

// Check if we have either phone or URL
$has_phone = !empty($company_info['tn']);
$has_url = !empty($company_info['url']);

if ($has_url) {
    $parsed_url = wp_parse_url($company_info['url']);
    $host = $parsed_url['host'] ?? $company_info['url'];
    // Remove www. prefix if it exists
    $display_url = preg_replace('/^www\./i', '', $host);
}
?>

<div class="jobcapturepro-company-info">
    <div class="jobcapturepro-company-details">
        <h2 class="jobcapturepro-company-name"><?php echo esc_html($company_info['name']); ?></h2>

        <div class="jobcapturepro-company-info-text">
            <p><?php echo esc_html($company_info['address']); ?></p>
        </div>

        <div class="jobcapturepro-company-div-2">
            <div class="jobcapturepro-company-reviews-text">
                <p>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                        <path d="M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C43.3 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z" />
                    </svg>
                    <span>No Reviews</span>
                </p>
            </div>

            <?php if ($has_phone): ?>
                <p> <strong> &nbsp;.&nbsp; </strong><a href="tel:<?php echo esc_attr(preg_replace('/[^0-9]/', '', $company_info['tn'])); ?>"><?php echo esc_html($company_info['phoneNumberString']); ?></a></p>
            <?php endif; ?>

            <?php if ($has_url): ?>
                <p> <strong> &nbsp;.&nbsp; </strong><a href="<?php echo esc_url($company_info['url']); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($display_url); ?></a></p>
            <?php endif; ?>

            <?php if (!$has_phone && !$has_url): ?>
                <p class="jobcapturepro-no-contact-info">Contact No. and Website information not available</p>
            <?php endif; ?>
        </div>

        <div class="jobcapturepro-company-description">
            <p><?php echo esc_html($company_info['description']); ?></p>
        </div>
    </div>

    <div class="jobcapturepro-company-logo">
        <a href="<?php echo esc_url($company_info['quoteUrl']); ?>" class="quote-btn">Get a Quote</a>
        <p class="jcp:text-xs jcp:text-gray-400 jcp:mt-2.5">Powered by <a href="https://jobcapturepro.com" class="jcp:font-bold jcp:no-underline jcp:text-black hover:jcp:underline">JobCapturePro</a></p>
    </div>

    <?php if (!empty($company_info['logoUrl'])): ?>
        <div class="jobcapturepro-company-logo">
            <img src="<?php echo esc_url($company_info['logoUrl']); ?>" alt="<?php echo esc_attr($company_info['name']); ?> Logo">
        </div>
    <?php endif; ?>
</div>