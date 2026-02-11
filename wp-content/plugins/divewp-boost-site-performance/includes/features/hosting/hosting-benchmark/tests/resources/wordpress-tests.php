<?php
/**
 * WordPress Core Performance Tests
 *
 * Replicates exact POC WordPress test specifications with enhanced UX features.
 * Tests WordPress-specific functionality and performance.
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
 * Resources WordPress Tests Class
 */
class DiveWP_Resources_WordPress_Tests {

    /**
     * Run WordPress core performance tests (exact POC replication)
     *
     * @param array $test_config POC test configuration
     * @return array Test results with enhanced UX data
     */
    public static function run($test_config = array()) {
        $start_time = microtime(true);
        
        // Use actual PHP time limit instead of hardcoded value
        $php_max_time = ini_get('max_execution_time');
        $max_test_time = $test_config['max_test_time_per_section'] ?? ($php_max_time > 0 ? $php_max_time * 0.9 : 54);
        
        // POC WordPress test structure
        $wp_results = array();
        $completed_operations = 0;
        $total_operations = 4; // 4 WordPress sub-tests
        $test_status = 'completed';
        $timeout_reason = null;
        
        try {
            // Sub-test 1: Shortcode Processing (POC exact specification)
            $shortcode_result = self::test_shortcode_processing($test_config, $start_time, $max_test_time);
            $wp_results['shortcode_processing'] = $shortcode_result;
            if ($shortcode_result['test_status'] === 'completed') {
                $completed_operations++;
            }
            
            // Sub-test 2: Hook Execution (POC exact specification)
            $hook_result = self::test_hook_execution($test_config, $start_time, $max_test_time);
            $wp_results['hook_execution'] = $hook_result;
            if ($hook_result['test_status'] === 'completed') {
                $completed_operations++;
            }
            
            // Sub-test 3: Transient Operations (POC exact specification)
            $transient_result = self::test_transient_operations($test_config, $start_time, $max_test_time);
            $wp_results['transient_operations'] = $transient_result;
            if ($transient_result['test_status'] === 'completed') {
                $completed_operations++;
            }
            
            // Sub-test 4: Security Functions (POC exact specification)
            $security_result = self::test_security_functions($test_config, $start_time, $max_test_time);
            $wp_results['security_functions'] = $security_result;
            if ($security_result['test_status'] === 'completed') {
                $completed_operations++;
            }
            
        } catch (Exception $e) {
            $test_status = 'error';
            $timeout_reason = 'WordPress test error: ' . $e->getMessage();
        }
        
        $total_time = microtime(true) - $start_time;
        
        // Calculate overall WordPress score using POC method
        $wp_score = self::calculate_wordpress_score($wp_results, $total_time);
        
        // Calculate operations per second for UI display
        $operations_per_second = $total_time > 0 ? round($completed_operations / $total_time, 1) : 0;
        
        return array(
            'score' => $wp_score,
            'total_time' => round($total_time, 3),
            'completed_operations' => $completed_operations,
            'total_operations' => $total_operations,
            'operations_per_second' => $operations_per_second,
            'status' => $test_status,
            'timeout_reason' => $timeout_reason,
            'sub_test_results' => $wp_results,
            'performance_interpretation' => DiveWP_Benchmark_Resources_Scoring::get_sub_test_performance_interpretation('wordpress_tests', array(
                'score' => $wp_score,
                'total_time' => $total_time,
                'completed_operations' => $completed_operations,
                'total_operations' => $total_operations,
                'operations_per_second' => $operations_per_second,
                'status' => $test_status,
                'timeout_reason' => $timeout_reason
            ))
        );
    }

    /**
     * WordPress Sub-test 1: Shortcode Processing (POC exact specification)
     * 
     * @param array $test_config Test configuration
     * @param float $start_time Overall test start time
     * @param int $max_test_time Maximum test time
     * @return array Test results
     */
    private static function test_shortcode_processing($test_config, $start_time, $max_test_time) {
        $test_start_time = microtime(true);
        
        // POC specification: Fixed 1000 iterations, specific test content
        $shortcode_iterations = 1000;
        $test_content = 'This is a test post with [gallery ids="1,2,3"] and [audio src="test.mp3"] shortcodes for performance testing.';
        $shortcodes_processed = 0;
        $test_status = 'completed';
        
        try {
            for ($i = 0; $i < $shortcode_iterations; $i++) {
                $processed = do_shortcode($test_content . ' ' . $i);
                $shortcodes_processed++;
                
                // POC specification: Timeout check at 90% of max_test_time to allow proper completion
                if ((microtime(true) - $test_start_time) > ($max_test_time * 0.9)) {
                    $test_status = 'timeout';
                    break;
                }
            }
        } catch (Exception $e) {
            $test_status = 'error';
        }
        
        $time = microtime(true) - $test_start_time;
        
        return array(
            'time' => round($time, 3),
            'shortcodes_processed' => $shortcodes_processed,
            'content_length' => strlen($processed ?? ''),
            'test_status' => $test_status,
            'completed_operations' => $shortcodes_processed,
            'total_operations' => $shortcode_iterations
        );
    }
    
    /**
     * WordPress Sub-test 2: Hook Execution (POC exact specification)
     * 
     * @param array $test_config Test configuration
     * @param float $start_time Overall test start time
     * @param int $max_test_time Maximum test time
     * @return array Test results
     */
    private static function test_hook_execution($test_config, $start_time, $max_test_time) {
        $test_start_time = microtime(true);
        
        // POC specification: Fixed 10,000 iterations, apply_filters tests
        $hook_iterations = 10000;
        $hooks_executed = 0;
        $test_status = 'completed';
        $final_content_length = 0;
        
        try {
            for ($i = 0; $i < $hook_iterations; $i++) {
                $content = 'Test content for filtering ' . $i;
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Testing WordPress core hook performance; not invoking plugin hooks
                $filtered = apply_filters('the_content', $content);
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Testing WordPress core hook performance; not invoking plugin hooks
                $filtered = apply_filters('wp_trim_excerpt', $filtered);
                $hooks_executed++;
                $final_content_length = strlen($filtered);
                
                // POC specification: Timeout check at 90% of max_test_time to allow proper completion
                if ((microtime(true) - $test_start_time) > ($max_test_time * 0.9)) {
                    $test_status = 'timeout';
                    break;
                }
            }
        } catch (Exception $e) {
            $test_status = 'error';
        }
        
        $time = microtime(true) - $test_start_time;
        
        return array(
            'time' => round($time, 3),
            'hooks_executed' => $hooks_executed,
            'final_content_length' => $final_content_length,
            'test_status' => $test_status,
            'completed_operations' => $hooks_executed,
            'total_operations' => $hook_iterations
        );
    }

    /**
     * WordPress Sub-test 3: Transient Operations (POC exact specification)
     * 2025-Optimized - Automatically detects and tests the appropriate caching layer
     * 
     * @param array $test_config Test configuration
     * @param float $start_time Overall test start time
     * @param int $max_test_time Maximum test time
     * @return array Test results
     */
    private static function test_transient_operations($test_config, $start_time, $max_test_time) {
        // POC specification: Use 2025-optimized WordPress caching performance testing
        $result = self::test_wordpress_caching_performance_2025($test_config, $start_time, $max_test_time);
        
        // POC specification: Ensure backwards compatibility with existing result structure
        $legacy_result = array(
            'time' => $result['time'],
            'transients_processed' => $result['operations_completed'],
            'data_size' => 0 // Will be calculated below
        );
        
        // Add enhanced 2025 data for debugging and insights (POC structure)
        $legacy_result['cache_detection'] = array(
            'test_approach' => $result['test_approach'],
            'performance_tier' => $result['performance_tier'],
            'hosting_generation' => ($result['test_approach'] === 'object_cache') ? '2025_modern' : '2024_traditional'
        );
        
        // Calculate data size based on test approach (POC method)
        if ($result['test_approach'] === 'object_cache') {
            $legacy_result['data_size'] = 500; // Approximate size of test datasets
            $legacy_result['cache_type'] = $result['cache_type'];
            $legacy_result['avg_operation_time_ms'] = $result['avg_operation_time'];
        } else {
            $legacy_result['data_size'] = 2000; // Approximate size of database test data
            $legacy_result['set_time_ms'] = $result['set_time'];
            $legacy_result['get_time_ms'] = $result['get_time'];
            $legacy_result['delete_time_ms'] = $result['delete_time'];
            $legacy_result['estimated_ops_per_second'] = $result['estimated_ops_per_second'];
        }
        
        // Add enhanced UX data structure
        $legacy_result['test_status'] = $result['test_status'] ?? 'completed';
        $legacy_result['completed_operations'] = $result['operations_completed'];
        $legacy_result['total_operations'] = $result['total_operations'] ?? $result['operations_completed'];
        
        return $legacy_result;
    }
    
    /**
     * WordPress Sub-test 4: Security Functions (POC exact specification)
     * 
     * @param array $test_config Test configuration
     * @param float $start_time Overall test start time
     * @param int $max_test_time Maximum test time
     * @return array Test results
     */
    private static function test_security_functions($test_config, $start_time, $max_test_time) {
        $test_start_time = microtime(true);
        
        // POC specification: Fixed 5000 iterations, wp_kses_post testing
        $security_iterations = 5000;
        $test_html = '<div class="test"><p>This is <strong>test HTML</strong> with <a href="#" onclick="alert()">links</a> and <script>alert("xss")</script> for sanitization testing.</p></div>';
        $security_operations = 0;
        $test_status = 'completed';
        $sanitized_length = 0;
        $stripped_length = 0;
        
        try {
            for ($i = 0; $i < $security_iterations; $i++) {
                $sanitized = wp_kses_post($test_html . ' ' . $i);
                $stripped = wp_strip_all_tags($sanitized);
                $security_operations++;
                $sanitized_length = strlen($sanitized);
                $stripped_length = strlen($stripped);
                
                // POC specification: Timeout check at 90% of max_test_time to allow proper completion
                if ((microtime(true) - $test_start_time) > ($max_test_time * 0.9)) {
                    $test_status = 'timeout';
                    break;
                }
            }
        } catch (Exception $e) {
            $test_status = 'error';
        }
        
        $time = microtime(true) - $test_start_time;
        
        return array(
            'time' => round($time, 3),
            'security_operations' => $security_operations,
            'sanitized_length' => $sanitized_length,
            'stripped_length' => $stripped_length,
            'test_status' => $test_status,
            'completed_operations' => $security_operations,
            'total_operations' => $security_iterations
        );
    }

    /**
     * WordPress 2025-optimized transient testing (POC exact method)
     * Tests the actual caching layer being used by the hosting environment
     * 
     * @param array $test_config Test configuration
     * @param float $start_time Overall test start time
     * @param int $max_test_time Maximum test time
     * @return array Test results adapted to hosting's caching setup
     */
    private static function test_wordpress_caching_performance_2025($test_config, $start_time, $max_test_time) {
        $cache_env = self::detect_wp_caching_environment();
        $test_start_time = microtime(true);
        
        if ($cache_env['recommended_test_approach'] === 'object_cache') {
            // Test object cache performance (modern hosting) (POC method)
            return self::test_object_cache_performance($test_config, $max_test_time, $start_time, $cache_env);
        } else {
            // Test database-backed transients (traditional hosting) (POC method)
            return self::test_database_transients_safe($test_config, $max_test_time, $start_time);
        }
    }
    
    /**
     * Detect WordPress caching environment for 2025-optimized testing (POC exact method)
     * 
     * @return array Caching environment details
     */
    private static function detect_wp_caching_environment() {
        $cache_info = array(
            'has_persistent_cache' => false,
            'cache_type' => 'default',
            'wp_cache_enabled' => defined('WP_CACHE') && WP_CACHE,
            'object_cache_dropin' => false,
            'recommended_test_approach' => 'database'
        );
        
        // Check for object-cache.php drop-in (2025 standard) (POC method)
        if (file_exists(WP_CONTENT_DIR . '/object-cache.php')) {
            $cache_info['object_cache_dropin'] = true;
            $cache_info['has_persistent_cache'] = true;
            
            // Try to detect cache type from common implementations (POC method)
            $object_cache_content = @file_get_contents(WP_CONTENT_DIR . '/object-cache.php');
            if ($object_cache_content) {
                if (strpos($object_cache_content, 'redis') !== false) {
                    $cache_info['cache_type'] = 'redis';
                } elseif (strpos($object_cache_content, 'memcached') !== false) {
                    $cache_info['cache_type'] = 'memcached';
                } else {
                    $cache_info['cache_type'] = 'custom';
                }
            }
        }
        
        // Test if object cache is actually working with persistent storage (POC method)
        if ($cache_info['has_persistent_cache']) {
            $test_key = 'divewp_cache_test_' . time();
            $test_value = wp_generate_password(10, false);
            
            // Use wp_cache_set instead of transients for direct object cache testing
            wp_cache_set($test_key, $test_value, 'divewp_test', 300);
            $retrieved = wp_cache_get($test_key, 'divewp_test');
            wp_cache_delete($test_key, 'divewp_test');
            
            if ($retrieved === $test_value) {
                $cache_info['recommended_test_approach'] = 'object_cache';
            } else {
                // Object cache not working properly, fallback to database testing
                $cache_info['has_persistent_cache'] = false;
                $cache_info['recommended_test_approach'] = 'database';
            }
        }
        
        return $cache_info;
    }
    
    /**
     * Test object cache performance for modern hosting (POC exact method)
     * 
     * @param array $test_config Test configuration
     * @param int $max_test_time Maximum test time
     * @param float $start_time Test start time
     * @param array $cache_env Cache environment info
     * @return array Object cache performance results
     */
    private static function test_object_cache_performance($test_config, $max_test_time, $start_time, $cache_env) {
        $operations_completed = 0;
        $total_operation_time = 0;
        $test_status = 'completed';
        
        // Test with various data sizes to simulate real WordPress usage (POC method)
        $test_datasets = array(
            'small' => array('user_pref' => wp_generate_password(50, false)),
            'medium' => array('post_meta' => str_repeat('content_', 100)),
            'large' => array('query_results' => array_fill(0, 100, wp_generate_password(20, false)))
        );
        
        try {
            foreach ($test_datasets as $size => $data) {
                // Limit iterations based on hosting environment (POC method)
                $iterations = ($cache_env['cache_type'] === 'redis') ? 100 : 50;
                
                for ($i = 0; $i < $iterations; $i++) {
                    $cache_key = "divewp_test_{$size}_{$i}";
                    $cache_group = 'divewp_performance_test';
                    
                    $op_start = microtime(true);
                    
                    // Set operation
                    wp_cache_set($cache_key, $data, $cache_group, 300);
                    
                    // Get operation  
                    $retrieved = wp_cache_get($cache_key, $cache_group);
                    
                    // Delete operation
                    wp_cache_delete($cache_key, $cache_group);
                    
                    $op_time = microtime(true) - $op_start;
                    $total_operation_time += $op_time;
                    $operations_completed++;
                    
                    // Gentle throttling for hosting-friendly testing (POC method)
                    usleep(5000); // 5ms delay
                    
                    // Safety timeout check (POC method)
                    if ((microtime(true) - $start_time) > ($max_test_time * 0.8)) {
                        break 2;
                    }
                }
            }
        } catch (Exception $e) {
            $test_status = 'error';
        }
        
        $avg_operation_time = $operations_completed > 0 ? $total_operation_time / $operations_completed : 0;
        
        return array(
            'time' => round($total_operation_time, 3),
            'operations_completed' => $operations_completed,
            'total_operations' => $operations_completed,
            'avg_operation_time' => round($avg_operation_time * 1000, 2), // Convert to milliseconds
            'cache_type' => $cache_env['cache_type'],
            'test_approach' => 'object_cache',
            'cache_hit_ratio' => 100, // Object cache should always hit
            'performance_tier' => self::classify_object_cache_performance($avg_operation_time),
            'test_status' => $test_status
        );
    }
    
    /**
     * Safe database transient testing for traditional hosting (POC exact method)
     * 
     * @param array $test_config Test configuration
     * @param int $max_test_time Maximum test time
     * @param float $start_time Test start time
     * @return array Database transient performance results
     */
    private static function test_database_transients_safe($test_config, $max_test_time, $start_time) {
        // POC specification: Use single operation approach to avoid triggering security systems
        $test_data = array(
            'wp_query_cache' => array_fill(0, 50, wp_generate_password(20, false)),
            'user_meta_cache' => wp_generate_password(100, false),
            'timestamp' => current_time('timestamp')
        );
        
        $test_status = 'completed';
        
        try {
            // Single transient operation with timing (POC method)
            $transient_name = 'divewp_perf_test_' . get_current_user_id();
            
            $set_start = microtime(true);
            set_transient($transient_name, $test_data, HOUR_IN_SECONDS);
            $set_time = microtime(true) - $set_start;
            
            $get_start = microtime(true);
            $retrieved = get_transient($transient_name);
            $get_time = microtime(true) - $get_start;
            
            $delete_start = microtime(true);
            delete_transient($transient_name);
            $delete_time = microtime(true) - $delete_start;
            
            $total_time = $set_time + $get_time + $delete_time;
            
            // Extrapolate performance metrics (POC method)
            $estimated_ops_per_second = 3 / $total_time; // 3 operations total
        } catch (Exception $e) {
            $test_status = 'error';
            $total_time = 0;
            $set_time = 0;
            $get_time = 0;
            $delete_time = 0;
            $estimated_ops_per_second = 0;
            $retrieved = false;
        }
        
        return array(
            'time' => round($total_time, 3),
            'operations_completed' => 3,
            'total_operations' => 3,
            'set_time' => round($set_time * 1000, 2),
            'get_time' => round($get_time * 1000, 2),
            'delete_time' => round($delete_time * 1000, 2),
            'estimated_ops_per_second' => round($estimated_ops_per_second, 1),
            'test_approach' => 'database_safe',
            'data_verified' => ($retrieved === $test_data),
            'performance_tier' => self::classify_database_performance($total_time),
            'test_status' => $test_status
        );
    }
    
    /**
     * Classify object cache performance tier (POC exact method)
     * 
     * @param float $avg_operation_time Average operation time in seconds
     * @return string Performance tier classification
     */
    private static function classify_object_cache_performance($avg_operation_time) {
        if ($avg_operation_time < 0.001) { // < 1ms
            return 'enterprise'; // Redis/Memcached with excellent network
        } elseif ($avg_operation_time < 0.005) { // < 5ms
            return 'premium'; // Good object cache setup
        } elseif ($avg_operation_time < 0.010) { // < 10ms
            return 'standard'; // Basic object cache
        } else {
            return 'limited'; // Slow or overloaded cache
        }
    }
    
    /**
     * Classify database transient performance tier (POC exact method)
     * 
     * @param float $total_time Total operation time in seconds
     * @return string Performance tier classification
     */
    private static function classify_database_performance($total_time) {
        if ($total_time < 0.050) { // < 50ms total
            return 'excellent'; // Fast SSD database
        } elseif ($total_time < 0.100) { // < 100ms total
            return 'good'; // Standard database performance
        } elseif ($total_time < 0.200) { // < 200ms total
            return 'fair'; // Acceptable for small sites
        } else {
            return 'poor'; // Slow database or overloaded
        }
    }
    
    /**
     * Calculate WordPress score using POC method
     * 
     * @param array $wp_results WordPress sub-test results
     * @param float $total_time Total WordPress test time
     * @return int WordPress score (0-100)
     */
    private static function calculate_wordpress_score($wp_results, $total_time) {
        if ($total_time <= 0) {
            return 100;
        }
        
        // POC specification: WordPress operations are typically faster than pure CPU
        $optimal_time = 0.5;  // Excellent WordPress performance
        $poor_time = 8.0;     // Poor WordPress performance threshold
        
        if ($total_time <= $optimal_time) {
            return 100;
        }
        
        if ($total_time >= $poor_time) {
            return 10;
        }
        
        // POC specification: Smooth curve for WordPress operations
        $normalized = ($total_time - $optimal_time) / ($poor_time - $optimal_time);
        $score = 100 - (90 * pow($normalized, 0.75)); // Slightly more forgiving
        
        return max(10, min(100, round($score)));
    }
} 