<?php
/**
 * elasticmail.php
 *
 * Elastic Email provider tester and sender for Authority Mailer (v4 API).
 *
 * - Builds v4-style payloads (recipients.to[] as strings, content.from, content.fromName,
 *   content.subject, content.body).
 * - Adds additional backward-compatible sender/body shapes (from object, from string,
 *   bodyHtml/bodyText) so the content and from-name are preserved across Elastic Email
 *   deployments/versions.
 * - Tester (authority_mailer_test_elasticmail) posts to /v4/emails/transactional with JSON.
 * - authority_mailer_smtp_send_elasticmail uses the same v4 payload shape and header-based API key.
 *
 * Location: wp-content/plugins/authority-mailer-smtp/includes/providers/elasticmail.php
 *
 * @package Authority_Mailer
 */

defined( 'ABSPATH' ) || exit;

require_once AUTHORITY_MAILER_PLUGIN_DIR . 'includes/providers/common.php';

global $AUTHORITY_MAILER_STRINGS;

if ( ! function_exists( 'authority_mailer_smtp_s' ) ) {
	/**
	 * Retrieve localized string from centralized array.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key String key.
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

if ( ! function_exists( 'authority_mailer_smtp_settings_summary' ) ) {
	/**
	 * Safe settings summary used in logs (no option/key names printed).
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings Settings array.
	 * @return array Summary of settings including masked API key.
	 */
	function authority_mailer_smtp_settings_summary( $settings ) {
		$settings = is_array( $settings ) ? $settings : array();
		$summary  = array(
			'count'        => count( $settings ),
			'has_api_key'  => false,
			'api_key_mask' => '',
		);
		if ( ! empty( $settings['elasticmail_api_key'] ) ) {
			$summary['has_api_key']  = true;
			$summary['api_key_mask'] = authority_mailer_smtp_mask_key( $settings['elasticmail_api_key'] );
		} elseif ( ! empty( $settings['elastic_api_key'] ) ) {
			$summary['has_api_key']  = true;
			$summary['api_key_mask'] = authority_mailer_smtp_mask_key( $settings['elastic_api_key'] );
		} elseif ( ! empty( $settings['api_key'] ) ) {
			$summary['has_api_key']  = true;
			$summary['api_key_mask'] = authority_mailer_smtp_mask_key( $settings['api_key'] );
		}
		return $summary;
	}
}

/**
 * Build Elastic Email v4 payload for transactional API.
 *
 * Notes:
 * - content.from/content.fromName are required for v4.
 * - Many Elastic installations/versions vary in accepted body/from shapes. To maximize
 *   compatibility we include multiple canonical shapes:
 *     - content.from / content.fromName
 *     - top-level 'from' object with email/name
 *     - legacy top-level 'from' string "Name <email>"
 *     - legacy bodyHtml/bodyText top-level fields
 *     - content.body array with contentType/value and type/content
 *
 * This mirrors how other adapters provide multiple compatibility fields (see mailersend/mailgun/mandrill).
 *
 * @param string $from_email From email.
 * @param string $from_name  From name.
 * @param string $to         Recipient email.
 * @param string $subject    Subject.
 * @param string $html       HTML body.
 * @param string $text       Plain text.
 * @return array
 */
function authority_mailer_smtp_build_elasticmail_payload_v4( $from_email, $from_name, $to, $subject, $html = '', $text = '' ) {
	$to_email   = (string) $to;
	$from_email = (string) $from_email;
	$from_name  = (string) $from_name;
	$subject    = (string) $subject;

	// Ensure sensible fallbacks for empty from_name/from_email.
	if ( '' === trim( $from_name ) ) {
		$from_name = get_bloginfo( 'name' );
	}
	if ( '' === trim( $from_email ) ) {
		$from_email = get_option( 'admin_email', '' );
	}

	// Build canonical payload for v4.
	$payload = array(
		'recipients' => array(
			// v4 expects an array of strings under "to".
			'to' => array( $to_email ),
		),
		'content'    => array(
			// required fields honored by v4 API.
			'from'     => $from_email,
			'fromName' => $from_name,
			'subject'  => $subject,
			'body'     => array(),
		),
	);

	// Add compatibility: top-level 'from' object and 'from' string.
	$payload['from']        = array(
		'email' => $from_email,
		'name'  => $from_name,
	);
	$payload['from_string'] = sprintf( '%s <%s>', $from_name, $from_email );

	// Add legacy bodyHtml/bodyText to increase chance content is used.
	if ( '' !== trim( (string) $html ) ) {
		$payload['bodyHtml'] = (string) $html;
	}
	if ( '' !== trim( (string) $text ) ) {
		$payload['bodyText'] = (string) $text;
	}

	// Add structured content.body entries.
	// Include both 'contentType'/'value' and 'type'/'content' variants for compatibility.
	if ( '' !== trim( (string) $html ) ) {
		$payload['content']['body'][] = array(
			'contentType' => 'HTML',
			'value'       => (string) $html,
			'type'        => 'html',
			'content'     => (string) $html,
		);
	}
	if ( '' !== trim( (string) $text ) ) {
		$payload['content']['body'][] = array(
			'contentType' => 'PlainText',
			'value'       => (string) $text,
			'type'        => 'plain',
			'content'     => (string) $text,
		);
	}

	// If both empty, include a minimal text part.
	if ( empty( $payload['content']['body'] ) ) {
		$payload['content']['body'][] = array(
			'contentType' => 'PlainText',
			'value'       => 'Authority Mailer test',
			'type'        => 'plain',
			'content'     => 'Authority Mailer test',
		);
		$payload['bodyText']          = 'Authority Mailer test';
	}

	return $payload;
}

/**
 * Run Elastic Email diagnostics and test transmission (v4 endpoint).
 *
 * @param array $settings Onboarding settings (may be empty).
 * @return array Ordered steps describing actions and outcomes.
 */
function authority_mailer_smtp_test_elasticmail( $settings = array() ) {
	$steps = array();

	$steps[] = array(
		'status'  => 'info',
		'message' => authority_mailer_smtp_s( 'elasticmail_diag_start' ),
	);

	$provided_settings = is_array( $settings ) ? $settings : array();

	// Provide safe summaries for logs.
	$steps[] = array(
		'status'  => 'detail',
		'message' => authority_mailer_smtp_s( 'elasticmail_onboarding_keys' ),
		'details' => authority_mailer_smtp_settings_summary( $provided_settings ),
	);

	$mm_opts = get_option( 'authority_mailer_smtp_options', array() );
	$mm_opts = is_array( $mm_opts ) ? $mm_opts : array();

	$steps[] = array(
		'status'  => 'detail',
		'message' => authority_mailer_smtp_s( 'elasticmail_mm_opts_keys' ),
		'details' => authority_mailer_smtp_settings_summary( $mm_opts ),
	);

	// Merge provider-scoped settings if present.
	$provider_groups = array( 'elastic', 'elasticmail', 'elastic_mail' );
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
		'elasticmail_api_key',
		'elastic_api_key',
		'apikey',
		'api_key',
		'key',
		'token',
		'elasticmail_from_email',
		'elasticmail_from_name',
		'elastic_from_email',
		'elastic_from_name',
	);

	foreach ( $mm_opts as $k => $v ) {
		if ( isset( $provided_settings[ $k ] ) ) {
			continue;
		}
		if ( in_array( $k, $allowed_keys, true ) || 0 === strpos( $k, 'elasticmail_' ) || 0 === strpos( $k, 'elastic_' ) ) {
			$provided_settings[ $k ] = $v;
		}
	}

	$steps[] = array(
		'status'  => 'detail',
		'message' => authority_mailer_smtp_s( 'elasticmail_final_settings' ),
		'details' => authority_mailer_smtp_settings_summary( $provided_settings ),
	);

	// Candidate API key names — prefer elasticmail-specific first.
	$key_names = array(
		'elasticmail_api_key',
		'elastic_api_key',
		'elastic_key',
		'api_key',
		'apikey',
		'key',
		'token',
	);

	list( $found, $api_key, $found_path ) = authority_mailer_smtp_find_api_key_in_array( $provided_settings, $key_names );

	if ( ! $found || empty( $api_key ) ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => authority_mailer_smtp_s( 'elasticmail_api_key_missing' ),
			'details' => array( 'checked_keys_count' => count( $key_names ) ),
		);

		$steps[] = array(
			'status'  => 'detail',
			'message' => authority_mailer_smtp_s( 'elasticmail_api_key_missing_detail' ),
		);

		return $steps;
	}

	$steps[] = array(
		'status'  => 'info',
		'message' => authority_mailer_smtp_s( 'elasticmail_api_detected' ),
		'details' => array(
			'found_in'   => (string) $found_path,
			'masked_key' => authority_mailer_smtp_mask_key( $api_key ),
		),
	);

	// Hosts to try.
	$hosts_to_try = array( 'api.elasticemail.com' );
	if ( ! empty( $provided_settings['endpoint_host'] ) ) {
		array_unshift( $hosts_to_try, sanitize_text_field( $provided_settings['endpoint_host'] ) );
	}

	$endpoint_ip_override = ! empty( $provided_settings['endpoint_ip'] ) ? trim( (string) $provided_settings['endpoint_ip'] ) : '';

	$resolved_ip = '';
	$used_host   = '';

	foreach ( $hosts_to_try as $host ) {
		$host = (string) $host;

		$steps[] = array(
			'status'  => 'info',
			'message' => sprintf( authority_mailer_smtp_s( 'elasticmail_resolving' ), $host ),
		);

		list( $ip, $debug ) = authority_mailer_smtp_resolve_host_with_doh( $host );

		foreach ( (array) $debug as $d ) {
			$d = trim( (string) $d );
			if ( '' === $d ) {
				continue;
			}
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
				'message' => sprintf( authority_mailer_smtp_s( 'elasticmail_resolved' ), $host, $ip ),
			);

			break;
		}

		$steps[] = array(
			'status'  => 'detail',
			'message' => sprintf( authority_mailer_smtp_s( 'elasticmail_could_not_resolve' ), $host ),
		);
	}

	if ( empty( $resolved_ip ) && ! empty( $endpoint_ip_override ) ) {
		$resolved_ip = $endpoint_ip_override;
		$used_host   = isset( $provided_settings['endpoint_host'] ) ? sanitize_text_field( $provided_settings['endpoint_host'] ) : 'forced-ip';

		$steps[] = array(
			'status'  => 'info',
			'message' => sprintf( authority_mailer_smtp_s( 'elasticmail_using_ip_override' ), $resolved_ip ),
		);
	}

	if ( empty( $resolved_ip ) ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => authority_mailer_smtp_s( 'elasticmail_dns_failed' ),
			'details' => authority_mailer_smtp_s( 'elasticmail_dns_failed_detail' ),
		);

		return $steps;
	}

	// From selection and force toggles (reuse same behavior as tester).
	$force_from_email = authority_mailer_smtp_to_bool(
		! empty( $provided_settings['elasticmail_force_from_email'] ) ? $provided_settings['elasticmail_force_from_email'] :
		( ! empty( $provided_settings['elastic_force_from_email'] ) ? $provided_settings['elastic_force_from_email'] :
		( ! empty( $provided_settings['force_from_email'] ) ? $provided_settings['force_from_email'] : false ) )
	);
	$force_from_name  = authority_mailer_smtp_to_bool(
		! empty( $provided_settings['elasticmail_force_from_name'] ) ? $provided_settings['elasticmail_force_from_name'] :
		( ! empty( $provided_settings['elastic_force_from_name'] ) ? $provided_settings['elastic_force_from_name'] :
		( ! empty( $provided_settings['force_from_name'] ) ? $provided_settings['force_from_name'] : false ) )
	);

	$provider_email_keys = array( 'elasticmail_from_email', 'elastic_from_email', 'elastic_from', 'from_email', 'other_from_email' );
	$provider_name_keys  = array( 'elasticmail_from_name', 'elastic_from_name', 'from_name', 'other_from_name' );

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

	// Candidate selection: only honor provider-specified FROM values when "force" toggles are enabled.
	if ( $force_from_email ) {
		$candidate_from_email = '';
		foreach ( $provider_email_keys as $fk ) {
			if ( ! empty( $provided_settings[ $fk ] ) ) {
				$candidate_from_email = sanitize_email( $provided_settings[ $fk ] );
				break;
			}
		}
		if ( empty( $candidate_from_email ) ) {
			$candidate_from_email = get_option( 'admin_email', '' );
			$steps[]              = array(
				'status'  => 'detail',
				'message' => authority_mailer_smtp_s( 'elasticmail_no_from_fallback' ),
				'details' => (string) $candidate_from_email,
			);
		}
	} else {
		$candidate_from_email = get_option( 'admin_email', '' );
		if ( $provider_has_from_email ) {
			$steps[] = array(
				'status'  => 'detail',
				'message' => authority_mailer_smtp_s( 'provider_ignored_from_email' ),
				'details' => (string) ( isset( $provided_settings['elasticmail_from_email'] ) ? $provided_settings['elasticmail_from_email'] : '' ),
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
				'details' => (string) ( isset( $provided_settings['elasticmail_from_name'] ) ? $provided_settings['elasticmail_from_name'] : '' ),
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
			'message' => authority_mailer_smtp_s( 'elasticmail_using_test_recipient' ),
			'details' => (string) $test_to,
		);
	} else {
		$test_to = get_option( 'admin_email', '' );
		$steps[] = array(
			'status'  => 'detail',
			'message' => authority_mailer_smtp_s( 'elasticmail_using_admin_email' ),
			'details' => (string) $test_to,
		);
	}

	if ( empty( $test_to ) || ! is_email( $test_to ) ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => authority_mailer_smtp_s( 'elasticmail_no_recipient' ),
		);
		return $steps;
	}

	// Log final addresses.
	authority_mailer_smtp_log_final_addresses( $steps, $test_to, $final_from_email, $final_from_name );

	// Prepare endpoint and resolve entry (v4).
	$probe_path        = '/v4/emails/transactional';
	$endpoint_host     = (string) $used_host;
	$endpoint_url_host = 'https://' . $endpoint_host . $probe_path;
	$resolve_entry     = $endpoint_host . ':443:' . $resolved_ip;

	// Build professional test email using centralized template.
	$test_email_args = array(
		'provider'   => 'elasticmail',
		'from_email' => $final_from_email,
		'from_name'  => $final_from_name,
		'to_email'   => $test_to,
	);

	// Use professional template if available, otherwise fallback.
	if ( function_exists( 'authority_mailer_smtp_get_test_email_subject' ) ) {
		$subject = authority_mailer_smtp_get_test_email_subject( 'Elastic Email' );
	} else {
		$subject = isset( $provided_settings['test_subject'] ) ? sanitize_text_field( $provided_settings['test_subject'] ) : ( ( authority_mailer_smtp_s( 'elasticmail_default_subject' ) ? authority_mailer_smtp_s( 'elasticmail_default_subject' ) : 'Authority Mailer Elastic Email Test' ) );
	}

	if ( function_exists( 'authority_mailer_smtp_get_test_email_html' ) ) {
		$body_html = authority_mailer_smtp_get_test_email_html( $test_email_args );
		$body_text = function_exists( 'authority_mailer_smtp_get_test_email_plain' ) ? authority_mailer_smtp_get_test_email_plain( $test_email_args ) : wp_strip_all_tags( $body_html );
	} else {
		$body_html = isset( $provided_settings['html_content'] ) ? (string) $provided_settings['html_content'] : ( ( authority_mailer_smtp_s( 'elasticmail_default_body' ) ? authority_mailer_smtp_s( 'elasticmail_default_body' ) : '<p>Authority Mailer Elastic Email test</p>' ) );
		$body_text = isset( $provided_settings['plain_content'] ) ? (string) $provided_settings['plain_content'] : wp_strip_all_tags( $body_html );
	}

	// Ensure non-empty body_text for provider.
	if ( '' === trim( (string) $body_text ) ) {
		$body_text = 'Authority Mailer test';
	}

	$payload = authority_mailer_smtp_build_elasticmail_payload_v4(
		$final_from_email,
		$final_from_name,
		$test_to,
		$subject,
		$body_html,
		$body_text
	);

	// Build args for JSON POST with API key header.
	$args_post = array(
		'timeout' => 30,
		'headers' => array(
			'Accept'                => 'application/json',
			'Content-Type'          => 'application/json',
			'X-ElasticEmail-ApiKey' => (string) $api_key,
			'Host'                  => $endpoint_host,
		),
		// pass subject so logger can record it in the DB.
		'subject' => (string) $subject,
		'body'    => wp_json_encode( $payload ),
		'curl'    => array( CURLOPT_RESOLVE => array( $resolve_entry ) ),
	);

	if ( ! empty( $provided_settings['allow_insecure'] ) ) {
		$args_post['sslverify'] = false;
		$steps[]                = array(
			'status'  => 'detail',
			'message' => authority_mailer_smtp_s( 'elasticmail_allow_insecure' ),
		);
	}

	$steps[] = array(
		'status'  => 'info',
		'message' => sprintf( authority_mailer_smtp_s( 'elasticmail_attempting_post' ), $endpoint_url_host ),
		'details' => array(
			authority_mailer_smtp_s( 'elasticmail_payload_preview' ) => authority_mailer_smtp_list_keys( (array) $payload ),
		),
	);

	// Use centralized test helper - sends through full wp_mail() pipeline with premium features.
	if ( function_exists( 'authority_mailer_smtp_send_test_via_wpmail' ) ) {
		authority_mailer_smtp_send_test_via_wpmail( 'elasticmail', $test_to, $final_from_email, $final_from_name, $steps );
	} else {
		$steps[] = array(
			'status'  => 'error',
			'message' => 'Centralized test helper function not found. Please update common.php.',
		);
	}

	return $steps;
}

/**
 * Send email via Elastic Email API (v4 transactional).
 *
 * Mirrors the content detection and payload construction used by other provider adapters:
 * - Detects HTML vs plain
 * - Builds HTML and text variants
 * - Ensures recipients, subject and sender are present
 * - Provides multiple compatibility sender/body shapes so content and from name are preserved
 *
 * @param array $email Email data from pre_wp_mail normalized format
 *                     keys: to, subject, message, headers, attachments, content_type
 * @return true|WP_Error
 */
function authority_mailer_smtp_send_elasticmail( $email ) {
	$options = get_option( 'authority_mailer_smtp_options', array() );

	// Get API key (use elasticmail_ prefix, fallback to elastic_).
	$api_key = '';
	if ( ! empty( $options['elasticmail_api_key'] ) ) {
		$api_key = $options['elasticmail_api_key'];
	} elseif ( ! empty( $options['elastic_api_key'] ) ) {
		$api_key = $options['elastic_api_key'];
	}

	if ( empty( $api_key ) ) {
		return new WP_Error( 'missing_api_key', 'Elastic Email API key not configured' );
	}

	// Get from email/name (try elasticmail_ first, then elastic_).
	$from_email = ! empty( $options['elasticmail_from_email'] )
		? $options['elasticmail_from_email']
		: ( ! empty( $options['elastic_from_email'] )
			? $options['elastic_from_email']
			: get_option( 'admin_email' ) );

	$from_name = ! empty( $options['elasticmail_from_name'] )
		? $options['elasticmail_from_name']
		: ( ! empty( $options['elastic_from_name'] )
			? $options['elastic_from_name']
			: get_bloginfo( 'name' ) );

	// sanity fallback.
	if ( '' === trim( (string) $from_name ) ) {
		$from_name = get_bloginfo( 'name' );
	}
	if ( '' === trim( (string) $from_email ) ) {
		$from_email = get_option( 'admin_email', '' );
	}

	// Get recipient (handle both string and array shapes).
	$to       = isset( $email['to'] ) ? $email['to'] : '';
	$to_email = '';
	if ( is_array( $to ) ) {
		// wp_mail may pass an array of strings or an array of recipient arrays.
		$first = reset( $to );
		if ( is_array( $first ) && isset( $first['email'] ) ) {
			$to_email = $first['email'];
		} else {
			$to_email = is_string( $first ) ? $first : '';
		}
	} else {
		$to_email = (string) $to;
	}
	$to_email = sanitize_email( $to_email );

	// Get subject and message.
	$subject      = isset( $email['subject'] ) ? $email['subject'] : '';
	$message      = isset( $email['message'] ) ? $email['message'] : '';
	$content_type = isset( $email['content_type'] ) ? $email['content_type'] : 'text/plain';

	// Auto-detect HTML content (same heuristic as other adapters).
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
		// Plain text email - convert URLs to links for HTML version if present.
		if ( preg_match( '/https?:\/\/[^\s<>"\']+/i', $message ) ) {
			$html = nl2br( esc_html( $message ) );
			$html = preg_replace( '/(https?:\/\/[^\s<>"\']+)/i', '<a href="$1">$1</a>', $html );
			$text = $message;
		} else {
			$text = $message;
		}
	}

	if ( empty( $to_email ) || ! is_email( $to_email ) ) {
		return new WP_Error( 'invalid_recipient', 'No valid recipient email provided' );
	}

	// Build v4 payload (sender placed inside content + compatibility fields).
	$payload = authority_mailer_smtp_build_elasticmail_payload_v4(
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
		if ( ! isset( $payload['Content']['ReplyTo'] ) ) {
			$payload['Content']['ReplyTo'] = $defaults['reply_to'];
		}
	}

	if ( ! empty( $defaults['cc'] ) ) {
		if ( ! isset( $payload['Recipients']['CC'] ) ) {
			$payload['Recipients']['CC'] = array();
		}
		foreach ( $defaults['cc'] as $cc_email ) {
			$payload['Recipients']['CC'][] = $cc_email;
		}
	}

	if ( ! empty( $defaults['bcc'] ) ) {
		if ( ! isset( $payload['Recipients']['BCC'] ) ) {
			$payload['Recipients']['BCC'] = array();
		}
		foreach ( $defaults['bcc'] as $bcc_email ) {
			$payload['Recipients']['BCC'][] = $bcc_email;
		}
	}

	// Add priority headers.
	if ( ! empty( $defaults['priority'] ) ) {
		if ( ! isset( $payload['Content']['Headers'] ) ) {
			$payload['Content']['Headers'] = array();
		}
		$priority_headers = authority_mailer_smtp_get_priority_headers( $defaults['priority'] );
		foreach ( $priority_headers as $priority_header ) {
			$payload['Content']['Headers'][ $priority_header['name'] ] = $priority_header['value'];
		}
	}

	// Add custom headers.
	if ( ! empty( $defaults['custom'] ) ) {
		if ( ! isset( $payload['Content']['Headers'] ) ) {
			$payload['Content']['Headers'] = array();
		}
		foreach ( $defaults['custom'] as $custom_header ) {
			$payload['Content']['Headers'][ $custom_header['name'] ] = $custom_header['value'];
		}
	}

	// Add Return-Path header if provided.
	if ( ! empty( $defaults['return_path'] ) ) {
		if ( ! isset( $payload['Content']['Headers'] ) ) {
			$payload['Content']['Headers'] = array();
		}
		$payload['Content']['Headers']['Return-Path'] = $defaults['return_path'];
	}

	// Debug logging for email defaults added to payload.
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
		error_log(
			'[Authority Mailer ElasticEmail] Email defaults added to payload | Reply-To: ' .
			( ! empty( $defaults['reply_to'] ) ? $defaults['reply_to'] : 'none' ) .
			' | CC count: ' . count( $defaults['cc'] ) .
			' | BCC count: ' . count( $defaults['bcc'] ) .
			' | Priority: ' . ( ! empty( $defaults['priority'] ) ? $defaults['priority'] : 'none' ) .
			' | Return-Path: ' . ( ! empty( $defaults['return_path'] ) ? $defaults['return_path'] : 'none' )
		);
	}

	// Add tracking ID to payload if present (for webhook correlation).
	if ( ! empty( $email['tracking_id'] ) ) {
		// ElasticEmail supports customid for tracking.
		$payload['Options']['CustomID'] = $email['tracking_id'];

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
			error_log( '[Authority Mailer ElasticEmail] Added tracking ID to payload: ' . $email['tracking_id'] );
		}
	}

	// Log the email attempt.
	$log_id = 0;
	if ( function_exists( 'authority_mailer_smtp_log_email_with_spam_score' ) ) {
		$log_data = array(
			'provider'   => 'elasticmail',
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

	// Make API call to v4 endpoint with header-based API key.
	$response = wp_remote_post(
		'https://api.elasticemail.com/v4/emails/transactional',
		array(
			'headers' => array(
				'X-ElasticEmail-ApiKey' => $api_key,
				'Content-Type'          => 'application/json',
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
				'provider'      => 'elasticmail',
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
				'provider'      => 'elasticmail',
				'error_code'    => $code,
				'error_message' => $body,
				'email_data'    => $email,
			)
		);

		return new WP_Error( 'elasticmail_error', 'Elastic Email API error: ' . $body, array( 'code' => $code ) );
	}

	return true;
}

// Alias for backward compatibility.
function authority_mailer_smtp_send_elastic( $email ) {
	return authority_mailer_smtp_send_elasticmail( $email );
}
