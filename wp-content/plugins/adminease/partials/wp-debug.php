<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables, not globals
defined( 'ABSPATH' ) || exit;

use AdminEase\FileHandler;
use AdminEase\Plugin;

$file_handler                    = FileHandler::get_instance();
$debug_log_path                  = $file_handler->get_debug_log_path();
$settings                        = Plugin::get_settings( 'debug' );
$debug_log_auto_refresh_interval = $settings['debug_log_auto_refresh_interval'] ?? 10;

// Get server limits and file info
$server_limits = $file_handler->get_server_limits();
$file_info     = $file_handler->get_debug_log_info();
?>
<div class="col-xs-12 p-t-20">
	<div id="debug-log-container" data-parent="wp-debug">
		<p><strong><?php esc_html_e( 'Debug log viewer', 'adminease' ); ?></strong></p>
		
		<!-- Server Limits and Warnings -->
		<div class="debug-log-info m-b-10">
			<div class="row">
				<div class="col-xs-12">
					<p class="server-limit-info">
						<span class="dashicons dashicons-admin-settings"></span> <strong><?php esc_html_e( 'PHP Memory Limit:', 'adminease' ); ?></strong> <?php echo esc_html( size_format( $server_limits['memory_limit'] ) ); ?>
					</p>
					
					<p class="server-limit-info">
						<span class="dashicons dashicons-media-document"></span> <strong><?php esc_html_e( 'Debug Log Size:', 'adminease' ); ?></strong> <span id="debug-log-size"><?php echo esc_html( $file_info['size_formatted'] ); ?></span>
						<?php
						if( $file_info['exists'] && $file_info['percentage'] > 0 ) {
							?>
							<span class="size-percentage" data-percentage="<?php echo esc_attr( $file_info['percentage'] ); ?>">
								(<?php echo esc_html( number_format( $file_info['percentage'], 1 ) ); ?>% <?php esc_html_e( 'of memory limit', 'adminease' ); ?>)
							</span>
							<?php
						}
						?>
					</p>
				</div>
			</div>
			
			<?php
			if( $file_info['warning'] ) {
				?>
				<div class="alert alert-warning" id="debug-log-warning">
					<?php
					printf(
					/* translators: 1: current file size, 2: percentage of memory limit */
						esc_html__( 'Debug log is getting large (%1$s, %2$s%% of PHP memory limit). Consider clearing it or WP_DEBUG will be automatically disabled when it reaches 90%% of the memory limit.', 'adminease' ),
						esc_html( $file_info['size_formatted'] ),
						esc_html( number_format( $file_info['percentage'], 1 ) )
					);
					?>
				</div>
				<?php
			}
			?>
			
			<?php
			if( $file_info['critical'] ) {
				?>
				<div class="alert alert-danger" id="debug-log-critical">
					<?php esc_html_e( 'Debug log has exceeded safe limits. WP_DEBUG settings have been automatically disabled to prevent server issues. Please clear the debug log.', 'adminease' ); ?>
				</div>
				<?php
			}
			?>
		</div>
		
		<div class="actions m-b-10">
			<div class="row">
				<div class="col-sm-7 col-xs-12">
					<button type="button" class="button button-secondary button-small" id="refresh-debug-log"><?php esc_html_e( 'Refresh', 'adminease' ); ?></button>
					
					<button type="button" class="button button-secondary button-small" id="clear-debug-log"><?php esc_html_e( 'Clear', 'adminease' ); ?></button>
					
					<button type="button" class="button button-secondary button-small auto-refresh-toggle" data-action="wp-debug" data-interval="<?php echo esc_attr( $debug_log_auto_refresh_interval ); ?>">
						<input type="checkbox" id="auto-refresh-toggle-debug">
						<?php
						/* translators: %s: number of seconds for the auto-refresh interval */ ?>
						<label for="auto-refresh-toggle-debug"><?php echo sprintf( esc_html__( 'Auto-refresh every %s seconds', 'adminease' ), esc_html( $debug_log_auto_refresh_interval ) ); ?></label>
					</button>
				</div>
				
				<div class="col-sm-5 col-xs-12 end-xs bottom-xs">
					<button type="button" class="button button-secondary button-small inline-flex justify-content-center align-items-center gap-5" id="download-debug-log">
						<a href="javascript:void(0);" target="_blank" rel="noopener"><?php esc_html_e( 'Download debug.log', 'adminease' ); ?></a> <span class="dashicons dashicons-download" id="download-debug-log-icon"></span>
					</button>
				</div>
			</div>
		</div>
		
		<pre><?php esc_html_e( 'Click on the Refresh button to see the current debug.log', 'adminease' ); ?></pre>
	</div>
</div>