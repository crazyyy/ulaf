<?php
/**
 * Plugin Name:       Plugins Page Tweaker
 * Description:       This plugin customizes plugins page and allows you to bulk deactivate and immediately remove other plugins
 * Version:           1.4.5
 * Author:            Webtemyk
 * Author URI:        temyk.ru
 * License:           GPL-2.0+
 * Text Domain:       bulk-plugins
 * Domain Path:       /languages
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Currently plugin version.
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WTBP_VERSION', '1.4.5' );
define( 'WTBP_PLUGIN_FILE', __FILE__ );
define( 'WTBP_ABSPATH', dirname( __FILE__ ) );
define( 'WTBP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'WTBP_PLUGIN_SLUG', dirname( plugin_basename( __FILE__ ) ) );

define( 'WTBP_PLUGIN_URL', plugins_url( null, __FILE__ ) );
define( 'WTBP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

define( 'WTBP_PLUGIN_PREFIX', 'WTBP' );

load_plugin_textdomain( 'bulk-plugins', false, dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/' );

require_once WTBP_PLUGIN_PATH . '/includes/boot.php';
if ( is_admin() ) {
	require_once WTBP_PLUGIN_PATH . '/admin/boot.php';
}

try {
	if ( is_admin() ) {
		new \WTBP\Plugin();
	}
} catch ( Exception $e ) {
	$mpn_plugin_error_func = function () use ( $e ) {
		$error = sprintf( __( 'The %1$s plugin has stopped. <b>Error:</b> %2$s Code: %3$s', 'bulk-plugins' ), 'Plugins Page Customize', $e->getMessage(), $e->getCode() );
		echo '<div class="notice notice-error"><p>' . $error . '</p></div>';
	};

	add_action( 'admin_notices', $mpn_plugin_error_func );
	add_action( 'network_admin_notices', $mpn_plugin_error_func );
}
