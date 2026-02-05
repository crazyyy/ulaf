<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 */

get_header(); 
?>

	<main id="primary" class="site-main">

		<?php
		while ( have_posts() ) :
			the_post();

			get_template_part( 'framework/template-parts/content', get_post_type() );
			
			get_template_part( 'framework/template-parts/about-author' );

			// If comments are open or we have at least one comment, load up the comment template.
			if ( comments_open() || get_comments_number() ) :
				comments_template();
			endif;

			boldthemes_the_post_navigation();

		endwhile; // End of the loop.
		?>

	</main><!-- main.site-main -->

<?php
get_sidebar();
get_footer();
