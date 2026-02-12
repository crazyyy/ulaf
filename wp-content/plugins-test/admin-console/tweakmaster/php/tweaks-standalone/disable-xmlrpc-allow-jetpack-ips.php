<?php
/**
 * @package   Nextgenthemes\TweakMaster
 * @link      https://nexgenthemes.com
 * @copyright 2025 Nicolas Jonas
 * @license   GPL-3.0
 *
 * @wordpress-plugin
 * Plugin Name:      Disable XML-RPC - allow Jetpack IPs
 * Description:      Disable XML-RPC but allow it for Jetpack IPs
 * Plugin URI:       https://nexgenthemes.com/plugins/tweakmaster/
 * Version:          1.0.0
 * Author:           Nicolas Jonas
 * Author URI:       https://nexgenthemes.com
 * License:          GPLv3
 * Requires at least: 6.6
 * Requires PHP:      7.4
 */

declare(strict_types = 1);

namespace Nextgenthemes\TweakMaster;

const JETPACK_IPS = array(
	'122.248.245.244/32',
	'54.217.201.243/32',
	'54.232.116.4/32',
	'192.0.80.0/20',
	'192.0.96.0/20',
	'192.0.112.0/20',
	'195.234.108.0/22',
	'192.0.64.0/18',
);

// Register the filter using the namespace
add_filter( 'xmlrpc_enabled', __NAMESPACE__ . '\filter_xmlrpc_enabled' );

// Filter to enable/disable XML-RPC based on Jetpack IP
function filter_xmlrpc_enabled(): bool {
	if ( is_jetpack_ip() ) {
		return true; // Allow XML-RPC for Jetpack IPs
	}
	return false; // Disable XML-RPC for all others
}

// Function to check if an IP is in a CIDR range
function is_ip_in_range( string $ip, string $cidr ): bool {
	list($subnet, $bits) = explode( '/', $cidr );
	$bits                = (int) $bits;
	$ip                  = ip2long( $ip );
	$subnet              = ip2long( $subnet );
	$mask                = -1 << ( 32 - $bits );
	$subnet             &= $mask; // Sanitize subnet
	return ( $ip & $mask ) === $subnet;
}

/**
 * Fetch and cache Jetpack IP ranges
 *
 * @return array<string> Array of IP ranges.
 */
function get_jetpack_ips(): array {
	$transient_key = 'tweakmaster_jetpack_ips';
	$ips           = get_transient( $transient_key );

	if ( false === $ips ) {
		$response = remote_get_body( 'https://jetpack.com/ips-v4.txt' );

		if ( is_wp_error( $response ) ) {
			wp_trigger_error( __FUNCTION__, $response->get_error_message() );
			$ips = JETPACK_IPS;
		} else {
			$ips = explode( "\n", trim( $response ) );
			$ips = array_filter( $ips ); // Remove empty lines
			set_transient( $transient_key, $ips, WEEK_IN_SECONDS ); // Cache for 1 week
		}
	}
	return $ips;
}

// Check if the current request is from a Jetpack IP
function is_jetpack_ip(): bool {

	if ( empty( $_SERVER['REMOTE_ADDR'] ) ) {
		$client_ip = '0.0.0.0';
	} else {
		$client_ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
	}

	$jetpack_ranges = get_jetpack_ips();

	if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		// HTTP_X_FORWARDED_FOR can contain a comma-separated list; take the first IP
		$forwarded_ips = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
		$client_ip     = trim( $forwarded_ips[0] ); // First IP is typically the original client
	}

	if ( ! valid_ip( $client_ip ) ) {
		wp_trigger_error( __FUNCTION__, 'Invalid Client IP: ' . $client_ip );
		return false;
	}

	foreach ( $jetpack_ranges as $range ) {
		if ( is_ip_in_range( $client_ip, $range ) ) {
			return true;
		}
	}
	return false;
}

function valid_ip( string $ip ): bool {
	return filter_var( $ip, FILTER_VALIDATE_IP ) !== false;
}

/**
 * Retrieves the body content from a remote URL.
 *
 * @param  string               $url  The URL of the remote resource.
 * @param  array<string, mixed> $args Optional. Additional arguments for wp_safe_remote_get.
 * @return string|\WP_Error           The response body content from the remote URL, or a WP_Error on failure.
 */
function remote_get_body( string $url, array $args = array() ) {

	$response      = wp_safe_remote_get( $url, $args );
	$response_code = wp_remote_retrieve_response_code( $response );

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	if ( 200 !== $response_code ) {

		return new \WP_Error(
			$response_code,
			sprintf(
				// Translators: 1 URL 2 HTTP response code.
				__( 'url: %1$s Status code 200 expected but was %2$s.', 'tweakmaster' ),
				$url,
				$response_code
			)
		);
	}

	$response = wp_remote_retrieve_body( $response );

	if ( '' === $response ) {
		return new \WP_Error(
			'empty-body',
			sprintf(
				// Translators: URL.
				__( 'url: %s Empty Body.', 'tweakmaster' ),
				$url
			)
		);
	}

	return $response;
}
