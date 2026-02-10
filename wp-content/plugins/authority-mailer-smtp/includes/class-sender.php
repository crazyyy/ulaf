<?php
/**
 * Authority Mailer Sender Class
 *
 * Authority Mailer sender integration that supports the 17 onboarding providers:
 *   sendlayer, smtpcom, brevo, aws, elastic, gmail, mailersend, mailgun,
 *   mailjet, mandrill, office365, postmark, sendgrid, smtp2go, sparkpost,
 *   zoho, other
 *
 * Behavior:
 *  - Reads runtime settings from option 'authority_mailer_smtp_options' on every send.
 *  - Uses providers_map to decide adapter filename and default type (api|smtp).
 *  - For SMTP providers: configures PHPMailer on phpmailer_init and allows wp_mail to proceed.
 *  - For API providers: attempts to call provider adapter (authority_mailer_send_{provider})
 *    and short-circuits wp_mail when adapter returns true.
 *
 * Adapter contract:
 *   includes/providers/{adapter-file}.php should expose a function:
 *     authority_mailer_send_{provider}( $to, $subject, $message, $headers, $attachments, $settings )
 *   The adapter must return true on success or a WP_Error on failure.
 *
 * Notes:
 *  - The class is defensive: if adapters or helpers are missing it falls back to WP behavior.
 *  - It expects common helpers (authority_mailer_smtp_to_bool, authority_mailer_http_post_and_log, authority_mailer_debug_log)
 *    to be available if adapters use them, but never requires them directly here.
 *
 * @package Authority_Mailer
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Authority_Mailer_Sender' ) ) {

	/**
	 * Authority_Mailer_Sender class.
	 *
	 * Handles email sending integration for supported providers including SMTP and API delivery.
	 *
	 * @since 1.0.0
	 */
	class Authority_Mailer_Sender {

		const OPTION_KEY = 'authority_mailer_smtp_options';

		/**
		 * Map of onboarding provider ids -> adapter file and default type.
		 * Keys correspond to the tile ids used in onboarding.
		 *
		 * @var array
		 */
		private static $providers_map = array(
			'sendlayer'  => array(
				'file' => 'sendlayer.php',
				'type' => 'api',
			),
			'smtpcom'    => array(
				'file' => 'smtpcom.php',
				'type' => 'api',
			),
			'brevo'      => array(
				'file' => 'brevo.php',
				'type' => 'api',
			),
			'aws'        => array(
				'file' => 'aws.php',
				'type' => 'smtp',
			), // Amazon SES SMTP adapter.
			'elastic'    => array(
				'file' => 'elasticmail.php',
				'type' => 'api',
			), // Onboarding id 'elastic' -> adapter 'elasticmail.php'.
			'gmail'      => array(
				'file' => 'google.php',
				'type' => 'api',
			), // 'gmail' tile uses google adapter.
			'mailersend' => array(
				'file' => 'mailersend.php',
				'type' => 'api',
			),
			'mailgun'    => array(
				'file' => 'mailgun.php',
				'type' => 'api',
			),
			'mailjet'    => array(
				'file' => 'mailjet.php',
				'type' => 'api',
			),
			'mandrill'   => array(
				'file' => 'mandrill.php',
				'type' => 'api',
			),
			'office365'  => array(
				'file' => 'office365.php',
				'type' => 'smtp',
			), // Office365 commonly used via SMTP
			'postmark'   => array(
				'file' => 'postmark.php',
				'type' => 'api',
			),
			'sendgrid'   => array(
				'file' => 'sendgrid.php',
				'type' => 'api',
			),
			'smtp2go'    => array(
				'file' => 'smtp2go.php',
				'type' => 'api',
			),
			'sparkpost'  => array(
				'file' => 'sparkpost.php',
				'type' => 'api',
			),
			'zoho'       => array(
				'file' => 'zoho.php',
				'type' => 'smtp',
			),
			'other'      => array(
				'file' => 'other.php',
				'type' => 'smtp',
			),
		);

		/**
		 * Initialize hooks.
		 */
		public static function init() {
			add_filter( 'pre_wp_mail', array( __CLASS__, 'pre_wp_mail' ), 10, 2 );
			add_action( 'phpmailer_init', array( __CLASS__, 'configure_phpmailer' ), 10, 1 );
			add_action( 'phpmailer_init', array( __CLASS__, 'apply_email_defaults_phpmailer' ), 12, 1 );
			add_action( 'phpmailer_init', array( __CLASS__, 'apply_gdpr_filters_phpmailer' ), 15, 1 );
			add_action( 'phpmailer_init', array( __CLASS__, 'log_smtp_email_on_phpmailer_init' ), 999, 1 );
			add_filter( 'wp_mail_from', array( __CLASS__, 'wp_mail_from' ), 20, 1 );
			add_filter( 'wp_mail_from_name', array( __CLASS__, 'wp_mail_from_name' ), 20, 1 );

			// WordPress 5.9+ hooks for updating log status
			add_action( 'wp_mail_succeeded', array( __CLASS__, 'log_smtp_email_success' ), 10, 1 );
			add_action( 'wp_mail_failed', array( __CLASS__, 'log_smtp_email_failure' ), 10, 1 );
		}

		/**
		 * Store the current log ID for SMTP emails being sent.
		 *
		 * @var int
		 */
		private static $current_smtp_log_id = 0;

		/**
		 * Store email data temporarily for logging.
		 *
		 * @var array
		 */
		private static $current_email_data = array();

		/**
		 * Store the current spam score for logging.
		 *
		 * @var float|null
		 */
		private static $current_spam_score = null;

		/**
		 * Store the current tracking ID for logging and GDPR compliance.
		 *
		 * @var string|null
		 */
		private static $current_tracking_id = null;

		/**
		 * Resolve selected provider and provider settings from the options table.
		 *
		 * Returns:
		 *   array(
		 *     'provider' => string,           // canonical provider id or '' if none
		 *     'settings' => array,            // merged provider settings (provider sub-array over top-level)
		 *     'is_smtp'  => bool,             // whether provider should use SMTP transport
		 *     'adapter_path' => string|null,  // realpath to adapter file if exists
		 *   )
		 */
		private static function resolve_provider_and_settings() {
			$options = get_option( self::OPTION_KEY, array() );
			if ( ! is_array( $options ) ) {
				$options = array();
			}

			$provider = isset( $options['selected_mailer'] ) ? sanitize_key( $options['selected_mailer'] ) : '';

			// Normalize a couple of common aliases (onboarding tiles -> adapter naming)
			$alias_map = array(
				'gmail'   => 'gmail',    // onboarding uses 'gmail' but adapter file is 'google.php' (providers_map handles file)
				'elastic' => 'elastic',
				'bird'    => 'sparkpost',
			);
			if ( isset( $alias_map[ $provider ] ) ) {
				$provider = $alias_map[ $provider ];
			}

			// Provider-specific sub-array (if present) should override top-level options
			$provider_settings = array();
			if ( $provider && isset( $options[ $provider ] ) && is_array( $options[ $provider ] ) ) {
				$provider_settings = $options[ $provider ];
			}
			$merged = array_merge( is_array( $options ) ? $options : array(), is_array( $provider_settings ) ? $provider_settings : array() );

			// Decide default provider type from providers_map if known
			$default_type = null;
			if ( $provider && isset( self::$providers_map[ $provider ] ) ) {
				$default_type = self::$providers_map[ $provider ]['type'];
			}

			// Determine whether this provider should be treated as SMTP
			$is_smtp = false;
			if ( 'other' === $provider ) {
				$is_smtp = true;
			} elseif ( 'smtp' === $default_type ) {
				$is_smtp = true;
			} elseif ( 'brevo' === $provider && isset( $merged['brevo_use_smtp'] ) ) {
				// Brevo can use either API or SMTP mode based on explicit toggle.
				// Use helper function if available, otherwise fallback to strict checks.
				$brevo_smtp_enabled = function_exists( 'authority_mailer_smtp_to_bool' )
					? authority_mailer_smtp_to_bool( $merged['brevo_use_smtp'] )
					: ( true === $merged['brevo_use_smtp'] || '1' === $merged['brevo_use_smtp'] || 1 === $merged['brevo_use_smtp'] || 'on' === $merged['brevo_use_smtp'] || 'yes' === strtolower( trim( (string) $merged['brevo_use_smtp'] ) ) || 'true' === strtolower( trim( (string) $merged['brevo_use_smtp'] ) ) );
				if ( $brevo_smtp_enabled ) {
					$is_smtp = true;
				}
			} elseif ( 'brevo' !== $provider ) {
				// If host keys exist in merged settings, treat as SMTP even if default is API.
				// Note: Brevo is excluded because it has an explicit brevo_use_smtp toggle.
				$host_keys = array(
					$provider . '_smtp_host',
					$provider . '_host',
					'smtp_host',
					'host',
					$provider . '_smtp_server',
					'smtp_server',
					$provider . '_hostname',
					'smtp_hostname',
				);
				foreach ( $host_keys as $k ) {
					if ( isset( $merged[ $k ] ) && '' !== trim( (string) $merged[ $k ] ) ) {
						$is_smtp = true;
						break;
					}
				}
			}

			// Resolve adapter path if exists in includes/providers
			$adapter_path = null;
			if ( $provider && isset( self::$providers_map[ $provider ] ) ) {
				$providers_dir = AUTHORITY_MAILER_PLUGIN_DIR . 'includes/providers/';
				$file          = self::$providers_map[ $provider ]['file'];
				$candidate     = $providers_dir . $file;
				$real          = realpath( $candidate );
				if ( $real && is_file( $real ) ) {
					$adapter_path = $real;
				}
			}

			return array(
				'provider'     => $provider,
				'settings'     => is_array( $merged ) ? $merged : array(),
				'is_smtp'      => (bool) $is_smtp,
				'adapter_path' => $adapter_path,
			);
		}

		/**
		 * Build a normalized wp_mail argument set from the $atts passed to pre_wp_mail.
		 *
		 * $atts can be either the array passed to wp_mail (associative) or numeric array.
		 *
		 * @param array|string $atts The mail attributes array or string.
		 * @return array Normalized mail attributes with keys: to, subject, message, headers, attachments.
		 */
		private static function normalize_mail_atts( $atts ) {
			$defaults = array(
				'to'          => '',
				'subject'     => '',
				'message'     => '',
				'headers'     => '',
				'attachments' => array(),
			);

			if ( is_array( $atts ) ) {
				// assoc keys: to, subject, message, headers, attachments
				$out = $defaults;
				// If it's a numerically indexed call (legacy), map by position
				if ( isset( $atts[0] ) || isset( $atts[1] ) ) {
					$out['to']          = isset( $atts[0] ) ? $atts[0] : '';
					$out['subject']     = isset( $atts[1] ) ? $atts[1] : '';
					$out['message']     = isset( $atts[2] ) ? $atts[2] : '';
					$out['headers']     = isset( $atts[3] ) ? $atts[3] : '';
					$out['attachments'] = isset( $atts[4] ) ? $atts[4] : array();
				}
				// Override with named keys if present
				foreach ( array( 'to', 'subject', 'message', 'headers', 'attachments' ) as $k ) {
					if ( array_key_exists( $k, $atts ) ) {
						$out[ $k ] = $atts[ $k ];
					}
				}
				return $out;
			}

			// Not an array: treat as single 'to' value
			$defaults['to'] = (string) $atts;
			return $defaults;
		}

		/**
		 * Pre_wp_mail handler: try to send via API provider adapter and short-circuit wp_mail.
		 *
		 * Returns:
		 *   - null to let WP continue with default mailer
		 *   - true to indicate success and short-circuit wp_mail
		 *   - WP_Error to indicate failure
		 *
		 * @param null|bool|WP_Error $pre  The pre-filtered value (null by default).
		 * @param array              $atts The wp_mail attributes.
		 * @return null|bool|WP_Error Null to continue, true for success, WP_Error for failure.
		 */
		public static function pre_wp_mail( $pre, $atts ) {
			// Validate $atts is an array before processing
			if ( ! is_array( $atts ) ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
					error_log( '[Authority Mailer Sender] pre_wp_mail: atts is not an array (type: ' . gettype( $atts ) . '), returning null' );
				}
				// Return null to respect earlier filter decisions (e.g., email queued by integrator)
				return null;
			}

			// Debug logging
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
				error_log( '[Authority Mailer Sender] pre_wp_mail called | atts keys: ' . wp_json_encode( array_keys( $atts ) ) );
			}

			// Respect earlier handlers
			if ( null !== $pre ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
					error_log( '[Authority Mailer Sender] pre_wp_mail: pre is not null, returning early' );
				}
				return $pre;
			}

			// Capture spam score from atts if present (set by Email Integrator).
			if ( isset( $atts['authority-mailer_spam_score'] ) ) {
				self::$current_spam_score = floatval( $atts['authority-mailer_spam_score'] );
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
					error_log( '[Authority Mailer Sender] Spam score captured from atts: ' . self::$current_spam_score );
				}
			} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
					error_log( '[Authority Mailer Sender] No spam score found in atts' );
			}

			// Capture tracking ID from atts if present (set by Tracking Injector).
			if ( isset( $atts['authority-mailer_tracking_id'] ) ) {
				self::$current_tracking_id = sanitize_text_field( $atts['authority-mailer_tracking_id'] );
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
					error_log( '[Authority Mailer Sender] Tracking ID captured from atts: ' . self::$current_tracking_id );
				}
			} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
					error_log( '[Authority Mailer Sender] No tracking ID found in atts' );
			}

			// Check suppression list before sending.
			$email_args = self::normalize_mail_atts( $atts );
			$recipient  = is_array( $email_args['to'] ) ? $email_args['to'][0] : $email_args['to'];

			if ( class_exists( 'Authority_Mailer_Recipient_Manager' ) ) {
				$recipient_manager = Authority_Mailer_Recipient_Manager::get_instance();
				if ( $recipient_manager && $recipient_manager->is_suppressed( $recipient ) ) {
					if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
						// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
						error_log( '[Authority Mailer Sender] Email blocked: recipient is suppressed: ' . $recipient );
					}
					// Return WP_Error to prevent email from being sent.
					return new WP_Error( 'suppressed_recipient', 'Email address is on the suppression list and cannot receive emails.' );
				}
			}

			$res          = self::resolve_provider_and_settings();
			$provider     = $res['provider'];
			$settings     = $res['settings'];
			$is_smtp      = $res['is_smtp'];
			$adapter_path = $res['adapter_path'];

			// Apply failover provider selection.
			if ( class_exists( 'Authority_Mailer_Failover_Manager' ) ) {
				$failover = Authority_Mailer_Failover_Manager::get_instance();
				if ( $failover ) {
					// Let failover manager select the optimal provider.
					$atts = apply_filters( 'authority_mailer_before_send', $atts, $settings );

					// Override provider if failover selected a different one.
					if ( isset( $atts['_selected_provider'] ) && $atts['_selected_provider'] !== $provider ) {
						$override_provider = $atts['_selected_provider'];

						// Temporarily override the option to resolve the correct provider.
						$override_callback = function ( $options ) use ( $override_provider ) {
							if ( is_array( $options ) ) {
								$options['selected_mailer'] = $override_provider;
							}
							return $options;
						};

						add_filter( 'option_authority_mailer_smtp_options', $override_callback, 999 );

						// Re-resolve with overridden provider.
						$res          = self::resolve_provider_and_settings();
						$provider     = $res['provider'];
						$settings     = $res['settings'];
						$is_smtp      = $res['is_smtp'];
						$adapter_path = $res['adapter_path'];

						// Remove the temporary filter.
						remove_filter( 'option_authority_mailer_smtp_options', $override_callback, 999 );
					}
				}
			}

			// Log all 17 providers and which one is selected
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG && function_exists( 'authority_mailer_debug_log' ) ) {
				$all_providers = array_keys( self::$providers_map );
				authority_mailer_debug_log( '[Authority Mailer Providers] Available providers: ' . implode( ', ', $all_providers ) );
				authority_mailer_debug_log( '[Authority Mailer Providers] Selected provider: ' . ( $provider ? $provider : 'none' ) );
				authority_mailer_debug_log( '[Authority Mailer Providers] Provider type: ' . ( $is_smtp ? 'SMTP' : 'API' ) );
			}

			// No provider selected -> don't intercept
			if ( '' === $provider ) {
				return null;
			}

			// If provider is SMTP-style, allow WP to use PHPMailer (configured in phpmailer_init)
			if ( $is_smtp ) {
				// Spam score will be captured and logged in log_smtp_email_on_phpmailer_init
				return null;
			}

			// API-style: adapter must be present and expose a send function
			if ( empty( $adapter_path ) ) {
				// adapter missing -> do not intercept
				return null;
			}

			// Include adapter (safe due to realpath check above)
			@include_once $adapter_path;

			$basename = pathinfo( $adapter_path, PATHINFO_FILENAME );

			// Candidate function names
			$candidates = array(
				'authority_mailer_smtp_send_' . $provider,
				'authority_mailer_smtp_send_' . $basename,
			);

			$fn = '';
			foreach ( $candidates as $c ) {
				if ( function_exists( $c ) ) {
					$fn = $c;
					break;
				}
			}
			if ( ! $fn ) {
				// Adapter file included but doesn't expose expected function -> do not intercept
				return null;
			}

			// Normalize arguments
			$args        = self::normalize_mail_atts( $atts );
			$to          = $args['to'];
			$subject     = $args['subject'];
			$message     = $args['message'];
			$headers     = $args['headers'];
			$attachments = $args['attachments'];

			// Determine content type from headers (default to text/plain)
			$content_type   = 'text/plain';
			$parsed_headers = self::parse_headers( $headers );
			if ( ! empty( $parsed_headers['content-type'] ) ) {
				// Extract content type (e.g., "text/html; charset=UTF-8" -> "text/html")
				$ct_parts     = explode( ';', $parsed_headers['content-type'] );
				$content_type = strtolower( trim( $ct_parts[0] ) );
			}

			// Build email array in the format expected by provider adapters
			$email = array(
				'to'           => $to,
				'subject'      => $subject,
				'message'      => $message,
				'headers'      => $headers,
				'attachments'  => $attachments,
				'content_type' => $content_type,
			);

			// Apply email defaults if available.
			if ( class_exists( 'Authority_Mailer_Email_Defaults' ) ) {
				$email_before = $email;
				$email = Authority_Mailer_Email_Defaults::apply_defaults( $email );

				if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
					$defaults_changed = ( $email !== $email_before );
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
					error_log( '[Authority Mailer Sender] Email defaults ' . ( $defaults_changed ? 'modified email array' : 'returned email unchanged' ) . ' for API provider' );
				}
			}

			// Add spam score if present (captured from Email Integrator).
			if ( null !== self::$current_spam_score ) {
				$email['spam_score'] = self::$current_spam_score;
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
					error_log( '[Authority Mailer Sender] Adding spam score to email array for API provider: ' . self::$current_spam_score );
				}
			} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
					error_log( '[Authority Mailer Sender] No spam score to add to email array for API provider' );
			}

			// Add tracking ID if present (captured from Tracking Injector).
			if ( isset( $atts['authority-mailer_tracking_id'] ) ) {
				$email['tracking_id'] = $atts['authority-mailer_tracking_id'];
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
					error_log( '[Authority Mailer Sender] Adding tracking ID to email array for API provider: ' . $atts['authority-mailer_tracking_id'] );
				}
			}

			// Apply GDPR compliance filters (unsubscribe links, headers).
			// These filters are registered by the compliance class.
			$email['headers'] = apply_filters( 'authority_mailer_email_headers', $headers, $email );
			$email['message'] = apply_filters( 'authority_mailer_email_content', $message, $email );

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
				error_log( '[Authority Mailer Sender] GDPR filters applied for API provider' );
			}

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
				error_log( '[Authority Mailer Sender] Calling API adapter: ' . $fn . ' | provider: ' . $provider );
			}
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG && function_exists( 'authority_mailer_debug_log' ) ) {
				authority_mailer_debug_log( '[Authority Mailer Provider] Sending email via API provider: ' . $provider );
			}

			// Call adapter defensively with the email array
			try {
				$result = call_user_func( $fn, $email );
			} catch ( Exception $e ) {
				$result = new WP_Error( 'authority_mailer_smtp_adapter_exception', $e->getMessage() );
			}

			// Adapter contract: return true on success, WP_Error on failure
			if ( is_wp_error( $result ) ) {
				// Trigger failover on send failure.
				if ( class_exists( 'Authority_Mailer_Failover_Manager' ) ) {
					$email_data = array_merge( $email, array( '_selected_provider' => $provider ) );
					do_action(
						'authority_mailer_send_failed',
						$email_data,
						array(
							'code'     => $result->get_error_code(),
							'message'  => $result->get_error_message(),
							'provider' => $provider,
						)
					);
				}

				// Propagate failure
				return $result;
			}
			if ( true === $result ) {
				// Successful send; short-circuit default mailer
				return true;
			}

			// Adapter didn't provide definitive response -> let WP continue
			return null;
		}

		/**
		 * Resolve force-from email and name settings for a provider.
		 *
		 * Returns an array: [ force_email, force_name, force_email_flag, force_name_flag, debug_info ]
		 *
		 * @param string $provider The provider key (e.g., 'smtp', 'sendgrid').
		 * @param array  $options  The plugin options array.
		 * @return array
		 */
		private static function resolve_force_from( $provider, $options ) {
			$debug = array(
				'provider'     => $provider,
				'checked_keys' => array(),
			);

			// Resolve force_from_email flag
			$force_email_flag = false;
			if ( isset( $options[ $provider . '_force_from_email' ] ) ) {
				$debug['checked_keys'][] = $provider . '_force_from_email';
				if ( function_exists( 'authority_mailer_smtp_to_bool' ) ) {
					$force_email_flag = authority_mailer_smtp_to_bool( $options[ $provider . '_force_from_email' ] );
				} else {
					$force_email_flag = (bool) $options[ $provider . '_force_from_email' ];
				}
			} elseif ( isset( $options['force_from_email'] ) ) {
				$debug['checked_keys'][] = 'force_from_email';
				if ( function_exists( 'authority_mailer_smtp_to_bool' ) ) {
					$force_email_flag = authority_mailer_smtp_to_bool( $options['force_from_email'] );
				} else {
					$force_email_flag = (bool) $options['force_from_email'];
				}
			}

			// Resolve force_from_name flag
			$force_name_flag = false;
			if ( isset( $options[ $provider . '_force_from_name' ] ) ) {
				$debug['checked_keys'][] = $provider . '_force_from_name';
				if ( function_exists( 'authority_mailer_smtp_to_bool' ) ) {
					$force_name_flag = authority_mailer_smtp_to_bool( $options[ $provider . '_force_from_name' ] );
				} else {
					$force_name_flag = (bool) $options[ $provider . '_force_from_name' ];
				}
			} elseif ( isset( $options['force_from_name'] ) ) {
				$debug['checked_keys'][] = 'force_from_name';
				if ( function_exists( 'authority_mailer_smtp_to_bool' ) ) {
					$force_name_flag = authority_mailer_smtp_to_bool( $options['force_from_name'] );
				} else {
					$force_name_flag = (bool) $options['force_from_name'];
				}
			}

			// Resolve the actual email address
			$force_email = '';
			if ( isset( $options[ $provider . '_from_email' ] ) && '' !== $options[ $provider . '_from_email' ] ) {
				$force_email             = sanitize_email( $options[ $provider . '_from_email' ] );
				$debug['checked_keys'][] = $provider . '_from_email';
			} elseif ( isset( $options['from_email'] ) && '' !== $options['from_email'] ) {
				$force_email             = sanitize_email( $options['from_email'] );
				$debug['checked_keys'][] = 'from_email';
			}

			// Resolve the actual from name
			$force_name = '';
			if ( isset( $options[ $provider . '_from_name' ] ) && '' !== $options[ $provider . '_from_name' ] ) {
				$force_name              = sanitize_text_field( $options[ $provider . '_from_name' ] );
				$debug['checked_keys'][] = $provider . '_from_name';
			} elseif ( isset( $options['from_name'] ) && '' !== $options['from_name'] ) {
				$force_name              = sanitize_text_field( $options['from_name'] );
				$debug['checked_keys'][] = 'from_name';
			}

			$debug['force_email_flag'] = $force_email_flag;
			$debug['force_name_flag']  = $force_name_flag;
			$debug['force_email']      = $force_email;
			$debug['force_name']       = $force_name;

			return array( $force_email, $force_name, $force_email_flag, $force_name_flag, $debug );
		}

		/**
		 * Configure PHPMailer based on saved SMTP settings.
		 *
		 * Only configures when provider resolves to SMTP and host is present.
		 */
		public static function configure_phpmailer( $phpmailer ) {
			if ( ! $phpmailer ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG && function_exists( 'authority_mailer_debug_log' ) ) {
					authority_mailer_debug_log( '[Authority Mailer Provider] configure_phpmailer skipped: phpmailer object is null' );
				}
				return;
			}

			$res      = self::resolve_provider_and_settings();
			$provider = $res['provider'];
			$options  = $res['settings'];
			$is_smtp  = $res['is_smtp'];

			$should_configure = ( '' !== $provider && $is_smtp );

			if ( ! $should_configure ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG && function_exists( 'authority_mailer_debug_log' ) ) {
					authority_mailer_debug_log( '[Authority Mailer Provider] configure_phpmailer skipped: provider=' . ( $provider ? $provider : 'none' ) . ', is_smtp=' . ( $is_smtp ? 'true' : 'false' ) );
				}
				return;
			}

			// Log SMTP provider being used
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG && function_exists( 'authority_mailer_debug_log' ) ) {
				authority_mailer_debug_log( '[Authority Mailer Provider] Configuring SMTP provider: ' . $provider );
			}

			$maybe = function ( $keys ) use ( $options ) {
				foreach ( (array) $keys as $k ) {
					if ( isset( $options[ $k ] ) && '' !== trim( (string) $options[ $k ] ) ) {
						return $options[ $k ];
					}
				}
				return null;
			};

				// FIXED: Include all possible key naming conventions used by different parts of the plugin
				$host      = $maybe( array( $provider . '_smtp_host', $provider . '_host', 'smtp_host', 'host' ) );
				$port      = $maybe( array( $provider . '_smtp_port', 'smtp_port', 'port' ) );
				$user      = $maybe( array( $provider . '_smtp_username', $provider . '_smtp_user', $provider . '_username', 'smtp_username', 'smtp_user', 'username' ) );
				$pass      = $maybe( array( $provider . '_smtp_password', $provider . '_smtp_pass', $provider . '_password', 'smtp_password', 'smtp_pass', 'password' ) );
				$secure    = $maybe( array( $provider . '_smtp_encryption', $provider . '_smtp_secure', $provider . '_encryption', 'smtp_encryption', 'smtp_secure', $provider . '_secure', 'encryption', 'secure' ) );
				$auth_flag = $maybe( array( $provider . '_smtp_auth', 'smtp_auth', $provider . '_auth', 'auth' ) );

			if ( empty( $host ) ) {
				return;
			}

				// Force PHPMailer to use SMTP
			if ( method_exists( $phpmailer, 'isSMTP' ) ) {
				$phpmailer->isSMTP();
			} else {
				$phpmailer->Mailer = 'smtp';
			}

				// Basic connection/auth configuration
				$phpmailer->Host = sanitize_text_field( wp_unslash( (string) $host ) );
				$phpmailer->Port = $port ? intval( $port ) : 587;

				// SMTPAuth: true if username exists OR explicit auth flag is set
				$phpmailer->SMTPAuth = ( $user || $auth_flag ) ? true : false;

			if ( $user ) {
				$phpmailer->Username = sanitize_text_field( wp_unslash( (string) $user ) );
			}
			if ( $pass ) {
				// Don't sanitize password - may contain special characters like &, <, >, etc.
				$phpmailer->Password = (string) $pass;
			}

				$secure = $secure ? strtolower( trim( (string) $secure ) ) : '';

				// AUTO-CORRECT common port/encryption mismatches to prevent hangs
				$port_int = intval( $phpmailer->Port );
			if ( 465 === $port_int && 'tls' === $secure ) {
				// Port 465 requires implicit SSL, not STARTTLS
				$secure = 'ssl';
			} elseif ( 587 === $port_int && 'ssl' === $secure ) {
				// Port 587 uses STARTTLS, not implicit SSL
				$secure = 'tls';
			}

				// Choose SMTPSecure value (respect explicit secure setting, but fall back sensibly)
			if ( in_array( $secure, array( 'ssl', 'tls' ), true ) ) {
				$phpmailer->SMTPSecure = $secure;
			} elseif ( 'none' === $secure || '' === $secure ) {
				// Explicit "none" or empty = no encryption
				$phpmailer->SMTPSecure  = '';
				$phpmailer->SMTPAutoTLS = false;
			} else {
				// Default based on port
				$phpmailer->SMTPSecure = ( 465 === intval( $phpmailer->Port ) ) ? 'ssl' : 'tls';
			}

				// CRITICAL: Set timeouts to prevent infinite hangs
				$phpmailer->Timeout       = 10; // Connection timeout in seconds (reduced from 15)
				$phpmailer->SMTPKeepAlive = false;

				// Disable PHPMailer's automatic STARTTLS attempt if using implicit SSL (port 465)
			if ( 'ssl' === $phpmailer->SMTPSecure || 465 === intval( $phpmailer->Port ) ) {
				$phpmailer->SMTPAutoTLS = false;
			} else {
				$phpmailer->SMTPAutoTLS = true;
			}

				// Define SMTPOptions to control SSL verification
				$allow_insecure         = ! empty( $options['allow_insecure'] ) || ! empty( $options[ $provider . '_allow_insecure' ] );
				$ssl_opts               = array(
					'verify_peer'       => ! $allow_insecure,
					'verify_peer_name'  => ! $allow_insecure,
					'allow_self_signed' => $allow_insecure,
				);
				$phpmailer->SMTPOptions = array( 'ssl' => $ssl_opts );

				// SMTP Debug is disabled in production. Use AUTHORITY_MAILER_SMTP_DEBUG constant for debugging.
				if ( defined( 'AUTHORITY_MAILER_SMTP_DEBUG' ) && AUTHORITY_MAILER_SMTP_DEBUG && ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) {
					$phpmailer->SMTPDebug   = 2; // 1 = client, 2 = client + server.
					$phpmailer->Debugoutput = 'error_log'; // PHPMailer config option, not a direct error_log call.
				}

				// Respect provider-level force-from options using the robust resolver.
				list( $force_email, $force_name, $feed_flag, $fn_flag, $dbg ) = self::resolve_force_from( $provider, $options );

				if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG && function_exists( 'authority_mailer_smtp_debug_log' ) ) {
					authority_mailer_debug_log( '[Authority_Mailer_Sender] resolve_force_from debug: ' . wp_json_encode( $dbg ) );
				}

				if ( $feed_flag && $force_email ) {
					try {
						if ( method_exists( $phpmailer, 'setFrom' ) ) {
							$phpmailer->setFrom( $force_email, $force_name ? $force_name : '' );
						}
					} catch ( Exception $e ) {
						if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG && function_exists( 'authority_mailer_smtp_debug_log' ) ) {
							authority_mailer_debug_log( '[Authority_Mailer_Sender] phpmailer setFrom failed: ' . $e->getMessage() );
						}
					}
				}
		}

		/**
		 * Apply Email Defaults to PHPMailer before sending.
		 * This adds Reply-To, Return-Path, CC, BCC, and Priority headers for SMTP providers.
		 *
		 * @since 1.0.0
		 * @param PHPMailer $phpmailer The PHPMailer instance.
		 */
		public static function apply_email_defaults_phpmailer( $phpmailer ) {
			if ( ! $phpmailer || ! class_exists( 'Authority_Mailer_Email_Defaults' ) ) {
				return;
			}

			$defaults = Authority_Mailer_Email_Defaults::get_defaults();

			// If no defaults are set, return early.
			if ( empty( array_filter( $defaults ) ) ) {
				return;
			}

			// Apply Reply-To if set and not already present.
			if ( ! empty( $defaults['reply_to'] ) && is_email( $defaults['reply_to'] ) ) {
				// Check if Reply-To is already set.
				$existing_reply_to = $phpmailer->getReplyToAddresses();
				if ( empty( $existing_reply_to ) ) {
					try {
						$phpmailer->addReplyTo( $defaults['reply_to'] );
						if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
							// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
							error_log( '[Authority Mailer Sender] Applied Reply-To default to SMTP email: ' . $defaults['reply_to'] );
						}
					} catch ( Exception $e ) {
						if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
							// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
							error_log( '[Authority Mailer Sender] Failed to add Reply-To: ' . $e->getMessage() );
						}
					}
				}
			}

			// Apply Return-Path if set (use Sender header as PHPMailer doesn't have Return-Path property).
			if ( ! empty( $defaults['return_path'] ) && is_email( $defaults['return_path'] ) ) {
				try {
					// PHPMailer doesn't have a setSender() method, so we set the property directly.
					// The Sender property is used for the Return-Path header.
					$phpmailer->Sender = $defaults['return_path'];
					if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
						// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
						error_log( '[Authority Mailer Sender] Applied Return-Path default to SMTP email: ' . $defaults['return_path'] );
					}
				} catch ( Exception $e ) {
					if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
						// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
						error_log( '[Authority Mailer Sender] Failed to set Return-Path: ' . $e->getMessage() );
					}
				}
			}

			// Apply CC - merge with existing CC if present.
			if ( ! empty( $defaults['cc'] ) ) {
				$cc_emails = Authority_Mailer_Email_Defaults::parse_email_list( $defaults['cc'] );
				if ( ! empty( $cc_emails ) ) {
					$existing_cc = $phpmailer->getCcAddresses();
					$existing_cc_emails = array();
					if ( ! empty( $existing_cc ) ) {
						foreach ( $existing_cc as $addr ) {
							$existing_cc_emails[] = isset( $addr[0] ) ? $addr[0] : '';
						}
					}

					// Add only CC addresses that aren't already present.
					foreach ( $cc_emails as $cc_email ) {
						if ( ! in_array( $cc_email, $existing_cc_emails, true ) ) {
							try {
								$phpmailer->addCC( $cc_email );
								if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
									// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
									error_log( '[Authority Mailer Sender] Applied CC default to SMTP email: ' . $cc_email );
								}
							} catch ( Exception $e ) {
								if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
									// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
									error_log( '[Authority Mailer Sender] Failed to add CC: ' . $e->getMessage() );
								}
							}
						}
					}
				}
			}

			// Apply BCC - merge with existing BCC if present.
			if ( ! empty( $defaults['bcc'] ) ) {
				$bcc_emails = Authority_Mailer_Email_Defaults::parse_email_list( $defaults['bcc'] );
				if ( ! empty( $bcc_emails ) ) {
					$existing_bcc = $phpmailer->getBccAddresses();
					$existing_bcc_emails = array();
					if ( ! empty( $existing_bcc ) ) {
						foreach ( $existing_bcc as $addr ) {
							$existing_bcc_emails[] = isset( $addr[0] ) ? $addr[0] : '';
						}
					}

					// Add only BCC addresses that aren't already present.
					foreach ( $bcc_emails as $bcc_email ) {
						if ( ! in_array( $bcc_email, $existing_bcc_emails, true ) ) {
							try {
								$phpmailer->addBCC( $bcc_email );
								if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
									// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
									error_log( '[Authority Mailer Sender] Applied BCC default to SMTP email: ' . $bcc_email );
								}
							} catch ( Exception $e ) {
								if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
									// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
									error_log( '[Authority Mailer Sender] Failed to add BCC: ' . $e->getMessage() );
								}
							}
						}
					}
				}
			}

			// Apply Priority headers if set and not already present.
			if ( ! empty( $defaults['priority'] ) && 'normal' !== $defaults['priority'] ) {
				// Check if priority headers already exist.
				$has_priority = false;
				$custom_headers = $phpmailer->getCustomHeaders();
				foreach ( $custom_headers as $header ) {
					if ( is_array( $header ) && isset( $header[0] ) ) {
						$header_name = strtolower( $header[0] );
						if ( in_array( $header_name, array( 'x-priority', 'x-msmail-priority', 'importance' ), true ) ) {
							$has_priority = true;
							break;
						}
					}
				}

				if ( ! $has_priority ) {
					$priority_headers = Authority_Mailer_Email_Defaults::get_priority_headers( $defaults['priority'] );
					foreach ( $priority_headers as $header ) {
						if ( strpos( $header, ':' ) !== false ) {
							$parts = explode( ':', $header, 2 );
							if ( isset( $parts[0], $parts[1] ) ) {
								$phpmailer->addCustomHeader( trim( $parts[0] ), trim( $parts[1] ) );
							}
						}
					}
					if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
						// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
						error_log( '[Authority Mailer Sender] Applied Priority default to SMTP email: ' . $defaults['priority'] );
					}
				}
			}

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
				error_log( '[Authority Mailer Sender] Email defaults applied to SMTP email via PHPMailer' );
			}
		}

		/**
		 * Filter wp_mail_from: optionally force from email per provider settings.
		 */
		public static function wp_mail_from( $original ) {
			$res      = self::resolve_provider_and_settings();
			$provider = $res['provider'];
			$options  = $res['settings'];

			if ( '' === $provider ) {
				return $original;
			}

			$force_email_flag = false;
			if ( isset( $options[ $provider . '_force_from_email' ] ) ) {
				if ( function_exists( 'authority_mailer_smtp_to_bool' ) ) {
					$force_email_flag = authority_mailer_smtp_to_bool( $options[ $provider . '_force_from_email' ] );
				} else {
					$force_email_flag = (bool) $options[ $provider . '_force_from_email' ];
				}
			} elseif ( isset( $options['force_from_email'] ) ) {
				if ( function_exists( 'authority_mailer_smtp_to_bool' ) ) {
					$force_email_flag = authority_mailer_smtp_to_bool( $options['force_from_email'] );
				} else {
					$force_email_flag = (bool) $options['force_from_email'];
				}
			}

			$force_from_email = isset( $options[ $provider . '_from_email' ] ) ? sanitize_email( $options[ $provider . '_from_email' ] ) : ( isset( $options['from_email'] ) ? sanitize_email( $options['from_email'] ) : '' );

			if ( $force_email_flag && $force_from_email ) {
				return $force_from_email;
			}

			return $original;
		}

		/**
		 * Filter wp_mail_from_name: optionally force from name per provider settings.
		 */
		public static function wp_mail_from_name( $original ) {
			$res      = self::resolve_provider_and_settings();
			$provider = $res['provider'];
			$options  = $res['settings'];

			if ( '' === $provider ) {
				return $original;
			}

			$force_name_flag = false;
			if ( isset( $options[ $provider . '_force_from_name' ] ) ) {
				if ( function_exists( 'authority_mailer_smtp_to_bool' ) ) {
					$force_name_flag = authority_mailer_smtp_to_bool( $options[ $provider . '_force_from_name' ] );
				} else {
					$force_name_flag = (bool) $options[ $provider . '_force_from_name' ];
				}
			} elseif ( isset( $options['force_from_name'] ) ) {
				if ( function_exists( 'authority_mailer_smtp_to_bool' ) ) {
					$force_name_flag = authority_mailer_smtp_to_bool( $options['force_from_name'] );
				} else {
					$force_name_flag = (bool) $options['force_from_name'];
				}
			}

			$force_from_name = isset( $options[ $provider . '_from_name' ] ) ? sanitize_text_field( $options[ $provider . '_from_name' ] ) : ( isset( $options['from_name'] ) ? sanitize_text_field( $options['from_name'] ) : '' );

			if ( $force_name_flag && $force_from_name ) {
				return $force_from_name;
			}

			return $original;
		}

		/**
		 * Apply GDPR compliance filters to PHPMailer before sending.
		 * This adds unsubscribe links and List-Unsubscribe headers for SMTP providers.
		 *
		 * @since 1.0.0
		 * @param PHPMailer $phpmailer The PHPMailer instance.
		 */
		public static function apply_gdpr_filters_phpmailer( $phpmailer ) {
			if ( ! $phpmailer ) {
				return;
			}

			// Build email context for GDPR filters.
			$to_addresses = array();
			if ( ! empty( $phpmailer->getToAddresses() ) ) {
				foreach ( $phpmailer->getToAddresses() as $addr ) {
					$to_addresses[] = isset( $addr[0] ) ? $addr[0] : '';
				}
			}

			$email_context = array(
				'to'           => implode( ', ', $to_addresses ),
				'subject'      => $phpmailer->Subject,
				'message'      => $phpmailer->Body,
				'content_type' => $phpmailer->ContentType,
			);

			// Add tracking ID if present (captured from pre_wp_mail).
			if ( null !== self::$current_tracking_id ) {
				$email_context['tracking_id'] = self::$current_tracking_id;
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
					error_log( '[Authority Mailer Sender] Adding tracking ID to email context for GDPR filters: ' . self::$current_tracking_id );
				}
			}

			// Apply GDPR content filter (adds unsubscribe link to body).
			$filtered_body = apply_filters( 'authority_mailer_email_content', $phpmailer->Body, $email_context );
			if ( $filtered_body !== $phpmailer->Body ) {
				$phpmailer->Body = $filtered_body;
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
					error_log( '[Authority Mailer Sender] GDPR content filter applied to SMTP email body' );
				}
			}

			// Apply GDPR headers filter (adds List-Unsubscribe header).
			$current_headers = array();
			if ( ! empty( $phpmailer->getCustomHeaders() ) ) {
				foreach ( $phpmailer->getCustomHeaders() as $header ) {
					if ( is_array( $header ) && count( $header ) >= 2 ) {
						$current_headers[] = $header[0] . ': ' . $header[1];
					}
				}
			}

			$filtered_headers = apply_filters( 'authority_mailer_email_headers', $current_headers, $email_context );

			// Parse filtered headers and add new ones to PHPMailer.
			if ( is_array( $filtered_headers ) ) {
				foreach ( $filtered_headers as $header ) {
					if ( is_string( $header ) && strpos( $header, ':' ) !== false ) {
						$parts = explode( ':', $header, 2 );
						// Ensure both parts exist before accessing.
						if ( ! isset( $parts[0], $parts[1] ) ) {
							continue;
						}
						$name  = trim( $parts[0] );
						$value = trim( $parts[1] );

						// Check if header already exists to avoid duplicates.
						$header_exists = false;
						foreach ( $phpmailer->getCustomHeaders() as $existing ) {
							if ( is_array( $existing ) && isset( $existing[0] ) && strtolower( $existing[0] ) === strtolower( $name ) ) {
								$header_exists = true;
								break;
							}
						}

						if ( ! $header_exists ) {
							$phpmailer->addCustomHeader( $name, $value );
							if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
								// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
								error_log( '[Authority Mailer Sender] Added GDPR header to SMTP email: ' . $name );
							}
						}
					}
				}
			}

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
				error_log( '[Authority Mailer Sender] GDPR filters applied to SMTP email via PHPMailer' );
			}
		}

		/**
		 * Log SMTP email when PHPMailer is initialized (right before send).
		 * This hook fires reliably for all SMTP emails.
		 *
		 * @param PHPMailer $phpmailer The PHPMailer instance.
		 */
		public static function log_smtp_email_on_phpmailer_init( $phpmailer ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
				error_log( '[Authority Mailer Sender] log_smtp_email_on_phpmailer_init called' );
			}

			// Only log for SMTP providers
			$res      = self::resolve_provider_and_settings();
			$provider = $res['provider'];
			$is_smtp  = $res['is_smtp'];

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
				error_log( '[Authority Mailer Sender] Provider: ' . $provider . ' | is_smtp: ' . ( $is_smtp ? 'yes' : 'no' ) );
			}

			if ( '' === $provider || ! $is_smtp ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
					error_log( '[Authority Mailer Sender] Not logging - no provider or not SMTP' );
				}
				return;
			}

			// Check if logger function exists
			if ( ! function_exists( 'authority_mailer_smtp_email_logger_insert' ) ) {
				// Try to load the logger
				$logger_paths = array(
					AUTHORITY_MAILER_PLUGIN_DIR . 'includes/email-logger.php',
					__DIR__ . '/email-logger.php',
				);
				foreach ( $logger_paths as $path ) {
					if ( file_exists( $path ) ) {
						require_once $path;
						break;
					}
				}
			}

			if ( ! function_exists( 'authority_mailer_smtp_email_logger_insert' ) ) {
				return;
			}

			// Extract email details from PHPMailer
			$to_addresses = array();
			if ( ! empty( $phpmailer->getToAddresses() ) ) {
				foreach ( $phpmailer->getToAddresses() as $addr ) {
					$to_addresses[] = isset( $addr[0] ) ? $addr[0] : '';
				}
			}
			$to = implode( ', ', $to_addresses );

			$from_email = $phpmailer->From;
			$from_name  = $phpmailer->FromName;
			$subject    = $phpmailer->Subject;
			$body       = $phpmailer->Body;

			// Build headers string from PHPMailer
			$headers_arr = array();
			if ( ! empty( $phpmailer->getCustomHeaders() ) ) {
				foreach ( $phpmailer->getCustomHeaders() as $header ) {
					if ( is_array( $header ) && count( $header ) >= 2 ) {
						$headers_arr[] = $header[0] . ': ' . $header[1];
					}
				}
			}

			// Create log entry - we'll mark it as 'success' directly since if phpmailer_init
			// runs without exception, the email is about to be sent
			// We use a late priority (999) so this runs after configure_phpmailer
			$log_data = array(
				'provider'      => $provider,
				'to_email'      => sanitize_text_field( $to ),
				'from_email'    => sanitize_email( $from_email ),
				'from_name'     => sanitize_text_field( $from_name ),
				'subject'       => sanitize_text_field( $subject ),
				'headers'       => implode( "\n", $headers_arr ),
				'body'          => $body,
				'payload'       => wp_json_encode(
					array(
						'to'      => $to,
						'from'    => $from_email,
						'subject' => $subject,
						'host'    => $phpmailer->Host,
						'port'    => $phpmailer->Port,
						'secure'  => $phpmailer->SMTPSecure,
					)
				),
				'status'        => 'success', // Assume success if we get this far
				'response_code' => 250,
				'response_body' => 'Email queued for SMTP delivery',
			);

			// Add spam score if present.
			if ( null !== self::$current_spam_score ) {
				$log_data['spam_score'] = self::$current_spam_score;
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
					error_log( '[Authority Mailer Sender] Adding spam score to SMTP log: ' . self::$current_spam_score );
				}
			} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
					error_log( '[Authority Mailer Sender] No spam score available for SMTP log' );
			}

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
				error_log( '[Authority Mailer Sender] Inserting SMTP email log with data: ' . wp_json_encode( array_keys( $log_data ) ) );
			}

			$log_id = authority_mailer_smtp_email_logger_insert( $log_data );

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
				error_log( '[Authority Mailer Sender] SMTP email logged with ID: ' . $log_id );
			}

			// Create tracking pixel record if tracking_id is present and email was logged successfully.
			if ( $log_id && null !== self::$current_tracking_id ) {
				// Get recipient email from log_data.
				$recipient_email = isset( $log_data['to_email'] ) ? $log_data['to_email'] : '';

				// Only create tracking pixel if Analytics DB class is available.
				if ( class_exists( 'Authority_Mailer_Analytics_DB' ) ) {
					$analytics_db = Authority_Mailer_Analytics_DB::get_instance();

					$pixel_data = array(
						'email_log_id'    => $log_id,
						'tracking_id'     => self::$current_tracking_id,
						'recipient_email' => $recipient_email,
					);

					$pixel_id = $analytics_db->insert_tracking_pixel( $pixel_data );

					if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
						if ( $pixel_id ) {
														// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
														error_log( '[Authority Mailer Sender] Tracking pixel record created with ID: ' . $pixel_id . ' for tracking_id: ' . self::$current_tracking_id );
						} else {
														// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
														error_log( '[Authority Mailer Sender] Failed to create tracking pixel record for tracking_id: ' . self::$current_tracking_id );
						}
					}
				} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
					error_log( '[Authority Mailer Sender] Analytics DB class not available, skipping tracking pixel creation' );
				}
			}

			// Store log ID in case we need to update it on failure
			self::$current_smtp_log_id = $log_id;

			// Reset spam score and tracking ID for next email
			self::$current_spam_score  = null;
			self::$current_tracking_id = null;
		}

		/**
		 * Update log entry on successful email send (WordPress 5.9+).
		 *
		 * @param array $mail_data Email data that was sent.
		 */
		public static function log_smtp_email_success( $mail_data ) {
			// Log already marked as success in phpmailer_init, but update if needed
			if ( empty( self::$current_smtp_log_id ) ) {
				return;
			}

			if ( function_exists( 'authority_mailer_smtp_email_logger_update' ) ) {
				authority_mailer_smtp_email_logger_update(
					self::$current_smtp_log_id,
					array(
						'status'        => 'success',
						'response_code' => 250,
						'response_body' => 'Email sent successfully via SMTP',
					)
				);
			}

			// Reset for next email
			self::$current_smtp_log_id = 0;
		}

		/**
		 * Update log entry on failed email send (WordPress 5.9+).
		 *
		 * @param WP_Error $error The error object.
		 */
		public static function log_smtp_email_failure( $error ) {
			if ( empty( self::$current_smtp_log_id ) ) {
				return;
			}

			if ( function_exists( 'authority_mailer_smtp_email_logger_update' ) ) {
				$error_message = is_wp_error( $error ) ? $error->get_error_message() : 'Unknown error';
				$error_data    = is_wp_error( $error ) ? $error->get_error_data() : '';

				authority_mailer_smtp_email_logger_update(
					self::$current_smtp_log_id,
					array(
						'status'        => 'error',
						'response_code' => 0,
						'response_body' => $error_message . ( $error_data ? ' | ' . wp_json_encode( $error_data ) : '' ),
					)
				);
			}

			// Reset for next email
			self::$current_smtp_log_id = 0;
		}

		/**
		 * Parse email headers into an associative array.
		 *
		 * Handles both string headers (newline-separated) and array headers.
		 *
		 * @param string|array $headers Email headers.
		 * @return array Associative array of header names (lowercase) to values.
		 */
		private static function parse_headers( $headers ) {
			$parsed = array();

			if ( empty( $headers ) ) {
				return $parsed;
			}

			// Convert string headers to array
			if ( is_string( $headers ) ) {
				$headers = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
			}

			if ( ! is_array( $headers ) ) {
				return $parsed;
			}

			foreach ( $headers as $header ) {
				if ( ! is_string( $header ) ) {
					continue;
				}
				$header = trim( $header );
				if ( empty( $header ) ) {
					continue;
				}

				// Split header into name and value
				$colon_pos = strpos( $header, ':' );
				if ( false === $colon_pos ) {
					continue;
				}

				$name  = strtolower( trim( substr( $header, 0, $colon_pos ) ) );
				$value = trim( substr( $header, $colon_pos + 1 ) );

				$parsed[ $name ] = $value;
			}

			return $parsed;
		}
	}

	// Auto-initialize when file is included by authority-mailer bootstrap.
	if ( defined( 'AUTHORITY_MAILER_PLUGIN_FILE' ) ) {
		add_action(
			'init',
			function () {
				if ( class_exists( 'Authority_Mailer_Sender' ) ) {
					Authority_Mailer_Sender::init();
				}
			}
		);
	}
}
