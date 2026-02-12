<?php
/*
 * Plugin Name:       Admin Custom Description
 * Description:       Add custom description for each plugin in plugins page
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Amin Jabari Asl
 * Author URI:        https://ux.aminjabari.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       admin-custom-description
 * Domain Path:       /languages
*/

use AdminCustomDescription\Loader;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

const ADMIN_CUSTOM_DESCRIPTION_FILE = __FILE__;
define("ADMIN_CUSTOM_DESCRIPTION_DIR", plugin_dir_path(ADMIN_CUSTOM_DESCRIPTION_FILE));
require 'vendor/autoload.php';

Loader::getInstance();
