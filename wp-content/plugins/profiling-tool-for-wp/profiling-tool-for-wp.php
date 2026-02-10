<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.dnsempresas.com/
 * @since             1.0.0
 * @package           Profiling_Tool_For_Wp
 *
 * @wordpress-plugin
 * Plugin Name:       Profiling Tool For WP
 * Plugin URI:        https://www.tbplugin.com/
 * Description:       Plugin para medir el rendimiento y el consumo de los plugins instalados en el sitio, con estadísticas detalladas generales y para cada plugin.
 * Version:           1.2.1
 * Author:            Dns Empresas
 * Author URI:        https://www.dnsempresas.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       profiling-tool-for-wp
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PROFILING_TOOL_FOR_WP_VERSION', '1.2.1' );
define( 'PROFILING_TOOL_FOR_WP_NAME', 'ptfwp' );
define( 'PROFILING_TOOL_FOR_WP_PATH', plugin_dir_path( __FILE__ ) );
define( 'PROFILING_TOOL_FOR_WP_URL', plugin_dir_url( __FILE__ ) );
define( 'PROFILING_TOOL_FOR_WP_MAIN_URL', home_url() );
define( 'PROFILING_TOOL_FOR_WP_ENABLE_MYSQL', true );
define( 'PROFILING_TOOL_FOR_WP_LANGUAGE_OPTION', 'PROFILING_TOOL_FOR_WP_LANGUAGE' );


if ( get_option( PROFILING_TOOL_FOR_WP_LANGUAGE_OPTION ) ) {
    // code..
} else {

    add_option( PROFILING_TOOL_FOR_WP_LANGUAGE_OPTION );

}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-profiling-tool-for-wp-activator.php
 */
function profiling_tool_for_wp_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-profiling-tool-for-wp-activator.php';
	Profiling_Tool_For_Wp_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-profiling-tool-for-wp-deactivator.php
 */
function profiling_tool_for_wp_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-profiling-tool-for-wp-deactivator.php';
	Profiling_Tool_For_Wp_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'profiling_tool_for_wp_activate' );
register_deactivation_hook( __FILE__, 'profiling_tool_for_wp_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-profiling-tool-for-wp.php';

require plugin_dir_path( __FILE__ ) . 'acciones.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function profiling_tool_for_wp_run() {

	$plugin = new Profiling_Tool_For_Wp();
	$plugin->run();

}
profiling_tool_for_wp_run();
