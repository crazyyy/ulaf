<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.5.0
 *
 * Shortcode attributes
 * @var $atts
 * @var $title
 * @var $items_per_page
 * @var $offset
 * @var $taxonomies_categories
 * @var $order
 * @var $order_by
 * @var $autoplay
 * @var $autoplay_speed
 * @var $arrows
 * @var $el_class
 * @var $el_id
 * @var $css
 * @var $css_animation
 * Shortcode class
 * @var $this WPBakeryShortCode_Alc_Post_Carousel
 */

$items_per_page = $offset = $taxonomies_categories = $order = $order_by = $autoplay = $autoplay_speed = $arrows = $el_class = $el_id = $css = $css_animation = '';

$output = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$class_to_filter = 'card card--clean';
$class_to_filter .= vc_shortcode_custom_css_class( $css, ' ' ) . $this->getExtraClass( $el_class ) . $this->getCSSAnimation( $css_animation );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class_to_filter, $this->settings['base'], $atts );

$id = rand();

// check if RTL
$is_rtl = is_rtl() ? 'true' : 'false';

$wrapper_attributes = array();
if ( ! empty( $el_id ) ) {
	$wrapper_attributes[] = 'id="' . esc_attr( $el_id ) . '"';
}

if ( !$autoplay ) {
	$autoplay = 'false';
} else {
	$autoplay = 'true';
}

if ( !$arrows ) {
	$arrows = 'false';
} else {
	$arrows = 'true';
}

// Posts arguments
$args = array(
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => $items_per_page,
	'order'          => $order,
	'orderby'        => $order_by,
	'offset'         => $offset,
	'no_found_rows'       => true,
	'ignore_sticky_posts' => true
);

// echo '<pre>' . var_export($taxonomies_categories, true) . '</pre>';

// filter by categories
if( !empty( $taxonomies_categories ) ) {
	$args['category_name'] = $taxonomies_categories;
}

$query = new WP_Query($args);


// Post Template
$post_template = 'post-carousel-item';
$slidesToShow  = 1;
$categoryFilter = 'category-filter';

$posts_classes_array = array(
	'posts',
	'posts--carousel-featured',
	'featured-carousel',
);

$posts_classes = implode( " ", $posts_classes_array );

?>


<!-- Post Carousel -->
<div <?php echo implode( ' ', $wrapper_attributes ); ?> class="<?php echo esc_attr( apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $css_class, $this->settings['base'], $atts ) ); ?>">

	<div class="card__content">

		<?php
		if ( $query->have_posts() ) : ?>

			<div id="slick-<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $posts_classes ); ?>">

			<?php /* Start the Loop */
			while ( $query->have_posts() ) : $query->the_post();

				get_template_part( 'template-parts/content', $post_template );

			endwhile;

			// Reset the global $the_post as this query will have stomped on it
			wp_reset_postdata(); ?>

			</div><!-- .posts -->

			<script type="text/javascript">
				(function($){
					$(function() {
						$slick_featured_carousel_<?php echo esc_js( $id ); ?> = $('#slick-<?php echo esc_js( $id ); ?>'),

						$slick_featured_carousel_<?php echo esc_js( $id ); ?>.slick({
							slidesToShow: 3,
							slidesToScroll: 1,
							autoplay: <?php echo esc_js( $autoplay); ?>,
							autoplaySpeed: <?php echo esc_js( $autoplay_speed ); ?>,
							centerMode: true,
							centerPadding: 0,
							arrows: <?php echo esc_js( $arrows ); ?>,
							rtl: <?php echo esc_js( $is_rtl ); ?>,
							rows: 0,

							responsive: [
								{
									breakpoint: 992,
									settings: {
										arrows: false,
										centerMode: true,
										centerPadding: 0,
										slidesToShow: 3
									}
								},
								{
									breakpoint: 769,
									settings: {
										arrows: false,
										centerMode: true,
										centerPadding: 0,
										slidesToShow: 2
									}
								},
								{
									breakpoint: 480,
									settings: {
										arrows: false,
										centerMode: true,
										centerPadding: 0,
										slidesToShow: 1,
										dots: true
									}
								}
							]
						});

					});
				})(jQuery);
			</script>

		<?php else :

			get_template_part( 'template-parts/content', 'none' );

		endif; ?>

	</div>


</div>
<!-- Post Carousel / End -->
