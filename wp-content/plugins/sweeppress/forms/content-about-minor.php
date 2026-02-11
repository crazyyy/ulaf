<?php

use Dev4Press\v54\Core\Quick\KSES;
use function Dev4Press\v54\Functions\panel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="d4p-about-minor">
    <h3><?php esc_html_e( 'Maintenance and Security Releases', 'sweeppress' ); ?></h3>
    <p>
        <strong><?php esc_html_e( 'Version', 'sweeppress' ); ?> <span>6.4.3 / 6.4.4</span></strong> &minus;
        Updated SDK.
    </p>
    <p>
        <strong><?php esc_html_e( 'Version', 'sweeppress' ); ?> <span>6.4.2</span></strong> &minus;
        Minor updates and fixes.
    </p>
    <p>
        <strong><?php esc_html_e( 'Version', 'sweeppress' ); ?> <span>6.4.1</span></strong> &minus;
        Updated library and SDK.
    </p>
    <p>
        <strong><?php esc_html_e( 'Version', 'sweeppress' ); ?> <span>6.4</span></strong> &minus;
        Refactoring, various improvements and fixes.
    </p>
    <p>
        <strong><?php esc_html_e( 'Version', 'sweeppress' ); ?> <span>6.3.1</span></strong> &minus;
        Minor updates and tweaks.
    </p>
    <p>
        <strong><?php esc_html_e( 'Version', 'sweeppress' ); ?> <span>6.3</span></strong> &minus;
        New Elementor sweeper. Various updates and fixes.
    </p>
    <p>
        <strong><?php esc_html_e( 'Version', 'sweeppress' ); ?> <span>6.2</span></strong> &minus;
        Composer use to install Dev4Press and Freemius libraries. Various updates and fixes.
    </p>
    <p>
        <strong><?php esc_html_e( 'Version', 'sweeppress' ); ?> <span>6.1</span></strong> &minus;
        Many updates to Pro only code. Fire actions on some events.
    </p>
    <p>
        <strong><?php esc_html_e( 'Version', 'sweeppress' ); ?> <span>6.0.1</span></strong> &minus;
        Minor updates and fixes.
    </p>
    <p>
		<?php echo KSES::standard( sprintf( __( 'For more information, see <a href=\'%s\'>the changelog</a>.', 'sweeppress' ), panel()->a()->panel_url( 'about', 'changelog' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
    </p>
</div>
