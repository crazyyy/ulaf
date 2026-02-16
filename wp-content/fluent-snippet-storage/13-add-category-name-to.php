<?php
// <Internal Doc Start>
/*
*
* @description: Add a category name to the body tag classes
* @tags: 
* @group: 
* @name: Add Category Name to body_class
* @type: PHP
* @status: draft
* @created_by: 1
* @created_at: 2026-02-13 23:43:58
* @updated_at: 2026-02-13 23:43:58
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
add_filter( 'body_class', function ( $classes ) {
	if ( is_single() ) {
		global $post;
		foreach ( ( get_the_category( $post->ID ) ) as $category ) {
			$classes[] = $category->category_nicename;
		}
	}

	return $classes;
} );