<?php

namespace DebugLogViewer\Admin\Controllers;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
class MenuController {

	public function init() {
		add_action('admin_menu', function () {
			$role    = 'edit_pages';
			$name    = __('Debug Log Viewer', 'debug-log-viewer');
			$slug    = 'debug-log-viewer';
			$handler =  [ '\DebugLogViewer\Front\Views\Pages\LogView', 'render' ];
			$icon    = plugin_dir_url(__FILE__) . '/../../../front/assets/img/logo-grayscale.svg';
			add_menu_page($name, $name, $role, $slug, $handler, $icon);
		});
	}
}
