<?php
/**
 * mailjet.php
 *
 * Mailjet provider tester for Authority Mailer onboarding.
 *
 * - Merges onboarding-provided settings with authority_mailer_options (including nested provider groups).
 * - Resolves Mailjet hosts, probes API and performs POST transmission to Mailjet's /v3.1/send endpoint.
 * - Uses centralized strings via authority_mailer_smtp_s() only (no hard-coded UI text).
 * - Skips empty resolver debug entries, avoids json_decode() on empty bodies,
 *   and uses authority_mailer_smtp_http_post_and_log() when available.
 *
 * Location: wp-content/plugins/authority-mailer-smtp/includes/providers/mailjet.php
 *
 * @package Authority_Mailer
 */

defined( 'ABSPATH' ) || exit;

require_once AUTHORITY_MAILER_PLUGIN_DIR . 'includes/providers/common.php';

global $AUTHORITY_MAILER_STRINGS;

if ( ! function_exists( 'authority_mailer_smtp_s' ) ) {
	/**
	 * Localized accessor that returns empty string when key missing.
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
 * Build Mailjet v3.1 payload (simplified for tester).
 *
 * Constructs a Mailjet API v3.1 compatible payload for sending test emails.
 *
 * @since 1.0.0
 *
 * @param string $from_email   The sender email address.
 * @param string $from_name    The sender display name.
 * @param string $to           The recipient email address.
 * @param string $subject      Optional. Email subject line. Default empty string.
 * @param string $html         Optional. HTML body content. Default empty string.
 * @param string $text         Optional. Plain text body content. Default empty string.
 * @param bool   $use_defaults Optional. Whether to use default subject/body if empty. Default true.
 * @return array The Mailjet API payload array.
 */
function authority_mailer_smtp_build_mailjet_payload( $from_email, $from_name, $to, $subject = '', $html = '', $text = '', $use_defaults = true ) {
	$subject = (string) $subject;
	$html    = (string) $html;
	$text    = (string) $text;

	if ( $use_defaults ) {
		if ( '' === trim( $subject ) ) {
			$subject = authority_mailer_smtp_s( 'mailjet_default_subject' );
		}
		if ( '' === trim( $html ) && '' === trim( $text ) ) {
			$html = authority_mailer_smtp_s( 'mailjet_default_body' );
		}
	}

	if ( '' === trim( $text ) && '' !== trim( $html ) ) {
		$text = trim( wp_strip_all_tags( $html ) );
	}

	$message = array(
		'From'    => array(
			'Email' => (string) $from_email,
			'Name'  => (string) $from_name,
		),
		'To'      => array(
			array( 'Email' => (string) $to ),
		),
		'Subject' => (string) $subject,
	);

	if ( '' !== trim( $html ) ) {
		$message['HTMLPart'] = (string) $html;
	}
	if ( '' !== trim( $text ) ) {
		$message['TextPart'] = (string) $text;
	}

	return array( 'Messages' => array( $message ) );
}

/**
 * Run Mailjet diagnostics and test transmission.
 *
 * Tests Mailjet API connection, validates credentials, and sends a test email.
 * Performs DNS resolution, API key validation, and HTTP POST to Mailjet endpoint.
 *
 * @since 1.0.0
 *
 * @param array $settings Optional. Array of Mailjet settings including api_key, api_secret, from_email, etc. Default empty array.
 * @return array Array of diagnostic steps with status, message, and details for each step.
 */
function authority_mailer_smtp_test_mailjet( $settings = array() ) {
	$steps = array();

	$steps[] = array(
		'status'  => 'info',
		'message' => authority_mailer_smtp_s( 'mailjet_diag_start' ),
	);

	$provided_settings = is_array( $settings ) ? $settings : array();

	$steps[] = array(
		'status'  => 'detail',
		'message' => authority_mailer_smtp_s( 'mailjet_onboarding_keys' ),
		'details' => function_exists( 'authority_mailer_smtp_settings_summary' ) ? authority_mailer_smtp_settings_summary( $provided_settings ) : array(),
	);

	$mm_opts = get_option( 'authority_mailer_smtp_options', array() );
	$mm_opts = is_array( $mm_opts ) ? $mm_opts : array();

	$steps[] = array(
		'status'  => 'detail',
		'message' => authority_mailer_smtp_s( 'mailjet_mm_opts_keys' ),
		'details' => function_exists( 'authority_mailer_smtp_settings_summary' ) ? authority_mailer_smtp_settings_summary( $mm_opts ) : array(),
	);

	// Merge provider-scoped settings (authority_mailer_options['mailjet'], etc.)
	$provider_groups = array( 'mailjet', 'mailjet_settings', 'mailjet_options' );
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

	// Whitelist some keys from mm_opts to merge.
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
		'mailjet_api_key',
		'mailjet_api_secret',
		'mailjet_api_key_public',
		'mailjet_api_key_private',
		'mailjet_from_email',
		'mailjet_from_name',
	);

	foreach ( $mm_opts as $k => $v ) {
		if ( isset( $provided_settings[ $k ] ) ) {
			continue;
		}
		if ( in_array( $k, $allowed_keys, true ) || 0 === strpos( $k, 'mailjet_' ) ) {
			$provided_settings[ $k ] = $v;
		}
	}

	$steps[] = array(
		'status'  => 'detail',
		'message' => authority_mailer_smtp_s( 'mailjet_final_settings' ),
		'details' => function_exists( 'authority_mailer_smtp_settings_summary' ) ? authority_mailer_smtp_settings_summary( $provided_settings ) : array(),
	);

	// Robust detection of API key & secret (search common shapes).
	$key_candidates = array( 'mailjet_api_key', 'mailjet_api_key_public', 'api_key', 'key' );

	// NOTE: include 'mailjet_secret_key' because the settings partial posts the secret using that name.
	$secret_candidates = array( 'mailjet_api_secret', 'mailjet_secret_key', 'mailjet_api_key_private', 'mailjet_secret', 'api_secret', 'secret' );

	$api_key    = '';
	$api_secret = '';
	$found_path = '';

	if ( function_exists( 'authority_mailer_smtp_find_api_key_in_array' ) ) {
		list( $found_k, $val_k, $path_k ) = authority_mailer_smtp_find_api_key_in_array( $provided_settings, $key_candidates );
		if ( $found_k ) {
			$api_key    = trim( (string) $val_k );
			$found_path = $path_k;
		}
		list( $found_s, $val_s, $path_s ) = authority_mailer_smtp_find_api_key_in_array( $provided_settings, $secret_candidates );
		if ( $found_s ) {
			$api_secret = trim( (string) $val_s );
			if ( '' === $found_path ) {
				$found_path = $path_s;
			}
		}
	}

	// Fallback: nested mm_opts['mailjet'].
	if ( '' === $api_key && isset( $mm_opts['mailjet'] ) && is_array( $mm_opts['mailjet'] ) ) {
		foreach ( $key_candidates as $kn ) {
			if ( ! empty( $mm_opts['mailjet'][ $kn ] ) ) {
				$api_key    = trim( (string) $mm_opts['mailjet'][ $kn ] );
				$found_path = 'authority_mailer_smtp_options.mailjet.' . $kn;
				break;
			}
		}
	}
	if ( '' === $api_secret && isset( $mm_opts['mailjet'] ) && is_array( $mm_opts['mailjet'] ) ) {
		foreach ( $secret_candidates as $sn ) {
			if ( ! empty( $mm_opts['mailjet'][ $sn ] ) ) {
				$api_secret = trim( (string) $mm_opts['mailjet'][ $sn ] );
				$found_path = $found_path ?: 'authority_mailer_smtp_options.mailjet.' . $sn;
				break;
			}
		}
	}

	// Fallback: top-level mm_opts.
	if ( '' === $api_key ) {
		foreach ( $key_candidates as $kn ) {
			if ( ! empty( $mm_opts[ $kn ] ) ) {
				$api_key    = trim( (string) $mm_opts[ $kn ] );
				$found_path = $kn;
				break;
			}
		}
	}
	if ( '' === $api_secret ) {
		foreach ( $secret_candidates as $sn ) {
			if ( ! empty( $mm_opts[ $sn ] ) ) {
				$api_secret = trim( (string) $mm_opts[ $sn ] );
				$found_path = $found_path ?: $sn;
				break;
			}
		}
	}

	if ( '' === $api_key || '' === $api_secret ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => authority_mailer_smtp_s( 'mailjet_api_key_missing' ),
			'details' => authority_mailer_smtp_s( 'mailjet_api_key_missing_detail' ),
		);
		return $steps;
	}

	$steps[] = array(
		'status'  => 'info',
		'message' => authority_mailer_smtp_s( 'mailjet_api_detected' ) ? authority_mailer_smtp_s( 'mailjet_api_detected' ) : authority_mailer_smtp_s( 'mailjet_api_key_missing' ),
		'details' => array(
			'found_in'   => (string) $found_path,
			'masked_key' => function_exists( 'authority_mailer_smtp_mask_key' ) ? authority_mailer_smtp_mask_key( $api_key ) : '',
		),
	);

	// Hosts to try.
	$hosts_to_try = array( 'api.mailjet.com' );
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
			'message' => sprintf( authority_mailer_smtp_s( 'mailjet_resolving' ), $host ),
		);

		list( $ip, $debug ) = function_exists( 'authority_mailer_smtp_resolve_host_with_doh' ) ? authority_mailer_smtp_resolve_host_with_doh( $host ) : array( '', array() );

		// Skip empty debug entries.
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
				'message' => sprintf( authority_mailer_smtp_s( 'mailjet_resolved' ), $host, $ip ),
			);
			break;
		}

		$steps[] = array(
			'status'  => 'detail',
			'message' => sprintf( authority_mailer_smtp_s( 'mailjet_could_not_resolve' ), $host ),
		);
	}

	if ( empty( $resolved_ip ) && ! empty( $endpoint_ip_override ) ) {
		$resolved_ip = $endpoint_ip_override;
		$used_host   = isset( $provided_settings['endpoint_host'] ) ? sanitize_text_field( $provided_settings['endpoint_host'] ) : '';
		$steps[]     = array(
			'status'  => 'info',
			'message' => sprintf( authority_mailer_smtp_s( 'mailjet_using_ip_override' ), $resolved_ip ),
		);
	}

	if ( empty( $resolved_ip ) ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => authority_mailer_smtp_s( 'mailjet_dns_failed' ),
			'details' => authority_mailer_smtp_s( 'mailjet_dns_failed_detail' ),
		);
		return $steps;
	}

	// From selection & force toggles.
	$force_from_email = authority_mailer_smtp_to_bool(
		! empty( $provided_settings['mailjet_force_from_email'] ) ? $provided_settings['mailjet_force_from_email'] :
		( ! empty( $provided_settings['force_from_email'] ) ? $provided_settings['force_from_email'] : false )
	);
	$force_from_name  = authority_mailer_smtp_to_bool(
		! empty( $provided_settings['mailjet_force_from_name'] ) ? $provided_settings['mailjet_force_from_name'] :
		( ! empty( $provided_settings['force_from_name'] ) ? $provided_settings['force_from_name'] : false )
	);

	$provider_email_keys = array( 'mailjet_from_email', 'mailjet_from', 'from_email', 'other_from_email' );
	$provider_name_keys  = array( 'mailjet_from_name', 'mailjet_fromname', 'from_name', 'other_from_name' );

	$provider_has_from_email = false;
	foreach ( $provider_email_keys as $k ) {
		if ( ! empty( $provided_settings[ $k ] ) ) {
			$provider_has_from_email = true;
			break;
		}
	}
	$provider_has_from_name = false;
	foreach ( $provider_name_keys as $k ) {
		if ( ! empty( $provided_settings[ $k ] ) ) {
			$provider_has_from_name = true;
			break;
		}
	}

	if ( $force_from_email ) {
		$candidate_from_email = '';
		foreach ( $provider_email_keys as $fk ) {
			if ( ! empty( $provided_settings[ $fk ] ) ) {
				$candidate_from_email = sanitize_email( $provided_settings[ $fk ] );
				break;
			}
		}
		if ( '' === $candidate_from_email ) {
			$candidate_from_email = get_option( 'admin_email', '' );
			$steps[]              = array(
				'status'  => 'detail',
				'message' => authority_mailer_smtp_s( 'mailjet_no_from_fallback' ),
				'details' => (string) $candidate_from_email,
			);
		}
	} else {
		$candidate_from_email = get_option( 'admin_email', '' );
		if ( $provider_has_from_email ) {
			$steps[] = array(
				'status'  => 'detail',
				'message' => authority_mailer_smtp_s( 'provider_ignored_from_email' ),
				'details' => (string) ( isset( $provided_settings['mailjet_from_email'] ) ? $provided_settings['mailjet_from_email'] : '' ),
			);
		}
	}

	if ( $force_from_name ) {
		$candidate_from_name = '';
		foreach ( $provider_name_keys as $fnk ) {
			if ( ! empty( $provided_settings[ $fnk ] ) ) {
				$candidate_from_name = sanitize_text_field( $provided_settings[ $fnk ] );
				break;
			}
		}
		if ( '' === $candidate_from_name ) {
			$candidate_from_name = get_bloginfo( 'name' );
		}
	} else {
		$candidate_from_name = get_bloginfo( 'name' );
		if ( $provider_has_from_name ) {
			$steps[] = array(
				'status'  => 'detail',
				'message' => authority_mailer_smtp_s( 'provider_ignored_from_name' ),
				'details' => (string) ( isset( $provided_settings['mailjet_from_name'] ) ? $provided_settings['mailjet_from_name'] : '' ),
			);
		}
	}

	$final_from_email = (string) $candidate_from_email;
	$final_from_name  = (string) $candidate_from_name;

	// Recipient selection.
	if ( ! empty( $provided_settings['test_recipient'] ) ) {
		$test_to = sanitize_email( $provided_settings['test_recipient'] );
		$steps[] = array(
			'status'  => 'detail',
			'message' => authority_mailer_smtp_s( 'mailjet_using_test_recipient' ),
			'details' => (string) $test_to,
		);
	} else {
		$test_to = get_option( 'admin_email', '' );
		$steps[] = array(
			'status'  => 'detail',
			'message' => authority_mailer_smtp_s( 'mailjet_using_admin_email' ),
			'details' => (string) $test_to,
		);
	}

	if ( empty( $test_to ) || ! is_email( $test_to ) ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => authority_mailer_smtp_s( 'mailjet_no_recipient' ),
		);
		return $steps;
	}

	// Log final addresses (single non-empty message).
	if ( function_exists( 'authority_mailer_smtp_log_final_addresses' ) ) {
		authority_mailer_smtp_log_final_addresses( $steps, $test_to, $final_from_email, $final_from_name );
	} else {
		$steps[] = array(
			'status'  => 'detail',
			'message' => authority_mailer_smtp_s( 'mailjet_sender_details' ) ? authority_mailer_smtp_s( 'mailjet_sender_details' ) : authority_mailer_smtp_s( 'mailjet_final_settings' ),
			'details' => array(
				'to'         => $test_to,
				'from_email' => $final_from_email,
				'from_name'  => $final_from_name,
			),
		);
	}

	// Prepare endpoint and payload.
	$probe_path        = '/v3.1/send';
	$endpoint_host     = (string) ( $used_host ?: ( isset( $provided_settings['endpoint_host'] ) ? sanitize_text_field( $provided_settings['endpoint_host'] ) : 'api.mailjet.com' ) );
	$endpoint_url_host = 'https://' . $endpoint_host . $probe_path;
	$resolve_entry     = $endpoint_host . ':443:' . $resolved_ip;

	// Build professional test email using centralized template.
	$test_email_args = array(
		'provider'   => 'mailjet',
		'from_email' => $final_from_email,
		'from_name'  => $final_from_name,
		'to_email'   => $test_to,
	);

	// Use professional template if available, otherwise fallback.
	if ( function_exists( 'authority_mailer_smtp_get_test_email_subject' ) ) {
		$subject = authority_mailer_smtp_get_test_email_subject( 'Mailjet' );
	} else {
		$subject = isset( $provided_settings['test_subject'] ) ? sanitize_text_field( $provided_settings['test_subject'] ) : ( ( authority_mailer_smtp_s( 'mailjet_default_subject' ) ? authority_mailer_smtp_s( 'mailjet_default_subject' ) : 'Authority Mailer Mailjet Test' ) );
	}

	if ( function_exists( 'authority_mailer_smtp_get_test_email_html' ) ) {
		$body_html = authority_mailer_smtp_get_test_email_html( $test_email_args );
		$body_text = function_exists( 'authority_mailer_smtp_get_test_email_plain' ) ? authority_mailer_smtp_get_test_email_plain( $test_email_args ) : wp_strip_all_tags( $body_html );
	} else {
		$body_html = isset( $provided_settings['html_content'] ) ? (string) $provided_settings['html_content'] : ( ( authority_mailer_smtp_s( 'mailjet_default_body' ) ? authority_mailer_smtp_s( 'mailjet_default_body' ) : '<p>Authority Mailer Mailjet test</p>' ) );
		$body_text = isset( $provided_settings['plain_content'] ) ? (string) $provided_settings['plain_content'] : wp_strip_all_tags( $body_html );
	}

	$payload = authority_mailer_smtp_build_mailjet_payload( $final_from_email, $final_from_name, $test_to, $subject, $body_html, $body_text );

	// Basic auth using api_key:api_secret (Mailjet uses Basic auth with public:private).
	$basic_auth = base64_encode( (string) $api_key . ':' . (string) $api_secret );

	$args_post = array(
		'timeout' => 30,
		'headers' => array(
			'Authorization' => 'Basic ' . $basic_auth,
			'Accept'        => 'application/json',
			'Content-Type'  => 'application/json',
			'Host'          => $endpoint_host,
		),
		'subject' => (string) $subject,
		'body'    => wp_json_encode( $payload ),
		'curl'    => array( CURLOPT_RESOLVE => array( $resolve_entry ) ),
	);

	if ( ! empty( $provided_settings['allow_insecure'] ) ) {
		$args_post['sslverify'] = false;
		$steps[]                = array(
			'status'  => 'detail',
			'message' => authority_mailer_smtp_s( 'mailjet_allow_insecure' ),
		);
	}

	$steps[] = array(
		'status'  => 'info',
		'message' => sprintf( authority_mailer_smtp_s( 'mailjet_attempting_post' ), $endpoint_url_host ),
		'details' => array( authority_mailer_smtp_s( 'mailjet_payload_preview' ) => authority_mailer_smtp_list_keys( (array) $payload ) ),
	);

	// Use centralized test helper - sends through full wp_mail() pipeline with premium features.
	if ( function_exists( 'authority_mailer_smtp_send_test_via_wpmail' ) ) {
		authority_mailer_smtp_send_test_via_wpmail( 'mailjet', $test_to, $final_from_email, $final_from_name, $steps );
	} else {
		$steps[] = array(
			'status'  => 'error',
			'message' => 'Centralized test helper function not found. Please update common.php.',
		);
	}

	return $steps;
}


/**
 * Send email via Mailjet API
 *
 * @param array $email Email data from wp_mail
 * @return true|WP_Error
 */
function authority_mailer_smtp_send_mailjet( $email ) {
	$options = get_option( 'authority_mailer_smtp_options', array() );

	// Get API key and secret.
	$api_key    = ! empty( $options['mailjet_api_key'] )
		? $options['mailjet_api_key']
		: '';
	$secret_key = ! empty( $options['mailjet_secret_key'] )
		? $options['mailjet_secret_key']
		: '';

	if ( empty( $api_key ) || empty( $secret_key ) ) {
		return new WP_Error( 'missing_credentials', 'Mailjet API key and secret key are required' );
	}

	// Get from email/name.
	$from_email = ! empty( $options['mailjet_from_email'] )
		? $options['mailjet_from_email']
		: get_option( 'admin_email' );

	$from_name = ! empty( $options['mailjet_from_name'] )
		? $options['mailjet_from_name']
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
	$payload = authority_mailer_smtp_build_mailjet_payload(
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
		if ( ! isset( $payload['Messages'][0]['ReplyTo'] ) ) {
			$payload['Messages'][0]['ReplyTo'] = array(
				'Email' => $defaults['reply_to'],
			);
		}
	}

	if ( ! empty( $defaults['cc'] ) ) {
		if ( ! isset( $payload['Messages'][0]['Cc'] ) ) {
			$payload['Messages'][0]['Cc'] = array();
		}
		foreach ( $defaults['cc'] as $cc_email ) {
			$payload['Messages'][0]['Cc'][] = array( 'Email' => $cc_email );
		}
	}

	if ( ! empty( $defaults['bcc'] ) ) {
		if ( ! isset( $payload['Messages'][0]['Bcc'] ) ) {
			$payload['Messages'][0]['Bcc'] = array();
		}
		foreach ( $defaults['bcc'] as $bcc_email ) {
			$payload['Messages'][0]['Bcc'][] = array( 'Email' => $bcc_email );
		}
	}

	// Add priority headers.
	if ( ! empty( $defaults['priority'] ) ) {
		if ( ! isset( $payload['Messages'][0]['Headers'] ) ) {
			$payload['Messages'][0]['Headers'] = array();
		}
		$priority_headers = authority_mailer_smtp_get_priority_headers( $defaults['priority'] );
		foreach ( $priority_headers as $priority_header ) {
			$payload['Messages'][0]['Headers'][ $priority_header['name'] ] = $priority_header['value'];
		}
	}

	// Add custom headers.
	if ( ! empty( $defaults['custom'] ) ) {
		if ( ! isset( $payload['Messages'][0]['Headers'] ) ) {
			$payload['Messages'][0]['Headers'] = array();
		}
		foreach ( $defaults['custom'] as $custom_header ) {
			$payload['Messages'][0]['Headers'][ $custom_header['name'] ] = $custom_header['value'];
		}
	}

	// Add Return-Path header if provided and supported by the provider.
	if ( ! empty( $defaults['return_path'] ) && authority_mailer_smtp_supports_return_path_header( 'mailjet' ) ) {
		if ( ! isset( $payload['Messages'][0]['Headers'] ) ) {
			$payload['Messages'][0]['Headers'] = array();
		}
		$payload['Messages'][0]['Headers']['Return-Path'] = $defaults['return_path'];
	} elseif ( ! empty( $defaults['return_path'] ) ) {
		// Debug logging when Return-Path is skipped.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
			error_log(
				'[Authority Mailer Mailjet] Return-Path header skipped - not supported in custom headers for this provider'
			);
		}
	}

	// Debug logging for email defaults added to payload.
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
		error_log(
			'[Authority Mailer Mailjet] Email defaults added to payload | Reply-To: ' .
			( ! empty( $defaults['reply_to'] ) ? $defaults['reply_to'] : 'none' ) .
			' | CC count: ' . count( $defaults['cc'] ) .
			' | BCC count: ' . count( $defaults['bcc'] ) .
			' | Priority: ' . ( ! empty( $defaults['priority'] ) ? $defaults['priority'] : 'none' ) .
			' | Return-Path: ' . ( ! empty( $defaults['return_path'] ) ? $defaults['return_path'] : 'none' )
		);
	}

	// Add tracking ID to payload if present (for webhook correlation).
	if ( ! empty( $email['tracking_id'] ) ) {
		// Mailjet supports custom variables in Messages array.
		if ( ! isset( $payload['Messages'][0]['Variables'] ) ) {
			$payload['Messages'][0]['Variables'] = array();
		}
		$payload['Messages'][0]['Variables']['authority-mailer_tracking_id'] = $email['tracking_id'];

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
			error_log( '[Authority Mailer Mailjet] Added tracking ID to payload: ' . $email['tracking_id'] );
		}
	}

	// Log the email attempt.
	$log_id = 0;
	if ( function_exists( 'authority_mailer_smtp_log_email_with_spam_score' ) ) {
		$log_data = array(
			'provider'   => 'mailjet',
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

	// Make API call (using Basic Auth with API key:secret).
	$response = wp_remote_post(
		'https://api.mailjet.com/v3.1/send',
		array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( $api_key . ':' . $secret_key ),
				'Content-Type'  => 'application/json',
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
				'provider'      => 'mailjet',
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
				'provider'      => 'mailjet',
				'error_code'    => $code,
				'error_message' => $body,
				'email_data'    => $email,
			)
		);

		return new WP_Error( 'mailjet_error', 'Mailjet API error: ' . $body, array( 'code' => $code ) );
	}

	return true;
}
