<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     3.4.0
 * @version   4.5.5
 *
 * Shortcode attributes
 * @var $atts
 * @var $title
 * @var $link
 * @var $items_per_page
 * @var $offset
 * @var $posts_layout
 * @var $img_size
 * @var $taxonomies_categories
 * @var $taxonomies_tags
 * @var $order
 * @var $order_by
 * @var $exclude_posts
 * @var $img_effect
 * @var $el_class
 * @var $el_id
 * @var $css
 * @var $css_animation
 * Shortcode class
 * @var $this WPBakeryShortCode_Alc_Post_Grid
 */

$title = $link = $items_per_page = $offset = $posts_layout = $img_size = $taxonomies_categories = $taxonomies_tags = $order = $order_by = $exclude_posts = $img_effect = $el_class = $el_id = $css = $css_animation = '';
$a_href = $a_title = $a_target = $a_rel = '';
$attributes = array();

$output = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$class_to_filter = 'card card--clean mb-0';
$class_to_filter .= vc_shortcode_custom_css_class( $css, ' ' ) . $this->getExtraClass( $el_class ) . $this->getCSSAnimation( $css_animation );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class_to_filter, $this->settings['base'], $atts );

$wrapper_attributes = array();
if ( ! empty( $el_id ) ) {
	$wrapper_attributes[] = 'id="' . esc_attr( $el_id ) . '"';
}

//parse link
$link = ( '||' === $link ) ? '' : $link;
$link = vc_build_link( $link );
$use_link = false;
if ( strlen( $link['url'] ) > 0 ) {
	$use_link = true;
	$a_href = $link['url'];
	$a_title = $link['title'];
	$a_target = $link['target'];
	$a_rel = $link['rel'];
}

if ( $use_link ) {
	$attributes[] = 'href="' . trim( $a_href ) . '"';
	$attributes[] = 'title="' . esc_attr( trim( $a_title ) ) . '"';
	if ( ! empty( $a_target ) ) {
		$attributes[] = 'target="' . esc_attr( trim( $a_target ) ) . '"';
	}
	if ( ! empty( $a_rel ) ) {
		$attributes[] = 'rel="' . esc_attr( trim( $a_rel ) ) . '"';
	}
}

$attributes = implode( ' ', $attributes );


$alchemists_data = get_option( 'alchemists_data' );
$categories_toggle = isset( $alchemists_data['alchemists__posts-categories'] ) ? $alchemists_data['alchemists__posts-categories'] : 1;


// Posts arguments
$args = array(
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => $items_per_page,
	'order'               => $order,
	'orderby'             => $order_by,
	'offset'              => $offset,
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
);

// filter by categories
if ( ! empty( $taxonomies_categories ) ) {
	$args['category_name'] = $taxonomies_categories;
}

// filter by tags
if ( ! empty( $taxonomies_tags ) ) {
	$args['tag'] = $taxonomies_tags;
}

// exclude posts by ID
if ( ! empty ( $exclude_posts ) ) {
	$exclude_posts = str_replace(' ', '', $exclude_posts);
	$args['post__not_in'] = explode( ',', $exclude_posts );
}

$posts_query = new WP_Query($args);

// Post Template
$post_template = '';

// Check for Posts Layout
if ( $posts_layout == 'layout_1' ) {

	$posts_classes_array = array(
		'posts',
		'posts--video-grid',
		'row',
	);
	$post_template = 'grid-1';

} elseif ( $posts_layout == 'layout_2' ) {

	$posts_classes_array = array(
		'posts',
		'posts--tile',
		'post-grid',
		'post-grid--3-4',
		'row',
	);
	$post_template = 'grid-2';
	$post_thumb = 'alchemists_thumbnail-tile-lg';

} elseif ( $posts_layout == 'layout_4' ) {

	$posts_classes_array = array(
		'posts',
		'posts--card-compact',
		'row',
	);
	$post_template = 'grid-4';
	$post_thumb = 'alchemists_thumbnail-tile-lg';

} elseif ( $posts_layout == 'layout_5' ) {

	$posts_classes_array = array(
		'posts',
		'posts--tile',
		'posts--tile-alt',
		'post-grid',
		'grid-layout',
		'grid-layout--1-2-1',
		'posts--tile-alt-nopaddings',
		'posts--tile-hero-post-grid',
		'posts--tile-meta-position',
	);
	$post_template = 'grid-5';

} else {

	$posts_classes_array = array(
		'posts',
		'posts--tile',
		'post-grid',
		'post-grid--masonry',
		'row',
	);
	$post_template = 'grid-3';
}

$posts_classes = implode( " ", $posts_classes_array );

// Image Effect
$thumb_classes = array(
	'posts__thumb'
);
if ( 'duotone_base' == $img_effect ) {
	array_push( $thumb_classes, 'effect-duotone', 'effect-duotone--base' );
} elseif ( 'duotone_cat' == $img_effect ) {
	$thumb_classes[] = 'effect-duotone';
} else {
	$thumb_classes[] = 'posts__thumb--overlay-dark';
}

// Set specific classes if Card Tile layout selected
if ( 'layout_4' == $posts_layout || 'layout_5' == $posts_layout ) {
	$thumb_classes = array(
		'posts__thumb',
	);
}

$thumb_classes = implode( ' ', $thumb_classes );
?>

<!-- Post Loop -->
<div <?php echo implode( ' ', $wrapper_attributes ); ?> class="<?php echo esc_attr( apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $css_class, $this->settings['base'], $atts ) ); ?>">

	<?php if ( $title ) : ?>
	<div class="card__header">
		<?php echo wpb_widget_title( array( 'title' => $title ) ) ?>

		<?php if ( $use_link ) {
			$card_header_btn_classes = array(
				'btn',
				'btn-xs',
				'btn-default',
				'card-header__button',
			);
			// make it outline
			if ( alchemists_sp_preset( 'basketball' ) || alchemists_sp_preset( 'soccer' ) ) {
				$card_header_btn_classes[] = 'btn-outline';
			}
			echo '<a class="' . esc_attr( implode( ' ', $card_header_btn_classes ) ) . '" ' . $attributes . '>' . $a_title . '</a>';
		} ?>
	</div>
	<?php endif; ?>

	<div class="card__content">

		<?php
		if ( $posts_query->have_posts() ) : ?>

			<div class="<?php echo esc_attr( $posts_classes ); ?>" style="<?php echo esc_attr( $posts_layout != 'layout_5' ? 'align-items: flex-start' : '' ); ?>;">

				<?php
				/* Start the Loop */
				$counter = 0;
				$col_class = '';
				$post_img_class = '';
				$post_thumb = '';

				while ( $posts_query->have_posts() ) : $posts_query->the_post();
					$counter++;

					include( locate_template( 'template-parts/post-grid/content-' . $post_template . '.php' ) );

				endwhile; ?>

			</div><!-- .posts -->

			<?php // Reset the global $the_post as this query will have stomped on it
			wp_reset_postdata(); ?>

		<?php else :

			get_template_part( 'template-parts/content', 'none' );

		endif; ?>

	</div>



</div>
<!-- Post Loop / End -->
