<?php
/*
Plugin Name: WP Data Logger
Description: Logging and debug events and vars on site. For adding data in log use the hook: <br><code>do_action( 'logger', $data );</code>
Author: WPCraft & iTRON
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Version: 2.4
*/

define( 'WPDL_PLUGIN_NAME', plugin_basename( __FILE__ ) );
define( 'WPDL_VERSION', '2.4' );

require_once ABSPATH . 'wp-admin/includes/upgrade.php';
require_once __DIR__ . '/class-wp-data-logger.php';

$wp_data_logger = WP_Data_Logger::get_instance();

register_activation_hook( __FILE__, array( $wp_data_logger, 'activation' ) );

add_action( 'in_plugin_update_message-' . WPDL_PLUGIN_NAME, array( $wp_data_logger, 'update_message' ), 10, 2 );

function wpdl_add_button( string $name, callable $clb, string $btnClass = '' ) {
	WP_Data_Logger::add_panel_button( ...func_get_args() );
}
