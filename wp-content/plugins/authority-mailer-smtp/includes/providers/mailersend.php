<?php
/**
 * mailersend.php
 *
 * MailerSend provider tester for Authority Mailer onboarding.
 *
 * Uses centralized strings only (authority_mailer_s) for all user-facing and diagnostic text.
 *
 * Location: wp-content/plugins/authority-mailer-smtp/includes/providers/mailersend.php
 *
 * @package Authority_Mailer
 */

defined( 'ABSPATH' ) || exit;

require_once AUTHORITY_MAILER_PLUGIN_DIR . 'includes/providers/common.php';

global $AUTHORITY_MAILER_STRINGS;

if ( ! function_exists( 'authority_mailer_smtp_s' ) ) {
	/**
	 * Localized accessor that returns an empty string when key missing.
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
 * Build MailerSend POST payload.
 *
 * Note: subject/html/text default content is read from strings.php keys.
 *
 * @param string $from_email
 * @param string $from_name
 * @param string $to
 * @param string $subject
 * @param string $html
 * @param string $text
 * @return array
 */
function authority_mailer_smtp_build_mailersend_payload( $from_email, $from_name, $to, $subject = '', $html = '', $text = '', $use_defaults = true ) {
	$subject = (string) $subject;
	$html    = (string) $html;
	$text    = (string) $text;

	// Only apply defaults when requested (for test emails).
	if ( $use_defaults ) {
		if ( '' === trim( $subject ) ) {
			$subject = authority_mailer_smtp_s( 'mailersend_default_subject' );
		}
		if ( '' === trim( $html ) && '' === trim( $text ) ) {
			$html = authority_mailer_smtp_s( 'mailersend_default_body' );
		}
	}

	if ( '' === trim( $text ) && '' !== trim( $html ) ) {
		$text = trim( wp_strip_all_tags( $html ) );
	}

	$payload = array(
		'from'    => array(
			'email' => (string) $from_email,
			'name'  => (string) $from_name,
		),
		'to'      => array(
			array( 'email' => (string) $to ),
		),
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
 * Run MailerSend diagnostics and test transmission.
 *
 * All messages and details use centralized strings.
 *
 * @param array $settings
 * @return array
 */
function authority_mailer_smtp_test_mailersend( $settings = array() ) {
	$steps = array();

	$steps[] = array(
		'status'  => 'info',
		'message' => authority_mailer_smtp_s( 'mailersend_diag_start' ),
	);

	$provided_settings = is_array( $settings ) ? $settings : array();

	$steps[] = array(
		'status'  => 'detail',
		'message' => authority_mailer_smtp_s( 'mailersend_onboarding_keys' ),
		'details' => function_exists( 'authority_mailer_smtp_settings_summary' ) ? authority_mailer_smtp_settings_summary( $provided_settings ) : array(),
	);

	$mm_opts = get_option( 'authority_mailer_smtp_options', array() );
	$mm_opts = is_array( $mm_opts ) ? $mm_opts : array();

	$steps[] = array(
		'status'  => 'detail',
		'message' => authority_mailer_smtp_s( 'mailersend_mm_opts_keys' ),
		'details' => function_exists( 'authority_mailer_smtp_settings_summary' ) ? authority_mailer_smtp_settings_summary( $mm_opts ) : array(),
	);

	// merge allowed keys from stored options without overwriting provided settings.
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
		'mailersend_api_key',
		'mailersend_token',
		'api_key',
		'apikey',
		'key',
		'token',
		'mailersend_from_email',
		'mailersend_from_name',
	);

	foreach ( $mm_opts as $k => $v ) {
		if ( isset( $provided_settings[ $k ] ) ) {
			continue;
		}
		if ( in_array( $k, $allowed_keys, true ) || 0 === strpos( $k, 'mailersend_' ) ) {
			$provided_settings[ $k ] = $v;
		}
	}

	$steps[] = array(
		'status'  => 'detail',
		'message' => authority_mailer_smtp_s( 'mailersend_final_settings' ),
		'details' => function_exists( 'authority_mailer_smtp_settings_summary' ) ? authority_mailer_smtp_settings_summary( $provided_settings ) : array(),
	);

	// find api key.
	$key_names = array(
		'mailersend_api_key',
		'mailersend_token',
		'mailersend_key',
		'api_key',
		'apikey',
		'key',
		'token',
	);

	list( $found, $api_key, $found_path ) = function_exists( 'authority_mailer_smtp_find_api_key_in_array' )
		? authority_mailer_smtp_find_api_key_in_array( $provided_settings, $key_names )
		: array( false, '', '' );

	if ( ! $found || empty( $api_key ) ) {
		$steps[]    = array(
			'status'  => 'error',
			'message' => authority_mailer_smtp_s( 'mailersend_api_key_missing' ),
			'details' => array( 'checked_keys_count' => count( $key_names ) ),
		);
		$detail_msg = authority_mailer_smtp_s( 'mailersend_api_key_missing_detail' );
		if ( '' !== trim( $detail_msg ) ) {
			$steps[] = array(
				'status'  => 'detail',
				'message' => $detail_msg,
			);
		}
		return $steps;
	}

	$steps[] = array(
		'status'  => 'info',
		'message' => authority_mailer_smtp_s( 'mailersend_api_detected' ),
		'details' => array(
			'found_in'   => (string) $found_path,
			'masked_key' => function_exists( 'authority_mailer_smtp_mask_key' ) ? authority_mailer_smtp_mask_key( $api_key ) : '',
		),
	);

	// hosts to try.
	$hosts_to_try = array( 'api.mailersend.com' );
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
			'message' => sprintf( authority_mailer_smtp_s( 'mailersend_resolving' ), $host ),
		);

		list( $ip, $debug ) = function_exists( 'authority_mailer_smtp_resolve_host_with_doh' ) ? authority_mailer_smtp_resolve_host_with_doh( $host ) : array( '', array() );

		// Skip empty debug messages so we don't add "(no message)" steps.
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
				'message' => sprintf( authority_mailer_smtp_s( 'mailersend_resolved' ), $host, $ip ),
			);
			break;
		}

		$steps[] = array(
			'status'  => 'detail',
			'message' => sprintf( authority_mailer_smtp_s( 'mailersend_could_not_resolve' ), $host ),
		);
	}

	if ( empty( $resolved_ip ) && ! empty( $endpoint_ip_override ) ) {
		$resolved_ip = $endpoint_ip_override;
		$used_host   = isset( $provided_settings['endpoint_host'] ) ? sanitize_text_field( $provided_settings['endpoint_host'] ) : '';
		$steps[]     = array(
			'status'  => 'info',
			'message' => sprintf( authority_mailer_smtp_s( 'mailersend_using_ip_override' ), $resolved_ip ),
		);
	}

	if ( empty( $resolved_ip ) ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => authority_mailer_smtp_s( 'mailersend_dns_failed' ),
			'details' => authority_mailer_smtp_s( 'mailersend_dns_failed_detail' ),
		);
		return $steps;
	}

	// from selection.
	$force_from_email = authority_mailer_smtp_to_bool( ! empty( $provided_settings['mailersend_force_from_email'] ) ? $provided_settings['mailersend_force_from_email'] : ( ! empty( $provided_settings['force_from_email'] ) ? $provided_settings['force_from_email'] : false ) );
	$force_from_name  = authority_mailer_smtp_to_bool( ! empty( $provided_settings['mailersend_force_from_name'] ) ? $provided_settings['mailersend_force_from_name'] : ( ! empty( $provided_settings['force_from_name'] ) ? $provided_settings['force_from_name'] : false ) );

	$provider_email_keys = array( 'mailersend_from_email', 'mailersend_from', 'from_email', 'other_from_email' );
	$provider_name_keys  = array( 'mailersend_from_name', 'mailersend_fromname', 'from_name', 'other_from_name' );

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
				'message' => authority_mailer_smtp_s( 'mailersend_no_from_fallback' ),
				'details' => (string) $candidate_from_email,
			);
		}
	} else {
		$candidate_from_email = get_option( 'admin_email', '' );
		if ( $provider_has_from_email ) {
			$steps[] = array(
				'status'  => 'detail',
				'message' => authority_mailer_smtp_s( 'provider_ignored_from_email' ),
				'details' => (string) ( isset( $provided_settings['mailersend_from_email'] ) ? $provided_settings['mailersend_from_email'] : '' ),
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
				'details' => (string) ( isset( $provided_settings['mailersend_from_name'] ) ? $provided_settings['mailersend_from_name'] : '' ),
			);
		}
	}

	$final_from_email = (string) $candidate_from_email;
	$final_from_name  = (string) $candidate_from_name;

	// recipient selection.
	if ( ! empty( $provided_settings['test_recipient'] ) ) {
		$test_to = sanitize_email( $provided_settings['test_recipient'] );
		$steps[] = array(
			'status'  => 'detail',
			'message' => authority_mailer_smtp_s( 'mailersend_using_test_recipient' ),
			'details' => (string) $test_to,
		);
	} else {
		$test_to = get_option( 'admin_email', '' );
		$steps[] = array(
			'status'  => 'detail',
			'message' => authority_mailer_smtp_s( 'mailersend_using_admin_email' ),
			'details' => (string) $test_to,
		);
	}

	if ( empty( $test_to ) || ! is_email( $test_to ) ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => authority_mailer_smtp_s( 'mailersend_no_recipient' ),
		);
		return $steps;
	}

	// log final addresses (explicit, with a message so there is no empty-message step).
	$steps[] = array(
		'status'  => 'detail',
		'message' => authority_mailer_smtp_s( 'mailersend_sender_details' ) ? authority_mailer_smtp_s( 'mailersend_sender_details' ) : authority_mailer_smtp_s( 'mailersend_final_settings' ),
		'details' => array(
			'to'         => $test_to,
			'from_email' => $final_from_email,
			'from_name'  => $final_from_name,
		),
	);

	// endpoint and payload.
	$probe_path        = '/v1/email';
	$endpoint_host     = (string) $used_host;
	$endpoint_url_host = 'https://' . $endpoint_host . $probe_path;
	$resolve_entry     = $endpoint_host . ':443:' . $resolved_ip;

	// Build professional test email using centralized template.
	$test_email_args = array(
		'provider'   => 'mailersend',
		'from_email' => $final_from_email,
		'from_name'  => $final_from_name,
		'to_email'   => $test_to,
	);

	// Use professional template if available, otherwise fallback.
	if ( function_exists( 'authority_mailer_smtp_get_test_email_subject' ) ) {
		$subject = authority_mailer_smtp_get_test_email_subject( 'MailerSend' );
	} else {
		$subject = isset( $provided_settings['test_subject'] ) ? sanitize_text_field( $provided_settings['test_subject'] ) : ( ( authority_mailer_smtp_s( 'mailersend_default_subject' ) ? authority_mailer_smtp_s( 'mailersend_default_subject' ) : 'Authority Mailer MailerSend Test' ) );
	}

	if ( function_exists( 'authority_mailer_smtp_get_test_email_html' ) ) {
		$body_html = authority_mailer_smtp_get_test_email_html( $test_email_args );
		$body_text = function_exists( 'authority_mailer_smtp_get_test_email_plain' ) ? authority_mailer_smtp_get_test_email_plain( $test_email_args ) : wp_strip_all_tags( $body_html );
	} else {
		$body_html = isset( $provided_settings['html_content'] ) ? (string) $provided_settings['html_content'] : ( ( authority_mailer_smtp_s( 'mailersend_default_body' ) ? authority_mailer_smtp_s( 'mailersend_default_body' ) : '<p>Authority Mailer MailerSend test</p>' ) );
		$body_text = isset( $provided_settings['plain_content'] ) ? (string) $provided_settings['plain_content'] : wp_strip_all_tags( $body_html );
	}

	$payload = authority_mailer_smtp_build_mailersend_payload( $final_from_email, $final_from_name, $test_to, $subject, $body_html, $body_text );

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
			'message' => authority_mailer_smtp_s( 'mailersend_allow_insecure' ),
		);
	}

	$steps[] = array(
		'status'  => 'info',
		'message' => sprintf( authority_mailer_smtp_s( 'mailersend_attempting_post' ), $endpoint_url_host ),
		'details' => array( authority_mailer_smtp_s( 'mailersend_payload_preview' ) => authority_mailer_smtp_list_keys( (array) $payload ) ),
	);

	// Use centralized test helper - sends through full wp_mail() pipeline with premium features.
	if ( function_exists( 'authority_mailer_smtp_send_test_via_wpmail' ) ) {
		authority_mailer_smtp_send_test_via_wpmail( 'mailersend', $test_to, $final_from_email, $final_from_name, $steps );
	} else {
		$steps[] = array(
			'status'  => 'error',
			'message' => 'Centralized test helper function not found. Please update common.php.',
		);
	}

	return $steps;
}

/**
 * Send email via MailerSend API.
 *
 * Adapter function called by Authority Mailer Sender class to send emails through
 * the MailerSend transactional email service.
 *
 * @since 1.0.0
 *
 * @param array $email Email data from wp_mail containing to, subject, message, headers, and attachments.
 * @return true|WP_Error True on success, WP_Error on failure.
 */
function authority_mailer_smtp_send_mailersend( $email ) {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
		error_log( '[Authority Mailer MailerSend] Send function called | email keys: ' . wp_json_encode( array_keys( $email ) ) );
		if ( isset( $email['spam_score'] ) ) {
						// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
						error_log( '[Authority Mailer MailerSend] Spam score in email: ' . $email['spam_score'] );
		}
	}

	$options = get_option( 'authority_mailer_smtp_options', array() );

	// Get API key.
	$api_key = ! empty( $options['mailersend_api_key'] )
		? $options['mailersend_api_key']
		: '';

	if ( empty( $api_key ) ) {
		return new WP_Error( 'missing_api_key', 'MailerSend API key not configured' );
	}

	// Get from email/name.
	$from_email = ! empty( $options['mailersend_from_email'] )
		? $options['mailersend_from_email']
		: get_option( 'admin_email' );

	$from_name = ! empty( $options['mailersend_from_name'] )
		? $options['mailersend_from_name']
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
	$payload = authority_mailer_smtp_build_mailersend_payload(
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
		if ( ! isset( $payload['reply_to'] ) ) {
			$payload['reply_to'] = array(
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
			'[Authority Mailer MailerSend] Email defaults added to payload | Reply-To: ' .
			( ! empty( $defaults['reply_to'] ) ? $defaults['reply_to'] : 'none' ) .
			' | CC count: ' . count( $defaults['cc'] ) .
			' | BCC count: ' . count( $defaults['bcc'] ) .
			' | Priority: ' . ( ! empty( $defaults['priority'] ) ? $defaults['priority'] : 'none' ) .
			' | Return-Path: ' . ( ! empty( $defaults['return_path'] ) ? $defaults['return_path'] : 'none' )
		);
	}

	// Add tracking ID to payload if present (for webhook correlation).
	if ( ! empty( $email['tracking_id'] ) ) {
		// MailerSend supports tags with am_tracking_ prefix.
		if ( ! isset( $payload['tags'] ) ) {
			$payload['tags'] = array();
		}
		$payload['tags'][] = 'am_tracking_' . $email['tracking_id'];

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
			error_log( '[Authority Mailer MailerSend] Added tracking ID to payload: ' . $email['tracking_id'] );
		}
	}

	// Log the email attempt.
	$log_id = 0;
	if ( function_exists( 'authority_mailer_smtp_log_email_with_spam_score' ) ) {
		$log_data = array(
			'provider'   => 'mailersend',
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
		'https://api.mailersend.com/v1/email',
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
				'provider'      => 'mailersend',
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
				'provider'      => 'mailersend',
				'error_code'    => $code,
				'error_message' => $body,
				'email_data'    => $email,
			)
		);

		return new WP_Error( 'mailersend_error', 'MailerSend API error: ' . $body, array( 'code' => $code ) );
	}

	return true;
}
