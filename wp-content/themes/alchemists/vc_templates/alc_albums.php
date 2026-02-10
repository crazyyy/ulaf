<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.2.0
 * @version   4.4.2
 *
 * Shortcode attributes
 * @var $atts
 * @var $display_style
 * @var $post_id
 * @var $items_per_page
 * @var $offset
 * @var $posts_layout
 * @var $album_type
 * @var $album_categories
 * @var $order
 * @var $order_by
 * @var $el_class
 * @var $el_id
 * @var $css
 * @var $css_animation
 * Shortcode class
 * @var $this WPBakeryShortCode_Alc_Albums
 */

$display_style = $post_id = $items_per_page = $offset = $posts_layout = $album_type = $album_categories = $order = $order_by = $el_class = $el_id = $css = $css_animation = '';
$a_href = $a_title = $a_target = $a_rel = '';
$attributes = array();

$output = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$class_to_filter = '';
$class_to_filter .= vc_shortcode_custom_css_class( $css, ' ' ) . $this->getExtraClass( $el_class ) . $this->getCSSAnimation( $css_animation );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class_to_filter, $this->settings['base'], $atts );

$wrapper_attributes = array();
if ( ! empty( $el_id ) ) {
	$wrapper_attributes[] = 'id="' . esc_attr( $el_id ) . '"';
}

if ( $display_style == 'pagination' ) {
	$paged = ( get_query_var('paged') ) ? get_query_var( 'paged' ) : 1;
}

// Posts arguments
$album_args = array(
	'post_type'      => 'albums',
	'post_status'    => 'publish',
	'posts_per_page' => $items_per_page,
	'order'          => $order,
	'orderby'        => $order_by,
	'offset'         => $offset,
);

// set particular post by id
if ( ! empty( $post_id ) ) {
	$album_args['post__in'] = explode(', ', $post_id );
}

if ( $display_style == 'pagination' ) {
	$album_args['paged'] = $paged;
}

// Filter by categories
if( !empty( $album_categories )) {
	$album_categories = explode(', ', $album_categories);
	if( !empty( $album_categories ) ) {
		$album_args['tax_query'] = array(
			'relation' => 'OR'
		);
		foreach( $album_categories as $album_category ) {
			$album_args['tax_query'][] = array(
				'taxonomy' => 'catalbums',
				'field'    => 'slug',
				'terms'    => $album_category
			);
		}
	}
}

$albums_query = new WP_Query( $album_args );

// Post Template
$post_template     = 'album-content-3cols';
$thumb_size        = 'alchemists_thumbnail';
$thumb_placeholder = 'placeholder-380x270';
$preset_name       = 'default';

if ( 'heading_top' == $album_type ) {
	$preset_name = 'heading-top';
} elseif ( 'img_top_thumbs_title_bottom' == $album_type ) {
	$preset_name = 'img-top-thumbs-title-bottom';
}

// Check for Posts Layout
if ( $posts_layout == 'grid_2cols' ) {

	$posts_classes_array = array(
		'gallery__item col-6 col-sm-6'
	);
	$post_template     = 'album-content-2cols';
	$thumb_size        = 'alchemists_thumbnail-n';
	$thumb_placeholder = 'placeholder-500x280';

} elseif ( $posts_layout == 'grid_4cols' ) {

	$posts_classes_array = array(
		'gallery__item col-6 col-sm-3'
	);
	$post_template     = 'album-content-4cols';
	$thumb_size        = 'alchemists_thumbnail-sm';
	$thumb_placeholder = 'placeholder-380x270';

} else {

	$posts_classes_array = array(
		'gallery__item col-6 col-sm-4'
	);
}

$posts_classes = implode( " ", $posts_classes_array );

?>


<!-- Albums -->
<div <?php echo implode( ' ', $wrapper_attributes ); ?> class="<?php echo esc_attr( apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $css_class, $this->settings['base'], $atts ) ); ?>">

	<?php
	if ( $albums_query->have_posts() ) : ?>

		<div class="gallery row">

			<?php /* Start the Loop */
			while ( $albums_query->have_posts() ) : $albums_query->the_post(); ?>

				<?php include( locate_template( 'vc_templates/alc_albums/alc_albums-' . $preset_name . '.php' ) ); ?>

			<?php endwhile; ?>

			<?php // Reset the global $the_post as this query will have stomped on it
			wp_reset_postdata(); ?>

		</div><!-- .posts -->

		<?php if ( $display_style == 'pagination' ) :
			echo paginate_links( array(
				'total'        => $albums_query->max_num_pages,
				'current'      => max( 1, get_query_var( 'paged' ) ),
				'format'       => '?paged=%#%',
				'prev_text'    => '<i class="fa fa-angle-left"></i>',
				'next_text'    => '<i class="fa fa-angle-right"></i>',
				'type'         => 'list',
			) );
		endif; ?>

	<?php else :

		get_template_part( 'template-parts/content', 'none' );

	endif; ?>


</div>
<!-- Albums / End -->
