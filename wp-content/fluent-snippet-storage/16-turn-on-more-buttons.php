<?php
// <Internal Doc Start>
/*
*
* @description: Add more buttons to the text editor
* @tags: Editor
* @group: Admin
* @name: Turn On More Buttons in the WordPress Visual Editor
* @type: PHP
* @status: draft
* @created_by: 1
* @created_at: 2026-02-13 23:46:17
* @updated_at: 2026-02-13 23:46:17
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
add_filter( 'mce_buttons_3', function ( $buttons ) {
	$buttons[] = 'fontselect';
	$buttons[] = 'fontsizeselect';
	$buttons[] = 'styleselect';

	return $buttons;
} );