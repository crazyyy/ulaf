<?php
/**
 * Event Logging Functions.
 *
 * @since 4.0
 */

// Exit if accessed directly.
if ( ! defined('ABSPATH') ) exit;

// Create log folder
add_action( 'iaff_before_bulk_updater', 'iaffpro_event_log_init_folder' );

/**
 * Create /iap-logs folder and initialize it with .htaccess and index.html.
 * 
 * @since 4.0
 */
function iaffpro_event_log_init_folder() {

	// Initialize the WP filesystem
	iaffpro_wp_filesystem_init();
	global $wp_filesystem;

	$upload_dir 	= wp_get_upload_dir();
	$logs_dir 		= $upload_dir['basedir'] . '/iap-logs';

	// Return if files already exist.
	if ( $wp_filesystem->exists( trailingslashit( $logs_dir ) . '.htaccess' ) ) {
		return;
	}

	// Create folder /iap-logs/ in WordPress uploads folder.
	if ( wp_mkdir_p( $logs_dir ) ) {

		// Add .htaccess to prevent hotlinking.
		iaffpro_file_put_contents( trailingslashit( $logs_dir ) . '.htaccess', 'deny from all' );

		// Add empty index.html file into the folder.
		iaffpro_file_put_contents( trailingslashit( $logs_dir ) . 'index.html', '' );
	}
}

/**
 * Append log to the log file.
 * 
 * @since 4.0
 * 
 * @param $content (string) Content to be logged.
 * @param $timestamp (boolean) Set to false to prevent adding of timestamp to log. True by default.
 * 
 * @return (bool) True on success, false on failure.
 */
function iaffpro_event_log_append( $content, $timestamp = true ) {

	$upload_dir 	= wp_get_upload_dir();
	$log_file 		= $upload_dir['basedir'] . '/iap-logs/iap-bu-log.log';

	// Read the current log file.
	$file_content = iaffpro_file_get_contents( $log_file );

	// Add timestamp
	$content = ( $timestamp === true ) ? date( 'd-M-Y H:i:s e', time() ) . ' : ' . $content : $content;

	// Append new content to file if the file is not empty.
	$file_content = ( $file_content === false ) ? $content : $file_content . PHP_EOL . $content;

	// Write the log to file.
	iaffpro_file_put_contents( $log_file, $file_content );
}

/** 
 * Read event log from the log file.
 * 
 * @since 4.0
 * 
 * @param $limit (int) Number of lines to read. If a limit n is specified, the last n lines are read. Defaults to 0 for all.
 * 
 * @return (array) Log entires from the log file as an array. Empty array on failure.
 */
function iaffpro_event_log_read( $limit = 0 ) {

	$upload_dir 	= wp_get_upload_dir();
	$log_file 		= $upload_dir['basedir'] . '/iap-logs/iap-bu-log.log';

	// Read the current log file.
	$file_content = iaffpro_file_get_contents( $log_file, true );

	if ( $file_content === false ) {
		return array();
	}

	if ( $limit > 0 ) {
		$limit = ( $limit > count( $file_content ) ) ? count( $file_content ) : $limit;
		$file_content = array_slice( $file_content, - $limit );
	}

	return $file_content;
}

/**
 * Delete Event Log file.
 * 
 * @since 4.1
 * 
 * @return (bool) True on success, false otherwise.
 */
function iaffpro_event_log_delete() {

	$upload_dir = wp_get_upload_dir();
	$log_file 	= $upload_dir['basedir'] . '/iap-logs/iap-bu-log.log';
	
	return iaffpro_file_delete( $log_file );
}

/**
 * Handle AJAX request to delete Event Log when delete button is clicked.
 * 
 * @since 4.1
 */
function iaffpro_event_log_delete_handle_ajax() {
	
	// Security Check
	check_ajax_referer( 'iaff_bulk_updater_delete_log_nonce', 'security' );

	// Delete the event log
	$file_deleted = iaffpro_event_log_delete();

	$response_message = $file_deleted ? 
		__( 'Event Log file deleted. The log above will be cleared when you refresh the page.', 'auto-image-attributes-pro' ) :
		__( 'Event Log file could not be deleted. Please delete it manually in /wp-content/uploads/iap-logs/iap-bu-log.log', 'auto-image-attributes-pro' );
	
	$response = array(
		'message' => $response_message,
	);
	wp_send_json( $response );
}
add_action( 'wp_ajax_iaff_bulk_updater_delete_log', 'iaffpro_event_log_delete_handle_ajax' );