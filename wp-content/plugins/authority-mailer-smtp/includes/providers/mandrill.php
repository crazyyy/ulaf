<?php
/**
 * mandrill.php
 *
 * Mandrill provider tester for Authority Mailer onboarding.
 *
 * Uses shared helpers from includes/providers/common.php.
 *
 * Location: wp-content/plugins/authority-mailer-smtp/includes/providers/mandrill.php
 *
 * @package Authority_Mailer
 */

defined( 'ABSPATH' ) || exit;

require_once AUTHORITY_MAILER_PLUGIN_DIR . 'includes/providers/common.php';

global $AUTHORITY_MAILER_STRINGS;

/**
 * Build Mandrill POST payload for messages/send.
 *
 * Constructs a Mandrill API compatible payload for sending emails through
 * the messages/send endpoint.
 *
 * @since 1.0.0
 *
 * @param string $api_key    The Mandrill API key.
 * @param string $from_email The sender email address.
 * @param string $from_name  The sender display name.
 * @param string $to         The recipient email address.
 * @param string $subject    Email subject line.
 * @param string $html       Optional. HTML body content. Default empty string.
 * @param string $text       Optional. Plain text body content. Default empty string.
 * @return array The Mandrill API payload array.
 */
function authority_mailer_smtp_build_mandrill_payload( $api_key, $from_email, $from_name, $to, $subject, $html = '', $text = '' ) {
	$message = array(
		'from_email' => (string) $from_email,
		'from_name'  => (string) $from_name,
		'to'         => array(
			array(
				'email' => (string) $to,
				'type'  => 'to',
			),
		),
		'subject'    => (string) $subject,
	);

	if ( '' !== (string) $html ) {
		$message['html'] = (string) $html;
	}

	if ( '' !== (string) $text ) {
		$message['text'] = (string) $text;
	}

	$payload = array(
		'key'     => (string) $api_key,
		'message' => $message,
		'async'   => false,
	);

	return $payload;
}

/**
 * Run Mandrill diagnostics and test transmission.
 *
 * Tests Mandrill API connection, validates API key, performs DNS resolution,
 * and sends a test email. Uses authority_mailer_smtp_http_post_and_log() for the actual POST
 * so attempts and responses are recorded in the authority_mailer_email_log table automatically.
 *
 * @since 1.0.0
 *
 * @param array $settings Optional. Array of Mandrill settings including api_key, from_email, etc. Default empty array.
 * @return array Array of diagnostic steps with status, message, and details for each step.
 */
function authority_mailer_smtp_test_mandrill( $settings = array() ) {
	$steps = array();

	$steps[] = array(
		'status'  => 'info',
		'message' => authority_mailer_smtp_s( 'mandrill_diag_start' ),
	);

	$provided_settings = is_array( $settings ) ? $settings : array();

	$steps[] = array(
		'status'  => 'detail',
		'message' => authority_mailer_smtp_s( 'mandrill_onboarding_keys' ),
		'details' => authority_mailer_smtp_settings_summary( $provided_settings ),
	);

	$mm_opts = get_option( 'authority_mailer_smtp_options', array() );
	$mm_opts = is_array( $mm_opts ) ? $mm_opts : array();

	$steps[] = array(
		'status'  => 'detail',
		'message' => authority_mailer_smtp_s( 'mandrill_mm_opts_keys' ),
		'details' => authority_mailer_smtp_settings_summary( $mm_opts ),
	);

	// Merge saved global options into provided settings (preserve provided_settings precedence).
	foreach ( $mm_opts as $k => $v ) {
		if ( ! isset( $provided_settings[ $k ] ) ) {
			$provided_settings[ $k ] = $v;
		}
	}

	$steps[] = array(
		'status'  => 'detail',
		'message' => authority_mailer_smtp_s( 'mandrill_final_settings' ),
		'details' => authority_mailer_smtp_settings_summary( $provided_settings ),
	);

	$key_names = array(
		'mandrill_api_key',
		'mandrill_key',
		'mandrill_token',
		'api_key',
		'apikey',
		'key',
		'token',
	);

	list( $found, $api_key, $found_path ) = authority_mailer_smtp_find_api_key_in_array( $provided_settings, $key_names );

	if ( ! $found || empty( $api_key ) ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => authority_mailer_smtp_s( 'mandrill_api_key_missing' ),
			'details' => array( 'checked_keys_count' => count( $key_names ) ),
		);
		$steps[] = array(
			'status'  => 'detail',
			'message' => authority_mailer_smtp_s( 'mandrill_api_key_missing_detail' ),
		);
		return $steps;
	}

	$steps[] = array(
		'status'  => 'info',
		'message' => authority_mailer_smtp_s( 'mandrill_api_detected' ),
		'details' => array(
			'found_in'   => (string) $found_path,
			'masked_key' => authority_mailer_smtp_mask_key( $api_key ),
		),
	);

	$hosts_to_try = array( 'mandrillapp.com' );
	if ( ! empty( $provided_settings['endpoint_host'] ) ) {
		$hosts_to_try = array_merge( array( sanitize_text_field( $provided_settings['endpoint_host'] ) ), $hosts_to_try );
	}
	$endpoint_ip_override = ! empty( $provided_settings['endpoint_ip'] ) ? trim( (string) $provided_settings['endpoint_ip'] ) : '';

	$resolved_ip = '';
	$used_host   = '';

	foreach ( $hosts_to_try as $host ) {
		$steps[]            = array(
			'status'  => 'info',
			'message' => sprintf( authority_mailer_smtp_s( 'mandrill_resolving' ), $host ),
		);
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
			$steps[]     = array(
				'status'  => 'info',
				'message' => sprintf( authority_mailer_smtp_s( 'mandrill_resolved' ), $host, $ip ),
			);
			break;
		}
		$steps[] = array(
			'status'  => 'detail',
			'message' => sprintf( authority_mailer_smtp_s( 'mandrill_could_not_resolve' ), $host ),
		);
	}

	if ( empty( $resolved_ip ) && ! empty( $endpoint_ip_override ) ) {
		$resolved_ip = $endpoint_ip_override;
		$used_host   = isset( $provided_settings['endpoint_host'] ) ? sanitize_text_field( $provided_settings['endpoint_host'] ) : 'forced-ip';

		$steps[] = array(
			'status'  => 'info',
			'message' => sprintf( authority_mailer_smtp_s( 'mandrill_using_ip_override' ), $resolved_ip ),
		);
	}

	if ( empty( $resolved_ip ) ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => authority_mailer_smtp_s( 'mandrill_dns_failed' ),
			'details' => authority_mailer_smtp_s( 'mandrill_dns_failed_detail' ),
		);
		return $steps;
	}

	$force_from_email = authority_mailer_smtp_to_bool( ! empty( $provided_settings['mandrill_force_from_email'] ) ? $provided_settings['mandrill_force_from_email'] : false );
	$force_from_name  = authority_mailer_smtp_to_bool( ! empty( $provided_settings['mandrill_force_from_name'] ) ? $provided_settings['mandrill_force_from_name'] : false );

	$candidate_from_email = '';
	$from_keys            = array( 'mandrill_from_email', 'from_email', 'other_from_email' );

	foreach ( $from_keys as $fk ) {
		if ( ! empty( $provided_settings[ $fk ] ) ) {
			$candidate_from_email = sanitize_email( $provided_settings[ $fk ] );
			break;
		}
	}

	if ( empty( $candidate_from_email ) ) {
		$candidate_from_email = get_option( 'admin_email', '' );

		$steps[] = array(
			'status'  => 'detail',
			'message' => authority_mailer_smtp_s( 'mandrill_no_from_fallback' ),
			'details' => (string) $candidate_from_email,
		);
	}

	$candidate_from_name = '';
	$from_name_keys      = array( 'mandrill_from_name', 'from_name', 'other_from_name' );

	foreach ( $from_name_keys as $fnk ) {
		if ( ! empty( $provided_settings[ $fnk ] ) ) {
			$candidate_from_name = sanitize_text_field( $provided_settings[ $fnk ] );
			break;
		}
	}

	$final_from_email = (string) $candidate_from_email;
	$final_from_name  = (string) $candidate_from_name;

	if ( $force_from_email ) {
		if ( ! empty( $provided_settings['mandrill_from_email'] ) ) {
			$final_from_email = sanitize_email( $provided_settings['mandrill_from_email'] );

			$steps[] = array(
				'status'  => 'detail',
				'message' => authority_mailer_smtp_s( 'mandrill_force_from_email_used' ),
				'details' => (string) $final_from_email,
			);
		} else {
			$steps[] = array(
				'status'  => 'detail',
				'message' => authority_mailer_smtp_s( 'mandrill_force_from_email_empty' ),
			);
		}
	}

	if ( $force_from_name ) {
		if ( ! empty( $provided_settings['mandrill_from_name'] ) ) {
			$final_from_name = sanitize_text_field( $provided_settings['mandrill_from_name'] );

			$steps[] = array(
				'status'  => 'detail',
				'message' => authority_mailer_smtp_s( 'mandrill_force_from_name_used' ),
				'details' => (string) $final_from_name,
			);
		} else {
			$steps[] = array(
				'status'  => 'detail',
				'message' => authority_mailer_smtp_s( 'mandrill_force_from_name_empty' ),
			);
		}
	}

	if ( ! empty( $provided_settings['test_recipient'] ) ) {
		$test_to = sanitize_email( $provided_settings['test_recipient'] );

		$steps[] = array(
			'status'  => 'detail',
			'message' => authority_mailer_smtp_s( 'mandrill_using_test_recipient' ),
			'details' => (string) $test_to,
		);
	} else {
		$test_to = get_option( 'admin_email', '' );

		$steps[] = array(
			'status'  => 'detail',
			'message' => authority_mailer_smtp_s( 'mandrill_using_admin_email' ),
			'details' => (string) $test_to,
		);
	}

	if ( empty( $test_to ) || ! is_email( $test_to ) ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => authority_mailer_smtp_s( 'mandrill_no_recipient' ),
		);
		return $steps;
	}

	authority_mailer_smtp_log_final_addresses( $steps, $test_to, $final_from_email, $final_from_name );

	$probe_path        = '/api/1.0/messages/send';
	$endpoint_host     = (string) $used_host;
	$endpoint_url_host = 'https://' . $endpoint_host . $probe_path;
	$resolve_entry     = $endpoint_host . ':443:' . $resolved_ip;

	// Build professional test email using centralized template.
	$test_email_args = array(
		'provider'   => 'mandrill',
		'from_email' => $final_from_email,
		'from_name'  => $final_from_name,
		'to_email'   => $test_to,
	);

	// Use professional template if available, otherwise fallback.
	if ( function_exists( 'authority_mailer_smtp_get_test_email_subject' ) ) {
		$subject = authority_mailer_smtp_get_test_email_subject( 'Mandrill' );
	} else {
		$subject = isset( $provided_settings['test_subject'] ) ? sanitize_text_field( $provided_settings['test_subject'] ) : ( ( authority_mailer_smtp_s( 'mandrill_default_subject' ) ? authority_mailer_smtp_s( 'mandrill_default_subject' ) : 'Authority Mailer Mandrill Test' ) );
	}

	if ( function_exists( 'authority_mailer_smtp_get_test_email_html' ) ) {
		$body_html = authority_mailer_smtp_get_test_email_html( $test_email_args );
		$body_text = function_exists( 'authority_mailer_smtp_get_test_email_plain' ) ? authority_mailer_smtp_get_test_email_plain( $test_email_args ) : wp_strip_all_tags( $body_html );
	} else {
		$body_html = isset( $provided_settings['html_content'] ) ? (string) $provided_settings['html_content'] : ( ( authority_mailer_smtp_s( 'mandrill_default_body' ) ? authority_mailer_smtp_s( 'mandrill_default_body' ) : '<p>Authority Mailer Mandrill test</p>' ) );
		$body_text = isset( $provided_settings['plain_content'] ) ? (string) $provided_settings['plain_content'] : wp_strip_all_tags( $body_html );
	}

	$payload = authority_mailer_smtp_build_mandrill_payload(
		$api_key,
		$final_from_email,
		$final_from_name,
		$test_to,
		$subject,
		$body_html,
		$body_text
	);

	$args_post = array(
		'timeout' => 30,
		'headers' => array(
			'Accept'       => 'application/json',
			'Content-Type' => 'application/json',
			'Host'         => $endpoint_host,
		),
		'body'    => wp_json_encode( $payload ),
		'curl'    => array( CURLOPT_RESOLVE => array( $resolve_entry ) ),
	);

	if ( ! empty( $provided_settings['allow_insecure'] ) ) {
		$args_post['sslverify'] = false;
		$steps[]                = array(
			'status'  => 'detail',
			'message' => authority_mailer_smtp_s( 'mandrill_allow_insecure' ),
		);
	}

	$steps[] = array(
		'status'  => 'info',
		'message' => sprintf( authority_mailer_smtp_s( 'mandrill_attempting_post' ), $endpoint_url_host ),
		'details' => array( authority_mailer_smtp_s( 'mandrill_payload_preview' ) => authority_mailer_smtp_list_keys( (array) $payload ) ),
	);

	// Use centralized test helper - sends through full wp_mail() pipeline with premium features.
	if ( function_exists( 'authority_mailer_smtp_send_test_via_wpmail' ) ) {
		authority_mailer_smtp_send_test_via_wpmail( 'mandrill', $test_to, $final_from_email, $final_from_name, $steps );
	} else {
		$steps[] = array(
			'status'  => 'error',
			'message' => 'Centralized test helper function not found. Please update common.php.',
		);
	}

	return $steps;
}

/**
 * Send email via Mandrill API.
 *
 * Adapter function called by Authority Mailer Sender class to send emails through
 * the Mandrill transactional email service.
 *
 * @since 1.0.0
 *
 * @param array $email Email data from wp_mail containing to, subject, message, headers, and attachments.
 * @return true|WP_Error True on success, WP_Error on failure.
 */
function authority_mailer_smtp_send_mandrill( $email ) {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
		error_log( '[Authority Mailer Mandrill] Send function called | email keys: ' . wp_json_encode( array_keys( $email ) ) );
		if ( isset( $email['spam_score'] ) ) {
						// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
						error_log( '[Authority Mailer Mandrill] Spam score in email: ' . $email['spam_score'] );
		}
	}

	$options = get_option( 'authority_mailer_smtp_options', array() );

	// Get API key.
	$api_key = ! empty( $options['mandrill_api_key'] )
		? $options['mandrill_api_key']
		: '';

	if ( empty( $api_key ) ) {
		return new WP_Error( 'missing_api_key', 'Mandrill API key not configured' );
	}

	// Get from email/name.
	$from_email = ! empty( $options['mandrill_from_email'] )
		? $options['mandrill_from_email']
		: get_option( 'admin_email' );

	$from_name = ! empty( $options['mandrill_from_name'] )
		? $options['mandrill_from_name']
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

	// Build payload - Mandrill requires API key in payload.
	$payload = authority_mailer_smtp_build_mandrill_payload(
		$api_key,
		$from_email,
		$from_name,
		$to_email,
		$subject,
		$html,
		$text
	);

	// Use helper function to extract email defaults with fallback to header parsing.
	$defaults = authority_mailer_smtp_get_email_defaults( $email );

	// Add email default headers to payload.
	if ( ! empty( $defaults['reply_to'] ) ) {
		if ( ! isset( $payload['message']['headers'] ) ) {
			$payload['message']['headers'] = array();
		}
		$payload['message']['headers']['Reply-To'] = $defaults['reply_to'];
	}

	if ( ! empty( $defaults['cc'] ) ) {
		if ( ! isset( $payload['message']['to'] ) ) {
			$payload['message']['to'] = array();
		}
		foreach ( $defaults['cc'] as $cc_email ) {
			$payload['message']['to'][] = array(
				'email' => $cc_email,
				'type'  => 'cc',
			);
		}
	}

	if ( ! empty( $defaults['bcc'] ) ) {
		if ( ! isset( $payload['message']['to'] ) ) {
			$payload['message']['to'] = array();
		}
		foreach ( $defaults['bcc'] as $bcc_email ) {
			$payload['message']['to'][] = array(
				'email' => $bcc_email,
				'type'  => 'bcc',
			);
		}
	}

	// Add priority headers.
	if ( ! empty( $defaults['priority'] ) ) {
		if ( ! isset( $payload['message']['headers'] ) ) {
			$payload['message']['headers'] = array();
		}
		$priority_headers = authority_mailer_smtp_get_priority_headers( $defaults['priority'] );
		foreach ( $priority_headers as $priority_header ) {
			$payload['message']['headers'][ $priority_header['name'] ] = $priority_header['value'];
		}
	}

	// Add custom headers.
	if ( ! empty( $defaults['custom'] ) ) {
		if ( ! isset( $payload['message']['headers'] ) ) {
			$payload['message']['headers'] = array();
		}
		foreach ( $defaults['custom'] as $custom_header ) {
			$payload['message']['headers'][ $custom_header['name'] ] = $custom_header['value'];
		}
	}

	// Add Return-Path header if provided.
	if ( ! empty( $defaults['return_path'] ) ) {
		if ( ! isset( $payload['message']['headers'] ) ) {
			$payload['message']['headers'] = array();
		}
		$payload['message']['headers']['Return-Path'] = $defaults['return_path'];
	}

	// Debug logging for email defaults added to payload.
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
		error_log(
			'[Authority Mailer Mandrill] Email defaults added to payload | Reply-To: ' .
			( ! empty( $defaults['reply_to'] ) ? $defaults['reply_to'] : 'none' ) .
			' | CC count: ' . count( $defaults['cc'] ) .
			' | BCC count: ' . count( $defaults['bcc'] ) .
			' | Priority: ' . ( ! empty( $defaults['priority'] ) ? $defaults['priority'] : 'none' ) .
			' | Return-Path: ' . ( ! empty( $defaults['return_path'] ) ? $defaults['return_path'] : 'none' )
		);
	}

	// Add tracking ID to payload if present (for webhook correlation).
	if ( ! empty( $email['tracking_id'] ) ) {
		// Mandrill supports metadata for tracking.
		if ( ! isset( $payload['message']['metadata'] ) ) {
			$payload['message']['metadata'] = array();
		}
		$payload['message']['metadata']['authority-mailer_tracking_id'] = $email['tracking_id'];

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
			error_log( '[Authority Mailer Mandrill] Added tracking ID to payload: ' . $email['tracking_id'] );
		}
	}

	// Log the email attempt.
	$log_id = 0;
	if ( function_exists( 'authority_mailer_smtp_log_email_with_spam_score' ) ) {
		$log_data = array(
			'provider'   => 'mandrill',
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
		'https://mandrillapp.com/api/1.0/messages/send.json',
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
				'provider'      => 'mandrill',
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
				'provider'      => 'mandrill',
				'error_code'    => $code,
				'error_message' => $body,
				'email_data'    => $email,
			)
		);

		return new WP_Error( 'mandrill_error', 'Mandrill API error: ' . $body, array( 'code' => $code ) );
	}

	return true;
}
