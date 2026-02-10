<?php
/**
 * Memory Performance Tests
 *
 * Replicates exact POC memory allocation test specifications with enhanced UX features.
 * Tests memory allocation limits and efficiency (single test execution).
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
 * Resources Memory Tests Class
 */
class DiveWP_Resources_Memory_Tests {

    /**
     * Run memory allocation tests (INTENSIFIED for Real Hosting Evaluation)
     *
     * @param array $test_config POC test configuration
     * @return array Test results with enhanced UX data
     */
    public static function run($test_config = array()) {
        $start_time = microtime(true);
        
        // INTENSIFIED: PROGRESSIVE MEMORY PRESSURE TESTING
        // Test multiple allocation levels to find hosting memory limits
        // Single test replaced with comprehensive memory stress testing
        
        $enabled_tests = $test_config['enabled_tests'] ?? array();
        
        // Check if memory allocation test is enabled (POC check)
        if (!in_array('test_memory_allocation_limits', $enabled_tests)) {
            return array(
                'score' => 0,
                'total_time' => 0,
                'completed_operations' => 0,
                'total_operations' => 5, // 5 progressive tests
                'status' => 'skipped',
                'timeout_reason' => 'Memory allocation test disabled by user configuration',
                'memory_results' => array(),
                'memory_stats' => array('mean' => 0, 'median' => 0, 'min' => 0, 'max' => 0, 'stddev' => 0, 'count' => 0),
                'allocation_stats' => array('mean' => 0, 'median' => 0, 'min' => 0, 'max' => 0, 'stddev' => 0, 'count' => 0),
                'performance_interpretation' => array(
                    'rating' => 'skipped',
                    'rating_label' => __('Skipped', 'divewp-boost-site-performance'),
                    'explanation' => __('Memory allocation test was disabled in configuration.', 'divewp-boost-site-performance')
                )
            );
        }
        
        // INTENSIFIED: Progressive Memory Pressure Tests
        $memory_results = array();
        $completed_operations = 0;
        $total_operations = 6; // Progressive tests + fragmentation + rapid cycles
        $test_status = 'completed';
        $min_runtime = 5; // 5 seconds minimum for memory stress
        
        try {
            // Test 1: 50% Memory Allocation
            $result_50 = self::test_progressive_memory_allocation($test_config, 0.5, $start_time);
            $memory_results[] = $result_50;
            if ($result_50['success']) $completed_operations++;
            
            // Test 2: 70% Memory Allocation  
            $result_70 = self::test_progressive_memory_allocation($test_config, 0.7, $start_time);
            $memory_results[] = $result_70;
            if ($result_70['success']) $completed_operations++;
            
            // Test 3: 85% Memory Allocation
            $result_85 = self::test_progressive_memory_allocation($test_config, 0.85, $start_time);
            $memory_results[] = $result_85;
            if ($result_85['success']) $completed_operations++;
            
            // Test 4: 95% Memory Allocation (Stress Test)
            $result_95 = self::test_progressive_memory_allocation($test_config, 0.95, $start_time);
            $memory_results[] = $result_95;
            if ($result_95['success']) $completed_operations++;
            
            // Test 5: Memory Fragmentation Test
            $fragmentation_result = self::test_memory_fragmentation($test_config, $start_time);
            $memory_results[] = $fragmentation_result;
            if ($fragmentation_result['success']) $completed_operations++;
            
            // Test 6: Rapid Allocation/Deallocation Cycles
            $rapid_cycles_result = self::test_rapid_memory_cycles($test_config, $start_time);
            $memory_results[] = $rapid_cycles_result;
            if ($rapid_cycles_result['success']) $completed_operations++;
            
        } catch (Exception $e) {
            $test_status = 'error';
        }
        
        $total_time = microtime(true) - $start_time;
        
        // MINIMUM RUNTIME ENFORCEMENT - Keep testing until minimum time reached
        if ($total_time < $min_runtime && $test_status === 'completed') {
            $additional_stress_result = self::run_additional_memory_stress($start_time, $min_runtime);
            $memory_results[] = $additional_stress_result;
            $total_time = microtime(true) - $start_time;
        }
        
        // Calculate comprehensive statistics
        $memory_scores = array_column($memory_results, 'score');
        $actual_allocations = array_column($memory_results, 'max_allocated');
        
        $memory_stats = DiveWP_Benchmark_Resources_Tests::calculate_performance_statistics($memory_scores);
        $allocation_stats = DiveWP_Benchmark_Resources_Tests::calculate_performance_statistics($actual_allocations);
        
        // Calculate overall score based on progressive results
        $overall_score = self::calculate_progressive_memory_score($memory_results, $total_time);
        
        // Calculate operations per second for UI display
        $operations_per_second = $total_time > 0 ? round($completed_operations / $total_time, 1) : 0;
        
        return array(
            'score' => $overall_score,
            'total_time' => round($total_time, 3),
            'completed_operations' => $completed_operations,
            'total_operations' => $total_operations,
            'operations_per_second' => $operations_per_second,
            'status' => $test_status,
            'timeout_reason' => $test_status === 'error' ? 'Memory test failed to complete' : null,
            'memory_results' => $memory_results,
            'memory_stats' => $memory_stats,
            'allocation_stats' => $allocation_stats,
            'progressive_scores' => $memory_scores,
            'hosting_memory_limits' => self::analyze_memory_hosting_limits($memory_results),
            'memory_pressure_handling' => self::evaluate_memory_pressure_handling($memory_results),
            'performance_interpretation' => DiveWP_Benchmark_Resources_Scoring::get_sub_test_performance_interpretation('memory_tests', array(
                'score' => $overall_score,
                'total_time' => $total_time,
                'completed_operations' => $completed_operations,
                'total_operations' => $total_operations,
                'operations_per_second' => $operations_per_second,
                'status' => $test_status
            ))
        );
    }
    
    /**
     * Test memory allocation limits (POC exact specification)
     * 
     * @param array $test_config Test configuration
     * @return array Test results
     */
    private static function test_memory_allocation_limits($test_config) {
        // Run the simple memory allocation test (POC method)
        $test_result = self::simple_memory_allocation_test($test_config['memory_allocation_percentage'] ?? 0.8);
        
        // Calculate score based on allocation efficiency (POC method)
        $efficiency = 0;
        if ($test_result['target'] > 0) {
            $efficiency = ($test_result['allocated'] / $test_result['target']) * 100;
        }
        
        // Calculate absolute target (80% of total memory limit) (POC method)
        $absolute_target = $test_result['memory_limit'] * ($test_config['memory_allocation_percentage'] ?? 0.8);
        $allocation_efficiency = 0;
        if ($absolute_target > 0) {
            $allocation_efficiency = ($test_result['allocated'] / $absolute_target) * 100;
        }
        
        // Build test results array for scoring (POC method)
        $scoring_results = array(
            'max_allocated' => $test_result['allocated'],
            'allocation_efficiency' => $allocation_efficiency,
            'memory_pressure_handled' => $test_result['success'],
            'wordpress_simulation_success' => $test_result['success'],
            'peak_memory_delta' => $test_result['allocated']
        );
        
        // Calculate score using POC method
        $score = self::calculate_memory_score_2025($scoring_results, $test_result['memory_limit']);
        
        // Return formatted results (POC structure)
        return array(
            'score' => $score,
            'max_allocated' => $test_result['allocated'],
            'memory_limit' => ini_get('memory_limit'),
            'memory_limit_bytes' => $test_result['memory_limit'],
            'absolute_target' => $absolute_target,
            'allocation_efficiency' => round($allocation_efficiency, 1),
            'memory_pressure_handled' => $test_result['success'],
            'wordpress_simulation_success' => $test_result['success'],
            'peak_memory_delta' => $test_result['allocated'],
            'success' => $test_result['success']
        );
    }
    
    /**
     * Simple memory allocation test (POC exact method)
     * Allocates 80% of available memory and cleans up
     * 
     * @param float $allocation_percentage Target percentage to allocate (0.8 = 80%)
     * @return array Test results with only numeric values (no data references)
     */
    private static function simple_memory_allocation_test($allocation_percentage = 0.8) {
        // POC specification: FORCE COMPLETE MEMORY CLEANUP FIRST
        if (function_exists('gc_collect_cycles')) {
            for ($i = 0; $i < 10; $i++) {
                gc_collect_cycles();
            }
        }
        usleep(100000); // 100ms for cleanup
        
        // Get FRESH memory state after cleanup (POC method)
        $start_memory = memory_get_usage(true);
        $memory_limit_bytes = wp_convert_hr_to_bytes(ini_get('memory_limit'));
        
        // Calculate what's ACTUALLY available NOW (POC method)
        $current_usage = memory_get_usage(true);
        $available = $memory_limit_bytes - $current_usage;
        $target_to_allocate = max(0, $available * $allocation_percentage);
        
        // Results array - numbers only, no data references (POC specification)
        $results = array(
            'memory_limit' => $memory_limit_bytes,
            'start_usage' => $current_usage,
            'available' => $available,
            'target' => $target_to_allocate,
            'allocated' => 0,
            'success' => false
        );
        
        // Skip test if not enough memory available (POC check)
        if ($target_to_allocate < 1048576) { // Less than 1MB available
            $results['allocated'] = 0;
            $results['success'] = false;
            $results['error'] = 'Insufficient memory available';
            return $results;
        }
        
        // Allocate memory in local scope (POC method)
        $test_data = array();
        $allocated = 0;
        
        try {
            // Simple allocation loop (POC specification)
            $chunk = str_repeat('X', 1024); // 1KB chunks
            $chunks_needed = (int)($target_to_allocate / 1024);
            
            for ($i = 0; $i < $chunks_needed; $i++) {
                $test_data[] = $chunk . $i; // Unique data to prevent PHP optimization
                
                // Check progress every 1000 chunks (1MB) (POC method)
                if ($i % 1000 === 0) {
                    $allocated = memory_get_usage(true) - $start_memory;
                    if ($allocated >= (int)($target_to_allocate * 0.95)) {
                        break; // Close enough
                    }
                }
            }
            
            // Final measurement (POC method)
            $allocated = memory_get_usage(true) - $start_memory;
            $results['allocated'] = $allocated;
            $results['success'] = true;
            
        } catch (Exception $e) {
            $results['error'] = 'Allocation failed: ' . $e->getMessage();
        }
        
        // POC specification: CRITICAL - Destroy data completely
        unset($test_data);
        
        // POC specification: AGGRESSIVE cleanup for next iteration
        if (function_exists('gc_collect_cycles')) {
            for ($i = 0; $i < 10; $i++) {
                gc_collect_cycles();
            }
        }
        
        // Longer delay for memory to be released (POC method)
        usleep(200000); // 200ms
        
        // Return ONLY the numbers, no references to allocated data (POC specification)
        return $results;
    }
    
    /**
     * Progressive memory allocation test (INTENSIFIED for Real Hosting Evaluation)
     * Tests specific percentage of available memory to find hosting limits
     * 
     * @param array $test_config Test configuration
     * @param float $allocation_percentage Target percentage to allocate (0.5 = 50%)
     * @param float $start_time Overall test start time
     * @return array Test results with detailed hosting analysis
     */
    private static function test_progressive_memory_allocation($test_config, $allocation_percentage, $start_time) {
        $test_start_time = microtime(true);
        
        // INTENSIFIED: Aggressive memory cleanup before each test
        if (function_exists('gc_collect_cycles')) {
            for ($i = 0; $i < 15; $i++) {
                gc_collect_cycles();
            }
        }
        usleep(200000); // 200ms for cleanup
        
        // Get current memory state
        $start_memory = memory_get_usage(true);
        $memory_limit_bytes = wp_convert_hr_to_bytes(ini_get('memory_limit'));
        $current_usage = memory_get_usage(true);
        $available = $memory_limit_bytes - $current_usage;
        $target_to_allocate = max(0, $available * $allocation_percentage);
        
        // Results tracking
        $results = array(
            'allocation_percentage' => $allocation_percentage * 100,
            'memory_limit' => $memory_limit_bytes,
            'start_usage' => $current_usage,
            'available' => $available,
            'target' => $target_to_allocate,
            'allocated' => 0,
            'success' => false,
            'hosting_behavior' => 'unknown',
            'allocation_speed' => 0,
            'pressure_resistance' => 0
        );
        
        // Skip if insufficient memory
        if ($target_to_allocate < 2097152) { // Less than 2MB available
            $results['error'] = 'Insufficient memory available for ' . ($allocation_percentage * 100) . '% test';
            $results['hosting_behavior'] = 'severely_limited';
            return $results;
        }
        
        // INTENSIFIED: WordPress-like allocation patterns
        $test_data = array();
        $allocated = 0;
        $allocation_chunks = array();
        $allocation_times = array();
        
        try {
            // Simulate WordPress memory usage patterns
            $chunk_size = min(524288, $target_to_allocate / 20); // 512KB chunks or smaller
            $chunks_needed = (int)($target_to_allocate / $chunk_size);
            
            for ($i = 0; $i < $chunks_needed; $i++) {
                $chunk_start = microtime(true);
                
                // INTENSIFIED: WordPress-like data structures
                $wordpress_data = array(
                    'posts' => array_fill(0, 50, array(
                        'id' => $i,
                        'title' => str_repeat('WordPress Post Title ' . $i . ' ', 10),
                        'content' => str_repeat('Lorem ipsum content for post ' . $i . ' ', 50),
                        'meta' => array_fill(0, 10, str_repeat('meta_value_' . $i, 20))
                    )),
                    'cache_data' => array_fill(0, 100, str_repeat('cache_' . $i, 100)),
                    'session_data' => array_fill(0, 25, str_repeat('session_' . $i, 200))
                );
                
                $test_data[] = $wordpress_data;
                $chunk_time = microtime(true) - $chunk_start;
                $allocation_times[] = $chunk_time;
                
                // Memory pressure monitoring
                $current_allocated = memory_get_usage(true) - $start_memory;
                $allocation_chunks[] = $current_allocated;
                
                // Check for hosting memory pressure signs
                if ($chunk_time > 0.5) { // Allocation taking too long
                    $results['hosting_behavior'] = 'memory_pressure_detected';
                    break;
                }
                
                // Check memory limit proximity
                if ($current_allocated >= $target_to_allocate * 0.98) {
                    break; // Close enough to target
                }
                
                // Safety timeout
                if ((microtime(true) - $start_time) > 15) { // 15 second safety
                    break;
                }
            }
            
            $allocated = memory_get_usage(true) - $start_memory;
            $results['allocated'] = $allocated;
            $results['success'] = true;
            
            // Calculate performance metrics
            if (!empty($allocation_times)) {
                $results['allocation_speed'] = round(array_sum($allocation_times) / count($allocation_times), 4);
                $results['allocation_variance'] = round(self::calculate_variance($allocation_times), 4);
            }
            
            // Determine hosting behavior pattern
            $results['hosting_behavior'] = self::analyze_allocation_behavior(
                $allocated, 
                $target_to_allocate, 
                $allocation_times, 
                $allocation_percentage
            );
            
            // Test memory pressure resistance
            $results['pressure_resistance'] = self::test_memory_pressure_resistance($test_data, $allocated);
            
        } catch (Exception $e) {
            $results['error'] = 'Allocation failed: ' . $e->getMessage();
            $results['hosting_behavior'] = 'allocation_failure';
        }
        
        // CRITICAL: Complete memory cleanup
        unset($test_data, $wordpress_data);
        
        if (function_exists('gc_collect_cycles')) {
            for ($i = 0; $i < 15; $i++) {
                gc_collect_cycles();
            }
        }
        usleep(300000); // 300ms for cleanup
        
        $results['test_time'] = microtime(true) - $test_start_time;
        $results['score'] = self::calculate_allocation_score($results);
        
        return $results;
    }
    
    /**
     * Memory fragmentation test (NEW - for shared hosting evaluation)
     * 
     * @param array $test_config Test configuration
     * @param float $start_time Overall test start time
     * @return array Test results
     */
    private static function test_memory_fragmentation($test_config, $start_time) {
        $test_start_time = microtime(true);
        
        // INTENSIVE: Simulate memory fragmentation scenarios
        $fragmentation_data = array();
        $allocated_blocks = 0;
        $fragmentation_score = 0;
        
        $results = array(
            'test_type' => 'memory_fragmentation',
            'allocated_blocks' => 0,
            'fragmentation_detected' => false,
            'allocation_efficiency' => 0,
            'success' => false
        );
        
        try {
            // Phase 1: Allocate many small blocks (simulate WordPress plugins)
            for ($i = 0; $i < 500; $i++) {
                $small_block = array_fill(0, 100, 'fragmentation_test_' . $i);
                $fragmentation_data[] = $small_block;
                $allocated_blocks++;
                
                // Safety timeout
                if ((microtime(true) - $start_time) > 12) {
                    break;
                }
            }
            
            // Phase 2: Deallocate every other block (create gaps)
            for ($i = 1; $i < count($fragmentation_data); $i += 2) {
                unset($fragmentation_data[$i]);
            }
            
            // Force garbage collection
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
            
            // Phase 3: Try to allocate larger blocks (test fragmentation impact)
            $large_allocations = 0;
            for ($i = 0; $i < 50; $i++) {
                try {
                    $large_block = array_fill(0, 1000, 'large_block_' . $i);
                    $fragmentation_data[] = $large_block;
                    $large_allocations++;
                } catch (Exception $e) {
                    $results['fragmentation_detected'] = true;
                    break;
                }
            }
            
            $results['allocated_blocks'] = $allocated_blocks;
            $results['large_allocations'] = $large_allocations;
            $results['allocation_efficiency'] = ($large_allocations / 50) * 100;
            $results['success'] = true;
            
        } catch (Exception $e) {
            $results['error'] = 'Fragmentation test failed: ' . $e->getMessage();
        }
        
        // Cleanup
        unset($fragmentation_data);
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
        
        $results['test_time'] = microtime(true) - $test_start_time;
        $results['score'] = $results['allocation_efficiency'];
        
        return $results;
    }
    
    /**
     * Rapid memory allocation/deallocation cycles (NEW - for hosting stress testing)
     * 
     * @param array $test_config Test configuration
     * @param float $start_time Overall test start time
     * @return array Test results
     */
    private static function test_rapid_memory_cycles($test_config, $start_time) {
        $test_start_time = microtime(true);
        
        $cycles_completed = 0;
        $allocation_times = array();
        $deallocation_times = array();
        
        $results = array(
            'test_type' => 'rapid_memory_cycles',
            'cycles_completed' => 0,
            'avg_allocation_time' => 0,
            'avg_deallocation_time' => 0,
            'memory_stability' => 100,
            'success' => false
        );
        
        try {
            // Rapid allocation/deallocation cycles
            for ($cycle = 0; $cycle < 100; $cycle++) {
                // Allocation phase
                $alloc_start = microtime(true);
                $cycle_data = array_fill(0, 1000, 'cycle_' . $cycle . '_data_' . str_repeat('x', 100));
                $allocation_times[] = microtime(true) - $alloc_start;
                
                // Small processing delay
                usleep(1000); // 1ms
                
                // Deallocation phase
                $dealloc_start = microtime(true);
                unset($cycle_data);
                $deallocation_times[] = microtime(true) - $dealloc_start;
                
                // Force garbage collection every 10 cycles
                if ($cycle % 10 === 0 && function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
                
                $cycles_completed++;
                
                // Safety timeout
                if ((microtime(true) - $start_time) > 10) {
                    break;
                }
            }
            
            $results['cycles_completed'] = $cycles_completed;
            $results['avg_allocation_time'] = array_sum($allocation_times) / count($allocation_times);
            $results['avg_deallocation_time'] = array_sum($deallocation_times) / count($deallocation_times);
            
            // Calculate memory stability (variance in timing)
            $alloc_variance = self::calculate_variance($allocation_times);
            $dealloc_variance = self::calculate_variance($deallocation_times);
            $results['memory_stability'] = max(0, 100 - ($alloc_variance + $dealloc_variance) * 1000);
            
            $results['success'] = true;
            
        } catch (Exception $e) {
            $results['error'] = 'Rapid cycles test failed: ' . $e->getMessage();
        }
        
        $results['test_time'] = microtime(true) - $test_start_time;
        $results['score'] = ($cycles_completed / 100) * $results['memory_stability'];
        
        return $results;
    }

    /**
     * Calculate memory score based purely on hosting performance (POC exact method)
     * 
     * Focuses only on memory allocation efficiency and performance,
     * not on user-configurable memory limits.
     * 
     * @param array $test_results Memory test results
     * @param int $memory_limit_bytes Memory limit in bytes (for reference only)
     * @return int Memory performance score (0-100)
     */
    private static function calculate_memory_score_2025($test_results, $memory_limit_bytes) {
        $base_score = 30; // Conservative baseline
        
        // Allocation efficiency scoring with smooth curve (POC method)
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
        
        // Memory pressure handling bonus (POC method)
        if ($test_results['memory_pressure_handled']) {
            $base_score += 15; // Increased weight for pressure handling
        }
        
        // WordPress simulation success bonus (POC method)
        if ($test_results['wordpress_simulation_success']) {
            $base_score += 5;
        }
        
        // POC specification: NO MEMORY LIMIT PENALTIES - this is hosting performance only
        
        return max(10, min(100, round($base_score)));
    }
    
    /**
     * Calculate variance for timing measurements
     * 
     * @param array $values Array of timing values
     * @return float Variance
     */
    private static function calculate_variance($values) {
        if (count($values) < 2) {
            return 0;
        }
        
        $mean = array_sum($values) / count($values);
        $variance_sum = 0;
        
        foreach ($values as $value) {
            $variance_sum += pow($value - $mean, 2);
        }
        
        return $variance_sum / count($values);
    }
    
    /**
     * Analyze allocation behavior to determine hosting type
     * 
     * @param int $allocated Amount of memory allocated
     * @param int $target Target allocation amount
     * @param array $allocation_times Array of allocation timing
     * @param float $allocation_percentage Percentage attempted
     * @return string Hosting behavior classification
     */
    private static function analyze_allocation_behavior($allocated, $target, $allocation_times, $allocation_percentage) {
        $allocation_efficiency = ($target > 0) ? ($allocated / $target) : 0;
        $avg_allocation_time = array_sum($allocation_times) / max(1, count($allocation_times));
        
        // Analyze patterns
        if ($allocation_efficiency > 0.95 && $avg_allocation_time < 0.01) {
            return 'premium_hosting'; // Fast, efficient allocation
        } elseif ($allocation_efficiency > 0.85 && $avg_allocation_time < 0.05) {
            return 'quality_vps'; // Good allocation performance
        } elseif ($allocation_efficiency > 0.70 && $avg_allocation_time < 0.1) {
            return 'standard_hosting'; // Adequate performance
        } elseif ($allocation_efficiency > 0.50) {
            return 'shared_hosting'; // Limited but functional
        } elseif ($allocation_percentage > 0.8 && $allocation_efficiency < 0.5) {
            return 'oversold_hosting'; // Severely limited at high usage
        } else {
            return 'limited_hosting'; // Poor allocation capability
        }
    }
    
    /**
     * Test memory pressure resistance
     * 
     * @param array $test_data Allocated test data
     * @param int $allocated Amount allocated
     * @return float Pressure resistance score (0-100)
     */
    private static function test_memory_pressure_resistance($test_data, $allocated) {
        try {
            // Try additional small allocations under pressure
            $pressure_allocations = 0;
            
            for ($i = 0; $i < 10; $i++) {
                $pressure_data = array_fill(0, 100, 'pressure_test_' . $i);
                $pressure_allocations++;
                unset($pressure_data);
            }
            
            // Try one larger allocation under pressure
            $large_pressure_data = array_fill(0, 1000, 'large_pressure_test');
            $pressure_allocations += 5; // Weight larger allocation more
            unset($large_pressure_data);
            
            return min(100, ($pressure_allocations / 15) * 100);
            
        } catch (Exception $e) {
            return 0; // Failed under pressure
        }
    }
    
    /**
     * Calculate allocation score based on test results
     * 
     * @param array $results Test results
     * @return int Score (0-100)
     */
    private static function calculate_allocation_score($results) {
        $base_score = 0;
        
        // Allocation efficiency scoring
        if (isset($results['allocated']) && isset($results['target']) && $results['target'] > 0) {
            $efficiency = ($results['allocated'] / $results['target']) * 100;
            $base_score += min(50, $efficiency * 0.5); // Up to 50 points for efficiency
        }
        
        // Speed scoring
        if (isset($results['allocation_speed'])) {
            $speed_score = max(0, 25 - ($results['allocation_speed'] * 1000)); // Faster = higher score
            $base_score += min(25, $speed_score);
        }
        
        // Pressure resistance scoring
        if (isset($results['pressure_resistance'])) {
            $base_score += ($results['pressure_resistance'] * 0.25); // Up to 25 points
        }
        
        // Hosting behavior bonus/penalty
        if (isset($results['hosting_behavior'])) {
            switch ($results['hosting_behavior']) {
                case 'premium_hosting':
                    $base_score += 10;
                    break;
                case 'quality_vps':
                    $base_score += 5;
                    break;
                case 'oversold_hosting':
                    $base_score -= 15;
                    break;
                case 'limited_hosting':
                    $base_score -= 10;
                    break;
                case 'allocation_failure':
                    $base_score -= 25;
                    break;
            }
        }
        
        return max(0, min(100, round($base_score)));
    }
    
    /**
     * Calculate progressive memory score based on all test results
     * 
     * @param array $memory_results Array of all memory test results
     * @param float $total_time Total test time
     * @return int Overall memory score (0-100)
     */
    private static function calculate_progressive_memory_score($memory_results, $total_time) {
        if (empty($memory_results)) {
            return 0;
        }
        
        $scores = array();
        $weights = array();
        
        foreach ($memory_results as $result) {
            if (isset($result['score'])) {
                $scores[] = $result['score'];
                
                // Weight based on test type and allocation percentage
                if (isset($result['allocation_percentage'])) {
                    // Higher allocation percentages get more weight
                    $weights[] = 1 + ($result['allocation_percentage'] / 100);
                } else {
                    $weights[] = 1; // Default weight for non-allocation tests
                }
            }
        }
        
        if (empty($scores)) {
            return 0;
        }
        
        // Calculate weighted average
        $weighted_sum = 0;
        $total_weight = 0;
        
        for ($i = 0; $i < count($scores); $i++) {
            $weighted_sum += $scores[$i] * $weights[$i];
            $total_weight += $weights[$i];
        }
        
        $base_score = $weighted_sum / $total_weight;
        
        // Time penalty for very slow execution
        if ($total_time > 8) {
            $time_penalty = min(20, ($total_time - 8) * 2);
            $base_score -= $time_penalty;
        }
        
        return max(5, min(100, round($base_score)));
    }
    
    /**
     * Analyze memory hosting limits based on test results
     * 
     * @param array $memory_results Array of memory test results
     * @return array Hosting limits analysis
     */
    private static function analyze_memory_hosting_limits($memory_results) {
        $limits = array(
            'max_allocation_achieved' => 0,
            'hosting_type_detected' => 'unknown',
            'memory_pressure_threshold' => 0,
            'allocation_efficiency_pattern' => 'unknown',
            'fragmentation_impact' => 'unknown'
        );
        
        foreach ($memory_results as $result) {
            // Track maximum allocation achieved
            if (isset($result['allocated']) && $result['allocated'] > $limits['max_allocation_achieved']) {
                $limits['max_allocation_achieved'] = $result['allocated'];
            }
            
            // Determine hosting type from behavior patterns
            if (isset($result['hosting_behavior']) && $limits['hosting_type_detected'] === 'unknown') {
                $limits['hosting_type_detected'] = $result['hosting_behavior'];
            }
            
            // Analyze fragmentation impact
            if (isset($result['test_type']) && $result['test_type'] === 'memory_fragmentation') {
                if (isset($result['fragmentation_detected']) && $result['fragmentation_detected']) {
                    $limits['fragmentation_impact'] = 'significant';
                } else {
                    $limits['fragmentation_impact'] = 'minimal';
                }
            }
        }
        
        return $limits;
    }
    
    /**
     * Evaluate memory pressure handling capability
     * 
     * @param array $memory_results Array of memory test results
     * @return array Memory pressure evaluation
     */
    private static function evaluate_memory_pressure_handling($memory_results) {
        $evaluation = array(
            'pressure_tolerance' => 0,
            'recovery_capability' => 0,
            'stability_under_load' => 0,
            'hosting_memory_management' => 'unknown'
        );
        
        $pressure_scores = array();
        $stability_scores = array();
        
        foreach ($memory_results as $result) {
            // Evaluate pressure resistance
            if (isset($result['pressure_resistance'])) {
                $pressure_scores[] = $result['pressure_resistance'];
            }
            
            // Evaluate stability
            if (isset($result['memory_stability'])) {
                $stability_scores[] = $result['memory_stability'];
            }
        }
        
        // Calculate averages
        if (!empty($pressure_scores)) {
            $evaluation['pressure_tolerance'] = array_sum($pressure_scores) / count($pressure_scores);
        }
        
        if (!empty($stability_scores)) {
            $evaluation['stability_under_load'] = array_sum($stability_scores) / count($stability_scores);
        }
        
        // Determine hosting memory management quality
        $overall_capability = ($evaluation['pressure_tolerance'] + $evaluation['stability_under_load']) / 2;
        
        if ($overall_capability > 85) {
            $evaluation['hosting_memory_management'] = 'excellent';
        } elseif ($overall_capability > 70) {
            $evaluation['hosting_memory_management'] = 'good';
        } elseif ($overall_capability > 50) {
            $evaluation['hosting_memory_management'] = 'adequate';
        } else {
            $evaluation['hosting_memory_management'] = 'poor';
        }
        
        return $evaluation;
    }
    
    /**
     * Run additional memory stress to reach minimum runtime
     * 
     * @param float $start_time Overall test start time
     * @param float $min_runtime Minimum required runtime
     * @return array Additional stress test results
     */
    private static function run_additional_memory_stress($start_time, $min_runtime) {
        $stress_start = microtime(true);
        
        $results = array(
            'test_type' => 'additional_stress',
            'stress_cycles_completed' => 0,
            'memory_allocations' => 0,
            'success' => false
        );
        
        try {
            $cycles = 0;
            $allocations = 0;
            
            while ((microtime(true) - $start_time) < $min_runtime) {
                // Allocate and deallocate memory in cycles
                $stress_data = array_fill(0, 500, 'stress_cycle_' . $cycles);
                $allocations++;
                unset($stress_data);
                
                if ($cycles % 10 === 0 && function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
                
                $cycles++;
                
                // Safety break
                if ($cycles > 1000) {
                    break;
                }
            }
            
            $results['stress_cycles_completed'] = $cycles;
            $results['memory_allocations'] = $allocations;
            $results['success'] = true;
            
        } catch (Exception $e) {
            $results['error'] = 'Additional stress failed: ' . $e->getMessage();
        }
        
        $results['test_time'] = microtime(true) - $stress_start;
        $results['score'] = min(100, $results['stress_cycles_completed'] / 10);
        
        return $results;
    }
} 