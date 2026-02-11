<?php
/**
 * Template part for displaying Hero Slider Posts Thumbs
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     2.0.0
 * @version   4.2.10
 */


$alchemists_data = get_option( 'alchemists_data' );
$categories_toggle      = isset( $alchemists_data['alchemists__posts-categories'] ) ? $alchemists_data['alchemists__posts-categories'] : 1;
$hero_categories_toggle = isset( $alchemists_data['alchemists__hero-posts-thumb-category'] ) ? $alchemists_data['alchemists__hero-posts-thumb-category'] : 1;

// get post category class
$post_class = alchemists_post_category_class();

// post classes
$post_classes = array(
	'hero-slider-thumbs__item',
	$post_class
);

?>


<div <?php post_class( $post_classes ); ?>>
	<div class="posts__item posts__item--category-1">
		<div class="posts__inner">

			<?php if ( $categories_toggle ) : ?>
				<?php if ( $hero_categories_toggle ) : ?>
					<?php alchemists_post_category_labels(); ?>
				<?php endif; ?>
			<?php endif; ?>

			<?php the_title( '<h6 class="posts__title">', '</h6>' ); ?>
			<?php alchemists_display_post_time(); ?>
		</div>
	</div>
</div>
