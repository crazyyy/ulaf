<?php
//Remove color settings.
if ( defined('ABSPATH') && defined('WP_UNINSTALL_PLUGIN') ) {
	delete_option('ws_ame_admin_color_scheme_css');
	delete_site_option('ws_ame_admin_color_scheme_css');

	delete_option('ws_ame_admin_colors');
	delete_site_option('ws_ame_admin_colors');
}