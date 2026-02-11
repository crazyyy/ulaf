<?php
/**
 * MU Debug Backtrace Logger
 *
 * Logs PHP warnings, notices and fatal errors with full backtrace
 * into a dedicated log file.
 *
 * @package MU_Debug_Backtrace
 * @version 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

namespace MU_Debug_Backtrace;

/**
 * Absolute path to custom debug log file.
 */
const LOG_FILE = WP_CONTENT_DIR . '/debug-backtrace.log';

/**
 * Write message to custom log file.
 *
 * @param string $message Log message.
 * @return void
 */
function log_message( $message ) {
    error_log(
        '[' . gmdate( 'Y-m-d H:i:s' ) . '] ' . $message . PHP_EOL,
        3,
        LOG_FILE
    );
}

/**
 * Format debug backtrace into readable string.
 *
 * @param int $limit Max stack depth.
 * @return string
 */
function format_backtrace( $limit = 20 ) {
    $trace  = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, $limit );
    $output = '';

    foreach ( $trace as $i => $step ) {
        $file     = $step['file'] ?? 'N/A';
        $line     = $step['line'] ?? 'N/A';
        $function = $step['function'] ?? 'N/A';
        $class    = isset( $step['class'] ) ? $step['class'] . '::' : '';

        $output .= sprintf(
            "#%d %s%s() at %s:%s\n",
            $i,
            $class,
            $function,
            $file,
            $line
        );
    }

    return $output;
}

/**
 * PHP error handler (warnings, notices, deprecated).
 *
 * @param int    $errno Error level.
 * @param string $errstr Error message.
 * @param string $errfile File path.
 * @param int    $errline Line number.
 * @return bool
 */
function php_error_handler( $errno, $errstr, $errfile, $errline ) {

    if ( ! ( error_reporting() & $errno ) ) {
        return false;
    }

    log_message( '=== PHP ERROR ===' );
    log_message( "TYPE: {$errno}" );
    log_message( "MESSAGE: {$errstr}" );
    log_message( "FILE: {$errfile}" );
    log_message( "LINE: {$errline}" );
    log_message( "URL: " . ( $_SERVER['REQUEST_URI'] ?? 'CLI' ) );
    log_message( 'BACKTRACE:' );
    log_message( format_backtrace( 25 ) );
    log_message( '=== END ERROR ===' );

    return false;
}

/**
 * Shutdown handler for fatal errors.
 *
 * @return void
 */
function shutdown_handler() {
    $error = error_get_last();

    if ( $error && in_array(
        $error['type'],
        array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR ),
        true
    ) ) {
        log_message( '=== FATAL ERROR ===' );
        log_message( "TYPE: {$error['type']}" );
        log_message( "MESSAGE: {$error['message']}" );
        log_message( "FILE: {$error['file']}" );
        log_message( "LINE: {$error['line']}" );
        log_message( '=== END FATAL ERROR ===' );
    }
}

/**
 * Bootstrap plugin.
 *
 * @return void
 */
function bootstrap() {
    set_error_handler( __NAMESPACE__ . '\\php_error_handler' );
    register_shutdown_function( __NAMESPACE__ . '\\shutdown_handler' );

    log_message( 'MU Debug Backtrace loaded' );
}

bootstrap();
