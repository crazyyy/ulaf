<?php
/**
 * Database INSERT Operations Test
 *
 * Tests database performance for data creation operations.
 * Simulates adding new products and orders with 500 records × 5 iterations × 5 meta records each.
 * Total operations: 12,500 (500 products + 2,500 meta records per iteration × 5 iterations)
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
 * Database INSERT Operations Test Class
 * 
 * Performs data creation performance tests using temporary tables
 * to avoid affecting the live WordPress database.
 * Enhanced to match PoC intensity with meta data table operations.
 */
class DiveWP_Insert_Operations_Test {

    /**
     * Run the INSERT operations performance test
     *
     * @return array Test results with timing and operations data
     */
    public static function run() {
        global $wpdb;

        // Internal configuration - NO external override allowed
        $config = array(
            'records_count' => 1000,  // DOUBLED from 500 to 1000
            'iterations' => 5,
            'meta_per_product' => 5,  // 5 meta records per product (like PoC)
            'table_prefix' => 'divewp_benchmark_insert'
        );

        $start_time = microtime(true);
        $iteration_times = array();
        $total_records_inserted = 0;
        $total_meta_inserted = 0;

        try {
            // Create temporary test tables (products and meta)
            $tables = self::create_test_tables($config['table_prefix']);
            $products_table = $tables['products'];
            $meta_table = $tables['meta'];
            
            // Run test iterations
            for ($iteration = 1; $iteration <= $config['iterations']; $iteration++) {
                $iteration_start = microtime(true);
                
                // Insert records for this iteration (products + meta)
                $insert_results = self::insert_test_records_with_meta(
                    $products_table, 
                    $meta_table, 
                    $config['records_count'],
                    $config['meta_per_product']
                );
                
                $total_records_inserted += $insert_results['products'];
                $total_meta_inserted += $insert_results['meta'];
                
                $iteration_time = microtime(true) - $iteration_start;
                $iteration_times[] = $iteration_time;
                
                // Brief pause between iterations to prevent overwhelming the database
                usleep(100000); // 0.1 seconds
            }

            // Clean up test tables
            self::cleanup_test_tables($products_table, $meta_table);

        } catch (Exception $e) {
            // Ensure cleanup even on error
            if (isset($products_table) && isset($meta_table)) {
                self::cleanup_test_tables($products_table, $meta_table);
            }
            
            return array(
                'status' => 'error',
                'message' => $e->getMessage(),
                'test_name' => 'INSERT Operations',
                'total_time' => microtime(true) - $start_time,
                'timestamp' => current_time('mysql')
            );
        }

        $total_time = microtime(true) - $start_time;
        $avg_iteration_time = array_sum($iteration_times) / count($iteration_times);
        $total_operations = $total_records_inserted + $total_meta_inserted;
        $operations_per_second = $total_operations / $total_time;

        // CRITICAL: Calculate score using the scoring system
        require_once __DIR__ . '/scoring.php';
        $score = DiveWP_Benchmark_Database_Scoring::calculate_sub_test_score('insert_operations', array(
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
            'test_name' => 'INSERT Operations',
            'total_time' => $total_time,
            'avg_iteration_time' => $avg_iteration_time,
            'iterations' => $config['iterations'],
            'records_per_iteration' => $config['records_count'],
            'meta_per_product' => $config['meta_per_product'],
            'total_records_inserted' => $total_records_inserted,
            'total_meta_inserted' => $total_meta_inserted,
            'total_operations' => $total_operations,
            'operations_per_second' => round($operations_per_second, 2),
            'iteration_times' => $iteration_times,
            'fastest_iteration' => min($iteration_times),
            'slowest_iteration' => max($iteration_times),
            'timestamp' => current_time('mysql'),
            // CRITICAL: Add fields that JavaScript expects
            'score' => round($score, 1),
            'rating' => $rating,
            'interpretation' => sprintf(
                // translators: %1$s is the operations per second rate (e.g., "45", "123"), %2$d is the number of products inserted, %3$d is the number of meta records inserted
                esc_html__('INSERT operations completed at %1$s operations/second (%2$d products + %3$d meta records)', 'divewp-boost-site-performance'),
                number_format($operations_per_second, 0),
                absint($total_records_inserted),
                absint($total_meta_inserted)
            )
        );

        // ENHANCED UX: Add performance interpretation data using scoring class
        $result['performance_interpretation'] = DiveWP_Benchmark_Database_Scoring::get_sub_test_performance_interpretation('insert_operations', $result);

        return $result;
    }

    /**
     * Create temporary test tables for INSERT operations (products and meta)
     *
     * @param string $table_prefix Table name prefix
     * @return array Array with 'products' and 'meta' table names
     */
    private static function create_test_tables($table_prefix) {
        global $wpdb;

        $products_table = $wpdb->prefix . $table_prefix . '_products_' . wp_rand(1000, 9999);
        $meta_table = $wpdb->prefix . $table_prefix . '_meta_' . wp_rand(1000, 9999);
        
        // Create products table structure similar to WordPress posts/products
        $products_sql = "CREATE TEMPORARY TABLE `{$products_table}` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `product_name` varchar(255) NOT NULL,
            `product_sku` varchar(100) NOT NULL,
            `product_price` decimal(10,2) NOT NULL DEFAULT '0.00',
            `product_stock` int(11) NOT NULL DEFAULT '0',
            `product_status` varchar(20) NOT NULL DEFAULT 'active',
            `product_description` text,
            `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `modified_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `product_sku` (`product_sku`),
            KEY `product_status` (`product_status`),
            KEY `created_date` (`created_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        // Create meta table structure similar to WordPress postmeta
        $meta_sql = "CREATE TEMPORARY TABLE `{$meta_table}` (
            `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `product_id` bigint(20) unsigned NOT NULL DEFAULT '0',
            `meta_key` varchar(255) DEFAULT NULL,
            `meta_value` longtext,
            PRIMARY KEY (`meta_id`),
            KEY `product_id` (`product_id`),
            KEY `meta_key` (`meta_key`(191))
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        // INSERT OPERATIONS BENCHMARK - Direct query required for temporary table creation during test setup
        // WordPress has no equivalent for CREATE TEMPORARY TABLE; table name is constructed from $wpdb->prefix + config prefix + random number, not user input
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter
        $products_result = $wpdb->query($products_sql);
        // INSERT OPERATIONS BENCHMARK - Direct query required for temporary meta table creation during test setup
        // WordPress has no equivalent for CREATE TEMPORARY TABLE; table name is constructed from $wpdb->prefix + config prefix + random number, not user input
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter
        $meta_result = $wpdb->query($meta_sql);
        
        if ($products_result === false || $meta_result === false) {
            throw new Exception(esc_html__('Failed to create test tables', 'divewp-boost-site-performance'));
        }

        return array(
            'products' => $products_table,
            'meta' => $meta_table
        );
    }

    /**
     * Insert test records into both products and meta tables (enhanced PoC-style intensity)
     *
     * @param string $products_table Products table name
     * @param string $meta_table Meta table name
     * @param int    $record_count Number of products to insert
     * @param int    $meta_per_product Number of meta records per product
     * @return array Number of records actually inserted
     */
    private static function insert_test_records_with_meta($products_table, $meta_table, $record_count, $meta_per_product) {
        global $wpdb;

        $products_inserted = 0;
        $meta_inserted = 0;
        
        // Insert products one by one (like PoC) to maintain realistic load
        for ($i = 1; $i <= $record_count; $i++) {
            // Insert product record
            $product_data = array(
                'product_name' => 'Test Product ' . $i,
                'product_sku' => 'SKU-' . str_pad($i, 8, '0', STR_PAD_LEFT),
                'product_price' => wp_rand(10, 999) + (wp_rand(0, 99) / 100),
                'product_stock' => wp_rand(0, 100),
                'product_status' => (wp_rand(0, 10) > 1) ? 'active' : 'inactive',
                'product_description' => 'Test product description for item ' . $i . '. ' . 
                                       'This is a longer description to simulate real product data with various lengths and content.'
            );
            
            // INSERT OPERATIONS BENCHMARK - Direct insert required for product data creation during performance testing
            // WordPress post functions inappropriate for temporary table test data; essential for INSERT benchmarking
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $result = $wpdb->insert($products_table, $product_data);
            
            if ($result !== false) {
                $products_inserted++;
                $product_id = $wpdb->insert_id;
                
                // Insert meta records for this product (like PoC: _attribute_ keys)
                for ($j = 1; $j <= $meta_per_product; $j++) {
                    $meta_data = array(
                        'product_id' => $product_id,
                        'meta_key' => '_attribute_' . wp_generate_password(5, false),
                        'meta_value' => wp_generate_password(20, false)
                    );
                    
                    // INSERT OPERATIONS BENCHMARK - Direct insert required for meta data creation during performance testing
                    // WordPress meta functions inappropriate for temporary table test data; essential for INSERT benchmarking
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                    $meta_result = $wpdb->insert($meta_table, $meta_data);
                    
                    if ($meta_result !== false) {
                        $meta_inserted++;
                    }
                }
            }
        }
        
        return array(
            'products' => $products_inserted,
            'meta' => $meta_inserted
        );
    }

    /**
     * Clean up the temporary test tables
     *
     * @param string $products_table Products table name to drop
     * @param string $meta_table Meta table name to drop
     */
    private static function cleanup_test_tables($products_table, $meta_table) {
        global $wpdb;
        
        // INSERT OPERATIONS BENCHMARK - Direct query required for temporary table cleanup after testing
        // WordPress has no equivalent for DROP TEMPORARY TABLE; table name is internally generated, not user input
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter
        $wpdb->query("DROP TEMPORARY TABLE IF EXISTS `{$products_table}`");
        // INSERT OPERATIONS BENCHMARK - Direct query required for temporary meta table cleanup after testing
        // WordPress has no equivalent for DROP TEMPORARY TABLE; table name is internally generated, not user input
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter
        $wpdb->query("DROP TEMPORARY TABLE IF EXISTS `{$meta_table}`");
    }

    /**
     * Get test information for display
     *
     * @return array Test information
     */
    public static function get_test_info() {
        return array(
            'name' => esc_html__('Data Creation (INSERT)', 'divewp-boost-site-performance'),
            'description' => esc_html__('Adding new products and orders (1,000 products + 5,000 meta records × 5 iterations)', 'divewp-boost-site-performance'),
            'category' => 'database',
            'type' => 'data_operations',
            'estimated_time' => '15-45 seconds',
            'operations' => array(
                'total_operations' => 30000, // 1,000 products × 5 meta each × 5 iterations = 5,000 products + 25,000 meta = 30,000 total
                'total_products' => 5000,    // 1,000 × 5 iterations
                'total_meta_records' => 25000, // 5,000 products × 5 meta each
                'batch_size' => 1,           // Individual inserts like PoC
                'iterations' => 5
            )
        );
    }
} 