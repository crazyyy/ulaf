<?php

use Dev4Press\Plugin\SweepPress\Pro\Meta\Tables;
use Dev4Press\v54\Core\Quick\File;
use Dev4Press\v54\Core\Quick\Sanitize;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$list   = sweeppress_settings()->list_statistics();
$period = isset( $_GET['period'] ) ? Sanitize::slug( $_GET['period'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification,WordPress.Security.ValidatedSanitizedInput

if ( ! isset( $list[ $period ] ) ) {
	$period = '';
}

$statistics = sweeppress_settings()->get_statistics( $period );

?>
<div class="d4p-content">
	<?php if ( ! isset( $statistics['sweepers'] ) ) { ?>
        <p><?php esc_html_e( 'There is no data to show here.', 'sweeppress' ); ?></p>
	<?php } else { ?>
        <div class="d4p-group">
            <h3><?php esc_html_e( 'Basic Statistics', 'sweeppress' ); ?></h3>
            <div class="d4p-group-inner">
                <dl class="sweeppress-list">
                    <dt><?php esc_html_e( 'Period', 'sweeppress' ); ?></dt>
                    <dd><?php echo esc_html( $statistics['label'] ); ?></dd>
                </dl>
                <hr/>
                <dl class="sweeppress-list">
                    <dt><?php esc_html_e( 'Sweeping Sources', 'sweeppress' ); ?></dt>
                    <dd>
						<?php echo esc_html__( 'Sweep Panel', 'sweeppress' ) . ': ' . absint( $statistics['panel'] ?? 0 ); ?><br/>
						<?php echo esc_html__( 'Quick Sweep', 'sweeppress' ) . ': ' . absint( $statistics['quick'] ?? 0 ); ?><br/>
						<?php echo esc_html__( 'Auto Sweep', 'sweeppress' ) . ': ' . absint( $statistics['auto'] ?? 0 ); ?><br/>
						<?php echo esc_html__( 'Scheduler Jobs', 'sweeppress' ) . ': ' . absint( $statistics['scheduler'] ?? 0 ); ?><br/>
						<?php echo esc_html__( 'Admin Bar', 'sweeppress' ) . ': ' . absint( $statistics['adminbar'] ?? 0 ); ?><br/>
						<?php echo esc_html__( 'Sweep via CLI', 'sweeppress' ) . ': ' . absint( $statistics['cli'] ?? 0 ); ?><br/>
						<?php echo esc_html__( 'Sweep via REST', 'sweeppress' ) . ': ' . absint( $statistics['rest'] ?? 0 ); ?>
                    </dd>
                </dl>
                <hr/>
                <dl class="sweeppress-list">
                    <dt><?php esc_html_e( 'Metadata Sweep Sources', 'sweeppress' ); ?></dt>
                    <dd>
						<?php echo esc_html__( 'Metadata', 'sweeppress' ) . ': ' . absint( $statistics['metadata'] ?? 0 ); ?><br/>
						<?php echo esc_html__( 'Options', 'sweeppress' ) . ': ' . absint( $statistics['options'] ?? 0 ); ?><br/>
						<?php echo esc_html__( 'Sitemeta', 'sweeppress' ) . ': ' . absint( $statistics['sitemeta'] ?? 0 ); ?>
                    </dd>
                </dl>
                <hr/>
                <dl class="sweeppress-list">
                    <dt><?php esc_html_e( 'Sweepers Run', 'sweeppress' ); ?></dt>
                    <dd><?php echo absint( $statistics['jobs'] ); ?></dd>
                    <dt><?php esc_html_e( 'Sweeper Tasks Run', 'sweeppress' ); ?></dt>
                    <dd><?php echo absint( $statistics['tasks'] ); ?></dd>
                    <dt><?php esc_html_e( 'Records Removed', 'sweeppress' ); ?></dt>
                    <dd><?php echo absint( $statistics['records'] ); ?></dd>
                    <dt><?php esc_html_e( 'Space Recovered', 'sweeppress' ); ?></dt>
                    <dd><?php echo File::size_format( absint( $statistics['size'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></dd>
                    <dt><?php esc_html_e( 'Sweeping Time', 'sweeppress' ); ?></dt>
                    <dd><?php echo esc_html( ceil( $statistics['time'] ) ) . ' ' . esc_html__( 'seconds', 'sweeppress' ); ?></dd>
                </dl>
                <hr/>
                <dl class="sweeppress-list">
                    <dt><?php esc_html_e( 'Total Space Recovered (With Database Cleanup)', 'sweeppress' ); ?></dt>
                    <dd><?php echo File::size_format( absint( $statistics['size_total'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></dd>
                </dl>
            </div>
        </div>
        <div class="d4p-group">
            <h3><?php esc_html_e( 'Individual Sweepers', 'sweeppress' ); ?></h3>
            <div class="d4p-group-inner sweeppress-statistics-sweeper">
				<?php foreach ( $statistics['sweepers'] as $sweeper => $data ) {
					if ( str_starts_with( $sweeper, '_' ) ) {
						continue;
					} ?>
                    <div class="sweeppress-statistics-box">
                        <h5><?php echo esc_html( sweeppress_core()->get_sweeper_title( $sweeper ) ); ?></h5>
                        <dl class="sweeppress-list">
                            <dt><?php esc_html_e( 'Sweeps Counter', 'sweeppress' ); ?></dt>
                            <dd><?php echo absint( $data['counts'] ?? 1 ); ?></dd>
                            <dt><?php esc_html_e( 'Records Removed', 'sweeppress' ); ?></dt>
                            <dd><?php echo absint( $data['records'] ); ?></dd>
                            <dt><?php esc_html_e( 'Space Recovered', 'sweeppress' ); ?></dt>
                            <dd><?php echo File::size_format( absint( $data['size'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></dd>
                            <dt><?php esc_html_e( 'Sweeping Time', 'sweeppress' ); ?></dt>
                            <dd><?php echo esc_html( ceil( $data['time'] ) ) . ' ' . esc_html__( 'seconds', 'sweeppress' ); ?></dd>
                        </dl>
                    </div>
				<?php } ?>
            </div>
        </div>
        <div class="d4p-group">
            <h3><?php esc_html_e( 'Options and Metadata', 'sweeppress' ); ?></h3>
            <div class="d4p-group-inner sweeppress-statistics-sweeper">
				<?php foreach ( $statistics['sweepers'] as $sweeper => $data ) {
					if ( ! str_starts_with( $sweeper, '_' ) ) {
						continue;
					}

					if ( str_starts_with( $sweeper, '__options_' ) ) {
						$title = __( 'Options', 'sweeppress' );

						if ( substr( $sweeper, 10 ) == 'options' ) {
							$title .= ': ' . __( 'WordPress Options', 'sweeppress' );
						} else {
							$title .= ': ' . __( 'Multisite Sitemeta', 'sweeppress' );
						}
					} else {
						$title = __( 'Metadata', 'sweeppress' );
						$table = Tables::instance()->table( substr( $sweeper, 11 ) );

						if ( ! is_null( $table ) ) {
							$title .= ': ' . $table->get_label();
						}
					}

					?>
                    <div class="sweeppress-statistics-box">
                        <h5><?php echo esc_html( $title ); ?></h5>
                        <dl class="sweeppress-list">
                            <dt><?php esc_html_e( 'Sweeps Counter', 'sweeppress' ); ?></dt>
                            <dd><?php echo absint( $data['counts'] ?? 1 ); ?></dd>
                            <dt><?php esc_html_e( 'Records Removed', 'sweeppress' ); ?></dt>
                            <dd><?php echo absint( $data['records'] ); ?></dd>
                            <dt><?php esc_html_e( 'Space Recovered', 'sweeppress' ); ?></dt>
                            <dd><?php echo File::size_format( absint( $data['size'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></dd>
                            <dt><?php esc_html_e( 'Sweeping Time', 'sweeppress' ); ?></dt>
                            <dd><?php echo esc_html( ceil( $data['time'] ) ) . ' ' . esc_html__( 'seconds', 'sweeppress' ); ?></dd>
                        </dl>
                    </div>
				<?php } ?>
            </div>
        </div>
	<?php } ?>
</div>
