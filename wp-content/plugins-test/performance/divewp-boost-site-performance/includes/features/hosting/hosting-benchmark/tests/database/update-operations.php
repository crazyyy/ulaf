<?php
/**
 * Database UPDATE Operations Test
 *
 * Tests database performance for data modification operations.
 * Simulates stock changes and modifications with 10 updates × 5 iterations.
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
 * Database UPDATE Operations Test Class
 * 
 * Performs data modification performance tests using temporary tables
 * with realistic WordPress/WooCommerce update patterns.
 */
class DiveWP_Update_Operations_Test {

    /**
     * Run the UPDATE operations performance test
     *
     * @return array Test results with timing and operations data
     */
    public static function run() {
        global $wpdb;

        // Internal configuration - NO external override allowed
        $config = array(
            'updates_count' => 200,  // INCREASED from 20 to 200 (10x more operations)
            'iterations' => 5,
            'table_prefix' => 'divewp_benchmark_update'
        );

        $start_time = microtime(true);
        $iteration_times = array();
        $total_updates_executed = 0;

        try {
            // Create temporary test table with sample data
            $table_name = self::create_test_table_with_data($config['table_prefix']);
            
            // Run test iterations
            for ($iteration = 1; $iteration <= $config['iterations']; $iteration++) {
                $iteration_start = microtime(true);
                
                // Execute updates for this iteration
                $updates_executed = self::execute_update_operations($table_name, $config['updates_count']);
                $total_updates_executed += $updates_executed;
                
                $iteration_time = microtime(true) - $iteration_start;
                $iteration_times[] = $iteration_time;
                
                // Brief pause between iterations
                usleep(100000); // 0.1 seconds
            }

            // Clean up test table
            self::cleanup_test_table($table_name);

        } catch (Exception $e) {
            // Ensure cleanup even on error
            if (isset($table_name)) {
                self::cleanup_test_table($table_name);
            }
            
            return array(
                'status' => 'error',
                'message' => $e->getMessage(),
                'test_name' => 'UPDATE Operations',
                'total_time' => microtime(true) - $start_time,
                'timestamp' => current_time('mysql')
            );
        }

        $total_time = microtime(true) - $start_time;
        $avg_iteration_time = array_sum($iteration_times) / count($iteration_times);
        $operations_per_second = $total_updates_executed / $total_time;

        // CRITICAL: Calculate score using the scoring system
        require_once __DIR__ . '/scoring.php';
        $score = DiveWP_Benchmark_Database_Scoring::calculate_sub_test_score('update_operations', array(
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
            'status' => 'completed',
            'test_name' => 'UPDATE Operations',
            'total_time' => $total_time,
            'avg_iteration_time' => $avg_iteration_time,
            'iterations' => $config['iterations'],
            'updates_per_iteration' => $config['updates_count'],
            'total_updates_executed' => $total_updates_executed,
            'operations_per_second' => round($operations_per_second, 2),
            'iteration_times' => $iteration_times,
            'fastest_iteration' => min($iteration_times),
            'slowest_iteration' => max($iteration_times),
            'timestamp' => current_time('mysql'),
            // CRITICAL: Add fields that JavaScript expects
            'score' => round($score, 1),
            'rating' => $rating,
            'interpretation' => sprintf(
                // translators: %1$s is the formatted number of database update operations per second (e.g., "1,500", "2,300")
                esc_html__('UPDATE operations completed at %1$s updates/second (with bulk operations)', 'divewp-boost-site-performance'),
                number_format($operations_per_second, 0)
            )
        );

        // ENHANCED UX: Add performance interpretation data using scoring class
        $result['performance_interpretation'] = DiveWP_Benchmark_Database_Scoring::get_sub_test_performance_interpretation('update_operations', $result);

        return $result;
    }

    /**
     * Create temporary test table with sample data for UPDATE operations
     *
     * @param string $table_prefix Table name prefix
     * @return string Full table name
     */
    private static function create_test_table_with_data($table_prefix) {
        global $wpdb;

        $table_name = $wpdb->prefix . $table_prefix . '_' . wp_rand(1000, 9999);
        
        // BEST PRACTICE: Assign dynamic table name to $wpdb property
        $wpdb->update_test_table = $table_name;
        
        // Create table structure optimized for UPDATE operations
        $sql = "CREATE TEMPORARY TABLE `{$wpdb->update_test_table}` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `product_name` varchar(255) NOT NULL,
            `product_sku` varchar(100) NOT NULL,
            `product_price` decimal(10,2) NOT NULL DEFAULT '0.00',
            `product_stock` int(11) NOT NULL DEFAULT '0',
            `product_status` varchar(20) NOT NULL DEFAULT 'active',
            `product_category` varchar(100) NOT NULL,
            `sales_count` int(11) NOT NULL DEFAULT '0',
            `last_updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `version` int(11) NOT NULL DEFAULT '1',
            `is_featured` tinyint(1) NOT NULL DEFAULT '0',
            `rating` decimal(3,2) NOT NULL DEFAULT '0.00',
            `review_count` int(11) NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`),
            KEY `product_sku` (`product_sku`),
            KEY `product_status` (`product_status`),
            KEY `product_category` (`product_category`),
            KEY `last_updated` (`last_updated`),
            KEY `sales_count` (`sales_count`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        // UPDATE OPERATIONS BENCHMARK - Direct query required for temporary table creation during test setup
        // WordPress has no equivalent for CREATE TEMPORARY TABLE; essential for isolated UPDATE testing environment
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.NotPrepared
        $result = $wpdb->query($sql);
        
        if ($result === false) {
            // Clean up $wpdb property on error
            unset($wpdb->update_test_table);
            throw new Exception(esc_html__('Failed to create test table', 'divewp-boost-site-performance'));
        }

        // Populate table with test data (5000 records for intensive updating)
        self::populate_test_data($table_name, 5000); // INCREASED from 500 to 5000 (10x more data)

        // Clean up $wpdb property after successful table creation
        unset($wpdb->update_test_table);
        
        return $table_name;
    }

    /**
     * Populate test table with sample data
     *
     * @param string $table_name Table name
     * @param int    $record_count Number of records to insert
     */
    private static function populate_test_data($table_name, $record_count) {
        global $wpdb;

        $categories = array('Electronics', 'Clothing', 'Books', 'Home & Garden', 'Sports');
        $batch_size = 100;

        for ($batch_start = 0; $batch_start < $record_count; $batch_start += $batch_size) {
            $batch_end = min($batch_start + $batch_size, $record_count);
            $batch_count = $batch_end - $batch_start;
            
            $values = array();
            $placeholders = array();
            
            for ($i = 0; $i < $batch_count; $i++) {
                $record_id = $batch_start + $i + 1;
                $category = $categories[wp_rand(0, count($categories) - 1)];
                
                $values[] = 'Test Product ' . $record_id . ' ' . $category;
                $values[] = 'SKU-' . str_pad($record_id, 8, '0', STR_PAD_LEFT);
                $values[] = wp_rand(10, 500) + (wp_rand(0, 99) / 100);
                $values[] = wp_rand(5, 200);
                $values[] = 'active';
                $values[] = $category;
                $values[] = wp_rand(0, 100);
                $values[] = wp_rand(0, 10) > 7 ? 1 : 0;
                $values[] = wp_rand(100, 500) / 100;
                $values[] = wp_rand(0, 50);
                
                $placeholders[] = '(%s, %s, %f, %d, %s, %s, %d, %d, %f, %d)';
            }
            
            // BEST PRACTICE: Use $wpdb property for dynamic table name
            $wpdb->update_insert_table = $table_name;
            $sql = "INSERT INTO `{$wpdb->update_insert_table}` 
                    (product_name, product_sku, product_price, product_stock, product_status, 
                     product_category, sales_count, is_featured, rating, review_count) 
                    VALUES " . implode(', ', $placeholders);
            
            // UPDATE OPERATIONS BENCHMARK - Direct query required for batch test data insertion during benchmark setup
            // WordPress post functions inappropriate for temporary table test data; SQL uses $wpdb property for table name, not user input
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter
            $wpdb->query($wpdb->prepare($sql, $values));
            
            // Clean up $wpdb property
            unset($wpdb->update_insert_table);
        }
    }

    /**
     * Execute various UPDATE operations for performance testing (ENHANCED WITH BULK OPERATIONS)
     *
     * @param string $table_name Table name
     * @param int    $update_count Number of updates to execute per iteration
     * @return int Number of updates actually executed
     */
    private static function execute_update_operations($table_name, $update_count) {
        global $wpdb;

        $executed_count = 0;
        
        // BEST PRACTICE: Assign dynamic table name to $wpdb property for all queries
        $wpdb->update_operations_table = $table_name;
        
        // REBALANCED: Prioritize complex bulk operations over simple single-row updates
        $update_types = array(
            'bulk_status_update' => 0.4,     // 40% (was 15%) - Heavy bulk category updates
            'complex_price_adjust' => 0.2,   // 20% (NEW) - Complex bulk price adjustments
            'stock_update' => 0.2,           // 20% (was 40%) - Simple single-row updates
            'price_update' => 0.1,           // 10% (was 20%) - Simple price changes
            'sales_increment' => 0.05,       // 5% (was 15%) - Sales count increments
            'rating_update' => 0.05          // 5% (was 10%) - Rating updates
        );

        for ($i = 0; $i < $update_count; $i++) {
            $update_type = self::select_weighted_update_type($update_types);
            
            try {
                switch ($update_type) {
                    case 'stock_update':
                        // Single product stock update (most common)
                        $product_id = wp_rand(1, 5000); // UPDATED range for 5000 records
                        $new_stock = wp_rand(0, 200);
                        // UPDATE OPERATIONS BENCHMARK - Direct query required for stock UPDATE performance measurement
                        // WordPress abstractions would distort single-row UPDATE timing accuracy
                        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                        $result = $wpdb->query($wpdb->prepare(
                            "UPDATE `{$wpdb->update_operations_table}` SET product_stock = %d, version = version + 1 WHERE id = %d",
                            $new_stock,
                            $product_id
                        ));
                        break;
                        
                    case 'price_update':
                        // Price adjustment with percentage change
                        $product_id = wp_rand(1, 5000); // UPDATED range for 5000 records
                        $price_multiplier = wp_rand(80, 120) / 100; // ±20% price change
                        // UPDATE OPERATIONS BENCHMARK - Direct query required for price UPDATE performance measurement
                        // WordPress abstractions would interfere with calculated UPDATE timing accuracy
                        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                        $result = $wpdb->query($wpdb->prepare(
                            "UPDATE `{$wpdb->update_operations_table}` SET product_price = ROUND(product_price * %f, 2), version = version + 1 WHERE id = %d",
                            $price_multiplier,
                            $product_id
                        ));
                        break;
                        
                    case 'bulk_status_update':
                        // ENHANCED: Bulk status update for ENTIRE category (removed LIMIT 5)
                        $categories = array('Electronics', 'Clothing', 'Books', 'Home & Garden', 'Sports');
                        $category = $categories[wp_rand(0, count($categories) - 1)];
                        $new_status = wp_rand(0, 10) > 2 ? 'active' : 'inactive'; // 80% active
                        // UPDATE OPERATIONS BENCHMARK - Direct query required for bulk UPDATE performance measurement
                        // WordPress abstractions would invalidate bulk UPDATE timing and defeat testing purpose
                        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                        $result = $wpdb->query($wpdb->prepare(
                            "UPDATE `{$wpdb->update_operations_table}` SET product_status = %s, version = version + 1 WHERE product_category = %s",
                            $new_status,
                            $category
                        ));
                        break;
                        
                    case 'complex_price_adjust':
                        // NEW: Complex bulk price adjustment with business logic
                        // Applies discount to in-stock products in a category (simulates sale promotion)
                        $categories = array('Electronics', 'Clothing', 'Books', 'Home & Garden', 'Sports');
                        $category = $categories[wp_rand(0, count($categories) - 1)];
                        $discount_percent = wp_rand(5, 30); // 5-30% discount
                        $min_stock = wp_rand(10, 50); // Only discount items with sufficient stock
                        
                        // UPDATE OPERATIONS BENCHMARK - Direct query required for complex bulk UPDATE performance measurement
                        // WordPress abstractions would distort complex conditional UPDATE timing accuracy
                        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                        $result = $wpdb->query($wpdb->prepare(
                            "UPDATE `{$wpdb->update_operations_table}` 
                             SET product_price = ROUND(product_price * (1 - %f), 2), 
                                 version = version + 1,
                                 last_updated = NOW()
                             WHERE product_category = %s 
                             AND product_stock >= %d 
                             AND product_status = 'active'
                             AND product_price > 10.00",
                            $discount_percent / 100,
                            $category,
                            $min_stock
                        ));
                        break;
                        
                    case 'sales_increment':
                        // Increment sales count (simulating purchase)
                        $product_id = wp_rand(1, 5000); // UPDATED range for 5000 records
                        $sales_increment = wp_rand(1, 5);
                        // UPDATE OPERATIONS BENCHMARK - Direct query required for incremental UPDATE performance measurement
                        // WordPress abstractions would interfere with atomic increment UPDATE timing accuracy
                        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                        $result = $wpdb->query($wpdb->prepare(
                            "UPDATE `{$wpdb->update_operations_table}` SET sales_count = sales_count + %d, product_stock = GREATEST(product_stock - %d, 0), version = version + 1 WHERE id = %d",
                            $sales_increment,
                            $sales_increment,
                            $product_id
                        ));
                        break;
                        
                    case 'rating_update':
                        // Update product rating and review count
                        $product_id = wp_rand(1, 5000); // UPDATED range for 5000 records
                        $new_rating = wp_rand(100, 500) / 100; // 1.00 to 5.00
                        $review_increment = wp_rand(1, 3);
                        // UPDATE OPERATIONS BENCHMARK - Direct query required for multi-field UPDATE performance measurement
                        // WordPress abstractions would distort multi-field UPDATE timing accuracy
                        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                        $result = $wpdb->query($wpdb->prepare(
                            "UPDATE `{$wpdb->update_operations_table}` SET rating = %f, review_count = review_count + %d, version = version + 1 WHERE id = %d",
                            $new_rating,
                            $review_increment,
                            $product_id
                        ));
                        break;
                }
                
                if ($result !== false) {
                    $executed_count++;
                }
                
            } catch (Exception $e) {
                // Log update errors but continue testing
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    // BENCHMARK REQUIREMENT - Debug logging for update error tracking
                    error_log('DiveWP Benchmark UPDATE Query Error: ' . $e->getMessage()); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                }
            }
        }
        
        // Clean up $wpdb property
        unset($wpdb->update_operations_table);
        
        return $executed_count;
    }

    /**
     * Select update type based on weighted distribution
     *
     * @param array $weights Weight distribution for update types
     * @return string Selected update type
     */
    private static function select_weighted_update_type($weights) {
        $rand = wp_rand(1, 100) / 100;
        $cumulative = 0;
        
        foreach ($weights as $type => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $type;
            }
        }
        
        return array_keys($weights)[0]; // Fallback
    }

    /**
     * Clean up the temporary test table
     *
     * @param string $table_name Table name to drop
     */
    private static function cleanup_test_table($table_name) {
        global $wpdb;
        
        // BEST PRACTICE: Use $wpdb property for dynamic table name
        $wpdb->cleanup_table = $table_name;
        
        // UPDATE OPERATIONS BENCHMARK - Direct query required for temporary table cleanup after testing
        // WordPress has no equivalent for DROP TEMPORARY TABLE; essential for proper test isolation
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange
        $wpdb->query("DROP TEMPORARY TABLE IF EXISTS `{$wpdb->cleanup_table}`");
        
        // Clean up $wpdb property
        unset($wpdb->cleanup_table);
    }

    /**
     * Get test information for display
     *
     * @return array Test information
     */
    public static function get_test_info() {
        return array(
            'name' => esc_html__('Data Updates (UPDATE)', 'divewp-boost-site-performance'),
            'description' => esc_html__('Intensive bulk updates and modifications (200 updates × 5 iterations)', 'divewp-boost-site-performance'),
            'category' => 'database',
            'type' => 'data_operations',
            'estimated_time' => '10-15 seconds', // UPDATED from '2-5 seconds'
            'operations' => array(
                'total_updates' => 1000, // UPDATED: 200 × 5
                'test_data_records' => 5000, // UPDATED from 500
                'iterations' => 5,
                'update_types' => array(
                    'bulk_status_update' => '40% - Full category status changes',
                    'complex_price_adjust' => '20% - Bulk price adjustments with conditions',
                    'stock_update' => '20% - Single product stock updates',
                    'price_update' => '10% - Individual price changes',
                    'sales_increment' => '5% - Sales count increments',
                    'rating_update' => '5% - Rating and review updates'
                )
            )
        );
    }
} 