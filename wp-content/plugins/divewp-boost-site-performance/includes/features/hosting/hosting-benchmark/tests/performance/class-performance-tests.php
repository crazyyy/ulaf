<?php
/**
 * Performance Tests Controller
 *
 * Manages and orchestrates all performance-related benchmark tests.
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
 * Hosting Benchmark Performance Tests Controller Class
 */
class DiveWP_Benchmark_Performance_Tests {

    /**
     * Available sub-tests
     */
    const SUB_TESTS = array(
        'price_calculations',
        'shipping_calculations', 
        'inventory_operations'
    );

    /**
     * Constructor
     */
    public function __construct() {
        $this->load_sub_tests();
    }

    /**
     * Load all performance sub-test files
     */
    private function load_sub_tests() {
        require_once __DIR__ . '/price-calculations.php';
        require_once __DIR__ . '/shipping-calculations.php';
        require_once __DIR__ . '/inventory-operations.php';
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
     * Run a single performance test
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
        
        // BENCHMARK REQUIREMENT - Extended time limit needed for performance stress testing
        set_time_limit($time_limit); // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged

        // Individual test files handle their own default configurations
        // No override from main controller

        switch ($test_name) {
            case 'price_calculations':
                $result = DiveWP_Price_Calculations_Test::run();
                break;
            case 'shipping_calculations':
                $result = DiveWP_Shipping_Calculations_Test::run();
                break;
            case 'inventory_operations':
                $result = DiveWP_Inventory_Operations_Test::run();
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
            'price_calculations' => __('Price Calculations', 'divewp-boost-site-performance'),
            'shipping_calculations' => __('Shipping Calculations', 'divewp-boost-site-performance'),
            'inventory_operations' => __('Inventory Operations', 'divewp-boost-site-performance')
        );
    }

    /**
     * Get test descriptions
     *
     * @return array Test descriptions
     */
    public function get_test_descriptions() {
        return array(
            'price_calculations' => __('How fast product prices update', 'divewp-boost-site-performance'),
            'shipping_calculations' => __('Speed of shipping cost calculations', 'divewp-boost-site-performance'),
            'inventory_operations' => __('Stock level checking speed', 'divewp-boost-site-performance')
        );
    }

    /**
     * Cleanup all performance test transients
     */
    public function cleanup_transients() {
        foreach (self::SUB_TESTS as $test) {
            delete_transient('divewp_benchmark_performance_' . $test);
        }
    }
} 