<?php
/**
 * Template part for displaying posts in Post Slider
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     3.0.0
 * @version   4.4.3
 */

$alchemists_data = get_option( 'alchemists_data' );
$categories_toggle = isset( $alchemists_data['alchemists__posts-categories'] ) ? $alchemists_data['alchemists__posts-categories'] : 1;
$post_author       = isset( $alchemists_data['alchemists__blog-post-author'] ) ? $alchemists_data['alchemists__blog-post-author'] : true;

// get post category class
$post_class = alchemists_post_category_class();

$post_classes = array(
	'posts__item',
	'posts__item--desc-center',
	$post_class
);

$thumb_classes = array(
	'posts__thumb'
);

if ( $duotone_effect ) {
	$thumb_classes[] = 'effect-duotone';
}

$thumb_classes = implode( ' ', $thumb_classes );
?>

<div class="col-lg-8">
	<article <?php post_class( $post_classes ); ?>>
		<a href="<?php echo esc_url( get_permalink() ); ?>" class="posts__link-wrapper">

			<figure class="<?php echo esc_attr( $thumb_classes ); ?>">
				<?php
				if ( has_post_thumbnail() ) :
					the_post_thumbnail( 'alchemists_thumbnail-lg-alt' );
				else :
					echo '<img src="' . get_theme_file_uri( '/assets/images/placeholder-773x408.jpg' ) . '" alt="" />';
				endif;

				do_action( 'alchemists_after_post_featured_img' );
				?>
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
						<?php alchemists_display_post_time(); ?>
					</div>
				</div>
			</div>
		</a>
	</article>
</div>
