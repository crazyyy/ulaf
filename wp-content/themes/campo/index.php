<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 */
 
// File Security Check
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

	<main id="primary" class="site-main">

		<?php
		if ( have_posts() ) :

			/* Start the Loop */
			while ( have_posts() ) :
				the_post();

				/*
				 * Include the Post-Type-specific template for the content.
				 * If you want to override this in a child theme, then include a file
				 * called content-___.php (where ___ is the Post Type name) and that will be used instead.
				 */
				if ( is_singular() ) :
					get_template_part( 'framework/template-parts/content', get_post_type() );
				else :
					get_template_part( 'framework/template-parts/content-archive', get_post_type() );
				endif;

			endwhile;

			boldthemes_pagination();

		else :

			get_template_part( 'framework/template-parts/content', 'none' );

		endif;
		?>

	</main><!-- main.site-main -->

<?php
get_sidebar();
get_footer();
