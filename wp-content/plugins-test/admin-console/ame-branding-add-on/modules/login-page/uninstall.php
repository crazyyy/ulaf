<?php
//Remove login page settings.
if ( defined('ABSPATH') && defined('WP_UNINSTALL_PLUGIN') ) {
	delete_option('ws_ame_login_page_settings');
	delete_site_option('ws_ame_login_page_settings');
}