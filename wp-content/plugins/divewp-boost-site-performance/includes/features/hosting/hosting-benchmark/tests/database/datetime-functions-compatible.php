<?php
/**
 * Cross-Database Compatible DateTime Functions Test
 *
 * Tests database performance for date and time operations.
 * Uses the DiveWP_Database_Compatibility layer for cross-database support.
 *
 * @package     DiveWP
 * @author      Oleg Petrov
 * @version     1.1.1
 * @license     GPL-2.0-or-later
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    die(esc_html__('Direct access not permitted.', 'divewp-boost-site-performance'));
}

/**
 * Cross-Database Compatible DateTime Functions Test Class
 * 
 * Performs date and time operations performance tests using database-agnostic
 * functions that work across MySQL, MariaDB, PostgreSQL, SQLite, and SQL Server.
 */
class DiveWP_DateTime_Functions_Compatible_Test {

    /**
     * Run the datetime functions performance test
     *
     * @param array $config Test configuration
     * @return array Test results with timing and operations data
     */
    public static function run($config = array()) {
        global $wpdb;

        // Ensure compatibility layer is loaded
        if (!class_exists('DiveWP_Database_Compatibility')) {
            require_once DIVEWP_PLUGIN_DIR . 'includes/class-divewp-database-compatibility.php';
        }

        // Detect database capabilities
        $db_capabilities = DiveWP_Database_Compatibility::get_capabilities();
        $db_engine = $db_capabilities['engine'];

        // Set default configuration
        $config = wp_parse_args($config, array(
            'operations_count' => 5000
        ));

        $start_time = microtime(true);
        $operations_completed = 0;
        $operation_times = array();

        try {
            // Base dates for testing
            $base_dates = array(
                '2025-01-01 12:00:00',
                '2024-12-25 08:30:00', 
                '2024-06-15 16:45:00',
                '2023-03-20 10:15:00',
                '2025-07-04 14:20:00'
            );

            // Run datetime operations
            for ($i = 0; $i < $config['operations_count']; $i++) {
                $op_start = microtime(true);
                
                $base_date = $base_dates[$i % count($base_dates)];
                $days_offset = ($i % 365);
                $hours_offset = ($i % 24);

                // Build cross-database compatible query using the compatibility layer
                $query_template = "
                    SELECT 
                        %NOW% as %CURRENT_DATETIME%,
                        %CURRENT_DATE% as %CURRENT_DATE_VAL%,
                        %CURRENT_TIME% as %CURRENT_TIME_VAL%,
                        DATE(%s) as date_part,
                        TIME(%s) as time_part,
                        YEAR(%s) as year_part,
                        MONTH(%s) as month_part,
                        DAY(%s) as day_part,
                        HOUR(%s) as hour_part,
                        MINUTE(%s) as minute_part,
                        SECOND(%s) as second_part
                ";

                // Get safe column aliases to avoid reserved keywords
                $safe_aliases = DiveWP_Database_Compatibility::get_safe_aliases(
                    array('current_datetime', 'current_date_val', 'current_time_val'),
                    $db_engine
                );

                // Replace function placeholders with database-specific functions
                $replacements = array(
                    '%NOW%' => array('function' => 'now'),
                    '%CURRENT_DATE%' => array('function' => 'current_date'),
                    '%CURRENT_TIME%' => array('function' => 'current_time'),
                    '%CURRENT_DATETIME%' => $safe_aliases['current_datetime'],
                    '%CURRENT_DATE_VAL%' => $safe_aliases['current_date_val'],
                    '%CURRENT_TIME_VAL%' => $safe_aliases['current_time_val']
                );

                $compatible_query = DiveWP_Database_Compatibility::build_compatible_query(
                    $query_template, 
                    $replacements, 
                    $db_engine
                );

                // Execute the query with prepared parameters
                $prepared_query = $wpdb->prepare(
                    $compatible_query,
                    $base_date,  // DATE
                    $base_date,  // TIME
                    $base_date,  // YEAR
                    $base_date,  // MONTH
                    $base_date,  // DAY
                    $base_date,  // HOUR
                    $base_date,  // MINUTE
                    $base_date   // SECOND
                );

                $results = $wpdb->get_row($prepared_query, ARRAY_A);

                // Additional datetime calculations (database-specific)
                if ($i % 100 === 0) {
                    self::run_advanced_datetime_operations($base_date, $days_offset, $hours_offset, $db_engine);
                }

                $op_end = microtime(true);
                $operation_times[] = $op_end - $op_start;
                $operations_completed++;

                // Check time limit every 500 operations
                if ($i % 500 === 0 && (microtime(true) - $start_time) > 25) {
                    break;
                }
            }

        } catch (Exception $e) {
            error_log('DiveWP Compatible DateTime Functions Test Error: ' . $e->getMessage());
        }

        $end_time = microtime(true);
        $total_time = $end_time - $start_time;

        // Calculate statistics
        $avg_operation_time = !empty($operation_times) ? array_sum($operation_times) / count($operation_times) : 0;
        $max_operation_time = !empty($operation_times) ? max($operation_times) : 0;
        $min_operation_time = !empty($operation_times) ? min($operation_times) : 0;

        return array(
            'test_name' => 'datetime_functions_compatible',
            'database_engine' => $db_engine,
            'database_version' => $db_capabilities['version'],
            'operations_completed' => $operations_completed,
            'operations_requested' => $config['operations_count'],
            'total_time' => round($total_time, 4),
            'avg_operation_time' => round($avg_operation_time * 1000, 4), // Convert to milliseconds
            'max_operation_time' => round($max_operation_time * 1000, 4),
            'min_operation_time' => round($min_operation_time * 1000, 4),
            'operations_per_second' => $total_time > 0 ? round($operations_completed / $total_time, 2) : 0,
            'success_rate' => round(($operations_completed / max($config['operations_count'], 1)) * 100, 2),
            'memory_used' => memory_get_usage(true),
            'database_features' => $db_capabilities['features'],
            'timestamp' => current_time('mysql')
        );
    }

    /**
     * Run advanced datetime operations (database-specific)
     *
     * @param string $base_date Base date for calculations
     * @param int $days_offset Days offset
     * @param int $hours_offset Hours offset  
     * @param string $db_engine Database engine
     */
    private static function run_advanced_datetime_operations($base_date, $days_offset, $hours_offset, $db_engine) {
        global $wpdb;

        // Only run advanced operations if database supports them
        if (!DiveWP_Database_Compatibility::supports_feature('temp_tables', $db_engine)) {
            return; // Skip if not supported
        }

        try {
            // Database-specific advanced datetime operations
            switch ($db_engine) {
                case 'mysql':
                case 'mariadb':
                    $advanced_query = $wpdb->prepare("
                        SELECT 
                            QUARTER(%s) as quarter,
                            LAST_DAY(%s) as last_day_of_month,
                            MAKEDATE(2025, %d) as make_date,
                            MAKETIME(%d, 30, 45) as make_time,
                            TO_DAYS(%s) as to_days,
                            FROM_DAYS(TO_DAYS(%s) + %d) as from_days
                    ", 
                        $base_date,                     // QUARTER
                        $base_date,                     // LAST_DAY
                        $days_offset,                   // MAKEDATE
                        $hours_offset,                  // MAKETIME
                        $base_date,                     // TO_DAYS
                        $base_date, $days_offset        // FROM_DAYS
                    );
                    break;

                case 'postgresql':
                    $advanced_query = $wpdb->prepare("
                        SELECT 
                            EXTRACT(QUARTER FROM %s::date) as quarter,
                            (%s::date + INTERVAL '1 month - 1 day') as last_day_of_month,
                            MAKE_DATE(2025, 1, %d) as make_date,
                            MAKE_TIME(%d, 30, 45) as make_time
                    ", 
                        $base_date,      // QUARTER
                        $base_date,      // LAST_DAY equivalent
                        $days_offset,    // MAKE_DATE
                        $hours_offset    // MAKE_TIME
                    );
                    break;

                case 'sqlite':
                    $advanced_query = $wpdb->prepare("
                        SELECT 
                            ((CAST(strftime('%%m', %s) AS INTEGER) + 2) / 3) as quarter,
                            date(%s, 'start of month', '+1 month', '-1 day') as last_day_of_month,
                            date('2025-01-01', '+' || %d || ' days') as make_date
                    ", 
                        $base_date,      // QUARTER calculation
                        $base_date,      // LAST_DAY equivalent  
                        $days_offset     // Date arithmetic
                    );
                    break;

                case 'sqlserver':
                    $advanced_query = $wpdb->prepare("
                        SELECT 
                            DATEPART(QUARTER, %s) as quarter,
                            EOMONTH(%s) as last_day_of_month,
                            DATEFROMPARTS(2025, 1, %d) as make_date,
                            TIMEFROMPARTS(%d, 30, 45, 0, 0) as make_time
                    ", 
                        $base_date,      // QUARTER
                        $base_date,      // EOMONTH
                        $days_offset,    // DATEFROMPARTS
                        $hours_offset    // TIMEFROMPARTS
                    );
                    break;

                default:
                    return; // Unsupported database
            }

            $advanced_results = $wpdb->get_row($advanced_query, ARRAY_A);

        } catch (Exception $e) {
            error_log("DiveWP: Advanced datetime operations failed for {$db_engine}: " . $e->getMessage());
        }
    }

    /**
     * Get test information for compatibility
     *
     * @return array Test information
     */
    public static function get_test_info() {
        // Ensure compatibility layer is loaded
        if (!class_exists('DiveWP_Database_Compatibility')) {
            require_once DIVEWP_PLUGIN_DIR . 'includes/class-divewp-database-compatibility.php';
        }

        $db_capabilities = DiveWP_Database_Compatibility::get_capabilities();

        return array(
            'name' => 'Cross-Database DateTime Functions',
            'description' => 'Tests date and time operations across different database engines',
            'operations_count' => 5000,
            'estimated_time' => '10-30 seconds',
            'database_engine' => $db_capabilities['engine'],
            'supported_features' => $db_capabilities['features'],
            'functions_tested' => array(
                'NOW', 'CURRENT_DATE', 'CURRENT_TIME', 'DATE', 'TIME',
                'YEAR', 'MONTH', 'DAY', 'HOUR', 'MINUTE', 'SECOND',
                'QUARTER', 'LAST_DAY', 'MAKEDATE', 'MAKETIME'
            ),
            'compatibility' => array(
                'mysql' => 'Full support',
                'mariadb' => 'Full support', 
                'postgresql' => 'Full support with function mapping',
                'sqlite' => 'Partial support (limited advanced functions)',
                'sqlserver' => 'Full support with function mapping'
            )
        );
    }
} 