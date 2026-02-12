<?php
/**
 * Plugin Name: Hungry REST API Monitor
 * Plugin URI: https://nandann.com/contact
 * Description: Monitor WordPress REST API requests with detailed analytics, performance metrics, WooCommerce support, and beautiful visualizations.
 * Version: 1.0.2
 * Author: Prakhar Bhatia
 * Author URI: https://nandann.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: hungry-rest-api-monitor
 * Domain Path: /languages
 * Requires at least: 6.2
 * Tested up to: 6.9
 * Requires PHP: 7.4
 *
 * @package HungryRestApiMonitor
 * @author Prakhar Bhatia <prakhar@nandann.com>
 * @copyright 2026 Nandann Creative Agency
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants with unique 10-char prefix: nandrestapi
define('NANDRESTAPI_VERSION', '1.0.2');
define('NANDRESTAPI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('NANDRESTAPI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('NANDRESTAPI_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('NANDRESTAPI_OPTIONS_KEY', 'nandrestapi_options');
define('NANDRESTAPI_LOGS_TABLE', 'nandrestapi_logs');

/**
 * Autoload includes.
 */
require_once NANDRESTAPI_PLUGIN_DIR . 'includes/functions.php';
require_once NANDRESTAPI_PLUGIN_DIR . 'includes/core/class-activator.php';
require_once NANDRESTAPI_PLUGIN_DIR . 'includes/core/class-deactivator.php';
require_once NANDRESTAPI_PLUGIN_DIR . 'includes/database/class-db-schema.php';
require_once NANDRESTAPI_PLUGIN_DIR . 'includes/database/class-db-handler.php';
require_once NANDRESTAPI_PLUGIN_DIR . 'includes/database/class-db-cleanup.php';
require_once NANDRESTAPI_PLUGIN_DIR . 'includes/trackers/class-rest-tracker.php';
require_once NANDRESTAPI_PLUGIN_DIR . 'includes/trackers/class-query-tracker.php';
require_once NANDRESTAPI_PLUGIN_DIR . 'includes/trackers/class-http-tracker.php';
require_once NANDRESTAPI_PLUGIN_DIR . 'includes/trackers/class-cache-tracker.php';
require_once NANDRESTAPI_PLUGIN_DIR . 'includes/trackers/class-error-tracker.php';
require_once NANDRESTAPI_PLUGIN_DIR . 'includes/trackers/class-transient-tracker.php';
require_once NANDRESTAPI_PLUGIN_DIR . 'includes/analytics/class-endpoint-stats.php';
require_once NANDRESTAPI_PLUGIN_DIR . 'includes/analytics/class-traffic-stats.php';
require_once NANDRESTAPI_PLUGIN_DIR . 'includes/analytics/class-performance-stats.php';

// Admin-only includes.
if (is_admin()) {
    require_once NANDRESTAPI_PLUGIN_DIR . 'includes/admin/class-admin-page.php';
    require_once NANDRESTAPI_PLUGIN_DIR . 'includes/admin/class-admin-assets.php';
    require_once NANDRESTAPI_PLUGIN_DIR . 'includes/admin/class-ajax-handlers.php';
}

// Activation and deactivation hooks.
register_activation_hook(__FILE__, array('NANDRESTAPI_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('NANDRESTAPI_Deactivator', 'deactivate'));

/**
 * Initialize the plugin.
 */
function nandrestapi_init()
{

    // Initialize trackers (always run to capture REST API requests).
    NANDRESTAPI_Rest_Tracker::init();

    // Initialize admin.
    if (is_admin()) {
        NANDRESTAPI_Admin_Page::init();
        NANDRESTAPI_Admin_Assets::init();
        NANDRESTAPI_Ajax_Handlers::init();
    }
}
add_action('plugins_loaded', 'nandrestapi_init');

/**
 * Schedule cleanup cron on init.
 */
function nandrestapi_schedule_cleanup()
{
    if (!wp_next_scheduled('nandrestapi_daily_cleanup')) {
        wp_schedule_event(time(), 'daily', 'nandrestapi_daily_cleanup');
    }
}
add_action('init', 'nandrestapi_schedule_cleanup');

/**
 * Run daily cleanup.
 */
function nandrestapi_run_daily_cleanup()
{
    NANDRESTAPI_DB_Cleanup::cleanup_old_logs();
}
add_action('nandrestapi_daily_cleanup', 'nandrestapi_run_daily_cleanup');
