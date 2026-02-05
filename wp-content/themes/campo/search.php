<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 */

get_header();
?>

	<main id="primary" class="site-main">

		<?php if ( have_posts() ) : ?>
			<?php if ( boldthemes_get_option( 'default_headline_height' ) == 'none' ): ?> 
				<header class="entry-header">
					<h1 class="entry-title">
						<?php
						printf( esc_html__( 'Search Results for: %s', 'campo' ), '<span>' . get_search_query() . '</span>' );						
						?>
					</h1>
				</header><!-- .page-header -->

			<?php endif; ?>
			
			<?php
			/* Start the Loop */
			while ( have_posts() ) :
				the_post();

				/**
				 * Run the loop for the search to output the results.
				 * If you want to overload this in a child theme then include a file
				 * called content-search.php and that will be used instead.
				 */
				get_template_part( 'framework/template-parts/content', 'search' );

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
