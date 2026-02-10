<?php
/**
 * Plugin Name: Interlinks Manager
 * Description: Manages the internal links of your WordPress website. (Lite Version)
 * Version: 1.16
 * Author: DAEXT
 * Author URI: https://daext.com
 * Text Domain: daext-interlinks-manager
 * License: GPLv3
 *
 * @package daext-interlinks-manager
 */

// Prevent direct access to this file.
if ( ! defined( 'WPINC' ) ) {
	die(); }

// Set constants.
define( 'DAEXTINMA_EDITION', 'FREE' );

// Class shared across public and admin.
require_once plugin_dir_path( __FILE__ ) . 'shared/class-daextinma-shared.php';

// Rest API.
require_once plugin_dir_path( __FILE__ ) . 'inc/class-daextinma-rest.php';
add_action( 'plugins_loaded', array( 'Daextinma_Rest', 'get_instance' ) );

// Admin.
if ( is_admin() ) {

	require_once plugin_dir_path( __FILE__ ) . 'admin/class-daextinma-admin.php';

	// If this is not an AJAX request, create a new singleton instance of the admin class.
	if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
		add_action( 'plugins_loaded', array( 'Daextinma_Admin', 'get_instance' ) );
	}

	// Activate the plugin using only the class static methods.
	register_activation_hook( __FILE__, array( 'Daextinma_Admin', 'ac_activate' ) );

}
// Ajax.
if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

	// Admin.
	require_once plugin_dir_path( __FILE__ ) . 'class-daextinma-ajax.php';
	add_action( 'plugins_loaded', array( 'Daextinma_Ajax', 'get_instance' ) );

}

/**
 * Customize the action links in the "Plugins" menu.
 *
 * @param array $actions An array of plugin action links.
 *
 * @return mixed
 */
function daextinma_customize_action_links( $actions ) {
	$actions[] = '<a href="https://daext.com/interlinks-manager/" target="_blank">' . esc_html__( 'Buy the Pro Version', 'daext-interlinks-manager' ) . '</a>';
	return $actions;
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'daextinma_customize_action_links' );

if ( is_admin() ) {

	require_once plugin_dir_path( __FILE__ ) . 'admin/class-daextinma-admin.php';

	// If needed, create or update the database tables.
	Daextinma_Admin::ac_create_database_tables();

	// If needed, create or update the plugin options.
	Daextinma_Admin::ac_initialize_options();

}
