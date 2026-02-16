<?php
// <Internal Doc Start>
/*
*
* @description: Uploaded images can be embedded into a post using custom sizes. WordPress comes out-of-the-box with a number of default image sizes. But you can also create your own dimensions which may be selected from the post editor.
* @tags: Attachment,media
* @group: Admin
* @name: Add Custom Image Sizes
* @type: PHP
* @status: draft
* @created_by: 1
* @created_at: 2026-02-13 23:34:42
* @updated_at: 2026-02-13 23:34:42
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
if ( function_exists( 'add_image_size' ) ) {
	add_image_size( 'new-size', 300, 100, true ); // (cropped)
}
add_filter( 'image_size_names_choose', 'my_image_sizes' );

if ( ! function_exists( 'my_image_sizes' ) ) {
	function my_image_sizes( $sizes ) {
		$addsizes = array(
			"new-size" => __( "New Size" ),
		);
		$newsizes = array_merge( $sizes, $addsizes );

		return $newsizes;
	}
}