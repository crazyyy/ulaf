<?php
/**
 * Template part for displaying posts
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.1.0
 * @version   4.4.0
 */

$alchemists_data = get_option( 'alchemists_data' );
$categories_toggle = isset( $alchemists_data['alchemists__posts-categories'] ) ? $alchemists_data['alchemists__posts-categories'] : 1;
$excerpt_size      = isset( $excerpt_size ) ? $excerpt_size : '';

// get post category class
$post_class = alchemists_post_category_class();

$post_classes = array(
	'posts__item',
	'posts__item--card',
	'card',
	'card--block',
	$post_class
);
?>

<article <?php post_class( $post_classes ); ?>>
	<?php if( has_post_thumbnail() ) { ?>
	<figure class="posts__thumb">
		<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('alchemists_thumbnail-lg'); ?></a>
		<?php do_action( 'alchemists_after_post_featured_img' ); ?>
	</figure>
	<?php } ?>

	<div class="posts__inner card__content">
		<a href="<?php the_permalink(); ?>" class="posts__cta"></a>

		<?php if ( $categories_toggle ) : ?>
			<?php alchemists_post_category_labels(); ?>
		<?php endif; ?>

		<?php the_title( '<h2 class="posts__title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>
		<?php alchemists_display_post_time(); ?>
		<div class="posts__excerpt">
			<?php
			if ( ! empty( $excerpt_size ) ) {
				echo alchemists_string_limit_words( get_the_excerpt(), $excerpt_size );
			} else {
				the_excerpt();
			}
			?>

			<?php
				wp_link_pages( array(
					'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'alchemists' ),
					'after'  => '</div>',
				) );
			?>
		</div>
	</div>

	<footer class="posts__footer card__footer">
		<?php alchemists_entry_footer(); ?>
	</footer>

</article><!-- #post-## -->
