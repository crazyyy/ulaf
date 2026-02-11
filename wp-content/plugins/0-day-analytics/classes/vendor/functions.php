<?php
/**
 * Helper functions to enable settings.
 *
 * @package 0-day
 */

use ADVAN\Helpers\Settings;

// Offer our own error logging if there is no way to enable WP_DEBUG and nothing else works.
if ( Settings::get_option( 'plugin_debug_enable' ) ) {
	if ( function_exists( 'error_reporting' ) ) {
		error_reporting( E_ALL );
	}

	ini_set( 'display_errors', 0 );

	$log_path = WP_CONTENT_DIR . '/debug.log';

	if ( $log_path ) {
		ini_set( 'log_errors', 1 );
		ini_set( 'error_log', $log_path );
	}
}
