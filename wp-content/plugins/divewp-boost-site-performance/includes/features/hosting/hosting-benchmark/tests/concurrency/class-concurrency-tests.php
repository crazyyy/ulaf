<?php
/**
 * Concurrency Tests Controller
 *
 * Manages and orchestrates all concurrency-related benchmark tests.
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
 * Hosting Benchmark Concurrency Tests Controller Class
 */
class DiveWP_Benchmark_Concurrency_Tests {

    /**
     * Available sub-tests
     */
    const SUB_TESTS = array(
        'database_concurrency',
        'http_concurrency',
        'memory_concurrency',
        'file_concurrency'
    );

    /**
     * Constructor
     */
    public function __construct() {
        $this->load_sub_tests();
    }

    /**
     * Load all concurrency sub-test files
     */
    private function load_sub_tests() {
        require_once __DIR__ . '/database-concurrency.php';
        require_once __DIR__ . '/http-concurrency.php';
        require_once __DIR__ . '/memory-concurrency.php';
        require_once __DIR__ . '/file-concurrency.php';
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
     * Run a single concurrency test
     *
     * @param string $test_name Test name to run
     * @return array Test results
     */
    public function run_single_test($test_name) {
        // Apply time limit for individual tests
        $time_limit = get_transient('divewp_benchmark_time_limit');
        if (!$time_limit) {
            $max_execution_time = ini_get('max_execution_time');
            $time_limit = $max_execution_time > 0 ? $max_execution_time * 0.9 : 54; // Default 54 seconds (90% of 60)
            set_transient('divewp_benchmark_time_limit', $time_limit, HOUR_IN_SECONDS);
        }
        
        // BENCHMARK REQUIREMENT - Extend execution time for concurrency stress testing
        // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
        set_time_limit($time_limit);

        // Individual test files handle their own default configurations
        // No override from main controller

        switch ($test_name) {
            case 'database_concurrency':
                $result = DiveWP_Database_Concurrency_Test::run();
                break;
            case 'http_concurrency':
                $result = DiveWP_HTTP_Concurrency_Test::run();
                break;
            case 'memory_concurrency':
                $result = DiveWP_Memory_Concurrency_Test::run();
                break;
            case 'file_concurrency':
                $result = DiveWP_File_Concurrency_Test::run();
                break;
            default:
                $result = array(
                    'status' => 'error',
                    'message' => 'Unknown test: ' . $test_name
                );
        }

        return $result;
    }

    /**
     * Get human-readable test names
     *
     * @return array Test names
     */
    public function get_test_names() {
        return array(
            'database_concurrency' => __('Database Concurrency', 'divewp-boost-site-performance'),
            'http_concurrency' => __('HTTP Concurrency', 'divewp-boost-site-performance'),
            'memory_concurrency' => __('Memory Concurrency', 'divewp-boost-site-performance'),
            'file_concurrency' => __('File Concurrency', 'divewp-boost-site-performance')
        );
    }

    /**
     * Get test descriptions
     *
     * @return array Test descriptions
     */
    public function get_test_descriptions() {
        return array(
            'database_concurrency' => __('Multiple database operations simultaneously', 'divewp-boost-site-performance'),
            'http_concurrency' => __('Multiple HTTP requests simultaneously', 'divewp-boost-site-performance'),
            'memory_concurrency' => __('Memory competition under load', 'divewp-boost-site-performance'),
            'file_concurrency' => __('File system operations under load', 'divewp-boost-site-performance')
        );
    }

    /**
     * Cleanup all concurrency test transients
     */
    public function cleanup_transients() {
        foreach (self::SUB_TESTS as $test) {
            delete_transient('divewp_benchmark_concurrency_' . $test);
        }
    }
} 