<?php
/**
 * Template part for displaying posts
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     2.1.0
 * @version   4.4.0
 */

$alchemists_data = get_option( 'alchemists_data' );
$categories_toggle = isset( $alchemists_data['alchemists__posts-categories'] ) ? $alchemists_data['alchemists__posts-categories'] : 1;
$excerpt_size      = isset( $excerpt_size ) && ! empty( $excerpt_size ) ? $excerpt_size : 16;

$post_thumbnail_size = 'alchemists_thumbnail-xs';
if ( isset( $img_size ) ) {
	if ( 'default' != $img_size && !empty( $img_size ) ) {
		$post_thumbnail_size = $img_size;
	}
}

// get post category class
$post_class = alchemists_post_category_class();

$post_classes = array(
	'posts__item',
	$post_class
);
?>

<article <?php post_class( $post_classes ); ?>>

	<?php if( has_post_thumbnail() ) { ?>
	<figure class="posts__thumb">
		<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( $post_thumbnail_size ); ?></a>
		<?php do_action( 'alchemists_after_post_featured_img' ); ?>
	</figure>
	<?php } ?>

	<div class="posts__inner">

		<?php if ( $categories_toggle ) : ?>
			<?php alchemists_post_category_labels(); ?>
		<?php endif; ?>

		<?php the_title( '<h6 class="posts__title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h6>' ); ?>
		<?php alchemists_display_post_time(); ?>
	</div>
	<div class="clearfix"></div>
	<div class="posts__excerpt posts__excerpt--space-sm">
		<?php echo alchemists_string_limit_words( get_the_excerpt(), $excerpt_size ); ?>
	</div>
</article><!-- #post-## -->
