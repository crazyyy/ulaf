<?php

use Dev4Press\v54\Core\Quick\URL;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="d4p-content">
    <div class="d4p-cards-wrapper" style="max-width: 880px">
        <div class="d4p-group d4p-dashboard-card d4p-card-double d4p-dashboard-card-dev4press d4p-dashboard-card-no-footer">
            <h3>SweepPress Pro Exclusive Features</h3>
            <div class="d4p-group-header">
                <p style="margin: 0">SweepPress Pro contains a lot more features that can help with the cleanup of the website, and management of the various database aspects. This includes metadata management, CRON controls, Gravity Forms sweepers, Sweeper Monitor, Scheduling Sweeper Jobs and more.</p>
            </div>
            <div class="d4p-group-inner sweeppress-statistics-sweeper">
                <div class="sweeppress-statistics-box" style="flex: 100%">
                    <h5><i class="d4p-icon d4p-ui-database" style="margin-right: .5em;"></i> Database Overview and Control</h5>
                    Overview of all the tables in the database related to the WordPress installation with detailed information about each table, basic plugin identification, and additional actions to optimize or repair tables. For advanced users, plugin has options to empty or delete tables.

                    <img src="<?php echo SWEEPPRESS_URL; ?>gfx/database.jpg" alt="SweepPress Pro: Database Management"/>
                </div>
            </div>
            <div class="d4p-group-footer">
                <a href="<?php echo esc_url( sweeppress_fs()->get_upgrade_url() ); ?>" class="button-primary">Upgrade to SweepPress Pro</a>
                <a href="<?php echo esc_url( URL::add_campaign_tracking( 'https://www.dev4press.com/plugins/sweeppress/', 'sweeppress-upgrade-to-pro' ) ); ?>" target="_blank" class="button-secondary">SweepPress Home Page</a>
            </div>
        </div>
    </div>
</div>
