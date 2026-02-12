<?php
/**
 * Database String Functions Test
 *
 * Tests database performance for text processing operations.
 * Performs 10,000 string manipulation operations using MySQL functions.
 *
 * @package     DiveWP
 * @author      Oleg Petrov
 * @version     2.0.1
 * @license     GPL-2.0-or-later
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    die(esc_html__('Direct access not permitted.', 'divewp-boost-site-performance'));
}

/**
 * Database String Functions Test Class
 * 
 * Performs text processing performance tests using MySQL
 * string manipulation functions.
 */
class DiveWP_String_Functions_Test {

    /**
     * Run the string functions performance test
     *
     * @return array Test results with timing and operations data
     */
    public static function run() {
        global $wpdb;

        // Internal configuration - NO external override allowed
        $config = array(
            'operations_count' => 10000
        );

        $start_time = microtime(true);
        $operations_completed = 0;
        $operation_times = array();
        
        // Get PHP max execution time for dynamic time limit checking
        $max_execution_time = (int) ini_get('max_execution_time');
        $time_limit = ($max_execution_time > 0) ? $max_execution_time - 5 : 25; // Leave 5 second buffer

        try {
            // Test strings for manipulation - expanded to 20 strings
            $test_strings = array(
                'DiveWP WordPress Performance Plugin',
                'MySQL String Functions Benchmark Test',
                'Database Text Processing Operations',
                'Hosting Server Performance Analysis',
                'Web Development Tools and Utilities',
                'Search Engine Optimization Content',
                'WooCommerce Product Description Text',
                'Blog Post Content Management System',
                'WordPress Plugin Development Framework',
                'Database Query Optimization Techniques',
                'Server Resource Management Solutions',
                'Web Application Performance Monitoring',
                'Content Delivery Network Integration',
                'Security Vulnerability Assessment Tools',
                'E-commerce Platform Enhancement Suite',
                'Digital Marketing Analytics Dashboard',
                'User Experience Optimization Platform',
                'Cross-browser Compatibility Testing',
                'Mobile-responsive Design Implementation',
                'Advanced Cache Management System'
            );

            // Run string operations
            for ($i = 0; $i < $config['operations_count']; $i++) {
                $op_start = microtime(true);
                
                $base_string = $test_strings[$i % count($test_strings)];
                $search_term = 'Performance';
                $replace_term = 'Optimization';

                // Perform various string operations in a single query
                // STRING FUNCTIONS BENCHMARK - Direct query required for string performance measurement
                // WordPress abstractions would distort timing results and defeat string testing purpose
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                $query = $wpdb->prepare("
                    SELECT 
                        LENGTH(%s) as string_length,
                        UPPER(%s) as upper_case,
                        LOWER(%s) as lower_case,
                        REVERSE(%s) as reversed,
                        LEFT(%s, 800) as left_chars,
                        RIGHT(%s, 800) as right_chars,
                        MID(%s, 5, 800) as mid_chars,
                        CONCAT(%s, ' - Test %d') as concatenated,
                        REPLACE(%s, %s, %s) as replaced,
                        LOCATE(%s, %s) as locate_position,
                        SUBSTRING(%s, 1, 50) as substring,
                        TRIM(%s) as trimmed,
                        LTRIM(%s) as left_trimmed,
                        RTRIM(%s) as right_trimmed,
                        REPEAT('*', 5) as repeated
                ", 
                    $base_string,                           // LENGTH
                    $base_string,                           // UPPER
                    $base_string,                           // LOWER
                    $base_string,                           // REVERSE
                    $base_string,                           // LEFT
                    $base_string,                           // RIGHT
                    $base_string,                           // MID
                    $base_string, $i,                       // CONCAT
                    $base_string, $search_term, $replace_term, // REPLACE
                    $search_term, $base_string,             // LOCATE
                    $base_string,                           // SUBSTRING
                    ' ' . $base_string . ' ',               // TRIM
                    ' ' . $base_string,                     // LTRIM
                    $base_string . ' '                      // RTRIM
                );

                // STRING FUNCTIONS BENCHMARK - Direct query execution required for string performance measurement
                // WordPress abstractions would invalidate string function timing accuracy
                $results = $wpdb->get_row($query, ARRAY_A); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared

                // Additional string operations every 20 iterations
                if ($i % 20 === 0) {
                    // STRING FUNCTIONS BENCHMARK - Direct query required for advanced string performance testing
                    // WordPress abstractions would interfere with advanced string function timing accuracy
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                    $advanced_query = $wpdb->prepare("
                        SELECT 
                            STRCMP(%s, %s) as string_compare,
                            SOUNDEX(%s) as soundex_code,
                            ASCII(%s) as ascii_value,
                            CHAR_LENGTH(%s) as char_length,
                            INSERT(%s, 5, 3, 'XXX') as inserted,
                            LPAD(%s, 200, '-') as left_padded,
                            RPAD(%s, 200, '-') as right_padded
                    ", 
                        $base_string, $replace_term,  // STRCMP
                        $base_string,                 // SOUNDEX
                        $base_string,                 // ASCII
                        $base_string,                 // CHAR_LENGTH
                        $base_string,                 // INSERT
                        $base_string,                 // LPAD
                        $base_string                  // RPAD
                    );
                    
                    // STRING FUNCTIONS BENCHMARK - Direct query execution required for advanced string performance measurement
                    // WordPress abstractions would distort advanced string function timing accuracy
                    $advanced_results = $wpdb->get_row($advanced_query, ARRAY_A); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
                }

                $op_end = microtime(true);
                $operation_times[] = $op_end - $op_start;
                $operations_completed++;

                // Check time limit every 300 operations
                if ($i % 300 === 0 && (microtime(true) - $start_time) > $time_limit) {
                    break;
                }
            }

        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('DiveWP String Functions Test Error: ' . $e->getMessage()); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- BENCHMARK REQUIREMENT: minimal debug logging gated by WP_DEBUG
            }
        }

        $end_time = microtime(true);
        $total_time = $end_time - $start_time;

        // Calculate statistics
        $avg_operation_time = !empty($operation_times) ? array_sum($operation_times) / count($operation_times) : 0;
        $max_operation_time = !empty($operation_times) ? max($operation_times) : 0;
        $min_operation_time = !empty($operation_times) ? min($operation_times) : 0;

        $operations_per_second = ($operations_completed > 0) ? $operations_completed / $total_time : 0;
        
        // CRITICAL: Calculate score using the scoring system
        require_once __DIR__ . '/scoring.php';
        $score = DiveWP_Benchmark_Database_Scoring::calculate_sub_test_score('string_functions', array(
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
            'test_name' => 'string_functions',
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
                // translators: %1$s is the formatted number of string operations per second (e.g., "1,500", "2,300")
                __('String functions completed at %1$s operations/second', 'divewp-boost-site-performance'),
                number_format($operations_per_second, 0)
            ),
            'status' => 'completed',
            'memory_used' => memory_get_usage(true),
            'timestamp' => current_time('mysql')
        );
        // ENHANCED UX: Add performance interpretation using scoring class (consistent with other DB sub-tests)
        $result['performance_interpretation'] = DiveWP_Benchmark_Database_Scoring::get_sub_test_performance_interpretation('string_functions', $result);

        return $result;
    }
} 