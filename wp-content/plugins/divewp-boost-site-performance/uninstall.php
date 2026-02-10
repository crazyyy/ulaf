<?php
/**
 * DiveWP Uninstall
 *
 * Uninstalling DiveWP deletes tables, options, and other site data.
 * 
 * Note on Direct Database Operations:
 * This file intentionally uses direct database operations because:
 * 1. This is a one-time cleanup operation during plugin uninstallation
 * 2. We need to ensure complete removal of all plugin data
 * 3. Caching is irrelevant as this is a cleanup operation
 * 4. WordPress's delete_option() is used where possible
 * 5. Direct queries are only used for bulk operations and schema changes
 *
 * @package DiveWP
 * @since 1.0.0
 * @license GPL-2.0+
 */

// Exit if uninstall not called from WordPress
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Verify user capabilities
if (!current_user_can('activate_plugins')) {
    return;
}

// Define plugin path for uninstall
define('DIVEWP_PLUGIN_PATH', trailingslashit(dirname(__FILE__)));

global $wpdb;

try {
    /**
     * 1. Remove Custom Tables
     * 
     * Direct schema changes are necessary here because:
     * - These are plugin-specific tables that must be removed
     * - This is a one-time operation during uninstallation
     * - WordPress doesn't provide a native function for this
     */
    // phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Local arrays for uninstallation cleanup
    $tables_to_delete = array(
        $wpdb->prefix . 'divewp_email_log',
        $wpdb->prefix . 'divewp_user_events',
        $wpdb->prefix . 'divewp_security_log',
        $wpdb->prefix . 'divewp_performance_log',
        $wpdb->prefix . 'divewp_seo_data',
        // Hosting Benchmark results
        $wpdb->prefix . 'divewp_benchmark_results'
    );

    foreach ($tables_to_delete as $table) {
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.NoCaching -- Necessary during uninstallation to completely remove plugin tables
        $wpdb->query("DROP TABLE IF EXISTS `" . esc_sql($table) . "`");
    }

    /**
     * 2. Remove Plugin Options
     * Using WordPress native function for proper cleanup
     */
    // phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
    $options_to_delete = array(
        'divewp_version',
        'divewp_installed_time',
        'divewp_last_check_time',
        'divewp_settings',
        'divewp_email_settings',
        'divewp_security_settings',
        'divewp_performance_settings',
        'divewp_seo_settings',
        'divewp_user_preferences',
        'divewp_activation_time',
        'divewp_dismissed_notices',
        'divewp_feedback_settings',
        'divewp_feedback_dismissed',
        'divewp_install_time'
    );

    foreach ($options_to_delete as $option) {
        delete_option($option);
    }

    /**
     * 2a. Remove Benchmark Options
     * Some benchmark operations can store fallback data/guards in options with the
     * 'divewp_benchmark_' prefix. Remove them in bulk.
     */
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk deletion during uninstallation
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            $wpdb->esc_like('divewp_benchmark_') . '%'
        )
    );

    /**
     * 3. Remove Transients
     * 
     * Direct query is necessary here because:
     * - We need to remove all plugin-related transients in bulk
     * - WordPress doesn't provide a function to delete transients by pattern
     * - This is more efficient than deleting individually
     */
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk deletion of transients during uninstallation
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
            $wpdb->esc_like('_transient_divewp_') . '%',
            $wpdb->esc_like('_transient_timeout_divewp_') . '%'
        )
    );

    // Explicitly clear all benchmark-related transients (session, enabled tests, errors)
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk deletion during uninstallation
    $wpdb->query(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_divewp_benchmark_%'"
    );
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk deletion during uninstallation
    $wpdb->query(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_divewp_benchmark_%'"
    );

    /**
     * 4. Remove User Meta
     * Using WordPress native functions for proper cleanup
     */
    delete_metadata('user', 0, 'divewp_last_login', '', true);
    delete_metadata('user', 0, 'divewp_user_settings', '', true);
    delete_metadata('user', 0, 'divewp_dismissed_notices', '', true);

    /**
     * 5. Remove Post Meta
     * Using WordPress native functions for proper cleanup
     */
    delete_metadata('post', 0, 'divewp_seo_data', '', true);
    delete_metadata('post', 0, 'divewp_page_insights', '', true);

    /**
     * 6. Clear Scheduled Tasks
     */
    // phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
    $cron_hooks = array(
        'divewp_daily_cleanup',
        'divewp_cron_job',
        'divewp_security_scan',
        'divewp_performance_check',
        'divewp_email_cleanup',
        'divewp_logs_cleanup'
    );

    foreach ($cron_hooks as $hook) {
        wp_clear_scheduled_hook($hook);
    }

    /**
     * 7. Clear Cache
     */
    wp_cache_flush();

    /**
     * 8. Remove User Capabilities
     */
    // phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
    $roles = array('administrator', 'editor', 'author');
    foreach ($roles as $role) {
        $role_obj = get_role($role);
        if ($role_obj) {
            $role_obj->remove_cap('manage_divewp');
            $role_obj->remove_cap('view_divewp_reports');
        }
    }

    /**
     * 9. Flush Rewrite Rules
     */
    flush_rewrite_rules();
    // phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

    // Log successful uninstallation
    if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
        divewp_debug_log('Plugin uninstalled successfully - All data removed', 'info');
    }

} catch (Exception $e) {
    if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
        divewp_debug_log('Uninstall Error: ' . $e->getMessage(), 'error');
    }
}
