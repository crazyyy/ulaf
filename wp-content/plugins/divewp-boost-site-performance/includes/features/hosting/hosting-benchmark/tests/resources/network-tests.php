<?php
/**
 * Network Performance Tests
 *
 * Replicates exact POC network capabilities test specifications with enhanced UX features.
 * Tests network connectivity and response times for hosting evaluation.
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
 * Resources Network Tests Class
 */
class DiveWP_Resources_Network_Tests {

    /**
     * Run network capabilities tests (exact POC replication)
     *
     * @param array $test_config POC test configuration
     * @return array Test results with enhanced UX data
     */
    public static function run($test_config = array()) {
        $start_time = microtime(true);
        
        // POC specification: Test network capabilities (2025-optimized for hosting safety)
        // Uses progressive testing approach:
        // 1. Single WordPress.org API test (essential for WP functionality)
        // 2. Minimal additional testing only if first succeeds
        // 3. Respects hosting security with proper delays
        
        $network_score = self::test_network_capabilities($test_config);
        $test_status = $network_score > 0 ? 'completed' : 'error';
        $total_time = microtime(true) - $start_time;
        
        // POC expects maximum 2 requests (WordPress API + reliability test)
        $expected_operations = 2;
        $completed_operations = $test_status === 'completed' ? $expected_operations : 0;
        
        // Calculate operations per second for UI display
        $operations_per_second = $total_time > 0 ? round($completed_operations / $total_time, 1) : 0;
        
        return array(
            'score' => $network_score,
            'total_time' => round($total_time, 3),
            'completed_operations' => $completed_operations,
            'total_operations' => $expected_operations,
            'operations_per_second' => $operations_per_second,
            'status' => $test_status,
            'timeout_reason' => $test_status === 'error' ? 'Network connectivity test failed to complete' : null,
            'network_score' => $network_score,
            'performance_interpretation' => DiveWP_Benchmark_Resources_Scoring::get_sub_test_performance_interpretation('network_tests', array(
                'score' => $network_score,
                'total_time' => $total_time,
                'completed_operations' => $completed_operations,
                'total_operations' => $expected_operations,
                'operations_per_second' => $operations_per_second,
                'status' => $test_status
            ))
        );
    }
    
    /**
     * Test network capabilities (POC exact method)
     * 2025-optimized for hosting safety
     * 
     * Uses progressive testing approach:
     * 1. Single WordPress.org API test (essential for WP functionality)
     * 2. Minimal additional testing only if first succeeds
     * 3. Respects hosting security with proper delays
     * 
     * @param array $test_config Test configuration (unused in POC)
     * @return int Network score (0-100)
     */
    private static function test_network_capabilities($test_config = array()) {
        $start_time = microtime(true);
        $network_results = array(
            'requests_successful' => 0,
            'total_response_time' => 0,
            'avg_response_time' => 0,
            'wordpress_api_working' => false,
            'test_approach' => '2025_hosting_safe'
        );
        
        // POC specification: Level 1 - Essential WordPress.org connectivity test
        $wordpress_api_test = self::test_wordpress_api_connectivity();
        $network_results['wordpress_api_working'] = $wordpress_api_test['success'];
        
        if ($wordpress_api_test['success']) {
            $network_results['requests_successful']++;
            $network_results['total_response_time'] += $wordpress_api_test['response_time'];
            
            // POC specification: Only proceed to additional tests if WordPress API works
            // This prevents unnecessary external requests on restricted hosts
            
            // POC specification: Level 2 - One additional reliability test (hosting-safe)
            sleep(2); // 2-second delay for hosting security compliance
            
            $reliability_test = self::test_basic_http_reliability();
            if ($reliability_test['success']) {
                $network_results['requests_successful']++;
                $network_results['total_response_time'] += $reliability_test['response_time'];
            }
        }
        
        // Calculate performance metrics (POC method)
        if ($network_results['requests_successful'] > 0) {
            $network_results['avg_response_time'] = $network_results['total_response_time'] / $network_results['requests_successful'];
        }
        
        // Score calculation for 2025 hosting environment (POC method)
        $network_score = self::calculate_network_score_2025($network_results);
        
        return max(20, min(100, $network_score));
    }
    
    /**
     * Test essential WordPress.org API connectivity (POC exact method)
     * 
     * @return array Test results
     */
    private static function test_wordpress_api_connectivity() {
        $request_start = microtime(true);
        
        // POC specification: Test WordPress.org API - essential for plugin/theme updates
        $response = wp_remote_get('https://api.wordpress.org/core/version-check/1.7/', array(
            'timeout' => 8,
            'sslverify' => true,
            'user-agent' => 'WordPress/' . get_bloginfo('version') . '; ' . home_url()
        ));
        
        $response_time = microtime(true) - $request_start;
        
        $success = (!is_wp_error($response) && 
                   wp_remote_retrieve_response_code($response) === 200 &&
                   !empty(wp_remote_retrieve_body($response)));
        
        return array(
            'success' => $success,
            'response_time' => $response_time,
            'url_tested' => 'WordPress.org API',
            'essential_for_wp' => true
        );
    }
    
    /**
     * Test basic HTTP reliability with hosting-safe endpoint (POC exact method)
     * 
     * @return array Test results
     */
    private static function test_basic_http_reliability() {
        $request_start = microtime(true);
        
        // POC specification: Use a simple, hosting-friendly endpoint
        $response = wp_remote_get('https://httpbin.org/status/200', array(
            'timeout' => 5,
            'sslverify' => false, // Some hosts have SSL issues
            'user-agent' => 'DiveWP-Benchmark/2.0'
        ));
        
        $response_time = microtime(true) - $request_start;
        
        $success = (!is_wp_error($response) && 
                   wp_remote_retrieve_response_code($response) === 200);
        
        return array(
            'success' => $success,
            'response_time' => $response_time,
            'url_tested' => 'HTTP reliability test',
            'essential_for_wp' => false
        );
    }
    
    /**
     * Calculate network score with smooth curve and simplified approach (POC exact method)
     * 
     * Focuses on essential connectivity and response times for hosting evaluation.
     * 
     * @param array $network_results Network test results
     * @return int Network performance score (0-100)
     */
    private static function calculate_network_score_2025($network_results) {
        $base_score = 30; // Conservative baseline
        
        // POC specification: WordPress.org API connectivity is critical for hosting functionality
        if ($network_results['wordpress_api_working']) {
            $base_score += 40; // Major boost for essential connectivity
        }
        
        // POC specification: Connection success scoring with smooth progression
        $success_rate = $network_results['requests_successful'] / max(1, 2); // Expect 2 max requests
        $base_score += ($success_rate * 20);
        
        // POC specification: Response time scoring with smooth curve
        if ($network_results['avg_response_time'] > 0) {
            $response_time = $network_results['avg_response_time'];
            
            if ($response_time <= 1.0) {
                $base_score += 10; // Excellent response time
            } elseif ($response_time <= 3.0) {
                // Smooth decline from 10 to 5 points
                $base_score += (10 - (($response_time - 1.0) / 2.0) * 5);
            } elseif ($response_time <= 8.0) {
                // Smooth decline from 5 to 0 points
                $base_score += (5 - (($response_time - 3.0) / 5.0) * 5);
            } else {
                $base_score -= 5; // Penalty for very slow responses
            }
        }
        
        return max(10, min(100, round($base_score)));
    }
} 