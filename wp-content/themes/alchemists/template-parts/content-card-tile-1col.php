<?php
/**
 * Template part for displaying posts
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.0.0
 * @version   4.4.3
 */

$alchemists_data = get_option( 'alchemists_data' );
$categories_toggle = isset( $alchemists_data['alchemists__posts-categories'] ) ? $alchemists_data['alchemists__posts-categories'] : 1;
$excerpt_size      = isset( $excerpt_size ) && ! empty( $excerpt_size ) ? $excerpt_size : 10;

$post_thumbnail_size = 'alchemists_thumbnail-ver';
if ( isset( $img_size ) ) {
	if ( 'default' != $img_size && !empty( $img_size ) ) {
		$post_thumbnail_size = $img_size;
	}
}


// get post category class
$post_class = alchemists_post_category_class();

$post_classes = array(
	'posts__item',
	'posts__item--tile',
	'card',
	$post_class
);

?>

<div class="post-grid__item">
	<article <?php post_class( $post_classes ); ?>>
		<figure class="posts__thumb">
			<?php
			if ( has_post_thumbnail() ) {
				the_post_thumbnail( $post_thumbnail_size );
			} else {
				echo '<img src="' . get_theme_file_uri( '/assets/images/placeholder-380x490.jpg' ) . '" alt="" />';
			}
			do_action( 'alchemists_after_post_featured_img' );
			?>
			<a href="<?php the_permalink(); ?>" class="posts__item-link-overlay"></a>
			<div class="posts__inner posts__inner--noactive">
				<?php
				if ( $categories_toggle ) {
					alchemists_post_category_labels();
				}
				the_title( '<h3 class="posts__title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h3>' );
				alchemists_display_post_time();
				?>
				<div class="posts__excerpt">
					<?php
					if ( ! empty( $excerpt_size ) ) {
						echo alchemists_string_limit_words( get_the_excerpt(), $excerpt_size );
					} else {
						the_excerpt();
					}
					?>
				</div>
			</div>
		</figure>
		<a href="<?php the_permalink(); ?>" class="posts__cta"></a>
		<footer class="posts__footer card__footer">
			<?php alchemists_entry_footer(); ?>
		</footer>
	</article><!-- #post-## -->
</div>
