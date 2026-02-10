<?php
/**
 * Plugin Name:       Data Debug Tool
 * Description:       A simple lightweight tool to debug WordPress data.
 * Requires at least: 6.1
 * Requires PHP:      7.2
 * Version:           0.1.1
 * Author:            wpspices
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       data-debug-tool
 *
 * @package CreateBlock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * Main plugin class
 *
 * @since 0.1.0
 */
class DATA_DEBUG_TOOL {

	/**
	 * Plugin instance.
	 *
	 * @var DATA_DEBUG_TOOL
	 */
	private static $instance = null;

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	private $version = '0.1.1';

	/**
	 * Plugin hook suffix.
	 *
	 * @var string
	 */
	private $hook_suffix;

	/**
	 * Plugin constructor.
	 */
	private function __construct() {
		$this->define_constants();
		$this->init_includes();
		$this->init_hooks();
	}

	/**
	 * Get plugin instance.
	 *
	 * @return DATA_DEBUG_TOOL
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Define plugin constants.
	 *
	 * @return void
	 */
	private function define_constants() {
		define( 'DATA_DEBUG_TOOL_VERSION', $this->version );
		define( 'DATA_DEBUG_TOOL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		define( 'DATA_DEBUG_TOOL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	}

	/**
	 * Include required files.
	 *
	 * @return void
	 */
	private function init_includes() {
		foreach ( array( 'api', 'helper' ) as $filename ) {
			include_once DATA_DEBUG_TOOL_PLUGIN_DIR . $filename . '.php';
		}
		$api = new DATA_DEBUG_TOOL_API();
		$api->init();
	}

	/**
	 * Initialize WordPress hooks.
	 *
	 * @return void
	 */
	private function init_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @return void
	 */
	public function enqueue_admin_assets( $hook_suffix ) {
		// Only load on our admin page
		if ( $hook_suffix !== $this->hook_suffix || ! file_exists( DATA_DEBUG_TOOL_PLUGIN_DIR . 'build/index.asset.php' ) ) {
			return;
		}

		$asset_file = include_once DATA_DEBUG_TOOL_PLUGIN_DIR . 'build/index.asset.php';

		if ( empty( $asset_file ) || ! is_array( $asset_file ) || ! isset( $asset_file['dependencies'] ) ) {
			return;
		}

		wp_enqueue_script(
			'data-debug-tool-admin',
			DATA_DEBUG_TOOL_PLUGIN_URL . 'build/index.js',
			$asset_file['dependencies'],
			$asset_file['version'],
			true
		);

		wp_localize_script(
			'data-debug-tool-admin',
			'dataDebugTool',
			array(
				'nonce'   => wp_create_nonce( 'wp_debug_rest' ),
				'title'   => __( 'Data Debug Tool', 'data-debug-tool' ),
				'filters' => array(
					'option'      => __( 'Get Option', 'data-debug-tool' ),
					'post'        => __( 'Get Post', 'data-debug-tool' ),
					'postmeta'    => __( 'Get Post Meta', 'data-debug-tool' ),
					'user'        => __( 'Get User', 'data-debug-tool' ),
					'usermeta'    => __( 'Get User Meta', 'data-debug-tool' ),
					'term'        => __( 'Get Term', 'data-debug-tool' ),
					'plugins'     => __( 'Plugins List', 'data-debug-tool' ),
					'themes'      => __( 'Themes List', 'data-debug-tool' ),
					'system-info' => __( 'System Information', 'data-debug-tool' ),
				),
			)
		);

		wp_enqueue_style(
			'data-debug-tool-admin',
			DATA_DEBUG_TOOL_PLUGIN_URL . 'build/style-index.css',
			array( 'wp-components' ),
			$asset_file['version']
		);
	}

	/**
	 * Add menu page for the debug tool.
	 *
	 * @return void
	 */
	public function add_menu_page() {
		$this->hook_suffix = add_submenu_page(
			'tools.php',                          // Parent slug (Tools menu)
			__( 'Data Debug Tool', 'data-debug-tool' ), // Page title
			__( 'Data Debug Tool', 'data-debug-tool' ), // Menu title
			'manage_options',                     // Capability
			'data-debug-tool',                      // Menu slug
			array( $this, 'render_app' )          // Callback function
		);
	}

	/**
	 * Render the app container.
	 *
	 * @return void
	 */
	public function render_app() {
		echo '<div id="data-debug-tool-root"></div>';
	}
}

// Initialize the plugin
add_action( 'plugins_loaded', array( 'DATA_DEBUG_TOOL', 'get_instance' ) );
