<?php
/**
 * Uninstall script for Hungry Rest API Monitor.
 *
 * @package HungryRestApiMonitor
 */

// Exit if not called by WordPress.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Delete options.
delete_option('nandrestapi_options');

// Drop custom table.
$nandrestapi_table_name = $wpdb->prefix . 'nandrestapi_logs';

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange
$wpdb->query(
    $wpdb->prepare('DROP TABLE IF EXISTS %i', $nandrestapi_table_name)
);

// Drop HTTP requests table.
$nandrestapi_http_table = $wpdb->prefix . 'nandrestapi_http_requests';
$wpdb->query(
    $wpdb->prepare('DROP TABLE IF EXISTS %i', $nandrestapi_http_table)
);
// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange

// Clear scheduled hooks.
wp_clear_scheduled_hook('nandrestapi_daily_cleanup');
