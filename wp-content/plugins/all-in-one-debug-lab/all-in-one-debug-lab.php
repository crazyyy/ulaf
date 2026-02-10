<?php
/**
* Plugin Name: 			All-in-One Debug Lab
* Plugin URI: 			https://www.yourpluginwebsiteurl.com/
* Description: 			All-in-One Debug Lab. Locate, easily and quickly, errors on your wordpress website. Debug, your wordpress site.
* Version: 				1.0.0
* Requires at least: 	3.4
* Requires 				PHP: 7.0
* Author: 				K P
* Author URI: 			https://www.yourwebsiteurl.com/
* License: 				GPLv2
* 
**/

if ( ! defined( 'ABSPATH' ) ) exit; 

define( 'AIODL_VERSION_36bQ3n'		, '1.0.0' );

define( 'AIODL_FILE_DIR_36bQ3n'		, __FILE__);
define( 'AIODL_PLUGIN_DIR_36bQ3n'	, plugin_dir_path(__FILE__));

require_once AIODL_PLUGIN_DIR_36bQ3n . 'init.php'; 