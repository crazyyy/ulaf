<?php
// <Internal Doc Start>
/*
*
* @description: Add action links displayed for each plugin in the Plugins list table.
* @tags: Plugin
* @group: Admin
* @name: Add action links to plugin
* @type: PHP
* @status: published
* @created_by: 1
* @created_at: 2026-02-13 23:38:51
* @updated_at: 2026-02-13 23:39:14
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
if ( ! function_exists( 'wbcr_add_plugin_link' ) ) {
	function wbcr_add_plugin_link( $actions, $plugin_file ) {
		$my_plugin_name = 'anti-spam';

		if ( false === strpos( $plugin_file, $my_plugin_name ) ) {
			return $actions;
		}

		$settings_link = '<a href="options-general.php?page=' . $my_plugin_name . '">Settings</a>';
		array_unshift( $actions, $settings_link );

		return $actions;
	}
}
add_filter( 'plugin_action_links', 'wbcr_add_plugin_link', 10, 2 );