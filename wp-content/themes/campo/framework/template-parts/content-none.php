<?php
/**
 * Template part for displaying a message that posts cannot be found
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 */

?>
<section class="no-results not-found">
	<header class="entry-header">
		<h1 class="entry-title"><?php esc_html_e( 'Nothing Found', 'campo' ); ?></h1>
	</header><!-- .entry-header -->

	<div class="entry-content">
		<div class="entry-content-inner">
			<?php
			if ( is_home() && current_user_can( 'publish_posts' ) ) :

				printf(
					'<p>' . wp_kses(
						/* translators: 1: link to WP admin new post page. */
						__( 'Ready to publish your first post? <a href="%1$s">Get started here</a>.', 'campo' ),
						array(
							'a' => array(
								'href' => array(),
							),
						)
					) . '</p>',
					esc_url( admin_url( 'post-new.php' ) )
				);

			elseif ( is_search() ) :
				?>

				<p><?php esc_html_e( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'campo' ); ?></p>
				<?php
				get_search_form();

			else :
				?>

				<p><?php esc_html_e( 'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'campo' ); ?></p>
				<?php
				get_search_form();

			endif;
			?>
		</div><!-- .entry-content-inner -->
	</div><!-- .entry-content -->
</section><!-- .no-results -->
