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
 * @var $slide_to_show
 * @var $img_size
 * @var $offset
 * @var $taxonomies_categories
 * @var $taxonomies_tags
 * @var $order
 * @var $order_by
 * @var $autoplay
 * @var $autoplay_speed
 * @var $arrows
 * @var $display_filter
 * @var $el_class
 * @var $el_id
 * @var $css
 * @var $css_animation
 * Shortcode class
 * @var $this WPBakeryShortCode_Alc_Post_Slider
 */

$title = $items_per_page = $slide_to_show = $img_size = $offset = $taxonomies_categories = $taxonomies_tags = $order = $order_by = $autoplay = $autoplay_speed = $arrows = $display_filter = $el_class = $el_id = $css = $css_animation = '';

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

// filter by categories
if( ! empty( $taxonomies_categories ) ) {
	$args['category_name'] = $taxonomies_categories;
}

// filter by tags
if( ! empty( $taxonomies_tags ) ) {
	$args['tag'] = $taxonomies_tags;
}

$query = new WP_Query( $args );


// Post Template
$post_template = 'post-slide';
$slidesToShow  = 1;
$categoryFilter = 'category-filter';

if ( $slide_to_show == 'slide_4' ) {
	$posts_classes_array = array(
		'posts',
		'posts--carousel',
		'slick',
		'video-carousel',
		'row'
	);
	$post_template = 'post-slide-sm';
	$slidesToShow  = 4;
	$categoryFilter = 'category-filter category-filter--extra-space';
} else {
	$posts_classes_array = array(
		'posts',
		'posts--slider-featured',
		'slick',
	);
}

$posts_classes = implode( " ", $posts_classes_array );
?>


<!-- Post Loop -->
<div <?php echo implode( ' ', $wrapper_attributes ); ?> class="<?php echo esc_attr( apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $css_class, $this->settings['base'], $atts ) ); ?>">

	<?php if ( $title ) : ?>
		<div class="card__header card__header--has-filter">
			<?php echo wpb_widget_title( array( 'title' => $title ) ) ?>

			<?php if ( $display_filter ) : ?>
				<?php if( !empty( $taxonomies_categories ) ) : ?>
				<?php $taxonomies_categories_array = explode( ", ", $taxonomies_categories ); ?>
				<ul class="<?php echo esc_attr( $categoryFilter ); ?>" id="category-filter-<?php echo esc_attr( $id ); ?>">
					<li class="category-filter__item"><a href="#" class="category-filter__link category-filter__link--reset category-filter__link--active"><?php esc_html_e( 'All', 'alchemists' ); ?></a></li>

					<?php foreach ( $taxonomies_categories_array as $taxonomies_category) : ?>
						<?php $category = get_category_by_slug($taxonomies_category); ?>
						<?php if (! empty( $category ) ) : ?>
						<li class="category-filter__item">
							<a href="#" class="category-filter__link" data-category="category-<?php echo esc_attr( $category->slug ); ?>"><?php echo esc_html( $category->name ); ?></a>
						</li>
						<?php endif; ?>
					<?php endforeach; ?>

				</ul>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="card__content">

		<?php
		if ( $query->have_posts() ) : ?>

			<div id="slick-<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $posts_classes ); ?>">

			<?php /* Start the Loop */
			while ( $query->have_posts() ) : $query->the_post();

				include( locate_template( 'template-parts/content-' . $post_template . '.php' ) );

			endwhile;

			// Reset the global $the_post as this query will have stomped on it
			wp_reset_postdata(); ?>

			</div><!-- .posts -->

			<script type="text/javascript">
				(function($){
					$(function() {
						// Posts
						$slick_post_slider_<?php echo esc_js( $id ); ?> = $('#slick-<?php echo esc_js( $id ); ?>'),

						$slick_post_slider_<?php echo esc_js( $id ); ?>.slick({
							slidesToShow: <?php echo esc_js( $slidesToShow ); ?>,
							slidesToScroll: 1,
							autoplay: <?php echo esc_js( $autoplay); ?>,
							autoplaySpeed: <?php echo esc_js( $autoplay_speed ); ?>,
							arrows: <?php echo esc_js( $arrows ); ?>,
							rtl: <?php echo esc_js( $is_rtl ); ?>,
							rows: 0,

							<?php if ( $slidesToShow == 4 ) : ?>

								responsive: [
									{
										breakpoint: 992,
										settings: {
											arrows: false,
											slidesToShow: 3,
											infinite: true
										}
									},
									{
										breakpoint: 769,
										settings: {
											arrows: false,
											slidesToShow: 2,
											infinite: false
										}
									},
									{
										breakpoint: 480,
										settings: {
											arrows: false,
											slidesToShow: 1,
											infinite: false
										}
									}
								]

							<?php else : ?>

								responsive: [
									{
										breakpoint: 769,
										settings: {
											arrows: false
										}
									}
								]

							<?php endif; ?>

						});

						// Filter by Categories
						var filtered = false;

						$('#category-filter-<?php echo esc_attr( $id ); ?> .category-filter__link').on('click', function(e){
							var category = $(this).data('category');
							$slick_post_slider_<?php echo esc_js( $id ); ?>.slick('slickUnfilter');
							$slick_post_slider_<?php echo esc_js( $id ); ?>.slick('slickFilter', '.' + category);
							$('#category-filter-<?php echo esc_attr( $id ); ?> .category-filter__link--active').removeClass('category-filter__link--active');
							$(this).addClass('category-filter__link--active');
							e.preventDefault();
						});

						// Reset Filter (Show All posts)
						$('#category-filter-<?php echo esc_attr( $id ); ?> .category-filter__link--reset').on('click', function(e){
							$slick_post_slider_<?php echo esc_js( $id ); ?>.slick('slickUnfilter');
							$('#category-filter-<?php echo esc_attr( $id ); ?> .category-filter__link').removeClass('category-filter__link--active');
							$(this).addClass('category-filter__link--active');
							filtered = false;
							e.preventDefault();
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
