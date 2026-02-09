<?php
/**
 * Template part for displaying results in search pages
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   3.2.0
 */

?>

<?php

$post_class = '';

// post classes
$post_classes = array(
	'posts__item',
	'card',
	$post_class
);

?>

<article id="post-<?php the_ID(); ?>" <?php post_class( $post_classes ); ?>>
	<div class="posts__inner card__content">
		<?php the_title( '<h6 class="posts__title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h6>' ); ?>
		<?php alchemists_display_post_time(); ?>
		<div class="posts__excerpt">
			<?php the_excerpt(); ?>
		</div>
	</div>
</article><!-- #post-## -->
