<?php
/**
 * common.php
 *
 * Shared helper utilities for provider adapters in Authority Mailer.
 *
 * Centralizes:
 *  - API key detection across nested option shapes
 *  - DNS resolution with DoH fallback
 *  - Masking and boolean normalization
 *  - Safe logging helpers for final recipient/from
 *  - HTTP wrappers that automatically log attempts/responses to the email logger
 *
 * This version hardens the HTTP wrappers so they:
 *  - normalize/validate their incoming parameters (guard against adapters that pass
 *    payload and headers in the wrong order),
 *  - extract subject and full body content from payloads for storage in the `body`
 *    column, and
 *  - include optional debug logging (WP_DEBUG).
 *
 * Include with: require_once AUTHORITY_MAILER_PLUGIN_DIR . 'includes/providers/common.php';
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Ensure the email-logger helper is available.
 */
$authority_mailer_logger_file = AUTHORITY_MAILER_PLUGIN_DIR . 'includes/email-logger.php';
if ( file_exists( $authority_mailer_logger_file ) ) {
	require_once $authority_mailer_logger_file;
}

/**
 * Helper for debug messages. Do NOT call error_log directly in this file to avoid
 * static-analysis flags. Instead trigger an action that integrators can hook to.
 *
 * @param string $message
 */
function authority_mailer_smtp_debug_log( $message ) {
	/**
	 * Action: authority_mailer_debug
	 * Fires with debug messages from Authority Mailer internals. Integrators can hook
	 * and forward to a logger if desired.
	 *
	 * @param string $message Debug message
	 */
	do_action( 'authority_mailer_smtp_debug', (string) $message );
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
	// Intentionally do not call error_log() here to avoid static-analysis warnings.
}

/**
 * Alias for authority_mailer_smtp_debug_log for backward compatibility.
 * Some code was calling authority_mailer_debug_log without the _smtp prefix.
 *
 * @param string $message Debug message
 */
if ( ! function_exists( 'authority_mailer_debug_log' ) ) {
	function authority_mailer_debug_log( $message ) {
		authority_mailer_smtp_debug_log( $message );
	}
}

/**
 * General logging function for Authority Mailer.
 * Alias to debug log function.
 *
 * @param string $message Log message
 */
if ( ! function_exists( 'authority_mailer_log' ) ) {
	function authority_mailer_log( $message ) {
		authority_mailer_smtp_debug_log( $message );
	}
}

if ( ! function_exists( 'authority_mailer_smtp_s' ) ) {
	/**
	 * Safe accessor for localized onboarding strings.
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
 * Mask an API key for logs (show start/end only).
 *
 * @param string $val
 * @return string
 */
function authority_mailer_smtp_mask_key( $val ) {
	if ( empty( $val ) ) {
		return '';
	}
	$val = (string) $val;
	$len = strlen( $val );
	if ( $len <= 8 ) {
		return str_repeat( '*', $len );
	}
	return substr( $val, 0, 4 ) . '...' . substr( $val, -4 );
}

/**
 * Normalize common truthy values to bool.
 *
 * @param mixed $val
 * @return bool
 */
function authority_mailer_smtp_to_bool( $val ) {
	if ( is_bool( $val ) ) {
		return $val;
	}
	if ( is_int( $val ) ) {
		return 1 === $val;
	}
	$val = strtolower( trim( (string) $val ) );
	return in_array( $val, array( '1', 'true', 'on', 'yes' ), true );
}

/**
 * Return sorted top-level keys for diagnostics.
 *
 * @param mixed $arr
 * @return array
 */
function authority_mailer_smtp_list_keys( $arr ) {
	if ( ! is_array( $arr ) ) {
		return array();
	}
	$keys = array_keys( $arr );
	sort( $keys );
	return $keys;
}

/**
 * Search for an API key within various shapes of settings arrays.
 *
 * Looks in top-level keys, vendor group, connections.*, providers.*.
 *
 * @param array $settings
 * @param array $key_names candidate key names
 * @return array [ bool $found, string $value, string $path ]
 */
function authority_mailer_smtp_find_api_key_in_array( $settings, $key_names ) {
	if ( ! is_array( $settings ) ) {
		return array( false, '', '' );
	}

	// top-level keys.
	foreach ( $key_names as $kn ) {
		if ( isset( $settings[ $kn ] ) && '' !== trim( (string) $settings[ $kn ] ) ) {
			return array( true, trim( (string) $settings[ $kn ] ), $kn );
		}
	}

	// vendor group (e.g. $settings['sendlayer'] or $settings['mandrill'])
	foreach ( $settings as $k => $v ) {
		if ( is_array( $v ) ) {
			foreach ( $key_names as $kn ) {
				if ( isset( $v[ $kn ] ) && '' !== trim( (string) $v[ $kn ] ) ) {
					return array( true, trim( (string) $v[ $kn ] ), "{$k}.{$kn}" );
				}
			}
		}
	}

	// connections group.
	if ( isset( $settings['connections'] ) && is_array( $settings['connections'] ) ) {
		foreach ( $settings['connections'] as $conn_key => $conn_val ) {
			if ( is_array( $conn_val ) ) {
				foreach ( $key_names as $kn ) {
					if ( isset( $conn_val[ $kn ] ) && '' !== trim( (string) $conn_val[ $kn ] ) ) {
						return array( true, trim( (string) $conn_val[ $kn ] ), "connections.{$conn_key}.{$kn}" );
					}
				}
			}
		}
	}

	// providers group.
	if ( isset( $settings['providers'] ) && is_array( $settings['providers'] ) ) {
		foreach ( $settings['providers'] as $pname => $pval ) {
			if ( is_array( $pval ) ) {
				foreach ( $key_names as $kn ) {
					if ( isset( $pval[ $kn ] ) && '' !== trim( (string) $pval[ $kn ] ) ) {
						return array( true, trim( (string) $pval[ $kn ] ), "providers.{$pname}.{$kn}" );
					}
				}
			}
		}
	}

	return array( false, '', '' );
}

/**
 * Recursively mask options for debug output (hides keys/secrets).
 *
 * @param mixed $arr
 * @return array
 */
function authority_mailer_smtp_mask_options_for_debug( $arr ) {
	$out = array();
	foreach ( (array) $arr as $k => $v ) {
		$key_lc = strtolower( (string) $k );
		if ( is_array( $v ) ) {
			$out[ $k ] = authority_mailer_smtp_mask_options_for_debug( $v );
		} else {
			$val = (string) $v;
			if ( strpos( $key_lc, 'secret' ) !== false || strpos( $key_lc, 'key' ) !== false || strpos( $key_lc, 'token' ) !== false || strpos( $key_lc, 'password' ) !== false ) {
				$out[ $k ] = authority_mailer_smtp_mask_key( $val );
			} else {
				$out[ $k ] = $val;
			}
		}
	}
	return $out;
}

/**
 * Simple settings summary used by provider testers.
 *
 * @param mixed $settings
 * @return array
 */
function authority_mailer_smtp_settings_summary( $settings ) {
	$settings = is_array( $settings ) ? $settings : array();
	$summary  = array(
		'count'    => count( $settings ),
		'has_key'  => false,
		'key_mask' => '',
	);

	// Check for API keys first
	$api_key_candidates = array( 'api_key', 'key', 'token' );
	foreach ( $api_key_candidates as $k ) {
		if ( ! empty( $settings[ $k ] ) ) {
			$summary['has_key']         = true;
			$summary['key_mask']        = authority_mailer_smtp_mask_key( $settings[ $k ] );
			$summary['credential_type'] = 'api';
			break;
		}
	}

	// If no API key found, check for SMTP credentials
	if ( ! $summary['has_key'] ) {
		// Common SMTP credential keys to check
		$smtp_password_keys = array( 'smtp_password', 'password' );

		// Also check provider-specific variants (e.g., brevo_smtp_password, mailgun_smtp_password)
		foreach ( $settings as $key => $value ) {
			if ( preg_match( '/^[a-z0-9]+_smtp_password$/i', $key ) ) {
				$smtp_password_keys[] = $key;
			}
		}

		$found_password = '';

		foreach ( $smtp_password_keys as $k ) {
			if ( ! empty( $settings[ $k ] ) && '' !== trim( (string) $settings[ $k ] ) ) {
				$found_password = trim( (string) $settings[ $k ] );
				break;
			}
		}

		// SMTP credentials are valid if we have a password
		if ( '' !== $found_password ) {
			$summary['has_key']         = true;
			$summary['key_mask']        = authority_mailer_smtp_mask_key( $found_password );
			$summary['credential_type'] = 'smtp';
		}
	}

	return $summary;
}

/**
 * Resolve a host: try system resolver then DoH (Cloudflare then Google).
 *
 * Returns [ $ip_or_empty, $debug_msgs_array ].
 *
 * Uses a transient cache to avoid repeated DoH calls.
 *
 * @param string $host
 * @return array
 */
function authority_mailer_smtp_resolve_host_with_doh( $host ) {
	$debug       = array();
	$resolved_ip = '';

	$host = (string) $host;
	if ( empty( $host ) ) {
		$debug[] = 'Empty host supplied';
		return array( '', $debug );
	}

	$cache_key = 'authority_mailer_smtp_doh_' . md5( $host );
	$cached    = get_transient( $cache_key );
	if ( $cached && is_array( $cached ) && isset( $cached['ip'] ) ) {
		$debug[] = sprintf( 'Cached resolver returned: %s', $cached['ip'] );
		$debug   = array_merge( $debug, (array) $cached['debug'] );
		return array( $cached['ip'], $debug );
	}

	// System resolver.
	$system_ip = false;
	if ( function_exists( 'gethostbynamel' ) ) {
		$ips = @gethostbynamel( $host );
		if ( is_array( $ips ) && ! empty( $ips ) ) {
			$system_ip = $ips[0];
		}
	} else {
		$res = @gethostbyname( $host );
		if ( $res && $res !== $host ) {
			$system_ip = $res;
		}
	}

	if ( $system_ip ) {
		$resolved_ip = $system_ip;
		$debug[]     = sprintf( 'System resolver returned: %s', $resolved_ip );
		set_transient(
			$cache_key,
			array(
				'ip'    => $resolved_ip,
				'debug' => $debug,
			),
			10 * MINUTE_IN_SECONDS
		);
		return array( $resolved_ip, $debug );
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
			if ( json_last_error() === JSON_ERROR_NONE && ! empty( $j['Answer'] ) && is_array( $j['Answer'] ) ) {
				foreach ( $j['Answer'] as $ans ) {
					if ( isset( $ans['data'] ) && filter_var( $ans['data'], FILTER_VALIDATE_IP ) ) {
						$resolved_ip = $ans['data'];
						$debug[]     = sprintf( 'Cloudflare DoH returned: %s', $resolved_ip );
						set_transient(
							$cache_key,
							array(
								'ip'    => $resolved_ip,
								'debug' => $debug,
							),
							10 * MINUTE_IN_SECONDS
						);
						return array( $resolved_ip, $debug );
					}
				}
			} else {
				$debug[] = 'Cloudflare DoH returned non-JSON or missing Answer';
			}
		} else {
			$debug[] = 'Cloudflare DoH request failed or non-200 response';
		}
	} catch ( Exception $e ) {
		$debug[] = 'Cloudflare DoH exception: ' . $e->getMessage();
		authority_mailer_debug_log( 'Cloudflare DoH exception: ' . $e->getMessage() );
	}

	// Google DoH fallback.
	try {
		$g = wp_remote_get( 'https://dns.google/resolve?name=' . rawurlencode( $host ) . '&type=A', array( 'timeout' => 8 ) );
		if ( ! is_wp_error( $g ) && wp_remote_retrieve_response_code( $g ) === 200 ) {
			$j = json_decode( wp_remote_retrieve_body( $g ), true );
			if ( json_last_error() === JSON_ERROR_NONE && ! empty( $j['Answer'] ) && is_array( $j['Answer'] ) ) {
				foreach ( $j['Answer'] as $ans ) {
					if ( isset( $ans['data'] ) && filter_var( $ans['data'], FILTER_VALIDATE_IP ) ) {
						$resolved_ip = $ans['data'];
						$debug[]     = sprintf( 'Google DoH returned: %s', $resolved_ip );
						set_transient(
							$cache_key,
							array(
								'ip'    => $resolved_ip,
								'debug' => $debug,
							),
							10 * MINUTE_IN_SECONDS
						);
						return array( $resolved_ip, $debug );
					}
				}
			} else {
				$debug[] = 'Google DoH returned non-JSON or missing Answer';
			}
		} else {
			$debug[] = 'Google DoH request failed or non-200 response';
		}
	} catch ( Exception $e ) {
		$debug[] = 'Google DoH exception: ' . $e->getMessage();
		authority_mailer_debug_log( 'Google DoH exception: ' . $e->getMessage() );
	}

	// cache negative result.
	set_transient(
		$cache_key,
		array(
			'ip'    => '',
			'debug' => $debug,
		),
		5 * MINUTE_IN_SECONDS
	);

	return array( $resolved_ip, $debug );
}

/**
 * Mask headers for DB storage. Masks Authorization / Bearer / Token-like values.
 *
 * @param array|string $headers
 * @return string JSON-encoded masked headers (string)
 */
function authority_mailer_smtp_mask_headers_for_db( $headers ) {
	$masked = array();

	if ( is_array( $headers ) ) {
		foreach ( $headers as $k => $v ) {
			$key_lc = strtolower( (string) $k );
			$val    = is_array( $v ) ? implode( ', ', $v ) : (string) $v;
			if ( strpos( $key_lc, 'auth' ) !== false || strpos( $key_lc, 'authorization' ) !== false || strpos( $key_lc, 'token' ) !== false || preg_match( '/bearer/i', $val ) ) {
				$masked[ $k ] = authority_mailer_smtp_mask_key( preg_replace( '/^.*\s+/', '', $val ) );
			} else {
				$masked[ $k ] = $val;
			}
		}
	} elseif ( is_string( $headers ) && ! empty( $headers ) ) {
		$lines = preg_split( "/\r\n|\n|\r/", $headers );
		foreach ( $lines as $line ) {
			if ( strpos( $line, ':' ) !== false ) {
				list( $k, $v ) = explode( ':', $line, 2 );
				$k             = trim( $k );
				$v             = trim( $v );
				$key_lc        = strtolower( $k );
				if ( strpos( $key_lc, 'auth' ) !== false || strpos( $key_lc, 'authorization' ) !== false || strpos( $key_lc, 'token' ) !== false || preg_match( '/bearer/i', $v ) ) {
					$masked[ $k ] = authority_mailer_smtp_mask_key( preg_replace( '/^.*\s+/', '', $v ) );
				} else {
					$masked[ $k ] = $v;
				}
			}
		}
	}

	return wp_json_encode( $masked );
}

/*
------------------------------------------------------------------
 * Helpers to detect whether a given value is likely a payload or headers
 * This allows the wrapper to be robust if adapters accidentally swap args.
 * ------------------------------------------------------------------ */

/**
 * Heuristic: is this value likely a payload (array or JSON string describing message)?
 *
 * @param mixed $v
 * @return bool
 */
function authority_mailer_smtp_is_payload_like( $v ) {
	if ( is_array( $v ) ) {
		// common payload indicators.
		$keys       = array_keys( $v );
		$keys_lc    = array_map( 'strtolower', $keys );
		$indicators = array( 'message', 'subject', 'html', 'text', 'content', 'from', 'to', 'personalizations', 'email' );
		foreach ( $indicators as $i ) {
			if ( in_array( $i, $keys_lc, true ) ) {
				return true;
			}
		}
		// arrays of parts are also payload-like.
		if ( ! empty( $keys ) && array_keys( $v ) === range( 0, count( $v ) - 1 ) ) {
			// numeric array - if first element has 'value' or 'type' keys it's payload-like.
			$first = reset( $v );
			if ( is_array( $first ) ) {
				$fk = array_map( 'strtolower', array_keys( $first ) );
				if ( in_array( 'value', $fk, true ) || in_array( 'type', $fk, true ) || in_array( 'html', $fk, true ) ) {
					return true;
				}
			}
		}
		return false;
	}

	if ( is_string( $v ) ) {
		// JSON string?
		$trim = trim( $v );
		if ( ( strpos( $trim, '{' ) === 0 ) || ( strpos( $trim, '[' ) === 0 ) ) {
			$dec = json_decode( $v, true );
			if ( is_array( $dec ) ) {
				return authority_mailer_smtp_is_payload_like( $dec );
			}
		}
	}

	return false;
}

/**
 * Heuristic: is this value likely headers (array with header names or header string)?
 *
 * @param mixed $v
 * @return bool
 */
function authority_mailer_smtp_is_headers_like( $v ) {
	if ( is_array( $v ) ) {
		foreach ( array_keys( $v ) as $k ) {
			$kl = strtolower( (string) $k );
			// common header keys.
			if ( in_array( $kl, array( 'authorization', 'content-type', 'accept', 'user-agent', 'x-api-key' ), true ) ) {
				return true;
			}
		}
		// if keys are all numeric and values look like "Key: value" strings, treat as header-like.
		if ( ! empty( $v ) && array_keys( $v ) === range( 0, count( $v ) - 1 ) ) {
			foreach ( $v as $line ) {
				if ( is_string( $line ) && strpos( $line, ':' ) !== false ) {
					return true;
				}
			}
		}
		return false;
	}

	if ( is_string( $v ) ) {
		// header string contains "Key: value" lines.
		if ( strpos( $v, ':' ) !== false && ( strpos( strtolower( $v ), 'content-type' ) !== false || strpos( strtolower( $v ), 'authorization' ) !== false ) ) {
			return true;
		}
	}

	return false;
}

/*
------------------------------------------------------------------
 * Subject/body extractor (returns sanitized full body up to a max length)
 * ------------------------------------------------------------------ */

if ( ! defined( 'AUTHORITY_MAILER_MAX_BODY_LENGTH' ) ) {
	define( 'AUTHORITY_MAILER_MAX_BODY_LENGTH', 20000 ); // 20k characters max stored in body column
}

/**
 * Helper: try to extract a subject and full body content from a payload structure.
 *
 * Returns array( $subject, $full_body ).
 */
function authority_mailer_smtp_extract_subject_and_body_from_payload( $payload ) {
	$subject   = '';
	$full_body = '';

	// normalize payload if string JSON.
	if ( is_string( $payload ) && strlen( $payload ) ) {
		$decoded = @json_decode( $payload, true );
		if ( is_array( $decoded ) ) {
			$payload = $decoded;
		}
	}

	$subject_candidates = array();
	$body_candidates    = array();

	$walk = function ( $node ) use ( &$walk, &$subject_candidates, &$body_candidates ) {
		if ( is_array( $node ) ) {
			$numeric = array_keys( $node ) === range( 0, count( $node ) - 1 );
			if ( $numeric ) {
				foreach ( $node as $item ) {
					$walk( $item );
				}
				return;
			}
			foreach ( $node as $k => $v ) {
				$key_lc = strtolower( (string) $k );
				// subject candidate keys.
				if ( preg_match( '/subject|title/', $key_lc ) ) {
					if ( is_string( $v ) && strlen( trim( $v ) ) ) {
						$subject_candidates[] = (string) $v;
					}
				}
				// common body keys or containers.
				if ( preg_match( '/^(html|htmlcontent|body|text|message|content|plain_content|htmlbody|html_body)$/i', $k ) ) {
					if ( is_string( $v ) && strlen( trim( $v ) ) ) {
						$body_candidates[] = (string) $v;
					} elseif ( is_array( $v ) ) {
						foreach ( $v as $part ) {
							if ( is_array( $part ) ) {
								if ( isset( $part['value'] ) && is_string( $part['value'] ) && strlen( trim( $part['value'] ) ) ) {
									$body_candidates[] = (string) $part['value'];
								} elseif ( isset( $part['html'] ) && is_string( $part['html'] ) ) {
									$body_candidates[] = (string) $part['html'];
								} elseif ( isset( $part['text'] ) && is_string( $part['text'] ) ) {
									$body_candidates[] = (string) $part['text'];
								}
							} elseif ( is_string( $part ) && strlen( trim( $part ) ) ) {
								$body_candidates[] = (string) $part;
							}
						}
					}
				}
				// direct scalar strings anywhere are potential body fallback.
				if ( is_string( $v ) && strlen( trim( $v ) ) ) {
					$body_candidates[] = (string) $v;
				} elseif ( is_array( $v ) || is_object( $v ) ) {
					$walk( $v );
				}
			}
		} elseif ( is_object( $node ) ) {
			$walk( (array) $node );
		} elseif ( is_string( $node ) && strlen( trim( $node ) ) ) {
			$body_candidates[] = (string) $node;
		}
	};

	$walk( $payload );

	// pick subject.
	if ( ! empty( $subject_candidates ) ) {
		foreach ( $subject_candidates as $s ) {
			if ( strlen( trim( $s ) ) ) {
				$subject = (string) $s;
				break;
			}
		}
	}
	if ( empty( $subject ) && is_array( $payload ) && ! empty( $payload['subject'] ) ) {
		$subject = (string) $payload['subject'];
	}
	$subject = $subject ? mb_substr( sanitize_text_field( $subject ), 0, 255 ) : '';

	// choose best body candidate: prefer HTML-like and longest after stripping tags.
	$best       = '';
	$best_score = 0;
	foreach ( $body_candidates as $c ) {
		$c_trim = trim( (string) $c );
		if ( '' === $c_trim ) {
			continue;
		}
		$is_html = (bool) preg_match( '/<\s*\/?\w+.*?>/', $c_trim );
		$len     = mb_strlen( wp_strip_all_tags( $c_trim ) );
		$score   = $len + ( $is_html ? 10000 : 0 );
		if ( $score > $best_score ) {
			$best_score = $score;
			$best       = $c_trim;
		}
	}

	// fallback: if payload is scalar string.
	if ( empty( $best ) && is_string( $payload ) ) {
		$best = trim( $payload );
	}

	if ( '' !== $best ) {
		if ( preg_match( '/<\s*\/?\w+.*?>/', $best ) ) {
			$san = wp_kses_post( $best );
		} else {
			$san = wp_strip_all_tags( $best );
		}
		$full_body = mb_substr( (string) $san, 0, AUTHORITY_MAILER_MAX_BODY_LENGTH );
	} else {
		$full_body = '';
	}

	return array( $subject, $full_body );
}

/*
------------------------------------------------------------------
 * Robust HTTP wrappers
 * ------------------------------------------------------------------ */

/**
 * Perform a POST request and log attempt + response.
 *
 * Robustness features:
 *  - If payload/headers were accidentally swapped by an adapter, we attempt to detect
 *    and correct the order using heuristics (authority_mailer_smtp_is_payload_like and authority_mailer_smtp_is_headers_like).
 *  - We extract subject and full email content from the original payload and store
 *    them in the subject/body columns at insert time.
 *
 * Signature:
 *   authority_mailer_smtp_http_post_and_log( $url, $args, $provider, $payload, $to, $from, $from_name, $headers );
 *
 * Adapters must pass the raw/original payload (array or JSON string) as the $payload param.
 *
 * @return array|WP_Error wp_remote_post response.
 */
function authority_mailer_smtp_http_post_and_log( $url, $args, $provider = '', $payload = null, $to = '', $from = '', $from_name = '', $headers = null, $spam_score = null ) {
	// Normalize common mistakes: some adapters passed headers where payload should be, or vice versa.
	// Detect and swap if necessary.
	try {
		$payload_like = authority_mailer_smtp_is_payload_like( $payload );
		$headers_like = authority_mailer_smtp_is_headers_like( $headers );

		// If payload is not payload-like but headers param looks payload-like, swap them.
		if ( ! $payload_like && authority_mailer_smtp_is_payload_like( $headers ) ) {
			$tmp     = $payload;
			$payload = $headers;
			$headers = $tmp;
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				authority_mailer_debug_log( '[Authority Mailer DEBUG] authority_mailer_http_post_and_log: swapped payload and headers (adapter likely passed them in wrong order).' );
			}
		} else {
			// Another common mistake: adapter passed masked/mangled payload as headers and provided headers in $payload.
			// If payload looks header-like and headers look payload-like, swap.
			if ( authority_mailer_smtp_is_headers_like( $payload ) && authority_mailer_smtp_is_payload_like( $headers ) ) {
				$tmp     = $payload;
				$payload = $headers;
				$headers = $tmp;
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
					authority_mailer_debug_log( '[Authority Mailer DEBUG] authority_mailer_http_post_and_log: swapped payload/headers by second heuristic.' );
				}
			}
		}
	} catch ( Exception $e ) {
		// If heuristics fail for any reason, proceed without swapping.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			authority_mailer_debug_log( '[Authority Mailer DEBUG] authority_mailer_http_post_and_log: heuristic exception: ' . $e->getMessage() );
		}
	}

	// Ensure table exists.

	// Prepare masked payload for DB (payload column).
	$payload_for_db = $payload;
	if ( is_array( $payload_for_db ) || is_object( $payload_for_db ) ) {
		$payload_for_db = authority_mailer_smtp_mask_options_for_debug( (array) $payload_for_db );
	} elseif ( is_string( $payload_for_db ) ) {
			$decoded = json_decode( $payload_for_db, true );
		if ( is_array( $decoded ) ) {
			$payload_for_db = authority_mailer_smtp_mask_options_for_debug( $decoded );
		} else {
			$payload_for_db = substr( $payload_for_db, 0, 2000 );
		}
	}

	// Headers to save (masked).
	$headers_for_db = $headers;
	if ( is_null( $headers_for_db ) && isset( $args['headers'] ) ) {
		$headers_for_db = $args['headers'];
	}
	$headers_json = authority_mailer_smtp_mask_headers_for_db( $headers_for_db );

	// Extract subject and full body from the original payload (unmasked/original).
	list( $extracted_subject, $extracted_full_body ) = authority_mailer_smtp_extract_subject_and_body_from_payload( $payload );

	$subject_for_db = '';
	if ( ! empty( $extracted_subject ) ) {
		$subject_for_db = $extracted_subject;
	} elseif ( isset( $args['subject'] ) && '' !== $args['subject'] ) {
		$subject_for_db = sanitize_text_field( $args['subject'] );
	}

	$body_for_db = '';
	if ( ! empty( $extracted_full_body ) ) {
		$body_for_db = $extracted_full_body;
	} else {
		// fallback to args['body'] (raw body) if provided by adapter.
		if ( isset( $args['body'] ) && is_string( $args['body'] ) && '' !== $args['body'] ) {
			$raw = $args['body'];
			if ( preg_match( '/<\s*\/?\w+.*?>/', $raw ) ) {
				$body_for_db = mb_substr( wp_kses_post( $raw ), 0, AUTHORITY_MAILER_MAX_BODY_LENGTH );
			} else {
				$body_for_db = mb_substr( wp_strip_all_tags( $raw ), 0, AUTHORITY_MAILER_MAX_BODY_LENGTH );
			}
		}
	}

	// Debug preview of what we will insert (masked).
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		$preview = array(
			'provider'        => $provider,
			'to'              => $to,
			'from'            => $from,
			'from_name'       => $from_name,
			'subject'         => $subject_for_db,
			'body_preview'    => mb_substr( wp_strip_all_tags( $body_for_db ), 0, 400 ),
			'payload_preview' => is_array( $payload ) ? authority_mailer_smtp_mask_options_for_debug( (array) $payload ) : ( is_string( $payload ) ? mb_substr( $payload, 0, 400 ) : '' ),
			'headers_preview' => authority_mailer_smtp_mask_headers_for_db( $headers_for_db ),
		);
		authority_mailer_debug_log( '[Authority Mailer DEBUG] authority_mailer_http_post_and_log pre-insert: ' . wp_json_encode( $preview ) );
	}

	$insert_data = array(
		'provider'   => $provider,
		'to_email'   => $to,
		'from_email' => $from,
		'from_name'  => (string) $from_name,
		'subject'    => $subject_for_db,
		'headers'    => $headers_json,
		'body'       => $body_for_db,
		'payload'    => is_array( $payload_for_db ) ? wp_json_encode( $payload_for_db ) : (string) $payload_for_db,
		'status'     => 'attempt',
	);

	// Add spam score if provided.
	if ( null !== $spam_score ) {
		$insert_data['spam_score'] = floatval( $spam_score );
	}

	$log_id = 0;
	if ( function_exists( 'authority_mailer_smtp_email_logger_insert' ) ) {
		$log_id = authority_mailer_smtp_email_logger_insert( $insert_data );
	}

	// Perform the actual request.
	$response = wp_remote_post( $url, $args );

	// Update log row with response/status.
	if ( function_exists( 'authority_mailer_smtp_email_logger_update' ) && $log_id ) {
		if ( is_wp_error( $response ) ) {
			$err = $response->get_error_message();
			authority_mailer_smtp_email_logger_update(
				$log_id,
				array(
					'status'        => 'error',
					'response_code' => 0,
					'response_body' => $err,
				)
			);
			return $response;
		}

		$code  = intval( wp_remote_retrieve_response_code( $response ) );
		$body  = wp_remote_retrieve_body( $response );
		$short = is_string( $body ) ? substr( $body, 0, 2000 ) : '';

		// Try decode JSON.
		$decoded = null;
		if ( is_string( $body ) && strlen( $body ) ) {
			$decoded = @json_decode( $body, true );
		}

		$logical_status = 'error';

		if ( is_array( $decoded ) ) {
			// explicit provider failure.
			if ( isset( $decoded['success'] ) && false === (bool) $decoded['success'] ) {
				$logical_status = 'error';
			} elseif ( isset( $decoded['success'] ) && true === (bool) $decoded['success'] ) {
				$logical_status = 'accepted';
			} elseif ( ! empty( $decoded['error'] ) || ! empty( $decoded['errors'] ) ) {
				$logical_status = 'error';
			} else {
				// check for message id keys.
				$message_id_keys = array( 'MessageID', 'MessageId', 'messageId', 'message_id', 'id' );
				$found_mid       = false;
				foreach ( $message_id_keys as $k ) {
					if ( isset( $decoded[ $k ] ) && ! empty( $decoded[ $k ] ) ) {
						$found_mid = true;
						break;
					}
				}
				if ( $found_mid ) {
					$logical_status = 'accepted';
				} elseif ( $code >= 200 && $code < 300 ) {
					// 2xx but no explicit success or mid -> consider accepted (queue).
					$logical_status = 'accepted';
				} else {
					$logical_status = 'error';
				}
			}
		} else {
			// non-JSON: fall back to HTTP code.
			if ( $code >= 200 && $code < 300 ) {
				$logical_status = 'accepted';
			} else {
				$logical_status = 'error';
			}
		}

		authority_mailer_smtp_email_logger_update(
			$log_id,
			array(
				'status'        => $logical_status,
				'response_code' => $code,
				'response_body' => $short,
			)
		);
	}

	return $response;
}

/**
 * Log final addresses (To/From) in steps array (safe, avoids printing secret values).
 *
 * @param array  $steps reference
 * @param string $to
 * @param string $from_email
 * @param string $from_name
 */
function authority_mailer_smtp_log_final_addresses( &$steps, $to, $from_email, $from_name ) {
	$steps[] = array(
		'status'  => 'detail',
		'message' => 'Final transmission addresses',
		'details' => array(
			'to'         => $to,
			'from_email' => $from_email,
			'from_name'  => $from_name,
		),
	);
}

/**
 * Get the professional test email subject line.
 *
 * @param string $provider Optional provider name to include.
 * @return string
 */
function authority_mailer_smtp_get_test_email_subject( $provider = '' ) {
	$site_name = get_bloginfo( 'name' );
	if ( empty( $site_name ) ) {
		$site_name = 'Your Website';
	}

	$provider_text = '';
	if ( ! empty( $provider ) ) {
		$provider_text = ' via ' . ucfirst( $provider );
	}

	return sprintf( '✓ Email Configuration Test%s - %s', $provider_text, $site_name );
}

/**
 * Get the professional HTML test email body.
 * This creates a branded, professional-looking test email that clearly indicates
 * the email system is working correctly.
 *
 * @param array $args {
 *     Optional. Arguments for customizing the test email.
 *
 *     @type string $provider     Provider name (e.g., 'SendGrid', 'SMTP2GO').
 *     @type string $from_email   The from email address.
 *     @type string $from_name    The from name.
 *     @type string $to_email     The recipient email address.
 *     @type string $site_name    Site name override.
 *     @type string $site_url     Site URL override.
 * }
 * @return string HTML email body.
 */
function authority_mailer_smtp_get_test_email_html( $args = array() ) {
	$defaults = array(
		'provider'   => '',
		'from_email' => '',
		'from_name'  => '',
		'to_email'   => '',
		'site_name'  => get_bloginfo( 'name' ),
		'site_url'   => home_url(),
	);

	$args = wp_parse_args( $args, $defaults );

	if ( empty( $args['site_name'] ) ) {
		$args['site_name'] = 'Your Website';
	}

	$provider_display = ! empty( $args['provider'] ) ? ucfirst( $args['provider'] ) : 'SMTP';
	$current_time     = current_time( 'F j, Y \a\t g:i A' );
	$year             = gmdate( 'Y' );

	// Build configuration details.
	$config_details = '';
	if ( ! empty( $args['provider'] ) ) {
		$config_details .= '<tr><td style="padding:8px 12px;border-bottom:1px solid #e5e7eb;color:#6b7280;font-size:13px;width:140px;">Provider</td><td style="padding:8px 12px;border-bottom:1px solid #e5e7eb;font-size:13px;font-weight:600;">' . esc_html( ucfirst( $args['provider'] ) ) . '</td></tr>';
	}
	if ( ! empty( $args['from_email'] ) ) {
		$config_details .= '<tr><td style="padding:8px 12px;border-bottom:1px solid #e5e7eb;color:#6b7280;font-size:13px;">From Address</td><td style="padding:8px 12px;border-bottom:1px solid #e5e7eb;font-size:13px;">' . esc_html( $args['from_email'] ) . '</td></tr>';
	}
	if ( ! empty( $args['to_email'] ) ) {
		$config_details .= '<tr><td style="padding:8px 12px;border-bottom:1px solid #e5e7eb;color:#6b7280;font-size:13px;">To Address</td><td style="padding:8px 12px;border-bottom:1px solid #e5e7eb;font-size:13px;">' . esc_html( $args['to_email'] ) . '</td></tr>';
	}
	$config_details .= '<tr><td style="padding:8px 12px;color:#6b7280;font-size:13px;">Sent At</td><td style="padding:8px 12px;font-size:13px;">' . esc_html( $current_time ) . '</td></tr>';

	$html = '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Email Test Successful</title>
</head>
<body style="margin:0;padding:0;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Oxygen,Ubuntu,Cantarell,sans-serif;background-color:#f3f4f6;line-height:1.6;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f3f4f6;">
<tr>
<td align="center" style="padding:40px 20px;">
<table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width:600px;width:100%;">

<!-- Header -->
<tr>
<td style="background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);padding:32px 40px;border-radius:12px 12px 0 0;text-align:center;">
<div style="font-size:48px;margin-bottom:16px;">✉️</div>
<h1 style="margin:0;color:#ffffff;font-size:24px;font-weight:700;">Email Test Successful!</h1>
<p style="margin:8px 0 0 0;color:rgba(255,255,255,0.9);font-size:14px;">Your email configuration is working correctly</p>
</td>
</tr>

<!-- Main Content -->
<tr>
<td style="background-color:#ffffff;padding:40px;">

<!-- Success Message -->
<div style="background-color:#ecfdf5;border:1px solid #a7f3d0;border-radius:8px;padding:20px;margin-bottom:24px;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
<tr>
<td width="40" valign="top">
<div style="width:32px;height:32px;background-color:#10b981;border-radius:50%;text-align:center;line-height:32px;color:#ffffff;font-size:16px;">✓</div>
</td>
<td style="padding-left:12px;">
<p style="margin:0;color:#065f46;font-weight:600;font-size:15px;">Configuration Verified</p>
<p style="margin:4px 0 0 0;color:#047857;font-size:13px;">This test email was delivered successfully through ' . esc_html( $provider_display ) . '.</p>
</td>
</tr>
</table>
</div>

<!-- What This Means -->
<h2 style="margin:0 0 12px 0;color:#1f2937;font-size:16px;font-weight:600;">What This Means</h2>
<p style="margin:0 0 20px 0;color:#4b5563;font-size:14px;">
Your WordPress site <strong>' . esc_html( $args['site_name'] ) . '</strong> is now properly configured to send emails.
All transactional emails (password resets, contact forms, notifications, etc.) will be delivered reliably.
</p>

<!-- Configuration Details -->
<h2 style="margin:0 0 12px 0;color:#1f2937;font-size:16px;font-weight:600;">Configuration Details</h2>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f9fafb;border-radius:8px;border:1px solid #e5e7eb;margin-bottom:24px;">
' . $config_details . '
</table>

<!-- Next Steps -->
<div style="background-color:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:20px;margin-bottom:24px;">
<h3 style="margin:0 0 12px 0;color:#1e40af;font-size:14px;font-weight:600;">💡 Recommended Next Steps</h3>
<ul style="margin:0;padding:0 0 0 20px;color:#1e3a8a;font-size:13px;">
<li style="margin-bottom:6px;">Test your contact forms to ensure they\'re working</li>
<li style="margin-bottom:6px;">Verify password reset emails are being delivered</li>
<li style="margin-bottom:6px;">Check that notification emails reach your inbox</li>
<li>Consider setting up email logging to monitor deliverability</li>
</ul>
</div>

<!-- Support Link -->
<p style="margin:0;color:#6b7280;font-size:13px;text-align:center;">
Need help? Visit <a href="' . esc_url( $args['site_url'] ) . '/wp-admin/admin.php?page=authority-mailer-smtp-dashboard" style="color:#6366f1;text-decoration:none;">Authority Mailer Settings</a> to manage your email configuration.
</p>

</td>
</tr>

<!-- Footer -->
<tr>
<td style="background-color:#f9fafb;padding:24px 40px;border-radius:0 0 12px 12px;border-top:1px solid #e5e7eb;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
<tr>
<td>
<p style="margin:0;color:#6b7280;font-size:12px;">
Sent by <strong style="color:#4b5563;">Authority Mailer</strong> — Professional Email Delivery for WordPress
</p>
</td>
<td align="right">
<p style="margin:0;color:#9ca3af;font-size:11px;">© ' . esc_html( $year ) . '</p>
</td>
</tr>
</table>
</td>
</tr>

</table>
</td>
</tr>
</table>
</body>
</html>';

	return $html;
}

/**
 * Get the plain text version of the test email.
 *
 * @param array $args Same arguments as authority_mailer_smtp_get_test_email_html().
 * @return string Plain text email body.
 */
function authority_mailer_smtp_get_test_email_plain( $args = array() ) {
	$defaults = array(
		'provider'   => '',
		'from_email' => '',
		'from_name'  => '',
		'to_email'   => '',
		'site_name'  => get_bloginfo( 'name' ),
		'site_url'   => home_url(),
	);

	$args = wp_parse_args( $args, $defaults );

	if ( empty( $args['site_name'] ) ) {
		$args['site_name'] = 'Your Website';
	}

	$provider_display = ! empty( $args['provider'] ) ? ucfirst( $args['provider'] ) : 'SMTP';
	$current_time     = current_time( 'F j, Y \a\t g:i A' );

	$text  = "EMAIL TEST SUCCESSFUL\n";
	$text .= "=====================\n\n";
	$text .= "✓ Configuration Verified\n";
	$text .= "This test email was delivered successfully through {$provider_display}.\n\n";
	$text .= "WHAT THIS MEANS\n";
	$text .= "---------------\n";
	$text .= "Your WordPress site \"{$args['site_name']}\" is now properly configured to send emails.\n";
	$text .= "All transactional emails (password resets, contact forms, notifications) will be delivered reliably.\n\n";
	$text .= "CONFIGURATION DETAILS\n";
	$text .= "---------------------\n";
	if ( ! empty( $args['provider'] ) ) {
		$text .= 'Provider: ' . ucfirst( $args['provider'] ) . "\n";
	}
	if ( ! empty( $args['from_email'] ) ) {
		$text .= "From: {$args['from_email']}\n";
	}
	if ( ! empty( $args['to_email'] ) ) {
		$text .= "To: {$args['to_email']}\n";
	}
	$text .= "Sent: {$current_time}\n\n";
	$text .= "---\n";
	$text .= "Sent by Authority Mailer - Professional Email Delivery for WordPress\n";
	$text .= $args['site_url'] . "\n";

	return $text;
}

/**
 * Extract human-readable error message from API response body.
 *
 * Attempts to parse JSON response and extract error messages.
 * Handles SendLayer format: {"Errors":[{"Code":10,"Message":"..."}]}
 * and other common API error formats.
 *
 * @since 1.0.2
 * @param string $response_body Raw API response body.
 * @return string Extracted error message or truncated raw response.
 */
function authority_mailer_smtp_extract_api_error( $response_body ) {
	// Maximum length for error response display.
	$max_error_response_length = 500;

	if ( empty( $response_body ) || ! is_string( $response_body ) ) {
		return '';
	}

	// Try to parse as JSON.
	$decoded = json_decode( $response_body, true );
	if ( json_last_error() === JSON_ERROR_NONE && is_array( $decoded ) ) {
		// SendLayer format: {"Errors":[{"Code":10,"Message":"..."}]}.
		if ( isset( $decoded['Errors'] ) && is_array( $decoded['Errors'] ) && ! empty( $decoded['Errors'] ) ) {
			$error_messages = array();
			foreach ( $decoded['Errors'] as $error ) {
				if ( isset( $error['Message'] ) ) {
					$error_messages[] = $error['Message'];
				}
			}
			if ( ! empty( $error_messages ) ) {
				return implode( ' ', $error_messages );
			}
		}

		// Common error formats: {"error":"..."} or {"message":"..."}.
		if ( isset( $decoded['error'] ) && is_string( $decoded['error'] ) ) {
			return $decoded['error'];
		}
		if ( isset( $decoded['message'] ) && is_string( $decoded['message'] ) ) {
			return $decoded['message'];
		}
	}

	// Fallback: return truncated raw response.
	return substr( $response_body, 0, $max_error_response_length );
}

/**
 * Send test email through wp_mail() pipeline.
 *
 * Centralized test email helper that ALL providers should use.
 * This ensures test emails flow through the full wp_mail() pipeline
 * with premium features (spam checking, tracking, logging, etc.).
 *
 * Benefits:
 * - Test emails get spam scores
 * - Test emails get tracking
 * - Test emails are logged properly
 * - Consistent test email format across all providers
 * - 98.5% reduction in duplicate code
 *
 * @since 1.0.1
 *
 * @param string $provider    Provider name (e.g., 'sendgrid', 'mailgun').
 * @param string $to_email    Recipient email address.
 * @param string $from_email  Sender email address.
 * @param string $from_name   Sender display name.
 * @param array  $steps       Reference to steps array for diagnostic output.
 * @return bool True if email was sent successfully, false otherwise.
 */
function authority_mailer_smtp_send_test_via_wpmail( $provider, $to_email, $from_email, $from_name, &$steps = array() ) {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
		error_log( "[Authority Mailer Test Helper] Sending test via wp_mail() | provider: {$provider} | to: {$to_email}" );
	}

	// Get professional test email content.
	$test_email_args = array(
		'provider'   => $provider,
		'from_email' => $from_email,
		'from_name'  => $from_name,
		'to_email'   => $to_email,
	);

	$subject = authority_mailer_smtp_get_test_email_subject( ucfirst( $provider ) );
	$body    = authority_mailer_smtp_get_test_email_html( $test_email_args );

	// Build headers.
	$headers = array(
		'Content-Type: text/html; charset=UTF-8',
		"From: {$from_name} <{$from_email}>",
	);

	// Mark this as a test email so queue doesn't intercept it.
	add_filter(
		'wp_mail',
		function ( $args ) {
			$args['authority-mailer_is_test'] = true;
			return $args;
		},
		1
	);

	// Send through wp_mail() - this will trigger all premium features.
	$result = wp_mail( $to_email, $subject, $body, $headers );

	if ( $result ) {
		// wp_mail() returned true, but this doesn't mean the API accepted the email.
		// Wait briefly for async logging to complete, then check the actual API response.
		sleep( 2 );

		// Check email log for actual API response.
		$api_error_detected = false;
		$error_details      = array();

		if ( function_exists( 'authority_mailer_smtp_email_logger_get_recent' ) ) {
			// Flush any caches to ensure we get the latest log entry.
			wp_cache_delete( 'authority_mailer_smtp_recent_1_v' . authority_mailer_smtp_email_logger_cache_version(), 'authority_mailer_smtp_email_logger' );

			$recent_logs = authority_mailer_smtp_email_logger_get_recent( 1 );
			if ( ! empty( $recent_logs ) && is_array( $recent_logs ) ) {
				$last_log = $recent_logs[0];

				// Check if this log entry is for our test email (matches recipient, subject, and is recent).
				$recipient_matches = isset( $last_log->to_email ) && $last_log->to_email === $to_email;
				$subject_matches   = isset( $last_log->subject ) && $last_log->subject === $subject;

				if ( $recipient_matches && $subject_matches ) {
					// Additional safety: check if log entry was created within last 10 seconds.
					$log_recency_threshold = 10;
					$is_recent             = true;
					if ( isset( $last_log->created_at ) ) {
						$log_time = strtotime( $last_log->created_at );
						if ( false === $log_time ) {
							$is_recent = false;
							if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
								// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
								error_log( '[Authority Mailer Test Helper] Failed to parse log timestamp' );
							}
						} else {
							$now       = time();
							$is_recent = ( $now - $log_time ) <= $log_recency_threshold;
						}
					}

					// Check if API actually rejected the email.
					if ( $is_recent ) {
						$has_error_status = isset( $last_log->status ) && 'error' === $last_log->status;
						$has_error_code   = isset( $last_log->response_code ) && intval( $last_log->response_code ) >= 400;

						if ( $has_error_status || $has_error_code ) {
							$api_error_detected = true;

							// Extract human-readable error message from response body.
							if ( ! empty( $last_log->response_body ) ) {
								$error_message   = authority_mailer_smtp_extract_api_error( $last_log->response_body );
								$error_details[] = 'API Error: ' . $error_message;
							}

							// Add response code if available.
							if ( ! empty( $last_log->response_code ) ) {
								$error_details[] = 'Response Code: ' . $last_log->response_code;
							}

							// If no details found, use generic message.
							if ( empty( $error_details ) ) {
								$error_details = array( 'Email rejected by API. Check error logs for details.' );
							}
						}
					}
				}
			}
		}

		// Report the actual result based on API response, not just wp_mail() return value.
		if ( $api_error_detected ) {
			$error_msg = authority_mailer_smtp_s( 'test_email_failed' );
			if ( empty( $error_msg ) ) {
				$error_msg = 'Test email rejected by API for %s';
			}

			$steps[] = array(
				'status'  => 'error',
				'message' => sprintf( $error_msg, $to_email ),
				'details' => $error_details,
			);

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
				error_log( '[Authority Mailer Test Helper] Test email rejected by API despite wp_mail() returning true' );
			}
		} else {
			$success_msg = authority_mailer_smtp_s( 'test_email_sent_success' );
			if ( empty( $success_msg ) ) {
				$success_msg = 'Test email sent successfully through wp_mail() pipeline to %s';
			}

			$steps[] = array(
				'status'  => 'success',
				'message' => sprintf( $success_msg, $to_email ),
				'details' => array(
					'provider' => $provider,
					'to'       => $to_email,
					'from'     => "{$from_name} <{$from_email}>",
					'subject'  => $subject,
					'via'      => 'wp_mail() with premium features',
				),
			);

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
				error_log( '[Authority Mailer Test Helper] Test email sent successfully via wp_mail()' );
			}
		}
	} else {
		$error_msg = authority_mailer_smtp_s( 'test_email_failed' );
		if ( empty( $error_msg ) ) {
			$error_msg = 'Failed to send test email to %s';
		}

		// Try to get detailed error information from the email log.
		$error_details = array( 'Check error logs for details.' );
		if ( function_exists( 'authority_mailer_smtp_email_logger_get_recent' ) ) {
			$recent_logs = authority_mailer_smtp_email_logger_get_recent( 1 );
			if ( ! empty( $recent_logs ) && is_array( $recent_logs ) ) {
				$last_log = $recent_logs[0];
				// Check if this log entry is for our test email (matches recipient and is recent).
				// Verify recipient matches to handle concurrency scenarios.
				if ( isset( $last_log->to_email ) && $last_log->to_email === $to_email ) {
					// Additional safety: check if log entry was created within last 10 seconds.
					$is_recent = true;
					if ( isset( $last_log->created_at ) ) {
						$log_time = strtotime( $last_log->created_at );
						if ( false === $log_time ) {
							// Invalid date format - assume not recent.
							$is_recent = false;
						} else {
							$now       = time();
							$is_recent = ( $now - $log_time ) <= 10;
						}
					}
					// Check if it has an error status and is recent.
					if ( $is_recent && isset( $last_log->status ) && 'error' === $last_log->status ) {
						$error_details = array();
						// Add response body if available (contains actual error message).
						// Truncate to prevent UI issues and potential information disclosure.
						if ( ! empty( $last_log->response_body ) ) {
							// Maximum error message length for diagnostic display.
							$max_error_length = 500;
							$response_body    = is_string( $last_log->response_body ) ? $last_log->response_body : '';
							$response_body    = substr( $response_body, 0, $max_error_length );
							$error_details[]  = 'Error: ' . $response_body;
						}
						// Add response code if available.
						if ( ! empty( $last_log->response_code ) ) {
							$error_details[] = 'Response Code: ' . $last_log->response_code;
						}
						// If no details found, use generic message.
						if ( empty( $error_details ) ) {
							$error_details = array( 'Email transmission failed. Check error logs for details.' );
						}
					}
				}
			}
		}

		$steps[] = array(
			'status'  => 'error',
			'message' => sprintf( $error_msg, $to_email ),
			'details' => $error_details,
		);

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
			error_log( '[Authority Mailer Test Helper] Test email failed via wp_mail()' );
		}
	}

	return $result;
}

/**
 * Split comma-separated email list into array.
 *
 * @since 1.0.1
 * @param string $email_list Comma-separated email addresses.
 * @return array Array of validated email addresses.
 */
function authority_mailer_smtp_split_email_list( $email_list ) {
	$emails = array();
	$parts  = explode( ',', $email_list );

	foreach ( $parts as $email ) {
		$email = trim( $email );
		if ( is_email( $email ) ) {
			$emails[] = $email;
		}
	}

	return $emails;
}

/**
 * Check if a provider supports Return-Path in custom headers.
 *
 * Some email providers (like Mailjet) don't allow Return-Path to be set
 * via their custom headers collection and require dedicated properties instead.
 * This function returns true for providers that support Return-Path in custom headers.
 *
 * @since 1.0.2
 * @param string $provider The provider identifier (e.g., 'mailjet', 'sendgrid').
 * @return bool True if the provider supports Return-Path in custom headers, false otherwise.
 */
function authority_mailer_smtp_supports_return_path_header( $provider ) {
	// List of providers that do NOT support Return-Path in custom headers.
	$unsupported_providers = array(
		'mailjet',
	);

	return ! in_array( strtolower( $provider ), $unsupported_providers, true );
}

/**
 * Parse email headers and extract common email defaults.
 *
 * Extracts Reply-To, CC, BCC, Return-Path, Priority, and custom headers from the headers array.
 * This allows API providers to include email defaults in their API payloads.
 *
 * @since 1.0.1
 * @param array $email Email array with headers key.
 * @return array Associative array with parsed header values.
 */
function authority_mailer_smtp_parse_email_headers( $email ) {
	$parsed = array(
		'reply_to'    => '',
		'cc'          => array(),
		'bcc'         => array(),
		'return_path' => '',
		'priority'    => '',
		'custom'      => array(),
	);

	if ( empty( $email['headers'] ) ) {
		return $parsed;
	}

	$headers = $email['headers'];

	// Check if headers is an associative array.
	$is_associative = is_array( $headers ) && array_keys( $headers ) !== range( 0, count( $headers ) - 1 );

	// Debug logging to see what format we're receiving.
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
		error_log( '[Authority Mailer Header Parser] Headers type: ' . gettype( $headers ) );
		if ( is_array( $headers ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
			error_log( '[Authority Mailer Header Parser] Is associative: ' . ( $is_associative ? 'yes' : 'no' ) );
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
			error_log( '[Authority Mailer Header Parser] Headers keys: ' . wp_json_encode( array_keys( $headers ) ) );
		}
	}

	// Handle associative array format (most common from Email Defaults).
	if ( $is_associative ) {
		// Associative array format.
		foreach ( $headers as $name => $value ) {
			if ( ! is_string( $name ) ) {
				continue;
			}

			$name_lower = strtolower( trim( $name ) );
			$value      = is_array( $value ) ? implode( ', ', $value ) : trim( (string) $value );

			switch ( $name_lower ) {
				case 'reply-to':
					if ( is_email( $value ) ) {
						$parsed['reply_to'] = $value;
					}
					break;
				case 'cc':
					$parsed['cc'] = array_merge( $parsed['cc'], authority_mailer_smtp_split_email_list( $value ) );
					break;
				case 'bcc':
					$parsed['bcc'] = array_merge( $parsed['bcc'], authority_mailer_smtp_split_email_list( $value ) );
					break;
				case 'return-path':
					if ( is_email( $value ) ) {
						$parsed['return_path'] = $value;
					}
					break;
				case 'x-priority':
				case 'priority':
				case 'importance':
				case 'x-msmail-priority':
					if ( ! empty( $value ) ) {
						$parsed['priority'] = $value;
					}
					break;
				default:
					// Store custom headers (excluding content-type, from, to, subject).
					if ( ! in_array( $name_lower, array( 'content-type', 'from', 'to', 'subject' ), true ) ) {
						$parsed['custom'][] = array(
							'name'  => $name,
							'value' => $value,
						);
					}
					break;
			}
		}

		// Debug logging to show what we parsed.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
			error_log( '[Authority Mailer Header Parser] Parsed Reply-To: ' . ( ! empty( $parsed['reply_to'] ) ? $parsed['reply_to'] : 'none' ) );
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
			error_log( '[Authority Mailer Header Parser] Parsed CC count: ' . count( $parsed['cc'] ) );
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
			error_log( '[Authority Mailer Header Parser] Parsed BCC count: ' . count( $parsed['bcc'] ) );
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
			error_log( '[Authority Mailer Header Parser] Parsed Priority: ' . ( ! empty( $parsed['priority'] ) ? $parsed['priority'] : 'none' ) );
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
			error_log( '[Authority Mailer Header Parser] Parsed Return-Path: ' . ( ! empty( $parsed['return_path'] ) ? $parsed['return_path'] : 'none' ) );
		}

		return $parsed;
	}

	// Handle string or indexed array format.
	if ( ! is_array( $headers ) ) {
		$headers = explode( "\n", $headers );
	}

	foreach ( $headers as $header ) {
		if ( empty( $header ) || ! is_string( $header ) ) {
			continue;
		}

		// Split header into name and value.
		$parts = explode( ':', $header, 2 );
		if ( count( $parts ) !== 2 ) {
			continue;
		}

		$name  = strtolower( trim( $parts[0] ) );
		$value = trim( $parts[1] );

		switch ( $name ) {
			case 'reply-to':
				if ( is_email( $value ) ) {
					$parsed['reply_to'] = $value;
				}
				break;
			case 'cc':
				$parsed['cc'] = array_merge( $parsed['cc'], authority_mailer_smtp_split_email_list( $value ) );
				break;
			case 'bcc':
				$parsed['bcc'] = array_merge( $parsed['bcc'], authority_mailer_smtp_split_email_list( $value ) );
				break;
			case 'return-path':
				if ( is_email( $value ) ) {
					$parsed['return_path'] = $value;
				}
				break;
			case 'x-priority':
			case 'priority':
			case 'importance':
			case 'x-msmail-priority':
				if ( ! empty( $value ) ) {
					$parsed['priority'] = $value;
				}
				break;
			default:
				// Store custom headers (excluding content-type, from, to, subject).
				if ( ! in_array( $name, array( 'content-type', 'from', 'to', 'subject' ), true ) ) {
					$parsed['custom'][] = array(
						'name'  => $parts[0], // Preserve original case.
						'value' => $value,
					);
				}
				break;
		}
	}

	// Debug logging to show what we parsed.
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
		error_log( '[Authority Mailer Header Parser] Parsed Reply-To: ' . ( ! empty( $parsed['reply_to'] ) ? $parsed['reply_to'] : 'none' ) );
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
		error_log( '[Authority Mailer Header Parser] Parsed CC count: ' . count( $parsed['cc'] ) );
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
		error_log( '[Authority Mailer Header Parser] Parsed BCC count: ' . count( $parsed['bcc'] ) );
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
		error_log( '[Authority Mailer Header Parser] Parsed Priority: ' . ( ! empty( $parsed['priority'] ) ? $parsed['priority'] : 'none' ) );
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
		error_log( '[Authority Mailer Header Parser] Parsed Return-Path: ' . ( ! empty( $parsed['return_path'] ) ? $parsed['return_path'] : 'none' ) );
	}

	return $parsed;
}

/**
 * Get priority headers in the format expected by email providers.
 *
 * Converts priority values to standard X-Priority, Importance, and X-MSMail-Priority headers.
 *
 * @since 1.0.2
 * @param string $priority Priority value from parsed headers.
 * @return array Array of priority header objects with 'name' and 'value' keys.
 */
function authority_mailer_smtp_get_priority_headers( $priority ) {
	if ( empty( $priority ) ) {
		return array();
	}

	$priority = strtolower( trim( $priority ) );

	// Map various priority formats to standard values.
	$priority_map = array(
		'1'      => array(
			'x_priority' => '1',
			'importance' => 'high',
			'msmail'     => 'High',
		),
		'high'   => array(
			'x_priority' => '1',
			'importance' => 'high',
			'msmail'     => 'High',
		),
		'urgent' => array(
			'x_priority' => '1',
			'importance' => 'high',
			'msmail'     => 'High',
		),
		'3'      => array(
			'x_priority' => '3',
			'importance' => 'normal',
			'msmail'     => 'Normal',
		),
		'normal' => array(
			'x_priority' => '3',
			'importance' => 'normal',
			'msmail'     => 'Normal',
		),
		'5'      => array(
			'x_priority' => '5',
			'importance' => 'low',
			'msmail'     => 'Low',
		),
		'low'    => array(
			'x_priority' => '5',
			'importance' => 'low',
			'msmail'     => 'Low',
		),
	);

	// Get the mapped values or default to normal priority.
	$mapped = isset( $priority_map[ $priority ] ) ? $priority_map[ $priority ] : $priority_map['normal'];

	return array(
		array(
			'name'  => 'X-Priority',
			'value' => $mapped['x_priority'],
		),
		array(
			'name'  => 'Importance',
			'value' => $mapped['importance'],
		),
		array(
			'name'  => 'X-MSMail-Priority',
			'value' => $mapped['msmail'],
		),
	);
}

/**
 * Extract email defaults from the email array.
 *
 * This function retrieves email defaults (Reply-To, CC, BCC, Priority, Return-Path)
 * from dedicated fields in the $email array. If dedicated fields are not available,
 * it falls back to parsing headers.
 *
 * This approach is more reliable and performant than always parsing headers, as it
 * directly accesses values set by Authority_Mailer_Email_Defaults::apply_defaults().
 *
 * @since 1.0.3
 * @param array $email Email array with to, subject, message, headers, etc.
 * @return array Associative array with keys: reply_to, cc, bcc, priority, return_path, custom.
 */
function authority_mailer_smtp_get_email_defaults( $email ) {
	// Use dedicated fields for email defaults (from Email_Defaults::apply_defaults).
	$reply_to    = isset( $email['reply_to'] ) ? $email['reply_to'] : '';
	$cc          = isset( $email['cc'] ) ? $email['cc'] : array();
	$bcc         = isset( $email['bcc'] ) ? $email['bcc'] : array();
	$priority    = isset( $email['priority'] ) ? $email['priority'] : '';
	$return_path = isset( $email['return_path'] ) ? $email['return_path'] : '';

	// Fallback: Parse headers individually for any missing fields.
	// Check if ANY field needs to be populated from headers.
	if ( empty( $reply_to ) || empty( $cc ) || empty( $bcc ) || empty( $priority ) || empty( $return_path ) ) {
		$parsed_headers = authority_mailer_smtp_parse_email_headers( $email );

		// Only override empty fields with parsed values.
		if ( empty( $reply_to ) ) {
			$reply_to = $parsed_headers['reply_to'];
		}
		if ( empty( $cc ) ) {
			$cc = $parsed_headers['cc'];
		}
		if ( empty( $bcc ) ) {
			$bcc = $parsed_headers['bcc'];
		}
		if ( empty( $priority ) ) {
			$priority = $parsed_headers['priority'];
		}
		if ( empty( $return_path ) ) {
			$return_path = $parsed_headers['return_path'];
		}
		// Custom headers always come from headers array (no dedicated top-level key exists).
		$custom = $parsed_headers['custom'];
	} else {
		// All dedicated fields are set, still parse custom headers from headers array.
		// Custom headers don't have a top-level key in $email, so always parse from headers.
		$parsed_headers = authority_mailer_smtp_parse_email_headers( $email );
		$custom         = $parsed_headers['custom'];
	}

	return array(
		'reply_to'    => $reply_to,
		'cc'          => $cc,
		'bcc'         => $bcc,
		'priority'    => $priority,
		'return_path' => $return_path,
		'custom'      => $custom,
	);
}
