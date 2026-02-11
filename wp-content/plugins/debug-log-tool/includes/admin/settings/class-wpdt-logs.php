<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPDT_Logs' ) ) :

	final class WPDT_Logs {

		/**
		 * Initalize the class.
		 *
		 * @return void
		 */
		public static function init() {

			add_action( 'init', array( __CLASS__, 'download_debug_file' ), 100 );
			add_action( 'init', array( __CLASS__, 'reset_debug_file' ), 100 );

			// Get debug logs.
			add_action( 'wp_ajax_wpdt_get_debug_logs', array( __CLASS__, 'get_debug_logs' ) );

			// Set auto refresh.
			add_action( 'wp_ajax_wpdt_set_auto_refresh', array( __CLASS__, 'set_auto_refresh' ) );

			// Set group logs.
			add_action( 'wp_ajax_wpdt_set_group_logs', array( __CLASS__, 'set_group_logs' ) );
		}

		/**
		 * Layout for logs page
		 *
		 * @return void
		 */
		public static function layout() {
			$auto_refresh = get_option( 'wpdt_auto_refresh_status', 0 );
			$group_logs  = get_option( 'wpdt_group_logs_status', 0 );
			?>
			<div class="wpdt-settings-page">
				<?php WPDT_Admin::load_setting_header_html(); ?>
				<div class="wpdt-settings-container">
					<div class="wpdt-log-container">
						<div class="filter-container">
							<div class="filter-left">
									<label for="entriesPerPage">Entries per page:</label>
									<select id="entriesPerPage">
										<option value="20">20</option>
										<option value="50" selected="selected">50</option>
										<option value="100">100</option>
										<option value="200">200</option>
									</select>
							</div>
						
							<div class="filter-right">

									<input type="text" id="customSearch" placeholder="Search...">

									<select id="error-type-filter">
										<option value="">All</option>
										<option value="Fatal error">Fatal error</option>
										<option value="Warning">Warning</option>
										<option value="Notice">Notice</option>
										<option value="Deprecated">Deprecated</option>
										<option value="PHP Parse">PHP Parse</option>
										<option value="Exceptions">Exceptions</option>
										<option value="Database">Database</option>
										<option value="Other">Other</option>
									</select>
									<div class="wpdt-filter-divider"></div>
									<a target="_blank" id="wpdb-download-log"
										href="<?php echo esc_url( admin_url( 'admin.php?page=wpdt-debugging&action=download&nonce=' . wp_create_nonce( 'wpdt_download_nonce' ) ) ); ?>"
										class="wpdt-log-tbl-action">
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor">
											<path d="M288 32c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 242.7-73.4-73.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l128 128c12.5 12.5 32.8 12.5 45.3 0l128-128c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L288 274.7 288 32zM64 352c-35.3 0-64 28.7-64 64l0 32c0 35.3 28.7 64 64 64l384 0c35.3 0 64-28.7 64-64l0-32c0-35.3-28.7-64-64-64l-101.5 0-45.3 45.3c-25 25-65.5 25-90.5 0L165.5 352 64 352zm368 56a24 24 0 1 1 0 48 24 24 0 1 1 0-48z"/>
										</svg>
									</a>
									<a id="wpdb-clear-log"
										href="<?php echo esc_url( admin_url( 'admin.php?page=wpdt-debugging&action=reset&nonce=' . wp_create_nonce( 'wpdt_reset_nonce' ) ) ); ?>"
										class="wpdt-log-tbl-action">
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" fill="currentColor">
											<path d="M135.2 17.7L128 32 32 32C14.3 32 0 46.3 0 64S14.3 96 32 96l384 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-96 0-7.2-14.3C307.4 6.8 296.3 0 284.2 0L163.8 0c-12.1 0-23.2 6.8-28.6 17.7zM416 128L32 128 53.2 467c1.6 25.3 22.6 45 47.9 45l245.8 0c25.3 0 46.3-19.7 47.9-45L416 128z"/>
										</svg>
									</a>
									<div class="wpdt-filter-divider"></div>
									<a id="wpdb-refresh-log" href="#" class="wpdt-log-tbl-action"
										title="Refresh">
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor">
											<path d="M463.5 224l8.5 0c13.3 0 24-10.7 24-24l0-128c0-9.7-5.8-18.5-14.8-22.2s-19.3-1.7-26.2 5.2L413.4 96.6c-87.6-86.5-228.7-86.2-315.8 1c-87.5 87.5-87.5 229.3 0 316.8s229.3 87.5 316.8 0c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0c-62.5 62.5-163.8 62.5-226.3 0s-62.5-163.8 0-226.3c62.2-62.2 162.7-62.5 225.3-1L327 183c-6.9 6.9-8.9 17.2-5.2 26.2s12.5 14.8 22.2 14.8l119.5 0z"/>
										</svg>
									</a>
									<label class="wpdt-toggle">
										<input type="checkbox" id="wpdt_auto_refresh" name="wpdt_auto_refresh" value="0" <?php checked( $auto_refresh, 1 ); ?> />
										<span class="wpdt-slider"></span>
									</label> <b>Auto Refresh</b>
									<div class="wpdt-filter-divider"></div>
									<label class="wpdt-toggle">
										<input type="checkbox" id="wpdt_group_logs" name="wpdt_group_logs" value="0" <?php checked( $group_logs, 1 ); ?> />
										<span class="wpdt-slider"></span>
									</label> <b>Group Logs</b>
							</div>
						</div>
						<table id="debug-log-table" class="widefat striped">
							<thead>
									<tr>
										<th>Error Type</th>
										<th>Log</th>
										<th></th>
										<th>Help</th>
										<th>Error Date (UTC)</th>
									</tr>
							</thead>
						</table>
						<?php WPDT_Config_Manager::wpdt_config_writable_notice(); ?>
					</div>
				</div>
				<div id="wpdt-alert-container"></div>
			</div>
			<?php
		}

		/**
		 * Get debug logs
		 *
		 * @return void
		 */
		public static function get_debug_logs() {

			if ( check_ajax_referer( 'wpdt_nonce', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}

			$logs = WPDT_Debug_Reader::get_debug_logs( 200 );
			$data = array();
			foreach ( $logs as $key => $log ) {

				$is_long = strlen( $log['log'] ) > 300;
				ob_start();
				?>
				<div class="wpdt-log-value<?php echo $is_long ? '' : ' expanded'; ?>">
					<?php echo nl2br( esc_html( $log['log'] ) ); ?>
				</div>
				<?php
				if ( $is_long ) :
					?>
					<div class="wpdt-view-toggle">View More</div>
					<?php
				endif;
				$log_html = ob_get_clean();

				$copy_button = '<svg class="wpdt-copy-icon" data-log="' . esc_html( $log['log'] ) . '" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" title="Copy log" aria-label="Copy log" fill="currentColor">
				<path d="M384 336l-192 0c-8.8 0-16-7.2-16-16l0-256c0-8.8 7.2-16 16-16l140.1 0L400 115.9 400 320c0 8.8-7.2 16-16 16zM192 384l192 0c35.3 0 64-28.7 64-64l0-204.1c0-12.7-5.1-24.9-14.1-33.9L366.1 14.1c-9-9-21.2-14.1-33.9-14.1L192 0c-35.3 0-64 28.7-64 64l0 256c0 35.3 28.7 64 64 64zM64 128c-35.3 0-64 28.7-64 64L0 448c0 35.3 28.7 64 64 64l192 0c35.3 0 64-28.7 64-64l0-32-48 0 0 32c0 8.8-7.2 16-16 16L64 464c-8.8 0-16-7.2-16-16l0-256c0-8.8 7.2-16 16-16l32 0 0-48-32 0z"/>
				</svg>';

				// Help links.
				$log_text = wp_strip_all_tags( $log['log'] ); // remove HTML.
				$log_text = preg_replace( '/\s+/', ' ', $log_text ); // collapse whitespace/newlines.
				$log_text = trim( $log_text );

				// Prepare trimmed version for search (max 300 chars).
				$max_length = 300;
				$query_text = mb_substr( $log_text, 0, $max_length );
				$encoded_query = rawurlencode( $query_text );

				// Create URLs for Google, ChatGPT, and Gemini.
				$google_url = 'https://www.google.com/search?q=' . $encoded_query;
				$chatgpt_url = 'https://chat.openai.com/?q=' . $encoded_query;

				$help_links_html = '
        <div class="wpdt-help-links">
            <a class="wpdt-google-link" href="' . esc_url( $google_url ) . '" target="_blank" title="Search on Google">
              <svg class="wpdt-help-icon google" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 488 512">
								<path d="M488 261.8C488 403.3 391.1 504 248 504 110.8 504 0 393.2 0 256S110.8 8 248 8c66.8 0 123 24.5 166.3 64.9l-67.5 64.9C258.5 52.6 94.3 116.6 94.3 256c0 86.5 69.1 156.6 153.7 156.6 98.2 0 135-70.4 140.8-106.9H248v-85.3h236.1c2.3 12.7 3.9 24.9 3.9 41.4z"/>
							</svg>
            </a>
            <a class="wpdt-chatgpt-link" href="' . esc_url( $chatgpt_url ) . '" target="_blank" title="Ask ChatGPT">
							<svg class="wpdt-help-icon chatgpt" fill="currentColor" xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 50 50" width="50px" height="50px">
								<path d="M45.403,25.562c-0.506-1.89-1.518-3.553-2.906-4.862c1.134-2.665,0.963-5.724-0.487-8.237	c-1.391-2.408-3.636-4.131-6.322-4.851c-1.891-0.506-3.839-0.462-5.669,0.088C28.276,5.382,25.562,4,22.647,4	c-4.906,0-9.021,3.416-10.116,7.991c-0.01,0.001-0.019-0.003-0.029-0.002c-2.902,0.36-5.404,2.019-6.865,4.549	c-1.391,2.408-1.76,5.214-1.04,7.9c0.507,1.891,1.519,3.556,2.909,4.865c-1.134,2.666-0.97,5.714,0.484,8.234	c1.391,2.408,3.636,4.131,6.322,4.851c0.896,0.24,1.807,0.359,2.711,0.359c1.003,0,1.995-0.161,2.957-0.45	C21.722,44.619,24.425,46,27.353,46c4.911,0,9.028-3.422,10.12-8.003c2.88-0.35,5.431-2.006,6.891-4.535	C45.754,31.054,46.123,28.248,45.403,25.562z M35.17,9.543c2.171,0.581,3.984,1.974,5.107,3.919c1.049,1.817,1.243,4,0.569,5.967	c-0.099-0.062-0.193-0.131-0.294-0.19l-9.169-5.294c-0.312-0.179-0.698-0.177-1.01,0.006l-10.198,6.041l-0.052-4.607l8.663-5.001	C30.733,9.26,33,8.963,35.17,9.543z M29.737,22.195l0.062,5.504l-4.736,2.805l-4.799-2.699l-0.062-5.504l4.736-2.805L29.737,22.195z M14.235,14.412C14.235,9.773,18.009,6,22.647,6c2.109,0,4.092,0.916,5.458,2.488C28,8.544,27.891,8.591,27.787,8.651l-9.17,5.294	c-0.312,0.181-0.504,0.517-0.5,0.877l0.133,11.851l-4.015-2.258V14.412z M6.528,23.921c-0.581-2.17-0.282-4.438,0.841-6.383	c1.06-1.836,2.823-3.074,4.884-3.474c-0.004,0.116-0.018,0.23-0.018,0.348V25c0,0.361,0.195,0.694,0.51,0.872l10.329,5.81	L19.11,34.03l-8.662-5.002C8.502,27.905,7.11,26.092,6.528,23.921z M14.83,40.457c-2.171-0.581-3.984-1.974-5.107-3.919	c-1.053-1.824-1.249-4.001-0.573-5.97c0.101,0.063,0.196,0.133,0.299,0.193l9.169,5.294c0.154,0.089,0.327,0.134,0.5,0.134	c0.177,0,0.353-0.047,0.51-0.14l10.198-6.041l0.052,4.607l-8.663,5.001C19.269,40.741,17.001,41.04,14.83,40.457z M35.765,35.588	c0,4.639-3.773,8.412-8.412,8.412c-2.119,0-4.094-0.919-5.459-2.494c0.105-0.056,0.216-0.098,0.32-0.158l9.17-5.294	c0.312-0.181,0.504-0.517,0.5-0.877L31.75,23.327l4.015,2.258V35.588z M42.631,32.462c-1.056,1.83-2.84,3.086-4.884,3.483	c0.004-0.12,0.018-0.237,0.018-0.357V25c0-0.361-0.195-0.694-0.51-0.872l-10.329-5.81l3.964-2.348l8.662,5.002	c1.946,1.123,3.338,2.937,3.92,5.107C44.053,28.249,43.754,30.517,42.631,32.462z"/>
							</svg>
            </a>
            <a class="wpdt-gemini-link" data-log="' . esc_attr( $log_text ) . '" target="_blank" title="Ask Gemini">
							<svg class="wpdt-help-icon gemini" fill="currentColor" fill-rule="evenodd" height="1em" style="flex:none;line-height:1" viewBox="0 0 24 24" width="1em" xmlns="http://www.w3.org/2000/svg">
								<path d="M12 24A14.304 14.304 0 000 12 14.304 14.304 0 0012 0a14.305 14.305 0 0012 12 14.305 14.305 0 00-12 12"></path>
							</svg>
            </a>
        </div>
    		';

				$data[] = array(
					'type'  => esc_html( $log['type'] ),
					'log'   => $log_html,
					'copy'  => $copy_button,
					'links' => $help_links_html,
					'date'  => esc_html( $log['date'] ) . '<br> <span>(' . $log['occurences'] . ' Occurences)</span>',
				);
			}

			$response = array(
				'draw'                 => 1,
				'iTotalRecords'        => count( $logs ),
				'iTotalDisplayRecords' => count( $logs ),
				'data'                 => $data,
			);
			wp_send_json( $response );
		}

		/**
		 * Download debug file
		 *
		 * @return void
		 */
		public static function download_debug_file() {

			if ( ! isset( $_GET['page'] ) || 'wpdt-debugging' !== $_GET['page'] || ! isset( $_GET['action'] ) || 'download' !== $_GET['action'] ) {
				return;
			}

			$nonce = isset( $_GET['nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['nonce'] ) ) : '';
			if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( $nonce, 'wpdt_download_nonce' ) ) {
				return;
			}

			$file_path = WPDT_Debug_Reader::get_debug_log_path();
			if ( ! file_exists( $file_path ) ) {
				echo 'File does not exists!';
				exit;
			}

			if ( ob_get_length() > 0 ) {
				ob_clean();
			}

			header( 'Content-Description: File Transfer' );
			header( 'Cache-Control: public' );
			header( 'Content-Type: application/force-download' );
			header( 'Content-Disposition: attachment;filename="debug.log"' );
			header( 'Content-Length: ' . filesize( $file_path ) );
			flush();
			readfile( $file_path ); // phpcs:ignore
			exit( 0 );
		}

		/**
		 * Reset debug file
		 *
		 * @return void
		 */
		public static function reset_debug_file() {

			if ( ! isset( $_GET['page'] ) || 'wpdt-debugging' !== $_GET['page'] || ! isset( $_GET['action'] ) || 'reset' !== $_GET['action'] ) {
				return;
			}

			$nonce = isset( $_GET['nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['nonce'] ) ) : '';
			if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( $nonce, 'wpdt_reset_nonce' ) ) {
				return;
			}

			$file_path = WPDT_Debug_Reader::get_debug_log_path();
			if ( ! file_exists( $file_path ) ) {
				exit;
			}

			file_put_contents( $file_path, '' ); //phpcs:ignore
			wp_safe_redirect( admin_url( 'admin.php?page=wpdt-debugging' ) );
		}

		/**
		 * Set auto refresh
		 *
		 * @return void
		 */
		public static function set_auto_refresh() {
			$nonce = isset( $_POST['_ajax_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_ajax_nonce'] ) ) : '';
			if ( ! isset( $_POST['_ajax_nonce'] ) || ! wp_verify_nonce( $nonce, 'wpdt_nonce' ) ) {
				wp_send_json_error( array( 'message' => __( 'Invalid nonce', 'debug-log-tool' ) ) );
			}

			$auto_refresh = isset( $_POST['auto_refresh'] ) ? sanitize_text_field( wp_unslash( $_POST['auto_refresh'] ) ) : 0;
			update_option( 'wpdt_auto_refresh_status', $auto_refresh );
			wp_send_json_success();
		}

		/**
		 * Set group logs
		 *
		 * @return void
		 */
		public static function set_group_logs() {
			$nonce = isset( $_POST['_ajax_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_ajax_nonce'] ) ) : '';
			if ( ! isset( $_POST['_ajax_nonce'] ) || ! wp_verify_nonce( $nonce, 'wpdt_nonce' ) ) {
				wp_send_json_error( array( 'message' => __( 'Invalid nonce', 'debug-log-tool' ) ) );
			}

			$group_logs = isset( $_POST['group_logs'] ) ? sanitize_text_field( wp_unslash( $_POST['group_logs'] ) ) : 0;
			update_option( 'wpdt_group_logs_status', $group_logs );
			wp_send_json_success();
		}
	}
endif;
WPDT_Logs::init();
