<?php
/*
Plugin Name: WP Helper - attachment
Description: Some function to help developer to create and to extend attachment  
Plugin URI:  http://www.decristofano.it/
Version:     0.9
Author:      lucdecri
Author URI:  http://www.decristofano.it/
*/

define('WP_HELPER_ATTACHMENT','0.9');
require_once ('helper_basic.php');

function register_attachment_fields($form_field) {
global $admin_attachment_data;
    foreach($form_field as $k=>$d)
	    $admin_attachment_data[$k]=$d;
}





function ah_attachment_first() {
	// ensure path to this file is via main wp plugin path
	$wp_path_to_this_file = preg_replace('/(.*)plugins\/(.*)$/', WP_PLUGIN_DIR."/$2", __FILE__);
	$this_plugin = plugin_basename(trim($wp_path_to_this_file));
	$active_plugins = get_option('active_plugins');
	$this_plugin_key = array_search($this_plugin, $active_plugins);
	if ($this_plugin_key) { // if it's 0 it's the first plugin already, no need to continue
		array_splice($active_plugins, $this_plugin_key, 1);
		array_unshift($active_plugins, $this_plugin);
		update_option('active_plugins', $active_plugins);
	}
}


function ah_attachment_hooks() {
   // add_filter("attachment_fields_to_edit", "ah_attachment_fields_to_edit", null, 2);
   // add_filter("attachment_fields_to_save", "ah_attachment_fields_to_save", null, 2);
   // add_action('activated_plugin', 'ah_attachment_first');
}

ah_attachment_hooks();

?>
