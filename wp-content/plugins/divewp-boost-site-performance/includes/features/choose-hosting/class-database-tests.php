<?php
/**
 * Database Tests for DiveWP Hosting Evaluation
 *
 * This class handles all database performance testing functionality,
 * including WooCommerce-like operations, MySQL function performance,
 * and cross-database compatibility testing.
 *
 * @package DiveWP_Boost_Site_Performance
 * @subpackage Choose_Hosting
 * @since 2.0.3
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Database Tests Class
 *
 * Performs comprehensive database performance testing including:
 * - WooCommerce-like product and meta operations
 * - MySQL function performance testing
 * - Cross-database compatibility
 * - Rate limiting and error handling
 *
 * @since 2.0.3
 */
class DiveWP_Database_Tests {

    /**
     * Run INSERT database tests independently
     *
     * @since 2.0.3
     * @return array|WP_Error INSERT test results or error
     */
    public function run_insert_test() {
        global $wpdb;
        
        // Check rate limiting (one test per 30 seconds per user)
        $rate_limit_key = 'divewp_db_insert_test_rate_limit_' . get_current_user_id();
        if (get_transient($rate_limit_key)) {
            return new WP_Error('rate_limit', esc_html__('Please wait before running another INSERT test.', 'divewp-boost-site-performance'));
        }
        set_transient($rate_limit_key, true, 30);
        
        // Use full PHP execution time for this test
        $php_max_execution_time = (int) ini_get('max_execution_time');
        $max_test_time = $php_max_execution_time > 0 ? ($php_max_execution_time * 0.8) : 30;
        
        $test_start = microtime(true);
        $test_iterations = 5;
        $insert_times = array();
        $timed_out = false;
        
        // Test tables
        $products_table = $wpdb->prefix . 'divewp_test_products_insert';
        $meta_table = $wpdb->prefix . 'divewp_test_productmeta_insert';
        
        // Cleanup on shutdown
        add_action('shutdown', function() use ($wpdb, $products_table, $meta_table) {
            $wpdb->query("DROP TABLE IF EXISTS `{$products_table}`");
            $wpdb->query("DROP TABLE IF EXISTS `{$meta_table}`");
        });
        
        try {
            // Create tables
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            
            $products_sql = "CREATE TABLE IF NOT EXISTS {$products_table} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                sku varchar(100) DEFAULT '',
                product_name varchar(200) NOT NULL,
                price decimal(10,2) NOT NULL DEFAULT '0.00',
                stock_quantity int(11) NOT NULL DEFAULT '0',
                status varchar(20) NOT NULL DEFAULT 'publish',
                created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY sku (sku),
                KEY status (status),
                KEY price (price)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            
            dbDelta($products_sql);
            
            $meta_sql = "CREATE TABLE IF NOT EXISTS {$meta_table} (
                meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                product_id bigint(20) unsigned NOT NULL DEFAULT '0',
                meta_key varchar(255) DEFAULT NULL,
                meta_value longtext,
                PRIMARY KEY (meta_id),
                KEY product_id (product_id),
                KEY meta_key (meta_key(191))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            
            dbDelta($meta_sql);
            
            $insert_count = 500;
            $meta_per_product = 5;
            $actual_products_inserted = 0;
            $actual_meta_inserted = 0;
            
            // Run INSERT test iterations
            for ($test_run = 0; $test_run < $test_iterations; $test_run++) {
                $insert_start = microtime(true);
                
                for ($i = 0; $i < $insert_count; $i++) {
                    $product_data = array(
                        'sku' => 'SKU-' . wp_generate_password(8, false),
                        'product_name' => 'Product ' . wp_generate_password(10, false),
                        'price' => mt_rand(1000, 99999) / 100,
                        'stock_quantity' => mt_rand(0, 1000),
                        'status' => mt_rand(0, 10) > 8 ? 'draft' : 'publish'
                    );
                    
                    if ($wpdb->insert($products_table, $product_data)) {
                        $actual_products_inserted++;
                        $product_id = $wpdb->insert_id;
                        
                        for ($j = 0; $j < $meta_per_product; $j++) {
                            $meta_data = array(
                                'product_id' => $product_id,
                                'meta_key' => '_attribute_' . wp_generate_password(5, false),
                                'meta_value' => wp_generate_password(20, false)
                            );
                            if ($wpdb->insert($meta_table, $meta_data)) {
                                $actual_meta_inserted++;
                            }
                        }
                    }
                    
                    // Check for timeout using full PHP limit
                    if ((microtime(true) - $test_start) > $max_test_time) {
                        $timed_out = true;
                        break;
                    }
                }
                
                if (!$timed_out) {
                    $insert_times[] = microtime(true) - $insert_start;
                } else {
                    break;
                }
                
                // Clean up for next iteration
                $wpdb->query("DELETE FROM `{$products_table}`");
                $wpdb->query("DELETE FROM `{$meta_table}`");
            }
            
        } catch (Exception $e) {
            delete_transient($rate_limit_key);
            $wpdb->query("DROP TABLE IF EXISTS `{$products_table}`");
            $wpdb->query("DROP TABLE IF EXISTS `{$meta_table}`");
            return new WP_Error('insert_test_failed', esc_html($e->getMessage()));
        }
        
        // Cleanup
        $wpdb->query("DROP TABLE IF EXISTS `{$products_table}`");
        $wpdb->query("DROP TABLE IF EXISTS `{$meta_table}`");
        
        $insert_time = count($insert_times) > 0 ? array_sum($insert_times) / count($insert_times) : 0;
        
        return array(
            'insert_time' => round($insert_time * 1000, 2),
            'products_tested' => $actual_products_inserted,
            'meta_records' => $actual_meta_inserted,
            'test_iterations' => count($insert_times),
            'timed_out' => $timed_out,
            'test_type' => 'insert'
        );
    }

    /**
     * Run SELECT database tests independently
     *
     * @since 2.0.3  
     * @return array|WP_Error SELECT test results or error
     */
    public function run_select_test() {
        global $wpdb;
        
        $rate_limit_key = 'divewp_db_select_test_rate_limit_' . get_current_user_id();
        if (get_transient($rate_limit_key)) {
            return new WP_Error('rate_limit', esc_html__('Please wait before running another SELECT test.', 'divewp-boost-site-performance'));
        }
        set_transient($rate_limit_key, true, 30);
        
        // Use full PHP execution time
        $php_max_execution_time = (int) ini_get('max_execution_time');
        $max_test_time = $php_max_execution_time > 0 ? ($php_max_execution_time * 0.8) : 30;
        
        $test_start = microtime(true);
        $test_iterations = 5;
        $select_times = array();
        $timed_out = false;
        
        $products_table = $wpdb->prefix . 'divewp_test_products_select';
        $meta_table = $wpdb->prefix . 'divewp_test_productmeta_select';
        
        add_action('shutdown', function() use ($wpdb, $products_table, $meta_table) {
            $wpdb->query("DROP TABLE IF EXISTS `{$products_table}`");
            $wpdb->query("DROP TABLE IF EXISTS `{$meta_table}`");
        });
        
        try {
            // Create and populate tables for SELECT testing
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            
            $products_sql = "CREATE TABLE IF NOT EXISTS {$products_table} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                sku varchar(100) DEFAULT '',
                product_name varchar(200) NOT NULL,
                price decimal(10,2) NOT NULL DEFAULT '0.00',
                stock_quantity int(11) NOT NULL DEFAULT '0',
                status varchar(20) NOT NULL DEFAULT 'publish',
                created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY sku (sku),
                KEY status (status),
                KEY price (price)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            
            dbDelta($products_sql);
            
            $meta_sql = "CREATE TABLE IF NOT EXISTS {$meta_table} (
                meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                product_id bigint(20) unsigned NOT NULL DEFAULT '0',
                meta_key varchar(255) DEFAULT NULL,
                meta_value longtext,
                PRIMARY KEY (meta_id),
                KEY product_id (product_id),
                KEY meta_key (meta_key(191))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            
            dbDelta($meta_sql);
            
            // Populate with test data
            for ($i = 0; $i < 500; $i++) {
                $product_data = array(
                    'sku' => 'SKU-' . wp_generate_password(8, false),
                    'product_name' => 'Product ' . wp_generate_password(10, false),
                    'price' => mt_rand(1000, 99999) / 100,
                    'stock_quantity' => mt_rand(0, 1000),
                    'status' => mt_rand(0, 10) > 8 ? 'draft' : 'publish'
                );
                
                if ($wpdb->insert($products_table, $product_data)) {
                    $product_id = $wpdb->insert_id;
                    for ($j = 0; $j < 5; $j++) {
                        $wpdb->insert($meta_table, array(
                            'product_id' => $product_id,
                            'meta_key' => '_attribute_' . wp_generate_password(5, false),
                            'meta_value' => wp_generate_password(20, false)
                        ));
                    }
                }
            }
            
            // Run SELECT test iterations
            for ($test_run = 0; $test_run < $test_iterations; $test_run++) {
                $select_start = microtime(true);
                
                for ($i = 0; $i < 2500; $i++) {
                    $min_price = mt_rand(10, 50);
                    $max_price = mt_rand(60, 200);
                    
                    $query = $wpdb->prepare(
                        "SELECT p.*, 
                            (SELECT GROUP_CONCAT(CONCAT(meta_key, ':', meta_value) SEPARATOR '|') 
                             FROM {$meta_table} WHERE product_id = p.id) as meta_data
                        FROM {$products_table} p
                        WHERE p.status = %s 
                        AND p.price BETWEEN %f AND %f
                        AND p.stock_quantity > 0
                        ORDER BY p.price ASC
                        LIMIT 20",
                        'publish',
                        $min_price,
                        $max_price
                    );
                    
                    $wpdb->get_results($query);
                    
                    // Check for timeout
                    if ((microtime(true) - $test_start) > $max_test_time) {
                        $timed_out = true;
                        break;
                    }
                }
                
                if (!$timed_out) {
                    $select_times[] = microtime(true) - $select_start;
                } else {
                    break;
                }
            }
            
        } catch (Exception $e) {
            delete_transient($rate_limit_key);
            $wpdb->query("DROP TABLE IF EXISTS `{$products_table}`");
            $wpdb->query("DROP TABLE IF EXISTS `{$meta_table}`");
            return new WP_Error('select_test_failed', esc_html($e->getMessage()));
        }
        
        // Cleanup
        $wpdb->query("DROP TABLE IF EXISTS `{$products_table}`");
        $wpdb->query("DROP TABLE IF EXISTS `{$meta_table}`");
        
        $select_time = count($select_times) > 0 ? array_sum($select_times) / count($select_times) : 0;
        
        return array(
            'select_time' => round($select_time * 1000, 2),
            'queries_run' => 2500,
            'test_iterations' => count($select_times),
            'timed_out' => $timed_out,
            'test_type' => 'select'
        );
    }

    /**
     * Run UPDATE database tests independently
     *
     * @since 2.0.3
     * @return array|WP_Error UPDATE test results or error  
     */
    public function run_update_test() {
        global $wpdb;
        
        $rate_limit_key = 'divewp_db_update_test_rate_limit_' . get_current_user_id();
        if (get_transient($rate_limit_key)) {
            return new WP_Error('rate_limit', esc_html__('Please wait before running another UPDATE test.', 'divewp-boost-site-performance'));
        }
        set_transient($rate_limit_key, true, 30);
        
        // Use full PHP execution time
        $php_max_execution_time = (int) ini_get('max_execution_time');
        $max_test_time = $php_max_execution_time > 0 ? ($php_max_execution_time * 0.8) : 30;
        
        $test_start = microtime(true);
        $test_iterations = 5;
        $update_times = array();
        $timed_out = false;
        
        $products_table = $wpdb->prefix . 'divewp_test_products_update';
        
        add_action('shutdown', function() use ($wpdb, $products_table) {
            $wpdb->query("DROP TABLE IF EXISTS `{$products_table}`");
        });
        
        try {
            // Create and populate table for UPDATE testing
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            
            $products_sql = "CREATE TABLE IF NOT EXISTS {$products_table} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                sku varchar(100) DEFAULT '',
                product_name varchar(200) NOT NULL,
                price decimal(10,2) NOT NULL DEFAULT '0.00',
                stock_quantity int(11) NOT NULL DEFAULT '0',
                status varchar(20) NOT NULL DEFAULT 'publish',
                created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY sku (sku),
                KEY status (status),
                KEY price (price)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            
            dbDelta($products_sql);
            
            // Populate with test data
            for ($i = 0; $i < 500; $i++) {
                $wpdb->insert($products_table, array(
                    'sku' => 'SKU-' . wp_generate_password(8, false),
                    'product_name' => 'Product ' . wp_generate_password(10, false),
                    'price' => mt_rand(1000, 99999) / 100,
                    'stock_quantity' => mt_rand(10, 1000),
                    'status' => 'publish'
                ));
            }
            
            // Run UPDATE test iterations
            for ($test_run = 0; $test_run < $test_iterations; $test_run++) {
                $update_start = microtime(true);
                
                for ($i = 0; $i < 10; $i++) {
                    $product_id = mt_rand(1, 500);
                    $quantity = mt_rand(1, 5);
                    
                    $wpdb->query($wpdb->prepare(
                        "UPDATE {$products_table} 
                        SET stock_quantity = stock_quantity - %d 
                        WHERE id = %d AND stock_quantity >= %d",
                        $quantity,
                        $product_id,
                        $quantity
                    ));
                    
                    // Check for timeout
                    if ((microtime(true) - $test_start) > $max_test_time) {
                        $timed_out = true;
                        break;
                    }
                }
                
                if (!$timed_out) {
                    $update_times[] = microtime(true) - $update_start;
                } else {
                    break;
                }
            }
            
        } catch (Exception $e) {
            delete_transient($rate_limit_key);
            $wpdb->query("DROP TABLE IF EXISTS `{$products_table}`");
            return new WP_Error('update_test_failed', esc_html($e->getMessage()));
        }
        
        // Cleanup
        $wpdb->query("DROP TABLE IF EXISTS `{$products_table}`");
        
        $update_time = count($update_times) > 0 ? array_sum($update_times) / count($update_times) : 0;
        
        return array(
            'update_time' => round($update_time * 1000, 2),
            'updates_run' => 10,
            'test_iterations' => count($update_times),
            'timed_out' => $timed_out,
            'test_type' => 'update'
        );
    }

    /**
     * Run comprehensive database tests
     *
     * Tests realistic WooCommerce operations including product inserts,
     * complex SELECT queries with JOINs, and UPDATE operations.
     * Runs multiple iterations for statistical accuracy.
     *
     * @since 2.0.3
     * @return array|WP_Error Database test results or error
     */
    public function run_database_tests() {
        global $wpdb;
        
        // Check rate limiting (one test per 30 seconds per user)
        $rate_limit_key = 'divewp_db_test_rate_limit_' . get_current_user_id();
        if (get_transient($rate_limit_key)) {
            return new WP_Error('rate_limit', esc_html__('Please wait before running another test.', 'divewp-boost-site-performance'));
        }
        set_transient($rate_limit_key, true, 30); // 30 seconds rate limit
        
        $results = array();
        $test_start = microtime(true);
        $test_iterations = 5; // Reduced from 15 for better performance/timeout balance
        $insert_times = array();
        $select_times = array();
        $update_times = array();
        
        // Timeout and scoring system
        $php_max_execution_time = (int) ini_get('max_execution_time');
        $max_test_time = $php_max_execution_time > 0 ? ($php_max_execution_time * 0.8) : 30;
        $test_timeout_limit = $max_test_time / 8; // Divide time among 8 total tests (3 main + 5 function tests)
        
        // Score weights for timeout deductions
        $score_weights = array(
            'insert' => 25,    // Critical for adding products
            'select' => 30,    // Most important for product listings  
            'update' => 20,    // Important for stock updates
            'crypto' => 5,     // MySQL function tests
            'math' => 5,
            'string' => 5,
            'datetime' => 5,
            'aggregate' => 5
        );
        
        $timed_out_tests = array();
        $total_score_deduction = 0;
        
        // Use WordPress native execution time (no manual limit setting)
        
        // Test tables for WooCommerce-like structure
        $products_table = $wpdb->prefix . 'divewp_test_products';
        $meta_table = $wpdb->prefix . 'divewp_test_productmeta';
        
        // Ensure cleanup even on failure
        add_action('shutdown', function() use ($wpdb, $products_table, $meta_table) {
            // Direct queries for DROP TABLE - prepare() doesn't work well with table names
            $wpdb->query("DROP TABLE IF EXISTS `{$products_table}`");
            $wpdb->query("DROP TABLE IF EXISTS `{$meta_table}`");
        });
        
        try {
            // Create WooCommerce-like product table structure
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            
            $products_sql = "CREATE TABLE IF NOT EXISTS {$products_table} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                sku varchar(100) DEFAULT '',
                product_name varchar(200) NOT NULL,
                price decimal(10,2) NOT NULL DEFAULT '0.00',
                stock_quantity int(11) NOT NULL DEFAULT '0',
                status varchar(20) NOT NULL DEFAULT 'publish',
                created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY sku (sku),
                KEY status (status),
                KEY price (price)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            
            dbDelta($products_sql);
            
            $meta_sql = "CREATE TABLE IF NOT EXISTS {$meta_table} (
                meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                product_id bigint(20) unsigned NOT NULL DEFAULT '0',
                meta_key varchar(255) DEFAULT NULL,
                meta_value longtext,
                PRIMARY KEY (meta_id),
                KEY product_id (product_id),
                KEY meta_key (meta_key(191))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            
            dbDelta($meta_sql);
            
            // Use optimized configuration for all hosting types
            $test_config = array(
                'environment' => 'optimized',
                'db_insert_count' => 500  // Proven VPS value that works well
            );
            
            // Run database tests multiple times for averaging
            for ($test_run = 0; $test_run < $test_iterations; $test_run++) {
                // INSERT TEST - with individual timeout protection
                $insert_test_start = microtime(true);
                $insert_timed_out = false;
                
                try {
                    // Insert products with meta data (WooCommerce simulation)
                    // Count adjusted based on hosting environment
                    $insert_start = microtime(true);
                    $insert_count = $test_config['db_insert_count'];
                    $meta_per_product = 5;
                    $actual_products_inserted = 0;
                    $actual_meta_inserted = 0;
                    
                    for ($i = 0; $i < $insert_count; $i++) {
                        $product_data = array(
                            'sku' => 'SKU-' . wp_generate_password(8, false),
                            'product_name' => 'Product ' . wp_generate_password(10, false),
                            'price' => mt_rand(1000, 99999) / 100,
                            'stock_quantity' => mt_rand(0, 1000),
                            'status' => mt_rand(0, 10) > 8 ? 'draft' : 'publish'
                        );
                        
                        if ($wpdb->insert($products_table, $product_data)) {
                            $actual_products_inserted++;
                            $product_id = $wpdb->insert_id;
                            
                            // Add meta data (like WooCommerce attributes)
                            for ($j = 0; $j < $meta_per_product; $j++) {
                                $meta_data = array(
                                    'product_id' => $product_id,
                                    'meta_key' => '_attribute_' . wp_generate_password(5, false),
                                    'meta_value' => wp_generate_password(20, false)
                                );
                                if ($wpdb->insert($meta_table, $meta_data)) {
                                    $actual_meta_inserted++;
                                }
                            }
                        }
                        
                        // Check for timeout on this specific test
                        if ((microtime(true) - $insert_test_start) > $test_timeout_limit) {
                            $insert_timed_out = true;
                            break;
                        }
                    }
                    
                    if (!$insert_timed_out) {
                        $insert_times[] = microtime(true) - $insert_start;
                    }
                    
                } catch (Exception $e) {
                    $insert_timed_out = true;
                }
                
                if ($insert_timed_out) {
                    $timed_out_tests['insert'] = true;
                    $total_score_deduction += $score_weights['insert'];
                    $insert_times[] = 0; // Add placeholder time
                    break; // Skip remaining iterations for this test
                }
            
                // SELECT TEST - with individual timeout protection
                $select_test_start = microtime(true);
                $select_timed_out = false;
                
                try {
                    // Complex SELECT queries (product listings with filters)
                    $select_start = microtime(true);
                    
                    // Simulate 2500 product listing queries with JOINs (reduced for better performance)
                    for ($i = 0; $i < 2500; $i++) {
                        $min_price = mt_rand(10, 50);
                        $max_price = mt_rand(60, 200);
                        
                        $query = $wpdb->prepare(
                            "SELECT p.*, 
                                (SELECT GROUP_CONCAT(CONCAT(meta_key, ':', meta_value) SEPARATOR '|') 
                                 FROM {$meta_table} WHERE product_id = p.id) as meta_data
                            FROM {$products_table} p
                            WHERE p.status = %s 
                            AND p.price BETWEEN %f AND %f
                            AND p.stock_quantity > 0
                            ORDER BY p.price ASC
                            LIMIT 20",
                            'publish',
                            $min_price,
                            $max_price
                        );
                        
                        $wpdb->get_results($query);
                        
                        // Check for timeout on this specific test
                        if ((microtime(true) - $select_test_start) > $test_timeout_limit) {
                            $select_timed_out = true;
                            break;
                        }
                    }
                    
                    if (!$select_timed_out) {
                        $select_times[] = microtime(true) - $select_start;
                    }
                    
                } catch (Exception $e) {
                    $select_timed_out = true;
                }
                
                if ($select_timed_out) {
                    $timed_out_tests['select'] = true;
                    $total_score_deduction += $score_weights['select'];
                    $select_times[] = 0; // Add placeholder time
                    break; // Skip remaining iterations for this test
                }
            
                // UPDATE TEST - with individual timeout protection
                $update_test_start = microtime(true);
                $update_timed_out = false;
                
                try {
                    // UPDATE queries (stock updates during checkout)
                    $update_start = microtime(true);
                    
                    for ($i = 0; $i < 10; $i++) {
                        // Simulate stock reduction during checkout (reduced for better performance)
                        $product_id = mt_rand(1, $insert_count);
                        $quantity = mt_rand(1, 5);
                        
                        $wpdb->query($wpdb->prepare(
                            "UPDATE {$products_table} 
                            SET stock_quantity = stock_quantity - %d 
                            WHERE id = %d AND stock_quantity >= %d",
                            $quantity,
                            $product_id,
                            $quantity
                        ));
                        
                        // Check for timeout on this specific test
                        if ((microtime(true) - $update_test_start) > $test_timeout_limit) {
                            $update_timed_out = true;
                            break;
                        }
                    }
                    
                    if (!$update_timed_out) {
                        $update_times[] = microtime(true) - $update_start;
                    }
                    
                } catch (Exception $e) {
                    $update_timed_out = true;
                }
                
                if ($update_timed_out) {
                    $timed_out_tests['update'] = true;
                    $total_score_deduction += $score_weights['update'];
                    $update_times[] = 0; // Add placeholder time
                    break; // Skip remaining iterations for this test
                }
            
            // Clean up test data for this iteration
            $wpdb->query("DELETE FROM `{$products_table}`");
            $wpdb->query("DELETE FROM `{$meta_table}`");
            }
            
            // Calculate averages (handle timed out tests)
            $insert_time = count($insert_times) > 0 ? array_sum($insert_times) / count($insert_times) : 0;
            $select_time = count($select_times) > 0 ? array_sum($select_times) / count($select_times) : 0;
            $update_time = count($update_times) > 0 ? array_sum($update_times) / count($update_times) : 0;
            
        } catch (Exception $e) {
            // Clean up rate limit transient on critical errors to allow immediate retry
            delete_transient($rate_limit_key);
            
            // Clean up database resources on error
            $wpdb->query("DROP TABLE IF EXISTS `{$products_table}`");
            $wpdb->query("DROP TABLE IF EXISTS `{$meta_table}`");
            
            return new WP_Error('db_test_failed', esc_html($e->getMessage()));
        }
        
        // Clean up
        $wpdb->query("DROP TABLE IF EXISTS `{$products_table}`");
        $wpdb->query("DROP TABLE IF EXISTS `{$meta_table}`");
        
        // Calculate score based on a continuous scale
        $total_time = $insert_time + $select_time + $update_time;
        $db_score = $this->calculate_database_score($total_time);
        
        // Deduct points for timed out tests
        $db_score = max(0, $db_score - $total_score_deduction);

        // Determine rating based on score
        if ($db_score >= 90) {
            $rating = 'excellent';
        } elseif ($db_score >= 70) {
            $rating = 'good';
        } elseif ($db_score >= 50) {
            $rating = 'fair';
        } elseif ($db_score >= 30) {
            $rating = 'poor';
        } else {
            $rating = 'critical';
        }
        
        return array(
            'insert_time' => round($insert_time * 1000, 2),
            'select_time' => round($select_time * 1000, 2),
            'update_time' => round($update_time * 1000, 2),
            'total_time' => round($total_time * 1000, 2),
            'products_tested' => $actual_products_inserted,
            'meta_records' => $actual_meta_inserted,
            'queries_run' => 2500,
            'test_iterations' => $test_iterations,
            'score' => $db_score,
            'rating' => $rating,
            'interpretation' => $this->get_db_interpretation($total_time),
            'timed_out_tests' => $timed_out_tests,
            'score_deduction' => $total_score_deduction
        );
    }

    /**
     * Calculate a continuous score based on total test time.
     *
     * @since 2.0.6
     * @param float $total_time The total time for the test in seconds.
     * @return int The calculated score from 0-100.
     */
    private function calculate_database_score($total_time) {
        if ($total_time <= 2.0) {
            // Excellent range: 100-90
            return round(100 - ($total_time * 5));
        } elseif ($total_time <= 5.0) {
            // Good range: 89-70
            return round(90 - (($total_time - 2) * (20 / 3)));
        } elseif ($total_time <= 10.0) {
            // Fair range: 69-50
            return round(70 - (($total_time - 5) * (20 / 5)));
        } elseif ($total_time <= 20.0) {
            // Poor range: 49-30
            return round(50 - (($total_time - 10) * (20 / 10)));
        } else {
            // Critical range: below 30, capped at a minimum of 10.
            return max(10, round(30 - ($total_time - 20)));
        }
    }

    /**
     * Get database type and compatibility information
     * 
     * @since 2.0.3
     * @return array Database type, version, and compatibility flags
     */
    public function get_database_info() {
        global $wpdb;
        
        static $db_info = null;
        if ($db_info !== null) {
            return $db_info;
        }
        
        try {
            // Get database version and server info
            $server_info = $wpdb->db_server_info();
            $version = $wpdb->db_version();
            
            // Detect database type
            $is_mariadb = stripos($server_info, 'mariadb') !== false;
            $is_mysql = !$is_mariadb && stripos($server_info, 'mysql') !== false;
            
            // For other potential databases
            $is_postgresql = stripos($server_info, 'postgresql') !== false || stripos($server_info, 'postgres') !== false;
            $is_sqlite = stripos($server_info, 'sqlite') !== false;
            
            // Determine compatibility
            $supports_aes_functions = $is_mysql || $is_mariadb;
            $supports_group_concat = $is_mysql || $is_mariadb;
            $supports_mysql_math_functions = $is_mysql || $is_mariadb;
            $supports_mysql_date_functions = $is_mysql || $is_mariadb;
            
            $db_info = array(
                'type' => $is_mariadb ? 'MariaDB' : ($is_mysql ? 'MySQL' : ($is_postgresql ? 'PostgreSQL' : ($is_sqlite ? 'SQLite' : 'Unknown'))),
                'version' => $version,
                'server_info' => $server_info,
                'is_mysql_compatible' => $is_mysql || $is_mariadb,
                'supports_aes_functions' => $supports_aes_functions,
                'supports_group_concat' => $supports_group_concat,
                'supports_mysql_math_functions' => $supports_mysql_math_functions,
                'supports_mysql_date_functions' => $supports_mysql_date_functions,
            );
            
        } catch (Exception $e) {
            // Fallback for unknown database types
            $db_info = array(
                'type' => 'Unknown',
                'version' => 'Unknown',
                'server_info' => 'Unknown',
                'is_mysql_compatible' => false,
                'supports_aes_functions' => false,
                'supports_group_concat' => false,
                'supports_mysql_math_functions' => false,
                'supports_mysql_date_functions' => false,
            );
        }
        
        return $db_info;
    }

    /**
     * Test database function performance with cross-database compatibility
     * 
     * Tests database functions commonly used in WordPress applications.
     * Automatically adapts to different database types (MySQL, MariaDB, PostgreSQL, etc.)
     * for maximum hosting provider compatibility.
     * 
     * @since 2.0.3
     * @return array Database function performance results
     */
    public function test_mysql_function_performance() {
        global $wpdb;
        
        $test_start = microtime(true);
        $test_timings = array();
        $function_scores = array();
        
        // Use WordPress native execution time for timeout calculations
        $php_max_execution_time = (int) ini_get('max_execution_time');
        $max_test_time = $php_max_execution_time > 0 ? intval($php_max_execution_time * 0.8) : 20;
        $function_timeout_limit = $max_test_time / 5; // Divide time among 5 function tests
        
        // Score weights for function test timeouts
        $function_score_weights = array(
            'crypto' => 5,
            'math' => 5,
            'string' => 5,
            'datetime' => 5,
            'aggregate' => 5
        );
        
        $timed_out_functions = array();
        $total_function_score_deduction = 0;
        
        // Get database compatibility information
        $db_info = $this->get_database_info();
        
        // Test 1: Cryptographic/Hash Functions (Database-Adaptive) - with timeout protection
        $crypto_time = 0;
        $crypto_timed_out = false;
        $crypto_test_start = microtime(true);
        
        try {
            $crypto_start = microtime(true);
            $crypto_iterations = 1000;  // Use optimized value for all hosting types
            
            if ($db_info['supports_aes_functions']) {
                // Use AES encryption for MySQL/MariaDB
                $encryption_key = wp_generate_password(32, false);
                
                for ($i = 0; $i < $crypto_iterations; $i++) {
                    $test_data = 'Test encryption data ' . wp_generate_password(50, false) . ' ' . $i;
                    
                    // Test AES encryption and decryption
                    $encrypted_query = $wpdb->prepare(
                        "SELECT AES_ENCRYPT(%s, %s) as encrypted_data",
                        $test_data,
                        $encryption_key
                    );
                    $encrypted_result = $wpdb->get_var($encrypted_query);
                    
                    if ($encrypted_result) {
                        $decrypted_query = $wpdb->prepare(
                            "SELECT AES_DECRYPT(%s, %s) as decrypted_data",
                            $encrypted_result,
                            $encryption_key
                        );
                        $decrypted_result = $wpdb->get_var($decrypted_query);
                    }
                    
                    // Check for timeout on this specific test
                    if ((microtime(true) - $crypto_test_start) > $function_timeout_limit) {
                        $crypto_timed_out = true;
                        break;
                    }
                }
            } else {
                // Fallback to hash functions for other databases
                for ($i = 0; $i < $crypto_iterations; $i++) {
                    $test_data = 'Test hash data ' . wp_generate_password(50, false) . ' ' . $i;
                    
                    // Use MD5 and similar hash functions that are more universally supported
                    $hash_query = $wpdb->prepare(
                        "SELECT MD5(%s) as md5_hash",
                        $test_data
                    );
                    $wpdb->get_var($hash_query);
                    
                    // Check for timeout on this specific test
                    if ((microtime(true) - $crypto_test_start) > $function_timeout_limit) {
                        $crypto_timed_out = true;
                        break;
                    }
                }
            }
            
            if (!$crypto_timed_out) {
                $crypto_time = microtime(true) - $crypto_start;
            }
            
        } catch (Exception $e) {
            error_log('DiveWP: Database crypto/hash functions test failed (' . $db_info['type'] . '): ' . $e->getMessage());
            $crypto_timed_out = true;
        }
        
        if ($crypto_timed_out) {
            $timed_out_functions['crypto'] = true;
            $total_function_score_deduction += $function_score_weights['crypto'];
            $crypto_time = 0;
        }
        
        $test_timings['crypto_functions'] = round($crypto_time, 3);
        
        // Test 2: Mathematical Functions (Database-Adaptive) - with timeout protection
        $math_time = 0;
        $math_timed_out = false;
        $math_test_start = microtime(true);
        
        try {
            $math_start = microtime(true);
            $math_iterations = 5000;  // Use optimized value for all hosting types
            
            if ($db_info['supports_mysql_math_functions']) {
                // Use full range of mathematical functions for MySQL/MariaDB
                for ($i = 1; $i <= $math_iterations; $i++) {
                    $math_query = $wpdb->prepare(
                        "SELECT 
                            SIN(%f) + COS(%f) as trig_result,
                            SQRT(%d) as sqrt_result,
                            LOG(%d) as log_result,
                            POW(%d, 2) as power_result,
                            ROUND(RAND() * 1000, 2) as random_result",
                        $i * 0.1,
                        $i * 0.2,
                        $i,
                        max(1, $i),
                        $i % 10
                    );
                    $wpdb->get_row($math_query);
                    
                    // Check for timeout on this specific test
                    if ((microtime(true) - $math_test_start) > $function_timeout_limit) {
                        $math_timed_out = true;
                        break;
                    }
                }
            } else {
                // Use basic mathematical operations for other databases
                for ($i = 1; $i <= $math_iterations; $i++) {
                    $math_query = $wpdb->prepare(
                        "SELECT 
                            (%d + %d) as addition_result,
                            (%d * %d) as multiplication_result,
                            (%d / %d) as division_result,
                            ABS(%d) as absolute_result",
                        $i,
                        $i + 1,
                        $i,
                        2,
                        $i * 10,
                        max(1, $i % 5),
                        $i - 100
                    );
                    $wpdb->get_row($math_query);
                    
                    // Check for timeout on this specific test
                    if ((microtime(true) - $math_test_start) > $function_timeout_limit) {
                        $math_timed_out = true;
                        break;
                    }
                }
            }
            
            if (!$math_timed_out) {
                $math_time = microtime(true) - $math_start;
            }
            
        } catch (Exception $e) {
            error_log('DiveWP: Database math functions test failed (' . $db_info['type'] . '): ' . $e->getMessage());
            $math_timed_out = true;
        }
        
        if ($math_timed_out) {
            $timed_out_functions['math'] = true;
            $total_function_score_deduction += $function_score_weights['math'];
            $math_time = 0;
        }
        
        $test_timings['math_functions'] = round($math_time, 3);
        
        // Test 3: String Functions (Common in WordPress) - with timeout protection
        $string_time = 0;
        $string_timed_out = false;
        $string_test_start = microtime(true);
        
        try {
            $string_start = microtime(true);
            $string_iterations = 3000;  // Use optimized value for all hosting types
            
            for ($i = 0; $i < $string_iterations; $i++) {
                $test_string = 'WordPress Test String for MySQL Functions ' . wp_generate_password(20, false);
                
                // String manipulation functions commonly used in WordPress
                $string_query = $wpdb->prepare(
                    "SELECT 
                        CONCAT(%s, ' - ', %d) as concat_result,
                        SUBSTRING(%s, 1, 20) as substring_result,
                        UPPER(%s) as upper_result,
                        LOWER(%s) as lower_result,
                        LENGTH(%s) as length_result,
                        MD5(%s) as hash_result",
                    $test_string,
                    $i,
                    $test_string,
                    $test_string,
                    $test_string,
                    $test_string,
                    $test_string
                );
                $wpdb->get_row($string_query);
                
                // Check for timeout on this specific test
                if ((microtime(true) - $string_test_start) > $function_timeout_limit) {
                    $string_timed_out = true;
                    break;
                }
            }
            
            if (!$string_timed_out) {
                $string_time = microtime(true) - $string_start;
            }
            
        } catch (Exception $e) {
            error_log('DiveWP: MySQL string functions test failed: ' . $e->getMessage());
            $string_timed_out = true;
        }
        
        if ($string_timed_out) {
            $timed_out_functions['string'] = true;
            $total_function_score_deduction += $function_score_weights['string'];
            $string_time = 0;
        }
        
        $test_timings['string_functions'] = round($string_time, 3);
        
        // Test 4: Date/Time Functions (Database-Adaptive) - with timeout protection
        $datetime_time = 0;
        $datetime_timed_out = false;
        $datetime_test_start = microtime(true);
        
        try {
            $datetime_start = microtime(true);
            $datetime_iterations = 5000;  // Use optimized value for all hosting types
            
            if ($db_info['supports_mysql_date_functions']) {
                // Use full MySQL/MariaDB date functions
                for ($i = 0; $i < $datetime_iterations; $i++) {
                    $datetime_query = "SELECT 
                        NOW() as current_datetime,
                        DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s') as formatted_date,
                        UNIX_TIMESTAMP() as unix_timestamp,
                        FROM_UNIXTIME(UNIX_TIMESTAMP()) as from_unix,
                        DATE_ADD(NOW(), INTERVAL 1 DAY) as future_date,
                        DATEDIFF(NOW(), DATE_SUB(NOW(), INTERVAL 30 DAY)) as date_diff,
                        DAYOFWEEK(NOW()) as day_of_week";
                    
                    $wpdb->get_row($datetime_query);
                    
                    // Check for timeout on this specific test
                    if ((microtime(true) - $datetime_test_start) > $function_timeout_limit) {
                        $datetime_timed_out = true;
                        break;
                    }
                }
            } else {
                // Use basic date/time functions for other databases
                for ($i = 0; $i < $datetime_iterations; $i++) {
                    // Use simpler date operations that are more universally supported
                    $datetime_query = "SELECT 
                        CURRENT_TIMESTAMP as current_datetime";
                    
                    $wpdb->get_row($datetime_query);
                    
                    // Check for timeout on this specific test
                    if ((microtime(true) - $datetime_test_start) > $function_timeout_limit) {
                        $datetime_timed_out = true;
                        break;
                    }
                }
            }
            
            if (!$datetime_timed_out) {
                $datetime_time = microtime(true) - $datetime_start;
            }
            
        } catch (Exception $e) {
            error_log('DiveWP: Database datetime functions test failed (' . $db_info['type'] . '): ' . $e->getMessage());
            $datetime_timed_out = true;
        }
        
        if ($datetime_timed_out) {
            $timed_out_functions['datetime'] = true;
            $total_function_score_deduction += $function_score_weights['datetime'];
            $datetime_time = 0;
        }
        
        $test_timings['datetime_functions'] = round($datetime_time, 3);
        
        // Test 5: Aggregate Functions (Database-Adaptive) - with timeout protection
        $aggregate_time = 0;
        $aggregate_timed_out = false;
        $aggregate_test_start = microtime(true);
        
        try {
            $aggregate_start = microtime(true);
            
            // Create temporary test data for aggregate functions
            $temp_table = $wpdb->prefix . 'divewp_db_test_data';
            
            // Create temporary table for aggregate testing with database-adaptive syntax
            if ($db_info['is_mysql_compatible']) {
                $create_query = "CREATE TEMPORARY TABLE {$temp_table} (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    category VARCHAR(50),
                    value DECIMAL(10,2),
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )";
            } else {
                // More generic table creation for other databases
                $create_query = "CREATE TEMPORARY TABLE {$temp_table} (
                    id INTEGER PRIMARY KEY,
                    category VARCHAR(50),
                    value DECIMAL(10,2),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
            }
            $wpdb->query($create_query);
            
            // Insert test data for aggregates
            $aggregate_rows = 1000;  // Use optimized value for all hosting types
            for ($i = 1; $i <= $aggregate_rows; $i++) {
                $category = 'Category_' . ($i % 10);
                $value = mt_rand(100, 10000) / 100;
                
                $wpdb->insert($temp_table, array(
                    'category' => $category,
                    'value' => $value
                ));
                
                // Check for timeout during data insertion
                if ((microtime(true) - $aggregate_test_start) > $function_timeout_limit) {
                    $aggregate_timed_out = true;
                    break;
                }
            }
            
            if (!$aggregate_timed_out) {
                // Run aggregate function tests with database-specific queries
                $aggregate_iterations = 100;  // Use optimized value for all hosting types
                for ($i = 0; $i < $aggregate_iterations; $i++) {
                    if ($db_info['supports_group_concat']) {
                        // Use GROUP_CONCAT for MySQL/MariaDB
                        $aggregate_query = "SELECT 
                            COUNT(*) as total_count,
                            SUM(value) as total_sum,
                            AVG(value) as average_value,
                            MIN(value) as min_value,
                            MAX(value) as max_value,
                            GROUP_CONCAT(DISTINCT category ORDER BY category) as categories
                        FROM {$temp_table}
                        GROUP BY category
                        HAVING COUNT(*) > 50
                        ORDER BY total_sum DESC
                        LIMIT 5";
                    } else {
                        // Use standard aggregate functions for other databases
                        $aggregate_query = "SELECT 
                            COUNT(*) as total_count,
                            SUM(value) as total_sum,
                            AVG(value) as average_value,
                            MIN(value) as min_value,
                            MAX(value) as max_value
                        FROM {$temp_table}
                        GROUP BY category
                        HAVING COUNT(*) > 50
                        ORDER BY total_sum DESC
                        LIMIT 5";
                    }
                    
                    $wpdb->get_results($aggregate_query);
                    
                    // Check for timeout on this specific test
                    if ((microtime(true) - $aggregate_test_start) > $function_timeout_limit) {
                        $aggregate_timed_out = true;
                        break;
                    }
                }
            }
            
            // Clean up temporary table
            $wpdb->query("DROP TEMPORARY TABLE IF EXISTS {$temp_table}");
            
            if (!$aggregate_timed_out) {
                $aggregate_time = microtime(true) - $aggregate_start;
            }
            
        } catch (Exception $e) {
            error_log('DiveWP: Database aggregate functions test failed (' . $db_info['type'] . '): ' . $e->getMessage());
            // Clean up on error
            $temp_table = $wpdb->prefix . 'divewp_db_test_data';
            $wpdb->query("DROP TEMPORARY TABLE IF EXISTS {$temp_table}");
            $aggregate_timed_out = true;
        }
        
        if ($aggregate_timed_out) {
            $timed_out_functions['aggregate'] = true;
            $total_function_score_deduction += $function_score_weights['aggregate'];
            $aggregate_time = 0;
        }
        
        $test_timings['aggregate_functions'] = round($aggregate_time, 3);
        
        $total_time = microtime(true) - $test_start;
        $test_timings['total_time'] = round($total_time, 3);
        
        // Calculate score based on database function performance with precise continuous scoring
        $score = 0;
        if ($total_time <= 0) {
            $score = 100;
        } elseif ($total_time <= 1.5) {
            // Excellent range: 100-95 (linear scale)
            $score = round(100 - ($total_time * 3.33));
        } elseif ($total_time <= 3.5) {
            // Very good range: 95-75 (linear scale)
            $score = round(95 - (($total_time - 1.5) * 10.0));
        } elseif ($total_time <= 7.0) {
            // Good range: 75-55 (linear scale)
            $score = round(75 - (($total_time - 3.5) * 5.71));
        } elseif ($total_time <= 15.0) {
            // Fair range: 55-35 (linear scale)
            $score = round(55 - (($total_time - 7.0) * 2.5));
        } else {
            // Poor range: 35-15 (capped minimum)
            $score = max(15, round(35 - (($total_time - 15.0) * 1.0)));
        }
        
        // Deduct points for timed out function tests
        $score = max(0, $score - $total_function_score_deduction);
        
        return array(
            'score' => $score,
            'database_type' => $db_info['type'],
            'database_version' => $db_info['version'],
            'is_mysql_compatible' => $db_info['is_mysql_compatible'],
            'crypto_functions_time' => $test_timings['crypto_functions'],
            'math_functions_time' => $test_timings['math_functions'],
            'string_functions_time' => $test_timings['string_functions'],
            'datetime_functions_time' => $test_timings['datetime_functions'],
            'aggregate_functions_time' => $test_timings['aggregate_functions'],
            'total_time' => $test_timings['total_time'],
            'iterations_completed' => array(
                'crypto' => 1000,
                'math' => 5000,
                'string' => 3000,
                'datetime' => 5000,
                'aggregate' => 100
            ),
            'database_features_used' => array(
                'aes_encryption' => $db_info['supports_aes_functions'],
                'advanced_math' => $db_info['supports_mysql_math_functions'],
                'advanced_datetime' => $db_info['supports_mysql_date_functions'],
                'group_concat' => $db_info['supports_group_concat']
            ),
            'timed_out_functions' => $timed_out_functions,
            'function_score_deduction' => $total_function_score_deduction
        );
    }

    /**
     * Get database performance interpretation
     *
     * @since 2.0.3
     * @param float $total_time Total database test time in seconds
     * @return string Interpretation message
     */
    public function get_db_interpretation($total_time) {
        if ($total_time < 2) {
            return esc_html__('Excellent! Can handle large WooCommerce stores with 10,000+ products.', 'divewp-boost-site-performance');
        } elseif ($total_time < 5) {
            return esc_html__('Good performance. Suitable for medium stores up to 5,000 products.', 'divewp-boost-site-performance');
        } elseif ($total_time < 10) {
            return esc_html__('Fair performance. Best for small stores under 1,000 products.', 'divewp-boost-site-performance');
        } else {
            return esc_html__('Poor performance. May struggle with WooCommerce. Consider upgrading.', 'divewp-boost-site-performance');
        }
    }


} 