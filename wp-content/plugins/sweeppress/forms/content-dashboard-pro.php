<?php

use Dev4Press\v54\Core\Quick\URL;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="d4p-group d4p-dashboard-card d4p-card-double d4p-dashboard-card-dev4press d4p-dashboard-card-no-footer" style="border-color: #67AF12;">
    <h3 style="background: #67AF12; color: white;">Upgrade to SweepPress Pro</h3>
    <div class="d4p-group-header">
        <p style="margin: 0;font-size: 1.1em;font-weight: 500;">Get more great features with a Pro version, with even more coming with future releases.</p>
    </div>
    <div class="d4p-group-inner">
        <ul class="d4p-three-columns">
            <li>
                <i class="d4p-icon d4p-ui-archive d4p-icon-fw" style="font-size: 1.3em"></i> Backup Data to SQL file before Removal
            </li>
            <li>
                <i class="d4p-icon d4p-ui-memo-pad d4p-icon-fw" style="font-size: 1.3em"></i> Manage and Clean all Metadata tables
            </li>
            <li>
                <i class="d4p-icon d4p-ui-network d4p-icon-fw" style="font-size: 1.3em"></i> Manage and Clean Sitemeta Multisite table
            </li>
            <li>
                <i class="d4p-icon d4p-ui-calendar-day d4p-icon-fw" style="font-size: 1.3em"></i> Scheduled Automatic Sweep Jobs
            </li>
            <li>
                <i class="d4p-icon d4p-ui-calendar d4p-icon-fw" style="font-size: 1.3em"></i> WordPress CRON Tracking and Control
            </li>
            <li>
                <i class="d4p-icon d4p-ui-clock d4p-icon-fw" style="font-size: 1.3em"></i> Daily and Weekly Sweeper Monitor
            </li>
            <li>
                <i class="d4p-icon d4p-ui-database d4p-icon-fw" style="font-size: 1.3em"></i> Database Tables Management and Control
            </li>
            <li style="flex-grow: 0">
                <i class="d4p-icon d4p-ui-list d4p-icon-fw" style="font-size: 1.3em"></i> Three Sweepers for Gravity Forms plugin
            </li>
        </ul>
    </div>
    <div class="d4p-group-footer">
        <a href="<?php echo esc_url( sweeppress_fs()->get_upgrade_url() ); ?>" class="button-primary">Upgrade to SweepPress Pro</a>
        <a href="<?php echo esc_url( URL::add_campaign_tracking( 'https://www.dev4press.com/plugins/sweeppress/', 'sweeppress-upgrade-to-pro' ) ); ?>" target="_blank" class="button-secondary">SweepPress Home Page</a>
    </div>
</div>
