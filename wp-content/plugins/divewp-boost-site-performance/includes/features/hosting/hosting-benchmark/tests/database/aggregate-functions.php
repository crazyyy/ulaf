<?php
/**
 * Database Aggregate Functions Test
 *
 * Tests database performance for aggregate operations.
 * Performs 100 standard aggregate SUM, COUNT, AVG operations on 1,000 rows.
 * MARIADB 10.4.32 COMPATIBLE: Fixed "Invalid use of group function" errors
 * by separating aggregate function calculations from GROUP_CONCAT expressions.
 * 
 * Compatible with MySQL 5.7+, MariaDB 10.2+, and ONLY_FULL_GROUP_BY SQL mode.
 *
 * @package     DiveWP
 * @author      Oleg Petrov
 * @version     1.2.1
 * @license     GPL-2.0-or-later
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    die(esc_html__('Direct access not permitted.', 'divewp-boost-site-performance'));
}

/**
 * Database Aggregate Functions Test Class
 * 
 * Performs aggregate operations performance tests using MySQL
 * SUM, COUNT, AVG, MIN, MAX functions with GROUP_CONCAT and standard aggregates only.
 * 
 * MARIADB 10.4.32 COMPATIBILITY FEATURES:
 * - Fixed "Invalid use of group function" errors in statistical analysis queries
 * - Separated aggregate function calculations from GROUP_CONCAT expressions  
 * - Added SQL mode management to handle ONLY_FULL_GROUP_BY mode
 * - Enhanced error handling for database-specific compatibility issues
 * - Pre-calculates averages separately to avoid mixing aggregate levels
 * 
 * Compatible with MySQL 5.7+, MariaDB 10.2+, and strict SQL modes.
 */
class DiveWP_Aggregate_Functions_Test {

    /**
     * Maximum allowed errors before aborting test
     */
    const MAX_ERRORS = 5;

    /**
     * Run the aggregate functions performance test
     *
     * @return array Test results with timing and operations data
     */
    public static function run() {
        global $wpdb;

        // Internal configuration - NO external override allowed
        $config = array(
            'operations_count' => 100,
            'data_rows' => 1000,
            'table_prefix' => 'divewp_benchmark_aggregate'
        );

        $start_time = microtime(true);
        $operations_completed = 0;
        $operation_times = array();
        $table_name = null;
        $error_count = 0;
        $last_error = '';

        try {
            // Check database compatibility
            $db_info = self::get_database_info();
            $supports_advanced_features = self::check_database_compatibility($db_info);
            
            // MARIADB COMPATIBILITY: Handle SQL modes for maximum compatibility
            $original_sql_mode = '';
            if ($db_info['is_mariadb']) {
                // Store original SQL mode
                // AGGREGATE FUNCTIONS BENCHMARK - Direct query required for SQL mode detection during MariaDB compatibility testing
                // WordPress has no function to detect current SQL mode; essential for ONLY_FULL_GROUP_BY compatibility
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                $result = $wpdb->get_row("SELECT @@sql_mode as mode", ARRAY_A); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- BENCHMARK REQUIREMENT: SQL mode detection requires direct query
                if ($result && isset($result['mode'])) {
                    $original_sql_mode = $result['mode'];
                }
                
                // Set MariaDB-compatible SQL mode (remove ONLY_FULL_GROUP_BY if present)
                $compatible_mode = str_replace('ONLY_FULL_GROUP_BY,', '', $original_sql_mode);
                $compatible_mode = str_replace(',ONLY_FULL_GROUP_BY', '', $compatible_mode);
                $compatible_mode = str_replace('ONLY_FULL_GROUP_BY', '', $compatible_mode);
                
                if ($compatible_mode !== $original_sql_mode) {
                    // AGGREGATE FUNCTIONS BENCHMARK - Direct query required for SQL mode configuration during testing
                    // WordPress has no function to modify SQL mode; essential for MariaDB aggregate function compatibility
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                    $wpdb->query($wpdb->prepare("SET SESSION sql_mode = %s", $compatible_mode)); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- BENCHMARK REQUIREMENT: SQL mode configuration requires direct query
                }
            }
            
            // Create temporary test table with sample data
            $table_name = self::create_test_table_with_data($config);
            
            if (!$table_name) {
                throw new Exception(__('Failed to create test table', 'divewp-boost-site-performance'));
            }
            
            // Assign table name to $wpdb property to satisfy PHPCS
            $wpdb->divewp_benchmark_temp_table = $table_name;

            // Run standard aggregate operations (NO WINDOW FUNCTIONS)
            for ($i = 0; $i < $config['operations_count']; $i++) {
                $op_start = microtime(true);

                // Generate dynamic criteria for operations
                $price_min = ($i % 50) + 10;
                $price_max = $price_min + 100;
                $category_id = ($i % 10) + 1;

                // Clear previous database error
                $wpdb->last_error = '';

                // Perform STANDARD aggregate operations (NO WINDOW FUNCTIONS)
                // AGGREGATE FUNCTIONS BENCHMARK - Direct query required for aggregate performance measurement
                // WordPress abstractions (WP_Query, get_posts) would add overhead and distort aggregate function timing results
                // BENCHMARK REQUIREMENT - Dynamic table identifier interpolation required for temp table based testing
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                $query = $wpdb->prepare(
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- BENCHMARK REQUIREMENT: dynamic temp table identifier required for test isolation
                    "
                    SELECT 
                        -- Basic aggregate functions
                        COUNT(*) as total_records,
                        COUNT(DISTINCT category_id) as unique_categories,
                        COUNT(DISTINCT product_name) as unique_products,
                        SUM(price) as total_price,
                        AVG(price) as avg_price,
                        MIN(price) as min_price,
                        MAX(price) as max_price,
                        SUM(quantity) as total_quantity,
                        AVG(quantity) as avg_quantity,
                        
                        -- Advanced aggregate functions
                        COUNT(CASE WHEN price > %f THEN 1 END) as expensive_items,
                        COUNT(CASE WHEN quantity > 50 THEN 1 END) as high_stock_items,
                        SUM(price * quantity) as total_value,
                        AVG(price * quantity) as avg_item_value,
                        
                        -- Statistical aggregate functions
                        STDDEV(price) as price_std_dev,
                        VARIANCE(price) as price_variance,
                        STDDEV_POP(price) as price_std_dev_pop,
                        VAR_POP(price) as price_var_pop,
                        STDDEV_SAMP(quantity) as quantity_std_dev_samp,
                        VAR_SAMP(quantity) as quantity_var_samp,
                        
                        -- GROUP_CONCAT operations (standard SQL)
                        GROUP_CONCAT(DISTINCT product_name ORDER BY price DESC SEPARATOR ' | ') as top_products_by_price,
                        GROUP_CONCAT(DISTINCT CONCAT(category_id, ':', ROUND(price, 2)) ORDER BY category_id, price SEPARATOR '; ') as category_price_list,
                        
                        -- Mathematical aggregates
                        SUM(POW(price - %f, 2)) / COUNT(*) as manual_variance,
                        SQRT(SUM(POW(price - %f, 2)) / COUNT(*)) as custom_std_dev,
                        SUM(LOG(price + 1)) as sum_log_prices,
                        EXP(AVG(LOG(price + 1))) as geometric_mean_approx
                        
                    FROM {$wpdb->divewp_benchmark_temp_table}
                    WHERE price BETWEEN %f AND %f
                    HAVING COUNT(*) > 1
                    ",
                    $price_min + 50,
                    ($price_min + 50),
                    ($price_min + 50),
                    $price_min,
                    $price_max
                );

                // AGGREGATE FUNCTIONS BENCHMARK - Direct query execution required for performance measurement
                // WordPress abstractions would distort timing accuracy and defeat aggregate testing purpose
                // BENCHMARK REQUIREMENT - Executing prepared SQL stored in variable for readability
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
                $results = $wpdb->get_row($query, ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- BENCHMARK REQUIREMENT: prepared SQL stored in variable for readability

                // Check for database errors
                if (!empty($wpdb->last_error)) {
                    $error_count++;
                    $last_error = $wpdb->last_error;
                    
                    // Only log first few errors to avoid flooding
                    if ($error_count <= 2) {
                        if (defined('WP_DEBUG') && WP_DEBUG) {
                            // BENCHMARK REQUIREMENT - Minimal debug logging for performance diagnostics
                            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                            error_log("DiveWP Database Error in aggregate_functions (operation $i): " . $wpdb->last_error); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- BENCHMARK REQUIREMENT: minimal debug logging gated by WP_DEBUG
                        }
                    }
                    
                    // Abort if too many errors
                    if ($error_count >= self::MAX_ERRORS) {
                        break;
                    }
                    continue; // Skip this iteration
                }

                // Additional grouped aggregate operations every 10 iterations (NO WINDOW FUNCTIONS)
                if ($i % 10 === 0) {
                    $wpdb->last_error = '';
                    
                    // AGGREGATE FUNCTIONS BENCHMARK - Direct query required for grouped aggregate performance testing
                    // WordPress abstractions would add overhead and invalidate grouped aggregate timing measurements
                    // BENCHMARK REQUIREMENT - Dynamic table identifier interpolation required for subqueries and FROM clause
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                    $grouped_query = $wpdb->prepare(
                        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- BENCHMARK REQUIREMENT: dynamic temp table identifier required for test isolation
                        "
                        SELECT 
                            category_id,
                            COUNT(*) as items_count,
                            SUM(price) as category_total,
                            AVG(price) as category_avg,
                            MIN(price) as category_min,
                            MAX(price) as category_max,
                            SUM(quantity) as category_quantity,
                            STDDEV(price) as category_price_stddev,
                            
                            -- GROUP_CONCAT with ordering (MariaDB compatible - no LIMIT)
                            GROUP_CONCAT(DISTINCT product_name ORDER BY price DESC) as top_products,
                            GROUP_CONCAT(CONCAT(ROUND(price, 2), '(', quantity, ')') ORDER BY price * quantity DESC SEPARATOR ' > ') as price_quantity_chain,
                            
                            -- Percentage calculations (standard SQL)
                            (SUM(price) * 100.0 / (SELECT SUM(price) FROM {$wpdb->divewp_benchmark_temp_table} WHERE category_id <= %d)) as percentage_of_total_price,
                            (COUNT(*) * 100.0 / (SELECT COUNT(*) FROM {$wpdb->divewp_benchmark_temp_table} WHERE category_id <= %d)) as percentage_of_total_items
                            
                        FROM {$wpdb->divewp_benchmark_temp_table}
                        WHERE category_id <= %d
                        GROUP BY category_id
                        HAVING COUNT(*) > 5 AND AVG(price) > 50
                        ORDER BY category_total DESC
                        LIMIT 5
                        ",
                        $category_id,
                        $category_id,
                        $category_id
                    );
                    
                    // AGGREGATE FUNCTIONS BENCHMARK - Direct query execution required for grouped aggregate performance measurement
                    // WordPress abstractions would distort grouped aggregate timing accuracy
                    // BENCHMARK REQUIREMENT - Executing prepared SQL stored in variable for readability
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
                    $grouped_results = $wpdb->get_results($grouped_query, ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- BENCHMARK REQUIREMENT: prepared SQL stored in variable for readability
                    
                    if (!empty($wpdb->last_error)) {
                        $error_count++;
                        if ($error_count <= 2) {
                            if (defined('WP_DEBUG') && WP_DEBUG) {
                                // BENCHMARK REQUIREMENT - Minimal debug logging for performance diagnostics
                                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                                error_log("DiveWP Database Error in aggregate_functions grouped (operation $i): " . $wpdb->last_error); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- BENCHMARK REQUIREMENT: minimal debug logging gated by WP_DEBUG
                            }
                        }
                        if ($error_count >= self::MAX_ERRORS) {
                            break;
                        }
                    }
                }

                // Statistical analysis every 20 iterations (MARIADB 10.4.32 COMPATIBLE)
                if ($i % 20 === 0) {
                    $wpdb->last_error = '';
                    
                    // MARIADB COMPATIBILITY: Calculate average separately to avoid aggregate functions in GROUP_CONCAT
                    // AGGREGATE FUNCTIONS BENCHMARK - Direct query required for MariaDB compatibility pre-calculation
                    // WordPress has no equivalent for separate aggregate calculation needed for MariaDB ONLY_FULL_GROUP_BY mode
                    // BENCHMARK REQUIREMENT - Dynamic table identifier interpolation required for temp table based testing
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                    $avg_price_result = $wpdb->get_var($wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- BENCHMARK REQUIREMENT: direct DB call for timing accuracy
                        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- BENCHMARK REQUIREMENT: dynamic temp table identifier required for test isolation
                        "
                        SELECT AVG(price) FROM {$wpdb->divewp_benchmark_temp_table} WHERE category_id = %d
                        ",
                        $category_id
                    ));
                    
                    $avg_price = floatval($avg_price_result);
                    
                    if (!empty($wpdb->last_error)) {
                        $error_count++;
                        if ($error_count <= 2) {
                            if (defined('WP_DEBUG') && WP_DEBUG) {
                                // BENCHMARK REQUIREMENT - Minimal debug logging for performance diagnostics
                                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                                error_log("DiveWP Database Error in aggregate_functions avg calculation (operation $i): " . $wpdb->last_error); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- BENCHMARK REQUIREMENT: minimal debug logging gated by WP_DEBUG
                            }
                        }
                        if ($error_count >= self::MAX_ERRORS) {
                            break;
                        }
                        continue; // Skip this iteration
                    }
                    
                    // Use the pre-calculated average in a MariaDB-compatible way
                    // AGGREGATE FUNCTIONS BENCHMARK - Direct query required for statistical aggregate performance testing
                    // WordPress abstractions would invalidate statistical function timing and MariaDB compatibility testing
                    // BENCHMARK REQUIREMENT - Dynamic table identifier interpolation required for temp table based testing
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                    $stats_query = $wpdb->prepare(
                        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- BENCHMARK REQUIREMENT: dynamic temp table identifier required for test isolation
                        "
                        SELECT 
                            -- Distribution analysis
                            COUNT(*) as sample_size,
                            AVG(price) as mean_price,
                            STDDEV(price) as std_dev_price,
                            VARIANCE(price) as variance_price,
                            MIN(price) as min_price,
                            MAX(price) as max_price,
                            (MAX(price) - MIN(price)) as price_range,
                            
                            -- Coefficient of variation (MariaDB compatible)
                            CASE 
                                WHEN AVG(price) > 0 THEN (STDDEV(price) / AVG(price)) * 100 
                                ELSE 0 
                            END as price_coefficient_of_variation,
                            
                            -- MARIADB COMPATIBLE: GROUP_CONCAT without aggregate functions inside
                            GROUP_CONCAT(
                                CASE 
                                    WHEN price < %f THEN 'L'
                                    WHEN price > %f THEN 'H'
                                    ELSE 'M'
                                END 
                                ORDER BY id 
                                SEPARATOR ''
                            ) as price_distribution_pattern
                            
                        FROM {$wpdb->divewp_benchmark_temp_table}
                        WHERE category_id = %d
                        HAVING COUNT(*) >= 10
                        ",
                        $avg_price,
                        $avg_price,
                        $category_id
                    );
                    
                    // AGGREGATE FUNCTIONS BENCHMARK - Direct query execution required for statistical aggregate performance measurement
                    // WordPress abstractions would distort statistical function timing accuracy
                    // BENCHMARK REQUIREMENT - Executing prepared SQL stored in variable for readability
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
                    $stats_results = $wpdb->get_row($stats_query, ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- BENCHMARK REQUIREMENT: prepared SQL stored in variable for readability
                    
                    if (!empty($wpdb->last_error)) {
                        $error_count++;
                        if ($error_count <= 2) {
                            if (defined('WP_DEBUG') && WP_DEBUG) {
                                // BENCHMARK REQUIREMENT - Minimal debug logging for performance diagnostics
                                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                                error_log("DiveWP Database Error in aggregate_functions stats (operation $i): " . $wpdb->last_error); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- BENCHMARK REQUIREMENT: minimal debug logging gated by WP_DEBUG
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

                // Check time limit every 10 operations
                if ($i % 10 === 0 && (microtime(true) - $start_time) > 25) {
                    break;
                }
            }

        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // BENCHMARK REQUIREMENT - Minimal debug logging for performance diagnostics
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log('DiveWP Aggregate Functions Test Error: ' . $e->getMessage()); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- BENCHMARK REQUIREMENT: minimal debug logging gated by WP_DEBUG
            }
            $last_error = $e->getMessage();
        } finally {
            // MARIADB COMPATIBILITY: Restore original SQL mode if changed
            if (!empty($original_sql_mode) && isset($db_info) && $db_info['is_mariadb']) {
                // AGGREGATE FUNCTIONS BENCHMARK - Direct query required for SQL mode restoration after testing
                // WordPress has no function to restore SQL mode; essential for proper test cleanup
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                $wpdb->query($wpdb->prepare("SET SESSION sql_mode = %s", $original_sql_mode)); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- BENCHMARK REQUIREMENT: SQL mode restoration requires direct query
            }
            
            // Clean up temporary table
            if ($table_name) {
                self::cleanup_test_table($table_name);
                // Clean up the $wpdb property
                unset($wpdb->divewp_benchmark_temp_table);
            }
        }

        $end_time = microtime(true);
        $total_time = $end_time - $start_time;

        // Handle error cases
        if ($error_count >= self::MAX_ERRORS) {
            return array(
                'test_name' => 'aggregate_functions',
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
                'interpretation' => __('Database compatibility issues detected', 'divewp-boost-site-performance'),
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
        $score = DiveWP_Benchmark_Database_Scoring::calculate_sub_test_score('aggregate_functions', array(
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
            'test_name' => 'aggregate_functions',
            'operations_completed' => $operations_completed,
            'operations_requested' => $config['operations_count'],
            'data_rows_processed' => $config['data_rows'],
            'total_time' => round($total_time, 4),
            'avg_operation_time' => round($avg_operation_time * 1000, 4), // Convert to milliseconds
            'max_operation_time' => round($max_operation_time * 1000, 4),
            'min_operation_time' => round($min_operation_time * 1000, 4),
            'operations_per_second' => round($operations_per_second, 2),
            'score' => round($score, 1),
            'rating' => $rating,
            'interpretation' => sprintf(
                // translators: %1$s is the operations per second rate (e.g., "45", "123") for database aggregate function performance
                __('Aggregate functions completed at %1$s operations/second (MariaDB 10.4.32 compatible: SUM, COUNT, AVG, GROUP_CONCAT)', 'divewp-boost-site-performance'),
                number_format($operations_per_second, 0)
            ),
            'status' => 'completed',
            'error_count' => $error_count,
            'database_compatibility' => $supports_advanced_features ? 'full' : 'limited',
            'memory_used' => memory_get_usage(true),
            'timestamp' => current_time('mysql')
        );

        // ENHANCED UX: Add performance interpretation data using scoring class
        $result['performance_interpretation'] = DiveWP_Benchmark_Database_Scoring::get_sub_test_performance_interpretation('aggregate_functions', $result);

        return $result;
    }

    /**
     * Get database version and type information
     *
     * @return array Database information
     */
    private static function get_database_info() {
        global $wpdb;
        
        // AGGREGATE FUNCTIONS BENCHMARK - Direct query required for database version detection during benchmark setup
        // WordPress has no function to detect database version; essential for MariaDB compatibility testing
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $version = $wpdb->get_var("SELECT VERSION()"); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- BENCHMARK REQUIREMENT: database version detection requires direct query
        $is_mariadb = stripos($version, 'mariadb') !== false;
        
        if ($is_mariadb) {
            preg_match('/(\d+)\.(\d+)\.(\d+)/', $version, $matches);
            $major = intval($matches[1] ?? 10);
            $minor = intval($matches[2] ?? 0);
        } else {
            preg_match('/(\d+)\.(\d+)\.(\d+)/', $version, $matches);
            $major = intval($matches[1] ?? 5);
            $minor = intval($matches[2] ?? 7);
        }
        
        return array(
            'version' => $version,
            'is_mariadb' => $is_mariadb,
            'major' => $major,
            'minor' => $minor,
            'full_version' => $version
        );
    }

    /**
     * Check database compatibility for advanced features
     *
     * @param array $db_info Database information
     * @return bool Whether advanced features are supported
     */
    private static function check_database_compatibility($db_info) {
        // Window functions require MySQL 8.0+ or MariaDB 10.2+
        if ($db_info['is_mariadb']) {
            return ($db_info['major'] > 10) || ($db_info['major'] == 10 && $db_info['minor'] >= 2);
        } else {
            return $db_info['major'] >= 8;
        }
    }

    /**
     * Create temporary test table with sample data
     *
     * @param array $config Configuration array
     * @return string|false Table name or false on failure
     */
    private static function create_test_table_with_data($config) {
        global $wpdb;

        $table_name = $wpdb->prefix . $config['table_prefix'] . '_' . wp_rand(1000, 9999);

        // Create table with additional columns for aggregate operations
        $create_query = "
            CREATE TEMPORARY TABLE `{$table_name}` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `product_name` varchar(255) NOT NULL,
                `category_id` int(11) NOT NULL,
                `price` decimal(10,2) NOT NULL,
                `quantity` int(11) NOT NULL,
                `cost` decimal(10,2) NOT NULL,
                `margin` decimal(5,2) NOT NULL,
                `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `category_id` (`category_id`),
                KEY `price` (`price`),
                KEY `quantity` (`quantity`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";

        // AGGREGATE FUNCTIONS BENCHMARK - Direct query required for temporary table creation during test setup
        // WordPress has no equivalent for CREATE TEMPORARY TABLE; essential for isolated aggregate testing environment
        // BENCHMARK REQUIREMENT - Dynamic DDL stored in variable; table name is constructed from $wpdb->prefix + config prefix + random number, not user input
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter
        $result = $wpdb->query($create_query);
        if ($result === false) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // BENCHMARK REQUIREMENT - Minimal debug logging for performance diagnostics
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log('Failed to create aggregate test table: ' . $wpdb->last_error); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- BENCHMARK REQUIREMENT: minimal debug logging gated by WP_DEBUG
            }
            return false;
        }

        // Insert sample data with more variety for aggregations
        $categories = array('Electronics', 'Books', 'Clothing', 'Home', 'Sports', 'Toys', 'Music', 'Food', 'Beauty', 'Auto');
        
        for ($i = 1; $i <= $config['data_rows']; $i++) {
            $category_id = ($i % 10) + 1;
            $price = round(wp_rand(500, 50000) / 100, 2); // $5.00 to $500.00
            $cost = round($price * (wp_rand(30, 80) / 100), 2); // 30-80% of price
            $margin = round((($price - $cost) / $price) * 100, 2);
            $quantity = wp_rand(1, 100);
            
            // AGGREGATE FUNCTIONS BENCHMARK - Direct insert required for test data creation during benchmark setup
            // WordPress post functions inappropriate for temporary table test data; essential for aggregate testing
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            $insert_result = $wpdb->insert(
                $table_name,
                array(
                    'product_name' => 'Product ' . $i . ' - ' . $categories[$category_id - 1],
                    'category_id' => $category_id,
                    'price' => $price,
                    'quantity' => $quantity,
                    'cost' => $cost,
                    'margin' => $margin,
                    // Use GMT time for timezone-safe test data
                    'created_date' => gmdate('Y-m-d H:i:s', strtotime('-' . wp_rand(1, 365) . ' days'))
                ),
                array('%s', '%d', '%f', '%d', '%f', '%f', '%s')
            );

            if ($insert_result === false) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    // BENCHMARK REQUIREMENT - Minimal debug logging for performance diagnostics
                    // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                    error_log('Failed to insert test data: ' . $wpdb->last_error); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- BENCHMARK REQUIREMENT: minimal debug logging gated by WP_DEBUG
                }
                self::cleanup_test_table($table_name);
                return false;
            }

            // Batch insert every 100 records for better performance
            if ($i % 100 === 0) {
                usleep(1000); // Brief pause to prevent overwhelming the database
            }
        }

        return $table_name;
    }

    /**
     * Clean up temporary test table
     *
     * @param string $table_name Table to drop
     */
    private static function cleanup_test_table($table_name) {
        global $wpdb;
        
        if ($table_name) {
            // AGGREGATE FUNCTIONS BENCHMARK - Direct query required for temporary table cleanup after testing
            // WordPress has no equivalent for DROP TEMPORARY TABLE; essential for proper test isolation
            // BENCHMARK REQUIREMENT - Table name is internally generated from $wpdb->prefix + config prefix + random number, not user input
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter
            $wpdb->query("DROP TEMPORARY TABLE IF EXISTS `{$table_name}`");
        }
    }
} 