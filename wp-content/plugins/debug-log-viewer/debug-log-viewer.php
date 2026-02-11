<?php

namespace DebugLogViewer;

/**
 * Plugin Name: Debug Log Viewer
 * Description: Effortlessly view, search, and manage your WordPress debug.log in the admin dashboard. Real-time monitoring, email alerts, and filtering.
 * Author: lysyiweb
 * Version: 2.0.5
 * Tags: wp_debug, debugging, error log, debug
 * Requires PHP: 7.2
 * Tested up to: 6.8.1
 * Stable tag: 2.0.5
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
if (!defined('ABSPATH')) {
	exit;
}

use DebugLogViewer\Admin\Helpers\Constants;
use DebugLogViewer\Admin\Controllers\MenuController;
use DebugLogViewer\Admin\Controllers\HooksController;
use DebugLogViewer\Admin\Controllers\FreemiusController;

require_once realpath(__DIR__) . '/admin/helpers/constants.php';
require_once realpath(__DIR__) . '/admin/translations/phrases.php';

foreach (Constants::CONTROLLERS_LIST as $controller) {
	require_once realpath(__DIR__) . "/admin/controllers/{$controller}Controller.php";
}

// Initialize controllers after WordPress is fully loaded
add_action('init', function () {
	( new MenuController() )->init();
	( new HooksController() )->init();
	( new FreemiusController() )->init();
});
