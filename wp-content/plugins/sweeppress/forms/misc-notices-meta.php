<?php

use function Dev4Press\v54\Functions\panel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( sweeppress_settings()->show_notice( 'meta' ) ) {
	$disable = panel()->a()->getback_url( array(
		'single-action' => 'disable-metas-notice',
		'_wpnonce'      => wp_create_nonce( 'sweeppress-disable-metas-notice' ),
	) );

	?>

    <div class="d4p-group d4p-dashboard-card d4p-card-double sweeppress-notice-group sweeppress-notice-backups">
        <h3><?php esc_html_e( 'Options and Metadata Management', 'sweeppress' ); ?></h3>
        <div class="d4p-group-inner">
            <p><?php esc_html_e( 'SweepPress has several methods to detect if the options and meta keys are in use or not. Based on the identification, you can decide if you want to remove records belonging to a specific option or meta key.', 'sweeppress' ); ?><?php
				esc_html_e( 'To learn more about the process, and how to efficiently cleanup your options and meta tables, make sure to start with the user guide in the Knowledge Base, and all the relevant articles.', 'sweeppress' ); ?></p>
        </div>
        <div class="d4p-group-footer">
            <a class="button-primary" href="https://www.dev4press.com/kb/user-guide/sweeppress-feature-options-sitemeta-and-metadata-management/" target="_blank" rel="noopener external"><?php esc_html_e( 'Knowledge Base', 'sweeppress' ); ?></a>
            <a style="float: right" class="button-secondary" href="<?php echo esc_url( $disable ); ?>" title="<?php esc_attr_e( 'Disable Meta notice display.', 'sweeppress' ); ?>"><?php esc_html_e( 'Hide', 'sweeppress' ); ?></a>
        </div>
    </div>

	<?php

}
