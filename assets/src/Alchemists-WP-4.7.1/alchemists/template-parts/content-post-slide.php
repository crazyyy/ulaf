<?php
/**
 * Template part for displaying posts in Post Slider
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.5.0
 */

$alchemists_data = get_option( 'alchemists_data' );
$categories_toggle = isset( $alchemists_data['alchemists__posts-categories'] ) ? $alchemists_data['alchemists__posts-categories'] : 1;
$post_author       = isset( $alchemists_data['alchemists__blog-post-author'] ) ? $alchemists_data['alchemists__blog-post-author'] : true;

$post_classes = array(
	'posts__item'
);

$post_thumbnail_size = 'alchemists_thumbnail-lg-alt';
if ( isset( $img_size ) ) {
	if ( 'default' != $img_size && !empty( $img_size ) ) {
		$post_thumbnail_size = $img_size;
	}
}
?>

<article <?php post_class( $post_classes ); ?>>
	<figure class="posts__thumb">
		<a href="<?php echo esc_url( get_permalink() ); ?>" class="posts__link-wrapper">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( $post_thumbnail_size ); ?>
			<?php else: ?>
				<img src="<?php echo get_theme_file_uri( '/assets/images/placeholder-773x408.jpg' ); ?>" alt="" />
			<?php endif; ?>
		</a>
		<?php do_action( 'alchemists_after_post_featured_img' ); ?>
	</figure>

	<div class="posts__inner">

		<?php if ( $categories_toggle ) : ?>
			<?php alchemists_post_category_labels(); ?>
		<?php endif; ?>

		<?php the_title( '<h3 class="posts__title">', '</h3>' ); ?>
		<div class="post-author">
			<?php if ( $post_author ) : ?>
				<figure class="post-author__avatar">
					<?php echo get_avatar( get_the_author_meta('email'), '24' ); ?>
				</figure>
			<?php endif; ?>
			<div class="post-author__info">
				<?php if ( $post_author ) : ?>
					<h4 class="post-author__name"><?php echo get_the_author_meta('display_name'); ?></h4>
				<?php endif; ?>
				<?php alchemists_display_post_time(); ?>
			</div>
		</div>
	</div>
</article>
