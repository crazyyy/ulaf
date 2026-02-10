<?php
/**
 * Template part for displaying posts in Post Slider
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

$post_thumbnail_size = 'alchemists_thumbnail-sm';
if ( isset( $img_size ) ) {
	if ( 'default' != $img_size && !empty( $img_size ) ) {
		$post_thumbnail_size = $img_size;
	}
}
?>

<article <?php post_class( $post_classes ); ?>>
	<a href="<?php echo esc_url( get_permalink() ); ?>" class="posts__link-wrapper">

		<figure class="posts__thumb">
			<?php
			if ( has_post_thumbnail() ) {
				the_post_thumbnail( $post_thumbnail_size );
			} else {
				echo '<img src="' . get_theme_file_uri( '/assets/images/placeholder-773x408.jpg' ) . '" alt="" />';
			}
			?>
			<?php do_action( 'alchemists_after_post_featured_img' ); ?>
		</figure>

		<div class="posts__inner">

			<?php if ( $categories_toggle ) : ?>
				<?php alchemists_post_category_labels(); ?>
			<?php endif; ?>

			<?php the_title( '<h3 class="posts__title">', '</h3>' ); ?>
			<?php alchemists_display_post_time(); ?>
		</div>
	</a>
</article>
