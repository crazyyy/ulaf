<?php
/**
 * Uninstall ImgSEO Plugin
 *
 * This file is executed when the plugin is deleted from the WordPress admin.
 * It checks if the user has enabled the "Delete data on uninstall" option
 * and only deletes data if explicitly requested.
 *
 * @package ImgSEO
 * @since 2.2.1
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Check if user wants to delete data on uninstall
$imgseo_delete_data = get_option('imgseo_delete_data_on_uninstall', 0);

// Only proceed with data deletion if explicitly enabled by user
if ((int) $imgseo_delete_data === 1) {

    global $wpdb;

    // ==================================================
    // DELETE ALL PLUGIN TABLES
    // ==================================================

    $imgseo_tables = array(
        'imgseo_content_images',
        'imgseo_url_index',
        'imgseo_scan_status',
        'imgseo_stats_cache',
        'imgseo_jobs',
        'imgseo_rename_logs',
        'imgseo_compression_jobs' // In case it exists
    );

    foreach ($imgseo_tables as $imgseo_table) {
        $imgseo_table_name = $wpdb->prefix . $imgseo_table;
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query("DROP TABLE IF EXISTS `$imgseo_table_name`");
    }

    // ==================================================
    // DELETE LOG FILES DIRECTORY
    // ==================================================

    $imgseo_upload_dir = wp_upload_dir();
    $imgseo_log_dir = trailingslashit($imgseo_upload_dir['basedir']) . 'imgseo-logs';

    if (is_dir($imgseo_log_dir)) {
        // Delete all log files
        $imgseo_files = glob($imgseo_log_dir . '/*.log');
        if ($imgseo_files) {
            foreach ($imgseo_files as $imgseo_file) {
                wp_delete_file($imgseo_file);
            }
        }
        // Delete protection files
        wp_delete_file($imgseo_log_dir . '/.htaccess');
        wp_delete_file($imgseo_log_dir . '/index.php');
        // Remove directory
        if (!class_exists('WP_Filesystem_Direct')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        $imgseo_filesystem = new WP_Filesystem_Direct(null);
        $imgseo_filesystem->rmdir($imgseo_log_dir);
    }

    // ==================================================
    // DELETE ALL PLUGIN OPTIONS
    // ==================================================

    // API Settings
    delete_option('imgseo_api_key');
    delete_option('imgseo_api_verified');
    delete_option('imgseo_api_credits');

    // General Settings
    delete_option('imgseo_language');
    delete_option('imgseo_max_characters');
    delete_option('imgseo_include_page_title');
    delete_option('imgseo_include_image_name');
    delete_option('imgseo_overwrite');
    delete_option('imgseo_auto_generate');
    delete_option('imgseo_always_use_base64');
    delete_option('imgseo_footer_badge');
    delete_option('imgseo_support_link');
    delete_option('imgseo_delete_data_on_uninstall');

    // Prompt Settings
    delete_option('imgseo_custom_prompt');
    delete_option('imgseo_woocommerce_prompt');
    delete_option('imgseo_enable_woocommerce_prompt');

    // Database Settings
    delete_option('imgseo_db_version');
    delete_option('imgseo_universal_scanner_enabled');
    delete_option('imgseo_scan_batch_size');
    delete_option('imgseo_scan_timeout');
    delete_option('imgseo_cache_expiry_hours');
    delete_option('imgseo_auto_scan_frequency');
    delete_option('imgseo_scan_external_images');
    delete_option('imgseo_scan_background_images');
    delete_option('imgseo_scan_page_builders');
    delete_option('imgseo_legacy_migration_completed');
    delete_option('imgseo_initial_scan_completed');

    // Renamer Settings
    delete_option('imgseo_log_retention_days');
    delete_option('imgseo_renamer_ai_max_words');
    delete_option('imgseo_renamer_ai_include_post_title');
    delete_option('imgseo_renamer_ai_include_category');
    delete_option('imgseo_renamer_enabled');
    delete_option('imgseo_renamer_mode');
    delete_option('imgseo_renamer_pattern');

    // Compression Settings
    delete_option('imgseo_compression_enabled');
    delete_option('imgseo_compression_quality');
    delete_option('imgseo_compression_format');
    delete_option('imgseo_compression_enable_webp');
    delete_option('imgseo_compression_enable_avif');
    delete_option('imgseo_compression_webp_quality');
    delete_option('imgseo_compression_avif_quality');
    delete_option('imgseo_compression_optimize_web');
    delete_option('imgseo_compression_strip_metadata');
    delete_option('imgseo_compression_auto_remove_larger');
    delete_option('imgseo_compression_serving_method');

    // Sitemap & Structured Data Settings
    delete_option('imgseo_enable_sitemap');
    delete_option('imgseo_sitemap_include_external');
    delete_option('imgseo_enable_structured_data');
    delete_option('imgseo_structured_data_type');

    // ==================================================
    // DELETE TRANSIENTS
    // ==================================================

    delete_transient('imgseo_stats_cache');
    delete_transient('imgseo_api_credits');
    delete_transient('imgseo_last_log_cleanup');
    delete_transient('imgseo_invalid_api_token');
    delete_transient('imgseo_invalid_token_code');
    delete_transient('imgseo_insufficient_credits');

    // ==================================================
    // CLEAR SCHEDULED HOOKS
    // ==================================================

    wp_clear_scheduled_hook('imgseo_cleanup_old_data');
    wp_clear_scheduled_hook('imgseo_scheduled_scan');
    wp_clear_scheduled_hook('imgseo_update_stats_cache');
    wp_clear_scheduled_hook('imgseo_cleanup_rename_logs');
    wp_clear_scheduled_hook(IMGSEO_CRON_HOOK);
    wp_clear_scheduled_hook('imgseo_check_stuck_jobs');

    // ==================================================
    // DELETE POST META (if any)
    // ==================================================

    // Delete any post meta created by the plugin (if you want to clean these too)
    // Uncomment if you want to remove all plugin-related post meta
    /*
    $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE 'imgseo_%'");
    */

} else {

    // ==================================================
    // USER CHOSE TO KEEP DATA
    // ==================================================

    // Only clear scheduled hooks, keep all data and options
    wp_clear_scheduled_hook('imgseo_cleanup_old_data');
    wp_clear_scheduled_hook('imgseo_scheduled_scan');
    wp_clear_scheduled_hook('imgseo_update_stats_cache');
    wp_clear_scheduled_hook('imgseo_cleanup_rename_logs');

    if (defined('IMGSEO_CRON_HOOK')) {
        wp_clear_scheduled_hook(IMGSEO_CRON_HOOK);
    }
    wp_clear_scheduled_hook('imgseo_check_stuck_jobs');

    // Keep all tables and options intact for future reinstallation
}
