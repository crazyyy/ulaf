=== WP Helper ===
Contributors: lucdecri
Tags: plugins, framework, posttype, attachement, 
Requires at least: 3.0
Tested up to: 3.5.1
Stable tag: 0.3

It provides additional functions and feature that allow you to simplify the development in your plugin  new posttype, new taxonomies, new pages and menu administrator, etc.


== Description ==
wp_helper have 4 distinct plugin. 

* posttype_helper
* taxonomy_helper
* attachment_helper
* adminpage_helper

each provides additional features that allow you to simplify the development of new posttype, new taxonomies, new pages and menu administrator, etc.

Detailed list of additional function is coming soon

You must check if wp_helper is actived before active your plugin, with similar code :
{{{

if (defined('WP_HELPER')) { 
  // your code here
} else { // if defined(WP_HELPER)
	add_action('admin_notices', 'myplugin_noframework_notice');
}

function myplugin_noframework_notice(){
	global $pagenow;
	if ( $pagenow == 'plugins.php' ) {
		echo '<div class="updated">
             <p>To activate this plugin you must install and activate <b>WP HELPER</b> plugin .</p>
         </div>';
	}
}


}}}

== Installation ==

install with standard procedure

== Screenshots ==

none

== Frequently Asked Questions ==

contact me for each questions...

