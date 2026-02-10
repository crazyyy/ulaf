<?php
/**
 * Authority Mailer SMTP Tools AJAX Handler
 *
 * Handles AJAX requests for email deliverability checks
 *
 * @package Authority_Mailer
 * @since   1.0.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handle deliverability check AJAX request
 */
function authority_mailer_check_deliverability() {
	// Verify nonce.
	check_ajax_referer( 'authority_mailer_tools', 'nonce' );

	// Check user capability.
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => authority_mailer_smtp_get_string( 'no_permission' ) ) );
	}

	// Get and validate domain.
	$domain = isset( $_POST['domain'] ) ? sanitize_text_field( wp_unslash( $_POST['domain'] ) ) : '';

	if ( empty( $domain ) ) {
		wp_send_json_error( array( 'message' => authority_mailer_smtp_get_string( 'tools_error_empty_domain' ) ) );
	}

	// Validate domain format.
	$domain = preg_replace( '/^https?:\/\//i', '', $domain );
	$domain = preg_replace( '/^www\./i', '', $domain );
	$domain = strtolower( trim( $domain ) );

	if ( ! preg_match( '/^[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,}$/i', $domain ) ) {
		wp_send_json_error( array( 'message' => authority_mailer_smtp_get_string( 'tools_error_invalid_domain' ) ) );
	}

	// Perform checks.
	$results = array(
		'domain'     => $domain,
		'spf'        => authority_mailer_check_spf( $domain ),
		'dkim'       => authority_mailer_check_dkim( $domain ),
		'dmarc'      => authority_mailer_check_dmarc( $domain ),
		'mx'         => authority_mailer_check_mx( $domain ),
		'reputation' => authority_mailer_check_reputation( $domain ),
		'blacklist'  => authority_mailer_check_blacklist( $domain ),
	);

	wp_send_json_success( $results );
}
add_action( 'wp_ajax_authority_mailer_check_deliverability', 'authority_mailer_check_deliverability' );

/**
 * Check SPF record for domain
 *
 * @param string $domain Domain to check.
 * @return array Check results.
 */
function authority_mailer_check_spf( $domain ) {
	$records = @dns_get_record( $domain, DNS_TXT );

	if ( ! $records || ! is_array( $records ) ) {
		return array(
			'status'  => 'fail',
			'message' => 'No DNS TXT records found',
		);
	}

	foreach ( $records as $record ) {
		if ( isset( $record['txt'] ) && strpos( $record['txt'], 'v=spf1' ) === 0 ) {
			return array(
				'status'  => 'pass',
				'message' => 'SPF record found: ' . esc_html( $record['txt'] ),
			);
		}
	}

	return array(
		'status'  => 'fail',
		'message' => 'No SPF record found. Add an SPF record to improve deliverability.',
	);
}

/**
 * Check DKIM record for domain
 *
 * @param string $domain Domain to check.
 * @return array Check results.
 */
function authority_mailer_check_dkim( $domain ) {
	// Common DKIM selectors to check.
	$selectors = array( 'default', 'google', 'k1', 'dkim', 'mail', 'selector1', 'selector2', 's1', 's2' );
	$found     = false;
	$message   = '';

	foreach ( $selectors as $selector ) {
		$dkim_domain = $selector . '._domainkey.' . $domain;
		$records     = @dns_get_record( $dkim_domain, DNS_TXT );

		if ( $records && is_array( $records ) ) {
			foreach ( $records as $record ) {
				if ( isset( $record['txt'] ) && ( strpos( $record['txt'], 'v=DKIM1' ) !== false || strpos( $record['txt'], 'k=' ) !== false ) ) {
					$found   = true;
					$message = 'DKIM record found for selector: ' . esc_html( $selector );
					break 2;
				}
			}
		}
	}

	if ( $found ) {
		return array(
			'status'  => 'pass',
			'message' => $message,
		);
	}

	return array(
		'status'  => 'fail',
		'message' => 'No DKIM records found for common selectors. Configure DKIM with your email provider.',
	);
}

/**
 * Check DMARC record for domain
 *
 * @param string $domain Domain to check.
 * @return array Check results.
 */
function authority_mailer_check_dmarc( $domain ) {
	$dmarc_domain = '_dmarc.' . $domain;
	$records      = @dns_get_record( $dmarc_domain, DNS_TXT );

	if ( ! $records || ! is_array( $records ) ) {
		return array(
			'status'  => 'fail',
			'message' => 'No DMARC record found. Add a DMARC policy to protect your domain.',
		);
	}

	foreach ( $records as $record ) {
		if ( isset( $record['txt'] ) && strpos( $record['txt'], 'v=DMARC1' ) === 0 ) {
			// Parse DMARC policy.
			preg_match( '/p=(none|quarantine|reject)/', $record['txt'], $matches );
			$policy = isset( $matches[1] ) ? $matches[1] : 'unknown';

			return array(
				'status'  => 'pass',
				'message' => 'DMARC record found with policy: ' . esc_html( $policy ),
			);
		}
	}

	return array(
		'status'  => 'fail',
		'message' => 'No valid DMARC record found.',
	);
}

/**
 * Check MX records for domain
 *
 * @param string $domain Domain to check.
 * @return array Check results.
 */
function authority_mailer_check_mx( $domain ) {
	$records = @dns_get_record( $domain, DNS_MX );

	if ( ! $records || ! is_array( $records ) || count( $records ) === 0 ) {
		return array(
			'status'  => 'fail',
			'message' => 'No MX records found. Configure MX records to receive emails.',
		);
	}

	$mx_list = array();
	foreach ( $records as $record ) {
		if ( isset( $record['target'] ) && isset( $record['pri'] ) ) {
			$mx_list[] = sprintf( '%s (Priority: %d)', esc_html( $record['target'] ), intval( $record['pri'] ) );
		}
	}

	return array(
		'status'  => 'pass',
		'message' => 'Found ' . count( $mx_list ) . ' MX record(s): ' . implode( ', ', $mx_list ),
	);
}

/**
 * Check domain reputation score
 *
 * @param string $domain Domain to check.
 * @return array Check results.
 */
function authority_mailer_check_reputation( $domain ) {
	// Calculate basic reputation score based on other checks
	$spf        = authority_mailer_check_spf( $domain );
	$dkim       = authority_mailer_check_dkim( $domain );
	$dmarc      = authority_mailer_check_dmarc( $domain );
	$mx         = authority_mailer_check_mx( $domain );
	$blacklist  = authority_mailer_check_blacklist( $domain );
	
	$score = 100;
	
	// Deduct points for failures
	if ( $spf['status'] === 'fail' ) {
		$score -= 25;
	}
	if ( $dkim['status'] === 'fail' ) {
		$score -= 20;
	}
	if ( $dmarc['status'] === 'fail' ) {
		$score -= 15;
	}
	if ( $mx['status'] === 'fail' ) {
		$score -= 15;
	}
	if ( $blacklist['status'] === 'fail' ) {
		$score -= 25;
	}
	
	// Determine status
	if ( $score >= 80 ) {
		$status = 'pass';
		$message = sprintf( authority_mailer_smtp_get_string( 'tools_reputation_excellent' ), $score );
	} elseif ( $score >= 60 ) {
		$status = 'pass';
		$message = sprintf( authority_mailer_smtp_get_string( 'tools_reputation_good' ), $score );
	} elseif ( $score >= 40 ) {
		$status = 'fail';
		$message = sprintf( authority_mailer_smtp_get_string( 'tools_reputation_fair' ), $score );
	} else {
		$status = 'fail';
		$message = sprintf( authority_mailer_smtp_get_string( 'tools_reputation_poor' ), $score );
	}
	
	return array(
		'status'  => $status,
		'message' => $message,
		'score'   => $score,
	);
}

/**
 * Check if domain is blacklisted
 *
 * @param string $domain Domain to check.
 * @return array Check results.
 */
function authority_mailer_check_blacklist( $domain ) {
	// Get domain IP address for blacklist checking.
	$ip_records = @dns_get_record( $domain, DNS_A );

	if ( ! $ip_records || ! is_array( $ip_records ) || empty( $ip_records ) ) {
		return array(
			'status'  => 'fail',
			'message' => 'Could not resolve domain IP address for blacklist check.',
		);
	}

	// Use the first A record.
	$ip = isset( $ip_records[0]['ip'] ) ? $ip_records[0]['ip'] : '';

	if ( ! filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
		return array(
			'status'  => 'fail',
			'message' => 'Could not resolve valid IPv4 address for blacklist check.',
		);
	}

	// Common blacklist services to check.
	$blacklists = array(
		'zen.spamhaus.org',
		'bl.spamcop.net',
		'b.barracudacentral.org',
	);

	$listed = array();
	$parts  = explode( '.', $ip );
	if ( count( $parts ) === 4 ) {
		$reversed_ip = $parts[3] . '.' . $parts[2] . '.' . $parts[1] . '.' . $parts[0];

		foreach ( $blacklists as $blacklist ) {
			$lookup  = $reversed_ip . '.' . $blacklist;
			$records = @dns_get_record( $lookup, DNS_A );

			// If DNS query returns A records, the IP is listed.
			if ( $records && is_array( $records ) && ! empty( $records ) ) {
				// Verify it's a blacklist response (typically 127.0.0.x).
				foreach ( $records as $record ) {
					if ( isset( $record['ip'] ) && strpos( $record['ip'], '127.0.0.' ) === 0 ) {
						$listed[] = $blacklist;
						break;
					}
				}
			}
		}
	}

	if ( empty( $listed ) ) {
		return array(
			'status'  => 'clean',
			'message' => 'Domain IP (' . esc_html( $ip ) . ') is not listed on checked blacklists',
		);
	}

	return array(
		'status'  => 'fail',
		'message' => 'Domain IP (' . esc_html( $ip ) . ') is listed on: ' . implode( ', ', $listed ),
	);
}
