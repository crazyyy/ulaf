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
  } else if ( strpos( $class, 'MeowKit_MWCODE_' ) !== false ) {
    $file = MWCODE_PATH . '/common/' . str_replace( 'meowkit_mwcode_', '', strtolower( $class ) ) . '.php';
  } else if ( strpos( $class, 'MeowKitPro_MWCODE_' ) !== false ) {
    $file = MWCODE_PATH . '/common/premium/' . str_replace( 'meowkitpro_mwcode_', '', strtolower( $class ) ) . '.php';
  } else if ( strpos( $class, 'MeowPro_MWCODE' ) !== false ) {
    $file = MWCODE_PATH . '/premium/' . str_replace( 'meowpro_mwcode_', '', strtolower( $class ) ) . '.php';
  }
  if ( $file && file_exists( $file ) ) {
    require( $file );
  }
} );

require_once( MWCODE_PATH . '/classes/api.php' );



