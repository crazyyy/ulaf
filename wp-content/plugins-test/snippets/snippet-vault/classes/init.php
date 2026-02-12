<?php

if ( class_exists( 'MeowPro_MWCODE_Core' ) && class_exists( 'Meow_MWCODE_Core' ) ) {
  function mwcode_thanks_admin_notices(  )
  {
    echo '<div class="error"><p>' . __( 'Thanks for installing the Pro version of Code Engine : ) However, the free version is still enabled. Please disable or uninstall it.', 'code-engine' ) . '</p></div>';
  }
  add_action( 'admin_notices', 'mwcode_thanks_admin_notices' );
  return;
}

spl_autoload_register( function ( $class ) {
  $file = null;
  if ( strpos( $class, 'Meow_MWCODE' ) !== false ) {
    $file = MWCODE_PATH . '/classes/' . str_replace( 'meow_mwcode_', '', strtolower( $class ) ) . '.php';
  }
  if ( strpos( $class, 'Meow_MWCODE_Modules' ) !== false ) {
    $file = MWCODE_PATH . '/classes/modules/' . str_replace( 'meow_mwcode_modules_', '', strtolower( $class ) ) . '.php';
  } else if ( strpos( $class, 'MeowCommon_' ) !== false ) {
    $file = MWCODE_PATH . '/common/' . str_replace( 'meowcommon_', '', strtolower( $class ) ) . '.php';
  } else if ( strpos( $class, 'MeowCommonPro_' ) !== false ) {
    $file = MWCODE_PATH . '/common/premium/' . str_replace( 'meowcommonpro_', '', strtolower( $class ) ) . '.php';
  } else if ( strpos( $class, 'MeowPro_MWCODE' ) !== false ) {
    $file = MWCODE_PATH . '/premium/' . str_replace( 'meowpro_mwcode_', '', strtolower( $class ) ) . '.php';
  }
  if ( $file && file_exists( $file ) ) {
    require( $file );
  }
} );

//require_once(  MWCODE_PATH . '/classes/api.php' );


$file_path = MWCODE_PATH . '/classes/modules/helpers.php';
if ( file_exists( $file_path ) ) {
  require_once( $file_path );
}

$file_path_common = MWCODE_PATH . '/common/helpers.php';
if ( file_exists( $file_path_common ) ) {
  require_once( $file_path_common );
}


// In admin or Rest API request ( REQUEST URI begins with '/wp-json/' )
if ( is_admin(  ) || MeowCommon_Helpers::is_rest(  ) || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
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

// Execute all active snippets
add_action( 'plugins_loaded', function (  ) {
  global $mwcode_core, $current_mwcode_snippet;
  if ( !isset( $mwcode_core ) ) {
    $mwcode_core = new Meow_MWCODE_Core(  );
  }
  $snippets = $mwcode_core->execute_active_snippets(  );
  $scheduled = new Meow_MWCODE_Modules_Cron( $mwcode_core );

  if ( empty( $snippets ) ) {
    return;
  }

  foreach ( $snippets as $snippet ) {

    if ( $snippet['blocked'] ) {
      $mwcode_core->log( "⚠️  Snippet is blocked: " . $snippet['name'] . " ( ID: " . $snippet['id'] . " ) Because it's not a frontend request, on a non authorized page." );
      continue;
    }

    $current_mwcode_snippet = $snippet;

    ob_start(  );

    try {
      eval( $snippet['code'] );
    } catch ( Throwable $e ) {
      $mwcode_core->log( "⚠️  Exception in snippet: " . $snippet['name'] . " ( ID: " . $snippet['id'] . " )" );
      $mwcode_core->log( $e->getMessage(  ) );
      $mwcode_core->update_option( 'fatal_error', "\"{$snippet["name"]}\" - " . $e->getMessage(  ) );
      $mwcode_core->update_option( 'thrown_snippet', $snippet );
    }

    ob_end_clean(  );
  }
  
  $current_mwcode_snippet = null;
} );

// Load all the JS functions to the front & back
// TODO: Add some restrictions by pages
function enqueue_mwcode_scripts(  )
{
  global $mwcode_core;

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
