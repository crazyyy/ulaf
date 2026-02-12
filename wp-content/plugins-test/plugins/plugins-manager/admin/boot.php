<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once WTBP_PLUGIN_PATH . '/admin/includes/base/class.page-base.php';
require_once WTBP_PLUGIN_PATH . '/admin/class.page-base-settings.php';
require_once WTBP_PLUGIN_PATH . '/admin/pages/class.page-settings.php';
require_once WTBP_PLUGIN_PATH . '/admin/includes/class-plugin-page-customizer.php';

$pages_dir = WTBP_PLUGIN_PATH . '/admin/pages/';
foreach ( scandir( $pages_dir ) as $page ) {
	if ( $page == '.' || $page == '..' ) {
		continue;
	}

	require_once $pages_dir . $page;
}
