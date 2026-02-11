<?php
/**
 * smtpcom.php
 *
 * SMTP.com provider adapter for Authority Mailer testers.
 *
 * Exports:
 *   - authority_mailer_test_smtpcom( array $settings ): array $steps
 *
 * Behavior:
 * - Validates presence of API key and channel (sender).
 * - Resolves api.smtp.com via system DNS then DoH fallback (Cloudflare -> Google).
 * - Performs a lightweight GET to https://api.smtp.com/v4/messages to check auth (Bearer).
 * - Optionally attempts a test POST to https://api.smtp.com/v4/messages using payload
 *   shape inferred from the competitor Mailer: originator, recipients, body.parts,
 *   custom_headers, body.attachments, reply_to.
 * - Returns ordered diagnostic steps (status/message/details) suitable for onboarding UI.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve hostname with fallback to DNS-over-HTTPS.
 *
 * @param string $host
 * @return array [string $ip_or_empty, array $debug_lines]
 */
function authority_mailer_smtp_resolve_host_with_doh_for_smtpcom( $host ) {
	$debug       = array();
	$resolved_ip = '';

	if ( function_exists( 'gethostbynamel' ) ) {
		$ips = @gethostbynamel( $host );
		if ( is_array( $ips ) && count( $ips ) ) {
			$resolved_ip = $ips[0];
			$debug[]     = sprintf( 'System resolver returned: %s', $resolved_ip );
			return array( $resolved_ip, $debug );
		}
	} else {
		$res = @gethostbyname( $host );
		if ( $res && $res !== $host ) {
			$resolved_ip = $res;
			$debug[]     = sprintf( 'System resolver returned: %s', $resolved_ip );
			return array( $resolved_ip, $debug );
		}
	}
	$debug[] = 'System DNS resolution failed';

	// Cloudflare DoH.
	try {
		$cf = wp_remote_get(
			'https://cloudflare-dns.com/dns-query?name=' . rawurlencode( $host ) . '&type=A',
			array(
				'headers' => array( 'Accept' => 'application/dns-json' ),
				'timeout' => 8,
			)
		);
		if ( ! is_wp_error( $cf ) && wp_remote_retrieve_response_code( $cf ) === 200 ) {
			$j = json_decode( wp_remote_retrieve_body( $cf ), true );
			if ( ! empty( $j['Answer'] ) && is_array( $j['Answer'] ) ) {
				foreach ( $j['Answer'] as $ans ) {
					if ( isset( $ans['data'] ) && filter_var( $ans['data'], FILTER_VALIDATE_IP ) ) {
						$resolved_ip = $ans['data'];
						$debug[]     = sprintf( 'Cloudflare DoH returned: %s', $resolved_ip );
						return array( $resolved_ip, $debug );
					}
				}
			}
		} else {
			$debug[] = 'Cloudflare DoH request failed or returned non-200';
		}
	} catch ( Exception $e ) {
		$debug[] = 'Cloudflare DoH exception: ' . $e->getMessage();
	}

	// Google DoH fallback.
	try {
		$g = wp_remote_get( 'https://dns.google/resolve?name=' . rawurlencode( $host ) . '&type=A', array( 'timeout' => 8 ) );
		if ( ! is_wp_error( $g ) && wp_remote_retrieve_response_code( $g ) === 200 ) {
			$j = json_decode( wp_remote_retrieve_body( $g ), true );
			if ( ! empty( $j['Answer'] ) && is_array( $j['Answer'] ) ) {
				foreach ( $j['Answer'] as $ans ) {
					if ( isset( $ans['data'] ) && filter_var( $ans['data'], FILTER_VALIDATE_IP ) ) {
						$resolved_ip = $ans['data'];
						$debug[]     = sprintf( 'Google DoH returned: %s', $resolved_ip );
						return array( $resolved_ip, $debug );
					}
				}
			}
		} else {
			$debug[] = 'Google DoH request failed or returned non-200';
		}
	} catch ( Exception $e ) {
		$debug[] = 'Google DoH exception: ' . $e->getMessage();
	}

	return array( $resolved_ip, $debug );
}

/**
 * Build SMTP.com POST payload.
 *
 * @param string $from_email
 * @param string $from_name
 * @param string $to
 * @param string $subject
 * @param string $html
 * @param string $text
 * @param bool   $use_defaults
 * @return array
 */
function authority_mailer_smtp_build_smtpcom_payload( $from_email, $from_name, $to, $subject = '', $html = '', $text = '', $use_defaults = true ) {
	$subject = (string) $subject;
	$html    = (string) $html;
	$text    = (string) $text;

	if ( $use_defaults ) {
		if ( '' === trim( $subject ) ) {
			$subject = 'Authority Mailer SMTP.com Test';
		}
		if ( '' === trim( $html ) && '' === trim( $text ) ) {
			$html = '<p>Authority Mailer SMTP.com test</p>';
		}
	}

	if ( '' === trim( $text ) && '' !== trim( $html ) ) {
		$text = trim( wp_strip_all_tags( $html ) );
	}

	$payload = array(
		'originator' => array(
			'from' => array(
				'address' => (string) $from_email,
				'name'    => (string) $from_name,
			),
		),
		'recipients' => array(
			'to' => array(
				array(
					'address' => (string) $to,
				),
			),
		),
		'subject'    => (string) $subject,
		'body'       => array(
			'parts' => array(),
		),
	);

	if ( '' !== trim( $text ) ) {
		$payload['body']['parts'][] = array(
			'type'    => 'text/plain',
			'content' => (string) $text,
		);
	}
	if ( '' !== trim( $html ) ) {
		$payload['body']['parts'][] = array(
			'type'    => 'text/html',
			'content' => (string) $html,
		);
	}

	return $payload;
}

/**
 * Prepare attachments for SMTP.com payload.
 *
 * Accepts PHPMailer-style attachments or file path strings.
 *
 * @param array $attachments
 * @return array
 */
function authority_mailer_smtp_smtpcom_prepare_attachments( $attachments ) {
	$data = array();

	foreach ( (array) $attachments as $attachment ) {
		$file_path = '';
		$filename  = '';
		$type      = '';
		$disp      = 'attachment';
		$cid       = '';

		if ( is_string( $attachment ) ) {
			$file_path = $attachment;
			$filename  = basename( $file_path );
		} elseif ( is_array( $attachment ) ) {
			$file_path = isset( $attachment[0] ) ? $attachment[0] : ( isset( $attachment['path'] ) ? $attachment['path'] : '' );
			$filename  = isset( $attachment[2] ) ? $attachment[2] : basename( $file_path );
			$type      = isset( $attachment[4] ) ? str_replace( ';', '', trim( $attachment[4] ) ) : '';
			$disp      = isset( $attachment[6] ) && in_array( $attachment[6], array( 'inline', 'attachment' ), true ) ? $attachment[6] : 'attachment';
			$cid       = ! empty( $attachment[7] ) ? (string) $attachment[7] : '';
		}

		if ( empty( $file_path ) || ! file_exists( $file_path ) ) {
			continue;
		}

		$contents = @file_get_contents( $file_path );
		if ( $contents === false ) {
			continue;
		}

		if ( empty( $type ) ) {
			$finfo = wp_check_filetype( $file_path );
			$type  = ! empty( $finfo['type'] ) ? $finfo['type'] : 'application/octet-stream';
		}

		// SMTP.com competitor used chunk_split(base64_encode(...))
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$data[] = array(
			'content'     => chunk_split( base64_encode( $contents ) ),
			'type'        => $type,
			'encoding'    => 'base64',
			'filename'    => $filename,
			'disposition' => $disp,
			'cid'         => $cid,
		);
	}

	return $data;
}

/**
 * Format address for SMTP.com: { address: 'email', name: 'Name' }
 *
 * Accepts string or [email,name]
 *
 * @param string|array $addr
 * @return array|null
 */
function authority_mailer_smtp_smtpcom_address_format( $addr ) {
	if ( is_string( $addr ) ) {
		$email = trim( $addr );
		$name  = '';
	} elseif ( is_array( $addr ) ) {
		$email = isset( $addr[0] ) ? trim( $addr[0] ) : '';
		$name  = isset( $addr[1] ) ? trim( $addr[1] ) : '';
	} else {
		return null;
	}
	if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
		return null;
	}
	$out = array( 'address' => $email );
	if ( $name !== '' ) {
		$out['name'] = $name;
	}
	return $out;
}

/**
 * Main SMTP.com adapter for testers.
 *
 * @param array $settings
 * @return array steps
 */
function authority_mailer_smtp_test_smtpcom( $settings = array() ) {
	$steps   = array();
	$steps[] = array(
		'status'  => 'info',
		'message' => "Running diagnostics for provider 'smtpcom'",
	);

	// Accept common key names.
	$key_names = array( 'smtpcom_api_key', 'smtpcom_key', 'api_key', 'apikey', 'key', 'token' );
	$api_key   = '';
	foreach ( $key_names as $kn ) {
		if ( ! empty( $settings[ $kn ] ) && is_string( $settings[ $kn ] ) ) {
			$api_key = trim( $settings[ $kn ] );
			break;
		}
	}
	// channel (sender) detection.
	$channel_names = array( 'channel', 'sender', 'smtpcom_channel', 'smtpcom_sender' );
	$channel       = '';
	foreach ( $channel_names as $cn ) {
		if ( ! empty( $settings[ $cn ] ) ) {
			$channel = $settings[ $cn ];
			break;
		}
	}

	if ( empty( $api_key ) ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => 'API key / token not found in saved settings',
			'details' => array( 'checked_keys' => $key_names ),
		);
		return $steps;
	}
	$steps[] = array(
		'status'  => 'info',
		'message' => 'API key present — attempting provider API check / transmission',
	);

	// Use centralized test helper - sends through full wp_mail() pipeline with premium features.
	if ( function_exists( 'authority_mailer_smtp_send_test_via_wpmail' ) ) {
		authority_mailer_smtp_send_test_via_wpmail( 'smtpcom', $test_to, $final_from_email, $final_from_name, $steps );
	} else {
		$steps[] = array(
			'status'  => 'error',
			'message' => 'Centralized test helper function not found. Please update common.php.',
		);
	}

	return $steps;
}

/**
 * Send email via SMTP.com API.
 *
 * Adapter function called by Authority Mailer Sender class to send emails through
 * the SMTP.com transactional email service.
 *
 * @since 1.0.0
 *
 * @param array $email Email data from wp_mail containing to, subject, message, headers, and attachments.
 * @return true|WP_Error True on success, WP_Error on failure.
 */
function authority_mailer_smtp_send_smtpcom( $email ) {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
		error_log( '[Authority Mailer SMTP.com] Send function called | email keys: ' . wp_json_encode( array_keys( $email ) ) );
		if ( isset( $email['spam_score'] ) ) {
						// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
						error_log( '[Authority Mailer SMTP.com] Spam score in email: ' . $email['spam_score'] );
		}
	}

	$options = get_option( 'authority_mailer_smtp_options', array() );

	// Get API key.
	$api_key = ! empty( $options['smtpcom_api_key'] )
		? $options['smtpcom_api_key']
		: '';

	if ( empty( $api_key ) ) {
		return new WP_Error( 'missing_api_key', 'SMTP.com API key not configured' );
	}

	// Get from email/name.
	$from_email = ! empty( $options['smtpcom_from_email'] )
		? $options['smtpcom_from_email']
		: get_option( 'admin_email' );

	$from_name = ! empty( $options['smtpcom_from_name'] )
		? $options['smtpcom_from_name']
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
	$payload = authority_mailer_smtp_build_smtpcom_payload(
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

	// Add Return-Path header if provided.
	if ( ! empty( $defaults['return_path'] ) ) {
		if ( ! isset( $payload['custom_headers'] ) ) {
			$payload['custom_headers'] = array();
		}
		$payload['custom_headers'][] = array(
			'header' => 'Return-Path',
			'value'  => $defaults['return_path'],
		);
	}

	// Debug logging for email defaults added to payload.
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
		error_log(
			'[Authority Mailer SMTP.com] Email defaults added to payload | Reply-To: ' .
			( ! empty( $defaults['reply_to'] ) ? $defaults['reply_to'] : 'none' ) .
			' | CC count: ' . count( $defaults['cc'] ) .
			' | BCC count: ' . count( $defaults['bcc'] ) .
			' | Priority: ' . ( ! empty( $defaults['priority'] ) ? $defaults['priority'] : 'none' ) .
			' | Return-Path: ' . ( ! empty( $defaults['return_path'] ) ? $defaults['return_path'] : 'none' )
		);
	}

	// Add tracking ID to payload if present (for webhook correlation).
	if ( ! empty( $email['tracking_id'] ) ) {
		// SMTP.com supports metadata for tracking - must be array of objects with header/value keys.
		if ( ! isset( $payload['custom_headers'] ) ) {
			$payload['custom_headers'] = array();
		}
		$payload['custom_headers'][] = array(
			'header' => 'X-Authority-Mailer-Tracking-ID',
			'value'  => $email['tracking_id'],
		);

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
			error_log( '[Authority Mailer SMTP.com] Added tracking ID to payload: ' . $email['tracking_id'] );
		}
	}

	// Log the email attempt.
	$log_id = 0;
	if ( function_exists( 'authority_mailer_smtp_log_email_with_spam_score' ) ) {
		$log_data = array(
			'provider'   => 'smtpcom',
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
		'https://api.smtp.com/v4/messages',
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
				'provider'      => 'smtpcom',
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
				'provider'      => 'smtpcom',
				'error_code'    => $code,
				'error_message' => $body,
				'email_data'    => $email,
			)
		);

		return new WP_Error( 'smtpcom_error', 'SMTP.com API error: ' . $body, array( 'code' => $code ) );
	}

	return true;
}
