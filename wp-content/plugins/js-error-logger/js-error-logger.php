<?php

/**
 * Plugin Name: JS Error Logger
 * Description: This plugin logs most javascript errors, and displays them in a dashboard widget
 * Version:     1.3.1
 * Author:      JFG Media
 * Author URI:  https://jfgmedia.com
 * Text Domain: js-error-logger
 * Domain Path: /lang
 * License: GPLv2 or later
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define('JSERRLOG_PLUGIN_FILE', __FILE__);
define('JSERRLOG_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
if (!defined('JSERRLOG_LOG_DIR')) {
    define('JSERRLOG_LOG_DIR', wp_upload_dir()['path'] . '/js-error-logger-log/');
}
define('JSERRLOG_OPTION', 'js-error-logger');

require_once __DIR__ . '/classes/Plugin.php';
require_once __DIR__ . '/classes/Logger.php';
register_uninstall_hook(__FILE__, ['JSERRLOG\Plugin','uninstall_plugin']);
register_activation_hook(__FILE__, ['JSERRLOG\Plugin','activate_plugin']);
register_deactivation_hook(__FILE__,['JSERRLOG\Plugin','deactivate_plugin']);
add_action('init', ['JSERRLOG\Plugin', 'init']);