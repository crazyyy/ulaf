<?php // phpcs:ignore
/**
 * Plugin Name: BugTrace - Debug Log Tool
 * Description: Easy & Powerful WordPress Debug Tool For Developers.
 * Version: 1.0.7
 * Author: nsgawli
 * Author URI: https://wordpress.org/plugins/debug-log-tool/
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Tested up to: 6.9
 * Text Domain: debug-log-tool
 * Domain Path: /i18n
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( defined( 'WP_INSTALLING' ) && WP_INSTALLING ) {
	return;
}

if ( ! class_exists( 'Debug_Log_Tool' ) ) :

	final class Debug_Log_Tool {

		/**
		 * Plugin version
		 *
		 * @var string
		 */
		public static $version = '1.0.7';

		/**
		 * Constructor for main class
		 */
		public static function init() {

			self::define_constants();
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( __CLASS__, 'add_plugin_links' ) );
			self::load_files();
		}

		/**
		 * Defines global constants that can be availabel anywhere in WordPress
		 *
		 * @return void
		 */
		public static function define_constants() {

			self::define( 'WPDT_PLUGIN_FILE', __FILE__ );
			self::define( 'WPDT_ABSPATH', __DIR__ . '/' );
			self::define( 'WPDT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			self::define( 'WPDT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			self::define( 'WPDT_VERSION', self::$version );
		}

		/**
		 * Load all classes
		 *
		 * @return void
		 */
		private static function load_files() {
			require_once WPDT_ABSPATH . 'includes/auto-load-files.php';
		}

		/**
		 * Add plugin action links
		 *
		 * @param array $links - array of plugin action links.
		 * @return array
		 */
		public static function add_plugin_links( $links ) {
			$custom_links = array(
				'<a href="' . admin_url( 'admin.php?page=wpdt-settings' ) . '">' . __( 'Settings', 'debug-log-tool' ) . '</a>',
			);
			return array_merge( $links, $custom_links );
		}

		/**
		 * Define constants
		 *
		 * @param string $name - name of global constant.
		 * @param string $value - value of constant.
		 * @return void
		 */
		private static function define( $name, $value ) {

			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}
	}
endif;

Debug_Log_Tool::init();
