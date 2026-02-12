<?php
/**
 * The plugin bootstrap file
 *
 *
 * @link              piwebsolution.com
 * @since             2.4.49.66
 * @package           Css_Js_Manager
 *
 * @wordpress-plugin
 * Plugin Name:       CSS JS Manager, Async JavaScript, Defer Render Blocking CSS
 * Plugin URI:        https://www.piwebsolution.com/css-js-manager-documentation/
 * Description:       You can control how to load CSS or JS file, Both CSS and JS can be loaded Asynchronous or Normal. There are many rules that allow you to remove them from different type of pages
 * Version:           2.4.49.66
 * Author:            Pi Websolution
 * Author URI: 		  https://www.piwebsolution.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       css-js-manager
 * Domain Path:       /languages
 * WC tested up to: 10.3.5
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

if(is_plugin_active( 'css-js-manager-pro/css-js-manager.php')){
	/** if free version is then deactivate the pro version */
    function pi_css_js_error_notice() {
        ?>
        <div class="error notice">
            <p><?php _e( 'You have pro version of Css JS Manager active please deactivate the pro version first and then activate free version','css-js-manager'); ?></p>
        </div>
        <?php
    }
    add_action( 'admin_notices', 'pi_css_js_error_notice' );
    deactivate_plugins(plugin_basename(__FILE__));
    return;
}else{


/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PLUGIN_CSS_JS_MANAGER', '2.4.49.66' );
define( 'CSS_JS_MANAGER_BUY_URL', 'https://www.piwebsolution.com/cart/?add-to-cart=751&&variation_id=755' );
define( 'CSS_JS_MANAGER_PRICE', '$25 ONLY' );
define( 'CSS_JS_MANAGER_DEVELOPMENT_MODE', false );
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
/**
 * Checking for the proversion
 */
function css_js_manager_pro_check(){
    if(in_array('css-js-manager-pro/css-js-manager.php', get_option('active_plugins'))){ 
        return true;
    }
	return false;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-css-js-manager-activator.php
 */
function activate_css_js_manager() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-css-js-manager-activator.php';
	Css_Js_Manager_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-css-js-manager-deactivator.php
 */
function deactivate_css_js_manager() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-css-js-manager-deactivator.php';
	Css_Js_Manager_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_css_js_manager' );
register_deactivation_hook( __FILE__, 'deactivate_css_js_manager' );

/**
 * Declare compatible with HPOS new order table 
 */
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-css-js-manager.php';

require_once plugin_dir_path(  __FILE__  ) . 'admin/general-option.php';

function pisol_cjma_plugin_link( $links ) {
	$links = array_merge( array(
        '<a href="' . esc_url( admin_url( '/admin.php?page=css-js-manager' ) ) . '">' . __( 'Settings' ) . '</a>'
	), $links );
	return $links;
}
add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'pisol_cjma_plugin_link' );
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_css_js_manager() {

	$plugin = new Css_Js_Manager();
	$plugin->run();

}
run_css_js_manager();

}