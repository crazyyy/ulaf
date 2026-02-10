<?php
/**
 * Template part for displaying posts
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.0.0
 * @version   4.4.0
 */

$alchemists_data = get_option( 'alchemists_data' );
$categories_toggle = isset( $alchemists_data['alchemists__posts-categories'] ) ? $alchemists_data['alchemists__posts-categories'] : 1;
$excerpt_size      = isset( $excerpt_size ) && ! empty( $excerpt_size ) ? $excerpt_size : 28;

$post_thumbnail_size = 'alchemists_thumbnail';
if ( isset( $img_size ) ) {
	if ( 'default' != $img_size && !empty( $img_size ) ) {
		$post_thumbnail_size = $img_size;
	}
}

// get post category class
$post_class = alchemists_post_category_class();

$post_classes = array(
	'post-grid__item',
	$post_class
);
?>

<article <?php post_class( $post_classes ); ?>>
	<div class="posts__item posts__item--tile card">

		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="posts__thumb">
				<?php the_post_thumbnail( $post_thumbnail_size ); ?>
				<?php do_action( 'alchemists_after_post_featured_img' ); ?>
				<div class="posts__inner posts__inner--noactive">
					<?php
					if ( $categories_toggle ) {
						alchemists_post_category_labels();
					}
					the_title( '<h6 class="posts__title">', '</h6>' );
					alchemists_display_post_time();
					?>
					<div class="posts__excerpt">
						<?php echo alchemists_string_limit_words( get_the_excerpt(), $excerpt_size ); ?>
					</div>
				</div>
			</figure>
			<a href="<?php the_permalink(); ?>" class="posts__cta"></a>
			<a href="<?php the_permalink(); ?>" class="posts__item-link-overlay"></a>
		<?php endif; ?>
	</div>
</article><!-- #post-## -->
