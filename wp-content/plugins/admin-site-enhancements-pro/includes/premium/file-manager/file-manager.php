<?php
/**
 * ASE Pro Module: File Manager
 *
 * @since 8.1.4
 */

namespace ASENHA\FileManager;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! defined( 'ASENHA_FILE_MANAGER_VERSION' ) ) {
	define( 'ASENHA_FILE_MANAGER_VERSION', ASENHA_VERSION );
}

/**
 * Plugin directory path.
 */
if ( ! defined( 'ASENHA_FILE_MANAGER_PATH' ) ) {
	define( 'ASENHA_FILE_MANAGER_PATH', plugin_dir_path( __FILE__ ) );
}

/**
 * Plugin directory URL.
 */
if ( ! defined( 'ASENHA_FILE_MANAGER_URL' ) ) {
	define( 'ASENHA_FILE_MANAGER_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * Plugin basename.
 */
if ( ! defined( 'ASENHA_FILE_MANAGER_BASENAME' ) ) {
	define( 'ASENHA_FILE_MANAGER_BASENAME', plugin_basename( __FILE__ ) );
}

require_once ASENHA_FILE_MANAGER_PATH . 'includes/class-plugin.php';
require_once ASENHA_FILE_MANAGER_PATH . 'includes/class-admin.php';
require_once ASENHA_FILE_MANAGER_PATH . 'includes/class-rest-api.php';
require_once ASENHA_FILE_MANAGER_PATH . 'includes/class-file-operations.php';

/**
 * Initialize the module.
 *
 * @return void
 */
function init_asenha_file_manager() : void {
	Plugin::get_instance();
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\init_asenha_file_manager' );

