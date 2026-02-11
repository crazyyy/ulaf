<?php
/**
 * Database Operations Handler for DiveWP
 *
 * This class handles all database operations for DiveWP plugin including:
 * - Table creation and initialization
 * - Sample data population
 * - Table verification
 *
 * @package DiveWP
 * @since 1.0.4
 * @license GPL-2.0+
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main database handler class
 */
class DiveWP_Database {
    /**
     * Table names without prefix
     *
     * @since 1.0.4
     * @var array
     */
    private static $tables = array(
        'email_log' => 'divewp_email_log',
        'user_events' => 'divewp_user_events',
        'benchmark_results' => 'divewp_benchmark_results',
        'cron_logs' => 'divewp_cron_logs'
    );

    /**
     * Initialize database tables and add sample data
     *
     * @since 1.0.4
     * @return boolean True on success, false on failure
     */
    public static function init_tables() {
        global $wpdb;
        
        if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
            divewp_debug_log(esc_html__('Starting database table initialization', 'divewp-boost-site-performance'), 'info');
        }
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Create tables first
        $success = self::create_tables($charset_collate);
        
        if (!$success) {
            if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
                divewp_debug_log(esc_html__('Failed to create database tables', 'divewp-boost-site-performance'), 'error');
            }
            return false;
        }

        // Add sample data if tables are empty
        $sample_data_result = self::add_sample_data();
        
        if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
            $message = $sample_data_result 
                ? esc_html__('Database initialization completed successfully', 'divewp-boost-site-performance')
                : esc_html__('Database initialization failed at sample data stage', 'divewp-boost-site-performance');
            divewp_debug_log($message, $sample_data_result ? 'info' : 'error');
        }

        return $sample_data_result;
    }

    /**
     * Add sample data to empty tables
     * 
     * @since 1.0.4
     * @return boolean True on success, false on failure
     */
    public static function add_sample_data() {
        global $wpdb;
        
        try {
            $admin_email = sanitize_email(get_option('admin_email'));
            $current_user_id = get_current_user_id();
            
            // Check email log table
            $email_table = $wpdb->prefix . self::$tables['email_log'];
            
            // Real-time monitoring - intentionally not cached
            $table_name = esc_sql($email_table); // Properly escape the table name
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin-only table verification requiring real-time data
            $email_count = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
            
            if (0 === $email_count) {
                // @codingStandardsIgnoreStart
                $result = $wpdb->insert(
                    $email_table,
                    array(
                        'date_sent' => current_time('mysql'),
                        'status' => 'sent',
                        'initiator' => 'Plugin Installation',
                        'from_email' => $admin_email,
                        'to_email' => $admin_email,
                        /* translators: Email subject sent during plugin installation */
                        'subject' => esc_html__('Welcome to DiveWP - Email Tracking Demo', 'divewp-boost-site-performance'),
                        'error_msg' => null
                    ),
                    array('%s', '%s', '%s', '%s', '%s', '%s', '%s')
                );
                // @codingStandardsIgnoreEnd

                if (false === $result) {
                    /* translators: Error message when sample data insertion fails */
                    throw new Exception(esc_html__('Failed to insert sample email log data', 'divewp-boost-site-performance'));
                }
            }

            // Check user events table
            $events_table = $wpdb->prefix . self::$tables['user_events'];
            
            // Real-time monitoring - intentionally not cached
            $events_table_name = esc_sql($events_table); // Properly escape the table name
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin-only table verification requiring real-time data
            $events_count = (int) $wpdb->get_var("SELECT COUNT(*) FROM $events_table_name");
            
            if (0 === $events_count) {
                // @codingStandardsIgnoreStart
                $result = $wpdb->insert(
                    $events_table,
                    array(
                        'event_type' => 'plugin_management',
                        'event_action' => 'installed',
                        'user_id' => $current_user_id,
                        /* translators: Success message shown when plugin is installed */
                        'description' => esc_html__('DiveWP plugin installed and activated successfully', 'divewp-boost-site-performance'),
                        'status' => 'success',
                        'created_at' => current_time('mysql')
                    ),
                    array('%s', '%s', '%d', '%s', '%s', '%s')
                );
                // @codingStandardsIgnoreEnd

                if (false === $result) {
                    /* translators: Error message when user event data insertion fails */
                    throw new Exception(esc_html__('Failed to insert sample user event data', 'divewp-boost-site-performance'));
                }
            }

            return true;

        } catch (Exception $e) {
            if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
                divewp_debug_log(sprintf(
                    /* translators: %s: Error message */
                    esc_html__('Sample Data Error: %s', 'divewp-boost-site-performance'),
                    $e->getMessage()
                ), 'error');
            }
            return false;
        }
    }

    /**
     * Create database tables
     * 
     * @since 1.0.4
     * @param string $charset_collate Database charset and collation
     * @return boolean True on success, false on failure
     */
    private static function create_tables($charset_collate) {
        if (!current_user_can('manage_options')) {
            return false;
        }

        try {
            self::create_email_log_table($charset_collate);
            self::create_user_events_table($charset_collate);
            self::create_benchmark_results_table($charset_collate);
            self::create_cron_logs_table($charset_collate);
            return true;
        } catch (Exception $e) {
            if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
                divewp_debug_log(sprintf(
                    /* translators: %s: Error message */
                    esc_html__('Database Init Error: %s', 'divewp-boost-site-performance'),
                    $e->getMessage()
                ), 'error');
            }
            return false;
        }
    }

    /**
     * Create email log table for tracking email communications
     * 
     * @since 1.0.4
     * @param string $charset_collate Database charset and collation
     * @throws Exception If table creation fails
     * @return void
     */
    private static function create_email_log_table($charset_collate) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$tables['email_log'];
        
        // dbDelta requires specific formatting
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name and charset are admin-defined, not user input
        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            status varchar(20) NOT NULL,
            initiator text NOT NULL,
            from_email varchar(100) NOT NULL,
            to_email varchar(100) NOT NULL,
            subject varchar(255) NOT NULL,
            error_msg text,
            date_sent timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
        
        // Real-time table existence check - intentionally not cached
        // @codingStandardsIgnoreStart
        $table_exists = $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $table_name
        )) === $table_name;
        // @codingStandardsIgnoreEnd
        
        if (!$table_exists) {
            if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
                divewp_debug_log(sprintf(
                    /* translators: %1$s: SQL query, %2$s: Database error message */
                    esc_html__('Failed to create email log table. SQL: %1$s Error: %2$s', 'divewp-boost-site-performance'),
                    $sql,
                    $wpdb->last_error
                ), 'error');
            }
            /* translators: Error message when email log table creation fails */
            throw new Exception(esc_html__('Failed to create email log table', 'divewp-boost-site-performance'));
        }
    }

    /**
     * Create user events table for tracking user activities
     * 
     * @since 1.0.4
     * @param string $charset_collate Database charset and collation
     * @throws Exception If table creation fails
     * @return void
     */
    private static function create_user_events_table($charset_collate) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$tables['user_events'];
        
        // dbDelta requires specific formatting
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name and charset are admin-defined, not user input
        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            event_type varchar(50) NOT NULL,
            event_action varchar(50) NOT NULL,
            user_id bigint(20) NOT NULL,
            description text NOT NULL,
            status varchar(20) NOT NULL,
            created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
        
        // Real-time table existence check - intentionally not cached
        // @codingStandardsIgnoreStart
        $table_exists = $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $table_name
        )) === $table_name;
        // @codingStandardsIgnoreEnd
        
        if (!$table_exists) {
            if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
                divewp_debug_log(sprintf(
                    /* translators: %1$s: SQL query, %2$s: Database error message */
                    esc_html__('Failed to create user events table. SQL: %1$s Error: %2$s', 'divewp-boost-site-performance'),
                    $sql,
                    $wpdb->last_error
                ), 'error');
            }
            /* translators: Error message when user events table creation fails */
            throw new Exception(esc_html__('Failed to create user events table', 'divewp-boost-site-performance'));
        }
    }

    /**
     * Create benchmark results table for storing hosting performance data
     * 
     * @since 1.0.4
     * @param string $charset_collate Database charset and collation
     * @throws Exception If table creation fails
     * @return void
     */
    private static function create_benchmark_results_table($charset_collate) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$tables['benchmark_results'];
        
        // dbDelta requires specific formatting
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name and charset are admin-defined, not user input
        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            session_id varchar(100) NOT NULL,
            overall_score decimal(5,2) NOT NULL,
            performance_score decimal(5,2) NOT NULL,
            database_score decimal(5,2) NOT NULL,
            resources_score decimal(5,2) NOT NULL,
            concurrency_score decimal(5,2) NOT NULL,
            full_results longtext NOT NULL,
            test_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY test_date (test_date)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
        
        // Real-time table existence check - intentionally not cached
        // @codingStandardsIgnoreStart
        $table_exists = $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $table_name
        )) === $table_name;
        // @codingStandardsIgnoreEnd
        
        if (!$table_exists) {
            if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
                divewp_debug_log(sprintf(
                    /* translators: %1$s: SQL query, %2$s: Database error message */
                    esc_html__('Failed to create benchmark results table. SQL: %1$s Error: %2$s', 'divewp-boost-site-performance'),
                    $sql,
                    $wpdb->last_error
                ), 'error');
            }
            /* translators: Error message when benchmark results table creation fails */
            throw new Exception(esc_html__('Failed to create benchmark results table', 'divewp-boost-site-performance'));
        }
    }

    /**
     * Create cron logs table for tracking cron execution history
     * 
     * @since 2.2.0
     * @param string $charset_collate Database charset and collation
     * @throws Exception If table creation fails
     * @return void
     */
    private static function create_cron_logs_table($charset_collate) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$tables['cron_logs'];
        
        // dbDelta requires specific formatting
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name and charset are admin-defined, not user input
        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            hook varchar(255) NOT NULL,
            args longtext,
            trigger_source varchar(50) NOT NULL DEFAULT 'wp_cron',
            started_at datetime NOT NULL,
            finished_at datetime,
            duration_ms int(10) unsigned,
            peak_memory_bytes bigint(20) unsigned,
            status varchar(20) NOT NULL DEFAULT 'running',
            error_message text,
            error_trace text,
            PRIMARY KEY  (id),
            KEY idx_hook (hook),
            KEY idx_started (started_at),
            KEY idx_status (status)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
        
        // Real-time table existence check - intentionally not cached
        // @codingStandardsIgnoreStart
        $table_exists = $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $table_name
        )) === $table_name;
        // @codingStandardsIgnoreEnd
        
        if (!$table_exists) {
            if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
                divewp_debug_log(sprintf(
                    /* translators: %1$s: SQL query, %2$s: Database error message */
                    esc_html__('Failed to create cron logs table. SQL: %1$s Error: %2$s', 'divewp-boost-site-performance'),
                    $sql,
                    $wpdb->last_error
                ), 'error');
            }
            /* translators: Error message when cron logs table creation fails */
            throw new Exception(esc_html__('Failed to create cron logs table', 'divewp-boost-site-performance'));
        }
    }

    /**
     * Verify existence of required database tables
     * 
     * @since 1.0.4
     * @return boolean True if all tables exist, false otherwise
     */
    public static function verify_tables() {
        global $wpdb;
        
        $missing_tables = array();

        foreach (self::$tables as $key => $table) {
            $table_name = $wpdb->prefix . $table;
            // Real-time table existence check - intentionally not cached
            // @codingStandardsIgnoreStart
            if ($wpdb->get_var($wpdb->prepare(
                "SHOW TABLES LIKE %s",
                $table_name
            )) !== $table_name) {
                $missing_tables[] = $table;
            }
            // @codingStandardsIgnoreEnd
        }

        if (!empty($missing_tables)) {
            if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
                divewp_debug_log(sprintf(
                    /* translators: %s: Comma-separated list of table names */
                    esc_html__('DiveWP missing required tables: %s', 'divewp-boost-site-performance'),
                    implode(', ', $missing_tables)
                ), 'error');
            }
            return false;
        }

        return true;
    }
} 