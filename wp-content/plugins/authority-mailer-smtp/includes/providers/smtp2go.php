<?php
/**
 * smtp2go.php
 *
 * SMTP2GO provider tester for Authority Mailer onboarding.
 *
 * - Merges onboarding-provided settings with authority_mailer_options (including nested provider groups).
 * - Detects and normalizes API key (adds "api-" prefix if a raw 32-char key is provided).
 * - Resolves SMTP2GO hosts, probes API and performs POST transmission to SMTP2GO's /v3/email/send endpoint.
 * - Uses centralized strings via authority_mailer_smtp_s() only (no hard-coded UI text).
 * - Skips empty resolver debug entries, avoids json_decode() on empty bodies,
 *   and uses authority_mailer_smtp_http_post_and_log() when available. Falls back to wp_remote_post()
 *   with authority_mailer_email_logger_insert/update instrumentation.
 *
 * Location: wp-content/plugins/authority-mailer-smtp/includes/providers/smtp2go.php
 *
 * @package Authority_Mailer
 */

defined( 'ABSPATH' ) || exit;

require_once AUTHORITY_MAILER_PLUGIN_DIR . 'includes/providers/common.php';

global $AUTHORITY_MAILER_STRINGS;

if ( ! function_exists( 'authority_mailer_smtp_s' ) ) {
	/**
	 * Safe localized accessor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The string key to look up in AUTHORITY_MAILER_STRINGS array.
	 * @return string The localized string value, or empty string if not found.
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
 * Build SMTP2GO payload for /v3/email/send endpoint.
 *
 * Constructs an SMTP2GO API compatible payload for sending test emails.
 * Requirements:
 * - Must include html_body or text_body (or template_id).
 * - 'to' should be an array of recipient email strings (or a single string).
 * - 'sender' is the sender email; 'sender_name' optional.
 *
 * @since 1.0.0
 *
 * @param string       $from_email   The sender email address.
 * @param string       $from_name    The sender display name.
 * @param string|array $to           The recipient email address(es).
 * @param string       $subject      Optional. Email subject line. Default empty string.
 * @param string       $html         Optional. HTML body content. Default empty string.
 * @param string       $text         Optional. Plain text body content. Default empty string.
 * @param bool         $use_defaults Optional. Whether to use default subject/body if empty. Default true.
 * @return array The SMTP2GO API payload array.
 */
function authority_mailer_smtp_build_smtp2go_payload( $from_email, $from_name, $to, $subject = '', $html = '', $text = '', $use_defaults = true ) {
	$subject = (string) $subject;
	$html    = (string) $html;
	$text    = (string) $text;

	if ( $use_defaults ) {
		if ( '' === trim( $subject ) ) {
			$subject = authority_mailer_smtp_s( 'smtp2go_default_subject' );
		}
		if ( '' === trim( $html ) && '' === trim( $text ) ) {
			$html = authority_mailer_smtp_s( 'smtp2go_default_body' );
		}
	}

	if ( '' === trim( $text ) && '' !== trim( $html ) ) {
		$text = trim( wp_strip_all_tags( $html ) );
	}

	$sender_email = (string) $from_email;
	$sender_name  = '' !== trim( $from_name ) ? (string) $from_name : '';

	// SMTP2GO expects RFC-822 format: "Name <email@example.com>"
	// If sender_name is provided, combine them in RFC-822 format.
	if ( '' !== $sender_name ) {
		$sender = sprintf( '%s <%s>', $sender_name, $sender_email );
	} else {
		$sender = $sender_email;
	}

	// Normalize 'to' as array of strings.
	$to_array = array();
	if ( is_array( $to ) ) {
		foreach ( $to as $t ) {
			$to_array[] = (string) $t;
		}
	} else {
		$to_array[] = (string) $to;
	}

	$payload = array(
		'sender'  => $sender,
		'to'      => $to_array,
		'subject' => (string) $subject,
	);

	if ( '' !== trim( $html ) ) {
		$payload['html_body'] = (string) $html;
	}
	if ( '' !== trim( $text ) ) {
		$payload['text_body'] = (string) $text;
	}

	return $payload;
}

/**
 * Run SMTP2GO diagnostics and test transmission.
 *
 * @param array $settings
 * @return array
 */
function authority_mailer_smtp_test_smtp2go( $settings = array() ) {
	$steps = array();

	$steps[] = array(
		'status'  => 'info',
		'message' => ( authority_mailer_smtp_s( 'smtp2go_diag_start' ) ? authority_mailer_smtp_s( 'smtp2go_diag_start' ) : 'Starting SMTP2GO diagnostics' ),
	);

	$provided_settings = is_array( $settings ) ? $settings : array();

	$steps[] = array(
		'status'  => 'detail',
		'message' => ( authority_mailer_smtp_s( 'smtp2go_onboarding_keys' ) ? authority_mailer_smtp_s( 'smtp2go_onboarding_keys' ) : 'Onboarding-provided settings summary' ),
		'details' => function_exists( 'authority_mailer_smtp_settings_summary' ) ? authority_mailer_smtp_settings_summary( $provided_settings ) : array(),
	);

	$mm_opts = get_option( 'authority_mailer_smtp_options', array() );
	$mm_opts = is_array( $mm_opts ) ? $mm_opts : array();

	$steps[] = array(
		'status'  => 'detail',
		'message' => ( authority_mailer_smtp_s( 'smtp2go_mm_opts_keys' ) ? authority_mailer_smtp_s( 'smtp2go_mm_opts_keys' ) : 'Stored authority_mailer_options inspected (summary)' ),
		'details' => function_exists( 'authority_mailer_smtp_settings_summary' ) ? authority_mailer_smtp_settings_summary( $mm_opts ) : array(),
	);

	// Merge provider-scoped settings (authority_mailer_options['smtp2go'], etc.)
	$provider_groups = array( 'smtp2go', 'smtp2go_settings', 'smtp2go_options' );
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

	// Whitelist/accept top-level keys.
	$allowed_keys = array(
		'from_email',
		'from_name',
		'test_recipient',
		'test_subject',
		'html_content',
		'plain_content',
		'endpoint_host',
		'endpoint_ip',
		'allow_insecure',
		'force_from_email',
		'force_from_name',
		'smtp2go_api_key',
		'smtp2go_key',
		'api_key',
		'apikey',
		'key',
		'token',
		'smtp2go_from_email',
		'smtp2go_from_name',
	);
	foreach ( $mm_opts as $k => $v ) {
		if ( isset( $provided_settings[ $k ] ) ) {
			continue;
		}
		if ( in_array( $k, $allowed_keys, true ) || 0 === strpos( $k, 'smtp2go_' ) ) {
			$provided_settings[ $k ] = $v;
		}
	}

	$steps[] = array(
		'status'  => 'detail',
		'message' => ( authority_mailer_smtp_s( 'smtp2go_final_settings' ) ? authority_mailer_smtp_s( 'smtp2go_final_settings' ) : 'Final settings used after merge (summary)' ),
		'details' => function_exists( 'authority_mailer_smtp_settings_summary' ) ? authority_mailer_smtp_settings_summary( $provided_settings ) : array(),
	);

	// API key detection (robust).
	$key_candidates = array( 'smtp2go_api_key', 'smtp2go_key', 'api_key', 'apikey', 'key', 'token' );
	$api_key        = '';
	$found_path     = '';

	if ( function_exists( 'authority_mailer_smtp_find_api_key_in_array' ) ) {
		list( $found, $val, $path ) = authority_mailer_smtp_find_api_key_in_array( $provided_settings, $key_candidates );
		if ( $found ) {
			$api_key    = trim( (string) $val );
			$found_path = $path;
		}
	}

	// fallback nested.
	if ( '' === $api_key && isset( $mm_opts['smtp2go'] ) && is_array( $mm_opts['smtp2go'] ) ) {
		foreach ( $key_candidates as $kn ) {
			if ( ! empty( $mm_opts['smtp2go'][ $kn ] ) ) {
				$api_key    = trim( (string) $mm_opts['smtp2go'][ $kn ] );
				$found_path = 'authority_mailer_smtp_options.smtp2go.' . $kn;
				break;
			}
		}
	}

	// fallback top-level.
	if ( '' === $api_key ) {
		foreach ( $key_candidates as $kn ) {
			if ( ! empty( $mm_opts[ $kn ] ) ) {
				$api_key    = trim( (string) $mm_opts[ $kn ] );
				$found_path = $kn;
				break;
			}
		}
	}

	if ( '' === $api_key ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => ( authority_mailer_smtp_s( 'smtp2go_api_key_missing' ) ? authority_mailer_smtp_s( 'smtp2go_api_key_missing' ) : 'SMTP2GO API key not found' ),
			'details' => ( authority_mailer_smtp_s( 'smtp2go_api_key_missing_detail' ) ? authority_mailer_smtp_s( 'smtp2go_api_key_missing_detail' ) : 'Ensure the SMTP2GO API key is provided in the onboarding form or stored in authority_mailer_options.' ),
		);
		return $steps;
	}

	// Normalize SMTP2GO API key: account keys are like api-[A-Za-z0-9]{32}.
	if ( ! preg_match( '/^api-[A-Za-z0-9]{32}$/', $api_key ) ) {
		if ( preg_match( '/^[A-Za-z0-9]{32}$/', $api_key ) ) {
			$api_key = 'api-' . $api_key;
			$steps[] = array(
				'status'  => 'detail',
				'message' => 'Normalized SMTP2GO API key by adding required "api-" prefix',
				'details' => function_exists( 'authority_mailer_smtp_mask_key' ) ? authority_mailer_smtp_mask_key( $api_key ) : substr( $api_key, -8 ),
			);
		} else {
			$steps[] = array(
				'status'  => 'detail',
				'message' => 'Using provided SMTP2GO API key (masked) — format did not match expected patterns',
				'details' => function_exists( 'authority_mailer_smtp_mask_key' ) ? authority_mailer_smtp_mask_key( $api_key ) : '(masked)',
			);
		}
	}

	$steps[] = array(
		'status'  => 'info',
		'message' => ( authority_mailer_smtp_s( 'smtp2go_api_detected' ) ? authority_mailer_smtp_s( 'smtp2go_api_detected' ) : 'API key detected' ),
		'details' => array(
			'found_in'   => (string) $found_path,
			'masked_key' => function_exists( 'authority_mailer_smtp_mask_key' ) ? authority_mailer_smtp_mask_key( $api_key ) : '',
		),
	);

	// Hosts to try and DNS resolution.
	$hosts_to_try = array( 'api.smtp2go.com' );
	if ( ! empty( $provided_settings['endpoint_host'] ) ) {
		array_unshift( $hosts_to_try, sanitize_text_field( $provided_settings['endpoint_host'] ) );
	}

	$endpoint_ip_override = ! empty( $provided_settings['endpoint_ip'] ) ? trim( (string) $provided_settings['endpoint_ip'] ) : '';
	$resolved_ip          = '';
	$used_host            = '';

	foreach ( $hosts_to_try as $host ) {
		$host    = (string) $host;
		$steps[] = array(
			'status'  => 'info',
			'message' => sprintf( ( authority_mailer_smtp_s( 'smtp2go_resolving' ) ? authority_mailer_smtp_s( 'smtp2go_resolving' ) : 'Resolving DNS for %s' ), $host ),
		);

		list( $ip, $debug ) = function_exists( 'authority_mailer_smtp_resolve_host_with_doh' ) ? authority_mailer_smtp_resolve_host_with_doh( $host ) : array( '', array() );

		// skip empty debug lines.
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
				'message' => sprintf( ( authority_mailer_smtp_s( 'smtp2go_resolved' ) ? authority_mailer_smtp_s( 'smtp2go_resolved' ) : 'Resolved %1$s -> %2$s' ), $host, $ip ),
			);
			break;
		}

		$steps[] = array(
			'status'  => 'detail',
			'message' => sprintf( ( authority_mailer_smtp_s( 'smtp2go_could_not_resolve' ) ? authority_mailer_smtp_s( 'smtp2go_could_not_resolve' ) : 'Could not resolve %s' ), $host ),
			'details' => $host,
		);
	}

	if ( empty( $resolved_ip ) && ! empty( $endpoint_ip_override ) ) {
		$resolved_ip = $endpoint_ip_override;
		$used_host   = isset( $provided_settings['endpoint_host'] ) ? sanitize_text_field( $provided_settings['endpoint_host'] ) : 'api.smtp2go.com';
		$steps[]     = array(
			'status'  => 'info',
			'message' => sprintf( ( authority_mailer_smtp_s( 'smtp2go_using_ip_override' ) ? authority_mailer_smtp_s( 'smtp2go_using_ip_override' ) : 'Using endpoint_ip override: %s' ), $resolved_ip ),
		);
	}

	if ( empty( $resolved_ip ) ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => ( authority_mailer_smtp_s( 'smtp2go_dns_failed' ) ? authority_mailer_smtp_s( 'smtp2go_dns_failed' ) : 'DNS resolution failed for SMTP2GO hosts' ),
			'details' => ( authority_mailer_smtp_s( 'smtp2go_dns_failed_detail' ) ? authority_mailer_smtp_s( 'smtp2go_dns_failed_detail' ) : '' ),
		);
		return $steps;
	}

	// From selection & force toggles (re-use pattern used across providers).
	$force_from_email = authority_mailer_smtp_to_bool( ! empty( $provided_settings['smtp2go_force_from_email'] ) ? $provided_settings['smtp2go_force_from_email'] : ( ! empty( $provided_settings['force_from_email'] ) ? $provided_settings['force_from_email'] : false ) );
	$force_from_name  = authority_mailer_smtp_to_bool( ! empty( $provided_settings['smtp2go_force_from_name'] ) ? $provided_settings['smtp2go_force_from_name'] : ( ! empty( $provided_settings['force_from_name'] ) ? $provided_settings['force_from_name'] : false ) );

	$provider_email_keys = array( 'smtp2go_from_email', 'smtp2go_from', 'from_email', 'other_from_email' );
	$provider_name_keys  = array( 'smtp2go_from_name', 'smtp2go_fromname', 'from_name', 'other_from_name' );

	$final_from_email = '';
	$final_from_name  = '';

	if ( $force_from_email ) {
		foreach ( $provider_email_keys as $fk ) {
			if ( ! empty( $provided_settings[ $fk ] ) ) {
				$final_from_email = sanitize_email( $provided_settings[ $fk ] );
				break; }
		}
		if ( '' === $final_from_email ) {
			$final_from_email = get_option( 'admin_email', '' );
		}
	} else {
		$final_from_email = get_option( 'admin_email', '' );
		if ( ! empty( $provided_settings['smtp2go_from_email'] ) ) {
			$steps[] = array(
				'status'  => 'detail',
				'message' => ( authority_mailer_smtp_s( 'provider_ignored_from_email' ) ? authority_mailer_smtp_s( 'provider_ignored_from_email' ) : 'Provider defines a From Email but Force From Email is disabled — using site admin_email instead.' ),
				'details' => (string) $provided_settings['smtp2go_from_email'],
			);
		}
	}

	if ( $force_from_name ) {
		foreach ( $provider_name_keys as $fnk ) {
			if ( ! empty( $provided_settings[ $fnk ] ) ) {
				$final_from_name = sanitize_text_field( $provided_settings[ $fnk ] );
				break; }
		}
		if ( '' === $final_from_name ) {
			$final_from_name = get_bloginfo( 'name' );
		}
	} else {
		$final_from_name = get_bloginfo( 'name' );
		if ( ! empty( $provided_settings['smtp2go_from_name'] ) ) {
			$steps[] = array(
				'status'  => 'detail',
				'message' => ( authority_mailer_smtp_s( 'provider_ignored_from_name' ) ? authority_mailer_smtp_s( 'provider_ignored_from_name' ) : 'Provider defines a From Name but Force From Name is disabled — using site name instead.' ),
				'details' => (string) $provided_settings['smtp2go_from_name'],
			);
		}
	}

	// Recipient selection.
	if ( ! empty( $provided_settings['test_recipient'] ) ) {
		$test_to = sanitize_email( $provided_settings['test_recipient'] );
		$steps[] = array(
			'status'  => 'detail',
			'message' => ( authority_mailer_smtp_s( 'smtp2go_using_test_recipient' ) ? authority_mailer_smtp_s( 'smtp2go_using_test_recipient' ) : 'Using test_recipient from settings' ),
			'details' => (string) $test_to,
		);
	} else {
		$test_to = get_option( 'admin_email', '' );
		$steps[] = array(
			'status'  => 'detail',
			'message' => ( authority_mailer_smtp_s( 'smtp2go_using_admin_email' ) ? authority_mailer_smtp_s( 'smtp2go_using_admin_email' ) : 'Using admin_email as test recipient' ),
			'details' => (string) $test_to,
		);
	}

	if ( empty( $test_to ) || ! is_email( $test_to ) ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => ( authority_mailer_smtp_s( 'smtp2go_no_recipient' ) ? authority_mailer_smtp_s( 'smtp2go_no_recipient' ) : 'No recipient available for test email (admin email not set).' ),
		);
		return $steps;
	}

	// Prepare endpoint and payload (defensive).
	$probe_path    = '/v3/email/send';
	$endpoint_host = ! empty( $used_host ) ? $used_host : ( ! empty( $provided_settings['endpoint_host'] ) ? sanitize_text_field( $provided_settings['endpoint_host'] ) : 'api.smtp2go.com' );
	$endpoint_host = trim( (string) $endpoint_host );
	if ( '' === $endpoint_host ) {
		$endpoint_host = 'api.smtp2go.com';
	}

	$endpoint_url_host = 'https://' . $endpoint_host . $probe_path;
	if ( false === filter_var( $endpoint_url_host, FILTER_VALIDATE_URL ) ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => ( authority_mailer_smtp_s( 'smtp2go_post_error_network' ) ? authority_mailer_smtp_s( 'smtp2go_post_error_network' ) : 'Invalid endpoint URL constructed' ),
			'details' => (string) $endpoint_url_host,
		);
		return $steps;
	}

	$resolve_entry = $endpoint_host . ':443:' . $resolved_ip;

	// Build professional test email using centralized template.
	$test_email_args = array(
		'provider'   => 'smtp2go',
		'from_email' => $final_from_email,
		'from_name'  => $final_from_name,
		'to_email'   => $test_to,
	);

	// Use professional template if available, otherwise fallback.
	if ( function_exists( 'authority_mailer_smtp_get_test_email_subject' ) ) {
		$subject = authority_mailer_smtp_get_test_email_subject( 'SMTP2GO' );
	} else {
		$subject = isset( $provided_settings['test_subject'] ) ? sanitize_text_field( $provided_settings['test_subject'] ) : ( ( authority_mailer_smtp_s( 'smtp2go_default_subject' ) ? authority_mailer_smtp_s( 'smtp2go_default_subject' ) : 'Authority Mailer SMTP2GO Test' ) );
	}

	if ( function_exists( 'authority_mailer_smtp_get_test_email_html' ) ) {
		$body_html = authority_mailer_smtp_get_test_email_html( $test_email_args );
		$body_text = function_exists( 'authority_mailer_smtp_get_test_email_plain' ) ? authority_mailer_smtp_get_test_email_plain( $test_email_args ) : wp_strip_all_tags( $body_html );
	} else {
		$body_html = isset( $provided_settings['html_content'] ) ? (string) $provided_settings['html_content'] : ( ( authority_mailer_smtp_s( 'smtp2go_default_body' ) ? authority_mailer_smtp_s( 'smtp2go_default_body' ) : '<p>Authority Mailer SMTP2GO test</p>' ) );
		$body_text = isset( $provided_settings['plain_content'] ) ? (string) $provided_settings['plain_content'] : wp_strip_all_tags( $body_html );
	}

	$payload_message = authority_mailer_smtp_build_smtp2go_payload( $final_from_email, $final_from_name, $test_to, $subject, $body_html, $body_text );

	// Ensure api_key top-level and proper sender/to shapes for SMTP2GO.
	$payload = array_merge( array( 'api_key' => (string) $api_key ), $payload_message );

	$args_post = array(
		'timeout' => 30,
		'headers' => array(
			'X-Smtp2go-Api-Key' => (string) $api_key,
			'Accept'            => 'application/json',
			'Content-Type'      => 'application/json',
			'Host'              => $endpoint_host,
		),
		'subject' => (string) $subject,
		'body'    => wp_json_encode( $payload ),
		'curl'    => array( CURLOPT_RESOLVE => array( $resolve_entry ) ),
	);

	if ( ! empty( $provided_settings['allow_insecure'] ) ) {
		$args_post['sslverify'] = false;
		$steps[]                = array(
			'status'  => 'detail',
			'message' => ( authority_mailer_smtp_s( 'smtp2go_allow_insecure' ) ? authority_mailer_smtp_s( 'smtp2go_allow_insecure' ) : 'allow_insecure is enabled — SSL verification disabled (debug only)' ),
		);
	}

	$steps[] = array(
		'status'  => 'info',
		'message' => sprintf( ( authority_mailer_smtp_s( 'smtp2go_attempting_post' ) ? authority_mailer_smtp_s( 'smtp2go_attempting_post' ) : 'Attempting POST %s' ), $endpoint_url_host ),
		'details' => array( ( authority_mailer_smtp_s( 'smtp2go_payload_preview' ) ? authority_mailer_smtp_s( 'smtp2go_payload_preview' ) : 'Payload keys sent (preview)' ) => authority_mailer_smtp_list_keys( (array) $payload ) ),
	);

	// Use centralized test helper - sends through full wp_mail() pipeline with premium features.
	if ( function_exists( 'authority_mailer_smtp_send_test_via_wpmail' ) ) {
		authority_mailer_smtp_send_test_via_wpmail( 'smtp2go', $test_to, $final_from_email, $final_from_name, $steps );
	} else {
		$steps[] = array(
			'status'  => 'error',
			'message' => 'Centralized test helper function not found. Please update common.php.',
		);
	}

	return $steps;
}



/**
 * Send email via SMTP2GO API
 *
 * @param array $email Email data from wp_mail
 * @return true|WP_Error
 */
function authority_mailer_smtp_send_smtp2go( $email ) {
	$options = get_option( 'authority_mailer_smtp_options', array() );

	// Get API key.
	$api_key = ! empty( $options['smtp2go_api_key'] )
		? $options['smtp2go_api_key']
		: '';

	if ( empty( $api_key ) ) {
		return new WP_Error( 'missing_api_key', 'SMTP2GO API key not configured' );
	}

	// Get from email/name.
	$from_email = ! empty( $options['smtp2go_from_email'] )
		? $options['smtp2go_from_email']
		: get_option( 'admin_email' );

	$from_name = ! empty( $options['smtp2go_from_name'] )
		? $options['smtp2go_from_name']
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
	$payload = authority_mailer_smtp_build_smtp2go_payload(
		$from_email,
		$from_name,
		$to_email,
		$subject,
		$html,
		$text,
		false
	);

	// SMTP2GO requires api_key in payload.
	$payload['api_key'] = $api_key;

	// Use helper function to extract email defaults with fallback to header parsing.
	$defaults = authority_mailer_smtp_get_email_defaults( $email );

	// Add email default headers to payload.
	if ( ! empty( $defaults['reply_to'] ) ) {
		if ( ! isset( $payload['custom_headers'] ) ) {
			$payload['custom_headers'] = array();
		}
		$payload['custom_headers'][] = array(
			'header' => 'Reply-To',
			'value'  => $defaults['reply_to'],
		);
	}

	if ( ! empty( $defaults['cc'] ) ) {
		if ( ! isset( $payload['cc'] ) ) {
			$payload['cc'] = array();
		}
		foreach ( $defaults['cc'] as $cc_email ) {
			$payload['cc'][] = $cc_email;
		}
	}

	if ( ! empty( $defaults['bcc'] ) ) {
		if ( ! isset( $payload['bcc'] ) ) {
			$payload['bcc'] = array();
		}
		foreach ( $defaults['bcc'] as $bcc_email ) {
			$payload['bcc'][] = $bcc_email;
		}
	}

	// Add priority headers.
	if ( ! empty( $defaults['priority'] ) ) {
		if ( ! isset( $payload['custom_headers'] ) ) {
			$payload['custom_headers'] = array();
		}
		$priority_headers = authority_mailer_smtp_get_priority_headers( $defaults['priority'] );
		foreach ( $priority_headers as $priority_header ) {
			$payload['custom_headers'][] = array(
				'header' => $priority_header['name'],
				'value'  => $priority_header['value'],
			);
		}
	}

	// Add custom headers.
	if ( ! empty( $defaults['custom'] ) ) {
		if ( ! isset( $payload['custom_headers'] ) ) {
			$payload['custom_headers'] = array();
		}
		foreach ( $defaults['custom'] as $custom_header ) {
			$payload['custom_headers'][] = array(
				'header' => $custom_header['name'],
				'value'  => $custom_header['value'],
			);
		}
	}

	// Add tracking ID to payload if present (for webhook correlation).
	if ( ! empty( $email['tracking_id'] ) ) {
		// SMTP2GO supports custom headers and tags for tracking.
		// Add as custom header (preferred method) - must be array of objects with header/value keys.
		if ( ! isset( $payload['custom_headers'] ) ) {
			$payload['custom_headers'] = array();
		}
		$payload['custom_headers'][] = array(
			'header' => 'X-Authority-Mailer-Tracking-ID',
			'value'  => $email['tracking_id'],
		);

		// Also add as tag (fallback method).
		if ( ! isset( $payload['tags'] ) ) {
			$payload['tags'] = array();
		}
		$payload['tags'][] = 'tracking_id:' . $email['tracking_id'];

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
			error_log( '[Authority Mailer SMTP2GO] Added tracking ID to payload: ' . $email['tracking_id'] );
		}
	}

	// Log the email attempt.
	$log_id = 0;
	if ( function_exists( 'authority_mailer_smtp_log_email_with_spam_score' ) ) {
		$log_data = array(
			'provider'   => 'smtp2go',
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

	// Make API call.
	$response = wp_remote_post(
		'https://api.smtp2go.com/v3/email/send',
		array(
			'headers' => array(
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
				'provider'      => 'smtp2go',
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
				'provider'      => 'smtp2go',
				'error_code'    => $code,
				'error_message' => $body,
				'email_data'    => $email,
			)
		);

		return new WP_Error( 'smtp2go_error', 'SMTP2GO API error: ' . $body, array( 'code' => $code ) );
	}

	return true;
}
