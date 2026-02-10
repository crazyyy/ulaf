<?php
/**
 * Security Headers
 * Configure security defenses in web browsers.
 * 
 * @since 1.0.0 
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Stop if accessed directly. 
}

$base_url = sanitize_url( (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" );

header('X-XSS-Protection: 1; mode=block');
header('X-Frame-Options: sameorigin');
header('Content-Security-Policy: frame-ancestors self ' . $base_url . '; upgrade-insecure-requests');
header('Referrer-Policy: strict-origin-when-cross-origin, origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), midi=(), camera=(), usb=(), magnetometer=(), accelerometer=(), gyroscope=(), microphone=()');
header('X-Content-Type-Options: nosniff');
header('Strict-Transport-Security: max-age=15552000; includeSubDomains; preload');
header('Set-Cookie: sess=123; path=/; Secure; HttpOnly; SameSite');
