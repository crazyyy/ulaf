<?php
/**
 * Plugin Name: Ultimate Extension for ACF
 * Plugin URI: https://wordpress.org/plugins/ultimate-extension-for-acf
 * Description: Enhanced ACF Flexible Content editing with image previews and performance optimizations - compatible with ACF v5.6+ and v6.5+
 * Version: 1.0.0
 * Author: Miro Sedlacek - Ultimate Agency
 * Author URI: https://www.ultimate.agency/
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ultimate-extension-for-acf
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.8
 * Requires PHP: 7.4
 */

/**
 * Security Check - Prevent direct access to this file
 * This prevents the plugin from being accessed directly via URL
 */
if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

/**
 * Plugin Constants Definition
 * These constants are used throughout the plugin for paths, URLs, and version tracking
 */
// Plugin version - used for cache busting and update checks
define('UEFAX_VERSION', '1.0.0');
// Absolute file system path to the plugin directory
define('UEFAX_PLUGIN_DIR', plugin_dir_path(__FILE__));
// URL to the plugin directory for loading assets
define('UEFAX_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main Plugin Class - Ultimate Extension for ACF
 *
 * This class handles the core functionality of the Ultimate Extension for ACF plugin.
 * It provides enhanced ACF Flexible Content editing with image previews and performance
 * optimisations, supporting both current (ACF v6.5+) and legacy (ACF v5.6-v6.4.x) versions.
 *
 * Features:
 * - Image preview functionality for flexible content layouts
 * - Accordion behaviour (one layout open at a time)
 * - Performance optimisations with caching
 * - Database management for preview images
 * - Admin interface for managing layout preview images
 *
 * @package Ultimate_Extension_for_ACF
 * @version 1.0.0
 * @author Ultimate Agency
 * @since 1.0.0
 */
class Ultimate_Extension_for_ACF
{
    /**
     * Singleton instance of the plugin
     *
     * @var Ultimate_Extension_for_ACF|null
     * @since 1.0.0
     */
    private static ?Ultimate_Extension_for_ACF $instance = null;

	/**
	 * Get Singleton Instance
	 *
	 * Implements the singleton pattern to ensure only one instance of the plugin
	 * is created and used throughout the WordPress lifecycle.
	 *
     * @return Ultimate_Extension_for_ACF|null The singleton instance
     * @since 1.0.0
     */
    public static function init(): ?Ultimate_Extension_for_ACF
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private Constructor
     *
     * Private constructor to prevent direct instantiation. The plugin should only
     * be instantiated through the init() method to maintain a singleton pattern.
     *
     * Sets up the plugin initialisation hooks and ensures ACF is loaded before
     * initialising plugin functionality.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        // Initialise WordPress hooks for activation, deactivation, and text domain
        $this->init_hooks();

        // Initialise after plugins are loaded to ensure ACF is available
        // Priority 20 ensures ACF plugin is already loaded
        add_action('plugins_loaded', array($this, 'init_plugin'), 20);
    }

    /**
     * Initialise Plugin Functionality
     *
     * This method is called after all plugins are loaded to ensure ACF is available.
     * It determines which ACF version is active and loads the appropriate compatibility
     * layer to handle version-specific differences in ACF's API and structure.
     *
     * Version Detection:
     * - ACF v6.5+: Uses the current compatibility layer with modern optimizations
     * - ACF v5.6-v6.4.x: Uses the legacy compatibility layer for older ACF versions
     *
     * @since 1.0.0
     */
    public function init_plugin(): void
    {
        // Check if the ACF plugin is active and get a version
        if (!defined('ACF_VERSION')) {
            // ACF is not active - add admin notice or fail gracefully
            add_action('admin_notices', array($this, 'acf_missing_notice'));
            return;
        }

        // Load the appropriate compatibility layer based on an ACF version
        if (version_compare(ACF_VERSION, '6.5.0', '>=')) {
            // Load current compatibility (ACF v6.5+)
            // Includes performance optimisations and modern ACF API usage
            require_once UEFAX_PLUGIN_DIR . 'includes/current/class-uefax-current-compatibility.php';
            new UEFAX_Current_Compatibility();
        } else {
            // Load legacy compatibility (ACF v5.6 - v6.4.x)
            // Maintains backward compatibility with older ACF versions
            require_once UEFAX_PLUGIN_DIR . 'includes/legacy/class-uefax-legacy-compatibility.php';
            new UEFAX_Legacy_Compatibility();
        }
    }

    /**
     * Initialise WordPress Hooks
     *
     * Sets up the core WordPress hooks that the plugin needs to function properly.
     * This includes activation/deactivation hooks and internationalisation setup.
     *
     * @since 1.0.0
     */
    private function init_hooks(): void
    {
        // Plugin lifecycle hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

    /**
     * Plugin Activation Handler
     *
     * Called when the plugin is activated. Sets up the database table and initial
     * configuration needed for the plugin to function properly.
     *
     * Database Table Structure:
     * - id: Auto-increment primary key
     * - component: The ACF layout name (unique)
     * - preview_image: WordPress attachment ID for the preview image
     * - date_updated: Timestamp of last update
     *
     * @since 1.0.0
     */
    public function activate(): void
    {
        global $wpdb;

        // Define database table name with WordPress prefix
        $data_table_name = $wpdb->prefix . 'uefax_modal_preview';
        $charset_collate = $wpdb->get_charset_collate();

        // SQL to create the preview images table
        // Uses dbDelta for safe table creation/updates
        $sql = "CREATE TABLE $data_table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            component text NULL,                          -- ACF layout name
            preview_image INT NULL,                       -- WordPress attachment ID
            date_updated datetime NOT NULL,               -- Last update timestamp
            UNIQUE KEY id (id),                          -- Primary key
            UNIQUE KEY unique_component (component(191))  -- Ensure one image per component
            ) $charset_collate;";

        // Include WordPress upgrade functions
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Use output buffering to prevent dbDelta from generating unexpected output
        ob_start();
        dbDelta($sql);
        ob_end_clean();

        // Set plugin activation flags and version for future reference
        add_option('uefax_activated', true);
        add_option('uefax_version', UEFAX_VERSION);
    }

    /**
     * Plugin Deactivation Handler
     *
     * Called when the plugin is deactivated. Cleans up the database by removing
     * the custom table and plugin options. This ensures a clean uninstallation.
     *
     * @since 1.0.0
     */
    public function deactivate(): void
    {
        global $wpdb;

        // Clear any cached data before removing the table
        wp_cache_flush();

        // Remove the preview images database table
        $data_table_name = $wpdb->prefix . 'uefax_modal_preview';
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.NoCaching -- Plugin deactivation cleanup, cache flushed above
        $wpdb->query($wpdb->prepare("DROP TABLE IF EXISTS %s", $data_table_name));

        // Remove plugin options from the wp_options table
        delete_option('uefax_activated');
        delete_option('uefax_version');
    }
    /**
     * Display Admin Notice When ACF is Missing
     *
     * Shows an admin notice when the ACF plugin is not active, informing the user
     * that this plugin requires ACF to function properly.
     *
     * @since 1.0.0
     */
    public function acf_missing_notice(): void
    {
        $class = 'notice notice-error';
        $message = __('Ultimate Extension for ACF requires Advanced Custom Fields plugin to be installed and active.', 'ultimate-extension-for-acf');
        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
    }
}

/**
 * Initialise the Plugin
 *
 * Creates and initialises the singleton instance of the Ultimate Extension for ACF plugin.
 * This is the entry point that starts the plugin functionality.
 */
Ultimate_Extension_for_ACF::init();
