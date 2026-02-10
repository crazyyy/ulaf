<?php
/**
 * AdminEase Uninstall Handler
 * This file is executed when the plugin is deleted from the WordPress admin.
 * It handles cleanup operations including:
 * - Removing database tables
 * - Clearing scheduled cron jobs
 * - Deleting plugin options (optional)
 * @package AdminEase
 */

// Exit if accessed directly or not in uninstall context
if( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Drop Network Viewer database table
 */
function adminease_drop_network_viewer_table() {
	global $wpdb;
	
	// Alternative for WordPress < 6.2
	$table_name = esc_sql( $wpdb->prefix . 'adminease_network_log' );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Intentional schema removal during plugin uninstallation, table name is escaped
	$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
	
	error_log( 'AdminEase: Network Viewer table dropped during uninstall' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
}

/**
 * Clear Network Viewer scheduled cron jobs
 */
function adminease_clear_network_viewer_cron() {
	$timestamp = wp_next_scheduled( 'adminease_cleanup_network_logs' );
	
	if( $timestamp ) {
		wp_unschedule_event( $timestamp, 'adminease_cleanup_network_logs' );
		error_log( 'AdminEase: Network Viewer cron cleared during uninstall' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}
}

/**
 * Optional: Delete plugin options
 * Uncomment if you want to remove all plugin settings on uninstall
 */
function adminease_delete_plugin_options() {
	// Delete main plugin options
	delete_option( 'adminease_settings' );
	delete_option( 'adminease_activation_status' );
	delete_option( 'adminease_activation_errors' );
	delete_option( 'adminease_deactivation_status' );
	delete_option( 'adminease_deactivation_errors' );
	
	error_log( 'AdminEase: Plugin options deleted during uninstall' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
}

// Execute cleanup operations
adminease_drop_network_viewer_table();
adminease_clear_network_viewer_cron();

// Optionally delete plugin options (uncomment the line below to enable)
// adminease_delete_plugin_options();

error_log( 'AdminEase: Uninstall completed successfully' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log