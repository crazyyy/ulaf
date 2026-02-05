<?php
/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 */

get_header();
?>

	<main id="primary" class="site-main">

		<?php 
		if ( have_posts() ) : 
		
			while ( have_posts() ) :
				the_post();
				get_template_part( 'framework/template-parts/content-archive', get_post_type() );
			endwhile;
			
			the_posts_navigation();

		else :
			get_template_part( 'framework/template-parts/content', 'none' );
		endif;
		?>

	</main><!-- main.site-main -->

<?php
get_sidebar();
get_footer();
