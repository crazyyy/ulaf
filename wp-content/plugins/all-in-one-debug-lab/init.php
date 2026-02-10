<?php
/**
* All-in-One Debug Lab
* @version 1.0.0
*
**/

defined( 'ABSPATH' ) or exit;

define( 'AIODL_SYS_LOG_FILE_36bQ3n', 'debug.log');
define( 'AIODL_SET_EXT_FILE_36bQ3n', 'settings-debugging-extension.php');

/**
* function library file
*/
require_once AIODL_PLUGIN_DIR_36bQ3n.'functions/functions.php';
require_once AIODL_PLUGIN_DIR_36bQ3n.'admin/settings.php';
require_once AIODL_PLUGIN_DIR_36bQ3n.'admin/show-error-log.php';

/**
* Add menu in admin menu
*/ 
function aiodl_menu_plugin_36bQ3n(){
	add_menu_page(
		'All-in-One Debug Lab',
		'All-in-One Debug Lab',
		'manage_options',
		'slug_debug_plugin_36bQ3n',
		'aiodl_call_function_error_log_36bQ3n',
		'dashicons-code-standards'
	);
	add_submenu_page(
		'slug_debug_plugin_36bQ3n', 
		'Show Debug Log', 
		'Show Debug Log', 
		'manage_options', 
		'slug_debug_plugin_36bQ3n', 
		'aiodl_call_function_error_log_36bQ3n'
	);
	add_submenu_page(
		'slug_debug_plugin_36bQ3n', 
		'Setting', 
		'Setting', 
		'manage_options', 
		'submenu_slug_settings_36bQ3n', 
		'aiodl_call_function_settings_36bQ3n'
	);			
}
add_action('admin_menu','aiodl_menu_plugin_36bQ3n');


/**
* Add menu in admin bar menu 
*/ 
if (isset($_POST['aiodl-downloadLogFile-36bQ3n'])) {
    add_action('admin_init', 'aiodl_downloadLogFile_36bQ3n');
}

/**
* Register "style.css" 
*/
function aiodl_enqueue_styles_36bQ3n() { 
	wp_register_style( 'aiodl-plugin-admin-style-css',  plugins_url( '/admin/css/style.css', __FILE__ ), false, AIODL_VERSION_36bQ3n );
	wp_enqueue_style( 'aiodl-plugin-admin-style-css' );
}
add_action( 'admin_enqueue_scripts', 'aiodl_enqueue_styles_36bQ3n' );

/**
* Register "style.css" 
*/
function aiodl_enqueue_scripts_36bQ3n() {
    wp_register_script( 'aiodl-plugin-admin-js', plugins_url( '/admin/js/my-script.js', __FILE__ ), array('jquery') );
	wp_enqueue_script('aiodl-plugin-admin-js');
}
add_action( 'admin_enqueue_scripts', 'aiodl_enqueue_scripts_36bQ3n' );

/**
* We vaccinate the file of the designated names in the configuration file 
*/
function aiodl_plugin_activate_36bQ3n() { 	
	$fileName = 'wp-config.php';
	$configPathFile = get_home_path() . $fileName; 								 		
	$pluginFilePath = AIODL_PLUGIN_DIR_36bQ3n.'admin/' . AIODL_SET_EXT_FILE_36bQ3n;	
	$data = "<?php include_once '$pluginFilePath'; ?>";							
	file_put_contents($configPathFile, $data."\r\n".file_get_contents($configPathFile));	
}
register_activation_hook( AIODL_FILE_DIR_36bQ3n, 'aiodl_plugin_activate_36bQ3n' );

/**
* Remove the definition names file from the configuration file 
*/
function aiodl_plugin_deactivation_36bQ3n() {
	$fileName = 'wp-config.php';
	$configPathFile = get_home_path() . $fileName; 
	$pluginFilePath = AIODL_PLUGIN_DIR_36bQ3n.'admin/' . AIODL_SET_EXT_FILE_36bQ3n;
	file_put_contents($configPathFile, str_replace( "<?php include_once '$pluginFilePath'; ?>". "\r\n", "", file_get_contents($configPathFile)) );	
}
register_deactivation_hook( AIODL_FILE_DIR_36bQ3n, 'aiodl_plugin_deactivation_36bQ3n' );

/**
* Specify the translation folder 
*/
function aiodl_setup_languages_36bQ3n(){
	load_theme_textdomain( 'aiopd_plugin', plugin_dir_url( __FILE__ ) . '/languages' );
	load_plugin_textdomain( 'aiodl-debug-plugin', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'init', 'aiodl_setup_languages_36bQ3n' );