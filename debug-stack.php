<?php
/**
 * Unified Debug Stack for WordPress (Standalone Hosting)
 *
 * - Early PHP error handling (before wp-settings.php)
 * - WordPress-aware MU-level logging
 * - Environment-aware behavior
 * - Production-safe defaults
 *
 * @package Debug_Stack
 * @version 2.0.0
 */

if ( defined( 'WPINC' ) ) {
	return;
}

/**
 * ------------------------------------------------------------------------
 * Environment detection
 * ------------------------------------------------------------------------
 */

if ( ! defined( 'WP_ENV' ) ) {
	define( 'WP_ENV', 'production' );
}

$is_production = ( WP_ENV === 'production' );

/**
 * ------------------------------------------------------------------------
 * Configuration
 * ------------------------------------------------------------------------
 */

define( 'DEBUG_STACK_LOG_FILE', __DIR__ . '/wp-content/debug-stack.log' );
define( 'DEBUG_STACK_RATE_LIMIT', 50 );

/**
 * Limit logging to custom code only.
 * Empty array = log everything.
 */
define( 'DEBUG_STACK_NAMESPACES', array(
	'/wp-content/plugins/',
	'/wp-content/themes/',
	'/wp-content/mu-plugins/',
) );

/**
 * ------------------------------------------------------------------------
 * Internal state
 * ------------------------------------------------------------------------
 */

$GLOBALS['debug_stack_log_count'] = 0;

/**
 * ------------------------------------------------------------------------
 * Utilities
 * ------------------------------------------------------------------------
 */

/**
 * Write log entry with rate limiting.
 *
 * @param string $message Log message.
 * @return void
 */
function debug_stack_log( $message ) {
	if ( $GLOBALS['debug_stack_log_count'] >= DEBUG_STACK_RATE_LIMIT ) {
		return;
	}

	$GLOBALS['debug_stack_log_count']++;

	error_log(
		'[' . gmdate( 'Y-m-d H:i:s' ) . '] ' . $message . PHP_EOL,
		3,
		DEBUG_STACK_LOG_FILE
	);
}

/**
 * Check whether file belongs to allowed namespace.
 *
 * @param string $file File path.
 * @return bool
 */
function debug_stack_is_allowed_file( $file ) {
	foreach ( DEBUG_STACK_NAMESPACES as $fragment ) {
		if ( strpos( $file, $fragment ) !== false ) {
			return true;
		}
	}

	return false;
}

/**
 * Get REST route if available.
 *
 * @return string|null
 */
function debug_stack_get_rest_route() {
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST && isset( $_SERVER['REQUEST_URI'] ) ) {
		return $_SERVER['REQUEST_URI'];
	}

	return null;
}

/**
 * Format backtrace.
 *
 * @param int $limit Max depth.
 * @return string
 */
function debug_stack_backtrace( $limit = 20 ) {
	$trace  = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, $limit );
	$result = '';

	foreach ( $trace as $i => $step ) {
		$file = $step['file'] ?? 'N/A';

		if ( 'N/A' !== $file && ! debug_stack_is_allowed_file( $file ) ) {
			continue;
		}

		$result .= sprintf(
			"#%d %s%s() %s:%s\n",
			$i,
			$step['class'] ?? '',
			$step['function'] ?? '',
			$file,
			$step['line'] ?? 'N/A'
		);
	}

	return $result;
}

/**
 * ------------------------------------------------------------------------
 * PHP Error Handler (Early)
 * ------------------------------------------------------------------------
 */

/**
 * PHP error handler.
 *
 * @param int    $type Error type.
 * @param string $message Error message.
 * @param string $file File.
 * @param int    $line Line.
 * @return bool
 */
function debug_stack_php_error_handler( $type, $message, $file, $line ) {

	if ( ! ( error_reporting() & $type ) ) {
		return false;
	}

	if ( ! debug_stack_is_allowed_file( $file ) ) {
		return false;
	}

	debug_stack_log( '=== PHP ERROR ===' );
	debug_stack_log( "TYPE: {$type}" );
	debug_stack_log( "MESSAGE: {$message}" );
	debug_stack_log( "FILE: {$file}" );
	debug_stack_log( "LINE: {$line}" );

	if ( ! $GLOBALS['is_production'] ) {
		debug_stack_log( "BACKTRACE:\n" . debug_stack_backtrace( 25 ) );
	}

	return false;
}

/**
 * Shutdown handler for fatal errors.
 *
 * @return void
 */
function debug_stack_shutdown_handler() {
	$error = error_get_last();

	if ( ! $error ) {
		return;
	}

	if ( ! in_array( $error['type'], array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR ), true ) ) {
		return;
	}

	if ( ! debug_stack_is_allowed_file( $error['file'] ) ) {
		return;
	}

	debug_stack_log( '=== FATAL ERROR ===' );
	debug_stack_log( $error['message'] );
	debug_stack_log( $error['file'] . ':' . $error['line'] );
}

/**
 * ------------------------------------------------------------------------
 * Bootstrap early handlers
 * ------------------------------------------------------------------------
 */

set_error_handler( 'debug_stack_php_error_handler' );
register_shutdown_function( 'debug_stack_shutdown_handler' );

/**
 * ------------------------------------------------------------------------
 * WordPress-aware layer (late)
 * ------------------------------------------------------------------------
 */

add_action(
	'muplugins_loaded',
	function () {

		global $wpdb;

		debug_stack_log( 'Debug Stack loaded (WP layer)' );

		if ( defined( 'SAVEQUERIES' ) && SAVEQUERIES && ! $GLOBALS['is_production'] ) {
			add_action(
				'shutdown',
				function () use ( $wpdb ) {
					foreach ( (array) $wpdb->queries as $query ) {
						debug_stack_log(
							sprintf(
								'SQL (%ss): %s | %s',
								$query[1],
								$query[0],
								$query[2]
							)
						);
					}
				}
			);
		}
	},
	1
);
