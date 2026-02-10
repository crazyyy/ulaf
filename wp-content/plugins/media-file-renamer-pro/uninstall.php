<?php
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
  die;
}

$options = get_option( 'mfrh_options', null );
$clean_uninstall = $options['clean_uninstall'] ?? false;
if ( ! $clean_uninstall ) {
  return;
}

global $wpdb;
$options = $wpdb->get_results( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE 'mfrh_%'" );
foreach ( $options as $option ) {
  delete_option( $option->option_name );
}
