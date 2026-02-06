<?php
/**
 * Template part for displaying posts
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     3.4.0
 * @version   4.7.0
 */

if ( $counter == 7 ) {
	$col_class      = 'col-sm-12';
	$post_img_class = 'posts__item--tile posts__item--tile-lg card';
	$post_thumb     = 'alchemists_thumbnail-lg-alt';
} elseif ( $counter == 9 || $counter == 10 ) {
	$col_class      = 'col-sm-6';
	$post_img_class = 'posts__item--tile posts__item--tile-sm';
	$post_thumb     = 'alchemists_thumbnail';
} else {
	$col_class      = 'col-sm-6';
	$post_img_class = 'posts__item--tile';
	$post_thumb     = 'alchemists_thumbnail-ver';
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

		<figure class="<?php echo esc_attr( $thumb_classes ); ?>">
			<?php the_post_thumbnail( $post_thumb ); ?>
		</figure>

		<?php if ( $counter <= 6 || $counter == 8 ) : ?>
		<a href="<?php the_permalink(); ?>" class="posts__cta"></a>
		<?php endif; ?>

		<div class="posts__inner <?php if ( $counter == 7 || $counter == 9 || $counter == 10 ) { echo 'posts__inner--centered'; } ?>">
			<?php
			if ( $categories_toggle ) {
				alchemists_post_category_labels();
			}
			?>

			<?php the_title( '<h6 class="posts__title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h6>' ); ?>

			<time datetime="<?php echo esc_attr( get_the_time('c') ); ?>" class="posts__date"><?php echo esc_html( get_the_time( get_option('date_format') ) ); ?></time>

			<?php if ( $counter <= 6 || $counter == 8 ) : ?>
			<footer class="posts__footer card__footer">
				<?php alchemists_entry_footer(); ?>
			</footer>
			<?php endif; ?>
		</div>

		<?php if ( $counter == 7 ) : ?>
		<footer class="posts__footer card__footer">
			<?php alchemists_entry_footer(); ?>
		</footer>
		<?php endif; ?>

	</article><!-- #post-## -->
</div>
