<?php

// Execute all active snippets
add_action( 'plugins_loaded', 'mwcode_loaded', 1 );

function mwcode_loaded(  ) {
  global $mwcode_core, $current_mwcode_snippet;
  static $last_blocked_log = [];


  if ( !isset( $mwcode_core ) ) {
    $mwcode_core = new Meow_MWCODE_Core(  );
  }

  $snippets = $mwcode_core->execute_active_snippets(  );

  if ( empty( $snippets ) ) {
    return;
  }

  foreach ( $snippets as $snippet ) {

    if ( $snippet['blocked'] ) {
      // Determine the reason for blocking
      $page = isset( $_GET["page"] ) ? sanitize_text_field( $_GET["page"] ) : null;
      $is_settings_page = ( $page === 'mwcode_settings' );
      $is_rest = MeowKit_MWCODE_Helpers::is_rest();
      $is_rest_whitelisted = Meow_MWCODE_Core::is_white_listed_rest();
      
      $reason = '';
      if ( $is_settings_page ) {
        $reason = "on Code Engine settings page (disabled for safety)";
      } elseif ( $is_rest && !$is_rest_whitelisted ) {
        $requested_route = Meow_MWCODE_Core::get_requested_rest_route();
        $reason = "REST route not whitelisted: " . ($requested_route ?: 'unknown');
      } else {
        // Add more debugging info
        $debug_info = [
          'is_rest' => $is_rest,
          'is_whitelisted' => $is_rest_whitelisted,
          'page' => $page,
          'request_uri' => $_SERVER['REQUEST_URI'] ?? 'not set'
        ];
        $reason = "unknown reason (debug: " . json_encode($debug_info) . ")";
      }
      
      // Avoid duplicate logs within 2 seconds
      $log_key = $snippet['id'] . '-' . $reason;
      $current_time = time();
      
      if ( !isset($last_blocked_log[$log_key]) || ($current_time - $last_blocked_log[$log_key]) > 2 ) {
        $mwcode_core->log( "⚠️  Snippet blocked: " . $snippet['name'] . " (ID: " . $snippet['id'] . ") - " . $reason );
        $last_blocked_log[$log_key] = $current_time;
      }
      
      continue;
    }

    $current_mwcode_snippet = $snippet;
    

    ob_start(  );

    try {
      $mwcode_core->log( "Executing snippet: " . $snippet['name'] . " (ID: " . $snippet['id'] . ")" );
      eval( $snippet['code'] );
    } catch ( Throwable $e ) {
      $mwcode_core->log( "⚠️  Exception in snippet: " . $snippet['name'] . " (ID: " . $snippet['id'] . ")" );
      $mwcode_core->log( $e->getMessage(  ) );
      $mwcode_core->update_option( 'fatal_error', "\"{$snippet["name"]}\" - " . $e->getMessage(  ) );
      $mwcode_core->update_option( 'thrown_snippet', $snippet );
    }

    ob_end_clean(  );
  }
  
  $current_mwcode_snippet = null;
}

$file_path = MWCODE_PATH . '/classes/modules/helpers.php';
if ( file_exists( $file_path ) ) {
  require_once( $file_path );
}

$file_path_common = MWCODE_PATH . '/common/helpers.php';
if ( file_exists( $file_path_common ) ) {
  require_once( $file_path_common );
}


// In admin or Rest API request ( REQUEST URI begins with '/wp-json/' )
if ( is_admin(  ) || MeowKit_MWCODE_Helpers::is_rest(  ) || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
  global $mwcode_core;
  $mwcode_core = new Meow_MWCODE_Core(  );
}

// Define a global variable to store the currently executing snippet
global $current_mwcode_snippet;

/**
 * Custom error handler
 */
function mwcode_error_handler( $errno, $errstr, $errfile, $errline )
{
  global $current_mwcode_snippet, $mwcode_core;

  if ( !isset( $current_mwcode_snippet ) ) {
    return false;
  }

  if ( !isset( $mwcode_core ) ) {
    $mwcode_core = new Meow_MWCODE_Core(  );
  }

  if ( !( error_reporting(  ) & $errno ) ) {
    // This error code is not included in error_reporting
    return false;
  }

  if ( !array_key_exists( 'name', $current_mwcode_snippet ) ) {
    return false;
  }

  $error = "$errstr in $errfile on line $errline";

  $mwcode_core->log( "⚠️  Error in snippet: " . $current_mwcode_snippet["name"] . " ( ID: " . $current_mwcode_snippet["id"] . " )" );
  $mwcode_core->log( $error );

  $mwcode_core->update_option( 'fatal_error', "\"{$current_mwcode_snippet["name"]}\" - $errstr" );
  $mwcode_core->update_option( 'thrown_snippet', $current_mwcode_snippet );

  // Don't execute PHP internal error handler
  return true;
}

/**
 * Shutdown function to catch fatal errors
 */
function mwcode_shutdown_function(  )
{
  global $current_mwcode_snippet, $mwcode_core;

  if ( !isset( $current_mwcode_snippet ) ) {
    return;
  }

  if ( !isset( $mwcode_core ) ) {
    $mwcode_core = new Meow_MWCODE_Core(  );
  }

  $error = error_get_last(  );

  $fatal_error =  $error !== null && in_array( $error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR] );

  if ( $fatal_error ) {

    $mwcode_core->log( "⚠️  Fatal error in snippet: " . $current_mwcode_snippet["name"] . " ( ID: " . $current_mwcode_snippet["id"] . " )" );
    $mwcode_core->log( $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line'] );

    $mwcode_core->update_option( 'fatal_error', "\"{$current_mwcode_snippet["name"]}\" - " . $error['message'] );
    $mwcode_core->update_option( 'thrown_snippet', $current_mwcode_snippet );

  }
}

// Set the custom error handler and shutdown function
set_error_handler( "mwcode_error_handler" );
register_shutdown_function( "mwcode_shutdown_function" );


// Load all the JS functions to the front & back
// TODO: Add some restrictions by pages
function enqueue_mwcode_scripts(  )
{
  global $mwcode_core;

  if ( !isset( $mwcode_core ) ) {
    $mwcode_core = new Meow_MWCODE_Core(  );
  }

  wp_register_script( 'mwcode-scripts-js', '' );
  wp_enqueue_script( 'mwcode-scripts-js' );

  $js_code = '';

  if ( is_admin(  ) ) {
    $page = isset( $_GET["page"] ) ? sanitize_text_field( $_GET["page"] ) : null;
    if ( $page === 'mwai_settings' ) {
      $js_code = $mwcode_core->get_js_functions_to_push(  );
    }
  } else {
    $js_code = $mwcode_core->get_js_functions_to_push(  );
  }

  if ( $js_code ) {
    wp_add_inline_script( 'mwcode-scripts-js', $js_code, 'after' );
  }
}

add_action( 'wp_enqueue_scripts', 'enqueue_mwcode_scripts' );
add_action( 'admin_enqueue_scripts', 'enqueue_mwcode_scripts' );