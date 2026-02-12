<?php
/**
 * CPU Performance Tests
 *
 * Replicates exact POC CPU test specifications with enhanced UX features.
 * Tests CPU performance through 5 sub-tests with exact POC configurations.
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
 * Resources CPU Tests Class
 */
class DiveWP_Resources_CPU_Tests {

    /**
     * Run CPU performance tests (exact POC replication)
     *
     * @param array $test_config POC test configuration
     * @return array Test results with enhanced UX data
     */
    public static function run($test_config = array()) {
        $start_time = microtime(true);
        
        // Use actual PHP time limit instead of hardcoded value
        $php_max_time = ini_get('max_execution_time');
        $max_test_time = $test_config['max_test_time_per_section'] ?? ($php_max_time > 0 ? $php_max_time * 0.9 : 54);
        
        // INTENSIVE CPU TEST CONFIGURATION - For Real Hosting Evaluation
        $cpu_results = array();
        $completed_operations = 0;
        $total_operations = 6; // Added intensive operations
        $test_status = 'completed';
        $timeout_reason = null;
        
        // Minimum runtime enforcement for proper stress testing
        $min_runtime = 8; // 8 seconds minimum for CPU stress
        
        try {
            // Sub-test 1: Prime Generation (INTENSIFIED)
            $prime_result = self::test_prime_generation($test_config, $start_time, $max_test_time);
            $cpu_results['prime_generation'] = $prime_result;
            if ($prime_result['test_status'] === 'completed') {
                $completed_operations++;
            }
            
            // Sub-test 2: Mathematical Operations (INTENSIFIED)
            $math_result = self::test_mathematical_operations($test_config, $start_time, $max_test_time);
            $cpu_results['mathematical_operations'] = $math_result;
            if ($math_result['test_status'] === 'completed') {
                $completed_operations++;
            }
            
            // Sub-test 3: Fibonacci Sequence (NEW INTENSIVE TEST)
            $fibonacci_result = self::test_fibonacci_sequence($test_config, $start_time, $max_test_time);
            $cpu_results['fibonacci_sequence'] = $fibonacci_result;
            if ($fibonacci_result['test_status'] === 'completed') {
                $completed_operations++;
            }
            
            // Sub-test 4: Conditional Logic (INTENSIFIED)
            $conditional_result = self::test_conditional_logic($test_config, $start_time, $max_test_time);
            $cpu_results['conditional_logic'] = $conditional_result;
            if ($conditional_result['test_status'] === 'completed') {
                $completed_operations++;
            }
            
            // Sub-test 5: String Processing (INTENSIFIED)
            $string_result = self::test_string_processing($test_config, $start_time, $max_test_time);
            $cpu_results['string_processing'] = $string_result;
            if ($string_result['test_status'] === 'completed') {
                $completed_operations++;
            }
            
            // Sub-test 6: Array Operations (INTENSIFIED)
            $array_result = self::test_array_operations($test_config, $start_time, $max_test_time);
            $cpu_results['array_operations'] = $array_result;
            if ($array_result['test_status'] === 'completed') {
                $completed_operations++;
            }
            
        } catch (Exception $e) {
            $test_status = 'error';
            $timeout_reason = 'CPU test error: ' . $e->getMessage();
        }
        
        $total_time = microtime(true) - $start_time;
        
        // MINIMUM RUNTIME ENFORCEMENT - Keep testing until minimum time reached
        if ($total_time < $min_runtime && $test_status === 'completed') {
            $additional_iterations = self::run_additional_stress_cycles($start_time, $min_runtime, $max_test_time);
            $cpu_results['additional_stress_cycles'] = $additional_iterations;
            $total_time = microtime(true) - $start_time;
        }
        
        // Calculate overall CPU score using intensive method
        $cpu_score = self::calculate_cpu_score($cpu_results, $total_time);
        
        // DEBUG: Log CPU scoring details for troubleshooting
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("DiveWP CPU Test Debug - Total Time: {$total_time}s, Completed Ops: {$completed_operations}, Total Ops: {$total_operations}, Score: {$cpu_score}"); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- BENCHMARK REQUIREMENT: minimal debug logging gated by WP_DEBUG
        }
        
        // Calculate operations per second for UI display
        $operations_per_second = $total_time > 0 ? round($completed_operations / $total_time, 1) : 0;
        
        // DEBUG: Log CPU operations calculation details
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("DiveWP CPU Ops Calculation - Completed: {$completed_operations}, Time: {$total_time}, Raw: " . ($total_time > 0 ? ($completed_operations / $total_time) : 0) . ", Rounded: {$operations_per_second}"); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- BENCHMARK REQUIREMENT: minimal debug logging gated by WP_DEBUG
        }
        
        return array(
            'score' => $cpu_score,
            'total_time' => round($total_time, 3),
            'completed_operations' => $completed_operations,
            'total_operations' => $total_operations,
            'operations_per_second' => $operations_per_second,
            'status' => $test_status,
            'timeout_reason' => $timeout_reason,
            'sub_test_results' => $cpu_results,
            'throttling_detected' => self::detect_cpu_throttling($cpu_results),
            'hosting_limitations' => self::analyze_hosting_limitations($cpu_results, $total_time),
            'performance_interpretation' => (function() use ($cpu_score, $total_time, $completed_operations, $total_operations, $operations_per_second, $test_status, $timeout_reason) {
                // DEBUG: Log parameters being passed to performance interpretation
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("DiveWP CPU Performance Interpretation Params - Score: {$cpu_score}, Time: {$total_time}, Completed: {$completed_operations}, Total: {$total_operations}, Ops/sec: {$operations_per_second}, Status: {$test_status}"); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- BENCHMARK REQUIREMENT: minimal debug logging gated by WP_DEBUG
                }
                return DiveWP_Benchmark_Resources_Scoring::get_sub_test_performance_interpretation('cpu_tests', array(
                'score' => $cpu_score,
                'total_time' => $total_time,
                'completed_operations' => $completed_operations,
                'total_operations' => $total_operations,
                'operations_per_second' => $operations_per_second,
                'status' => $test_status,
                'timeout_reason' => $timeout_reason
            ));
            })()
        );
    }

    /**
     * CPU Sub-test 1: Prime Generation (INTENSIFIED for Real Hosting Evaluation)
     * 
     * @param array $test_config Test configuration
     * @param float $start_time Overall test start time
     * @param int $max_test_time Maximum test time
     * @return array Test results
     */
    private static function test_prime_generation($test_config, $start_time, $max_test_time) {
        $test_start_time = microtime(true);
        
        // INTENSIFIED: 10x more iterations for proper CPU stress (with safety limits)
        $base_iterations = $test_config['cpu_math_iterations'] ?? 10000;
        $math_iterations = min($base_iterations * 10, 200000); // Cap at 200,000 for safety
        
        // INTENSIFIED: More aggressive chunking for better throttling detection
        $chunk_size = max(500, $math_iterations / 50); // Smaller chunks for more granular monitoring
        $primes_found = 0;
        $primes_checked = 0;
        $chunks_completed = 0;
        $throttling_detected = false;
        $iteration_times = array();
        $test_status = 'completed';
        
        // INTENSIFIED: Add CPU-intensive mathematical operations per prime check
        $cpu_stress_multiplier = 3; // Triple the CPU work per iteration
        
        try {
            for ($chunk_start = 2; $chunk_start < $math_iterations; $chunk_start += $chunk_size) {
                $chunk_end = min($chunk_start + $chunk_size, $math_iterations);
                $chunk_time_start = microtime(true);
                
                for ($i = $chunk_start; $i < $chunk_end; $i++) {
                    // INTENSIFIED: More sophisticated prime checking with CPU stress
                    $is_prime = true;
                    $sqrt = sqrt($i);
                    
                    // Add intensive mathematical operations to stress CPU
                    for ($stress = 0; $stress < $cpu_stress_multiplier; $stress++) {
                        $dummy = sin($i + $stress) * cos($i - $stress) + sqrt($i * $stress + 1);
                        $dummy = pow($dummy, 2) + log(max(1, $i + $stress));
                    }
                    
                    // Enhanced prime checking algorithm (more CPU intensive)
                    for ($j = 2; $j <= $sqrt; $j++) {
                        if ($i % $j === 0) {
                            $is_prime = false;
                            break;
                        }
                        
                                                // INTENSIFIED: Add modular arithmetic stress
                        if ($j % 3 === 0) {
                            $modular_result = (int)(($i * $j) % 97) + (int)(($i + $j) % 101);
                            $is_prime = $is_prime && ($modular_result % 2 !== 0);
                        }
                    }
                    
                    if ($is_prime) {
                        $primes_found++;
                        
                        // INTENSIFIED: Verify prime with additional CPU work
                        $verification_sum = 0;
                        for ($v = 2; $v < (int)min(50, $i); $v++) {
                            $verification_sum += ($i % $v === 0) ? 1 : 0;
                        }
                        if ($verification_sum > 0) {
                            $primes_found--; // False positive correction
                        }
                    }
                    $primes_checked++;
                }
                
                $chunk_time = microtime(true) - $chunk_time_start;
                $iteration_times[] = $chunk_time;
                $chunks_completed++;
                
                // INTENSIFIED: More sensitive throttling detection
                if (count($iteration_times) >= 3) {
                    $recent_avg = array_sum(array_slice($iteration_times, -3)) / 3;
                    $initial_avg = array_sum(array_slice($iteration_times, 0, 3)) / 3;
                    
                    // Lower threshold for throttling detection (shared hosting often throttles gradually)
                    if ($recent_avg > ($initial_avg * 1.8)) {
                        $throttling_detected = true;
                    }
                }
                
                // INTENSIFIED: Timeout check at 90% of max_test_time to allow proper completion
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
            'primes_found' => $primes_found,
            'primes_checked' => $primes_checked,
            'chunks_completed' => $chunks_completed,
            'throttling_detected' => $throttling_detected,
            'performance_consistency' => self::calculate_consistency_score($iteration_times),
            'test_status' => $test_status,
            'completed_operations' => $primes_checked,
            'total_operations' => $math_iterations,
            'iteration_times' => $iteration_times, // Store for throttling analysis
            'cpu_stress_applied' => $cpu_stress_multiplier,
            'hosting_grade' => self::determine_hosting_grade_from_prime_test($time, $primes_checked, $throttling_detected)
        );
    }

    /**
     * CPU Sub-test 2: Mathematical Operations (INTENSIFIED for Real Hosting Evaluation)
     * 
     * @param array $test_config Test configuration
     * @param float $start_time Overall test start time
     * @param int $max_test_time Maximum test time
     * @return array Test results
     */
    private static function test_mathematical_operations($test_config, $start_time, $max_test_time) {
        $test_start_time = microtime(true);
        
        // INTENSIFIED: 10x more iterations for proper CPU stress (with safety limits)
        $base_iterations = $test_config['cpu_math_iterations'] ?? 10000;
        $math_iterations = min($base_iterations * 10, 150000); // Cap at 150,000 for safety
        
        $math_results = 0;
        $math_ops_completed = 0;
        $cpu_intensive_ops = 0;
        $test_status = 'completed';
        
        // INTENSIFIED: Much more complex mathematical operations
        $matrix_operations = 0;
        $trigonometric_operations = 0;
        $logarithmic_operations = 0;
        
        try {
            for ($i = 1; $i <= $math_iterations; $i++) {
                // INTENSIFIED: Exponentially more CPU-intensive mathematical calculations
                $base_calc = sin($i) * cos($i) + sqrt($i) * log(max(1, $i));
                $exponential_calc = pow($i % 100, 3) + exp(min($i % 10, 5));
                $trigonometric_calc = atan2($i, max(1, $i % 50)) * tan(min($i % 20, 10));
                
                // INTENSIFIED: Add matrix-like operations (very CPU intensive)
                $matrix_result = 0;
                for ($row = 0; $row < 5; $row++) {
                    for ($col = 0; $col < 5; $col++) {
                        $matrix_value = (($i + $row) * ($i + $col)) % 1000;
                        $matrix_result += sin($matrix_value) * cos($matrix_value);
                        $matrix_operations++;
                    }
                }
                
                // INTENSIFIED: Complex trigonometric series (hosting stress test)
                $trig_series = 0;
                for ($k = 1; $k <= 20; $k++) {
                    $trig_series += sin($i / $k) * cos($i * $k) / max(1, $k);
                    $trig_series += atan($i * $k) / max(1, sqrt($k));
                    $trigonometric_operations++;
                }
                
                // INTENSIFIED: Logarithmic and exponential stress operations
                $log_exp_result = 0;
                for ($l = 1; $l <= 15; $l++) {
                    $log_exp_result += log(max(1, $i + $l)) * exp(min($l / 10, 2));
                    $log_exp_result += pow(($i % 10) + 1, min($l / 5, 3));
                    $logarithmic_operations++;
                }
                
                // INTENSIFIED: 15 intensive ops per iteration (was 5)
                for ($j = 0; $j < 15; $j++) {
                    $result = ($base_calc + $exponential_calc + $matrix_result + $trig_series + $log_exp_result) / max(1, $trigonometric_calc);
                    $result = round($result * 10000) / 10000; // Force high-precision operations
                    
                    // Add modular arithmetic complexity
                    $modular_stress = fmod($result * 1000, 97) + fmod($result * 2000, 101);
                    $result += sin($modular_stress) * cos($modular_stress / 2);
                    
                    $cpu_intensive_ops++;
                }
                
                $math_results += $result;
                $math_ops_completed++;
                
                // INTENSIFIED: Timeout check at 90% of max_test_time to allow proper completion
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
            'operations_completed' => $math_ops_completed,
            'intensive_ops_completed' => $cpu_intensive_ops,
            'matrix_operations' => $matrix_operations,
            'trigonometric_operations' => $trigonometric_operations,
            'logarithmic_operations' => $logarithmic_operations,
            'operations_per_second' => round($math_ops_completed / max(0.001, $time), 1),
            'intensive_ops_per_second' => round($cpu_intensive_ops / max(0.001, $time), 1),
            'math_result_sum' => round($math_results, 3),
            'test_status' => $test_status,
            'completed_operations' => $math_ops_completed,
            'total_operations' => $math_iterations,
            'hosting_grade' => self::determine_hosting_grade_from_math_test($time, $math_ops_completed, $cpu_intensive_ops)
        );
    }

    /**
     * CPU Sub-test 3: Fibonacci Sequence - NEW INTENSIVE TEST
     * 
     * @param array $test_config Test configuration
     * @param float $start_time Overall test start time
     * @param int $max_test_time Maximum test time
     * @return array Test results
     */
    private static function test_fibonacci_sequence($test_config, $start_time, $max_test_time) {
        $test_start_time = microtime(true);
        
        // Clear memoization cache for consistent testing
        self::$fibonacci_memo = array();
        
        // INTENSIVE: Fibonacci calculations are extremely CPU demanding
        $fibonacci_iterations = 100; // Reduced from 1000 to prevent timeouts while still testing thoroughly
        
        $fibonacci_results = 0;
        $sequences_calculated = 0;
        $recursive_calls = 0;
        $iterative_calls = 0;
        $test_status = 'completed';
        
        // Matrix multiplication for Fibonacci (very CPU intensive)
        $matrix_multiplications = 0;
        $golden_ratio_calculations = 0;
        
        try {
            for ($i = 1; $i <= $fibonacci_iterations; $i++) {
                // Method 1: Recursive Fibonacci (extremely CPU intensive for large numbers)
                // Limit to max 25 for recursive to prevent exponential explosion
                $recursive_fib = self::fibonacci_recursive((int)min(25, $i % 26)); // Reduced from 35 to 25
                $recursive_calls++;
                
                // Method 2: Iterative Fibonacci with stress operations
                $iterative_fib = self::fibonacci_iterative_with_stress((int)($i % 100), (int)$i);
                $iterative_calls++;
                
                // Method 3: Matrix-based Fibonacci (CPU and memory intensive)
                if ($i % 10 === 0) {
                    $matrix_fib = self::fibonacci_matrix_method((int)min(50, $i));
                    $matrix_multiplications++;
                }
                
                // Method 4: Golden ratio approximation with intensive calculations
                $golden_ratio_fib = self::fibonacci_golden_ratio_method((int)($i % 75));
                $golden_ratio_calculations++;
                
                // INTENSIVE: Verify results with cross-validation (more CPU work)
                $verification_passed = true;
                if ($i % 20 === 0) {
                                            $verification_passed = self::verify_fibonacci_results(
                            $recursive_fib, 
                            $iterative_fib, 
                            $golden_ratio_fib, 
                            (int)min(25, $i % 26)
                        );
                }
                
                // Calculate final result with intensive operations
                $sequence_result = ($recursive_fib + $iterative_fib + $golden_ratio_fib) / 3;
                
                // Add CPU stress with modular arithmetic
                for ($mod = 2; $mod <= 10; $mod++) {
                    $sequence_result += (float)(fmod($sequence_result, $mod)) * sin($mod * $i);
                }
                
                $fibonacci_results += $sequence_result;
                $sequences_calculated++;
                
                // INTENSIVE: Timeout check at 90% of max_test_time to allow proper completion
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
            'sequences_calculated' => $sequences_calculated,
            'recursive_calls' => $recursive_calls,
            'iterative_calls' => $iterative_calls,
            'matrix_multiplications' => $matrix_multiplications,
            'golden_ratio_calculations' => $golden_ratio_calculations,
            'sequences_per_second' => round($sequences_calculated / max(0.001, $time), 1),
            'fibonacci_result_sum' => round($fibonacci_results, 3),
            'test_status' => $test_status,
            'completed_operations' => $sequences_calculated,
            'total_operations' => $fibonacci_iterations,
            'hosting_grade' => self::determine_hosting_grade_from_fibonacci_test($time, $sequences_calculated, $recursive_calls)
        );
    }
    
    /**
     * Recursive Fibonacci calculation with memoization to prevent exponential time
     */
    private static $fibonacci_memo = array();
    
    private static function fibonacci_recursive($n) {
        if ($n <= 1) {
            return $n;
        }
        
        // Check memoization cache
        if (isset(self::$fibonacci_memo[$n])) {
            return self::$fibonacci_memo[$n];
        }
        
        // Add CPU stress to each recursive call
        $stress_result = sin($n) * cos($n) + sqrt($n);
        
        // Calculate and memoize result
        $result = self::fibonacci_recursive($n - 1) + self::fibonacci_recursive($n - 2) + ($stress_result * 0.0001);
        self::$fibonacci_memo[$n] = $result;
        
        return $result;
    }
    
    /**
     * Iterative Fibonacci with stress operations
     */
    private static function fibonacci_iterative_with_stress($n, $stress_multiplier) {
        if ($n <= 1) {
            return $n;
        }
        
        $prev = 0;
        $curr = 1;
        
        for ($i = 2; $i <= $n; $i++) {
            $next = $prev + $curr;
            
            // Add CPU stress operations (reduced for better performance)
            $stress_ops = 0;
            $stress_limit = min(($stress_multiplier % 3 + 1), 3); // Max 3 iterations instead of 5
            for ($s = 0; $s < $stress_limit; $s++) {
                $stress_ops += sin($i + $s) * cos($i - $s) + sqrt($i * $s + 1);
                $stress_ops += pow(($i % 10) + 1, min($s + 1, 2)); // Reduced power from 3 to 2
            }
            
            $next += ($stress_ops * 0.0001); // Minimal impact on result, maximum CPU stress
            
            $prev = $curr;
            $curr = $next;
        }
        
        return $curr;
    }
    
    /**
     * Matrix-based Fibonacci calculation (memory and CPU intensive)
     */
    private static function fibonacci_matrix_method($n) {
        if ($n <= 1) {
            return $n;
        }
        
        // Matrix multiplication approach - very CPU intensive
        $base_matrix = array(array(1, 1), array(1, 0));
        $result_matrix = array(array(1, 0), array(0, 1)); // Identity matrix
        
        $temp_n = (int)($n - 1);
        while ($temp_n > 0) {
            if ($temp_n % 2 === 1) {
                $result_matrix = self::multiply_matrices($result_matrix, $base_matrix);
            }
            $base_matrix = self::multiply_matrices($base_matrix, $base_matrix);
            $temp_n = (int)($temp_n / 2);
        }
        
        return $result_matrix[0][0];
    }
    
    /**
     * Matrix multiplication helper (CPU intensive)
     */
    private static function multiply_matrices($a, $b) {
        $result = array(array(0, 0), array(0, 0));
        
        for ($i = 0; $i < 2; $i++) {
            for ($j = 0; $j < 2; $j++) {
                for ($k = 0; $k < 2; $k++) {
                    $result[$i][$j] += $a[$i][$k] * $b[$k][$j];
                    
                    // Add CPU stress to matrix operations
                    $stress = sin($result[$i][$j]) * cos($result[$i][$j]);
                    $result[$i][$j] += ($stress * 0.00001);
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Golden ratio Fibonacci approximation with intensive calculations
     */
    private static function fibonacci_golden_ratio_method($n) {
        if ($n <= 1) {
            return $n;
        }
        
        // Calculate golden ratio with high precision (CPU intensive)
        $golden_ratio = (1 + sqrt(5)) / 2;
        $inverse_golden_ratio = (1 - sqrt(5)) / 2;
        
        // Add intensive trigonometric calculations
        for ($precision = 0; $precision < 10; $precision++) {
            $golden_ratio += (sin($n + $precision) * cos($n - $precision)) * 0.00001;
            $inverse_golden_ratio += (tan($n * $precision + 1) * atan($n + $precision)) * 0.00001;
        }
        
        // Binet's formula with stress operations
        $result = (pow($golden_ratio, $n) - pow($inverse_golden_ratio, $n)) / sqrt(5);
        
        // Add verification stress
        $verification_stress = 0;
        for ($v = 1; $v <= (int)min(20, $n); $v++) {
            $verification_stress += log(max(1, $v)) * exp(min($v / 10, 2));
        }
        
        return (int)round($result + ($verification_stress * 0.0001));
    }
    
    /**
     * Verify Fibonacci results accuracy (additional CPU stress)
     */
    private static function verify_fibonacci_results($recursive, $iterative, $golden_ratio, $n) {
        // Cross-validation with tolerance for floating point differences
        $tolerance = max(1, $n * 0.01);
        
        $recursive_vs_iterative = abs($recursive - $iterative) <= $tolerance;
        $iterative_vs_golden = abs($iterative - $golden_ratio) <= $tolerance;
        
        // Add CPU stress to verification (reduced from 50 to 10 iterations)
        $verification_calculations = 0;
        for ($i = 0; $i < 10; $i++) {
            $verification_calculations += sin((float)$recursive + $i) * cos((float)$iterative - $i);
            $verification_calculations += sqrt((float)$golden_ratio * $i + 1);
        }
        
        return $recursive_vs_iterative && $iterative_vs_golden;
    }

    /**
     * CPU Sub-test 4: Conditional Logic (INTENSIFIED for Real Hosting Evaluation)
     * 
     * @param array $test_config Test configuration
     * @param float $start_time Overall test start time
     * @param int $max_test_time Maximum test time
     * @return array Test results
     */
    private static function test_conditional_logic($test_config, $start_time, $max_test_time) {
        $test_start_time = microtime(true);
        $base_conditional_iterations = $test_config['cpu_conditional_iterations'] ?? 100000;
        $conditional_iterations = (int)min($base_conditional_iterations, 500000); // Cap at 500,000 for safety
        
        $conditional_results = 0;
        $conditions_processed = 0;
        $test_status = 'completed';
        
        try {
            for ($i = 0; $i < $conditional_iterations; $i++) {
                // POC specification: Complex conditional logic testing
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
                
                // POC specification: Nested conditional for complexity
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
            'conditions_processed' => $conditions_processed,
            'conditional_result_sum' => $conditional_results,
            'test_status' => $test_status,
            'completed_operations' => $conditions_processed,
            'total_operations' => $conditional_iterations
        );
    }

    /**
     * CPU Sub-test 4: String Processing (POC exact specification)
     * 
     * @param array $test_config Test configuration
     * @param float $start_time Overall test start time
     * @param int $max_test_time Maximum test time
     * @return array Test results
     */
    private static function test_string_processing($test_config, $start_time, $max_test_time) {
        $test_start_time = microtime(true);
        
        // POC specification: Fixed 200 iterations, 100KB+ base text
        $base_text = str_repeat('Lorem ipsum dolor sit amet consectetur adipiscing elit. ', 2000); // 100KB+
        $strings_processed = 0;
        $memory_operations = 0;
        $test_status = 'completed';
        $final_string_length = 0;
        
        try {
            for ($i = 0; $i < 200; $i++) { // POC specification: Fixed 200 iterations
                $working_text = $base_text . str_repeat(" Additional content $i ", 100);
                
                // POC specification: More memory and CPU intensive string operations
                $processed = strtoupper($working_text);
                $processed = str_replace(array('LOREM', 'IPSUM', 'DOLOR'), array('WORDPRESS', 'HOSTING', 'PERFORMANCE'), $processed);
                $processed = substr($processed, 0, (int)(strlen($processed) / 2));
                $processed = strrev($processed);
                
                // POC specification: Multiple memory allocations to stress shared hosting limits
                $temp_array = str_split($processed, 100);
                $rejoined = implode('-', $temp_array);
                $final = str_shuffle(substr($rejoined, 0, min(10000, strlen($rejoined))));
                
                $memory_operations += 3; // Track memory-intensive operations
                $strings_processed++;
                $final_string_length = strlen($final);
                
                // Clean up to prevent memory accumulation
                unset($working_text, $processed, $temp_array, $rejoined);
                
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
            'strings_processed' => $strings_processed,
            'memory_operations' => $memory_operations,
            'memory_efficiency' => round($memory_operations / max(0.001, $time), 1),
            'final_string_length' => $final_string_length,
            'test_status' => $test_status,
            'completed_operations' => $strings_processed,
            'total_operations' => 200
        );
    }

    /**
     * CPU Sub-test 5: Array Operations (POC exact specification)
     * 
     * @param array $test_config Test configuration
     * @param float $start_time Overall test start time
     * @param int $max_test_time Maximum test time
     * @return array Test results
     */
    private static function test_array_operations($test_config, $start_time, $max_test_time) {
        $test_start_time = microtime(true);
        
        // POC specification: Fixed 30,000 array size, 5 operations total
        $large_array = array();
        $operations_completed = 0;
        $test_status = 'completed';
        
        try {
            // Create larger, more complex array (POC specification)
            for ($i = 0; $i < 30000; $i++) {
                $large_array[] = array(
                    'id' => $i,
                    'value' => wp_rand(1, 100000),
                    'text' => 'item_' . wp_generate_password(10, false),
                    'meta' => array('type' => $i % 10, 'active' => ($i % 3 === 0))
                );
            }
            $operations_completed++;
            
            // POC specification: Intensive array operations that stress shared hosting
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
            
        } catch (Exception $e) {
            $test_status = 'error';
        }
        
        $time = microtime(true) - $test_start_time;
        
        // POC specification: Memory cleanup required
        unset($large_array, $filtered, $mapped, $chunks, $reassembled);
        
        return array(
            'time' => round($time, 3),
            'array_size' => 30000,
            'operations_performed' => $operations_completed,
            'operations_per_second' => round($operations_completed / max(0.001, $time), 1),
            'memory_intensive' => true,
            'test_status' => $test_status,
            'completed_operations' => $operations_completed,
            'total_operations' => 5
        );
    }
    
    /**
     * Calculate performance consistency score (POC method)
     * 
     * @param array $iteration_times Array of timing measurements
     * @return float Consistency score (0-100, higher is more consistent)
     */
    private static function calculate_consistency_score($iteration_times) {
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
     * Calculate CPU score using STRICT REALISTIC method for 2025 hosting evaluation
     * 
     * @param array $cpu_results CPU sub-test results
     * @param float $total_time Total CPU test time
     * @return int CPU score (0-100)
     */
    private static function calculate_cpu_score($cpu_results, $total_time) {
        if ($total_time <= 0) {
            return 100;
        }
        
        // STRICT THRESHOLDS for realistic hosting differentiation
        $excellent_time = 3.0;   // Premium dedicated servers (was 1.0s - too easy)
        $good_time = 6.0;        // Quality VPS hosting (was too generous)
        $fair_time = 12.0;       // Standard shared hosting
        $poor_time = 20.0;       // Limited/oversold shared hosting
        
        $base_score = 0;
        
        // Primary scoring based on total execution time (STRICT)
        if ($total_time <= $excellent_time) {
            $base_score = 90 + (10 * (($excellent_time - $total_time) / $excellent_time));
        } elseif ($total_time <= $good_time) {
            // Linear interpolation between excellent and good
            $range = $good_time - $excellent_time;
            $position = $total_time - $excellent_time;
            $base_score = 75 + (15 * (1 - ($position / $range)));
        } elseif ($total_time <= $fair_time) {
            // Linear interpolation between good and fair
            $range = $fair_time - $good_time;
            $position = $total_time - $good_time;
            $base_score = 50 + (25 * (1 - ($position / $range)));
        } elseif ($total_time <= $poor_time) {
            // Linear interpolation between fair and poor
            $range = $poor_time - $fair_time;
            $position = $total_time - $fair_time;
            $base_score = 25 + (25 * (1 - ($position / $range)));
        } else {
            // Very poor performance - shared hosting with severe limitations
            $base_score = max(5, 25 - (($total_time - $poor_time) * 2));
        }
        
        // PENALTIES for hosting limitations (STRICT)
        $penalty = 0;
        
        // Throttling detection penalty
        if (isset($cpu_results['prime_generation']['throttling_detected']) && 
            $cpu_results['prime_generation']['throttling_detected']) {
            $penalty += 15; // Major penalty for CPU throttling
        }
        
        // Timeout penalties (STRICT)
        $timeout_count = 0;
        foreach ($cpu_results as $test_name => $result) {
            if (isset($result['test_status']) && $result['test_status'] === 'timeout') {
                $timeout_count++;
            }
        }
        
        if ($timeout_count > 0) {
            $penalty += ($timeout_count * 10); // 10 points per timeout
        }
        
        // Performance consistency penalty
        if (isset($cpu_results['prime_generation']['performance_consistency'])) {
            $consistency = $cpu_results['prime_generation']['performance_consistency'];
            if ($consistency < 70) {
                $penalty += (70 - $consistency) / 5; // Up to 14 point penalty for inconsistency
            }
        }
        
        // Operations completion penalty (STRICT)
        $total_operations_expected = 0;
        $total_operations_completed = 0;
        
        foreach ($cpu_results as $test_name => $result) {
            if (isset($result['total_operations']) && isset($result['completed_operations'])) {
                $total_operations_expected += $result['total_operations'];
                $total_operations_completed += $result['completed_operations'];
            }
        }
        
        if ($total_operations_expected > 0) {
            $completion_rate = $total_operations_completed / $total_operations_expected;
            if ($completion_rate < 1.0) {
                $penalty += ((1.0 - $completion_rate) * 20); // Up to 20 point penalty for incomplete operations
            }
        }
        
        // BONUSES for excellent performance (STRICT - only for truly exceptional performance)
        $bonus = 0;
        
        // Fibonacci test bonus (only for very fast execution)
        if (isset($cpu_results['fibonacci_sequence']['time']) && 
            $cpu_results['fibonacci_sequence']['time'] < 2.0) {
            $bonus += 3; // Small bonus for excellent recursive performance
        }
        
        // Mathematical operations bonus (only for very fast execution)
        if (isset($cpu_results['mathematical_operations']['intensive_ops_per_second'])) {
            $intensive_ops_per_sec = $cpu_results['mathematical_operations']['intensive_ops_per_second'];
            if ($intensive_ops_per_sec > 100000) {
                $bonus += 2; // Small bonus for excellent mathematical processing
            }
        }
        
        // Calculate final score with STRICT bounds
        $final_score = $base_score - $penalty + $bonus;
        
        return max(5, min(100, round($final_score)));
    }
    
    /**
     * Detect CPU throttling based on prime generation test results.
     *
     * @param array $cpu_results Array of CPU sub-test results.
     * @return bool True if throttling detected, false otherwise.
     */
    private static function detect_cpu_throttling($cpu_results) {
        $prime_generation_result = $cpu_results['prime_generation'] ?? null;
        if (!$prime_generation_result) {
            return false;
        }

        $iteration_times = $prime_generation_result['iteration_times'] ?? array();
        if (count($iteration_times) < 3) {
            return false; // Not enough data
        }

        $recent_avg = array_sum(array_slice($iteration_times, -3)) / 3;
        $initial_avg = array_sum(array_slice($iteration_times, 0, 3)) / 3;

        return $recent_avg > ($initial_avg * 2.5);
    }

    /**
     * Analyze hosting limitations based on CPU test results.
     *
     * @param array $cpu_results Array of CPU sub-test results.
     * @param float $total_time Total CPU test time.
     * @return array Array of hosting limitations.
     */
    private static function analyze_hosting_limitations($cpu_results, $total_time) {
        $limitations = array();

        $prime_generation_result = $cpu_results['prime_generation'] ?? null;
        if ($prime_generation_result) {
            $primes_checked = $prime_generation_result['primes_checked'] ?? 0;
            $chunks_completed = $prime_generation_result['chunks_completed'] ?? 0;
            $throttling_detected = $prime_generation_result['throttling_detected'] ?? false;

            if ($primes_checked > 0 && $chunks_completed > 0) {
                $limitations['prime_generation'] = array(
                    'primes_checked' => $primes_checked,
                    'chunks_completed' => $chunks_completed,
                    'throttling_detected' => $throttling_detected
                );
            }
        }

        $mathematical_operations_result = $cpu_results['mathematical_operations'] ?? null;
        if ($mathematical_operations_result) {
            $operations_completed = $mathematical_operations_result['operations_completed'] ?? 0;
            $intensive_ops_completed = $mathematical_operations_result['intensive_ops_completed'] ?? 0;
            $matrix_operations = $mathematical_operations_result['matrix_operations'] ?? 0;
            $trigonometric_operations = $mathematical_operations_result['trigonometric_operations'] ?? 0;
            $logarithmic_operations = $mathematical_operations_result['logarithmic_operations'] ?? 0;

            if ($operations_completed > 0 && $intensive_ops_completed > 0) {
                $limitations['mathematical_operations'] = array(
                    'operations_completed' => $operations_completed,
                    'intensive_ops_completed' => $intensive_ops_completed,
                    'matrix_operations' => $matrix_operations,
                    'trigonometric_operations' => $trigonometric_operations,
                    'logarithmic_operations' => $logarithmic_operations
                );
            }
        }

        $fibonacci_sequence_result = $cpu_results['fibonacci_sequence'] ?? null;
        if ($fibonacci_sequence_result) {
            $sequences_calculated = $fibonacci_sequence_result['sequences_calculated'] ?? 0;
            $recursive_calls = $fibonacci_sequence_result['recursive_calls'] ?? 0;
            $iterative_calls = $fibonacci_sequence_result['iterative_calls'] ?? 0;
            $matrix_multiplications = $fibonacci_sequence_result['matrix_multiplications'] ?? 0;
            $golden_ratio_calculations = $fibonacci_sequence_result['golden_ratio_calculations'] ?? 0;

            if ($sequences_calculated > 0 && $recursive_calls > 0) {
                $limitations['fibonacci_sequence'] = array(
                    'sequences_calculated' => $sequences_calculated,
                    'recursive_calls' => $recursive_calls,
                    'iterative_calls' => $iterative_calls,
                    'matrix_multiplications' => $matrix_multiplications,
                    'golden_ratio_calculations' => $golden_ratio_calculations
                );
            }
        }

        $string_processing_result = $cpu_results['string_processing'] ?? null;
        if ($string_processing_result) {
            $strings_processed = $string_processing_result['strings_processed'] ?? 0;
            $memory_operations = $string_processing_result['memory_operations'] ?? 0;

            if ($strings_processed > 0 && $memory_operations > 0) {
                $limitations['string_processing'] = array(
                    'strings_processed' => $strings_processed,
                    'memory_operations' => $memory_operations
                );
            }
        }

        $array_operations_result = $cpu_results['array_operations'] ?? null;
        if ($array_operations_result) {
            $array_size = $array_operations_result['array_size'] ?? 0;
            $operations_performed = $array_operations_result['operations_performed'] ?? 0;

            if ($array_size > 0 && $operations_performed > 0) {
                $limitations['array_operations'] = array(
                    'array_size' => $array_size,
                    'operations_performed' => $operations_performed
                );
            }
        }

        return $limitations;
    }

    /**
     * Run additional stress cycles to reach minimum runtime.
     *
     * @param float $start_time Overall test start time.
     * @param float $min_runtime Minimum required runtime.
     * @param int $max_test_time Maximum test time.
     * @return array Array of results for additional cycles.
     */
    private static function run_additional_stress_cycles($start_time, $min_runtime, $max_test_time) {
        // Create default test config to avoid undefined variable errors
        $test_config = array(
            'cpu_math_iterations' => 10000,
            'cpu_conditional_iterations' => 100000,
            'max_test_time_per_section' => $max_test_time
        );
        
        $additional_results = array();
        $additional_completed_operations = 0;
        $additional_total_operations = 0;
        $additional_test_status = 'completed';
        $additional_timeout_reason = null;

        try {
            // Sub-test 1: Prime Generation (INTENSIFIED)
            if ((microtime(true) - $start_time) < ($max_test_time * 0.9)) {
                $prime_result = self::test_prime_generation($test_config, $start_time, $max_test_time);
                $additional_results['prime_generation'] = $prime_result;
                if ($prime_result['test_status'] === 'completed') {
                    $additional_completed_operations++;
                }
            } else {
                $additional_test_status = 'timeout';
                $additional_timeout_reason = 'Prime generation test exceeded time limit';
            }
            
            // Sub-test 2: Mathematical Operations (INTENSIFIED)
            if ((microtime(true) - $start_time) < ($max_test_time * 0.9) && $additional_test_status === 'completed') {
                $math_result = self::test_mathematical_operations($test_config, $start_time, $max_test_time);
                $additional_results['mathematical_operations'] = $math_result;
                if ($math_result['test_status'] === 'completed') {
                    $additional_completed_operations++;
                }
            } else if ($additional_test_status === 'completed') {
                $additional_test_status = 'timeout';
                $additional_timeout_reason = 'Mathematical operations test exceeded time limit';
            }
            
            // Sub-test 3: Fibonacci Sequence (NEW INTENSIVE TEST)
            if ((microtime(true) - $start_time) < ($max_test_time * 0.9) && $additional_test_status === 'completed') {
                $fibonacci_result = self::test_fibonacci_sequence($test_config, $start_time, $max_test_time);
                $additional_results['fibonacci_sequence'] = $fibonacci_result;
                if ($fibonacci_result['test_status'] === 'completed') {
                    $additional_completed_operations++;
                }
            } else if ($additional_test_status === 'completed') {
                $additional_test_status = 'timeout';
                $additional_timeout_reason = 'Fibonacci sequence test exceeded time limit';
            }
            
            // Sub-test 4: Conditional Logic (INTENSIFIED)
            if ((microtime(true) - $start_time) < ($max_test_time * 0.9) && $additional_test_status === 'completed') {
                $conditional_result = self::test_conditional_logic($test_config, $start_time, $max_test_time);
                $additional_results['conditional_logic'] = $conditional_result;
                if ($conditional_result['test_status'] === 'completed') {
                    $additional_completed_operations++;
                }
            } else if ($additional_test_status === 'completed') {
                $additional_test_status = 'timeout';
                $additional_timeout_reason = 'Conditional logic test exceeded time limit';
            }
            
            // Sub-test 5: String Processing (INTENSIFIED)
            if ((microtime(true) - $start_time) < ($max_test_time * 0.9) && $additional_test_status === 'completed') {
                $string_result = self::test_string_processing($test_config, $start_time, $max_test_time);
                $additional_results['string_processing'] = $string_result;
                if ($string_result['test_status'] === 'completed') {
                    $additional_completed_operations++;
                }
            } else if ($additional_test_status === 'completed') {
                $additional_test_status = 'timeout';
                $additional_timeout_reason = 'String processing test exceeded time limit';
            }
            
            // Sub-test 6: Array Operations (INTENSIFIED)
            if ((microtime(true) - $start_time) < ($max_test_time * 0.9) && $additional_test_status === 'completed') {
                $array_result = self::test_array_operations($test_config, $start_time, $max_test_time);
                $additional_results['array_operations'] = $array_result;
                if ($array_result['test_status'] === 'completed') {
                    $additional_completed_operations++;
                }
            } else if ($additional_test_status === 'completed') {
                $additional_test_status = 'timeout';
                $additional_timeout_reason = 'Array operations test exceeded time limit';
            }
            
        } catch (Exception $e) {
            $additional_test_status = 'error';
            $additional_timeout_reason = 'CPU test error: ' . $e->getMessage();
        }
        
        $additional_total_time = microtime(true) - $start_time;
        
        return array(
            'total_time' => round($additional_total_time, 3),
            'completed_operations' => $additional_completed_operations,
            'total_operations' => $additional_total_operations,
            'status' => $additional_test_status,
            'timeout_reason' => $additional_timeout_reason,
            'sub_test_results' => $additional_results
        );
    }
    
    /**
     * Determine hosting grade from prime generation test results
     *
     * @param float $time Test execution time
     * @param int $primes_checked Number of primes checked
     * @param bool $throttling_detected Whether throttling was detected
     * @return string Hosting grade assessment
     */
    private static function determine_hosting_grade_from_prime_test($time, $primes_checked, $throttling_detected) {
        if ($throttling_detected) {
            return 'Shared hosting with CPU throttling detected';
        }
        
        if ($time > 8) {
            return 'Limited CPU resources - shared hosting likely';
        } elseif ($time > 4) {
            return 'Moderate CPU performance - VPS or managed hosting';
        } elseif ($time > 2) {
            return 'Good CPU performance - quality VPS or dedicated resources';
        } else {
            return 'Excellent CPU performance - premium hosting or dedicated server';
        }
    }
    
    /**
     * Determine hosting grade from mathematical operations test results
     *
     * @param float $time Test execution time
     * @param int $math_ops_completed Math operations completed
     * @param int $cpu_intensive_ops CPU intensive operations completed
     * @return string Hosting grade assessment
     */
    private static function determine_hosting_grade_from_math_test($time, $math_ops_completed, $cpu_intensive_ops) {
        $ops_per_second = $math_ops_completed / max(0.001, $time);
        $intensive_ops_per_second = $cpu_intensive_ops / max(0.001, $time);
        
        if ($intensive_ops_per_second > 50000) {
            return 'Premium hosting - excellent mathematical processing';
        } elseif ($intensive_ops_per_second > 25000) {
            return 'High-performance hosting - good mathematical processing';
        } elseif ($intensive_ops_per_second > 10000) {
            return 'Standard hosting - adequate mathematical processing';
        } else {
            return 'Limited hosting - slow mathematical processing';
        }
    }
    
    /**
     * Determine hosting grade from Fibonacci sequence test results
     *
     * @param float $time Test execution time
     * @param int $sequences_calculated Sequences calculated
     * @param int $recursive_calls Recursive calls made
     * @return string Hosting grade assessment
     */
    private static function determine_hosting_grade_from_fibonacci_test($time, $sequences_calculated, $recursive_calls) {
        $sequences_per_second = $sequences_calculated / max(0.001, $time);
        
        if ($time > 15) {
            return 'Shared hosting - struggles with recursive algorithms';
        } elseif ($sequences_per_second > 100) {
            return 'Premium hosting - handles complex algorithms efficiently';
        } elseif ($sequences_per_second > 50) {
            return 'Good hosting - decent algorithm performance';
        } elseif ($sequences_per_second > 20) {
            return 'Standard hosting - adequate algorithm performance';
        } else {
            return 'Limited hosting - poor algorithm performance';
        }
    }
} 