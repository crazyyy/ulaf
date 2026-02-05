<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 */

get_header();
?>

	<main id="primary" class="site-main">
	
	<?php
	
	$page_slug = boldthemes_get_option( 'error_404_page_slug' );
	if ( $page_slug != '' ) :
		$page_id = boldthemes_get_id_by_slug( $page_slug );
		if ( ! is_null( $page_id ) ) :
			$page = get_post( $page_id );
			$content = $page->post_content;
			if ( get_post_type() != 'attachment' && ! is_404() ) $content = apply_filters( 'the_content', $content );
			$content = do_shortcode($content);
			$content = preg_replace( '/data-edit_url="(.*?)"/s', 'data-edit_url="' . get_edit_post_link( $page_id, '' ) . '"', $content );
			echo str_replace( ']]>', ']]&gt;', $content );
		endif;
	else : 
	?>
		<section class="error-404 not-found" style="<?php echo esc_attr( boldthemes_get_style_from_color_scheme( [ 'css_var1' => '--primary-color', 'css_var2' => '--secondary-color', 'color_scheme_id' => boldthemes_get_option( 'error_404_color_scheme' ) ] ) ); ?>">
			<header class="entry-header">
				<?php if ( boldthemes_get_option( 'default_headline_height' ) == 'none' ): ?> 
				<h1 class="entry-title"><?php esc_html_e( '404', 'campo' ); ?></h1>
				<?php endif; ?>
				<h2 class="entry-title"><?php esc_html_e( 'Oops! That page can&rsquo;t be found.', 'campo' ); ?></h2>
			</header><!-- .page-header -->

			<div class="page-content">
				<p><?php esc_html_e( 'It looks like nothing was found at this location. Maybe try one of the links below or a search?', 'campo' ); ?></p>
				<?php get_search_form(); ?>
			</div><!-- .page-content -->
		</section><!-- .error-404 -->
	
	<?php
	
	endif;
	
	?>

	</main><!-- main.site-main -->

<?php
get_footer();
