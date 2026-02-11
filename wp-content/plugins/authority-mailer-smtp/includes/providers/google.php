<?php
/**
 * google.php - Fixed Gmail/Google Provider
 *
 * Complete OAuth flow implementation for Gmail API
 */

defined( 'ABSPATH' ) || exit;

require_once AUTHORITY_MAILER_PLUGIN_DIR . 'includes/providers/common.php';

global $AUTHORITY_MAILER_STRINGS;

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

if ( ! function_exists( 'authority_mailer_smtp_google_build_raw_message' ) ) {
	/**
	 * Build RFC2822 message for Gmail API.
	 *
	 * @since 1.0.0
	 *
	 * @param string $from_email The sender email address.
	 * @param string $from_name  The sender name.
	 * @param string $to         The recipient email address.
	 * @param string $subject    The email subject.
	 * @param string $html       The HTML email body.
	 * @param string $text       The plain text email body.
	 * @return string Base64url-encoded email message.
	 */
	function authority_mailer_smtp_google_build_raw_message( $from_email, $from_name, $to, $subject, $html = '', $text = '', $tracking_id = null, $parsed_headers = array() ) {
		$from    = $from_name ? "{$from_name} <{$from_email}>" : $from_email;
		$lines   = array();
		$lines[] = "From: {$from}";
		$lines[] = "To: {$to}";

		// Add CC and BCC from parsed headers
		if ( ! empty( $defaults['cc'] ) ) {
			$lines[] = 'Cc: ' . implode( ', ', $defaults['cc'] );
		}
		if ( ! empty( $defaults['bcc'] ) ) {
			$lines[] = 'Bcc: ' . implode( ', ', $defaults['bcc'] );
		}

		// Add MIME encoding for subject line to fix Gmail display issues with non-ASCII characters.
		$encoded_subject = $subject;
		if ( function_exists( 'mb_encode_mimeheader' ) && ! mb_check_encoding( $subject, 'ASCII' ) ) {
			$encoded_subject = mb_encode_mimeheader( $subject, 'UTF-8', 'B' );
		}
		$lines[] = "Subject: {$encoded_subject}";
		
		// Add Reply-To from parsed headers
		if ( ! empty( $defaults['reply_to'] ) ) {
			$lines[] = "Reply-To: {$defaults['reply_to']}";
		}
		
		$lines[] = 'MIME-Version: 1.0';

		// Add tracking ID header if provided.
		if ( ! empty( $tracking_id ) ) {
			$lines[] = "X-Authority-Mailer-Tracking-ID: {$tracking_id}";
		}
		
		// Add custom headers from parsed headers
		if ( ! empty( $defaults['custom'] ) ) {
			foreach ( $defaults['custom'] as $custom_header ) {
				$lines[] = "{$custom_header['name']}: {$custom_header['value']}";
			}
		}

		if ( '' !== $html ) {
			$boundary = '----=_Authority_Mailer_' . wp_hash( microtime() );
			$lines[]  = "Content-Type: multipart/alternative; boundary=\"{$boundary}\"";
			$lines[]  = '';
			$lines[]  = "--{$boundary}";
			$lines[]  = 'Content-Type: text/plain; charset="UTF-8"';
			$lines[]  = 'Content-Transfer-Encoding: 7bit';
			$lines[]  = '';
			$lines[]  = '' !== $text ? $text : wp_strip_all_tags( $html );
			$lines[]  = "--{$boundary}";
			$lines[]  = 'Content-Type: text/html; charset="UTF-8"';
			$lines[]  = 'Content-Transfer-Encoding: 7bit';
			$lines[]  = '';
			$lines[]  = $html;
			$lines[]  = "--{$boundary}--";
		} else {
			$lines[] = 'Content-Type: text/plain; charset="UTF-8"';
			$lines[] = 'Content-Transfer-Encoding: 7bit';
			$lines[] = '';
			$lines[] = '' !== $text ? $text : '';
		}

		$raw    = implode( "\r\n", $lines );
		$b64    = base64_encode( $raw );
		$b64url = rtrim( strtr( $b64, '+/', '-_' ), '=' );
		return $b64url;
	}
}

if ( ! function_exists( 'authority_mailer_smtp_google_refresh_access_token' ) ) {
	/**
	 * Exchange refresh token for access token.
	 *
	 * @since 1.0.0
	 *
	 * @param string $client_id     The Google OAuth client ID.
	 * @param string $client_secret The Google OAuth client secret.
	 * @param string $refresh_token The refresh token.
	 * @return string|WP_Error The access token on success, WP_Error on failure.
	 */
	function authority_mailer_smtp_google_refresh_access_token( $client_id, $client_secret, $refresh_token ) {
		$token_url = 'https://oauth2.googleapis.com/token';

		$args = array(
			'timeout' => 20,
			'headers' => array(
				'Content-Type' => 'application/x-www-form-urlencoded',
			),
			'body'    => array(
				'client_id'     => (string) $client_id,
				'client_secret' => (string) $client_secret,
				'refresh_token' => (string) $refresh_token,
				'grant_type'    => 'refresh_token',
			),
		);

		$resp = wp_remote_post( $token_url, $args );

		if ( is_wp_error( $resp ) ) {
			return array( false, 'network_error', $resp->get_error_message() );
		}

		$code    = wp_remote_retrieve_response_code( $resp );
		$body    = wp_remote_retrieve_body( $resp );
		$decoded = json_decode( $body, true );

		if ( 200 === (int) $code && is_array( $decoded ) && ! empty( $decoded['access_token'] ) ) {
			return array( true, $decoded['access_token'], $decoded );
		}

		$msg = is_array( $decoded ) && ! empty( $decoded['error_description'] )
			? $decoded['error_description']
			: ( is_array( $decoded ) && ! empty( $decoded['error'] ) ? wp_json_encode( $decoded['error'] ) : $body );
		return array( false, 'oauth_error', (string) $msg );
	}
}

/**
 * Run Google/Gmail diagnostics and test transmission.
 *
 * @param array $settings Onboarding settings (may be empty).
 * @return array Ordered steps describing actions and outcomes.
 */
function authority_mailer_smtp_test_google( $settings = array() ) {
	$steps = array();

	$steps[] = array(
		'status'  => 'info',
		'message' => ( authority_mailer_smtp_s( 'google_diag_start' ) ? authority_mailer_smtp_s( 'google_diag_start' ) : 'Starting Google/Gmail diagnostics' ),
	);

	$provided_settings = is_array( $settings ) ? $settings : array();

	// Onboarding summary (only if provided).
	if ( ! empty( $provided_settings ) ) {
		$steps[] = array(
			'status'  => 'detail',
			'message' => ( authority_mailer_smtp_s( 'google_onboarding_keys' ) ? authority_mailer_smtp_s( 'google_onboarding_keys' ) : 'Onboarding-provided settings summary' ),
			'details' => function_exists( 'authority_mailer_smtp_settings_summary' ) ? authority_mailer_smtp_settings_summary( $provided_settings ) : array(),
		);
	}

	// Merge stored options carefully (provider-group first, then whitelist).
	$mm_opts = get_option( 'authority_mailer_smtp_options', array() );
	$mm_opts = is_array( $mm_opts ) ? $mm_opts : array();

	if ( ! empty( $mm_opts ) ) {
		$steps[] = array(
			'status'  => 'detail',
			'message' => ( authority_mailer_smtp_s( 'google_mm_opts_keys' ) ? authority_mailer_smtp_s( 'google_mm_opts_keys' ) : 'Stored authority_mailer_options inspected (summary)' ),
			'details' => function_exists( 'authority_mailer_smtp_settings_summary' ) ? authority_mailer_smtp_settings_summary( $mm_opts ) : array(),
		);
	}

	// Merge provider-scoped groups if present.
	$provider_groups = array( 'google', 'gmail', 'google_api', 'google_oauth' );
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

	// Whitelist top-level keys to merge from mm_opts.
	$allowed_keys = array(
		'client_id',
		'client_secret',
		'refresh_token',
		'access_token',
		'google_client_id',
		'google_client_secret',
		'google_refresh_token',
		'google_access_token',
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
	);

	foreach ( $mm_opts as $k => $v ) {
		if ( isset( $provided_settings[ $k ] ) ) {
			continue;
		}
		if ( in_array( $k, $allowed_keys, true ) || 0 === strpos( $k, 'google_' ) || 0 === strpos( $k, 'gmail_' ) ) {
			$provided_settings[ $k ] = $v;
		}
	}

	$steps[] = array(
		'status'  => 'detail',
		'message' => ( authority_mailer_smtp_s( 'google_final_settings' ) ? authority_mailer_smtp_s( 'google_final_settings' ) : 'Final settings used after merge (summary)' ),
		'details' => function_exists( 'authority_mailer_smtp_settings_summary' ) ? authority_mailer_smtp_settings_summary( $provided_settings ) : array(),
	);

	// Detect credentials.
	$client_id     = ! empty( $provided_settings['client_id'] ) ? (string) $provided_settings['client_id'] : ( ! empty( $provided_settings['google_client_id'] ) ? (string) $provided_settings['google_client_id'] : '' );
	$client_secret = ! empty( $provided_settings['client_secret'] ) ? (string) $provided_settings['client_secret'] : ( ! empty( $provided_settings['google_client_secret'] ) ? (string) $provided_settings['google_client_secret'] : '' );

	$access_token  = ! empty( $provided_settings['access_token'] ) ? (string) $provided_settings['access_token'] : ( ! empty( $provided_settings['oauth_access_token'] ) ? (string) $provided_settings['oauth_access_token'] : ( ! empty( $provided_settings['google_access_token'] ) ? (string) $provided_settings['google_access_token'] : '' ) );
	$refresh_token = ! empty( $provided_settings['refresh_token'] ) ? (string) $provided_settings['refresh_token'] : ( ! empty( $provided_settings['oauth_refresh_token'] ) ? (string) $provided_settings['oauth_refresh_token'] : ( ! empty( $provided_settings['google_refresh_token'] ) ? (string) $provided_settings['google_refresh_token'] : '' ) );

	// If no client_id/secret -> cannot perform refresh flow.
	if ( empty( $client_id ) || empty( $client_secret ) ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => ( authority_mailer_smtp_s( 'google_oauth_client_missing' ) ? authority_mailer_smtp_s( 'google_oauth_client_missing' ) : 'OAuth client credentials missing' ),
			'details' => ( authority_mailer_smtp_s( 'google_oauth_client_missing_detail' ) ? authority_mailer_smtp_s( 'google_oauth_client_missing_detail' ) : 'Provide OAuth Client ID and Client Secret for Google/Gmail (fields: client_id, client_secret).' ),
		);
		return $steps;
	}

	$steps[] = array(
		'status'  => 'info',
		'message' => ( authority_mailer_smtp_s( 'google_oauth_client_detected' ) ? authority_mailer_smtp_s( 'google_oauth_client_detected' ) : 'OAuth credentials detected' ),
		'details' => array( 'client_id_mask' => function_exists( 'authority_mailer_smtp_mask_key' ) ? authority_mailer_smtp_mask_key( $client_id ) : substr( $client_id, 0, 12 ) . '...' ),
	);

	// If we don't have an access token but have a refresh token, try exchange.
	$token_used = '';
	if ( empty( $access_token ) && ! empty( $refresh_token ) ) {
		$steps[] = array(
			'status'  => 'info',
			'message' => ( authority_mailer_smtp_s( 'google_refresh_attempt' ) ? authority_mailer_smtp_s( 'google_refresh_attempt' ) : 'Attempting to refresh access token' ),
		);

		list( $ok, $token_or_error, $debug ) = authority_mailer_smtp_google_refresh_access_token( $client_id, $client_secret, $refresh_token );

		if ( $ok ) {
			$access_token = (string) $token_or_error;
			$token_used   = 'refreshed';
			$steps[]      = array(
				'status'  => 'detail',
				'message' => ( authority_mailer_smtp_s( 'google_refresh_success' ) ? authority_mailer_smtp_s( 'google_refresh_success' ) : 'Access token refreshed successfully' ),
				'details' => is_array( $debug ) ? $debug : '',
			);

			// Save refreshed token back to options.
			$mm_opts['google_access_token'] = $access_token;
			update_option( 'authority_mailer_smtp_options', $mm_opts );
		} else {
			// token refresh failed.
			$steps[] = array(
				'status'  => 'error',
				'message' => ( authority_mailer_smtp_s( 'google_refresh_failed' ) ? authority_mailer_smtp_s( 'google_refresh_failed' ) : 'Token refresh failed' ),
				'details' => $token_or_error,
			);
			return $steps;
		}
	} elseif ( ! empty( $access_token ) ) {
		$token_used = 'provided';
		$steps[]    = array(
			'status'  => 'detail',
			'message' => ( authority_mailer_smtp_s( 'google_access_token_provided' ) ? authority_mailer_smtp_s( 'google_access_token_provided' ) : 'Using existing access token' ),
		);
	} else {
		// No access or refresh token.
		$steps[] = array(
			'status'  => 'error',
			'message' => ( authority_mailer_smtp_s( 'google_no_tokens' ) ? authority_mailer_smtp_s( 'google_no_tokens' ) : 'No access or refresh token available' ),
			'details' => ( authority_mailer_smtp_s( 'google_no_tokens_detail' ) ? authority_mailer_smtp_s( 'google_no_tokens_detail' ) : 'Please complete OAuth authorization first.' ),
		);
		return $steps;
	}

	// Determine From/To (respect force toggles).
	$force_from_email = function_exists( 'authority_mailer_smtp_to_bool' )
		? authority_mailer_smtp_to_bool( ! empty( $provided_settings['force_from_email'] ) ? $provided_settings['force_from_email'] : ( ! empty( $provided_settings['google_force_from_email'] ) ? $provided_settings['google_force_from_email'] : false ) )
		: ( ! empty( $provided_settings['force_from_email'] ) || ! empty( $provided_settings['google_force_from_email'] ) );

	$force_from_name = function_exists( 'authority_mailer_smtp_to_bool' )
		? authority_mailer_smtp_to_bool( ! empty( $provided_settings['force_from_name'] ) ? $provided_settings['force_from_name'] : ( ! empty( $provided_settings['google_force_from_name'] ) ? $provided_settings['google_force_from_name'] : false ) )
		: ( ! empty( $provided_settings['force_from_name'] ) || ! empty( $provided_settings['google_force_from_name'] ) );

	$candidate_from_email = '';
	$from_keys            = array( 'google_from_email', 'from_email', 'other_from_email' );
	foreach ( $from_keys as $fk ) {
		if ( ! empty( $provided_settings[ $fk ] ) ) {
			$candidate_from_email = sanitize_email( $provided_settings[ $fk ] );
			break;
		}
	}
	if ( empty( $candidate_from_email ) ) {
		$candidate_from_email = get_option( 'admin_email', '' );
		$steps[]              = array(
			'status'  => 'detail',
			'message' => ( authority_mailer_smtp_s( 'google_no_from_fallback' ) ? authority_mailer_smtp_s( 'google_no_from_fallback' ) : 'No from address in settings; falling back to admin_email' ),
			'details' => (string) $candidate_from_email,
		);
	}

	$candidate_from_name = '';
	$from_name_keys      = array( 'google_from_name', 'from_name', 'other_from_name' );
	foreach ( $from_name_keys as $fnk ) {
		if ( ! empty( $provided_settings[ $fnk ] ) ) {
			$candidate_from_name = sanitize_text_field( $provided_settings[ $fnk ] );
			break;
		}
	}

	$final_from_email = (string) $candidate_from_email;
	$final_from_name  = (string) $candidate_from_name;

	if ( $force_from_email ) {
		if ( ! empty( $provided_settings['google_from_email'] ) ) {
			$final_from_email = sanitize_email( $provided_settings['google_from_email'] );
			$steps[]          = array(
				'status'  => 'detail',
				'message' => ( authority_mailer_smtp_s( 'google_force_from_email_used' ) ? authority_mailer_smtp_s( 'google_force_from_email_used' ) : 'Force-from-email enabled; using google_from_email' ),
				'details' => (string) $final_from_email,
			);
		} else {
			$steps[] = array(
				'status'  => 'detail',
				'message' => ( authority_mailer_smtp_s( 'google_force_from_email_empty' ) ? authority_mailer_smtp_s( 'google_force_from_email_empty' ) : 'Force-from-email enabled but google_from_email is empty' ),
			);
		}
	}

	if ( $force_from_name ) {
		if ( ! empty( $provided_settings['google_from_name'] ) ) {
			$final_from_name = sanitize_text_field( $provided_settings['google_from_name'] );
			$steps[]         = array(
				'status'  => 'detail',
				'message' => ( authority_mailer_smtp_s( 'google_force_from_name_used' ) ? authority_mailer_smtp_s( 'google_force_from_name_used' ) : 'Force-from-name enabled; using google_from_name' ),
				'details' => (string) $final_from_name,
			);
		} else {
			$steps[] = array(
				'status'  => 'detail',
				'message' => ( authority_mailer_smtp_s( 'google_force_from_name_empty' ) ? authority_mailer_smtp_s( 'google_force_from_name_empty' ) : 'Force-from-name enabled but google_from_name is empty' ),
			);
		}
	}

	// Recipient.
	if ( ! empty( $provided_settings['test_recipient'] ) ) {
		$test_to = sanitize_email( $provided_settings['test_recipient'] );
		$steps[] = array(
			'status'  => 'detail',
			'message' => ( authority_mailer_smtp_s( 'google_using_test_recipient' ) ? authority_mailer_smtp_s( 'google_using_test_recipient' ) : 'Using test_recipient from settings' ),
			'details' => (string) $test_to,
		);
	} else {
		$test_to = get_option( 'admin_email', '' );
		$steps[] = array(
			'status'  => 'detail',
			'message' => ( authority_mailer_smtp_s( 'google_using_admin_email' ) ? authority_mailer_smtp_s( 'google_using_admin_email' ) : 'Using admin_email as test recipient' ),
			'details' => (string) $test_to,
		);
	}

	if ( empty( $test_to ) || ! is_email( $test_to ) ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => ( authority_mailer_smtp_s( 'google_no_recipient' ) ? authority_mailer_smtp_s( 'google_no_recipient' ) : 'No recipient available to send test email (admin email not set).' ),
		);
		return $steps;
	}

	// Log final addresses.
	if ( function_exists( 'authority_mailer_smtp_log_final_addresses' ) ) {
		authority_mailer_smtp_log_final_addresses( $steps, $test_to, $final_from_email, $final_from_name );
	} else {
		$steps[] = array(
			'status'  => 'detail',
			'message' => 'Final transmission addresses',
			'details' => array(
				'to'         => $test_to,
				'from_email' => $final_from_email,
				'from_name'  => $final_from_name,
			),
		);
	}

	// Build professional test email using centralized template.
	$test_email_args = array(
		'provider'   => 'gmail',
		'from_email' => $final_from_email,
		'from_name'  => $final_from_name,
		'to_email'   => $test_to,
	);

	// Use professional template if available, otherwise fallback.
	if ( function_exists( 'authority_mailer_smtp_get_test_email_subject' ) ) {
		$subject = authority_mailer_smtp_get_test_email_subject( 'Gmail' );
	} else {
		$subject = isset( $provided_settings['test_subject'] ) ? sanitize_text_field( $provided_settings['test_subject'] ) : ( ( authority_mailer_smtp_s( 'google_default_subject' ) ? authority_mailer_smtp_s( 'google_default_subject' ) : 'Authority Mailer Gmail test' ) );
	}

	if ( function_exists( 'authority_mailer_smtp_get_test_email_html' ) ) {
		$body_html = authority_mailer_smtp_get_test_email_html( $test_email_args );
		$body_text = function_exists( 'authority_mailer_smtp_get_test_email_plain' ) ? authority_mailer_smtp_get_test_email_plain( $test_email_args ) : wp_strip_all_tags( $body_html );
	} else {
		$body_html = isset( $provided_settings['html_content'] ) ? (string) $provided_settings['html_content'] : ( ( authority_mailer_smtp_s( 'google_default_body' ) ? authority_mailer_smtp_s( 'google_default_body' ) : '<p>Authority Mailer Gmail test</p>' ) );
		$body_text = isset( $provided_settings['plain_content'] ) ? (string) $provided_settings['plain_content'] : wp_strip_all_tags( $body_html );
	}

	$raw = authority_mailer_smtp_google_build_raw_message( $final_from_email, $final_from_name, $test_to, $subject, $body_html, $body_text );

	// Prepare endpoint and request.
	$endpoint  = 'https://gmail.googleapis.com/gmail/v1/users/me/messages/send';
	$args_post = array(
		'timeout' => 30,
		'headers' => array(
			'Authorization' => 'Bearer ' . $access_token,
			'Accept'        => 'application/json',
			'Content-Type'  => 'application/json',
		),
		'body'    => wp_json_encode( array( 'raw' => $raw ) ),
	);

	$steps[] = array(
		'status'  => 'info',
		'message' => sprintf( ( authority_mailer_smtp_s( 'google_attempting_post' ) ? authority_mailer_smtp_s( 'google_attempting_post' ) : 'Attempting POST %s' ), $endpoint ),
		'details' => array( ( ( authority_mailer_smtp_s( 'google_payload_preview' ) ? authority_mailer_smtp_s( 'google_payload_preview' ) : 'Payload keys sent (preview)' ) ) => ( function_exists( 'authority_mailer_smtp_list_keys' ) ? authority_mailer_smtp_list_keys( array( 'raw' ) ) : array( 'raw' ) ) ),
	);

	// Use centralized test helper - sends through full wp_mail() pipeline with premium features.
	if ( function_exists( 'authority_mailer_smtp_send_test_via_wpmail' ) ) {
		authority_mailer_smtp_send_test_via_wpmail( 'google', $test_to, $final_from_email, $final_from_name, $steps );
	} else {
		$steps[] = array(
			'status'  => 'error',
			'message' => 'Centralized test helper function not found. Please update common.php.',
		);
	}

	return $steps;
}

/**
 * Send email via Gmail API.
 *
 * Adapter function called by Authority Mailer Sender class to send emails through
 * the Gmail API using OAuth2.
 *
 * @since 1.0.0
 *
 * @param array $email Email data from wp_mail containing to, subject, message, headers, and attachments.
 * @return true|WP_Error True on success, WP_Error on failure.
 */
function authority_mailer_smtp_send_gmail( $email ) {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
		error_log( '[Authority Mailer Gmail] Send function called | email keys: ' . wp_json_encode( array_keys( $email ) ) );
		if ( isset( $email['spam_score'] ) ) {
						// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
						error_log( '[Authority Mailer Gmail] Spam score in email: ' . $email['spam_score'] );
		}
	}

	$options = get_option( 'authority_mailer_smtp_options', array() );

	// Get OAuth credentials.
	$client_id     = ! empty( $options['google_client_id'] ) ? $options['google_client_id'] : '';
	$client_secret = ! empty( $options['google_client_secret'] ) ? $options['google_client_secret'] : '';
	$refresh_token = ! empty( $options['google_refresh_token'] ) ? $options['google_refresh_token'] : '';

	if ( empty( $client_id ) || empty( $client_secret ) || empty( $refresh_token ) ) {
		return new WP_Error( 'missing_credentials', 'Gmail OAuth credentials not configured (client_id, client_secret, or refresh_token missing)' );
	}

	// Get from email/name.
	$from_email = ! empty( $options['google_from_email'] )
		? $options['google_from_email']
		: get_option( 'admin_email' );

	$from_name = ! empty( $options['google_from_name'] )
		? $options['google_from_name']
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
	}

	// Get access token (refresh if needed).
	$token_result = authority_mailer_smtp_google_refresh_access_token( $client_id, $client_secret, $refresh_token );
	if ( ! is_array( $token_result ) || ! $token_result[0] ) {
		$error_msg = isset( $token_result[2] ) ? $token_result[2] : 'Failed to refresh access token';
		return new WP_Error( 'oauth_error', 'Gmail OAuth error: ' . $error_msg );
	}
	$access_token = $token_result[1];

	// Build RFC2822 raw message.
	$tracking_id_for_raw = ! empty( $email['tracking_id'] ) ? $email['tracking_id'] : null;
	
	// Use helper function to extract email defaults with fallback to header parsing.
	$defaults = authority_mailer_smtp_get_email_defaults( $email );
	
	$raw = authority_mailer_smtp_google_build_raw_message( $from_email, $from_name, $to_email, $subject, $html, $text, $tracking_id_for_raw, $parsed_headers );

	if ( $tracking_id_for_raw && defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
		error_log( '[Authority Mailer Google] Added tracking ID to raw message: ' . $tracking_id_for_raw );
	}

	// Log the email attempt.
	$log_id = 0;
	if ( function_exists( 'authority_mailer_smtp_log_email_with_spam_score' ) ) {
		$log_data = array(
			'provider'   => 'gmail',
			'to_email'   => $to_email,
			'from_email' => $from_email,
			'from_name'  => $from_name,
			'subject'    => $subject,
			'headers'    => isset( $email['headers'] ) ? ( is_array( $email['headers'] ) ? wp_json_encode( $email['headers'] ) : $email['headers'] ) : '',
			'body'       => $message,
			'payload'    => wp_json_encode( array( 'raw' => substr( $raw, 0, 200 ) . '...' ) ),
			'status'     => 'attempt',
		);

		// Use centralized helper that handles spam score extraction and debug logging automatically.
		$log_id = authority_mailer_smtp_log_email_with_spam_score( $email, $log_data );
	}

	// Make API call.
	$response = wp_remote_post(
		'https://gmail.googleapis.com/gmail/v1/users/me/messages/send',
		array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
				'Content-Type'  => 'application/json',
			),
			'body'    => wp_json_encode( array( 'raw' => $raw ) ),
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
				'provider'      => 'gmail',
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
				'provider'      => 'gmail',
				'error_code'    => $code,
				'error_message' => $body,
				'email_data'    => $email,
			)
		);

		return new WP_Error( 'gmail_error', 'Gmail API error: ' . $body, array( 'code' => $code ) );
	}

	return true;
}
