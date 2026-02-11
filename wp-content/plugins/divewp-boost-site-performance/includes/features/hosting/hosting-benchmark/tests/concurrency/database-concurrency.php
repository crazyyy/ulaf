<?php
/**
 * Database Concurrency Test
 *
 * Tests database performance under concurrent load by simulating
 * multiple simultaneous database operations (495 operations total).
 *
 * @package     DiveWP
 * @author      Oleg Petrov
 * @version     1.0.4
 * @license     GPL-2.0-or-later
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    die(esc_html__('Direct access not permitted.', 'divewp-boost-site-performance'));
}

/**
 * Database Concurrency Test Class
 */
class DiveWP_Database_Concurrency_Test {

    /**
     * Maximum error count before aborting test
     */
    const MAX_ERRORS = 5;

    /**
     * Test configuration
     */
    const CONCURRENT_OPERATIONS = 495;
    const BATCH_SIZE = 15;
    const MAX_TEST_TIME = 45; // 45 seconds max

    /**
     * Database compatibility instance
     * @var DiveWP_Database_Compatibility
     */
    private static $db_compat = null;

    /**
     * Run the database concurrency test
     *
     * @return array Test results
     */
    public static function run() {
        $start_time = microtime(true);
        $test_name = 'Database Concurrency';
        
        // Initialize database compatibility layer
        self::init_database_compatibility();
        
        $result = array(
            'status' => 'completed',
            'test_name' => $test_name,
            'total_time' => 0,
            'operations_completed' => 0,
            'operations_per_second' => 0,
            'concurrent_efficiency' => 0,
            'score' => 0,
            'rating' => 'unknown',
            'interpretation' => '',
            'error_count' => 0,
            'timestamp' => current_time('mysql')
        );

        try {
            // Set appropriate time limit
            $time_limit = get_transient('divewp_benchmark_time_limit') ?: 54;
            // BENCHMARK REQUIREMENT - Extended time limit needed for server stress testing
            // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
            set_time_limit($time_limit);

            // Store original SQL mode and set compatible mode
            $original_sql_mode = self::set_compatible_sql_mode();

            // TRUE PARALLEL: use REST worker with db type
            require_once __DIR__ . '/helpers.php';
            $token = wp_generate_password(16, false, false);
            set_transient('divewp_concurrency_worker_token', $token, MINUTE_IN_SECONDS);
            $parallel = 12;
            $runtime = 8.0;
            $pool = DiveWP_Concurrency_MultiRunner::run('db', $parallel, $runtime, $token);
            delete_transient('divewp_concurrency_worker_token');

            // Translate pool results
            $test_result = array(
                'operations_completed' => $pool['success_count'] + $pool['fail_count'],
                'error_count' => $pool['fail_count'],
                'batch_times' => $pool['durations'],
                'batches_completed' => count($pool['durations'])
            );
            
            // Restore original SQL mode
            self::restore_sql_mode($original_sql_mode);
            
            // Merge test results
            $result = array_merge($result, $test_result);
            
            // Calculate final metrics
            $total_time = microtime(true) - $start_time;
            $result['total_time'] = $total_time;
            
            if ($total_time > 0 && $result['operations_completed'] > 0) {
                $result['operations_per_second'] = round($result['operations_completed'] / $total_time, 2);
                // Efficiency relative to target throughput: parallel * 10 ops per second target
                $target = $parallel * 10;
                $result['concurrent_efficiency'] = $target > 0 ? round(min(120, ($result['operations_per_second'] / $target) * 100), 2) : 0;
            }

            // Calculate score and rating
            $result['score'] = self::calculate_score($result);
            $result['rating'] = self::get_rating($result['score']);
            $result['interpretation'] = self::get_interpretation($result);
            
            // Add status fields for UX enhancement
            $result['test_status'] = ($result['operations_completed'] >= self::CONCURRENT_OPERATIONS) ? 'completed' : 'partial';
            $result['completed_operations'] = $result['operations_completed'];
            $result['total_operations'] = self::CONCURRENT_OPERATIONS;
            
            if ($result['test_status'] === 'partial') {
                $result['timeout_reason'] = __('Performance degraded under concurrent load.', 'divewp-boost-site-performance');
            } else {
                $result['timeout_reason'] = '';
            }

            // ENHANCED UX: Add performance interpretation data using scoring class
            require_once DIVEWP_PLUGIN_DIR . 'includes/features/hosting/hosting-benchmark/tests/concurrency/scoring.php';
            $result['performance_interpretation'] = DiveWP_Benchmark_Concurrency_Scoring::get_sub_test_performance_interpretation('database_concurrency', $result);

        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // BENCHMARK REQUIREMENT - minimal debug logging gated by WP_DEBUG
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log("DiveWP Database Concurrency Error: " . $e->getMessage());
            }
            
            $result['status'] = 'error';
            $result['message'] = $e->getMessage();
            $result['total_time'] = microtime(true) - $start_time;
            $result['score'] = 0;
            $result['rating'] = 'error';
            // translators: %s is the specific error message from the database concurrency test failure
            $result['interpretation'] = sprintf(__('Database concurrency test failed: %s', 'divewp-boost-site-performance'), $e->getMessage());
            
            // Add status fields for error case
            $result['test_status'] = 'error';
            $result['completed_operations'] = $result['operations_completed'] ?? 0;
            $result['total_operations'] = self::CONCURRENT_OPERATIONS;
            // translators: %s is the specific error message explaining why the database concurrency test failed
            $result['timeout_reason'] = sprintf(__('Test failed with error: %s', 'divewp-boost-site-performance'), $e->getMessage());
        }

        return $result;
    }

    /**
     * Initialize database compatibility layer
     */
    private static function init_database_compatibility() {
        if (self::$db_compat === null) {
            // Load database compatibility class
            if (!class_exists('DiveWP_Database_Compatibility')) {
                require_once ABSPATH . 'wp-content/plugins/divewp-boost-site-performance/includes/class-divewp-database-compatibility.php';
            }
            self::$db_compat = new DiveWP_Database_Compatibility();
        }
    }

    /**
     * Set compatible SQL mode for MariaDB/MySQL
     *
     * @return string Original SQL mode
     */
    private static function set_compatible_sql_mode() {
        global $wpdb;
        
        $original_sql_mode = '';
        $db_info = self::get_database_info();

        if ($db_info['is_mariadb']) {
            // DATABASE CONCURRENCY TEST - Direct query required for SQL mode detection during benchmark setup
            // WordPress has no function to detect current SQL mode; essential for MariaDB compatibility testing
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            $result = $wpdb->get_row("SELECT @@sql_mode as mode", ARRAY_A);
            if ($result && isset($result['mode'])) {
                $original_sql_mode = $result['mode'];
                
                // Remove ONLY_FULL_GROUP_BY for compatibility
                $compatible_mode = str_replace('ONLY_FULL_GROUP_BY,', '', $original_sql_mode);
                $compatible_mode = str_replace(',ONLY_FULL_GROUP_BY', '', $compatible_mode);
                $compatible_mode = str_replace('ONLY_FULL_GROUP_BY', '', $compatible_mode);
                
                if ($compatible_mode !== $original_sql_mode) {
                    // DATABASE CONCURRENCY TEST - Direct query required for SQL mode configuration during benchmark
                    // WordPress has no function to modify SQL mode; essential for accurate concurrency testing
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                    $wpdb->query($wpdb->prepare("SET SESSION sql_mode = %s", $compatible_mode));
                }
            }
        }

        return $original_sql_mode;
    }

    /**
     * Restore original SQL mode
     *
     * @param string $original_sql_mode Original SQL mode to restore
     */
    private static function restore_sql_mode($original_sql_mode) {
        global $wpdb;
        
        if (!empty($original_sql_mode)) {
            $db_info = self::get_database_info();
            if ($db_info['is_mariadb']) {
                // DATABASE CONCURRENCY TEST - Direct query required for SQL mode restoration after benchmark
                // WordPress has no function to restore SQL mode; essential for proper test cleanup
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                $wpdb->query($wpdb->prepare("SET SESSION sql_mode = %s", $original_sql_mode));
            }
        }
    }

    /**
     * Execute concurrent database operations
     *
     * @param float $start_time Test start time
     * @return array Operation results
     */
    private static function execute_concurrent_database_operations($start_time) {
        global $wpdb;
        
        $operations_completed = 0;
        $error_count = 0;
        $batch_times = array();
        
        // Calculate number of batches
        $total_batches = ceil(self::CONCURRENT_OPERATIONS / self::BATCH_SIZE);
        
        for ($batch = 0; $batch < $total_batches; $batch++) {
            // Check timeout
            if ((microtime(true) - $start_time) > self::MAX_TEST_TIME) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    // BENCHMARK REQUIREMENT - Minimal debug logging for performance diagnostics
                    // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                    error_log("DiveWP Database Concurrency: Timeout reached, processed {$operations_completed} operations");
                }
                break;
            }
            
            $batch_start = microtime(true);
            $batch_operations = min(self::BATCH_SIZE, self::CONCURRENT_OPERATIONS - $operations_completed);
            
            // Execute batch of concurrent operations
            $batch_result = self::execute_operation_batch($batch_operations);
            
            if ($batch_result['success']) {
                $operations_completed += $batch_result['completed'];
                $batch_times[] = microtime(true) - $batch_start;
            } else {
                $error_count++;
                if ($error_count >= self::MAX_ERRORS) {
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        // BENCHMARK REQUIREMENT - minimal debug logging gated by WP_DEBUG
                        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                        error_log("DiveWP Database Concurrency: Max errors reached, aborting");
                    }
                    break;
                }
            }
            
            // Brief pause between batches to avoid overwhelming the database
            usleep(50000); // 50ms
        }
        
        return array(
            'operations_completed' => $operations_completed,
            'error_count' => $error_count,
            'batch_times' => $batch_times,
            'batches_completed' => count($batch_times)
        );
    }

    /**
     * Execute a batch of database operations
     *
     * @param int $operations Number of operations in this batch
     * @return array Batch results
     */
    private static function execute_operation_batch($operations) {
        global $wpdb;
        
        $completed = 0;
        $operations_types = array('insert', 'select', 'update', 'delete');
        
        try {
            // Clear any previous errors
            $wpdb->last_error = '';
            
            for ($i = 0; $i < $operations; $i++) {
                $operation_type = $operations_types[$i % 4];
                
                switch ($operation_type) {
                    case 'insert':
                        $success = self::execute_insert_operation();
                        break;
                    case 'select':
                        $success = self::execute_select_operation();
                        break;
                    case 'update':
                        $success = self::execute_update_operation();
                        break;
                    case 'delete':
                        $success = self::execute_delete_operation();
                        break;
                    default:
                        $success = false;
                }
                
                if ($success) {
                    $completed++;
                } else {
                    // Log error but continue
                    if (!empty($wpdb->last_error)) {
                        if (defined('WP_DEBUG') && WP_DEBUG) {
                            // BENCHMARK REQUIREMENT - minimal debug logging gated by WP_DEBUG
                            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                            error_log("DiveWP Database Concurrency Operation Error: " . $wpdb->last_error);
                        }
                    }
                }
            }
            
            return array(
                'success' => true,
                'completed' => $completed
            );
            
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // BENCHMARK REQUIREMENT - minimal debug logging gated by WP_DEBUG
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log("DiveWP Database Concurrency Batch Error: " . $e->getMessage());
            }
            return array(
                'success' => false,
                'completed' => $completed
            );
        }
    }

    /**
     * Execute INSERT operation
     *
     * @return bool Success status
     */
    private static function execute_insert_operation() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'divewp_benchmark_temp_' . uniqid();
        
        // Create temporary table
        // BENCHMARK REQUIREMENT - Dynamic temporary table required for isolated INSERT testing
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $create_sql = "CREATE TEMPORARY TABLE `{$table_name}` (
            id INT AUTO_INCREMENT PRIMARY KEY,
            test_data VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        // DATABASE CONCURRENCY TEST - Direct query required for temporary table creation during INSERT testing
        // WordPress has no equivalent for CREATE TEMPORARY TABLE; essential for isolated concurrency testing
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange
        // BENCHMARK REQUIREMENT - Executing dynamic DDL for temp table creation during benchmark
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $created = $wpdb->query($create_sql); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
        if ($created === false) {
            return false;
        }
        
        // Insert test data
        $test_data = 'concurrent_test_' . uniqid();
        // BENCHMARK REQUIREMENT - Dynamic table name cannot be parameterized; identifier interpolation is required
        $insert_sql = $wpdb->prepare(
            "INSERT INTO `{$table_name}` (test_data) VALUES (%s)", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $test_data
        );
        
        // DATABASE CONCURRENCY TEST - Direct query required for INSERT performance measurement
        // WordPress has no equivalent for timing raw INSERT operations; essential for concurrency benchmarking
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        // BENCHMARK REQUIREMENT - Running prepared statement stored in variable for clarity
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $result = $wpdb->query($insert_sql); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
        
        // Clean up temporary table
        // DATABASE CONCURRENCY TEST - Direct query required for temporary table cleanup after INSERT testing
        // WordPress has no equivalent for DROP TEMPORARY TABLE; essential for proper test isolation
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange
        // BENCHMARK REQUIREMENT - Dynamic temp table cleanup; identifier interpolation required
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query("DROP TEMPORARY TABLE IF EXISTS `{$table_name}`"); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        
        return $result !== false;
    }

    /**
     * Execute SELECT operation
     *
     * @return bool Success status
     */
    private static function execute_select_operation() {
        global $wpdb;
        
        // Use existing WordPress tables for realistic testing
        $queries = array(
            "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'publish'",
            "SELECT COUNT(*) FROM {$wpdb->users} WHERE user_status = 0",
            "SELECT COUNT(*) FROM {$wpdb->options} WHERE autoload = 'yes'",
            "SELECT MAX(ID) FROM {$wpdb->posts}",
            "SELECT COUNT(DISTINCT post_type) FROM {$wpdb->posts}"
        );
        
        $query = $queries[array_rand($queries)];
        // DATABASE CONCURRENCY TEST - Direct query required for SELECT performance measurement under concurrent load
        // WordPress abstractions (WP_Query) would add overhead and distort concurrency testing accuracy
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        // Query strings are selected from a fixed whitelist with no user input
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $result = $wpdb->get_var($query); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
        
        return $result !== null;
    }

    /**
     * Execute UPDATE operation
     *
     * @return bool Success status
     */
    private static function execute_update_operation() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'divewp_benchmark_temp_' . uniqid();
        
        // Create temporary table with data
        // BENCHMARK REQUIREMENT - Dynamic temporary table required for isolated UPDATE testing
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $create_sql = "CREATE TEMPORARY TABLE `{$table_name}` (
            id INT AUTO_INCREMENT PRIMARY KEY,
            test_value INT DEFAULT 0,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        // DATABASE CONCURRENCY TEST - Direct query required for temporary table creation during UPDATE testing
        // WordPress has no equivalent for CREATE TEMPORARY TABLE; essential for isolated UPDATE concurrency testing
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange
        // BENCHMARK REQUIREMENT - Executing dynamic DDL for temp table creation during benchmark
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        if ($wpdb->query($create_sql) === false) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
            return false;
        }
        
        // Insert initial data
        // DATABASE CONCURRENCY TEST - Direct query required for test data insertion during UPDATE benchmarking
        // WordPress has no equivalent for bulk INSERT into temporary tables; essential for UPDATE performance testing
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        // BENCHMARK REQUIREMENT - Bulk insert into dynamic temp table for UPDATE testing
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query("INSERT INTO `{$table_name}` (test_value) VALUES (1), (2), (3), (4), (5)"); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        
        // Update data
        // BENCHMARK REQUIREMENT - Dynamic table name required; identifier interpolation is necessary
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $update_sql = "UPDATE `{$table_name}` SET test_value = test_value + 1 WHERE id > 0";
        // DATABASE CONCURRENCY TEST - Direct query required for UPDATE performance measurement under concurrent load
        // WordPress has no equivalent for timing raw UPDATE operations; essential for concurrency benchmarking
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        // BENCHMARK REQUIREMENT - Executing dynamic UPDATE statement stored in variable for readability
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $result = $wpdb->query($update_sql); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
        
        // Clean up
        // DATABASE CONCURRENCY TEST - Direct query required for temporary table cleanup after UPDATE testing
        // WordPress has no equivalent for DROP TEMPORARY TABLE; essential for proper test isolation
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange
        // BENCHMARK REQUIREMENT - Dynamic temp table cleanup; identifier interpolation required
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query("DROP TEMPORARY TABLE IF EXISTS `{$table_name}`"); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        
        return $result !== false;
    }

    /**
     * Execute DELETE operation
     *
     * @return bool Success status
     */
    private static function execute_delete_operation() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'divewp_benchmark_temp_' . uniqid();
        
        // Create temporary table with data
        // BENCHMARK REQUIREMENT - Dynamic temporary table required for isolated DELETE testing
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $create_sql = "CREATE TEMPORARY TABLE `{$table_name}` (
            id INT AUTO_INCREMENT PRIMARY KEY,
            delete_flag BOOLEAN DEFAULT FALSE
        )";
        
        // DATABASE CONCURRENCY TEST - Direct query required for temporary table creation during DELETE testing
        // WordPress has no equivalent for CREATE TEMPORARY TABLE; essential for isolated DELETE concurrency testing
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange
        // BENCHMARK REQUIREMENT - Executing dynamic DDL for temp table creation during benchmark
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        if ($wpdb->query($create_sql) === false) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
            return false;
        }
        
        // Insert data to delete
        // DATABASE CONCURRENCY TEST - Direct query required for test data insertion during DELETE benchmarking
        // WordPress has no equivalent for bulk INSERT into temporary tables; essential for DELETE performance testing
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        // BENCHMARK REQUIREMENT - Bulk insert into dynamic temp table for DELETE testing
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query("INSERT INTO `{$table_name}` (delete_flag) VALUES (TRUE), (FALSE), (TRUE)"); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        
        // Delete specific data
        // BENCHMARK REQUIREMENT - Dynamic table name required; identifier interpolation is necessary
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $delete_sql = "DELETE FROM `{$table_name}` WHERE delete_flag = TRUE";
        // DATABASE CONCURRENCY TEST - Direct query required for DELETE performance measurement under concurrent load
        // WordPress has no equivalent for timing raw DELETE operations; essential for concurrency benchmarking
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        // BENCHMARK REQUIREMENT - Executing dynamic DELETE statement stored in variable for readability
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $result = $wpdb->query($delete_sql); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
        
        // Clean up
        // DATABASE CONCURRENCY TEST - Direct query required for temporary table cleanup after DELETE testing
        // WordPress has no equivalent for DROP TEMPORARY TABLE; essential for proper test isolation
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange
        // BENCHMARK REQUIREMENT - Dynamic temp table cleanup; identifier interpolation required
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query("DROP TEMPORARY TABLE IF EXISTS `{$table_name}`"); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        
        return $result !== false;
    }

    /**
     * Get database information
     *
     * @return array Database info
     */
    private static function get_database_info() {
        global $wpdb;
        
        // DATABASE CONCURRENCY TEST - Direct query required for database version detection during benchmark setup
        // WordPress has no function to detect database version; essential for MariaDB compatibility testing
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $version = $wpdb->get_var("SELECT VERSION()");
        $is_mariadb = stripos($version, 'mariadb') !== false;
        
        return array(
            'version' => $version,
            'is_mariadb' => $is_mariadb
        );
    }

    /**
     * Calculate performance score
     *
     * @param array $result Test results
     * @return float Score from 0 to 100
     */
    private static function calculate_score($result) {
        if ($result['status'] !== 'completed' || $result['operations_completed'] === 0) {
            return 0;
        }
        
        $ops_per_second = $result['operations_per_second'];
        $efficiency = $result['concurrent_efficiency'];
        
        // Base score from operations per second
        $base_score = 0;
        if ($ops_per_second >= 100) {
            $base_score = 100;
        } elseif ($ops_per_second >= 75) {
            $base_score = 80 + (20 * (($ops_per_second - 75) / 25));
        } elseif ($ops_per_second >= 50) {
            $base_score = 60 + (20 * (($ops_per_second - 50) / 25));
        } elseif ($ops_per_second >= 25) {
            $base_score = 40 + (20 * (($ops_per_second - 25) / 25));
        } elseif ($ops_per_second >= 10) {
            $base_score = 20 + (20 * (($ops_per_second - 10) / 15));
        } else {
            $base_score = max(10, $ops_per_second * 2);
        }
        
        // Efficiency bonus/penalty
        $efficiency_factor = $efficiency / 100;
        $final_score = $base_score * $efficiency_factor;
        
        // Error penalty
        if ($result['error_count'] > 0) {
            $error_penalty = min(30, $result['error_count'] * 5);
            $final_score = max(0, $final_score - $error_penalty);
        }
        
        return round(max(0, min(100, $final_score)), 2);
    }

    /**
     * Get performance rating based on score
     *
     * @param float $score Score from 0 to 100
     * @return string Rating label
     */
    private static function get_rating($score) {
        if ($score >= 90) {
            return 'excellent';
        } elseif ($score >= 75) {
            return 'good';
        } elseif ($score >= 60) {
            return 'fair';
        } elseif ($score >= 40) {
            return 'poor';
        } else {
            return 'critical';
        }
    }

    /**
     * Get interpretation text for the result
     *
     * @param array $result Test results
     * @return string Interpretation text
     */
    private static function get_interpretation($result) {
        if ($result['status'] !== 'completed') {
            return __('Database concurrency test could not be completed.', 'divewp-boost-site-performance');
        }
        
        $score = $result['score'];
        $ops_per_second = $result['operations_per_second'];
        $efficiency = $result['concurrent_efficiency'];
        
        if ($score >= 90) {
            return sprintf(
                // translators: %1$d is the number of database operations completed, %2$.1f is operations per second performance metric, %3$.1f is the concurrent efficiency percentage
                __('Excellent database concurrency performance! Completed %1$d operations at %2$.1f ops/sec with %3$.1f%% efficiency.', 'divewp-boost-site-performance'),
                $result['operations_completed'],
                $ops_per_second,
                $efficiency
            );
        } elseif ($score >= 75) {
            return sprintf(
                // translators: %1$d is the number of database operations processed, %2$.1f is operations per second performance metric
                __('Good database concurrency handling. Processed %1$d operations at %2$.1f ops/sec. Your database handles concurrent load well.', 'divewp-boost-site-performance'),
                $result['operations_completed'],
                $ops_per_second
            );
        } elseif ($score >= 60) {
            return sprintf(
                // translators: %1$d is the number of database operations completed, %2$.1f is the concurrent efficiency percentage
                __('Fair concurrency performance. Completed %1$d operations but efficiency was %2$.1f%%. Consider database optimization.', 'divewp-boost-site-performance'),
                $result['operations_completed'],
                $efficiency
            );
        } elseif ($score >= 40) {
            return sprintf(
                // translators: %.1f is the low operations per second performance metric indicating poor database concurrency
                __('Poor concurrency handling. Only %.1f ops/sec achieved. Database may struggle under concurrent load.', 'divewp-boost-site-performance'),
                $ops_per_second
            );
        } else {
            return sprintf(
                // translators: %.1f is the very low operations per second metric indicating critical database performance issues
                __('Critical concurrency issues. Very low performance (%.1f ops/sec) indicates serious database limitations.', 'divewp-boost-site-performance'),
                $ops_per_second
            );
        }
    }
} 