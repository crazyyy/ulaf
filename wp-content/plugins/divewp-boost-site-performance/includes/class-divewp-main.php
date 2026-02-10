<?php
/**
 * Main DiveWP class
 *
 * This class initializes and manages all DiveWP functionality.
 * Security improvements added in 1.0.4:
 * - Path traversal prevention
 * - Standardized error handling
 * - Enhanced AJAX security
 * - XSS prevention
 * - Capability checks
 *
 * @package DiveWP
 * @since 1.0.0
 * @since 1.0.4 Added security improvements
 * @license GPL-2.0+
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    die(esc_html__('Direct access not permitted.', 'divewp-boost-site-performance'));
}

class DiveWP_Main {
    private $server_insights;
    private $security;
    private $timing_data = array();
    private $email_insights;
    private $email_logger;
    private $dashboard_overview;
    private $theme_builder;
    private $user_events;
    private $performance_checks;
    private $server_insights_new;
    private $db_insights;
    private $woocommerce_best_practices;
    private $seo_optimization;
    private $hosting;
    private $cron_jobs;
    private $ai_capabilities;

    /**
     * Abilities API integration instance
     *
     * @since 2.1.0
     * @var DiveWP_Abilities|null
     */
    private $abilities;

    /**
     * Constructor with security headers
     */
    public function __construct() {
        // Set security headers
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        
        if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
            divewp_debug_log('DiveWP Main: Constructor called');
        }
        
        $this->load_dependencies();
        $this->initialize_components();
        
        add_action('admin_bar_menu', array($this, 'add_admin_bar_button'), 100);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_bar_styles'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_admin_bar_styles'));
    }

    /**
     * Validate and load dependencies
     */
    private function load_dependencies() {
        if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
            divewp_debug_log('DiveWP Main: Loading dependencies');
        }
        
        // Core files first
        require_once DIVEWP_PLUGIN_DIR . 'includes/class-content-loader.php';
        require_once DIVEWP_PLUGIN_DIR . 'includes/class-dashboard-overview.php';
        
        // Feature files
        require_once DIVEWP_PLUGIN_DIR . 'includes/features/server-insights/class-server-insights-new.php';
        require_once DIVEWP_PLUGIN_DIR . 'includes/features/security-insights/class-security.php';
        require_once DIVEWP_PLUGIN_DIR . 'includes/features/theme-builder/class-theme-builder.php';
        require_once DIVEWP_PLUGIN_DIR . 'includes/features/woocommerce-best-practices/class-woocommerce-best-practices.php';
        require_once DIVEWP_PLUGIN_DIR . 'includes/features/seo-optimization/class-seo-optimization.php';
        require_once DIVEWP_PLUGIN_DIR . 'includes/features/email-communications/class-email-insights.php';
        require_once DIVEWP_PLUGIN_DIR . 'includes/features/email-communications/class-email-logger.php';
        require_once DIVEWP_PLUGIN_DIR . 'includes/features/user-events/class-user-events.php';
        require_once DIVEWP_PLUGIN_DIR . 'includes/features/performance-optimizations/class-performance-checks.php';
        require_once DIVEWP_PLUGIN_DIR . 'includes/features/db-insights/class-db-insights.php';
        require_once DIVEWP_PLUGIN_DIR . 'includes/features/hosting/class-hosting.php';
        require_once DIVEWP_PLUGIN_DIR . 'includes/features/cron-jobs/class-cron-jobs.php';
        require_once DIVEWP_PLUGIN_DIR . 'includes/features/class-ai-capabilities.php';

        // Abilities API integration (WordPress 6.9+)
        require_once DIVEWP_PLUGIN_DIR . 'includes/class-divewp-abilities.php';

        if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
            divewp_debug_log('DiveWP Main: Dependencies loaded');
        }
    }

    /**
     * Standardized error handling
     */
    private function handle_error($e, $context = '') {
        if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
            divewp_debug_log(sprintf('DiveWP %s Error: %s', $context, $e->getMessage()));
        }
        
        if (current_user_can('manage_options')) {
            add_action('admin_notices', function() use ($e, $context) {
                echo '<div class="notice notice-error"><p>';
                echo sprintf(
                    /* translators: 1: Error context, 2: Error message */
                    esc_html__('DiveWP %1$s Error: %2$s', 'divewp-boost-site-performance'),
                    esc_html($context),
                    esc_html($e->getMessage())
                );
                echo '</p></div>';
            });
        }
    }

    /**
     * Initialize components with error handling
     */
    private function initialize_components() {
        // Initialize cron logger FIRST - it needs to run during wp-cron.php execution
        // even without a logged-in user, so it must be outside the capability check
        $this->initialize_cron_logger();

        try {
            // Initialize Server Insights and Abilities early so MCP/Abilities API can expose tools even on non-admin requests.
            $this->server_insights_new = new DiveWP_Server_Insights_New();
            try {
                $this->abilities = new DiveWP_Abilities( $this->server_insights_new );
            } catch (Exception $e) {
                $this->handle_error($e, 'Abilities API Initialization');
            }

            // Ensure API access logging hooks are registered for REST/MCP requests.
            $this->maybe_boot_user_events_logger_for_rest();

            if (!current_user_can('manage_options')) {
                return;
            }

            // Initialize cron jobs first (needed for dashboard widget)
            $this->cron_jobs = new DiveWP_Cron_Jobs();

            // Initialize components with checks
            if (!class_exists('DiveWP_Dashboard_Overview')) {
                throw new Exception(__('Dashboard Overview component not found', 'divewp-boost-site-performance'));
            }
            $this->dashboard_overview = new DiveWP_Dashboard_Overview($this->cron_jobs);

            // Initialize components
            $this->security = new DiveWP_Security();
            $this->theme_builder = new DiveWP_Theme_Builder();
            $this->woocommerce_best_practices = new DiveWP_WooCommerce_Best_Practices();
            $this->seo_optimization = new DiveWP_SEO_Optimization();
            $this->email_insights = new DiveWP_Email_Insights();
            $this->email_logger = DiveWP_Email_Logger::get_instance();
            $this->performance_checks = new DiveWP_Performance_Checks();
            $this->db_insights = new DiveWP_DB_Insights();
            $this->hosting = new DiveWP_Hosting();
            $this->ai_capabilities = new DiveWP_AI_Capabilities();

            try {
                $this->user_events = DiveWP_User_Events::get_instance();
            } catch (Exception $e) {
                $this->handle_error($e, 'User Events Initialization');
            }

            // Initialize Abilities API integration (WordPress 6.9+)
            // Pass server_insights_new instance for ability handlers
            try {
                $this->abilities = new DiveWP_Abilities( $this->server_insights_new );
            } catch (Exception $e) {
                $this->handle_error($e, 'Abilities API Initialization');
            }

        } catch (Exception $e) {
            $this->handle_error($e, 'Component Initialization');
        }
    }

    /**
     * Detect REST-like requests early in the load process.
     *
     * REST_REQUEST can be defined later in the request lifecycle, so we also
     * check the request URI for wp-json.
     *
     * @since 2.1.2
     * @return bool
     */
    private function is_rest_like_request() {
        if (defined('REST_REQUEST') && REST_REQUEST) {
            return true;
        }

        if (function_exists('wp_is_json_request') && wp_is_json_request()) {
            return true;
        }

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Server variable used only for route detection, not output.
        $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
        return $uri !== '' && strpos($uri, '/wp-json/') !== false;
    }

    /**
     * Boot User Events logger for REST requests (e.g., MCP + app passwords).
     *
     * The full admin UI is still gated by manage_options; this only ensures the
     * logging hooks exist when WordPress processes REST authentication.
     *
     * @since 2.1.2
     * @return void
     */
    private function maybe_boot_user_events_logger_for_rest() {
        if (!$this->is_rest_like_request()) {
            return;
        }

        try {
            if (!class_exists('DiveWP_Event_Logger')) {
                require_once DIVEWP_PLUGIN_DIR . 'includes/features/user-events/class-event-logger.php';
            }

            if (class_exists('DiveWP_Event_Logger')) {
                DiveWP_Event_Logger::get_instance();
            }
        } catch (Exception $e) {
            if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
                divewp_debug_log('REST User Events Logger boot failed: ' . $e->getMessage(), 'error');
            }
        }
    }

    /**
     * Initialize cron logger separately
     * 
     * This must run even during wp-cron.php execution without a logged-in user,
     * so it's called before the capability check in initialize_components().
     *
     * @since 2.2.0
     * @return void
     */
    private function initialize_cron_logger() {
        try {
            // Load the cron logger class if not already loaded
            if (!class_exists('DiveWP_Cron_Logger')) {
                require_once DIVEWP_PLUGIN_DIR . 'includes/features/cron-jobs/class-cron-logger.php';
            }
            
            // Initialize the singleton - this sets up all the tracking hooks
            DiveWP_Cron_Logger::get_instance();
            
        } catch (Exception $e) {
            // Silent fail - don't break the site if logger fails
            if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
                divewp_debug_log('Cron Logger Initialization failed: ' . $e->getMessage(), 'error');
            }
        }
    }

    public function run() {
        if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
            divewp_debug_log('Run method called - version ' . DIVEWP_VERSION, 'debug');
            divewp_debug_log('DIVEWP_PLUGIN_URL: ' . DIVEWP_PLUGIN_URL, 'debug');
            divewp_debug_log('DIVEWP_PLUGIN_DIR: ' . DIVEWP_PLUGIN_DIR, 'debug');
        }
        
        add_action('admin_menu', array($this, 'add_menu_page'));
        
        // Properly enqueue all scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'), 99);
        
        if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
            divewp_debug_log('Run method completed', 'debug');
        }
    }

    public function add_menu_page() {
        add_menu_page(
            'DiveWP',
            'DiveWP',
            'manage_options',
            'divewp',
            array($this, 'render_main_page'),
            'dashicons-performance',
            2
        );
    }

    /**
     * Enqueue admin styles and scripts
     */
    public function enqueue_styles($hook) {
        // Add explicit file check for JS file
        $admin_js_file = DIVEWP_PLUGIN_DIR . 'assets/js/divewp-admin.js';
        $admin_js_url = DIVEWP_PLUGIN_URL . 'assets/js/divewp-admin.js';
        
        // Check if the file exists
        if (!file_exists($admin_js_file)) {
            // Log error for missing file
            if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
                divewp_debug_log('ERROR: Main JS file not found at: ' . $admin_js_file, 'error');
            }
            
            // Add admin notice about missing file
            add_action('admin_notices', function() use ($admin_js_file) {
                echo '<div class="notice notice-error"><p>';
                echo sprintf(
                    /* translators: %s: File path */
                    esc_html__('DiveWP Error: JavaScript file not found at %s', 'divewp-boost-site-performance'),
                    esc_html($admin_js_file)
                );
                echo '</p></div>';
            });
        } else {
            if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
                divewp_debug_log('JS file exists at: ' . $admin_js_file, 'debug');
            }
        }
        
        if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
            divewp_debug_log('Enqueue called with hook: ' . $hook, 'debug');
            divewp_debug_log('DIVEWP_PLUGIN_URL: ' . DIVEWP_PLUGIN_URL, 'debug');
        }
        
        // Skip nonce verification for the initial page load to ensure JS files load
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Initial page load must load JS without nonce
        if (!$this->verify_admin_access() && 'toplevel_page_divewp' !== $hook) {
            if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
                divewp_debug_log('Admin access verification failed or hook not matching in enqueue_styles', 'error');
            }
            return;
        }

        if ('toplevel_page_divewp' !== $hook) {
            if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
                divewp_debug_log('Hook does not match toplevel_page_divewp: ' . $hook, 'debug');
            }
            return;
        }

        if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
            divewp_debug_log('Loading scripts and styles for divewp page', 'debug');
        }

        // Core CSS files
        wp_enqueue_style(
            'divewp-global',
            DIVEWP_PLUGIN_URL . 'assets/css/divewp-global.css',
            array(),
            DIVEWP_VERSION
        );

        wp_enqueue_style(
            'divewp-style',
            DIVEWP_PLUGIN_URL . 'assets/css/style.css',
            array('divewp-global'),
            DIVEWP_VERSION
        );

        // Feature-specific CSS files
        wp_enqueue_style(
            'divewp-dashboard',
            DIVEWP_PLUGIN_URL . 'assets/css/features/dashboard.css',
            array('divewp-style'),
            DIVEWP_VERSION
        );

        wp_enqueue_style(
            'divewp-user-events',
            DIVEWP_PLUGIN_URL . 'assets/css/features/user-events.css',
            array('divewp-style'),
            DIVEWP_VERSION
        );

        wp_enqueue_style(
            'divewp-timeline',
            DIVEWP_PLUGIN_URL . 'assets/css/features/timeline.css',
            array('divewp-style'),
            DIVEWP_VERSION
        );

        // Email communications CSS with proper dependencies
        wp_enqueue_style(
            'divewp-email',
            DIVEWP_PLUGIN_URL . 'assets/css/features/email-communications.css',
            array('divewp-style', 'divewp-global'),
            DIVEWP_VERSION
        );

        wp_enqueue_style(
            'divewp-hosting-benchmark',
            DIVEWP_PLUGIN_URL . 'assets/css/features/hosting-benchmark.css',
            array('divewp-style'),
            DIVEWP_VERSION
        );

        // Hosting evaluation styles (for card templates)
        wp_enqueue_style(
            'divewp-hosting-evaluation',
            DIVEWP_PLUGIN_URL . 'assets/css/features/hosting-evaluation.css',
            array('divewp-style'),
            DIVEWP_VERSION
        );

        // Hosting feature tabs styles
        wp_enqueue_style(
            'divewp-hosting',
            DIVEWP_PLUGIN_URL . 'assets/css/features/hosting.css',
            array('divewp-style'),
            DIVEWP_VERSION
        );
        
        // Ensure jQuery is properly loaded
        wp_enqueue_script('jquery');
        
        // Main admin script
        wp_register_script(
            'divewp-admin-js',
            DIVEWP_PLUGIN_URL . 'assets/js/divewp-admin.js',
            array('jquery'),
            DIVEWP_VERSION,
            true // Load in footer
        );
        
        wp_enqueue_script('divewp-admin-js');
        
        // Localize script data
        wp_localize_script('divewp-admin-js', 'divewpData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('divewp_nonce'),
            'testEmailFailed' => __('Failed to send test email', 'divewp-boost-site-performance'),
            'preloader' => array(
                'minDuration' => 1000,
                'maxDuration' => 10000,
                'text' => __('Checking your site...', 'divewp-boost-site-performance'),
                'timeoutText' => __('Taking longer than usual...', 'divewp-boost-site-performance')
            )
        ));

        // Test JS file removed - no longer needed
        
        // Dashboard counter script
        wp_enqueue_script(
            'divewp-dashboard-counter',
            DIVEWP_PLUGIN_URL . 'assets/js/dashboard-counter.js',
            array('jquery'),
            DIVEWP_VERSION,
            true
        );
        
        // Recommendations script
        wp_enqueue_script(
            'divewp-recommendations',
            DIVEWP_PLUGIN_URL . 'assets/js/recommendations.js',
            array('jquery', 'divewp-admin-js'),
            DIVEWP_VERSION,
            true
        );
        
        // Hosting benchmark script
        wp_enqueue_script(
            'divewp-hosting-benchmark',
            DIVEWP_PLUGIN_URL . 'assets/js/divewp-hosting-benchmark.js',
            array('jquery', 'divewp-admin-js'),
            DIVEWP_VERSION,
            true
        );
        
        // Add inline debug script if debugging is enabled
        if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
            wp_add_inline_script('divewp-admin-js', 'console.log("DiveWP scripts loaded properly");');
        }
    }

    /**
     * Enqueue only styles for non-plugin pages
     */
    public function enqueue_styles_only($hook) {
        if ('toplevel_page_divewp' === $hook) {
            return; // Full enqueue handled by enqueue_styles
        }
        
        // Only load minimal styles on other admin pages
        wp_enqueue_style(
            'divewp-global',
            DIVEWP_PLUGIN_URL . 'assets/css/divewp-global.css',
            array(),
            DIVEWP_VERSION
        );
    }

    /**
     * Block all WordPress notices on our admin page
     */
    private function block_all_notices() {
        // Remove all notice hooks - but be careful not to remove script actions
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
        remove_all_actions('user_admin_notices');
        remove_all_actions('network_admin_notices');
        
        // Prevent notices from being re-added
        add_action('admin_notices', '__return_false', PHP_INT_MAX);
        add_action('all_admin_notices', '__return_false', PHP_INT_MAX);
        
        // Start output buffering to catch any direct echoed notices
        // But DO NOT filter any script tags to avoid interfering with JS loading
        add_action('admin_head', function() {
            ob_start(function($buffer) {
                // Only remove divs with notice classes, not scripts
                $patterns = array(
                    '/<div[^>]*class="[^"]*notice[^"]*".*?<\/div>/s',
                    '/<div[^>]*class="[^"]*update[^"]*".*?<\/div>/s',
                    '/<div[^>]*class="[^"]*error[^"]*".*?<\/div>/s',
                    '/<div[^>]*class="[^"]*updated[^"]*".*?<\/div>/s'
                );
                
                // Skip filtering if the buffer contains script tags
                if (strpos($buffer, '<script') !== false) {
                    if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
                        divewp_debug_log('Buffer contains script tags, skipping notice filtering', 'debug');
                    }
                    return $buffer;
                }
                
                return preg_replace($patterns, '', $buffer);
            });
        }, 0);
        
        // Clean up buffer at the end
        add_action('admin_footer', function() {
            if (ob_get_level() > 0) {
                ob_end_flush();
            }
        }, PHP_INT_MAX);
    }

    /**
     * Render the main admin page
     */
    public function render_main_page() {
        // Verify user capabilities first
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'divewp-boost-site-performance'));
        }

        // Create nonce first
        $admin_nonce = wp_create_nonce('divewp_admin_page');
        
        // Block notices before anything else
        $this->block_all_notices();
        
        // Add debug marker
        $this->add_debug_marker();

        ?>
        <div class="wrap divewp-wrap">
            <?php 
            // Output the nonce field
            wp_nonce_field('divewp_admin_page', '_wpnonce');
            ?>
            
            <div class="divewp-container">
                <?php 
                if (file_exists(DIVEWP_PLUGIN_DIR . 'includes/admin/templates/admin-left-sidebar.php')) {
                    require DIVEWP_PLUGIN_DIR . 'includes/admin/templates/admin-left-sidebar.php';
                } else {
                    if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
                        divewp_debug_log('Left sidebar template not found');
                    }
                }
                ?>

                <div class="divewp-main-content">
                    <?php
                    $start_time = microtime(true);
                    
                    // 1. Dashboard / Welcome
                    $tab_start = microtime(true);
                    echo '<div class="divewp-tab-content active" id="welcome">';
                    echo '<h1>' . esc_html__('diveWP - Boost WordPress Performance with Clear, Actionable Steps', 'divewp-boost-site-performance') . '</h1>';
                    
                    if (file_exists(DIVEWP_PLUGIN_DIR . 'includes/admin/templates/admin-page.php')) {
                        require DIVEWP_PLUGIN_DIR . 'includes/admin/templates/admin-page.php';
                    }

                    if (isset($this->dashboard_overview)) {
                        $this->dashboard_overview->render_overview_cards();
                    }
                    echo '</div>';
                    $this->log_timing('Welcome Tab', microtime(true) - $tab_start);

                    // --- Utilities & Monitoring Section ---

                    // 2. Hosting Benchmark
                    $tab_start = microtime(true);
                    echo '<div class="divewp-tab-content" id="hosting">';
                    if (isset($this->hosting)) {
                         $this->hosting->render();
                    }
                    echo '</div>';
                    $this->log_timing('Hosting Tab', microtime(true) - $tab_start);

                    // 3. Cron Job Manager
                    $tab_start = microtime(true);
                    echo '<div class="divewp-tab-content" id="cron-jobs">';
                    if (isset($this->cron_jobs)) {
                         $this->cron_jobs->render();
                    }
                    echo '</div>';
                    $this->log_timing('Cron Jobs Tab', microtime(true) - $tab_start);

                    // 4. User Events
                    $tab_start = microtime(true);
                    echo '<div class="divewp-tab-content" id="user-events">';
                    if (isset($this->user_events)) {
                        $this->user_events->render_user_events_data($this->user_events->get_user_events_data());
                    }
                    echo '</div>';
                    $this->log_timing('User Events Tab', microtime(true) - $tab_start);

                    // 5. Email Communications
                    $tab_start = microtime(true);
                    echo '<div class="divewp-tab-content" id="email">';
                    if (isset($this->email_insights)) {
                        $this->email_insights->render();
                    }
                    echo '</div>';
                    $this->log_timing('Email Tab', microtime(true) - $tab_start);

                    // 6. AI Capabilities
                    $tab_start = microtime(true);
                    echo '<div class="divewp-tab-content" id="ai-capabilities">';
                    if (isset($this->ai_capabilities)) {
                         $this->ai_capabilities->render();
                    }
                    echo '</div>';
                    $this->log_timing('AI Capabilities Tab', microtime(true) - $tab_start);


                    // --- Analysis Section ---

                    // 7. Server Insights
                    $tab_start = microtime(true);
                    echo '<div class="divewp-tab-content" id="server-new">';
                    if (isset($this->server_insights_new)) {
                        $this->server_insights_new->render();
                    }
                    echo '</div>';
                    $this->log_timing('Server Insights Tab (New)', microtime(true) - $tab_start);
                    
                    // 8. Performance Checks
                    $tab_start = microtime(true);
                    echo '<div class="divewp-tab-content" id="performance-checks">';
                    if (isset($this->performance_checks)) {
                        $this->performance_checks->render();
                    }
                    echo '</div>';
                    $this->log_timing('Performance Checks Tab', microtime(true) - $tab_start);

                    // 9. Database Insights
                    $tab_start = microtime(true);
                    echo '<div class="divewp-tab-content" id="db-insights">';
                    if (isset($this->db_insights)) {
                        $this->db_insights->render();
                    }
                    echo '</div>';
                    $this->log_timing('DB Insights Tab', microtime(true) - $tab_start);

                    // 10. Security Insights
                    $tab_start = microtime(true);
                    echo '<div class="divewp-tab-content" id="security-new">';
                    if (isset($this->security)) {
                        $this->security->render();
                    }
                    echo '</div>';
                    $this->log_timing('Security Tab', microtime(true) - $tab_start);

                    // 11. Theme & Builder
                    $tab_start = microtime(true);
                    echo '<div class="divewp-tab-content" id="theme-builder">';
                    if (isset($this->theme_builder)) {
                        $this->theme_builder->render();
                    }
                    echo '</div>';
                    $this->log_timing('Theme & Builder Tab', microtime(true) - $tab_start);

                    // 12. WooCommerce Insights
                    $tab_start = microtime(true);
                    echo '<div class="divewp-tab-content" id="woocommerce-best-practices">';
                    if (isset($this->woocommerce_best_practices)) {
                        $this->woocommerce_best_practices->render();
                    }
                    echo '</div>';
                    $this->log_timing('WooCommerce Best Practices Tab', microtime(true) - $tab_start);

                    // 13. SEO Optimization
                    $tab_start = microtime(true);
                    echo '<div class="divewp-tab-content" id="seo-optimization">';
                    if (isset($this->seo_optimization)) {
                        $this->seo_optimization->render();
                    }
                    echo '</div>';
                    $this->log_timing('SEO Optimization Tab', microtime(true) - $tab_start);

                    // Log total time
                    $this->log_timing('Total Load Time', microtime(true) - $start_time);
                    ?>

                </div>
                <?php 
                if (file_exists(DIVEWP_PLUGIN_DIR . 'includes/admin/templates/admin-right-sidebar.php')) {
                    require DIVEWP_PLUGIN_DIR . 'includes/admin/templates/admin-right-sidebar.php';
                } else {
                    if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
                        divewp_debug_log('Right sidebar template not found');
                    }
                }
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Logs timing data for performance monitoring
     *
     * @since 1.0.4
     * @param string $action The name of the action being timed
     * @param float $time The time taken in seconds
     * @return void
     */
    private function log_timing($action, $time) {
        // Limit stored timing data
        if (count($this->timing_data) > 100) {
            array_shift($this->timing_data);
        }
        
        $this->timing_data[] = [
            'action' => $action,
            'time' => number_format($time * 1000, 2) . 'ms'
        ];
        
        // Cache timing data
        set_transient('divewp_timing_' . get_current_user_id(), $this->timing_data, HOUR_IN_SECONDS);
    }

    /**
     * Protected timing data output
     */
    private function output_timing_data() {
        if (!current_user_can('manage_options') || !defined('DIVEWP_DEBUG') || !DIVEWP_DEBUG) {
            return;
        }

        // Get cached timing data
        $cached_data = get_transient('divewp_timing_' . get_current_user_id());
        $timing_data = $cached_data ?: $this->timing_data;

        // Load from template if exists
        $template_path = DIVEWP_PLUGIN_DIR . 'includes/admin/templates/timing-data.php';
        if (file_exists($template_path)) {
            include $template_path;
            return;
        }

        // Fallback to direct output if template doesn't exist
        echo '<div class="timing-data">';
        echo '<h3>' . esc_html__('Loading Times', 'divewp-boost-site-performance') . '</h3>';
        echo '<table class="divewp-table">';
        foreach ($timing_data as $timing) {
            echo '<tr>';
            echo '<td>' . esc_html($timing['action']) . '</td>';
            echo '<td>' . esc_html($timing['time']) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '</div>';
    }

    /**
     * Adds the DiveWP button to the WordPress admin bar
     *
     * @since 1.0.0
     * @param WP_Admin_Bar $wp_admin_bar The WordPress admin bar object
     * @return void
     */
    public function add_admin_bar_button($wp_admin_bar) {
        if (!current_user_can('manage_options')) {
            return;
        }

        $wp_admin_bar->add_node(array(
            'id'    => 'divewp-button',
            'title' => '<img src="' . esc_url(DIVEWP_PLUGIN_URL . 'assets/images/diveWP.svg') . '" alt="' . esc_attr__('DiveWP', 'divewp-boost-site-performance') . '" class="divewp-admin-bar-logo">',
            'href'  => admin_url('admin.php?page=divewp'),
            'meta'  => array(
                'class' => 'divewp-admin-button',
                'title' => esc_attr__('Open DiveWP Dashboard', 'divewp-boost-site-performance')
            )
        ));
    }

    /**
     * Enqueues styles for the admin bar button
     *
     * @since 1.0.0
     * @return void
     */
    public function enqueue_admin_bar_styles() {
        wp_enqueue_style(
            'divewp-admin-bar',
            DIVEWP_PLUGIN_URL . 'assets/css/admin-bar-button.css',
            array(),
            DIVEWP_VERSION
        );
    }

    /**
     * Removes WordPress admin notices on DiveWP pages
     *
     * @since 1.0.4
     * @return void
     */
    public function remove_notices_on_divewp_page() {
        global $pagenow;
        
        // Verify we're on the DiveWP plugin page with valid nonce
        if (!isset($_GET['page']) || 
            $_GET['page'] !== 'divewp' || 
            !isset($_REQUEST['_wpnonce']) || 
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])), 'divewp_admin_page')) {
            return;
        }

        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
    }

    /**
     * Empty function to prevent notices from being shown
     *
     * @since 1.0.4
     * @return void
     */
    public function show_divewp_notices() {
        // Empty function - no notices will be shown
        return;
    }

    /**
     * Gets the list of valid features available in DiveWP
     *
     * @since 1.0.0
     * @return array List of valid feature identifiers
     */
    private function get_valid_features() {
        return array(
            'welcome',           // Dashboard overview
            'server-new',        // Server insights
            'security-new',      // Security insights
            'theme-builder',     // Theme builder
            'woocommerce-best-practices',
            'seo-optimization',
            'email',            // Email communications
            'user-events',      // User events
            'performance-checks',
            'db-insights',       // Database insights
            'cron-jobs',         // Cron jobs management
            'ai-capabilities'    // AI Capabilities & API Integration
        );
    }

    /**
     * Initializes plugin features
     *
     * @since 1.0.0
     * @return void
     */
    private function initialize_features() {
        $this->features['db_insights'] = new DiveWP_DB_Insights();
    }

    /**
     * Gets the list of available features with their metadata
     *
     * @since 1.0.0
     * @return array Array of feature configurations
     */
    public function get_features() {
        return array(
            'db_insights' => array(
                'title' => esc_html__('DB Insights', 'divewp-boost-site-performance'),
                'description' => esc_html__('Database health and optimization recommendations.', 'divewp-boost-site-performance'),
                'icon' => 'database'
            ),
        );
    }

    /**
     * Registers AJAX handlers for the plugin
     *
     * @since 1.0.4
     * @return void
     */
    private function register_ajax_handlers() {
        // Add nonce verification for all AJAX actions
        add_action('wp_ajax_divewp_update_settings', array($this, 'handle_settings_update'));
        add_action('wp_ajax_divewp_fetch_data', array($this, 'handle_data_fetch'));
        // ... any other AJAX handlers
    }

    /**
     * Handle settings update via AJAX
     */
    public function handle_settings_update() {
        // Verify nonce first
        // Nonces are cryptographically signed and validated by WordPress core
        // sanitize_text_field() is not appropriate for nonces as it may alter the signature
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(wp_unslash($_POST['_wpnonce']), 'divewp_ajax_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }

        // Process settings update
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }

        // Rest of the handler code...
    }

    /**
     * Handle data fetch via AJAX
     */
    public function handle_data_fetch() {
        // Verify nonce first
        // Nonces are cryptographically signed and validated by WordPress core
        // sanitize_text_field() is not appropriate for nonces as it may alter the signature
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(wp_unslash($_POST['_wpnonce']), 'divewp_ajax_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }

        // Process data fetch
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }

        // Rest of the handler code...
    }

    /**
     * Verify admin page access and nonce
     *
     * @since 1.0.4
     * @return bool Whether access is verified
     */
    private function verify_admin_access() {
        // Check capabilities first
        if (!current_user_can('manage_options')) {
            if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
                divewp_debug_log('User does not have manage_options capability', 'error');
            }
            return false;
        }

        // For initial page load (GET request without action), only verify user capability
        // This is necessary for initial script loading while maintaining security
        if (isset($_GET['page']) && !isset($_GET['action']) && empty($_POST)) {
            $page = sanitize_text_field(wp_unslash($_GET['page']));
            
            if ($page === 'divewp') {
                if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
                    divewp_debug_log('Initial page load verified for: ' . $page, 'debug');
                }
                return true;
            }
        }
        
        // Verify page parameter with nonce for GET requests with actions
        if (isset($_GET['page'])) {
            $page = sanitize_text_field(wp_unslash($_GET['page']));
            
            if ($page !== 'divewp') {
                if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
                    divewp_debug_log('Page is not divewp: ' . $page, 'error');
                }
                return false;
            }
            
            // Verify nonce if this is an admin action
            if (isset($_GET['action'])) {
                $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
                
                if (!wp_verify_nonce($nonce, 'divewp_admin_action')) {
                    if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
                        divewp_debug_log('Admin action nonce verification failed', 'error');
                    }
                    return false;
                }
            }
        }

        // For POST requests, always verify nonce
        if (!empty($_POST)) {
            // Nonces are cryptographically signed and validated by WordPress core
            // sanitize_text_field() is not appropriate for nonces as it may alter the signature
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            $nonce = isset($_POST['_wpnonce']) ? wp_unslash($_POST['_wpnonce']) : '';
            
            if (!wp_verify_nonce($nonce, 'divewp_admin_page')) {
                if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
                    divewp_debug_log('POST nonce verification failed', 'error');
                }
                return false;
            }
        }

        if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
            divewp_debug_log('Access verification passed', 'debug');
        }
        return true;
    }

    /**
     * Initialize admin features with error handling
     */
    private function initialize_admin_features() {
        try {
            if (!$this->verify_admin_access()) {
                return;
            }

            add_action('admin_menu', array($this, 'add_menu_page'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
            add_action('admin_bar_menu', array($this, 'add_admin_bar_button'), 100);
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_bar_styles'));
            add_action('wp_enqueue_scripts', array($this, 'enqueue_admin_bar_styles'));
            
            // Register AJAX handlers
            $this->register_ajax_handlers();

        } catch (Exception $e) {
            $this->handle_error($e, 'Admin Features');
        }
    }

    private function log_error($message, $context = '') {
        if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG && WP_DEBUG) {
            divewp_debug_log(
                sprintf(
                    'Dashboard Overview: %s - Context: %s',
                    $message,
                    $context
                ),
                'error'
            );
        }
    }

    public function enqueue_scripts() {
        wp_localize_script('divewp-admin', 'divewpAjax', array(
            'nonce' => wp_create_nonce('divewp_ajax_nonce')
        ));
    }

    /**
     * Check if scripts are registered but not printed
     */
    public function check_registered_scripts() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Simple page check, not processing form submission
        if (!is_admin() || !isset($_GET['page']) || sanitize_text_field(wp_unslash($_GET['page'])) !== 'divewp') {
            return;
        }
        
        global $wp_scripts;
        $registered_scripts = array();
        
        // Check if our scripts are registered
        if (isset($wp_scripts->registered['divewp-admin-js'])) {
            $registered_scripts[] = 'divewp-admin-js';
        }
        
        if (isset($wp_scripts->registered['divewp-test-js'])) {
            $registered_scripts[] = 'divewp-test-js';
        }
        
        if (isset($wp_scripts->registered['divewp-admin-direct'])) {
            $registered_scripts[] = 'divewp-admin-direct';
        }
        
        if (isset($wp_scripts->registered['divewp-test-emergency'])) {
            $registered_scripts[] = 'divewp-test-emergency';
        }
        
        if (isset($wp_scripts->registered['divewp-admin-emergency'])) {
            $registered_scripts[] = 'divewp-admin-emergency';
        }
        
        if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
            divewp_debug_log('Registered scripts: ' . implode(', ', $registered_scripts), 'debug');
        }
        
        // Output script registration status to console - this is required for debugging
        // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript -- Required for debugging, not loaded for display
        echo "<script>console.log('DiveWP registered scripts: " . esc_js(implode(', ', $registered_scripts)) . "');</script>";
    }
    
    /**
     * Inject scripts directly as a last resort for debugging
     * 
     * @deprecated since 1.0.5 Use wp_enqueue_script() instead
     */
    public function inject_scripts_directly() {
        // This method is kept for backwards compatibility but should not be used
        // All scripts should be loaded via wp_enqueue_script()
        
        if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
            divewp_debug_log('inject_scripts_directly is deprecated. Use wp_enqueue_script() instead.', 'warning');
        }
        
        return;
    }

    /**
     * Add a visible debug marker in the HTML for troubleshooting
     * 
     * This is only used during development and debugging
     */
    private function add_debug_marker() {
        if (!defined('DIVEWP_DEBUG') || !DIVEWP_DEBUG) {
            return;
        }
        
        // Add a marker that will be visible in View Source - ok for debugging
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Debug comments
        echo "<!-- DIVEWP DEBUG MARKER: Script loading test -->\n";
        
        // Add a visible element for debugging
        echo '<div id="divewp-debug-marker" style="display:none;">' . esc_html__('DiveWP Debug Marker', 'divewp-boost-site-performance') . '</div>';
        
        // Debug URLs as HTML comments - ok for debugging
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Debug comments
        echo "<!-- Debug URLs: -->\n";
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Debug comments
        echo "<!-- DIVEWP_PLUGIN_URL: " . esc_html(DIVEWP_PLUGIN_URL) . " -->\n";
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Debug comments
        echo "<!-- Site URL: " . esc_html(site_url()) . " -->\n";
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Debug comments
        echo "<!-- ABSPATH: " . esc_html(ABSPATH) . " -->\n";
    }
}