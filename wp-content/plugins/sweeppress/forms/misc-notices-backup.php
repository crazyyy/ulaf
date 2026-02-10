<?php

use function Dev4Press\v54\Functions\panel;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( sweeppress_settings()->show_notice() ) {
    $disable = panel()->a()->getback_url( array(
        'single-action' => 'disable-backups-notice',
        '_wpnonce'      => wp_create_nonce( 'sweeppress-disable-backups-notice' ),
    ) );
    ?>

    <div class="d4p-group d4p-dashboard-card d4p-card-double sweeppress-notice-group sweeppress-notice-backups">
        <h3>
			<?php 
    esc_html_e( 'Backup your Database', 'sweeppress' );
    ?>
        </h3>
        <div class="d4p-group-inner">
            <p>
                <strong><?php 
    esc_html_e( 'All the sweeping operations will remove data from the database, and these operations are not reversible!', 'sweeppress' );
    ?></strong> <?php 
    esc_html_e( 'Sweeping operations are considered safe and are designed to remove the data that is actually not used by WordPress. But, to be extra safe, it is highly recommended to backup your database before you use any of the sweepers.', 'sweeppress' );
    ?>
            </p>
            <p>
                <strong><?php 
    esc_html_e( 'Pro version of the plugin has the ability to export all the data that is about to be removed (by sweeper or via management panels) into an SQL backup file.', 'sweeppress' );
    ?></strong>
            </p>
        </div>
        <div class="d4p-group-footer">
            <a class="button-primary" href="https://www.dev4press.com/kb/user-guide/sweeppress-ultimate-guide-to-sweeppress-pro-and-lite/" target="_blank" rel="noopener external"><?php 
    esc_html_e( 'Plugin Ultimate Guide', 'sweeppress' );
    ?></a>
			<?php 
    ?>
                <a href="<?php 
    echo esc_url( sweeppress_fs()->get_upgrade_url() );
    ?>" class="button-primary">Upgrade to SweepPress Pro to get Backup feature</a>
			<?php 
    ?>
            <a style="float:right;" class="button-secondary" href="<?php 
    echo esc_url( $disable );
    ?>" title="<?php 
    esc_attr_e( 'Disable Backup notice display.', 'sweeppress' );
    ?>"><?php 
    esc_html_e( 'Hide', 'sweeppress' );
    ?></a>
        </div>
    </div>

	<?php 
}