<?php
// <Internal Doc Start>
/*
*
* @description: 
* @tags: Attachment,media
* @group: Admin
* @name: Make upload filenames lowercase
* @type: PHP
* @status: published
* @created_by: 1
* @created_at: 2026-02-13 23:22:54
* @updated_at: 2026-02-13 23:22:59
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
add_filter( 'sanitize_file_name', 'mb_strtolower' );