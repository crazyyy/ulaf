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
                    <h5><i class="d4p-icon d4p-ui-memo-pad" style="margin-right: .5em;"></i> Manage all Metadata tables</h5>
                    Management for all 5 WordPress metadata tables (postmeta, commentmeta, usermeta, termmeta and blogmeta) with detection of the meta key usage and source. Plugin can identify and preview all meta records, allowing you to decide on which records and keys you want to remove from all supported tables.

                    <img src="<?php echo SWEEPPRESS_URL; ?>gfx/metadata.jpg" alt="SweepPress Pro: Metadata Management"/>
                </div>
            </div>
            <div class="d4p-group-footer">
                <a href="<?php echo esc_url( sweeppress_fs()->get_upgrade_url() ); ?>" class="button-primary">Upgrade to SweepPress Pro</a>
                <a href="<?php echo esc_url( URL::add_campaign_tracking( 'https://www.dev4press.com/plugins/sweeppress/', 'sweeppress-upgrade-to-pro' ) ); ?>" target="_blank" class="button-secondary">SweepPress Home Page</a>
            </div>
        </div>
    </div>
</div>
