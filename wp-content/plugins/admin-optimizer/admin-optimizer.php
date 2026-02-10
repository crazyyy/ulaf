<?php
/**
 * Plugin Name: Admin Optimizer
 * Plugin URI: https://www.adminoptimizer.com
 * Description: An all-in-one plugin to enhance your WordPress sites
 * Version: 1.5.3
 * Requires PHP: 7.4.0
 * Author: Yipresser
 * Author URI: https://damienoh.com
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: admin-optimizer
 * Domain Path: /languages
 *
 * @package admin-optimizer
 */

namespace Yipresser\AdminOptimizer;

use Yipresser\AdminOptimizer\Admin\Bootstrap;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! defined( 'ADMINOPTIMIZER_PATH' ) ) {
	define( 'ADMINOPTIMIZER_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'ADMINOPTIMIZER_URI' ) ) {
	define( 'ADMINOPTIMIZER_URI', plugin_dir_url( __FILE__ ) );
}

require ADMINOPTIMIZER_PATH . 'vendor/autoload.php';

$adminoptim_as_file = ADMINOPTIMIZER_PATH . 'vendor/woocommerce/action-scheduler/action-scheduler.php';
if ( ! function_exists( 'as_has_scheduled_action' ) && is_readable( $adminoptim_as_file ) ) {
	require_once $adminoptim_as_file;
}

register_activation_hook( __FILE__, __NAMESPACE__ . '\activate_plugin_check' );

register_deactivation_hook( __FILE__, __NAMESPACE__ . '\deactivate_plugin_hook' );

/**
 * Check if the base plugin is activated. If not, activate it.
 *
 * @return void
 */
function activate_plugin_check() {
	// Makes sure the plugin is defined before trying to use it.
	if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
		require_once ABSPATH . '/wp-admin/includes/plugin.php';
	}

	/** Admin Optimizer Pro conflicts with the free Admin Optimizer plugin. Check to see if admin-optimizer-pro plugin is activated. If yes, deactivate it.
	 * */
	if ( is_plugin_active( 'admin-optimizer-pro/admin-optimizer-pro.php' ) ) {
		deactivate_plugins( 'admin-optimizer-pro/admin-optimizer-pro.php' );
	}
}

/**
 * Deactivation hooks.
 *
 * @return void
 */
function deactivate_plugin_hook() {
	do_action( 'adminoptim_deactivate_plugin' );
}

if ( ! class_exists( Bootstrap::class ) ) {
	require_once 'admin/bootstrap.php';
}

add_action(
	'plugins_loaded',
	function () {
		Bootstrap::get_instance();
	}
);
