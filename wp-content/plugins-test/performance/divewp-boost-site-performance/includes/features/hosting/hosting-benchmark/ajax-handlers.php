<?php
/**
 * AJAX Handlers for Hosting Benchmark
 *
 * Handles all AJAX requests for running benchmark tests.
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
 * DiveWP Benchmark AJAX Handler Class
 */
class DiveWP_Benchmark_Ajax {

    /**
     * Initialize AJAX handlers
     */
    public static function init() {
        // Initialize benchmark
        add_action('wp_ajax_divewp_benchmark_init', array(__CLASS__, 'handle_benchmark_init'));
        
        // Run single test
        add_action('wp_ajax_divewp_benchmark_run_test', array(__CLASS__, 'handle_run_test'));
        
        // Finalize benchmark
        add_action('wp_ajax_divewp_benchmark_finalize', array(__CLASS__, 'handle_benchmark_finalize'));
        
        // Get test status
        add_action('wp_ajax_divewp_benchmark_get_status', array(__CLASS__, 'handle_get_status'));
        
        // Saved benchmarks functionality
        add_action('wp_ajax_divewp_get_saved_benchmarks', array(__CLASS__, 'handle_get_saved_benchmarks'));
        add_action('wp_ajax_divewp_load_saved_benchmark', array(__CLASS__, 'handle_load_saved_benchmark'));
        add_action('wp_ajax_divewp_delete_saved_benchmark', array(__CLASS__, 'handle_delete_saved_benchmark'));
        add_action('wp_ajax_divewp_delete_all_benchmarks', array(__CLASS__, 'handle_delete_all_benchmarks'));
        
        // Debug endpoint removed
    }

    /**
     * Handle benchmark initialization
     */
    public static function handle_benchmark_init() {
        // Verify nonce - nonces are validated by wp_verify_nonce() which handles security checks
        // wp_unslash() is safe here as nonces are cryptographically signed and validated
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $nonce = isset($_POST['nonce']) ? wp_unslash($_POST['nonce']) : '';
        if (!wp_verify_nonce($nonce, 'divewp_benchmark_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed', 'divewp-boost-site-performance')));
        }

        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'divewp-boost-site-performance')));
        }

        // Detect PHP max_execution_time and store it
        $max_execution_time = ini_get('max_execution_time');
        $safe_time_limit = $max_execution_time > 0 ? $max_execution_time * 0.9 : 54; // Default 54 seconds (90% of 60)
        
        set_transient('divewp_benchmark_time_limit', $safe_time_limit, HOUR_IN_SECONDS);
        
        // Initialize benchmark session
        $session_id = uniqid('benchmark_', true);
        set_transient('divewp_benchmark_session', $session_id, HOUR_IN_SECONDS);
        
        // Get enabled tests from settings (sanitize structure and keys)
        // Avoid direct superglobal access for WPCS; use filter_input for arrays
        $raw_enabled_tests = filter_input(INPUT_POST, 'enabled_tests', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        if (!is_array($raw_enabled_tests)) {
            $raw_enabled_tests = array();
        }
        $enabled_tests = self::sanitize_enabled_tests_input($raw_enabled_tests);
        set_transient('divewp_benchmark_enabled_tests', $enabled_tests, HOUR_IN_SECONDS);
        
        // Clear any previous results
        self::cleanup_old_transients();
        
        wp_send_json_success(array(
            'session_id' => $session_id,
            'time_limit' => $safe_time_limit,
            'max_execution_time' => $max_execution_time,
            'enabled_tests' => $enabled_tests
        ));
    }

    /**
     * Handle running a single test
     */
    public static function handle_run_test() {
        // Verify nonce - nonces are validated by wp_verify_nonce() which handles security checks
        // wp_unslash() is safe here as nonces are cryptographically signed and validated
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $nonce = isset($_POST['nonce']) ? wp_unslash($_POST['nonce']) : '';
        if (!wp_verify_nonce($nonce, 'divewp_benchmark_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed', 'divewp-boost-site-performance')));
        }

        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'divewp-boost-site-performance')));
        }

        // Sanitize input variables - category and test_name must be valid identifiers
        $category = isset($_POST['category']) ? sanitize_text_field(wp_unslash($_POST['category'])) : '';
        $test_name = isset($_POST['test_name']) ? sanitize_text_field(wp_unslash($_POST['test_name'])) : '';
        // REMOVED: No external configuration - test files control their own settings
        // $config = isset($_POST['config']) ? $_POST['config'] : array();

        if (empty($category) || empty($test_name)) {
            self::limited_log('DiveWP Benchmark: Missing category or test_name');
            wp_send_json_error(array('message' => __('Invalid test parameters', 'divewp-boost-site-performance')));
        }

        // Load test controller based on category
        $controller = null;
        switch ($category) {
            case 'performance':
                require_once DIVEWP_PLUGIN_DIR . 'includes/features/hosting/hosting-benchmark/tests/performance/class-performance-tests.php';
                $controller = new DiveWP_Benchmark_Performance_Tests();
                break;
            case 'database':
                require_once DIVEWP_PLUGIN_DIR . 'includes/features/hosting/hosting-benchmark/tests/database/class-database-tests.php';
                $controller = new DiveWP_Benchmark_Database_Tests();
                break;
            case 'resources':
                require_once DIVEWP_PLUGIN_DIR . 'includes/features/hosting/hosting-benchmark/tests/resources/class-resources-tests.php';
                $controller = new DiveWP_Benchmark_Resources_Tests();
                break;
            case 'concurrency':
                require_once DIVEWP_PLUGIN_DIR . 'includes/features/hosting/hosting-benchmark/tests/concurrency/class-concurrency-tests.php';
                $controller = new DiveWP_Benchmark_Concurrency_Tests();
                break;
            default:
                self::limited_log('DiveWP Benchmark: Unknown test category: ' . $category);
                wp_send_json_error(array('message' => __('Unknown test category', 'divewp-boost-site-performance')));
        }

        // Run the test
        try {
            // CRITICAL: Check if this specific test is enabled BEFORE running anything
            $enabled_tests = get_transient('divewp_benchmark_enabled_tests');
            $is_test_enabled = false;
            
            if (is_array($enabled_tests) && isset($enabled_tests[$category])) {
                // Check if this specific test is in the enabled list for this category
                $is_test_enabled = in_array($test_name, $enabled_tests[$category]);
            } else {
                // If no enabled tests config found, assume all tests are enabled (fallback)
                $is_test_enabled = true;
            }
            
            if (!$is_test_enabled) {
                // Test is disabled, return a skipped result immediately
                self::limited_log('DiveWP Benchmark: Test ' . $category . '/' . $test_name . ' is disabled, skipping');
                
                $result = array(
                    'status' => 'skipped',
                    'test_name' => $test_name,
                    'message' => __('Test disabled by user configuration', 'divewp-boost-site-performance'),
                    'total_time' => 0,
                    'score' => 0,
                    'rating' => 'skipped',
                    'interpretation' => __('This test was skipped based on your settings', 'divewp-boost-site-performance'),
                    'timestamp' => current_time('mysql')
                );
                
                // Store skipped result in transient for consistency
                $transient_name = 'divewp_benchmark_' . $category . '_' . $test_name;
                set_transient($transient_name, $result, HOUR_IN_SECONDS);
                
                wp_send_json_success(array(
                    'category' => $category,
                    'test_name' => $test_name,
                    'result' => $result
                ));
                return;
            }
            
            // Test is enabled, proceed to run it
            self::limited_log('DiveWP Benchmark: Running enabled test ' . $category . '/' . $test_name);
            $result = $controller->run_single_test($test_name); // No external configuration passed
            
            // Store result in transient
            $transient_name = 'divewp_benchmark_' . $category . '_' . $test_name;
            $transient_stored = set_transient($transient_name, $result, HOUR_IN_SECONDS);
            
            // FALLBACK: If transient fails, store in options table
            if (!$transient_stored) {
                $option_name = 'divewp_benchmark_' . $category . '_' . $test_name;
                update_option($option_name, $result);
            }
            
            wp_send_json_success(array(
                'category' => $category,
                'test_name' => $test_name,
                'result' => $result
            ));
        } catch (Exception $e) {
            // Get current error count for this session
            $error_count_key = 'divewp_benchmark_errors_' . get_transient('divewp_benchmark_session');
            $session_error_count = get_transient($error_count_key) ?: 0;
            $session_error_count++;
            set_transient($error_count_key, $session_error_count, HOUR_IN_SECONDS);
            
            // Only log first few errors to avoid log flooding
            if ($session_error_count <= 3) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    self::limited_log('DiveWP Benchmark Error in ' . $category . '/' . $test_name . ': ' . $e->getMessage());
                }
            } elseif ($session_error_count == 4) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    self::limited_log('DiveWP Benchmark: Too many errors, suppressing further error logging for this session');
                }
            }
            
            // Handle timeout or process kill
            $result = array(
                'status' => 'error',
                'message' => $e->getMessage(),
                'test_name' => $test_name,
                'total_time' => 0,
                'score' => 0,
                'rating' => 'error',
                'interpretation' => sprintf(
                    // translators: %1$s is the specific error message explaining why the test failed
                    __('Test failed: %1$s', 'divewp-boost-site-performance'), 
                    $e->getMessage()
                ),
                'error_count' => $session_error_count,
                'timestamp' => current_time('mysql')
            );
            
            $transient_name = 'divewp_benchmark_' . $category . '_' . $test_name;
            set_transient($transient_name, $result, HOUR_IN_SECONDS);
            
            wp_send_json_error(array(
                'category' => $category,
                'test_name' => $test_name,
                'result' => $result,
                'session_error_count' => $session_error_count
            ));
        }
    }

    /**
     * Handle benchmark finalization
     */
    public static function handle_benchmark_finalize() {
        // Verify nonce - nonces are validated by wp_verify_nonce() which handles security checks
        // wp_unslash() is safe here as nonces are cryptographically signed and validated
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $nonce = isset($_POST['nonce']) ? wp_unslash($_POST['nonce']) : '';
        if (!wp_verify_nonce($nonce, 'divewp_benchmark_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed', 'divewp-boost-site-performance')));
        }

        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'divewp-boost-site-performance')));
        }

        // Get enabled tests to only process categories that were actually run
        $enabled_tests = get_transient('divewp_benchmark_enabled_tests');
        if (empty($enabled_tests)) {
            $enabled_tests = array(); // Fallback to empty if no enabled tests found
        }
        
        // Only process categories that have enabled tests
        $categories_to_process = array();
        foreach (array('performance', 'database', 'resources', 'concurrency') as $category) {
            if (isset($enabled_tests[$category]) && !empty($enabled_tests[$category])) {
                $categories_to_process[] = $category;
            }
        }
        
        // Collect results only from enabled categories
        $all_results = array();
        $category_scores = array();

        foreach ($categories_to_process as $category) {
            $category_results = array();

            // Load scoring class for category (once per category)
            $scoring_file = DIVEWP_PLUGIN_DIR . 'includes/features/hosting/hosting-benchmark/tests/' . $category . '/scoring.php';

            if (file_exists($scoring_file)) {
                require_once $scoring_file;

                // Determine which sub-tests to collect: use enabled list directly
                $sub_tests = isset($enabled_tests[$category]) && is_array($enabled_tests[$category])
                    ? array_values($enabled_tests[$category])
                    : array();

                // Short-circuit if nothing enabled (safety)
                if (empty($sub_tests)) {
                    $all_results[$category] = array();
                    continue;
                }

                // Batch fetch available transients for enabled tests, then fallback per test if needed
                $batched = self::fetch_transients_for_tests($category, $sub_tests);

                foreach ($sub_tests as $test_name) {
                    $result = isset($batched[$test_name]) ? $batched[$test_name] : false;

                    if ($result === false) {
                        // Standard transient lookup fallback
                        $transient_name = 'divewp_benchmark_' . $category . '_' . $test_name;
                        $result = get_transient($transient_name);
                    }

                    if ($result !== false) {
                        if (isset($result['status']) && $result['status'] !== 'skipped') {
                            $category_results[$test_name] = $result;
                        }
                    } else {
                        // FALLBACK: Try options table if transient failed
                        $option_name = 'divewp_benchmark_' . $category . '_' . $test_name;
                        $result = get_option($option_name, false);
                        if ($result !== false && isset($result['status']) && $result['status'] !== 'skipped') {
                            $category_results[$test_name] = $result;
                            delete_option($option_name);
                        }
                    }
                }

                // Calculate category score
                $scoring_class = self::get_scoring_class($category);
                if (class_exists($scoring_class) && method_exists($scoring_class, 'calculate_category_score')) {
                    $category_score = $scoring_class::calculate_category_score($category_results);
                    $category_scores[$category] = $category_score;
                } else {
                    self::limited_log("DiveWP Benchmark: Scoring class " . $scoring_class . " not found or method missing");
                    $category_scores[$category] = array(
                        'score' => 0,
                        'rating' => 'error',
                        'interpretation' => 'Scoring system not available'
                    );
                }
            } else {
                self::limited_log("DiveWP Benchmark: Scoring file not found: " . $scoring_file);
            }

            $all_results[$category] = $category_results;
        }

        // Calculate overall score only from enabled categories
        $overall_score = 0;
        $total_weight = 0;
        foreach ($category_scores as $category => $score_data) {
            if (isset($score_data['score']) && is_numeric($score_data['score']) && $score_data['score'] > 0) {
                // Use equal weighting for now
                $weight = 1.0;
                $overall_score += $score_data['score'] * $weight;
                $total_weight += $weight;
            }
        }
        
        if ($total_weight > 0) {
            $overall_score = $overall_score / $total_weight;
        }

        // Save to database using DiveWP_DB_Access
        global $wpdb;
        require_once DIVEWP_PLUGIN_DIR . 'includes/class-divewp-db-access.php';
        require_once DIVEWP_PLUGIN_DIR . 'includes/class-divewp-database.php';
        
        // Ensure tables exist before attempting to save
        if (!DiveWP_Database::verify_tables()) {
            DiveWP_Database::init_tables();
        }
        
        $db_access = DiveWP_DB_Access::get_instance();
        $table_name = 'divewp_benchmark_results';
        
        $data = array(
            'user_id' => get_current_user_id(),
            'session_id' => get_transient('divewp_benchmark_session'),
            'overall_score' => $overall_score,
            'performance_score' => isset($category_scores['performance']['score']) ? $category_scores['performance']['score'] : 0,
            'database_score' => isset($category_scores['database']['score']) ? $category_scores['database']['score'] : 0,
            'resources_score' => isset($category_scores['resources']['score']) ? $category_scores['resources']['score'] : 0,
            'concurrency_score' => isset($category_scores['concurrency']['score']) ? $category_scores['concurrency']['score'] : 0,
            'full_results' => wp_json_encode($all_results),
            'test_date' => current_time('mysql')
        );
        
        $saved = $db_access->insert($table_name, $data);
        if (!$saved) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                self::limited_log('DiveWP Benchmark: Failed to save results to database');
            }
        }
        
        // Cleanup transients
        self::cleanup_benchmark_transients();
        
        // Transform results for template display (like PoC)
        $template_data = self::transform_results_for_template($all_results, $category_scores);
        
        $final_response = array(
            'overall_score' => $overall_score,
            'category_scores' => $category_scores,
            'all_results' => $all_results,
            'template_data' => $template_data
        );
        
        wp_send_json_success($final_response);
    }

    /**
     * Get current benchmark status
     */
    public static function handle_get_status() {
        // Verify nonce - nonces are validated by wp_verify_nonce() which handles security checks
        // wp_unslash() is safe here as nonces are cryptographically signed and validated
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $nonce = isset($_POST['nonce']) ? wp_unslash($_POST['nonce']) : '';
        if (!wp_verify_nonce($nonce, 'divewp_benchmark_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed', 'divewp-boost-site-performance')));
        }

        $categories = array('performance', 'database', 'resources', 'concurrency');
        $status = array();

        foreach ($categories as $category) {
            $status[$category] = array();
            
            // Check for stored results
            $controller_class = self::get_controller_class($category);
            if (class_exists($controller_class)) {
                $controller = new $controller_class();
                $sub_tests = $controller->get_sub_tests();
                
                foreach ($sub_tests as $test_name) {
                    $transient_name = 'divewp_benchmark_' . $category . '_' . $test_name;
                    $result = get_transient($transient_name);
                    $status[$category][$test_name] = $result !== false ? 'completed' : 'pending';
                }
            }
        }

        wp_send_json_success(array('status' => $status));
    }

    /**
     * Cleanup old benchmark transients
     */
    private static function cleanup_old_transients() {
        global $wpdb;
        
        // Delete all benchmark-related transients
        // BENCHMARK CLEANUP - Direct database query required for bulk transient cleanup during benchmark initialization
        // WordPress has no bulk transient cleanup function; individual delete_transient() calls would be extremely inefficient
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $like_all      = '_transient_divewp_benchmark_%';
        $not_time      = '_transient_divewp_benchmark_time_limit';
        $not_session   = '_transient_divewp_benchmark_session';
        $not_enabled   = '_transient_divewp_benchmark_enabled_tests';
        $prepared_sql1 = $wpdb->prepare(
            'DELETE FROM ' . $wpdb->options . ' WHERE option_name LIKE %s AND option_name NOT LIKE %s AND option_name NOT LIKE %s AND option_name NOT LIKE %s',
            $like_all,
            $not_time,
            $not_session,
            $not_enabled
        );
        $wpdb->query($prepared_sql1); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
        
        // Also delete options table fallback entries
        // BENCHMARK CLEANUP - Direct database query required for bulk cleanup of benchmark fallback options
        // WordPress has no bulk option cleanup function for pattern-based deletion
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $like_bench    = 'divewp_benchmark_%';
        $not_transient = '_transient_%';
        $prepared_sql2 = $wpdb->prepare(
            'DELETE FROM ' . $wpdb->options . ' WHERE option_name LIKE %s AND option_name NOT LIKE %s',
            $like_bench,
            $not_transient
        );
        $wpdb->query($prepared_sql2); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
    }

    /**
     * Sanitize enabled_tests array from POST
     *
     * Expected shape:
     *   [ 'performance' => [ 'price_calculations', ... ], 'database' => [ ... ], 'resources' => [ ... ], 'concurrency' => [ ... ] ]
     * Unknown categories or tests are discarded.
     *
     * @param mixed $raw Raw input value from request
     * @return array Sanitized structure
     */
    private static function sanitize_enabled_tests_input($raw) {
        $sanitized = array();

        if (!is_array($raw)) {
            return $sanitized;
        }

        // Allowed categories
        $allowed_categories = array('performance', 'database', 'resources', 'concurrency');

        foreach ($allowed_categories as $category) {
            if (!isset($raw[$category])) {
                continue;
            }

            $tests = $raw[$category];
            if (!is_array($tests)) {
                continue;
            }

            $clean_tests = array();
            foreach ($tests as $test_name) {
                if (!is_string($test_name)) {
                    continue;
                }
                $clean_tests[] = sanitize_key($test_name);
            }

            if (!empty($clean_tests)) {
                $sanitized[$category] = array_values(array_unique($clean_tests));
            }
        }

        return $sanitized;
    }

    /**
     * Cleanup all benchmark transients
     */
    private static function cleanup_benchmark_transients() {
        global $wpdb;
        
        // Guard to avoid duplicate cleanup runs in the same request/session
        $session_id = get_transient('divewp_benchmark_session');
        $guard_key = 'divewp_benchmark_cleanup_guard_' . md5($session_id ? $session_id : 'global');
        if (get_transient($guard_key)) {
            return;
        }
        set_transient($guard_key, 1, MINUTE_IN_SECONDS * 5);

        // Delete all benchmark-related transients including error counters
        // BENCHMARK CLEANUP - Direct database query required for comprehensive transient cleanup after benchmark completion
        // WordPress has no bulk transient cleanup function; individual calls would create hundreds of unnecessary queries
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
            WHERE option_name LIKE '_transient_divewp_benchmark_%'"
        );
        
        // Also delete timeout transients
        // BENCHMARK CLEANUP - Direct database query required for timeout transient cleanup
        // WordPress stores transient timeouts separately; no bulk cleanup function available
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
            WHERE option_name LIKE '_transient_timeout_divewp_benchmark_%'"
        );
        
        // Clean up any stray options that might have been used as fallback
        // BENCHMARK CLEANUP - Direct database query required for fallback option cleanup
        // WordPress has no bulk option deletion by pattern; individual delete_option() calls would be inefficient
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
            WHERE option_name LIKE 'divewp_benchmark_%' 
            AND option_name NOT LIKE '_transient_%'"
        );
        
        self::limited_log("DiveWP Benchmark: Cleaned up all transients and temporary options");
    }

    /**
     * Minimal logging helper to prevent log flooding within a single request
     */
    private static function limited_log($message, $limit = 5) {
        static $count = 0;
        if ($count < $limit) {
            $count++;
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // BENCHMARK REQUIREMENT - Minimal logging for diagnostic purposes, gated by WP_DEBUG
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log($message);
            }
        } elseif ($count === $limit) {
            $count++;
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // BENCHMARK REQUIREMENT - Suppress further log noise after limit
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log('DiveWP Benchmark: too many log messages, suppressing further output');
            }
        }
    }

    /**
     * Get the correct controller class name for a category
     *
     * @param string $category Test category name
     * @return string Controller class name
     */
    private static function get_controller_class($category) {
        $class_mapping = array(
            'performance' => 'DiveWP_Benchmark_Performance_Tests',
            'database' => 'DiveWP_Benchmark_Database_Tests',
            'resources' => 'DiveWP_Benchmark_Resources_Tests',
            'concurrency' => 'DiveWP_Benchmark_Concurrency_Tests'
        );
        
        return isset($class_mapping[$category]) ? $class_mapping[$category] : 'DiveWP_Benchmark_' . ucfirst($category) . '_Tests';
    }

    /**
     * Batch fetch transients for a list of enabled tests in a category
     *
     * This reduces multiple get_transient() calls by reading matching
     * _transient_divewp_benchmark_<category>_<test> rows in a single query.
     * Falls back to per-key get_transient if direct read fails.
     *
     * @param string $category
     * @param array  $sub_tests
     * @return array map test_name => result array or false
     */
    private static function fetch_transients_for_tests($category, $sub_tests) {
        global $wpdb;

        $results_map = array();
        if (empty($sub_tests)) {
            return $results_map;
        }

        // Prepare LIKE list for option_name IN (...) by building exact names
        $option_names = array();
        foreach ($sub_tests as $test) {
            $option_names[] = '_transient_divewp_benchmark_' . $category . '_' . $test;
        }

        // Build placeholders for variable-length IN clause
        $placeholders = array_fill(0, count($option_names), '%s');

        // Execute; if it fails, return empty map and let caller fallback per-key
        // BENCHMARK OPTIMIZATION - Batch transient fetching
        // Prepare variable-length IN list safely using call_user_func_array
        $query  = 'SELECT option_name, option_value FROM ' . $wpdb->options . ' WHERE option_name IN (' . implode(',', $placeholders) . ')';
        $params = array_merge(array($query), $option_names);
        // WPCS: Dynamic placeholders via call_user_func_array; justified for variable-length IN clause
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
        $prepared_query = call_user_func_array(array($wpdb, 'prepare'), $params);
        // BENCHMARK OPTIMIZATION - Prepared query variable required for variable-length IN clause; $prepared_query is output of $wpdb->prepare() which is safe; option_names are internally generated transient keys, not user input
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter
        $rows = $wpdb->get_results($prepared_query, ARRAY_A);
        if (!is_array($rows)) {
            return $results_map;
        }

        foreach ($rows as $row) {
            $raw_name = $row['option_name'];
            $stored = maybe_unserialize($row['option_value']);
            // Extract test name from _transient_divewp_benchmark_<category>_<test>
            $prefix = '_transient_divewp_benchmark_' . $category . '_';
            if (strpos($raw_name, $prefix) === 0) {
                $test = substr($raw_name, strlen($prefix));
                $results_map[$test] = $stored;
            }
        }

        return $results_map;
    }

    /**
     * Get the correct scoring class name for a category
     *
     * @param string $category Test category name
     * @return string Scoring class name
     */
    private static function get_scoring_class($category) {
        $class_mapping = array(
            'performance' => 'DiveWP_Benchmark_Performance_Scoring',
            'database' => 'DiveWP_Benchmark_Database_Scoring',
            'resources' => 'DiveWP_Benchmark_Resources_Scoring',
            'concurrency' => 'DiveWP_Benchmark_Concurrency_Scoring'
        );
        
        return isset($class_mapping[$category]) ? $class_mapping[$category] : 'DiveWP_Benchmark_' . ucfirst($category) . '_Scoring';
    }

    /**
     * Transform raw benchmark results into template-ready format (copied from PoC)
     * 
     * @param array $all_results Raw test results
     * @param array $category_scores Calculated scores
     * @return array Template-ready results for all test cards
     */
    private static function transform_results_for_template($all_results, $category_scores) {
        $template_data = array();
        
        // Performance Card
        if (isset($all_results['performance'])) {
            $perf_results = $all_results['performance'];
            $perf_score = $category_scores['performance'] ?? array('score' => 0, 'rating' => 'unknown');
            
            // Load performance scoring class for enhanced features
            require_once DIVEWP_PLUGIN_DIR . 'includes/features/hosting/hosting-benchmark/tests/performance/scoring.php';
            
            // Get enhanced performance interpretations for each sub-test
            $enhanced_sub_tests = self::get_enhanced_performance_sub_tests($perf_results);
            
            // Get score impact analysis
            $score_impact = array();
            if (isset($perf_score['sub_scores'])) {
                $score_impact = DiveWP_Benchmark_Performance_Scoring::get_score_impact_analysis($perf_score['sub_scores']);
            }
            
            // Get business impact and recommendations
            $business_impact = array();
            $recommendations = array();
            if (isset($perf_score['sub_scores'])) {
                $recommendations = DiveWP_Benchmark_Performance_Scoring::get_recommendations($perf_score['sub_scores']);
            }
            
            $template_data['performance'] = array(
                'test_name' => 'E‑commerce Performance',
                'icon' => '⚡',
                'score' => $perf_score['score'] ?? 0,
                'rating' => $perf_score['rating'] ?? 'unknown',
                'total_time' => self::calculate_total_time($perf_results),
                'business_impact' => $business_impact,
                'sub_tests' => $enhanced_sub_tests,
                'summary' => 'E‑commerce operations speed (pricing, shipping, stock checks)',
                'recommendations' => $recommendations,
                'technical_details' => array(),
                // Enhanced UX features for performance
                'score_impact_analysis' => $score_impact,
                'performance_interpretations' => true // Flag to indicate this category has enhanced features
            );
        }
        
        // Database Card
        if (isset($all_results['database'])) {
            $db_results = $all_results['database'];
            $db_score = $category_scores['database'] ?? array('score' => 0, 'rating' => 'unknown');
            
            // Load database scoring class for enhanced features
            require_once DIVEWP_PLUGIN_DIR . 'includes/features/hosting/hosting-benchmark/tests/database/scoring.php';
            
            // Get enhanced database interpretations for each sub-test
            $enhanced_sub_tests = self::get_enhanced_database_sub_tests($db_results);
            
            // Get score impact analysis
            $score_impact = array();
            if (isset($db_score['sub_scores'])) {
                $score_impact = DiveWP_Benchmark_Database_Scoring::get_score_impact_analysis($db_score['sub_scores']);
            }
            
            // Get business impact and recommendations
            $business_impact = array();
            $recommendations = array();
            if (isset($db_score['sub_scores'])) {
                $recommendations = DiveWP_Benchmark_Database_Scoring::get_recommendations($db_score['sub_scores']);
            }
            
            $template_data['database'] = array(
                'test_name' => 'Database Tests',
                'icon' => '🗄️',
                'score' => $db_score['score'] ?? 0,
                'rating' => $db_score['rating'] ?? 'unknown',
                'total_time' => self::calculate_total_time($db_results),
                'business_impact' => $business_impact,
                'sub_tests' => $enhanced_sub_tests,
                'summary' => 'Database tests measure INSERT, SELECT, UPDATE and MySQL function performance',
                'recommendations' => $recommendations,
                'technical_details' => array(),
                // Enhanced UX features for database
                'score_impact_analysis' => $score_impact,
                'performance_interpretations' => true // Flag to indicate this category has enhanced features
            );
        }
        
        // Resources Card
        if (isset($all_results['resources'])) {
            $res_results = $all_results['resources'];
            $res_score = $category_scores['resources'] ?? array('score' => 0, 'rating' => 'unknown');
            
            // Load resources scoring class for enhanced features
            require_once DIVEWP_PLUGIN_DIR . 'includes/features/hosting/hosting-benchmark/tests/resources/scoring.php';
            
            // Get enhanced resources interpretations for each sub-test
            $enhanced_sub_tests = self::get_enhanced_resources_sub_tests($res_results);
            
            // Get score impact analysis
            $score_impact = array();
            if (isset($res_score['sub_scores'])) {
                $score_impact = DiveWP_Benchmark_Resources_Scoring::get_score_impact_analysis($res_score['sub_scores']);
            }
            
            // Get business impact and recommendations
            $business_impact = array();
            $recommendations = array();
            if (isset($res_score['sub_scores'])) {
                $recommendations = DiveWP_Benchmark_Resources_Scoring::get_recommendations($res_score['sub_scores']);
            }
            
            $template_data['resources'] = array(
                'test_name' => 'Resources Tests',
                'icon' => '💻',
                'score' => $res_score['score'] ?? 0,
                'rating' => $res_score['rating'] ?? 'unknown',
                'total_time' => self::calculate_total_time($res_results),
                'business_impact' => $business_impact,
                'sub_tests' => $enhanced_sub_tests,
                'summary' => 'Resource tests measure CPU, memory, file I/O, network, and WordPress performance',
                'recommendations' => $recommendations,
                'technical_details' => array(),
                // Enhanced UX features for resources
                'score_impact_analysis' => $score_impact,
                'performance_interpretations' => true // Flag to indicate this category has enhanced features
            );
        }
        
        // Concurrency Card
        if (isset($all_results['concurrency'])) {
            $conc_results = $all_results['concurrency'];
            $conc_score = $category_scores['concurrency'] ?? array('score' => 0, 'rating' => 'unknown');
            
            // Load concurrency scoring class for enhanced features
            require_once DIVEWP_PLUGIN_DIR . 'includes/features/hosting/hosting-benchmark/tests/concurrency/scoring.php';
            
            // Analyze concurrency test statuses for enhanced UX
            $status_analysis = self::analyze_concurrency_status($conc_results);
            
            // Get enhanced performance interpretations for each sub-test
            $enhanced_sub_tests = self::get_enhanced_concurrency_sub_tests($conc_results);
            
            // Get baseline comparison data
            $baseline_data = DiveWP_Benchmark_Concurrency_Scoring::get_baseline_comparison_data();
            
            // Get score impact analysis
            $score_impact = array();
            if (isset($conc_score['sub_scores'])) {
                $score_impact = DiveWP_Benchmark_Concurrency_Scoring::get_score_impact_analysis($conc_score['sub_scores']);
            }
            
            // Get business impact and recommendations
            $business_impact = array();
            $recommendations = array();
            if (isset($conc_score['sub_scores'])) {
                $business_impact = DiveWP_Benchmark_Concurrency_Scoring::get_business_impact($conc_score['sub_scores']);
                $recommendations = DiveWP_Benchmark_Concurrency_Scoring::get_recommendations($conc_score['sub_scores']);
            }
            
            // Get overall hosting quality assessment
            $hosting_assessment = self::get_concurrency_hosting_assessment($conc_score['score'] ?? 0, $score_impact);
            
            $template_data['concurrency'] = array(
                'test_name' => 'Concurrency Tests',
                'icon' => '🔄',
                'score' => $conc_score['score'] ?? 0,
                'rating' => $conc_score['rating'] ?? 'unknown',
                'total_time' => self::calculate_total_time($conc_results),
                'business_impact' => $business_impact,
                'sub_tests' => $enhanced_sub_tests,
                'summary' => 'Concurrency tests measure multi-user and simultaneous operation handling',
                'recommendations' => $recommendations,
                'technical_details' => array(),
                // Enhanced UX features for concurrency
                'status_indicator' => $status_analysis['status_indicator'],
                'issue_explanation' => $status_analysis['issue_explanation'],
                'completion_percentage' => $status_analysis['completion_percentage'],
                'baseline_comparison' => $baseline_data,
                'score_impact_analysis' => $score_impact,
                'hosting_quality_assessment' => $hosting_assessment,
                'performance_interpretations' => true // Flag to indicate this category has enhanced features
            );
        }
        
        return $template_data;
    }

    /**
     * Calculate total time from test results
     */
    private static function calculate_total_time($results) {
        $total_ms = 0;
        
        foreach ($results as $test_result) {
            if (isset($test_result['total_time'])) {
                $total_ms += $test_result['total_time'] * 1000; // Convert to ms
            }
        }
        
        return self::format_time_for_display($total_ms);
    }

    /**
     * Get performance sub-tests formatted for template
     */
    private static function get_performance_sub_tests($perf_results) {
        $sub_tests = array();
        
        if (isset($perf_results['price_calculations'])) {
            $result = $perf_results['price_calculations'];
            $sub_tests[] = array(
                'name' => 'Price Calculations',
                'description' => 'How fast product prices update',
                'time' => isset($result['total_time']) ? self::format_time_for_display($result['total_time'] * 1000) : '0.00s',
                'operations' => '2500 calculations × 15 iterations'
            );
        }
        
        if (isset($perf_results['shipping_calculations'])) {
            $result = $perf_results['shipping_calculations'];
            $sub_tests[] = array(
                'name' => 'Shipping Calculations',
                'description' => 'Speed of shipping cost calculations',
                'time' => isset($result['total_time']) ? self::format_time_for_display($result['total_time'] * 1000) : '0.00s',
                'operations' => '1250 calculations × 15 iterations'
            );
        }
        
        if (isset($perf_results['inventory_operations'])) {
            $result = $perf_results['inventory_operations'];
            $sub_tests[] = array(
                'name' => 'Inventory Operations',
                'description' => 'Stock level checking speed',
                'time' => isset($result['total_time']) ? self::format_time_for_display($result['total_time'] * 1000) : '0.00s',
                'operations' => '1500 checks × 15 iterations'
            );
        }
        
        return $sub_tests;
    }

    /**
     * Get enhanced performance interpretations for each sub-test
     */
    private static function get_enhanced_performance_sub_tests($perf_results) {
        $enhanced_sub_tests = array();
        
        if (isset($perf_results['price_calculations'])) {
            $result = $perf_results['price_calculations'];
            $ops_iter = isset($result['operations_per_iteration']) ? intval($result['operations_per_iteration']) : 2500;
            $iters = isset($result['iterations']) ? intval($result['iterations']) : 15;
            $ops_label = $ops_iter . ' calculations × ' . $iters . ' iterations';
            $enhanced_sub_tests[] = array(
                'name' => 'Price Calculations',
                'description' => 'How fast product prices update',
                'time' => isset($result['total_time']) ? self::format_time_for_display($result['total_time'] * 1000) : '0.00s',
                'operations' => $ops_label,
                'performance_interpretation' => self::get_performance_interpretation_safe('price_calculations', $result)
            );
        }

        if (isset($perf_results['shipping_calculations'])) {
            $result = $perf_results['shipping_calculations'];
            $ops_iter = isset($result['operations_per_iteration']) ? intval($result['operations_per_iteration']) : 1250;
            $iters = isset($result['iterations']) ? intval($result['iterations']) : 15;
            $ops_label = $ops_iter . ' calculations × ' . $iters . ' iterations';
            $enhanced_sub_tests[] = array(
                'name' => 'Shipping Calculations',
                'description' => 'Speed of shipping cost calculations',
                'time' => isset($result['total_time']) ? self::format_time_for_display($result['total_time'] * 1000) : '0.00s',
                'operations' => $ops_label,
                'performance_interpretation' => self::get_performance_interpretation_safe('shipping_calculations', $result)
            );
        }

        if (isset($perf_results['inventory_operations'])) {
            $result = $perf_results['inventory_operations'];
            $ops_iter = isset($result['operations_per_iteration']) ? intval($result['operations_per_iteration']) : 1500;
            $iters = isset($result['iterations']) ? intval($result['iterations']) : 15;
            $ops_label = $ops_iter . ' checks × ' . $iters . ' iterations';
            $enhanced_sub_tests[] = array(
                'name' => 'Inventory Operations',
                'description' => 'Stock level checking speed',
                'time' => isset($result['total_time']) ? self::format_time_for_display($result['total_time'] * 1000) : '0.00s',
                'operations' => $ops_label,
                'performance_interpretation' => self::get_performance_interpretation_safe('inventory_operations', $result)
            );
        }
        
        return $enhanced_sub_tests;
    }

    /**
     * Get database sub-tests formatted for template
     */
    private static function get_database_sub_tests($db_results) {
        $sub_tests = array();
        
        // Handle individual database test results (not grouped)
        if (isset($db_results['insert_operations'])) {
            $result = $db_results['insert_operations'];
            $sub_tests[] = array(
                'name' => 'INSERT Operations',
                'description' => 'Data creation and insertion speed',
                'time' => isset($result['total_time']) ? self::format_time_for_display($result['total_time'] * 1000) : '0.00s',
                'operations' => isset($result['total_records_inserted']) ? $result['total_records_inserted'] . ' records inserted' : 'Database insertions'
            );
        }
        
        if (isset($db_results['select_operations'])) {
            $result = $db_results['select_operations'];
            $sub_tests[] = array(
                'name' => 'SELECT Operations',
                'description' => 'Data retrieval and query speed',
                'time' => isset($result['total_time']) ? self::format_time_for_display($result['total_time'] * 1000) : '0.00s',
                'operations' => isset($result['queries_executed']) ? $result['queries_executed'] . ' queries executed' : 'Database queries'
            );
        }
        
        if (isset($db_results['update_operations'])) {
            $result = $db_results['update_operations'];
            $sub_tests[] = array(
                'name' => 'UPDATE Operations',
                'description' => 'Data modification speed',
                'time' => isset($result['total_time']) ? self::format_time_for_display($result['total_time'] * 1000) : '0.00s',
                'operations' => isset($result['updates_executed']) ? $result['updates_executed'] . ' updates executed' : 'Database updates'
            );
        }
        
        if (isset($db_results['crypto_functions'])) {
            $result = $db_results['crypto_functions'];
            $sub_tests[] = array(
                'name' => 'Crypto Functions',
                'description' => 'Encryption and hash operations',
                'time' => isset($result['total_time']) ? self::format_time_for_display($result['total_time'] * 1000) : '0.00s',
                'operations' => isset($result['operations_executed']) ? $result['operations_executed'] . ' crypto operations' : 'Crypto functions'
            );
        }
        
        if (isset($db_results['math_functions'])) {
            $result = $db_results['math_functions'];
            $sub_tests[] = array(
                'name' => 'Math Functions',
                'description' => 'Mathematical calculations',
                'time' => isset($result['total_time']) ? self::format_time_for_display($result['total_time'] * 1000) : '0.00s',
                'operations' => isset($result['operations_executed']) ? $result['operations_executed'] . ' math operations' : 'Math functions'
            );
        }
        
        if (isset($db_results['string_functions'])) {
            $result = $db_results['string_functions'];
            $sub_tests[] = array(
                'name' => 'String Functions',
                'description' => 'Text processing operations',
                'time' => isset($result['total_time']) ? self::format_time_for_display($result['total_time'] * 1000) : '0.00s',
                'operations' => isset($result['operations_executed']) ? $result['operations_executed'] . ' string operations' : 'String functions'
            );
        }
        
        if (isset($db_results['datetime_functions'])) {
            $result = $db_results['datetime_functions'];
            $sub_tests[] = array(
                'name' => 'DateTime Functions',
                'description' => 'Date and time operations',
                'time' => isset($result['total_time']) ? self::format_time_for_display($result['total_time'] * 1000) : '0.00s',
                'operations' => isset($result['operations_executed']) ? $result['operations_executed'] . ' datetime operations' : 'DateTime functions'
            );
        }
        
        if (isset($db_results['aggregate_functions'])) {
            $result = $db_results['aggregate_functions'];
            $sub_tests[] = array(
                'name' => 'Aggregate Functions',
                'description' => 'SUM, COUNT, AVG operations',
                'time' => isset($result['total_time']) ? self::format_time_for_display($result['total_time'] * 1000) : '0.00s',
                'operations' => isset($result['operations_executed']) ? $result['operations_executed'] . ' aggregate operations' : 'Aggregate functions'
            );
        }
        
        return $sub_tests;
    }

    /**
     * Get enhanced database interpretations for each sub-test
     */
    private static function get_enhanced_database_sub_tests($db_results) {
        $enhanced_sub_tests = array();

        if (isset($db_results['insert_operations'])) {
            $result = $db_results['insert_operations'];
            $records = isset($result['total_operations']) ? number_format($result['total_operations']) : '1000';
            $iterations = isset($result['iterations']) ? $result['iterations'] : 1;
            $enhanced_sub_tests[] = array(
                'name' => 'INSERT Operations',
                'description' => 'Data creation and insertion speed',
                'time' => isset($result['total_time']) ? self::format_time_for_display($result['total_time'] * 1000) : '0.00s',
                'operations' => $records . ' records × ' . $iterations . ' iteration' . ($iterations > 1 ? 's' : ''),
                'performance_interpretation' => self::get_performance_interpretation_safe('insert_operations', $result)
            );
        }

        if (isset($db_results['select_operations'])) {
            $result = $db_results['select_operations'];
            $queries = isset($result['total_operations']) ? number_format($result['total_operations']) : '1000';
            $iterations = isset($result['iterations']) ? $result['iterations'] : 1;
            $enhanced_sub_tests[] = array(
                'name' => 'SELECT Operations',
                'description' => 'Data retrieval and query speed',
                'time' => isset($result['total_time']) ? self::format_time_for_display($result['total_time'] * 1000) : '0.00s',
                'operations' => $queries . ' queries × ' . $iterations . ' iteration' . ($iterations > 1 ? 's' : ''),
                'performance_interpretation' => self::get_performance_interpretation_safe('select_operations', $result)
            );
        }
        
        if (isset($db_results['update_operations'])) {
            $result = $db_results['update_operations'];
            $updates = isset($result['total_operations']) ? number_format($result['total_operations']) : '1000';
            $iterations = isset($result['iterations']) ? $result['iterations'] : 1;
            $enhanced_sub_tests[] = array(
                'name' => 'UPDATE Operations',
                'description' => 'Data modification speed',
                'time' => isset($result['total_time']) ? self::format_time_for_display($result['total_time'] * 1000) : '0.00s',
                'operations' => $updates . ' updates × ' . $iterations . ' iteration' . ($iterations > 1 ? 's' : ''),
                'performance_interpretation' => self::get_performance_interpretation_safe('update_operations', $result)
            );
        }

        if (isset($db_results['crypto_functions'])) {
            $result = $db_results['crypto_functions'];
            $operations = isset($result['total_operations']) ? number_format($result['total_operations']) : '1000';
            $iterations = isset($result['iterations']) ? $result['iterations'] : 1;
            $enhanced_sub_tests[] = array(
                'name' => 'Crypto Functions',
                'description' => 'Encryption and hash operations',
                'time' => isset($result['total_time']) ? self::format_time_for_display($result['total_time'] * 1000) : '0.00s',
                'operations' => $operations . ' crypto operations × ' . $iterations . ' iteration' . ($iterations > 1 ? 's' : ''),
                'performance_interpretation' => self::get_performance_interpretation_safe('crypto_functions', $result)
            );
        }
        
        if (isset($db_results['math_functions'])) {
            $result = $db_results['math_functions'];
            $operations = isset($result['total_operations']) ? number_format($result['total_operations']) : '5000';
            $iterations = isset($result['iterations']) ? $result['iterations'] : 1;
            $enhanced_sub_tests[] = array(
                'name' => 'Math Functions',
                'description' => 'Mathematical calculations',
                'time' => isset($result['total_time']) ? self::format_time_for_display($result['total_time'] * 1000) : '0.00s',
                'operations' => $operations . ' math operations × ' . $iterations . ' iteration' . ($iterations > 1 ? 's' : ''),
                'performance_interpretation' => self::get_performance_interpretation_safe('math_functions', $result)
            );
        }

        if (isset($db_results['string_functions'])) {
            $result = $db_results['string_functions'];
            $operations = isset($result['total_operations']) ? number_format($result['total_operations']) : '10000';
            $iterations = isset($result['iterations']) ? $result['iterations'] : 1;
            $enhanced_sub_tests[] = array(
                'name' => 'String Functions',
                'description' => 'Text processing operations',
                'time' => isset($result['total_time']) ? self::format_time_for_display($result['total_time'] * 1000) : '0.00s',
                'operations' => $operations . ' string operations × ' . $iterations . ' iteration' . ($iterations > 1 ? 's' : ''),
                'performance_interpretation' => self::get_performance_interpretation_safe('string_functions', $result)
            );
        }
        
        if (isset($db_results['datetime_functions'])) {
            $result = $db_results['datetime_functions'];
            $operations = isset($result['total_operations']) ? number_format($result['total_operations']) : '5000';
            $iterations = isset($result['iterations']) ? $result['iterations'] : 1;
            $enhanced_sub_tests[] = array(
                'name' => 'DateTime Functions',
                'description' => 'Date and time operations',
                'time' => isset($result['total_time']) ? self::format_time_for_display($result['total_time'] * 1000) : '0.00s',
                'operations' => $operations . ' datetime operations × ' . $iterations . ' iteration' . ($iterations > 1 ? 's' : ''),
                'performance_interpretation' => self::get_performance_interpretation_safe('datetime_functions', $result)
            );
        }

        if (isset($db_results['aggregate_functions'])) {
            $result = $db_results['aggregate_functions'];
            $operations = isset($result['total_operations']) ? number_format($result['total_operations']) : '100';
            $iterations = isset($result['iterations']) ? $result['iterations'] : 1;
            $enhanced_sub_tests[] = array(
                'name' => 'Aggregate Functions',
                'description' => 'SUM, COUNT, AVG operations',
                'time' => isset($result['total_time']) ? self::format_time_for_display($result['total_time'] * 1000) : '0.00s',
                'operations' => $operations . ' aggregate operations × ' . $iterations . ' iteration' . ($iterations > 1 ? 's' : ''),
                'performance_interpretation' => self::get_performance_interpretation_safe('aggregate_functions', $result)
            );
        }
        
        return $enhanced_sub_tests;
    }

    /**
     * Get performance interpretation safely, falling back to scoring classes if not set
     */
    private static function get_performance_interpretation_safe($test_name, $result) {
        // If already set, use it
        if (isset($result['performance_interpretation'])) {
            return $result['performance_interpretation'];
        }

        // Otherwise, generate it using the appropriate scoring class
        $category = self::get_category_from_test_name($test_name);
        if (!$category) {
            return null;
        }

        $scoring_class = self::get_scoring_class($category);
        if ($scoring_class && method_exists($scoring_class, 'get_sub_test_performance_interpretation')) {
            return $scoring_class::get_sub_test_performance_interpretation($test_name, $result);
        }

        return null;
    }

    /**
     * Get category from test name
     */
    private static function get_category_from_test_name($test_name) {
        $category_map = array(
            // Performance tests
            'price_calculations' => 'performance',
            'shipping_calculations' => 'performance',
            'inventory_operations' => 'performance',

            // Database tests
            'insert_operations' => 'database',
            'select_operations' => 'database',
            'update_operations' => 'database',
            'crypto_functions' => 'database',
            'math_functions' => 'database',
            'string_functions' => 'database',
            'datetime_functions' => 'database',
            'aggregate_functions' => 'database',

            // Resources tests
            'cpu_tests' => 'resources',
            'memory_tests' => 'resources',
            'file_io_tests' => 'resources',
            'network_tests' => 'resources',
            'wordpress_tests' => 'resources',

            // Concurrency tests
            'database_concurrency' => 'concurrency',
            'http_concurrency' => 'concurrency',
            'memory_concurrency' => 'concurrency',
            'file_concurrency' => 'concurrency'
        );

        return isset($category_map[$test_name]) ? $category_map[$test_name] : null;
    }

    /**
     * Get resources sub-tests formatted for template
     */
    private static function get_resources_sub_tests($res_results) {
        $sub_tests = array();
        
        foreach ($res_results as $test_name => $result) {
            $formatted_name = ucwords(str_replace('_', ' ', $test_name));
            $description = self::get_resource_test_description($test_name);
            
            $sub_tests[] = array(
                'name' => $formatted_name,
                'description' => $description,
                'time' => isset($result['total_time']) ? self::format_time_for_display($result['total_time'] * 1000) : '0.00s',
                'operations' => self::get_resource_operations_info($test_name, $result)
            );
        }
        
        return $sub_tests;
    }

    /**
     * Analyze concurrency test statuses for enhanced UX display
     */
    private static function analyze_concurrency_status($conc_results) {
        $status_analysis = array(
            'status_indicator' => array('type' => 'completed', 'icon' => '✅', 'label' => 'COMPLETED'),
            'issue_explanation' => '',
            'completion_percentage' => 100
        );
        
        $total_tests = count($conc_results);
        $completed_tests = 0;
        $timeout_tests = array();
        $error_tests = array();
        $partial_tests = array();
        
        foreach ($conc_results as $test_name => $result) {
            $test_status = $result['test_status'] ?? 'completed';
            
            switch ($test_status) {
                case 'completed':
                    $completed_tests++;
                    break;
                case 'timeout':
                    $timeout_tests[] = array(
                        'name' => ucwords(str_replace('_', ' ', $test_name)),
                        'reason' => $result['timeout_reason'] ?? 'Test timed out'
                    );
                    break;
                case 'partial':
                    $partial_tests[] = array(
                        'name' => ucwords(str_replace('_', ' ', $test_name)),
                        'completed' => $result['completed_operations'] ?? 0,
                        'total' => $result['total_operations'] ?? 1,
                        'reason' => $result['timeout_reason'] ?? 'Partial completion'
                    );
                    break;
                case 'error':
                    $error_tests[] = array(
                        'name' => ucwords(str_replace('_', ' ', $test_name)),
                        'reason' => $result['timeout_reason'] ?? 'Test failed'
                    );
                    break;
            }
        }
        
        // Determine overall status and explanation
        if (!empty($error_tests)) {
            $status_analysis['status_indicator'] = array('type' => 'error', 'icon' => '❌', 'label' => 'ERROR');
            $status_analysis['issue_explanation'] = sprintf(
                'Test errors detected: %s',
                implode(', ', array_column($error_tests, 'name'))
            );
        } elseif (!empty($timeout_tests)) {
            $status_analysis['status_indicator'] = array('type' => 'timeout', 'icon' => '⏱️', 'label' => 'TIMED OUT');
            $status_analysis['issue_explanation'] = sprintf(
                'Tests timed out: %s. This indicates hosting resource limitations that will affect real-world performance.',
                implode(', ', array_column($timeout_tests, 'name'))
            );
        } elseif (!empty($partial_tests)) {
            // Mark partial only if at least one test shows significant failure ratio; otherwise treat as completed
            $significant = array();
            foreach ($partial_tests as $partial) {
                $significant[] = sprintf('%s (%d of %d)', $partial['name'], $partial['completed'], $partial['total']);
            }
            if (!empty($significant)) {
                $status_analysis['status_indicator'] = array('type' => 'partial', 'icon' => '⚠️', 'label' => 'PARTIAL');
                $status_analysis['issue_explanation'] = 'Partial completion: ' . implode(', ', $significant);
            }
        }
        
        // Calculate completion percentage
        if ($total_tests > 0) {
            $status_analysis['completion_percentage'] = round(($completed_tests / $total_tests) * 100, 1);
        }
        
        return $status_analysis;
    }

    /**
     * Get concurrency sub-tests formatted for template with enhanced status display
     */
    private static function get_concurrency_sub_tests($conc_results) {
        $sub_tests = array();
        
        foreach ($conc_results as $test_name => $result) {
            $formatted_name = ucwords(str_replace('_', ' ', $test_name));
            $description = self::get_concurrency_test_description($test_name);
            $test_status = $result['test_status'] ?? 'completed';
            
            // Create status badge for individual tests
            $status_badge = null;
            if ($test_status === 'timeout') {
                $status_badge = array('type' => 'timeout', 'icon' => '⏱️', 'label' => 'TIMED OUT');
            } elseif ($test_status === 'error') {
                $status_badge = array('type' => 'error', 'icon' => '❌', 'label' => 'ERROR');
            } elseif ($test_status === 'partial') {
                // Do not show a PARTIAL pill; main rating pill already reflects severity
            }
            
            $sub_test_data = array(
                'name' => $formatted_name,
                'description' => $description,
                'time' => isset($result['total_time']) ? self::format_time_for_display($result['total_time'] * 1000) : '0.00s',
                'operations' => self::get_concurrency_operations_info($test_name, $result)
            );
            
            // Add status badge if needed (only for non-completed tests)
            if ($status_badge !== null) {
                $sub_test_data['status_badge'] = $status_badge;
            }
            
            $sub_tests[] = $sub_test_data;
        }
        
        return $sub_tests;
    }

    /**
     * Get enhanced performance interpretations for each sub-test
     */
    private static function get_enhanced_concurrency_sub_tests($conc_results) {
        $enhanced_sub_tests = array();
        
        foreach ($conc_results as $test_name => $result) {
            $formatted_name = ucwords(str_replace('_', ' ', $test_name));
            $description = self::get_concurrency_test_description($test_name);
            $test_status = $result['test_status'] ?? 'completed';
            
            // Create status badge for individual tests
            $status_badge = null;
            if ($test_status === 'timeout') {
                $status_badge = array('type' => 'timeout', 'icon' => '⏱️', 'label' => 'TIMED OUT');
            } elseif ($test_status === 'error') {
                $status_badge = array('type' => 'error', 'icon' => '❌', 'label' => 'ERROR');
            } elseif ($test_status === 'partial') {
                // Suppress PARTIAL pill in enhanced view; rating/interpretation explains
            }
            
            $sub_test_data = array(
                'name' => $formatted_name,
                'description' => $description,
                'time' => isset($result['total_time']) ? self::format_time_for_display($result['total_time'] * 1000) : '0.00s',
                'operations' => self::get_concurrency_operations_info($test_name, $result),
                'status_badge' => $status_badge
            );
            
            // Add performance interpretation if available
            $sub_test_data['performance_interpretation'] = self::get_performance_interpretation_safe($test_name, $result);
            
            $enhanced_sub_tests[] = $sub_test_data;
        }
        
        return $enhanced_sub_tests;
    }

    /**
     * Get overall hosting quality assessment
     */
    private static function get_concurrency_hosting_assessment($score, $score_impact) {
        $assessment = array(
            'overall_rating' => self::get_rating_from_score($score),
            'score' => $score,
            'interpretation' => '',
            'recommendations' => array()
        );

        if ($score >= 90) {
            $assessment['interpretation'] = 'Your hosting infrastructure is highly optimized for concurrent operations, providing excellent performance and reliability.';
            $assessment['recommendations'][] = 'Continue maintaining your current hosting setup and monitoring for any anomalies.';
        } elseif ($score >= 75) {
            $assessment['interpretation'] = 'Your hosting infrastructure performs well for concurrent operations, but there is room for optimization.';
            $assessment['recommendations'][] = 'Consider implementing caching strategies for frequently accessed data.';
            $assessment['recommendations'][] = 'Optimize database queries and reduce query complexity.';
        } elseif ($score >= 60) {
            $assessment['interpretation'] = 'Your hosting infrastructure may struggle with concurrent operations, particularly under heavy load.';
            $assessment['recommendations'][] = 'Implement a content delivery network (CDN) for static assets.';
            $assessment['recommendations'][] = 'Optimize database queries and reduce query complexity.';
            $assessment['recommendations'][] = 'Consider upgrading your hosting plan to a more robust option.';
        } elseif ($score >= 40) {
            $assessment['interpretation'] = 'Your hosting infrastructure is not well-suited for concurrent operations, which will significantly impact performance.';
            $assessment['recommendations'][] = 'Upgrade your hosting plan to a dedicated server or managed hosting solution.';
            $assessment['recommendations'][] = 'Implement a robust caching strategy.';
            $assessment['recommendations'][] = 'Optimize database queries and reduce query complexity.';
        } else {
            $assessment['interpretation'] = 'Your hosting infrastructure is severely inadequate for concurrent operations, resulting in poor performance and potential service disruptions.';
            $assessment['recommendations'][] = 'Immediate action is required. Upgrade your hosting plan to a dedicated server or managed hosting solution.';
            $assessment['recommendations'][] = 'Implement a robust caching strategy.';
            $assessment['recommendations'][] = 'Optimize database queries and reduce query complexity.';
        }

        return $assessment;
    }

    /**
     * Get rating from numeric score
     *
     * @param float $score Numeric score (0-100)
     * @return string Rating label
     */
    private static function get_rating_from_score($score) {
        if ($score >= 90) {
            return 'excellent';
        } elseif ($score >= 70) {
            return 'good';
        } elseif ($score >= 50) {
            return 'fair';
        } elseif ($score >= 30) {
            return 'poor';
        } else {
            return 'critical';
        }
    }

    /**
     * Get description for resource test
     */
    private static function get_resource_test_description($test_name) {
        $descriptions = array(
            'cpu_tests' => 'CPU-intensive prime number calculations',
            'wordpress_tests' => 'WordPress function tests',
            'memory_tests' => 'Memory allocation capability',
            'file_io_tests' => 'File write/read operations',
            'network_tests' => 'HTTP request performance'
        );
        
        return $descriptions[$test_name] ?? 'Resource test';
    }

    /**
     * Get operations info for resource test
     */
    private static function get_resource_operations_info($test_name, $result) {
        switch ($test_name) {
            case 'cpu_tests':
                return '1,000 calculations × 8 iterations';
            case 'wordpress_tests':
                return '100 operations × 8 iterations';
            case 'memory_tests':
                return '10MB allocation test';
            case 'file_io_tests':
                return '10 write/read operations';
            case 'network_tests':
                return '20 HTTP requests';
            default:
                return 'Test operations';
        }
    }

    /**
     * Get description for concurrency test
     */
    private static function get_concurrency_test_description($test_name) {
        $descriptions = array(
            'database_concurrency' => 'Multiple database operations simultaneously',
            'http_concurrency' => 'Multiple HTTP requests simultaneously',
            'memory_concurrency' => 'Memory competition under load',
            'file_concurrency' => 'File system operations under load'
        );
        
        return $descriptions[$test_name] ?? 'Concurrency test';
    }

    /**
     * Get operations info for concurrency test with status awareness
     */
    private static function get_concurrency_operations_info($test_name, $result) {
        // Prefer clear runtime summary: "N operations (success X%)"
        $completed = $result['completed_operations'] ?? null;
        if ($completed !== null) {
            switch ($test_name) {
                case 'database_concurrency':
                    return sprintf('%d database operations', intval($result['operations_completed']));
                case 'http_concurrency':
                    return sprintf('%d HTTP requests', intval($result['requests_completed']));
                case 'memory_concurrency':
                    return sprintf('%d memory processes', intval($result['processes_completed']));
                case 'file_concurrency':
                    return sprintf('%d file operations', intval($result['operations_completed']));
            }
        }
        // Fallbacks
        if (isset($result['operations'])) {
            return $result['operations'] . ' operations';
        }
        switch ($test_name) {
            case 'database_concurrency':
                return 'Database operations';
            case 'http_concurrency':
                return 'HTTP requests';
            case 'memory_concurrency':
                return 'Memory processes';
            case 'file_concurrency':
                return 'File operations';
            default:
                return 'Concurrent operations';
        }
    }

    /**
     * Format time from milliseconds to user-friendly seconds format (copied from PoC)
     */
    private static function format_time_for_display($time_ms) {
        if ($time_ms <= 0) {
            return '0.00s';
        }
        
        $seconds = $time_ms / 1000;
        
        if ($seconds < 0.01) {
            return number_format($seconds, 3) . 's';
        } elseif ($seconds < 1) {
            return number_format($seconds, 2) . 's';
        } else {
            return number_format($seconds, 1) . 's';
        }
    }

    /**
     * Handle get saved benchmarks request
     */
    public static function handle_get_saved_benchmarks() {
        // Verify nonce - nonces are validated by wp_verify_nonce() which handles security checks
        // wp_unslash() is safe here as nonces are cryptographically signed and validated
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $nonce = isset($_POST['nonce']) ? wp_unslash($_POST['nonce']) : '';
        if (!wp_verify_nonce($nonce, 'divewp_benchmark_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed', 'divewp-boost-site-performance')));
        }

        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'divewp-boost-site-performance')));
        }

        // Get saved benchmarks using existing database methods
        require_once DIVEWP_PLUGIN_DIR . 'includes/class-divewp-db-access.php';
        $db_access = DiveWP_DB_Access::get_instance();
        
        // Sanitize limit parameter - must be a positive integer between 1-50
        $limit = isset($_POST['limit']) ? absint(wp_unslash($_POST['limit'])) : 10;
        $limit = max(1, min(50, $limit)); // Enforce bounds
        if ($limit <= 0) {
            $limit = 10;
        } elseif ($limit > 50) {
            $limit = 50; // clamp to a reasonable maximum
        }
        $user_id = get_current_user_id();
        
        $saved_benchmarks = $db_access->get_recent_benchmark_results($limit, $user_id);
        
        // Format the results for display
        $formatted_results = array();
        foreach ($saved_benchmarks as $benchmark) {
            $formatted_results[] = array(
                'id' => intval($benchmark->id),
                'test_date' => $benchmark->test_date,
                'overall_score' => floatval($benchmark->overall_score),
                'performance_score' => floatval($benchmark->performance_score),
                'database_score' => floatval($benchmark->database_score),
                'resources_score' => floatval($benchmark->resources_score),
                'concurrency_score' => floatval($benchmark->concurrency_score),
                'session_id' => $benchmark->session_id
            );
        }
        
        wp_send_json_success(array(
            'benchmarks' => $formatted_results,
            'count' => count($formatted_results)
        ));
    }

    /**
     * Handle load saved benchmark request
     */
    public static function handle_load_saved_benchmark() {
        // Verify nonce - nonces are validated by wp_verify_nonce() which handles security checks
        // wp_unslash() is safe here as nonces are cryptographically signed and validated
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $nonce = isset($_POST['nonce']) ? wp_unslash($_POST['nonce']) : '';
        if (!wp_verify_nonce($nonce, 'divewp_benchmark_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed', 'divewp-boost-site-performance')));
        }

        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'divewp-boost-site-performance')));
        }

        // Sanitize benchmark_id parameter - must be a positive integer
        $benchmark_id = isset($_POST['benchmark_id']) ? absint(wp_unslash($_POST['benchmark_id'])) : 0;
        if (!$benchmark_id) {
            wp_send_json_error(array('message' => __('Invalid benchmark ID', 'divewp-boost-site-performance')));
        }

        // Get the specific benchmark result
        require_once DIVEWP_PLUGIN_DIR . 'includes/class-divewp-db-access.php';
        $db_access = DiveWP_DB_Access::get_instance();
        
        $benchmark = $db_access->get_benchmark_result($benchmark_id);
        if (!$benchmark) {
            wp_send_json_error(array('message' => __('Benchmark not found', 'divewp-boost-site-performance')));
        }

        // Parse the stored full_results JSON
        $all_results = json_decode($benchmark->full_results, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error(array('message' => __('Invalid benchmark data', 'divewp-boost-site-performance')));
        }

        // Convert numeric fields to proper numbers
        $overall_score = floatval($benchmark->overall_score);
        $performance_score = floatval($benchmark->performance_score);
        $database_score = floatval($benchmark->database_score);
        $resources_score = floatval($benchmark->resources_score);
        $concurrency_score = floatval($benchmark->concurrency_score);

        // Recreate category scores from stored data
        $category_scores = array();
        
        // For each category in the saved results, recalculate with sub_scores
        $categories = array('performance', 'database', 'resources', 'concurrency');
        
        // Load scoring classes
        require_once DIVEWP_PLUGIN_DIR . 'includes/features/hosting/hosting-benchmark/tests/performance/scoring.php';
        require_once DIVEWP_PLUGIN_DIR . 'includes/features/hosting/hosting-benchmark/tests/database/scoring.php';
        require_once DIVEWP_PLUGIN_DIR . 'includes/features/hosting/hosting-benchmark/tests/resources/scoring.php';
        require_once DIVEWP_PLUGIN_DIR . 'includes/features/hosting/hosting-benchmark/tests/concurrency/scoring.php';
        
        foreach ($categories as $category) {
            if (isset($all_results[$category])) {
                $category_results = $all_results[$category];
                
                // Get scoring class and recalculate with sub_scores
                $scoring_class = self::get_scoring_class($category);
                
                if (class_exists($scoring_class) && method_exists($scoring_class, 'calculate_category_score')) {
                    $category_score = $scoring_class::calculate_category_score($category_results);
                    $category_scores[$category] = $category_score;
                } else {
                    // Fallback to basic score from database
                    $score_field = $category . '_score';
                    $category_scores[$category] = array(
                        'score' => isset($benchmark->$score_field) ? floatval($benchmark->$score_field) : 0,
                        'rating' => self::get_rating_from_score(isset($benchmark->$score_field) ? floatval($benchmark->$score_field) : 0)
                    );
                }
            }
        }

        // Transform results for template display (reuse existing method)
        $template_data = self::transform_results_for_template($all_results, $category_scores);
        
        wp_send_json_success(array(
            'overall_score' => $overall_score,
            'category_scores' => $category_scores,
            'all_results' => $all_results,
            'template_data' => $template_data,
            'test_date' => $benchmark->test_date,
            'is_saved_benchmark' => true
        ));
    }

    /**
     * Handle delete saved benchmark request
     */
    public static function handle_delete_saved_benchmark() {
        // Verify nonce - nonces are validated by wp_verify_nonce() which handles security checks
        // wp_unslash() is safe here as nonces are cryptographically signed and validated
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $nonce = isset($_POST['nonce']) ? wp_unslash($_POST['nonce']) : '';
        if (!wp_verify_nonce($nonce, 'divewp_benchmark_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed', 'divewp-boost-site-performance')));
        }

        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'divewp-boost-site-performance')));
        }

        // Sanitize benchmark_id parameter - must be a positive integer
        $benchmark_id = isset($_POST['benchmark_id']) ? absint(wp_unslash($_POST['benchmark_id'])) : 0;
        if (!$benchmark_id) {
            wp_send_json_error(array('message' => __('Invalid benchmark ID', 'divewp-boost-site-performance')));
        }

        // Delete the benchmark using the database access class
        require_once DIVEWP_PLUGIN_DIR . 'includes/class-divewp-db-access.php';
        $db_access = DiveWP_DB_Access::get_instance();
        
        $deleted = $db_access->delete_benchmark_result($benchmark_id);
        
        if ($deleted) {
            wp_send_json_success(array(
                'message' => __('Benchmark deleted successfully', 'divewp-boost-site-performance'),
                'benchmark_id' => $benchmark_id
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to delete benchmark', 'divewp-boost-site-performance')));
        }
    }

    /**
     * Handle delete all saved benchmarks request
     */
    public static function handle_delete_all_benchmarks() {
        // Verify nonce - nonces are validated by wp_verify_nonce() which handles security checks
        // wp_unslash() is safe here as nonces are cryptographically signed and validated
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $nonce = isset($_POST['nonce']) ? wp_unslash($_POST['nonce']) : '';
        if (!wp_verify_nonce($nonce, 'divewp_benchmark_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed', 'divewp-boost-site-performance')));
        }

        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'divewp-boost-site-performance')));
        }

        require_once DIVEWP_PLUGIN_DIR . 'includes/class-divewp-db-access.php';
        $db_access = DiveWP_DB_Access::get_instance();
        $deleted = $db_access->delete_all_benchmark_results();
        if ($deleted) {
            wp_send_json_success(array('message' => __('All benchmark results deleted successfully', 'divewp-boost-site-performance')));
        } else {
            wp_send_json_error(array('message' => __('Failed to delete all benchmark results', 'divewp-boost-site-performance')));
        }
    }

    /**
     * Handle debug information request
     */
    public static function handle_debug_info() {
        // Removed in production: debug information endpoint is disabled.
        wp_send_json_error(array('message' => __('Debug endpoint disabled', 'divewp-boost-site-performance')));
    }

    /**
     * Get enhanced resources interpretations for each sub-test
     */
    private static function get_enhanced_resources_sub_tests($res_results) {
        $enhanced_sub_tests = array();
        
        if (isset($res_results['cpu_tests'])) {
            $result = $res_results['cpu_tests'];
            
            // Get actual operations per second from test results
            $ops_per_sec = isset($result['operations_per_second']) ? $result['operations_per_second'] : 0;
            $completed_ops = isset($result['completed_operations']) ? $result['completed_operations'] : 0;
            $total_ops = isset($result['total_operations']) ? $result['total_operations'] : 0;
            
            // DEBUG: Log CPU UI data to verify fix (only in debug mode)
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // BENCHMARK REQUIREMENT - Debug logging helps verify performance interpretation values during development
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log('DiveWP CPU UI Data - Ops/sec: ' . $ops_per_sec . ', Completed: ' . $completed_ops . ', Total: ' . $total_ops);
            }
            
            // Format operations display WITH detailed failed sub-tests
            $operations_display = self::format_cpu_sub_test_failures($result);
            
            $enhanced_sub_tests[] = array(
                'name' => 'CPU Tests',
                'description' => 'Computational performance',
                'time' => isset($result['total_time']) ? self::format_time_for_display($result['total_time'] * 1000) : '0.00s',
                'operations' => $operations_display,
                'performance_interpretation' => self::get_performance_interpretation_safe('cpu_tests', $result)
            );
        }
        
        if (isset($res_results['memory_tests'])) {
            $result = $res_results['memory_tests'];
            
            // Get actual operations per second from test results
            $ops_per_sec = isset($result['operations_per_second']) ? $result['operations_per_second'] : 0;
            $completed_ops = isset($result['completed_operations']) ? $result['completed_operations'] : 0;
            $total_ops = isset($result['total_operations']) ? $result['total_operations'] : 0;
            
            // Format operations display WITH detailed failed sub-tests
            $operations_display = self::format_memory_sub_test_failures($result);
            
            $enhanced_sub_tests[] = array(
                'name' => 'Memory Tests',
                'description' => 'Memory allocation efficiency',
                'time' => isset($result['total_time']) ? self::format_time_for_display($result['total_time'] * 1000) : '0.00s',
                'operations' => $operations_display,
                'performance_interpretation' => self::get_performance_interpretation_safe('memory_tests', $result)
            );
        }
        
        if (isset($res_results['file_io_tests'])) {
            $result = $res_results['file_io_tests'];
            
            // Get actual operations per second from test results
            $ops_per_sec = isset($result['operations_per_second']) ? $result['operations_per_second'] : 0;
            $completed_ops = isset($result['completed_operations']) ? $result['completed_operations'] : 0;
            $total_ops = isset($result['total_operations']) ? $result['total_operations'] : 0;
            
            // Format operations display WITH detailed failed sub-tests
            $operations_display = self::format_file_io_sub_test_failures($result);
            
            $enhanced_sub_tests[] = array(
                'name' => 'File I/O Tests',
                'description' => 'File system performance',
                'time' => isset($result['total_time']) ? self::format_time_for_display($result['total_time'] * 1000) : '0.00s',
                'operations' => $operations_display,
                'performance_interpretation' => self::get_performance_interpretation_safe('file_io_tests', $result)
            );
        }
        
        if (isset($res_results['network_tests'])) {
            $result = $res_results['network_tests'];
            
            // Get actual operations per second from test results
            $ops_per_sec = isset($result['operations_per_second']) ? $result['operations_per_second'] : 0;
            $completed_ops = isset($result['completed_operations']) ? $result['completed_operations'] : 0;
            $total_ops = isset($result['total_operations']) ? $result['total_operations'] : 0;
            
            // Format operations display WITH detailed failed sub-tests
            $operations_display = self::format_network_sub_test_failures($result);
            
            $enhanced_sub_tests[] = array(
                'name' => 'Network Tests',
                'description' => 'Network connectivity speed',
                'time' => isset($result['total_time']) ? self::format_time_for_display($result['total_time'] * 1000) : '0.00s',
                'operations' => $operations_display,
                'performance_interpretation' => self::get_performance_interpretation_safe('network_tests', $result)
            );
        }
        
        if (isset($res_results['wordpress_tests'])) {
            $result = $res_results['wordpress_tests'];
            
            // Get actual operations per second from test results
            $ops_per_sec = isset($result['operations_per_second']) ? $result['operations_per_second'] : 0;
            $completed_ops = isset($result['completed_operations']) ? $result['completed_operations'] : 0;
            $total_ops = isset($result['total_operations']) ? $result['total_operations'] : 0;
            
            // Format operations display WITH detailed failed sub-tests
            $operations_display = self::format_wordpress_sub_test_failures($result);
            
            $enhanced_sub_tests[] = array(
                'name' => 'WordPress Tests',
                'description' => 'WordPress-specific operations',
                'time' => isset($result['total_time']) ? self::format_time_for_display($result['total_time'] * 1000) : '0.00s',
                'operations' => $operations_display,
                'performance_interpretation' => self::get_performance_interpretation_safe('wordpress_tests', $result)
            );
        }
        
        return $enhanced_sub_tests;
    }

    /**
     * Format CPU sub-test failures for detailed display
     * 
     * @param array $cpu_result CPU test result data
     * @return string Formatted operations display
     */
    private static function format_cpu_sub_test_failures($cpu_result) {
        // Define all expected CPU sub-tests in order
        $expected_sub_tests = array(
            'prime_generation' => 'Prime Generation',
            'mathematical_operations' => 'Math Operations', 
            'fibonacci_sequence' => 'Fibonacci Sequence',
            'conditional_logic' => 'Conditional Logic',
            'string_processing' => 'String Processing',
            'array_operations' => 'Array Operations'
        );
        
        $completed_ops = isset($cpu_result['completed_operations']) ? $cpu_result['completed_operations'] : 0;
        $total_ops = count($expected_sub_tests);
        $sub_test_results = isset($cpu_result['sub_test_results']) ? $cpu_result['sub_test_results'] : array();
        
        // Build failure details
        $failure_lines = array();
        
        foreach ($expected_sub_tests as $test_key => $test_name) {
            if (!isset($sub_test_results[$test_key])) {
                // Test was never started (skipped due to timeout)
                $failure_lines[] = "Sub-test {$test_name} - not started - overall timeout";
            } else {
                $sub_result = $sub_test_results[$test_key];
                $status = isset($sub_result['test_status']) ? $sub_result['test_status'] : 'unknown';
                
                if ($status !== 'completed') {
                    $reason = self::get_cpu_failure_reason($status, $sub_result);
                    $failure_lines[] = "Sub-test {$test_name} - failed - {$reason}";
                }
            }
        }
        
        // Format final display with proper HTML formatting
        if (empty($failure_lines)) {
            return "{$completed_ops}/{$total_ops} sub-tests completed";
        } else {
            $summary = "{$completed_ops}/{$total_ops} sub-tests completed";
            $formatted_failures = implode("<br>", $failure_lines);
            return $summary . "<br>" . $formatted_failures;
        }
    }
    
    /**
     * Get failure reason for CPU sub-test
     * 
     * @param string $status Test status
     * @param array $sub_result Sub-test result data
     * @return string Human-readable failure reason
     */
    private static function get_cpu_failure_reason($status, $sub_result) {
        switch ($status) {
            case 'timeout':
                return 'test timed out';
            case 'error':
                return 'execution error';
            default:
                return 'unknown issue';
        }
    }
    
    /**
     * Format Memory sub-test failures for detailed display
     * 
     * @param array $memory_result Memory test result data
     * @return string Formatted operations display
     */
    private static function format_memory_sub_test_failures($memory_result) {
        // Define all expected Memory sub-tests in order
        $expected_sub_tests = array(
            array('name' => '50% Memory Allocation', 'key' => 0),
            array('name' => '70% Memory Allocation', 'key' => 1),
            array('name' => '85% Memory Allocation', 'key' => 2),
            array('name' => '95% Memory Allocation', 'key' => 3),
            array('name' => 'Memory Fragmentation', 'key' => 4),
            array('name' => 'Rapid Memory Cycles', 'key' => 5)
        );
        
        $completed_ops = isset($memory_result['completed_operations']) ? $memory_result['completed_operations'] : 0;
        $total_ops = count($expected_sub_tests);
        $memory_results = isset($memory_result['memory_results']) ? $memory_result['memory_results'] : array();
        
        // Build failure details
        $failure_lines = array();
        
        foreach ($expected_sub_tests as $test_info) {
            $test_name = $test_info['name'];
            $test_key = $test_info['key'];
            
            if (!isset($memory_results[$test_key])) {
                // Test was never executed (skipped due to timeout or error)
                $failure_lines[] = "Sub-test {$test_name} - not started - overall timeout";
            } else {
                $sub_result = $memory_results[$test_key];
                $success = isset($sub_result['success']) ? $sub_result['success'] : false;
                
                if (!$success) {
                    $reason = self::get_memory_failure_reason($sub_result);
                    $failure_lines[] = "Sub-test {$test_name} - failed - {$reason}";
                }
            }
        }
        
        // Format final display with proper HTML formatting
        if (empty($failure_lines)) {
            return "{$completed_ops}/{$total_ops} sub-tests completed";
        } else {
            $summary = "{$completed_ops}/{$total_ops} sub-tests completed";
            $formatted_failures = implode("<br>", $failure_lines);
            return $summary . "<br>" . $formatted_failures;
        }
    }
    
    /**
     * Get failure reason for Memory sub-test
     * 
     * @param array $sub_result Sub-test result data
     * @return string Human-readable failure reason
     */
    private static function get_memory_failure_reason($sub_result) {
        if (isset($sub_result['error'])) {
            $error = $sub_result['error'];
            if (strpos($error, 'Insufficient memory') !== false) {
                return 'insufficient memory available';
            } elseif (strpos($error, 'Allocation failed') !== false) {
                return 'memory allocation failed';
            } elseif (strpos($error, 'Fragmentation test failed') !== false) {
                return 'fragmentation test error';
            } else {
                return 'execution error';
            }
        }
        
        if (isset($sub_result['hosting_behavior'])) {
            $behavior = $sub_result['hosting_behavior'];
            switch ($behavior) {
                case 'severely_limited':
                    return 'memory severely limited by hosting';
                case 'allocation_failure':
                    return 'hosting prevented allocation';
                case 'throttling_detected':
                    return 'memory throttling detected';
                default:
                    return 'unknown hosting limitation';
            }
        }
        
        return 'unknown issue';
    }
    
    /**
     * Format File I/O sub-test failures for detailed display
     * 
     * @param array $file_io_result File I/O test result data
     * @return string Formatted operations display
     */
    private static function format_file_io_sub_test_failures($file_io_result) {
        // Define all expected File I/O sub-tests in order
        $expected_sub_tests = array(
            'small_files' => array('name' => 'Small File Operations', 'ops' => 50),
            'medium_files' => array('name' => 'Medium File Operations', 'ops' => 25),
            'large_files' => array('name' => 'Large File Operations', 'ops' => 10),
            'backup_simulation' => array('name' => 'Backup Simulation', 'ops' => 15),
            'concurrent_ops' => array('name' => 'Concurrent I/O', 'ops' => 30),
            'cache_thrashing' => array('name' => 'Cache Thrashing', 'ops' => 20)
        );
        
        $completed_ops = isset($file_io_result['completed_operations']) ? $file_io_result['completed_operations'] : 0;
        $total_ops = 150; // Total expected operations
        $status = isset($file_io_result['status']) ? $file_io_result['status'] : 'unknown';
        
        // Check if all operations completed successfully
        if ($status === 'completed' && $completed_ops >= $total_ops) {
            return "{$completed_ops}/{$total_ops} file operations completed";
        }
        
        // If test failed, show breakdown of which operations were attempted
        $failure_lines = array();
        
        if ($status === 'error') {
            $failure_lines[] = "Sub-test Small File Operations - failed - I/O error during execution";
            $failure_lines[] = "Sub-test Medium File Operations - not started - previous test failed";
            $failure_lines[] = "Sub-test Large File Operations - not started - previous test failed";
            $failure_lines[] = "Sub-test Backup Simulation - not started - previous test failed";
            $failure_lines[] = "Sub-test Concurrent I/O - not started - previous test failed";
            $failure_lines[] = "Sub-test Cache Thrashing - not started - previous test failed";
        } else {
            // Partial completion - estimate which tests may have completed
            $remaining_ops = $total_ops - $completed_ops;
            if ($remaining_ops > 0) {
                if ($completed_ops < 50) {
                    $failure_lines[] = "Sub-test Small File Operations - failed - incomplete execution";
                } else if ($completed_ops < 75) {
                    $failure_lines[] = "Sub-test Medium File Operations - failed - incomplete execution";
                } else if ($completed_ops < 85) {
                    $failure_lines[] = "Sub-test Large File Operations - failed - incomplete execution";
                } else if ($completed_ops < 100) {
                    $failure_lines[] = "Sub-test Backup Simulation - failed - incomplete execution";
                } else if ($completed_ops < 130) {
                    $failure_lines[] = "Sub-test Concurrent I/O - failed - incomplete execution";
                } else {
                    $failure_lines[] = "Sub-test Cache Thrashing - failed - incomplete execution";
                }
            }
        }
        
        // Format final display with proper HTML formatting
        if (empty($failure_lines)) {
            return "{$completed_ops}/{$total_ops} file operations completed";
        } else {
            $summary = "{$completed_ops}/{$total_ops} file operations completed";
            $formatted_failures = implode("<br>", $failure_lines);
            return $summary . "<br>" . $formatted_failures;
        }
    }
    
    /**
     * Format Network sub-test failures for detailed display
     * 
     * @param array $network_result Network test result data
     * @return string Formatted operations display
     */
    private static function format_network_sub_test_failures($network_result) {
        // Network tests have 2 sub-tests: WordPress.org API and HTTP reliability
        $expected_sub_tests = array(
            'WordPress.org API Test',
            'HTTP Reliability Test'
        );
        
        $completed_ops = isset($network_result['completed_operations']) ? $network_result['completed_operations'] : 0;
        $total_ops = 2;
        $status = isset($network_result['status']) ? $network_result['status'] : 'unknown';
        
        // Check if all operations completed successfully
        if ($status === 'completed' && $completed_ops >= $total_ops) {
            return "{$completed_ops}/{$total_ops} network tests completed";
        }
        
        // If test failed, show which tests failed
        $failure_lines = array();
        
        if ($status === 'error' || $completed_ops < $total_ops) {
            if ($completed_ops === 0) {
                $failure_lines[] = "Sub-test WordPress.org API Test - failed - network connectivity error";
                $failure_lines[] = "Sub-test HTTP Reliability Test - not started - previous test failed";
            } elseif ($completed_ops === 1) {
                $failure_lines[] = "Sub-test HTTP Reliability Test - failed - network timeout or error";
            }
        }
        
        // Format final display with proper HTML formatting
        if (empty($failure_lines)) {
            return "{$completed_ops}/{$total_ops} network tests completed";
        } else {
            $summary = "{$completed_ops}/{$total_ops} network tests completed";
            $formatted_failures = implode("<br>", $failure_lines);
            return $summary . "<br>" . $formatted_failures;
        }
    }
    
    /**
     * Format WordPress sub-test failures for detailed display
     * 
     * @param array $wordpress_result WordPress test result data
     * @return string Formatted operations display
     */
    private static function format_wordpress_sub_test_failures($wordpress_result) {
        // Define all expected WordPress sub-tests in order
        $expected_sub_tests = array(
            'shortcode_processing' => 'Shortcode Processing',
            'hook_execution' => 'Hook Execution',
            'transient_operations' => 'Transient Operations',
            'security_functions' => 'Security Functions'
        );
        
        $completed_ops = isset($wordpress_result['completed_operations']) ? $wordpress_result['completed_operations'] : 0;
        $total_ops = count($expected_sub_tests);
        $wp_results = isset($wordpress_result['sub_test_results']) ? $wordpress_result['sub_test_results'] : array();
        
        // Build failure details
        $failure_lines = array();
        
        foreach ($expected_sub_tests as $test_key => $test_name) {
            if (!isset($wp_results[$test_key])) {
                // Test was never started (skipped due to timeout)
                $failure_lines[] = "Sub-test {$test_name} - not started - overall timeout";
            } else {
                $sub_result = $wp_results[$test_key];
                $status = isset($sub_result['test_status']) ? $sub_result['test_status'] : 'unknown';
                
                if ($status !== 'completed') {
                    $reason = self::get_wordpress_failure_reason($status, $sub_result);
                    $failure_lines[] = "Sub-test {$test_name} - failed - {$reason}";
                }
            }
        }
        
        // Format final display with proper HTML formatting
        if (empty($failure_lines)) {
            return "{$completed_ops}/{$total_ops} WP tests completed";
        } else {
            $summary = "{$completed_ops}/{$total_ops} WP tests completed";
            $formatted_failures = implode("<br>", $failure_lines);
            return $summary . "<br>" . $formatted_failures;
        }
    }
    
    /**
     * Get failure reason for WordPress sub-test
     * 
     * @param string $status Test status
     * @param array $sub_result Sub-test result data
     * @return string Human-readable failure reason
     */
    private static function get_wordpress_failure_reason($status, $sub_result) {
        switch ($status) {
            case 'timeout':
                return 'test timed out';
            case 'error':
                return 'execution error';
            case 'partial':
                return 'incomplete execution';
            default:
                return 'unknown issue';
        }
    }
    
    /**
     * Format Performance sub-test failures for detailed display
     * 
     * @param array $performance_results Performance category results data
     * @return string Formatted operations display
     */
    private static function format_performance_sub_test_failures($performance_results) {
        // Define all expected Performance sub-tests
        $expected_sub_tests = array(
            'price_calculations' => 'Price Calculations',
            'shipping_calculations' => 'Shipping Calculations',
            'inventory_operations' => 'Inventory Operations'
        );
        
        // Count completed vs total tests
        $completed_tests = 0;
        $total_tests = count($expected_sub_tests);
        $failure_lines = array();
        
        // Check each expected test
        foreach ($expected_sub_tests as $test_key => $test_name) {
            if (isset($performance_results[$test_key])) {
                $test_result = $performance_results[$test_key];
                $status = isset($test_result['status']) ? $test_result['status'] : 'completed';
                
                if ($status === 'completed' || $status === 'success') {
                    $completed_tests++;
                } else {
                    $reason = self::get_performance_failure_reason($status, $test_result);
                    $failure_lines[] = "Sub-test {$test_name} - failed - {$reason}";
                }
            } else {
                // Test result not present - assume it failed to run
                $failure_lines[] = "Sub-test {$test_name} - failed - test did not execute";
            }
        }
        
        // Format final display with proper HTML formatting
        if (empty($failure_lines)) {
            return "{$completed_tests}/{$total_tests} performance tests completed";
        } else {
            $summary = "{$completed_tests}/{$total_tests} performance tests completed";
            $formatted_failures = implode("<br>", $failure_lines);
            return $summary . "<br>" . $formatted_failures;
        }
    }
    
    /**
     * Get failure reason for Performance sub-test
     * 
     * @param string $status Test status
     * @param array $test_result Test result data
     * @return string Human-readable failure reason
     */
    private static function get_performance_failure_reason($status, $test_result) {
        switch ($status) {
            case 'error':
                return 'execution error';
            case 'timeout':
                return 'test timed out';
            case 'failed':
                return 'calculation failure';
            default:
                return 'unknown issue';
        }
    }
    
    /**
     * Format Database sub-test failures for detailed display
     * 
     * @param array $database_results Database category results data
     * @return string Formatted operations display
     */
    private static function format_database_sub_test_failures($database_results) {
        // Define all expected Database sub-tests
        $expected_sub_tests = array(
            'insert_operations' => 'INSERT Operations',
            'select_operations' => 'SELECT Operations', 
            'update_operations' => 'UPDATE Operations',
            'crypto_functions' => 'Crypto Functions',
            'math_functions' => 'Math Functions',
            'string_functions' => 'String Functions',
            'datetime_functions' => 'DateTime Functions',
            'aggregate_functions' => 'Aggregate Functions'
        );
        
        // Count completed vs total tests
        $completed_tests = 0;
        $total_tests = count($expected_sub_tests);
        $failure_lines = array();
        
        // Check each expected test
        foreach ($expected_sub_tests as $test_key => $test_name) {
            if (isset($database_results[$test_key])) {
                $test_result = $database_results[$test_key];
                $status = isset($test_result['status']) ? $test_result['status'] : 'completed';
                
                if ($status === 'completed' || $status === 'success') {
                    $completed_tests++;
                } else {
                    $reason = self::get_database_failure_reason($status, $test_result);
                    $failure_lines[] = "Sub-test {$test_name} - failed - {$reason}";
                }
            } else {
                // Test result not present - assume it failed to run
                $failure_lines[] = "Sub-test {$test_name} - failed - test did not execute";
            }
        }
        
        // Format final display with proper HTML formatting
        if (empty($failure_lines)) {
            return "{$completed_tests}/{$total_tests} database tests completed";
        } else {
            $summary = "{$completed_tests}/{$total_tests} database tests completed";
            $formatted_failures = implode("<br>", $failure_lines);
            return $summary . "<br>" . $formatted_failures;
        }
    }
    
    /**
     * Get failure reason for Database sub-test
     * 
     * @param string $status Test status
     * @param array $test_result Test result data
     * @return string Human-readable failure reason
     */
    private static function get_database_failure_reason($status, $test_result) {
        switch ($status) {
            case 'error':
                return 'database execution error';
            case 'timeout':
                return 'database query timed out';
            case 'failed':
                return 'database operation failed';
            default:
                return 'unknown database issue';
        }
    }
    
    /**
     * Format Concurrency sub-test failures for detailed display
     * 
     * @param array $concurrency_results Concurrency category results data
     * @return string Formatted operations display
     */
    private static function format_concurrency_sub_test_failures($concurrency_results) {
        // Define all expected Concurrency sub-tests
        $expected_sub_tests = array(
            'database_concurrency' => 'Database Concurrency',
            'http_concurrency' => 'HTTP Concurrency',
            'memory_concurrency' => 'Memory Concurrency', 
            'file_concurrency' => 'File Concurrency'
        );
        
        // Count completed vs total tests
        $completed_tests = 0;
        $total_tests = count($expected_sub_tests);
        $failure_lines = array();
        
        // Check each expected test
        foreach ($expected_sub_tests as $test_key => $test_name) {
            if (isset($concurrency_results[$test_key])) {
                $test_result = $concurrency_results[$test_key];
                $status = isset($test_result['test_status']) ? $test_result['test_status'] : 'completed';
                
                if ($status === 'completed') {
                    $completed_tests++;
                } else {
                    $reason = self::get_concurrency_failure_reason($status, $test_result);
                    $failure_lines[] = "Sub-test {$test_name} - failed - {$reason}";
                }
            } else {
                // Test result not present - assume it failed to run
                $failure_lines[] = "Sub-test {$test_name} - failed - test did not execute";
            }
        }
        
        // Format final display with proper HTML formatting
        if (empty($failure_lines)) {
            return "{$completed_tests}/{$total_tests} concurrency tests completed";
        } else {
            $summary = "{$completed_tests}/{$total_tests} concurrency tests completed";
            $formatted_failures = implode("<br>", $failure_lines);
            return $summary . "<br>" . $formatted_failures;
        }
    }
    
    /**
     * Get failure reason for Concurrency sub-test
     * 
     * @param string $status Test status
     * @param array $test_result Test result data
     * @return string Human-readable failure reason
     */
    private static function get_concurrency_failure_reason($status, $test_result) {
        switch ($status) {
            case 'timeout':
                return 'concurrency test timed out';
            case 'error':
                return 'concurrency execution error';
            case 'partial':
                return 'incomplete concurrency test';
            default:
                return 'unknown concurrency issue';
        }
    }
}

// Initialize AJAX handlers when this file is loaded
DiveWP_Benchmark_Ajax::init();