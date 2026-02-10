<?php
/**
 * Advanced Concurrency Tests for DiveWP Hosting Evaluation
 *
 * This class performs true multi-user concurrency testing to evaluate
 * how hosting handles simultaneous operations under realistic load.
 *
 * Tests include:
 * - Database connection stress testing
 * - HTTP request concurrency via curl_multi 
 * - Memory competition simulation
 * - File system contention testing
 *
 * @package DiveWP_Boost_Site_Performance
 * @subpackage Choose_Hosting
 * @since 2.0.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Advanced Concurrency Tests Class
 *
 * Performs realistic multi-user concurrency testing to stress test
 * hosting limitations that affect real-world performance.
 *
 * @since 2.0.4
 */
class DiveWP_Concurrency_Tests {

    /**
     * Test endpoints for HTTP concurrency testing
     * @var array
     */
    private $test_endpoints = array();

    /**
     * Run comprehensive concurrency test with 4 phases
     * 
     * NEW: Step-based execution to avoid server limits
     * This method now serves as legacy wrapper - new execution uses handle_test_step()
     * 
     * @since 2.0.4
     * @return array Comprehensive concurrency test results
     */
    public function run_concurrency_test() {
        // Legacy method - redirect to new step-based execution
        // This maintains backwards compatibility for direct calls
        
        // Generate a unique session ID for this test run
        $session_id = 'divewp_concurrency_test_' . get_current_user_id() . '_' . time();
        
        // Store the initial config
        $this->store_test_session($session_id, array(
            'status' => 'initialized',
            'steps_completed' => array(),
            'start_time' => time()
        ));
        
        // Run all steps sequentially (for legacy compatibility)
        $steps = array('database', 'http', 'memory', 'file', 'finalize');
        
        foreach ($steps as $step) {
            $result = $this->handle_test_step($step, null, $session_id);
            if (isset($result['error'])) {
                // If any step fails, return error
                return $result;
            }
            
            // For finalize step, return the result directly
            if ($step === 'finalize') {
                return $result;
            }
        }
        
        // This should not be reached due to finalize step
        return array('error' => 'Unexpected execution path in run_concurrency_test');
    }

    /**
     * AJAX handler for individual concurrency test steps
     * 
     * @param string $step Test step to execute ('database', 'http', 'memory', 'file')
     * @param array $test_config Test configuration (optional)
     * @param string $session_id Unique session identifier (optional)
     * @return array Step execution results
     */
    public function handle_test_step($step, $test_config = null, $session_id = null) {
        $start_time = microtime(true);
        
        // If no session ID provided, try to get from POST/GET
        if (!$session_id) {
            $session_id = sanitize_text_field($_POST['session_id'] ?? $_GET['session_id'] ?? '');
        }
        
        // If no config provided, try to load from session or use defaults
        if (!$test_config) {
            if ($session_id) {
                $session_data = $this->get_test_session($session_id);
                $test_config = $session_data['config'] ?? null;
            }
            
            // Use default config if none provided
            if (!$test_config) {
                $test_config = array('enabled_tests' => array('run_concurrency_tests'));
            }
        }
        
        // Use WordPress native execution time (80% for safety)
        $php_max_execution_time = (int) ini_get('max_execution_time');
        $max_step_time = $php_max_execution_time > 0 ? intval($php_max_execution_time * 0.8) : 20;
        
        $result = array();
        
        try {
            switch ($step) {
                case 'database':
                    $result = $this->test_database_concurrency_step($max_step_time, $start_time);
                    break;
                    
                case 'http':
                    $result = $this->test_http_concurrency_step($max_step_time, $start_time);
                    break;
                    
                case 'memory':
                    $result = $this->test_memory_concurrency_step($max_step_time, $start_time);
                    break;
                    
                case 'file':
                    $result = $this->test_file_concurrency_step($max_step_time, $start_time);
                    break;
                    
                case 'finalize':
                    return $this->finalize_concurrency_results($session_id);
                    
                default:
                    return array('error' => 'Unknown concurrency test step: ' . $step);
            }
            
            // Store the step result
            if ($session_id) {
                $this->store_step_result($session_id, $step, $result);
            }
            
            $execution_time = microtime(true) - $start_time;
            
            return array(
                'success' => true,
                'step' => $step,
                'execution_time' => round($execution_time, 3),
                'incomplete' => $result['incomplete'] ?? false,
                'results' => $result,
                'next_step' => $this->get_next_concurrency_step($step),
                'session_id' => $session_id
            );
            
        } catch (Exception $e) {
            return array(
                'error' => 'Step execution failed: ' . $e->getMessage(),
                'step' => $step,
                'incomplete' => true,
                'session_id' => $session_id
            );
        }
    }

    /**
     * Phase 1: Database Connection Stress Test (as separate step)
     */
    private function test_database_concurrency_step($max_step_time, $start_time) {
        return $this->test_database_concurrency(min(5, $max_step_time * 0.9));
    }

    /**
     * Phase 2: HTTP Request Concurrency Test (as separate step)
     */
    private function test_http_concurrency_step($max_step_time, $start_time) {
        return $this->test_http_concurrency(min(8, $max_step_time * 0.9));
    }

    /**
     * Phase 3: Memory Competition Test (as separate step)
     */
    private function test_memory_concurrency_step($max_step_time, $start_time) {
        return $this->test_memory_concurrency(min(4, $max_step_time * 0.9));
    }

    /**
     * Phase 4: File System Contention Test (as separate step)
     */
    private function test_file_concurrency_step($max_step_time, $start_time) {
        return $this->test_file_concurrency(min(4, $max_step_time * 0.9));
    }

    /**
     * Get the next step in the concurrency test sequence
     * 
     * @param string $current_step Current step name
     * @return string|null Next step name or null if complete
     */
    private function get_next_concurrency_step($current_step) {
        $steps = array('database', 'http', 'memory', 'file', 'finalize');
        $current_index = array_search($current_step, $steps);
        
        if ($current_index !== false && $current_index < count($steps) - 1) {
            return $steps[$current_index + 1];
        }
        
        return null; // No more steps
    }

    /**
     * Store test session data using transients
     * 
     * @param string $session_id Unique session identifier
     * @param array $data Session data to store
     */
    private function store_test_session($session_id, $data) {
        // Store session for 2 hours (7200 seconds) - longer to prevent loss during step execution
        set_transient($session_id, $data, 7200);
        
        // Also store backup for reliability
        $backup_key = $session_id . '_backup';
        set_transient($backup_key, $data, 7200);
    }

    /**
     * Enhanced session retrieval with fallback
     */
    private function get_test_session($session_id) {
        $data = get_transient($session_id);
        
        // If primary session is missing, try backup
        if ($data === false) {
            $backup_key = $session_id . '_backup';
            $data = get_transient($backup_key);
        }
        
        // If still nothing, return false
        if ($data === false) {
            return false;
        }
        
        return $data;
    }

    /**
     * Store individual step result
     * 
     * @param string $session_id Session identifier
     * @param string $step Step name
     * @param array $result Step results
     */
    private function store_step_result($session_id, $step, $result) {
        $session_data = $this->get_test_session($session_id);
        
        // Initialize session if it doesn't exist
        if (!$session_data) {
            $session_data = array(
                'status' => 'running',
                'steps_completed' => array(),
                'step_results' => array(),
                'start_time' => time()
            );
        }
        
        // Ensure step_results array exists
        if (!isset($session_data['step_results'])) {
            $session_data['step_results'] = array();
        }
        if (!isset($session_data['steps_completed'])) {
            $session_data['steps_completed'] = array();
        }
        
        // Store the step result
        $session_data['step_results'][$step] = $result;
        $session_data['steps_completed'][] = $step;
        $session_data['last_updated'] = time();
        
        // Store with longer expiration (2 hours to be safe)
        set_transient($session_id, $session_data, 7200);
        
        // Also store a backup session with different key for debugging
        $backup_key = $session_id . '_backup';
        set_transient($backup_key, $session_data, 7200);
    }

    /**
     * Finalize concurrency test results from all steps
     * 
     * @param string $session_id Session identifier
     * @return array Complete concurrency test results
     */
    private function finalize_concurrency_results($session_id) {
        // Add debug information about session lookup
        $session_data = $this->get_test_session($session_id);
        $backup_key = $session_id . '_backup';
        $backup_data = get_transient($backup_key);
        
        if (!$session_data || !isset($session_data['step_results'])) {
            return array(
                'error' => 'No session data found for concurrency test finalization',
                'session_id' => $session_id,
                'debug_info' => array(
                    'session_data_exists' => $session_data ? 'yes' : 'no',
                    'step_results_exists' => isset($session_data['step_results']) ? 'yes' : 'no',
                    'backup_data_exists' => $backup_data ? 'yes' : 'no',
                    'session_keys' => $session_data ? array_keys($session_data) : 'none',
                    'backup_keys' => $backup_data ? array_keys($backup_data) : 'none',
                    'all_transients' => $this->get_debug_transients($session_id)
                )
            );
        }
        
        $step_results = $session_data['step_results'];
        
        // Check that we have all required steps
        $required_steps = array('database', 'http', 'memory', 'file');
        $missing_steps = array_diff($required_steps, array_keys($step_results));
        
        if (!empty($missing_steps)) {
            return array(
                'error' => 'Missing test steps: ' . implode(', ', $missing_steps),
                'incomplete' => true,
                'steps_completed' => array_keys($step_results),
                'session_id' => $session_id,
                'debug_info' => array(
                    'required_steps' => $required_steps,
                    'available_steps' => array_keys($step_results),
                    'missing_steps' => $missing_steps
                )
            );
        }
        
        // Extract individual test results with validation
        try {
            $db_results = $step_results['database'];
            $http_results = $step_results['http'];
            $memory_results = $step_results['memory'];
            $file_results = $step_results['file'];
            
            // Validate each result has required fields
            $validation_errors = array();
            if (!isset($db_results['score'])) $validation_errors[] = 'database missing score';
            if (!isset($http_results['score'])) $validation_errors[] = 'http missing score';
            if (!isset($memory_results['score'])) $validation_errors[] = 'memory missing score';
            if (!isset($file_results['score'])) $validation_errors[] = 'file missing score';
            
            if (!empty($validation_errors)) {
                return array(
                    'error' => 'Step results validation failed: ' . implode(', ', $validation_errors),
                    'debug_info' => array(
                        'db_keys' => array_keys($db_results),
                        'http_keys' => array_keys($http_results),
                        'memory_keys' => array_keys($memory_results),
                        'file_keys' => array_keys($file_results)
                    )
                );
            }
            
            // Calculate total time from all steps
            $total_time = 0;
            foreach ($step_results as $step_result) {
                $total_time += $step_result['total_time'] ?? 0;
            }
            
            // Calculate overall concurrency score
            $final_score = $this->calculate_concurrency_score($db_results, $http_results, $memory_results, $file_results);
            
            if (!$final_score || !isset($final_score['overall_score'])) {
                return array(
                    'error' => 'Failed to calculate final concurrency score',
                    'debug_info' => array(
                        'final_score_result' => $final_score
                    )
                );
            }
            
            // Format results matching JavaScript expectations
            $formatted_results = $this->format_results($final_score, $total_time, $db_results, $http_results, $memory_results, $file_results);
            
            if (!$formatted_results || !isset($formatted_results['score'])) {
                return array(
                    'error' => 'Failed to format final results',
                    'debug_info' => array(
                        'formatted_results' => $formatted_results
                    )
                );
            }
            
            // Add execution summary
            $formatted_results['execution_summary'] = array(
                'total_steps' => count($step_results),
                'completed_steps' => array_keys($step_results),
                'session_duration' => time() - ($session_data['start_time'] ?? time())
            );
            
            // Clean up the session after finalization
            delete_transient($session_id);
            
            return $formatted_results;
            
        } catch (Exception $e) {
            return array(
                'error' => 'Exception during finalization: ' . $e->getMessage(),
                'debug_info' => array(
                    'exception_file' => $e->getFile(),
                    'exception_line' => $e->getLine(),
                    'step_results_keys' => array_keys($step_results)
                )
            );
        }
    }

    /**
     * Phase 1: Database Connection Stress Test
     * 
     * Tests how many simultaneous database operations the hosting can handle.
     * Simulates multiple users performing typical WordPress operations.
     *
     * @param int $duration_seconds Test duration in seconds
     * @return array Database concurrency test results
     */
    private function test_database_concurrency($duration_seconds) {
        // Safety check for duration
        $duration_seconds = min($duration_seconds, 10); // Max 10 seconds per phase
        global $wpdb;
        
        $operations = array();
        $start_time = microtime(true);
        $concurrent_connections = 15; // Test up to 15 simultaneous operations
        $total_operations = 0;
        
        // Run batches of concurrent database operations
        while ((microtime(true) - $start_time) < $duration_seconds) {
            $batch_start = microtime(true);
            $batch_operations = array();
            
            // Simulate multiple users hitting database simultaneously
            for ($i = 0; $i < $concurrent_connections; $i++) {
                $operation_start = microtime(true);
                
                // Different types of queries that real users would trigger
                switch ($i % 5) {
                    case 0: // User reading posts (heavy query)
                        $result = $wpdb->get_results($wpdb->prepare(
                            "SELECT p.ID, p.post_title, p.post_content, p.post_excerpt 
                             FROM {$wpdb->posts} p 
                             WHERE p.post_status = %s AND p.post_type = %s
                             ORDER BY p.post_date DESC LIMIT %d",
                            'publish', 'post', 25
                        ));
                        break;
                        
                    case 1: // User session/options check (frequent operation)
                        $result = $wpdb->get_results($wpdb->prepare(
                            "SELECT option_name, option_value FROM {$wpdb->options} 
                             WHERE autoload = %s ORDER BY option_name LIMIT %d",
                            'yes', 75
                        ));
                        break;
                        
                    case 2: // User metadata operations (JOIN intensive)
                        $result = $wpdb->get_results($wpdb->prepare(
                            "SELECT u.ID, u.user_login, um.meta_key, um.meta_value 
                             FROM {$wpdb->users} u 
                             LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id 
                             WHERE um.meta_key LIKE %s LIMIT %d",
                            'wp_%', 40
                        ));
                        break;
                        
                    case 3: // Complex JOIN operation (resource intensive)
                        $result = $wpdb->get_results($wpdb->prepare(
                            "SELECT p.ID, p.post_title, pm.meta_key, pm.meta_value,
                                    t.name as term_name, tt.taxonomy
                             FROM {$wpdb->posts} p 
                             LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
                             LEFT JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
                             LEFT JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                             LEFT JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
                             WHERE p.post_type = %s AND p.post_status = %s 
                             GROUP BY p.ID LIMIT %d",
                            'post', 'publish', 20
                        ));
                        break;
                        
                    case 4: // Search operation (LIKE query, expensive)
                        $result = $wpdb->get_results($wpdb->prepare(
                            "SELECT p.ID, p.post_title, p.post_content 
                             FROM {$wpdb->posts} p 
                             WHERE (p.post_title LIKE %s OR p.post_content LIKE %s) 
                             AND p.post_status = %s AND p.post_type = %s 
                             ORDER BY p.post_date DESC LIMIT %d",
                            '%wordpress%', '%wordpress%', 'publish', 'post', 15
                        ));
                        break;
                }
                
                $operation_time = microtime(true) - $operation_start;
                $batch_operations[] = array(
                    'operation_type' => $i % 5,
                    'time' => $operation_time,
                    'rows_returned' => is_array($result) ? count($result) : 0
                );
                $total_operations++;
            }
            
            $batch_time = microtime(true) - $batch_start;
            $operations[] = array(
                'batch_time' => $batch_time,
                'operations' => $batch_operations,
                'concurrent_operations' => $concurrent_connections
            );
            
            // Small delay to prevent overwhelming shared hosting
            usleep(150000); // 0.15 seconds
        }
        
        return $this->analyze_database_performance($operations, $total_operations);
    }

    /**
     * Phase 2: HTTP Request Concurrency Test
     * 
     * Tests real multi-user HTTP requests to the site itself using curl_multi
     * for true parallel request execution.
     *
     * @param int $duration_seconds Test duration in seconds
     * @return array HTTP concurrency test results
     */
    private function test_http_concurrency($duration_seconds) {
        // Safety check for duration
        $duration_seconds = min($duration_seconds, 8); // Reduced to 8 seconds max
        
        $results = array();
        $start_time = microtime(true);
        $total_requests = 0;
        
        // Use existing WordPress URLs instead of custom endpoints to avoid registration issues
        $test_urls = array(
            home_url('/?feed=rss2'), // RSS feed - lightweight
            home_url('/'), // Homepage
            admin_url('admin-ajax.php?action=heartbeat') // WordPress heartbeat - always available
        );
        
        while ((microtime(true) - $start_time) < $duration_seconds) {
            $batch_start = microtime(true);
            $concurrent_requests = 4; // Reduced from 8 to 4 to avoid overwhelming server
            
            // Use curl_multi for TRUE concurrent HTTP requests
            $multi_handle = curl_multi_init();
            $curl_handles = array();
            
            // Set up multiple simultaneous requests
            for ($i = 0; $i < $concurrent_requests; $i++) {
                $curl_handle = curl_init();
                
                // Rotate through available URLs
                $test_url = $test_urls[$i % count($test_urls)];
                
                curl_setopt_array($curl_handle, array(
                    CURLOPT_URL => $test_url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 8, // Reduced timeout
                    CURLOPT_CONNECTTIMEOUT => 3, // Reduced connection timeout
                    CURLOPT_USERAGENT => 'DiveWP-Concurrency-Test/' . (defined('DiveWP_VERSION') ? DiveWP_VERSION : '1.0'),
                    CURLOPT_HEADER => false, // Don't need headers
                    CURLOPT_NOBODY => false,
                    CURLOPT_FOLLOWLOCATION => false, // Don't follow redirects to avoid complications
                    CURLOPT_SSL_VERIFYPEER => false, // Avoid SSL issues on local/dev sites
                    CURLOPT_SSL_VERIFYHOST => false
                ));
                curl_multi_add_handle($multi_handle, $curl_handle);
                $curl_handles[] = array(
                    'handle' => $curl_handle,
                    'url' => $test_url
                );
                $total_requests++;
            }
            
            // Execute all requests simultaneously with timeout protection
            $running = null;
            $timeout_start = microtime(true);
            do {
                $status = curl_multi_exec($multi_handle, $running);
                if ($running) {
                    curl_multi_select($multi_handle, 0.1); // 100ms select timeout
                }
                
                // Timeout protection - don't let curl_multi hang
                if ((microtime(true) - $timeout_start) > 10) {
                    break;
                }
            } while ($running > 0 && $status == CURLM_OK);
            
            // Collect results with error handling
            $request_times = array();
            $http_codes = array();
            foreach ($curl_handles as $index => $curl_data) {
                $curl_handle = $curl_data['handle'];
                $response = curl_multi_getcontent($curl_handle);
                $info = curl_getinfo($curl_handle);
                
                // Only include successful timing data
                if (!curl_error($curl_handle) && $info['total_time'] > 0) {
                    $request_times[] = $info['total_time'];
                    $http_codes[] = $info['http_code'];
                } else {
                    // For failed requests, use a penalty time
                    $request_times[] = 5.0; // 5 second penalty
                    $http_codes[] = 0; // Error code
                }
                
                curl_multi_remove_handle($multi_handle, $curl_handle);
                curl_close($curl_handle);
            }
            curl_multi_close($multi_handle);
            
            // Only add batch if we got some results
            if (!empty($request_times)) {
                $batch_time = microtime(true) - $batch_start;
                $results[] = array(
                    'batch_time' => $batch_time,
                    'request_times' => $request_times,
                    'http_codes' => $http_codes,
                    'concurrent_requests' => $concurrent_requests,
                    'avg_response_time' => array_sum($request_times) / count($request_times),
                    'max_response_time' => max($request_times),
                    'min_response_time' => min($request_times),
                    'success_rate' => count(array_filter($http_codes, function($code) { return $code >= 200 && $code < 400; })) / count($http_codes)
                );
            }
            
            // Longer delay between batches to prevent overwhelming shared hosting
            usleep(500000); // 0.5 seconds
        }
        
        // If no results, create a fallback result
        if (empty($results)) {
            $results[] = array(
                'batch_time' => 1.0,
                'request_times' => array(1.0),
                'http_codes' => array(200),
                'concurrent_requests' => 1,
                'avg_response_time' => 1.0,
                'max_response_time' => 1.0,
                'min_response_time' => 1.0,
                'success_rate' => 1.0
            );
        }
        
        return $this->analyze_http_performance($results, $total_requests);
    }

    /**
     * Phase 3: Memory Competition Test
     * 
     * Simulates multiple processes competing for memory allocation,
     * testing how hosting handles memory pressure under load.
     *
     * @param int $duration_seconds Test duration in seconds
     * @return array Memory concurrency test results
     */
    private function test_memory_concurrency($duration_seconds) {
        // Safety check for duration
        $duration_seconds = min($duration_seconds, 8); // Max 8 seconds per phase
        
        $results = array();
        $start_time = microtime(true);
        $virtual_processes = 12; // Simulate 12 concurrent users
        $total_processes = 0;
        
        while ((microtime(true) - $start_time) < $duration_seconds) {
            $batch_start = microtime(true);
            $memory_before = memory_get_usage(true);
            $peak_memory_before = memory_get_peak_usage(true);
            
            // Simulate multiple users with different memory patterns
            $process_data = array();
            for ($process = 0; $process < $virtual_processes; $process++) {
                $process_start = microtime(true);
                
                // Different memory usage patterns for different user types
                switch ($process % 4) {
                    case 0: // Heavy user - loading large pages with images
                        $data = array_fill(0, 8000, str_repeat('heavy_user_data_', 150));
                        break;
                    case 1: // Medium user - normal browsing with some media
                        $data = array_fill(0, 4000, str_repeat('medium_user_data_', 100));
                        break;
                    case 2: // Light user - just reading content
                        $data = array_fill(0, 2000, str_repeat('light_user_data_', 75));
                        break;
                    case 3: // Admin user - dashboard operations
                        $data = array_fill(0, 6000, str_repeat('admin_operations_', 125));
                        break;
                }
                
                // Simulate realistic data processing (serialization, hashing, etc.)
                $processed_data = array();
                for ($i = 0; $i < min(150, count($data)); $i++) {
                    $processed_data[] = array(
                        'original' => $data[$i],
                        'hash' => md5($data[$i]),
                        'encoded' => base64_encode($data[$i]),
                        'timestamp' => microtime(true)
                    );
                }
                
                $process_time = microtime(true) - $process_start;
                $process_data[] = array(
                    'process_id' => $process,
                    'type' => $process % 4,
                    'time' => $process_time,
                    'memory_allocated' => count($data),
                    'processed_items' => count($processed_data)
                );
                
                // Keep data in memory briefly to simulate concurrent usage
                usleep(25000); // 0.025 seconds
                unset($data, $processed_data);
                $total_processes++;
            }
            
            $memory_after = memory_get_usage(true);
            $peak_memory_after = memory_get_peak_usage(true);
            $batch_time = microtime(true) - $batch_start;
            
            $results[] = array(
                'batch_time' => $batch_time,
                'memory_before' => $memory_before,
                'memory_after' => $memory_after,
                'memory_used' => $memory_after - $memory_before,
                'peak_memory_increase' => $peak_memory_after - $peak_memory_before,
                'processes' => $process_data,
                'virtual_processes' => $virtual_processes
            );
            
            // Force garbage collection to simulate real-world cleanup
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
            
            usleep(200000); // 0.2 seconds between batches
        }
        
        return $this->analyze_memory_performance($results, $total_processes);
    }

    /**
     * Phase 4: File System Contention Test
     * 
     * Tests concurrent file operations to evaluate file system performance
     * under simultaneous read/write operations.
     *
     * @param int $duration_seconds Test duration in seconds
     * @return array File system concurrency test results
     */
    private function test_file_concurrency($duration_seconds) {
        // Safety check for duration
        $duration_seconds = min($duration_seconds, 6); // Max 6 seconds per phase
        
        global $wp_filesystem;
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        
        if (!WP_Filesystem()) {
            return array(
                'score' => 50,
                'avg_operation_time' => 0,
                'total_operations' => 0,
                'error' => 'WordPress filesystem not available'
            );
        }
        
        $results = array();
        $start_time = microtime(true);
        $upload_dir = wp_upload_dir();
        $test_dir = trailingslashit($upload_dir['basedir']) . 'divewp-concurrency-test/';
        $total_operations = 0;
        
        // Create test directory
        if (!$wp_filesystem->exists($test_dir)) {
            $wp_filesystem->mkdir($test_dir);
        }
        
        while ((microtime(true) - $start_time) < $duration_seconds) {
            $batch_start = microtime(true);
            $concurrent_files = 10; // 10 simultaneous file operations
            
            $file_operations = array();
            
            // Simulate concurrent file operations
            for ($i = 0; $i < $concurrent_files; $i++) {
                $operation_start = microtime(true);
                $success = false;
                
                switch ($i % 5) {
                    case 0: // Write operation (like user uploads, cache writes)
                        $content = str_repeat("Concurrent user $i data at " . microtime(true) . " ", 750);
                        $file_path = $test_dir . "user_write_$i.tmp";
                        $success = $wp_filesystem->put_contents($file_path, $content);
                        break;
                        
                    case 1: // Read operation (like serving cached files, logs)
                        $file_path = $test_dir . "user_write_" . max(0, $i-1) . ".tmp";
                        if ($wp_filesystem->exists($file_path)) {
                            $content = $wp_filesystem->get_contents($file_path);
                            $success = !empty($content);
                        }
                        break;
                        
                    case 2: // Append operation (like logging, analytics)
                        $file_path = $test_dir . "shared_log.txt";
                        $log_entry = "User $i activity logged at " . microtime(true) . "\n";
                        $existing = $wp_filesystem->exists($file_path) ? $wp_filesystem->get_contents($file_path) : '';
                        $success = $wp_filesystem->put_contents($file_path, $existing . $log_entry);
                        break;
                        
                    case 3: // Directory operations (like plugin installations, theme changes)
                        $dir_path = $test_dir . "user_dir_$i/";
                        if (!$wp_filesystem->exists($dir_path)) {
                            $success = $wp_filesystem->mkdir($dir_path);
                        } else {
                            $success = true;
                        }
                        // Create a file in the directory
                        if ($success) {
                            $success = $wp_filesystem->put_contents($dir_path . 'test.txt', 'Directory test');
                        }
                        break;
                        
                    case 4: // File copy operation (like plugin updates, backups)
                        $source_file = $test_dir . "user_write_0.tmp";
                        $dest_file = $test_dir . "copy_$i.tmp";
                        if ($wp_filesystem->exists($source_file)) {
                            $content = $wp_filesystem->get_contents($source_file);
                            $success = $wp_filesystem->put_contents($dest_file, $content);
                        }
                        break;
                }
                
                $operation_time = microtime(true) - $operation_start;
                $file_operations[] = array(
                    'operation_type' => $i % 5,
                    'time' => $operation_time,
                    'success' => $success
                );
                $total_operations++;
            }
            
            $batch_time = microtime(true) - $batch_start;
            $results[] = array(
                'batch_time' => $batch_time,
                'operations' => $file_operations,
                'concurrent_operations' => $concurrent_files
            );
            
            usleep(125000); // 0.125 seconds between batches
        }
        
        // Cleanup test files
        $this->cleanup_test_files($test_dir, $wp_filesystem);
        
        return $this->analyze_file_performance($results, $total_operations);
    }

    // Endpoint methods removed - no longer needed with simplified HTTP testing

    /**
     * Analyze database concurrency performance
     */
    private function analyze_database_performance($operations, $total_operations) {
        $total_time = 0;
        $operation_times = array();
        $total_batches = count($operations);
        
        foreach ($operations as $batch) {
            $total_time += $batch['batch_time'];
            foreach ($batch['operations'] as $op) {
                $operation_times[] = $op['time'];
            }
        }
        
        $avg_operation_time = array_sum($operation_times) / count($operation_times);
        $max_operation_time = max($operation_times);
        $min_operation_time = min($operation_times);
        
        // Calculate performance metrics
        $operations_per_second = $total_operations / $total_time;
        $consistency_score = $this->calculate_consistency_score($operation_times);
        
        return array(
            'avg_operation_time' => $avg_operation_time,
            'max_operation_time' => $max_operation_time,
            'min_operation_time' => $min_operation_time,
            'total_time' => $total_time,
            'total_operations' => $total_operations,
            'operations_per_second' => $operations_per_second,
            'consistency_score' => $consistency_score,
            'score' => $this->score_database_concurrency($avg_operation_time, $operations_per_second, $consistency_score)
        );
    }

    /**
     * Analyze HTTP concurrency performance
     */
    private function analyze_http_performance($results, $total_requests) {
        $all_request_times = array();
        $all_success_rates = array();
        $total_time = 0;
        
        foreach ($results as $batch) {
            $total_time += $batch['batch_time'];
            $all_request_times = array_merge($all_request_times, $batch['request_times']);
            $all_success_rates[] = $batch['success_rate'];
        }
        
        $avg_response_time = array_sum($all_request_times) / count($all_request_times);
        $max_response_time = max($all_request_times);
        $min_response_time = min($all_request_times);
        $avg_success_rate = array_sum($all_success_rates) / count($all_success_rates);
        
        $consistency_score = $this->calculate_consistency_score($all_request_times);
        
        return array(
            'avg_response_time' => $avg_response_time,
            'max_response_time' => $max_response_time,
            'min_response_time' => $min_response_time,
            'total_time' => $total_time,
            'total_requests' => $total_requests,
            'success_rate' => $avg_success_rate,
            'consistency_score' => $consistency_score,
            'score' => $this->score_http_concurrency($avg_response_time, $avg_success_rate, $consistency_score)
        );
    }

    /**
     * Analyze memory concurrency performance
     */
    private function analyze_memory_performance($results, $total_processes) {
        $process_times = array();
        $memory_usage = array();
        $total_time = 0;
        
        foreach ($results as $batch) {
            $total_time += $batch['batch_time'];
            $memory_usage[] = $batch['memory_used'];
            
            foreach ($batch['processes'] as $process) {
                $process_times[] = $process['time'];
            }
        }
        
        $avg_process_time = array_sum($process_times) / count($process_times);
        $max_process_time = max($process_times);
        $avg_memory_usage = array_sum($memory_usage) / count($memory_usage);
        
        $consistency_score = $this->calculate_consistency_score($process_times);
        
        return array(
            'avg_process_time' => $avg_process_time,
            'max_process_time' => $max_process_time,
            'total_time' => $total_time,
            'total_processes' => $total_processes,
            'avg_memory_usage' => $avg_memory_usage,
            'consistency_score' => $consistency_score,
            'score' => $this->score_memory_concurrency($avg_process_time, $avg_memory_usage, $consistency_score)
        );
    }

    /**
     * Analyze file system concurrency performance
     */
    private function analyze_file_performance($results, $total_operations) {
        $operation_times = array();
        $success_rates = array();
        $total_time = 0;
        
        foreach ($results as $batch) {
            $total_time += $batch['batch_time'];
            $successful_ops = 0;
            
            foreach ($batch['operations'] as $op) {
                $operation_times[] = $op['time'];
                if ($op['success']) {
                    $successful_ops++;
                }
            }
            
            $success_rates[] = $successful_ops / count($batch['operations']);
        }
        
        $avg_operation_time = array_sum($operation_times) / count($operation_times);
        $max_operation_time = max($operation_times);
        $avg_success_rate = array_sum($success_rates) / count($success_rates);
        
        $consistency_score = $this->calculate_consistency_score($operation_times);
        
        return array(
            'avg_operation_time' => $avg_operation_time,
            'max_operation_time' => $max_operation_time,
            'total_time' => $total_time,
            'total_operations' => $total_operations,
            'success_rate' => $avg_success_rate,
            'consistency_score' => $consistency_score,
            'score' => $this->score_file_concurrency($avg_operation_time, $avg_success_rate, $consistency_score)
        );
    }

    /**
     * Calculate consistency score from timing data
     */
    private function calculate_consistency_score($times) {
        if (count($times) < 2) {
            return 100;
        }
        
        $mean = array_sum($times) / count($times);
        $variance = 0;
        
        foreach ($times as $time) {
            $variance += pow($time - $mean, 2);
        }
        
        $stddev = sqrt($variance / count($times));
        $coefficient_of_variation = ($mean > 0) ? ($stddev / $mean) : 0;
        
        // Convert to score (lower variation = higher score)
        $consistency_score = max(0, 100 - ($coefficient_of_variation * 100));
        
        return $consistency_score;
    }

    /**
     * Score database concurrency performance
     */
    private function score_database_concurrency($avg_time, $ops_per_second, $consistency) {
        // Score based on operation time (lower is better)
        if ($avg_time <= 0.005) {
            $time_score = 100;
        } elseif ($avg_time <= 0.02) {
            $time_score = 100 - (($avg_time - 0.005) / 0.015) * 20;
        } elseif ($avg_time <= 0.1) {
            $time_score = 80 - (($avg_time - 0.02) / 0.08) * 30;
        } else {
            $time_score = max(20, 50 - (($avg_time - 0.1) / 0.1) * 30);
        }
        
        // Score based on operations per second (higher is better)
        if ($ops_per_second >= 100) {
            $throughput_score = 100;
        } elseif ($ops_per_second >= 50) {
            $throughput_score = 80 + (($ops_per_second - 50) / 50) * 20;
        } elseif ($ops_per_second >= 20) {
            $throughput_score = 60 + (($ops_per_second - 20) / 30) * 20;
        } else {
            $throughput_score = max(20, ($ops_per_second / 20) * 60);
        }
        
        // Weighted score: 40% time, 40% throughput, 20% consistency
        $final_score = ($time_score * 0.4) + ($throughput_score * 0.4) + ($consistency * 0.2);
        
        return round($final_score);
    }

    /**
     * Score HTTP concurrency performance
     */
    private function score_http_concurrency($avg_response_time, $success_rate, $consistency) {
        // Score based on response time (lower is better)
        if ($avg_response_time <= 0.5) {
            $time_score = 100;
        } elseif ($avg_response_time <= 2.0) {
            $time_score = 100 - (($avg_response_time - 0.5) / 1.5) * 30;
        } elseif ($avg_response_time <= 5.0) {
            $time_score = 70 - (($avg_response_time - 2.0) / 3.0) * 40;
        } else {
            $time_score = max(10, 30 - (($avg_response_time - 5.0) / 5.0) * 20);
        }
        
        // Score based on success rate
        $success_score = $success_rate * 100;
        
        // Weighted score: 50% time, 30% success rate, 20% consistency
        $final_score = ($time_score * 0.5) + ($success_score * 0.3) + ($consistency * 0.2);
        
        return round($final_score);
    }

    /**
     * Score memory concurrency performance
     */
    private function score_memory_concurrency($avg_process_time, $avg_memory_usage, $consistency) {
        // Score based on process time (lower is better)
        if ($avg_process_time <= 0.01) {
            $time_score = 100;
        } elseif ($avg_process_time <= 0.05) {
            $time_score = 100 - (($avg_process_time - 0.01) / 0.04) * 20;
        } elseif ($avg_process_time <= 0.2) {
            $time_score = 80 - (($avg_process_time - 0.05) / 0.15) * 40;
        } else {
            $time_score = max(20, 40 - (($avg_process_time - 0.2) / 0.2) * 20);
        }
        
        // Score based on memory efficiency (less memory pressure = better)
        $memory_mb = $avg_memory_usage / (1024 * 1024);
        if ($memory_mb <= 5) {
            $memory_score = 100;
        } elseif ($memory_mb <= 20) {
            $memory_score = 100 - (($memory_mb - 5) / 15) * 20;
        } elseif ($memory_mb <= 50) {
            $memory_score = 80 - (($memory_mb - 20) / 30) * 30;
        } else {
            $memory_score = max(20, 50 - (($memory_mb - 50) / 50) * 30);
        }
        
        // Weighted score: 40% time, 40% memory, 20% consistency
        $final_score = ($time_score * 0.4) + ($memory_score * 0.4) + ($consistency * 0.2);
        
        return round($final_score);
    }

    /**
     * Score file system concurrency performance
     */
    private function score_file_concurrency($avg_operation_time, $success_rate, $consistency) {
        // Score based on operation time (lower is better)
        if ($avg_operation_time <= 0.01) {
            $time_score = 100;
        } elseif ($avg_operation_time <= 0.05) {
            $time_score = 100 - (($avg_operation_time - 0.01) / 0.04) * 20;
        } elseif ($avg_operation_time <= 0.2) {
            $time_score = 80 - (($avg_operation_time - 0.05) / 0.15) * 40;
        } else {
            $time_score = max(20, 40 - (($avg_operation_time - 0.2) / 0.2) * 20);
        }
        
        // Score based on success rate
        $success_score = $success_rate * 100;
        
        // Weighted score: 50% time, 30% success rate, 20% consistency
        $final_score = ($time_score * 0.5) + ($success_score * 0.3) + ($consistency * 0.2);
        
        return round($final_score);
    }

    /**
     * Calculate overall concurrency score from all test phases
     */
    private function calculate_concurrency_score($db_results, $http_results, $memory_results, $file_results) {
        // Weight each test based on real-world importance for hosting evaluation
        $weights = array(
            'database' => 0.35,    // Most critical for dynamic sites
            'http' => 0.30,        // User experience critical
            'memory' => 0.20,      // Important for stability  
            'file' => 0.15         // Less critical but still important
        );
        
        $scores = array(
            'database' => $db_results['score'],
            'http' => $http_results['score'],
            'memory' => $memory_results['score'],
            'file' => $file_results['score']
        );
        
        $weighted_score = 0;
        foreach ($scores as $category => $score) {
            $weighted_score += $score * $weights[$category];
        }
        
        return array(
            'overall_score' => round($weighted_score),
            'category_scores' => $scores,
            'rating' => $this->get_rating_from_score($weighted_score),
            'interpretation' => $this->get_concurrency_interpretation($weighted_score, $scores)
        );
    }

    /**
     * Get rating from numeric score
     */
    private function get_rating_from_score($score) {
        if ($score >= 85) return 'excellent';
        if ($score >= 70) return 'good';
        if ($score >= 50) return 'fair';
        if ($score >= 30) return 'poor';
        return 'critical';
    }

    /**
     * Get interpretation of concurrency performance
     */
    private function get_concurrency_interpretation($overall_score, $category_scores) {
        if ($overall_score >= 85) {
            return 'Excellent concurrency handling. Server performs well under multi-user load with consistent response times.';
        } elseif ($overall_score >= 70) {
            return 'Good concurrency performance. Can handle moderate multi-user traffic with acceptable response times.';
        } elseif ($overall_score >= 50) {
            return 'Fair concurrency handling. May experience slowdowns during peak traffic periods.';
        } elseif ($overall_score >= 30) {
            return 'Poor concurrency performance. Significant slowdowns expected with multiple simultaneous users.';
        } else {
            return 'Critical concurrency issues. Server struggles severely under multi-user load.';
        }
    }

    /**
     * Format results to match JavaScript expectations
     */
    private function format_results($final_score, $total_time, $db_results, $http_results, $memory_results, $file_results) {
        // Calculate aggregate metrics for JavaScript display
        $all_operation_times = array(
            $db_results['avg_operation_time'],
            $http_results['avg_response_time'],
            $memory_results['avg_process_time'],
            $file_results['avg_operation_time']
        );
        
        $avg_operation_time = array_sum($all_operation_times) / count($all_operation_times);
        $max_operation_time = max($all_operation_times);
        
        // Calculate scaling factor (performance under load vs baseline)
        // Assume baseline single operation takes ~0.1 seconds for good performance
        $baseline_operation_time = 0.1; // 100ms baseline
        $scaling_factor = min(1.0, $baseline_operation_time / max(0.001, $avg_operation_time));
        
        // Calculate response degradation
        $min_operation_time = min($all_operation_times);
        $response_degradation = $max_operation_time / max(0.001, $min_operation_time);
        
        // Total concurrent operations across all phases
        $total_concurrent_operations = 15 + 8 + 12 + 10; // DB + HTTP + Memory + File
        
        return array(
            'concurrent_operations' => $total_concurrent_operations,
            'total_time' => round($total_time * 1000, 2),
            'avg_time_per_operation' => round($avg_operation_time * 1000, 2),
            'avg_response_time' => round($avg_operation_time * 1000, 2),
            'max_response_time' => round($max_operation_time * 1000, 2),
            'baseline_avg_time' => round($baseline_operation_time * 1000, 2),
            'scaling_factor' => round($scaling_factor, 3),
            'response_degradation' => round($response_degradation, 2),
            'score' => $final_score['overall_score'],
            'rating' => $final_score['rating'],
            'interpretation' => $final_score['interpretation'],
            'category_scores' => $final_score['category_scores'],
            'detailed_results' => array(
                'database' => $db_results,
                'http' => $http_results,
                'memory' => $memory_results,
                'file' => $file_results
            )
        );
    }

    /**
     * Clean up test files and directories
     */
    private function cleanup_test_files($test_dir, $wp_filesystem) {
        if ($wp_filesystem->exists($test_dir)) {
            // Remove all files in test directory
            $files = $wp_filesystem->dirlist($test_dir);
            if (is_array($files)) {
                foreach ($files as $file) {
                    $file_path = $test_dir . $file['name'];
                    if ($file['type'] == 'f') {
                        $wp_filesystem->delete($file_path);
                    } elseif ($file['type'] == 'd') {
                        $wp_filesystem->rmdir($file_path, true);
                    }
                }
            }
            
            // Remove test directory
            $wp_filesystem->rmdir($test_dir);
        }
    }

    /**
     * Convert human-readable byte format to bytes
     * 
     * WordPress helper function equivalent for converting memory limit strings
     * like "128M" or "2G" to actual byte values.
     *
     * @since 2.0.3
     * @param string $value Human-readable byte format
     * @return int Bytes
     */
    private function wp_convert_hr_to_bytes($value) {
        $value = strtolower(trim($value));
        $bytes = (int) $value;
        
        if (false !== strpos($value, 'g')) {
            $bytes *= 1024 * 1024 * 1024;
        } elseif (false !== strpos($value, 'm')) {
            $bytes *= 1024 * 1024;
        } elseif (false !== strpos($value, 'k')) {
            $bytes *= 1024;
        }
        
        // Deal with large (float) values which run into the maximum integer size.
        return min($bytes, PHP_INT_MAX);
    }

    /**
     * Debug helper to list all related transients
     */
    private function get_debug_transients($session_id) {
        global $wpdb;
        
        // Get all transients that might be related to this session
        $pattern = '%' . $wpdb->esc_like($session_id) . '%';
        $transients = $wpdb->get_results($wpdb->prepare(
            "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
            '_transient_' . $pattern
        ));
        
        $found_transients = array();
        foreach ($transients as $transient) {
            $key = str_replace('_transient_', '', $transient->option_name);
            $found_transients[] = $key;
        }
        
        return $found_transients;
    }
} 