<?php
/**
 * Template part for displaying a message that posts cannot be found
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package alchemists
 */

?>

<section class="no-results not-found">
	<div class="page-content">
		<?php
		if ( is_home() && current_user_can( 'publish_posts' ) ) : ?>

			<div class="alert alert-info">
				<?php printf( wp_kses( __( 'Ready to publish your first post? <a href="%1$s">Get started here</a>.', 'alchemists' ), array( 'a' => array( 'href' => array() ) ) ), esc_url( admin_url( 'post-new.php' ) ) ); ?>
			</div>

		<?php elseif ( is_search() ) : ?>

			<div class="alert alert-warning">
				<strong><?php esc_html_e( 'Nothing Found!', 'alchemists' ); ?></strong> <?php esc_html_e( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'alchemists' ); ?>
			</div>

		<?php else : ?>

			<div class="alert alert-warning">
				<?php esc_html_e( 'It seems we can&rsquo;t find what you&rsquo;re looking for.', 'alchemists' ); ?>
			</div>

		<?php endif; ?>
	</div><!-- .page-content -->
</section><!-- .no-results -->
