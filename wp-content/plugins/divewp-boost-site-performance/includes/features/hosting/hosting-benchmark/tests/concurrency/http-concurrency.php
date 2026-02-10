<?php
/**
 * HTTP Concurrency Test
 *
 * Tests HTTP request handling under concurrent load by making
 * 8 simultaneous HTTP requests and analyzing response times.
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
 * HTTP Concurrency Test Class
 */
class DiveWP_HTTP_Concurrency_Test {

    /**
     * Test configuration
     */
    const CONCURRENT_REQUESTS = 8;
    const REQUEST_TIMEOUT = 10; // 10 seconds per request
    const MAX_TEST_TIME = 30; // 30 seconds max
    const MAX_RETRIES = 2;

    /**
     * Run the HTTP concurrency test
     *
     * @return array Test results
     */
    public static function run() {
        $start_time = microtime(true);
        $test_name = 'HTTP Concurrency';
        
        $result = array(
            'status' => 'completed',
            'test_name' => $test_name,
            'total_time' => 0,
            'requests_completed' => 0,
            'successful_requests' => 0,
            'failed_requests' => 0,
            'avg_response_time' => 0,
            'max_response_time' => 0,
            'min_response_time' => 0,
            'concurrent_efficiency' => 0,
            'operations_per_second' => 0,
            'score' => 0,
            'rating' => 'unknown',
            'interpretation' => '',
            'error_details' => array(),
            'timestamp' => current_time('mysql')
        );

        try {
            // Set appropriate time limit
            $time_limit = get_transient('divewp_benchmark_time_limit') ?: 54;
            // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged -- BENCHMARK REQUIREMENT: Extended time limit needed for server stress testing
            set_time_limit($time_limit);

            // Launch true parallel workers via curl_multi using REST worker
            require_once __DIR__ . '/helpers.php';
            $token = wp_generate_password(16, false, false);
            set_transient('divewp_concurrency_worker_token', $token, MINUTE_IN_SECONDS);

            $parallel = 8;
            $runtime = 8.0; // seconds
            $pool = DiveWP_Concurrency_MultiRunner::run('http', $parallel, $runtime, $token);
            delete_transient('divewp_concurrency_worker_token');

            // Translate pool results
            $completed_requests = $pool['success_count'] + $pool['fail_count'];
            $avg_response_time = 0;
            $max_response_time = 0;
            $min_response_time = 0;
            if (!empty($pool['durations'])) {
                $avg_response_time = array_sum($pool['durations']) / count($pool['durations']);
                $max_response_time = max($pool['durations']);
                $min_response_time = min($pool['durations']);
            }

            $test_result = array(
                'requests_completed' => $completed_requests,
                'successful_requests' => $pool['success_count'],
                'failed_requests' => $pool['fail_count'],
                'avg_response_time' => round($avg_response_time, 3),
                'max_response_time' => round($max_response_time, 3),
                'min_response_time' => round($min_response_time, 3),
                'response_times' => $pool['durations'],
                // status fields
                'test_status' => 'completed',
                'completed_operations' => $completed_requests,
                'total_operations' => $parallel * ($runtime / max(0.001, $avg_response_time ?: 0.5)),
                'timeout_reason' => ''
            );

            // Merge
            $result = array_merge($result, $test_result);
            
            // Calculate final metrics
            $total_time = microtime(true) - $start_time;
            $result['total_time'] = $total_time;
            
            if ($total_time > 0 && $result['requests_completed'] > 0) {
                $result['operations_per_second'] = round($result['requests_completed'] / $total_time, 2);
                // Efficiency relative to target: parallel concurrency baseline 6 req/sec is excellent
                $target = 6.0 * 8; // 6 req/sec per slot × 8 slots baseline
                $result['concurrent_efficiency'] = $target > 0 ? round(min(120, ($result['operations_per_second'] / $target) * 100), 2) : 0;
            }

            // Calculate score and rating
            $result['score'] = self::calculate_score($result);
            $result['rating'] = self::get_rating($result['score']);
            $result['interpretation'] = self::get_interpretation($result);
            
            // Ensure status fields are set properly
            if (!isset($result['test_status'])) {
                $result['test_status'] = 'completed';
            }
            if (!isset($result['completed_operations'])) {
                $result['completed_operations'] = $result['requests_completed'] ?? 0;
            }
            if (!isset($result['total_operations'])) {
                $result['total_operations'] = self::CONCURRENT_REQUESTS;
            }
            if (!isset($result['timeout_reason'])) {
                $result['timeout_reason'] = '';
            }

            // ENHANCED UX: Add performance interpretation data using scoring class
            require_once DIVEWP_PLUGIN_DIR . 'includes/features/hosting/hosting-benchmark/tests/concurrency/scoring.php';
            $result['performance_interpretation'] = DiveWP_Benchmark_Concurrency_Scoring::get_sub_test_performance_interpretation('http_concurrency', $result);

        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("DiveWP HTTP Concurrency Error: " . $e->getMessage()); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- BENCHMARK REQUIREMENT: minimal debug logging gated by WP_DEBUG
            }
            
            $result['status'] = 'error';
            $result['message'] = $e->getMessage();
            $result['total_time'] = microtime(true) - $start_time;
            $result['score'] = 0;
            $result['rating'] = 'error';
            $result['interpretation'] = sprintf(
                // translators: %1$s is the specific error message explaining why the HTTP concurrency test failed
                __('HTTP concurrency test failed: %1$s', 'divewp-boost-site-performance'), 
                $e->getMessage()
            );
            
            // Add status fields for error case
            $result['test_status'] = 'error';
            $result['completed_operations'] = 0;
            $result['total_operations'] = self::CONCURRENT_REQUESTS;
            $result['timeout_reason'] = sprintf(
                // translators: %1$s is the specific error message explaining why the test failed
                __('Test failed with error: %1$s', 'divewp-boost-site-performance'), 
                $e->getMessage()
            );
        }

        return $result;
    }

    /**
     * Get test URLs for HTTP requests
     *
     * @return array Array of test URLs
     */
    private static function get_test_urls() {
        $urls = array();
        
        // Use current site URLs for realistic testing
        $site_url = home_url();
        $admin_url = admin_url();
        
        // Test different endpoints
        $test_endpoints = array(
            '/',                          // Homepage
            '/wp-admin/admin-ajax.php',   // AJAX endpoint
            '/wp-json/wp/v2/',           // REST API
            '/wp-login.php',             // Login page
            '/feed/',                    // RSS feed
            '/sitemap.xml',              // Sitemap (if exists)
            '/robots.txt',               // Robots.txt
            '/wp-admin/'                 // Admin area
        );
        
        foreach ($test_endpoints as $endpoint) {
            $full_url = rtrim($site_url, '/') . $endpoint;
            
            // Validate URL
            if (filter_var($full_url, FILTER_VALIDATE_URL)) {
                $urls[] = $full_url;
            }
        }
        
        // If we don't have enough URLs, add external fallbacks
        if (count($urls) < self::CONCURRENT_REQUESTS) {
            $external_urls = array(
                'https://httpbin.org/delay/1',
                'https://httpbin.org/get',
                'https://jsonplaceholder.typicode.com/posts/1',
                'https://api.github.com'
            );
            
            foreach ($external_urls as $external_url) {
                if (count($urls) < self::CONCURRENT_REQUESTS) {
                    $urls[] = $external_url;
                }
            }
        }
        
        // Ensure we have exactly the number of URLs we need
        return array_slice($urls, 0, self::CONCURRENT_REQUESTS);
    }

    /**
     * Execute concurrent HTTP requests
     *
     * @param array $urls Array of URLs to test
     * @param float $start_time Test start time
     * @return array Request results
     */
    private static function execute_concurrent_http_requests($urls, $start_time) {
        $successful_requests = 0;
        $failed_requests = 0;
        $response_times = array();
        $error_details = array();
        
        // Prepare requests for concurrent execution
        $request_data = array();
        for ($i = 0; $i < self::CONCURRENT_REQUESTS; $i++) {
            $url = $urls[$i % count($urls)]; // Cycle through URLs if needed
            $request_data[] = array(
                'url' => $url,
                'args' => array(
                    'timeout' => self::REQUEST_TIMEOUT,
                    'redirection' => 2,
                    'httpversion' => '1.1',
                    'user-agent' => 'DiveWP Benchmark/1.0',
                    'blocking' => true,
                    'headers' => array(),
                    'cookies' => array(),
                    'body' => null,
                    'compress' => false,
                    'decompress' => true,
                    'sslverify' => false
                )
            );
        }
        
        // Execute requests with timeout checking
        foreach ($request_data as $index => $request) {
                    // Check timeout
        if ((microtime(true) - $start_time) > self::MAX_TEST_TIME) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("DiveWP HTTP Concurrency: Timeout reached, processed " . ($successful_requests + $failed_requests) . " requests"); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- BENCHMARK REQUIREMENT: minimal debug logging gated by WP_DEBUG
            }
            break;
        }
            
            $request_start = microtime(true);
            
            try {
                // Make HTTP request
                $response = wp_remote_get($request['url'], $request['args']);
                $request_time = microtime(true) - $request_start;
                
                if (is_wp_error($response)) {
                    $failed_requests++;
                    $error_details[] = array(
                        'url' => $request['url'],
                        'error' => $response->get_error_message(),
                        'index' => $index
                    );
                } else {
                    $response_code = wp_remote_retrieve_response_code($response);
                    
                    if ($response_code >= 200 && $response_code < 400) {
                        $successful_requests++;
                        $response_times[] = $request_time;
                    } else {
                        $failed_requests++;
                        $error_details[] = array(
                            'url' => $request['url'],
                            'error' => "HTTP {$response_code}",
                            'index' => $index
                        );
                    }
                }
                
            } catch (Exception $e) {
                $failed_requests++;
                $error_details[] = array(
                    'url' => $request['url'],
                    'error' => $e->getMessage(),
                    'index' => $index
                );
            }
            
            // Small delay between requests to avoid overwhelming the server
            usleep(100000); // 100ms
        }
        
        // Calculate response time statistics
        $avg_response_time = 0;
        $max_response_time = 0;
        $min_response_time = 0;
        
        if (!empty($response_times)) {
            $avg_response_time = array_sum($response_times) / count($response_times);
            $max_response_time = max($response_times);
            $min_response_time = min($response_times);
        }
        
        // Determine test status and timeout reason
        $completed_requests = $successful_requests + $failed_requests;
            $test_status = 'completed';
            $timeout_reason = '';
            if ($completed_requests < self::CONCURRENT_REQUESTS) {
                $test_status = 'timeout';
                $timeout_reason = __('Test timed out before completing all requests.', 'divewp-boost-site-performance');
            } elseif ($failed_requests > ($successful_requests / 2)) {
                $test_status = 'partial';
                $timeout_reason = __('High failure rate during concurrent requests.', 'divewp-boost-site-performance');
            }

        return array(
            'requests_completed' => $completed_requests,
            'successful_requests' => $successful_requests,
            'failed_requests' => $failed_requests,
            'avg_response_time' => round($avg_response_time, 3),
            'max_response_time' => round($max_response_time, 3),
            'min_response_time' => round($min_response_time, 3),
            'response_times' => $response_times,
            'error_details' => $error_details,
            // New status fields for UX enhancement
            'test_status' => $test_status,
            'completed_operations' => $completed_requests,
            'total_operations' => self::CONCURRENT_REQUESTS,
            'timeout_reason' => $timeout_reason
        );
    }

    /**
     * Calculate performance score
     *
     * @param array $result Test results
     * @return float Score from 0 to 100
     */
    private static function calculate_score($result) {
        if ($result['status'] !== 'completed' || $result['requests_completed'] === 0) {
            return 0;
        }
        
        $success_rate = ($result['successful_requests'] / $result['requests_completed']) * 100;
        $avg_response_time = $result['avg_response_time'];
        $efficiency = $result['concurrent_efficiency'];
        
        // Base score from success rate (60% weight)
        $success_score = $success_rate * 0.6;
        
        // Response time score (30% weight)
        $response_score = 0;
        if ($avg_response_time <= 0.5) {
            $response_score = 30; // Excellent
        } elseif ($avg_response_time <= 1.0) {
            $response_score = 25; // Good
        } elseif ($avg_response_time <= 2.0) {
            $response_score = 20; // Fair
        } elseif ($avg_response_time <= 5.0) {
            $response_score = 15; // Poor
        } else {
            $response_score = 10; // Critical
        }
        
        // Efficiency bonus (10% weight)
        $efficiency_score = ($efficiency / 100) * 10;
        
        $final_score = $success_score + $response_score + $efficiency_score;
        
        // Penalty for failed requests
        if ($result['failed_requests'] > 0) {
            $failure_penalty = ($result['failed_requests'] / $result['requests_completed']) * 20;
            $final_score = max(0, $final_score - $failure_penalty);
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
            return __('HTTP concurrency test could not be completed.', 'divewp-boost-site-performance');
        }
        
        $score = $result['score'];
        $success_rate = ($result['successful_requests'] / $result['requests_completed']) * 100;
        $avg_response_time = $result['avg_response_time'];
        
        if ($score >= 90) {
            return sprintf(
                // translators: %1$d is the number of successful HTTP requests, %2$d is the total requests completed, %3$.3f is the average response time in seconds
                __('Excellent HTTP concurrency performance! %1$d/%2$d requests successful with %3$.3fs average response time.', 'divewp-boost-site-performance'),
                $result['successful_requests'],
                $result['requests_completed'],
                $avg_response_time
            );
        } elseif ($score >= 75) {
            return sprintf(
                // translators: %1$.0f is the success rate percentage, %2$.3f is the average response time in seconds
                __('Good HTTP handling under load. %1$.0f%% success rate with %2$.3fs average response time.', 'divewp-boost-site-performance'),
                $success_rate,
                $avg_response_time
            );
        } elseif ($score >= 60) {
            return sprintf(
                // translators: %1$d is the number of successful requests completed, %2$.3f is the response time in seconds indicating performance issues
                __('Fair HTTP concurrency. %1$d requests completed but response time was %2$.3fs. Consider optimization.', 'divewp-boost-site-performance'),
                $result['successful_requests'],
                $avg_response_time
            );
        } elseif ($score >= 40) {
            return sprintf(
                // translators: %1$d is the number of failed HTTP requests, %2$d is the total requests completed during testing
                __('Poor HTTP performance under load. %1$d/%2$d requests failed. Server may struggle with concurrent users.', 'divewp-boost-site-performance'),
                $result['failed_requests'],
                $result['requests_completed']
            );
        } else {
            return sprintf(
                // translators: %1$d is the number of failed HTTP requests, %2$d is the total requests completed, indicating critical server performance issues
                __('Critical HTTP concurrency issues. High failure rate (%1$d/%2$d failed) indicates severe server limitations.', 'divewp-boost-site-performance'),
                $result['failed_requests'],
                $result['requests_completed']
            );
        }
    }
} 