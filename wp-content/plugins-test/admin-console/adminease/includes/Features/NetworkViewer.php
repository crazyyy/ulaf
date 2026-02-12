<?php
namespace AdminEase\Features;

use AdminEase\Plugin;
use AdminEase\Utils;
use UAParser\Parser;
use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Class NetworkViewer
 * This class manages the functionality to capture, store, retrieve, and manage network connection logs for a WordPress plugin.
 */
class NetworkViewer {
	private array $settings;
	private string $table_name;
	
	public function __construct() {
		global $wpdb;
		
		$this->table_name = $wpdb->prefix . 'adminease_network_viewer_log';
		$this->settings   = Plugin::get_settings( 'debug' );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
		add_action( 'adminease_settings_saved', [ $this, 'adminease_settings_saved' ] );
		add_action( 'adminease_after_field_render', [ $this, 'adminease_after_field_render' ] );
		
		if( !empty( $this->settings['network_viewer_enabled'] ) ) {
			$this->create_table();
			
			add_action( 'init', [ $this, 'init' ], 1 );
			add_action( 'adminease_cleanup_network_viewer_logs', [ $this, 'cleanup_old_logs' ] );
			
			add_action( 'wp_ajax_adminease_get_network_viewer_log', [ $this, 'ajax_get_network_viewer_log' ] );
			add_action( 'wp_ajax_adminease_clear_network_viewer_log', [ $this, 'ajax_clear_network_viewer_log' ] );
			
			if( !wp_next_scheduled( 'adminease_cleanup_network_viewer_logs' ) ) {
				wp_schedule_event( time(), 'hourly', 'adminease_cleanup_network_viewer_logs' );
			}
		}
	}
	
	/**
	 * Updates the settings fields array with additional configuration options for the AdminEase plugin.
	 *
	 * @param array $fields Associative array of existing settings fields. Each key represents a settings section, and its value is an array of fields under that section.
	 *                      - A new set of fields is added under the 'debug' section, with each field containing parameters such as:
	 *                        - type: string Field input type (e.g., 'switch', 'number', 'select', 'textarea').
	 *                        - id: string Unique identifier for the field.
	 *                        - name: string HTML name attribute for the field.
	 *                        - value: mixed Current value of the field, or a default value if not set.
	 *                        - label_class: string CSS class for the field label.
	 *                        - input_class: string CSS class for the input element.
	 *                        - wrapper_class: string CSS class for the field wrapper (optional).
	 *                        - label: string Field label text.
	 *                        -*/
	public function adminease_settings_fields( array $fields ): array {
		$max_entries = apply_filters( 'adminease_default_network_viewer_max_entries', $this->settings['network_viewer_max_entries'] ?? '1000' );
		
		$fields['debug']['fields'][] = [
			'type'         => 'switch',
			'id'           => 'network-viewer-enabled',
			'name'         => 'adminease[debug][network_viewer_enabled]',
			'value'        => $this->settings['network_viewer_enabled'] ?? '',
			'label_class'  => 'adminease-switch',
			'input_class'  => 'form-control toggle-field',
			'label'        => __( 'Network Viewer', 'adminease' ),
			'description'  => __( "Real-time HTTP request logging. See <strong>exactly</strong> who's connecting to your site, where they're from, what they're accessing, and when. <strong>Identify security threats before they become problems</strong>, debug connection issues with precision, and gain valuable insights into visitor behavior, all from a single, powerful interface.", 'adminease' ),
			'child_fields' => [
				[
					'type'          => 'number',
					'id'            => 'network-viewer-max-entries',
					'name'          => 'adminease[debug][network_viewer_max_entries]',
					'value'         => $max_entries,
					'label_class'   => '',
					'input_class'   => 'form-control',
					'wrapper_class' => 'form-group-child',
					'label'         => __( 'Maximum Log Entries', 'adminease' ),
					'description'   => __( 'Maximum number of entries to keep in the log (100-100000).', 'adminease' ),
					'attributes'    => [
						'data-parent' => 'network-viewer-enabled',
						'min'         => '100',
						'max'         => $max_entries,
					],
				],
				[
					'type'          => 'select',
					'id'            => 'network-viewer-auto-refresh-interval',
					'name'          => 'adminease[debug][network_viewer_auto_refresh_interval]',
					'value'         => $this->settings['network_viewer_auto_refresh_interval'] ?? '10',
					'label_class'   => 'adminease-label',
					'input_class'   => 'form-control',
					'wrapper_class' => 'form-group-child',
					'label'         => __( 'Network Viewer Log Auto-Refresh Interval', 'adminease' ),
					'description'   => __( 'Set the interval for auto-refreshing the network viewer viewer.', 'adminease' ),
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
						'data-parent' => 'network-viewer-enabled',
					],
				],
				[
					'type'          => 'select',
					'id'            => 'network-viewer-auto-clear',
					'name'          => 'adminease[debug][network_viewer_auto_clear]',
					'value'         => $this->settings['network_viewer_auto_clear'] ?? '24_hours',
					'label_class'   => '',
					'input_class'   => 'form-control',
					'wrapper_class' => 'form-group-child',
					'label'         => __( 'Auto Clear Logs After', 'adminease' ),
					'description'   => __( 'Automatically delete logs older than this period.', 'adminease' ),
					'options'       => [
						'1_hour'   => __( '1 Hour', 'adminease' ),
						'24_hours' => __( '24 Hours', 'adminease' ),
						'7_days'   => __( '7 Days', 'adminease' ),
						'never'    => __( 'Never', 'adminease' ),
					],
					'attributes'    => [
						'data-parent' => 'network-viewer-enabled',
					],
				],
				[
					'type'          => 'textarea',
					'id'            => 'network-viewer-exclude-ips',
					'name'          => 'adminease[debug][network_viewer_exclude_ips]',
					'value'         => str_replace( ' ', PHP_EOL, ( $this->settings['network_viewer_exclude_ips'] ?? '' ) ),
					'label_class'   => '',
					'input_class'   => 'form-control',
					'wrapper_class' => 'form-group-child',
					'label'         => __( 'Exclude IP Addresses', 'adminease' ),
					'description'   => __( 'Enter IP addresses to exclude from logging (one per line).', 'adminease' ),
					'attributes'    => [
						'data-parent' => 'network-viewer-enabled',
						'rows'        => '4',
					],
				],
				[
					'type'          => 'switch',
					'id'            => 'network-viewer-log-ajax',
					'name'          => 'adminease[debug][network_viewer_log_ajax]',
					'value'         => $this->settings['network_viewer_log_ajax'] ?? '',
					'label_class'   => 'adminease-switch',
					'input_class'   => 'form-control',
					'wrapper_class' => 'form-group-child',
					'label'         => __( 'Log WP AJAX Requests', 'adminease' ),
					'description'   => __( 'Enable logging of WordPress AJAX requests (admin-ajax.php).', 'adminease' ),
					'attributes'    => [
						'data-parent' => 'network-viewer-enabled',
					],
				],
				[
					'type'          => 'switch',
					'id'            => 'network-viewer-log-cron',
					'name'          => 'adminease[debug][network_viewer_log_cron]',
					'value'         => $this->settings['network_viewer_log_cron'] ?? '',
					'label_class'   => 'adminease-switch',
					'input_class'   => 'form-control',
					'wrapper_class' => 'form-group-child',
					'label'         => __( 'Log Cron Requests', 'adminease' ),
					'description'   => __( 'Enable logging of WordPress cron requests (wp-cron.php).', 'adminease' ),
					'attributes'    => [
						'data-parent' => 'network-viewer-enabled',
					],
				],
				[
					'type'          => 'switch',
					'id'            => 'network-viewer-log-rest-api',
					'name'          => 'adminease[debug][network_viewer_log_rest_api]',
					'value'         => $this->settings['network_viewer_log_rest_api'] ?? '',
					'label_class'   => 'adminease-switch',
					'input_class'   => 'form-control',
					'wrapper_class' => 'form-group-child',
					'label'         => __( 'Log REST API Requests', 'adminease' ),
					'description'   => __( 'Enable logging of WordPress REST API requests.', 'adminease' ),
					'attributes'    => [
						'data-parent' => 'network-viewer-enabled',
					],
				],
				[
					'type'              => 'switch',
					'id'                => 'network-viewer-auto-load',
					'name'              => 'adminease[debug][network_viewer_auto_load]',
					'value'             => $this->settings['network_viewer_auto_load'] ?? false,
					'label_class'       => 'adminease-switch',
					'input_class'       => 'form-control',
					'wrapper_class'     => 'form-group-child',
					'label'             => __( 'Auto-Load Network Viewer', 'adminease' ),
					'field_description' => __( 'Automatically load the network viewer log when the AdminEase admin page is opened.', 'adminease' ),
					'attributes'        => [
						'data-parent' => 'network-viewer-enabled',
					],
				],
			],
		];
		
		return $fields;
	}
	
	/**
	 * Enqueues the necessary scripts and styles for the admin page.
	 *
	 * @param string $hook The current admin page hook suffix. Used to determine if scripts should be enqueued.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts( string $hook ): void {
		if( 'toplevel_page_adminease' !== $hook ) {
			return;
		}
		
		wp_enqueue_style(
			ADMINEASE_NAME . 'NetworkViewer',
			ADMINEASE_PLUGIN_URL . 'assets/css/AdminEaseNetworkViewer.css',
			[ ADMINEASE_NAME ],
			filemtime( ADMINEASE_DIR . 'assets/css/AdminEaseNetworkViewer.css' )
		);
		
		wp_enqueue_script(
			ADMINEASE_NAME . 'NetworkViewer',
			ADMINEASE_PLUGIN_URL . 'assets/js/AdminEaseNetworkViewer.js',
			[ 'jquery', ADMINEASE_NAME ],
			filemtime( ADMINEASE_DIR . 'assets/js/AdminEaseNetworkViewer.js' ),
			true
		);
		
		$security = [
			'refreshNetworkViewerLog' => wp_create_nonce( 'adminease_refresh_network_viewer_log' ),
			'clearNetworkViewerLog'   => wp_create_nonce( 'adminease_clear_network_viewer_log' ),
		];
		
		$i18n = [
			'confirmClearNetworkViewerLog' => esc_html__( 'Are you sure you want to clear all network viewer logs?', 'adminease' ),
			'networkViewerLogEmpty'        => esc_html__( 'No network connections yet. Enable the feature and connections will appear here.', 'adminease' ),
			'networkViewerLogCleared'      => esc_html__( 'Network viewer log cleared successfully.', 'adminease' ),
			'networkViewerLoadLogFailed'   => esc_html__( 'Failed to load network viewer log.', 'adminease' ),
			'networkViewerRefreshError'    => esc_html__( 'Failed to refresh network viewer log.', 'adminease' ),
			'clearNetworkViewerLogFailed'  => esc_html__( 'Failed to clear network viewer log.', 'adminease' ),
			/* translators: 1: current page, 2: total pages */
			'paginationInfo'               => esc_html__( 'Page %1$s of %2$s', 'adminease' ),
			/* translators: 1: start number, 2: end number, 3: total count */
			'connectionCount'              => esc_html__( 'Showing %1$s-%2$s of %3$s connections', 'adminease' ),
			'networkViewerDetailLabels'    => [
				'requestInfo'  => esc_html__( 'Request Information', 'adminease' ),
				'timestamp'    => esc_html__( 'Timestamp', 'adminease' ),
				'method'       => esc_html__( 'Method', 'adminease' ),
				'type'         => esc_html__( 'Type', 'adminease' ),
				'protocol'     => esc_html__( 'Protocol', 'adminease' ),
				'port'         => esc_html__( 'Port', 'adminease' ),
				'fullUri'      => esc_html__( 'Full URI', 'adminease' ),
				'queryString'  => esc_html__( 'Query String', 'adminease' ),
				'clientInfo'   => esc_html__( 'Client Information', 'adminease' ),
				'ipAddress'    => esc_html__( 'IP Address', 'adminease' ),
				'hostname'     => esc_html__( 'Hostname', 'adminease' ),
				'country'      => esc_html__( 'Country', 'adminease' ),
				'browser'      => esc_html__( 'Browser', 'adminease' ),
				'device'       => esc_html__( 'Device', 'adminease' ),
				'userAgent'    => esc_html__( 'User Agent', 'adminease' ),
				'responseInfo' => esc_html__( 'Response Information', 'adminease' ),
				'statusCode'   => esc_html__( 'Status Code', 'adminease' ),
				'responseTime' => esc_html__( 'Response Time', 'adminease' ),
				'requestSize'  => esc_html__( 'Request Size', 'adminease' ),
				'referer'      => esc_html__( 'Referer', 'adminease' ),
				'userInfo'     => esc_html__( 'User Information', 'adminease' ),
				'userId'       => esc_html__( 'User ID', 'adminease' ),
				'userRole'     => esc_html__( 'User Role', 'adminease' ),
				'sessionId'    => esc_html__( 'Session ID', 'adminease' ),
				'guest'        => esc_html__( 'Guest', 'adminease' ),
			],
		];
		
		wp_localize_script(
			ADMINEASE_NAME . 'NetworkViewer',
			ADMINEASE_NAME . 'NetworkViewerAjaxObj',
			[
				'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
				'security'  => $security,
				'i18n'      => $i18n,
				'autoLoad'  => !empty( $this->settings['network_viewer_auto_load'] ),
				'pluginUrl' => esc_url_raw( ADMINEASE_PLUGIN_URL ),
			]
		);
	}
	
	/**
	 * Handles the saving of AdminEase settings and updates internal properties accordingly.
	 *
	 * @param array $sanitized_settings Associative array containing sanitized settings. Expected keys include:
	 *                                   - debug: array|null Debug settings, including:
	 *                                   - network_viewer_enabled: bool Indicates whether the network viewer feature is enabled.
	 *
	 * @return void
	 */
	public function adminease_settings_saved( array $sanitized_settings ): void {
		// Reload settings
		$this->settings = $sanitized_settings['debug'] ?? [];
		
		// Create table if enabled
		if( !empty( $sanitized_settings['debug']['network_viewer_enabled'] ) ) {
			$this->create_table();
		}
	}
	
	/**
	 * Handles the after render logic for the specified field.
	 *
	 * @param array $field The field information, including the 'id' property used for conditional logic.
	 *
	 * @return void
	 */
	public function adminease_after_field_render( array $field ) {
		if( 'network-viewer-enabled' !== $field['id'] ) {
			return;
		}
		
		$settings = $this->settings;
		
		include_once ADMINEASE_DIR . 'partials/network-viewer-log.php';
	}
	
	/**
	 * Capture and log network connection data if logging is enabled and the request meets criteria.
	 * @return void
	 */
	public function init(): void {
		if( empty( $this->settings['network_viewer_enabled'] ) ) {
			return;
		}
		
		if( !$this->should_log_request() ) {
			return;
		}
		
		$data = $this->get_request_data();
		
		$this->insert_log_entry( $data );
		
		$this->enforce_max_entries();
	}
	
	/**
	 * Determines whether the current request should be logged.
	 * This method checks various conditions to exclude certain types of requests,
	 * such as internal AJAX calls, cron jobs, REST API requests, static asset requests,
	 * and requests from excluded IP addresses, from being logged.
	 * @return bool True if the request should be logged, false otherwise.
	 */
	private function should_log_request(): bool {
		// Don't log admin-ajax.php unless explicitly enabled
		if( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			if( empty( $this->settings['network_viewer_log_ajax'] ) ) {
				return false;
			}
		}
		
		// Don't log wp-cron.php unless explicitly enabled
		if( defined( 'DOING_CRON' ) && DOING_CRON ) {
			if( empty( $this->settings['network_viewer_log_cron'] ) ) {
				return false;
			}
		}
		
		// Don't log REST API internal calls unless explicitly enabled
		if( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			if( empty( $this->settings['network_viewer_log_rest_api'] ) ) {
				return false;
			}
		}
		
		// Don't log static assets
		$request_uri       = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$static_extensions = [ '.css', '.js', '.jpg', '.jpeg', '.png', '.gif', '.svg', '.ico', '.woff', '.woff2', '.ttf', '.eot' ];
		
		foreach( $static_extensions as $ext ) {
			if( false !== strpos( $request_uri, $ext ) ) {
				return false;
			}
		}
		
		// Check excluded IPs
		$excluded_ips = !empty( $this->settings['network_viewer_exclude_ips'] ) ? $this->settings['network_viewer_exclude_ips'] : '';
		
		if( !empty( $excluded_ips ) ) {
			$excluded_ips_array = array_map( 'trim', explode( " ", $excluded_ips ) );
			$current_ip         = Utils::get_client_ip();
			
			if( in_array( $current_ip, $excluded_ips_array, true ) ) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Retrieve data related to the current request.
	 * @return array An associative array containing request details.
	 */
	private function get_request_data(): array {
		$country_code = Utils::get_client_country();
		$country_name = '';
		
		if( !empty( $country_code ) ) {
			$countries    = Utils::get_countries_iso();
			$country_name = $countries[ $country_code ] ?? $country_code;
		}
		
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$parsed_url  = wp_parse_url( $request_uri );
		
		$user_agent   = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		$browser_info = $this->parse_user_agent( $user_agent );
		
		return [
			'timestamp'      => current_time( 'mysql' ),
			'ip_address'     => Utils::get_client_ip(),
			'country'        => $country_name,
			'hostname'       => $this->get_hostname(),
			'request_method' => isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '',
			'request_uri'    => $request_uri,
			'request_type'   => $this->get_request_type(),
			'query_string'   => $parsed_url['query'] ?? '',
			'user_agent'     => $user_agent,
			'browser'        => $browser_info['browser'],
			'device'         => $browser_info['device'],
			'is_bot'         => $browser_info['is_bot'] ? 1 : 0,
			'referer'        => isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '',
			'response_code'  => $this->get_response_code(),
			'request_size'   => isset( $_SERVER['CONTENT_LENGTH'] ) ? (int) $_SERVER['CONTENT_LENGTH'] : null,
			'protocol'       => isset( $_SERVER['SERVER_PROTOCOL'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_PROTOCOL'] ) ) : '',
			'port'           => isset( $_SERVER['SERVER_PORT'] ) ? (int) $_SERVER['SERVER_PORT'] : null,
			'user_id'        => get_current_user_id(),
			'user_role'      => $this->get_user_role(),
			'session_id'     => $this->get_session_id(),
		];
	}
	
	/**
	 * Get the type of request.
	 * @return string Request type.
	 */
	private function get_request_type(): string {
		if( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return 'ajax';
		}
		
		if( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return 'cron';
		}
		
		if( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return 'rest_api';
		}
		
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		
		if( false !== strpos( $request_uri, '/wp-json/' ) || strpos( $request_uri, '?rest_route=' ) !== false ) {
			return 'rest_api';
		}
		
		if( is_admin() ) {
			return 'admin';
		}
		
		return 'frontend';
	}
	
	/**
	 * Get current user's role.
	 * @return string User role or empty string.
	 */
	private function get_user_role(): string {
		if( !is_user_logged_in() ) {
			return '';
		}
		
		$user = wp_get_current_user();
		
		return !empty( $user->roles ) ? implode( ', ', $user->roles ) : '';
	}
	
	/**
	 * Get session ID.
	 * @return string Session ID or empty string.
	 */
	private function get_session_id(): string {
		if( session_id() ) {
			return session_id();
		}
		
		// Try to get a WooCommerce session if available
		if( function_exists( 'WC' ) && WC()->session ) {
			return WC()->session->get_customer_id();
		}
		
		return '';
	}
	
	/**
	 * Detect if the user agent is a bot.
	 *
	 * @param string $user_agent User agent string.
	 *
	 * @return bool True if bot, false if human.
	 */
	private function is_bot( string $user_agent ): bool {
		$categorized_bots = Utils::get_categorized_bots();
		
		$user_agent_lower = strtolower( $user_agent );
		
		foreach( $categorized_bots as $category_data ) {
			foreach( $category_data['bots'] as $bot_key => $bot_name ) {
				// Check both the bot key and bot name
				if( false !== strpos( $user_agent_lower, strtolower( $bot_key ) ) ) {
					return true;
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Parse user agent string to extract browser and device info.
	 *
	 * @param string $user_agent User agent string.
	 *
	 * @return array Array with 'browser', 'device', and 'is_bot' keys.
	 */
	private function parse_user_agent( string $user_agent ): array {
		try {
			$parser = Parser::create();
			$result = $parser->parse( $user_agent );
			
			// Get browser name
			$browser = $result->ua->family !== 'Other' ? $result->ua->family : 'Unknown';
			
			// Get device type
			$device_family = $result->device->family;
			$device        = 'Desktop';
			
			if( $device_family !== 'Other' ) {
				// Check if it's a tablet
				if( stripos( $device_family, 'tablet' ) !== false ||
				    stripos( $device_family, 'ipad' ) !== false ) {
					$device = 'Tablet';
				} // Check if it's a mobile device
				else if( stripos( $device_family, 'phone' ) !== false ||
				         $result->device->brand !== null ) {
					$device = 'Mobile';
				}
			} // Fallback to wp_is_mobile() if device is still Desktop
			else if( wp_is_mobile() ) {
				if( strpos( $user_agent, 'iPad' ) !== false ) {
					$device = 'Tablet';
				} else {
					$device = 'Mobile';
				}
			}
			
			// Check if it's a bot
			$is_bot = $result->device->family === 'Spider' ||
			          $result->ua->family === 'Bot' ||
			          $this->is_bot( $user_agent ); // Fallback to existing bot detection
			
			return [
				'browser' => $browser,
				'device'  => $device,
				'is_bot'  => $is_bot,
			];
		}
		catch( \Exception $e ) {
			// Fallback to basic detection if the parser fails
			return [
				'browser' => 'Unknown',
				'device'  => wp_is_mobile() ? 'Mobile' : 'Desktop',
				'is_bot'  => $this->is_bot( $user_agent ),
			];
		}
	}
	
	/**
	 * Get the hostname from the IP address.
	 * @return string The hostname or empty string if unable to resolve.
	 */
	private function get_hostname(): string {
		$ip_address = Utils::get_client_ip();
		
		if( empty( $ip_address ) ) {
			return '';
		}
		
		$hostname = gethostbyaddr( $ip_address );
		
		// gethostbyaddr returns the IP if it cannot resolve
		return ( $hostname !== $ip_address ) ? $hostname : '';
	}
	
	/**
	 * Get the HTTP response code.
	 * Note: This captures the initial response code. The final code may differ.
	 * @return int The HTTP response code, defaults to 200.
	 */
	private function get_response_code(): int {
		// Default to 200 for successful requests
		$response_code = 200;
		
		// Check if WordPress has set a status
		$status = http_response_code();
		
		if( false !== $status ) {
			$response_code = $status;
		}
		
		return $response_code;
	}
	
	/**
	 * Inserts a log entry into the database.
	 *
	 * @param array $data Associative array containing log entry data.
	 *
	 * @return void
	 */
	private function insert_log_entry( array $data ): void {
		global $wpdb;
		
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Real-time logging requires direct insert
		$wpdb->insert(
			$this->table_name,
			[
				'timestamp'      => $data['timestamp'],
				'ip_address'     => $data['ip_address'],
				'country'        => $data['country'],
				'hostname'       => $data['hostname'],
				'request_method' => $data['request_method'],
				'request_uri'    => $data['request_uri'],
				'request_type'   => $data['request_type'],
				'query_string'   => $data['query_string'],
				'user_agent'     => $data['user_agent'],
				'browser'        => $data['browser'],
				'device'         => $data['device'],
				'is_bot'         => $data['is_bot'],
				'referer'        => $data['referer'],
				'response_code'  => $data['response_code'],
				'request_size'   => $data['request_size'],
				'protocol'       => $data['protocol'],
				'port'           => $data['port'],
				'user_id'        => $data['user_id'] > 0 ? $data['user_id'] : null,
				'user_role'      => $data['user_role'],
				'session_id'     => $data['session_id'],
			],
			[
				'%s', // timestamp
				'%s', // ip_address
				'%s', // country
				'%s', // hostname
				'%s', // request_method
				'%s', // request_uri
				'%s', // request_type
				'%s', // query_string
				'%s', // user_agent
				'%s', // browser
				'%s', // device
				'%d', // is_bot
				'%s', // referer
				'%d', // response_code
				'%d', // request_size
				'%s', // protocol
				'%d', // port
				'%d', // user_id
				'%s', // user_role
				'%s', // session_id
			]
		);
	}
	
	/**
	 * Enforces the maximum number of entries in the log table by removing the oldest entries when the count exceeds the allowed maximum.
	 * The method checks the total number of entries in the log table and deletes the oldest entries if the count exceeds the maximum
	 * number of allowed entries as defined by the `get_max_entries` method.
	 * @return void
	 */
	private function enforce_max_entries(): void {
		global $wpdb;
		
		$max_entries = $this->get_max_entries();
		
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Count check for log maintenance, table name is safe
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_name}" );
		
		if( $count > $max_entries ) {
			$delete_count = $count - $max_entries;
			
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Log cleanup operation
			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$this->table_name} ORDER BY timestamp ASC LIMIT %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$delete_count
				)
			);
		}
	}
	
	/**
	 * Retrieves the maximum number of entries allowed for network viewer.
	 * This value is determined from the settings configuration. If no value is
	 * explicitly set, a default value of 1000 is returned.
	 * @return int The maximum number of entries.
	 */
	private function get_max_entries(): int {
		return !empty( $this->settings['network_viewer_max_entries'] ) ? (int) $this->settings['network_viewer_max_entries'] : 1000;
	}
	
	/**
	 * Cleans up old logs from the database based on the configured auto-clear period.
	 * The auto-clear period determines the time range for retaining logs
	 * and can be set to intervals like 1 hour, 24 hours, or 7 days.
	 * If the auto-clear period is set to 'never', no logs will be deleted.
	 * @return void
	 */
	public function cleanup_old_logs(): void {
		$auto_clear = $this->get_auto_clear_period();
		
		if( 'never' === $auto_clear ) {
			return;
		}
		
		global $wpdb;
		
		// Map to number of seconds for each period
		$time_map = [
			'1_hour'   => 3600,        // 1 hour in seconds
			'24_hours' => 86400,       // 24 hours in seconds
			'7_days'   => 604800,      // 7 days in seconds
		];
		
		$seconds = $time_map[ $auto_clear ] ?? 86400;
		
		// Calculate the cutoff timestamp
		$cutoff_time = gmdate( 'Y-m-d H:i:s', time() - $seconds );
		
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Scheduled log cleanup operation
		$wpdb->query(
			$wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"DELETE FROM {$this->table_name} WHERE timestamp < %s",
				$cutoff_time
			)
		);
	}
	
	/**
	 * Retrieves a list of connections from the database based on the provided arguments.
	 *
	 * @param array $args Optional. Associative array of arguments to filter and paginate the query:
	 *                    - per_page: int Number of records to fetch per page. Default 50.
	 *                    - page: int Current page number for pagination. Default 1.
	 *                    - method: string HTTP request method to filter by (e.g., GET, POST). Default empty.
	 *                    - ip: string IP address or partial IP to filter by. Default empty.
	 *                    - orderby: string Column to sort results by. Default 'timestamp'.
	 *                    - order: string Sorting order, either 'ASC' or 'DESC'. Default 'DESC'.
	 *
	 * @return array Array of connections matching the provided filters and pagination options.
	 */
	public function get_connections( array $args = [] ): array {
		global $wpdb;
		
		$defaults = [
			'per_page' => 50,
			'page'     => 1,
			'method'   => '',
			'ip'       => '',
			'orderby'  => 'timestamp',
			'order'    => 'DESC',
		];
		
		$args = wp_parse_args( $args, $defaults );
		
		// Validate orderby against allowed columns
		$allowed_orderby = [ 'timestamp', 'ip_address', 'request_method', 'response_code', 'request_type' ];
		$orderby         = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'timestamp';
		
		// Validate order
		$order = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';
		
		$offset = ( $args['page'] - 1 ) * $args['per_page'];
		
		$where        = [];
		$where_values = [];
		
		if( !empty( $args['method'] ) ) {
			$where[]        = 'request_method = %s';
			$where_values[] = $args['method'];
		}
		
		if( !empty( $args['ip'] ) ) {
			$where[]        = 'ip_address LIKE %s';
			$where_values[] = '%' . $wpdb->esc_like( $args['ip'] ) . '%';
		}
		
		$where_clause = !empty( $where ) ? 'WHERE ' . implode( ' AND ', $where ) : '';
		
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name and orderby/order are validated above
		$query = "SELECT * FROM {$this->table_name} {$where_clause} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
		
		$where_values[] = $args['per_page'];
		$where_values[] = $offset;
		
		if( !empty( $where_values ) ) {
			$query = $wpdb->prepare( $query, $where_values ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
		
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Real-time log viewing, orderby/order validated against whitelist, all user inputs properly escaped
		return $wpdb->get_results( $query, ARRAY_A );
	}
	
	/**
	 * Retrieves the total count of records from the database based on the specified conditions.
	 *
	 * @param array $args Optional associative array to filter the query. Possible keys:
	 *                    - method: string HTTP request method to filter by (e.g., GET, POST).
	 *                    - ip: string IP address or partial IP address to filter by.
	 *
	 * @return int The total number of matching records.
	 */
	public function get_total_count( array $args = [] ): int {
		global $wpdb;
		
		$where        = [];
		$where_values = [];
		
		if( !empty( $args['method'] ) ) {
			$where[]        = 'request_method = %s';
			$where_values[] = $args['method'];
		}
		
		if( !empty( $args['ip'] ) ) {
			$where[]        = 'ip_address LIKE %s';
			$where_values[] = '%' . $wpdb->esc_like( $args['ip'] ) . '%';
		}
		
		$where_clause = !empty( $where ) ? 'WHERE ' . implode( ' AND ', $where ) : '';
		
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe, the query is properly prepared below
		$query = "SELECT COUNT(*) FROM {$this->table_name} {$where_clause}";
		
		if( !empty( $where_values ) ) {
			$query = $wpdb->prepare( $query, $where_values ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
		
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Real-time count for pagination, all user inputs properly escaped via wpdb::prepare()
		return (int) $wpdb->get_var( $query );
	}
	
	/**
	 * Clears all entries from the connections table by truncating it.
	 * @return bool True if the operation was successful, false otherwise.
	 */
	public function clear_connections(): bool {
		global $wpdb;
		
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Intentional log clearing operation
		return false !== $wpdb->query( "TRUNCATE TABLE {$this->table_name}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}
	
	/**
	 * Retrieves the auto-clear period for the network viewer.
	 * This method checks the `network_viewer_auto_clear` setting in the class's settings property
	 * and returns its value if it is defined and not empty. If the setting is not defined or is empty,
	 * a default value of '24_hours' is returned.
	 * @return string The auto-clear period value, either from the settings or the default '24_hours'.
	 */
	private function get_auto_clear_period(): string {
		return !empty( $this->settings['network_viewer_auto_clear'] ) ? $this->settings['network_viewer_auto_clear'] : '24_hours';
	}
	
	/**
	 * Handles an AJAX request to clear network connections logs.
	 * Validates the user's permissions and nonce before performing the action.
	 * @return void Outputs a JSON response indicating success or failure.
	 */
	public function ajax_clear_network_viewer_log(): void {
		// Verify nonce
		if( !isset( $_POST['security'] ) || !wp_verify_nonce( sanitize_key( wp_unslash( $_POST['security'] ) ), 'adminease_clear_network_viewer_log' ) ) {
			wp_send_json_error( new WP_Error( 'security_error', __( 'An error occurred. Refresh the page and try again.', 'adminease' ) ), 403 );
		}
		
		// Check user capabilities
		if( !current_user_can( 'manage_options' ) ) {
			wp_send_json_error( new WP_Error( 'permissions', esc_html__( 'You do not have sufficient permissions to perform this action', 'adminease' ) ) );
		}
		
		$result = $this->clear_connections();
		
		if( $result ) {
			wp_send_json_success( esc_html__( 'Network viewer log cleared successfully.', 'adminease' ) );
		} else {
			wp_send_json_error( new WP_Error( 'clear_failed', __( 'Failed to clear network viewer log.', 'adminease' ) ) );
		}
	}
	
	/**
	 * Handles the AJAX request to refresh connection data.
	 * Checks user permissions, verifies nonce, and retrieves connection data based
	 * on the provided parameters. Returns a JSON response containing connection
	 * details and pagination info.
	 * @return void
	 */
	public function ajax_get_network_viewer_log(): void {
		// Verify nonce
		if( !isset( $_POST['security'] ) || !wp_verify_nonce( sanitize_key( wp_unslash( $_POST['security'] ) ), 'adminease_refresh_network_viewer_log' ) ) {
			wp_send_json_error( new WP_Error( 'security_error', __( 'An error occurred. Refresh the page and try again.', 'adminease' ) ), 403 );
		}
		
		// Check user capabilities
		if( !current_user_can( 'manage_options' ) ) {
			wp_send_json_error( new WP_Error( 'permissions', esc_html__( 'You do not have sufficient permissions to perform this action', 'adminease' ) ) );
		}
		
		$page     = isset( $_POST['page'] ) ? (int) $_POST['page'] : 1;
		$per_page = isset( $_POST['per_page'] ) ? (int) $_POST['per_page'] : 50;
		$method   = isset( $_POST['method'] ) ? sanitize_text_field( wp_unslash( $_POST['method'] ) ) : '';
		$ip       = isset( $_POST['ip'] ) ? sanitize_text_field( wp_unslash( $_POST['ip'] ) ) : '';
		
		$args = [
			'page'     => $page,
			'per_page' => $per_page,
			'method'   => $method,
			'ip'       => $ip,
		];
		
		$connections = $this->get_connections( $args );
		$total       = $this->get_total_count( $args );
		
		$date_format     = get_option( 'date_format' );
		$time_format     = get_option( 'time_format' );
		$datetime_format = $date_format . ' ' . $time_format;
		
		// Format connections for display (NO HTML snippets in returned data).
		foreach( $connections as &$connection ) {
			$connection['formatted_time']    = date_i18n( $datetime_format, strtotime( $connection['timestamp'] ) );
			$connection['country_code']      = Utils::get_country_code_by_name( $connection['country'] ?? '' );
			$connection['browser_icon_file'] = $this->get_browser_icon_file( $connection['browser'] ?? '' );
			$connection['device_icon_file']  = $this->get_device_icon_file( $connection['device'] ?? '' );
			
			if( !empty( $connection['user_id'] ) ) {
				$user = get_userdata( (int) $connection['user_id'] );
				
				$connection['username_text'] = $user ? $user->user_email : esc_html__( 'Unknown', 'adminease' );
				$connection['user_edit_url'] = $user ? get_edit_user_link( $user->ID ) : '';
			} else {
				$connection['username_text'] = esc_html__( 'Guest', 'adminease' );
				$connection['user_edit_url'] = '';
			}
		}
		
		wp_send_json_success( [
			'table_html' => $this->render_network_viewer_log_table( $connections ),
			'total'      => $total,
			'page'       => $page,
			'per_page'   => $per_page,
		] );
	}
	
	/**
	 * Renders an HTML table displaying network viewer log data.
	 *
	 * @param array $connections Array of connection log entries.
	 *
	 * @return string The rendered HTML table as a string, or an empty string if the $connections array is empty.
	 */
	public function render_network_viewer_log_table( array $connections ): string {
		if( empty( $connections ) ) {
			return '';
		}
		
		ob_start();
		?>
		<table id="adminease-network-viewer-table" class="adminease-table network-viewer-table">
			<thead>
			<tr>
				<th class="col-time"><?php esc_html_e( 'Time', 'adminease' ); ?></th>
				<th class="col-method"><?php esc_html_e( 'Method', 'adminease' ); ?></th>
				<th class="col-status"><?php esc_html_e( 'Status', 'adminease' ); ?></th>
				<th class="col-type"><?php esc_html_e( 'Type', 'adminease' ); ?></th>
				<th class="col-location"><?php esc_html_e( 'Location', 'adminease' ); ?></th>
				<th class="col-ip"><?php esc_html_e( 'IP', 'adminease' ); ?></th>
				<th class="col-path"><?php esc_html_e( 'Path', 'adminease' ); ?></th>
				<th class="col-visitor"><?php esc_html_e( 'Visitor', 'adminease' ); ?></th>
				<th class="col-view"><?php esc_html_e( 'View', 'adminease' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php
			foreach( $connections as $connection ) {
				$country        = $this->get_country_flag( $connection['country'] ?? '' );
				$response_code  = (int) ( $connection['response_code'] ?? 200 );
				$response_class = $this->get_response_class( $response_code );
				$method_class   = $this->get_method_class( $connection['request_method'] ?? '' );
				$type_class     = $this->get_type_class( $connection['request_type'] ?? '' );
				
				$path       = $connection['request_uri'] ?? '';
				$short_path = strlen( $path ) > 40 ? substr( $path, 0, 40 ) . '...' : $path;
				
				$is_bot = !empty( $connection['is_bot'] );
				?>
				<tr data-id="<?php echo esc_attr( $connection['id'] ); ?>">
					<td class="col-time" data-label="<?php esc_attr_e( 'Time', 'adminease' ); ?>">
						<?php echo esc_html( $connection['formatted_time'] ?? $connection['timestamp'] ); ?>
					</td>
					<td class="col-method" data-label="<?php esc_attr_e( 'Method', 'adminease' ); ?>">
					<span class="badge method-badge <?php echo esc_attr( $method_class ); ?>">
						<?php echo esc_html( $connection['request_method'] ?? '' ); ?>
					</span>
					</td>
					<td class="col-status" data-label="<?php esc_attr_e( 'Status', 'adminease' ); ?>">
					<span class="badge badge-success <?php echo esc_attr( $response_class ); ?>">
						<?php echo esc_html( $response_code ); ?>
					</span>
					</td>
					<td class="col-type" data-label="<?php esc_attr_e( 'Type', 'adminease' ); ?>">
					<span class="badge type-badge <?php echo esc_attr( $type_class ); ?>">
						<?php echo esc_html( ucfirst( str_replace( '_', ' ', $connection['request_type'] ?? 'unknown' ) ) ); ?>
					</span>
					</td>
					<td class="col-location" data-label="<?php esc_attr_e( 'Location', 'adminease' ); ?>"><?php echo wp_kses_post( $country ); ?><?php echo esc_html( $connection['country'] ?? '-' ); ?></td>
					<td class="col-ip" data-label="<?php esc_attr_e( 'IP Address', 'adminease' ); ?>"><?php echo esc_html( $connection['ip_address'] ?? '' ); ?></td>
					<td class="col-path" data-label="<?php esc_attr_e( 'Path', 'adminease' ); ?>" title="<?php echo esc_attr( $path ); ?>"><?php echo esc_html( $short_path ); ?></td>
					<td class="col-visitor" data-label="<?php esc_attr_e( 'Visitor', 'adminease' ); ?>">
						<?php if( $is_bot ) : ?>
							<span class="visitor-icon visitor-bot" title="<?php esc_attr_e( 'Bot', 'adminease' ); ?>"><img src="<?php echo esc_url( ADMINEASE_PLUGIN_URL . 'assets/img/icon-bot.svg' ); ?>" alt="<?php esc_attr_e( 'Bot', 'adminease' ); ?>"/></span>
						<?php else : ?>
							<span class="visitor-icon visitor-human" title="<?php esc_attr_e( 'Human', 'adminease' ); ?>"><img src="<?php echo esc_url( ADMINEASE_PLUGIN_URL . 'assets/img/icon-person.svg' ); ?>" alt="<?php esc_attr_e( 'Human', 'adminease' ); ?>"/></span>
						<?php endif; ?>
					</td>
					<td class="col-view" data-label="<?php esc_attr_e( 'View', 'adminease' ); ?>">
						<button
							type="button"
							class="button button-small view-details"
							data-id="<?php echo esc_attr( $connection['id'] ?? '' ); ?>"
							data-formatted-time="<?php echo esc_attr( $connection['formatted_time'] ?? '' ); ?>"
							data-timestamp="<?php echo esc_attr( $connection['timestamp'] ?? '' ); ?>"
							data-request-method="<?php echo esc_attr( $connection['request_method'] ?? '' ); ?>"
							data-request-type="<?php echo esc_attr( $connection['request_type'] ?? '' ); ?>"
							data-protocol="<?php echo esc_attr( $connection['protocol'] ?? '' ); ?>"
							data-port="<?php echo esc_attr( (string) ( $connection['port'] ?? '' ) ); ?>"
							data-request-uri="<?php echo esc_attr( $connection['request_uri'] ?? '' ); ?>"
							data-query-string="<?php echo esc_attr( $connection['query_string'] ?? '' ); ?>"
							data-ip-address="<?php echo esc_attr( $connection['ip_address'] ?? '' ); ?>"
							data-hostname="<?php echo esc_attr( $connection['hostname'] ?? '' ); ?>"
							data-country="<?php echo esc_attr( $connection['country'] ?? '' ); ?>"
							data-country-code="<?php echo esc_attr( $connection['country_code'] ?? '' ); ?>"
							data-browser="<?php echo esc_attr( $connection['browser'] ?? '' ); ?>"
							data-browser-icon-file="<?php echo esc_attr( $connection['browser_icon_file'] ?? '' ); ?>"
							data-device="<?php echo esc_attr( $connection['device'] ?? '' ); ?>"
							data-device-icon-file="<?php echo esc_attr( $connection['device_icon_file'] ?? '' ); ?>"
							data-user-agent="<?php echo esc_attr( $connection['user_agent'] ?? '' ); ?>"
							data-response-code="<?php echo esc_attr( (string) ( $connection['response_code'] ?? '' ) ); ?>"
							data-request-size="<?php echo esc_attr( (string) ( $connection['request_size'] ?? '' ) ); ?>"
							data-referer="<?php echo esc_attr( $connection['referer'] ?? '' ); ?>"
							data-user-id="<?php echo esc_attr( (string) ( $connection['user_id'] ?? '' ) ); ?>"
							data-user-role="<?php echo esc_attr( $connection['user_role'] ?? '' ); ?>"
							data-session-id="<?php echo esc_attr( $connection['session_id'] ?? '' ); ?>"
							data-username-text="<?php echo esc_attr( $connection['username_text'] ?? '' ); ?>"
							data-user-edit-url="<?php echo esc_attr( $connection['user_edit_url'] ?? '' ); ?>"
						>
							<span class="dashicons dashicons-visibility"></span>
						</button>
					</td>
				</tr>
				<?php
			}
			?>
			</tbody>
		</table>
		
		<!-- Modal for full details -->
		<div id="connection-details-modal" class="adminease-modal" style="display:none;">
			<div class="adminease-modal-overlay"></div>
			<div class="adminease-modal-content">
				<div class="adminease-modal-header">
					<h2><?php esc_html_e( 'Connection Details', 'adminease' ); ?></h2>
					<button type="button" class="adminease-modal-close">&times;</button>
				</div>
				
				<div class="adminease-modal-body">
					<div class="connection-details-grid">
						<div class="detail-section">
							<h3><?php esc_html_e( 'Request Information', 'adminease' ); ?></h3>
							
							<div class="detail-row">
								<span class="detail-label"><?php esc_html_e( 'Timestamp', 'adminease' ); ?>:</span>
								<span class="detail-value" data-field="formatted_time"></span>
							</div>
							
							<div class="detail-row">
								<span class="detail-label"><?php esc_html_e( 'Method', 'adminease' ); ?>:</span>
								<span class="detail-value"><span class="badge method-badge" data-field="request_method_badge"></span></span>
							</div>
							
							<div class="detail-row">
								<span class="detail-label"><?php esc_html_e( 'Type', 'adminease' ); ?>:</span>
								<span class="detail-value" data-field="request_type"></span>
							</div>
							
							<div class="detail-row">
								<span class="detail-label"><?php esc_html_e( 'Protocol', 'adminease' ); ?>:</span>
								<span class="detail-value" data-field="protocol"></span>
							</div>
							
							<div class="detail-row">
								<span class="detail-label"><?php esc_html_e( 'Port', 'adminease' ); ?>:</span>
								<span class="detail-value" data-field="port"></span>
							</div>
							
							<div class="detail-row">
								<span class="detail-label"><?php esc_html_e( 'Full URI', 'adminease' ); ?>:</span>
								<span class="detail-value detail-value-break" data-field="request_uri"></span>
							</div>
							
							<div class="detail-row">
								<span class="detail-label"><?php esc_html_e( 'Query String', 'adminease' ); ?>:</span>
								<span class="detail-value detail-value-break" data-field="query_string"></span>
							</div>
						</div>
						
						<div class="detail-section">
							<h3><?php esc_html_e( 'Client Information', 'adminease' ); ?></h3>
							
							<div class="detail-row">
								<span class="detail-label"><?php esc_html_e( 'IP Address', 'adminease' ); ?>:</span>
								<span class="detail-value" data-field="ip_address"></span>
							</div>
							
							<div class="detail-row">
								<span class="detail-label"><?php esc_html_e( 'Hostname', 'adminease' ); ?>:</span>
								<span class="detail-value detail-value-break" data-field="hostname"></span>
							</div>
							
							<div class="detail-row">
								<span class="detail-label"><?php esc_html_e( 'Country', 'adminease' ); ?>:</span>
								<span class="detail-value">
									<img src="" class="flag-icon" alt="" loading="lazy" data-field="country_flag" style="display:none;">
									<span data-field="country"></span>
								</span>
							</div>
							
							<div class="detail-row">
								<span class="detail-label"><?php esc_html_e( 'Browser', 'adminease' ); ?>:</span>
								<span class="detail-value">
									<img src="" class="browser-icon" alt="" loading="lazy" data-field="browser_icon" style="display:none;">
									<span data-field="browser"></span>
								</span>
							</div>
							
							<div class="detail-row">
								<span class="detail-label"><?php esc_html_e( 'Device', 'adminease' ); ?>:</span>
								<span class="detail-value">
									<img src="" class="device-icon" alt="" loading="lazy" data-field="device_icon" style="display:none;">
									<span data-field="device"></span>
								</span>
							</div>
							
							<div class="detail-row">
								<span class="detail-label"><?php esc_html_e( 'User Agent', 'adminease' ); ?>:</span>
								<span class="detail-value detail-value-break" data-field="user_agent"></span>
							</div>
						</div>
						
						<div class="detail-section">
							<h3><?php esc_html_e( 'Response Information', 'adminease' ); ?></h3>
							
							<div class="detail-row">
								<span class="detail-label"><?php esc_html_e( 'Status Code', 'adminease' ); ?>:</span>
								<span class="detail-value"><span class="badge badge-success" data-field="response_code_badge"></span></span>
							</div>
							
							<div class="detail-row">
								<span class="detail-label"><?php esc_html_e( 'Request Size', 'adminease' ); ?>:</span>
								<span class="detail-value" data-field="request_size"></span>
							</div>
							
							<div class="detail-row">
								<span class="detail-label"><?php esc_html_e( 'Referer', 'adminease' ); ?>:</span>
								<span class="detail-value detail-value-break" data-field="referer"></span>
							</div>
						</div>
						
						<div class="detail-section">
							<h3><?php esc_html_e( 'User Information', 'adminease' ); ?></h3>
							
							<div class="detail-row">
								<span class="detail-label"><?php esc_html_e( 'User ID', 'adminease' ); ?>:</span>
								<span class="detail-value">
									<a href="#" target="_blank" data-field="user_link" style="display:none;"></a>
									<span data-field="username_text"></span>
								</span>
							</div>
							
							<div class="detail-row">
								<span class="detail-label"><?php esc_html_e( 'User Role', 'adminease' ); ?>:</span>
								<span class="detail-value" data-field="user_role"></span>
							</div>
							
							<div class="detail-row">
								<span class="detail-label"><?php esc_html_e( 'Session ID', 'adminease' ); ?>:</span>
								<span class="detail-value detail-value-break" data-field="session_id"></span>
							</div>
						</div>
					</div>
				</div><!-- .adminease-modal-body -->
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
	
	/**
	 * Retrieve the appropriate browser icon based on the browser name.
	 *
	 * @param string $browser The name of the browser to retrieve the icon for.
	 *
	 * @return string An HTML image element containing the browser icon. Returns a default icon if the browser is not mapped.
	 */
	public function get_browser_icon_file( string $browser ): string {
		if( empty( $browser ) ) {
			$browser = 'default';
		}
		
		$icon_map = [
			'Chrome'           => 'chrome.svg',
			'Safari'           => 'safari.svg',
			'Mobile Safari'    => 'safari.svg',
			'Edge'             => 'microsoftedge.svg',
			'Firefox'          => 'firefox-browser.svg',
			'Samsung Internet' => 'samsung.svg',
			'Opera'            => 'opera.svg',
			'UC Browser'       => 'ucbrowser.svg',
			'Android'          => 'android-webview.svg',
			'Android Browser'  => 'android-webview.svg',
			'QQ Browser'       => 'qq.png',
			'Yandex Browser'   => 'yandex.png',
			'WordPress'        => 'wordpress.svg',
			'UptimeRobot'      => 'uptimerobot.svg',
			'default'          => 'question.svg',
		];
		
		return $icon_map[ $browser ] ?? $icon_map['default'];
	}
	
	/**
	 * Retrieve the corresponding device icon based on the device type.
	 *
	 * @param string $device_type The type of the device (e.g., 'Desktop', 'Mobile').
	 *
	 * @return string The HTML string for the device icon, defaulting to 'Unknown' if the device type is not specified or unrecognized.
	 */
	public function get_device_icon_file( string $device_type ): string {
		if( empty( $device_type ) ) {
			$device_type = 'Unknown';
		}
		
		$icon_map = [
			'Desktop' => 'desktop.svg',
			'Mobile'  => 'mobile.svg',
			'Tablet'  => 'tablet.svg',
			'Unknown' => 'question.svg',
		];
		
		return $icon_map[ $device_type ] ?? $icon_map['Unknown'];
	}
	
	/**
	 * Retrieve the flag emoji or image representation for a given country.
	 *
	 * @param string $country_name The name of the country for which to retrieve the flag.
	 *
	 * @return string The country flag or an empty string if the flag cannot be determined.
	 */
	public function get_country_flag( string $country_name ): string {
		if( empty( $country_name ) ) {
			return '';
		}
		
		$country_code = strtolower( Utils::get_country_code_by_name( $country_name ) );
		
		return '<img src="https://flagcdn.com/' . $country_code . '.svg" alt="' . $country_name . '" class="flag-icon" loading="lazy">';
	}
	
	/**
	 * Get CSS class for response code.
	 *
	 * @param int $code Response code.
	 *
	 * @return string CSS class.
	 */
	private function get_response_class( int $code ): string {
		if( $code >= 200 && $code < 300 ) {
			return 'response-success';
		}
		if( $code >= 300 && $code < 400 ) {
			return 'response-redirect';
		}
		if( $code >= 400 && $code < 500 ) {
			return 'response-client-error';
		}
		if( $code >= 500 ) {
			return 'response-server-error';
		}
		
		return '';
	}
	
	/**
	 * Get CSS class for request method.
	 *
	 * @param string $method Request method.
	 *
	 * @return string CSS class.
	 */
	private function get_method_class( string $method ): string {
		$classes = [
			'GET'    => 'method-get',
			'POST'   => 'method-post',
			'PUT'    => 'method-put',
			'DELETE' => 'method-delete',
			'PATCH'  => 'method-patch',
		];
		
		return $classes[ $method ] ?? 'method-other';
	}
	
	/**
	 * Get the CSS class for the request type.
	 *
	 * @param string $type Request type.
	 *
	 * @return string CSS class.
	 */
	private function get_type_class( string $type ): string {
		return 'type-' . sanitize_html_class( $type );
	}
	
	/**
	 * Create the database table if it does not already exist.
	 * @return void
	 */
	public function create_table(): void {
		global $wpdb;
		
		$charset_collate = $wpdb->get_charset_collate();
		
		$sql = "CREATE TABLE {$this->table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			timestamp datetime NOT NULL,
			ip_address varchar(45) NOT NULL,
			country varchar(255) DEFAULT NULL,
			city varchar(255) DEFAULT NULL,
			hostname varchar(255) DEFAULT NULL,
			request_method varchar(10) NOT NULL,
			request_uri text NOT NULL,
			request_type varchar(20) DEFAULT NULL,
			query_string text DEFAULT NULL,
			user_agent text DEFAULT NULL,
			browser varchar(50) DEFAULT NULL,
			device varchar(50) DEFAULT NULL,
			is_bot tinyint(1) DEFAULT 0,
			referer text DEFAULT NULL,
			response_code int(3) DEFAULT 200,
			request_size int(11) DEFAULT NULL,
			protocol varchar(20) DEFAULT NULL,
			port int(5) DEFAULT NULL,
			user_id bigint(20) unsigned DEFAULT NULL,
			user_role varchar(50) DEFAULT NULL,
			session_id varchar(255) DEFAULT NULL,
			PRIMARY KEY  (id),
			KEY timestamp (timestamp),
			KEY ip_address (ip_address),
			KEY user_id (user_id),
			KEY request_method (request_method),
			KEY response_code (response_code),
			KEY request_type (request_type),
			KEY is_bot (is_bot)
		) {$charset_collate};";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}
	
	/**
	 * Drop the database table if it exists.
	 * @return void
	 */
	public function drop_table(): void {
		global $wpdb;
		
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange -- Intentional schema change for plugin uninstallation
		$wpdb->query( "DROP TABLE IF EXISTS {$this->table_name}" );
	}
}