<?php

use Dev4Press\v54\Core\Quick\KSES;
use function Dev4Press\v54\Functions\panel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$_panel     = panel()->a()->panel_object();
$_subpanel  = panel()->a()->subpanel;
$_subpanels = panel()->subpanels();

?>
<div class="d4p-sidebar">
	<?php require SWEEPPRESS_PATH . 'forms/misc-simulation-mode.php'; ?>
    <div class="d4p-panel-scroller d4p-scroll-active">
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

		<?php if ( sweeppress_settings()->get( 'estimated_cache', 'sweepers' ) ) { ?>
            <div class="d4p-panel-title">
                <div class="sweeppress-panel-inside sweeppress-sweeper-counters">
                    <h5><?php esc_html_e( 'Cache', 'sweeppress' ); ?></h5>
                    <p><?php esc_html_e( 'Estimation data cache is enabled.', 'sweeppress' ); ?></p>
                    <a class="button-secondary" href="<?php echo esc_url( panel()->a()->action_url( 'clear-estimation-cache', 'sweeppress-clear-estimation-cache', '', 'sweep' ) ); ?>"><?php esc_html_e( 'Clear Estimation Cache', 'sweeppress' ); ?></a>
                </div>
            </div>
		<?php } ?>

        <div class="d4p-panel-title">
            <div class="sweeppress-panel-inside sweeppress-sweeper-counters">
                <h5><?php esc_html_e( 'Selected Sweepers', 'sweeppress' ); ?></h5>
                <dl class="sweeppress-list">
                    <dt><?php esc_html_e( 'Tasks', 'sweeppress' ); ?>:</dt>
                    <dd class="sweeppress-sweep-tasks">0</dd>
                    <dt><?php esc_html_e( 'Records', 'sweeppress' ); ?>:</dt>
                    <dd class="sweeppress-sweep-records">0</dd>
                    <dt><?php esc_html_e( 'Size', 'sweeppress' ); ?>:</dt>
                    <dd class="sweeppress-sweep-size">0</dd>
                </dl>
            </div>
        </div>
        <div class="d4p-panel-buttons">
            <input id="sweeppress-sweep-run" disabled type="submit" value="<?php esc_attr_e( 'Run Sweeper', 'sweeppress' ); ?>" class="button-primary"/>
        </div>
        <div class="d4p-return-to-top">
            <a href="#wpwrap"><?php esc_html_e( 'Return to top', 'sweeppress' ); ?></a>
        </div>
    </div>
</div>
