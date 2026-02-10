<?php
/**
 * other.php
 *
 * Generic "Other SMTP" provider tester for Authority Mailer onboarding.
 *
 * Uses localized strings via authority_mailer_smtp_s() from includes/strings.php (do not hard-code UI text).
 * Performs:
 *  - resolution of saved settings (provider nested group + top-level fallbacks)
 *  - host resolution (DoH if available)
 *  - optional STARTTLS / implicit SSL handling
 *  - AUTH (AUTH PLAIN, fallback AUTH LOGIN)
 *  - MAIL FROM / RCPT TO / DATA transmission (simple message)
 *  - logs into authority_mailer_email_log when available
 *
 * Location: wp-content/plugins/authority-mailer-smtp/includes/providers/other.php
 */

defined( 'ABSPATH' ) || exit;

require_once AUTHORITY_MAILER_PLUGIN_DIR . 'includes/providers/common.php';

global $AUTHORITY_MAILER_STRINGS;

/* safe string accessor (falls back to common implementation if not loaded) */
if ( ! function_exists( 'authority_mailer_smtp_s' ) ) {
	/**
	 * Get a localized string from the global AUTHORITY_MAILER_STRINGS array.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The string key to retrieve.
	 * @return string The localized string or empty string if not found.
	 */
	function authority_mailer_smtp_s( $key ) {
		global $AUTHORITY_MAILER_STRINGS;
		if ( ! isset( $AUTHORITY_MAILER_STRINGS ) || ! is_array( $AUTHORITY_MAILER_STRINGS ) ) {
			return '';
		}
		return isset( $AUTHORITY_MAILER_STRINGS[ $key ] ) ? $AUTHORITY_MAILER_STRINGS[ $key ] : '';
	}
}

if ( ! function_exists( 'authority_mailer_smtp_pick_first_key' ) ) {
	/**
	 * Return first key present in $arr (use array_key_exists so '0' is respected).
	 *
	 * @since 1.0.0
	 *
	 * @param array $arr        The array to search.
	 * @param array $candidates The candidate keys to check.
	 * @return string|null The first matching key or null if none found.
	 */
	function authority_mailer_smtp_pick_first_key( $arr, $candidates ) {
		foreach ( (array) $candidates as $k ) {
			if ( is_array( $arr ) && array_key_exists( $k, $arr ) && '' !== trim( (string) $arr[ $k ] ) ) {
				return $k;
			}
		}
		return null;
	}
}

if ( ! function_exists( 'authority_mailer_smtp_open_smtp_connection' ) ) {
	/**
	 * Open an SMTP connection with optional SNI/peer_name for SSL.
	 *
	 * @since 1.0.0
	 *
	 * @param string $host         The SMTP host.
	 * @param string $resolved_ip  The resolved IP address.
	 * @param int    $port         The SMTP port.
	 * @param string $encryption   The encryption type (ssl/tls).
	 * @param array  $ctx_options  Stream context options.
	 * @param int    $timeout      Connection timeout in seconds.
	 * @param array  $steps        Reference to steps array for logging.
	 * @param int    $log_id       Optional log entry ID.
	 * @return resource|false Socket resource on success, false on failure.
	 */
	function authority_mailer_smtp_open_smtp_connection( $host, $resolved_ip, $port, $encryption, $ctx_options, $timeout, &$steps, $log_id = 0 ) {
		$prefer_ssl = ( strtolower( (string) $encryption ) === 'ssl' || intval( $port ) === 465 );
		$attempts   = $prefer_ssl ? array( 'ssl', 'tcp' ) : array( 'tcp', 'ssl' );

		foreach ( $attempts as $transport ) {
			$ctx = is_array( $ctx_options ) ? $ctx_options : array();
			if ( ! isset( $ctx['ssl'] ) ) {
				$ctx['ssl'] = array();
			}
			$ctx['ssl']['peer_name'] = $host;

			$context       = stream_context_create( $ctx );
			$remote_socket = sprintf( '%s://%s:%d', $transport, $resolved_ip, intval( $port ) );
			$errno         = 0;
			$errstr        = '';
			$stream        = @stream_socket_client( $remote_socket, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context );

			$steps[] = array(
				'status'  => 'detail',
				'message' => sprintf( authority_mailer_smtp_s( 'other_connecting' ) ? authority_mailer_smtp_s( 'other_connecting' ) : 'Attempted connection using transport: %s to %s:%d', $transport, $resolved_ip, $port ),
				'details' => ( $errno ? "errno={$errno} errstr=" . trim( (string) $errstr ) : 'connected' ),
			);

			if ( $stream ) {
				return $stream;
			}

			if ( $log_id && function_exists( 'authority_mailer_smtp_email_logger_update' ) ) {
				authority_mailer_smtp_email_logger_update(
					$log_id,
					array(
						'status'        => 'error',
						'response_code' => 0,
						'response_body' => "connect {$transport} failed: errno={$errno} errstr=" . trim( (string) $errstr ),
					)
				);
			}
		}

		$steps[] = array(
			'status'  => 'error',
			'message' => ( authority_mailer_smtp_s( 'other_connect_failed' ) ? authority_mailer_smtp_s( 'other_connect_failed' ) : 'Failed to connect to SMTP host after trying transports' ),
		);

		return false;
	}
}

/**
 * Run generic SMTP diagnostics and test transmission via a raw SMTP dialog.
 *
 * @param array $settings Optional settings (overrides saved options)
 * @return array Steps array for diagnostic UI
 */
function authority_mailer_smtp_test_other_smtp( $settings = array() ) {
	$steps = array();

	$steps[] = array(
		'status'  => 'info',
		'message' => ( authority_mailer_smtp_s( 'other_diag_start' ) ? authority_mailer_smtp_s( 'other_diag_start' ) : 'Starting generic SMTP diagnostics' ),
	);

	$provided_settings = is_array( $settings ) ? $settings : array();

	$steps[] = array(
		'status'  => 'detail',
		'message' => ( authority_mailer_smtp_s( 'other_onboarding_keys' ) ? authority_mailer_smtp_s( 'other_onboarding_keys' ) : 'Onboarding-provided settings summary' ),
		'details' => function_exists( 'authority_mailer_smtp_settings_summary' ) ? authority_mailer_smtp_settings_summary( $provided_settings ) : array(),
	);

	// Load saved options and merge sensible shapes (nested provider group + top-level fallbacks).
	$mm_opts = get_option( 'authority_mailer_smtp_options', array() );
	$mm_opts = is_array( $mm_opts ) ? $mm_opts : array();

	$steps[] = array(
		'status'  => 'detail',
		'message' => ( authority_mailer_smtp_s( 'other_mm_opts_keys' ) ? authority_mailer_smtp_s( 'other_mm_opts_keys' ) : 'Stored authority_mailer_options inspected (summary)' ),
		'details' => function_exists( 'authority_mailer_smtp_settings_summary' ) ? authority_mailer_smtp_settings_summary( $mm_opts ) : array(),
	);

	// Provider groups to consider as nested configurations.
	$provider_groups = array( 'other', 'smtp', 'smtp_settings', 'other_smtp', 'other_options' );
	foreach ( $provider_groups as $grp ) {
		if ( isset( $mm_opts[ $grp ] ) && is_array( $mm_opts[ $grp ] ) ) {
			foreach ( $mm_opts[ $grp ] as $k => $v ) {
				// Do not overwrite explicitly provided overrides.
				if ( ! array_key_exists( $k, $provided_settings ) ) {
					$provided_settings[ $k ] = $v;
				}
			}
			break;
		}
	}

	// Accept common keys from top-level mm_opts into provided_settings (preserve explicit values).
	$accepted_keys = array(
		'other_smtp_host',
		'smtp_host',
		'other_smtp_port',
		'smtp_port',
		'other_smtp_username',
		'other_smtp_password',
		'smtp_username',
		'smtp_password',
		'other_smtp_encryption',
		'smtp_encryption',
		'encryption',
		'other_smtp_auth',
		'smtp_auth',
		'from_email',
		'from_name',
		'test_recipient',
		'allow_insecure',
		'endpoint_host',
		'endpoint_ip',
		// ensure force toggles are copied if saved at top-level.
		'other_force_from_email',
		'other_force_from_name',
		'force_from_email',
		'force_from_name',
		// some installs may use provider-prefixed variants.
		'other_from_email',
		'other_from_name',
		'other_from',
	);
	foreach ( $accepted_keys as $k ) {
		if ( ! array_key_exists( $k, $provided_settings ) && array_key_exists( $k, $mm_opts ) ) {
			$provided_settings[ $k ] = $mm_opts[ $k ];
		}
	}

	$steps[] = array(
		'status'  => 'detail',
		'message' => ( authority_mailer_smtp_s( 'other_final_settings' ) ? authority_mailer_smtp_s( 'other_final_settings' ) : 'Final settings used after merge (summary)' ),
		'details' => function_exists( 'authority_mailer_smtp_settings_summary' ) ? authority_mailer_smtp_settings_summary( $provided_settings ) : array(),
	);

	// Determine host and port.
	$host = '';
	if ( ! empty( $provided_settings['other_smtp_host'] ) ) {
		$host = sanitize_text_field( $provided_settings['other_smtp_host'] );
	} elseif ( ! empty( $provided_settings['smtp_host'] ) ) {
		$host = sanitize_text_field( $provided_settings['smtp_host'] );
	} elseif ( ! empty( $provided_settings['endpoint_host'] ) ) {
		$host = sanitize_text_field( $provided_settings['endpoint_host'] );
	}

	$port = 25;
	if ( ! empty( $provided_settings['other_smtp_port'] ) ) {
		$port = intval( $provided_settings['other_smtp_port'] );
	} elseif ( ! empty( $provided_settings['smtp_port'] ) ) {
		$port = intval( $provided_settings['smtp_port'] );
	}

	if ( '' === trim( (string) $host ) ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => ( authority_mailer_smtp_s( 'other_missing_host' ) ? authority_mailer_smtp_s( 'other_missing_host' ) : 'SMTP host is not configured.' ),
		);
		return $steps;
	}
	if ( empty( $port ) || $port < 1 || $port > 65535 ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => ( authority_mailer_smtp_s( 'other_missing_port' ) ? authority_mailer_smtp_s( 'other_missing_port' ) : 'SMTP port is not configured.' ),
		);
		return $steps;
	}

	$steps[] = array(
		'status'  => 'info',
		'message' => sprintf( authority_mailer_smtp_s( 'other_resolving' ) ? authority_mailer_smtp_s( 'other_resolving' ) : 'Resolving host: %s', $host ),
		'details' => '',
	);

	// Resolve host to IP (use authority_mailer_resolve_host_with_doh if available).
	$resolved_ip = '';
	if ( function_exists( 'authority_mailer_smtp_resolve_host_with_doh' ) ) {
		list( $ip, $debug ) = authority_mailer_smtp_resolve_host_with_doh( $host );
		foreach ( (array) $debug as $d ) {
			$d = trim( (string) $d );
			if ( '' === $d ) {
				continue;
			}
			$steps[] = array(
				'status'  => 'detail',
				'message' => $d,
			);
		}
		if ( ! empty( $ip ) ) {
			$resolved_ip = $ip;
			$steps[]     = array(
				'status'  => 'info',
				'message' => sprintf( authority_mailer_smtp_s( 'other_resolved' ) ? authority_mailer_smtp_s( 'other_resolved' ) : 'Resolved %1$s -> %2$s', $host, $ip ),
			);
		}
	}

	// Fallback to basic resolver.
	if ( empty( $resolved_ip ) ) {
		$ip = gethostbyname( $host );
		if ( $ip && $ip !== $host ) {
			$resolved_ip = $ip;
			$steps[]     = array(
				'status'  => 'info',
				'message' => sprintf( authority_mailer_smtp_s( 'other_resolved' ) ? authority_mailer_smtp_s( 'other_resolved' ) : 'Resolved %1$s -> %2$s', $host, $ip ),
			);
		} else {
			$steps[] = array(
				'status'  => 'detail',
				'message' => ( authority_mailer_smtp_s( 'other_resolve_failed' ) ? authority_mailer_smtp_s( 'other_resolve_failed' ) : 'Could not resolve SMTP host' ),
			);
		}
	}

	$endpoint_ip_override = ! empty( $provided_settings['endpoint_ip'] ) ? trim( (string) $provided_settings['endpoint_ip'] ) : '';
	if ( empty( $resolved_ip ) && ! empty( $endpoint_ip_override ) ) {
		$resolved_ip = $endpoint_ip_override;
		$steps[]     = array(
			'status'  => 'info',
			'message' => sprintf( authority_mailer_smtp_s( 'other_using_ip_override' ) ? authority_mailer_smtp_s( 'other_using_ip_override' ) : 'Using endpoint_ip override: %s', $resolved_ip ),
		);
	}

	if ( empty( $resolved_ip ) ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => ( authority_mailer_smtp_s( 'other_resolve_failed' ) ? authority_mailer_smtp_s( 'other_resolve_failed' ) : 'Could not resolve SMTP host' ),
		);
		return $steps;
	}

	// Determine encryption early.
	$encryption_setting = '';
	if ( ! empty( $provided_settings['other_smtp_encryption'] ) ) {
		$encryption_setting = strtolower( trim( $provided_settings['other_smtp_encryption'] ) );
	} elseif ( ! empty( $provided_settings['smtp_encryption'] ) ) {
		$encryption_setting = strtolower( trim( $provided_settings['smtp_encryption'] ) );
	} else {
		$encryption_setting = '';
	}
	$steps[] = array(
		'status'  => 'detail',
		'message' => ( authority_mailer_smtp_s( 'other_encryption_selected' ) ? authority_mailer_smtp_s( 'other_encryption_selected' ) : 'Encryption selected' ),
		'details' => $encryption_setting ? $encryption_setting : 'none',
	);

	// --- From selection & force toggles handling ---.
	// Determine force flags (check for several candidate keys).
	$force_email_flag = false;
	$force_name_flag  = false;

	// candidate toggle keys to check (top-level and common variants).
	$force_email_candidates = array( 'other_force_from_email', 'other_force_from', 'force_from_email', 'force_from', 'other_force_from_email' );
	$force_name_candidates  = array( 'other_force_from_name', 'force_from_name', 'other_force_from_name' );

	foreach ( $force_email_candidates as $k ) {
		if ( array_key_exists( $k, $provided_settings ) ) {
			$force_email_flag = authority_mailer_smtp_to_bool( $provided_settings[ $k ] );
			$steps[]          = array(
				'status'  => 'detail',
				'message' => sprintf( '%s => %s', $k, $force_email_flag ? 'true' : 'false' ),
			);
			break;
		}
	}

	foreach ( $force_name_candidates as $k ) {
		if ( array_key_exists( $k, $provided_settings ) ) {
			$force_name_flag = authority_mailer_smtp_to_bool( $provided_settings[ $k ] );
			$steps[]         = array(
				'status'  => 'detail',
				'message' => sprintf( '%s => %s', $k, $force_name_flag ? 'true' : 'false' ),
			);
			break;
		}
	}

	// Candidate keys for provider-provided from address/name.
	$provider_email_keys = array( 'other_from_email', 'other_from', 'from_email', 'from', 'email_from' );
	$provider_name_keys  = array( 'other_from_name', 'from_name', 'fromDisplayName', 'display_name' );

	$final_from_email = '';
	$final_from_name  = '';

	if ( $force_email_flag ) {
		$key = authority_mailer_smtp_pick_first_key( $provided_settings, $provider_email_keys );
		if ( $key ) {
			$final_from_email = sanitize_email( $provided_settings[ $key ] );
			$steps[]          = array(
				'status'  => 'detail',
				'message' => ( authority_mailer_smtp_s( 'other_force_from_email_forced' ) ? authority_mailer_smtp_s( 'other_force_from_email_forced' ) : 'Forcing from email using provider key' ),
				'details' => array(
					'key'   => $key,
					'value' => $final_from_email,
				),
			);
		} else {
			$final_from_email = get_option( 'admin_email', '' );
			$steps[]          = array(
				'status'  => 'detail',
				'message' => ( authority_mailer_smtp_s( 'other_force_from_email_missing_fallback' ) ? authority_mailer_smtp_s( 'other_force_from_email_missing_fallback' ) : 'Force from email enabled but no provider address found — falling back to admin_email' ),
				'details' => $final_from_email,
			);
		}
	} else {
		// not forcing: use admin_email but log provider presence if present.
		$key = authority_mailer_smtp_pick_first_key( $provided_settings, $provider_email_keys );
		if ( $key ) {
			$steps[] = array(
				'status'  => 'detail',
				'message' => ( authority_mailer_smtp_s( 'provider_ignored_from_email' ) ? authority_mailer_smtp_s( 'provider_ignored_from_email' ) : 'Provider defines a From Email but Force From Email is disabled — using site admin_email instead.' ),
				'details' => (string) $provided_settings[ $key ],
			);
		}
		$final_from_email = get_option( 'admin_email', '' );
	}

	if ( $force_name_flag ) {
		$keyn = authority_mailer_smtp_pick_first_key( $provided_settings, $provider_name_keys );
		if ( $keyn ) {
			$final_from_name = sanitize_text_field( $provided_settings[ $keyn ] );
			$steps[]         = array(
				'status'  => 'detail',
				'message' => ( authority_mailer_smtp_s( 'other_force_from_name_forced' ) ? authority_mailer_smtp_s( 'other_force_from_name_forced' ) : 'Forcing from name using provider key' ),
				'details' => array(
					'key'   => $keyn,
					'value' => $final_from_name,
				),
			);
		} else {
			$final_from_name = get_bloginfo( 'name' );
			$steps[]         = array(
				'status'  => 'detail',
				'message' => ( authority_mailer_smtp_s( 'other_force_from_name_missing_fallback' ) ? authority_mailer_smtp_s( 'other_force_from_name_missing_fallback' ) : 'Force from name enabled but no provider name found — falling back to site name' ),
				'details' => $final_from_name,
			);
		}
	} else {
		$keyn = authority_mailer_smtp_pick_first_key( $provided_settings, $provider_name_keys );
		if ( $keyn ) {
			$steps[] = array(
				'status'  => 'detail',
				'message' => ( authority_mailer_smtp_s( 'provider_ignored_from_name' ) ? authority_mailer_smtp_s( 'provider_ignored_from_name' ) : 'Provider defines a From Name but Force From Name is disabled — using site name instead.' ),
				'details' => (string) $provided_settings[ $keyn ],
			);
		}
		$final_from_name = get_bloginfo( 'name' );
	}

	// Recipient selection.
	if ( ! empty( $provided_settings['test_recipient'] ) ) {
		$test_to = sanitize_email( $provided_settings['test_recipient'] );
		$steps[] = array(
			'status'  => 'detail',
			'message' => ( authority_mailer_smtp_s( 'other_using_test_recipient' ) ? authority_mailer_smtp_s( 'other_using_test_recipient' ) : 'Using test_recipient from settings' ),
			'details' => (string) $test_to,
		);
	} else {
		$test_to = get_option( 'admin_email', '' );
		$steps[] = array(
			'status'  => 'detail',
			'message' => ( authority_mailer_smtp_s( 'other_using_admin_email' ) ? authority_mailer_smtp_s( 'other_using_admin_email' ) : 'Using admin_email as test recipient' ),
			'details' => (string) $test_to,
		);
	}

	if ( empty( $test_to ) || ! is_email( $test_to ) ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => ( authority_mailer_smtp_s( 'other_no_recipient' ) ? authority_mailer_smtp_s( 'other_no_recipient' ) : 'No recipient available for test email (admin email not set).' ),
		);
		return $steps;
	}

	// Prepare SMTP connect.
	$timeout        = 15;
	$ctx_options    = array();
	$allow_insecure = ! empty( $provided_settings['allow_insecure'] );
	if ( $allow_insecure ) {
		$steps[]            = array(
			'status'  => 'detail',
			'message' => ( authority_mailer_smtp_s( 'mailgun_allow_insecure' ) ? authority_mailer_smtp_s( 'mailgun_allow_insecure' ) : 'allow_insecure is enabled — SSL verification disabled (debug only)' ),
		);
		$ctx_options['ssl'] = array(
			'verify_peer'      => false,
			'verify_peer_name' => false,
		);
	} else {
		$ctx_options['ssl'] = array(
			'verify_peer'      => true,
			'verify_peer_name' => true,
		);
	}

	$steps[] = array(
		'status'  => 'info',
		'message' => sprintf( authority_mailer_smtp_s( 'other_connecting' ) ? authority_mailer_smtp_s( 'other_connecting' ) : 'Connecting to %1$s on port %2$s (%3$s', $host, $port, $resolved_ip ),
		'details' => '',
	);

	// Set log_id to 0 - the centralized wp_mail() handler will create the log entry.
	// This prevents duplicate logging (one 'attempt' here + one 'success' from wp_mail).
	$log_id = 0;

	// Open connection.
	$stream = authority_mailer_smtp_open_smtp_connection( $host, $resolved_ip, $port, $encryption_setting, $ctx_options, $timeout, $steps, $log_id );

	if ( ! $stream ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => ( authority_mailer_smtp_s( 'other_connect_failed' ) ? authority_mailer_smtp_s( 'other_connect_failed' ) : 'Failed to connect to SMTP host' ),
			'details' => '',
		);
		if ( $log_id && function_exists( 'authority_mailer_smtp_email_logger_update' ) ) {
			authority_mailer_smtp_email_logger_update(
				$log_id,
				array(
					'status'        => 'error',
					'response_code' => 0,
					'response_body' => 'connect failed',
				)
			);
		}
		return $steps;
	}

	stream_set_timeout( $stream, $timeout );
	stream_set_blocking( $stream, true );

	// Read server greeting (up to a short timeout).
	$greeting = '';
	$start    = time();
	while ( ( $line = @fgets( $stream, 1024 ) ) !== false ) {
		$greeting .= $line;
		if ( preg_match( '/^220\s/', ltrim( $line ) ) ) {
			break;
		}
		if ( time() - $start > 3 ) {
			break;
		}
	}
	$steps[] = array(
		'status'  => 'detail',
		'message' => ( authority_mailer_smtp_s( 'other_smtp_greeting' ) ? authority_mailer_smtp_s( 'other_smtp_greeting' ) : 'SMTP greeting received' ),
		'details' => array(
			'greeting' => trim( (string) $greeting ),
			'len'      => strlen( (string) $greeting ),
		),
	);

	// Helper to write command and read multi-line response.
	$send_smtp = function ( $s ) use ( $stream, &$steps ) {
		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- Socket write operation, not file system.
		$written = @fwrite( $stream, $s . "\r\n" );
		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- Socket flush operation.
		@fflush( $stream );
		$steps[] = array(
			'status'  => 'detail',
			'message' => 'Wrote to socket',
			'details' => array(
				'bytes' => $written,
				'line'  => $s,
			),
		);

		$resp    = '';
		$attempt = 0;
		$start   = time();
		while ( ( $line = @fgets( $stream, 1024 ) ) !== false ) {
			$resp .= $line;
			++$attempt;
			// break when a line begins with 3-digit space (end of multi-line response).
			if ( preg_match( '/^[0-9]{3}\s/', ltrim( $line ) ) ) {
				break;
			}
			if ( $attempt > 200 || ( time() - $start ) > 6 ) {
				break;
			}
		}
		$steps[] = array(
			'status'  => 'detail',
			'message' => 'Read from socket',
			'details' => array(
				'len'     => strlen( (string) $resp ),
				'excerpt' => substr( $resp, 0, 800 ),
			),
		);
		return $resp;
	};

	// Identify client name.
	$localhost = 'localhost';
	if ( function_exists( 'gethostname' ) ) {
		$hostname = @gethostname();
		if ( $hostname ) {
			$localhost = $hostname;
		}
	} elseif ( function_exists( 'php_uname' ) ) {
		$localhost = php_uname( 'n' );
	}

	// Send EHLO.
	$ehlo = 'EHLO ' . $localhost;
	$resp = $send_smtp( $ehlo );

	if ( stripos( $resp, '250' ) === false ) {
		$resp_helo = $send_smtp( 'HELO ' . $localhost );
		if ( stripos( $resp_helo, '250' ) === false ) {
			$details = 'EHLO response length: ' . strlen( (string) $resp ) . "\nHELO response length: " . strlen( (string) $resp_helo ) . "\nEHLO excerpt: " . substr( $resp, 0, 800 ) . "\nHELO excerpt: " . substr( $resp_helo, 0, 800 );
			$steps[] = array(
				'status'  => 'error',
				'message' => ( authority_mailer_smtp_s( 'other_smtp_ehlo_failed' ) ? authority_mailer_smtp_s( 'other_smtp_ehlo_failed' ) : 'EHLO/HELO failed' ),
				'details' => $details,
			);
			if ( $log_id && function_exists( 'authority_mailer_smtp_email_logger_update' ) ) {
				authority_mailer_smtp_email_logger_update(
					$log_id,
					array(
						'status'        => 'error',
						'response_code' => 0,
						'response_body' => $details,
					)
				);
			}
			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Closing socket stream, not file.
			@fclose( $stream );
			return $steps;
		}
		$steps[] = array(
			'status'  => 'detail',
			'message' => ( authority_mailer_smtp_s( 'other_smtp_ehlo' ) ? authority_mailer_smtp_s( 'other_smtp_ehlo' ) : 'Sent EHLO/HELO (HELO fallback used)' ),
			'details' => $resp_helo,
		);
		$resp    = $resp_helo;
	} else {
		$steps[] = array(
			'status'  => 'detail',
			'message' => ( authority_mailer_smtp_s( 'other_smtp_ehlo' ) ? authority_mailer_smtp_s( 'other_smtp_ehlo' ) : 'Sent EHLO/HELO' ),
			'details' => $resp,
		);
	}

	// STARTTLS if advertised and requested via 'tls' and not implicit ssl port.
	$resp_lc         = strtolower( $resp );
	$use_ssl_connect = ( strtolower( $encryption_setting ) === 'ssl' || intval( $port ) === 465 );
	if ( strpos( $resp_lc, 'starttls' ) !== false && strtolower( $encryption_setting ) === 'tls' && ! $use_ssl_connect ) {
		$steps[]    = array(
			'status'  => 'detail',
			'message' => ( authority_mailer_smtp_s( 'other_smtp_starttls_attempt' ) ? authority_mailer_smtp_s( 'other_smtp_starttls_attempt' ) : 'Attempting STARTTLS' ),
		);
		$start_resp = $send_smtp( 'STARTTLS' );
		if ( stripos( $start_resp, '220' ) !== false ) {
			$crypto_ok = @stream_socket_enable_crypto( $stream, true, STREAM_CRYPTO_METHOD_TLS_CLIENT );
			if ( ! $crypto_ok ) {
				$steps[] = array(
					'status'  => 'error',
					'message' => ( authority_mailer_smtp_s( 'other_smtp_starttls_failed' ) ? authority_mailer_smtp_s( 'other_smtp_starttls_failed' ) : 'STARTTLS negotiation failed' ),
					'details' => $start_resp,
				);
				if ( $log_id && function_exists( 'authority_mailer_smtp_email_logger_update' ) ) {
					authority_mailer_smtp_email_logger_update(
						$log_id,
						array(
							'status'        => 'error',
							'response_code' => 0,
							'response_body' => $start_resp,
						)
					);
				}
				// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Closing socket stream, not file.
				@fclose( $stream );
				return $steps;
			}
			$resp2   = $send_smtp( $ehlo );
			$steps[] = array(
				'status'  => 'detail',
				'message' => ( authority_mailer_smtp_s( 'other_smtp_ehlo_after_tls' ) ? authority_mailer_smtp_s( 'other_smtp_ehlo_after_tls' ) : 'EHLO after STARTTLS' ),
				'details' => $resp2,
			);
			$resp    = $resp2;
		} else {
			$steps[] = array(
				'status'  => 'error',
				'message' => ( authority_mailer_smtp_s( 'other_smtp_starttls_failed' ) ? authority_mailer_smtp_s( 'other_smtp_starttls_failed' ) : 'STARTTLS negotiation failed' ),
				'details' => $start_resp,
			);
			if ( $log_id && function_exists( 'authority_mailer_smtp_email_logger_update' ) ) {
				authority_mailer_smtp_email_logger_update(
					$log_id,
					array(
						'status'        => 'error',
						'response_code' => 0,
						'response_body' => $start_resp,
					)
				);
			}
			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Closing socket stream, not file.
			@fclose( $stream );
			return $steps;
		}
	}

	// --- AUTHENTICATION (if username/password provided) ---.
	$username = '';
	$password = '';
	if ( ! empty( $provided_settings['other_smtp_username'] ) ) {
		$username = (string) $provided_settings['other_smtp_username'];
	} elseif ( ! empty( $provided_settings['smtp_username'] ) ) {
		$username = (string) $provided_settings['smtp_username'];
	}
	if ( ! empty( $provided_settings['other_smtp_password'] ) ) {
		$password = (string) $provided_settings['other_smtp_password'];
	} elseif ( ! empty( $provided_settings['smtp_password'] ) ) {
		$password = (string) $provided_settings['smtp_password'];
	}

	$authed = false;
	if ( '' !== $username ) {
		$steps[] = array(
			'status'  => 'detail',
			'message' => ( authority_mailer_smtp_s( 'other_smtp_auth_start' ) ? authority_mailer_smtp_s( 'other_smtp_auth_start' ) : 'Starting SMTP AUTH' ),
		);
		// Try AUTH PLAIN.
		$auth_plain = base64_encode( "\0{$username}\0{$password}" );
		$auth_resp  = $send_smtp( "AUTH PLAIN {$auth_plain}" );
		if ( stripos( $auth_resp, '235' ) !== false ) {
			$steps[] = array(
				'status'  => 'detail',
				'message' => ( authority_mailer_smtp_s( 'other_smtp_auth_user' ) ? authority_mailer_smtp_s( 'other_smtp_auth_user' ) : 'Authenticated (AUTH PLAIN)' ),
				'details' => substr( $auth_resp, 0, 200 ),
			);
			$authed  = true;
		} else {
			// Fallback to AUTH LOGIN.
			$steps[]    = array(
				'status'  => 'detail',
				'message' => ( authority_mailer_smtp_s( 'other_smtp_auth_start' ) ? authority_mailer_smtp_s( 'other_smtp_auth_start' ) : 'AUTH PLAIN not accepted, attempting AUTH LOGIN' ),
				'details' => substr( $auth_resp, 0, 200 ),
			);
			$login_resp = $send_smtp( 'AUTH LOGIN' );
			if ( stripos( $login_resp, '334' ) !== false ) {
				$user_b = base64_encode( $username );
				$u_resp = $send_smtp( $user_b );
				if ( stripos( $u_resp, '334' ) !== false ) {
					$pass_b = base64_encode( $password );
					$p_resp = $send_smtp( $pass_b );
					if ( stripos( $p_resp, '235' ) !== false ) {
						$steps[] = array(
							'status'  => 'detail',
							'message' => ( authority_mailer_smtp_s( 'other_smtp_auth_pass' ) ? authority_mailer_smtp_s( 'other_smtp_auth_pass' ) : 'Authenticated using AUTH LOGIN' ),
							'details' => substr( $p_resp, 0, 200 ),
						);
						$authed  = true;
					} else {
						$steps[] = array(
							'status'  => 'error',
							'message' => ( authority_mailer_smtp_s( 'other_smtp_auth_failed' ) ? authority_mailer_smtp_s( 'other_smtp_auth_failed' ) : 'AUTH LOGIN failed' ),
							'details' => substr( $p_resp, 0, 400 ),
						);
					}
				} else {
					$steps[] = array(
						'status'  => 'error',
						'message' => ( authority_mailer_smtp_s( 'other_smtp_auth_failed' ) ? authority_mailer_smtp_s( 'other_smtp_auth_failed' ) : 'AUTH LOGIN/username exchange failed' ),
						'details' => substr( $u_resp, 0, 400 ),
					);
				}
			} else {
				$steps[] = array(
					'status'  => 'error',
					'message' => ( authority_mailer_smtp_s( 'other_smtp_auth_failed' ) ? authority_mailer_smtp_s( 'other_smtp_auth_failed' ) : 'AUTH LOGIN not supported or rejected' ),
					'details' => substr( $login_resp, 0, 400 ),
				);
			}
		}

		if ( ! $authed ) {
			if ( $log_id && function_exists( 'authority_mailer_smtp_email_logger_update' ) ) {
				authority_mailer_smtp_email_logger_update(
					$log_id,
					array(
						'status'        => 'error',
						'response_code' => 0,
						'response_body' => 'auth failed',
					)
				);
			}
			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Closing socket stream, not file.
			@fclose( $stream );
			return $steps;
		}
	} else {
		$steps[] = array(
			'status'  => 'detail',
			'message' => ( authority_mailer_smtp_s( 'other_no_smtp_username' ) ? authority_mailer_smtp_s( 'other_no_smtp_username' ) : 'No SMTP username provided — attempting unauthenticated send if server permits' ),
		);
	}

	// --- MAIL FROM / RCPT TO / DATA ---.
	$from_addr = $final_from_email ? $final_from_email : get_option( 'admin_email', '' );
	$from_addr = sanitize_email( $from_addr );

	// MAIL FROM.
	$mail_from_resp = $send_smtp( 'MAIL FROM:<' . $from_addr . '>' );
	$steps[]        = array(
		'status'  => 'detail',
		'message' => ( authority_mailer_smtp_s( 'other_smtp_mail_from' ) ? authority_mailer_smtp_s( 'other_smtp_mail_from' ) : 'MAIL FROM response' ),
		'details' => substr( $mail_from_resp, 0, 400 ),
	);
	if ( stripos( $mail_from_resp, '250' ) === false ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => ( authority_mailer_smtp_s( 'other_smtp_mail_from_failed' ) ? authority_mailer_smtp_s( 'other_smtp_mail_from_failed' ) : 'MAIL FROM rejected by server' ),
			'details' => substr( $mail_from_resp, 0, 400 ),
		);
		if ( $log_id && function_exists( 'authority_mailer_smtp_email_logger_update' ) ) {
			authority_mailer_smtp_email_logger_update(
				$log_id,
				array(
					'status'        => 'error',
					'response_code' => 0,
					'response_body' => 'mail from rejected',
				)
			);
		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Closing socket stream, not file.
		}
		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Closing socket stream, not file.
		@fclose( $stream );
		return $steps;
	}

	// RCPT TO.
	$rcpt_resp = $send_smtp( 'RCPT TO:<' . $test_to . '>' );
	$steps[]   = array(
		'status'  => 'detail',
		'message' => ( authority_mailer_smtp_s( 'other_smtp_rcpt_to' ) ? authority_mailer_smtp_s( 'other_smtp_rcpt_to' ) : 'RCPT TO response' ),
		'details' => substr( $rcpt_resp, 0, 400 ),
	);
	if ( stripos( $rcpt_resp, '250' ) === false && stripos( $rcpt_resp, '251' ) === false ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => ( authority_mailer_smtp_s( 'other_smtp_rcpt_failed' ) ? authority_mailer_smtp_s( 'other_smtp_rcpt_failed' ) : 'RCPT TO rejected by server' ),
			'details' => substr( $rcpt_resp, 0, 400 ),
		);
		if ( $log_id && function_exists( 'authority_mailer_smtp_email_logger_update' ) ) {
			authority_mailer_smtp_email_logger_update(
				$log_id,
				array(
					'status'        => 'error',
					'response_code' => 0,
					'response_body' => substr( $rcpt_resp, 0, 400 ),
				)
			);
		}
		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Closing socket stream, not file.
		@fclose( $stream );
		return $steps;
	}

	// SMTP validation successful - RCPT TO accepted.
	$steps[] = array(
		'status'  => 'success',
		'message' => ( authority_mailer_smtp_s( 'other_smtp_validation_success' ) ? authority_mailer_smtp_s( 'other_smtp_validation_success' ) : 'SMTP configuration validated successfully' ),
		'details' => 'MAIL FROM and RCPT TO accepted. Ready to send via wp_mail() pipeline.',
	);

	// Close connection politely.
	// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- Socket write operation, not file system.
	@fwrite( $stream, "QUIT\r\n" );
	// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- Socket flush operation.
	@fflush( $stream );
	// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Closing socket stream, not file.
	@fclose( $stream );

	// Send test email through wp_mail() pipeline to enable premium features (tracking, compliance, etc.).
	$steps[] = array(
		'status'  => 'info',
		'message' => ( authority_mailer_smtp_s( 'other_smtp_attempting_test' ) ? authority_mailer_smtp_s( 'other_smtp_attempting_test' ) : 'Sending test email through wp_mail() pipeline...' ),
	);

	// Use centralized test helper - sends through full wp_mail() pipeline with premium features.
	// Use the preserved provider name (zoho, office365, aws) instead of hardcoding 'other'
	$provider_name = ! empty( $provided_settings['provider'] ) ? $provided_settings['provider'] : 'other';
	if ( function_exists( 'authority_mailer_smtp_send_test_via_wpmail' ) ) {
		authority_mailer_smtp_send_test_via_wpmail( $provider_name, $test_to, $final_from_email, $final_from_name, $steps );
	} else {
		$steps[] = array(
			'status'  => 'error',
			'message' => 'Centralized test helper function not found. Please update common.php.',
		);
	}

	// Log final addresses for diagnostics.
	if ( function_exists( 'authority_mailer_smtp_log_final_addresses' ) ) {
		authority_mailer_smtp_log_final_addresses( $steps, $test_to, $final_from_email, $final_from_name );
	} else {
		$steps[] = array(
			'status'  => 'detail',
			'message' => ( authority_mailer_smtp_s( 'final_transmission_addresses' ) ? authority_mailer_smtp_s( 'final_transmission_addresses' ) : 'Final transmission addresses' ),
			'details' => array(
				'to'         => $test_to,
				'from_email' => $final_from_email,
				'from_name'  => $final_from_name,
			),
		);
	}

	return $steps;
}

// Uses PHPMailer directly.
/**
 * Send email via generic SMTP
 *
 * @param array $email Email data from wp_mail
 * @return true|WP_Error
 */
function authority_mailer_smtp_send_other_smtp( $email ) {
	// Use PHPMailer which WordPress includes.
	global $phpmailer;

	// Load PHPMailer if not already loaded.
	if ( ! is_object( $phpmailer ) || ! is_a( $phpmailer, 'PHPMailer' ) ) {
		require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
		require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
		require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
		$phpmailer = new PHPMailer\PHPMailer\PHPMailer( true );
	}

	$options = get_option( 'authority_mailer_smtp_options', array() );

	// Get SMTP settings (check for provider-specific settings first, then 'other' settings).
	$smtp_host       = ! empty( $email['smtp_host'] )
		? $email['smtp_host']
		: ( ! empty( $options['other_smtp_host'] ) ? $options['other_smtp_host'] : '' );
	$smtp_port       = ! empty( $email['smtp_port'] )
		? $email['smtp_port']
		: ( ! empty( $options['other_smtp_port'] ) ? $options['other_smtp_port'] : 587 );
	$smtp_username   = ! empty( $email['smtp_username'] )
		? $email['smtp_username']
		: ( ! empty( $options['other_smtp_username'] ) ? $options['other_smtp_username'] : '' );
	$smtp_password   = ! empty( $email['smtp_password'] )
		? $email['smtp_password']
		: ( ! empty( $options['other_smtp_password'] ) ? $options['other_smtp_password'] : '' );
	$smtp_encryption = ! empty( $email['smtp_encryption'] )
		? $email['smtp_encryption']
		: ( ! empty( $options['other_smtp_encryption'] ) ? $options['other_smtp_encryption'] : 'tls' );
	$smtp_auth       = isset( $email['smtp_auth'] )
		? (bool) $email['smtp_auth']
		: ! empty( $options['other_smtp_auth'] );

	if ( empty( $smtp_host ) ) {
		return new WP_Error( 'missing_host', 'SMTP host not configured' );
	}

	// Get from email/name (check email array first, then options).
	$from_email = ! empty( $email['from_email'] )
		? $email['from_email']
		: ( ! empty( $options['other_from_email'] ) ? $options['other_from_email'] : get_option( 'admin_email' ) );

	$from_name = ! empty( $email['from_name'] )
		? $email['from_name']
		: ( ! empty( $options['other_from_name'] ) ? $options['other_from_name'] : get_bloginfo( 'name' ) );

	// Determine provider name (use from email array if set, otherwise default to 'other').
	// Whitelist allowed provider names for security.
	$allowed_providers = array( 'other', 'aws', 'zoho', 'office365', 'sendgrid', 'mailgun', 'sendlayer', 'brevo', 'sparkpost', 'postmark', 'mailjet', 'smtpcom', 'smtp2go', 'mailersend', 'elasticmail', 'google', 'mandrill' );
	$raw_provider      = ! empty( $email['provider'] ) ? sanitize_text_field( $email['provider'] ) : 'other';
	$provider_name     = in_array( $raw_provider, $allowed_providers, true ) ? $raw_provider : 'other';

	// Create email log table if needed.

	// Initialize log entry.
	$log_id = 0;
	if ( function_exists( 'authority_mailer_smtp_email_logger_insert' ) ) {
		$to_email = '';
		$to_arr   = is_array( $email['to'] ) ? $email['to'] : array( $email['to'] );
		if ( ! empty( $to_arr ) ) {
			$first_to = $to_arr[0];
			$to_email = is_array( $first_to ) && isset( $first_to['email'] ) ? $first_to['email'] : (string) $first_to;
		}

		$initial_headers = array(
			'From' => $from_email,
			'To'   => $to_email,
		);

		$log_data = array(
			'provider'   => $provider_name,
			'to_email'   => $to_email,
			'from_email' => $from_email,
			'from_name'  => $from_name,
			'subject'    => isset( $email['subject'] ) ? sanitize_text_field( $email['subject'] ) : '',
			'headers'    => wp_json_encode( $initial_headers ),
			// Store raw body content for accurate email logging and debugging.
			// Security: Body is sanitized on display via wp_kses_post() in email-logger.php (line 564)
			// and admin/email-log.php (line 1043). Raw storage is required to preserve
			// original email content for debugging purposes.
			'body'       => isset( $email['message'] ) ? $email['message'] : '',
			'payload'    => wp_json_encode(
				array(
					'smtp_host' => $smtp_host,
					'smtp_port' => $smtp_port,
				)
			),
			'status'     => 'attempt',
		);

		// Use centralized helper that handles spam score extraction and debug logging automatically.
		$log_id = authority_mailer_smtp_log_email_with_spam_score( $email, $log_data );
	}

	try {
		// Configure PHPMailer.
		$phpmailer->isSMTP();
		$phpmailer->Host       = $smtp_host;
		$phpmailer->Port       = $smtp_port;
		$phpmailer->SMTPSecure = $smtp_encryption;
		$phpmailer->SMTPAuth   = $smtp_auth;

		if ( $smtp_auth ) {
			$phpmailer->Username = $smtp_username;
			$phpmailer->Password = $smtp_password;
		}

		// Set from.
		$phpmailer->setFrom( $from_email, $from_name );

		// Add recipients.
		$to = is_array( $email['to'] ) ? $email['to'] : array( $email['to'] );
		foreach ( $to as $recipient ) {
			if ( is_array( $recipient ) ) {
				$phpmailer->addAddress(
					$recipient['email'],
					isset( $recipient['name'] ) ? $recipient['name'] : ''
				);
			} else {
				$phpmailer->addAddress( $recipient );
			}
		}

		// Set subject and body.
		$phpmailer->Subject = $email['subject'];

		if ( 'text/html' === $email['content_type'] ) {
			$phpmailer->isHTML( true );
			$phpmailer->Body = $email['message'];
		} else {
			$phpmailer->isHTML( false );
			$phpmailer->Body = $email['message'];
		}

		// Send.
		$result = $phpmailer->send();

		if ( ! $result ) {
			$error_msg = $phpmailer->ErrorInfo;
			// Update log with error.
			if ( $log_id && function_exists( 'authority_mailer_smtp_email_logger_update' ) ) {
				authority_mailer_smtp_email_logger_update(
					$log_id,
					array(
						'status'        => 'error',
						'response_code' => 0,
						'response_body' => $error_msg,
						'body'          => isset( $email['message'] ) ? $email['message'] : '',
					)
				);
			}

			// Trigger failover notification.
			do_action(
				'authority_mailer_provider_failed',
				array(
					'provider'      => $provider_name,
					'error_code'    => 'smtp_send_failed',
					'error_message' => $error_msg,
					'email_data'    => $email,
				)
			);

			return new WP_Error( 'smtp_error', $error_msg );
		}

		// Update log with success.
		if ( $log_id && function_exists( 'authority_mailer_smtp_email_logger_update' ) ) {
			authority_mailer_smtp_email_logger_update(
				$log_id,
				array(
					'status'        => 'accepted',
					'response_code' => 250,
					'response_body' => 'Email accepted by SMTP server',
				)
			);
		}

		return true;

	} catch ( Exception $e ) {
		$error_msg = $e->getMessage();
		// Update log with exception.
		if ( $log_id && function_exists( 'authority_mailer_smtp_email_logger_update' ) ) {
			authority_mailer_smtp_email_logger_update(
				$log_id,
				array(
					'status'        => 'error',
					'response_code' => 0,
					'response_body' => $error_msg,
					'body'          => isset( $email['message'] ) ? $email['message'] : '',
				)
			);
		}

		// Trigger failover notification.
		do_action(
			'authority_mailer_provider_failed',
			array(
				'provider'      => $provider_name,
				'error_code'    => 'smtp_exception',
				'error_message' => $error_msg,
				'email_data'    => $email,
			)
		);

		return new WP_Error( 'smtp_exception', $error_msg );
	}
}
