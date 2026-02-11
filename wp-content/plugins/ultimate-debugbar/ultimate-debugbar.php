<?php
defined('ABSPATH') or die('No script kiddies please!');

/*
Plugin Name: Ultimate DebugBar
Plugin URI: http://wordpress.org/plugins/ultimate-debugbar/
Description: PHP debugbar implementation for Wordpress. Monitor configuration, request times, hook execution times, database queries and more on each page of your website!
Author: Nemanja Avramović
Version: 0.2
Author URI: https://avramovic.info
*/


define('ULTIMATE_DEBUGBAR_PLUGIN_FILE', __FILE__);
defined('SAVEQUERIES') or define('SAVEQUERIES', true);
defined('ULTIMATE_DEBUG_AJAX') or define('ULTIMATE_DEBUG_AJAX', true);

require plugin_dir_path(ULTIMATE_DEBUGBAR_PLUGIN_FILE).'vendor/autoload.php';

if (!function_exists('get_plugins')) {
    require_once ABSPATH.'wp-admin/includes/plugin.php';
}

\Avram\WPDebugBar\WPDebugBar::run();