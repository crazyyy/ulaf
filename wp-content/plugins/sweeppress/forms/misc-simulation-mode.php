<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( SWEEPPRESS_SIMULATION ) { ?>
    <div class="sweeppress-simulation-mode">
        <span><?php esc_html_e( 'Simulation Mode', 'sweeppress' ); ?></span>
        <em><?php esc_html_e( 'Data will not be removed!', 'sweeppress' ); ?></em>
    </div>
<?php }