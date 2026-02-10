<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     3.0.0
 * @version   4.7.0
 *
 * Shortcode attributes
 * @var $atts
 * @var $title
 * @var $items_per_page
 * @var $offset
 * @var $include
 * @var $posts_layout
 * @var $taxonomies_categories
 * @var $taxonomies_tags
 * @var $order
 * @var $order_by
 * @var $show_excerpt
 * @var $excerpt_length
 * @var $layout
 * @var $show_categories
 * @var $animation_speed
 * @var $pause_on_hover
 * @var $start_visible
 * @var $el_class
 * @var $el_id
 * @var $css
 * @var $css_animation
 * Shortcode class
 * @var $this WPBakeryShortCode_Alc_Text_Ticker
 */

$title = $items_per_page = $offset = $include = $posts_layout = $taxonomies_categories = $taxonomies_tags = $order = $order_by = $show_excerpt = $excerpt_length = $layout = $show_categories = $animation_speed = $pause_on_hover = $start_visible = $el_class = $el_id = $css = $css_animation = '';
$options_attributes = array();

wp_enqueue_script( 'alchemists-marquee' );

$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$class_to_filter = 'marquee-wrapper';
if ( $layout == 'boxed' ) {
	$class_to_filter .= ' marquee-wrapper--boxed ';
}
if ( $show_categories ) {
	$class_to_filter .= ' marquee-wrapper--is-categories ';
} else {
	$class_to_filter .= ' marquee-wrapper--no-categories ';
}
$class_to_filter .= vc_shortcode_custom_css_class( $css, ' ' ) . $this->getExtraClass( $el_class ) . $this->getCSSAnimation( $css_animation );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class_to_filter, $this->settings['base'], $atts );

$wrapper_attributes = array();
if ( ! empty( $el_id ) ) {
	$wrapper_attributes[] = 'id="' . esc_attr( $el_id ) . '"';
}

// Marquee settings
$start_visible = ( 1 == $start_visible ) ? 'true' : 'false';
$pause_on_hover = ( 1 == $pause_on_hover ) ? 'true' : 'false';

$options_attributes[] = 'data-startVisible="' . $start_visible . '"';
$options_attributes[] = 'data-pauseOnHover="' . $pause_on_hover . '"';
$options_attributes[] = 'data-speed="' . $animation_speed . '"';

$options_attributes = implode( ' ', $options_attributes );

// Posts arguments
$posts_ids_array = ! empty( $include ) ? explode( ',', $include ) : array();

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

// Include only specifi posts if this option enabled
if ( ! empty( $posts_ids_array ) ) {
	$args['post__in'] = $posts_ids_array;
}

// filter by categories
if( ! empty( $taxonomies_categories ) ) {
	$args['category_name'] = $taxonomies_categories;
}

// filter by tags
if( !empty( $taxonomies_tags ) ) {
	$args['tag'] = $taxonomies_tags;
}

// Posts Query
$text_ticker_query = new WP_Query( $args );
?>

<!-- Text Ticker -->
<div <?php echo implode( ' ', $wrapper_attributes ); ?> class="<?php echo esc_attr( apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $css_class, $this->settings['base'], $atts ) ); ?>">
	<div class="container">

		<?php if ( $title ) : ?>
			<div class="marquee-label">
				<?php echo esc_html( $title ); ?>
			</div>
		<?php endif; ?>

		<?php
		if ( $text_ticker_query->have_posts() ) : ?>

			<div class="marquee" <?php echo wp_kses_post($options_attributes ); ?> data-gap="10" data-duplicated="true" <?php echo is_rtl() ? 'data-direction="right" dir="ltr"' : '' ?>>
				<ul class="posts posts--inline">
					<?php while ( $text_ticker_query->have_posts() ) : $text_ticker_query->the_post(); ?>

						<li class="posts__item">
							<?php if ( $show_categories ) : ?>
								<?php alchemists_post_category_labels(); ?>
							<?php endif; ?>
							<?php the_title( '<h6 class="posts__title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h6>' ); ?>
							<?php if ( $show_excerpt ) : ?>
								<div class="posts__excerpt">
									<?php echo alchemists_string_limit_words( get_the_excerpt(), $excerpt_length ); ?>
								</div>
							<?php endif; ?>
						</li>

					<?php endwhile; ?>
				</ul>
			</div><!-- .marquee posts -->

			<?php // Reset the global $the_post as this query will have stomped on it
			wp_reset_postdata(); ?>

		<?php endif; ?>

	</div>
</div>
<!-- Text Ticker / End -->
