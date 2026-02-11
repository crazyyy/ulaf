<?php
/**
 * Plugin Name: Auto Image Attributes Pro
 * Plugin URI: https://imageattributespro.com
 * Description: Pro add-on of the popular Auto Image Attributes From Filename With Bulk Updater WordPress plugin.
 * Author: Arun Basil Lal
 * Author URI: https://millionclues.com
 * Version: 4.2
 * License: GPL v2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: auto-image-attributes-pro
 */

$settingslc = get_option('iaffpro_settings');
$settingslc = array('registered_email' => 'registered@email.com', 'license_key' => '####-####-####-####-####'); 
update_option('iaffpro_settings', $settingslc);

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Plugin name and directory constants
 *
 * @since 	1.0
 * @constant 	IAFFPRO_AUTO_IMAGE_ATTRIBUTES_PRO		The name of the plugin - 'auto-image-attributes-pro'
 * @constant 	IAFFPRO_AUTO_IMAGE_ATTRIBUTES_PRO_DIR	The absolute path to the plugin directory without the trailing slash - C:\xampp\htdocs\wp/wp-content/plugins/auto-image-attributes-pro
 */
if ( ! defined('IAFFPRO_AUTO_IMAGE_ATTRIBUTES_PRO') )
    define('IAFFPRO_AUTO_IMAGE_ATTRIBUTES_PRO', trim(dirname(plugin_basename(__FILE__)), '/'));

if ( ! defined('IAFFPRO_AUTO_IMAGE_ATTRIBUTES_PRO_DIR') )
    define('IAFFPRO_AUTO_IMAGE_ATTRIBUTES_PRO_DIR', WP_PLUGIN_DIR . '/' . IAFFPRO_AUTO_IMAGE_ATTRIBUTES_PRO);


/**
 * Add plugin version to database
 *
 * @since 1.0
 * @constant IAFFPRO_VERSION_NUM the version number of the current version
 * @refer https://codex.wordpress.org/Creating_Tables_with_Plugins#Adding_an_Upgrade_Function
 */
if ( ! defined( 'IAFFPRO_VERSION_NUM' ) ) {
	define( 'IAFFPRO_VERSION_NUM', '4.2' );
}

/**
 * Do database update
 *
 * @since 1.4
 */
function iaffpro_upgrader() {
	
	// Get the current version of the plugin stored in the db.
	$current_ver = get_option( 'abl_iaffpro_version', '1.0' );
	
	// Return if we are already on updated version. 
	if ( version_compare( $current_ver, IAFFPRO_VERSION_NUM, '==' ) ) {
		return;
	}
	
	// Add any upgrade related jobs here. 
	
	// Finally add the current version to the database. Upgrade todo complete. 
	update_option( 'abl_iaffpro_version', IAFFPRO_VERSION_NUM );
}
add_action( 'admin_init', 'iaffpro_upgrader' );


// Load everything
require_once( IAFFPRO_AUTO_IMAGE_ATTRIBUTES_PRO_DIR . '/loader.php');

// Register activation hook (this has to be in the main plugin file.)
register_activation_hook( __FILE__, 'iaffpro_activate_plugin' );

/**
 * Update checker class
 *
 * @since	1.0
 */
require IAFFPRO_AUTO_IMAGE_ATTRIBUTES_PRO_DIR . '/includes/plugin-update-checker/plugin-update-checker.php';
$MyUpdateChecker = Puc_v4_Factory::buildUpdateChecker (
	'https://millionclues.org/superdome/?action=get_metadata&slug=auto-image-attributes-pro', // Metadata URL.
	__FILE__, 			// Full path to the main plugin file.
	'auto-image-attributes-pro', 	// Plugin slug. Usually it's the same as the name of the directory.
	72				// How often to check for updates (in hours)
);

/**
 * Add the license key and other details to query arguments.
 * 
 * Editing this does not work. I checked.
 * @since	1.0
 */
function iaffpro_license_key_filter($queryArgs) {
	
	// Get plugin Settings
	$settings = iaffpro_get_settings();
	
    if ( ! empty( $settings['registered_email'] ) && ! empty( $settings['license_key'] ) ) {
	    $queryArgs['registered_email'] 	= strtolower( $settings['registered_email'] );
	    $queryArgs['license_key'] 		= $settings['license_key'];
	    $queryArgs['product'] 			= 'IAP';
	    $queryArgs['installed_version'] = IAFFPRO_VERSION_NUM;
    }
	
    return $queryArgs;
}
$MyUpdateChecker->addQueryArgFilter( 'iaffpro_license_key_filter' );