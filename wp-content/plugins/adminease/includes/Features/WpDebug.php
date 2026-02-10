<?php
namespace AdminEase\Features;

use AdminEase\Plugin;
use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Handles the WordPress debug configuration based on plugin settings.
 */
class WpDebug {
	private array $settings;
	
	public function __construct() {
		$this->settings = Plugin::get_settings( 'debug' );
		
		$this->check_and_disable_if_critical();
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		add_filter( 'adminease_localize_script', [ $this, 'adminease_localize_script' ] );
		
		add_action( 'adminease_settings_saved', [ $this, 'adminease_settings_saved' ] );
		add_action( 'adminease_after_field_render', [ $this, 'adminease_after_field_render' ] );
		
		add_action( 'wp_ajax_adminease_get_debug_log', [ $this, 'ajax_get_debug_log' ] );
		add_action( 'wp_ajax_adminease_clear_debug_log', [ $this, 'ajax_clear_debug_log' ] );
		add_action( 'wp_ajax_adminease_download_debug_log', [ $this, 'ajax_download_debug_log' ] );
	}
	
	/**
	 * Check debug log size and automatically disable WP_DEBUG if approaching critical limits.
	 *
	 * @return void
	 */
	private function check_and_disable_if_critical(): void {
		// Only check if WP_DEBUG is currently enabled
		if( empty( $this->settings['wp_debug'] ) ) {
			return;
		}
		
		// Check if FileHandler is available before proceeding
		if( null === Plugin::$FileHandler ) {
			return;
		}
		
		$file_handler = Plugin::$FileHandler;
		$file_info    = $file_handler->get_debug_log_info();
		
		// Auto-disable at 90% of memory limit
		if( $file_info['critical'] ) {
			$this->disable_debug_mode();
			
			// Add admin notice to inform user
			add_action( 'admin_notices', function() use ( $file_info ) {
				?>
				<div class="notice notice-error is-dismissible">
					<p>
						<strong><?php esc_html_e( 'AdminEase - WP Debug Auto-Disabled', 'adminease' ); ?></strong><br>
						<?php
						printf(
						/* translators: 1: file size, 2: percentage */
							esc_html__( 'The debug.log file has reached %1$s (%2$s%% of PHP memory limit). WP_DEBUG settings have been automatically disabled to prevent server issues. Please clear the debug log before re-enabling.', 'adminease' ),
							esc_html( $file_info['size_formatted'] ),
							esc_html( number_format( $file_info['percentage'], 1 ) )
						);
						?>
					</p>
				</div>
				<?php
			} );
		}
	}
	
	/**
	 * Disable WP_DEBUG settings when log file exceeds safe limits.
	 *
	 * @return void
	 */
	private function disable_debug_mode(): void {
		if( null === Plugin::$FileHandler ) {
			return;
		}
		
		$file_handler = Plugin::$FileHandler;
		
		// Update settings in memory
		$this->settings['wp_debug']         = false;
		$this->settings['wp_debug_log']     = false;
		$this->settings['wp_debug_display'] = false;
		
		// Update settings in database
		$all_settings          = get_option( 'adminease', [] );
		$all_settings['debug'] = $this->settings;
		update_option( 'adminease', $all_settings );
		
		// Update wp-config.php constants
		$file_handler->stack_wp_config_constant( 'WP_DEBUG', false );
		$file_handler->stack_wp_config_constant( 'WP_DEBUG_LOG', false );
		$file_handler->stack_wp_config_constant( 'WP_DEBUG_DISPLAY', false );
	}
	
	/**
	 * Adds custom settings fields related to debugging to the Adminease security settings section.
	 *
	 * @param array $fields The original array of settings fields before modification.
	 *
	 * @return array The modified array of settings fields with added debugging options.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$fields['debug']['fields'][] = [
			'type'         => 'switch',
			'id'           => 'wp-debug',
			'name'         => 'adminease[debug][wp_debug]',
			'value'        => $this->settings['wp_debug'] ?? false,
			'label_class'  => 'adminease-switch',
			'input_class'  => 'form-control toggle-field',
			'label'        => __( 'WP Debug', 'adminease' ),
			'description'  => __( 'The <strong>WP_DEBUG</strong> setting in WordPress is used to turn on debugging mode. When enabled, it displays PHP errors, warnings, and notices directly on your site—helping developers and site owners identify problems during development or troubleshooting. It’s especially useful when plugins or themes aren’t working as expected. However, it should <strong>never be left on in a live site</strong> as it can expose sensitive information to visitors.', 'adminease' ),
			'child_fields' => [
				[
					'type'          => 'switch',
					'id'            => 'wp-debug-log',
					'name'          => 'adminease[debug][wp_debug_log]',
					'value'         => $this->settings['wp_debug_log'] ?? false,
					'label_class'   => 'adminease-switch',
					'input_class'   => 'form-control',
					'wrapper_class' => 'form-group-child',
					'label'         => __( 'WP Debug Log', 'adminease' ),
					'description'   => __( 'Enable WP Debug Log.', 'adminease' ),
					'attributes'    => [
						'data-parent' => 'wp-debug',
					],
				],
				[
					'type'          => 'switch',
					'id'            => 'wp-debug-display',
					'name'          => 'adminease[debug][wp_debug_display]',
					'value'         => $this->settings['wp_debug_display'] ?? false,
					'label_class'   => 'adminease-switch',
					'input_class'   => 'form-control',
					'wrapper_class' => 'form-group-child',
					'label'         => __( 'WP Debug Display', 'adminease' ),
					'description'   => __( 'Enable WP Debug Display.', 'adminease' ),
					'attributes'    => [
						'data-parent' => 'wp-debug',
					],
				],
				[
					'type'          => 'select',
					'id'            => 'debug-log-auto-refresh-interval',
					'name'          => 'adminease[debug][debug_log_auto_refresh_interval]',
					'value'         => $this->settings['debug_log_auto_refresh_interval'] ?? '10',
					'label_class'   => 'adminease-label',
					'input_class'   => 'form-control',
					'wrapper_class' => 'form-group-child',
					'label'         => __( 'Debug Log Auto-Refresh Interval', 'adminease' ),
					'description'   => __( 'Set the interval for auto-refreshing the debug log viewer.', 'adminease' ),
					'options'       => [
						/* translators: %d: number of seconds for the auto-refresh interval */
						'3'  => sprintf( __( 'Every %d seconds', 'adminease' ), 3 ),
						/* translators: %d: number of seconds for the auto-refresh interval */
						'5'  => sprintf( __( 'Every %d seconds', 'adminease' ), 5 ),
						/* translators: %d: number of seconds for the auto-refresh interval */
						'10' => sprintf( __( 'Every %d seconds', 'adminease' ), 10 ),
						/* translators: %d: number of seconds for the auto-refresh interval */
						'30' => sprintf( __( 'Every %d seconds', 'adminease' ), 30 ),
						/* translators: %d: number of seconds for the auto-refresh interval */
						'60' => sprintf( __( 'Every %d seconds', 'adminease' ), 60 ),
					],
					'attributes'    => [
						'data-parent' => 'wp-debug',
					],
				],
				[
					'type'          => 'select',
					'id'            => 'debug-log-lines-to-show',
					'name'          => 'adminease[debug][debug_log_lines_to_show]',
					'value'         => $this->settings['debug_log_lines_to_show'] ?? '1000',
					'label_class'   => 'adminease-label',
					'input_class'   => 'form-control',
					'wrapper_class' => 'form-group-child',
					'label'         => __( 'Debug Log Lines to Show', 'adminease' ),
					'description'   => __( 'Number of most recent lines to display in the debug log viewer (showing fewer lines improves performance).', 'adminease' ),
					'options'       => [
						'1000'   => __( '1000 lines (faster)', 'adminease' ),
						'10000'  => __( '10,000 lines (recommended)', 'adminease' ),
						'50000'  => __( '50,000 lines', 'adminease' ),
						'100000' => __( '100,000 lines (slower)', 'adminease' ),
						'250000' => __( '250,000 lines (slowest)', 'adminease' ),
						'500000' => __( '500,000 lines (hell)', 'adminease' ),
					],
					'attributes'    => [
						'data-parent' => 'wp-debug',
					],
				],
			],
		];
		
		return $fields;
	}
	
	/**
	 * Localizes script data by adding security nonces and internationalized strings.
	 *
	 * @param array $data An array used for script localization. It can be updated with additional localized data.
	 *
	 * @return array Returns an array containing localized data including security nonces and internationalized strings.
	 */
	public function adminease_localize_script( array $data ): array {
		$data['security']['refreshDebugLog']  = wp_create_nonce( 'adminease_get_debug_log' );
		$data['security']['clearDebugLog']    = wp_create_nonce( 'adminease_clear_debug_log' );
		$data['security']['downloadDebugLog'] = wp_create_nonce( 'adminease_download_debug_log' );
		
		$data['i18n']['confirmClearDebugLog']  = esc_html__( 'Are you sure you want to clear the debug log?', 'adminease' );
		$data['i18n']['debugLogEmpty']         = esc_html__( 'Debug log is empty.', 'adminease' );
		$data['i18n']['debugLogRefreshError']  = esc_html__( 'Failed to get debug log. Refresh the page and try again.', 'adminease' );
		$data['i18n']['debugLogTruncatedInfo'] = esc_html__( 'Info:', 'adminease' );
		/* translators: 1: number of lines shown, 2: total number of lines, 3: file size */
		$data['i18n']['debugLogTruncatedMessage'] = wp_kses( sprintf( __( 'Showing the last %1$s lines of %2$s total lines. File size: %3$s. Use the <strong>Download debug.log</strong> button to view the complete file.', 'adminease' ), '<strong>', '</strong>', '<strong>', '</strong>', '<strong>', '</strong>' ), array( 'strong' => [] ) );
		$data['i18n']['debugLogCritical']         = esc_html__( 'Critical:', 'adminease' );
		$data['i18n']['debugLogCriticalMessage']  = esc_html__( 'Debug log has exceeded safe limits. WP_DEBUG settings have been automatically disabled. Please clear the debug log.', 'adminease' );
		$data['i18n']['debugLogWarning']          = esc_html__( 'Warning:', 'adminease' );
		/* translators: 1: file size, 2: percentage of memory limit */
		$data['i18n']['debugLogWarningMessage'] = esc_html__( 'Debug log is getting large (%s, %s%% of PHP memory limit). Consider clearing it or WP_DEBUG will be automatically disabled at 90%%.', 'adminease' );
		$data['i18n']['debugLogOfMemoryLimit']  = esc_html__( 'of memory limit', 'adminease' );
		$data['i18n']['debugLogDownloadFailed'] = esc_html__( 'Failed to download debug log. Please try again.', 'adminease' );
		
		return $data;
	}
	
	/**
	 * Saves the sanitized settings and updates WordPress configuration constants based on provided settings.
	 *
	 * @param array $sanitized_settings An array of sanitized settings which includes debug options such as 'wp_debug', 'wp_debug_log', and 'wp_debug_display'.
	 *
	 * @return void
	 */
	public function adminease_settings_saved( array $sanitized_settings ): void {
		$file_handler = Plugin::$FileHandler;
		
		$file_handler->stack_wp_config_constant( 'WP_DEBUG', (bool) $sanitized_settings['debug']['wp_debug'] );
		$file_handler->stack_wp_config_constant( 'WP_DEBUG_LOG', (bool) $sanitized_settings['debug']['wp_debug_log'] );
		$file_handler->stack_wp_config_constant( 'WP_DEBUG_DISPLAY', (bool) $sanitized_settings['debug']['wp_debug_display'] );
	}
	
	/**
	 * Handles the after render logic for the specified field.
	 *
	 * @param array $field The field information, including the 'id' property used for conditional logic.
	 *
	 * @return void
	 */
	public function adminease_after_field_render( array $field ): void {
		if( 'wp-debug' !== $field['id'] ) {
			return;
		}
		
		$settings = $this->settings;
		
		include_once ADMINEASE_DIR . 'partials/wp-debug.php';
	}
	
	/**
	 * Handles the AJAX request to get and retrieve the contents of the debug log file.
	 * Verifies user permissions and nonce for security.
	 *
	 * @return void
	 */
	public function ajax_get_debug_log(): void {
		// Verify nonce
		if( !isset( $_POST['security'] ) || !wp_verify_nonce( sanitize_key( wp_unslash( $_POST['security'] ) ), 'adminease_get_debug_log' ) ) {
			wp_send_json_error( new WP_Error( 'security_error', esc_html__( 'An error occurred. Refresh the page and try again.', 'adminease' ) ) );
		}
		
		// Check user capabilities
		if( !current_user_can( 'manage_options' ) ) {
			wp_send_json_error( new WP_Error( 'permissions', esc_html__( 'You do not have sufficient permissions to perform this action', 'adminease' ) ) );
		}
		
		$file_handler = Plugin::$FileHandler;
		$path         = $file_handler->get_debug_log_path();
		
		if( !is_readable( $path ) ) {
			wp_send_json_error( new WP_Error( 'file_read_error', esc_html__( 'Debug log file is not readable.', 'adminease' ) ) );
		}
		
		// Get number of lines to read (default: 1000, allow custom via POST)
		$lines = isset( $_POST['lines'] ) ? max( 100, min( 10000, intval( $_POST['lines'] ) ) ) : 1000;
		
		// Read with tail method (much faster for large files)
		$result = $file_handler->read_debug_log_tail( $lines );
		
		// Get file info for warnings
		$file_info = $file_handler->get_debug_log_info();
		
		wp_send_json_success( [
			'contents'      => esc_html( $result['content'] ),
			'truncated'     => $result['truncated'],
			'lines_shown'   => $result['lines'],
			'total_lines'   => $result['total_lines'],
			'file_size'     => size_format( $result['file_size'] ),
			'file_size_raw' => $result['file_size'],
			'warning'       => $file_info['warning'],
			'critical'      => $file_info['critical'],
			'percentage'    => number_format( $file_info['percentage'], 1 ),
		] );
	}
	
	/**
	 * Handles an AJAX request to clear the debug log.
	 * Checks user capabilities and verifies the security nonce before clearing
	 * the debug log. Responds with a success or error message based on the
	 * operation result.
	 *
	 * @return void
	 */
	public function ajax_clear_debug_log(): void {
		// Verify nonce
		if( !isset( $_POST['security'] ) || !wp_verify_nonce( sanitize_key( wp_unslash( $_POST['security'] ) ), 'adminease_clear_debug_log' ) ) {
			wp_send_json_error( esc_html__( 'An error occurred. Refresh the page and try again.', 'adminease' ), 403 );
		}
		
		// Check user capabilities
		if( !current_user_can( 'manage_options' ) ) {
			wp_send_json_error( new WP_Error( 'permissions', esc_html__( 'You do not have sufficient permissions to perform this action', 'adminease' ) ) );
		}
		
		$file_handler = Plugin::$FileHandler;
		$result       = $file_handler->clear_debug_log();
		
		if( $result ) {
			wp_send_json_success( esc_html__( 'Debug log cleared successfully.', 'adminease' ) );
		}
		else {
			wp_send_json_error( new WP_Error( 'clear_error', esc_html__( 'Failed to clear debug log.', 'adminease' ) ) );
		}
	}
	
	public function ajax_download_debug_log(): void {
		// Verify nonce
		if( !isset( $_POST['security'] ) || !wp_verify_nonce( sanitize_key( wp_unslash( $_POST['security'] ) ), 'adminease_download_debug_log' ) ) {
			wp_send_json_error( esc_html__( 'An error occurred. Refresh the page and try again.', 'adminease' ), 403 );
		}
		
		// Check user capabilities
		if( !current_user_can( 'manage_options' ) ) {
			wp_send_json_error( new WP_Error( 'permissions', esc_html__( 'You do not have sufficient permissions to perform this action', 'adminease' ) ) );
		}
		
		$file_handler = Plugin::$FileHandler;
		$path         = $file_handler->get_debug_log_path();
		
		if( !is_readable( $path ) ) {
			wp_send_json_error( new WP_Error( 'file_read_error', esc_html__( 'Debug log file is not readable.', 'adminease' ) ) );
		}
		
		$content = $file_handler->read_debug_log();
		
		if( false === $content ) {
			wp_send_json_error( new WP_Error( 'file_read_error', esc_html__( 'Debug log file does not exist.', 'adminease' ) ) );
		}
		
		// Send the file content directly with proper headers
		header( 'Content-Type: text/plain' );
		header( 'Content-Disposition: attachment; filename="debug.log"' );
		header( 'Content-Length: ' . strlen( $content ) );
		header( 'Cache-Control: no-cache, no-store, must-revalidate' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );
		
		echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}
}