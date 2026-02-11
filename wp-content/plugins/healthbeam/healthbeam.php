<?php
/**
 * Plugin Name: HealthBeam
 * Plugin URI: https://wordpress.org/plugins/healthbeam
 * Description: Advanced tools to monitor and debug your WordPress site with a modern React interface.
 * Version: 1.0.0
 * Author: WooSofts
 * Author Email: support@woosofts.com
 * Tested up to: 6.9
 * Requires PHP: 7.4
 * License: GPLv2
 * Text Domain: healthbeam
 */

namespace HealthBeam;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'HEALTHBEAM_PATH', plugin_dir_path( __FILE__ ) );
define( 'HEALTHBEAM_URL', plugin_dir_url( __FILE__ ) );
define( 'HEALTHBEAM_VERSION', '1.0.0' );

// Autoloader for includes
require_once HEALTHBEAM_PATH . 'includes/class-admin.php';
require_once HEALTHBEAM_PATH . 'includes/class-rest-api.php';

// Initialize classes
function init() {
	new Admin();
	new REST_API();
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\\init' );
