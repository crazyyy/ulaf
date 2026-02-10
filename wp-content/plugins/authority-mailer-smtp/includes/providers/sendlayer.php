<?php
/**
 * sendlayer.php
 *
 * SendLayer provider tester for Authority Mailer onboarding.
 *
 * Uses centralized helpers from includes/providers/common.php.
 *
 * Location: wp-content/plugins/authority-mailer-smtp/includes/providers/sendlayer.php
 */

defined( 'ABSPATH' ) || exit;

require_once AUTHORITY_MAILER_PLUGIN_DIR . 'includes/providers/common.php';

global $AUTHORITY_MAILER_STRINGS;

if ( ! function_exists( 'authority_mailer_smtp_get_string' ) ) {
	/**
	 * Helper to get localized string with fallback.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key      The string key to retrieve.
	 * @param string $fallback The fallback value if key not found.
	 * @return string The localized string or fallback value.
	 */
	function authority_mailer_smtp_get_string( $key, $fallback = '' ) {
		$txt = '';
		if ( function_exists( 'authority_mailer_smtp_s' ) ) {
			$txt = authority_mailer_smtp_s( $key );
		}
		if ( ! is_string( $txt ) || '' === trim( $txt ) ) {
			return $fallback;
		}
		return $txt;
	}
}

/**
 * Build SendLayer POST payload.
 *
 * This builder ensures Subject and PlainContent are present (non-empty).
 *
 * @param string $from_email From email address.
 * @param string $from_name  From name.
 * @param string $to         Recipient email.
 * @param string $subject    Email subject.
 * @param string $html       HTML body.
 * @param string $text       Plain text body.
 * @return array
 */
function authority_mailer_smtp_build_sendlayer_payload( $from_email, $from_name, $to, $subject = '', $html = '', $text = '', $use_defaults = true ) {
	// Ensure subject isn't empty.
	$subject = (string) $subject;
	if ( $use_defaults && '' === trim( $subject ) ) {
		$subject = authority_mailer_smtp_get_string( 'sendlayer_default_subject', 'Authority Mailer test' );
	}

	// Normalize bodies.
	$html = (string) $html;
	$text = (string) $text;

	// If no plain text provided, derive from HTML.
	if ( '' === trim( $text ) && '' !== trim( $html ) ) {
		$text = trim( wp_strip_all_tags( $html ) );
	}

	// Apply defaults only when requested (for test emails).
	if ( $use_defaults && '' === trim( $html ) && '' === trim( $text ) ) {
		$default_body = authority_mailer_smtp_get_string( 'sendlayer_default_body', '<p>Authority Mailer test</p>' );
		$html         = $default_body;
		$text         = trim( wp_strip_all_tags( $html ) );
	}

	// Build payload using the SendLayer legacy/console shape (capitalized keys).
	$payload = array(
		'From'        => array(
			'Email' => (string) $from_email,
			'Name'  => (string) $from_name,
		),
		'To'          => array(
			array(
				'Email' => (string) $to,
				'Name'  => '',
				'Type'  => 'to',
			),
		),
		'Subject'     => (string) $subject,
		'ContentType' => ( '' !== trim( $html ) ) ? 'html' : 'plain',
	);

	if ( '' !== trim( $html ) ) {
		$payload['HTMLContent'] = (string) $html;
	}
	if ( '' !== trim( $text ) ) {
		$payload['PlainContent'] = (string) $text;
	}

	return $payload;
}

/**
 * Run SendLayer diagnostics and optional test transmission.
 *
 * Returns an ordered array of steps describing actions and outcomes.
 *
 * @param array $settings Onboarding settings (may be empty).
 * @return array
 */
function authority_mailer_smtp_test_sendlayer( $settings = array() ) {
	$steps = array();

	$steps[] = array(
		'status'  => 'info',
		'message' => authority_mailer_smtp_get_string( 'sendlayer_diag_start', 'Starting SendLayer diagnostics' ),
	);

	$provided_settings = is_array( $settings ) ? $settings : array();

	// Inspect onboarding-provided keys (summary only).
	$steps[] = array(
		'status'  => 'detail',
		'message' => authority_mailer_smtp_get_string( 'sendlayer_onboarding_keys', 'Onboarding-provided settings summary' ),
		'details' => authority_mailer_smtp_settings_summary( $provided_settings ),
	);

	// Merge stored options without overwriting onboarding-provided keys.
	$mm_opts = get_option( 'authority_mailer_smtp_options', array() );
	$mm_opts = is_array( $mm_opts ) ? $mm_opts : array();

	$steps[] = array(
		'status'  => 'detail',
		'message' => authority_mailer_smtp_get_string( 'sendlayer_mm_opts_keys', 'Stored authority_mailer_options inspected (summary)' ),
		'details' => authority_mailer_smtp_settings_summary( $mm_opts ),
	);

	foreach ( $mm_opts as $k => $v ) {
		if ( ! isset( $provided_settings[ $k ] ) ) {
			$provided_settings[ $k ] = $v;
		}
	}

	$steps[] = array(
		'status'  => 'detail',
		'message' => authority_mailer_smtp_get_string( 'sendlayer_final_settings', 'Final settings used after merge (summary)' ),
		'details' => authority_mailer_smtp_settings_summary( $provided_settings ),
	);

	// Candidate API key names (prioritize sendlayer-specific keys).
	$key_names = array(
		'sendlayer_api_key',
		'sendlayer_key',
		'sendlayer_token',
		'sendlayer_api_token',
		'api_key',
		'apikey',
		'key',
		'token',
	);

	list( $found, $api_key, $found_path ) = authority_mailer_smtp_find_api_key_in_array( $provided_settings, $key_names );

	if ( ! $found || empty( $api_key ) ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => authority_mailer_smtp_get_string( 'sendlayer_api_key_missing', 'API key / token not found' ),
			'details' => array( 'checked_keys_count' => count( $key_names ) ),
		);

		$steps[] = array(
			'status'  => 'detail',
			'message' => authority_mailer_smtp_get_string( 'sendlayer_api_key_missing_detail', '' ),
		);

		return $steps;
	}

	$steps[] = array(
		'status'  => 'info',
		'message' => authority_mailer_smtp_get_string( 'sendlayer_api_detected', 'API key detected' ),
		'details' => array(
			'found_in'   => (string) $found_path,
			'masked_key' => authority_mailer_smtp_mask_key( $api_key ),
		),
	);

	// Hosts to try (prefer known hosts).
	$hosts_to_try = array( 'console.sendlayer.com', 'api.sendlayer.com' );

	if ( ! empty( $provided_settings['endpoint_host'] ) ) {
		$hosts_to_try = array_merge( array( sanitize_text_field( $provided_settings['endpoint_host'] ) ), $hosts_to_try );
	}

	$endpoint_ip_override = ! empty( $provided_settings['endpoint_ip'] ) ? trim( (string) $provided_settings['endpoint_ip'] ) : '';

	$resolved_ip = '';
	$used_host   = '';

	foreach ( $hosts_to_try as $host ) {
		$host = (string) $host;

		$steps[] = array(
			'status'  => 'info',
			'message' => sprintf( authority_mailer_smtp_get_string( 'sendlayer_resolving', 'Resolving DNS for %s' ), $host ),
		);

		// Use the common resolver (may return system/cached/DoH debug).
		list( $ip, $debug ) = authority_mailer_smtp_resolve_host_with_doh( $host );

		foreach ( (array) $debug as $d ) {
			$steps[] = array(
				'status'  => 'detail',
				'message' => (string) $d,
			);
		}

		if ( ! empty( $ip ) ) {
			$resolved_ip = $ip;
			$used_host   = $host;

			$steps[] = array(
				'status'  => 'info',
				'message' => sprintf( authority_mailer_smtp_get_string( 'sendlayer_resolved', 'Resolved %1$s -> %2$s' ), $host, $ip ),
			);

			break;
		}

		$steps[] = array(
			'status'  => 'detail',
			'message' => sprintf( authority_mailer_smtp_get_string( 'sendlayer_could_not_resolve', 'Could not resolve %s' ), $host ),
		);
	}

	if ( empty( $resolved_ip ) && ! empty( $endpoint_ip_override ) ) {
		$resolved_ip = $endpoint_ip_override;
		$used_host   = isset( $provided_settings['endpoint_host'] ) ? sanitize_text_field( $provided_settings['endpoint_host'] ) : '';

		$steps[] = array(
			'status'  => 'info',
			'message' => sprintf( authority_mailer_smtp_get_string( 'sendlayer_using_ip_override', 'Using endpoint_ip override: %s' ), $resolved_ip ),
		);
	}

	if ( empty( $resolved_ip ) ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => authority_mailer_smtp_get_string( 'sendlayer_dns_failed', 'DNS resolution failed for SendLayer hosts' ),
			'details' => authority_mailer_smtp_get_string( 'sendlayer_dns_failed_detail', '' ),
		);

		return $steps;
	}

	/* Determine from email/name honoring force toggles. Sanitize inputs. */
	$force_from_email = authority_mailer_smtp_to_bool( ! empty( $provided_settings['sendlayer_force_from_email'] ) ? $provided_settings['sendlayer_force_from_email'] : false );
	$force_from_name  = authority_mailer_smtp_to_bool( ! empty( $provided_settings['sendlayer_force_from_name'] ) ? $provided_settings['sendlayer_force_from_name'] : false );

	// Determine whether provider defines from values (various keys).
	$provider_email_keys = array( 'sendlayer_from_email', 'sendlayer_from', 'elastic_from_email', 'elasticmail_from_email', 'from_email', 'other_from_email' );
	$provider_name_keys  = array( 'sendlayer_from_name', 'sendlayer_fromname', 'elastic_from_name', 'elasticmail_from_name', 'from_name', 'other_from_name' );

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

	// Candidate selection:.
	// - If force toggle is ON -> honor provider-specific sendlayer_* values (preferred).
	// - If force toggle is OFF -> always use site defaults (admin_email for email, blog name for name).
	if ( $force_from_email ) {
		$candidate_from_email = '';
		// prefer provider-specific keys when force is enabled.
		foreach ( $provider_email_keys as $fk ) {
			if ( ! empty( $provided_settings[ $fk ] ) ) {
				$candidate_from_email = sanitize_email( $provided_settings[ $fk ] );
				break;
			}
		}
		if ( empty( $candidate_from_email ) ) {
			// fallback to admin_email.
			$candidate_from_email = get_option( 'admin_email', '' );
			$steps[]              = array(
				'status'  => 'detail',
				'message' => authority_mailer_smtp_get_string( 'sendlayer_no_from_fallback', 'No from address in settings; falling back to admin_email' ),
				'details' => (string) $candidate_from_email,
			);
		}
	} else {
		// Force is disabled => always use site admin_email (do NOT use provider-saved values).
		$candidate_from_email = get_option( 'admin_email', '' );
		if ( $provider_has_from_email ) {
			$steps[] = array(
				'status'  => 'detail',
				'message' => authority_mailer_smtp_get_string( 'provider_ignored_from_email', 'Provider defines a From Email but "Force From Email" is disabled — using site admin_email instead.' ),
				'details' => (string) ( isset( $provided_settings['sendlayer_from_email'] ) ? $provided_settings['sendlayer_from_email'] : '' ),
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
				'message' => authority_mailer_smtp_get_string( 'provider_ignored_from_name', 'Provider defines a From Name but "Force From Name" is disabled — using site name instead.' ),
				'details' => (string) ( isset( $provided_settings['sendlayer_from_name'] ) ? $provided_settings['sendlayer_from_name'] : '' ),
			);
		}
	}

	$final_from_email = (string) $candidate_from_email;
	$final_from_name  = (string) $candidate_from_name;

	/* Recipient selection: test_recipient (onboarding) -> admin_email. */
	$test_to = '';

	if ( ! empty( $provided_settings['test_recipient'] ) ) {
		$test_to = sanitize_email( $provided_settings['test_recipient'] );

		$steps[] = array(
			'status'  => 'detail',
			'message' => authority_mailer_smtp_get_string( 'sendlayer_using_test_recipient', 'Using test_recipient from settings' ),
			'details' => (string) $test_to,
		);
	} else {
		$test_to = get_option( 'admin_email', '' );

		$steps[] = array(
			'status'  => 'detail',
			'message' => authority_mailer_smtp_get_string( 'sendlayer_using_admin_email', 'Using admin_email as test recipient' ),
			'details' => (string) $test_to,
		);
	}

	if ( empty( $test_to ) || ! is_email( $test_to ) ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => authority_mailer_smtp_get_string( 'sendlayer_no_recipient', 'No recipient available to send test email to (admin email not set).' ),
		);
		return $steps;
	}

	/* Log final addresses (helper will append details in a safe manner). */
	authority_mailer_smtp_log_final_addresses( $steps, $test_to, $final_from_email, $final_from_name );

	/* Prepare endpoint and resolve entry. */
	$probe_path        = '/api/v1/email';
	$endpoint_host     = (string) $used_host;
	$endpoint_url_host = 'https://' . $endpoint_host . $probe_path;
	$resolve_entry     = $endpoint_host . ':443:' . $resolved_ip;

	// Build professional test email using centralized template.
	$test_email_args = array(
		'provider'   => 'sendlayer',
		'from_email' => $final_from_email,
		'from_name'  => $final_from_name,
		'to_email'   => $test_to,
	);

	// Use professional template if available, otherwise fallback.
	if ( function_exists( 'authority_mailer_smtp_get_test_email_subject' ) ) {
		$subject = authority_mailer_smtp_get_test_email_subject( 'SendLayer' );
	} else {
		$subject = isset( $provided_settings['test_subject'] ) ? sanitize_text_field( $provided_settings['test_subject'] ) : authority_mailer_smtp_get_string( 'sendlayer_default_subject', 'Authority Mailer SendLayer Test' );
	}

	if ( function_exists( 'authority_mailer_smtp_get_test_email_html' ) ) {
		$body_html = authority_mailer_smtp_get_test_email_html( $test_email_args );
		$body_text = function_exists( 'authority_mailer_smtp_get_test_email_plain' ) ? authority_mailer_smtp_get_test_email_plain( $test_email_args ) : wp_strip_all_tags( $body_html );
	} else {
		$body_html = isset( $provided_settings['html_content'] ) ? (string) $provided_settings['html_content'] : authority_mailer_smtp_get_string( 'sendlayer_default_body', '<p>Authority Mailer SendLayer test</p>' );
		$body_text = isset( $provided_settings['plain_content'] ) ? (string) $provided_settings['plain_content'] : wp_strip_all_tags( $body_html );
	}

	// Ensure non-empty body_text.
	if ( '' === trim( (string) $body_text ) ) {
		$body_text = 'Authority Mailer test';
	}

	// Build payload (always include both HTMLContent and PlainContent to satisfy API).
	$payload = authority_mailer_smtp_build_sendlayer_payload( $final_from_email, $final_from_name, $test_to, $subject, $body_html, $body_text );

	$args_post = array(
		'timeout' => 30,
		'headers' => array(
			'Authorization' => 'Bearer ' . (string) $api_key,
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
			'message' => authority_mailer_smtp_get_string( 'sendlayer_allow_insecure', 'allow_insecure is enabled — SSL verification disabled (debug only)' ),
		);
	}

	$steps[] = array(
		'status'  => 'info',
		'message' => sprintf( authority_mailer_smtp_get_string( 'sendlayer_attempting_post', 'Attempting POST %s' ), $endpoint_url_host ),
		'details' => array( authority_mailer_smtp_get_string( 'sendlayer_payload_preview', 'Payload keys sent (preview)' ) => authority_mailer_smtp_list_keys( (array) $payload ) ),
	);

	// Use centralized test helper - sends through full wp_mail() pipeline with premium features.
	if ( function_exists( 'authority_mailer_smtp_send_test_via_wpmail' ) ) {
		authority_mailer_smtp_send_test_via_wpmail( 'sendlayer', $test_to, $final_from_email, $final_from_name, $steps );
	} else {
		$steps[] = array(
			'status'  => 'error',
			'message' => 'Centralized test helper function not found. Please update common.php.',
		);
	}

	return $steps;
}

/**
 * Send email via SendLayer API.
 *
 * Adapter function called by Authority Mailer Sender class to send emails through
 * the SendLayer transactional email service.
 *
 * @since 1.0.0
 *
 * @param array $email Email data from wp_mail containing to, subject, message, headers, and attachments.
 * @return true|WP_Error True on success, WP_Error on failure.
 */
function authority_mailer_smtp_send_sendlayer( $email ) {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
		error_log( '[Authority Mailer SendLayer] Send function called | email keys: ' . wp_json_encode( array_keys( $email ) ) );
		if ( isset( $email['spam_score'] ) ) {
						// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
						error_log( '[Authority Mailer SendLayer] Spam score in email: ' . $email['spam_score'] );
		}
	}

	$options = get_option( 'authority_mailer_smtp_options', array() );

	// Get API key.
	$api_key = ! empty( $options['sendlayer_api_key'] )
		? $options['sendlayer_api_key']
		: '';

	if ( empty( $api_key ) ) {
		return new WP_Error( 'missing_api_key', 'SendLayer API key not configured' );
	}

	// Get from email/name.
	$from_email = ! empty( $options['sendlayer_from_email'] )
		? $options['sendlayer_from_email']
		: get_option( 'admin_email' );

	$from_name = ! empty( $options['sendlayer_from_name'] )
		? $options['sendlayer_from_name']
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
		$text = $message;
		// Check if it contains URLs - if so, convert to simple HTML with clickable links.
		if ( preg_match( '/https?:\/\/[^\s<>"\']+/i', $message ) ) {
			$html = nl2br( esc_html( $message ) );
			$html = preg_replace(
				'/(https?:\/\/[^\s<>"\']+)/i',
				'<a href="$1">$1</a>',
				$html
			);
		}
	}

	// Build payload without defaults.
	$payload = authority_mailer_smtp_build_sendlayer_payload(
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
		if ( ! isset( $payload['ReplyTo'] ) ) {
			$payload['ReplyTo'] = array(
				'Email' => $defaults['reply_to'],
			);
		}
	}

	if ( ! empty( $defaults['cc'] ) ) {
		if ( ! isset( $payload['Cc'] ) ) {
			$payload['Cc'] = array();
		}
		foreach ( $defaults['cc'] as $cc_email ) {
			$payload['Cc'][] = array( 'Email' => $cc_email );
		}
	}

	if ( ! empty( $defaults['bcc'] ) ) {
		if ( ! isset( $payload['Bcc'] ) ) {
			$payload['Bcc'] = array();
		}
		foreach ( $defaults['bcc'] as $bcc_email ) {
			$payload['Bcc'][] = array( 'Email' => $bcc_email );
		}
	}

	// Add priority headers.
	if ( ! empty( $defaults['priority'] ) ) {
		if ( ! isset( $payload['CustomHeaders'] ) ) {
			$payload['CustomHeaders'] = array();
		}
		$priority_headers = authority_mailer_smtp_get_priority_headers( $defaults['priority'] );
		foreach ( $priority_headers as $priority_header ) {
			$payload['CustomHeaders'][] = array(
				'Name'  => $priority_header['name'],
				'Value' => $priority_header['value'],
			);
		}
	}

	// Add custom headers (for tracking, etc.).
	if ( ! empty( $defaults['custom'] ) ) {
		if ( ! isset( $payload['CustomHeaders'] ) ) {
			$payload['CustomHeaders'] = array();
		}
		foreach ( $defaults['custom'] as $custom_header ) {
			$payload['CustomHeaders'][] = array(
				'Name'  => $custom_header['name'],
				'Value' => $custom_header['value'],
			);
		}
	}

	// Add Return-Path header if provided.
	if ( ! empty( $defaults['return_path'] ) ) {
		if ( ! isset( $payload['CustomHeaders'] ) ) {
			$payload['CustomHeaders'] = array();
		}
		$payload['CustomHeaders'][] = array(
			'Name'  => 'Return-Path',
			'Value' => $defaults['return_path'],
		);
	}

	// Debug logging for email defaults added to payload.
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
		error_log(
			'[Authority Mailer SendLayer] Email defaults added to payload | Reply-To: ' .
			( ! empty( $defaults['reply_to'] ) ? $defaults['reply_to'] : 'none' ) .
			' | CC count: ' . count( $defaults['cc'] ) .
			' | BCC count: ' . count( $defaults['bcc'] ) .
			' | Priority: ' . ( ! empty( $defaults['priority'] ) ? $defaults['priority'] : 'none' ) .
			' | Return-Path: ' . ( ! empty( $defaults['return_path'] ) ? $defaults['return_path'] : 'none' )
		);
	}

	// Add tracking ID to payload if present (for webhook correlation).
	if ( ! empty( $email['tracking_id'] ) ) {
		// Sendlayer supports custom_variables for tracking.
		if ( ! isset( $payload['custom_variables'] ) ) {
			$payload['custom_variables'] = array();
		}
		$payload['custom_variables']['authority-mailer_tracking_id'] = $email['tracking_id'];

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
			error_log( '[Authority Mailer Sendlayer] Added tracking ID to payload: ' . $email['tracking_id'] );
		}
	}

	// Log the email attempt.
	$log_id = 0;
	if ( function_exists( 'authority_mailer_smtp_log_email_with_spam_score' ) ) {
		$log_data = array(
			'provider'   => 'sendlayer',
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
		'https://console.sendlayer.com/api/v1/email',
		array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $api_key,
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
				'provider'      => 'sendlayer',
				'error_code'    => $response->get_error_code(),
				'error_message' => $response->get_error_message(),
				'email_data'    => $email,
			)
		);

		return $response;
	}

	$code = wp_remote_retrieve_response_code( $response );
	$body = wp_remote_retrieve_body( $response );

	// Parse JSON response to check for errors even with 2xx status codes.
	$response_data = json_decode( $body, true );
	$has_errors    = false;
	$error_message = '';

	// Validate JSON decode succeeded before checking for errors.
	if ( JSON_ERROR_NONE === json_last_error() && is_array( $response_data ) ) {
		// Check if response contains an Errors array (Sendlayer reports errors this way).
		if ( ! empty( $response_data['Errors'] ) && is_array( $response_data['Errors'] ) ) {
			$has_errors = true;
			// Extract error messages from the Errors array.
			$error_messages = array();
			foreach ( $response_data['Errors'] as $error ) {
				if ( is_array( $error ) && ! empty( $error['Message'] ) ) {
					$error_messages[] = $error['Message'];
				}
			}
			$error_message = implode( '; ', $error_messages );
			if ( empty( $error_message ) ) {
				$error_message = 'SendLayer reported errors in response';
			}
		}
	}

	// Determine status: error if non-2xx status OR if Errors array is present.
	$is_success = ( $code >= 200 && $code < 300 ) && ! $has_errors;

	// Update log with response.
	if ( function_exists( 'authority_mailer_smtp_email_logger_update' ) && $log_id ) {
		authority_mailer_smtp_email_logger_update(
			$log_id,
			array(
				'status'        => $is_success ? 'accepted' : 'error',
				'response_code' => $code,
				'response_body' => is_string( $body ) ? substr( $body, 0, 2000 ) : '',
			)
		);
	}

	// Return error if not successful.
	if ( ! $is_success ) {
		// Prefer specific error message from parsed response if available.
		$error_detail = $has_errors && ! empty( $error_message ) ? $error_message : $body;

		// Trigger failover notification.
		do_action(
			'authority_mailer_provider_failed',
			array(
				'provider'      => 'sendlayer',
				'error_code'    => $code,
				'error_message' => $error_detail,
				'email_data'    => $email,
			)
		);

		return new WP_Error( 'sendlayer_error', 'SendLayer API error: ' . $error_detail, array( 'code' => $code ) );
	}

	return true;
}
