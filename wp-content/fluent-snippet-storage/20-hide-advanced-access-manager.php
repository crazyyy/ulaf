<?php
// <Internal Doc Start>
/*
*
* @description: This snippet hides the AAM (Advanced Access Manager) metabox on posts.


* @tags: 
* @group: 
* @name: Hide Advanced Access Manager Metabox
* @type: PHP
* @status: draft
* @created_by: 1
* @created_at: 2026-02-13 23:54:54
* @updated_at: 2026-02-13 23:54:54
* @is_valid: 1
* @updated_by: 1
* @priority: 10
* @run_at: all
* @load_as_file: 
* @load_in_block_editor: 
* @condition: {"status":"no","run_if":"assertive","items":[[]]}
*/
?>
<?php if (!defined("ABSPATH")) { return;} // <Internal Doc End> ?>
<?php
function wpcb_hide_aam_metaboxes() {
	$screen = get_current_screen();
	if ( !$screen ) {
		return;
	}

	remove_meta_box('aam-access-manager', $screen->id, 'advanced');
}

add_action('add_meta_boxes', 'wpcb_hide_aam_metaboxes', 20);