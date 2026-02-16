<?php
// <Internal Doc Start>
/*
*
* @description: 
* @tags: admin column
* @group: Admin
* @name: Add ID column in admin tables
* @type: PHP
* @status: published
* @created_by: 1
* @created_at: 2026-02-13 22:58:12
* @updated_at: 2026-02-13 22:59:22
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
add_action( 'admin_init', function () {
// Get all public post types
	$post_types = get_post_types( array(), 'names' );

	function wpcode_add_post_id_column( $columns ) {
		$columns['wpcode_post_id'] = 'ID'; // 'ID' is the column title

		return $columns;
	}

	function wpcode_show_post_id_column_data( $column, $post_id ) {
		if ( 'wpcode_post_id' === $column ) {
			echo '<code>' . absint( $post_id ) . '</code>';
		}
	}

	foreach ( $post_types as $post_type ) {
		// Add new column to the posts list
		add_filter( "manage_{$post_type}_posts_columns", 'wpcode_add_post_id_column' );

		// Fill the new column with the post ID
		add_action( "manage_{$post_type}_posts_custom_column", 'wpcode_show_post_id_column_data', 10, 2 );
	}
} );