<?php
/**
 * Plugin Name: VigIA - AI Crawler Activity Analytics & Control
 * Plugin URI: https://servicios.ayudawp.com
 * Description: Track, analyze and block or control AI crawler visits to your WordPress site. Monitor GPTBot, ClaudeBot, PerplexityBot, and 50+ AI crawlers with detailed analytics, robots.txt management, email alerts, and llms.txt generation
 * Version: 1.4.1
 * Author: Fernando Tellado
 * Author URI: https://ayudawp.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: vigia
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Tested up to: 6.9
 *
 * @package VigIA
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin constants.
define( 'VIGIA_VERSION', '1.4.1' );
define( 'VIGIA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'VIGIA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'VIGIA_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main plugin class
 */
final class VigIA {

    /**
     * Single instance of the class
     *
     * @var VigIA
     */
    private static $instance = null;

    /**
     * Get single instance of the class
     *
     * @return VigIA
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Load required files
     */
    private function load_dependencies() {
        require_once VIGIA_PLUGIN_DIR . 'includes/class-database.php';
        require_once VIGIA_PLUGIN_DIR . 'includes/class-settings.php';
        require_once VIGIA_PLUGIN_DIR . 'includes/class-crawler-detector.php';
        require_once VIGIA_PLUGIN_DIR . 'includes/class-blocker.php';
        require_once VIGIA_PLUGIN_DIR . 'includes/class-robots-manager.php';
        require_once VIGIA_PLUGIN_DIR . 'includes/class-email-alerts.php';
        require_once VIGIA_PLUGIN_DIR . 'includes/class-llms-generator.php';
        require_once VIGIA_PLUGIN_DIR . 'includes/class-admin-page.php';
        require_once VIGIA_PLUGIN_DIR . 'includes/class-extras-page.php';
        require_once VIGIA_PLUGIN_DIR . 'includes/class-dashboard-widget.php';
        require_once VIGIA_PLUGIN_DIR . 'includes/class-rest-api.php';
        require_once VIGIA_PLUGIN_DIR . 'includes/class-abilities.php';
        require_once VIGIA_PLUGIN_DIR . 'includes/class-promo-banner.php';
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Activation and deactivation.
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        // Initialize blocker early (before tracking).
        add_action( 'plugins_loaded', array( 'VigIA_Blocker', 'init' ), 1 );

        // Initialize components.
        add_action( 'init', array( $this, 'init' ), 1 );
        add_action( 'admin_menu', array( 'VigIA_Admin_Page', 'register_menu' ) );
        add_action( 'admin_menu', array( 'VigIA_Extras_Page', 'register_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        add_action( 'wp_dashboard_setup', array( 'VigIA_Dashboard_Widget', 'register' ) );
        add_action( 'rest_api_init', array( 'VigIA_Rest_API', 'register_routes' ) );

        // Abilities API (WordPress 6.9+).
        if ( function_exists( 'wp_register_ability' ) ) {
            VigIA_Abilities::init();
        }

        // Scheduled tasks.
        add_action( 'vigia_daily_cleanup', array( 'VigIA_Settings', 'run_cleanup' ) );
        add_action( 'vigia_send_email_alerts', array( 'VigIA_Email_Alerts', 'send_scheduled_alerts' ) );
        add_action( 'vigia_llms_regenerate', array( 'VigIA_LLMS_Generator', 'cron_regenerate' ) );

        // Settings link in plugins page.
        add_filter( 'plugin_action_links_' . VIGIA_PLUGIN_BASENAME, array( $this, 'add_settings_link' ) );

        // Activation notice.
        add_action( 'admin_notices', array( $this, 'activation_notice' ) );
        add_action( 'admin_notices', array( $this, 'block_success_notice' ) );
        add_action( 'wp_ajax_vigia_dismiss_notice', array( $this, 'dismiss_notice' ) );

        // AJAX handlers.
        $this->register_ajax_handlers();
    }

    /**
     * Register AJAX handlers
     */
    private function register_ajax_handlers() {
        // Settings.
        add_action( 'wp_ajax_vigia_save_settings', array( $this, 'ajax_save_settings' ) );
        add_action( 'wp_ajax_vigia_delete_all_data', array( $this, 'ajax_delete_all_data' ) );
        add_action( 'wp_ajax_vigia_add_custom_crawler', array( $this, 'ajax_add_custom_crawler' ) );
        add_action( 'wp_ajax_vigia_remove_custom_crawler', array( $this, 'ajax_remove_custom_crawler' ) );
        add_action( 'wp_ajax_vigia_toggle_crawlers_box', array( $this, 'ajax_toggle_crawlers_box' ) );

        // Blocking.
        add_action( 'wp_ajax_vigia_block_crawler', array( $this, 'ajax_block_crawler' ) );
        add_action( 'wp_ajax_vigia_unblock_crawler', array( $this, 'ajax_unblock_crawler' ) );
        add_action( 'wp_ajax_vigia_unblock_by_id', array( $this, 'ajax_unblock_by_id' ) );

        // Robots.txt.
        add_action( 'wp_ajax_vigia_add_robots_rule', array( $this, 'ajax_add_robots_rule' ) );
        add_action( 'wp_ajax_vigia_remove_robots_rule', array( $this, 'ajax_remove_robots_rule' ) );
        add_action( 'wp_ajax_vigia_get_robots_content', array( $this, 'ajax_get_robots_content' ) );

        // Email alerts.
        add_action( 'wp_ajax_vigia_save_email_settings', array( $this, 'ajax_save_email_settings' ) );
        add_action( 'wp_ajax_vigia_test_email', array( $this, 'ajax_test_email' ) );

        // LLMs.txt.
        add_action( 'wp_ajax_vigia_generate_llms', array( $this, 'ajax_generate_llms' ) );
        add_action( 'wp_ajax_vigia_save_llms_settings', array( $this, 'ajax_save_llms_settings' ) );
        add_action( 'wp_ajax_vigia_delete_llms_files', array( $this, 'ajax_delete_llms_files' ) );

        // LLMs.txt - New handlers for v1.2.0.
        add_action( 'wp_ajax_vigia_search_posts', array( $this, 'ajax_search_posts' ) );
        add_action( 'wp_ajax_vigia_get_taxonomies', array( $this, 'ajax_get_taxonomies' ) );
    }

    /**
     * Plugin activation
     */
    public function activate() {
        VigIA_Database::create_tables();

        // Set activation notice flag.
        update_option( 'vigia_activation_notice', true );

        // Schedule daily cleanup.
        if ( ! wp_next_scheduled( 'vigia_daily_cleanup' ) ) {
            wp_schedule_event( time(), 'daily', 'vigia_daily_cleanup' );
        }

        // Schedule email alerts if enabled.
        VigIA_Email_Alerts::schedule_alerts();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        wp_clear_scheduled_hook( 'vigia_daily_cleanup' );
        wp_clear_scheduled_hook( 'vigia_send_email_alerts' );
        wp_clear_scheduled_hook( 'vigia_llms_regenerate' );
    }

    /**
     * Initialize crawler detection on every request
     */
    public function init() {
        // Only track on frontend requests.
        if ( ! is_admin() && ! wp_doing_ajax() && ! wp_doing_cron() && ! defined( 'REST_REQUEST' ) ) {
            VigIA_Crawler_Detector::track_request();
        }
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page hook.
     */
    public function enqueue_admin_assets( $hook ) {
        // Only load on plugin pages and dashboard.
        $plugin_pages = array( 'toplevel_page_vigia', 'vigia_page_vigia-extras', 'index.php' );
        if ( ! in_array( $hook, $plugin_pages, true ) ) {
            return;
        }

        // Load thickbox for plugin install modals.
        if ( 'toplevel_page_vigia' === $hook ) {
            add_thickbox();
        }

        wp_enqueue_style(
            'vigia-admin',
            VIGIA_PLUGIN_URL . 'assets/css/admin-styles.css',
            array(),
            VIGIA_VERSION
        );

        // Extras page styles.
        if ( 'vigia_page_vigia-extras' === $hook ) {
            wp_enqueue_style(
                'vigia-extras',
                VIGIA_PLUGIN_URL . 'assets/css/extras-styles.css',
                array( 'vigia-admin' ),
                VIGIA_VERSION
            );
        }

        wp_enqueue_script(
            'vigia-chart',
            VIGIA_PLUGIN_URL . 'assets/js/chart.min.js',
            array(),
            '4.5.0',
            true
        );

        wp_enqueue_script(
            'vigia-admin',
            VIGIA_PLUGIN_URL . 'assets/js/admin-scripts.js',
            array( 'jquery', 'vigia-chart' ),
            VIGIA_VERSION,
            true
        );

        // Extras page scripts.
        if ( 'vigia_page_vigia-extras' === $hook ) {
            wp_enqueue_script(
                'vigia-extras',
                VIGIA_PLUGIN_URL . 'assets/js/extras-scripts.js',
                array( 'jquery', 'vigia-admin' ),
                VIGIA_VERSION,
                true
            );
        }

        // Get current settings for JS.
        $settings = VigIA_Settings::get_settings();

        // Get category labels and colors for JS.
        $categories      = VigIA_Crawler_Detector::get_category_labels();
        $category_colors = VigIA_Crawler_Detector::get_category_colors();

        // Get blocking data for JS.
        $blocked_ua     = array();
        $blocked_ips    = array();
        $ua_blocks      = VigIA_Blocker::get_blocked_by_type( 'useragent' );
        $ip_blocks      = VigIA_Blocker::get_blocked_by_type( 'ip' );
        foreach ( $ua_blocks as $block ) {
            $blocked_ua[] = $block['name'];
        }
        foreach ( $ip_blocks as $block ) {
            $blocked_ips[] = $block['pattern'];
        }
        $robots_disallow = VigIA_Robots_Manager::get_ai_rules()['disallow'];

        wp_localize_script(
            'vigia-admin',
            'vigiaData',
            array(
                'restUrl'   => esc_url_raw( rest_url( 'vigia/v1/' ) ),
                'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
                'nonce'     => wp_create_nonce( 'wp_rest' ),
                'ajaxNonce' => wp_create_nonce( 'vigia_ajax_nonce' ),
                'settings'  => $settings,
                'extrasUrl' => admin_url( 'admin.php?page=vigia-extras' ),
                'strings'   => array(
                    'loading'             => __( 'Loading...', 'vigia' ),
                    'error'               => __( 'Error loading data', 'vigia' ),
                    'noData'              => __( 'No data available', 'vigia' ),
                    'requests'            => __( 'Requests', 'vigia' ),
                    'previousPeriod'      => __( 'Previous period', 'vigia' ),
                    'others'              => __( 'Others', 'vigia' ),
                    'exported'            => __( 'Data exported successfully', 'vigia' ),
                    'settingsSaved'       => __( 'Settings saved', 'vigia' ),
                    'confirmDelete'       => __( 'Are you sure you want to delete ALL crawler data? This action cannot be undone.', 'vigia' ),
                    'dataDeleted'         => __( 'All data has been deleted', 'vigia' ),
                    'crawlerAdded'        => __( 'Custom crawler added', 'vigia' ),
                    'crawlerRemoved'      => __( 'Custom crawler removed', 'vigia' ),
                    'confirmRemove'       => __( 'Are you sure you want to remove this custom crawler?', 'vigia' ),
                    /* translators: 1: number of items shown, 2: total number of items */
                    'showingOf'           => __( 'Showing %1$s of %2$s', 'vigia' ),
                    // Blocking action labels.
                    'addDisallow'         => __( 'Disallow', 'vigia' ),
                    'blockUA'             => __( 'Block User-Agent', 'vigia' ),
                    'blockIP'             => __( 'Block IP address', 'vigia' ),
                    // Block status labels.
                    'disallowed'          => __( 'Disallowed', 'vigia' ),
                    'uaBlocked'           => __( 'UA blocked', 'vigia' ),
                    'ipBlocked'           => __( 'IP blocked', 'vigia' ),
                    'fullyBlocked'        => __( 'Fully blocked', 'vigia' ),
                    'phpBlocked'          => __( 'PHP blocked', 'vigia' ),
                    'disallowedOnly'      => __( 'Disallowed in robots.txt', 'vigia' ),
                    'blockActions'        => __( 'Block actions', 'vigia' ),
                    // Notice messages.
                    'blockedVia'          => __( 'blocked via', 'vigia' ),
                    'manageInExtras'      => __( 'Manage in Extras', 'vigia' ),
                    'blocked'             => __( 'Blocked successfully', 'vigia' ),
                    'unblocked'           => __( 'Unblocked successfully', 'vigia' ),
                    'emailTestSent'       => __( 'Test email sent', 'vigia' ),
                    'llmsGenerated'       => __( 'LLMs files generated successfully', 'vigia' ),
                    'llmsDeleted'         => __( 'File deleted', 'vigia' ),
                    'confirmDeleteLlms'   => __( 'Are you sure you want to delete this file?', 'vigia' ),
                    'robotsRuleAdded'     => __( 'Robots.txt rule added', 'vigia' ),
                    'robotsRuleRemoved'   => __( 'Robots.txt rule removed', 'vigia' ),
                    // LLMs Generator v1.2.0 strings.
                    'selectCrawler'       => __( 'Please select a crawler', 'vigia' ),
                    'enterBothFields'     => __( 'Please enter both name and pattern', 'vigia' ),
                    'enterIP'             => __( 'Please enter an IP address', 'vigia' ),
                    'sending'             => __( 'Sending...', 'vigia' ),
                    'testEmailSent'       => __( 'Test email sent', 'vigia' ),
                    'noResults'           => __( 'No results found', 'vigia' ),
                    'manuallyAdded'       => __( 'Manually added', 'vigia' ),
                    'excluded'            => __( 'Excluded', 'vigia' ),
                    /* translators: 1: number of items, 2: details string */
                    'estimatedContent'    => __( 'Estimated content: %1$d items (%2$s)', 'vigia' ),
                    'selectContentTypes'  => __( 'Select content types to see estimated count.', 'vigia' ),
                    'siteNameRequired'    => __( 'Site name is required', 'vigia' ),
                    'selectContent'       => __( 'Please select at least one content type or add content manually', 'vigia' ),
                    'generating'          => __( 'Generating...', 'vigia' ),
                    'allIncluded'         => __( 'All included', 'vigia' ),
                    'allExcluded'         => __( 'All excluded', 'vigia' ),
                    /* translators: %d: number of excluded terms */
                    'excludedCount'       => __( '%d excluded', 'vigia' ),
                    'includeAll'          => __( 'Include all', 'vigia' ),
                    'excludeAll'          => __( 'Exclude all', 'vigia' ),
                    'uncheckToExclude'    => __( 'Uncheck to exclude specific terms', 'vigia' ),
                    // Period indicators (v1.3.0).
                    /* translators: %d: number of days */
                    'lastDays'            => __( 'Last %d days', 'vigia' ),
                    'today'               => __( 'Today', 'vigia' ),
                    'allTime'             => __( 'All time', 'vigia' ),
                    'loadMore'            => __( 'Load more', 'vigia' ),
                ),
            )
        );

        // Categories data for JS.
        wp_localize_script(
            'vigia-admin',
            'vigiaDataCategories',
            array(
                'labels' => $categories,
                'colors' => $category_colors,
            )
        );

        // Blocking data for JS.
        wp_localize_script(
            'vigia-admin',
            'vigiaBlockedCrawlers',
            $blocked_ua
        );
        wp_localize_script(
            'vigia-admin',
            'vigiaRobotsDisallow',
            $robots_disallow
        );
        wp_localize_script(
            'vigia-admin',
            'vigiaBlockedIPs',
            $blocked_ips
        );

        // Inline script for notice dismiss.
        $notice_script = "
            jQuery(document).ready(function($) {
                $('.vigia-activation-notice, .vigia-block-notice').on('click', '.notice-dismiss', function() {
                    var nonce = $(this).closest('.notice').data('nonce');
                    $.post(ajaxurl, {
                        action: 'vigia_dismiss_notice',
                        nonce: nonce
                    });
                });
            });
        ";
        wp_add_inline_script( 'vigia-admin', $notice_script );
    }

    /**
     * Add settings link to plugins page
     *
     * @param array $links Existing plugin action links.
     * @return array Modified links.
     */
    public function add_settings_link( $links ) {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            esc_url( admin_url( 'admin.php?page=vigia' ) ),
            esc_html__( 'Analytics', 'vigia' )
        );
        $extras_link = sprintf(
            '<a href="%s">%s</a>',
            esc_url( admin_url( 'admin.php?page=vigia-extras' ) ),
            esc_html__( 'Extras', 'vigia' )
        );
        array_unshift( $links, $extras_link );
        array_unshift( $links, $settings_link );
        return $links;
    }

    /**
     * Display activation notice
     */
    public function activation_notice() {
        if ( ! get_option( 'vigia_activation_notice' ) ) {
            return;
        }

        $screen = get_current_screen();
        if ( ! $screen || 'plugins' !== $screen->id ) {
            return;
        }

        $nonce = wp_create_nonce( 'vigia_dismiss_notice' );
        ?>
        <div class="notice notice-success is-dismissible vigia-activation-notice" data-nonce="<?php echo esc_attr( $nonce ); ?>">
            <p>
                <strong><?php esc_html_e( 'VigIA - AI Crawler Activity Analytics & Control is now active!', 'vigia' ); ?></strong>
                <?php esc_html_e( 'The plugin is now tracking AI crawler visits to your site. A summary widget has been added to your Dashboard.', 'vigia' ); ?>
            </p>
            <p>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=vigia' ) ); ?>" class="button button-primary">
                    <?php esc_html_e( 'View AI Analytics', 'vigia' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=vigia-extras' ) ); ?>" class="button button-secondary" style="margin-left: 8px;">
                    <?php esc_html_e( 'Configure Extras', 'vigia' ); ?>
                </a>
            </p>
        </div>
        <script>
        jQuery(document).ready(function($) {
            $('.vigia-activation-notice').on('click', '.notice-dismiss', function() {
                $.post(ajaxurl, {
                    action: 'vigia_dismiss_notice',
                    nonce: '<?php echo esc_js( $nonce ); ?>'
                });
            });
        });
        </script>
        <?php
    }

    /**
     * Display block success notice
     */
    public function block_success_notice() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display only, no data processing.
        if ( ! isset( $_GET['vigia_blocked'] ) ) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $crawler = sanitize_text_field( wp_unslash( $_GET['vigia_blocked'] ) );
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $method = isset( $_GET['method'] ) ? sanitize_key( wp_unslash( $_GET['method'] ) ) : 'php';

        $method_label = 'robots' === $method ? 'robots.txt' : 'PHP';

        ?>
        <div class="notice notice-success is-dismissible vigia-block-notice" data-nonce="<?php echo esc_attr( wp_create_nonce( 'vigia_dismiss_notice' ) ); ?>">
            <p>
                <strong><?php echo esc_html( $crawler ); ?></strong>
                <?php
                printf(
                    /* translators: %s: blocking method (robots.txt or PHP) */
                    esc_html__( 'has been blocked via %s.', 'vigia' ),
                    esc_html( $method_label )
                );
                ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=vigia-extras' ) ); ?>">
                    <?php esc_html_e( 'Manage blocking rules in Extras', 'vigia' ); ?>
                </a>
            </p>
        </div>
        <?php
    }

    /**
     * Dismiss activation notice via AJAX
     */
    public function dismiss_notice() {
        check_ajax_referer( 'vigia_dismiss_notice', 'nonce' );
        delete_option( 'vigia_activation_notice' );
        wp_die();
    }

    /**
     * AJAX: Save settings
     */
    public function ajax_save_settings() {
        check_ajax_referer( 'vigia_ajax_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized', 'vigia' ) );
        }

        $retention_days      = isset( $_POST['retention_days'] ) ? absint( $_POST['retention_days'] ) : 0;
        $delete_on_uninstall = isset( $_POST['delete_on_uninstall'] ) && 'true' === $_POST['delete_on_uninstall'];

        VigIA_Settings::update_settings(
            array(
                'retention_days'      => $retention_days,
                'delete_on_uninstall' => $delete_on_uninstall,
            )
        );

        wp_send_json_success();
    }

    /**
     * AJAX: Delete all data
     */
    public function ajax_delete_all_data() {
        check_ajax_referer( 'vigia_ajax_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized', 'vigia' ) );
        }

        VigIA_Settings::delete_all_data();

        wp_send_json_success();
    }

    /**
     * AJAX: Add custom crawler
     */
    public function ajax_add_custom_crawler() {
        check_ajax_referer( 'vigia_ajax_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized', 'vigia' ) );
        }

        $user_agent = isset( $_POST['user_agent'] ) ? sanitize_text_field( wp_unslash( $_POST['user_agent'] ) ) : '';
        $name       = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
        $company    = isset( $_POST['company'] ) ? sanitize_text_field( wp_unslash( $_POST['company'] ) ) : '';
        $category   = isset( $_POST['category'] ) ? sanitize_key( wp_unslash( $_POST['category'] ) ) : 'other';

        if ( empty( $user_agent ) || empty( $name ) ) {
            wp_send_json_error( __( 'User agent and name are required', 'vigia' ) );
        }

        VigIA_Settings::add_custom_crawler( $user_agent, $name, $company, $category );

        $crawlers = VigIA_Settings::get_custom_crawlers();
        wp_send_json_success( array( 'crawlers' => $crawlers ) );
    }

    /**
     * AJAX: Remove custom crawler
     */
    public function ajax_remove_custom_crawler() {
        check_ajax_referer( 'vigia_ajax_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized', 'vigia' ) );
        }

        $crawler_id = isset( $_POST['crawler_id'] ) ? sanitize_key( wp_unslash( $_POST['crawler_id'] ) ) : '';

        if ( empty( $crawler_id ) ) {
            wp_send_json_error( __( 'Crawler ID is required', 'vigia' ) );
        }

        VigIA_Settings::remove_custom_crawler( $crawler_id );

        $crawlers = VigIA_Settings::get_custom_crawlers();
        wp_send_json_success( array( 'crawlers' => $crawlers ) );
    }

    /**
     * AJAX: Toggle crawlers box collapsed state
     */
    public function ajax_toggle_crawlers_box() {
        check_ajax_referer( 'vigia_ajax_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized', 'vigia' ) );
        }

        $collapsed = isset( $_POST['collapsed'] ) && 'true' === $_POST['collapsed'];
        VigIA_Settings::update( 'crawlers_box_collapsed', $collapsed );

        wp_send_json_success();
    }

    /**
     * AJAX: Block crawler (User-Agent or IP)
     */
    public function ajax_block_crawler() {
        check_ajax_referer( 'vigia_ajax_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized', 'vigia' ) );
        }

        $block_type = isset( $_POST['block_type'] ) ? sanitize_key( wp_unslash( $_POST['block_type'] ) ) : 'useragent';
        $method     = isset( $_POST['method'] ) ? sanitize_key( wp_unslash( $_POST['method'] ) ) : 'php';

        // Handle robots.txt disallow (backwards compatible).
        if ( 'robots' === $method || 'disallow' === $block_type ) {
            $crawler_name = isset( $_POST['crawler_name'] ) ? sanitize_text_field( wp_unslash( $_POST['crawler_name'] ) ) : '';
            if ( empty( $crawler_name ) ) {
                wp_send_json_error( __( 'Crawler name is required', 'vigia' ) );
            }
            VigIA_Robots_Manager::add_disallow( $crawler_name );

            wp_send_json_success(
                array(
                    'message'   => __( 'Crawler added to robots.txt Disallow', 'vigia' ),
                    'extrasUrl' => admin_url( 'admin.php?page=vigia-extras' ),
                )
            );
            return;
        }

        // Handle IP block.
        if ( 'ip' === $block_type ) {
            $ip   = isset( $_POST['ip'] ) ? sanitize_text_field( wp_unslash( $_POST['ip'] ) ) : '';
            $name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';

            if ( empty( $ip ) ) {
                wp_send_json_error( __( 'IP address is required', 'vigia' ) );
            }

            if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
                wp_send_json_error( __( 'Invalid IP address', 'vigia' ) );
            }

            $result = VigIA_Blocker::add_ip_block( $name, $ip );

            if ( ! $result ) {
                wp_send_json_error( __( 'IP is already blocked', 'vigia' ) );
            }

            wp_send_json_success(
                array(
                    'message'   => __( 'IP address blocked', 'vigia' ),
                    'extrasUrl' => admin_url( 'admin.php?page=vigia-extras' ),
                )
            );
            return;
        }

        // Handle User-Agent block.
        $crawler_name = isset( $_POST['crawler_name'] ) ? sanitize_text_field( wp_unslash( $_POST['crawler_name'] ) ) : '';
        $user_agent   = isset( $_POST['user_agent'] ) ? sanitize_text_field( wp_unslash( $_POST['user_agent'] ) ) : '';

        if ( empty( $user_agent ) && empty( $crawler_name ) ) {
            wp_send_json_error( __( 'User-Agent pattern is required', 'vigia' ) );
        }

        $pattern = ! empty( $user_agent ) ? $user_agent : $crawler_name;
        $name    = ! empty( $crawler_name ) ? $crawler_name : $user_agent;

        $result = VigIA_Blocker::add_useragent_block( $name, $pattern );

        if ( ! $result ) {
            wp_send_json_error( __( 'User-Agent is already blocked', 'vigia' ) );
        }

        wp_send_json_success(
            array(
                'message'   => __( 'User-Agent blocked', 'vigia' ),
                'extrasUrl' => admin_url( 'admin.php?page=vigia-extras' ),
            )
        );
    }

    /**
     * AJAX: Unblock crawler
     */
    public function ajax_unblock_crawler() {
        check_ajax_referer( 'vigia_ajax_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized', 'vigia' ) );
        }

        // New method: unblock by ID.
        $block_id = isset( $_POST['block_id'] ) ? sanitize_text_field( wp_unslash( $_POST['block_id'] ) ) : '';

        if ( ! empty( $block_id ) ) {
            VigIA_Blocker::remove_block( $block_id );
            wp_send_json_success( array( 'message' => __( 'Block removed', 'vigia' ) ) );
            return;
        }

        // Legacy method: unblock by crawler name.
        $crawler_name = isset( $_POST['crawler_name'] ) ? sanitize_text_field( wp_unslash( $_POST['crawler_name'] ) ) : '';
        $method       = isset( $_POST['method'] ) ? sanitize_key( wp_unslash( $_POST['method'] ) ) : 'php';

        if ( empty( $crawler_name ) ) {
            wp_send_json_error( __( 'Crawler name or block ID is required', 'vigia' ) );
        }

        if ( 'robots' === $method ) {
            VigIA_Robots_Manager::remove_disallow( $crawler_name );
        } else {
            VigIA_Blocker::remove_blocked_crawler( $crawler_name );
        }

        wp_send_json_success( array( 'message' => __( 'Block removed', 'vigia' ) ) );
    }

    /**
     * AJAX: Unblock by ID (new method)
     */
    public function ajax_unblock_by_id() {
        check_ajax_referer( 'vigia_ajax_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized', 'vigia' ) );
        }

        $block_id = isset( $_POST['block_id'] ) ? sanitize_text_field( wp_unslash( $_POST['block_id'] ) ) : '';

        if ( empty( $block_id ) ) {
            wp_send_json_error( __( 'Block ID is required', 'vigia' ) );
        }

        VigIA_Blocker::remove_block( $block_id );

        wp_send_json_success( array( 'message' => __( 'Block removed', 'vigia' ) ) );
    }

    /**
     * AJAX: Add robots.txt rule
     */
    public function ajax_add_robots_rule() {
        check_ajax_referer( 'vigia_ajax_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized', 'vigia' ) );
        }

        $crawler_name = isset( $_POST['crawler_name'] ) ? sanitize_text_field( wp_unslash( $_POST['crawler_name'] ) ) : '';
        $action_type  = isset( $_POST['action_type'] ) ? sanitize_key( wp_unslash( $_POST['action_type'] ) ) : 'disallow';

        if ( empty( $crawler_name ) ) {
            wp_send_json_error( __( 'Crawler name is required', 'vigia' ) );
        }

        if ( 'allow' === $action_type ) {
            VigIA_Robots_Manager::add_allow( $crawler_name );
        } else {
            VigIA_Robots_Manager::add_disallow( $crawler_name );
        }

        wp_send_json_success(
            array(
                'rules'   => VigIA_Robots_Manager::get_ai_rules(),
                'preview' => VigIA_Robots_Manager::get_preview(),
            )
        );
    }

    /**
     * AJAX: Remove robots.txt rule
     */
    public function ajax_remove_robots_rule() {
        check_ajax_referer( 'vigia_ajax_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized', 'vigia' ) );
        }

        $crawler_name = isset( $_POST['crawler_name'] ) ? sanitize_text_field( wp_unslash( $_POST['crawler_name'] ) ) : '';
        $action_type  = isset( $_POST['action_type'] ) ? sanitize_key( wp_unslash( $_POST['action_type'] ) ) : 'disallow';

        if ( empty( $crawler_name ) ) {
            wp_send_json_error( __( 'Crawler name is required', 'vigia' ) );
        }

        if ( 'allow' === $action_type ) {
            VigIA_Robots_Manager::remove_allow( $crawler_name );
        } else {
            VigIA_Robots_Manager::remove_disallow( $crawler_name );
        }

        wp_send_json_success(
            array(
                'rules'   => VigIA_Robots_Manager::get_ai_rules(),
                'preview' => VigIA_Robots_Manager::get_preview(),
            )
        );
    }

    /**
     * AJAX: Get robots.txt content
     */
    public function ajax_get_robots_content() {
        check_ajax_referer( 'vigia_ajax_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized', 'vigia' ) );
        }

        wp_send_json_success(
            array(
                'content' => VigIA_Robots_Manager::get_current_robots(),
                'preview' => VigIA_Robots_Manager::get_preview(),
            )
        );
    }

    /**
     * AJAX: Save email settings
     */
    public function ajax_save_email_settings() {
        check_ajax_referer( 'vigia_ajax_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized', 'vigia' ) );
        }

        $enabled   = isset( $_POST['enabled'] ) && 'true' === $_POST['enabled'];
        $frequency = isset( $_POST['frequency'] ) ? sanitize_key( wp_unslash( $_POST['frequency'] ) ) : 'weekly';
        $level     = isset( $_POST['level'] ) ? sanitize_key( wp_unslash( $_POST['level'] ) ) : 'normal';
        $email     = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

        VigIA_Email_Alerts::save_settings(
            array(
                'enabled'   => $enabled,
                'frequency' => $frequency,
                'level'     => $level,
                'email'     => $email,
            )
        );

        // Reschedule alerts based on new settings.
        VigIA_Email_Alerts::schedule_alerts();

        wp_send_json_success();
    }

    /**
     * AJAX: Test email
     */
    public function ajax_test_email() {
        check_ajax_referer( 'vigia_ajax_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized', 'vigia' ) );
        }

        $result = VigIA_Email_Alerts::send_test_email();

        if ( $result ) {
            wp_send_json_success();
        } else {
            wp_send_json_error( __( 'Failed to send test email', 'vigia' ) );
        }
    }

    /**
     * AJAX: Generate LLMs files (v1.2.0 - updated with new settings structure)
     */
    public function ajax_generate_llms() {
        check_ajax_referer( 'vigia_ajax_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized', 'vigia' ) );
        }

        // Build settings array directly from POST (no merging with old values).
        $settings = array(
            'site_name'        => isset( $_POST['site_name'] ) ? sanitize_text_field( wp_unslash( $_POST['site_name'] ) ) : '',
            'site_description' => isset( $_POST['site_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['site_description'] ) ) : '',
            'post_types'       => isset( $_POST['post_types'] ) ? array_map( 'sanitize_key', (array) $_POST['post_types'] ) : array(),
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized below.
            'taxonomy_filters' => isset( $_POST['taxonomy_filters'] ) ? $this->sanitize_taxonomy_filters( wp_unslash( $_POST['taxonomy_filters'] ) ) : array(),
            'manual_includes'  => isset( $_POST['manual_includes'] ) ? array_map( 'absint', (array) $_POST['manual_includes'] ) : array(),
            'manual_excludes'  => isset( $_POST['manual_excludes'] ) ? array_map( 'absint', (array) $_POST['manual_excludes'] ) : array(),
            'exclude_patterns' => isset( $_POST['exclude_patterns'] ) ? sanitize_textarea_field( wp_unslash( $_POST['exclude_patterns'] ) ) : '',
            'exclude_noindex'  => isset( $_POST['exclude_noindex'] ) && 'true' === $_POST['exclude_noindex'],
            'generate_full'    => isset( $_POST['generate_full'] ) && 'true' === $_POST['generate_full'],
            'full_mode'        => isset( $_POST['full_mode'] ) ? sanitize_key( wp_unslash( $_POST['full_mode'] ) ) : 'full',
            'auto_regenerate'  => isset( $_POST['auto_regenerate'] ) ? sanitize_key( wp_unslash( $_POST['auto_regenerate'] ) ) : 'manual',
            'robots_llms'      => isset( $_POST['robots_llms'] ) && 'true' === $_POST['robots_llms'],
            'robots_llms_full' => isset( $_POST['robots_llms_full'] ) && 'true' === $_POST['robots_llms_full'],
        );

        if ( empty( $settings['site_name'] ) ) {
            $settings['site_name'] = get_bloginfo( 'name' );
        }

        if ( empty( $settings['post_types'] ) && empty( $settings['manual_includes'] ) ) {
            wp_send_json_error( __( 'Please select at least one content type or add content manually', 'vigia' ) );
        }

        // Use the new combined save_and_generate method.
        $result = VigIA_LLMS_Generator::save_and_generate( $settings );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( $result->get_error_message() );
        }

        wp_send_json_success( $result );
    }

    /**
     * AJAX: Save LLMs settings only (without generating files)
     */
    public function ajax_save_llms_settings() {
        check_ajax_referer( 'vigia_ajax_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized', 'vigia' ) );
        }

        // Build settings array directly from POST.
        $settings = array(
            'site_name'        => isset( $_POST['site_name'] ) ? sanitize_text_field( wp_unslash( $_POST['site_name'] ) ) : '',
            'site_description' => isset( $_POST['site_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['site_description'] ) ) : '',
            'post_types'       => isset( $_POST['post_types'] ) ? array_map( 'sanitize_key', (array) $_POST['post_types'] ) : array(),
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized below.
            'taxonomy_filters' => isset( $_POST['taxonomy_filters'] ) ? $this->sanitize_taxonomy_filters( wp_unslash( $_POST['taxonomy_filters'] ) ) : array(),
            'manual_includes'  => isset( $_POST['manual_includes'] ) ? array_map( 'absint', (array) $_POST['manual_includes'] ) : array(),
            'manual_excludes'  => isset( $_POST['manual_excludes'] ) ? array_map( 'absint', (array) $_POST['manual_excludes'] ) : array(),
            'exclude_patterns' => isset( $_POST['exclude_patterns'] ) ? sanitize_textarea_field( wp_unslash( $_POST['exclude_patterns'] ) ) : '',
            'exclude_noindex'  => isset( $_POST['exclude_noindex'] ) && 'true' === $_POST['exclude_noindex'],
            'generate_full'    => isset( $_POST['generate_full'] ) && 'true' === $_POST['generate_full'],
            'full_mode'        => isset( $_POST['full_mode'] ) ? sanitize_key( wp_unslash( $_POST['full_mode'] ) ) : 'full',
            'auto_regenerate'  => isset( $_POST['auto_regenerate'] ) ? sanitize_key( wp_unslash( $_POST['auto_regenerate'] ) ) : 'manual',
            'robots_llms'      => isset( $_POST['robots_llms'] ) && 'true' === $_POST['robots_llms'],
            'robots_llms_full' => isset( $_POST['robots_llms_full'] ) && 'true' === $_POST['robots_llms_full'],
        );

        if ( empty( $settings['site_name'] ) ) {
            $settings['site_name'] = get_bloginfo( 'name' );
        }

        VigIA_LLMS_Generator::save_settings( $settings );

        wp_send_json_success();
    }

    /**
     * AJAX: Delete LLMs files (single or both)
     */
    public function ajax_delete_llms_files() {
        check_ajax_referer( 'vigia_ajax_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized', 'vigia' ) );
        }

        $file = isset( $_POST['file'] ) ? sanitize_file_name( wp_unslash( $_POST['file'] ) ) : '';

        // Delete specific file.
        if ( ! empty( $file ) ) {
            if ( 'llms.txt' === $file ) {
                $result = VigIA_LLMS_Generator::delete_file( 'llms.txt' );
            } elseif ( 'llms-full.txt' === $file ) {
                $result = VigIA_LLMS_Generator::delete_file( 'llms-full.txt' );
            } else {
                wp_send_json_error( __( 'Invalid file', 'vigia' ) );
                return;
            }

            if ( $result ) {
                wp_send_json_success( array( 'message' => __( 'File deleted', 'vigia' ) ) );
            } else {
                wp_send_json_error( __( 'Failed to delete file', 'vigia' ) );
            }
            return;
        }

        // Delete all files (legacy).
        $result = VigIA_LLMS_Generator::delete_files();

        if ( $result ) {
            wp_send_json_success();
        } else {
            wp_send_json_error( __( 'Failed to delete files', 'vigia' ) );
        }
    }

    /**
     * AJAX: Search posts for LLMs manual include/exclude (v1.2.0)
     */
    public function ajax_search_posts() {
        check_ajax_referer( 'vigia_ajax_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized', 'vigia' ) );
        }

        $search      = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';
        $exclude_ids = isset( $_POST['exclude_ids'] ) ? array_map( 'absint', (array) $_POST['exclude_ids'] ) : array();

        $results = VigIA_LLMS_Generator::search_posts( $search, $exclude_ids, 20 );

        wp_send_json_success( $results );
    }

    /**
     * AJAX: Get taxonomies for a post type (v1.2.0)
     */
    public function ajax_get_taxonomies() {
        check_ajax_referer( 'vigia_ajax_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized', 'vigia' ) );
        }

        $post_type = isset( $_POST['post_type'] ) ? sanitize_key( wp_unslash( $_POST['post_type'] ) ) : '';

        if ( empty( $post_type ) ) {
            wp_send_json_error( __( 'Post type is required', 'vigia' ) );
        }

        $taxonomies = VigIA_LLMS_Generator::get_post_type_taxonomies( $post_type );

        wp_send_json_success( $taxonomies );
    }

    /**
     * Sanitize taxonomy filters array (v1.2.0)
     *
     * @param array $filters Raw filters array.
     * @return array Sanitized filters.
     */
    private function sanitize_taxonomy_filters( $filters ) {
        if ( ! is_array( $filters ) ) {
            return array();
        }

        $sanitized = array();
        foreach ( $filters as $post_type => $taxonomies ) {
            $post_type = sanitize_key( $post_type );
            if ( ! is_array( $taxonomies ) ) {
                continue;
            }

            $sanitized[ $post_type ] = array();
            foreach ( $taxonomies as $taxonomy => $terms ) {
                $taxonomy = sanitize_key( $taxonomy );
                if ( ! is_array( $terms ) ) {
                    continue;
                }
                $sanitized[ $post_type ][ $taxonomy ] = array_map( 'absint', $terms );
            }
        }

        return $sanitized;
    }
}

/**
 * Initialize the plugin
 *
 * @return VigIA
 */
function vigia() {
    return VigIA::get_instance();
}

// Start the plugin.
vigia();