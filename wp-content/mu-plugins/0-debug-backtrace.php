<?php
/**
 * Debug Backtrace Logger
 * 
 * Enables detailed error logging with backtrace for debugging WordPress issues.
 * 
 * @package Debug_Backtrace
 * @version 1.0.2
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
 * Shutdown handler to catch all errors including those handled by WordPress
 */
function wp_debug_backtrace_shutdown_handler() {
    $error = error_get_last();
    
    if ( $error && in_array( $error['type'], array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR ) ) ) {
        error_log( "=== FATAL ERROR DETECTED ===" );
        error_log( "TYPE: " . $error['type'] );
        error_log( "MESSAGE: " . $error['message'] );
        error_log( "FILE: " . $error['file'] );
        error_log( "LINE: " . $error['line'] );
        error_log( "=== END FATAL ERROR ===" );
        error_log( "" );
    }
}
register_shutdown_function( 'wp_debug_backtrace_shutdown_handler' );

/**
 * Hook into WordPress to log warnings with backtrace
 */
add_action( 'admin_init', function() {
    // Add filter to capture doing_it_wrong and deprecated notices
    add_action( 'doing_it_wrong_run', function( $function, $message, $version ) {
        if ( strpos( $function, '_load_textdomain_just_in_time' ) !== false ) {
            error_log( "=== DOING IT WRONG DETECTED ===" );
            error_log( "FUNCTION: $function" );
            error_log( "MESSAGE: $message" );
            error_log( "VERSION: $version" );
            
            $backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 20 );
            error_log( "BACKTRACE:" );
            foreach ( $backtrace as $index => $trace ) {
                $file = isset( $trace['file'] ) ? $trace['file'] : 'N/A';
                $line = isset( $trace['line'] ) ? $trace['line'] : 'N/A';
                $function = isset( $trace['function'] ) ? $trace['function'] : 'N/A';
                $class = isset( $trace['class'] ) ? $trace['class'] . '::' : '';
                
                error_log( "  #$index {$class}{$function}() at [$file:$line]" );
                
                if ( strpos( $file, '/wp-content/themes/' ) !== false || 
                     strpos( $file, '/wp-content/plugins/' ) !== false ) {
                    error_log( "  >>> CUSTOM CODE: $file <<<" );
                }
            }
            error_log( "=== END DOING IT WRONG ===" );
            error_log( "" );
        }
    }, 10, 3 );
}, 1 );

/**
 * Intercept errors before WordPress handles them
 */
add_action( 'init', function() {
    set_error_handler( function( $errno, $errstr, $errfile, $errline ) {
        static $logged_errors = array();
        
        // Patterns to detect
        $error_patterns = array(
            'Attempt to read property',
            'Trying to access array offset on',
            'on null',
            'on false',
        );
        
        $should_log = false;
        foreach ( $error_patterns as $pattern ) {
            if ( stripos( $errstr, $pattern ) !== false ) {
                $should_log = true;
                break;
            }
        }
        
        // Only log template.php errors with backtrace
        if ( $should_log && strpos( $errfile, 'wp-admin/includes/template.php' ) !== false ) {
            $error_key = md5( $errstr . $errfile . $errline );
            
            if ( ! isset( $logged_errors[ $error_key ] ) ) {
                $logged_errors[ $error_key ] = true;
                
                error_log( "=== ERROR DETECTED (template.php) ===" );
                error_log( "MESSAGE: $errstr" );
                error_log( "FILE: $errfile" );
                error_log( "LINE: $errline" );
                
                $backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 25 );
                error_log( "BACKTRACE:" );
                
                foreach ( $backtrace as $index => $trace ) {
                    $file = isset( $trace['file'] ) ? $trace['file'] : 'N/A';
                    $line = isset( $trace['line'] ) ? $trace['line'] : 'N/A';
                    $function = isset( $trace['function'] ) ? $trace['function'] : 'N/A';
                    $class = isset( $trace['class'] ) ? $trace['class'] . '::' : '';
                    
                    error_log( "  #$index {$class}{$function}() at [$file:$line]" );
                    
                    if ( strpos( $file, '/wp-content/themes/' ) !== false || 
                         strpos( $file, '/wp-content/plugins/' ) !== false ||
                         strpos( $file, '/wp-content/mu-plugins/' ) !== false ) {
                        error_log( "  >>> CUSTOM CODE FOUND: $file <<<" );
                    }
                }
                error_log( "=== END ERROR ===" );
            }
        }
        
        // Повертаємо false, щоб стандартний обробник WordPress (або PHP) продовжив роботу
        return false;
    } );
}, PHP_INT_MAX );

/**
 * Log when MU plugin is loaded
 */
add_action( 'muplugins_loaded', function() {
    error_log( '=== Debug Backtrace MU Plugin v1.0.2: Loaded successfully ===' );
}, 1 );