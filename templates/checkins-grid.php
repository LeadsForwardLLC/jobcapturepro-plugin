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
        <button id="load-more-checkins-btn" class="block text-center no-underline bg-accent text-white font-bold px-6 py-3 text-base rounded-full transition-colors duration-200 ease-in-out hover:bg-red-600 cursor-pointer">Load More</button>
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