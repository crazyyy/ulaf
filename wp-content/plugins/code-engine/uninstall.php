<?php
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

function mwcode_remove_database() {
	global $wpdb;
	$table_name1 = $wpdb->prefix . "mwcode_snippets";
	$sql = "DROP TABLE IF EXISTS $table_name1";
	$wpdb->query( $sql );

	
}

function mwcode_remove_options() {

	// Delete the options
	$option_functions = 'mwcode_functions';
    $option_intervals = 'mwcode_intervals';
	$options = 'mwcode_options';

	delete_option( $options );
	delete_option( $option_functions );
	delete_option( $option_intervals );
}

function mwcode_uninstall () {
	$options = get_option( 'mwcode_options' );
	$cleanUninstall = array_key_exists( 'clean_uninstall', $options ) ? $options['clean_uninstall'] : false;

	if ( $cleanUninstall ) {
		mwcode_remove_options();
		mwcode_remove_database();
	}

}

mwcode_uninstall();