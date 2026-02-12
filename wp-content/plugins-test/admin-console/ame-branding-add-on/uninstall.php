<?php
if ( defined('ABSPATH') && defined('WP_UNINSTALL_PLUGIN') ) {
	include dirname(__FILE__) . '/modules/login-page/uninstall.php';
	include dirname(__FILE__) . '/modules/branding/uninstall.php';
	include dirname(__FILE__) . '/modules/admin-colors/uninstall.php';
}