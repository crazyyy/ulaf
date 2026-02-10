<?php
/**
 * Template part for displaying posts
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.0.0
 * @version   4.2.9
 */

$post_title_classes = array( 'posts__title' );

if ( $counter == 1 ) {
	$post_thumb     = 'alchemists_thumbnail-player-lg-fit';
	$post_title_classes[] = 'posts__title--lg';
} elseif ( $counter == 4 ) {
	$post_thumb = 'alchemists_thumbnail-n';
} else {
	$post_thumb     = 'alchemists_thumbnail-square';
}

// get post category class
$post_class = alchemists_post_category_class();

$post_classes = array(
	'post-grid__item',
	'posts__item--tile',
	'posts__item',
	$post_class,
);
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( $post_classes ); ?>>
	<figure class="<?php echo esc_attr( $thumb_classes ); ?> h-100">
		<?php the_post_thumbnail( $post_thumb, array( 'class' => 'h-100' ) ); ?>
		<a href="<?php the_permalink(); ?>" class="posts__item-link-overlay"></a>
		<div class="posts__inner posts__inner--noactive">
			<?php
			if ( $categories_toggle ) {
				alchemists_post_category_labels();
			}

			the_title( '<h3 class="' . esc_attr( implode( ' ', $post_title_classes ) ). '">', '</h3>' );
			alchemists_entry_footer( false, true );
			?>
			<div class="posts__excerpt">
				<?php echo alchemists_string_limit_words( get_the_excerpt(), 11 ); ?>
			</div>
		</div>
	</figure>
	<a href="<?php the_permalink(); ?>" class="posts__cta"></a>
</article><!-- #post-<?php the_ID(); ?> -->
