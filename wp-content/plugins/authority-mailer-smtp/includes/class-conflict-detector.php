<?php
/**
 * Authority Mailer Conflict Detector
 *
 * Detects conflicting SMTP plugins and performs system compatibility checks.
 * Displays admin notices and disables SMTP functionality when conflicts are found.
 *
 * @package Authority_Mailer
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Authority_Mailer_Conflict_Detector' ) ) {

	/**
	 * Authority_Mailer_Conflict_Detector class.
	 *
	 * Handles detection of conflicting SMTP plugins and system compatibility checks.
	 *
	 * @since 1.0.0
	 */
	class Authority_Mailer_Conflict_Detector {

		/**
		 * Singleton instance.
		 *
		 * @var Authority_Mailer_Conflict_Detector|null
		 */
		private static $instance = null;

		/**
		 * List of known conflicting SMTP plugins.
		 *
		 * Format: 'plugin-folder/plugin-file.php' => 'Plugin Display Name'
		 *
		 * @var array
		 */
		private $conflicting_plugins = array(
			'wp-mail-smtp/wp_mail_smtp.php'        => 'WP Mail SMTP',
			'wp-mail-smtp-pro/wp_mail_smtp.php'    => 'WP Mail SMTP Pro',
			'fluent-smtp/fluent-smtp.php'          => 'FluentSMTP',
			'post-smtp/postman-smtp.php'           => 'Post SMTP',
			'easy-wp-smtp/easy-wp-smtp.php'        => 'Easy WP SMTP',
			'smtp-mailer/main.php'                 => 'SMTP Mailer',
			'gmail-smtp/main.php'                  => 'Gmail SMTP',
			'wp-smtp/wp-smtp.php'                  => 'WP SMTP',
			'mailgun/mailgun.php'                  => 'Mailgun',
			'sparkpost/wordpress-sparkpost.php'    => 'SparkPost',
			'sendgrid-email-delivery-simplified/wpsendgrid.php' => 'SendGrid',
			'mailjet-for-wordpress/mailjet-for-wordpress.php' => 'Mailjet',
			'mailpoet/mailpoet.php'                => 'MailPoet',
			'brevo-email-marketing/brevo-email-marketing.php' => 'Brevo (Sendinblue)',
			'sendinblue/sendinblue.php'            => 'Sendinblue',
			'amazon-ses-and-target/amazon-ses.php' => 'Amazon SES',
			'wp-ses/wp-ses.php'                    => 'WP Offload SES',
			'offload-ses/offload-ses-lite.php'     => 'WP Offload SES Lite',
			'turbosmtp/turbo-smtp.php'             => 'TurboSMTP',
			'smtp2go/smtp2go.php'                  => 'SMTP2GO',
			'my-smtp-wp/my-smtp-wp.php'            => 'My SMTP WP',
			'wp-html-mail/wp-html-mail.php'        => 'WP HTML Mail',
			'configure-smtp/configure-smtp.php'    => 'Configure SMTP',
			'otter-blocks/otter-blocks.php'        => 'Otter Blocks (SMTP)',
			'suremails/suremails.php'              => 'SureMail',
			'gravity-smtp/gravity-smtp.php'        => 'Gravity SMTP',
			'mail-bank/mail-bank.php'              => 'Mail Bank',
			'solid-mail/solid-mail.php'            => 'Solid Mail',
			'mailin/sendinblue.php'                => 'Brevo Mail',
			'wp-ses/wp-offload-ses.php'            => 'WP Offload SES v2',
			'sendpulse-email/sendpulse.php'        => 'SendPulse',
			'elastic-email-sender/elastic-email-sender.php' => 'Elastic Email',
			'wp-mail-bank/wp-mail-bank.php'        => 'WP Mail Bank',
		);

		/**
		 * Cached conflict check results.
		 *
		 * @var array|null
		 */
		private $conflict_cache = null;

		/**
		 * Cached system check results.
		 *
		 * @var array|null
		 */
		private $system_check_cache = null;

		/**
		 * Option name for dismissed notices.
		 *
		 * @var string
		 */
		const DISMISSED_NOTICES_OPTION = 'authority_mailer_dismissed_notices';

		/**
		 * Minimum PHP version required.
		 *
		 * @var string
		 */
		const MIN_PHP_VERSION = '8.0';

		/**
		 * Minimum WordPress version required.
		 *
		 * @var string
		 */
		const MIN_WP_VERSION = '6.0';

		/**
		 * Get singleton instance.
		 *
		 * @since 1.0.0
		 * @return Authority_Mailer_Conflict_Detector
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		private function __construct() {
			$this->init_hooks();
		}

		/**
		 * Initialize hooks.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		private function init_hooks() {
			// Admin notices (only in wp-admin).
			add_action( 'admin_notices', array( $this, 'display_admin_notices' ) );

			// AJAX handler for dismissing notices.
			add_action( 'wp_ajax_authority_mailer_dismiss_notice', array( $this, 'ajax_dismiss_notice' ) );

			// Filter to disable SMTP functionality during conflicts.
			add_filter( 'authority_mailer_smtp_enabled', array( $this, 'filter_smtp_enabled' ), 10, 1 );

			// Enqueue admin scripts for dismissible notices.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

			// Add system status to plugin health checks.
			add_filter( 'authority_mailer_system_status', array( $this, 'add_system_status' ), 10, 1 );
		}

		/**
		 * Check for conflicting SMTP plugins.
		 *
		 * @since 1.0.0
		 * @return array Array of detected conflicts with plugin info.
		 */
		public function check_for_conflicts() {
			// Return cached result if available.
			if ( null !== $this->conflict_cache ) {
				return $this->conflict_cache;
			}

			$conflicts = array();

			// Ensure is_plugin_active() is available.
			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			/**
			 * Filter the list of conflicting plugins to check.
			 *
			 * Allows developers to add or remove plugins from the conflict check list.
			 *
			 * @since 1.0.0
			 *
			 * @param array $conflicting_plugins Array of plugin paths and names.
			 */
			$plugins_to_check = apply_filters( 'authority_mailer_conflicting_plugins', $this->conflicting_plugins );

			foreach ( $plugins_to_check as $plugin_path => $plugin_name ) {
				if ( is_plugin_active( $plugin_path ) ) {
					$conflicts[] = array(
						'path'           => $plugin_path,
						'name'           => $plugin_name,
						'deactivate_url' => $this->get_deactivate_url( $plugin_path ),
					);
				}
			}

			// Also check for any plugin with 'smtp' in the name that we might have missed.
			$all_plugins = get_plugins();

			// Get our own plugin basename for comparison.
			$our_plugin_basename = defined( 'AUTHORITY_MAILER_PLUGIN_FILE' )
				? plugin_basename( AUTHORITY_MAILER_PLUGIN_FILE )
				: '';
			$our_plugin_folder   = dirname( $our_plugin_basename );

			foreach ( $all_plugins as $plugin_path => $plugin_data ) {
				// Skip if already in our conflict list.
				if ( isset( $plugins_to_check[ $plugin_path ] ) ) {
					continue;
				}

				// Skip our own plugin using plugin basename comparison.
				$plugin_folder = dirname( $plugin_path );
				if ( $plugin_folder === $our_plugin_folder ) {
					continue;
				}

				// Also skip by checking common naming patterns.
				if ( strpos( $plugin_path, 'authority-mailer' ) !== false ) {
					continue;
				}

				// Check if plugin name or description contains SMTP-related keywords.
				$name_lower = strtolower( $plugin_data['Name'] );

				$smtp_keywords  = array( 'smtp', 'mail delivery', 'email delivery', 'transactional email' );
				$is_smtp_plugin = false;

				foreach ( $smtp_keywords as $keyword ) {
					if ( strpos( $name_lower, $keyword ) !== false ) {
						$is_smtp_plugin = true;
						break;
					}
				}

				if ( $is_smtp_plugin && is_plugin_active( $plugin_path ) ) {
					$conflicts[] = array(
						'path'           => $plugin_path,
						'name'           => $plugin_data['Name'],
						'deactivate_url' => $this->get_deactivate_url( $plugin_path ),
					);
				}
			}

			$this->conflict_cache = $conflicts;

			/**
			 * Fires after conflict detection is complete.
			 *
			 * @since 1.0.0
			 *
			 * @param array $conflicts Array of detected conflicts.
			 */
			do_action( 'authority_mailer_conflicts_detected', $conflicts );

			return $conflicts;
		}

		/**
		 * Perform system compatibility checks.
		 *
		 * Returns a structured array of check results for easy extension.
		 *
		 * @since 1.0.0
		 * @return array Array of system check results.
		 */
		public function check_system_compatibility() {
			// Return cached result if available.
			if ( null !== $this->system_check_cache ) {
				return $this->system_check_cache;
			}

			global $wp_version;

			$checks = array();

			// PHP Version Check.
			$checks['php_version'] = array(
				'id'          => 'php_version',
				'label'       => __( 'PHP Version', 'authority-mailer-smtp' ),
				'status'      => version_compare( PHP_VERSION, self::MIN_PHP_VERSION, '>=' ) ? 'good' : 'critical',
				'current'     => PHP_VERSION,
				'required'    => self::MIN_PHP_VERSION,
				'description' => version_compare( PHP_VERSION, self::MIN_PHP_VERSION, '>=' )
					? sprintf(
						/* translators: %s: PHP version */
						__( 'PHP version %s is compatible.', 'authority-mailer-smtp' ),
						PHP_VERSION
					)
					: sprintf(
						/* translators: 1: Current PHP version, 2: Required PHP version */
						__( 'PHP version %1$s is below the required %2$s. Please upgrade PHP.', 'authority-mailer-smtp' ),
						PHP_VERSION,
						self::MIN_PHP_VERSION
					),
				'action'      => version_compare( PHP_VERSION, self::MIN_PHP_VERSION, '>=' ) ? '' : __( 'Contact your hosting provider to upgrade PHP.', 'authority-mailer-smtp' ),
			);

			// WordPress Version Check.
			$checks['wp_version'] = array(
				'id'          => 'wp_version',
				'label'       => __( 'WordPress Version', 'authority-mailer-smtp' ),
				'status'      => version_compare( $wp_version, self::MIN_WP_VERSION, '>=' ) ? 'good' : 'critical',
				'current'     => $wp_version,
				'required'    => self::MIN_WP_VERSION,
				'description' => version_compare( $wp_version, self::MIN_WP_VERSION, '>=' )
					? sprintf(
						/* translators: %s: WordPress version */
						__( 'WordPress version %s is compatible.', 'authority-mailer-smtp' ),
						$wp_version
					)
					: sprintf(
						/* translators: 1: Current WP version, 2: Required WP version */
						__( 'WordPress version %1$s is below the required %2$s. Please update WordPress.', 'authority-mailer-smtp' ),
						$wp_version,
						self::MIN_WP_VERSION
					),
				'action'      => version_compare( $wp_version, self::MIN_WP_VERSION, '>=' ) ? '' : admin_url( 'update-core.php' ),
			);

			// WP-Cron Check.
			$cron_disabled     = defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON;
			$checks['wp_cron'] = array(
				'id'          => 'wp_cron',
				'label'       => __( 'WP-Cron', 'authority-mailer-smtp' ),
				'status'      => $cron_disabled ? 'warning' : 'good',
				'current'     => $cron_disabled ? __( 'Disabled', 'authority-mailer-smtp' ) : __( 'Enabled', 'authority-mailer-smtp' ),
				'required'    => __( 'Enabled (recommended)', 'authority-mailer-smtp' ),
				'description' => $cron_disabled
					? __( 'WP-Cron is disabled. Email queue processing and scheduled tasks may not work properly unless you have a real cron job configured.', 'authority-mailer-smtp' )
					: __( 'WP-Cron is enabled and working.', 'authority-mailer-smtp' ),
				'action'      => $cron_disabled ? __( 'Set up a real cron job or enable WP-Cron in wp-config.php', 'authority-mailer-smtp' ) : '',
			);

			// PHP mail() Function Check.
			// Optimize by using strpos on raw string instead of explode + array_map.
			$disable_functions  = ini_get( 'disable_functions' );
			$mail_in_disabled   = ! empty( $disable_functions ) && (
				strpos( $disable_functions, 'mail' ) !== false &&
				preg_match( '/\bmail\b/', $disable_functions )
			);
			$mail_disabled      = ! function_exists( 'mail' ) || $mail_in_disabled;
			$checks['php_mail'] = array(
				'id'          => 'php_mail',
				'label'       => __( 'PHP mail() Function', 'authority-mailer-smtp' ),
				'status'      => $mail_disabled ? 'warning' : 'good',
				'current'     => $mail_disabled ? __( 'Disabled', 'authority-mailer-smtp' ) : __( 'Available', 'authority-mailer-smtp' ),
				'required'    => __( 'Available (for fallback)', 'authority-mailer-smtp' ),
				'description' => $mail_disabled
					? __( 'PHP mail() is disabled. This is fine if using SMTP, but there is no fallback option.', 'authority-mailer-smtp' )
					: __( 'PHP mail() is available as a fallback option.', 'authority-mailer-smtp' ),
				'action'      => '',
			);

			// OpenSSL Extension Check (required for TLS/SSL connections).
			$openssl_loaded    = extension_loaded( 'openssl' );
			$checks['openssl'] = array(
				'id'          => 'openssl',
				'label'       => __( 'OpenSSL Extension', 'authority-mailer-smtp' ),
				'status'      => $openssl_loaded ? 'good' : 'critical',
				'current'     => $openssl_loaded ? OPENSSL_VERSION_TEXT : __( 'Not loaded', 'authority-mailer-smtp' ),
				'required'    => __( 'Required', 'authority-mailer-smtp' ),
				'description' => $openssl_loaded
					? __( 'OpenSSL is available for secure SMTP connections.', 'authority-mailer-smtp' )
					: __( 'OpenSSL is required for TLS/SSL encrypted SMTP connections.', 'authority-mailer-smtp' ),
				'action'      => $openssl_loaded ? '' : __( 'Contact your hosting provider to enable OpenSSL.', 'authority-mailer-smtp' ),
			);

			// cURL Extension Check (required for API-based providers).
			$curl_loaded    = extension_loaded( 'curl' );
			$curl_version   = $curl_loaded ? curl_version() : array();
			$checks['curl'] = array(
				'id'          => 'curl',
				'label'       => __( 'cURL Extension', 'authority-mailer-smtp' ),
				'status'      => $curl_loaded ? 'good' : 'critical',
				'current'     => $curl_loaded ? ( isset( $curl_version['version'] ) ? $curl_version['version'] : __( 'Loaded', 'authority-mailer-smtp' ) ) : __( 'Not loaded', 'authority-mailer-smtp' ),
				'required'    => __( 'Required', 'authority-mailer-smtp' ),
				'description' => $curl_loaded
					? __( 'cURL is available for API-based email providers.', 'authority-mailer-smtp' )
					: __( 'cURL is required for API-based email providers (SendGrid, Mailgun, etc.).', 'authority-mailer-smtp' ),
				'action'      => $curl_loaded ? '' : __( 'Contact your hosting provider to enable cURL.', 'authority-mailer-smtp' ),
			);

			// JSON Extension Check.
			$json_loaded    = extension_loaded( 'json' );
			$checks['json'] = array(
				'id'          => 'json',
				'label'       => __( 'JSON Extension', 'authority-mailer-smtp' ),
				'status'      => $json_loaded ? 'good' : 'critical',
				'current'     => $json_loaded ? __( 'Loaded', 'authority-mailer-smtp' ) : __( 'Not loaded', 'authority-mailer-smtp' ),
				'required'    => __( 'Required', 'authority-mailer-smtp' ),
				'description' => $json_loaded
					? __( 'JSON extension is available.', 'authority-mailer-smtp' )
					: __( 'JSON extension is required for processing API responses.', 'authority-mailer-smtp' ),
				'action'      => $json_loaded ? '' : __( 'Contact your hosting provider to enable JSON.', 'authority-mailer-smtp' ),
			);

			// Memory Limit Check.
			$memory_limit           = wp_convert_hr_to_bytes( ini_get( 'memory_limit' ) );
			$min_memory             = 64 * MB_IN_BYTES; // 64MB minimum.
			$checks['memory_limit'] = array(
				'id'          => 'memory_limit',
				'label'       => __( 'PHP Memory Limit', 'authority-mailer-smtp' ),
				'status'      => $memory_limit >= $min_memory ? 'good' : 'warning',
				'current'     => ini_get( 'memory_limit' ),
				'required'    => '64M',
				'description' => $memory_limit >= $min_memory
					? sprintf(
						/* translators: %s: Memory limit */
						__( 'Memory limit of %s is sufficient.', 'authority-mailer-smtp' ),
						ini_get( 'memory_limit' )
					)
					: sprintf(
						/* translators: %s: Memory limit */
						__( 'Memory limit of %s may be too low for processing large email queues.', 'authority-mailer-smtp' ),
						ini_get( 'memory_limit' )
					),
				'action'      => $memory_limit >= $min_memory ? '' : __( 'Increase memory_limit in php.ini or contact your hosting provider.', 'authority-mailer-smtp' ),
			);

			// Max Execution Time Check.
			$max_execution_time           = (int) ini_get( 'max_execution_time' );
			$checks['max_execution_time'] = array(
				'id'          => 'max_execution_time',
				'label'       => __( 'Max Execution Time', 'authority-mailer-smtp' ),
				'status'      => ( 0 === $max_execution_time || $max_execution_time >= 30 ) ? 'good' : 'warning',
				'current'     => 0 === $max_execution_time ? __( 'Unlimited', 'authority-mailer-smtp' ) : $max_execution_time . 's',
				'required'    => '30s',
				'description' => ( 0 === $max_execution_time || $max_execution_time >= 30 )
					? __( 'Max execution time is sufficient for email processing.', 'authority-mailer-smtp' )
					: __( 'Max execution time may be too short for large email batches.', 'authority-mailer-smtp' ),
				'action'      => ( 0 === $max_execution_time || $max_execution_time >= 30 ) ? '' : __( 'Increase max_execution_time in php.ini.', 'authority-mailer-smtp' ),
			);

			// Outbound Connections Check.
			$can_connect                    = $this->check_outbound_connections();
			$checks['outbound_connections'] = array(
				'id'          => 'outbound_connections',
				'label'       => __( 'Outbound Connections', 'authority-mailer-smtp' ),
				'status'      => $can_connect ? 'good' : 'warning',
				'current'     => $can_connect ? __( 'Working', 'authority-mailer-smtp' ) : __( 'May be blocked', 'authority-mailer-smtp' ),
				'required'    => __( 'Required', 'authority-mailer-smtp' ),
				'description' => $can_connect
					? __( 'Server can make outbound connections for SMTP and API calls.', 'authority-mailer-smtp' )
					: __( 'Outbound connections may be blocked. SMTP port access could be restricted.', 'authority-mailer-smtp' ),
				'action'      => $can_connect ? '' : __( 'Contact your hosting provider to ensure SMTP ports (25, 465, 587) are not blocked.', 'authority-mailer-smtp' ),
			);

			// Writable Temp Directory Check.
			$temp_dir                 = get_temp_dir();
			$temp_writable            = wp_is_writable( $temp_dir );
			$checks['temp_directory'] = array(
				'id'          => 'temp_directory',
				'label'       => __( 'Temp Directory', 'authority-mailer-smtp' ),
				'status'      => $temp_writable ? 'good' : 'warning',
				'current'     => $temp_writable ? __( 'Writable', 'authority-mailer-smtp' ) : __( 'Not writable', 'authority-mailer-smtp' ),
				'required'    => __( 'Writable', 'authority-mailer-smtp' ),
				'description' => $temp_writable
					? __( 'Temp directory is writable for attachment processing.', 'authority-mailer-smtp' )
					: __( 'Temp directory is not writable. Email attachments may not work properly.', 'authority-mailer-smtp' ),
				'action'      => $temp_writable ? '' : __( 'Ensure the temp directory has write permissions.', 'authority-mailer-smtp' ),
			);

			// Conflicting Plugins Check.
			$conflicts                  = $this->check_for_conflicts();
			$has_conflicts              = ! empty( $conflicts );
			$conflict_names             = array_map(
				function ( $c ) {
					return $c['name'];
				},
				$conflicts
			);
			$checks['plugin_conflicts'] = array(
				'id'          => 'plugin_conflicts',
				'label'       => __( 'Plugin Conflicts', 'authority-mailer-smtp' ),
				'status'      => $has_conflicts ? 'critical' : 'good',
				'current'     => $has_conflicts ? implode( ', ', $conflict_names ) : __( 'None detected', 'authority-mailer-smtp' ),
				'required'    => __( 'No conflicts', 'authority-mailer-smtp' ),
				'description' => $has_conflicts
					? sprintf(
						/* translators: %s: List of conflicting plugins */
						__( 'Conflicting SMTP plugins detected: %s. This may cause email delivery issues.', 'authority-mailer-smtp' ),
						implode( ', ', $conflict_names )
					)
					: __( 'No conflicting SMTP plugins detected.', 'authority-mailer-smtp' ),
				'action'      => $has_conflicts ? __( 'Deactivate conflicting plugins to ensure reliable email delivery.', 'authority-mailer-smtp' ) : '',
				'conflicts'   => $conflicts,
			);

			/**
			 * Filter system compatibility checks.
			 *
			 * Allows developers to add custom system checks.
			 *
			 * @since 1.0.0
			 *
			 * @param array $checks Array of system check results.
			 */
			$this->system_check_cache = apply_filters( 'authority_mailer_system_checks', $checks );

			return $this->system_check_cache;
		}

		/**
		 * Check if outbound connections are working.
		 *
		 * @since 1.0.0
		 * @return bool True if connections work, false otherwise.
		 */
		private function check_outbound_connections() {
			// Use transient to cache the result for 1 hour.
			$cached = get_transient( 'authority_mailer_outbound_check' );
			if ( false !== $cached ) {
				return 'yes' === $cached;
			}

			// Simple check - try to connect to WordPress.org.
			$response = wp_remote_get(
				'https://api.wordpress.org/core/version-check/1.7/',
				array(
					'timeout'   => 5,
					'sslverify' => true,
				)
			);

			$can_connect = ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response );

			set_transient( 'authority_mailer_outbound_check', $can_connect ? 'yes' : 'no', HOUR_IN_SECONDS );

			return $can_connect;
		}

		/**
		 * Display admin notices for conflicts and system issues.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function display_admin_notices() {
			// Only show in admin and to users who can manage options.
			if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$dismissed = get_option( self::DISMISSED_NOTICES_OPTION, array() );

			// Display conflict notices.
			$this->display_conflict_notices( $dismissed );

			// Display critical system issue notices.
			$this->display_system_notices( $dismissed );
		}

		/**
		 * Display notices for conflicting plugins.
		 *
		 * @since 1.0.0
		 *
		 * @param array $dismissed Array of dismissed notice IDs.
		 * @return void
		 */
		private function display_conflict_notices( $dismissed ) {
			$conflicts = $this->check_for_conflicts();

			if ( empty( $conflicts ) ) {
				return;
			}

			$notice_id = 'smtp_conflict_' . md5( wp_json_encode( array_column( $conflicts, 'path' ) ) );

			// Skip if this specific conflict combination was dismissed.
			if ( isset( $dismissed[ $notice_id ] ) && ( $dismissed[ $notice_id ] > time() - WEEK_IN_SECONDS ) ) {
				return;
			}

			$conflict_names = array_map(
				function ( $c ) {
					return '<strong>' . esc_html( $c['name'] ) . '</strong>';
				},
				$conflicts
			);

			$deactivate_links = array();
			foreach ( $conflicts as $conflict ) {
				$deactivate_links[] = sprintf(
					'<a href="%s">%s</a>',
					esc_url( $conflict['deactivate_url'] ),
					/* translators: %s: Plugin name */
					sprintf( __( 'Deactivate %s', 'authority-mailer-smtp' ), esc_html( $conflict['name'] ) )
				);
			}

			?>
			<div class="notice notice-warning is-dismissible authority-mailer-notice" data-notice-id="<?php echo esc_attr( $notice_id ); ?>">
				<p>
					<strong><?php esc_html_e( 'Authority Mailer SMTP Warning:', 'authority-mailer-smtp' ); ?></strong>
					<?php
					printf(
						/* translators: %s: List of conflicting plugin names */
						esc_html__( 'The following SMTP plugin(s) are active and may conflict with Authority Mailer: %s', 'authority-mailer-smtp' ),
						implode( ', ', $conflict_names ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped above.
					);
					?>
				</p>
				<p>
					<?php esc_html_e( 'Having multiple SMTP plugins active can cause email delivery issues. We recommend deactivating the conflicting plugin(s).', 'authority-mailer-smtp' ); ?>
				</p>
				<p>
					<?php echo implode( ' | ', $deactivate_links ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- URLs are escaped above. ?>
				</p>
			</div>
			<?php
		}

		/**
		 * Display notices for critical system issues.
		 *
		 * @since 1.0.0
		 *
		 * @param array $dismissed Array of dismissed notice IDs.
		 * @return void
		 */
		private function display_system_notices( $dismissed ) {
			$checks = $this->check_system_compatibility();

			foreach ( $checks as $check ) {
				// Only show notices for critical issues.
				if ( 'critical' !== $check['status'] ) {
					continue;
				}

				// Skip plugin conflicts - already handled above.
				if ( 'plugin_conflicts' === $check['id'] ) {
					continue;
				}

				$notice_id = 'system_' . $check['id'];

				// Skip if dismissed within the last week.
				if ( isset( $dismissed[ $notice_id ] ) && ( $dismissed[ $notice_id ] > time() - WEEK_IN_SECONDS ) ) {
					continue;
				}

				?>
				<div class="notice notice-error is-dismissible authority-mailer-notice" data-notice-id="<?php echo esc_attr( $notice_id ); ?>">
					<p>
						<strong><?php esc_html_e( 'Authority Mailer SMTP:', 'authority-mailer-smtp' ); ?></strong>
						<?php echo esc_html( $check['label'] ); ?> - <?php echo esc_html( $check['description'] ); ?>
					</p>
					<?php if ( ! empty( $check['action'] ) ) : ?>
						<p>
							<em><?php echo esc_html( $check['action'] ); ?></em>
						</p>
					<?php endif; ?>
				</div>
				<?php
			}
		}

		/**
		 * AJAX handler for dismissing notices.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function ajax_dismiss_notice() {
			check_ajax_referer( 'authority_mailer_dismiss_notice', 'nonce' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array( 'message' => __( 'Permission denied.', 'authority-mailer-smtp' ) ) );
			}

			$notice_id = isset( $_POST['notice_id'] ) ? sanitize_key( wp_unslash( $_POST['notice_id'] ) ) : '';

			if ( empty( $notice_id ) ) {
				wp_send_json_error( array( 'message' => __( 'Invalid notice ID.', 'authority-mailer-smtp' ) ) );
			}

			$dismissed               = get_option( self::DISMISSED_NOTICES_OPTION, array() );
			$dismissed[ $notice_id ] = time();

			// Limit stored dismissals to prevent option bloat.
			if ( count( $dismissed ) > 50 ) {
				// Remove oldest dismissals.
				asort( $dismissed );
				$dismissed = array_slice( $dismissed, -50, 50, true );
			}

			update_option( self::DISMISSED_NOTICES_OPTION, $dismissed );

			wp_send_json_success( array( 'message' => __( 'Notice dismissed.', 'authority-mailer-smtp' ) ) );
		}

		/**
		 * Filter to disable SMTP functionality during conflicts.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $enabled Whether SMTP is enabled.
		 * @return bool Modified enabled status.
		 */
		public function filter_smtp_enabled( $enabled ) {
			// If already disabled, don't change.
			if ( ! $enabled ) {
				return $enabled;
			}

			$conflicts = $this->check_for_conflicts();

			// Disable SMTP if conflicts are detected.
			if ( ! empty( $conflicts ) ) {
				/**
				 * Filter whether to disable SMTP during conflicts.
				 *
				 * Allows developers to override the automatic disabling behavior.
				 *
				 * @since 1.0.0
				 *
				 * @param bool  $disable   Whether to disable SMTP.
				 * @param array $conflicts Array of detected conflicts.
				 */
				$disable = apply_filters( 'authority_mailer_disable_on_conflict', true, $conflicts );

				if ( $disable ) {
					return false;
				}
			}

			return $enabled;
		}

		/**
		 * Enqueue admin scripts for dismissible notices.
		 *
		 * @since 1.0.0
		 *
		 * @param string $hook Current admin page hook.
		 * @return void
		 */
		public function enqueue_admin_scripts( $hook ) {
			// Only enqueue on pages where we might show notices.
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			// Enqueue conflict detector JavaScript.
			wp_enqueue_script(
				'authority-mailer-conflict-detector',
				AUTHORITY_MAILER_PLUGIN_URL . 'assets/js/conflict-detector.js',
				array( 'jquery' ),
				AUTHORITY_MAILER_VERSION,
				true
			);

			// Pass nonce to JavaScript via wp_localize_script.
			wp_localize_script(
				'authority-mailer-conflict-detector',
				'authorityMailerConflictDetector',
				array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'authority_mailer_dismiss_notice' ),
				)
			);
		}

		/**
		 * Add system status to plugin's status page.
		 *
		 * @since 1.0.0
		 *
		 * @param array $status Existing status array.
		 * @return array Modified status array.
		 */
		public function add_system_status( $status ) {
			$status['conflicts']     = $this->check_for_conflicts();
			$status['system_checks'] = $this->check_system_compatibility();
			return $status;
		}

		/**
		 * Get deactivation URL for a plugin.
		 *
		 * @since 1.0.0
		 *
		 * @param string $plugin_path Plugin path (folder/file.php).
		 * @return string Deactivation URL with nonce.
		 */
		private function get_deactivate_url( $plugin_path ) {
			return wp_nonce_url(
				admin_url( 'plugins.php?action=deactivate&plugin=' . urlencode( $plugin_path ) ),
				'deactivate-plugin_' . $plugin_path
			);
		}

		/**
		 * Check if there are any critical issues preventing email delivery.
		 *
		 * @since 1.0.0
		 * @return bool True if there are critical issues.
		 */
		public function has_critical_issues() {
			$checks = $this->check_system_compatibility();

			foreach ( $checks as $check ) {
				if ( 'critical' === $check['status'] ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Get a summary of all issues.
		 *
		 * @since 1.0.0
		 * @return array Array with 'critical', 'warning', and 'good' counts and issues.
		 */
		public function get_issues_summary() {
			$checks = $this->check_system_compatibility();

			$summary = array(
				'critical' => array(),
				'warning'  => array(),
				'good'     => array(),
			);

			foreach ( $checks as $check ) {
				$summary[ $check['status'] ][] = $check;
			}

			return $summary;
		}

		/**
		 * Clear cached check results.
		 *
		 * Useful when plugin status changes (activation/deactivation).
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function clear_cache() {
			$this->conflict_cache     = null;
			$this->system_check_cache = null;
			delete_transient( 'authority_mailer_outbound_check' );
		}

		/**
		 * Add a custom conflicting plugin to the check list.
		 *
		 * @since 1.0.0
		 *
		 * @param string $plugin_path Plugin path (folder/file.php).
		 * @param string $plugin_name Plugin display name.
		 * @return void
		 */
		public function add_conflicting_plugin( $plugin_path, $plugin_name ) {
			$this->conflicting_plugins[ $plugin_path ] = $plugin_name;
			$this->conflict_cache                      = null; // Clear cache.
		}

		/**
		 * Remove a plugin from the conflict check list.
		 *
		 * @since 1.0.0
		 *
		 * @param string $plugin_path Plugin path to remove.
		 * @return void
		 */
		public function remove_conflicting_plugin( $plugin_path ) {
			unset( $this->conflicting_plugins[ $plugin_path ] );
			$this->conflict_cache = null; // Clear cache.
		}
	}
}

/**
 * Initialize the Conflict Detector.
 *
 * @since 1.0.0
 * @return Authority_Mailer_Conflict_Detector
 */
function authority_mailer_conflict_detector() {
	return Authority_Mailer_Conflict_Detector::get_instance();
}

// Initialize on plugins_loaded to ensure all plugins are loaded first.
add_action( 'plugins_loaded', 'authority_mailer_conflict_detector', 20 );
