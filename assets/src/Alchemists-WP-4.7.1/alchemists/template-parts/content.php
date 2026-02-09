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
$excerpt_size      = isset( $excerpt_size ) && ! empty( $excerpt_size ) ? $excerpt_size : 24;

$post_thumbnail_size = '';
if ( isset( $img_size ) ) {
	if ( 'default' != $img_size && !empty( $img_size ) ) {
		$post_thumbnail_size = $img_size;
	}
}

// get post category class
$post_class = alchemists_post_category_class();

$post_classes = array(
	'post-list__item',
	'posts__item',
	'posts__item--card',
	'card',
	'card--block',
	$post_class
);

?>

<article id="post-<?php the_ID(); ?>" <?php post_class( $post_classes ); ?>>

	<?php if( has_post_thumbnail() ) { ?>
	<figure class="posts__thumb">
		<a href="<?php the_permalink(); ?>">
			<?php
				if ( '' != $post_thumbnail_size ) :
					the_post_thumbnail( $post_thumbnail_size );
				else :
					the_post_thumbnail();
				endif;
			?>
		</a>
		<a href="<?php the_permalink(); ?>" class="posts__cta"></a>
	</figure>
	<?php } ?>

	<div class="posts__inner">

		<div class="card__content">

			<?php if ( $categories_toggle ) : ?>
				<?php alchemists_post_category_labels(); ?>
			<?php endif; ?>

			<?php the_title( '<h2 class="posts__title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>

			<time datetime="<?php esc_attr( the_time('c') ); ?>" class="posts__date"><a href="<?php echo esc_url( get_permalink() ); ?>"><?php the_time( get_option('date_format') ); ?></a></time>

			<div class="posts__excerpt">
				<?php
				if ( ! empty( $excerpt_size ) ) {
					echo alchemists_string_limit_words( get_the_excerpt(), $excerpt_size );
				} else {
					the_excerpt();
				}
				?>

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

<?php
// Page Links
alchemists_page_links();
