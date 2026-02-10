<?php
/**
 * Plugin Name: LH JavaScript Error Log
 * Plugin URI: https://lhero.org/portfolio/lh-javascript-error-log/
 * Description: Catch and log JavaScript errors locally.
 * Version: 1.00
 * Author: Peter Shaw
 * Requires PHP: 7.0
 * Author URI: https://shawfactor.com
 * Text Domain: lh_js_error_log
 * Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if (!class_exists('LH_Javascript_error_log_plugin')) {

class LH_Javascript_error_log_plugin {

private static $instance;

static function return_plugin_namespace(){

return 'lh_js_error_log';

}

static function curpageurl() {
	$pageURL = 'http';

	if ((isset($_SERVER["HTTPS"])) && ($_SERVER["HTTPS"] == "on")){
		$pageURL .= "s";
}

	$pageURL .= "://";

	if (($_SERVER["SERVER_PORT"] != "80") and ($_SERVER["SERVER_PORT"] != "443")){
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];

	} else {
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];

}

	return $pageURL;
}


    // The primary sanity check, automatically disable the plugin on activation if it doesn't
    // meet minimum requirements.

static function activation_check() {
if (empty(WP_DEBUG) or empty(WP_DEBUG_LOG) ) {

            deactivate_plugins( plugin_basename( __FILE__ ) );

            wp_die( __( 'This plugin requires both WP_DEBUG and WP_DEBUG_LOG to be enabled, it has not been activated. More information on enabling debugging is available <a href="https://wordpress.org/support/article/debugging-in-wordpress/">here</a>.', self::return_plugin_namespace() ) );

        }

    }


public function register_core_scripts() {
        

if (!class_exists('LH_Register_file_class')) {
     
include_once('includes/lh-register-file-class.php');
    
}

$add_array = array('id="'.self::return_plugin_namespace().'-script"');
$add_array[] = 'data-ajaxurl="'.admin_url('admin-ajax.php').'"';
$add_array[] = 'data-nonce="'.wp_create_nonce(self::return_plugin_namespace()).'"';
$add_array[] = 'data-current_url="'.self::curpageurl().'"';

$lh_javacript_error_log_script = new LH_Register_file_class(  self::return_plugin_namespace().'-script', plugin_dir_path( __FILE__ ).'scripts/lh-javascript-error-log.js',plugins_url( '/scripts/lh-javascript-error-log.js', __FILE__ ), true, array(), false, $add_array);

unset($add_array);

wp_enqueue_script( self::return_plugin_namespace().'-script');    

}








	public function js_log_error() {
		if ( isset( $_REQUEST['msg'] ) && isset( $_REQUEST['line'] ) && isset( $_REQUEST['url'] ) && !empty( $_REQUEST['nonce'] ) && wp_verify_nonce( $_REQUEST['nonce'], self::return_plugin_namespace())) {
			$error = filter_input_array( INPUT_POST, array(
				'msg' => FILTER_SANITIZE_STRING,
				'url' => FILTER_SANITIZE_STRING,
				'line' => FILTER_SANITIZE_STRING,
				'current_url' => FILTER_SANITIZE_STRING,
			));

			error_log( 'JavaScript Error: ' . html_entity_decode( $error['msg'], ENT_QUOTES ) . ', file: ' . $error['url'] . ': ' . $error['line']. ' initiated by '. $error['current_url'] );
			wp_send_json( $error );
		}
		wp_die();
	}
	
	
	public function plugin_init(){
	    
	  	//register the logging script
        add_action('wp_loaded', array($this,'register_core_scripts'));
		
		
		add_action( 'wp_ajax_'.self::return_plugin_namespace(), array( $this, 'js_log_error' ) );
		add_action( 'wp_ajax_nopriv_'.self::return_plugin_namespace(), array( $this, 'js_log_error' ) );  
	    
	    
	}
	
	/**
     * Gets an instance of our plugin.
     *
     * using the singleton pattern
     */
     
    public static function get_instance(){
        if (null === self::$instance) {
            self::$instance = new self();
        }
 
        return self::$instance;
    }
	
		public function __construct() {
		    
    if (!empty(WP_DEBUG) && !empty(WP_DEBUG_LOG)) {

    //run our hooks on plugins loaded to as we may need checks       
    add_action( 'plugins_loaded', array($this,'plugin_init'));
    
    
    }
		


	}
	
}

$lh_javascript_error_log_instance = LH_Javascript_error_log_plugin::get_instance();
register_activation_hook( __FILE__, array( 'LH_Javascript_error_log_plugin', 'activation_check' ) );

}

?>