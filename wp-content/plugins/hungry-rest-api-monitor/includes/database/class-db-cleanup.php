<?php
/**
 * Database Cleanup Handler.
 *
 * @package HungryRestApiMonitor
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class NANDRESTAPI_DB_Cleanup
 * Handles data retention and cleanup.
 */
class NANDRESTAPI_DB_Cleanup
{

    /**
     * Cleanup old log entries based on retention setting.
     */
    public static function cleanup_old_logs()
    {
        global $wpdb;

        $options = nandrestapi_get_options();
        $retention_days = absint($options['data_retention_days']);

        if ($retention_days < 1) {
            $retention_days = 7;
        }

        $table_name = $wpdb->prefix . NANDRESTAPI_LOGS_TABLE;
        $cutoff_date = gmdate('Y-m-d H:i:s', strtotime("-{$retention_days} days"));

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query(
            $wpdb->prepare(
                'DELETE FROM %i WHERE recorded_at < %s',
                $table_name,
                $cutoff_date
            )
        );
    }

    /**
     * Clear all logs.
     *
     * @return int|false Number of rows deleted or false on error.
     */
    public static function clear_all_logs()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . NANDRESTAPI_LOGS_TABLE;
        $http_table = $wpdb->prefix . 'nandrestapi_http_requests';

        // Clear HTTP requests table first (child records).
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query(
            $wpdb->prepare('TRUNCATE TABLE %i', $http_table)
        );

        // Clear main logs table.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        return $wpdb->query(
            $wpdb->prepare('TRUNCATE TABLE %i', $table_name)
        );
    }

    /**
     * Get database size in bytes.
     *
     * @return int Size in bytes.
     */
    public static function get_table_size()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . NANDRESTAPI_LOGS_TABLE;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $size = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT data_length + index_length 
                FROM information_schema.TABLES 
                WHERE table_schema = %s AND table_name = %s",
                DB_NAME,
                $table_name
            )
        );

        return $size ? intval($size) : 0;
    }

    /**
     * Get total log count.
     *
     * @return int Log count.
     */
    public static function get_log_count()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . NANDRESTAPI_LOGS_TABLE;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $count = $wpdb->get_var(
            $wpdb->prepare('SELECT COUNT(*) FROM %i', $table_name)
        );

        return $count ? intval($count) : 0;
    }
}
