<?php
$show_company_stats = $should_show_feature('show_company_stats', !empty($company_info['stats']));
$show_company_stats_fallback = $should_show_feature('show_company_stats', true);
?>

<!-- Checkins Grid -->
<div class="jobcapturepro-container">
    <div id="checkins-grid" class="jobcapturepro-checkins-grid <?php echo $gridId; ?>" data-column-count="3">
        <?php foreach ($checkins as $checkin): ?>
            <?php
            echo Template::render_template('checkin-card', [
                'checkin' => $checkin,
            ]);
            ?>
        <?php endforeach; ?>
    </div>

    <div class="flex justify-center">
        <button id="load-more-checkins-btn" class="flex items-center gap-3 text-center no-underline bg-accent text-white font-bold px-6 py-3 text-base rounded-full transition-colors duration-200 ease-in-out hover:bg-red-600 cursor-pointer">
            <span>Load More</span>
            <svg class="hidden animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </button>
    </div>
</div>


<!-- Company Stats -->
<?php echo Template::render_template('company-stats', [
    'company_info' => $company_info,
    'show_company_stats' => $show_company_stats,
    'show_company_stats_fallback' => $show_company_stats_fallback,
]); ?>

<!-- CTA section -->
<?php echo Template::render_template('cta-section'); ?>