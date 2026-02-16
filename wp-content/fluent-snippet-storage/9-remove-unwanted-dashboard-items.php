<?php
// <Internal Doc Start>
/*
*
* @description: If you’re like me then you prefer a clean interface without too many distractions. The WordPress dashboard includes a lot of extra stuff like news, latest plugins, quick draft, and other similar features. This PHP code allows you to disable any unwanted items on the dashboard panel.
* @tags: 
* @group: Admin
* @name: Remove Unwanted Dashboard Items
* @type: PHP
* @status: draft
* @created_by: 1
* @created_at: 2026-02-13 23:35:30
* @updated_at: 2026-02-13 23:35:30
* @is_valid: 1
* @updated_by: 1
* @priority: 10
* @run_at: backend
* @load_as_file: 
* @load_in_block_editor: 
* @condition: {"status":"no","run_if":"assertive","items":[[]]}
*/
?>
<?php if (!defined("ABSPATH")) { return;} // <Internal Doc End> ?>
<?php
if ( ! function_exists( 'my_custom_dashboard_widgets' ) ) {
	function my_custom_dashboard_widgets() {
		global $wp_meta_boxes;
		// Right Now - Comments, Posts, Pages at a glance
		unset( $wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now'] );
		// Recent Comments
		unset( $wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments'] );
		// Incoming Links
		unset( $wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links'] );
		// Plugins - Popular, New and Recently updated WordPress Plugins
		unset( $wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins'] );

		// Wordpress Development Blog Feed
		unset( $wp_meta_boxes['dashboard']['side']['core']['dashboard_primary'] );
		// Other WordPress News Feed
		unset( $wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary'] );
		// Quick Press Form
		unset( $wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press'] );
		// Recent Drafts List
		unset( $wp_meta_boxes['dashboard']['side']['core']['dashboard_recent_drafts'] );
	}
}
add_action( 'wp_dashboard_setup', 'my_custom_dashboard_widgets' );