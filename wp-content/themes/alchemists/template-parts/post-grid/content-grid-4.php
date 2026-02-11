<?php
/**
 * Template part for displaying posts
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.0.0
 * @version   4.0.0
 */

if ( $counter == 1 ) {
	$col_class      = 'col-sm-12';
	$post_img_class = 'posts__item--lg';
	$post_thumb     = 'alchemists_thumbnail-lg-alt';
} else {
	$col_class      = 'col-sm-6';
	$post_img_class = '';
	$post_thumb     = 'alchemists_thumbnail';
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
	'card',
);
?>

<div class="<?php echo esc_attr( implode( ' ', $col_classes ) ); ?>">
	<article id="post-<?php the_ID(); ?>" <?php post_class( $post_classes ); ?>>
		<figure class="<?php echo esc_attr( $thumb_classes ); ?> <?php echo esc_attr( 'video' == get_post_format() ? 'posts__thumb--video' : '' ); ?>">
			<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( $post_thumb ); ?></a>
		</figure>
		<div class="posts__inner card__content">
			<?php
			if ( $categories_toggle ) {
				alchemists_post_category_labels();
			}

			the_title( '<h3 class="posts__title posts__title--color-hover"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h3>' );
			alchemists_entry_footer( false, true );
			?>
		</div>
	</article><!-- #post-## -->
</div>
