<?php
// <Internal Doc Start>
/*
*
* @description: Adds the state (label) of the post in the Posts list of the dashboard. For example, it shows when the post was saved as a draft, sent for the approval, etc.
* @tags: admin column
* @group: Admin
* @name: Add display post states
* @type: PHP
* @status: published
* @created_by: 1
* @created_at: 2026-02-13 23:36:45
* @updated_at: 2026-02-13 23:37:02
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
if ( ! function_exists( 'wbcr_add_page_mark' ) ) {
	function wbcr_add_page_mark( $post_states, $post ) {
		$frase        = 'my';
		$frase_length = strlen( $frase ) + 1;

		if (
			'post' === $post->post_type
			&& (				
				$frase === strtolower( $post->post_title )
				|| false !== stripos( $post->post_title, $frase . ' ' )
				|| strtolower( substr( $post->post_title, - $frase_length ) ) === ' ' . $frase
			)
		) {
			$post_states[] = 'My Post';
		}

		return $post_states;
	}
}
add_filter( 'display_post_states', 'wbcr_add_page_mark', 10, 2 );