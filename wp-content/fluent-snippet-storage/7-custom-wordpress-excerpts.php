<?php
// <Internal Doc Start>
/*
*
* @description: Sometimes you may need to limit how many words are in the excerpt, with this snippet you can create your own custom excerpt (my_excerpts) replacing the original.
* @tags: 
* @group: Template
* @name: Custom WordPress Excerpts
* @type: PHP
* @status: draft
* @created_by: 1
* @created_at: 2026-02-13 23:33:56
* @updated_at: 2026-02-13 23:33:56
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
if ( ! function_exists( 'my_excerpts' ) ) {
	function my_excerpts( $content = false ) {
		$excerpt_length = 55;
		$words          = explode( ' ', $content, $excerpt_length + 1 );
		if ( count( $words ) > $excerpt_length ) :
			array_pop( $words );
			array_push( $words, '...' );
			$content = implode( ' ', $words );
		endif;

		return $content;
	}
}
add_filter( 'the_excerpt', 'my_excerpts' );

// Use 'echo my_excerpts();' only for shortcode mode
// echo my_excerpts();