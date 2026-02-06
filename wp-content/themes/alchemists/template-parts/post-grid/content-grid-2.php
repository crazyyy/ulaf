<?php
/**
 * Template part for displaying posts
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     3.4.0
 * @version   3.4.0
 */

if ( $counter == 1 ) {
	$col_class      = 'col-lg-6';
	$post_img_class = 'posts__item--desc-center';
	$post_thumb     = 'alchemists_thumbnail-tile-xlg';
} elseif ( $counter == 2 || $counter == 3 ) {
	$col_class      = 'col-sm-6 col-lg-3';
	$post_img_class = 'posts__item--desc-left';
	$post_thumb     = 'alchemists_thumbnail-tile-lg';
} else {
	$col_class      = 'col-sm-6 col-lg-3';
	$post_img_class = 'posts__item--desc-left';
	$post_thumb     = 'alchemists_thumbnail-alt';
}

$col_classes = array(
	'post-grid__item',
	$col_class
);

// get post category class
$post_class = alchemists_post_category_class();

$post_classes = array(
	'posts__item',
	$post_class,
	$post_img_class,
);
?>

<div class="<?php echo esc_attr( implode( ' ', $col_classes ) ); ?>">

	<article id="post-<?php the_ID(); ?>" <?php post_class( $post_classes ); ?>>

		<a href="<?php the_permalink(); ?>" class="posts__link-wrapper">
			<figure class="<?php echo esc_attr( $thumb_classes ); ?>">
				<?php the_post_thumbnail( $post_thumb ); ?>
			</figure>
			<div class="posts__inner">
				<?php
				if ( $categories_toggle ) {
					alchemists_post_category_labels();
				}
				?>

				<?php the_title( '<h3 class="posts__title">', '</h3>' ); ?>

				<div class="post-author">

					<?php if ( $counter == 1 ) : ?>
					<figure class="post-author__avatar">
						<?php echo get_avatar( get_the_author_meta('email'), '24' ); ?>
					</figure>
					<?php endif; ?>

					<div class="post-author__info">
						<time datetime="<?php echo esc_attr( get_the_time('c') ); ?>" class="posts__date"><?php echo esc_html( get_the_time( get_option('date_format') ) ); ?></time>
					</div>
				</div>
			</div>
		</a>

	</article><!-- #post-## -->
</div>
