<?php
/**
 * Authority Mailer SMTP Uninstall
 *
 * Fired when the plugin is uninstalled.
 * Performs comprehensive cleanup of all plugin options, transients, and database tables.
 *
 * @package Authority_Mailer
 * @since   1.0.0
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Check if user wants to remove data on uninstall.
 *
 * @return bool True if data should be removed, false otherwise.
 */
function authority_mailer_smtp_should_remove_data() {
	$uninstall_settings = get_option( 'authority_mailer_uninstall_settings', array() );

	// Default to true if setting doesn't exist.
	// This preserves the original behavior where data was always removed on uninstall,
	// ensuring we don't accidentally keep data for users who never configured the setting.
	if ( ! isset( $uninstall_settings['remove_data_on_uninstall'] ) ) {
		return false;
	}

	return (bool) $uninstall_settings['remove_data_on_uninstall'];
}

/**
 * Delete all plugin options and transients.
 *
 * Removes all options created by the plugin including:
 * - Core plugin options (SMTP settings, onboarding state)
 * - Premium feature options (feature flags, notification settings, etc.)
 * - All plugin transients
 */
function authority_mailer_smtp_uninstall_cleanup_options() {
	// Core plugin options.
	delete_option( 'authority_mailer_smtp_options' );
	delete_option( 'authority_mailer_onboarding_completed' );
	delete_option( 'authority_mailer_version' );

	// Premium feature options.
	delete_option( 'authority-mailer_premium_features' );
	delete_option( 'authority-mailer_analytics_db_version' );

	// Premium settings options.
	delete_option( 'authority_mailer_notification_rules' );
	delete_option( 'authority_mailer_notification_settings' );
	delete_option( 'authority_mailer_compliance_settings' );
	delete_option( 'authority_mailer_custom_templates' );
	delete_option( 'authority_mailer_health_alerts' );
	delete_option( 'authority_mailer_failover_mode' );
	delete_option( 'authority_mailer_failover_providers' );
	delete_option( 'authority_mailer_rr_index' );
	delete_option( 'authority_mailer_email_defaults' );
	delete_option( 'authority_mailer_uninstall_settings' );
	delete_option( 'authority_mailer_dismissed_notices' );

	// Delete transients.
	delete_transient( 'authority_mailer_smtp_activation_redirect' );

	// Delete dynamic transients (provider cooldown transients).
	// These are prefixed with 'authority_mailer_provider_cooldown_'.
	global $wpdb;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
			'_transient_authority_mailer_provider_cooldown_%',
			'_transient_timeout_authority_mailer_provider_cooldown_%'
		)
	);
}

/**
 * Drop all plugin database tables.
 *
 * Removes all database tables created by the plugin including:
 * - Core email log table
 * - Premium analytics tables (email events, tracking, recipient profiles, etc.)
 *
 * Only call this if you want to completely remove user data.
 * Consider giving users an option in settings to keep data.
 */
function authority_mailer_smtp_uninstall_drop_tables() {
	global $wpdb;

	// All tables created by the plugin.
	// Table names are hardcoded strings - safe to use with esc_sql().
	$tables = array(
		// Core email log table.
		'am_email_log',
		// Premium analytics tables.
		'am_email_events',
		'am_tracking_links',
		'am_tracking_pixels',
		'am_recipient_profiles',
		'am_email_health_scores',
		'am_provider_performance',
		'am_ab_tests',
		'am_suppression_list',
		'am_notification_log',
		'am_consent_log',
	);

	foreach ( $tables as $table ) {
		$table_name = $wpdb->prefix . $table;

		// Validate table name format before dropping (security check).
		if ( ! preg_match( '/^[a-zA-Z0-9_]+$/', $table ) ) {
			continue;
		}

		// Additional layer of protection: escape the table name.
		$escaped_table = esc_sql( $table_name );

		// Drop the table if it exists.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "DROP TABLE IF EXISTS `{$escaped_table}`" );
	}
}

/**
 * Clear scheduled cron events.
 *
 * Removes all cron jobs scheduled by the plugin.
 */
function authority_mailer_smtp_uninstall_clear_cron() {
	// Clear scheduled hooks used by the plugin.
	wp_clear_scheduled_hook( 'authority_mailer_email_health_check' );
	wp_clear_scheduled_hook( 'authority_mailer_cleanup_old_events' );
}

/**
 * Handle multisite uninstallation.
 *
 * Performs cleanup across all sites in a multisite network,
 * or just the single site for standard WordPress installations.
 */
function authority_mailer_smtp_uninstall_multisite() {
	// Check if user wants to remove data.
	if ( ! authority_mailer_smtp_should_remove_data() ) {
		// Log that data was preserved.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
			error_log( 'Authority Mailer SMTP: Plugin uninstalled but data preserved (user setting).' );
		}
		return;
	}

	if ( is_multisite() ) {
		// Get all blog IDs.
		if ( function_exists( 'get_sites' ) ) {
			$sites = get_sites(
				array(
					'fields' => 'ids',
					'number' => 999,
				)
			);

			foreach ( $sites as $blog_id ) {
				switch_to_blog( $blog_id );
				authority_mailer_smtp_uninstall_cleanup_options();
				authority_mailer_smtp_uninstall_drop_tables();
				authority_mailer_smtp_uninstall_clear_cron();
				restore_current_blog();
			}
		}

		// Also clean up network-wide options if any.
		delete_site_option( 'authority_mailer_smtp_network_settings' );
	} else {
		// Single site uninstall.
		authority_mailer_smtp_uninstall_cleanup_options();
		authority_mailer_smtp_uninstall_drop_tables();
		authority_mailer_smtp_uninstall_clear_cron();
	}
}

// Run the uninstall.
authority_mailer_smtp_uninstall_multisite();

// Optional: Add a notice that data was removed.
// This won't be visible to users but can be logged.
if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging wrapped in WP_DEBUG checks.
	error_log( 'Authority Mailer SMTP: Plugin uninstalled and all data removed.' );
}
