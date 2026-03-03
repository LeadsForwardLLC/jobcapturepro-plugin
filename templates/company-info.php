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

<div class="jcp-company-info jcp:flex jcp:flex-col jcp:md:flex-row jcp:items-start jcp:md:items-center jcp:px-4 jcp:md:px-8 jcp:py-8 jcp:md:py-10 jcp:bg-white jcp:border-b jcp:border-[#eee] jcp:gap-6">
    <div class="jcp-company-details jcp:flex-1 jcp:w-full jcp:min-w-0">
        <h2 class="jcp-company-name jcp:text-[#333] jcp:m-0 jcp:text-2xl jcp:md:text-[1.8rem] jcp:font-bold jcp:mb-2"><?php echo esc_html($company_info['name']); ?></h2>

        <div class="jcp-company-info-text jcp:mb-2">
            <p class="jcp:m-0 jcp:text-base jcp:text-[#444]"><?php echo esc_html($company_info['address']); ?></p>
        </div>

        <div class="jcp-company-div-2 jcp:flex jcp:flex-wrap jcp:items-center jcp:gap-x-1 jcp:gap-y-1">
            <div class="jcp-company-reviews-text">
                <p class="jcp:flex jcp:items-center jcp:gap-1.5 jcp:m-0">
                    <?php echo jcp_icon('star', 18); ?>
                    <span>No Reviews</span>
                </p>
            </div>

            <?php if ($has_phone): ?>
                <p class="jcp:m-0 jcp:text-base jcp:text-[#444] jcp:flex jcp:items-center"><strong>&nbsp;·&nbsp;</strong><a class="jcp:text-[#111] jcp:no-underline jcp:hover:underline" href="tel:<?php echo esc_attr(preg_replace('/[^0-9]/', '', $company_info['tn'])); ?>"><?php echo esc_html($company_info['phoneNumberString']); ?></a></p>
            <?php endif; ?>

            <?php if ($has_url): ?>
                <p class="jcp:m-0 jcp:text-base jcp:text-[#444] jcp:flex jcp:items-center"><strong>&nbsp;·&nbsp;</strong><a class="jcp:text-[#111] jcp:no-underline jcp:hover:underline" href="<?php echo esc_url($company_info['url']); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($display_url); ?></a></p>
            <?php endif; ?>

            <?php if (!$has_phone && !$has_url): ?>
                <p class="jcp-no-contact-info jcp:m-0 jcp:text-[#999] jcp:italic">Contact No. and Website information not available</p>
            <?php endif; ?>
        </div>

        <div class="jcp-company-description jcp:mt-2">
            <p class="jcp:m-0 jcp:text-base jcp:text-[#444]"><?php echo esc_html($company_info['description']); ?></p>
        </div>
    </div>

    <div class="jcp-company-actions jcp:flex jcp:flex-row jcp:md:flex-col jcp:items-center jcp:justify-between jcp:md:items-end jcp:md:justify-start jcp:gap-3 jcp:w-full jcp:md:w-auto jcp:shrink-0">
        <?php if (!empty($company_info['logoUrl'])): ?>
            <img class="jcp-company-logo__img jcp:max-w-40 jcp:md:max-w-[200px] jcp:h-auto jcp:object-contain" src="<?php echo esc_url($company_info['logoUrl']); ?>" alt="<?php echo esc_attr($company_info['name']); ?> logo">
        <?php endif; ?>
        <div class="jcp-company-cta jcp:flex jcp:flex-col jcp:items-end jcp:gap-2">
            <a href="<?php echo esc_url($company_info['quoteUrl']); ?>" class="jcp-quote-btn">Get a Quote</a>
            <p class="jcp:text-xs jcp:text-gray-400 jcp:m-0">Powered by <a href="https://jobcapturepro.com" class="jcp:font-bold jcp:no-underline jcp:text-black jcp:hover:underline">JobCapturePro</a></p>
        </div>
    </div>
</div>
