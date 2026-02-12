<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPDT_Settings' ) ) :

	final class WPDT_Settings {

		/**
		 * Initialize the class
		 *
		 * @return void
		 */
		public static function init() {

			// Save settings.
			add_action( 'wp_ajax_wpdt_save_general_settings', array( __CLASS__, 'save_general_settings' ) );

			// Reset settings.
			add_action( 'wp_ajax_wpdt_reset_general_settings', array( __CLASS__, 'reset_general_settings' ) );
		}

		/**
		 * Layout for settings page
		 *
		 * @return void
		 */
		public static function layout() {
			$settings = get_option( 'wpdt_settings' );
			?>
			<div class="wpdt-settings-page">
				<?php WPDT_Admin::load_setting_header_html(); ?>
				<div class="wpdt-settings-container">
					<div class="wpdt-general-setting-container">
						<h2>Settings</h2>
						<form class="wpdt-general-settings" onsubmit="return false;" action="#">
							<table class="wpdt-settings-table">
								<thead>
									<tr>
										<th>Action</th>
										<th>Constant</th>
										<th>Description</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>
											<label class="wpdt-toggle">
												<input type="checkbox" name="wp_debug" value="1" <?php checked( $settings['wp_debug'], 1 ); ?> />
												<span class="wpdt-slider"></span>
											</label>
										</td>
										<td>WP_DEBUG</td>
										<td>Enables debugging mode in WordPress.</td>
									</tr>
									<tr>
										<td>
											<label class="wpdt-toggle">
												<input type="checkbox" name="wp_debug_log" value="1" <?php checked( $settings['wp_debug_log'], 1 ); ?> />
												<span class="wpdt-slider"></span>
											</label>
										</td>
										<td>WP_DEBUG_LOG</td>
										<td>Logs debug messages to a file.</td>
									</tr>
									<tr>
										<td>
											<label class="wpdt-toggle">
												<input type="checkbox" name="wp_debug_display" value="1" <?php checked( $settings['wp_debug_display'], 1 ); ?> />
												<span class="wpdt-slider"></span>
											</label>
										</td>
										<td>WP_DEBUG_DISPLAY</td>
										<td>Controls whether debug messages are shown on the screen.</td>
									</tr>
									<tr>
										<td>
											<label class="wpdt-toggle">
												<input type="checkbox" name="script_debug" value="1" <?php checked( $settings['script_debug'], 1 ); ?> />
												<span class="wpdt-slider"></span>
											</label>
										</td>
										<td>SCRIPT_DEBUG</td>
										<td>Forces WordPress to load unminified CSS and JS files.</td>
									</tr>
								</tbody>
							</table>
							
							<div class="wpdt-input-group">
								<div class="label-container">
									<label for="log_date_timezone"><?php esc_attr_e( 'Log Timezone', 'debug-log-tool' ); ?></label>
								</div>
								<select name="log_date_timezone">
									<option <?php selected( $settings['log_date_timezone'], 'utc' ); ?> value="utc"><?php esc_attr_e( 'UTC', 'debug-log-tool' ); ?></option>
									<option <?php selected( $settings['log_date_timezone'], 'local' ); ?> value="local"><?php esc_attr_e( 'Local', 'debug-log-tool' ); ?></option>
								</select>
								<span>Choose how timestamps in logs are displayed. You can view them in UTC or in your site’s local WordPress timezone.</span>
							</div>

							<div class="wpdt-form-buttons">
								<button type="submit" name="submit" onclick="wpdt_save_general_settings(this)" class="wpdt-btn-primary">Save Settings</button>
								<button type="reset" onclick="wpdt_reset_general_settings(this, '<?php echo esc_attr( wp_create_nonce( 'wpdt_reset_general_settings' ) ); ?>')" class="wpdt-btn-secondary">Reset</button>
							</div>
							<input type="hidden" name="action" value="wpdt_save_general_settings"/>
							<input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpdt_save_general_settings' ) ); ?>">
						</form>
						<?php WPDT_Config_Manager::wpdt_config_writable_notice(); ?>
					</div>
				</div>
				<div id="wpdt-alert-container"></div>
			</div>
			<?php
		}

		/**
		 * Save general settings
		 *
		 * @return void
		 */
		public static function save_general_settings() {

			if ( check_ajax_referer( 'wpdt_save_general_settings', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorised request!', 401 );
			}

			if ( ! WPDT_Config_Manager::wpdt_is_wp_config_writable() ) {
				wp_send_json_error( 'wp-config.php is not writable!', 403 );
			}

			$timezone = isset( $_POST['log_date_timezone'] ) && in_array( $_POST['log_date_timezone'], array( 'utc', 'local' ), true ) ? sanitize_text_field( wp_unslash( $_POST['log_date_timezone'] ) ) : 'utc';

			$settings = array(
				'wp_debug'          => isset( $_POST['wp_debug'] ) ? 1 : 0,
				'wp_debug_log'      => isset( $_POST['wp_debug_log'] ) ? 1 : 0,
				'wp_debug_display'  => isset( $_POST['wp_debug_display'] ) ? 1 : 0,
				'script_debug'      => isset( $_POST['script_debug'] ) ? 1 : 0,
				'savequeries'       => isset( $_POST['savequeries'] ) ? 1 : 0,
				'log_date_timezone' => $timezone,
			);

			update_option( 'wpdt_settings', $settings );

			do_action( 'wpdt_save_general_settings', $settings );

			wp_send_json_success( array( 'message' => 'Settings saved successfully!' ) );
		}

		/**
		 * Reset general settings
		 *
		 * @return void
		 */
		public static function reset_general_settings() {

			if ( check_ajax_referer( 'wpdt_reset_general_settings', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorised request!', 401 );
			}

			if ( ! WPDT_Config_Manager::wpdt_is_wp_config_writable() ) {
				wp_send_json_error( 'wp-config.php is not writable!', 403 );
			}

			$settings = array(
				'wp_debug'          => 1,
				'wp_debug_log'      => 1,
				'wp_debug_display'  => 0,
				'script_debug'      => 0,
				'savequeries'       => 0,
				'log_date_timezone' => 'utc',
			);

			update_option( 'wpdt_settings', $settings );

			do_action( 'wpdt_reset_general_settings', $settings );

			wp_send_json_success( array( 'message' => 'Settings reset successfully!' ) );
		}
	}
endif;
WPDT_Settings::init();
