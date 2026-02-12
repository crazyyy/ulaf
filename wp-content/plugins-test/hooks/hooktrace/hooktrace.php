<?php
/**
 * Plugin Name: HookTrace - Trace Hooks with Precision
 * Description: Cross-Plugin Debug & Trace Recorder - Records and visualizes hook execution order for WordPress developers.
 * Version: 1.1.0
 * Author: smilingsyntax
 * Author URI: https://smilingsyntax.com
 * Contributors: smilingsyntax, codemadan
 * Contributor URI (codemadan): https://manjul.me
 * Requires at least: 5.0
 * Requires PHP: 8.0
 * Text Domain: hooktrace
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package HookTrace
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Only run if WP_DEBUG is enabled.
if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
	return;
}

// Define plugin constants.
define( 'HOOKTRACE_VERSION', '1.1.0' );
define( 'HOOKTRACE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'HOOKTRACE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Autoloader.
spl_autoload_register(
	function ( $class ) {
		$prefix   = 'HookTrace\\';
		$base_dir = HOOKTRACE_PLUGIN_DIR . 'src/';

		$len = strlen( $prefix );
		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			return;
		}

		$relative_class = substr( $class, $len );
		$file           = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

		if ( file_exists( $file ) ) {
			require $file;
		}
	}
);

// Initialize plugin.
add_action(
	'plugins_loaded',
	array( \HookTrace\Core\Plugin::get_instance(), 'init' ),
	1
);

// Early bootstrap if loaded as MU plugin.
if ( defined( 'WPMU_PLUGIN_DIR' ) && strpos( __FILE__, WPMU_PLUGIN_DIR ) === 0 ) {
	$plugin = \HookTrace\Core\Plugin::get_instance();
	$plugin->init();
}
