<?php
/**
 * Resource Tests Class for DiveWP Hosting Evaluation
 * 
 * Handles CPU, Memory, Network, File I/O, and WordPress capability testing
 * 
 * @package DiveWP_Boost_Site_Performance
 * @since 1.0.6
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class DiveWP_Resource_Tests
 * 
 * Comprehensive resource testing for hosting evaluation
 */
class DiveWP_Resource_Tests {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Constructor can be used for any initialization if needed
    }
    
    /**
     * Run all resource tests with AJAX-based step execution
     * This method now serves as a legacy wrapper - new execution uses handle_test_step()
     * 
     * @param array $test_config Test configuration from main class
     * @return array Complete resource test results
     */
    public function run_resource_tests($test_config) {
        // Legacy method - redirect to new step-based execution
        // This maintains backwards compatibility for direct calls
        
        // Generate a unique session ID for this test run
        $session_id = 'divewp_resource_test_' . get_current_user_id() . '_' . time();
        
        // Store the initial config
        $this->store_test_session($session_id, array(
            'config' => $test_config,
            'status' => 'initialized',
            'steps_completed' => array(),
            'start_time' => time()
        ));
        
        // Run all steps sequentially (for legacy compatibility)
        $steps = array('cpu', 'wp', 'memory', 'io', 'network');
        
        foreach ($steps as $step) {
            $result = $this->handle_test_step($step, $test_config, $session_id);
        }
        
        // Finalize and return complete results
        return $this->finalize_test_results($session_id);
    }
    
    /**
     * AJAX handler for individual test steps
     * 
     * @param string $step Test step to execute
     * @param array $test_config Test configuration
     * @param string $session_id Unique session identifier
     * @return array Step execution results
     */
    public function handle_test_step($step, $test_config = null, $session_id = null) {
        $start_time = microtime(true);
        $request_start = time();
        
        // If no session ID provided, try to get from POST/GET
        if (!$session_id) {
            $session_id = sanitize_text_field($_POST['session_id'] ?? $_GET['session_id'] ?? '');
        }
        
        // If no config provided, try to load from session or POST
        if (!$test_config) {
            if ($session_id) {
                $session_data = $this->get_test_session($session_id);
                $test_config = $session_data['config'] ?? null;
            }
            
            if (!$test_config && isset($_POST['test_config'])) {
                $test_config = json_decode(stripslashes($_POST['test_config']), true);
            }
        }
        
        if (!$test_config) {
            return array('error' => 'No test configuration provided');
        }
        
        // Check if this step is enabled in configuration
        $enabled_tests = $test_config['enabled_tests'] ?? array();
        $step_function_map = array(
            'cpu' => 'run_cpu_tests',
            'wp' => 'run_wp_tests', 
            'memory' => 'run_memory_tests',
            'io' => 'run_io_test',
            'network' => 'run_network_test'
        );
        
        if (isset($step_function_map[$step]) && !in_array($step_function_map[$step], $enabled_tests)) {
            return array(
                'success' => true,
                'step' => $step,
                'skipped' => true,
                'reason' => 'Test disabled by user configuration',
                'execution_time' => 0,
                'incomplete' => false,
                'results' => array('skipped' => true),
                'next_step' => $this->get_next_step($step),
                'session_id' => $session_id
            );
        }
        
        // Use WordPress native execution time (80% for safety)
        $php_max_execution_time = (int) ini_get('max_execution_time');
        $max_step_time = $php_max_execution_time > 0 ? intval($php_max_execution_time * 0.8) : 20;
        
        $result = array();
        
        try {
            switch ($step) {
                case 'cpu':
                    $result = $this->run_cpu_tests($test_config, $max_step_time, $start_time);
                    break;
                    
                case 'wp':
                    $result = $this->run_wp_tests($test_config, $max_step_time, $start_time);
                    break;
                    
                case 'memory':
                    $result = $this->run_memory_tests($test_config, $max_step_time, $start_time);
                    break;
                    
                case 'io':
                    $result = $this->run_io_test($test_config, $max_step_time, $start_time);
                    break;
                    
                case 'network':
                    $result = $this->run_network_test($test_config, $max_step_time, $start_time);
                    break;
                    
                case 'finalize':
                    return $this->finalize_test_results($session_id);
                    
                default:
                    return array('error' => 'Unknown test step: ' . $step);
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
                'next_step' => $this->get_next_step($step),
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
     * Run CPU tests as a separate step
     * 
     * @param array $test_config Test configuration
     * @param int $max_step_time Maximum execution time for this step
     * @param float $start_time Step start time
     * @return array CPU test results
     */
    private function run_cpu_tests($test_config, $max_step_time, $start_time) {
        $test_iterations = $test_config['test_iterations'];
        $cpu_results = array();
        $completed_iterations = 0;
        $enabled_tests = $test_config['enabled_tests'] ?? array();
        
        for ($test_run = 0; $test_run < $test_iterations; $test_run++) {
            // Aggressive timeout check
            if ((microtime(true) - $start_time) > ($max_step_time * 0.9)) {
                break;
            }
            
            $cpu_iteration_result = array();
            $cpu_total_time = 0;
            
            // Sub-test 1: Prime Generation
            if (in_array('test_prime_generation', $enabled_tests)) {
                $prime_result = $this->test_prime_generation($test_config);
                $cpu_iteration_result['prime_generation_time'] = $prime_result['time'];
                $cpu_iteration_result['primes_found'] = $prime_result['primes_found'];
                $cpu_total_time += $prime_result['time'];
            } else {
                $cpu_iteration_result['prime_generation_time'] = 0;
                $cpu_iteration_result['primes_found'] = 0;
            }
            
            // Check timeout between sub-tests
            if ((microtime(true) - $start_time) > ($max_step_time * 0.9)) break;
            
            // Sub-test 2: Mathematical Operations
            if (in_array('test_mathematical_operations', $enabled_tests)) {
                $math_result = $this->test_mathematical_operations($test_config);
                $cpu_iteration_result['mathematical_operations_time'] = $math_result['time'];
                $cpu_total_time += $math_result['time'];
            } else {
                $cpu_iteration_result['mathematical_operations_time'] = 0;
            }
            
            if ((microtime(true) - $start_time) > ($max_step_time * 0.9)) break;
            
            // Sub-test 3: Conditional Logic
            if (in_array('test_conditional_logic', $enabled_tests)) {
                $conditional_result = $this->test_conditional_logic($test_config);
                $cpu_iteration_result['conditional_logic_time'] = $conditional_result['time'];
                $cpu_total_time += $conditional_result['time'];
            } else {
                $cpu_iteration_result['conditional_logic_time'] = 0;
            }
            
            if ((microtime(true) - $start_time) > ($max_step_time * 0.9)) break;
            
            // Sub-test 4: String Processing
            if (in_array('test_string_processing', $enabled_tests)) {
                $string_result = $this->test_string_processing($test_config);
                $cpu_iteration_result['string_processing_time'] = $string_result['time'];
                $cpu_total_time += $string_result['time'];
            } else {
                $cpu_iteration_result['string_processing_time'] = 0;
            }
            
            if ((microtime(true) - $start_time) > ($max_step_time * 0.9)) break;
            
            // Sub-test 5: Array Operations
            if (in_array('test_array_operations', $enabled_tests)) {
                $array_result = $this->test_array_operations($test_config);
                $cpu_iteration_result['array_operations_time'] = $array_result['time'];
                $cpu_iteration_result['array_sorting_time'] = $array_result['time']; // Backwards compatibility
                $cpu_total_time += $array_result['time'];
            } else {
                $cpu_iteration_result['array_operations_time'] = 0;
                $cpu_iteration_result['array_sorting_time'] = 0;
            }
            
            // Calculate total CPU time and score for this iteration
            $cpu_iteration_result['total_time'] = $cpu_total_time;
            $cpu_iteration_result['score'] = $this->calculate_cpu_score($cpu_total_time);
            
            $cpu_results[] = $cpu_iteration_result;
            $completed_iterations++;
        }
        
        // Calculate statistics from completed iterations
        if (empty($cpu_results)) {
            return array('incomplete' => true, 'reason' => 'No CPU iterations completed due to timeout');
        }
        
        $cpu_scores = array_column($cpu_results, 'score');
        $cpu_total_times = array_column($cpu_results, 'total_time');
        
        $cpu_stats = $this->calculate_performance_statistics($cpu_scores);
        $cpu_time_stats = $this->calculate_performance_statistics($cpu_total_times);
        $cpu_detailed_averages = $this->calculate_detailed_cpu_averages($cpu_results);
        
        $incomplete = $completed_iterations < $test_iterations;
        
        return array(
            'cpu_results' => $cpu_results,
            'cpu_stats' => $cpu_stats,
            'cpu_time_stats' => $cpu_time_stats,
            'cpu_detailed_averages' => $cpu_detailed_averages,
            'completed_iterations' => $completed_iterations,
            'planned_iterations' => $test_iterations,
            'incomplete' => $incomplete,
            'step_duration' => microtime(true) - $start_time
        );
    }
    
    /**
     * Run WordPress tests as a separate step
     * 
     * @param array $test_config Test configuration
     * @param int $max_step_time Maximum execution time for this step
     * @param float $start_time Step start time
     * @return array WordPress test results
     */
    private function run_wp_tests($test_config, $max_step_time, $start_time) {
        $test_iterations = $test_config['test_iterations'];
        $wp_results = array();
        $completed_iterations = 0;
        $enabled_tests = $test_config['enabled_tests'] ?? array();
        
        for ($test_run = 0; $test_run < $test_iterations; $test_run++) {
            // Aggressive timeout check
            if ((microtime(true) - $start_time) > ($max_step_time * 0.9)) {
                break;
            }
            
            $wp_iteration_result = array();
            $wp_total_time = 0;
            
            // Sub-test 1: Shortcode Processing
            if (in_array('test_shortcode_processing', $enabled_tests)) {
                $shortcode_result = $this->test_shortcode_processing($test_config);
                $wp_iteration_result['shortcode_processing_time'] = $shortcode_result['time'];
                $wp_total_time += $shortcode_result['time'];
            } else {
                $wp_iteration_result['shortcode_processing_time'] = 0;
            }
            
            if ((microtime(true) - $start_time) > ($max_step_time * 0.9)) break;
            
            // Sub-test 2: Hook Execution
            if (in_array('test_hook_execution', $enabled_tests)) {
                $hook_result = $this->test_hook_execution($test_config);
                $wp_iteration_result['hook_execution_time'] = $hook_result['time'];
                $wp_total_time += $hook_result['time'];
            } else {
                $wp_iteration_result['hook_execution_time'] = 0;
            }
            
            if ((microtime(true) - $start_time) > ($max_step_time * 0.9)) break;
            
            // Sub-test 3: Transient Operations
            if (in_array('test_transient_operations', $enabled_tests)) {
                $transient_result = $this->test_transient_operations($test_config);
                $wp_iteration_result['transient_operations_time'] = $transient_result['time'];
                $wp_total_time += $transient_result['time'];
            } else {
                $wp_iteration_result['transient_operations_time'] = 0;
            }
            
            if ((microtime(true) - $start_time) > ($max_step_time * 0.9)) break;
            
            // Sub-test 4: Security Functions
            if (in_array('test_security_functions', $enabled_tests)) {
                $security_result = $this->test_security_functions($test_config);
                $wp_iteration_result['security_functions_time'] = $security_result['time'];
                $wp_total_time += $security_result['time'];
            } else {
                $wp_iteration_result['security_functions_time'] = 0;
            }
            
            // Calculate total WordPress time and score for this iteration
            $wp_iteration_result['total_time'] = $wp_total_time;
            $wp_iteration_result['score'] = $this->calculate_wordpress_score($wp_total_time);
            
            $wp_results[] = $wp_iteration_result;
            $completed_iterations++;
        }
        
        if (empty($wp_results)) {
            return array('incomplete' => true, 'reason' => 'No WordPress iterations completed due to timeout');
        }
        
        $wp_scores = array_column($wp_results, 'score');
        $wp_total_times = array_column($wp_results, 'total_time');
        
        $wp_stats = $this->calculate_performance_statistics($wp_scores);
        $wp_time_stats = $this->calculate_performance_statistics($wp_total_times);
        $wp_detailed_averages = $this->calculate_detailed_wp_averages($wp_results);
        
        $incomplete = $completed_iterations < $test_iterations;
        
        return array(
            'wp_results' => $wp_results,
            'wp_stats' => $wp_stats,
            'wp_time_stats' => $wp_time_stats,
            'wp_detailed_averages' => $wp_detailed_averages,
            'completed_iterations' => $completed_iterations,
            'planned_iterations' => $test_iterations,
            'incomplete' => $incomplete,
            'step_duration' => microtime(true) - $start_time
        );
    }
    
    /**
     * Run memory tests as a separate step
     * 
     * @param array $test_config Test configuration
     * @param int $max_step_time Maximum execution time for this step
     * @param float $start_time Step start time
     * @return array Memory test results
     */
    private function run_memory_tests($test_config, $max_step_time, $start_time) {
        $enabled_tests = $test_config['enabled_tests'] ?? array();
        
        // Check if memory allocation test is enabled
        if (!in_array('test_memory_allocation_limits', $enabled_tests)) {
            return array(
                'memory_results' => array(),
                'memory_stats' => array('mean' => 0, 'median' => 0, 'min' => 0, 'max' => 0, 'stddev' => 0, 'count' => 0),
                'allocation_stats' => array('mean' => 0, 'median' => 0, 'min' => 0, 'max' => 0, 'stddev' => 0, 'count' => 0),
                'completed_iterations' => 0,
                'planned_iterations' => 1,
                'incomplete' => false,
                'skipped' => true,
                'reason' => 'Memory allocation test disabled by user configuration',
                'step_duration' => microtime(true) - $start_time
            );
        }
        
        // SINGLE RELIABLE MEMORY TEST - PHP memory cleanup between iterations is unreliable
        // For hosting evaluation, one accurate measurement is better than multiple inconsistent ones
        $memory_result = $this->test_memory_allocation_limits($test_config);
        
        // Store the single result
        $memory_results = array(
            array(
                'score' => $memory_result['score'],
                'max_allocated' => $memory_result['max_allocated'],
                'allocation_efficiency' => $memory_result['allocation_efficiency']
            )
        );
        $completed_iterations = 1;
        
        if (empty($memory_results)) {
            return array('incomplete' => true, 'reason' => 'Memory test failed to complete');
        }
        
        $memory_scores = array_column($memory_results, 'score');
        $actual_allocations = array_column($memory_results, 'max_allocated');
        
        // For single iteration, stats are just the single result
        $memory_stats = $this->calculate_performance_statistics($memory_scores);
        $allocation_stats = $this->calculate_performance_statistics($actual_allocations);
        
        return array(
            'memory_results' => $memory_results,
            'memory_stats' => $memory_stats,
            'allocation_stats' => $allocation_stats,
            'completed_iterations' => $completed_iterations,
            'planned_iterations' => 1,
            'incomplete' => false, // Single test either works or fails completely
            'step_duration' => microtime(true) - $start_time
        );
    }
    
    /**
     * Run I/O test as a separate step
     * 
     * @param array $test_config Test configuration
     * @param int $max_step_time Maximum execution time for this step
     * @param float $start_time Step start time
     * @return array I/O test results
     */
    private function run_io_test($test_config, $max_step_time, $start_time) {
        // I/O test is single execution, but with timeout protection
        $io_score = $this->test_file_io();
        
        return array(
            'io_score' => $io_score,
            'incomplete' => false,
            'step_duration' => microtime(true) - $start_time
        );
    }
    
    /**
     * Run network test as a separate step
     * 
     * @param array $test_config Test configuration
     * @param int $max_step_time Maximum execution time for this step
     * @param float $start_time Step start time
     * @return array Network test results
     */
    private function run_network_test($test_config, $max_step_time, $start_time) {
        // Network test is single execution, but with timeout protection
        $network_score = $this->test_network_capabilities($test_config);
        
        return array(
            'network_score' => $network_score,
            'incomplete' => false,
            'step_duration' => microtime(true) - $start_time
        );
    }
    
    /**
     * Calculate statistical metrics for performance data
     */
    private function calculate_performance_statistics($data) {
        if (empty($data)) {
            return array(
                'mean' => 0,
                'median' => 0,
                'min' => 0,
                'max' => 0,
                'stddev' => 0,
                'count' => 0
            );
        }
        
        $count = count($data);
        $sum = array_sum($data);
        $mean = $sum / $count;
        
        // Calculate median
        sort($data);
        $middle = floor($count / 2);
        if ($count % 2 === 0) {
            $median = ($data[$middle - 1] + $data[$middle]) / 2;
        } else {
            $median = $data[$middle];
        }
        
        // Calculate standard deviation
        $variance_sum = 0;
        foreach ($data as $value) {
            $variance_sum += pow($value - $mean, 2);
        }
        $variance = $variance_sum / $count;
        $stddev = sqrt($variance);
        
        return array(
            'mean' => $mean,
            'median' => $median,
            'min' => min($data),
            'max' => max($data),
            'stddev' => $stddev,
            'count' => $count
        );
    }
    
    /**
     * Calculate detailed averages for CPU performance metrics
     * 
     * @since 1.0.5
     * @param array $cpu_results Array of CPU test results
     * @return array Detailed averages for each CPU metric
     */
    private function calculate_detailed_cpu_averages($cpu_results) {
        if (empty($cpu_results)) {
            return array();
        }
        
        $metrics = array(
            'prime_generation_time' => array(),
            'mathematical_operations_time' => array(),
            'conditional_logic_time' => array(),
            'string_processing_time' => array(),
            'array_operations_time' => array(),
            'array_sorting_time' => array(), // Backwards compatibility
            'primes_found' => array()
        );
        
        // Collect all metrics
        foreach ($cpu_results as $result) {
            foreach ($metrics as $key => $values) {
                if (isset($result[$key])) {
                    $metrics[$key][] = $result[$key];
                }
            }
        }
        
        // Calculate averages
        $averages = array();
        foreach ($metrics as $key => $values) {
            if (!empty($values)) {
                $averages[$key] = round(array_sum($values) / count($values), 3);
            } else {
                $averages[$key] = 0;
            }
        }
        
        // Ensure backwards compatibility: if array_operations_time exists but array_sorting_time doesn't
        if (isset($averages['array_operations_time']) && !isset($averages['array_sorting_time'])) {
            $averages['array_sorting_time'] = $averages['array_operations_time'];
        }
        
        return $averages;
    }
    
    /**
     * Calculate detailed averages for WordPress core performance metrics
     * 
     * @since 1.0.5
     * @param array $wp_results Array of WordPress test results
     * @return array Detailed averages for each WordPress metric
     */
    private function calculate_detailed_wp_averages($wp_results) {
        if (empty($wp_results)) {
            return array();
        }
        
        $metrics = array(
            'shortcode_processing_time' => array(),
            'hook_execution_time' => array(),
            'transient_operations_time' => array(),
            'security_functions_time' => array()
        );
        
        // Collect all metrics
        foreach ($wp_results as $result) {
            foreach ($metrics as $key => $values) {
                if (isset($result[$key])) {
                    $metrics[$key][] = $result[$key];
                }
            }
        }
        
        // Calculate averages
        $averages = array();
        foreach ($metrics as $key => $values) {
            if (!empty($values)) {
                $averages[$key] = round(array_sum($values) / count($values), 3);
            } else {
                $averages[$key] = 0;
            }
        }
        
        return $averages;
    }
    
    /**
     * Assess test stability based on standard deviation
     * 
     * @since 1.0.5
     * @param array $cpu_stats CPU performance statistics
     * @param array $wp_stats WordPress performance statistics
     * @param array $memory_stats Memory performance statistics
     * @return string Stability assessment
     */
    private function assess_test_stability($cpu_stats, $wp_stats, $memory_stats) {
        // Calculate coefficient of variation for each test type
        $cpu_cv = (isset($cpu_stats['mean']) && $cpu_stats['mean'] > 0) ? ($cpu_stats['stddev'] / $cpu_stats['mean']) * 100 : 0;
        $wp_cv = (isset($wp_stats['mean']) && $wp_stats['mean'] > 0) ? ($wp_stats['stddev'] / $wp_stats['mean']) * 100 : 0;
        $memory_cv = (isset($memory_stats['mean']) && $memory_stats['mean'] > 0) ? ($memory_stats['stddev'] / $memory_stats['mean']) * 100 : 0;
        
        // Calculate average only from available tests (in case memory test is disabled)
        $test_count = 0;
        $total_cv = 0;
        
        if (isset($cpu_stats['mean'])) {
            $total_cv += $cpu_cv;
            $test_count++;
        }
        
        if (isset($wp_stats['mean'])) {
            $total_cv += $wp_cv;
            $test_count++;
        }
        
        if (isset($memory_stats['mean'])) {
            $total_cv += $memory_cv;
            $test_count++;
        }
        
        $avg_cv = ($test_count > 0) ? $total_cv / $test_count : 0;
        
        if ($avg_cv < 5) {
            return 'excellent'; // Very stable performance
        } elseif ($avg_cv < 10) {
            return 'good'; // Good stability
        } elseif ($avg_cv < 20) {
            return 'fair'; // Acceptable stability
        } else {
            return 'poor'; // High variability
        }
    }
    
    /**
     * Enhanced resource interpretation with statistical context
     * 
     * @since 1.0.5
     * @param int $resource_score Overall resource score
     * @param array $cpu_stats CPU performance statistics
     * @param array $wp_stats WordPress performance statistics
     * @return string Enhanced interpretation
     */
    private function get_enhanced_resource_interpretation($resource_score, $cpu_stats, $wp_stats) {
        $base_interpretation = $this->get_resource_interpretation($resource_score);
        
        // Add statistical context
        $cpu_consistency = ($cpu_stats['stddev'] < 5) ? 'consistent' : 'variable';
        $wp_consistency = ($wp_stats['stddev'] < 5) ? 'consistent' : 'variable';
        
        $enhanced = $base_interpretation . sprintf(
            ' CPU performance is %s (σ=%.1f), WordPress operations are %s (σ=%.1f). Based on %d test iterations.',
            $cpu_consistency,
            $cpu_stats['stddev'],
            $wp_consistency,
            $wp_stats['stddev'],
            $cpu_stats['count']
        );
        
        return $enhanced;
    }
    
    /**
     * Individual CPU Sub-test: Prime Generation (Enhanced for 2025 Shared Hosting)
     * 
     * @param array $test_config Test configuration
     * @return array Test results with time and primes found
     */
    private function test_prime_generation($test_config) {
        $start_time = microtime(true);
        $math_iterations = $test_config['cpu_math_iterations'];
        $php_max_execution_time = (int) ini_get('max_execution_time');
        $max_test_time = $php_max_execution_time > 0 ? intval($php_max_execution_time * 0.8) : 20;
        
        // Enhanced for shared hosting: Test CPU throttling recovery
        $results = array();
        $primes_checked = 0;
        $throttling_detected = false;
        $iteration_times = array();
        
        // Test in smaller chunks to detect CPU throttling
        $chunk_size = max(100, $math_iterations / 20);
        $chunks_completed = 0;
        
        for ($chunk_start = 2; $chunk_start < $math_iterations; $chunk_start += $chunk_size) {
            $chunk_end = min($chunk_start + $chunk_size, $math_iterations);
            $chunk_time_start = microtime(true);
            
            for ($i = $chunk_start; $i < $chunk_end; $i++) {
                $is_prime = true;
                $sqrt = sqrt($i);
                for ($j = 2; $j <= $sqrt; $j++) {
                    if ($i % $j === 0) {
                        $is_prime = false;
                        break;
                    }
                }
                if ($is_prime) {
                    $results[] = $i;
                }
                $primes_checked++;
            }
            
            $chunk_time = microtime(true) - $chunk_time_start;
            $iteration_times[] = $chunk_time;
            $chunks_completed++;
            
            // Detect CPU throttling (sudden performance degradation)
            if (count($iteration_times) >= 3) {
                $recent_avg = array_sum(array_slice($iteration_times, -3)) / 3;
                $initial_avg = array_sum(array_slice($iteration_times, 0, 3)) / 3;
                
                if ($recent_avg > ($initial_avg * 2.5)) {
                    $throttling_detected = true;
                }
            }
            
            // Safety check for timeout or throttling
            if ((microtime(true) - $start_time) > ($max_test_time * 0.25)) {
                break;
            }
        }
        
        $time = microtime(true) - $start_time;
        
        return array(
            'time' => round($time, 3),
            'primes_found' => count($results),
            'primes_checked' => $primes_checked,
            'chunks_completed' => $chunks_completed,
            'throttling_detected' => $throttling_detected,
            'performance_consistency' => $this->calculate_consistency_score($iteration_times)
        );
    }
    
    /**
     * Individual CPU Sub-test: Mathematical Operations (Enhanced for Shared Hosting Stress)
     * 
     * @param array $test_config Test configuration
     * @return array Test results with time and operations completed
     */
    private function test_mathematical_operations($test_config) {
        $start_time = microtime(true);
        $math_iterations = $test_config['cpu_math_iterations'];
        $php_max_execution_time = (int) ini_get('max_execution_time');
        $max_test_time = $php_max_execution_time > 0 ? intval($php_max_execution_time * 0.8) : 20;
        
        // Enhanced: Test floating-point intensive operations that stress shared CPU
        $math_results = 0;
        $math_ops_completed = 0;
        $cpu_intensive_ops = 0;
        
        for ($i = 1; $i <= $math_iterations; $i++) {
            // More CPU-intensive mathematical calculations for 2025 testing
            $base_calc = sin($i) * cos($i) + sqrt($i) * log(max(1, $i));
            $exponential_calc = pow($i % 100, 3) + exp(min($i % 10, 5));
            $trigonometric_calc = atan2($i, max(1, $i % 50)) * tan(min($i % 20, 10));
            
            // Floating-point intensive operations that reveal CPU throttling
            for ($j = 0; $j < 5; $j++) {
                $result = ($base_calc + $exponential_calc) / max(1, $trigonometric_calc);
                $result = round($result * 1000) / 1000; // Force precision operations
                $cpu_intensive_ops++;
            }
            
            $math_results += $result;
            $math_ops_completed++;
            
            // Safety check for timeout
            if ((microtime(true) - $start_time) > ($max_test_time * 0.2)) {
                break;
            }
        }
        
        $time = microtime(true) - $start_time;
        
        return array(
            'time' => round($time, 3),
            'operations_completed' => $math_ops_completed,
            'intensive_ops_completed' => $cpu_intensive_ops,
            'operations_per_second' => round($math_ops_completed / max(0.001, $time), 1),
            'math_result_sum' => round($math_results, 3)
        );
    }
    
    /**
     * Individual CPU Sub-test: String Processing (Enhanced for Memory + CPU Stress)
     * 
     * @param array $test_config Test configuration
     * @return array Test results with time and operations completed
     */
    private function test_string_processing($test_config) {
        $start_time = microtime(true);
        $php_max_execution_time = (int) ini_get('max_execution_time');
        $max_test_time = $php_max_execution_time > 0 ? intval($php_max_execution_time * 0.8) : 20;
        
        // Enhanced: Larger text and more memory-intensive operations
        $base_text = str_repeat('Lorem ipsum dolor sit amet consectetur adipiscing elit. ', 2000); // 100KB+
        $strings_processed = 0;
        $memory_operations = 0;
        
        for ($i = 0; $i < 200; $i++) { // Reduced iterations but increased complexity
            $working_text = $base_text . str_repeat(" Additional content $i ", 100);
            
            // More memory and CPU intensive string operations
            $processed = strtoupper($working_text);
            $processed = str_replace(array('LOREM', 'IPSUM', 'DOLOR'), array('WORDPRESS', 'HOSTING', 'PERFORMANCE'), $processed);
            $processed = substr($processed, 0, strlen($processed) / 2);
            $processed = strrev($processed);
            
            // Multiple memory allocations to stress shared hosting limits
            $temp_array = str_split($processed, 100);
            $rejoined = implode('-', $temp_array);
            $final = str_shuffle(substr($rejoined, 0, 10000));
            
            $memory_operations += 3; // Track memory-intensive operations
            $strings_processed++;
            
            // Clean up to prevent memory accumulation
            unset($working_text, $processed, $temp_array, $rejoined);
            
            // Safety check for timeout
            if ((microtime(true) - $start_time) > ($max_test_time * 0.15)) {
                break;
            }
        }
        
        $time = microtime(true) - $start_time;
        
        return array(
            'time' => round($time, 3),
            'strings_processed' => $strings_processed,
            'memory_operations' => $memory_operations,
            'memory_efficiency' => round($memory_operations / max(0.001, $time), 1),
            'final_string_length' => strlen($final ?? '')
        );
    }
    
    /**
     * Individual CPU Sub-test: Array Operations (Enhanced for Memory + CPU Pressure)
     * 
     * @param array $test_config Test configuration
     * @return array Test results with time and operations completed
     */
    private function test_array_operations($test_config) {
        $start_time = microtime(true);
        
        // Enhanced: Multiple array operations that stress both CPU and memory
        $large_array = array();
        $operations_completed = 0;
        
        // Create larger, more complex array
        for ($i = 0; $i < 30000; $i++) {
            $large_array[] = array(
                'id' => $i,
                'value' => mt_rand(1, 100000),
                'text' => 'item_' . wp_generate_password(10, false),
                'meta' => array('type' => $i % 10, 'active' => ($i % 3 === 0))
            );
        }
        $operations_completed++;
        
        // Intensive array operations that stress shared hosting
        usort($large_array, function($a, $b) {
            return $a['value'] <=> $b['value'];
        });
        $operations_completed++;
        
        // Filter operations (CPU + memory intensive)
        $filtered = array_filter($large_array, function($item) {
            return $item['meta']['active'] && $item['value'] > 50000;
        });
        $operations_completed++;
        
        // Map operations with complex transformations
        $mapped = array_map(function($item) {
            return array(
                'computed' => $item['value'] * 1.5 + strlen($item['text']),
                'hash' => md5($item['text'] . $item['id'])
            );
        }, array_slice($large_array, 0, 5000)); // Limit to prevent timeout
        $operations_completed++;
        
        // Array manipulation that stresses memory
        $chunks = array_chunk($large_array, 1000);
        $reassembled = array_merge(...array_slice($chunks, 0, 10)); // Limit chunks
        $operations_completed++;
        
        $time = microtime(true) - $start_time;
        
        // Clean up large arrays
        unset($large_array, $filtered, $mapped, $chunks, $reassembled);
        
        return array(
            'time' => round($time, 3),
            'array_size' => 30000,
            'operations_performed' => $operations_completed,
            'operations_per_second' => round($operations_completed / max(0.001, $time), 1),
            'memory_intensive' => true
        );
    }
    
    /**
     * Calculate performance consistency score to detect CPU throttling
     * 
     * @param array $iteration_times Array of timing measurements
     * @return float Consistency score (0-100, higher is more consistent)
     */
    private function calculate_consistency_score($iteration_times) {
        if (count($iteration_times) < 3) {
            return 100; // Not enough data
        }
        
        $mean = array_sum($iteration_times) / count($iteration_times);
        $variance = 0;
        
        foreach ($iteration_times as $time) {
            $variance += pow($time - $mean, 2);
        }
        
        $std_dev = sqrt($variance / count($iteration_times));
        $coefficient_of_variation = ($mean > 0) ? ($std_dev / $mean) * 100 : 0;
        
        // Convert to consistency score (lower CV = higher consistency)
        return max(0, round(100 - ($coefficient_of_variation * 2)));
    }
    
    /**
     * Individual CPU Sub-test: Conditional Logic
     * 
     * @param array $test_config Test configuration
     * @return array Test results with time and operations completed
     */
    private function test_conditional_logic($test_config) {
        $start_time = microtime(true);
        $conditional_iterations = $test_config['cpu_conditional_iterations'];
        $max_test_time = $test_config['max_test_time_per_section'];
        
        $conditional_results = 0;
        $conditions_processed = 0;
        
        for ($i = 0; $i < $conditional_iterations; $i++) {
            // Complex conditional logic testing
            if ($i % 2 === 0) {
                if ($i % 4 === 0) {
                    $conditional_results += $i * 2;
                } else {
                    $conditional_results += $i / 2;
                }
            } elseif ($i % 3 === 0) {
                $conditional_results += $i * 3;
            } elseif ($i % 5 === 0) {
                $conditional_results += $i / 5;
            } else {
                $conditional_results += $i;
            }
            
            // Nested conditional for complexity
            switch ($i % 10) {
                case 0:
                case 1:
                    $conditional_results *= 1.1;
                    break;
                case 2:
                case 3:
                    $conditional_results *= 0.9;
                    break;
                default:
                    $conditional_results += 1;
            }
            
            $conditions_processed++;
            
            // Safety check for timeout
            if ((microtime(true) - $start_time) > ($max_test_time * 0.25)) {
                break;
            }
        }
        
        $time = microtime(true) - $start_time;
        
        return array(
            'time' => round($time, 3),
            'conditions_processed' => $conditions_processed,
            'conditional_result_sum' => $conditional_results
        );
    }
    
    /**
     * Test actual memory allocation limits - simplified wrapper
     * 
     * Uses the simple memory allocation test and formats results for scoring
     */
    private function test_memory_allocation_limits($test_config) {
        // Run the simple test
        $test_result = $this->simple_memory_allocation_test($test_config['memory_allocation_percentage']);
        
        // Calculate score based on allocation efficiency
        $efficiency = 0;
        if ($test_result['target'] > 0) {
            $efficiency = ($test_result['allocated'] / $test_result['target']) * 100;
        }
        
        // Calculate absolute target (80% of total memory limit)
        $absolute_target = $test_result['memory_limit'] * $test_config['memory_allocation_percentage'];
        $allocation_efficiency = 0;
        if ($absolute_target > 0) {
            $allocation_efficiency = ($test_result['allocated'] / $absolute_target) * 100;
        }
        
        // Build test results array for scoring
        $scoring_results = array(
            'max_allocated' => $test_result['allocated'],
            'allocation_efficiency' => $allocation_efficiency,
            'memory_pressure_handled' => $test_result['success'],
            'wordpress_simulation_success' => $test_result['success'],
            'peak_memory_delta' => $test_result['allocated']
        );
        
        // Calculate score
        $score = $this->calculate_memory_score_2025($scoring_results, $test_result['memory_limit']);
        
        // Return formatted results
        return array(
            'score' => $score,
            'max_allocated' => $test_result['allocated'],
            'memory_limit' => ini_get('memory_limit'),
            'memory_limit_bytes' => $test_result['memory_limit'],
            'absolute_target' => $absolute_target,
            'allocation_efficiency' => round($allocation_efficiency, 1),
            'memory_pressure_handled' => $test_result['success'],
            'wordpress_simulation_success' => $test_result['success'],
            'peak_memory_delta' => $test_result['allocated']
        );
    }
    

    

    

    
    /**
     * Calculate memory score based purely on hosting performance
     * 
     * Focuses only on memory allocation efficiency and performance,
     * not on user-configurable memory limits.
     * 
     * @param array $test_results Memory test results
     * @param int $memory_limit_bytes Memory limit in bytes (for reference only)
     * @return int Memory performance score (0-100)
     */
    private function calculate_memory_score_2025($test_results, $memory_limit_bytes) {
        $base_score = 30; // Conservative baseline
        
        // Allocation efficiency scoring with smooth curve
        $efficiency = $test_results['allocation_efficiency'];
        
        $efficiency_points = 0;
        if ($efficiency >= 95) {
            $efficiency_points = 50; // Excellent allocation efficiency
        } elseif ($efficiency >= 85) {
            $efficiency_points = 40; // Very good allocation efficiency
        } elseif ($efficiency >= 75) {
            $efficiency_points = 30; // Good allocation efficiency
        } elseif ($efficiency >= 60) {
            $efficiency_points = 20; // Fair allocation efficiency
        } elseif ($efficiency >= 40) {
            $efficiency_points = 10; // Poor allocation efficiency
        }
        $base_score += $efficiency_points;
        
        // Memory pressure handling bonus
        if ($test_results['memory_pressure_handled']) {
            $base_score += 15; // Increased weight for pressure handling
        }
        
        // WordPress simulation success bonus
        if ($test_results['wordpress_simulation_success']) {
            $base_score += 5;
        }
        
        // NO MEMORY LIMIT PENALTIES - this is hosting performance only
        
        return max(10, min(100, round($base_score)));
    }
    

    
    /**
     * Test file I/O performance with 2025 WordPress-realistic scenarios
     * 
     * Enhanced to test multiple file sizes and operations that WordPress actually performs:
     * - Image uploads and processing
     * - Log file operations
     * - Cache file operations
     * - Plugin/theme file access
     */
    private function test_file_io() {
        // Initialize WordPress filesystem
        global $wp_filesystem;
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        
        $score = 50; // Default score if filesystem not available
        
        // Try direct filesystem access first
        if (!WP_Filesystem()) {
            return $score;
        }
        
        $upload_dir = wp_upload_dir();
        $test_dir = trailingslashit($upload_dir['basedir']) . 'divewp-tests/';
        
        try {
            // Create test directory
            if (!$wp_filesystem->exists($test_dir)) {
                $wp_filesystem->mkdir($test_dir);
            }
            
            $io_results = array();
            $total_operations = 0;
            $total_time = 0;
            
            // Test 1: Small file operations (WordPress log files, cache entries)
            $small_file_time = $this->test_small_file_operations($wp_filesystem, $test_dir);
            $io_results['small_files'] = $small_file_time;
            $total_time += $small_file_time;
            $total_operations += 10;
            
            // Test 2: Medium file operations (WordPress uploads, plugin files)
            $medium_file_time = $this->test_medium_file_operations($wp_filesystem, $test_dir);
            $io_results['medium_files'] = $medium_file_time;
            $total_time += $medium_file_time;
            $total_operations += 5;
            
            // Test 3: Large file operations (WordPress backups, exports)
            $large_file_time = $this->test_large_file_operations($wp_filesystem, $test_dir);
            $io_results['large_files'] = $large_file_time;
            $total_time += $large_file_time;
            $total_operations += 2;
            
            // Test 4: Concurrent I/O simulation (multiple WordPress processes)
            $concurrent_time = $this->test_concurrent_io_simulation($wp_filesystem, $test_dir);
            $io_results['concurrent_ops'] = $concurrent_time;
            $total_time += $concurrent_time;
            $total_operations += 3;
            
            // Calculate 2025-calibrated I/O score based on WordPress usage patterns
            $score = $this->calculate_io_score_2025($io_results, $total_time, $total_operations);
            
            // Clean up test directory
            if ($wp_filesystem->exists($test_dir)) {
                $wp_filesystem->rmdir($test_dir);
            }
            
        } catch (Exception $e) {
            $score = 20;
            // Ensure cleanup on error
            if ($wp_filesystem->exists($test_dir)) {
                $wp_filesystem->rmdir($test_dir);
            }
        }
        
        return min(100, max(20, intval($score)));
    }
    
    /**
     * Test small file operations (WordPress logs, cache files, meta)
     * 
     * @param object $wp_filesystem WordPress filesystem object
     * @param string $test_dir Test directory path
     * @return float Total time for small file operations
     */
    private function test_small_file_operations($wp_filesystem, $test_dir) {
        $start_time = microtime(true);
        
        // Simulate WordPress cache file operations (1-10KB files)
        for ($i = 0; $i < 10; $i++) {
            $cache_data = array(
                'post_id' => $i,
                'meta' => array_fill(0, 20, wp_generate_password(50, false)),
                'timestamp' => current_time('timestamp'),
                'query_results' => array_fill(0, 10, array('id' => $i, 'title' => wp_generate_password(30, false)))
            );
            
            $serialized_data = serialize($cache_data);
            $file_path = $test_dir . "cache_$i.tmp";
            
            // Write, read, verify, delete
            $wp_filesystem->put_contents($file_path, $serialized_data);
            $read_data = $wp_filesystem->get_contents($file_path);
            $wp_filesystem->delete($file_path);
        }
        
        return microtime(true) - $start_time;
    }
    
    /**
     * Test medium file operations (WordPress uploads, plugin assets)
     * 
     * @param object $wp_filesystem WordPress filesystem object
     * @param string $test_dir Test directory path
     * @return float Total time for medium file operations
     */
    private function test_medium_file_operations($wp_filesystem, $test_dir) {
        $start_time = microtime(true);
        
        // Simulate WordPress medium file operations (50-500KB files)
        for ($i = 0; $i < 5; $i++) {
            // Create data similar to WordPress uploads or plugin files
            $medium_data = array(
                'image_data' => str_repeat('IMAGEDATA', 6400), // ~50KB
                'metadata' => array(
                    'width' => 1920,
                    'height' => 1080,
                    'mime_type' => 'image/jpeg',
                    'sizes' => array_fill(0, 10, array('file' => wp_generate_password(20, false) . '.jpg'))
                ),
                'processing_log' => array_fill(0, 100, wp_generate_password(100, false))
            );
            
            $file_content = json_encode($medium_data);
            $file_path = $test_dir . "upload_$i.json";
            
            // WordPress-like file operations
            $wp_filesystem->put_contents($file_path, $file_content);
            $retrieved = $wp_filesystem->get_contents($file_path);
            $wp_filesystem->delete($file_path);
        }
        
        return microtime(true) - $start_time;
    }
    
    /**
     * Test large file operations (WordPress exports, backups)
     * 
     * @param object $wp_filesystem WordPress filesystem object
     * @param string $test_dir Test directory path
     * @return float Total time for large file operations
     */
    private function test_large_file_operations($wp_filesystem, $test_dir) {
        $start_time = microtime(true);
        
        // Simulate WordPress large file operations (1-5MB files)
        for ($i = 0; $i < 2; $i++) {
            // Create data similar to WordPress exports or backup files
            $base_chunk = str_repeat('WORDPRESS_EXPORT_DATA_CHUNK_', 1000); // ~28KB chunk
            $large_data = str_repeat($base_chunk, 40); // ~1.1MB total
            
            $file_path = $test_dir . "export_$i.sql";
            
            // Large file operations with chunked writing (realistic for shared hosting)
            $wp_filesystem->put_contents($file_path, $large_data);
            
            // Simulate reading large files in chunks (WordPress backup/export reading)
            $chunk_size = 102400; // 100KB chunks
            $read_data = '';
            $file_size = strlen($large_data);
            
            for ($offset = 0; $offset < $file_size; $offset += $chunk_size) {
                $chunk = $wp_filesystem->get_contents($file_path);
                // Simulate processing each chunk
                $read_data .= substr($chunk, $offset, $chunk_size);
                
                // Break if we've read enough for testing
                if ($offset > ($file_size / 2)) break;
            }
            
            $wp_filesystem->delete($file_path);
        }
        
        return microtime(true) - $start_time;
    }
    
    /**
     * Test concurrent I/O simulation (multiple WordPress processes)
     * 
     * @param object $wp_filesystem WordPress filesystem object
     * @param string $test_dir Test directory path
     * @return float Total time for concurrent operations
     */
    private function test_concurrent_io_simulation($wp_filesystem, $test_dir) {
        $start_time = microtime(true);
        
        // Simulate concurrent WordPress operations (cache writes, log writes, uploads)
        $files_created = array();
        
        // Create multiple files simultaneously (like WordPress under load)
        for ($i = 0; $i < 3; $i++) {
            $concurrent_data = array(
                'session_data' => array_fill(0, 50, wp_generate_password(100, false)),
                'user_meta' => array_fill(0, 20, array('key' => wp_generate_password(20, false), 'value' => wp_generate_password(200, false))),
                'transient_cache' => array_fill(0, 30, wp_generate_password(150, false))
            );
            
            $file_content = serialize($concurrent_data);
            $file_path = $test_dir . "concurrent_$i.cache";
            $files_created[] = $file_path;
            
            $wp_filesystem->put_contents($file_path, $file_content);
        }
        
        // Now read all files (simulating concurrent WordPress requests)
        foreach ($files_created as $file_path) {
            $data = $wp_filesystem->get_contents($file_path);
            $unserialized = unserialize($data);
            $wp_filesystem->delete($file_path);
        }
        
        return microtime(true) - $start_time;
    }
    
    /**
     * Calculate I/O score with smooth curve and 1-point granularity
     * 
     * Focuses purely on file I/O performance for hosting evaluation.
     * 
     * @param array $io_results Results from different I/O tests
     * @param float $total_time Total time for all operations
     * @param int $total_operations Total number of operations
     * @return int I/O performance score (0-100)
     */
    private function calculate_io_score_2025($io_results, $total_time, $total_operations) {
        if ($total_time <= 0) {
            return 100;
        }
        
        // Smooth curve: Excellent I/O at 0.8s, poor at 10.0s
        $optimal_time = 0.8;  // Excellent I/O performance (NVMe SSD)
        $poor_time = 10.0;    // Poor I/O performance threshold
        
        if ($total_time <= $optimal_time) {
            return 100;
        }
        
        if ($total_time >= $poor_time) {
            return 15;
        }
        
        // Smooth logarithmic curve for I/O (storage performance varies exponentially)
        $normalized = ($total_time - $optimal_time) / ($poor_time - $optimal_time);
        $score = 100 - (85 * pow($normalized, 0.7)); // Logarithmic-like curve
        
        return max(15, min(100, round($score)));
    }
    
    /**
     * Get rating from score
     */
    private function get_rating_from_score($score) {
        if ($score >= 90) {
            return 'excellent';
        } elseif ($score >= 75) {
            return 'good';
        } elseif ($score >= 50) {
            return 'fair';
        } else {
            return 'poor';
        }
    }
    
    /**
     * Individual WordPress Sub-test: Shortcode Processing
     * 
     * @param array $test_config Test configuration
     * @return array Test results with time and operations completed
     */
    private function test_shortcode_processing($test_config) {
        $start_time = microtime(true);
        $php_max_execution_time = (int) ini_get('max_execution_time');
        $max_test_time = $php_max_execution_time > 0 ? intval($php_max_execution_time * 0.8) : 20;
        
        $shortcode_iterations = 1000;
        $test_content = 'This is a test post with [gallery ids="1,2,3"] and [audio src="test.mp3"] shortcodes for performance testing.';
        $shortcodes_processed = 0;
        
        for ($i = 0; $i < $shortcode_iterations; $i++) {
            $processed = do_shortcode($test_content . ' ' . $i);
            $shortcodes_processed++;
            
            // Safety check for timeout
            if ((microtime(true) - $start_time) > ($max_test_time * 0.25)) {
                break;
            }
        }
        
        $time = microtime(true) - $start_time;
        
        return array(
            'time' => round($time, 3),
            'shortcodes_processed' => $shortcodes_processed,
            'content_length' => strlen($processed)
        );
    }
    
    /**
     * Individual WordPress Sub-test: Hook Execution
     * 
     * @param array $test_config Test configuration
     * @return array Test results with time and operations completed
     */
    private function test_hook_execution($test_config) {
        $start_time = microtime(true);
        $php_max_execution_time = (int) ini_get('max_execution_time');
        $max_test_time = $php_max_execution_time > 0 ? intval($php_max_execution_time * 0.8) : 20;
        
        $hook_iterations = 10000;
        $hooks_executed = 0;
        
        for ($i = 0; $i < $hook_iterations; $i++) {
            $content = 'Test content for filtering ' . $i;
            $filtered = apply_filters('the_content', $content);
            $filtered = apply_filters('wp_trim_excerpt', $filtered);
            $hooks_executed++;
            
            // Safety check for timeout
            if ((microtime(true) - $start_time) > ($max_test_time * 0.25)) {
                break;
            }
        }
        
        $time = microtime(true) - $start_time;
        
        return array(
            'time' => round($time, 3),
            'hooks_executed' => $hooks_executed,
            'final_content_length' => strlen($filtered)
        );
    }
    
    /**
     * Individual WordPress Sub-test: Transient Operations (2025-Optimized)
     * 
     * Automatically detects and tests the appropriate caching layer:
     * - Object cache (Redis/Memcached) for modern hosting
     * - Database transients for traditional hosting
     * 
     * @param array $test_config Test configuration
     * @return array Test results with time and operations completed
     */
    private function test_transient_operations($test_config) {
        // Use 2025-optimized WordPress caching performance testing
        $result = $this->test_wordpress_caching_performance_2025($test_config);
        
        // Ensure backwards compatibility with existing result structure
        $legacy_result = array(
            'time' => $result['time'],
            'transients_processed' => $result['operations_completed'],
            'data_size' => 0 // Will be calculated below
        );
        
        // Add enhanced 2025 data for debugging and insights
        $legacy_result['cache_detection'] = array(
            'test_approach' => $result['test_approach'],
            'performance_tier' => $result['performance_tier'],
            'hosting_generation' => ($result['test_approach'] === 'object_cache') ? '2025_modern' : '2024_traditional'
        );
        
        // Calculate data size based on test approach
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
        
        return $legacy_result;
    }
    
    /**
     * Individual WordPress Sub-test: Security Functions
     * 
     * @param array $test_config Test configuration
     * @return array Test results with time and operations completed
     */
    private function test_security_functions($test_config) {
        $start_time = microtime(true);
        $php_max_execution_time = (int) ini_get('max_execution_time');
        $max_test_time = $php_max_execution_time > 0 ? intval($php_max_execution_time * 0.8) : 20;
        
        $security_iterations = 5000;
        $test_html = '<div class="test"><p>This is <strong>test HTML</strong> with <a href="#" onclick="alert()">links</a> and <script>alert("xss")</script> for sanitization testing.</p></div>';
        $security_operations = 0;
        
        for ($i = 0; $i < $security_iterations; $i++) {
            $sanitized = wp_kses_post($test_html . ' ' . $i);
            $stripped = wp_strip_all_tags($sanitized);
            $security_operations++;
            
            // Safety check for timeout
            if ((microtime(true) - $start_time) > ($max_test_time * 0.25)) {
                break;
            }
        }
        
        $time = microtime(true) - $start_time;
        
        return array(
            'time' => round($time, 3),
            'security_operations' => $security_operations,
            'sanitized_length' => strlen($sanitized),
            'stripped_length' => strlen($stripped)
        );
    }
    
    /**
     * Calculate CPU score based on total time with smooth curve and 1-point granularity
     * 
     * Updated for hosting-only evaluation with smooth scoring curve.
     * No harsh cliffs - gradual performance degradation scoring.
     * 
     * @param float $total_time Total CPU test time in seconds
     * @return int CPU performance score (0-100)
     */
    private function calculate_cpu_score($total_time) {
        if ($total_time <= 0) {
            return 100;
        }
        
        // Smooth curve: Excellent performance at 1.0s, poor at 12.0s
        // Using exponential decay for realistic hosting performance curve
        $optimal_time = 1.0;  // Best case scenario
        $poor_time = 12.0;    // Poor performance threshold
        
        if ($total_time <= $optimal_time) {
            return 100;
        }
        
        if ($total_time >= $poor_time) {
            return 10;
        }
        
        // Smooth exponential decay between optimal and poor
        $normalized = ($total_time - $optimal_time) / ($poor_time - $optimal_time);
        $score = 100 - (90 * pow($normalized, 0.8)); // Gentler curve
        
        return max(10, min(100, round($score)));
    }
    
    /**
     * Calculate WordPress score based on total time with smooth curve and 1-point granularity
     * 
     * Updated for hosting-only evaluation focusing on database and WordPress core performance.
     * 
     * @param float $total_time Total WordPress test time in seconds
     * @return int WordPress performance score (0-100)
     */
    private function calculate_wordpress_score($total_time) {
        if ($total_time <= 0) {
            return 100;
        }
        
        // WordPress operations are typically faster than pure CPU
        $optimal_time = 0.5;  // Excellent WordPress performance
        $poor_time = 8.0;     // Poor WordPress performance threshold
        
        if ($total_time <= $optimal_time) {
            return 100;
        }
        
        if ($total_time >= $poor_time) {
            return 10;
        }
        
        // Smooth curve for WordPress operations
        $normalized = ($total_time - $optimal_time) / ($poor_time - $optimal_time);
        $score = 100 - (90 * pow($normalized, 0.75)); // Slightly more forgiving
        
        return max(10, min(100, round($score)));
    }
    
    /**
     * Store test session data using transients
     * 
     * @param string $session_id Unique session identifier
     * @param array $data Session data to store
     */
    private function store_test_session($session_id, $data) {
        // Store session for 1 hour (3600 seconds)
        set_transient($session_id, $data, 3600);
    }
    
    /**
     * Get test session data from transients
     * 
     * @param string $session_id Unique session identifier
     * @return array|false Session data or false if not found
     */
    private function get_test_session($session_id) {
        $data = get_transient($session_id);
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
        
        if ($session_data) {
            $session_data['step_results'][$step] = $result;
            $session_data['steps_completed'][] = $step;
            $session_data['last_updated'] = time();
            
            $this->store_test_session($session_id, $session_data);
        }
    }
    
    /**
     * Get the next step in the test sequence
     * 
     * @param string $current_step Current step name
     * @return string|null Next step name or null if complete
     */
    private function get_next_step($current_step) {
        $steps = array('cpu', 'wp', 'memory', 'io', 'network', 'finalize');
        $current_index = array_search($current_step, $steps);
        
        if ($current_index !== false && $current_index < count($steps) - 1) {
            return $steps[$current_index + 1];
        }
        
        return null; // No more steps
    }
    

    
    /**
     * Finalize test results by aggregating all step results
     * 
     * @param string $session_id Session identifier
     * @return array Complete aggregated results
     */
    private function finalize_test_results($session_id) {
        $session_data = $this->get_test_session($session_id);
        
        if (!$session_data) {
            return array('error' => 'Session not found for finalization');
        }
        
        $step_results = $session_data['step_results'] ?? array();
        $config = $session_data['config'] ?? array();
        
        // Initialize final results array with safe defaults
        $final_results = array(
            'test_session_id' => $session_id,
            'test_completed' => true,
            'incomplete' => false,
            'cpu_score' => 0,
            'wp_core_score' => 0,
            'memory_score' => 0,
            'io_score' => 0,
            'network_score' => 0,
            'overall_score' => 0,
            'rating' => 'error',
            'interpretation' => 'One or more tests failed to complete.',
            'cpu_score_stats' => [],
            'wp_core_score_stats' => [],
            'memory_score_stats' => []
        );
        
        // Aggregate CPU results
        if (!empty($step_results['cpu']['cpu_stats'])) {
            $cpu_data = $step_results['cpu'];
            $final_results['cpu_score'] = round($cpu_data['cpu_stats']['median'] ?? 0);
            $final_results['cpu_score_stats'] = array(
                'mean' => round($cpu_data['cpu_stats']['mean'] ?? 0, 1),
                'median' => round($cpu_data['cpu_stats']['median'] ?? 0, 1),
                'min' => round($cpu_data['cpu_stats']['min'] ?? 0, 1),
                'max' => round($cpu_data['cpu_stats']['max'] ?? 0, 1),
                'stddev' => round($cpu_data['cpu_stats']['stddev'] ?? 0, 2)
            );
            $final_results['cpu_time_stats'] = array(
                'mean' => round($cpu_data['cpu_time_stats']['mean'] ?? 0, 3),
                'median' => round($cpu_data['cpu_time_stats']['median'] ?? 0, 3),
                'min' => round($cpu_data['cpu_time_stats']['min'] ?? 0, 3),
                'max' => round($cpu_data['cpu_time_stats']['max'] ?? 0, 3),
                'stddev' => round($cpu_data['cpu_time_stats']['stddev'] ?? 0, 3)
            );
            $final_results['cpu_detailed_averages'] = $cpu_data['cpu_detailed_averages'] ?? array();
        }
        
        // Aggregate WordPress results
        if (!empty($step_results['wp']['wp_stats'])) {
            $wp_data = $step_results['wp'];
            $final_results['wp_core_score'] = round($wp_data['wp_stats']['median'] ?? 0);
            $final_results['wp_core_score_stats'] = array(
                'mean' => round($wp_data['wp_stats']['mean'] ?? 0, 1),
                'median' => round($wp_data['wp_stats']['median'] ?? 0, 1),
                'min' => round($wp_data['wp_stats']['min'] ?? 0, 1),
                'max' => round($wp_data['wp_stats']['max'] ?? 0, 1),
                'stddev' => round($wp_data['wp_stats']['stddev'] ?? 0, 2)
            );
            $final_results['wp_core_time_stats'] = array(
                'mean' => round($wp_data['wp_time_stats']['mean'] ?? 0, 3),
                'median' => round($wp_data['wp_time_stats']['median'] ?? 0, 3),
                'min' => round($wp_data['wp_time_stats']['min'] ?? 0, 3),
                'max' => round($wp_data['wp_time_stats']['max'] ?? 0, 3),
                'stddev' => round($wp_data['wp_time_stats']['stddev'] ?? 0, 3)
            );
            $final_results['wp_core_detailed_averages'] = $wp_data['wp_detailed_averages'] ?? array();
        }
        
        // Aggregate Memory results
        if (!empty($step_results['memory']['memory_stats'])) {
            $memory_data = $step_results['memory'];
            $final_results['memory_score'] = round($memory_data['memory_stats']['median'] ?? 0);
            $final_results['memory_score_stats'] = array(
                'mean' => round($memory_data['memory_stats']['mean'] ?? 0, 1),
                'median' => round($memory_data['memory_stats']['median'] ?? 0, 1),
                'min' => round($memory_data['memory_stats']['min'] ?? 0, 1),
                'max' => round($memory_data['memory_stats']['max'] ?? 0, 1),
                'stddev' => round($memory_data['memory_stats']['stddev'] ?? 0, 2)
            );
            
            if (isset($memory_data['allocation_stats'])) {
                $final_results['max_memory_allocated'] = size_format($memory_data['allocation_stats']['median'] ?? 0);
                $final_results['memory_allocation_stats'] = array(
                    'mean' => size_format($memory_data['allocation_stats']['mean'] ?? 0),
                    'median' => size_format($memory_data['allocation_stats']['median'] ?? 0),
                    'min' => size_format($memory_data['allocation_stats']['min'] ?? 0),
                    'max' => size_format($memory_data['allocation_stats']['max'] ?? 0)
                );
            }
            
            // Legacy compatibility - get from last memory test (guidance removed)
            if (!empty($memory_data['memory_results'])) {
                $last_memory = end($memory_data['memory_results']);
                $final_results['memory_limit'] = $last_memory['memory_limit'] ?? 'Unknown';
                $final_results['allocation_efficiency'] = $last_memory['allocation_efficiency'] ?? 'Unknown';
            }
        }
        
        // Add single-execution test results
        $final_results['io_score'] = $step_results['io']['io_score'] ?? 0;
        $final_results['network_score'] = $step_results['network']['network_score'] ?? 0;
        
        // Calculate overall resource score using weighted system
        // Weights: CPU 30%, WP Core 20%, Memory 15%, I/O 25%, Network 10%
        $weighted_score = 0;
        $total_weight = 0;
        
        if (isset($final_results['cpu_score']) && $final_results['cpu_score'] > 0) {
            $weighted_score += $final_results['cpu_score'] * 0.30;
            $total_weight += 0.30;
        }
        
        if (isset($final_results['wp_core_score']) && $final_results['wp_core_score'] > 0) {
            $weighted_score += $final_results['wp_core_score'] * 0.20;
            $total_weight += 0.20;
        }
        
        if (isset($final_results['memory_score']) && $final_results['memory_score'] > 0) {
            $weighted_score += $final_results['memory_score'] * 0.15;
            $total_weight += 0.15;
        }
        
        if (isset($final_results['io_score']) && $final_results['io_score'] > 0) {
            $weighted_score += $final_results['io_score'] * 0.25;
            $total_weight += 0.25;
        }
        
        if (isset($final_results['network_score']) && $final_results['network_score'] > 0) {
            $weighted_score += $final_results['network_score'] * 0.10;
            $total_weight += 0.10;
        }
        
        // Normalize the score based on available tests
        $overall_score = ($total_weight > 0) ? round($weighted_score / $total_weight) : 0;
        
        $final_results['overall_score'] = $overall_score;
        $final_results['rating'] = $this->get_rating_from_score($overall_score);
        
        // Test metadata
        $total_iterations_completed = 0;
        if (isset($step_results['cpu'])) $total_iterations_completed += $step_results['cpu']['completed_iterations'] ?? 0;
        if (isset($step_results['wp'])) $total_iterations_completed += $step_results['wp']['completed_iterations'] ?? 0;
        if (isset($step_results['memory'])) $total_iterations_completed += $step_results['memory']['completed_iterations'] ?? 0;
        
        $final_results['test_iterations_completed'] = $total_iterations_completed;
        $final_results['total_tests_run'] = count($step_results);
        
        // Calculate total sub-tests executed
        $total_sub_tests = 0;
        if (isset($step_results['cpu'])) $total_sub_tests += ($step_results['cpu']['completed_iterations'] ?? 0) * 5;
        if (isset($step_results['wp'])) $total_sub_tests += ($step_results['wp']['completed_iterations'] ?? 0) * 4;
        if (isset($step_results['memory'])) $total_sub_tests += $step_results['memory']['completed_iterations'] ?? 0;
        if (isset($step_results['io'])) $total_sub_tests += 1;
        if (isset($step_results['network'])) $total_sub_tests += 1;
        
        $final_results['total_sub_tests_executed'] = $total_sub_tests;
        
        // Test stability assessment
        $cpu_stats = $step_results['cpu']['cpu_stats'] ?? array();
        $wp_stats = $step_results['wp']['wp_stats'] ?? array();
        $memory_stats = $step_results['memory']['memory_stats'] ?? array();
        
        $final_results['test_stability'] = $this->assess_test_stability($cpu_stats, $wp_stats, $memory_stats);
        
        // Enhanced interpretation
        $final_results['interpretation'] = $this->get_enhanced_resource_interpretation($overall_score, $cpu_stats, $wp_stats);
        
        // Execution summary
        $final_results['execution_summary'] = array(
            'total_steps' => count($step_results),
            'completed_steps' => array_keys($step_results),
            'session_duration' => time() - ($session_data['start_time'] ?? time())
        );
        

        
        // Clean up the session after finalization
        delete_transient($session_id);
        
        // Clean up test configuration transient
        $user_id = get_current_user_id();
        if ($user_id) {
            delete_transient('divewp_test_config_' . $user_id);
        }
        
        return $final_results;
    }
    
    /**
     * Detect WordPress caching environment for 2025-optimized testing
     * 
     * @since 2.0.7
     * @return array Caching environment details
     */
    private function detect_wp_caching_environment() {
        $cache_info = array(
            'has_persistent_cache' => false,
            'cache_type' => 'default',
            'wp_cache_enabled' => defined('WP_CACHE') && WP_CACHE,
            'object_cache_dropin' => false,
            'recommended_test_approach' => 'database'
        );
        
        // Check for object-cache.php drop-in (2025 standard)
        if (file_exists(WP_CONTENT_DIR . '/object-cache.php')) {
            $cache_info['object_cache_dropin'] = true;
            $cache_info['has_persistent_cache'] = true;
            
            // Try to detect cache type from common implementations
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
        
        // Test if object cache is actually working with persistent storage
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
     * WordPress 2025-optimized transient testing
     * Tests the actual caching layer being used by the hosting environment
     * 
     * @since 2.0.7
     * @param array $test_config Test configuration
     * @return array Test results adapted to hosting's caching setup
     */
    private function test_wordpress_caching_performance_2025($test_config) {
        $cache_env = $this->detect_wp_caching_environment();
        $start_time = microtime(true);
        $php_max_execution_time = (int) ini_get('max_execution_time');
        $max_test_time = $php_max_execution_time > 0 ? intval($php_max_execution_time * 0.8) : 20;
        
        if ($cache_env['recommended_test_approach'] === 'object_cache') {
            // Test object cache performance (modern hosting)
            return $this->test_object_cache_performance($test_config, $max_test_time, $start_time, $cache_env);
        } else {
            // Test database-backed transients (traditional hosting)
            return $this->test_database_transients_safe($test_config, $max_test_time, $start_time);
        }
    }
    
    /**
     * Test object cache performance for modern hosting (2025)
     * 
     * @since 2.0.7
     * @param array $test_config Test configuration
     * @param int $max_test_time Maximum test time
     * @param float $start_time Test start time
     * @param array $cache_env Cache environment info
     * @return array Object cache performance results
     */
    private function test_object_cache_performance($test_config, $max_test_time, $start_time, $cache_env) {
        $operations_completed = 0;
        $total_operation_time = 0;
        
        // Test with various data sizes to simulate real WordPress usage
        $test_datasets = array(
            'small' => array('user_pref' => wp_generate_password(50, false)),
            'medium' => array('post_meta' => str_repeat('content_', 100)),
            'large' => array('query_results' => array_fill(0, 100, wp_generate_password(20, false)))
        );
        
        foreach ($test_datasets as $size => $data) {
            // Limit iterations based on hosting environment
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
                
                // Gentle throttling for hosting-friendly testing
                usleep(5000); // 5ms delay
                
                // Safety timeout check
                if ((microtime(true) - $start_time) > ($max_test_time * 0.8)) {
                    break 2;
                }
            }
        }
        
        $avg_operation_time = $operations_completed > 0 ? $total_operation_time / $operations_completed : 0;
        
        return array(
            'time' => round($total_operation_time, 3),
            'operations_completed' => $operations_completed,
            'avg_operation_time' => round($avg_operation_time * 1000, 2), // Convert to milliseconds
            'cache_type' => $cache_env['cache_type'],
            'test_approach' => 'object_cache',
            'cache_hit_ratio' => 100, // Object cache should always hit
            'performance_tier' => $this->classify_object_cache_performance($avg_operation_time)
        );
    }
    
    /**
     * Safe database transient testing for traditional hosting
     * 
     * @since 2.0.7
     * @param array $test_config Test configuration
     * @param int $max_test_time Maximum test time
     * @param float $start_time Test start time
     * @return array Database transient performance results
     */
    private function test_database_transients_safe($test_config, $max_test_time, $start_time) {
        // Use single operation approach to avoid triggering security systems
        $test_data = array(
            'wp_query_cache' => array_fill(0, 50, wp_generate_password(20, false)),
            'user_meta_cache' => wp_generate_password(100, false),
            'timestamp' => current_time('timestamp')
        );
        
        // Single transient operation with timing
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
        
        // Extrapolate performance metrics
        $estimated_ops_per_second = 3 / $total_time; // 3 operations total
        
        return array(
            'time' => round($total_time, 3),
            'operations_completed' => 3,
            'set_time' => round($set_time * 1000, 2),
            'get_time' => round($get_time * 1000, 2),
            'delete_time' => round($delete_time * 1000, 2),
            'estimated_ops_per_second' => round($estimated_ops_per_second, 1),
            'test_approach' => 'database_safe',
            'data_verified' => ($retrieved === $test_data),
            'performance_tier' => $this->classify_database_performance($total_time)
        );
    }
    
    /**
     * Classify object cache performance tier
     * 
     * @since 2.0.7
     * @param float $avg_operation_time Average operation time in seconds
     * @return string Performance tier classification
     */
    private function classify_object_cache_performance($avg_operation_time) {
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
     * Classify database transient performance tier
     * 
     * @since 2.0.7
     * @param float $total_time Total operation time in seconds
     * @return string Performance tier classification
     */
    private function classify_database_performance($total_time) {
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
     * Test network capabilities (2025-optimized for hosting safety)
     * 
     * Uses progressive testing approach:
     * 1. Single WordPress.org API test (essential for WP functionality)
     * 2. Minimal additional testing only if first succeeds
     * 3. Respects hosting security with proper delays
     */
    private function test_network_capabilities($test_config = array()) {
        $start_time = microtime(true);
        $network_results = array(
            'requests_successful' => 0,
            'total_response_time' => 0,
            'avg_response_time' => 0,
            'wordpress_api_working' => false,
            'test_approach' => '2025_hosting_safe'
        );
        
        // Level 1: Essential WordPress.org connectivity test
        $wordpress_api_test = $this->test_wordpress_api_connectivity();
        $network_results['wordpress_api_working'] = $wordpress_api_test['success'];
        
        if ($wordpress_api_test['success']) {
            $network_results['requests_successful']++;
            $network_results['total_response_time'] += $wordpress_api_test['response_time'];
            
            // Only proceed to additional tests if WordPress API works
            // This prevents unnecessary external requests on restricted hosts
            
            // Level 2: One additional reliability test (hosting-safe)
            sleep(2); // 2-second delay for hosting security compliance
            
            $reliability_test = $this->test_basic_http_reliability();
            if ($reliability_test['success']) {
                $network_results['requests_successful']++;
                $network_results['total_response_time'] += $reliability_test['response_time'];
            }
        }
        
        // Calculate performance metrics
        if ($network_results['requests_successful'] > 0) {
            $network_results['avg_response_time'] = $network_results['total_response_time'] / $network_results['requests_successful'];
        }
        
        // Score calculation for 2025 hosting environment
        $network_score = $this->calculate_network_score_2025($network_results);
        
        return max(20, min(100, $network_score));
    }
    
    /**
     * Test essential WordPress.org API connectivity
     * 
     * @since 2.0.7
     * @return array Test results
     */
    private function test_wordpress_api_connectivity() {
        $request_start = microtime(true);
        
        // Test WordPress.org API - essential for plugin/theme updates
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
     * Test basic HTTP reliability with hosting-safe endpoint
     * 
     * @since 2.0.7
     * @return array Test results
     */
    private function test_basic_http_reliability() {
        $request_start = microtime(true);
        
        // Use a simple, hosting-friendly endpoint
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
     * Calculate network score with smooth curve and simplified approach
     * 
     * Focuses on essential connectivity and response times for hosting evaluation.
     * 
     * @param array $network_results Network test results
     * @return int Network performance score (0-100)
     */
    private function calculate_network_score_2025($network_results) {
        $base_score = 30; // Conservative baseline
        
        // WordPress.org API connectivity is critical for hosting functionality
        if ($network_results['wordpress_api_working']) {
            $base_score += 40; // Major boost for essential connectivity
        }
        
        // Connection success scoring with smooth progression
        $success_rate = $network_results['requests_successful'] / max(1, 2); // Expect 2 max requests
        $base_score += ($success_rate * 20);
        
        // Response time scoring with smooth curve
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
    
    /**
     * Get resource interpretation
     */
    private function get_resource_interpretation($score) {
        if ($score >= 85) {
            return esc_html__('Excellent hosting resources! High-performance CPU, generous memory allocation, fast I/O, and unrestricted network access.', 'divewp-boost-site-performance');
        } elseif ($score >= 70) {
            return esc_html__('Good hosting resources. Adequate performance for most WooCommerce stores with room for growth.', 'divewp-boost-site-performance');
        } elseif ($score >= 50) {
            return esc_html__('Fair hosting resources. May struggle with high traffic or complex operations. Consider upgrading for better performance.', 'divewp-boost-site-performance');
        } else {
            return esc_html__('Limited hosting resources. Significant constraints on CPU, memory, or network access. Upgrade recommended for WooCommerce.', 'divewp-boost-site-performance');
        }
    }
    
    /**
     * Simple memory allocation test - allocates 80% of available memory and cleans up
     * 
     * @param float $allocation_percentage Target percentage to allocate (0.8 = 80%)
     * @return array Test results with only numeric values (no data references)
     */
    private function simple_memory_allocation_test($allocation_percentage = 0.8) {
        // FORCE COMPLETE MEMORY CLEANUP FIRST
        if (function_exists('gc_collect_cycles')) {
            for ($i = 0; $i < 10; $i++) {
                gc_collect_cycles();
            }
        }
        usleep(100000); // 100ms for cleanup
        
        // Get FRESH memory state after cleanup
        $start_memory = memory_get_usage(true);
        $memory_limit_bytes = wp_convert_hr_to_bytes(ini_get('memory_limit'));
        
        // Calculate what's ACTUALLY available NOW (not at test start)
        $current_usage = memory_get_usage(true);
        $available = $memory_limit_bytes - $current_usage;
        $target_to_allocate = max(0, $available * $allocation_percentage);
        
        // Results array - numbers only, no data references
        $results = array(
            'memory_limit' => $memory_limit_bytes,
            'start_usage' => $current_usage,
            'available' => $available,
            'target' => $target_to_allocate,
            'allocated' => 0,
            'success' => false
        );
        
        // Skip test if not enough memory available
        if ($target_to_allocate < 1048576) { // Less than 1MB available
            $results['allocated'] = 0;
            $results['success'] = false;
            $results['error'] = 'Insufficient memory available';
            return $results;
        }
        
        // Allocate memory in local scope
        $test_data = array();
        $allocated = 0;
        
        try {
            // Simple allocation loop
            $chunk = str_repeat('X', 1024); // 1KB chunks
            $chunks_needed = (int)($target_to_allocate / 1024);
            
            for ($i = 0; $i < $chunks_needed; $i++) {
                $test_data[] = $chunk . $i; // Unique data to prevent PHP optimization
                
                // Check progress every 1000 chunks (1MB)
                if ($i % 1000 === 0) {
                    $allocated = memory_get_usage(true) - $start_memory;
                    if ($allocated >= $target_to_allocate * 0.95) {
                        break; // Close enough
                    }
                }
            }
            
            // Final measurement
            $allocated = memory_get_usage(true) - $start_memory;
            $results['allocated'] = $allocated;
            $results['success'] = true;
            
        } catch (Exception $e) {
            $results['error'] = 'Allocation failed: ' . $e->getMessage();
        }
        
        // CRITICAL: Destroy data completely
        unset($test_data);
        
        // AGGRESSIVE cleanup for next iteration
        if (function_exists('gc_collect_cycles')) {
            for ($i = 0; $i < 10; $i++) {
                gc_collect_cycles();
            }
        }
        
        // Longer delay for memory to be released
        usleep(200000); // 200ms
        
        // Return ONLY the numbers, no references to allocated data
        return $results;
    }
} 