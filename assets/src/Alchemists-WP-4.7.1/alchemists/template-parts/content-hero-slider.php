<?php
/**
 * Template part for displaying Hero Slider Posts
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     2.0.0
 * @version   4.5.6
 */

$alchemists_data = get_option( 'alchemists_data' );

// get post category class
$post_class = alchemists_post_category_class();

// Title tag
$title_tag = isset( $alchemists_data['alchemists__hero-posts-title-tag'] ) ? $alchemists_data['alchemists__hero-posts-title-tag'] : 'h1';

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
?>


<div <?php post_class( $post_classes ); ?>>

	<div class="hero-slider__item-image" <?php echo wp_kses_post( $post_thumb ); ?>></div>

	<?php if ( $slide_link_toggle ) : ?>
	<a href="<?php echo esc_url( get_permalink() ); ?>" class="hero-slider__item-link"></a>
	<?php endif; ?>

	<div class="container hero-slider__item-container">
		<div class="row justify-content-center">
			<div class="col-11 col-sm-10 col-lg-8">
				<!-- Post Meta - Top -->
				<div class="post__meta-block post__meta-block--top">

					<?php if ( $categories_toggle ) : ?>
						<?php if ( $hero_categories_toggle ) : ?>
							<?php alchemists_post_category_labels(); ?>
						<?php endif; ?>
					<?php endif; ?>

					<!-- Post Title -->
					<?php if ( $post_toggle ) {
						the_title( "<$title_tag class='posts__title'><a href='" . esc_url( get_permalink() ) . "' rel='bookmark'>", "</a></$title_tag>" );
					}
					?>
					<!-- Post Title / End -->

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

					<?php if ( $author_toggle ) : ?>
					<!-- Post Author -->
					<div class="post-author">
						<figure class="post-author__avatar">
							<?php echo get_avatar( get_the_author_meta('email'), '40' ); ?>
						</figure>
						<div class="post-author__info">
							<h4 class="post-author__name"><?php echo get_the_author_meta( 'display_name' ); ?></h4>
							<span class="post-author__slogan"><?php the_author_meta( 'nickname' ); ?></span>
						</div>
					</div>
					<!-- Post Author / End -->
					<?php endif; ?>

				</div>
				<!-- Post Meta - Top / End -->
			</div>
		</div>
	</div>

</div>
