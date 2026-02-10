<?php
/**
 * Database Tests Controller
 *
 * Manages and orchestrates all database-related benchmark tests.
 * Each test runs independently with proper timeout handling and scoring.
 *
 * @package     DiveWP
 * @author      Oleg Petrov
 * @version     2.0.1
 * @license     GPL-2.0-or-later
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    die(esc_html__('Direct access not permitted.', 'divewp-boost-site-performance'));
}

/**
 * Database Benchmark Tests Controller Class
 * 
 * Handles 8 individual database performance tests:
 * - insert_operations: Data creation tests
 * - select_operations: Data retrieval tests
 * - update_operations: Data modification tests
 * - crypto_functions: Encryption and hash operations
 * - math_functions: Mathematical calculations
 * - string_functions: Text processing operations
 * - datetime_functions: Date and time operations
 * - aggregate_functions: SUM, COUNT, AVG operations
 */
class DiveWP_Benchmark_Database_Tests {

    /**
     * Database compatibility instance
     * @var DiveWP_Database_Compatibility
     */
    private static $db_compat = null;

    /**
     * Available sub-tests with proper naming conventions
     */
    const SUB_TESTS = array(
        'insert_operations',
        'select_operations', 
        'update_operations',
        'crypto_functions',
        'math_functions',
        'string_functions',
        'datetime_functions',
        'aggregate_functions'
    );

    /**
     * Constructor
     */
    public function __construct() {
        // Load database compatibility layer
        if (!class_exists('DiveWP_Database_Compatibility')) {
            require_once ABSPATH . 'wp-content/plugins/divewp-boost-site-performance/includes/class-divewp-database-compatibility.php';
        }
        
        // Initialize database compatibility
        if (self::$db_compat === null) {
            self::$db_compat = new DiveWP_Database_Compatibility();
        }
    }

    /**
     * Run all enabled database tests
     *
     * @param array $config Test configuration
     * @return array Complete test results
     */
    public function run_tests($config = array()) {
        $results = array();

        foreach (self::SUB_TESTS as $test_name) {
            if ($this->is_test_enabled($test_name, $config)) {
                // Pass only the specific test config (no override from main controller)
                $test_config = $config[$test_name] ?? array();
                
                $results[$test_name] = $this->run_single_test($test_name);
            }
        }

        return $results;
    }

    /**
     * Run a specific database sub-test
     *
     * @param string $test_name Sub-test identifier
     * @return array Test results with timing and operations data
     */
    public function run_single_test($test_name) {
        // Validate test name
        if (!in_array($test_name, self::SUB_TESTS, true)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // BENCHMARK REQUIREMENT - Debug logging for invalid test name detection
                error_log("DiveWP Database: Invalid test name: " . $test_name); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            }
            return array(
                'status' => 'error',
                'message' => __('Invalid database test name', 'divewp-boost-site-performance'),
                'test_name' => $test_name,
                'timestamp' => current_time('mysql')
            );
        }

        // Apply WordPress time limit with 90% safety margin
        $this->apply_safe_time_limit();

        // Individual test files handle their own default configurations
        // No override from main controller

        $start_time = microtime(true);
        $result = array();

        try {
            // Clear any previous database errors
            global $wpdb;
            $wpdb->last_error = '';
            
            // Execute the specific test
            switch ($test_name) {
                case 'insert_operations':
                    $result = $this->run_insert_operations();
                    break;
                case 'select_operations':
                    $result = $this->run_select_operations();
                    break;
                case 'update_operations':
                    $result = $this->run_update_operations();
                    break;
                case 'crypto_functions':
                    $result = $this->run_crypto_functions();
                    break;
                case 'math_functions':
                    $result = $this->run_math_functions();
                    break;
                case 'string_functions':
                    $result = $this->run_string_functions();
                    break;
                case 'datetime_functions':
                    $result = $this->run_datetime_functions();
                    break;
                case 'aggregate_functions':
                    $result = $this->run_aggregate_functions();
                    break;
                default:
                    throw new Exception(__('Test implementation not found', 'divewp-boost-site-performance'));
            }

        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // BENCHMARK REQUIREMENT - Debug logging for exception tracking
                error_log("DiveWP Database Error in " . $test_name . ": " . $e->getMessage()); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            }
            
            $result = array(
                'status' => 'error',
                'message' => $e->getMessage(),
                'test_name' => $test_name,
                'total_time' => microtime(true) - $start_time,
                'timestamp' => current_time('mysql')
            );
        }

        // Check for WordPress database errors even if no exception was thrown
        if (!empty($wpdb->last_error) && (!isset($result['status']) || $result['status'] !== 'error')) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // BENCHMARK REQUIREMENT - Debug logging for database error tracking
                error_log("DiveWP Database Error in " . $test_name . ": " . $wpdb->last_error); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            }
            $result = array(
                'status' => 'error',
                'message' => __('Database error: ', 'divewp-boost-site-performance') . $wpdb->last_error,
                'test_name' => $test_name,
                'total_time' => microtime(true) - $start_time,
                'timestamp' => current_time('mysql')
            );
        }

        // Ensure result has proper structure for JavaScript
        if (isset($result['status']) && $result['status'] === 'completed') {
            // Add missing fields that JavaScript expects
            if (!isset($result['score'])) {
                $result['score'] = 0;
            }
            if (!isset($result['rating'])) {
                $result['rating'] = 'unknown';
            }
            if (!isset($result['interpretation'])) {
                $result['interpretation'] = 'Test completed but no interpretation available';
            }
        }

        // Store result in transient for later retrieval
        $transient_name = 'divewp_benchmark_database_' . $test_name;
        set_transient($transient_name, $result, HOUR_IN_SECONDS);

        return $result;
    }

    /**
     * Check database compatibility for specific features
     *
     * @param string $feature Feature to check (e.g., 'fulltext_indexes', 'window_functions')
     * @return bool Whether the feature is supported
     */
    private function check_database_compatibility($feature) {
        if (self::$db_compat === null) {
            return false; // Default to not supported if compatibility layer not loaded
        }
        
        return self::$db_compat->supports_feature($feature);
    }

    /**
     * Get database version and type information
     *
     * @return array Database information
     */
    public function get_database_info() {
        global $wpdb;
        
        // Get database version with caching (static per session)
        $cache_key = 'divewp_db_version_info';
        $version = wp_cache_get($cache_key, 'divewp_db_tests');

        if (false === $version) {
            // DATABASE UTILITY - Direct query required for database version detection during test setup
            // WordPress has no equivalent function for detecting database version; caching applied for performance
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            $version = $wpdb->get_var("SELECT VERSION()");
            // Cache for the entire session since database version is static
            wp_cache_set($cache_key, $version, 'divewp_db_tests', 0);
        }
        
        $is_mariadb = stripos($version, 'mariadb') !== false;
        
        if ($is_mariadb) {
            preg_match('/(\d+)\.(\d+)\.(\d+)/', $version, $matches);
            $major = intval($matches[1] ?? 10);
            $minor = intval($matches[2] ?? 0);
        } else {
            preg_match('/(\d+)\.(\d+)\.(\d+)/', $version, $matches);
            $major = intval($matches[1] ?? 5);
            $minor = intval($matches[2] ?? 7);
        }
        
        return array(
            'version' => $version,
            'is_mariadb' => $is_mariadb,
            'major' => $major,
            'minor' => $minor,
            'full_version' => $version,
            'supports_window_functions' => $this->supports_window_functions($is_mariadb, $major, $minor),
            'supports_cte' => $this->supports_cte($is_mariadb, $major, $minor)
        );
    }

    /**
     * Check if database supports window functions
     *
     * @param bool $is_mariadb Whether this is MariaDB
     * @param int $major Major version number
     * @param int $minor Minor version number
     * @return bool Whether window functions are supported
     */
    private function supports_window_functions($is_mariadb, $major, $minor) {
        if ($is_mariadb) {
            // MariaDB supports window functions from 10.2+
            return ($major > 10) || ($major == 10 && $minor >= 2);
        } else {
            // MySQL supports window functions from 8.0+
            return $major >= 8;
        }
    }

    /**
     * Check if database supports Common Table Expressions (CTE)
     *
     * @param bool $is_mariadb Whether this is MariaDB
     * @param int $major Major version number
     * @param int $minor Minor version number
     * @return bool Whether CTE is supported
     */
    private function supports_cte($is_mariadb, $major, $minor) {
        if ($is_mariadb) {
            // MariaDB supports CTE from 10.2+
            return ($major > 10) || ($major == 10 && $minor >= 2);
        } else {
            // MySQL supports CTE from 8.0+
            return $major >= 8;
        }
    }

    /**
     * Run INSERT operations test (Data Creation)
     *
     * @param array $config Test configuration
     * @return array Test results
     */
    private function run_insert_operations() {
        $test_file = __DIR__ . '/insert-operations.php';
        if (!file_exists($test_file)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // BENCHMARK REQUIREMENT - Debug logging for missing test file detection
                error_log("Missing test file: " . $test_file); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            }
            return $this->get_fallback_result('insert_operations', 'Test file missing');
        }
        
        require_once $test_file;
        if (!class_exists('DiveWP_Insert_Operations_Test')) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // BENCHMARK REQUIREMENT - Debug logging for missing test class detection
                error_log("Missing test class: DiveWP_Insert_Operations_Test"); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            }
            return $this->get_fallback_result('insert_operations', 'Test class missing');
        }
        
        return DiveWP_Insert_Operations_Test::run();
    }

    /**
     * Run SELECT operations test (Data Retrieval)
     *
     * @param array $config Test configuration
     * @return array Test results
     */
    private function run_select_operations() {
        $test_file = __DIR__ . '/select-operations.php';
        if (!file_exists($test_file)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // BENCHMARK REQUIREMENT - Debug logging for missing test file detection
                error_log("Missing test file: " . $test_file); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            }
            return $this->get_fallback_result('select_operations', 'Test file missing');
        }
        
        require_once $test_file;
        if (!class_exists('DiveWP_Select_Operations_Test')) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // BENCHMARK REQUIREMENT - Debug logging for missing test class detection
                error_log("Missing test class: DiveWP_Select_Operations_Test"); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            }
            return $this->get_fallback_result('select_operations', 'Test class missing');
        }
        
        return DiveWP_Select_Operations_Test::run();
    }

    /**
     * Run UPDATE operations test (Data Updates)
     *
     * @param array $config Test configuration
     * @return array Test results
     */
    private function run_update_operations() {
        $test_file = __DIR__ . '/update-operations.php';
        if (!file_exists($test_file)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // BENCHMARK REQUIREMENT - Debug logging for missing test file detection
                error_log("Missing test file: " . $test_file); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            }
            return $this->get_fallback_result('update_operations', 'Test file missing');
        }
        
        require_once $test_file;
        if (!class_exists('DiveWP_Update_Operations_Test')) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // BENCHMARK REQUIREMENT - Debug logging for missing test class detection
                error_log("Missing test class: DiveWP_Update_Operations_Test"); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            }
            return $this->get_fallback_result('update_operations', 'Test class missing');
        }
        
        return DiveWP_Update_Operations_Test::run();
    }

    /**
     * Run cryptographic functions test
     *
     * @param array $config Test configuration
     * @return array Test results
     */
    private function run_crypto_functions() {
        $test_file = __DIR__ . '/crypto-functions.php';
        if (!file_exists($test_file)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // BENCHMARK REQUIREMENT - Debug logging for missing test file detection
                error_log("Missing test file: " . $test_file); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            }
            return $this->get_fallback_result('crypto_functions', 'Test file missing');
        }
        
        require_once $test_file;
        if (!class_exists('DiveWP_Crypto_Functions_Test')) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // BENCHMARK REQUIREMENT - Debug logging for missing test class detection
                error_log("Missing test class: DiveWP_Crypto_Functions_Test"); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            }
            return $this->get_fallback_result('crypto_functions', 'Test class missing');
        }
        
        return DiveWP_Crypto_Functions_Test::run();
    }

    /**
     * Run mathematical functions test
     *
     * @param array $config Test configuration
     * @return array Test results
     */
    private function run_math_functions() {
        $test_file = __DIR__ . '/math-functions.php';
        if (!file_exists($test_file)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // BENCHMARK REQUIREMENT - Debug logging for missing test file detection
                error_log("Missing test file: " . $test_file); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            }
            return $this->get_fallback_result('math_functions', 'Test file missing');
        }
        
        require_once $test_file;
        if (!class_exists('DiveWP_Math_Functions_Test')) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // BENCHMARK REQUIREMENT - Debug logging for missing test class detection
                error_log("Missing test class: DiveWP_Math_Functions_Test"); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            }
            return $this->get_fallback_result('math_functions', 'Test class missing');
        }
        
        return DiveWP_Math_Functions_Test::run();
    }

    /**
     * Run string functions test
     *
     * @param array $config Test configuration
     * @return array Test results
     */
    private function run_string_functions() {
        $test_file = __DIR__ . '/string-functions.php';
        if (!file_exists($test_file)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // BENCHMARK REQUIREMENT - Debug logging for missing test file detection
                error_log("Missing test file: " . $test_file); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            }
            return $this->get_fallback_result('string_functions', 'Test file missing');
        }
        
        require_once $test_file;
        if (!class_exists('DiveWP_String_Functions_Test')) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // BENCHMARK REQUIREMENT - Debug logging for missing test class detection
                error_log("Missing test class: DiveWP_String_Functions_Test"); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            }
            return $this->get_fallback_result('string_functions', 'Test class missing');
        }
        
        return DiveWP_String_Functions_Test::run();
    }

    /**
     * Run datetime functions test
     *
     * @param array $config Test configuration
     * @return array Test results
     */
    private function run_datetime_functions() {
        $test_file = __DIR__ . '/datetime-functions.php';
        if (!file_exists($test_file)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // BENCHMARK REQUIREMENT - Debug logging for missing test file detection
                error_log("Missing test file: " . $test_file); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            }
            return $this->get_fallback_result('datetime_functions', 'Test file missing');
        }
        
        require_once $test_file;
        if (!class_exists('DiveWP_DateTime_Functions_Test')) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // BENCHMARK REQUIREMENT - Debug logging for missing test class detection
                error_log("Missing test class: DiveWP_DateTime_Functions_Test"); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            }
            return $this->get_fallback_result('datetime_functions', 'Test class missing');
        }
        
        return DiveWP_DateTime_Functions_Test::run();
    }

    /**
     * Run aggregate functions test
     *
     * @param array $config Test configuration
     * @return array Test results
     */
    private function run_aggregate_functions() {
        $test_file = __DIR__ . '/aggregate-functions.php';
        if (!file_exists($test_file)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // BENCHMARK REQUIREMENT - Debug logging for missing test file detection
                error_log("Missing test file: " . $test_file); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            }
            return $this->get_fallback_result('aggregate_functions', 'Test file missing');
        }
        
        require_once $test_file;
        if (!class_exists('DiveWP_Aggregate_Functions_Test')) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // BENCHMARK REQUIREMENT - Debug logging for missing test class detection
                error_log("Missing test class: DiveWP_Aggregate_Functions_Test"); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            }
            return $this->get_fallback_result('aggregate_functions', 'Test class missing');
        }
        
        return DiveWP_Aggregate_Functions_Test::run();
    }

    /**
     * Get fallback result for missing or failed tests
     *
     * @param string $test_name Test identifier
     * @param string $reason Reason for fallback
     * @return array Fallback result structure
     */
    private function get_fallback_result($test_name, $reason) {
        return array(
            'status' => 'error',
            'test_name' => $test_name,
            'message' => $reason,
            'total_time' => 0,
            'operations_per_second' => 0,
            'score' => 0,
            'rating' => 'error',
            'interpretation' => sprintf(
                // translators: %1$s is the test name, %2$s is the error message explaining why it could not be executed
                __('Test %1$s could not be executed: %2$s', 'divewp-boost-site-performance'), 
                $test_name, 
                $reason
            ),
            'timestamp' => current_time('mysql')
        );
    }

    /**
     * Apply safe time limit (90% of max_execution_time)
     */
    private function apply_safe_time_limit() {
        $time_limit = get_transient('divewp_benchmark_time_limit');
        if (!$time_limit) {
            $max_execution_time = ini_get('max_execution_time');
            $time_limit = $max_execution_time > 0 ? absint($max_execution_time * 0.9) : 54;
            set_transient('divewp_benchmark_time_limit', $time_limit, HOUR_IN_SECONDS);
        }
        
        if ($time_limit > 0) {
            // BENCHMARK REQUIREMENT - Extended time limit needed for database stress testing
            set_time_limit($time_limit); // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
        }
    }

    /**
     * Check if a specific test is enabled
     *
     * @param string $test_name Test identifier
     * @param array $config Configuration array
     * @return bool Whether test is enabled
     */
    private function is_test_enabled($test_name, $config) {
        if (isset($config['disabled_tests']) && in_array($test_name, $config['disabled_tests'])) {
            return false;
        }
        
        if (isset($config['enabled_tests'])) {
            return in_array($test_name, $config['enabled_tests']);
        }
        
        return true; // Default to enabled
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
     * Get human-readable test names
     *
     * @return array Test names
     */
    public function get_test_names() {
        return array(
            'insert_operations' => __('Data Creation (INSERT)', 'divewp-boost-site-performance'),
            'select_operations' => __('Data Retrieval (SELECT)', 'divewp-boost-site-performance'),
            'update_operations' => __('Data Updates (UPDATE)', 'divewp-boost-site-performance'),
            'crypto_functions' => __('Crypto Functions', 'divewp-boost-site-performance'),
            'math_functions' => __('Math Functions', 'divewp-boost-site-performance'),
            'string_functions' => __('String Functions', 'divewp-boost-site-performance'),
            'datetime_functions' => __('DateTime Functions', 'divewp-boost-site-performance'),
            'aggregate_functions' => __('Aggregate Functions', 'divewp-boost-site-performance')
        );
    }

    /**
     * Get test descriptions
     *
     * @return array Test descriptions
     */
    public function get_test_descriptions() {
        return array(
            'insert_operations' => __('Adding new products and orders (500 records × 5 iterations)', 'divewp-boost-site-performance'),
            'select_operations' => __('Product searches and listings (2,500 queries × 5 iterations)', 'divewp-boost-site-performance'),
            'update_operations' => __('Intensive bulk updates and modifications (200 updates × 5 iterations)', 'divewp-boost-site-performance'),
            'crypto_functions' => __('Encryption and hash operations (1,000 operations)', 'divewp-boost-site-performance'),
            'math_functions' => __('Mathematical calculations (5,000 operations)', 'divewp-boost-site-performance'),
            'string_functions' => __('Text processing operations (3,000 operations)', 'divewp-boost-site-performance'),
            'datetime_functions' => __('Date and time operations (5,000 operations)', 'divewp-boost-site-performance'),
            'aggregate_functions' => __('SUM, COUNT, AVG operations (100 operations on 1,000 rows)', 'divewp-boost-site-performance')
        );
    }

    /**
     * Cleanup all database test transients
     */
    public function cleanup_transients() {
        foreach (self::SUB_TESTS as $test) {
            delete_transient('divewp_benchmark_database_' . $test);
        }
        delete_transient('divewp_benchmark_time_limit');
    }

    /**
     * Get test statistics for reporting
     *
     * @return array Test statistics
     */
    public function get_test_statistics() {
        $stats = array(
            'total_tests' => count(self::SUB_TESTS),
            'data_operation_tests' => 3, // INSERT, SELECT, UPDATE
            'function_tests' => 5, // Crypto, Math, String, DateTime, Aggregate
            'estimated_time_range' => '30-90 seconds'
        );

        return $stats;
    }
} 