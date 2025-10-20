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

<?php /* elseif ($show_company_stats_fallback): */ ?>
    <!-- <div class="jobcapturepro-stats-container">
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
    </div> -->
<?php /* endif; */ ?>