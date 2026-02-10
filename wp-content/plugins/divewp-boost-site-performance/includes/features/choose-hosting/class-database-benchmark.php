<?php
/**
 * Database Benchmark Tests for DiveWP Hosting Evaluation
 *
 * This class performs realistic database performance testing using actual
 * WordPress database structure to simulate real-world WooCommerce and
 * high-volume content operations.
 *
 * @package DiveWP_Boost_Site_Performance
 * @subpackage Choose_Hosting
 * @since 2.0.5
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Database Benchmark Class
 *
 * Performs realistic database benchmarks including:
 * - WooCommerce operations (product browsing, cart, checkout, orders)
 * - High-volume content operations (archives, search, comments)
 * - Database health analysis (table sizes, indexes, fragmentation)
 * - Real-world query pattern simulation
 *
 * @since 2.0.5
 */
class DiveWP_Database_Benchmark {

    /**
     * Maximum time per test section in seconds
     * Shared hosting friendly - prevents process termination
     */
    private $max_section_time = 25;
    
    /**
     * Overall test timeout in seconds
     */
    private $max_total_time = 90;

    /**
     * Run comprehensive database benchmark
     *
     * Simulates real-world database operations using existing WordPress
     * database structure. Safe for production - no data modification.
     *
     * @since 2.0.5
     * @return array|WP_Error Benchmark results or error
     */
    public function run_database_benchmark() {
        global $wpdb;
        
        // Check rate limiting (one test per 45 seconds per user)
        $rate_limit_key = 'divewp_db_benchmark_rate_limit_' . get_current_user_id();
        if (get_transient($rate_limit_key)) {
            return new WP_Error('rate_limit', esc_html__('Please wait before running another benchmark.', 'divewp-boost-site-performance'));
        }
        set_transient($rate_limit_key, true, 45); // 45 seconds rate limit
        
        $benchmark_start = microtime(true);
        $results = array();
        
        // Set conservative timeout
        @set_time_limit(120);
        
        try {
            // Suppress database error output to prevent HTML in JSON response
            $wpdb->suppress_errors(true);
            
            // Analyze existing database structure first
            $db_analysis = $this->analyze_database_structure();
            
            // Test 1: WooCommerce Operations Simulation
            $woocommerce_results = $this->test_woocommerce_operations();
            
            // Test 2: High-Volume Content Operations
            $content_results = $this->test_content_operations();
            
            // Test 3: Database Performance Analysis
            $performance_results = $this->test_database_performance();
            
            // Test 4: Query Optimization Analysis
            $optimization_results = $this->test_query_optimization();
            
            // Re-enable error output
            $wpdb->suppress_errors(false);
            
            $total_time = microtime(true) - $benchmark_start;
            
            // Calculate overall benchmark score
            $overall_score = $this->calculate_benchmark_score(
                $woocommerce_results,
                $content_results, 
                $performance_results,
                $optimization_results
            );
            
            // Save raw database benchmark results to file for debugging
            $upload_dir = wp_upload_dir();
            $results_file = $upload_dir['basedir'] . '/divewp-database-benchmark-results.json';
            @file_put_contents($results_file, json_encode($overall_score, JSON_PRETTY_PRINT));
            
            return array(
                'total_time' => round($total_time * 1000, 2),
                'score' => $overall_score['score'],
                'rating' => $overall_score['rating'],
                'interpretation' => $overall_score['interpretation'],
                'database_analysis' => $db_analysis,
                'woocommerce_operations' => $woocommerce_results,
                'content_operations' => $content_results,
                'performance_analysis' => $performance_results,
                'optimization_analysis' => $optimization_results,
                'benchmark_completed' => true
            );
            
        } catch (Exception $e) {
            // Re-enable error output in case of exception
            $wpdb->suppress_errors(false);
            
            // Clean up rate limit on critical errors
            delete_transient($rate_limit_key);
            
            return new WP_Error('benchmark_failed', esc_html($e->getMessage()));
        }
    }

    /**
     * Analyze existing database structure and content
     * 
     * @return array Database analysis results
     */
    private function analyze_database_structure() {
        global $wpdb;
        
        $analysis_start = microtime(true);
        $analysis = array();
        
        try {
            // Count existing content for realistic testing
            $post_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'publish'");
            $meta_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta}");
            $comment_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = '1'");
            $user_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->users}");
            
            // Check for WooCommerce
            $has_woocommerce = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'product'") > 0;
            $product_count = $has_woocommerce ? $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status = 'publish'") : 0;
            
            // Table size analysis
            $table_sizes = $wpdb->get_results("
                SELECT 
                    table_name,
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
                    table_rows
                FROM information_schema.TABLES 
                WHERE table_schema = '" . DB_NAME . "' 
                AND table_name LIKE '{$wpdb->prefix}%'
                ORDER BY (data_length + index_length) DESC
                LIMIT 10
            ");
            
            $analysis = array(
                'content_volume' => array(
                    'posts' => intval($post_count),
                    'postmeta' => intval($meta_count),
                    'comments' => intval($comment_count),
                    'users' => intval($user_count)
                ),
                'woocommerce' => array(
                    'detected' => $has_woocommerce,
                    'products' => intval($product_count)
                ),
                'table_sizes' => $table_sizes,
                'analysis_time' => round((microtime(true) - $analysis_start) * 1000, 2)
            );
            
        } catch (Exception $e) {
            $analysis['error'] = $e->getMessage();
        }
        
        return $analysis;
    }

    /**
     * Test WooCommerce operations simulation
     * 
     * @return array WooCommerce test results
     */
    private function test_woocommerce_operations() {
        global $wpdb;
        
        $section_start = microtime(true);
        $results = array();
        
        // Check if WooCommerce data exists
        $product_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status = 'publish'");
        
        if ($product_count < 5) {
            return array(
                'skipped' => true,
                'reason' => 'Insufficient WooCommerce data for realistic testing',
                'suggestion' => 'Add some products to test WooCommerce performance'
            );
        }
        
        try {
            // Suppress database error output to prevent HTML in JSON response
            $wpdb->suppress_errors(true);
            
            // Test 1: Product Catalog Browsing
            $catalog_times = array();
            $operations_completed = 0;
            
            while ((microtime(true) - $section_start) < ($this->max_section_time * 0.4) && $operations_completed < 50) {
                $query_start = microtime(true);
                
                // Simulate product catalog query with filters and sorting
                $random_price_min = mt_rand(1, 50);
                $random_price_max = mt_rand(51, 200);
                $offset = mt_rand(0, max(0, $product_count - 20));
                
                $catalog_query = $wpdb->prepare("
                    SELECT p.ID, p.post_title, p.post_excerpt,
                           price.meta_value as price,
                           stock.meta_value as stock_quantity,
                           status.meta_value as stock_status
                    FROM {$wpdb->posts} p
                    LEFT JOIN {$wpdb->postmeta} price ON p.ID = price.post_id AND price.meta_key = '_price'
                    LEFT JOIN {$wpdb->postmeta} stock ON p.ID = stock.post_id AND stock.meta_key = '_stock'
                    LEFT JOIN {$wpdb->postmeta} status ON p.ID = status.post_id AND status.meta_key = '_stock_status'
                    WHERE p.post_type = 'product' 
                      AND p.post_status = 'publish'
                      AND price.meta_value IS NOT NULL
                      AND CAST(price.meta_value AS DECIMAL(10,2)) BETWEEN %f AND %f
                    ORDER BY CAST(price.meta_value AS DECIMAL(10,2)) ASC
                    LIMIT 20 OFFSET %d
                ", $random_price_min, $random_price_max, $offset);
                
                $wpdb->get_results($catalog_query);
                
                $catalog_times[] = microtime(true) - $query_start;
                $operations_completed++;
            }
            
            // Test 2: Product Detail Queries
            $detail_times = array();
            $detail_operations = 0;
            
            while ((microtime(true) - $section_start) < ($this->max_section_time * 0.6) && $detail_operations < 25) {
                $query_start = microtime(true);
                
                // Get random product ID
                $random_product = $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status = 'publish' ORDER BY RAND() LIMIT 1");
                
                if ($random_product) {
                    // Simulate product detail page query
                    $detail_query = $wpdb->prepare("
                        SELECT p.*, 
                               pm.meta_key, pm.meta_value
                        FROM {$wpdb->posts} p
                        LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                        WHERE p.ID = %d
                    ", $random_product);
                    
                    $wpdb->get_results($detail_query);
                    $detail_times[] = microtime(true) - $query_start;
                    $detail_operations++;
                }
            }
            
            // Test 3: Order History Simulation (if orders exist)
            $order_times = array();
            $order_operations = 0;
            $order_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'shop_order'");
            
            if ($order_count > 0) {
                while ((microtime(true) - $section_start) < ($this->max_section_time * 0.8) && $order_operations < 15) {
                    $query_start = microtime(true);
                    
                    // Simulate customer order history query
                    $order_query = "
                        SELECT p.ID, p.post_date, p.post_status,
                               total.meta_value as order_total,
                               customer.meta_value as customer_id
                        FROM {$wpdb->posts} p
                        LEFT JOIN {$wpdb->postmeta} total ON p.ID = total.post_id AND total.meta_key = '_order_total'
                        LEFT JOIN {$wpdb->postmeta} customer ON p.ID = customer.post_id AND customer.meta_key = '_customer_user'
                        WHERE p.post_type = 'shop_order'
                          AND p.post_status IN ('wc-completed', 'wc-processing')
                        ORDER BY p.post_date DESC
                        LIMIT 10
                    ";
                    
                    $wpdb->get_results($order_query);
                    $order_times[] = microtime(true) - $query_start;
                    $order_operations++;
                }
            }
            
            $results = array(
                'product_catalog' => array(
                    'operations' => $operations_completed,
                    'avg_time' => count($catalog_times) > 0 ? round(array_sum($catalog_times) / count($catalog_times) * 1000, 2) : 0,
                    'max_time' => count($catalog_times) > 0 ? round(max($catalog_times) * 1000, 2) : 0
                ),
                'product_details' => array(
                    'operations' => $detail_operations,
                    'avg_time' => count($detail_times) > 0 ? round(array_sum($detail_times) / count($detail_times) * 1000, 2) : 0,
                    'max_time' => count($detail_times) > 0 ? round(max($detail_times) * 1000, 2) : 0
                ),
                'order_history' => array(
                    'operations' => $order_operations,
                    'avg_time' => count($order_times) > 0 ? round(array_sum($order_times) / count($order_times) * 1000, 2) : 0,
                    'max_time' => count($order_times) > 0 ? round(max($order_times) * 1000, 2) : 0
                ),
                'total_time' => round((microtime(true) - $section_start) * 1000, 2),
                'products_tested' => $product_count
            );
            
        } catch (Exception $e) {
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }

    /**
     * Test high-volume content operations
     * 
     * @return array Content operations test results
     */
    private function test_content_operations() {
        global $wpdb;
        
        $section_start = microtime(true);
        $results = array();
        
        try {
            // Suppress database error output to prevent HTML in JSON response
            $wpdb->suppress_errors(true);
            
            // Test 1: Homepage/Archive Loading Simulation
            $archive_times = array();
            $archive_operations = 0;
            
            while ((microtime(true) - $section_start) < ($this->max_section_time * 0.35) && $archive_operations < 30) {
                $query_start = microtime(true);
                
                // Simulate homepage/category archive query
                $offset = mt_rand(0, 100);
                $archive_query = "
                    SELECT p.ID, p.post_title, p.post_excerpt, p.post_date, p.comment_count,
                           u.display_name as author_name,
                           (SELECT meta_value FROM {$wpdb->postmeta} 
                            WHERE post_id = p.ID AND meta_key = '_thumbnail_id' LIMIT 1) as featured_image
                    FROM {$wpdb->posts} p
                    LEFT JOIN {$wpdb->users} u ON p.post_author = u.ID
                    WHERE p.post_type = 'post' 
                      AND p.post_status = 'publish'
                    ORDER BY p.post_date DESC
                    LIMIT 10 OFFSET {$offset}
                ";
                
                $wpdb->get_results($archive_query);
                
                $archive_times[] = microtime(true) - $query_start;
                $archive_operations++;
            }
            
            // Test 2: Search Operations Simulation
            $search_times = array();
            $search_operations = 0;
            $search_terms = array('wordpress', 'website', 'business', 'design', 'development', 'marketing');
            
            while ((microtime(true) - $section_start) < ($this->max_section_time * 0.6) && $search_operations < 20) {
                $query_start = microtime(true);
                
                $search_term = $search_terms[array_rand($search_terms)];
                
                // Simulate search query - using LIKE only for compatibility
                // FULLTEXT indexes are not available by default in WordPress
                $search_query = $wpdb->prepare("
                    SELECT p.ID, p.post_title, p.post_excerpt, p.post_date,
                           (CASE 
                               WHEN p.post_title LIKE %s THEN 2
                               WHEN p.post_content LIKE %s THEN 1
                               ELSE 0
                           END) as relevance_score
                    FROM {$wpdb->posts} p
                    WHERE p.post_type = 'post' 
                      AND p.post_status = 'publish'
                      AND (p.post_title LIKE %s OR p.post_content LIKE %s)
                    ORDER BY relevance_score DESC, p.post_date DESC
                    LIMIT 15
                ", '%' . $search_term . '%', '%' . $search_term . '%', '%' . $search_term . '%', '%' . $search_term . '%');
                
                $wpdb->get_results($search_query);
                
                $search_times[] = microtime(true) - $query_start;
                $search_operations++;
            }
            
            // Test 3: Comment System Simulation
            $comment_times = array();
            $comment_operations = 0;
            
            while ((microtime(true) - $section_start) < ($this->max_section_time * 0.85) && $comment_operations < 15) {
                $query_start = microtime(true);
                
                // Get random post with comments
                $post_with_comments = $wpdb->get_var("
                    SELECT post_id FROM {$wpdb->comments} 
                    WHERE comment_approved = '1' 
                    GROUP BY post_id 
                    ORDER BY RAND() 
                    LIMIT 1
                ");
                
                if ($post_with_comments) {
                    // Simulate comment loading query
                    $comment_query = $wpdb->prepare("
                        SELECT c.*, u.display_name
                        FROM {$wpdb->comments} c
                        LEFT JOIN {$wpdb->users} u ON c.user_id = u.ID
                        WHERE c.comment_post_ID = %d 
                          AND c.comment_approved = '1'
                        ORDER BY c.comment_date ASC
                        LIMIT 50
                    ", $post_with_comments);
                    
                    $wpdb->get_results($comment_query);
                    $comment_times[] = microtime(true) - $query_start;
                    $comment_operations++;
                }
            }
            
            // Test 4: Popular Content Queries
            $popular_times = array();
            $popular_operations = 0;
            
            while ((microtime(true) - $section_start) < $this->max_section_time && $popular_operations < 10) {
                $query_start = microtime(true);
                
                // Simulate popular posts query (by comment count)
                $popular_query = "
                    SELECT p.ID, p.post_title, p.post_date, p.comment_count,
                           COUNT(c.comment_ID) as total_comments
                    FROM {$wpdb->posts} p
                    LEFT JOIN {$wpdb->comments} c ON p.ID = c.comment_post_ID AND c.comment_approved = '1'
                    WHERE p.post_type = 'post' 
                      AND p.post_status = 'publish'
                      AND p.post_date > DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY p.ID
                    ORDER BY total_comments DESC, p.comment_count DESC
                    LIMIT 10
                ";
                
                $wpdb->get_results($popular_query);
                $popular_times[] = microtime(true) - $query_start;
                $popular_operations++;
            }
            
            $results = array(
                'archive_loading' => array(
                    'operations' => $archive_operations,
                    'avg_time' => count($archive_times) > 0 ? round(array_sum($archive_times) / count($archive_times) * 1000, 2) : 0,
                    'max_time' => count($archive_times) > 0 ? round(max($archive_times) * 1000, 2) : 0
                ),
                'search_operations' => array(
                    'operations' => $search_operations,
                    'avg_time' => count($search_times) > 0 ? round(array_sum($search_times) / count($search_times) * 1000, 2) : 0,
                    'max_time' => count($search_times) > 0 ? round(max($search_times) * 1000, 2) : 0
                ),
                'comment_loading' => array(
                    'operations' => $comment_operations,
                    'avg_time' => count($comment_times) > 0 ? round(array_sum($comment_times) / count($comment_times) * 1000, 2) : 0,
                    'max_time' => count($comment_times) > 0 ? round(max($comment_times) * 1000, 2) : 0
                ),
                'popular_content' => array(
                    'operations' => $popular_operations,
                    'avg_time' => count($popular_times) > 0 ? round(array_sum($popular_times) / count($popular_times) * 1000, 2) : 0,
                    'max_time' => count($popular_times) > 0 ? round(max($popular_times) * 1000, 2) : 0
                ),
                'total_time' => round((microtime(true) - $section_start) * 1000, 2)
            );
            
        } catch (Exception $e) {
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }

    /**
     * Test database performance characteristics
     * 
     * @return array Database performance results
     */
    private function test_database_performance() {
        global $wpdb;
        
        $section_start = microtime(true);
        $results = array();
        
        try {
            // Test 1: Query Cache Effectiveness
            $cache_test_query = "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'publish'";
            
            $cache_times = array();
            for ($i = 0; $i < 5; $i++) {
                $query_start = microtime(true);
                $wpdb->get_var($cache_test_query);
                $cache_times[] = microtime(true) - $query_start;
            }
            
            // Test 2: JOIN Performance
            $join_times = array();
            $join_operations = 0;
            
            while ((microtime(true) - $section_start) < ($this->max_section_time * 0.4) && $join_operations < 20) {
                $query_start = microtime(true);
                
                $join_query = "
                    SELECT p.ID, p.post_title, COUNT(pm.meta_id) as meta_count
                    FROM {$wpdb->posts} p
                    LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                    WHERE p.post_type IN ('post', 'page', 'product')
                      AND p.post_status = 'publish'
                    GROUP BY p.ID
                    ORDER BY meta_count DESC
                    LIMIT 20
                ";
                
                $wpdb->get_results($join_query);
                $join_times[] = microtime(true) - $query_start;
                $join_operations++;
            }
            
            // Test 3: Aggregate Function Performance
            $aggregate_times = array();
            $aggregate_operations = 0;
            
            while ((microtime(true) - $section_start) < ($this->max_section_time * 0.7) && $aggregate_operations < 15) {
                $query_start = microtime(true);
                
                $aggregate_query = "
                    SELECT 
                        post_type,
                        COUNT(*) as total_posts,
                        AVG(CHAR_LENGTH(post_content)) as avg_content_length,
                        MAX(post_date) as latest_post,
                        MIN(post_date) as earliest_post
                    FROM {$wpdb->posts}
                    WHERE post_status = 'publish'
                    GROUP BY post_type
                    ORDER BY total_posts DESC
                ";
                
                $wpdb->get_results($aggregate_query);
                $aggregate_times[] = microtime(true) - $query_start;
                $aggregate_operations++;
            }
            
            // Test 4: Index Usage Analysis (MySQL specific)
            $index_analysis = array();
            try {
                $explain_query = "EXPLAIN SELECT * FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status = 'publish' ORDER BY post_date DESC LIMIT 10";
                $explain_result = $wpdb->get_results($explain_query);
                $index_analysis['query_plan'] = $explain_result;
            } catch (Exception $e) {
                $index_analysis['error'] = 'Index analysis not available';
            }
            
            $results = array(
                'query_cache' => array(
                    'first_run' => round($cache_times[0] * 1000, 2),
                    'cached_avg' => count($cache_times) > 1 ? round(array_sum(array_slice($cache_times, 1)) / (count($cache_times) - 1) * 1000, 2) : 0,
                    'cache_improvement' => count($cache_times) > 1 ? round((($cache_times[0] - array_sum(array_slice($cache_times, 1)) / (count($cache_times) - 1)) / $cache_times[0]) * 100, 1) : 0
                ),
                'join_performance' => array(
                    'operations' => $join_operations,
                    'avg_time' => count($join_times) > 0 ? round(array_sum($join_times) / count($join_times) * 1000, 2) : 0,
                    'max_time' => count($join_times) > 0 ? round(max($join_times) * 1000, 2) : 0
                ),
                'aggregate_performance' => array(
                    'operations' => $aggregate_operations,
                    'avg_time' => count($aggregate_times) > 0 ? round(array_sum($aggregate_times) / count($aggregate_times) * 1000, 2) : 0,
                    'max_time' => count($aggregate_times) > 0 ? round(max($aggregate_times) * 1000, 2) : 0
                ),
                'index_analysis' => $index_analysis,
                'total_time' => round((microtime(true) - $section_start) * 1000, 2)
            );
            
        } catch (Exception $e) {
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }

    /**
     * Test query optimization opportunities
     * 
     * @return array Query optimization results
     */
    private function test_query_optimization() {
        global $wpdb;
        
        $section_start = microtime(true);
        $results = array();
        
        try {
            // Test 1: Slow Query Simulation
            $slow_query_times = array();
            $slow_operations = 0;
            
            // Intentionally complex queries to test database limits
            while ((microtime(true) - $section_start) < ($this->max_section_time * 0.6) && $slow_operations < 10) {
                $query_start = microtime(true);
                
                // Complex query that might be slow on poor hosting
                $complex_query = "
                    SELECT p1.ID, p1.post_title, 
                           (SELECT COUNT(*) FROM {$wpdb->posts} p2 WHERE p2.post_author = p1.post_author) as author_post_count,
                           (SELECT COUNT(*) FROM {$wpdb->comments} c WHERE c.comment_post_ID = p1.ID) as comment_count,
                           (SELECT AVG(CHAR_LENGTH(post_content)) FROM {$wpdb->posts} p3 WHERE p3.post_type = p1.post_type) as avg_content_length
                    FROM {$wpdb->posts} p1
                    WHERE p1.post_type = 'post' 
                      AND p1.post_status = 'publish'
                    ORDER BY p1.post_date DESC
                    LIMIT 5
                ";
                
                $wpdb->get_results($complex_query);
                $slow_query_times[] = microtime(true) - $query_start;
                $slow_operations++;
                
                // Safety break if queries are taking too long
                if (end($slow_query_times) > 5.0) {
                    break;
                }
            }
            
            // Test 2: Memory Usage Estimation
            $memory_start = memory_get_usage();
            $large_result = $wpdb->get_results("
                SELECT p.*, pm.meta_key, pm.meta_value 
                FROM {$wpdb->posts} p 
                LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
                WHERE p.post_status = 'publish' 
                LIMIT 100
            ");
            $memory_used = memory_get_usage() - $memory_start;
            
            // Test 3: Database Connection Stability
            $connection_times = array();
            for ($i = 0; $i < 5; $i++) {
                $query_start = microtime(true);
                $wpdb->get_var("SELECT 1");
                $connection_times[] = microtime(true) - $query_start;
            }
            
            $results = array(
                'complex_queries' => array(
                    'operations' => $slow_operations,
                    'avg_time' => count($slow_query_times) > 0 ? round(array_sum($slow_query_times) / count($slow_query_times) * 1000, 2) : 0,
                    'max_time' => count($slow_query_times) > 0 ? round(max($slow_query_times) * 1000, 2) : 0,
                    'timeout_risk' => count($slow_query_times) > 0 ? (max($slow_query_times) > 2.0 ? 'high' : 'low') : 'unknown'
                ),
                'memory_usage' => array(
                    'query_memory' => round($memory_used / 1024 / 1024, 2), // MB
                    'records_processed' => count($large_result)
                ),
                'connection_stability' => array(
                    'avg_connection_time' => round(array_sum($connection_times) / count($connection_times) * 1000, 2),
                    'connection_variance' => round($this->calculate_variance($connection_times) * 1000, 2)
                ),
                'total_time' => round((microtime(true) - $section_start) * 1000, 2)
            );
            
        } catch (Exception $e) {
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }

    /**
     * Calculate overall benchmark score
     * 
     * @param array $woocommerce_results WooCommerce test results
     * @param array $content_results Content test results
     * @param array $performance_results Performance test results
     * @param array $optimization_results Optimization test results
     * @return array Score and rating information
     */
    private function calculate_benchmark_score($woocommerce_results, $content_results, $performance_results, $optimization_results) {
        $scores = array();
        
        // WooCommerce Score (30% weight)
        if (!isset($woocommerce_results['skipped'])) {
            $wc_score = $this->score_response_times(array(
                $woocommerce_results['product_catalog']['avg_time'] ?? 1000,
                $woocommerce_results['product_details']['avg_time'] ?? 1000,
                $woocommerce_results['order_history']['avg_time'] ?? 1000
            ));
            $scores['woocommerce'] = $wc_score;
        }
        
        // Content Operations Score (30% weight)
        $content_score = $this->score_response_times(array(
            $content_results['archive_loading']['avg_time'] ?? 1000,
            $content_results['search_operations']['avg_time'] ?? 1000,
            $content_results['comment_loading']['avg_time'] ?? 1000,
            $content_results['popular_content']['avg_time'] ?? 1000
        ));
        $scores['content'] = $content_score;
        
        // Performance Score (25% weight)
        $perf_score = $this->score_response_times(array(
            $performance_results['join_performance']['avg_time'] ?? 1000,
            $performance_results['aggregate_performance']['avg_time'] ?? 1000
        ));
        $scores['performance'] = $perf_score;
        
        // Optimization Score (15% weight)
        $opt_score = $this->score_response_times(array(
            $optimization_results['complex_queries']['avg_time'] ?? 2000
        ));
        $scores['optimization'] = $opt_score;
        
        // Calculate weighted average
        $weights = array(
            'woocommerce' => 0.30,
            'content' => 0.30,
            'performance' => 0.25,
            'optimization' => 0.15
        );
        
        $total_weight = 0;
        $weighted_score = 0;
        
        foreach ($scores as $type => $score) {
            $weighted_score += $score * $weights[$type];
            $total_weight += $weights[$type];
        }
        
        $final_score = $total_weight > 0 ? round($weighted_score / $total_weight) : 50;
        
        // Determine rating and interpretation
        if ($final_score >= 85) {
            $rating = 'excellent';
            $interpretation = 'Excellent database performance for high-volume WooCommerce and content operations.';
        } elseif ($final_score >= 70) {
            $rating = 'good';
            $interpretation = 'Good database performance suitable for most WooCommerce stores and content sites.';
        } elseif ($final_score >= 50) {
            $rating = 'fair';
            $interpretation = 'Fair database performance. May experience slowdowns with high traffic or complex queries.';
        } else {
            $rating = 'poor';
            $interpretation = 'Poor database performance. Significant optimization or hosting upgrade needed.';
        }
        
        return array(
            'score' => $final_score,
            'rating' => $rating,
            'interpretation' => $interpretation,
            'component_scores' => $scores
        );
    }

    /**
     * Score response times using realistic thresholds
     * 
     * @param array $times Array of response times in milliseconds
     * @return int Score from 0-100
     */
    private function score_response_times($times) {
        $avg_time = array_sum($times) / count($times);
        
        // Convert to seconds for scoring
        $avg_seconds = $avg_time / 1000;
        
        // Continuous scoring based on realistic web performance thresholds
        if ($avg_seconds <= 0.1) {
            // Excellent: 100-95 (under 100ms)
            return round(100 - ($avg_seconds * 50));
        } elseif ($avg_seconds <= 0.3) {
            // Very good: 95-80 (100-300ms)
            return round(95 - (($avg_seconds - 0.1) * 75));
        } elseif ($avg_seconds <= 0.8) {
            // Good: 80-60 (300-800ms)
            return round(80 - (($avg_seconds - 0.3) * 40));
        } elseif ($avg_seconds <= 2.0) {
            // Fair: 60-35 (800ms-2s)
            return round(60 - (($avg_seconds - 0.8) * 20.83));
        } else {
            // Poor: 35-15 (over 2s)
            return max(15, round(35 - (($avg_seconds - 2.0) * 10)));
        }
    }

    /**
     * Calculate variance for array of values
     * 
     * @param array $values Array of numeric values
     * @return float Variance
     */
    private function calculate_variance($values) {
        $mean = array_sum($values) / count($values);
        $squared_differences = array_map(function($value) use ($mean) {
            return pow($value - $mean, 2);
        }, $values);
        
        return array_sum($squared_differences) / count($values);
    }

    /**
     * Get database benchmark interpretation
     *
     * @param float $total_time Total benchmark time in seconds
     * @return string Interpretation message
     */
    public function get_benchmark_interpretation($total_time) {
        if ($total_time < 30) {
            return esc_html__('Excellent! Database handles realistic workloads efficiently. Suitable for high-traffic sites.', 'divewp-boost-site-performance');
        } elseif ($total_time < 60) {
            return esc_html__('Good database performance for realistic operations. Suitable for most production sites.', 'divewp-boost-site-performance');
        } elseif ($total_time < 120) {
            return esc_html__('Fair performance with realistic workloads. Monitor performance under peak traffic.', 'divewp-boost-site-performance');
        } else {
            return esc_html__('Slow database performance with realistic operations. Optimization or upgrade recommended.', 'divewp-boost-site-performance');
        }
    }
} 