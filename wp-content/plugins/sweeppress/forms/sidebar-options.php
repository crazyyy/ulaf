<?php

use Dev4Press\Plugin\SweepPress\Meta\Monitor;
use Dev4Press\Plugin\SweepPress\Meta\Tracker;
use Dev4Press\v54\Core\Quick\KSES;
use function Dev4Press\v54\Functions\panel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$_panel    = panel()->object();
$_scanner  = Tracker::instance()->more_themes() !== false || Tracker::instance()->more_plugins() !== false;
$_subpanel = panel()->a()->subpanel;

?>

<div class="d4p-sidebar">
	<?php require SWEEPPRESS_PATH . 'forms/misc-simulation-mode.php'; ?>
    <div class="d4p-panel-title">
        <div class="_icon">
			<?php echo KSES::strong( panel()->r()->icon( $_panel->icon ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
        <h3><?php echo KSES::strong( $_panel->title ); ?></h3>

        <div class="_info">
			<?php

			echo esc_html( $_panel->info );

			if ( isset( $_panel->kb ) ) {
				$url   = $_panel->kb['url'];
				$label = $_panel->kb['label'] ?? __( 'Knowledge Base', 'sweeppress' );

				?>

                <div class="_kb">
                    <a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $label ); ?></a>
                </div>

				<?php
			}

			?>
        </div>
    </div>

	<?php if ( $_subpanel == 'quick' ) { ?>
        <div class="d4p-panel-title">
            <div class="sweeppress-panel-inside">
                <h5><?php esc_html_e( 'Options Management', 'sweeppress' ); ?></h5>
                <p><?php esc_html_e( 'Review all the records in the WordPress options table with ability to remove them.', 'sweeppress' ); ?></p>
                <a class="button-secondary" href="<?php echo esc_url( panel()->a()->panel_url( 'options' ) ); ?>"><?php esc_html_e( 'Back to Management', 'sweeppress' ); ?></a>
            </div>
        </div>
	<?php } else { ?>
        <div class="d4p-panel-title">
            <div class="sweeppress-panel-inside">
                <h5><?php esc_html_e( 'Results Notice', 'sweeppress' ); ?></h5>
				<?php if ( $_scanner ) { ?>
                    <p>
                        <strong><?php esc_html_e( 'Plugin is currently scanning plugins and themes for use of various options and meta data, so some results displayed on this page may be incomplete.', 'sweeppress' ); ?></strong>
                    </p>
				<?php } ?>
                <p><?php esc_html_e( 'Open the Help tab on top of this page to get more information about the results presented on this page.', 'sweeppress' ); ?></p>
            </div>
        </div>

        <div class="d4p-panel-title">
            <div class="sweeppress-panel-inside">
                <h5><?php esc_html_e( 'Usage Monitoring', 'sweeppress' ); ?></h5>
                <p>
					<?php

					if ( Monitor::instance()->is_active() ) {
						printf( esc_html__( 'Options identified: %s.', 'sweeppress' ), sweeppress_settings()->count_usage_monitor_data( 'option', 'option' ) );
					} else {
						esc_html_e( 'Meta & Options Usage Monitoring is not active. You can activate it in the plugin settings', 'sweeppress' );
					}

					?>
                </p>
            </div>
        </div>

        <div class="d4p-panel-title">
            <div class="sweeppress-panel-inside">
                <h5><?php esc_html_e( 'Options Quick Tasks', 'sweeppress' ); ?></h5>
                <p><?php esc_html_e( 'Run quick cleanup tasks for the Options database table.', 'sweeppress' ); ?></p>
                <a class="button-secondary" href="<?php echo esc_url( panel()->a()->panel_url( 'options', 'quick' ) ); ?>"><?php esc_html_e( 'Quick Tasks', 'sweeppress' ); ?></a>
            </div>
        </div>
	<?php } ?>

</div>
