<?php

use Dev4Press\Plugin\SweepPress\Basic\License;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$problems = array();
$actions  = array();

if ( ! License::instance()->is_freemius() ) {
	License::instance()->dashboard();

	if ( ! License::instance()->is_valid() ) {
		$problems[] = '<span class="d4p-card-badge d4p-badge-red"><i class="d4p-icon d4p-ui-warning-triangle d4p-icon-fw"></i>' . esc_html__( 'Invalid License', 'sweeppress' ) . '</span><div class="d4p-status-message">' . esc_html__( 'Valid license is required to activate Pro features', 'sweeppress' ) . '</div>';
		$actions[]  = '<a class="button-primary" href="' . sweeppress_admin()->panel_url( 'settings', 'license' ) . '">' . esc_html__( 'Add License Code', 'sweeppress' ) . '</a>';
	}
}

if ( SWEEPPRESS_SIMULATION ) {
	$problems[] = '<span class="d4p-card-badge d4p-badge-maintenance"><i class="d4p-icon d4p-ui-warning d4p-icon-fw"></i>' . esc_html__( 'Simulation', 'sweeppress' ) . '</span><div class="d4p-status-message">' . esc_html__( 'The plugin works in simulation mode, data is not removed!', 'sweeppress' ) . '</div>';
}

if ( empty( $problems ) ) {
	$problems[] = '<span class="d4p-card-badge d4p-badge-ok"><i class="d4p-icon d4p-ui-check-square d4p-icon-fw"></i>' . esc_html__( 'OK', 'sweeppress' ) . '</span><div class="d4p-status-message">' . esc_html__( 'Everything appears to be in order.', 'sweeppress' ) . '</div>';
}

?>
<div class="d4p-group d4p-dashboard-card d4p-card-double d4p-dashboard-status">
    <h3><?php esc_html_e( 'Plugin Status', 'sweeppress' ); ?></h3>
    <div class="d4p-group-inner">
        <div>
			<?php echo join( '</div><div>', $problems ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
    </div>
	<?php if ( ! empty( $actions ) ) { ?>
        <div class="d4p-group-footer">
			<?php echo join( '', $actions ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
	<?php } ?>
</div>
