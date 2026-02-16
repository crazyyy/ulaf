<?php
// <Internal Doc Start>
/*
*
* @description: 
* @tags: admin column,media
* @group: Admin
* @name: Add Media File Size Column
* @type: PHP
* @status: published
* @created_by: 1
* @created_at: 2026-02-13 22:55:59
* @updated_at: 2026-02-13 22:56:03
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
add_filter( 'manage_upload_columns', function ( $columns ) {
	$columns ['file_size'] = esc_html__( 'File size' );
	return $columns;
} );

add_action( 'manage_media_custom_column', function ( $column_name, $media_item ) {
	if ( 'file_size' !== $column_name || ! wp_attachment_is_image( $media_item ) ) {
		return;
	}
	$filesize = size_format( filesize( get_attached_file( $media_item ) ), 2 );
	echo esc_html( $filesize );
}, 10, 2 );