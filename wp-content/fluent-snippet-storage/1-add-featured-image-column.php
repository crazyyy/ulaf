<?php
// <Internal Doc Start>
/*
*
* @description: 
* @tags: 
* @group: Admin
* @name: Add Featured Image Column
* @type: PHP
* @status: published
* @created_by: 1
* @created_at: 2026-02-13 22:53:58
* @updated_at: 2026-02-13 22:54:18
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
add_filter( 'manage_posts_columns', function ( $columns ) {
	// You can change this to any other position by changing 'title' to the name of the column you want to put it after.
	$move_after     = 'title';
	$move_after_key = array_search( $move_after, array_keys( $columns ), true );

	$first_columns = array_slice( $columns, 0, $move_after_key + 1 );
	$last_columns  = array_slice( $columns, $move_after_key + 1 );

	return array_merge(
		$first_columns,
		array(
			'featured_image' => __( 'Featured Image' ),
		),
		$last_columns
	);
} );

add_action( 'manage_posts_custom_column', function ( $column ) {
	if ( 'featured_image' === $column ) {
		the_post_thumbnail( array( 300, 80 ) );
	}
} );