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

$post_thumbnail_size = 'alchemists_thumbnail';
if ( isset( $img_size ) ) {
	if ( 'default' != $img_size && !empty( $img_size ) ) {
		$post_thumbnail_size = $img_size;
	}
}

// get post category class
$post_class = alchemists_post_category_class();

$post_classes = array(
	'posts__item',
	'posts__item--card',
	'card',
	$post_class
);

?>

<div class="post-grid__item col-sm-6 col-lg-4">
	<article <?php post_class( $post_classes ); ?>>
		<?php if( has_post_thumbnail() ) { ?>
		<figure class="posts__thumb">

			<?php if ( $categories_toggle ) : ?>
				<?php alchemists_post_category_labels(); ?>
			<?php endif; ?>

			<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( $post_thumbnail_size ); ?></a>

			<?php do_action( 'alchemists_after_post_featured_img' ); ?>
		</figure>
		<?php } ?>

		<div class="posts__inner card__content">
			<?php if ( has_post_thumbnail() ) : ?>
			<a href="<?php the_permalink(); ?>" class="posts__cta"></a>
			<?php endif; ?>
			<?php alchemists_display_post_time(); ?>
			<?php the_title( '<h2 class="posts__title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>
		</div>

		<footer class="posts__footer card__footer">
			<?php alchemists_entry_footer(); ?>
		</footer>

	</article><!-- #post-## -->
</div>
