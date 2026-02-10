<?php
/**
 * Template part for displaying posts in Post Carousel
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.4.3
 */

$alchemists_data = get_option( 'alchemists_data' );
$categories_toggle = isset( $alchemists_data['alchemists__posts-categories'] ) ? $alchemists_data['alchemists__posts-categories'] : 1;

$post_classes = array(
	'posts__item'
);

?>


<article <?php post_class( $post_classes ); ?>>
	<div class="posts__link-wrapper">
		<figure class="posts__thumb">
			<?php
			if ( has_post_thumbnail() ) {
				the_post_thumbnail('alchemists_thumbnail-n');
			} else {
				echo '<img src="' . get_theme_file_uri( '/assets/images/placeholder-500x280.jpg' ) . '" alt="" />';
			}
			?>
		</figure>
		<div class="posts__inner">

			<?php if ( $categories_toggle ) : ?>
				<?php alchemists_post_category_labels(); ?>
			<?php endif; ?>

			<?php the_title( '<h3 class="posts__title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h3>' ); ?>
			<?php alchemists_display_post_time(); ?>
			<?php alchemists_entry_meta_single( $date = 'off'); ?>
		</div>
	</div>
</article>
