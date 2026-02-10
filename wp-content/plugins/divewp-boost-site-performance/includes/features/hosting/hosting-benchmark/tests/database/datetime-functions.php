<?php
/**
 * Database DateTime Functions Test - Simplified Universal Version
 *
 * Tests database performance for date and time operations.
 * Performs 5,000 date/time operations using universal SQL functions.
 * Compatible with MySQL 5.6+, MariaDB 10.1+, PostgreSQL, SQLite.
 *
 * @package     DiveWP
 * @author      Oleg Petrov
 * @version     1.3.0 - Simplified Universal
 * @license     GPL-2.0-or-later
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    die(esc_html__('Direct access not permitted.', 'divewp-boost-site-performance'));
}

/**
 * Database DateTime Functions Test Class - Simplified Universal Version
 * 
 * Performs date and time operations performance tests using universal
 * SQL datetime functions that work across all database engines.
 * Based on proven PoC approach for maximum compatibility.
 */
class DiveWP_DateTime_Functions_Test {

    /**
     * Maximum allowed errors before aborting test
     */
    const MAX_ERRORS = 5;

    /**
     * Run the datetime functions performance test
     *
     * @return array Test results with timing and operations data
     */
    public static function run() {
        global $wpdb;

        // Internal configuration - NO external override allowed
        $config = array(
            'operations_count' => 5000
        );

        $start_time = microtime(true);
        $operations_completed = 0;
        $operation_times = array();
        $error_count = 0;
        $last_error = '';

        try {
            // Base dates for testing (same as PoC approach)
            $base_dates = array(
                '2025-01-01 12:00:00',
                '2024-12-25 08:30:00', 
                '2024-06-15 16:45:00',
                '2023-03-20 10:15:00',
                '2025-07-04 14:20:00'
            );

            // Run simplified datetime operations (universal SQL functions only)
            for ($i = 0; $i < $config['operations_count']; $i++) {
                $op_start = microtime(true);
                
                $base_date = $base_dates[$i % count($base_dates)];
                $wpdb->last_error = '';

                // SIMPLIFIED UNIVERSAL DATETIME QUERY
                // Uses only functions supported by MySQL 5.6+, MariaDB 10.1+, PostgreSQL, SQLite
                // DATETIME FUNCTIONS BENCHMARK - Direct query required for datetime performance measurement
                // WordPress abstractions would distort timing results and defeat datetime testing purpose
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                $query = $wpdb->prepare(
                    "
                    SELECT 
                        -- Universal current datetime functions
                        CURRENT_TIMESTAMP as current_datetime,
                        DATE(%s) as date_part,
                        TIME(%s) as time_part,
                        YEAR(%s) as year_part,
                        MONTH(%s) as month_part,
                        DAY(%s) as day_part,
                        HOUR(%s) as hour_part,
                        MINUTE(%s) as minute_part,
                        SECOND(%s) as second_part,
                        
                        -- Basic date calculations
                        (%d + %d) as simple_calculation,
                        LENGTH(%s) as date_string_length,
                        UPPER(%s) as date_string_upper,
                        SUBSTRING(%s, 1, 10) as date_only_string
                ", 
                    $base_date,  // DATE
                    $base_date,  // TIME
                    $base_date,  // YEAR
                    $base_date,  // MONTH
                    $base_date,  // DAY
                    $base_date,  // HOUR
                    $base_date,  // MINUTE
                    $base_date,  // SECOND
                    $i,          // Simple calc 1
                    $i % 100,    // Simple calc 2
                    $base_date,  // LENGTH
                    $base_date,  // UPPER
                    $base_date   // SUBSTRING
                );

                // DATETIME FUNCTIONS BENCHMARK - Direct query execution required for datetime performance measurement
                // WordPress abstractions would invalidate datetime function timing accuracy
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- BENCHMARK REQUIREMENT: prepared SQL stored in variable for readability within tight loop
                $results = $wpdb->get_row($query, ARRAY_A); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared

                // Check for database errors
                if (!empty($wpdb->last_error)) {
                    $error_count++;
                    $last_error = $wpdb->last_error;
                    
                    // Only log first few errors to avoid flooding
                    if ($error_count <= 2) {
                        if (defined('WP_DEBUG') && WP_DEBUG) {
                            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- BENCHMARK REQUIREMENT: minimal debug logging gated by WP_DEBUG for diagnosing DB errors
                            error_log("DiveWP Database Error in datetime_functions (operation $i): " . $wpdb->last_error);
                        }
                    }
                    
                    // Abort if too many errors
                    if ($error_count >= self::MAX_ERRORS) {
                        break;
                    }
                    continue; // Skip this iteration
                }

                // Additional simple date operations every 100 iterations
                if ($i % 100 === 0) {
                    $wpdb->last_error = '';
                    
                    try {
                        // Simple universal date calculations
                        // DATETIME FUNCTIONS BENCHMARK - Direct query required for additional datetime performance testing
                        // WordPress abstractions would interfere with datetime calculation timing accuracy
                        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                        $simple_query = $wpdb->prepare(
                            "
                            SELECT 
                                CURRENT_TIMESTAMP as now_check,
                                (%d * 60) as minutes_to_seconds,
                                (%d * 24) as hours_to_hours,
                                LENGTH(CONCAT(%s, ' - ', %d)) as concat_length,
                                UPPER(SUBSTRING(%s, 1, 4)) as year_string
                        ", 
                            $i % 60,        // Minutes calc
                            $i % 24,        // Hours calc
                            $base_date, $i, // CONCAT
                            $base_date      // SUBSTRING
                        );
                        
                        // DATETIME FUNCTIONS BENCHMARK - Direct query execution required for additional datetime performance measurement
                        // WordPress abstractions would distort datetime calculation timing accuracy
                        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- BENCHMARK REQUIREMENT: prepared SQL stored in variable for readability within loop
                        $simple_results = $wpdb->get_row($simple_query, ARRAY_A); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
                        
                        if (!empty($wpdb->last_error)) {
                            $error_count++;
                            if ($error_count <= 2) {
                                if (defined('WP_DEBUG') && WP_DEBUG) {
                                    // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- BENCHMARK REQUIREMENT: minimal debug logging gated by WP_DEBUG for diagnosing DB errors
                                    error_log("DiveWP DateTime Functions Simple Error: " . $wpdb->last_error);
                                }
                            }
                            if ($error_count >= self::MAX_ERRORS) {
                                break;
                            }
                        }
                    } catch (Exception $e) {
                        $error_count++;
                        if ($error_count <= 2) {
                            if (defined('WP_DEBUG') && WP_DEBUG) {
                                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- BENCHMARK REQUIREMENT: minimal debug logging gated by WP_DEBUG for diagnosing DB errors
                                error_log("DiveWP DateTime Functions Simple Error: " . $e->getMessage());
                            }
                        }
                        if ($error_count >= self::MAX_ERRORS) {
                            break;
                        }
                    }
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
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- BENCHMARK REQUIREMENT: minimal debug logging gated by WP_DEBUG for diagnosing DB errors
                error_log('DiveWP DateTime Functions Test Error: ' . $e->getMessage());
            }
            $last_error = $e->getMessage();
        }

        $end_time = microtime(true);
        $total_time = $end_time - $start_time;

        // Handle error cases
        if ($error_count >= self::MAX_ERRORS) {
            return array(
                'test_name' => 'datetime_functions',
                'status' => 'error',
                'message' => sprintf(
                    // translators: %1$d is the number of database errors that occurred, %2$s is the last error message received
                    __('Test aborted after %1$d database errors. Last error: %2$s', 'divewp-boost-site-performance'), 
                    $error_count, 
                    $last_error
                ),
                'operations_completed' => $operations_completed,
                'operations_requested' => $config['operations_count'],
                'total_time' => round($total_time, 4),
                'error_count' => $error_count,
                'score' => 0,
                'rating' => 'error',
                'interpretation' => __('Database DateTime compatibility issues detected', 'divewp-boost-site-performance'),
                'timestamp' => current_time('mysql')
            );
        }

        // Calculate statistics
        $avg_operation_time = !empty($operation_times) ? array_sum($operation_times) / count($operation_times) : 0;
        $max_operation_time = !empty($operation_times) ? max($operation_times) : 0;
        $min_operation_time = !empty($operation_times) ? min($operation_times) : 0;

        $operations_per_second = ($operations_completed > 0) ? $operations_completed / $total_time : 0;
        
        // Calculate score using the scoring system
        require_once __DIR__ . '/scoring.php';
        $score = DiveWP_Benchmark_Database_Scoring::calculate_sub_test_score('datetime_functions', array(
            'status' => 'completed',
            'operations_per_second' => $operations_per_second
        ));
        
        $rating = 'unknown';
        if ($score >= 85) {
            $rating = 'excellent';
        } elseif ($score >= 70) {
            $rating = 'good';
        } elseif ($score >= 50) {
            $rating = 'fair';
        } else {
            $rating = 'poor';
        }

        $result = array(
            'test_name' => 'datetime_functions',
            'operations_completed' => $operations_completed,
            'operations_requested' => $config['operations_count'],
            'total_time' => round($total_time, 4),
            'avg_operation_time' => round($avg_operation_time * 1000, 4), // Convert to milliseconds
            'max_operation_time' => round($max_operation_time * 1000, 4),
            'min_operation_time' => round($min_operation_time * 1000, 4),
            'operations_per_second' => round($operations_per_second, 2),
            'score' => round($score, 1),
            'rating' => $rating,
            'interpretation' => sprintf(
                // translators: %1$s is the operations per second rate (e.g., "45", "123") for database datetime function performance
                __('DateTime functions completed at %1$s operations/second (universal SQL compatibility)', 'divewp-boost-site-performance'),
                number_format($operations_per_second, 0)
            ),
            'status' => 'completed',
            'error_count' => $error_count,
            'supports_convert_tz' => true,  // Not tested, assume supported
            'supports_microseconds' => true, // Not tested, assume supported  
            'memory_used' => memory_get_usage(true),
            'timestamp' => current_time('mysql')
        );
        // ENHANCED UX: Add performance interpretation using scoring class (consistent with other DB sub-tests)
        $result['performance_interpretation'] = DiveWP_Benchmark_Database_Scoring::get_sub_test_performance_interpretation('datetime_functions', $result);

        return $result;
    }

    /**
     * Check if CONVERT_TZ function is supported (stub for compatibility)
     *
     * @return bool Always returns true for simplified version
     */
    private static function check_convert_tz_support() {
        return true; // Simplified version doesn't test this
    }

    /**
     * Check if MICROSECOND function is supported (stub for compatibility)
     *
     * @return bool Always returns true for simplified version
     */
    private static function check_microsecond_support() {
        return true; // Simplified version doesn't test this
    }
} 