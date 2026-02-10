<?php
/**
 * Memory Concurrency Test
 *
 * Tests memory allocation and management under concurrent load by creating
 * 96 memory processes that compete for available memory resources.
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
 * Memory Concurrency Test Class
 */
class DiveWP_Memory_Concurrency_Test {

    /**
     * Test configuration
     */
    const MEMORY_PROCESSES = 96;
    const ALLOCATION_SIZE = 1048576; // 1MB per allocation
    const MAX_TEST_TIME = 25; // 25 seconds max
    const MAX_MEMORY_USAGE = 0.8; // 80% of available memory limit

    /**
     * Run the memory concurrency test
     *
     * @return array Test results
     */
    public static function run() {
        $start_time = microtime(true);
        $test_name = 'Memory Concurrency';
        $initial_memory = memory_get_usage(true);
        
        $result = array(
            'status' => 'completed',
            'test_name' => $test_name,
            'total_time' => 0,
            'processes_completed' => 0,
            'successful_allocations' => 0,
            'failed_allocations' => 0,
            'peak_memory_usage' => 0,
            'memory_efficiency' => 0,
            'avg_allocation_time' => 0,
            'operations_per_second' => 0,
            'score' => 0,
            'rating' => 'unknown',
            'interpretation' => '',
            'memory_details' => array(),
            'timestamp' => current_time('mysql')
        );

        try {
            // Set appropriate time limit
            $time_limit = get_transient('divewp_benchmark_time_limit') ?: 54;
            // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged -- BENCHMARK REQUIREMENT: Extended time limit needed for server stress testing
            set_time_limit($time_limit);

            // Get initial memory limits
            $memory_info = self::get_memory_info();
            
            if ($memory_info['limit_bytes'] <= 0) {
                throw new Exception(__('Unable to determine memory limit for concurrency test', 'divewp-boost-site-performance'));
            }

            // TRUE PARALLEL: REST worker mem units via curl_multi
            require_once __DIR__ . '/helpers.php';
            $token = wp_generate_password(16, false, false);
            set_transient('divewp_concurrency_worker_token', $token, MINUTE_IN_SECONDS);
            $parallel = 10;
            $runtime = 6.0;
            $pool = DiveWP_Concurrency_MultiRunner::run('mem', $parallel, $runtime, $token);
            delete_transient('divewp_concurrency_worker_token');

            $test_result = array(
                'processes_completed' => $pool['success_count'] + $pool['fail_count'],
                'successful_allocations' => $pool['success_count'],
                'failed_allocations' => $pool['fail_count'],
                'avg_allocation_time' => !empty($pool['durations']) ? round(array_sum($pool['durations']) / count($pool['durations']), 6) : 0,
                'allocation_times' => $pool['durations'],
                'memory_details' => array(
                    'peak_during_test' => memory_get_peak_usage(true),
                    'final_usage' => memory_get_usage(true)
                )
            );
            
            // Merge test results
            $result = array_merge($result, $test_result);
            
            // Calculate final metrics
            $total_time = microtime(true) - $start_time;
            $result['total_time'] = $total_time;
            $result['peak_memory_usage'] = memory_get_peak_usage(true) - $initial_memory;
            
            if ($total_time > 0 && $result['processes_completed'] > 0) {
                $result['operations_per_second'] = round($result['processes_completed'] / $total_time, 2);
                $result['memory_efficiency'] = round(($result['successful_allocations'] / max(1, $result['processes_completed'])) * 100, 2);
            }

            // Calculate score and rating
            $result['score'] = self::calculate_score($result, $memory_info);
            $result['rating'] = self::get_rating($result['score']);
            $result['interpretation'] = self::get_interpretation($result);
            
            // Add status fields for UX enhancement
            $result['test_status'] = ($result['processes_completed'] >= self::MEMORY_PROCESSES) ? 'completed' : 'partial';
            $result['completed_operations'] = $result['processes_completed'];
            $result['total_operations'] = self::MEMORY_PROCESSES;
            
            if ($result['test_status'] === 'partial') {
                $result['timeout_reason'] = __('Performance degraded under concurrent load.', 'divewp-boost-site-performance');
            } elseif ($result['failed_allocations'] > 0) {
                $result['timeout_reason'] = sprintf(
                    // translators: %1$d is the number of memory allocations that failed during testing
                    __('%1$d memory allocations failed due to hosting limits.', 'divewp-boost-site-performance'),
                    $result['failed_allocations']
                );
            } else {
                $result['timeout_reason'] = '';
            }

            // ENHANCED UX: Add performance interpretation data using scoring class
            require_once DIVEWP_PLUGIN_DIR . 'includes/features/hosting/hosting-benchmark/tests/concurrency/scoring.php';
            $result['performance_interpretation'] = DiveWP_Benchmark_Concurrency_Scoring::get_sub_test_performance_interpretation('memory_concurrency', $result);

        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("DiveWP Memory Concurrency Error: " . $e->getMessage()); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- BENCHMARK REQUIREMENT: minimal debug logging gated by WP_DEBUG
            }
            
            $result['status'] = 'error';
            $result['message'] = $e->getMessage();
            $result['total_time'] = microtime(true) - $start_time;
            $result['peak_memory_usage'] = memory_get_peak_usage(true) - $initial_memory;
            $result['score'] = 0;
            $result['rating'] = 'error';
            $result['interpretation'] = sprintf(
                // translators: %1$s is the specific error message explaining why the memory concurrency test failed
                __('Memory concurrency test failed: %1$s', 'divewp-boost-site-performance'), 
                $e->getMessage()
            );
            
            // Add status fields for error case
            $result['test_status'] = 'error';
            $result['completed_operations'] = $result['processes_completed'] ?? 0;
            $result['total_operations'] = self::MEMORY_PROCESSES;
            $result['timeout_reason'] = sprintf(
                // translators: %1$s is the specific error message explaining why the test failed
                __('Test failed with error: %1$s', 'divewp-boost-site-performance'), 
                $e->getMessage()
            );
        }

        // Force garbage collection to clean up
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }

        return $result;
    }

    /**
     * Get memory information
     *
     * @return array Memory configuration details
     */
    private static function get_memory_info() {
        $memory_limit = ini_get('memory_limit');
        $limit_bytes = 0;
        
        // Convert memory limit to bytes
        if ($memory_limit && $memory_limit !== '-1') {
            $unit = strtolower(substr($memory_limit, -1));
            $value = intval($memory_limit);
            
            switch ($unit) {
                case 'g':
                    $limit_bytes = $value * 1024 * 1024 * 1024;
                    break;
                case 'm':
                    $limit_bytes = $value * 1024 * 1024;
                    break;
                case 'k':
                    $limit_bytes = $value * 1024;
                    break;
                default:
                    $limit_bytes = $value;
            }
        }
        
        // If no limit or unlimited, use a reasonable default
        if ($limit_bytes <= 0) {
            $limit_bytes = 128 * 1024 * 1024; // 128MB default
        }
        
        return array(
            'limit' => $memory_limit,
            'limit_bytes' => $limit_bytes,
            'current_usage' => memory_get_usage(true),
            'peak_usage' => memory_get_peak_usage(true),
            'max_safe_usage' => $limit_bytes * self::MAX_MEMORY_USAGE
        );
    }

    /**
     * Execute concurrent memory operations
     *
     * @param array $memory_info Memory configuration
     * @param float $start_time Test start time
     * @return array Operation results
     */
    private static function execute_concurrent_memory_operations($memory_info, $start_time) {
        $successful_allocations = 0;
        $failed_allocations = 0;
        $allocation_times = array();
        $memory_blocks = array();
        $batch_size = 8; // Process in batches to avoid overwhelming memory
        
        for ($process = 0; $process < self::MEMORY_PROCESSES; $process += $batch_size) {
            // Check timeout
            if ((microtime(true) - $start_time) > self::MAX_TEST_TIME) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("DiveWP Memory Concurrency: Timeout reached, processed {$successful_allocations} allocations"); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- BENCHMARK REQUIREMENT: minimal debug logging gated by WP_DEBUG
                }
                break;
            }
            
            // Check memory usage before batch
            $current_memory = memory_get_usage(true);
            if ($current_memory > $memory_info['max_safe_usage']) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("DiveWP Memory Concurrency: Memory limit approached, stopping test"); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- BENCHMARK REQUIREMENT: minimal debug logging gated by WP_DEBUG
                }
                break;
            }
            
            // Process batch of memory operations
            $batch_result = self::execute_memory_batch($batch_size, $process, $allocation_times, $memory_blocks);
            
            $successful_allocations += $batch_result['successful'];
            $failed_allocations += $batch_result['failed'];
            
            // Brief pause to allow memory management
            usleep(50000); // 50ms
            
            // Periodically clean up memory blocks to prevent excessive usage
            if ($process % 24 === 0 && count($memory_blocks) > 10) {
                $memory_blocks = array_slice($memory_blocks, -10); // Keep only last 10 blocks
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
            }
        }
        
        // Calculate allocation statistics
        $avg_allocation_time = 0;
        if (!empty($allocation_times)) {
            $avg_allocation_time = array_sum($allocation_times) / count($allocation_times);
        }
        
        // Clean up all memory blocks
        unset($memory_blocks);
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
        
        return array(
            'processes_completed' => $successful_allocations + $failed_allocations,
            'successful_allocations' => $successful_allocations,
            'failed_allocations' => $failed_allocations,
            'avg_allocation_time' => round($avg_allocation_time, 6),
            'allocation_times' => $allocation_times,
            'memory_details' => array(
                'peak_during_test' => memory_get_peak_usage(true),
                'final_usage' => memory_get_usage(true)
            )
        );
    }

    /**
     * Execute a batch of memory operations
     *
     * @param int $batch_size Number of operations in this batch
     * @param int $process_offset Starting process number
     * @param array &$allocation_times Reference to allocation times array
     * @param array &$memory_blocks Reference to memory blocks array
     * @return array Batch results
     */
    private static function execute_memory_batch($batch_size, $process_offset, &$allocation_times, &$memory_blocks) {
        $successful = 0;
        $failed = 0;
        
        for ($i = 0; $i < $batch_size; $i++) {
            $process_number = $process_offset + $i;
            if ($process_number >= self::MEMORY_PROCESSES) {
                break;
            }
            
            $allocation_start = microtime(true);
            
            try {
                // Allocate memory block
                $memory_block = self::allocate_memory_block($process_number);
                
                if ($memory_block !== false) {
                    $allocation_time = microtime(true) - $allocation_start;
                    $allocation_times[] = $allocation_time;
                    $memory_blocks[] = $memory_block;
                    $successful++;
                    
                    // Perform some operations on the memory block
                    self::perform_memory_operations($memory_block);
                } else {
                    $failed++;
                }
                
            } catch (Exception $e) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("DiveWP Memory Allocation Error (Process {$process_number}): " . $e->getMessage()); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- BENCHMARK REQUIREMENT: minimal debug logging gated by WP_DEBUG
                }
                $failed++;
            } catch (Error $e) {
                // Handle fatal errors like out of memory
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("DiveWP Memory Fatal Error (Process {$process_number}): " . $e->getMessage()); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- BENCHMARK REQUIREMENT: minimal debug logging gated by WP_DEBUG
                }
                $failed++;
            }
        }
        
        return array(
            'successful' => $successful,
            'failed' => $failed
        );
    }

    /**
     * Allocate a memory block
     *
     * @param int $process_number Process identifier
     * @return mixed Memory block or false on failure
     */
    private static function allocate_memory_block($process_number) {
        try {
            // Create memory block with pattern data
            $block_size = self::ALLOCATION_SIZE;
            $pattern = str_repeat("DIVEWP_MEMORY_TEST_{$process_number}_", intval($block_size / 32));
            
            // Ensure block is exactly the right size
            $memory_block = substr($pattern, 0, $block_size);
            
            // Verify allocation
            if (strlen($memory_block) === $block_size) {
                return $memory_block;
            } else {
                return false;
            }
            
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Perform operations on memory block
     *
     * @param string &$memory_block Reference to memory block
     */
    private static function perform_memory_operations(&$memory_block) {
        // Perform various memory operations to test memory handling
        $operations = array(
            'search' => function(&$block) {
                return strpos($block, 'DIVEWP');
            },
            'modify' => function(&$block) {
                $block[100] = 'X';
            },
            'copy' => function(&$block) {
                $temp = substr($block, 0, 1000);
                return strlen($temp);
            },
            'calculate' => function(&$block) {
                return crc32($block);
            }
        );
        
        // Execute random operations
        $operation_keys = array_keys($operations);
        $random_operation = $operations[$operation_keys[array_rand($operation_keys)]];
        $random_operation($memory_block);
    }

    /**
     * Calculate performance score
     *
     * @param array $result Test results
     * @param array $memory_info Memory configuration
     * @return float Score from 0 to 100
     */
    private static function calculate_score($result, $memory_info) {
        if ($result['status'] !== 'completed' || $result['processes_completed'] === 0) {
            return 0;
        }
        
        $success_rate = ($result['successful_allocations'] / $result['processes_completed']) * 100;
        $efficiency = $result['memory_efficiency'];
        $allocation_speed = $result['operations_per_second'];
        
        // Base score from success rate (50% weight)
        $success_score = $success_rate * 0.5;
        
        // Allocation speed score (30% weight)
        $speed_score = 0;
        if ($allocation_speed >= 50) {
            $speed_score = 30; // Excellent
        } elseif ($allocation_speed >= 30) {
            $speed_score = 25; // Good
        } elseif ($allocation_speed >= 20) {
            $speed_score = 20; // Fair
        } elseif ($allocation_speed >= 10) {
            $speed_score = 15; // Poor
        } else {
            $speed_score = 10; // Critical
        }
        
        // Memory efficiency score (20% weight)
        $efficiency_score = ($efficiency / 100) * 20;
        
        $final_score = $success_score + $speed_score + $efficiency_score;
        
        // Penalty for memory pressure
        $memory_pressure = $result['peak_memory_usage'] / $memory_info['limit_bytes'];
        if ($memory_pressure > 0.9) {
            $pressure_penalty = 15;
        } elseif ($memory_pressure > 0.8) {
            $pressure_penalty = 10;
        } elseif ($memory_pressure > 0.7) {
            $pressure_penalty = 5;
        } else {
            $pressure_penalty = 0;
        }
        
        $final_score = max(0, $final_score - $pressure_penalty);
        
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
            return __('Memory concurrency test could not be completed.', 'divewp-boost-site-performance');
        }
        
        $score = $result['score'];
        $success_rate = ($result['successful_allocations'] / $result['processes_completed']) * 100;
        $peak_mb = round($result['peak_memory_usage'] / 1024 / 1024, 2);
        
        if ($score >= 90) {
            return sprintf(
                // translators: %1$d is successful memory allocations, %2$d is total processes, %3$.1f is efficiency percentage, %4$.2f is peak memory usage in megabytes
                __('Excellent memory management! Successfully allocated %1$d/%2$d memory blocks with %3$.1f%% efficiency. Peak usage: %4$.2f MB.', 'divewp-boost-site-performance'),
                $result['successful_allocations'],
                $result['processes_completed'],
                $success_rate,
                $peak_mb
            );
        } elseif ($score >= 75) {
            return sprintf(
                // translators: %1$.0f is the allocation success rate as a percentage, %2$.2f is peak memory usage in megabytes
                __('Good memory handling under concurrent load. %1$.0f%% allocation success rate with peak usage of %2$.2f MB.', 'divewp-boost-site-performance'),
                $success_rate,
                $peak_mb
            );
        } elseif ($score >= 60) {
            return sprintf(
                // translators: %1$d is the number of successful memory allocations, %2$.2f is peak memory usage in megabytes
                __('Fair memory performance. %1$d successful allocations but peak usage reached %2$.2f MB. Consider memory optimization.', 'divewp-boost-site-performance'),
                $result['successful_allocations'],
                $peak_mb
            );
        } elseif ($score >= 40) {
            return sprintf(
                // translators: %1$d is the number of failed allocations, %2$d is the total processes completed
                __('Poor memory concurrency. %1$d/%2$d allocations failed. Memory pressure may affect performance under load.', 'divewp-boost-site-performance'),
                $result['failed_allocations'],
                $result['processes_completed']
            );
        } else {
            return sprintf(
                // translators: %1$d is the number of failed memory allocations indicating severe system issues
                __('Critical memory issues. High failure rate (%1$d failed) indicates severe memory limitations or leaks.', 'divewp-boost-site-performance'),
                $result['failed_allocations']
            );
        }
    }
} 