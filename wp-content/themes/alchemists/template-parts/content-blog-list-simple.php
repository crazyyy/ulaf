<?php
/**
 * Template part for displaying posts
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.2.10
 */

$alchemists_data = get_option( 'alchemists_data' );
$categories_toggle = isset( $alchemists_data['alchemists__posts-categories'] ) ? $alchemists_data['alchemists__posts-categories'] : 1;
$excerpt_size      = isset( $excerpt_size ) && ! empty( $excerpt_size ) ? $excerpt_size : 16;

// get post category class
$post_class = alchemists_post_category_class();

$post_classes = array(
	'posts__item',
	$post_class
);

?>

<article <?php post_class( $post_classes ); ?>>
	<div class="posts__inner">

		<?php if ( $categories_toggle ) : ?>
			<?php alchemists_post_category_labels(); ?>
		<?php endif; ?>

		<?php the_title( '<h6 class="posts__title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h6>' ); ?>
		<?php alchemists_display_post_time(); ?>
		<div class="posts__excerpt">
			<?php echo alchemists_string_limit_words( get_the_excerpt(), $excerpt_size ); ?>
		</div>
	</div>
</article><!-- #post-## -->
