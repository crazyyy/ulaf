<?php
/**
 * mailgun.php
 *
 * Mailgun provider tester for Authority Mailer onboarding.
 *
 * - Merges onboarding-provided settings with authority_mailer_options (including nested provider groups).
 * - Resolves Mailgun hosts, probes API and performs POST transmission to Mailgun's /v3/{domain}/messages endpoint.
 * - Uses centralized strings via authority_mailer_smtp_s() only (no hard-coded UI text).
 * - Skips empty resolver debug entries, avoids json_decode() on empty bodies,
 *   and uses authority_mailer_smtp_http_post_and_log() when available.
 *
 * Location: wp-content/plugins/authority-mailer-smtp/includes/providers/mailgun.php
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
 * Build Mailgun form payload (multipart/form-data or application/x-www-form-urlencoded).
 *
 * @param string $from_email
 * @param string $from_name
 * @param string $to
 * @param string $subject
 * @param string $html
 * @param string $text
 * @return array
 */
function authority_mailer_smtp_build_mailgun_payload( $from_email, $from_name, $to, $subject = '', $html = '', $text = '', $use_defaults = true ) {
	$subject = (string) $subject;
	$html    = (string) $html;
	$text    = (string) $text;

	if ( $use_defaults ) {
		if ( '' === trim( $subject ) ) {
			$subject = authority_mailer_smtp_s( 'mailgun_default_subject' );
		}
		if ( '' === trim( $html ) && '' === trim( $text ) ) {
			$html = authority_mailer_smtp_s( 'mailgun_default_body' );
		}
	}

	if ( '' === trim( $text ) && '' !== trim( $html ) ) {
		$text = trim( wp_strip_all_tags( $html ) );
	}

	$from = (string) $from_email;
	if ( '' !== trim( (string) $from_name ) ) {
		$from = sprintf( '%s <%s>', $from_name, $from_email );
	}

	// Mailgun expects form fields: from, to, subject, html/text.
	$payload = array(
		'from'    => $from,
		'to'      => (string) $to,
		'subject' => (string) $subject,
	);

	if ( '' !== trim( $html ) ) {
		$payload['html'] = (string) $html;
	}
	if ( '' !== trim( $text ) ) {
		$payload['text'] = (string) $text;
	}

	return $payload;
}

/**
 * Run Mailgun diagnostics and test transmission.
 *
 * @param array $settings
 * @return array
 */
function authority_mailer_smtp_test_mailgun( $settings = array() ) {
	$steps = array();

	$steps[] = array(
		'status'  => 'info',
		'message' => authority_mailer_smtp_s( 'mailgun_diag_start' ),
	);

	$provided_settings = is_array( $settings ) ? $settings : array();

	$steps[] = array(
		'status'  => 'detail',
		'message' => authority_mailer_smtp_s( 'mailgun_onboarding_keys' ),
		'details' => function_exists( 'authority_mailer_smtp_settings_summary' ) ? authority_mailer_smtp_settings_summary( $provided_settings ) : array(),
	);

	$mm_opts = get_option( 'authority_mailer_smtp_options', array() );
	$mm_opts = is_array( $mm_opts ) ? $mm_opts : array();

	$steps[] = array(
		'status'  => 'detail',
		'message' => authority_mailer_smtp_s( 'mailgun_mm_opts_keys' ),
		'details' => function_exists( 'authority_mailer_smtp_settings_summary' ) ? authority_mailer_smtp_settings_summary( $mm_opts ) : array(),
	);

	// Merge provider-scoped settings (authority_mailer_options['mailgun'], etc.)
	$provider_groups = array( 'mailgun', 'mailgun_settings', 'mailgun_options' );
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

	// Whitelist top-level allowed keys from mm_opts.
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
		'mailgun_api_key',
		'mailgun_domain',
		'api_key',
		'key',
		'token',
		'mailgun_from_email',
		'mailgun_from_name',
	);

	foreach ( $mm_opts as $k => $v ) {
		if ( isset( $provided_settings[ $k ] ) ) {
			continue;
		}
		if ( in_array( $k, $allowed_keys, true ) || 0 === strpos( $k, 'mailgun_' ) ) {
			$provided_settings[ $k ] = $v;
		}
	}

	$steps[] = array(
		'status'  => 'detail',
		'message' => authority_mailer_smtp_s( 'mailgun_final_settings' ),
		'details' => function_exists( 'authority_mailer_smtp_settings_summary' ) ? authority_mailer_smtp_settings_summary( $provided_settings ) : array(),
	);

	// API key detection (robust).
	$key_candidates    = array( 'mailgun_api_key', 'mailgun_key', 'api_key', 'key', 'token' );
	$domain_candidates = array( 'mailgun_domain', 'mg_domain', 'domain' );

	$api_key        = '';
	$found_key_path = '';

	if ( function_exists( 'authority_mailer_smtp_find_api_key_in_array' ) ) {
		list( $found, $val, $path ) = authority_mailer_smtp_find_api_key_in_array( $provided_settings, $key_candidates );
		if ( $found ) {
			$api_key        = trim( (string) $val );
			$found_key_path = $path;
		}
	}

	// fallback: nested mm_opts['mailgun'].
	if ( '' === $api_key && isset( $mm_opts['mailgun'] ) && is_array( $mm_opts['mailgun'] ) ) {
		foreach ( $key_candidates as $kn ) {
			if ( ! empty( $mm_opts['mailgun'][ $kn ] ) ) {
				$api_key        = trim( (string) $mm_opts['mailgun'][ $kn ] );
				$found_key_path = 'authority_mailer_smtp_options.mailgun.' . $kn;
				break;
			}
		}
	}

	// fallback: top-level mm_opts.
	if ( '' === $api_key ) {
		foreach ( $key_candidates as $kn ) {
			if ( ! empty( $mm_opts[ $kn ] ) ) {
				$api_key        = trim( (string) $mm_opts[ $kn ] );
				$found_key_path = $kn;
				break;
			}
		}
	}

	if ( '' === $api_key ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => authority_mailer_smtp_s( 'mailgun_api_key_missing' ),
			'details' => authority_mailer_smtp_s( 'mailgun_api_key_missing_detail' ),
		);
		return $steps;
	}

	$steps[] = array(
		'status'  => 'info',
		'message' => authority_mailer_smtp_s( 'mailgun_api_detected' ),
		'details' => array(
			'found_in'   => (string) $found_key_path,
			'masked_key' => function_exists( 'authority_mailer_smtp_mask_key' ) ? authority_mailer_smtp_mask_key( $api_key ) : '',
		),
	);

	// Domain detection.
	$domain            = '';
	$found_domain_path = '';

	foreach ( $domain_candidates as $dn ) {
		if ( ! empty( $provided_settings[ $dn ] ) ) {
			$domain            = trim( (string) $provided_settings[ $dn ] );
			$found_domain_path = $dn;
			break;
		}
	}
	if ( '' === $domain && isset( $mm_opts['mailgun'] ) && is_array( $mm_opts['mailgun'] ) ) {
		foreach ( $domain_candidates as $dn ) {
			if ( ! empty( $mm_opts['mailgun'][ $dn ] ) ) {
				$domain            = trim( (string) $mm_opts['mailgun'][ $dn ] );
				$found_domain_path = 'authority_mailer_smtp_options.mailgun.' . $dn;
				break;
			}
		}
	}
	if ( '' === $domain ) {
		foreach ( $domain_candidates as $dn ) {
			if ( ! empty( $mm_opts[ $dn ] ) ) {
				$domain            = trim( (string) $mm_opts[ $dn ] );
				$found_domain_path = $dn;
				break;
			}
		}
	}

	if ( '' === $domain ) {
		$domain_msg = authority_mailer_smtp_s( 'mailgun_domain_missing' );
		if ( empty( $domain_msg ) ) {
			$domain_msg = authority_mailer_smtp_s( 'mailgun_api_key_missing' );
		}
		$steps[] = array(
			'status'  => 'error',
			'message' => $domain_msg,
			'details' => ( authority_mailer_smtp_s( 'mailgun_domain_missing_detail' ) ? authority_mailer_smtp_s( 'mailgun_domain_missing_detail' ) : '' ),
		);
		return $steps;
	}

	$steps[] = array(
		'status'  => 'info',
		'message' => sprintf( authority_mailer_smtp_s( 'mailgun_resolving' ), "api.mailgun.net (domain: {$domain})" ),
	);

	// Hosts to try (Mailgun provides region-specific hosts, but api.mailgun.net is common)
	$hosts_to_try = array( 'api.mailgun.net' );
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
			'message' => sprintf( authority_mailer_smtp_s( 'mailgun_resolving' ), $host ),
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
				'message' => sprintf( authority_mailer_smtp_s( 'mailgun_resolved' ), $host, $ip ),
			);
			break;
		}

		$steps[] = array(
			'status'  => 'detail',
			'message' => sprintf( authority_mailer_smtp_s( 'mailgun_could_not_resolve' ), $host ),
		);
	}

	if ( empty( $resolved_ip ) && ! empty( $endpoint_ip_override ) ) {
		$resolved_ip = $endpoint_ip_override;
		$used_host   = isset( $provided_settings['endpoint_host'] ) ? sanitize_text_field( $provided_settings['endpoint_host'] ) : '';
		$steps[]     = array(
			'status'  => 'info',
			'message' => sprintf( authority_mailer_smtp_s( 'mailgun_using_ip_override' ), $resolved_ip ),
		);
	}

	if ( empty( $resolved_ip ) ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => authority_mailer_smtp_s( 'mailgun_dns_failed' ),
			'details' => authority_mailer_smtp_s( 'mailgun_dns_failed_detail' ),
		);
		return $steps;
	}

	// From selection & force toggles (only use provider values when force enabled).
	$force_from_email = authority_mailer_smtp_to_bool(
		! empty( $provided_settings['mailgun_force_from_email'] ) ? $provided_settings['mailgun_force_from_email'] :
		( ! empty( $provided_settings['force_from_email'] ) ? $provided_settings['force_from_email'] : false )
	);
	$force_from_name  = authority_mailer_smtp_to_bool(
		! empty( $provided_settings['mailgun_force_from_name'] ) ? $provided_settings['mailgun_force_from_name'] :
		( ! empty( $provided_settings['force_from_name'] ) ? $provided_settings['force_from_name'] : false )
	);

	$provider_email_keys = array( 'mailgun_from_email', 'mailgun_from', 'from_email', 'other_from_email' );
	$provider_name_keys  = array( 'mailgun_from_name', 'mailgun_fromname', 'from_name', 'other_from_name' );

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
				'message' => authority_mailer_smtp_s( 'mailgun_no_from_fallback' ),
				'details' => (string) $candidate_from_email,
			);
		}
	} else {
		$candidate_from_email = get_option( 'admin_email', '' );
		if ( $provider_has_from_email ) {
			$steps[] = array(
				'status'  => 'detail',
				'message' => authority_mailer_smtp_s( 'provider_ignored_from_email' ),
				'details' => (string) ( isset( $provided_settings['mailgun_from_email'] ) ? $provided_settings['mailgun_from_email'] : '' ),
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
				'details' => (string) ( isset( $provided_settings['mailgun_from_name'] ) ? $provided_settings['mailgun_from_name'] : '' ),
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
			'message' => authority_mailer_smtp_s( 'mailgun_using_test_recipient' ),
			'details' => (string) $test_to,
		);
	} else {
		$test_to = get_option( 'admin_email', '' );
		$steps[] = array(
			'status'  => 'detail',
			'message' => authority_mailer_smtp_s( 'mailgun_using_admin_email' ),
			'details' => (string) $test_to,
		);
	}

	if ( empty( $test_to ) || ! is_email( $test_to ) ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => authority_mailer_smtp_s( 'mailgun_no_recipient' ),
		);
		return $steps;
	}

	// Log final addresses.
	if ( function_exists( 'authority_mailer_smtp_log_final_addresses' ) ) {
		authority_mailer_smtp_log_final_addresses( $steps, $test_to, $final_from_email, $final_from_name );
	} else {
		$steps[] = array(
			'status'  => 'detail',
			'message' => authority_mailer_smtp_s( 'mailgun_sender_details' ) ? authority_mailer_smtp_s( 'mailgun_sender_details' ) : authority_mailer_smtp_s( 'mailgun_final_settings' ),
			'details' => array(
				'to'         => $test_to,
				'from_email' => $final_from_email,
				'from_name'  => $final_from_name,
			),
		);
	}

	// Prepare endpoint and payload.
	$probe_path        = '/v3/' . rawurlencode( $domain ) . '/messages';
	$endpoint_host     = ! empty( $used_host ) ? $used_host : ( isset( $provided_settings['endpoint_host'] ) ? sanitize_text_field( $provided_settings['endpoint_host'] ) : 'api.mailgun.net' );
	$endpoint_host     = (string) $endpoint_host;
	$endpoint_url_host = 'https://' . $endpoint_host . $probe_path;
	$resolve_entry     = $endpoint_host . ':443:' . $resolved_ip;

	// Build professional test email using centralized template.
	$test_email_args = array(
		'provider'   => 'mailgun',
		'from_email' => $final_from_email,
		'from_name'  => $final_from_name,
		'to_email'   => $test_to,
	);

	// Use professional template if available, otherwise fallback.
	if ( function_exists( 'authority_mailer_smtp_get_test_email_subject' ) ) {
		$subject = authority_mailer_smtp_get_test_email_subject( 'Mailgun' );
	} else {
		$subject = isset( $provided_settings['test_subject'] ) ? sanitize_text_field( $provided_settings['test_subject'] ) : ( ( authority_mailer_smtp_s( 'mailgun_default_subject' ) ? authority_mailer_smtp_s( 'mailgun_default_subject' ) : 'Authority Mailer Mailgun Test' ) );
	}

	if ( function_exists( 'authority_mailer_smtp_get_test_email_html' ) ) {
		$body_html = authority_mailer_smtp_get_test_email_html( $test_email_args );
		$body_text = function_exists( 'authority_mailer_smtp_get_test_email_plain' ) ? authority_mailer_smtp_get_test_email_plain( $test_email_args ) : wp_strip_all_tags( $body_html );
	} else {
		$body_html = isset( $provided_settings['html_content'] ) ? (string) $provided_settings['html_content'] : ( ( authority_mailer_smtp_s( 'mailgun_default_body' ) ? authority_mailer_smtp_s( 'mailgun_default_body' ) : '<p>Authority Mailer Mailgun test</p>' ) );
		$body_text = isset( $provided_settings['plain_content'] ) ? (string) $provided_settings['plain_content'] : wp_strip_all_tags( $body_html );
	}

	$payload = authority_mailer_smtp_build_mailgun_payload( $final_from_email, $final_from_name, $test_to, $subject, $body_html, $body_text );

	// Mailgun expects form-encoded body and Basic auth 'api:APIKEY'.
	$args_post = array(
		'timeout' => 30,
		'headers' => array(
			'Authorization' => 'Basic ' . base64_encode( 'api:' . (string) $api_key ),
			'Accept'        => 'application/json',
			'Host'          => $endpoint_host,
		),
		'subject' => (string) $subject,
		'body'    => $payload, // WP will encode as form-data when body is an array
		'curl'    => array( CURLOPT_RESOLVE => array( $resolve_entry ) ),
	);

	if ( ! empty( $provided_settings['allow_insecure'] ) ) {
		$args_post['sslverify'] = false;
		$steps[]                = array(
			'status'  => 'detail',
			'message' => authority_mailer_smtp_s( 'mailgun_allow_insecure' ),
		);
	}

	$steps[] = array(
		'status'  => 'info',
		'message' => ( authority_mailer_smtp_s( 'mailgun_attempting_test' ) ? authority_mailer_smtp_s( 'mailgun_attempting_test' ) : 'Sending test email through wp_mail() pipeline...' ),
	);

	// Use centralized test helper - sends through full wp_mail() pipeline with premium features.
	if ( function_exists( 'authority_mailer_smtp_send_test_via_wpmail' ) ) {
		authority_mailer_smtp_send_test_via_wpmail( 'mailgun', $test_to, $final_from_email, $final_from_name, $steps );
	} else {
		$steps[] = array(
			'status'  => 'error',
			'message' => 'Centralized test helper function not found. Please update common.php.',
		);
	}

	return $steps;
}

/**
 * Send email via Mailgun API
 *
 * @param array $email Email data from wp_mail
 * @return true|WP_Error
 */
function authority_mailer_smtp_send_mailgun( $email ) {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
		error_log( '[Authority Mailer Mailgun] Send function called | email keys: ' . wp_json_encode( array_keys( $email ) ) );
		if ( isset( $email['spam_score'] ) ) {
						// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
						error_log( '[Authority Mailer Mailgun] Spam score in email: ' . $email['spam_score'] );
		}
	}

	$options = get_option( 'authority_mailer_smtp_options', array() );

	// Get API key and domain.
	$api_key = ! empty( $options['mailgun_api_key'] )
		? $options['mailgun_api_key']
		: '';
	$domain  = ! empty( $options['mailgun_domain'] )
		? $options['mailgun_domain']
		: '';

	if ( empty( $api_key ) || empty( $domain ) ) {
		return new WP_Error( 'missing_credentials', 'Mailgun API key and domain are required' );
	}

	// Get region (default: us).
	$region = ! empty( $options['mailgun_region'] )
		? $options['mailgun_region']
		: 'us';

	// Build API endpoint based on region.
	$api_base = ( 'eu' === $region )
		? 'https://api.eu.mailgun.net/v3/'
		: 'https://api.mailgun.net/v3/';

	// Get from email/name.
	$from_email = ! empty( $options['mailgun_from_email'] )
		? $options['mailgun_from_email']
		: get_option( 'admin_email' );

	$from_name = ! empty( $options['mailgun_from_name'] )
		? $options['mailgun_from_name']
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
	$form_data = authority_mailer_smtp_build_mailgun_payload(
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

	// Add email default headers to payload (Mailgun uses form fields)
	if ( ! empty( $defaults['reply_to'] ) ) {
		if ( ! isset( $form_data['h:Reply-To'] ) ) {
			$form_data['h:Reply-To'] = $defaults['reply_to'];
		}
	}

	if ( ! empty( $defaults['cc'] ) ) {
		if ( ! isset( $form_data['cc'] ) ) {
			$form_data['cc'] = array();
		}
		foreach ( $defaults['cc'] as $cc_email ) {
			$form_data['cc'][] = $cc_email;
		}
	}

	if ( ! empty( $defaults['bcc'] ) ) {
		if ( ! isset( $form_data['bcc'] ) ) {
			$form_data['bcc'] = array();
		}
		foreach ( $defaults['bcc'] as $bcc_email ) {
			$form_data['bcc'][] = $bcc_email;
		}
	}

	// Add priority headers (Mailgun uses h:Header-Name format).
	if ( ! empty( $defaults['priority'] ) ) {
		$priority_headers = authority_mailer_smtp_get_priority_headers( $defaults['priority'] );
		foreach ( $priority_headers as $priority_header ) {
			$form_data[ 'h:' . $priority_header['name'] ] = $priority_header['value'];
		}
	}

	// Add custom headers (Mailgun uses h:Header-Name format)
	if ( ! empty( $defaults['custom'] ) ) {
		foreach ( $defaults['custom'] as $custom_header ) {
			$form_data[ 'h:' . $custom_header['name'] ] = $custom_header['value'];
		}
	}

	// Add Return-Path header if provided (Mailgun uses h:Return-Path format).
	if ( ! empty( $defaults['return_path'] ) ) {
		$form_data['h:Return-Path'] = $defaults['return_path'];
	}

	// Debug logging for email defaults added to payload.
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
		error_log(
			'[Authority Mailer Mailgun] Email defaults added to payload | Reply-To: ' .
			( ! empty( $defaults['reply_to'] ) ? $defaults['reply_to'] : 'none' ) .
			' | CC count: ' . count( $defaults['cc'] ) .
			' | BCC count: ' . count( $defaults['bcc'] ) .
			' | Priority: ' . ( ! empty( $defaults['priority'] ) ? $defaults['priority'] : 'none' ) .
			' | Return-Path: ' . ( ! empty( $defaults['return_path'] ) ? $defaults['return_path'] : 'none' )
		);
	}

	// Add tracking ID to payload if present (for webhook correlation).
	if ( ! empty( $email['tracking_id'] ) ) {
		// Mailgun supports user variables (v:variable_name format).
		$form_data['v:authority-mailer_tracking_id'] = $email['tracking_id'];

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
			error_log( '[Authority Mailer Mailgun] Added tracking ID to payload: ' . $email['tracking_id'] );
		}
	}

	// Log the email attempt.
	$log_id = 0;
	if ( function_exists( 'authority_mailer_smtp_log_email_with_spam_score' ) ) {
		$log_data = array(
			'provider'   => 'mailgun',
			'to_email'   => $to_email,
			'from_email' => $from_email,
			'from_name'  => $from_name,
			'subject'    => $subject,
			'headers'    => isset( $email['headers'] ) ? ( is_array( $email['headers'] ) ? wp_json_encode( $email['headers'] ) : $email['headers'] ) : '',
			'body'       => $message,
			'payload'    => wp_json_encode( $form_data ),
			'status'     => 'attempt',
		);

		// Use centralized helper that handles spam score extraction and debug logging automatically.
		$log_id = authority_mailer_smtp_log_email_with_spam_score( $email, $log_data );
	}

	// Make API call (Mailgun uses application/x-www-form-urlencoded).
	$response = wp_remote_post(
		$api_base . $domain . '/messages',
		array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( 'api:' . $api_key ),
			),
			'body'    => $form_data, // Not JSON encoded for Mailgun
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
				'provider'      => 'mailgun',
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
				'provider'      => 'mailgun',
				'error_code'    => $code,
				'error_message' => $body,
				'email_data'    => $email,
			)
		);

		return new WP_Error( 'mailgun_error', 'Mailgun API error: ' . $body, array( 'code' => $code ) );
	}

	return true;
}
