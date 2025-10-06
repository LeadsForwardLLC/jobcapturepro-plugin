<?php
// Sort checkins by date (newest first)
usort($checkins, function ($a, $b) {
    // Compare timestamps (higher timestamp = more recent)
    return strtotime($b['createdAt']) - strtotime($a['createdAt']);
});

// jcp stats section logic
$show_stats = $show_company_stats && !empty($company_info['stats']);
$show_fallback_stats = $show_company_stats_fallback;
?>

<!-- Container with CSS Grid for responsive layout -->
<div class="jobcapturepro-container">
    <!-- Grid container with data attribute to store the column count -->
    <div class="jobcapturepro-checkins-grid <?php echo $gridId; ?>" data-column-count="3">
        <!-- Add each checkin to the grid in date-sorted order -->
        <?php foreach ($checkins as $checkin): ?>
            <?php
                echo $render_checkin_card_html($checkin);
            ?>
        <?php endforeach; ?>
    </div> <!-- Close grid -->
</div> <!-- Close container -->

<!-- JCP stats section - only show if stats data is available or feature is enabled -->
<?php if ($show_stats): ?>
    <div class="jobcapturepro-stats-container">
        <?php if (!empty($company_info['stats']['jobs_this_month'])): ?>
            <div class="jobcapturepro-stat-item">
                <div class="jobcapturepro-stat-number"><?php echo esc_html($company_info['stats']['jobs_this_month']); ?></div>
                <div class="jobcapturepro-stat-label">Jobs Posted This Month</div>
            </div>
        <?php endif; ?>

        <?php if (!empty($company_info['stats']['average_rating'])): ?>
            <div class="jobcapturepro-stat-item">
                <div class="jobcapturepro-stat-number"><?php echo esc_html($company_info['stats']['average_rating']); ?></div>
                <div class="jobcapturepro-stat-label">Average Rating</div>
            </div>
        <?php endif; ?>

        <?php if (!empty($company_info['stats']['last_checkin'])): ?>
            <div class="jobcapturepro-stat-item">
                <div class="jobcapturepro-stat-number"><?php echo esc_html($company_info['stats']['last_checkin']); ?></div>
                <div class="jobcapturepro-stat-label">Last Job Check-In</div>
            </div>
        <?php endif; ?>
    </div>
    <!-- Close jobcapturepro-stats-container -->

<?php elseif ($show_fallback_stats): ?>
    <!-- Fallback to hard-coded stats if feature is enabled but no data -->
    <div class="jobcapturepro-stats-container">
        <div class="jobcapturepro-stat-item">
            <div class="jobcapturepro-stat-number">86</div>
            <div class="jobcapturepro-stat-label">Jobs Posted This Month</div>
        </div>

        <div class="jobcapturepro-stat-item">
            <div class="jobcapturepro-stat-number">96%</div>
            <div class="jobcapturepro-stat-label">Average 5-Star Rating</div>
        </div>

        <div class="jobcapturepro-stat-item">
            <div class="jobcapturepro-stat-number">12 mins ago</div>
            <div class="jobcapturepro-stat-label">Last Job Check-In</div>
        </div>
    </div>
    <!-- Close jobcapturepro-stats-container -->
<?php endif; ?>

<!-- JCP CTA section -->
<div class="jobcapturepro-cta-container">
    <!-- CTA Heading -->
    <div class="jobcapturepro-cta">
        <h2>Let Your Work Speak For Itself</h2>
        <p>Capture check-ins like these with JobCapturePro. Set it and forget it.</p>
        <a href="#" class="quote-btn">Get JobCapturePro</a>
    </div>
</div>