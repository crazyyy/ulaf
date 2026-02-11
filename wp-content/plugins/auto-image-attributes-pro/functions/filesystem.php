<?php
/**
 * Filesystem Operations.
 *
 * @since 4.0
 */

// Exit if accessed directly.
if ( ! defined('ABSPATH') ) exit;

/**
 * Initialize the WP filesystem.
 * 
 * @since 4.0
 */
function iaffpro_wp_filesystem_init() {
	
	global $wp_filesystem;
	
	if ( empty( $wp_filesystem ) ) {
		require_once( trailingslashit( ABSPATH ) . 'wp-admin/includes/file.php' );
		WP_Filesystem();
	}
}

/**
 * Write to a file using WP_Filesystem() functions.
 * 
 * @since 4.0
 *
 * @param $file (string) Filename with path.
 * @param $content (string) Contents to be written to the file. Default null.
 * 
 * @return (bool) True on success, false if file isn't passed or if writing failed.
 */
function iaffpro_file_put_contents( $file, $content = null ) {
	
	// Return false if no filename is provided
	if ( empty( $file ) ) {
		return false;
	}
	
	// Initialize the WP filesystem
	iaffpro_wp_filesystem_init();
	global $wp_filesystem;
	
	if( ! $wp_filesystem->put_contents( $file, $content, 0644 ) ) {
		return false;
	}
	
	return true;
}

/**
 * Read contents of a file using WP_Filesystem() functions.
 * 
 * @since 4.0 
 *
 * @param $file (string) Filename with path.
 * @param $array (boolean) Set true to return read data as an array. False by default.
 * 
 * @return (string|bool) The function returns the read data or false on failure.
 */
function iaffpro_file_get_contents( $file, $array = false ) {
	
	// Return false if no filename is provided
	if ( empty( $file ) ) {
		return false;
	}
	
	// Initialize the WP filesystem
	iaffpro_wp_filesystem_init();
	global $wp_filesystem;
	
	// Reads entire file into a string
	if ( $array === false ) {
		return $wp_filesystem->get_contents( $file );
	}
	
	// Reads entire file into an array
	return $wp_filesystem->get_contents_array( $file );
}

/**
 * Delete a file.
 * 
 * @since 4.0
 * 
 * @param $file (string) Filename with path.
 * 
 * @return (bool) True on success, false otherwise.
 */
function iaffpro_file_delete( $file ) {
	
	// Return false if no filename is provided
	if ( empty( $file ) ) {
		return false;
	}
	
	// Initialize the WP filesystem
	iaffpro_wp_filesystem_init();
	global $wp_filesystem;
	
	return $wp_filesystem->delete( $file );
}