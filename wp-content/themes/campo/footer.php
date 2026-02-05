<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 */

?>
	</div><!-- .site-content -->
	<footer id="colophon" class="site-footer">
		<div class="site-footer-widgets">
			<?php get_template_part( 'framework/template-parts/footer-widgets' ); ?>
		</div><!-- .site-footer-widgets -->
		<div class="site-footer-page">
			<?php
			$page_slug = boldthemes_get_option( 'footer_page_slug' );
			if ( $page_slug != '' ) {
				$page_id = boldthemes_get_id_by_slug( $page_slug );
				if ( ! is_null( $page_id ) ) {
					$page = get_post( $page_id );
					$content = $page->post_content;
					if ( get_post_type() != 'attachment' && ! is_404() ) $content = apply_filters( 'the_content', $content );
					$content = do_shortcode( $content );
					echo str_replace( ']]>', ']]&gt;', $content );
				}
			}

			?>
		</div><!-- .footer-page -->
	</footer><!-- footer.site-footer -->
</div><!-- .site -->

<?php wp_footer(); ?>

</body>
</html>
