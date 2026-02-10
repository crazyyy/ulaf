<?php
/**
 * Plugin Name: AdminEase
 * Description: Boosts your WordPress admin with tools for updates, security, performance, and user management - no coding required.
 * Version:     1.5.3
 * Author:      PrecisionWP
 * Author URI:  https://precisionwp.net/
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: adminease
 * Domain Path: /languages
 */

defined( 'ABSPATH' ) || exit;

// Plugin constants.
define( 'ADMINEASE_VERSION', '1.5.3' );
define( 'ADMINEASE_NAME', 'AdminEase' );
define( 'ADMINEASE_SLUG', 'adminease' );
define( 'ADMINEASE_FILE', __FILE__ );
define( 'ADMINEASE_DIR', plugin_dir_path( __FILE__ ) );
define( 'ADMINEASE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ADMINEASE_BASENAME', plugin_basename( __FILE__ ) );

if( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

if( !version_compare( PHP_VERSION, '7.4', '>=' ) ) {
	add_action( 'admin_notices', 'adminease_fail_php_version' );
} else if( !version_compare( get_bloginfo( 'version' ), '5.0', '>=' ) ) {
	add_action( 'admin_notices', 'adminease_fail_wp_version' );
} else if( class_exists( '\AdminEase\Plugin' ) ) {
	// Initialize the plugin after all plugins are loaded.
	add_action( 'plugins_loaded', function() {
		\AdminEase\Plugin::get_instance();
	} );
}

/**
 * Outputs an error message informing the user that the PHP version is outdated.
 * @return void
 */
function adminease_fail_php_version(): void {
	$html_message = sprintf(
		'<div class="error"><h3>%1$s</h3><p>%2$s</p></div>',
		esc_html__( 'AdminEase isn’t running because PHP is outdated.', 'adminease' ),
		sprintf(
		/* translators: %s: PHP version. */
			esc_html__( 'Update to version %s and get back to creating!', 'adminease' ),
			'7.4'
		),
	);
	
	echo wp_kses_post( $html_message );
}

/**
 * Outputs an error message notifying the user that AdminEase cannot run due to an outdated WordPress version.
 * @return void
 */
function adminease_fail_wp_version(): void {
	$html_message = sprintf(
		'<div class="error"><h3>%1$s</h3><p>%2$s</p></div>',
		esc_html__( 'AdminEase isn’t running because WordPress is outdated.', 'adminease' ),
		sprintf(
		/* translators: %s: WordPress version. */
			esc_html__( 'Update to version %s and get back to creating!', 'adminease' ),
			'5.0'
		),
	);
	
	echo wp_kses_post( $html_message );
}

// Register the activation hook
register_activation_hook( __FILE__, 'adminease_activation_handler' );

/**
 * Handles the activation process for the AdminEase plugin.
 * This function initializes the FileHandler, creates backups of critical files,
 * logs any errors encountered during the process, and updates the activation status
 * to notify the system of the activation outcome.
 * @return void
 */
function adminease_activation_handler() {
	// Initialize the FileHandler
	$file_handler = new \AdminEase\FileHandler();
	
	// Step 1: Create backups of original files
	$wp_config_backup = $file_handler->create_backup( 'wp-config' );
	$htaccess_backup  = $file_handler->create_backup( 'htaccess' );
	
	// Step 2: Check for errors and log them
	if( !$wp_config_backup || !$htaccess_backup ) {
		$errors = $file_handler->get_errors();
		
		error_log( 'AdminEase: Failed to create backups during activation: ' . implode( ', ', $errors ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Using error_log for debugging purposes
		
		// Store activation status for admin notice
		update_option( 'adminease_activation_status', 'backup_error' );
		update_option( 'adminease_activation_errors', $errors );
		
		return; // Exit if backup creation fails
	}
	
	// Step 3: Log successful backup creation
	error_log( 'AdminEase: Backup files created successfully during activation' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Using error_log for debugging purposes
	
	// Step 4: Create Network Viewer database table if feature is enabled
	$settings = get_option( 'adminease_settings', [] );
	if( !empty( $settings['debug']['network_viewer_enabled'] ) ) {
		$network_viewer = new \AdminEase\Features\NetworkViewer();
		$network_viewer->create_table();
		error_log( 'AdminEase: Network Viewer table created during activation' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Using error_log for debugging purposes
	}
	
	// Step 5: Store activation status for admin notice
	update_option( 'adminease_activation_status', 'success' );
}

register_deactivation_hook( __FILE__, 'adminease_deactivation_handler' );

/**
 * Handles the deactivation process for the AdminEase plugin by restoring files from backup,
 * cleaning up any remaining markers, and optionally deleting backup files.
 * The method performs the following steps:
 * 1. Restores all files from backup.
 * 2. Cleans up any remaining AdminEase markers.
 * 3. Deletes backup files (optional).
 * It logs errors if any step fails and logs a success message if the deactivation process completes without issues.
 * @return void
 */
function adminease_deactivation_handler() {
	// Initialize the FileHandler
	$file_handler = new \AdminEase\FileHandler();
	
	// Clear any pending stacks
	$file_handler->clear_all_stacks();
	
	// Check backup status first
	$backup_status = $file_handler->check_backup_status();
	
	// Log backup status
	error_log( 'AdminEase: Backup status during deactivation: ' . print_r( $backup_status, true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r,WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Using print_r for debugging purposes
	
	// Step 1: Try safe restoration first
	$restore_success = $file_handler->safe_restore_from_backup( 'wp-config' );
	
	if( !$restore_success ) {
		// Step 2: Try force restoration if safe restoration fails
		error_log( 'AdminEase: Safe restoration failed, attempting force restoration' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Using error_log for debugging purposes
		$restore_success = $file_handler->force_restore_from_backup( 'wp-config' );
	}
	
	// Clear errors before next operation
	$file_handler->clear_errors();
	
	// Restore .htaccess
	$htaccess_restore = $file_handler->safe_restore_from_backup( 'htaccess' );
	
	if( !$htaccess_restore ) {
		$htaccess_restore = $file_handler->force_restore_from_backup( 'htaccess' );
	}
	
	// Step 3: Clean up any remaining markers as fallback
	$file_handler->cleanup_all_markers();
	
	// Step 4: Store deactivation results
	if( $restore_success && $htaccess_restore ) {
		update_option( 'adminease_deactivation_status', 'success' );
		error_log( 'AdminEase: Files restored successfully during deactivation' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Using error_log for debugging purposes
	} else {
		update_option( 'adminease_deactivation_status', 'partial_success' );
		update_option( 'adminease_deactivation_errors', $file_handler->get_errors() );
		error_log( 'AdminEase: Deactivation completed with some issues' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Using error_log for debugging purposes
	}
	
	// Step 5: Optionally delete backups (only if restoration was successful)
	if( $restore_success && $htaccess_restore ) {
		$file_handler->delete_backups();
	}
}