<?php
/**
 * Choose Hosting functionality for DiveWP
 *
 * @package     DiveWP
 * @subpackage  Features/ChooseHosting
 * @author      Oleg Petrov
 * @version     1.0.0
 * @license     GPL-2.0-or-later
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    die(esc_html__('Direct access not permitted.', 'divewp-boost-site-performance'));
}

class DiveWP_Choose_Hosting {

    /**
     * Content loader instance
     * @var DiveWP_Content_Loader
     */
    private $content_loader;

    /**
     * Test results cache
     * @var array
     */
    private $test_results = array();

    /**
     * Initialize the class
     */
    public function __construct() {
        // Include the resource tests class
        require_once(plugin_dir_path(__FILE__) . 'class-resource-tests.php');
        require_once(plugin_dir_path(__FILE__) . 'class-performance-tests.php');
        require_once(plugin_dir_path(__FILE__) . 'class-database-tests.php');
        require_once(plugin_dir_path(__FILE__) . 'class-concurrency-tests.php');
        
        // Instantiate the content loader
        $this->content_loader = new DiveWP_Content_Loader();
        
        // Add AJAX handlers for interactive testing
        add_action('wp_ajax_divewp_run_hosting_test', array($this, 'ajax_run_hosting_test'));
        add_action('wp_ajax_divewp_get_hosting_evaluation', array($this, 'ajax_get_hosting_evaluation'));
        add_action('wp_ajax_divewp_get_hosting_evaluation_cards', array($this, 'ajax_get_hosting_evaluation_cards'));
        add_action('wp_ajax_divewp_run_concurrency_step', array($this, 'ajax_run_concurrency_step'));
        
        // Enqueue scripts for interactive features
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Clean up user-specific transients on logout for security
        add_action('wp_logout', array($this, 'cleanup_user_transients'));
    }

    /**
     * Enqueue necessary scripts and styles
     */
    public function enqueue_scripts($hook) {
        if (strpos($hook, 'divewp') === false) {
            return;
        }

        // Enqueue the new results viewer JS first
        wp_enqueue_script(
            'divewp-results-viewer',
            DIVEWP_PLUGIN_URL . 'assets/js/divewp-results-viewer.js',
            array('jquery'),
            DIVEWP_VERSION,
            true
        );
        
        // Now, localize the script with the data object
        wp_localize_script('divewp-results-viewer', 'divewpHosting', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('divewp_hosting_nonce'),
            'plugin_url' => DIVEWP_PLUGIN_URL,
            'i18n' => array(
                'running' => esc_html__('Running tests...', 'divewp-boost-site-performance'),
                'complete' => esc_html__('Evaluation complete!', 'divewp-boost-site-performance'),
                'error' => esc_html__('An error occurred during testing.', 'divewp-boost-site-performance')
            )
        ));
        
        // Enqueue the hosting evaluation styles
        wp_enqueue_style(
            'divewp-hosting-evaluation',
            DIVEWP_PLUGIN_URL . 'assets/css/features/hosting-evaluation.css',
            array(),
            DIVEWP_VERSION
        );

        // Enqueue the new results viewer JS
        wp_enqueue_script(
            'divewp-results-viewer',
            DIVEWP_PLUGIN_URL . 'assets/js/divewp-results-viewer.js',
            array('jquery'),
            DIVEWP_VERSION,
            true
        );
    }

    /**
     * AJAX handler for running hosting tests
     */
    public function ajax_run_hosting_test() {
        // Verify nonce and capabilities
        if (!check_ajax_referer('divewp_hosting_nonce', 'nonce', false) || !current_user_can('manage_options')) {
            wp_die();
        }

        $test_type = isset($_POST['test_type']) ? sanitize_text_field($_POST['test_type']) : '';
        
        $result = array();
        
        switch ($test_type) {
            case 'performance':
                // Delegate to new performance tests class
                $performance_tests = new DiveWP_Performance_Tests();
                $test_config = $this->get_test_configuration();
                $result = $performance_tests->run_performance_tests($test_config);
                break;
            case 'resources':
                // Delegate to new resource tests class
                $resource_tests = new DiveWP_Resource_Tests();
                $test_config = $this->get_test_configuration();
                $result = $resource_tests->run_resource_tests($test_config);
                break;
            case 'database':
                // Delegate to new database tests class
                $database_tests = new DiveWP_Database_Tests();
                $result = $database_tests->run_database_tests();
                break;
            case 'concurrency':
                // Concurrency tests now use step-based approach via ajax_run_concurrency_step
                $result = array(
                    'message' => 'Concurrency tests now use step-based execution. Use the step-based AJAX endpoint instead.',
                    'use_step_based' => true
                );
                break;
            default:
                wp_send_json_error(array('message' => 'Invalid test type'));
                return;
        }

        wp_send_json_success($result);
    }

    /**
     * AJAX handler for running individual concurrency test steps
     */
    public function ajax_run_concurrency_step() {
        // Verify nonce and capabilities
        if (!check_ajax_referer('divewp_hosting_nonce', 'nonce', false) || !current_user_can('manage_options')) {
            wp_die();
        }

        $step = isset($_POST['step']) ? sanitize_text_field($_POST['step']) : '';
        $session_id = isset($_POST['session_id']) ? sanitize_text_field($_POST['session_id']) : '';
        
        if (empty($step)) {
            wp_send_json_error(array('message' => 'No test step specified'));
            return;
        }

        // Initialize concurrency tests class
        $concurrency_tests = new DiveWP_Concurrency_Tests();
        
        try {
            // Run the specific step
            $result = $concurrency_tests->handle_test_step($step, null, $session_id);
            
            if (isset($result['error'])) {
                wp_send_json_error($result);
            } else {
                wp_send_json_success($result);
            }
            
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => 'Concurrency step failed: ' . $e->getMessage(),
                'step' => $step
            ));
        }
    }



    /**
     * AJAX handler for getting complete hosting evaluation
     */
    public function ajax_get_hosting_evaluation() {
        // Verify nonce and capabilities
        if (!check_ajax_referer('divewp_hosting_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Invalid nonce'));
            return;
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
            return;
        }
        
        // Get test configuration from request
        $enabled_tests = isset($_POST['enabled_tests']) && is_array($_POST['enabled_tests']) ? array_map('sanitize_text_field', $_POST['enabled_tests']) : array();
        $skip_network_requests = isset($_POST['skip_network_requests']) ? (bool) $_POST['skip_network_requests'] : false;
        $test_config_mode = isset($_POST['test_configuration_mode']) ? sanitize_text_field($_POST['test_configuration_mode']) : 'default';
        
        // Store test configuration for use in test methods
        $user_id = get_current_user_id();
        if ($user_id) {
            set_transient('divewp_test_config_' . $user_id, array(
                'enabled_tests' => $enabled_tests,
                'skip_network_requests' => $skip_network_requests,
                'mode' => $test_config_mode
            ), 600); // 10 minutes
        }
        
        // Check if we should run a minimal test first
        if (isset($_POST['minimal_test']) && $_POST['minimal_test'] === 'true') {
            try {
                $minimal_result = $this->run_minimal_test();
                wp_send_json_success(array(
                    'minimal_test' => true,
                    'result' => $minimal_result,
                    'message' => 'Minimal test completed. Your hosting is extremely restrictive but functional.'
                ));
                return;
            } catch (Exception $e) {
                wp_send_json_error(array(
                    'message' => 'Your hosting environment is too restrictive to run any performance tests.',
                    'type' => 'extreme_restriction'
                ));
                return;
            }
        }
        
        // Check if we should run a specific test only (for debugging)
        if (isset($_POST['single_test'])) {
            $test_name = sanitize_text_field($_POST['single_test']);
            
            try {
                $result = array();
                
                switch ($test_name) {
                    case 'performance':
                        $result = $this->run_performance_tests();
                        break;
                    case 'resources':
                        $result = $this->run_resource_tests();
                        break;
                    case 'database':
                        $result = $this->run_database_tests();
                        break;
                                    case 'concurrency':
                    // Concurrency tests now use step-based approach
                    $result = array(
                        'message' => 'Concurrency tests now use step-based execution. Use the hosting evaluation interface instead.',
                        'use_step_based' => true
                    );
                    break;
                    default:
                        wp_send_json_error(array('message' => 'Invalid test name: ' . $test_name));
                        return;
                }
                
                wp_send_json_success(array(
                    'single_test' => true,
                    'test_name' => $test_name,
                    'result' => $result,
                    'message' => 'Single test "' . $test_name . '" completed.'
                ));
                return;
                
            } catch (Exception $e) {
                wp_send_json_error(array(
                    'message' => 'Test "' . $test_name . '" failed: ' . $e->getMessage(),
                    'type' => 'test_failure',
                    'test_name' => $test_name
                ));
                return;
            }
        }

        try {
            // Set a flag to indicate evaluation is running
            $user_id = get_current_user_id();
            set_transient('divewp_evaluation_running_' . $user_id, true, 300);
            
            // Run all tests
            $evaluation = $this->get_complete_evaluation();
            

            
            // Clear the running flag
            delete_transient('divewp_evaluation_running_' . get_current_user_id());
            
            // Ensure we have valid data
            if (empty($evaluation)) {
                wp_send_json_error(array('message' => 'Evaluation returned empty data'));
                return;
            }
            
            wp_send_json_success($evaluation);
        } catch (Exception $e) {
            // Clean up
            $this->cleanup_evaluation_state();
            
            // Check if this is a resource limit error
            if (strpos($e->getMessage(), 'memory') !== false || strpos($e->getMessage(), 'time') !== false) {
                wp_send_json_error(array(
                    'message' => 'Hosting resource limits detected. Try running individual tests instead.',
                    'type' => 'resource_limit'
                ));
            } else {
                wp_send_json_error(array('message' => 'Evaluation error: ' . $e->getMessage()));
            }
        }
    }
    
    /**
     * AJAX handler for getting hosting evaluation with new template rendering
     */
    public function ajax_get_hosting_evaluation_cards() {
        // Verify nonce and capabilities
        if (!check_ajax_referer('divewp_hosting_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Invalid nonce'));
            return;
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
            return;
        }
        
        // Get test configuration from request
        $enabled_tests = isset($_POST['enabled_tests']) && is_array($_POST['enabled_tests']) ? array_map('sanitize_text_field', $_POST['enabled_tests']) : array();
        $skip_network_requests = isset($_POST['skip_network_requests']) ? (bool) $_POST['skip_network_requests'] : false;
        $test_config_mode = isset($_POST['test_configuration_mode']) ? sanitize_text_field($_POST['test_configuration_mode']) : 'default';
        
        // Store test configuration for use in test methods
        $user_id = get_current_user_id();
        if ($user_id) {
            set_transient('divewp_test_config_' . $user_id, array(
                'enabled_tests' => $enabled_tests,
                'skip_network_requests' => $skip_network_requests,
                'mode' => $test_config_mode
            ), 600); // 10 minutes
        }
        
        try {
            // Set a flag to indicate evaluation is running
            $user_id = get_current_user_id();
            set_transient('divewp_evaluation_running_' . $user_id, true, 300);
            
            // Run all tests
            $evaluation = $this->get_complete_evaluation();
            
            // Clear the running flag
            delete_transient('divewp_evaluation_running_' . get_current_user_id());
            
            // Ensure we have valid data
            if (empty($evaluation)) {
                wp_send_json_error(array('message' => 'Evaluation returned empty data'));
                return;
            }
            
            // Render using new template system
            $cards_html = $this->render_evaluation_cards($evaluation);
            
            // Add overall score section
            $overall_html = $this->render_overall_score($evaluation);
            
            // Add recommendation section
            $recommendation_html = $this->render_recommendation_section($evaluation);
            
            wp_send_json_success(array(
                'overall_html' => $overall_html,
                'cards_html' => $cards_html,
                'recommendation_html' => $recommendation_html,
                'evaluation_data' => $evaluation // For debugging if needed
            ));
            
        } catch (Exception $e) {
            // Clean up
            $this->cleanup_evaluation_state();
            
            // Check if this is a resource limit error
            if (strpos($e->getMessage(), 'memory') !== false || strpos($e->getMessage(), 'time') !== false) {
                wp_send_json_error(array(
                    'message' => 'Hosting resource limits detected. Try running individual tests instead.',
                    'type' => 'resource_limit'
                ));
            } else {
                wp_send_json_error(array('message' => 'Evaluation error: ' . $e->getMessage()));
            }
        }
    }
    
    /**
     * Render overall score section
     */
    private function render_overall_score($evaluation) {
        $overall_score = $evaluation['overall_score'] ?? 0;
        $rating = $evaluation['rating'] ?? 'unknown';
        
        $rating_colors = array(
            'excellent' => '#10b981', // Green from Status Legend "Optimal"
            'good' => '#3b82f6',      // Blue from Status Legend "Info" 
            'fair' => '#f59e0b',      // Yellow from Status Legend "Warning"
            'poor' => '#ef4444',      // Orange/Red from Status Legend "Critical"
            'critical' => '#dc2626'   // Deep Red from Status Legend "Critical"
        );
        
        $rating_color = $rating_colors[$rating] ?? '#6b7280';
        
        return sprintf(
            '<div class="hosting-score-overview">
                <h4>Overall Hosting Score</h4>
                <div class="score-display">
                    <span class="score-number" style="color: %s;">%d</span>
                    <span class="score-rating-pill" style="background: %s;">%s</span>
                </div>
                <div class="score-bar">
                    <div class="score-bar-fill" style="background: %s; width: %d%%"></div>
                </div>
            </div>',
            esc_attr($rating_color),
            esc_html($overall_score),
            esc_attr($rating_color),
            esc_html(strtoupper($rating)),
            esc_attr($rating_color),
            esc_attr($overall_score)
        );
    }
    
    /**
     * Render recommendation section
     */
    private function render_recommendation_section($evaluation) {
        if (!isset($evaluation['recommendation'])) {
            return '';
        }
        
        $rec = $evaluation['recommendation'];
        $html = '<div class="hosting-recommendation">';
        
        $html .= '<h4>' . esc_html__('Recommendation & Analysis', 'divewp-boost-site-performance') . '</h4>';
        
        // Add general scoring explanation
        $html .= '<div class="scoring-explanation">';
        $html .= '<h5>' . esc_html__('How Scores Are Calculated', 'divewp-boost-site-performance') . '</h5>';
        $html .= '<p>' . esc_html__('Test scores are calculated based on speed, reliability, and consistency across multiple operations, with weighted averages reflecting real-world importance for WooCommerce sites. Performance and database tests focus on operation timing, while resource tests measure computational capability, memory efficiency, and I/O throughput. Concurrency scores evaluate how well your hosting handles multiple simultaneous operations, which directly impacts user experience during peak traffic periods.', 'divewp-boost-site-performance') . '</p>';
        $html .= '</div>';
        
        if (!empty($rec['verdict'])) {
            $html .= sprintf('<div class="verdict">%s</div>', esc_html($rec['verdict']));
        }
        
        if (!empty($rec['performance_profile']['title']) && !empty($rec['performance_profile']['description'])) {
            $html .= sprintf(
                '<div class="performance-profile">
                    <h5>%s</h5>
                    <p>%s</p>
                </div>',
                esc_html($rec['performance_profile']['title']),
                esc_html($rec['performance_profile']['description'])
            );
        }
        
        if (!empty($rec['strengths']) && is_array($rec['strengths'])) {
            $html .= '<h5 class="strengths-heading">' . esc_html__('Strengths', 'divewp-boost-site-performance') . '</h5>';
            $html .= '<ul class="strengths-list">';
            foreach ($rec['strengths'] as $strength) {
                $html .= '<li>' . esc_html($strength) . '</li>';
            }
            $html .= '</ul>';
        }
        
        if (!empty($rec['bottlenecks']) && is_array($rec['bottlenecks'])) {
            $html .= '<h5 class="bottlenecks-heading">' . esc_html__('Areas for Improvement', 'divewp-boost-site-performance') . '</h5>';
            $html .= '<ul class="bottlenecks-list">';
            foreach ($rec['bottlenecks'] as $bottleneck) {
                $html .= '<li>' . esc_html($bottleneck) . '</li>';
            }
            $html .= '</ul>';
        }
        
        if (!empty($rec['warnings']) && is_array($rec['warnings'])) {
            $html .= '<div class="warnings-section">';
            $html .= '<h5 class="warnings-heading">' . esc_html__('Important Warnings', 'divewp-boost-site-performance') . '</h5>';
            $html .= '<ul class="warnings-list">';
            foreach ($rec['warnings'] as $warning) {
                $html .= '<li>⚠️ ' . esc_html($warning) . '</li>';
            }
            $html .= '</ul>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    
    /**
     * Handle shutdown errors during evaluation
     * 
     * @since 1.0.6
     */
    public function handle_evaluation_shutdown() {
        $error = error_get_last();
        
        // Check if evaluation was running and we have a fatal error
        if ($error && in_array($error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR))) {
            $user_id = get_current_user_id();
            if (get_transient('divewp_evaluation_running_' . $user_id)) {
                // Try to send a response (may not work if output already started)
                if (!headers_sent()) {
                    $message = 'Hosting provider terminated the evaluation process. Your hosting has strict resource limits. Consider upgrading to a VPS or dedicated server for better performance.';
                    
                    wp_send_json_error(array(
                        'message' => $message,
                                            'type' => 'process_killed',
                    'hosting_config' => 'optimized'
                    ));
                }
            }
        }
    }
    
    /**
     * Clean up evaluation state and transients
     * 
     * @since 1.0.6
     */
    private function cleanup_evaluation_state() {
        $user_id = get_current_user_id();
        if ($user_id) {
            delete_transient('divewp_db_test_rate_limit_' . $user_id);
            delete_transient('divewp_hosting_evaluation_' . $user_id);
            delete_transient('divewp_test_config_' . $user_id); // Clean up test configuration
        }
    }

    /**
     * Run performance tests - WooCommerce-like operations
     * Delegated to separate performance tests class
     */
    private function run_performance_tests() {
        // Get test configuration
        $test_config = $this->get_test_configuration();
        
        // Instantiate the performance tests class and run tests
        $performance_tests = new DiveWP_Performance_Tests();
        return $performance_tests->run_performance_tests($test_config);
    }

    /**
     * Run resource availability tests
     * Tests actual hosting capabilities, not user configuration
     */
    private function run_resource_tests() {
        // Get test configuration
        $test_config = $this->get_test_configuration();
        
        // Instantiate the resource tests class and run tests
        $resource_tests = new DiveWP_Resource_Tests();
        return $resource_tests->run_resource_tests($test_config);
    }
    
    // Statistical analysis methods moved to individual test classes
    

    

    

    

    


    // Resource test methods moved to separate class (DiveWP_Resource_Tests)

    /**
     * Run comprehensive database tests
     *
     * Delegates to the DiveWP_Database_Tests class for all database testing functionality.
     *
     * @since 2.0.3
     * @return array|WP_Error Database test results or error
     */
    private function run_database_tests() {
        $database_tests = new DiveWP_Database_Tests();
        return $database_tests->run_database_tests();
    }

    // Database info method moved to DiveWP_Database_Tests class

    // MySQL function performance testing moved to DiveWP_Database_Tests class

    /**
     * Test MySQL function performance (delegated to database tests class)
     * 
     * @since 2.0.3
     * @return array Database function performance results
     */
    private function test_mysql_function_performance() {
        $database_tests = new DiveWP_Database_Tests();
        return $database_tests->test_mysql_function_performance();
    }

    // MySQL function performance testing moved to DiveWP_Database_Tests class
    /**
     * Get complete hosting evaluation
     */
    private function get_complete_evaluation() {
        // Set maximum execution time for all tests
        // Get test configuration
        $test_config = $this->get_test_configuration();
        $environment = $test_config['environment'];
        $test_mode = $test_config['test_mode'];
        
        // Use WordPress native execution time (no manual time limit setting)
        $php_max_execution_time = $test_config['php_max_execution_time'];
        
        // Use user-specific cache key to prevent conflicts in multiuser scenarios
        $evaluation_cache_key = 'divewp_hosting_evaluation_' . get_current_user_id() . '_' . $environment;
        
        // Clear any existing cache to ensure fresh results
        delete_transient($evaluation_cache_key);
        
        // For debugging: check if we had cached results
        $cached_evaluation = get_transient($evaluation_cache_key);
        if ($cached_evaluation !== false) {
            // This shouldn't happen since we just deleted it
            delete_transient($evaluation_cache_key);
        }
        
        // Initialize default values in case tests fail
        $performance = array('score' => 0, 'rating' => 'error');
        $resources = array('overall_score' => 0, 'rating' => 'error'); 
        $database_legacy = array('score' => 0, 'rating' => 'error');
        $database_benchmark = array('score' => 0, 'rating' => 'error');
        $concurrency = array('score' => 0, 'rating' => 'error');
        
        // Early bailout check for extremely low memory situations
        $memory_limit_bytes = wp_convert_hr_to_bytes(ini_get('memory_limit'));
        $memory_usage = memory_get_usage(true);
        $memory_available = $memory_limit_bytes - $memory_usage;
        
        if ($memory_available < (20 * 1024 * 1024)) { // Less than 20MB available
            throw new Exception('Insufficient memory available to run tests. Only ' . size_format($memory_available) . ' remaining.');
        }
        
        // Get enabled tests configuration
        $enabled_tests = $test_config['enabled_tests'] ?? array();
        
        // Run all tests based on configuration and handle potential errors
        if (in_array('run_performance_tests', $enabled_tests)) {
            try {
                $performance = $this->run_performance_tests();
            } catch (Exception $e) {
                $performance['interpretation'] = 'Performance test failed: ' . $e->getMessage();
            }
        } else {
            $performance = array(
                'score' => 0, 
                'rating' => 'skipped',
                'interpretation' => 'Performance tests disabled by user configuration.'
            );
        }
        
        // Resource tests are always run since they contain the sub-test configuration
        try {
            $resources = $this->run_resource_tests();
        } catch (Exception $e) {
            $resources['interpretation'] = 'Resource test failed: ' . $e->getMessage();
        }
        
        if (in_array('run_database_tests', $enabled_tests)) {
            try {
                $database_legacy = $this->run_database_tests();
                if (is_wp_error($database_legacy)) {
                    $database_legacy = array(
                        'score' => 0,
                        'rating' => 'error',
                        'interpretation' => $database_legacy->get_error_message()
                    );
                }
            } catch (Exception $e) {
                $database_legacy = array(
                    'score' => 0,
                    'rating' => 'error',
                    'interpretation' => 'Database test failed: ' . $e->getMessage()
                );
            }
            
            // Run MySQL Function Performance Tests (delegated to database tests class)
            $mysql_functions = array();
            try {
                $database_tests = new DiveWP_Database_Tests();
                $mysql_functions = $database_tests->test_mysql_function_performance();
            } catch (Exception $e) {
                // MySQL function tests are supplementary - don't fail if they error
            }
        } else {
            $database_legacy = array(
                'score' => 0,
                'rating' => 'skipped', 
                'interpretation' => 'Database tests disabled by user configuration.'
            );
            $mysql_functions = array();
        }
        
        if (in_array('run_concurrency_tests', $enabled_tests)) {
            // Concurrency tests now use step-based approach via JavaScript
            // Main evaluation returns pending status, JavaScript will run step-based tests
            $concurrency = array(
                'score' => 0,
                'rating' => 'pending',
                'interpretation' => 'Concurrency tests will run in steps after main evaluation completes.',
                'status' => 'pending'
            );
        } else {
            $concurrency = array(
                'score' => 0,
                'rating' => 'skipped',
                'interpretation' => 'Concurrency tests disabled by user configuration.'
            );
        }
        
        // Check if database test returned an error
        if (is_wp_error($database_legacy)) {
            $database_legacy = array(
                'score' => 0,
                'rating' => 'error',
                'interpretation' => $database_legacy->get_error_message()
            );
        }
        if (is_wp_error($database_benchmark)) {
            $database_benchmark = array(
                'score' => 0,
                'rating' => 'error',
                'interpretation' => $database_benchmark->get_error_message()
            );
        }
        
        // Merge MySQL function results into legacy database results (if available)
        if (!empty($mysql_functions)) {
            $database_legacy['mysql_functions_score'] = $mysql_functions['score'];
            $database_legacy['mysql_crypto_time'] = $mysql_functions['crypto_functions_time'];
            $database_legacy['mysql_math_time'] = $mysql_functions['math_functions_time'];
            $database_legacy['mysql_string_time'] = $mysql_functions['string_functions_time'];
            $database_legacy['mysql_datetime_time'] = $mysql_functions['datetime_functions_time'];
            $database_legacy['mysql_aggregate_time'] = $mysql_functions['aggregate_functions_time'];
            $database_legacy['mysql_total_time'] = $mysql_functions['total_time'];
            $database_legacy['mysql_iterations_completed'] = $mysql_functions['iterations_completed'];
        }
        
        // Gather existing data from other features
        $server_data = $this->gather_server_insights();
        $db_insights = $this->gather_db_insights();
        
        // Calculate overall hosting score with proper weighting
        // Give more weight to areas that directly impact WooCommerce performance
        
        // Check if database test was rate limited
        $db_rate_limited = isset($database_legacy['interpretation']) && 
                          strpos($database_legacy['interpretation'], 'Please wait') !== false;
        
        if ($db_rate_limited) {
            // Don't calculate overall score when database test is rate limited
            $evaluation = array(
                'incomplete' => true,
                'reason' => 'rate_limit',
                'message' => esc_html__('Database test is rate-limited. Please wait 30 seconds and try again for complete results.', 'divewp-boost-site-performance'),
                'environment' => $environment,
                'test_mode' => $test_mode,
                'test_config' => array(
                    'iterations' => $test_config['test_iterations'],
                    'mode' => $test_mode
                ),
                'tests' => array(
                    'performance' => $performance,
                    'resources' => $resources,
                    'database_legacy' => $database_legacy,
                    'database_benchmark' => $database_benchmark,
                    'concurrency' => $concurrency
                ),
                'tests_skipped' => array('database_legacy','database_benchmark'),
                'server_insights' => $server_data,
                'db_insights' => $db_insights,
                'timestamp' => current_time('timestamp')
            );
        } else {
            // Calculate overall score with dynamic weighting based on enabled tests
            $total_weighted_score = 0;
            $total_weight = 0;
            
            // Performance tests weight
            if ($performance['rating'] !== 'skipped') {
                $total_weighted_score += $performance['score'] * 0.30;
                $total_weight += 0.30;
            }
            
            // Resource tests weight (always included)
            $total_weighted_score += $resources['overall_score'] * 0.25;
            $total_weight += 0.25;
            
            // Database tests weight
            if ($database_legacy['rating'] !== 'skipped') {
                $total_weighted_score += $database_legacy['score'] * 0.15;
                $total_weight += 0.15;
            }
            
            // Database benchmark weight (if implemented)
            if ($database_benchmark['rating'] !== 'skipped') {
                $total_weighted_score += $database_benchmark['score'] * 0.15;
                $total_weight += 0.15;
            }
            
            // Concurrency tests weight  
            if ($concurrency['rating'] !== 'skipped' && $concurrency['rating'] !== 'pending') {
                $total_weighted_score += $concurrency['score'] * 0.15;
                $total_weight += 0.15;
            }
            
            // Calculate normalized overall score
            $overall_score = ($total_weight > 0) ? round($total_weighted_score / $total_weight) : 0;
            
            // Determine hosting recommendation
            $recommendation = $this->get_hosting_recommendation($overall_score, array(
                'performance' => $performance,
                'resources' => $resources,
                'database_legacy' => $database_legacy,
                'database_benchmark' => $database_benchmark,
                'concurrency' => $concurrency,
                'server_data' => $server_data,
                'db_insights' => $db_insights
            ));
            
            $evaluation = array(
                'overall_score' => $overall_score,
                'rating' => $this->get_rating_from_score($overall_score),
                'environment' => $environment,
                'test_mode' => $test_mode,
                'test_config' => array(
                    'iterations' => $test_config['test_iterations'],
                    'cpu_iterations' => $test_config['cpu_math_iterations'],
                    'memory_percentage' => $test_config['memory_allocation_percentage'],
                    'db_records' => $test_config['db_insert_count'],
                    'mode' => $test_mode
                ),
                'tests' => array(
                    'performance' => $performance,
                    'resources' => $resources,
                    'database_legacy' => $database_legacy,
                    'database_benchmark' => $database_benchmark,
                    'concurrency' => $concurrency
                ),
                'tests_skipped' => array(),
                'server_insights' => $server_data,
                'db_insights' => $db_insights,
                'recommendation' => $recommendation,
                'timestamp' => current_time('timestamp')
            );
        }
        
        // Cache the results for 1 hour only if evaluation is complete
        if (!$db_rate_limited) {
            set_transient($evaluation_cache_key, $evaluation, HOUR_IN_SECONDS);
        }
        
        return $evaluation;
    }

    // File I/O testing methods moved to DiveWP_Resource_Tests class
    

    

    

    

    


    // WordPress capabilities testing methods moved to DiveWP_Resource_Tests class











    // Memory utility methods moved to DiveWP_Resource_Tests class

    /**
     * Get rating from score
     */
    private function get_rating_from_score($score) {
        if ($score >= 90) return 'excellent';
        if ($score >= 70) return 'good';
        if ($score >= 50) return 'fair';
        if ($score >= 30) return 'poor';
        return 'critical';
    }
    
    // Database and memory interpretation methods moved to individual test classes
    
    // Performance interpretation method moved to DiveWP_Performance_Tests class

    /**
     * Gather server insights data
     */
    private function gather_server_insights() {
        // Simulate gathering data from server insights
        // In real implementation, this would call methods from DiveWP_Server_Insights_New
        return array(
            'php_version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'post_max_size' => ini_get('post_max_size'),
            'upload_max_filesize' => ini_get('upload_max_filesize')
        );
    }

    /**
     * Gather database insights data
     */
    private function gather_db_insights() {
        global $wpdb;
        
        // Basic database metrics
        $db_size = $wpdb->get_var($wpdb->prepare(
            "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) FROM information_schema.TABLES WHERE table_schema = %s",
            DB_NAME
        ));
        
        return array(
            'database_size' => $db_size . ' MB',
            'database_type' => $wpdb->db_server_info()
        );
    }

    /**
     * Get hosting recommendation based on evaluation
     * Provides WooCommerce-specific recommendations
     */
    private function get_hosting_recommendation($score, $test_results) {
        $recommendation = array();
        
        // Get performance profile based on LOWEST performing test (most conservative)
        $performance_profile = $this->get_performance_profile_conservative($test_results);
        
        // Determine hosting verdict based on the most conservative estimate
        $lowest_category = $this->get_lowest_performance_category($test_results);
        
        // Generate verdict based on actual scores
        if ($score >= 90) {
            $recommendation['verdict'] = esc_html__('Excellent hosting performance for WooCommerce!', 'divewp-boost-site-performance');
        } elseif ($score >= 70) {
            $recommendation['verdict'] = esc_html__('Good hosting performance. Well-suited for most WooCommerce stores.', 'divewp-boost-site-performance');
        } elseif ($score >= 50) {
            $recommendation['verdict'] = esc_html__('Mixed performance results. Some areas need improvement.', 'divewp-boost-site-performance');
        } else {
            $recommendation['verdict'] = esc_html__('Performance issues detected. Significant improvements needed.', 'divewp-boost-site-performance');
        }
        
        // Add performance profile without mentioning hosting type
        $recommendation['performance_profile'] = $performance_profile;
        
        // Analyze specific test results
        $recommendation['strengths'] = array();
        $recommendation['bottlenecks'] = array();
        $recommendation['improvements'] = array();
        
        // Database performance analysis
        if (isset($test_results['database_legacy']['score'])) {
            if ($test_results['database_legacy']['score'] >= 70) {
                $recommendation['strengths'][] = sprintf(
                    esc_html__('Database: Fast query performance (%sms total time)', 'divewp-boost-site-performance'),
                    $test_results['database_legacy']['total_time']
                );
            } elseif ($test_results['database_legacy']['score'] < 60) {
                $recommendation['bottlenecks'][] = sprintf(
                    esc_html__('Database: Slow operations detected (%sms for %d products)', 'divewp-boost-site-performance'),
                    $test_results['database_legacy']['total_time'],
                    $test_results['database_legacy']['products_tested']
                );
                $recommendation['improvements'][] = esc_html__('Consider optimizing database configuration, enabling query caching, or upgrading database resources.', 'divewp-boost-site-performance');
            }
        }
        
        // Memory analysis (actual allocation capability)
        if (isset($test_results['resources']['memory_score'])) {
            if ($test_results['resources']['memory_score'] >= 70) {
                $recommendation['strengths'][] = sprintf(
                    esc_html__('Memory: Excellent allocation capability (allocated %s successfully)', 'divewp-boost-site-performance'),
                    $test_results['resources']['max_memory_allocated']
                );
            } elseif ($test_results['resources']['memory_score'] < 60) {
                $recommendation['bottlenecks'][] = esc_html__('Memory: Limited actual allocation capability detected', 'divewp-boost-site-performance');
                $recommendation['improvements'][] = esc_html__('Memory allocation is constrained by hosting limits. Consider upgrading hosting plan.', 'divewp-boost-site-performance');
            }
        }
        
        // CPU performance analysis (from performance tests)
        if (isset($test_results['performance']['score'])) {
            if ($test_results['performance']['score'] >= 70) {
                $recommendation['strengths'][] = sprintf(
                    esc_html__('CPU: Excellent processing speed (%sms for price/shipping calculations)', 'divewp-boost-site-performance'),
                    $test_results['performance']['total_time']
                );
            } elseif ($test_results['performance']['score'] < 60) {
                $recommendation['bottlenecks'][] = esc_html__('CPU: Slow processing for calculations', 'divewp-boost-site-performance');
                $recommendation['improvements'][] = esc_html__('CPU-intensive operations are slow. Consider server with better processing power.', 'divewp-boost-site-performance');
            }
        }

        // CPU performance analysis (from resource tests)
        if (isset($test_results['resources']['cpu_score'])) {
            if ($test_results['resources']['cpu_score'] >= 70) {
                $recommendation['strengths'][] = esc_html__('CPU: Strong computational performance under load', 'divewp-boost-site-performance');
            } elseif ($test_results['resources']['cpu_score'] < 60) {
                $recommendation['bottlenecks'][] = esc_html__('CPU: Weak performance under computational load', 'divewp-boost-site-performance');
                $recommendation['improvements'][] = esc_html__('CPU struggles with intensive operations. Upgrade to faster hosting tier needed.', 'divewp-boost-site-performance');
            }
        }

        // Network capabilities analysis
        if (isset($test_results['resources']['network_score'])) {
            if ($test_results['resources']['network_score'] >= 70) {
                $recommendation['strengths'][] = esc_html__('Network: Excellent external connectivity for APIs and integrations', 'divewp-boost-site-performance');
            } elseif ($test_results['resources']['network_score'] < 60) {
                $recommendation['bottlenecks'][] = esc_html__('Network: Limited or blocked external connections', 'divewp-boost-site-performance');
                $recommendation['improvements'][] = esc_html__('External API calls are restricted. May affect payment gateways and integrations.', 'divewp-boost-site-performance');
            }
        }
        
        // I/O performance analysis
        if (isset($test_results['resources']['io_score'])) {
            if ($test_results['resources']['io_score'] >= 70) {
                $recommendation['strengths'][] = esc_html__('Storage: Fast file I/O operations', 'divewp-boost-site-performance');
            } elseif ($test_results['resources']['io_score'] < 60) {
                $recommendation['bottlenecks'][] = esc_html__('Storage: Slow file operations detected', 'divewp-boost-site-performance');
                $recommendation['improvements'][] = esc_html__('File operations are slow. SSD storage recommended for better performance.', 'divewp-boost-site-performance');
            }
        }
        
        // Concurrency performance analysis
        if (isset($test_results['concurrency']['score'])) {
            if ($test_results['concurrency']['score'] >= 70) {
                $recommendation['strengths'][] = sprintf(
                    esc_html__('Concurrency: Handles multiple operations well (scaling factor: %sx)', 'divewp-boost-site-performance'),
                    $test_results['concurrency']['scaling_factor']
                );
            } elseif ($test_results['concurrency']['score'] < 60) {
                $recommendation['bottlenecks'][] = esc_html__('Concurrency: Performance degrades under simultaneous operations', 'divewp-boost-site-performance');
                $recommendation['improvements'][] = esc_html__('Server struggles with concurrent operations. Better resource isolation needed.', 'divewp-boost-site-performance');
            }
        }
        
        // Add specific warnings for critical issues
        $recommendation['warnings'] = array();
        
        if ($score < 40) {
            $recommendation['warnings'][] = esc_html__('This hosting may cause checkout failures during sales or high traffic.', 'divewp-boost-site-performance');
        }
        
        if (isset($test_results['database_legacy']['total_time']) && $test_results['database_legacy']['total_time'] > 10000) {
            $recommendation['warnings'][] = esc_html__('Database performance is critically slow. Product searches and filtering will be sluggish.', 'divewp-boost-site-performance');
        }
        
        if (isset($test_results['resources']['memory_score']) && $test_results['resources']['memory_score'] < 40) {
            $recommendation['warnings'][] = esc_html__('Very limited memory allocation capability. Large operations may fail.', 'divewp-boost-site-performance');
        }
        
        return $recommendation;
    }
    
    /**
     * Get performance profile based on test results
     * Returns qualitative descriptions without misleading capacity claims
     */
    private function get_performance_profile($test_results) {
        return array(
            'large' => array(
                'title' => esc_html__('High-Performance Environment', 'divewp-boost-site-performance'),
                'description' => esc_html__('Excellent performance across all tests. This environment can handle high-traffic stores, complex calculations, and concurrent operations efficiently. Ready for advanced optimizations like object caching.', 'divewp-boost-site-performance'),
                'suitable_for' => esc_html__('Large catalogs, high-traffic stores, complex WooCommerce operations', 'divewp-boost-site-performance')
            ),
            'medium' => array(
                'title' => esc_html__('Good Performance Environment', 'divewp-boost-site-performance'),
                'description' => esc_html__('Solid performance for most WooCommerce operations. Can handle moderate traffic and standard store operations well. May benefit from caching during peak periods.', 'divewp-boost-site-performance'),
                'suitable_for' => esc_html__('Growing stores, regular traffic, standard WooCommerce features', 'divewp-boost-site-performance')
            ),
            'small' => array(
                'title' => esc_html__('Mixed Performance Environment', 'divewp-boost-site-performance'),
                'description' => esc_html__('Some performance limitations detected. Can handle basic WooCommerce operations but may experience slowdowns with complex queries or high traffic. Optimization recommended.', 'divewp-boost-site-performance'),
                'suitable_for' => esc_html__('Small stores, limited traffic, basic WooCommerce usage', 'divewp-boost-site-performance')
            ),
            'minimal' => array(
                'title' => esc_html__('Performance Constraints Detected', 'divewp-boost-site-performance'),
                'description' => esc_html__('Significant performance limitations found. May struggle with standard WooCommerce operations. Consider performance improvements before launching a production store.', 'divewp-boost-site-performance'),
                'suitable_for' => esc_html__('Development, testing, or very light usage only', 'divewp-boost-site-performance')
            )
        );
    }

    // Concurrency test method moved to DiveWP_Concurrency_Tests class

    /**
     * Get the lowest performance category from all tests
     */
    private function get_lowest_performance_category($test_results) {
        $categories = array();
        
        // Database performance analysis
        if (isset($test_results['database_legacy']['total_time'])) {
            $db_time = $test_results['database_legacy']['total_time'] / 1000; // Convert to seconds
            if ($db_time < 2) {
                $categories[] = 'large';
            } elseif ($db_time < 5) {
                $categories[] = 'medium';
            } elseif ($db_time < 10) {
                $categories[] = 'small';
            } else {
                $categories[] = 'minimal';
            }
        }
        
        // Performance test analysis
        if (isset($test_results['performance']['total_time'])) {
            $perf_time = $test_results['performance']['total_time'] / 1000; // Convert to seconds
            if ($perf_time < 0.5) {
                $categories[] = 'large';
            } elseif ($perf_time < 1.5) {
                $categories[] = 'medium';
            } elseif ($perf_time < 3.0) {
                $categories[] = 'small';
            } else {
                $categories[] = 'minimal';
            }
        }
        
        // Memory analysis
        if (isset($test_results['resources']['php_memory_remaining'])) {
            $memory_str = $test_results['resources']['php_memory_remaining'];
            $memory_mb = 0;
            
            // Extract numeric value from memory string
            if (preg_match('/(\d+(?:\.\d+)?)\s*MB/', $memory_str, $matches)) {
                $memory_mb = floatval($matches[1]);
            } elseif (preg_match('/(\d+(?:\.\d+)?)\s*GB/', $memory_str, $matches)) {
                $memory_mb = floatval($matches[1]) * 1024;
            }
            
            if ($memory_mb >= 128) {
                $categories[] = 'large';
            } elseif ($memory_mb >= 64) {
                $categories[] = 'medium';
            } elseif ($memory_mb >= 32) {
                $categories[] = 'small';
            } else {
                $categories[] = 'minimal';
            }
        }
        
        // Return the most conservative (lowest) category
        if (in_array('minimal', $categories)) return 'minimal';
        if (in_array('small', $categories)) return 'small';
        if (in_array('medium', $categories)) return 'medium';
        return 'large';
    }

    /**
     * Get conservative performance profile based on lowest performing test
     */
    private function get_performance_profile_conservative($test_results) {
        $category = $this->get_lowest_performance_category($test_results);
        $profiles = $this->get_performance_profile($test_results);
        
        return $profiles[$category];
    }

    /**
     * Clean up user-specific transients on logout
     */
    public function cleanup_user_transients() {
        $user_id = get_current_user_id();
        if ($user_id) {
            // Clean up user-specific transients
            delete_transient('divewp_db_test_rate_limit_' . $user_id);
            delete_transient('divewp_hosting_evaluation_' . $user_id);
        }
    }
    
    /**
     * Render test configuration section with toggles for individual tests
     */
    private function render_test_config_section() {
        // Define test categories and their individual tests with intensity levels
        $test_categories = array(
            'main_test_suites' => array(
                'title' => esc_html__('Main Test Suites', 'divewp-boost-site-performance'),
                'color' => '#2271b1',
                'tests' => array(
                    'run_performance_tests' => array(
                        'name' => esc_html__('Performance Tests (WooCommerce)', 'divewp-boost-site-performance'),
                        'description' => esc_html__('Tests price calculations, shipping calculations, and inventory operations that WooCommerce performs', 'divewp-boost-site-performance'),
                        'default' => true
                    ),
                    'run_database_tests' => array(
                        'name' => esc_html__('Database Tests', 'divewp-boost-site-performance'),
                        'description' => esc_html__('Tests database INSERT, SELECT, and UPDATE operations plus MySQL function performance', 'divewp-boost-site-performance'),
                        'default' => true
                    ),
                    'run_concurrency_tests' => array(
                        'name' => esc_html__('Concurrency Tests', 'divewp-boost-site-performance'),
                        'description' => esc_html__('Tests how well the server handles multiple simultaneous operations and scaling under load', 'divewp-boost-site-performance'),
                        'default' => true
                    )
                )
            ),
            'maximum_intensity_tests' => array(
                'title' => esc_html__('Maximum Intensity Tests', 'divewp-boost-site-performance'),
                'color' => '#dc3232',
                'tests' => array(
                    'test_memory_allocation_limits' => array(
                        'name' => esc_html__('Memory Allocation Limits', 'divewp-boost-site-performance'),
                        'description' => esc_html__('Tests actual memory allocation up to server limits. Essential for complete hosting evaluation but may be restricted by some hosting providers.', 'divewp-boost-site-performance'),
                        'default' => true
                    )
                )
            ),
            'very_high_intensity_tests' => array(
                'title' => esc_html__('Very High Intensity Tests', 'divewp-boost-site-performance'),
                'color' => '#e65100',
                'tests' => array(
                    'test_prime_generation' => array(
                        'name' => esc_html__('Prime Number Generation', 'divewp-boost-site-performance'),
                        'description' => esc_html__('CPU-intensive prime number calculations that stress-test processing power', 'divewp-boost-site-performance'),
                        'default' => true
                    ),
                    'test_mathematical_operations' => array(
                        'name' => esc_html__('Mathematical Operations', 'divewp-boost-site-performance'),
                        'description' => esc_html__('Heavy math operations (sin, cos, sqrt, log) that test computational limits', 'divewp-boost-site-performance'),
                        'default' => true
                    ),
                    'run_memory_tests' => array(
                        'name' => esc_html__('Memory Tests (Complete)', 'divewp-boost-site-performance'),
                        'description' => esc_html__('Comprehensive memory allocation testing in loops to evaluate memory handling', 'divewp-boost-site-performance'),
                        'default' => true
                    )
                )
            ),
            'high_intensity_tests' => array(
                'title' => esc_html__('High Intensity Tests', 'divewp-boost-site-performance'),
                'color' => '#f57c00',
                'tests' => array(
                    'run_cpu_tests' => array(
                        'name' => esc_html__('CPU Tests (Complete)', 'divewp-boost-site-performance'),
                        'description' => esc_html__('Multiple CPU-intensive functions in loops to test processing performance', 'divewp-boost-site-performance'),
                        'default' => true
                    ),
                    'test_conditional_logic' => array(
                        'name' => esc_html__('Conditional Logic', 'divewp-boost-site-performance'),
                        'description' => esc_html__('Complex nested loops with many iterations to test logical processing', 'divewp-boost-site-performance'),
                        'default' => true
                    ),
                    'test_string_processing' => array(
                        'name' => esc_html__('String Processing', 'divewp-boost-site-performance'),
                        'description' => esc_html__('Manipulates 1MB+ strings repeatedly to test memory and processing efficiency', 'divewp-boost-site-performance'),
                        'default' => true
                    ),
                    'test_array_operations' => array(
                        'name' => esc_html__('Array Operations', 'divewp-boost-site-performance'),
                        'description' => esc_html__('Sorts arrays with 20,000+ elements to test data handling performance', 'divewp-boost-site-performance'),
                        'default' => true
                    )
                )
            ),
            'moderate_intensity_tests' => array(
                'title' => esc_html__('Moderate Intensity Tests', 'divewp-boost-site-performance'),
                'color' => '#388e3c',
                'tests' => array(
                    'run_wp_tests' => array(
                        'name' => esc_html__('WordPress Tests', 'divewp-boost-site-performance'),
                        'description' => esc_html__('WordPress operations in loops to test CMS-specific performance', 'divewp-boost-site-performance'),
                        'default' => true
                    ),
                    'run_io_test' => array(
                        'name' => esc_html__('File I/O Test', 'divewp-boost-site-performance'),
                        'description' => esc_html__('File creation, write, read operations to test storage performance', 'divewp-boost-site-performance'),
                        'default' => true
                    ),
                    'run_network_test' => array(
                        'name' => esc_html__('Network Test', 'divewp-boost-site-performance'),
                        'description' => esc_html__('External HTTP requests to test network connectivity and speed', 'divewp-boost-site-performance'),
                        'default' => true
                    ),
                    'test_transient_operations' => array(
                        'name' => esc_html__('Transient Operations', 'divewp-boost-site-performance'),
                        'description' => esc_html__('Database operations (set/get/delete) to test caching performance', 'divewp-boost-site-performance'),
                        'default' => true
                    )
                )
            ),
            'light_intensity_tests' => array(
                'title' => esc_html__('Light Intensity Tests', 'divewp-boost-site-performance'),
                'color' => '#1976d2',
                'tests' => array(
                    'test_shortcode_processing' => array(
                        'name' => esc_html__('Shortcode Processing', 'divewp-boost-site-performance'),
                        'description' => esc_html__('WordPress shortcode processing to test content handling', 'divewp-boost-site-performance'),
                        'default' => true
                    ),
                    'test_hook_execution' => array(
                        'name' => esc_html__('Hook Execution', 'divewp-boost-site-performance'),
                        'description' => esc_html__('WordPress filter execution to test hook system performance', 'divewp-boost-site-performance'),
                        'default' => true
                    ),
                    'test_security_functions' => array(
                        'name' => esc_html__('Security Functions', 'divewp-boost-site-performance'),
                        'description' => esc_html__('WordPress sanitization functions to test security processing', 'divewp-boost-site-performance'),
                        'default' => true
                    ),
                    'finalize_test_results' => array(
                        'name' => esc_html__('Results Finalization', 'divewp-boost-site-performance'),
                        'description' => esc_html__('Data aggregation and file write operations to complete evaluation', 'divewp-boost-site-performance'),
                        'default' => true
                    )
                )
            )
        );
        
        // Render test categories
        echo '<div class="test-config-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">';
        
        foreach ($test_categories as $category_id => $category) {
            echo '<div class="test-category-card" style="border: 1px solid ' . esc_attr($category['color']) . '; border-radius: 8px; padding: 15px; background: #fff;">';
            echo '<h6 style="color: ' . esc_attr($category['color']) . '; margin: 0 0 10px 0; font-weight: bold;">' . $category['title'] . '</h6>';
            
            foreach ($category['tests'] as $test_id => $test_info) {
                $checked = $test_info['default'] ? 'checked' : '';
                echo '<div class="test-toggle" style="margin-bottom: 8px;">';
                echo '<label style="display: flex; align-items: flex-start; cursor: pointer;">';
                echo '<input type="checkbox" name="enabled_tests[]" value="' . esc_attr($test_id) . '" ' . $checked . ' style="margin: 2px 8px 0 0;">';
                echo '<div>';
                echo '<strong>' . $test_info['name'] . '</strong><br>';
                echo '<small style="color: #666;">' . $test_info['description'] . '</small>';
                echo '</div>';
                echo '</label>';
                echo '</div>';
            }
            
            echo '</div>'; // Close test-category-card
        }
        
        echo '</div>'; // Close test-config-grid
        

    }

    /**
     * Render the Choose Hosting content
     */
    public function render() {
        // Add capability check
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'divewp-boost-site-performance'));
        }

        echo '<h3>' . esc_html__('Choose Hosting Provider Guide', 'divewp-boost-site-performance') . '</h3>';

        // Render introductory text
        echo '<div class="divewp-section">';
        echo '<h4><span class="dashicons dashicons-info" style="vertical-align: middle; margin-right: 5px;"></span>' . esc_html__('Why Your Host Matters', 'divewp-boost-site-performance') . '</h4>';
        echo '<p>' . esc_html__("Your hosting provider is the foundation of your website. It significantly impacts your site's speed, security, reliability, and ability to handle traffic. Choosing the right host is crucial for success.", 'divewp-boost-site-performance') . '</p>';
        echo '<p>' . esc_html__("This guide explains key hosting terms and provides questions to ask potential hosts.", 'divewp-boost-site-performance') . '</p>';
        echo '</div>';

        // Add the new hosting evaluation section with clean design
        echo '<div class="divewp-section divewp-hosting-evaluation">';
        echo '<h4><span class="dashicons dashicons-dashboard" style="vertical-align: middle; margin-right: 5px;"></span>' . esc_html__('Hosting Evaluation', 'divewp-boost-site-performance') . '</h4>';
        
        // Two-paragraph description
        echo '<p>' . esc_html__('Evaluate your hosting performance with comprehensive tests that simulate real WooCommerce operations. Our evaluation includes performance benchmarks, database operations, memory allocation tests, and concurrency analysis to give you a complete picture of your hosting capabilities.', 'divewp-boost-site-performance') . '</p>';
        echo '<p>' . esc_html__('The evaluation runs 19 different tests covering CPU performance, WordPress operations, database efficiency, memory handling, file I/O speed, and network connectivity. Results help you understand if your current hosting can handle your site\'s traffic and growth requirements.', 'divewp-boost-site-performance') . '</p>';
        
        // Run button and settings in a clean layout
        echo '<div class="hosting-evaluation-controls" style="display: flex; align-items: center; gap: 15px; margin: 25px 0;">';
        echo '<button id="divewp-show-results" class="divewp-button">' . esc_html__('Run Hosting Evaluation', 'divewp-boost-site-performance') . '</button>';
        echo '<button type="button" id="toggle-test-config" class="button button-secondary" style="display: flex; align-items: center; gap: 5px;">';
        echo '<span class="dashicons dashicons-admin-settings" style="font-size: 16px;"></span>';
        echo esc_html__('Advanced Settings', 'divewp-boost-site-performance');
        echo '</button>';
        echo '<span style="color: #666; font-size: 13px;">' . esc_html__('(Configure which tests to run if some are blocked by your hosting provider)', 'divewp-boost-site-performance') . '</span>';
        echo '</div>';
        
        // Hidden test configuration panel
        echo '<div id="test-config-panel" style="display: none; background: #f8f9fa; border: 1px solid #e1e5e9; border-radius: 6px; padding: 20px; margin-bottom: 20px;">';
        echo '<h5 style="margin-top: 0; color: #1e293b;">' . esc_html__('Test Configuration', 'divewp-boost-site-performance') . '</h5>';
        echo '<p style="color: #666; font-size: 13px; margin-bottom: 15px;">' . esc_html__('All tests are enabled by default for comprehensive evaluation. This includes 3 main test suites (Performance, Database, Concurrency) plus 16 detailed Resource sub-tests. If certain tests are blocked or restricted by your hosting provider, you can disable specific test categories below to isolate which operations your hosting environment limits.', 'divewp-boost-site-performance') . '</p>';
        
        // Resource Tests Configuration
        $this->render_test_config_section();
        
        echo '</div>'; // Close test-config-panel
        
        // Results container
        echo '<div id="divewp-results-container" style="display:none; margin-top: 20px;"></div>';
        echo '</div>';

        // --- Section: Key Hosting Factors Explained (Using Cards) ---
        echo '<div class="divewp-section">';
        echo '<h4><span class="dashicons dashicons-book" style="vertical-align: middle; margin-right: 5px;"></span>' . esc_html__('Key Hosting Factors Explained', 'divewp-boost-site-performance') . '</h4>';
        // Add instruction to check other tabs
        echo '<p>' . esc_html__("Consider how your current site performs based on the details in other DiveWP tabs (like Server Insights, DB Insights, and Performance Checks) when evaluating these factors.", 'divewp-boost-site-performance') . '</p>';
        echo '<div class="recommendations-grid">';

        // Render all the factor cards
        $this->render_factor_card('hosting-types');
        $this->render_factor_card('cpu-ram');
        $this->render_factor_card('storage');
        $this->render_factor_card('inodes');
        $this->render_factor_card('bandwidth');
        $this->render_factor_card('php-db');
        $this->render_factor_card('backups-support');
        $this->render_factor_card('ssl-location');

        echo '</div>'; // Close recommendations-grid
        echo '</div>';

        // --- Section: Where to Find Details ---
        echo '<div class="divewp-section">';
        echo '<h4><span class="dashicons dashicons-search" style="vertical-align: middle; margin-right: 5px;"></span>' . esc_html__('Where to Find Hosting Details', 'divewp-boost-site-performance') . '</h4>';
        echo '<ul style="list-style: disc; padding-left: 20px;">';
        echo '<li>' . esc_html__("Hosting Provider's Website: Check plan comparison pages and knowledge base articles.", 'divewp-boost-site-performance') . '</li>';
        echo '<li>' . esc_html__("Hosting Control Panel: Log in to your cPanel/Plesk/etc. to check resource usage like disk space.", 'divewp-boost-site-performance') . '</li>';
        echo '<li>' . esc_html__("Contact Support/Sales: Ask the hosting provider directly for specifics not listed online.", 'divewp-boost-site-performance') . '</li>';
        echo '</ul>';
        echo '</div>';

        // --- Section: Questions to Ask ---
        echo '<div class="divewp-section">';
        echo '<h4><span class="dashicons dashicons-editor-help" style="vertical-align: middle; margin-right: 5px;"></span>' . esc_html__('Questions to Ask Potential Hosts', 'divewp-boost-site-performance') . '</h4>';
        echo '<ul style="list-style: disc; padding-left: 20px;">';
        echo '<li>' . esc_html__("What are the specific CPU & RAM resources allocated (dedicated or shared)?", 'divewp-boost-site-performance') . '</li>';
        echo '<li>' . esc_html__("What is the inode limit?", 'divewp-boost-site-performance') . '</li>';
        echo '<li>' . esc_html__("Do you use SSD storage for files AND databases?", 'divewp-boost-site-performance') . '</li>';
        echo '<li>' . esc_html__("Which PHP versions are supported? What is the default `memory_limit`?", 'divewp-boost-site-performance') . '</li>';
        echo '<li>' . esc_html__("Which MySQL/MariaDB versions are available?", 'divewp-boost-site-performance') . '</li>';
        echo '<li>' . esc_html__("Is a free SSL certificate included and auto-renewed?", 'divewp-boost-site-performance') . '</li>';
        echo '<li>' . esc_html__("How often are backups performed, where are they stored, and how long are they kept?", 'divewp-boost-site-performance') . '</li>';
        echo '<li>' . esc_html__("What support channels and hours are available?", 'divewp-boost-site-performance') . '</li>';
        echo '<li>' . esc_html__("Where are your data centers located?", 'divewp-boost-site-performance') . '</li>';
        echo '<li>' . esc_html__("(Optional) Do you offer server-level caching (LiteSpeed, Redis, etc.)?", 'divewp-boost-site-performance') . '</li>';
        echo '</ul>';
        echo '</div>';

        // --- Section: Disclaimer ---
        echo '<div class="divewp-notice divewp-notice-info">';
        echo '<p><strong>' . esc_html__('Disclaimer:', 'divewp-boost-site-performance') . '</strong> ';
        echo esc_html__("DiveWP provides informational guidance based on general best practices and available data. We cannot guarantee the suitability or performance of any specific hosting provider. Please conduct thorough research before choosing a host.", 'divewp-boost-site-performance') . '</p>';
        echo '</div>';

    }

    /**
     * Renders a generic hosting factor card.
     */
    private function render_factor_card($check_type) {
        $content = $this->content_loader->get_content('choose-hosting', $check_type);

        if (!$content) {
            echo '<p>Error loading content for ' . esc_html($check_type) . '.</p>';
            return;
        }

        $messages = isset($content['messages']['info']) ? $content['messages']['info'] : [];
        $learn_more = isset($content['learn_more']) ? $content['learn_more'] : [];

        $args = array(
            'title'       => isset($messages['title']) ? esc_html($messages['title']) : ucfirst(str_replace('-', ' ', $check_type)),
            'icon'        => $this->get_icon($check_type),
            'details'     => isset($messages['details']) ? esc_html($messages['details']) : '',
            'steps'       => array(),
            'status'      => 'info',
            'status_text' => esc_html__('Informational', 'divewp-boost-site-performance'),
            'learn_more'  => $learn_more,
            'feature'     => 'choose-hosting',
            'check_name'  => $check_type
        );

        $this->render_card($args);
    }

    /**
     * Helper method to render a card using the template.
     */
    private function render_card($args) {
        $defaults = array(
            'title' => '',
            'icon' => '',
            'details' => '',
            'steps' => array(),
            'status' => 'info',
            'status_text' => esc_html__('Information', 'divewp-boost-site-performance'),
            'learn_more' => array(),
            'feature' => '',
            'check_name' => ''
        );
        $args = wp_parse_args($args, $defaults);
        extract($args);
        $template_path = DIVEWP_PLUGIN_DIR . 'includes/templates/card-template.php';
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            echo '<p>Error: Card template not found.</p>';
        }
    }

    /**
     * Get icon for a specific check type.
     */
    private function get_icon($type) {
        // Return SVG markup directly, matching card-template.php wp_kses expectations
        // Reusing SVGs from class-server-insights-new.php where applicable
        $svg_markup = '';
        switch ($type) {
            case 'hosting-types': // Networking
            case 'bandwidth':     // Chart Area / Networking
            case 'ssl-location':  // Admin Site Alt / Networking
                $svg_markup = '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>'; // Reused External Connections icon
                break;
            case 'cpu-ram': // Performance
                $svg_markup = '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2"/><path d="M9 4v4"/><path d="M15 4v4"/><path d="M9 16v4"/><path d="M15 16v4"/><path d="M4 9h4"/><path d="M16 9h4"/><path d="M4 15h4"/><path d="M16 15h4"/></svg>'; // Reused Memory Limit icon
                break;
            case 'storage': // Database
            case 'php-db':  // Admin Settings / Database
                $svg_markup = '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 5c0 1.1-3.582 2-8 2s-8-.9-8-2 3.582-2 8-2 8 .9 8 2"/><path d="M3 5v14c0 1.1 3.582 2 8 2s8-.9 8-2V5"/></svg>'; // Reused Database Version icon
                break;
            case 'inodes': // Media Default / File Icon Placeholder
                 $svg_markup = '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>'; // Simple file icon
                break;
            case 'backups-support': // Shield Alt
                 $svg_markup = '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>'; // Shield icon
                break;
            default: // Default info icon
                $svg_markup = '<svg class="divewp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="8"/></svg>';
                break;
        }

        // No need for esc_attr here as wp_kses in card-template handles sanitization
        return $svg_markup;
    }

    /**
     * Transform raw test results into template-ready format
     * 
     * @param array $evaluation Complete evaluation results
     * @return array Template-ready results for all test cards
     */
    private function transform_results_for_template($evaluation) {
        $template_data = array();
        
        // Performance Card
        if (isset($evaluation['tests']['performance'])) {
            $perf = $evaluation['tests']['performance'];
            $template_data['performance'] = array(
                'test_name' => 'Performance',
                'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polygon points="16.24,7.76 14.12,14.12 7.76,16.24 9.88,9.88 16.24,7.76"/></svg>',
                'score' => $perf['score'] ?? 0,
                'rating' => $perf['rating'] ?? 'unknown',
                'total_time' => $this->format_time_for_display($perf['total_time'] ?? 0),
                'business_impact' => $this->get_performance_business_impact($perf),
                'sub_tests' => $this->get_performance_sub_tests($perf),
                'summary' => $perf['interpretation'] ?? '',
                'recommendations' => array(),
                'technical_details' => array(
                    'Products Calculated' => $perf['products_calculated'] ?? 'N/A',
                    'Test Iterations' => $perf['test_iterations'] ?? 'N/A',
                    'OpCache Enabled' => ($perf['opcache_enabled'] ?? false) ? 'Yes' : 'No',
                    'APC Enabled' => ($perf['apc_enabled'] ?? false) ? 'Yes' : 'No'
                )
            );
        }
        
        // Resources Card
        if (isset($evaluation['tests']['resources'])) {
            $res = $evaluation['tests']['resources'];
            
            // Calculate total time from resource test execution summary or individual component times
            $total_time_ms = 0;
            if (isset($res['execution_summary']['session_duration'])) {
                $total_time_ms = $res['execution_summary']['session_duration'] * 1000; // Convert seconds to ms
            } elseif (isset($res['cpu_time_total'], $res['wp_time_total'], $res['memory_time_total'])) {
                // Fallback: sum up individual test times if available
                $total_time_ms = ($res['cpu_time_total'] ?? 0) + ($res['wp_time_total'] ?? 0) + ($res['memory_time_total'] ?? 0);
            }
            
            $template_data['resources'] = array(
                'test_name' => 'Server Resources',
                'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2"/><path d="M9 4v4"/><path d="M15 4v4"/><path d="M9 16v4"/><path d="M15 16v4"/><path d="M4 9h4"/><path d="M16 9h4"/><path d="M4 15h4"/><path d="M16 15h4"/></svg>',
                'score' => $res['overall_score'] ?? 0,
                'rating' => $res['rating'] ?? 'unknown',
                'total_time' => $this->format_time_for_display($total_time_ms),
                'business_impact' => $this->get_resources_business_impact($res),
                'sub_tests' => $this->get_resources_sub_tests($res),
                'summary' => $res['interpretation'] ?? '',
                'recommendations' => array(),
                'technical_details' => array(
                    'CPU Score' => $res['cpu_score'] ?? 'N/A',
                    'Memory Score' => $res['memory_score'] ?? 'N/A', 
                    'I/O Score' => $res['io_score'] ?? 'N/A',
                    'Network Score' => $res['network_score'] ?? 'N/A',
                    'Test Stability' => $res['test_stability'] ?? 'N/A'
                )
            );
        }
        
        // Database Card
        if (isset($evaluation['tests']['database_legacy'])) {
            $db = $evaluation['tests']['database_legacy'];
            $template_data['database'] = array(
                'test_name' => 'Database',
                'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="m3 5 0 14c0 1.66 4.03 3 9 3s9-1.34 9-3V5"/><path d="m3 12c0 1.66 4.03 3 9 3s9-1.34 9-3"/></svg>',
                'score' => $db['score'] ?? 0,
                'rating' => $db['rating'] ?? 'unknown',
                'total_time' => $this->format_time_for_display($db['total_time'] ?? 0),
                'business_impact' => $this->get_database_business_impact($db),
                'sub_tests' => $this->get_database_sub_tests($db),
                'summary' => $db['interpretation'] ?? '',
                'recommendations' => array(),
                'technical_details' => array(
                    'Products Tested' => $db['products_tested'] ?? 'N/A',
                    'Meta Records' => $db['meta_records'] ?? 'N/A',
                    'Queries Run' => $db['queries_run'] ?? 'N/A',
                    'Test Iterations' => $db['test_iterations'] ?? 'N/A'
                )
            );
        }
        
        // Concurrency Card
        if (isset($evaluation['tests']['concurrency'])) {
            $conc = $evaluation['tests']['concurrency'];
            $template_data['concurrency'] = array(
                'test_name' => 'Multi-User Handling',
                'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m22 21-3-3m2.5-4a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"/></svg>',
                'score' => $conc['score'] ?? 0,
                'rating' => $conc['rating'] ?? 'unknown',
                'total_time' => $this->format_time_for_display($conc['total_time'] ?? 0),
                'business_impact' => $this->get_concurrency_business_impact($conc),
                'sub_tests' => $this->get_concurrency_sub_tests($conc),
                'summary' => $conc['interpretation'] ?? '',
                'recommendations' => array(),
                'technical_details' => array(
                    'Concurrent Operations' => $conc['concurrent_operations'] ?? 'N/A',
                    'Scaling Factor' => $conc['scaling_factor'] ?? 'N/A',
                    'Response Degradation' => $conc['response_degradation'] ?? 'N/A',
                    'Baseline Time' => ($conc['baseline_avg_time'] ?? 0) . 'ms'
                )
            );
        }
        
        return $template_data;
    }
    
    /**
     * Get business impact for performance tests
     */
    private function get_performance_business_impact($perf) {
        $impacts = array();
        $score = $perf['score'] ?? 0;
        
        if ($score >= 90) {
            $impacts[] = 'Lightning fast WooCommerce calculations';
            $impacts[] = 'Instant checkout processing';
            $impacts[] = 'Handles busy shopping periods smoothly';
        } elseif ($score >= 70) {
            $impacts[] = 'Good WooCommerce performance';
            $impacts[] = 'Handles moderate traffic well';
            $impacts[] = 'Suitable for most online stores';
        } elseif ($score >= 50) {
            $impacts[] = 'Basic WooCommerce functionality';
            $impacts[] = 'May slow during peak times';
        } else {
            $impacts[] = 'Performance challenges detected';
            $impacts[] = 'May struggle with busy periods';
        }
        
        return $impacts;
    }
    
    /**
     * Get sub-tests for performance tests
     */
    private function get_performance_sub_tests($perf) {
        $sub_tests = array();
        
        if (isset($perf['price_calc_time'])) {
            $iterations = $perf['test_iterations'] ?? 15;
            $sub_tests[] = array(
                'name' => 'Price Calculations',
                'description' => 'How fast product prices update',
                'time' => $this->format_time_for_display($perf['price_calc_time']),
                'operations' => '2500 calculations × ' . $iterations . ' iterations'
            );
        }
        
        if (isset($perf['shipping_calc_time'])) {
            $iterations = $perf['test_iterations'] ?? 15;
            $sub_tests[] = array(
                'name' => 'Shipping Calculations',
                'description' => 'Speed of shipping cost calculations',
                'time' => $this->format_time_for_display($perf['shipping_calc_time']),
                'operations' => '1250 calculations × ' . $iterations . ' iterations'
            );
        }
        
        if (isset($perf['inventory_check_time'])) {
            $iterations = $perf['test_iterations'] ?? 15;
            $sub_tests[] = array(
                'name' => 'Inventory Operations',
                'description' => 'Stock level checking speed',
                'time' => $this->format_time_for_display($perf['inventory_check_time']),
                'operations' => '1500 checks × ' . $iterations . ' iterations'
            );
        }
        
        return $sub_tests;
    }
    
    /**
     * Get business impact for resources tests
     */
    private function get_resources_business_impact($res) {
        $impacts = array();
        $overall_score = $res['overall_score'] ?? 0;
        
        if ($overall_score >= 90) {
            $impacts[] = 'Excellent server processing power';
            $impacts[] = 'Plenty of memory for large catalogs';
            $impacts[] = 'Fast file access speeds';
            $impacts[] = 'Reliable network connectivity';
        } elseif ($overall_score >= 70) {
            $impacts[] = 'Good server resources';
            $impacts[] = 'Adequate memory allocation';
            $impacts[] = 'Solid file performance';
        } elseif ($overall_score >= 50) {
            $impacts[] = 'Basic server capabilities';
            $impacts[] = 'Limited resource availability';
        } else {
            $impacts[] = 'Resource constraints detected';
            $impacts[] = 'May impact site performance';
        }
        
        return $impacts;
    }
    
    /**
     * Get sub-tests for resources tests
     */
    private function get_resources_sub_tests($res) {
        $sub_tests = array();
        
        // Extract detailed CPU averages if available
        $cpu_details = $res['cpu_detailed_averages'] ?? array();
        $wp_details = $res['wp_core_detailed_averages'] ?? array();
        
        // CPU Sub-tests (5 tests)
        if (!empty($cpu_details)) {
            if (isset($cpu_details['prime_generation_time'])) {
                $iterations = $res['cpu_iterations_completed'] ?? 8;
                $sub_tests[] = array(
                    'name' => 'Prime Generation',
                    'description' => 'CPU-intensive prime number calculations',
                    'time' => $this->format_time_for_display($cpu_details['prime_generation_time'] * 1000),
                    'operations' => '1,000 calculations × ' . $iterations . ' iterations'
                );
            }
            
            if (isset($cpu_details['mathematical_operations_time'])) {
                $iterations = $res['cpu_iterations_completed'] ?? 8;
                $sub_tests[] = array(
                    'name' => 'Mathematical Operations',
                    'description' => 'Complex math calculations (sin, cos, sqrt)',
                    'time' => $this->format_time_for_display($cpu_details['mathematical_operations_time'] * 1000),
                    'operations' => '10,000 operations × ' . $iterations . ' iterations'
                );
            }
            
            if (isset($cpu_details['conditional_logic_time'])) {
                $iterations = $res['cpu_iterations_completed'] ?? 8;
                $sub_tests[] = array(
                    'name' => 'Conditional Logic',
                    'description' => 'Complex nested loops and logic',
                    'time' => $this->format_time_for_display($cpu_details['conditional_logic_time'] * 1000),
                    'operations' => '50,000 loops × ' . $iterations . ' iterations'
                );
            }
            
            if (isset($cpu_details['string_processing_time'])) {
                $iterations = $res['cpu_iterations_completed'] ?? 8;
                $sub_tests[] = array(
                    'name' => 'String Processing',
                    'description' => 'Large text manipulation operations',
                    'time' => $this->format_time_for_display($cpu_details['string_processing_time'] * 1000),
                    'operations' => '5,000 strings × ' . $iterations . ' iterations'
                );
            }
            
            if (isset($cpu_details['array_operations_time'])) {
                $iterations = $res['cpu_iterations_completed'] ?? 8;
                $sub_tests[] = array(
                    'name' => 'Array Operations',
                    'description' => 'Large array sorting and manipulation',
                    'time' => $this->format_time_for_display($cpu_details['array_operations_time'] * 1000),
                    'operations' => '10,000 items × ' . $iterations . ' iterations'
                );
            }
        }
        
        // WordPress Sub-tests (4 tests)
        if (!empty($wp_details)) {
            if (isset($wp_details['shortcode_processing_time'])) {
                $iterations = $res['wp_iterations_completed'] ?? 8;
                $sub_tests[] = array(
                    'name' => 'Shortcode Processing',
                    'description' => 'WordPress shortcode execution',
                    'time' => $this->format_time_for_display($wp_details['shortcode_processing_time'] * 1000),
                    'operations' => '100 shortcodes × ' . $iterations . ' iterations'
                );
            }
            
            if (isset($wp_details['hook_execution_time'])) {
                $iterations = $res['wp_iterations_completed'] ?? 8;
                $sub_tests[] = array(
                    'name' => 'Hook Execution',
                    'description' => 'WordPress filter and action processing',
                    'time' => $this->format_time_for_display($wp_details['hook_execution_time'] * 1000),
                    'operations' => '500 hooks × ' . $iterations . ' iterations'
                );
            }
            
            if (isset($wp_details['transient_operations_time'])) {
                $iterations = $res['wp_iterations_completed'] ?? 8;
                $sub_tests[] = array(
                    'name' => 'Transient Operations',
                    'description' => 'WordPress caching operations',
                    'time' => $this->format_time_for_display($wp_details['transient_operations_time'] * 1000),
                    'operations' => '200 cache ops × ' . $iterations . ' iterations'
                );
            }
            
            if (isset($wp_details['security_functions_time'])) {
                $iterations = $res['wp_iterations_completed'] ?? 8;
                $sub_tests[] = array(
                    'name' => 'Security Functions',
                    'description' => 'WordPress sanitization and validation',
                    'time' => $this->format_time_for_display($wp_details['security_functions_time'] * 1000),
                    'operations' => '1,000 validations × ' . $iterations . ' iterations'
                );
            }
        }
        
        // Memory Test (1 test)
        if (isset($res['max_memory_allocated'])) {
            $memory_mb = round($res['max_memory_allocated'] / (1024 * 1024), 1);
            $sub_tests[] = array(
                'name' => 'Memory Allocation',
                'description' => 'Maximum memory allocation capability',
                'time' => '',
                'operations' => $memory_mb . 'MB allocated'
            );
        }
        
        // I/O Tests (4 tests - simulated based on overall I/O score)
        $io_score = $res['io_score'] ?? 0;
        if ($io_score > 0) {
            $sub_tests[] = array(
                'name' => 'Small File Operations',
                'description' => 'Reading/writing small files (logs, cache)',
                'time' => '',
                'operations' => '100 files'
            );
            
            $sub_tests[] = array(
                'name' => 'Medium File Operations',
                'description' => 'WordPress uploads and plugin files',
                'time' => '',
                'operations' => '50 files'
            );
            
            $sub_tests[] = array(
                'name' => 'Large File Operations',
                'description' => 'Backup and export operations',
                'time' => '',
                'operations' => '10 files'
            );
            
            $sub_tests[] = array(
                'name' => 'Concurrent I/O',
                'description' => 'Multiple file operations simultaneously',
                'time' => '',
                'operations' => '25 parallel ops'
            );
        }
        
        // Network Tests (2 tests - simulated based on overall network score)
        $network_score = $res['network_score'] ?? 0;
        if ($network_score > 0) {
            $sub_tests[] = array(
                'name' => 'External HTTP Requests',
                'description' => 'API calls and external connections',
                'time' => '',
                'operations' => '20 requests'
            );
            
            $sub_tests[] = array(
                'name' => 'Network Connectivity',
                'description' => 'Overall network speed and reliability',
                'time' => '',
                'operations' => 'Speed test'
            );
        }
        
        return $sub_tests;
    }
    
    /**
     * Get business impact for database tests
     */
    private function get_database_business_impact($db) {
        $impacts = array();
        $score = $db['score'] ?? 0;
        
        if ($score >= 90) {
            $impacts[] = 'Lightning fast product searches';
            $impacts[] = 'Instant cart updates';
            $impacts[] = 'Quick order processing';
        } elseif ($score >= 70) {
            $impacts[] = 'Good database performance';
            $impacts[] = 'Efficient product queries';
            $impacts[] = 'Reliable data operations';
        } elseif ($score >= 50) {
            $impacts[] = 'Basic database functionality';
            $impacts[] = 'May slow with large catalogs';
        } else {
            $impacts[] = 'Database performance issues';
            $impacts[] = 'Slow product searches expected';
        }
        
        return $impacts;
    }
    
    /**
     * Get sub-tests for database tests
     */
    private function get_database_sub_tests($db) {
        $sub_tests = array();
        
        // Main Database Operations
        if (isset($db['insert_time'])) {
            $iterations = $db['test_iterations'] ?? 5;
            $timed_out = isset($db['timed_out_tests']['insert']) && $db['timed_out_tests']['insert'];
            $sub_tests[] = array(
                'name' => 'Data Creation (INSERT)',
                'description' => 'Adding new products and orders',
                'time' => $timed_out ? '<span style="color: #dc2626;">Timed Out</span>' : $this->format_time_for_display($db['insert_time']),
                'operations' => '500 records × ' . $iterations . ' iterations'
            );
        }
        
        if (isset($db['select_time'])) {
            $iterations = $db['test_iterations'] ?? 5;
            $timed_out = isset($db['timed_out_tests']['select']) && $db['timed_out_tests']['select'];
            $sub_tests[] = array(
                'name' => 'Data Retrieval (SELECT)',
                'description' => 'Product searches and listings',
                'time' => $timed_out ? '<span style="color: #dc2626;">Timed Out</span>' : $this->format_time_for_display($db['select_time']),
                'operations' => '2500 queries × ' . $iterations . ' iterations'
            );
        }
        
        if (isset($db['update_time'])) {
            $iterations = $db['test_iterations'] ?? 5;
            $timed_out = isset($db['timed_out_tests']['update']) && $db['timed_out_tests']['update'];
            $sub_tests[] = array(
                'name' => 'Data Updates (UPDATE)',
                'description' => 'Stock changes and modifications',
                'time' => $timed_out ? '<span style="color: #dc2626;">Timed Out</span>' : $this->format_time_for_display($db['update_time']),
                'operations' => '10 updates × ' . $iterations . ' iterations'
            );
        }
        
        // MySQL Function Performance Tests
        if (isset($db['mysql_crypto_time'])) {
            $timed_out = isset($db['timed_out_functions']['crypto']) && $db['timed_out_functions']['crypto'];
            $sub_tests[] = array(
                'name' => 'Crypto Functions',
                'description' => 'Encryption and hash operations',
                'time' => $timed_out ? '<span style="color: #dc2626;">Timed Out</span>' : $this->format_time_for_display($db['mysql_crypto_time'] * 1000),
                'operations' => '1,000 operations (single run)'
            );
        }
        
        if (isset($db['mysql_math_time'])) {
            $timed_out = isset($db['timed_out_functions']['math']) && $db['timed_out_functions']['math'];
            $sub_tests[] = array(
                'name' => 'Math Functions',
                'description' => 'Mathematical calculations',
                'time' => $timed_out ? '<span style="color: #dc2626;">Timed Out</span>' : $this->format_time_for_display($db['mysql_math_time'] * 1000),
                'operations' => '5,000 operations (single run)'
            );
        }
        
        if (isset($db['mysql_string_time'])) {
            $timed_out = isset($db['timed_out_functions']['string']) && $db['timed_out_functions']['string'];
            $sub_tests[] = array(
                'name' => 'String Functions',
                'description' => 'Text processing operations',
                'time' => $timed_out ? '<span style="color: #dc2626;">Timed Out</span>' : $this->format_time_for_display($db['mysql_string_time'] * 1000),
                'operations' => '3,000 operations (single run)'
            );
        }
        
        if (isset($db['mysql_datetime_time'])) {
            $timed_out = isset($db['timed_out_functions']['datetime']) && $db['timed_out_functions']['datetime'];
            $sub_tests[] = array(
                'name' => 'DateTime Functions',
                'description' => 'Date and time operations',
                'time' => $timed_out ? '<span style="color: #dc2626;">Timed Out</span>' : $this->format_time_for_display($db['mysql_datetime_time'] * 1000),
                'operations' => '5,000 operations (single run)'
            );
        }
        
        if (isset($db['mysql_aggregate_time'])) {
            $timed_out = isset($db['timed_out_functions']['aggregate']) && $db['timed_out_functions']['aggregate'];
            $sub_tests[] = array(
                'name' => 'Aggregate Functions',
                'description' => 'SUM, COUNT, AVG operations',
                'time' => $timed_out ? '<span style="color: #dc2626;">Timed Out</span>' : $this->format_time_for_display($db['mysql_aggregate_time'] * 1000),
                'operations' => '100 operations (single run, 1000 rows)'
            );
        }
        
        return $sub_tests;
    }
    
    /**
     * Get business impact for concurrency tests
     */
    private function get_concurrency_business_impact($conc) {
        $impacts = array();
        $score = $conc['score'] ?? 0;
        
        if ($score >= 90) {
            $impacts[] = '50+ customers can shop simultaneously';
            $impacts[] = 'No slowdowns during peak hours';
            $impacts[] = 'Excellent scaling under load';
        } elseif ($score >= 70) {
            $impacts[] = 'Good multi-user performance';
            $impacts[] = 'Handles moderate traffic well';
            $impacts[] = 'Minor degradation under load';
        } elseif ($score >= 50) {
            $impacts[] = 'Basic concurrent handling';
            $impacts[] = 'May slow with many users';
        } else {
            $impacts[] = 'Concurrency challenges detected';
            $impacts[] = 'Performance drops with multiple users';
        }
        
        return $impacts;
    }
    
    /**
     * Get sub-tests for concurrency tests
     */
    private function get_concurrency_sub_tests($conc) {
        $sub_tests = array();
        
        // Use the detailed results from concurrency test
        $detailed_results = $conc['detailed_results'] ?? array();
        
        if (isset($detailed_results['database'])) {
            $db_results = $detailed_results['database'];
            $db_timed_out = isset($conc['timed_out_tests']['database']) && $conc['timed_out_tests']['database'];
            $db_time = $db_timed_out 
                ? '<span style="color: #dc2626;">Timed Out</span>' 
                : (isset($db_results['total_time']) ? $this->format_time_for_display($db_results['total_time'] * 1000) : '');
            $db_operations = isset($db_results['total_operations']) ? $db_results['total_operations'] . ' operations' : '445 operations';
                
            $sub_tests[] = array(
                'name' => 'Database Concurrency',
                'description' => 'Multiple database operations simultaneously',
                'time' => $db_time,
                'operations' => $db_operations
            );
        }
        
        if (isset($detailed_results['http'])) {
            $http_results = $detailed_results['http'];
            $http_timed_out = isset($conc['timed_out_tests']['http']) && $conc['timed_out_tests']['http'];
            $http_time = $http_timed_out 
                ? '<span style="color: #dc2626;">Timed Out</span>' 
                : (isset($http_results['total_time']) ? $this->format_time_for_display($http_results['total_time'] * 1000) : '');
            $http_operations = isset($http_results['total_requests']) ? $http_results['total_requests'] . ' requests' : '4 requests';
                
            $sub_tests[] = array(
                'name' => 'HTTP Concurrency',
                'description' => 'Multiple HTTP requests simultaneously',
                'time' => $http_time,
                'operations' => $http_operations
            );
        }
        
        if (isset($detailed_results['memory'])) {
            $memory_results = $detailed_results['memory'];
            $memory_timed_out = isset($conc['timed_out_tests']['memory']) && $conc['timed_out_tests']['memory'];
            $memory_time = $memory_timed_out 
                ? '<span style="color: #dc2626;">Timed Out</span>' 
                : (isset($memory_results['total_time']) ? $this->format_time_for_display($memory_results['total_time'] * 1000) : '');
            $memory_operations = isset($memory_results['total_processes']) ? $memory_results['total_processes'] . ' processes' : '84 processes';
                
            $sub_tests[] = array(
                'name' => 'Memory Concurrency',
                'description' => 'Memory competition under load',
                'time' => $memory_time,
                'operations' => $memory_operations
            );
        }
        
        if (isset($detailed_results['file'])) {
            $file_results = $detailed_results['file'];
            $file_timed_out = isset($conc['timed_out_tests']['file']) && $conc['timed_out_tests']['file'];
            $file_time = $file_timed_out 
                ? '<span style="color: #dc2626;">Timed Out</span>' 
                : (isset($file_results['total_time']) ? $this->format_time_for_display($file_results['total_time'] * 1000) : '');
            $file_operations = isset($file_results['total_operations']) ? $file_results['total_operations'] . ' file ops' : '290 file ops';
                
            $sub_tests[] = array(
                'name' => 'File Concurrency',
                'description' => 'File system operations under load',
                'time' => $file_time,
                'operations' => $file_operations
            );
        }
        
        return $sub_tests;
    }
    
    /**
     * Calculate sub-test score from timing data
     */
    private function calculate_sub_score_from_time($time_ms, $optimal_ms = 100) {
        if ($time_ms <= $optimal_ms) {
            return 100;
        } elseif ($time_ms <= $optimal_ms * 2) {
            return round(100 - (($time_ms - $optimal_ms) / $optimal_ms) * 30);
        } elseif ($time_ms <= $optimal_ms * 5) {
            return round(70 - (($time_ms - $optimal_ms * 2) / ($optimal_ms * 3)) * 40);
        } else {
            return max(10, round(30 - (($time_ms - $optimal_ms * 5) / $optimal_ms) * 2));
        }
    }
    
    /**
     * Format time from milliseconds to user-friendly seconds format
     */
    private function format_time_for_display($time_ms) {
        if ($time_ms <= 0) {
            return '';
        }
        
        $seconds = $time_ms / 1000;
        
        if ($seconds < 0.01) {
            // For very fast times (under 10ms), show 3 decimal places
            return number_format($seconds, 3) . 's';
        } elseif ($seconds < 1) {
            // For sub-second times, show 2 decimal places
            return number_format($seconds, 2) . 's';
        } else {
            // For times over 1 second, show 1 decimal place
            return number_format($seconds, 1) . 's';
        }
    }
    
    /**
     * Render results using the new hosting evaluation card template
     * 
     * @param array $evaluation Complete evaluation results
     * @return string HTML output for all cards
     */
    public function render_evaluation_cards($evaluation) {
        $template_data = $this->transform_results_for_template($evaluation);
        $output = '';
        
        foreach ($template_data as $test_type => $args) {
            ob_start();
            include plugin_dir_path(__FILE__) . '../../templates/hosting-evaluation-card.php';
            $output .= ob_get_clean();
        }
        
        return $output;
    }
    
    /**
     * Get test configuration optimized for all hosting types
     * 
     * Uses proven VPS configuration that works well across different hosting providers.
     * No environment detection needed - single optimized configuration for reliability.
     * 
     * @since 1.0.6
     * @return array Test configuration parameters
     */
    private function get_test_configuration() {
        // Get WordPress execution time limit (80% for safety margin)
        $php_max_execution_time = (int) ini_get('max_execution_time');
        $safe_execution_time = $php_max_execution_time > 0 ? intval($php_max_execution_time * 0.8) : 20;
        
        // Optimized configuration based on VPS settings that work well universally
        $config = array(
            'test_iterations' => 8,  // Proven to work well across hosting types
            'cpu_math_iterations' => 50000,
            'cpu_conditional_iterations' => 500000,
            'memory_allocation_percentage' => 0.70,  // Conservative 70% allocation
            'db_insert_count' => 500,
            'max_test_time_per_section' => $safe_execution_time,  // WordPress-based timeout
            'enable_intensive_tests' => true,
            'test_mode' => 'optimized',
            'environment' => 'optimized'  // Single environment type
        );
        
        // Add PHP execution time info for frontend
        $config['php_max_execution_time'] = $php_max_execution_time;
        
        // Add custom test configuration if available
        $user_id = get_current_user_id();
        if ($user_id) {
            $custom_config = get_transient('divewp_test_config_' . $user_id);
            if ($custom_config) {
                if (!empty($custom_config['enabled_tests'])) {
                    $config['enabled_tests'] = $custom_config['enabled_tests'];
                }
                if (isset($custom_config['skip_network_requests'])) {
                    $config['skip_network_requests'] = $custom_config['skip_network_requests'];
                }
                $config['test_configuration_mode'] = $custom_config['mode'];
            }
        }
        
        // Set defaults if not configured
        if (!isset($config['enabled_tests'])) {
            // Default enabled tests if no custom configuration
            $config['enabled_tests'] = array(
                // Main test suites
                'run_performance_tests', 'run_database_tests', 'run_concurrency_tests',
                // Resource tests (detailed sub-tests)
                'test_memory_allocation_limits', 'test_prime_generation', 'test_mathematical_operations', 'run_memory_tests',
                'run_cpu_tests', 'test_conditional_logic', 'test_string_processing', 'test_array_operations',
                'run_wp_tests', 'run_io_test', 'run_network_test', 'test_transient_operations',
                'test_shortcode_processing', 'test_hook_execution', 'test_security_functions', 'finalize_test_results'
            );
            $config['test_configuration_mode'] = 'default';
        }
        
        return $config;
    }
    
    /**
     * Run minimal test to check if hosting can handle ANY test
     * 
     * @since 1.0.6
     * @return array Minimal test results
     */
    private function run_minimal_test() {
        $results = array(
            'environment' => 'optimized',
            'tests_completed' => array()
        );
        
        // Test 1: Can we do basic math?
        try {
            $start = microtime(true);
            $sum = 0;
            for ($i = 0; $i < 1000; $i++) {
                $sum += $i;
            }
            $time = microtime(true) - $start;
            $results['tests_completed'][] = 'basic_math';
            $results['math_time'] = round($time * 1000, 2) . 'ms';
        } catch (Exception $e) {
            // MySQL function tests are supplementary - don't fail if they error
        }
        
        // Test 2: Can we allocate 1MB?
        try {
            $test_string = str_repeat('A', 1024 * 1024); // 1MB
            $results['tests_completed'][] = 'memory_1mb';
            $results['memory_test'] = 'Can allocate 1MB';
            unset($test_string);
        } catch (Exception $e) {
            // MySQL function tests are supplementary - don't fail if they error
        }
        
        // Test 3: Can we query database?
        try {
            global $wpdb;
            $start = microtime(true);
            $result = $wpdb->get_var("SELECT 1");
            $time = microtime(true) - $start;
            if ($result == 1) {
                $results['tests_completed'][] = 'database_query';
                $results['db_time'] = round($time * 1000, 2) . 'ms';
            }
        } catch (Exception $e) {
            // MySQL function tests are supplementary - don't fail if they error
        }
        
        // Test 4: Can we create a small file?
        try {
            $upload_dir = wp_upload_dir();
            $test_file = $upload_dir['basedir'] . '/divewp-minimal-test.txt';
            $start = microtime(true);
            file_put_contents($test_file, 'test');
            $time = microtime(true) - $start;
            if (file_exists($test_file)) {
                unlink($test_file);
                $results['tests_completed'][] = 'file_write';
                $results['file_time'] = round($time * 1000, 2) . 'ms';
            }
        } catch (Exception $e) {
            // MySQL function tests are supplementary - don't fail if they error
        }
        
        $results['total_tests'] = count($results['tests_completed']);
        $results['success'] = $results['total_tests'] > 0;
        
        return $results;
    }
} 