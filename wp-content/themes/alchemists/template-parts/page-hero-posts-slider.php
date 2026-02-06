<?php
/**
 * Page Header - Hero Slider
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     2.0.0
 * @version   4.5.3
 */

$alchemists_data = get_option( 'alchemists_data' );
$hero_posts           = isset( $alchemists_data['alchemists__hero-posts-slider'] ) ? $alchemists_data['alchemists__hero-posts-slider'] : '';
$hero_slider_per_page = isset( $alchemists_data['alchemists__hero-posts-slider--per-page'] ) ? $alchemists_data['alchemists__hero-posts-slider--per-page'] : 3;
$hero_slider_orderby  = isset( $alchemists_data['alchemists__hero-posts-slider--orderby'] ) ? $alchemists_data['alchemists__hero-posts-slider--orderby'] : 'date';
$hero_slider_order    = isset( $alchemists_data['alchemists__hero-posts-slider--order'] ) ? $alchemists_data['alchemists__hero-posts-slider--order'] : 'DESC';
$hero_slider_cats     = isset( $alchemists_data['alchemists__hero-posts-slider--categories'] ) ? $alchemists_data['alchemists__hero-posts-slider--categories'] : '';
$hero_slider_tags     = isset( $alchemists_data['alchemists__hero-posts-slider--tags'] ) ? $alchemists_data['alchemists__hero-posts-slider--tags'] : '';
$hero_slider_autoplay = isset( $alchemists_data['alchemists__hero-posts-autoplay'] ) ? $alchemists_data['alchemists__hero-posts-autoplay'] : true;
$hero_slider_autoplay_speed = isset( $alchemists_data['alchemists__hero-posts-autoplay-speed'] ) ? $alchemists_data['alchemists__hero-posts-autoplay-speed'] : 8;
$hero_slider_autoplay_speed = $hero_slider_autoplay_speed * 1000;

$categories_toggle      = isset( $alchemists_data['alchemists__posts-categories'] ) ? $alchemists_data['alchemists__posts-categories'] : 1;
$post_toggle            = isset( $alchemists_data['alchemists__hero-posts-title'] ) ? $alchemists_data['alchemists__hero-posts-title'] : 1;
$meta_toggle            = isset( $alchemists_data['alchemists__hero-posts-meta'] ) ? $alchemists_data['alchemists__hero-posts-meta'] : 1;
$author_toggle          = isset( $alchemists_data['alchemists__hero-posts-author'] ) ? $alchemists_data['alchemists__hero-posts-author'] : 1;
$hero_categories_toggle = isset( $alchemists_data['alchemists__hero-posts-category'] ) ? $alchemists_data['alchemists__hero-posts-category'] : 1;
$slide_link_toggle      = isset( $alchemists_data['alchemists__hero-posts-link-slide'] ) ? $alchemists_data['alchemists__hero-posts-link-slide'] : 0;

$hero_slider_nav      = isset( $alchemists_data['alchemists__hero-posts-slider--nav'] ) ? $alchemists_data['alchemists__hero-posts-slider--nav'] : 'thumbs';
$hero_slider_thumbs   = isset( $alchemists_data['alchemists__hero-posts-slider--nav-num'] ) ? $alchemists_data['alchemists__hero-posts-slider--nav-num'] : 3;
$hero_overlay_type    = isset( $alchemists_data['alchemists__hero-posts-slider--overlay'] ) ? $alchemists_data['alchemists__hero-posts-slider--overlay'] : 'simple';
$hero_img             = isset( $alchemists_data['alchemists__hero-posts-slider--featured-image'] ) ? $alchemists_data['alchemists__hero-posts-slider--featured-image'] : 0;
$hero_img_url         = isset( $alchemists_data['alchemists__hero-posts-slider--featured-image-upload']['url'] ) ? $alchemists_data['alchemists__hero-posts-slider--featured-image-upload']['url'] : '';

$post_likes         = isset( $alchemists_data['alchemists__blog-post-likes'] ) ? $alchemists_data['alchemists__blog-post-likes'] : true;
$post_views         = isset( $alchemists_data['alchemists__blog-post-views'] ) ? $alchemists_data['alchemists__blog-post-views'] : true;
$post_comments      = isset( $alchemists_data['alchemists__blog-post-comments'] ) ? $alchemists_data['alchemists__blog-post-comments'] : true;

// check if RTL
$is_rtl = is_rtl() ? 'true' : 'false';

// Hero Slider classess and templates
$hero_slider_classes = array();
$hero_slider_template = 'hero-slider';
$hero_slider_slick_setting = '';

if ( 'numbers' == $hero_slider_nav ) {
	array_push( $hero_slider_classes, 'slick', 'posts', 'posts-slider', 'posts--slider-top-news');
	$hero_slider_template = 'hero-slider-numbers';

	// Slick settings
	$hero_slider_slick_setting = '{
		"slidesToShow": 1,
		"slidesToScroll": 1,
		"arrows": false,
		"fade": true,
		"dots": true,
		"rtl": ' . esc_js( $is_rtl ) . ',
		"rows": 0,
		"autoplay": ' . esc_js( $hero_slider_autoplay ) . ',
		"autoplaySpeed": ' . esc_js( $hero_slider_autoplay_speed ) . ',
		"adaptiveHeight": true,
		"responsive": [
			{
				"breakpoint": 769,
				"settings": {
					"dots": false
				}
			}
		]
	}';

} else {

	// Slick settings
	$hero_slider_slick_setting = '{
		"slidesToShow": 1,
		"slidesToScroll": 1,
		"arrows": false,
		"fade": true,
		"rtl": ' . esc_js( $is_rtl ) . ',
		"rows": 0,
		"autoplay": ' . esc_js( $hero_slider_autoplay ) . ',
		"autoplaySpeed": ' . esc_js( $hero_slider_autoplay_speed ) . ',
		"asNavFor": ".hero-slider-thumbs",
		"responsive": [
			{
				"breakpoint":992,
				"settings": {
					"fade": false
				}
			}
		]
	}';

	array_push( $hero_slider_classes, 'hero-slider', 'hero-slider--thumbs' );
}

// add overlay if set
if ( 'simple' == $hero_overlay_type ) {
	$hero_slider_classes[] = 'hero-slider--overlay-on';
} elseif ( 'duotone' == $hero_overlay_type ) {
	$hero_slider_classes[] = 'hero-slider--overlay-duotone';
}

// Posts arguments
$hero_args = array(
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => $hero_slider_per_page,
	'order'               => $hero_slider_order,
	'ignore_sticky_posts' => 1,
	'no_found_rows'       => true,
);

// order posts differently depending selected options
if ( ! empty( $hero_posts ) ) {
	$hero_args['post__in'] = $hero_posts;
	$hero_args['orderby'] = 'post__in';
} else {
	$hero_args['orderby'] = $hero_slider_orderby;
}

// filter posts by categories
if ( ! empty( $hero_slider_cats ) ) {
	$hero_args['cat'] = $hero_slider_cats;
}

// filter by tags
if ( ! empty( $hero_slider_tags ) ) {
	$hero_args['tag__in'] = $hero_slider_tags;
}

$hero_query = new WP_Query( $hero_args );
if ( $hero_query->have_posts() ) :
	?>

	<!-- Hero Slider
	================================================== -->
	<div class="hero-slider-wrapper">

		<!-- Slides -->
		<div class="<?php echo esc_attr( implode( ' ', $hero_slider_classes ) ); ?>" data-slick='<?php echo esc_attr( $hero_slider_slick_setting ); ?>'>

			<?php
			$post_count = 1;
			while ( $hero_query->have_posts() ) :
				$hero_query->the_post();

				include( locate_template( 'template-parts/content-' . $hero_slider_template . '.php' ) );

				$post_count++;
			endwhile;

			wp_reset_postdata();
			?>

		</div>
		<!-- Slides / End -->

		<?php if ( 'thumbs' == $hero_slider_nav ) : ?>
			<!-- Thumbs -->
			<div class="hero-slider-thumbs-wrapper">
				<div class="container">
					<div class="hero-slider-thumbs posts posts--simple-list" data-slick='{
						"slidesToShow": <?php echo esc_js( $hero_slider_thumbs ); ?>,
						"slidesToScroll": 1,
						"asNavFor": ".hero-slider",
						"focusOnSelect": true,
						"rtl": <?php echo esc_js( $is_rtl ); ?>,
						"rows": 0
					}'>

						<?php
						while ( $hero_query->have_posts() ) :
							$hero_query->the_post();

							get_template_part( 'template-parts/content', 'hero-thumb' );

						endwhile;

						wp_reset_postdata();
						?>

					</div>
				</div>
			</div>
			<!-- Thumbs / End -->
		<?php endif; ?>

	</div>
	<?php
endif;
