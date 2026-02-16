<?php
// <Internal Doc Start>
/*
*
* @description: Want additional dashboard widgets to give some info to your clients?
* @tags: 
* @group: Admin
* @name: Add Custom Dashboard Widgets
* @type: PHP
* @status: draft
* @created_by: 1
* @created_at: 2026-02-13 23:36:16
* @updated_at: 2026-02-13 23:36:16
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
		wp_add_dashboard_widget( 'custom_help_widget', 'Theme Support', 'custom_dashboard_help' );
	}
}

if ( ! function_exists( 'custom_dashboard_help' ) ) {
	function custom_dashboard_help() {
		echo '<p>Welcome to Custom Blog Theme! Need help? Contact the developer <a href="mailto:yourusername@gmail.com">here</a>. For WordPress Tutorials visit: <a href="http://www.wpbeginner.com" target="_blank">WPBeginner</a></p>';
	}
}
add_action( 'wp_dashboard_setup', 'my_custom_dashboard_widgets' );