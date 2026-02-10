<?php
/**
 * Resources Tests Controller
 *
 * Manages and orchestrates all resource-related benchmark tests.
 * Replicates exact POC specifications with enhanced UX features.
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
 * Hosting Benchmark Resources Tests Controller Class
 */
class DiveWP_Benchmark_Resources_Tests {

    /**
     * Available sub-tests (exact POC order and specifications)
     */
    const SUB_TESTS = array(
        'cpu_tests',
        'memory_tests',
        'file_io_tests',
        'network_tests',
        'wordpress_tests'
    );

    /**
     * Maximum test time per section (POC compatibility)
     */
    const MAX_TEST_TIME = 54; // Default to 90% of 60 seconds if no PHP limit set

    /**
     * Maximum errors before test abort (POC compatibility)
     */
    const MAX_ERRORS = 5;

    /**
     * Constructor
     */
    public function __construct() {
        $this->load_sub_tests();
    }

    /**
     * Load all resource sub-test files
     */
    private function load_sub_tests() {
        require_once __DIR__ . '/cpu-tests.php';
        require_once __DIR__ . '/memory-tests.php';
        require_once __DIR__ . '/file-io-tests.php';
        require_once __DIR__ . '/network-tests.php';
        require_once __DIR__ . '/wordpress-tests.php';
        require_once __DIR__ . '/scoring.php';
    }

    /**
     * Get list of available sub-tests
     *
     * @return array Sub-test identifiers
     */
    public function get_sub_tests() {
        return self::SUB_TESTS;
    }

    /**
     * Run a single resource test
     *
     * @param string $test_name Test name to run
     * @return array Test results with enhanced UX data
     */
    public function run_single_test($test_name) {
        // Suppress non-critical PHP notices during intensive testing (debug/admin only)
        $original_error_reporting = error_reporting(); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting
        if (defined('WP_DEBUG') && WP_DEBUG && current_user_can('manage_options')) {
            error_reporting(E_ERROR | E_WARNING | E_PARSE); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting
        }
        
        // Apply time limit for individual tests
        $time_limit = get_transient('divewp_benchmark_time_limit');
        if (!$time_limit) {
            $max_execution_time = ini_get('max_execution_time');
            $time_limit = $max_execution_time > 0 ? $max_execution_time * 0.9 : 54; // Default 54 seconds (90% of 60)
            set_transient('divewp_benchmark_time_limit', $time_limit, HOUR_IN_SECONDS);
        }
        
        // BENCHMARK REQUIREMENT - Extended time limit needed for resource stress testing
        set_time_limit($time_limit); // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged

        // POC Configuration (exact replication)
        $test_config = array(
            'test_iterations' => 3, // POC default
            'cpu_math_iterations' => 10000, // POC default
            'cpu_conditional_iterations' => 100000, // POC default
            'memory_allocation_percentage' => 0.8, // POC default (80%)
            'max_test_time_per_section' => $time_limit, // Use actual PHP time limit
            'enabled_tests' => array(
                // CPU tests (POC enabled tests)
                'test_prime_generation',
                'test_mathematical_operations', 
                'test_conditional_logic',
                'test_string_processing',
                'test_array_operations',
                // WordPress tests (POC enabled tests)
                'test_shortcode_processing',
                'test_hook_execution',
                'test_transient_operations',
                'test_security_functions',
                // Memory test (POC enabled test)
                'test_memory_allocation_limits'
            )
        );

        switch ($test_name) {
            case 'cpu_tests':
                $result = DiveWP_Resources_CPU_Tests::run($test_config);
                break;
            case 'memory_tests':
                $result = DiveWP_Resources_Memory_Tests::run($test_config);
                break;
            case 'file_io_tests':
                $result = DiveWP_Resources_File_IO_Tests::run($test_config);
                break;
            case 'network_tests':
                $result = DiveWP_Resources_Network_Tests::run($test_config);
                break;
            case 'wordpress_tests':
                $result = DiveWP_Resources_WordPress_Tests::run($test_config);
                break;
            default:
                $result = array(
                    'status' => 'error',
                    'message' => 'Unknown test: ' . $test_name
                );
        }

        // Ensure enhanced UX data structure is returned
        if (!isset($result['performance_interpretation'])) {
            $result['performance_interpretation'] = DiveWP_Benchmark_Resources_Scoring::get_sub_test_performance_interpretation($test_name, $result);
        }

        // Restore original error reporting level (debug/admin only)
        if (defined('WP_DEBUG') && WP_DEBUG && current_user_can('manage_options')) {
            error_reporting($original_error_reporting); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting
        }

        return $result;
    }

    /**
     * Get human-readable test names (exact POC structure)
     *
     * @return array Test names
     */
    public static function get_test_names() {
        return array(
            'cpu_tests' => __('CPU Performance', 'divewp-boost-site-performance'),
            'memory_tests' => __('Memory Allocation', 'divewp-boost-site-performance'),
            'file_io_tests' => __('File I/O Performance', 'divewp-boost-site-performance'),
            'network_tests' => __('Network Capabilities', 'divewp-boost-site-performance'),
            'wordpress_tests' => __('WordPress Core Performance', 'divewp-boost-site-performance')
        );
    }

    /**
     * Get test descriptions (exact POC structure)
     *
     * @return array Test descriptions
     */
    public static function get_test_descriptions() {
        return array(
            'cpu_tests' => __('Prime generation, math operations, conditional logic, string processing, and array operations', 'divewp-boost-site-performance'),
            'memory_tests' => __('Memory allocation limits and efficiency testing', 'divewp-boost-site-performance'),
            'file_io_tests' => __('File system performance with various operation types and sizes', 'divewp-boost-site-performance'),
            'network_tests' => __('Network connectivity and response time testing', 'divewp-boost-site-performance'),
            'wordpress_tests' => __('Shortcode processing, hook execution, transient operations, and security functions', 'divewp-boost-site-performance')
        );
    }

    /**
     * Cleanup all resource test transients
     */
    public static function cleanup_transients() {
        foreach (self::SUB_TESTS as $test) {
            delete_transient('divewp_benchmark_resources_' . $test);
        }
        
        // Clean up any POC-related transients
        delete_transient('divewp_resource_test_' . get_current_user_id());
        delete_transient('divewp_test_config_' . get_current_user_id());
    }

    /**
     * Calculate performance statistics (POC compatibility method)
     * 
     * @param array $data Performance data array
     * @return array Statistical metrics
     */
    public static function calculate_performance_statistics($data) {
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
     * Get performance rating from score (POC compatibility method)
     * 
     * @param int $score Score from 0 to 100
     * @return string Rating label
     */
    public static function get_rating_from_score($score) {
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
     * Get resource interpretation (POC compatibility method)
     * 
     * @param int $score Overall resource score
     * @return string Resource interpretation
     */
    public static function get_resource_interpretation($score) {
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
} 