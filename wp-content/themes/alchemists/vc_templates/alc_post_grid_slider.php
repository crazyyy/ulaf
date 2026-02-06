<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.7.0
 *
 * Shortcode attributes
 * @var $atts
 * @var $title
 * @var $items_per_page
 * @var $layout
 * @var $slide_to_show
 * @var $offset
 * @var $taxonomies_categories
 * @var $order
 * @var $order_by
 * @var $autoplay
 * @var $autoplay_speed
 * @var $arrows
 * @var $duotone_effect
 * @var $el_class
 * @var $el_id
 * @var $css
 * @var $css_animation
 * Shortcode class
 * @var $this WPBakeryShortCode_Alc_Post_Grid_Slider
 */

$title = $items_per_page = $layout = $slide_to_show = $offset = $taxonomies_categories = $order = $order_by = $autoplay = $autoplay_speed = $arrows = $duotone_effect = $el_class = $el_id = $css = $css_animation = '';

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
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => $items_per_page,
	'order'               => $order,
	'orderby'             => $order_by,
	'offset'              => $offset,
	'no_found_rows'       => true,
	'ignore_sticky_posts' => true
);

// filter by categories
if( !empty( $taxonomies_categories ) ) {
	$args['category_name'] = $taxonomies_categories;
}

// filter by tags
if( !empty( $taxonomies_tags ) ) {
	$args['tag'] = $taxonomies_tags;
}

$query = new WP_Query($args);


// Post Template
$post_template    = 'post-grid-slide';
$post_template_lg = 'post-grid-slide-featured';
$slidesToShow   = 1;

$posts_classes_array = array(
	'posts',
	'slick',
	'posts-slider',
	'posts--slider-var-width',
);

if ( $layout == '1-2' ) {
	$posts_classes_array[] = 'posts--slider-layout-1x2';
}

$posts_classes = implode( ' ', $posts_classes_array );
?>


<!-- Post Loop -->
<div <?php echo implode( ' ', $wrapper_attributes ); ?> class="<?php echo esc_attr( apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $css_class, $this->settings['base'], $atts ) ); ?>">

	<?php if ( $title ) : ?>
		<div class="card__header">
			<?php echo wpb_widget_title( array( 'title' => $title ) ) ?>
		</div>
	<?php endif; ?>

	<div class="card__content">

		<?php
		if ( $query->have_posts() ) : ?>

			<div id="slick-<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $posts_classes ); ?>">

				<?php /* Start the Loop */
				while ( $query->have_posts() ) : $query->the_post();

					$count = $query->current_post;

					if ( $layout == '1-2' ) {

						if ( 0 == $count % 3 ) {
							echo '<div class="slick__slide"><div class="row">';
						}

							if ( 0 == $count % 3 ) {
								include( locate_template( 'template-parts/content-' . $post_template_lg . '.php' ) );
							} else {
								include( locate_template( 'template-parts/content-' . $post_template . '.php' ) );
							}

						if ( $query->post_count == $count + 1 || 2 == $count % 3 ) {
							echo '</div></div>';
						}

					} else {

						if ( 0 == $count % 6 ) {
							echo '<div class="slick__slide"><div class="row">';
						}

							include( locate_template( 'template-parts/content-' . $post_template . '.php' ) );

						if ( $query->post_count == $count + 1 || 5 == $count % 6 ) {
							echo '</div></div>';
						}
					}

				endwhile;

				// Reset the global $the_post as this query will have stomped on it
				wp_reset_postdata();
				?>

			</div><!-- .posts -->

			<script type="text/javascript">
				(function($){
					$(function() {
						// Posts
						$slider_<?php echo esc_js( $id ); ?> = $('#slick-<?php echo esc_js( $id ); ?>'),

						$slider_<?php echo esc_js( $id ); ?>.slick({
							slidesToShow: <?php echo esc_js( $slidesToShow ); ?>,
							slidesToScroll: 1,
							autoplay: <?php echo esc_js( $autoplay); ?>,
							autoplaySpeed: <?php echo esc_js( $autoplay_speed ); ?>,
							arrows: <?php echo esc_js( $arrows ); ?>,
							adaptiveHeight: true,
							rtl: <?php echo esc_js( $is_rtl ); ?>,
							rows: 0,
							responsive: [
								{
									breakpoint: 769,
									settings: {
										arrows: false
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
<!-- Post Loop / End -->
