<?php
/**
 * Template part for displaying Hero Slider Posts - Numbered
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     3.0.0
 * @version   4.7.0
 */

// get post category class
$post_class = alchemists_post_category_class();

// Post Thumbnail
$post_thumb = '';
if ( has_post_thumbnail() ) {
	$thumb_id = get_post_thumbnail_id();
	$thumb_url = wp_get_attachment_image_src($thumb_id, 'full', true);

	$post_thumb = 'style="background-image:url(' . $thumb_url[0] . ')"';
}

// post classes
$post_classes = array(
	'hero-slider__item',
	$post_class
);

if ( 'duotone' == $hero_overlay_type ) {
	$post_classes[] = 'effect-duotone';
}

if ( $hero_img && $post_count == 1 ) {
	$post_classes[] = 'posts__item-has-img';
}
?>


<div <?php post_class( $post_classes ); ?>>

	<div class="hero-slider__item-image" <?php echo wp_kses_post( $post_thumb ); ?>></div>

	<?php if ( $slide_link_toggle ) : ?>
	<a href="<?php echo esc_url( get_permalink() ); ?>" class="hero-slider__item-link"></a>
	<?php endif; ?>

	<?php if ( $hero_img && $post_count == 1 ) : ?>
		<!-- Player Image -->
		<div class="posts__img-player">
			<?php if ( !empty( $hero_img_url ) ) : ?>
				<img src="<?php echo esc_url( $hero_img_url ); ?>" alt="<?php esc_attr_e( 'Hero Image', 'alchemists' ); ?>">
			<?php else : ?>
				<img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/football/hero-unit-player.png" alt="<?php esc_attr_e( 'Hero Image', 'alchemists' ); ?>">
			<?php endif; ?>
		</div>
		<!-- Player Image / End -->
	<?php endif; ?>

	<div class="container hero-slider__item-container">
		<div class="posts__inner">
			<!-- Post Meta - Top -->
			<div class="post__meta-block post__meta-block--top">

				<?php if ( $categories_toggle ) : ?>
					<?php if ( $hero_categories_toggle ) : ?>
						<?php alchemists_post_category_labels(); ?>
					<?php endif; ?>
				<?php endif; ?>

				<!-- Post Title -->
				<?php
				if ( $post_toggle ) {
					the_title( '<h1 class="posts__title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h1>' );
				}
				?>
				<!-- Post Title / End -->

				<footer class="posts__footer">
					<?php if ( $author_toggle ) : ?>
						<!-- Post Author -->
						<div class="post-author">
							<figure class="post-author__avatar">
								<?php echo get_avatar( get_the_author_meta('email'), '40' ); ?>
							</figure>
						</div>
						<!-- Post Author / End -->
						<?php endif; ?>

						<?php if ( $meta_toggle ) : ?>
						<!-- Post Meta Info -->
						<ul class="post__meta meta">

							<li class="meta__item meta__item--date"><time datetime="<?php esc_attr( the_time('c') ); ?>"><?php the_time( get_option('date_format') ); ?></time></li>

							<?php
							if ( $post_views ) :
								if ( function_exists( 'alchemists_getPostViews' ) ) :
									echo alchemists_getPostViews( get_the_ID() );
								endif;
							endif;
							?>

							<?php
							if ( $post_likes ) :
								if ( function_exists( 'get_simple_likes_button') ) :
									?>
									<li class="meta__item meta__item--likes"><?php echo get_simple_likes_button( get_the_ID() ); ?></li>
									<?php
								endif;
							endif;
							?>

							<?php
							if ( $post_comments ) {
								if ( ! is_single() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
									echo '<div class="meta__item meta__item--comments">';
										comments_popup_link( '0', '1', '%', '', '-' );
									echo '</div>';
								}
							}
							?>
						</ul>
						<!-- Post Meta Info / End -->
					<?php endif; ?>
				</footer>

			</div>
			<!-- Post Meta - Top / End -->
		</div>
	</div>

</div>
