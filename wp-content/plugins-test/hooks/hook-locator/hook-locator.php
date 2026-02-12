<?php
/**
 * Plugin Name: Hook Locator
 * Plugin URI: https://wordpress.org/plugins/hook-locator/
 * Description: Search and analyze WordPress hook usage in plugins and themes directly from the admin panel. Perfect for developers debugging hooks and understanding WordPress execution flow.
 * Version: 1.0
 * Author: Jaydip Ahir
 * Author URI: https://profiles.wordpress.org/jdahir0789/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: hook-locator
 * Requires at least: 5.0
 * Tested up to: 6.8
 * Requires PHP: 7.4
 *
 * @package HookLocator
 * @since   1.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'HOOK_LOCATOR_VERSION', '1.0' );
define( 'HOOK_LOCATOR_PLUGIN_FILE', __FILE__ );
define( 'HOOK_LOCATOR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'HOOK_LOCATOR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include required files.
require_once HOOK_LOCATOR_PLUGIN_DIR . 'includes/class-hook-locator-core.php';
require_once HOOK_LOCATOR_PLUGIN_DIR . 'includes/class-hook-locator-admin.php';
require_once HOOK_LOCATOR_PLUGIN_DIR . 'includes/class-hook-locator-search.php';
require_once HOOK_LOCATOR_PLUGIN_DIR . 'includes/class-hook-locator-results-table.php';
require_once HOOK_LOCATOR_PLUGIN_DIR . 'includes/class-hook-locator-detail.php';

/**
 * Main plugin bootstrap class.
 *
 * Handles plugin initialization and core functionality loading.
 *
 * @since 1.0
 */
final class Hook_Locator_Plugin {

	/**
	 * Plugin instance.
	 *
	 * @since 1.0
	 * @var   Hook_Locator_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get plugin instance.
	 *
	 * Implements singleton pattern to ensure only one instance exists.
	 *
	 * @since 1.0
	 * @return Hook_Locator_Plugin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * Private constructor to prevent direct instantiation.
	 *
	 * @since 1.0
	 */
	private function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize WordPress hooks.
	 *
	 * @since 1.0
	 */
	private function init_hooks() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
	}

	/**
	 * Initialize the plugin.
	 *
	 * Loads core functionality and admin interface.
	 *
	 * @since 1.0
	 */
	public function init() {
		// Initialize core functionality.
		Hook_Locator_Core::get_instance();

		// Initialize admin interface if in admin area.
		if ( is_admin() ) {
			Hook_Locator_Admin::get_instance();
		}
	}

	/**
	 * Plugin activation callback.
	 *
	 * @since 1.0
	 */
	public function activate() {
		// Flush rewrite rules if needed.
		flush_rewrite_rules();
	}

	/**
	 * Plugin deactivation callback.
	 *
	 * @since 1.0
	 */
	public function deactivate() {
		// Clean up if needed.
		flush_rewrite_rules();
	}
}

/**
 * Initialize the plugin.
 *
 * @since 1.0
 * @return Hook_Locator_Plugin
 */
function hook_locator() {
	return Hook_Locator_Plugin::get_instance();
}

// Initialize the plugin.
hook_locator();
