<?php
// <Internal Doc Start>
/*
*
* @description: Change the name of the uploaded file
* @tags: Attachment
* @group: Admin
* @name: Change uploaded file name
* @type: PHP
* @status: draft
* @created_by: 1
* @created_at: 2026-02-13 23:27:27
* @updated_at: 2026-02-13 23:27:31
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
add_filter( 'wp_handle_upload_prefilter', function ( $file ) {
	$file['name'] = 'wbcr-files-' . $file['name'];

	return $file;
} );