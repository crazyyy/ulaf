<?php
/**
 * Template part for displaying Post Navigation a Single Post Page
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.0.0
 * @version   4.2.10
 */

$alchemists_data = get_option( 'alchemists_data' );
$categories_toggle = isset( $alchemists_data['alchemists__posts-categories'] ) ? $alchemists_data['alchemists__posts-categories'] : 1;

// check if prev, next post exist
$prevPost = get_previous_post();
$nextPost = get_next_post();
?>
<!-- Next/Prev Posts -->
<div class="post-related">

	<div class="posts posts--tile posts--tile-alt posts--tile-alt-noborder post-grid row no-gutters">

		<?php
		$prevPost = get_previous_post();

		if ( ! empty( $prevPost ) ) {
			$args = array(
				'posts_per_page' => 1,
				'include' => $prevPost->ID
			);

			$prevPost = get_posts( $args );
			foreach ( $prevPost as $post ) {
				setup_postdata( $post );

				get_template_part( 'template-parts/post/nav/content', 'nav-3' );

				wp_reset_postdata();
			} //end foreach
		} // end if

		$nextPost = get_next_post();

		if ( ! empty( $nextPost ) ) {
			$args = array(
				'posts_per_page' => 1,
				'include' => $nextPost->ID
			);

			$nextPost = get_posts( $args );
			foreach ( $nextPost as $post ) {
				setup_postdata( $post );

				get_template_part( 'template-parts/post/nav/content', 'nav-3' );

				wp_reset_postdata();
			} //end foreach
		} // end if
		?>
	</div>

</div>
<!-- Next/Prev / End -->
