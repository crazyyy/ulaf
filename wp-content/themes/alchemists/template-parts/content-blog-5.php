<?php
/**
 * Template part for displaying posts
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.1.0
 * @version   4.4.3
 */

$alchemists_data = get_option( 'alchemists_data' );
$categories_toggle = isset( $alchemists_data['alchemists__posts-categories'] ) ? $alchemists_data['alchemists__posts-categories'] : 1;

// get post category class
$post_class = alchemists_post_category_class();

$post_classes = array(
	'posts__item',
	'posts__item--tile',
	'card',
	$post_class
);

$thumb_classes = array(
	'posts__thumb',
);

if ( alchemists_sp_preset( 'football' ) ) {
	array_push( $thumb_classes, 'effect-duotone', 'effect-duotone--base' );
} else {
	$thumb_classes[] = 'posts__thumb--overlay-dark';
}

?>

<div class="post-grid__item col-sm-6">
	<article <?php post_class( $post_classes ); ?>>
		<figure class="<?php echo esc_attr( implode( ' ', $thumb_classes ) ); ?>">
			<?php
			if ( has_post_thumbnail() ) {
				the_post_thumbnail( 'alchemists_thumbnail-ver' );
			} else {
				echo '<img src="' . get_theme_file_uri( '/assets/images/placeholder-380x490.jpg' ) . '" alt="" />';
			}

			do_action( 'alchemists_after_post_featured_img' );
			?>
		</figure>
		<a href="<?php the_permalink(); ?>" class="posts__cta"></a>

		<div class="posts__inner">
			<?php if ( $categories_toggle ) : ?>
				<?php alchemists_post_category_labels(); ?>
			<?php endif; ?>
			<?php the_title( '<h2 class="posts__title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>
			<?php alchemists_display_post_time(); ?>

			<footer class="posts__footer card__footer">
				<?php alchemists_entry_footer(); ?>
			</footer>
		</div>

	</article><!-- #post-## -->
</div>
