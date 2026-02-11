<?php
/**
 * Template part for displaying posts
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.4.3
 */

$alchemists_data = get_option( 'alchemists_data' );
$categories_toggle = isset( $alchemists_data['alchemists__posts-categories'] ) ? $alchemists_data['alchemists__posts-categories'] : 1;
$excerpt_size      = isset( $excerpt_size ) && ! empty( $excerpt_size ) ? $excerpt_size : 28;

// get post category class
$post_class = alchemists_post_category_class();

$post_classes = array(
	'post-grid__item',
	'col-sm-6',
	'col-lg-4',
	$post_class
);
?>

<article <?php post_class( $post_classes ); ?>>
	<div class="posts__item posts__item--card posts__item--category-1 card">

		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="posts__thumb">
				<?php
				if ( $categories_toggle ) {
					alchemists_post_category_labels();
				}
				?>
				<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'alchemists_thumbnail' ); ?></a>
				<?php do_action( 'alchemists_after_post_featured_img' ); ?>
			</figure>
		<?php endif; ?>

		<div class="posts__inner card__content">
			<?php if( has_post_thumbnail() ) { ?>
			<a href="<?php the_permalink(); ?>" class="posts__cta"></a>
			<?php } ?>
			<?php alchemists_display_post_time(); ?>
			<?php the_title( '<h2 class="posts__title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>
			<div class="posts__excerpt">
				<?php echo alchemists_string_limit_words( get_the_excerpt(), $excerpt_size ); ?>

				<?php
					wp_link_pages( array(
						'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'alchemists' ),
						'after'  => '</div>',
					) );
				?>
			</div>
		</div>

		<footer class="posts__footer card__footer">
			<?php alchemists_entry_footer(); ?>
		</footer>

	</div>
</article><!-- #post-## -->
