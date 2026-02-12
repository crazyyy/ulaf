<?php
/**
 * Plugin Name: Alchemists Color Filters for WooCommerce
 * Plugin URI: https://github.com/danfisher85/alc-color-filters
 * Description: Filter WooCommerce products by color from a sidebar widget.
 * Author: Dan Fisher
 * Author URI: https://github.com/danfisher85/alc-color-filters
 * Version: 1.0.8
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Text Domain: alc-color-filters
 * Domain Path: /languages/
 * WC requires at least: 6.0
 * WC tested up to: 9.5
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package Alchemists_Color_Filters
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'CF_VERSION', '1.0.8' );
define( 'CF_PLUGIN_FILE', __FILE__ );
define( 'CF_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'CF_INCLUDES_PATH', CF_PLUGIN_PATH . 'includes/' );
define( 'CF_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'CF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main plugin class initialization
 */
final class Alchemists_Color_Filters {

	/**
	 * Single instance of the class
	 *
	 * @var Alchemists_Color_Filters
	 */
	private static $instance = null;

	/**
	 * Get single instance of the class
	 *
	 * @return Alchemists_Color_Filters
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
		// Check requirements
		if ( ! $this->check_requirements() ) {
			return;
		}

		// Load plugin files
		$this->includes();

		// Initialize plugin
		$this->init();

		// Register hooks
		$this->register_hooks();
	}

	/**
	 * Prevent cloning
	 */
	private function __clone() {}

	/**
	 * Prevent unserialization
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Unserializing instances of this class is forbidden.', 'alc-color-filters' ), '1.0.0' );
	}

	/**
	 * Check plugin requirements
	 *
	 * @return bool
	 */
	private function check_requirements() {
		// Check PHP version
		if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
			add_action( 'admin_notices', array( $this, 'php_version_notice' ) );
			return false;
		}

		// Check WordPress version
		global $wp_version;
		if ( version_compare( $wp_version, '5.8', '<' ) ) {
			add_action( 'admin_notices', array( $this, 'wp_version_notice' ) );
			return false;
		}

		// Check if WooCommerce is active
		if ( ! $this->is_woocommerce_active() ) {
			add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
			return false;
		}

		return true;
	}

	/**
	 * Check if WooCommerce is active
	 *
	 * @return bool
	 */
	private function is_woocommerce_active() {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * Include required files
	 */
	private function includes() {
		// Core files
		require_once CF_PLUGIN_PATH . 'color-filters.php';
		require_once CF_INCLUDES_PATH . 'widgets.php';
	}

	/**
	 * Initialize plugin
	 */
	private function init() {
		// Initialize main color filters class
		if ( class_exists( 'NM_Color_Filters' ) ) {
			new NM_Color_Filters();
		}
	}

	/**
	 * Register plugin hooks
	 */
	private function register_hooks() {
		// Activation hook
		register_activation_hook( CF_PLUGIN_FILE, array( $this, 'activate' ) );

		// Deactivation hook
		register_deactivation_hook( CF_PLUGIN_FILE, array( $this, 'deactivate' ) );

		// Load plugin textdomain
		add_action( 'init', array( $this, 'load_textdomain' ) );

		// Add settings link on plugins page
		add_filter( 'plugin_action_links_' . CF_PLUGIN_BASENAME, array( $this, 'add_action_links' ) );

		// Add plugin meta links
		add_filter( 'plugin_row_meta', array( $this, 'add_plugin_meta_links' ), 10, 2 );
	}

	/**
	 * Plugin activation
	 */
	public function activate() {
		// Check if WooCommerce is active
		if ( ! $this->is_woocommerce_active() ) {
			deactivate_plugins( CF_PLUGIN_BASENAME );
			wp_die(
				esc_html__( 'Alchemists Color Filters requires WooCommerce to be installed and active.', 'alc-color-filters' ),
				esc_html__( 'Plugin Activation Error', 'alc-color-filters' ),
				array( 'back_link' => true )
			);
		}

		// Set activation flag
		set_transient( 'alc_color_filters_activated', true, 60 );

		// Update version
		update_option( 'alc_color_filters_version', CF_VERSION );

		// Flush rewrite rules
		flush_rewrite_rules();
	}

	/**
	 * Plugin deactivation
	 */
	public function deactivate() {
		// Flush rewrite rules
		flush_rewrite_rules();
	}

	/**
	 * Load plugin textdomain
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'alc-color-filters',
			false,
			dirname( CF_PLUGIN_BASENAME ) . '/languages/'
		);
	}

	/**
	 * Add action links on plugins page
	 *
	 * @param array $links Plugin action links.
	 * @return array
	 */
	public function add_action_links( $links ) {
		$plugin_links = array(
			'<a href="' . esc_url( admin_url( 'edit-tags.php?taxonomy=product_color&post_type=product' ) ) . '">' . esc_html__( 'Manage Colors', 'alc-color-filters' ) . '</a>',
		);

		return array_merge( $plugin_links, $links );
	}

	/**
	 * Add plugin meta links
	 *
	 * @param array  $links Plugin meta links.
	 * @param string $file  Plugin file.
	 * @return array
	 */
	public function add_plugin_meta_links( $links, $file ) {
		if ( CF_PLUGIN_BASENAME === $file ) {
			$plugin_links = array(
				'<a href="https://github.com/danfisher85/alc-color-filters" target="_blank">' . esc_html__( 'GitHub', 'alc-color-filters' ) . '</a>',
				'<a href="https://github.com/danfisher85/alc-color-filters/issues" target="_blank">' . esc_html__( 'Support', 'alc-color-filters' ) . '</a>',
			);

			$links = array_merge( $links, $plugin_links );
		}

		return $links;
	}

	/**
	 * PHP version notice
	 */
	public function php_version_notice() {
		?>
		<div class="notice notice-error">
			<p>
				<?php
				printf(
					/* translators: %s: Required PHP version */
					esc_html__( 'Alchemists Color Filters requires PHP version %s or higher. Please update PHP.', 'alc-color-filters' ),
					'<strong>7.4</strong>'
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * WordPress version notice
	 */
	public function wp_version_notice() {
		?>
		<div class="notice notice-error">
			<p>
				<?php
				printf(
					/* translators: %s: Required WordPress version */
					esc_html__( 'Alchemists Color Filters requires WordPress version %s or higher. Please update WordPress.', 'alc-color-filters' ),
					'<strong>5.8</strong>'
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * WooCommerce missing notice
	 */
	public function woocommerce_missing_notice() {
		?>
		<div class="notice notice-error">
			<p>
				<?php
				printf(
					/* translators: %s: WooCommerce plugin link */
					wp_kses_post( __( 'Alchemists Color Filters requires <a href="%s" target="_blank">WooCommerce</a> to be installed and active.', 'alc-color-filters' ) ),
					'https://wordpress.org/plugins/woocommerce/'
				);
				?>
			</p>
		</div>
		<?php
	}
}

/**
 * Initialize the plugin
 *
 * @return Alchemists_Color_Filters
 */
function alc_color_filters() {
	return Alchemists_Color_Filters::get_instance();
}

// Launch the plugin
alc_color_filters();