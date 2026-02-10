<?php
/**
 * Database Schema Handler.
 *
 * @package HungryRestApiMonitor
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class NANDRESTAPI_DB_Schema
 * Manages database table creation.
 */
class NANDRESTAPI_DB_Schema
{

    /**
     * Create database tables.
     */
    public static function create_tables()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . NANDRESTAPI_LOGS_TABLE;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            endpoint VARCHAR(255) NOT NULL,
            full_url VARCHAR(500) NOT NULL,
            method VARCHAR(10) NOT NULL,
            status_code SMALLINT NOT NULL DEFAULT 0,
            response_time FLOAT NOT NULL DEFAULT 0,
            memory_usage BIGINT NOT NULL DEFAULT 0,
            memory_peak BIGINT NOT NULL DEFAULT 0,
            memory_limit BIGINT NOT NULL DEFAULT 0,
            memory_percent FLOAT NOT NULL DEFAULT 0,
            time_limit INT NOT NULL DEFAULT 0,
            time_percent FLOAT NOT NULL DEFAULT 0,
            query_count INT NOT NULL DEFAULT 0,
            query_time FLOAT NOT NULL DEFAULT 0,
            duplicate_queries INT NOT NULL DEFAULT 0,
            user_id BIGINT NOT NULL DEFAULT 0,
            ip_address VARCHAR(45) DEFAULT NULL,
            request_size INT NOT NULL DEFAULT 0,
            response_size INT NOT NULL DEFAULT 0,
            is_error TINYINT(1) NOT NULL DEFAULT 0,
            error_message TEXT DEFAULT NULL,
            php_errors_count INT NOT NULL DEFAULT 0,
            http_requests_count INT NOT NULL DEFAULT 0,
            http_requests_time FLOAT NOT NULL DEFAULT 0,
            cache_hits INT NOT NULL DEFAULT 0,
            cache_misses INT NOT NULL DEFAULT 0,
            transient_updates INT NOT NULL DEFAULT 0,
            recorded_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY idx_endpoint (endpoint(191)),
            KEY idx_recorded_at (recorded_at),
            KEY idx_status_code (status_code),
            KEY idx_method (method),
            KEY idx_user_id (user_id)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        // Create HTTP requests table for detailed outgoing request tracking.
        self::create_http_requests_table();
    }

    /**
     * Create HTTP requests detail table.
     */
    private static function create_http_requests_table()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'nandrestapi_http_requests';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            parent_log_id BIGINT UNSIGNED DEFAULT NULL,
            request_url VARCHAR(1000) NOT NULL,
            request_method VARCHAR(10) NOT NULL,
            request_headers TEXT DEFAULT NULL,
            request_body_size INT NOT NULL DEFAULT 0,
            response_code SMALLINT NOT NULL DEFAULT 0,
            response_headers TEXT DEFAULT NULL,
            response_body_size INT NOT NULL DEFAULT 0,
            response_time FLOAT NOT NULL DEFAULT 0,
            timeout_value INT NOT NULL DEFAULT 0,
            ssl_verify TINYINT(1) NOT NULL DEFAULT 1,
            is_error TINYINT(1) NOT NULL DEFAULT 0,
            error_message TEXT DEFAULT NULL,
            caller_file VARCHAR(255) DEFAULT NULL,
            caller_line INT DEFAULT NULL,
            caller_function VARCHAR(100) DEFAULT NULL,
            transport VARCHAR(50) DEFAULT NULL,
            recorded_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY idx_parent_log (parent_log_id),
            KEY idx_request_url (request_url(191)),
            KEY idx_recorded_at (recorded_at),
            KEY idx_response_code (response_code)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}
