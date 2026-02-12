<?php
/**
 * Admin Page Controller.
 *
 * @package HungryRestApiMonitor
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class NANDRESTAPI_Admin_Page
 * Handles the admin page and tabs.
 */
class NANDRESTAPI_Admin_Page
{

    /**
     * Current tab.
     *
     * @var string
     */
    private static $current_tab = 'dashboard';

    /**
     * Initialize admin page.
     */
    public static function init()
    {
        add_action('admin_menu', array(__CLASS__, 'add_menu'));
    }

    /**
     * Add admin menu.
     */
    public static function add_menu()
    {
        add_menu_page(
            __('Hungry REST API Monitor', 'hungry-rest-api-monitor'),
            __('Hungry API', 'hungry-rest-api-monitor'),
            'manage_options',
            'hungry-rest-api-monitor',
            array(__CLASS__, 'render_page'),
            'dashicons-rest-api',
            75
        );
    }

    /**
     * Render admin page.
     */
    public static function render_page()
    {
        // Check capabilities.
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'hungry-rest-api-monitor'));
        }

        // Get current tab.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        self::$current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'dashboard';

        $tabs = self::get_tabs();

        // Validate tab.
        if (!isset($tabs[self::$current_tab])) {
            self::$current_tab = 'dashboard';
        }

        ?>
        <div class="wrap nandrestapi-admin-wrap">
            <h1 class="nandrestapi-page-title">
                <span class="dashicons dashicons-rest-api"></span>
                <?php esc_html_e('Hungry REST API Monitor', 'hungry-rest-api-monitor'); ?>
            </h1>

            <nav class="nav-tab-wrapper nandrestapi-nav-tabs">
                <?php foreach ($tabs as $tab_id => $tab_label): ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=hungry-rest-api-monitor&tab=' . $tab_id)); ?>"
                        class="nav-tab <?php echo self::$current_tab === $tab_id ? 'nav-tab-active' : ''; ?>">
                        <?php echo esc_html($tab_label); ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="nandrestapi-tab-content">
                <?php self::render_tab_content(); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Get available tabs.
     *
     * @return array Tabs.
     */
    private static function get_tabs()
    {
        return array(
            'dashboard' => __('Dashboard', 'hungry-rest-api-monitor'),
            'endpoints' => __('Endpoints', 'hungry-rest-api-monitor'),
            'logs' => __('Logs', 'hungry-rest-api-monitor'),
            'http-requests' => __('HTTP Requests', 'hungry-rest-api-monitor'),
            'settings' => __('Settings', 'hungry-rest-api-monitor'),
            'support' => __('Support', 'hungry-rest-api-monitor'),
        );
    }

    /**
     * Render current tab content.
     */
    private static function render_tab_content()
    {
        $tab_file = NANDRESTAPI_PLUGIN_DIR . 'includes/admin-tabs/tab-' . self::$current_tab . '.php';

        if (file_exists($tab_file)) {
            include $tab_file;
        } else {
            echo '<p>' . esc_html__('Tab content not found.', 'hungry-rest-api-monitor') . '</p>';
        }
    }

    /**
     * Get current tab.
     *
     * @return string Current tab ID.
     */
    public static function get_current_tab()
    {
        return self::$current_tab;
    }
}
