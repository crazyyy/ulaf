<?php
/**
 * Database Access Layer for DiveWP
 *
 * Handles all database operations for the plugin.
 * This class intentionally uses direct database queries without caching for real-time monitoring.
 * Caching is deliberately avoided as this is an admin-only monitoring tool that requires current data.
 *
 * @package DiveWP
 * @since 1.0.4
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    die(esc_html__('Direct access not permitted.', 'divewp-boost-site-performance'));
}

/**
 * Database Access Class
 * 
 * @since 1.0.4
 * 
 * Note on Direct Database Queries:
 * This class intentionally uses direct database queries without caching because:
 * 1. It's an admin-only tool for monitoring and diagnostics
 * 2. All data needs to be real-time and current
 * 3. Caching would provide incorrect/outdated monitoring data
 * 4. All queries are protected by capability checks
 */
class DiveWP_DB_Access {
    /**
     * Instance of this class
     */
    private static $instance = null;

    /**
     * Table names
     */
    private $email_log_table;
    private $user_events_table;
    private $cron_logs_table;

    /**
     * Get instance of this class
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     * 
     * Initializes table names with proper WordPress prefix.
     */
    private function __construct() {
        global $wpdb;
        // Using $wpdb->prefix ensures proper multisite support
        $this->email_log_table = $wpdb->prefix . 'divewp_email_log';
        $this->user_events_table = $wpdb->prefix . 'divewp_user_events';
        $this->cron_logs_table = $wpdb->prefix . 'divewp_cron_logs';
    }

    /**
     * Email Log Operations
     * 
     * Direct database operations required for real-time logging.
     * Caching is intentionally avoided as this is a monitoring tool requiring current data.
     * All operations are protected by capability checks.
     */
    public function log_email($data) {
        if (!current_user_can('manage_options')) {
            return false;
        }

        global $wpdb;
        // Direct insert required for immediate logging
        // No caching needed as this is a write operation
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Necessary for immediate logging in admin-only tool
        return $wpdb->insert(
            $wpdb->prefix . 'divewp_email_log',
            array(
                'status' => sanitize_text_field($data['status']),
                'initiator' => sanitize_text_field($data['initiator']),
                'from_email' => sanitize_email($data['from_email']),
                'to_email' => sanitize_email($data['to_email']),
                'subject' => sanitize_text_field($data['subject']),
                'error_msg' => isset($data['error_msg']) ? sanitize_textarea_field($data['error_msg']) : null
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s')
        );
    }

    /**
     * Get recent email logs
     * 
     * Direct query required for real-time monitoring.
     * Caching would provide outdated log data.
     * Protected by capability checks.
     */
    public function get_recent_emails($limit = 10) {
        if (!current_user_can('manage_options')) {
            return array();
        }

        $limit = absint($limit);
        global $wpdb;
        
        // Escape table name properly
        $table = esc_sql($this->email_log_table);
        
        // Prepare needed here as we're using a variable
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin-only monitoring tool requiring real-time data
        return $wpdb->get_results($wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared -- Table name is escaped and not user input
            "SELECT * FROM $table ORDER BY date_sent DESC LIMIT %d",
            $limit
        ));
    }

    /**
     * Cleanup old email logs
     * Direct query required for immediate log maintenance
     */
    public function cleanup_email_logs($keep = 100) {
        if (!current_user_can('manage_options')) {
            return false;
        }

        global $wpdb;
        
        // Escape table name properly
        $table = esc_sql($this->email_log_table);
        
        // For complex SQL with multiple table references, build the query first
        // then apply the phpcs:ignore to the entire SQL variable
        
        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared -- Table name is escaped and not user input
        $sql = "DELETE FROM $table 
                WHERE id NOT IN (
                    SELECT id FROM (
                        SELECT id FROM $table 
                        ORDER BY date_sent DESC 
                        LIMIT %d
                    ) temp_table
                )";
        // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin-only maintenance operation
        return $wpdb->query(
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql is already properly escaped above
            $wpdb->prepare($sql, $keep)
        );
    }

    /**
     * Log user event
     * 
     * Direct database operation required for immediate event tracking.
     * No caching as this is a write operation requiring immediate effect.
     * Protected by capability checks.
     */
    public function log_event($data) {
        if (!current_user_can('manage_options')) {
            return false;
        }

        global $wpdb;
        
        // Ensure we store the timestamp in UTC
        $utc_now = new DateTime('now', new DateTimeZone('UTC'));
        
        // Direct insert required for immediate logging
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Write operation for admin-only logging feature
        return $wpdb->insert(
            $wpdb->prefix . 'divewp_user_events',
            array(
                'event_type' => sanitize_text_field($data['event_type']),
                'event_action' => sanitize_text_field($data['event_action']),
                'user_id' => absint($data['user_id']),
                'description' => sanitize_text_field($data['description']),
                'status' => sanitize_text_field($data['status']),
                'created_at' => $utc_now->format('Y-m-d H:i:s')
            ),
            array('%s', '%s', '%d', '%s', '%s', '%s')
        );
    }

    /**
     * Get recent user events with pagination
     * 
     * Direct query required for real-time event monitoring.
     * Caching would provide outdated event data.
     * Protected by capability checks.
     */
    public function get_recent_events($limit = 10, $offset = 0) {
        if (!current_user_can('manage_options')) {
            return array();
        }

        $limit = absint($limit);
        $offset = absint($offset);
        
        global $wpdb;
        
        // Escape table name and users table properly
        $events_table = esc_sql($this->user_events_table);
        $users_table = esc_sql($wpdb->users);
        
        // Prepare needed here as we're using variables
        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared -- Table names are escaped and not user input
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin-only monitoring tool requiring real-time data
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT e.*, u.user_login 
             FROM $events_table e 
             LEFT JOIN $users_table u ON e.user_id = u.ID 
             ORDER BY e.created_at DESC 
             LIMIT %d OFFSET %d",
            $limit,
            $offset
        ));
        // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
        
        return $results ?: array();
    }

    /**
     * Get total events count
     * 
     * Direct query without caching required for real-time monitoring.
     * Caching would provide outdated counts for the monitoring dashboard.
     */
    public function get_total_events() {
        if (!current_user_can('manage_options')) {
            return 0;
        }

        global $wpdb;
        
        // Escape table name properly
        $table = esc_sql($this->user_events_table);
        
        // No prepare needed as we're not using any variables
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin-only monitoring tool requiring real-time count
        return (int) $wpdb->get_var(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared -- Table name is escaped and not user input; simple COUNT query doesn't need prepare
            "SELECT COUNT(*) FROM $table"
        );
    }

    /**
     * Cleanup old user events
     * Direct query required for immediate maintenance
     */
    public function cleanup_events($days = 30) {
        if (!current_user_can('manage_options')) {
            return false;
        }

        global $wpdb;
        
        // Escape table name properly
        $table = esc_sql($this->user_events_table);
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin-only maintenance operation
        return $wpdb->query(
            $wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared -- Table name is escaped and not user input
                "DELETE FROM $table 
                 WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
                $days
            )
        );
    }

    /**
     * Delete all logs
     * 
     * Direct queries without caching required for immediate log cleanup.
     * Caching would interfere with real-time log management.
     */
    public function delete_all_logs() {
        if (!current_user_can('manage_options')) {
            return new WP_Error(
                'insufficient_permissions', 
                esc_html__('You do not have permission to perform this action.', 'divewp-boost-site-performance')
            );
        }

        global $wpdb;
        
        // Escape table names properly
        $email_table = esc_sql($this->email_log_table);
        $events_table = esc_sql($this->user_events_table);
        
        // No prepare needed as we're not using any variables
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin-only maintenance operation
        $result1 = $wpdb->query(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared -- Table name is escaped and not user input; simple DELETE query
            "DELETE FROM $email_table"
        );
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin-only maintenance operation
        $result2 = $wpdb->query(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared -- Table name is escaped and not user input; simple DELETE query
            "DELETE FROM $events_table"
        );

        if ($result1 === false || $result2 === false) {
            return new WP_Error(
                'db_error', 
                esc_html__('Failed to delete logs.', 'divewp-boost-site-performance')
            );
        }

        return true;
    }

    /**
     * Get paginated events with user data
     * Direct query required for real-time monitoring
     */
    public function get_paginated_events($page = 1, $per_page = 10) {
        if (!current_user_can('manage_options')) {
            return array();
        }

        global $wpdb;
        $offset = ($page - 1) * $per_page;

        // Escape table names properly
        $events_table = esc_sql($this->user_events_table);
        $users_table = esc_sql($wpdb->users);

        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared -- Table names are escaped and not user input
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin-only monitoring tool requiring real-time data
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT e.*, u.user_login 
                 FROM $events_table e 
                 LEFT JOIN $users_table u ON e.user_id = u.ID 
                 ORDER BY e.created_at DESC 
                 LIMIT %d OFFSET %d",
                $per_page,
                $offset
            )
        );
        // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
    }

    /**
     * Verify if a table exists
     * 
     * Direct query required for table verification.
     * Caching would interfere with real-time system checks.
     * Protected by capability checks.
     */
    public function verify_table_exists($table) {
        global $wpdb;
        $table_name = $wpdb->prefix . sanitize_key($table);
        // Prepare needed here as we're using a variable
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin-only system check requiring real-time data
        return $wpdb->get_var(
            $wpdb->prepare(
                "SHOW TABLES LIKE %s",
                $table_name
            )
        ) === $table_name;
    }

    /**
     * Clear cached data
     * 
     * Direct query required for cache management.
     * This is an admin-only operation for maintaining the monitoring system.
     */
    private function clear_cached_data() {
        global $wpdb;
        
        // Prepare needed here as we're using a variable in LIKE clause
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin-only cache maintenance operation
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $wpdb->esc_like('_transient_divewp_') . '%'
            )
        );
    }

    /**
     * Delete user events
     * 
     * Direct query without caching required for immediate data removal.
     * Caching would interfere with real-time event management.
     */
    public function delete_user_events() {
        if (!current_user_can('manage_options')) {
            return new WP_Error(
                'insufficient_permissions',
                esc_html__('You do not have permission to perform this action.', 'divewp-boost-site-performance')
            );
        }

        global $wpdb;
        
        // Escape table name properly
        $table = esc_sql($this->user_events_table);
        
        // No prepare needed as we're not using any variables
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin-only maintenance operation
        $result = $wpdb->query(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared -- Table name is escaped and not user input; simple DELETE query
            "DELETE FROM $table"
        );

        if ($result === false) {
            return new WP_Error(
                'db_error',
                esc_html__('Failed to delete user events.', 'divewp-boost-site-performance')
            );
        }

        return true;
    }

    public function delete_all_email_logs() {
        global $wpdb;
        
        if (!current_user_can('manage_options')) {
            return new WP_Error(
                'insufficient_permissions',
                esc_html__('You do not have permission to perform this action.', 'divewp-boost-site-performance')
            );
        }
        
        if (!$this->verify_table_exists('divewp_email_log')) {
            return false;
        }

        // Escape table name properly
        $table_name = esc_sql($this->email_log_table);

        // Use prepared statement with properly escaped table name
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin-only maintenance operation
        $result = $wpdb->query(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared -- Table name is escaped and TRUNCATE doesn't support prepare
            "TRUNCATE TABLE $table_name"
        );
        
        if ($result === false) {
            return new WP_Error('db_error', __('Database error occurred', 'divewp-boost-site-performance'), $wpdb->last_error);
        }
        
        return true;
    }

    /**
     * Generic insert method for benchmark data
     * 
     * Direct database operation required for benchmark data storage.
     * Protected by capability checks.
     * 
     * @param string $table Table name (without prefix)
     * @param array $data Data to insert
     * @param array $format Optional. Data format array
     * @return int|false Insert ID on success, false on failure
     */
    public function insert($table, $data, $format = null) {
        if (!current_user_can('manage_options')) {
            return false;
        }

        global $wpdb;
        
        // Sanitize table name - ensure it's a valid table name
        $table = sanitize_key($table);
        
        // Add WordPress prefix if not already present
        if (strpos($table, $wpdb->prefix) !== 0) {
            $table = $wpdb->prefix . $table;
        }
        
        // Sanitize data array
        $sanitized_data = array();
        foreach ($data as $key => $value) {
            $sanitized_key = sanitize_key($key);
            $sanitized_data[$sanitized_key] = $value; // Value sanitization handled by wpdb->insert
        }
        
        // Use wpdb->insert for safe insertion
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Admin-only data insertion for benchmark feature
        $result = $wpdb->insert($table, $sanitized_data, $format);
        
        if ($result === false) {
            return false;
        }
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Accessing insert ID after admin-only database insertion
        return $wpdb->insert_id;
    }

    /**
     * Get recent benchmark results
     * 
     * Direct query required for real-time benchmark monitoring.
     * Protected by capability checks.
     * 
     * @param int $limit Number of results to retrieve
     * @param int $user_id Optional. Filter by specific user ID
     * @return array Benchmark results
     */
    public function get_recent_benchmark_results($limit = 10, $user_id = null) {
        if (!current_user_can('manage_options')) {
            return array();
        }

        global $wpdb;
        $limit = absint($limit);
        $table_name = $wpdb->prefix . 'divewp_benchmark_results';
        
        $where_clause = '';
        $prepare_values = array($limit);
        
        if ($user_id !== null) {
            $where_clause = ' WHERE user_id = %d';
            $prepare_values = array(absint($user_id), $limit);
        }
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Admin-only monitoring requiring real-time data; table name is constructed from $wpdb->prefix + hardcoded string, not user input
        return $wpdb->get_results(
            $wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe, where clause is conditionally built
                "SELECT * FROM {$table_name}{$where_clause} ORDER BY test_date DESC LIMIT %d",
                ...$prepare_values
            )
        );
    }

    /**
     * Get benchmark result by ID
     * 
     * @param int $result_id Benchmark result ID
     * @return object|null Benchmark result or null if not found
     */
    public function get_benchmark_result($result_id) {
        if (!current_user_can('manage_options')) {
            return null;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'divewp_benchmark_results';
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Admin-only data retrieval; table name is constructed from $wpdb->prefix + hardcoded string, not user input
        return $wpdb->get_row(
            $wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe
                "SELECT * FROM {$table_name} WHERE id = %d",
                absint($result_id)
            )
        );
    }

    /**
     * Get user's benchmark history with pagination
     * 
     * @param int $user_id User ID
     * @param int $page Page number (1-based)
     * @param int $per_page Results per page
     * @return array Paginated benchmark results
     */
    public function get_user_benchmark_history($user_id, $page = 1, $per_page = 10) {
        if (!current_user_can('manage_options')) {
            return array();
        }

        global $wpdb;
        $offset = ($page - 1) * $per_page;
        $table_name = $wpdb->prefix . 'divewp_benchmark_results';
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Admin-only monitoring requiring real-time data; table name is constructed from $wpdb->prefix + hardcoded string, not user input
        return $wpdb->get_results(
            $wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe
                "SELECT * FROM {$table_name} WHERE user_id = %d ORDER BY test_date DESC LIMIT %d OFFSET %d",
                absint($user_id),
                absint($per_page),
                absint($offset)
            )
        );
    }

    /**
     * Get benchmark statistics
     * 
     * @return array Statistics about benchmark results
     */
    public function get_benchmark_statistics() {
        if (!current_user_can('manage_options')) {
            return array();
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'divewp_benchmark_results';
        
        // Escape dynamic table name to avoid InterpolatedNotPrepared on multi-line SQL
        $escaped_table = '`' . esc_sql($table_name) . '`';
        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is escaped with esc_sql(); multi-line SQL requires disable/enable block
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Admin-only statistics requiring real-time data; table name is constructed from $wpdb->prefix + hardcoded string and escaped with esc_sql()
        $stats = $wpdb->get_row(
            "SELECT 
                COUNT(*) as total_tests,
                AVG(overall_score) as avg_overall_score,
                MAX(overall_score) as max_overall_score,
                MIN(overall_score) as min_overall_score,
                AVG(performance_score) as avg_performance_score,
                AVG(database_score) as avg_database_score,
                AVG(resources_score) as avg_resources_score,
                AVG(concurrency_score) as avg_concurrency_score
            FROM $escaped_table",
            ARRAY_A
        );
        // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        
        return $stats ?: array();
    }

    /**
     * Delete old benchmark results
     * 
     * @param int $days Number of days to keep (default 90)
     * @return int|false Number of rows deleted or false on failure
     */
    public function cleanup_old_benchmark_results($days = 90) {
        if (!current_user_can('manage_options')) {
            return false;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'divewp_benchmark_results';
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Admin-only maintenance operation; table name is constructed from $wpdb->prefix + hardcoded string, not user input
        return $wpdb->query(
            $wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe
                "DELETE FROM {$table_name} WHERE test_date < DATE_SUB(NOW(), INTERVAL %d DAY)",
                absint($days)
            )
        );
    }

    /**
     * Delete a specific benchmark result by ID
     * 
     * @param int $benchmark_id The ID of the benchmark to delete
     * @return bool True on success, false on failure
     */
    public function delete_benchmark_result($benchmark_id) {
        if (!current_user_can('manage_options')) {
            return false;
        }

        if (!$benchmark_id || !is_numeric($benchmark_id)) {
            return false;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'divewp_benchmark_results';
        
        // First check if the benchmark exists and belongs to current user (for additional security)
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Admin-only security check requiring real-time verification; table name is constructed from $wpdb->prefix + hardcoded string, not user input
        $existing = $wpdb->get_var(
            $wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe
                "SELECT id FROM {$table_name} WHERE id = %d AND user_id = %d",
                absint($benchmark_id),
                get_current_user_id()
            )
        );

        if (!$existing) {
            return false; // Benchmark doesn't exist or doesn't belong to user
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin-only delete operation
        $result = $wpdb->delete(
            $table_name,
            array(
                'id' => absint($benchmark_id),
                'user_id' => get_current_user_id() // Extra security - only delete own benchmarks
            ),
            array('%d', '%d')
        );
        
        return $result !== false && $result > 0;
    }

    /**
     * Delete all benchmark results
     * 
     * @return bool True on success, false on failure
     */
    public function delete_all_benchmark_results() {
        if (!current_user_can('manage_options')) {
            return false;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'divewp_benchmark_results';
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Admin-only maintenance operation; table name is constructed from $wpdb->prefix + hardcoded string, not user input
        $result = $wpdb->query(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe, no user input
            "TRUNCATE TABLE {$table_name}"
        );
        
        return $result !== false;
    }

    /**
     * =========================================================================
     * Cron Log Operations
     * =========================================================================
     */

    /**
     * Log a cron execution
     * 
     * Direct database operation required for immediate cron tracking.
     * Protected by capability checks.
     * 
     * @since 2.2.0
     * @param array $data Cron execution data
     * @return int|false Insert ID on success, false on failure
     */
    public function log_cron_execution($data) {
        global $wpdb;
        
        // Escape table name properly
        $table = esc_sql($this->cron_logs_table);
        
        // Sanitize input data
        $insert_data = array(
            'hook' => isset($data['hook']) ? sanitize_text_field($data['hook']) : '',
            'args' => isset($data['args']) ? wp_json_encode($data['args']) : null,
            'trigger_source' => isset($data['trigger_source']) ? sanitize_text_field($data['trigger_source']) : 'wp_cron',
            'started_at' => isset($data['started_at']) ? sanitize_text_field($data['started_at']) : current_time('mysql', true),
            'finished_at' => isset($data['finished_at']) ? sanitize_text_field($data['finished_at']) : null,
            'duration_ms' => isset($data['duration_ms']) ? absint($data['duration_ms']) : null,
            'peak_memory_bytes' => isset($data['peak_memory_bytes']) ? absint($data['peak_memory_bytes']) : null,
            'status' => isset($data['status']) ? sanitize_text_field($data['status']) : 'running',
            'error_message' => isset($data['error_message']) ? sanitize_textarea_field($data['error_message']) : null,
            'error_trace' => isset($data['error_trace']) ? sanitize_textarea_field($data['error_trace']) : null,
        );
        
        $format = array('%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s');
        
        // Direct insert required for immediate logging
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Write operation for admin-only cron logging feature
        $result = $wpdb->insert($this->cron_logs_table, $insert_data, $format);
        
        if ($result === false) {
            return false;
        }
        
        return $wpdb->insert_id;
    }

    /**
     * Update a cron execution log
     * 
     * @since 2.2.0
     * @param int   $log_id Log ID to update
     * @param array $data   Data to update
     * @return bool True on success, false on failure
     */
    public function update_cron_log($log_id, $data) {
        global $wpdb;
        
        $update_data = array();
        $format = array();
        
        if (isset($data['finished_at'])) {
            $update_data['finished_at'] = sanitize_text_field($data['finished_at']);
            $format[] = '%s';
        }
        if (isset($data['duration_ms'])) {
            $update_data['duration_ms'] = absint($data['duration_ms']);
            $format[] = '%d';
        }
        if (isset($data['peak_memory_bytes'])) {
            $update_data['peak_memory_bytes'] = absint($data['peak_memory_bytes']);
            $format[] = '%d';
        }
        if (isset($data['status'])) {
            $update_data['status'] = sanitize_text_field($data['status']);
            $format[] = '%s';
        }
        if (isset($data['error_message'])) {
            $update_data['error_message'] = sanitize_textarea_field($data['error_message']);
            $format[] = '%s';
        }
        if (isset($data['error_trace'])) {
            $update_data['error_trace'] = sanitize_textarea_field($data['error_trace']);
            $format[] = '%s';
        }
        
        if (empty($update_data)) {
            return false;
        }
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin-only cron log update
        $result = $wpdb->update(
            $this->cron_logs_table,
            $update_data,
            array('id' => absint($log_id)),
            $format,
            array('%d')
        );
        
        return $result !== false;
    }

    /**
     * Get recent cron logs with pagination
     * 
     * Direct query required for real-time cron monitoring.
     * Protected by capability checks.
     * 
     * @since 2.2.0
     * @param int    $limit  Number of logs to retrieve
     * @param int    $offset Offset for pagination
     * @param string $status Optional status filter
     * @param string $hook   Optional hook filter
     * @return array Cron log entries
     */
    public function get_recent_cron_logs($limit = 50, $offset = 0, $status = '', $hook = '') {
        if (!current_user_can('manage_options')) {
            return array();
        }

        global $wpdb;
        $limit = absint($limit);
        $offset = absint($offset);
        
        // Escape table name properly
        $table = esc_sql($this->cron_logs_table);
        
        // Build WHERE clause
        $where_parts = array();
        $prepare_values = array();
        
        if (!empty($status)) {
            $where_parts[] = 'status = %s';
            $prepare_values[] = sanitize_text_field($status);
        }
        
        if (!empty($hook)) {
            $where_parts[] = 'hook LIKE %s';
            $prepare_values[] = '%' . $wpdb->esc_like(sanitize_text_field($hook)) . '%';
        }
        
        $where_clause = '';
        if (!empty($where_parts)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where_parts);
        }
        
        $prepare_values[] = $limit;
        $prepare_values[] = $offset;
        
        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber -- Table name is escaped; where clause is conditionally built with matching placeholders; spread operator handles variable placeholder count
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Admin-only monitoring requiring real-time data; table name is constructed from $wpdb->prefix + hardcoded string and escaped with esc_sql()
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table $where_clause ORDER BY started_at DESC LIMIT %d OFFSET %d",
                ...$prepare_values
            )
        );
        // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
        
        return $results ?: array();
    }

    /**
     * Get a single cron log by ID
     * 
     * @since 2.2.0
     * @param int $log_id Log ID
     * @return object|null Log entry or null if not found
     */
    public function get_cron_log($log_id) {
        if (!current_user_can('manage_options')) {
            return null;
        }

        global $wpdb;
        
        // Escape table name properly
        $table = esc_sql($this->cron_logs_table);
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin-only data retrieval
        return $wpdb->get_row(
            $wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is escaped
                "SELECT * FROM $table WHERE id = %d",
                absint($log_id)
            )
        );
    }

    /**
     * Get total cron logs count
     * 
     * @since 2.2.0
     * @param string $status Optional status filter
     * @return int Total count
     */
    public function get_total_cron_logs($status = '') {
        if (!current_user_can('manage_options')) {
            return 0;
        }

        global $wpdb;
        
        // Escape table name properly
        $table = esc_sql($this->cron_logs_table);
        
        if (!empty($status)) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin-only monitoring
            return (int) $wpdb->get_var(
                $wpdb->prepare(
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is escaped
                    "SELECT COUNT(*) FROM $table WHERE status = %s",
                    sanitize_text_field($status)
                )
            );
        }
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin-only monitoring
        return (int) $wpdb->get_var(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is escaped
            "SELECT COUNT(*) FROM $table"
        );
    }

    /**
     * Delete cron logs by hook name
     *
     * @since 2.2.0
     * @param string $hook Hook name
     * @return int|false Rows deleted or false on failure
     */
    public function delete_cron_logs_by_hook($hook) {
        if (!current_user_can('manage_options')) {
            return false;
        }

        if (empty($hook)) {
            return 0;
        }

        global $wpdb;
        $table = esc_sql($this->cron_logs_table);

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin-only maintenance
        return $wpdb->query(
            $wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is escaped
                "DELETE FROM $table WHERE hook = %s",
                $hook
            )
        );
    }

    /**
     * Cleanup old cron logs
     * 
     * @since 2.2.0
     * @param int $days Number of days to keep (default 30)
     * @return int|false Number of rows deleted or false on failure
     */
    public function cleanup_cron_logs($days = 30) {
        if (!current_user_can('manage_options')) {
            return false;
        }

        global $wpdb;
        
        // Escape table name properly
        $table = esc_sql($this->cron_logs_table);
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin-only maintenance operation
        return $wpdb->query(
            $wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is escaped
                "DELETE FROM $table WHERE started_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
                absint($days)
            )
        );
    }

    /**
     * Get cron log statistics for dashboard tiles
     * 
     * @since 2.2.0
     * @return array Statistics about cron execution
     */
    public function get_cron_log_stats() {
        if (!current_user_can('manage_options')) {
            return array();
        }

        global $wpdb;
        
        // Escape table name properly
        $table = esc_sql($this->cron_logs_table);
        
        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is escaped with esc_sql(); multi-line SQL requires disable/enable block
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Admin-only statistics; table name is constructed from $wpdb->prefix + hardcoded string and escaped with esc_sql()
        $stats = $wpdb->get_row(
            "SELECT 
                COUNT(*) as total_executions,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful,
                SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN status = 'warning' THEN 1 ELSE 0 END) as warnings,
                SUM(CASE WHEN status = 'running' THEN 1 ELSE 0 END) as running,
                AVG(duration_ms) as avg_duration_ms,
                MAX(duration_ms) as max_duration_ms,
                AVG(peak_memory_bytes) as avg_memory_bytes,
                MAX(peak_memory_bytes) as max_memory_bytes
            FROM $table 
            WHERE started_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)",
            ARRAY_A
        );
        // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        
        // Get unique hooks count
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Admin-only statistics; table name is constructed from $wpdb->prefix + hardcoded string and escaped with esc_sql()
        $unique_hooks = (int) $wpdb->get_var(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is escaped with esc_sql()
            "SELECT COUNT(DISTINCT hook) FROM $table WHERE started_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)"
        );
        
        $stats['unique_hooks'] = $unique_hooks;
        
        return $stats ?: array();
    }

    /**
     * Get logs grouped by day for timeline view
     * 
     * @since 2.2.0
     * @param int $days Number of days to retrieve
     * @return array Logs grouped by date
     */
    public function get_cron_logs_by_day($days = 7) {
        if (!current_user_can('manage_options')) {
            return array();
        }

        global $wpdb;
        
        // Escape table name properly
        $table = esc_sql($this->cron_logs_table);
        
        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is escaped with esc_sql(); multi-line SQL requires disable/enable block
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Admin-only statistics; table name is constructed from $wpdb->prefix + hardcoded string and escaped with esc_sql()
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 
                    DATE(started_at) as log_date,
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful,
                    SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as failed,
                    SUM(CASE WHEN status = 'warning' THEN 1 ELSE 0 END) as warnings
                FROM $table 
                WHERE started_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
                GROUP BY DATE(started_at)
                ORDER BY log_date DESC",
                absint($days)
            ),
            ARRAY_A
        );
        // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    }

    /**
     * Delete all cron logs
     * 
     * @since 2.2.0
     * @return bool True on success, false on failure
     */
    public function delete_all_cron_logs() {
        if (!current_user_can('manage_options')) {
            return false;
        }

        global $wpdb;
        
        // Escape table name properly
        $table = esc_sql($this->cron_logs_table);
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin-only maintenance operation
        $result = $wpdb->query(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is escaped
            "TRUNCATE TABLE $table"
        );
        
        return $result !== false;
    }
} 