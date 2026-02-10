<?php
/**
 * Debug Backtrace Logger
 * 
 * Enables detailed error logging with backtrace for debugging WordPress issues.
 * Only activates if WP_DEBUG constants are not already defined.
 * 
 * @package Debug_Backtrace
 * @version 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Enable WP_DEBUG only if not already defined
if ( ! defined( 'WP_DEBUG' ) ) {
    define( 'WP_DEBUG', true );
}

// Enable WP_DEBUG_LOG only if not already defined
if ( ! defined( 'WP_DEBUG_LOG' ) ) {
    define( 'WP_DEBUG_LOG', true );
}

// Disable WP_DEBUG_DISPLAY only if not already defined
if ( ! defined( 'WP_DEBUG_DISPLAY' ) ) {
    define( 'WP_DEBUG_DISPLAY', false );
}

// Set PHP ini settings only if not in CLI mode
if ( php_sapi_name() !== 'cli' ) {
    @ini_set( 'display_errors', 0 );
    @ini_set( 'log_errors', 'on' );
}

// Define SAVEQUERIES only if not already defined
if ( ! defined( 'SAVEQUERIES' ) ) {
    define( 'SAVEQUERIES', false );
}

// Define SCRIPT_DEBUG only if not already defined
if ( ! defined( 'SCRIPT_DEBUG' ) ) {
    define( 'SCRIPT_DEBUG', false );
}

/**
 * Custom error handler for detailed backtrace logging
 * 
 * @param int    $errno   Error level
 * @param string $errstr  Error message
 * @param string $errfile File where error occurred
 * @param int    $errline Line number where error occurred
 * @return bool False to continue with normal error handling
 */
if ( ! function_exists( 'custom_error_handler_with_backtrace' ) ) {
    function custom_error_handler_with_backtrace( $errno, $errstr, $errfile, $errline ) {
        // Only log specific error types
        $error_patterns = array(
            'Attempt to read property',
            'Trying to access array offset on',
            'Undefined variable',
            'Undefined array key',
            'Undefined index',
        );
        
        $should_log = false;
        foreach ( $error_patterns as $pattern ) {
            if ( strpos( $errstr, $pattern ) !== false ) {
                $should_log = true;
                break;
            }
        }
        
        if ( $should_log ) {
            // Log the error with file and line
            error_log( "=== ERROR DETECTED ===" );
            error_log( "ERROR: $errstr in $errfile on line $errline" );
            
            // Get backtrace
            $backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
            
            // Format backtrace for better readability
            error_log( "BACKTRACE:" );
            foreach ( $backtrace as $index => $trace ) {
                $file = isset( $trace['file'] ) ? $trace['file'] : 'N/A';
                $line = isset( $trace['line'] ) ? $trace['line'] : 'N/A';
                $function = isset( $trace['function'] ) ? $trace['function'] : 'N/A';
                $class = isset( $trace['class'] ) ? $trace['class'] . '::' : '';
                
                error_log( "  #$index {$class}{$function}() called at [$file:$line]" );
            }
            error_log( "=== END ERROR ===" );
        }
        
        // Return false to continue with normal PHP error handling
        return false;
    }
    
    // Set custom error handler only for warnings
    set_error_handler( 'custom_error_handler_with_backtrace', E_WARNING | E_NOTICE );
}

/**
 * Log when MU plugin is loaded (for verification)
 */
add_action( 'muplugins_loaded', function() {
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( 'Debug Backtrace MU Plugin: Loaded successfully' );
    }
}, 1 );