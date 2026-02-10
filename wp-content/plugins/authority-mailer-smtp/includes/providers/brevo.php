<?php
/**
 * brevo.php
 *
 * Brevo (Sendinblue) provider tester for Authority Mailer onboarding.
 *
 * - Robust settings merge, API key detection, DNS resolution (with endpoint_ip override),
 *   force-from handling, payload builder, HTTP POST with retries for alternate header names
 *   (api-key, X-Mailin-API-Key, Authorization: Bearer) to handle account/key shape differences.
 * - Uses authority_mailer_smtp_http_post_and_log() when available, otherwise wp_remote_post() fallback
 *   and authority_mailer_email_logger_insert/update instrumentation.
 *
 * Endpoint used: https://api.brevo.com/v3/smtp/email
 *
 * Location: wp-content/plugins/authority-mailer-smtp/includes/providers/brevo.php
 *
 * @package Authority_Mailer
 */

defined( 'ABSPATH' ) || exit;

require_once AUTHORITY_MAILER_PLUGIN_DIR . 'includes/providers/common.php';

global $AUTHORITY_MAILER_STRINGS;

/* safe localized accessor */
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

/**
 * Build Brevo payload for /v3/smtp/email
 */
function authority_mailer_smtp_build_brevo_payload( $from_email, $from_name, $to_email, $subject = '', $html = '', $text = '', $use_defaults = true ) {
	$subject = (string) $subject;
	$html    = (string) $html;
	$text    = (string) $text;

	// Only use defaults when explicitly requested (for test emails).
	if ( $use_defaults ) {
		if ( '' === trim( $subject ) ) {
			$default_subject = authority_mailer_smtp_s( 'brevo_default_subject' );
			$subject         = ! empty( $default_subject ) ? $default_subject : 'Authority Mailer Brevo test';
		}
		if ( '' === trim( $html ) && '' === trim( $text ) ) {
			$default_body = authority_mailer_smtp_s( 'brevo_default_body' );
			$html         = ! empty( $default_body ) ? $default_body : '<p>Authority Mailer Brevo test</p>';
		}
	}

	if ( '' === trim( $text ) && '' !== trim( $html ) ) {
		$text = trim( wp_strip_all_tags( $html ) );
	}

	$sender = array( 'email' => (string) $from_email );
	if ( '' !== trim( $from_name ) ) {
		$sender['name'] = (string) $from_name;
	}

	$to = array( array( 'email' => (string) $to_email ) );

	$payload = array(
		'sender'  => $sender,
		'to'      => $to,
		'subject' => $subject,
	);

	if ( '' !== trim( $html ) ) {
		$payload['htmlContent'] = $html;
	}
	if ( '' !== trim( $text ) ) {
		$payload['textContent'] = $text;
	}

	return $payload;
}

/**
 * Helper: locate provider values in provided_settings, mm_opts top-level, or nested groups.
 */
function authority_mailer_smtp_find_provider_value_generic( $keys, $provided_settings, $mm_opts, $provider_groups = array( 'brevo', 'other', 'smtp', 'smtp_settings' ) ) {
	foreach ( $keys as $k ) {
		if ( isset( $provided_settings[ $k ] ) && '' !== trim( (string) $provided_settings[ $k ] ) ) {
			return array( (string) $provided_settings[ $k ], 'provided_settings.' . $k );
		}
	}
	foreach ( $keys as $k ) {
		if ( isset( $mm_opts[ $k ] ) && '' !== trim( (string) $mm_opts[ $k ] ) ) {
			return array( (string) $mm_opts[ $k ], $k );
		}
	}
	foreach ( $provider_groups as $grp ) {
		if ( isset( $mm_opts[ $grp ] ) && is_array( $mm_opts[ $grp ] ) ) {
			foreach ( $keys as $k ) {
				if ( isset( $mm_opts[ $grp ][ $k ] ) && '' !== trim( (string) $mm_opts[ $grp ][ $k ] ) ) {
					return array( (string) $mm_opts[ $grp ][ $k ], $grp . '.' . $k );
				}
			}
		}
	}
	return array( '', '' );
}

/**
 * Try HTTP POST for Brevo with a list of header shapes until success or exhaustion.
 * Logs each attempt into $steps. Returns array( 'response' => $post_resp, 'attempts' => array(...) )
 */
function authority_mailer_smtp_brevo_post_with_retries( $endpoint_url, $payload, $api_key, $resolve_entry, $provided_settings, &$steps ) {
	$attempts = array();

	$header_variants = array(
		array( 'api-key' => $api_key ),
		array( 'X-Mailin-API-Key' => $api_key ),
		array( 'Authorization' => 'Bearer ' . $api_key ),
	);

	foreach ( $header_variants as $idx => $hdrs ) {
		$args_post = array(
			'timeout' => 30,
			'headers' => array_merge(
				array(
					'Accept'       => 'application/json',
					'Content-Type' => 'application/json',
				),
				$hdrs
			),
			'body'    => wp_json_encode( $payload ),
			'curl'    => array( CURLOPT_RESOLVE => array( $resolve_entry ) ),
		);

		$steps[] = array(
			'status'  => 'detail',
			'message' => sprintf( 'Attempting POST with header variant %d', $idx + 1 ),
			'details' => array( 'header_names' => array_keys( $hdrs ) ),
		);

		// Prefer centralized wrapper if available (it should handle logging itself).
		if ( function_exists( 'authority_mailer_smtp_http_post_and_log' ) ) {
			$post_resp = authority_mailer_smtp_http_post_and_log( $endpoint_url, $args_post, 'brevo', $payload, isset( $provided_settings['test_recipient'] ) ? $provided_settings['test_recipient'] : '', isset( $provided_settings['brevo_from_email'] ) ? $provided_settings['brevo_from_email'] : '', isset( $provided_settings['brevo_from_name'] ) ? $provided_settings['brevo_from_name'] : '', $hdrs );
		} else {
			// Basic wp_remote_post.
			$post_resp = wp_remote_post( $endpoint_url, $args_post );
		}

		$attempt_meta = array(
			'variant'      => $idx + 1,
			'header_names' => array_keys( $hdrs ),
		);
		if ( is_wp_error( $post_resp ) ) {
			$attempt_meta['error'] = $post_resp->get_error_message();
			$attempts[]            = $attempt_meta;
			$steps[]               = array(
				'status'  => 'detail',
				'message' => 'HTTP request error',
				'details' => $post_resp->get_error_message(),
			);
			// continue to next header variant.
			continue;
		}

		$code                         = intval( wp_remote_retrieve_response_code( $post_resp ) );
		$body                         = wp_remote_retrieve_body( $post_resp );
		$attempt_meta['http_code']    = $code;
		$attempt_meta['body_preview'] = is_string( $body ) ? substr( $body, 0, 1000 ) : '';
		$attempts[]                   = $attempt_meta;

		$steps[] = array(
			'status'  => 'detail',
			'message' => sprintf( 'POST HTTP status: %s (variant %d)', $code, $idx + 1 ),
			'details' => $attempt_meta['body_preview'],
		);

		// success codes 2xx.
		if ( $code >= 200 && $code < 300 ) {
			return array(
				'response' => $post_resp,
				'attempts' => $attempts,
			);
		}

		// if 401 unauthorized, try next header variant.
		// otherwise for other 4xx/5xx we can stop early if desired; here we attempt all variants for robustness.
	}

	// exhausted variants.
	return array(
		'response' => isset( $post_resp ) ? $post_resp : new WP_Error( 'brevo_no_response', 'No HTTP response obtained' ),
		'attempts' => $attempts,
	);
}

/**
 * Run Brevo diagnostics and test transmission.
 */
function authority_mailer_smtp_test_brevo( $settings = array() ) {
	$steps          = array();
	$diag_start_msg = authority_mailer_smtp_s( 'brevo_diag_start' );
	$steps[]        = array(
		'status'  => 'info',
		'message' => ! empty( $diag_start_msg ) ? $diag_start_msg : 'Starting Brevo diagnostics',
	);

	$provided_settings = is_array( $settings ) ? $settings : array();

	$onboarding_keys_msg = authority_mailer_smtp_s( 'brevo_onboarding_keys' );
	$steps[]             = array(
		'status'  => 'detail',
		'message' => ! empty( $onboarding_keys_msg ) ? $onboarding_keys_msg : 'Onboarding-provided settings summary',
		'details' => function_exists( 'authority_mailer_smtp_settings_summary' ) ? authority_mailer_smtp_settings_summary( $provided_settings ) : array(),
	);

	$mm_opts = get_option( 'authority_mailer_smtp_options', array() );
	$mm_opts = is_array( $mm_opts ) ? $mm_opts : array();

	$mm_opts_keys_msg = authority_mailer_smtp_s( 'brevo_mm_opts_keys' );
	$steps[]          = array(
		'status'  => 'detail',
		'message' => ! empty( $mm_opts_keys_msg ) ? $mm_opts_keys_msg : 'Stored authority_mailer_options inspected (summary)',
		'details' => function_exists( 'authority_mailer_smtp_settings_summary' ) ? authority_mailer_smtp_settings_summary( $mm_opts ) : array(),
	);

	// Merge provider-scoped groups into provided_settings (backwards compat).
	$provider_groups = array( 'brevo', 'brevo_settings', 'brevo_options', 'smtp', 'other' );
	foreach ( $provider_groups as $grp ) {
		if ( isset( $mm_opts[ $grp ] ) && is_array( $mm_opts[ $grp ] ) ) {
			foreach ( $mm_opts[ $grp ] as $k => $v ) {
				if ( ! isset( $provided_settings[ $k ] ) ) {
					$provided_settings[ $k ] = $v;
				}
			}
			break;
		}
	}

	// Accept top-level keys from mm_opts into provided_settings.
	$allowed_keys = array( 'brevo_api_key', 'api_key', 'token', 'from_email', 'from_name', 'test_recipient', 'test_subject', 'html_content', 'plain_content', 'endpoint_host', 'endpoint_ip', 'allow_insecure', 'force_from_email', 'force_from_name' );
	foreach ( $mm_opts as $k => $v ) {
		if ( isset( $provided_settings[ $k ] ) ) {
			continue;
		}
		if ( in_array( $k, $allowed_keys, true ) || 0 === strpos( $k, 'brevo_' ) ) {
			$provided_settings[ $k ] = $v;
		}
	}

	$steps[] = array(
		'status'  => 'detail',
		'message' => ( authority_mailer_smtp_s( 'brevo_final_settings' ) ? authority_mailer_smtp_s( 'brevo_final_settings' ) : 'Final settings used after merge (summary)' ),
		'details' => function_exists( 'authority_mailer_smtp_settings_summary' ) ? authority_mailer_smtp_settings_summary( $provided_settings ) : array(),
	);

	// Check if SMTP mode is enabled
	$use_smtp = isset( $provided_settings['brevo_use_smtp'] ) && ( true === $provided_settings['brevo_use_smtp'] || '1' === $provided_settings['brevo_use_smtp'] || 1 === $provided_settings['brevo_use_smtp'] );

	if ( $use_smtp ) {
		$steps[] = array(
			'status'  => 'info',
			'message' => 'Brevo SMTP mode enabled - using SMTP authentication',
		);

		// Validate SMTP credentials
		$smtp_username = isset( $provided_settings['brevo_smtp_username'] ) ? trim( $provided_settings['brevo_smtp_username'] ) : '';
		$smtp_password = isset( $provided_settings['brevo_smtp_password'] ) ? trim( $provided_settings['brevo_smtp_password'] ) : '';

		if ( empty( $smtp_username ) || empty( $smtp_password ) ) {
			$steps[] = array(
				'status'  => 'error',
				'message' => 'SMTP mode enabled but SMTP username or password is missing',
				'details' => 'Please provide both SMTP username and password for Brevo SMTP authentication.',
			);
			return $steps;
		}

		$steps[] = array(
			'status'  => 'info',
			'message' => 'SMTP credentials detected',
			'details' => array(
				'username'        => function_exists( 'authority_mailer_smtp_mask_key' ) ? authority_mailer_smtp_mask_key( $smtp_username ) : $smtp_username,
				'credential_type' => 'smtp',
			),
		);

		// Get SMTP host and port
		$smtp_host       = isset( $provided_settings['brevo_smtp_host'] ) ? trim( $provided_settings['brevo_smtp_host'] ) : 'smtp-relay.brevo.com';
		$smtp_port       = isset( $provided_settings['brevo_smtp_port'] ) ? intval( $provided_settings['brevo_smtp_port'] ) : 587;
		$smtp_encryption = isset( $provided_settings['brevo_smtp_encryption'] ) ? trim( $provided_settings['brevo_smtp_encryption'] ) : 'tls';

		$steps[] = array(
			'status'  => 'info',
			'message' => sprintf( 'Testing SMTP connection to %s:%d', $smtp_host, $smtp_port ),
		);

		$steps[] = array(
			'status'  => 'detail',
			'message' => sprintf( 'Connection attempt with %s encryption', strtoupper( $smtp_encryption ) ),
		);

		// Determine from email and name using force-from logic
		$provider_email_keys                  = array( 'brevo_from_email', 'from_email' );
		$provider_name_keys                   = array( 'brevo_from_name', 'from_name' );
		list( $found_email, $found_email_in ) = authority_mailer_smtp_find_provider_value_generic( $provider_email_keys, $provided_settings, $mm_opts, array( 'brevo', 'other', 'smtp', 'smtp_settings' ) );
		list( $found_name, $found_name_in )   = authority_mailer_smtp_find_provider_value_generic( $provider_name_keys, $provided_settings, $mm_opts, array( 'brevo', 'other', 'smtp', 'smtp_settings' ) );

		$force_from_email = isset( $provided_settings['brevo_force_from_email'] ) ? authority_mailer_smtp_to_bool( $provided_settings['brevo_force_from_email'] ) : ( isset( $mm_opts['brevo_force_from_email'] ) ? authority_mailer_smtp_to_bool( $mm_opts['brevo_force_from_email'] ) : false );
		$force_from_name  = isset( $provided_settings['brevo_force_from_name'] ) ? authority_mailer_smtp_to_bool( $provided_settings['brevo_force_from_name'] ) : ( isset( $mm_opts['brevo_force_from_name'] ) ? authority_mailer_smtp_to_bool( $mm_opts['brevo_force_from_name'] ) : false );

		if ( $force_from_email && '' !== $found_email ) {
			$final_from_email = sanitize_email( $found_email );
		} else {
			$final_from_email = get_option( 'admin_email', '' );
		}

		if ( $force_from_name && '' !== $found_name ) {
			$final_from_name = sanitize_text_field( $found_name );
		} else {
			$final_from_name = get_bloginfo( 'name' );
		}

		// Recipient
		if ( ! empty( $provided_settings['test_recipient'] ) ) {
			$test_to = sanitize_email( $provided_settings['test_recipient'] );
		} else {
			$test_to = get_option( 'admin_email', '' );
		}

		if ( empty( $test_to ) || ! is_email( $test_to ) ) {
			$steps[] = array(
				'status'  => 'error',
				'message' => 'No recipient available for test email (admin email not set).',
			);
			return $steps;
		}

		$steps[] = array(
			'status'  => 'detail',
			'message' => sprintf( 'Test recipient: %s', $test_to ),
		);

		// Send test email through wp_mail() pipeline - this will use PHPMailer and actually test the SMTP connection
		$steps[] = array(
			'status'  => 'info',
			'message' => 'Sending test email through wp_mail() pipeline...',
		);

		if ( function_exists( 'authority_mailer_smtp_send_test_via_wpmail' ) ) {
			authority_mailer_smtp_send_test_via_wpmail( 'brevo', $test_to, $final_from_email, $final_from_name, $steps );
		} else {
			$steps[] = array(
				'status'  => 'error',
				'message' => 'Centralized test helper function not found. Please update common.php.',
			);
		}

		return $steps;
	}

	// API mode - proceed with existing API validation
	$steps[] = array(
		'status'  => 'info',
		'message' => 'Brevo API mode enabled (default) - using API key authentication',
	);

	// API key detection.
	$key_candidates = array( 'brevo_api_key', 'brevo_key', 'api_key', 'apikey', 'key', 'token' );
	$api_key        = '';
	$found_path     = '';
	if ( function_exists( 'authority_mailer_smtp_find_api_key_in_array' ) ) {
		list( $found, $val, $path ) = authority_mailer_smtp_find_api_key_in_array( $provided_settings, $key_candidates );
		if ( $found ) {
			$api_key    = trim( (string) $val );
			$found_path = $path; }
	}
	// fallback nested.
	if ( '' === $api_key && isset( $mm_opts['brevo'] ) && is_array( $mm_opts['brevo'] ) ) {
		foreach ( $key_candidates as $kn ) {
			if ( ! empty( $mm_opts['brevo'][ $kn ] ) ) {
				$api_key    = trim( (string) $mm_opts['brevo'][ $kn ] );
				$found_path = 'authority_mailer_smtp_options.brevo.' . $kn;
				break; }
		}
	}
	// fallback top-level.
	if ( '' === $api_key ) {
		foreach ( $key_candidates as $kn ) {
			if ( ! empty( $mm_opts[ $kn ] ) ) {
				$api_key    = trim( (string) $mm_opts[ $kn ] );
				$found_path = $kn;
				break; }
		}
	}

	if ( '' === $api_key ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => ( authority_mailer_smtp_s( 'brevo_api_key_missing' ) ? authority_mailer_smtp_s( 'brevo_api_key_missing' ) : 'Brevo API key not found' ),
			'details' => ( authority_mailer_smtp_s( 'brevo_api_key_missing_detail' ) ? authority_mailer_smtp_s( 'brevo_api_key_missing_detail' ) : '' ),
		);
		return $steps;
	}

	$steps[] = array(
		'status'  => 'info',
		'message' => ( authority_mailer_smtp_s( 'brevo_api_detected' ) ? authority_mailer_smtp_s( 'brevo_api_detected' ) : 'API key detected' ),
		'details' => array(
			'found_in'   => $found_path,
			'masked_key' => function_exists( 'authority_mailer_smtp_mask_key' ) ? authority_mailer_smtp_mask_key( $api_key ) : '',
		),
	);

	// Resolve endpoint.
	$endpoint_host        = ! empty( $provided_settings['endpoint_host'] ) ? sanitize_text_field( $provided_settings['endpoint_host'] ) : 'api.brevo.com';
	$probe_path           = '/v3/smtp/email';
	$hosts_to_try         = array( $endpoint_host );
	$endpoint_ip_override = ! empty( $provided_settings['endpoint_ip'] ) ? trim( (string) $provided_settings['endpoint_ip'] ) : '';
	$resolved_ip          = '';
	$used_host            = '';

	foreach ( $hosts_to_try as $host ) {
		list( $ip, $debug ) = function_exists( 'authority_mailer_smtp_resolve_host_with_doh' ) ? authority_mailer_smtp_resolve_host_with_doh( $host ) : array( '', array() );
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
			$used_host   = $host;
			$steps[]     = array(
				'status'  => 'info',
				'message' => sprintf( ( authority_mailer_smtp_s( 'brevo_resolved' ) ? authority_mailer_smtp_s( 'brevo_resolved' ) : 'Resolved %1$s -> %2$s' ), $host, $ip ),
			);
			break; }
		// fallback to system.
		$sys = gethostbyname( $host );
		if ( $sys && $sys !== $host ) {
			$resolved_ip = $sys;
			$used_host   = $host;
			$steps[]     = array(
				'status'  => 'info',
				'message' => sprintf( ( authority_mailer_smtp_s( 'brevo_resolved' ) ? authority_mailer_smtp_s( 'brevo_resolved' ) : 'Resolved %1$s -> %2$s' ), $host, $sys ),
			);
			break; }
		$steps[] = array(
			'status'  => 'detail',
			'message' => sprintf( ( authority_mailer_smtp_s( 'brevo_could_not_resolve' ) ? authority_mailer_smtp_s( 'brevo_could_not_resolve' ) : 'Could not resolve %s' ), $host ),
		);
	}

	if ( empty( $resolved_ip ) && ! empty( $endpoint_ip_override ) ) {
		$resolved_ip = $endpoint_ip_override;
		$used_host   = $endpoint_host;
		$steps[]     = array(
			'status'  => 'info',
			'message' => sprintf( ( authority_mailer_smtp_s( 'brevo_using_ip_override' ) ? authority_mailer_smtp_s( 'brevo_using_ip_override' ) : 'Using endpoint_ip override: %s' ), $resolved_ip ),
		);
	}

	if ( empty( $resolved_ip ) ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => ( authority_mailer_smtp_s( 'brevo_dns_failed' ) ? authority_mailer_smtp_s( 'brevo_dns_failed' ) : 'Could not resolve Brevo host' ),
			'details' => '',
		);
		return $steps;
	}

	// Force-from handling.
	$provider_email_keys                  = array( 'brevo_from_email', 'from_email' );
	$provider_name_keys                   = array( 'brevo_from_name', 'from_name' );
	list( $found_email, $found_email_in ) = authority_mailer_smtp_find_provider_value_generic( $provider_email_keys, $provided_settings, $mm_opts, $provider_groups );
	list( $found_name, $found_name_in  )  = authority_mailer_smtp_find_provider_value_generic( $provider_name_keys, $provided_settings, $mm_opts, $provider_groups );

	$force_from_email = isset( $provided_settings['brevo_force_from_email'] ) ? authority_mailer_smtp_to_bool( $provided_settings['brevo_force_from_email'] ) : ( isset( $mm_opts['brevo_force_from_email'] ) ? authority_mailer_smtp_to_bool( $mm_opts['brevo_force_from_email'] ) : false );
	$force_from_name  = isset( $provided_settings['brevo_force_from_name'] ) ? authority_mailer_smtp_to_bool( $provided_settings['brevo_force_from_name'] ) : ( isset( $mm_opts['brevo_force_from_name'] ) ? authority_mailer_smtp_to_bool( $mm_opts['brevo_force_from_name'] ) : false );

	if ( $force_from_email ) {
		if ( '' !== $found_email ) {
			$final_from_email = sanitize_email( $found_email );
			$steps[]          = array(
				'status'  => 'detail',
				'message' => 'Force-from-email enabled — using provider-provided from email',
				'details' => array(
					'value'    => $final_from_email,
					'found_in' => $found_email_in,
				),
			);
		} else {
			$final_from_email = get_option( 'admin_email', '' );
			$steps[]          = array(
				'status'  => 'detail',
				'message' => 'Force-from-email enabled but no provider from email found — falling back to admin_email',
				'details' => array( 'admin_email' => $final_from_email ),
			);
		}
	} else {
		$final_from_email = get_option( 'admin_email', '' );
		if ( '' !== $found_email ) {
			$steps[] = array(
				'status'  => 'detail',
				'message' => ( authority_mailer_smtp_s( 'provider_ignored_from_email' ) ? authority_mailer_smtp_s( 'provider_ignored_from_email' ) : 'Provider defines a From Email but Force From Email is disabled — using site admin_email instead.' ),
				'details' => array(
					'provider_value' => $found_email,
					'found_in'       => $found_email_in,
				),
			);
		}
	}

	if ( $force_from_name ) {
		if ( '' !== $found_name ) {
			$final_from_name = sanitize_text_field( $found_name );
			$steps[]         = array(
				'status'  => 'detail',
				'message' => 'Force-from-name enabled — using provider-provided from name',
				'details' => array(
					'value'    => $final_from_name,
					'found_in' => $found_name_in,
				),
			);
		} else {
			$final_from_name = get_bloginfo( 'name' );
			$steps[]         = array(
				'status'  => 'detail',
				'message' => 'Force-from-name enabled but no provider from name found — falling back to site name',
				'details' => array( 'site_name' => $final_from_name ),
			);
		}
	} else {
		$final_from_name = get_bloginfo( 'name' );
		if ( '' !== $found_name ) {
			$steps[] = array(
				'status'  => 'detail',
				'message' => ( authority_mailer_smtp_s( 'provider_ignored_from_name' ) ? authority_mailer_smtp_s( 'provider_ignored_from_name' ) : 'Provider defines a From Name but Force From Name is disabled — using site name instead.' ),
				'details' => array(
					'provider_value' => $found_name,
					'found_in'       => $found_name_in,
				),
			);
		}
	}

	// Recipient.
	if ( ! empty( $provided_settings['test_recipient'] ) ) {
		$test_to = sanitize_email( $provided_settings['test_recipient'] );
		$steps[] = array(
			'status'  => 'detail',
			'message' => ( authority_mailer_smtp_s( 'brevo_using_test_recipient' ) ? authority_mailer_smtp_s( 'brevo_using_test_recipient' ) : 'Using test_recipient from settings' ),
			'details' => (string) $test_to,
		);
	} else {
		$test_to = get_option( 'admin_email', '' );
		$steps[] = array(
			'status'  => 'detail',
			'message' => ( authority_mailer_smtp_s( 'brevo_using_admin_email' ) ? authority_mailer_smtp_s( 'brevo_using_admin_email' ) : 'Using admin_email as test recipient' ),
			'details' => (string) $test_to,
		);
	}
	if ( empty( $test_to ) || ! is_email( $test_to ) ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => ( authority_mailer_smtp_s( 'brevo_no_recipient' ) ? authority_mailer_smtp_s( 'brevo_no_recipient' ) : 'No recipient available for test email (admin email not set).' ),
		);
		return $steps;
	}

	// Build professional test email using centralized template.
	$test_email_args = array(
		'provider'   => 'brevo',
		'from_email' => $final_from_email,
		'from_name'  => $final_from_name,
		'to_email'   => $test_to,
	);

	// Use professional template if available, otherwise fallback.
	if ( function_exists( 'authority_mailer_smtp_get_test_email_subject' ) ) {
		$subject = authority_mailer_smtp_get_test_email_subject( 'Brevo' );
	} else {
		$subject = isset( $provided_settings['test_subject'] ) ? sanitize_text_field( $provided_settings['test_subject'] ) : ( ( authority_mailer_smtp_s( 'brevo_default_subject' ) ? authority_mailer_smtp_s( 'brevo_default_subject' ) : 'Authority Mailer Brevo Test' ) );
	}

	if ( function_exists( 'authority_mailer_smtp_get_test_email_html' ) ) {
		$body_html = authority_mailer_smtp_get_test_email_html( $test_email_args );
		$body_text = function_exists( 'authority_mailer_smtp_get_test_email_plain' ) ? authority_mailer_smtp_get_test_email_plain( $test_email_args ) : wp_strip_all_tags( $body_html );
	} else {
		$body_html = isset( $provided_settings['html_content'] ) ? (string) $provided_settings['html_content'] : ( ( authority_mailer_smtp_s( 'brevo_default_body' ) ? authority_mailer_smtp_s( 'brevo_default_body' ) : '<p>Authority Mailer Brevo test</p>' ) );
		$body_text = isset( $provided_settings['plain_content'] ) ? (string) $provided_settings['plain_content'] : wp_strip_all_tags( $body_html );
	}

	$payload = authority_mailer_smtp_build_brevo_payload( $final_from_email, $final_from_name, $test_to, $subject, $body_html, $body_text );

	// Prepare request details.
	$endpoint_host = ! empty( $used_host ) ? $used_host : $endpoint_host;
	$endpoint_url  = 'https://' . $endpoint_host . $probe_path;
	$resolve_entry = $endpoint_host . ':443:' . $resolved_ip;

	$steps[] = array(
		'status'  => 'info',
		'message' => ( authority_mailer_smtp_s( 'brevo_attempting_test' ) ? authority_mailer_smtp_s( 'brevo_attempting_test' ) : 'Sending test email through wp_mail() pipeline...' ),
	);

	// Use centralized test helper - sends through full wp_mail() pipeline with premium features.
	if ( function_exists( 'authority_mailer_smtp_send_test_via_wpmail' ) ) {
		authority_mailer_smtp_send_test_via_wpmail( 'brevo', $test_to, $final_from_email, $final_from_name, $steps );
	} else {
		$steps[] = array(
			'status'  => 'error',
			'message' => 'Centralized test helper function not found. Please update common.php.',
		);
	}

	return $steps;
}

/**
 * Send email via Brevo API
 *
 * @param array $email Email data from wp_mail
 * @return true|WP_Error|null Returns null for SMTP mode to let PHPMailer handle it
 */
function authority_mailer_smtp_send_brevo( $email ) {
	$options = get_option( 'authority_mailer_smtp_options', array() );

	// Check if SMTP mode is enabled
	$use_smtp = isset( $options['brevo_use_smtp'] ) && ( true === $options['brevo_use_smtp'] || '1' === $options['brevo_use_smtp'] || 1 === $options['brevo_use_smtp'] );

	if ( $use_smtp ) {
		// SMTP mode - return null to let PHPMailer handle the transmission
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
			error_log( '[Authority Mailer Brevo] Using SMTP mode - deferring to PHPMailer' );
		}
		return null;
	}

	// API mode - proceed with API transmission
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
		error_log( '[Authority Mailer Brevo] Using API mode' );
	}

	// Get API key.
	$api_key = ! empty( $options['brevo_api_key'] )
		? $options['brevo_api_key']
		: '';

	if ( empty( $api_key ) ) {
		return new WP_Error( 'missing_api_key', 'Brevo API key not configured' );
	}

	// Get from email/name.
	$from_email = ! empty( $options['brevo_from_email'] )
		? $options['brevo_from_email']
		: get_option( 'admin_email' );

	$from_name = ! empty( $options['brevo_from_name'] )
		? $options['brevo_from_name']
		: get_bloginfo( 'name' );

	// Get recipient.
	$to       = is_array( $email['to'] ) ? $email['to'][0] : $email['to'];
	$to_email = is_array( $to ) ? $to['email'] : $to;

	// Get subject and message.
	$subject      = isset( $email['subject'] ) ? $email['subject'] : '';
	$message      = isset( $email['message'] ) ? $email['message'] : '';
	$content_type = isset( $email['content_type'] ) ? $email['content_type'] : 'text/plain';

	// Auto-detect HTML content.
	$is_html = ( 'text/html' === $content_type );
	if ( ! $is_html && ! empty( $message ) ) {
		if ( preg_match( '/<(html|body|div|p|br|a|table|tr|td|span|strong|em|h[1-6]|ul|ol|li|img)[^>]*>/i', $message ) ) {
			$is_html = true;
		}
	}

	// Determine HTML and text content.
	$html = '';
	$text = '';
	if ( $is_html ) {
		$html = $message;
		$text = wp_strip_all_tags( $message );
	} else {
		// Plain text email.
		// Check if it contains URLs - if so, convert to simple HTML with clickable links.
		if ( preg_match( '/https?:\/\/[^\s<>"\']+/i', $message ) ) {
			// Convert URLs to clickable links for HTML version.
			$html = nl2br( esc_html( $message ) );
			$html = preg_replace(
				'/(https?:\/\/[^\s<>"\']+)/i',
				'<a href="$1">$1</a>',
				$html
			);
			$text = $message;
		} else {
			// Pure plain text without URLs.
			$text = $message;
		}
	}

	// Build payload without defaults.
	$payload = authority_mailer_smtp_build_brevo_payload(
		$from_email,
		$from_name,
		$to_email,
		$subject,
		$html,
		$text,
		false
	);

	// Use helper function to extract email defaults with fallback to header parsing.
	$defaults = authority_mailer_smtp_get_email_defaults( $email );

	// Add email default headers to payload.
	if ( ! empty( $defaults['reply_to'] ) ) {
		if ( ! isset( $payload['replyTo'] ) ) {
			$payload['replyTo'] = array(
				'email' => $defaults['reply_to'],
			);
		}
	}

	if ( ! empty( $defaults['cc'] ) ) {
		if ( ! isset( $payload['cc'] ) ) {
			$payload['cc'] = array();
		}
		foreach ( $defaults['cc'] as $cc_email ) {
			$payload['cc'][] = array( 'email' => $cc_email );
		}
	}

	if ( ! empty( $defaults['bcc'] ) ) {
		if ( ! isset( $payload['bcc'] ) ) {
			$payload['bcc'] = array();
		}
		foreach ( $defaults['bcc'] as $bcc_email ) {
			$payload['bcc'][] = array( 'email' => $bcc_email );
		}
	}

	// Add priority headers.
	if ( ! empty( $defaults['priority'] ) ) {
		if ( ! isset( $payload['headers'] ) ) {
			$payload['headers'] = array();
		}
		$priority_headers = authority_mailer_smtp_get_priority_headers( $defaults['priority'] );
		foreach ( $priority_headers as $priority_header ) {
			$payload['headers'][ $priority_header['name'] ] = $priority_header['value'];
		}
	}

	// Add custom headers.
	if ( ! empty( $defaults['custom'] ) ) {
		if ( ! isset( $payload['headers'] ) ) {
			$payload['headers'] = array();
		}
		foreach ( $defaults['custom'] as $custom_header ) {
			$payload['headers'][ $custom_header['name'] ] = $custom_header['value'];
		}
	}

	// Add Return-Path header if provided.
	if ( ! empty( $defaults['return_path'] ) ) {
		if ( ! isset( $payload['headers'] ) ) {
			$payload['headers'] = array();
		}
		$payload['headers']['Return-Path'] = $defaults['return_path'];
	}

	// Debug logging for email defaults added to payload.
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
		error_log(
			'[Authority Mailer Brevo] Email defaults added to payload | Reply-To: ' .
			( ! empty( $defaults['reply_to'] ) ? $defaults['reply_to'] : 'none' ) .
			' | CC count: ' . count( $defaults['cc'] ) .
			' | BCC count: ' . count( $defaults['bcc'] ) .
			' | Priority: ' . ( ! empty( $defaults['priority'] ) ? $defaults['priority'] : 'none' ) .
			' | Return-Path: ' . ( ! empty( $defaults['return_path'] ) ? $defaults['return_path'] : 'none' )
		);
	}

	// Add tracking ID to payload if present (for webhook correlation).
	if ( ! empty( $email['tracking_id'] ) ) {
		// Brevo supports tags for tracking.
		if ( ! isset( $payload['tags'] ) ) {
			$payload['tags'] = array();
		}
		$payload['tags'][] = 'authority-mailer_tracking_id:' . $email['tracking_id'];

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
			error_log( '[Authority Mailer Brevo] Added tracking ID to payload: ' . $email['tracking_id'] );
		}
	}

	// Log the email attempt.
	$log_id = 0;
	if ( function_exists( 'authority_mailer_smtp_log_email_with_spam_score' ) ) {
		$log_data = array(
			'provider'   => 'brevo',
			'to_email'   => $to_email,
			'from_email' => $from_email,
			'from_name'  => $from_name,
			'subject'    => $subject,
			'headers'    => isset( $email['headers'] ) ? ( is_array( $email['headers'] ) ? wp_json_encode( $email['headers'] ) : $email['headers'] ) : '',
			'body'       => $message,
			'payload'    => wp_json_encode( $payload ),
			'status'     => 'attempt',
		);

		// Use centralized helper that handles spam score extraction and debug logging automatically.
		$log_id = authority_mailer_smtp_log_email_with_spam_score( $email, $log_data );
	}

	// Get SMTP host (default: api.brevo.com)
	$smtp_host = ! empty( $options['brevo_smtp_host'] )
		? $options['brevo_smtp_host']
		: 'api.brevo.com';

	// Make API call.
	$response = wp_remote_post(
		'https://' . $smtp_host . '/v3/smtp/email',
		array(
			'headers' => array(
				'Accept'       => 'application/json',
				'api-key'      => $api_key,
				'Content-Type' => 'application/json',
			),
			'body'    => wp_json_encode( $payload ),
			'timeout' => 30,
		)
	);

	if ( is_wp_error( $response ) ) {
		if ( function_exists( 'authority_mailer_smtp_email_logger_update' ) && $log_id ) {
			authority_mailer_smtp_email_logger_update(
				$log_id,
				array(
					'status'        => 'error',
					'response_code' => 0,
					'response_body' => $response->get_error_message(),
				)
			);
		}

		// Trigger failover notification.
		do_action(
			'authority_mailer_provider_failed',
			array(
				'provider'      => 'brevo',
				'error_code'    => $response->get_error_code(),
				'error_message' => $response->get_error_message(),
				'email_data'    => $email,
			)
		);

		return $response;
	}

	$code = wp_remote_retrieve_response_code( $response );
	$body = wp_remote_retrieve_body( $response );

	// Update log with response.
	if ( function_exists( 'authority_mailer_smtp_email_logger_update' ) && $log_id ) {
		authority_mailer_smtp_email_logger_update(
			$log_id,
			array(
				'status'        => ( $code >= 200 && $code < 300 ) ? 'accepted' : 'error',
				'response_code' => $code,
				'response_body' => is_string( $body ) ? substr( $body, 0, 2000 ) : '',
			)
		);
	}

	if ( $code < 200 || $code >= 300 ) {
		// Trigger failover notification.
		do_action(
			'authority_mailer_provider_failed',
			array(
				'provider'      => 'brevo',
				'error_code'    => $code,
				'error_message' => $body,
				'email_data'    => $email,
			)
		);

		return new WP_Error( 'brevo_error', 'Brevo API error: ' . $body, array( 'code' => $code ) );
	}

	return true;
}
