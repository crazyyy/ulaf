<?php
// <Internal Doc Start>
/*
*
* @description: Upload SVG files using Media uploader. By default, uploading SVG files is disabled
* @tags: 
* @group: 
* @name: Allow SVG through WordPress Media Uploader
* @type: PHP
* @status: published
* @created_by: 1
* @created_at: 2026-02-13 23:44:28
* @updated_at: 2026-02-13 23:45:06
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
add_filter( 'upload_mimes', function ( $mimes ) {
	$mimes['svg'] = 'image/svg+xml';

	return $mimes;
} );