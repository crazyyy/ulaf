<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.5.9
 *
 * Shortcode attributes
 * @var $atts
 * @var $title
 * @var $link
 * @var $items_per_page
 * @var $add_pagination
 * @var $offset
 * @var $posts_layout
 * @var $img_size
 * @var $taxonomies_categories
 * @var $taxonomies_tags
 * @var $order
 * @var $order_by
 * @var $exclude_posts
 * @var $exclude_taxonomies_tags
 * @var $disable_excerpt
 * @var $excerpt_size
 * @var $el_class
 * @var $ignore_sticky_posts
 * @var $el_id
 * @var $css
 * @var $css_animation
 * Shortcode class
 * @var $this WPBakeryShortCode_Alc_Post_Loop
 */

$title = $link = $items_per_page = $add_pagination = $offset = $posts_layout = $img_size = $taxonomies_categories = $taxonomies_tags = $order = $order_by = $exclude_posts = $exclude_taxonomies_tags = $disable_excerpt = $ignore_sticky_posts = $excerpt_size = $el_class = $el_id = $css = $css_animation = '';
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


// Posts arguments
$args = array(
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => $items_per_page,
	'order'               => $order,
	'orderby'             => $order_by,
);

// Ignore sticky posts
if ( ! empty( $ignore_sticky_posts ) ) {
	$args['ignore_sticky_posts'] = true;
}

// Simplify query if no pagination selected
if ( empty( $add_pagination ) ) {
	$args['no_found_rows'] = true;
}

// Adds paged if pagination enabled
if ( is_front_page() ) {
	$paged_val = 'page';
} else {
	$paged_val = 'paged';
}
if ( ! empty( $add_pagination ) ) {
	$paged = ( get_query_var( $paged_val ) ) ? get_query_var( $paged_val ) : 1;
	$args['paged'] = $paged;
}

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

// exclude posts by tags
if ( ! empty( $exclude_taxonomies_tags ) ) {
	$exclude_taxonomies_tags_array = explode(', ', $exclude_taxonomies_tags);
	$exclude_taxonomies_tag_ids = array();
	foreach ($exclude_taxonomies_tags_array as $slug) {
		$tag = get_term_by( 'slug', $slug, 'post_tag' );
		$exclude_taxonomies_tag_ids[] = $tag->term_id;
	}
	$args['tag__not_in'] = str_replace( ' ', '', $exclude_taxonomies_tag_ids );
}

// Offset
if ( ! empty( $offset ) ) {
	$current_page = max( 1, get_query_var( $paged_val ) );
	$offset_calc = ( $current_page - 1 ) * $items_per_page + $offset;

	$args['offset'] = $offset_calc;
}

$posts_query = new WP_Query($args);

// Post Template
$post_template = '';

// Check for Posts Layout
if ( $posts_layout == 'grid_2cols' ) {

	$posts_classes_array = array(
		'posts',
		'posts--cards',
		'post-grid',
		'post-grid--2cols',
		'row',
	);
	$post_template = 'blog-1';

} elseif ( $posts_layout == 'grid_3cols' ) {

		$posts_classes_array = array(
			'posts',
			'posts--cards',
			'post-grid',
			'row',
		);
		$post_template = 'blog-1-3cols';

} elseif ( $posts_layout == 'grid_tile_2cols' ) {

	$posts_classes_array = array(
		'posts',
		'posts--tile',
		'posts--tile-alt',
		'post-grid',
		'row',
	);
	$post_template = 'blog-6';

} elseif ( $posts_layout == 'grid_1col' ) {

	$posts_classes_array = array(
		'posts',
		'posts--cards',
		'post-grid',
		'post-grid--1col',
		'row',
	);
	$post_template = 'card-1col';

} elseif ( $posts_layout == 'grid_1col_tile' ) {

	$posts_classes_array = array(
		'posts',
		'posts--tile',
		'posts--tile-alt',
	);
	$post_template = 'card-tile-1col';

} elseif ( $posts_layout == 'grid_1col_tile_sm' ) {

	$posts_classes_array = array(
		'posts',
		'posts--tile',
		'posts--tile-alt',
		'posts--tile-meta-position',
		'posts--tile-thumb-bordered',
	);
	$post_template = 'card-tile-sm-1col';

} elseif ( $posts_layout == 'list_simple' ) {

	$posts_classes_array = array(
		'posts',
		'posts--simple-list',
		'posts--simple-list--lg',
	);
	$post_template = 'blog-list-simple';

} elseif ( $posts_layout == 'list_simple_1st_ext' ) {

	$posts_classes_array = array(
		'posts',
		'posts--simple-list',
		'posts--simple-list-condensed',
	);
	$post_template = 'blog-list-simple';

} elseif ( $posts_layout == 'list_simple_hor' ) {

	$posts_classes_array = array(
		'posts',
		'posts--simple-list',
		'posts--horizontal',
	);
	$post_template = 'blog-list-simple';

} elseif ( $posts_layout == 'list_thumb_sm' ) {

	$posts_classes_array = array(
		'posts',
		'posts--simple-list',
	);
	$post_template = 'blog-list-thumb-sm';

}  elseif ( $posts_layout == 'masonry' ) {

	$posts_classes_array = array(
		'posts',
		'posts--cards',
		'post-grid',
		'post-grid--masonry',
		'row',
	);
	$post_template = 'blog-4';

} else {

	$posts_classes_array = array(
		'posts',
		'posts--cards',
		'posts--cards-thumb-left',
		'post-list',
	);
}

// Disable Excerpt
if( !empty( $disable_excerpt ) ) {
	$posts_classes_array[] = 'posts--excerpt-hide';
}

$posts_classes = implode( " ", $posts_classes_array );
?>

<!-- Post Loop -->
<div <?php echo implode( ' ', $wrapper_attributes ); ?> class="<?php echo esc_attr( apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $css_class, $this->settings['base'], $atts ) ); ?>">

	<?php if ( $title ) { ?>
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
	<?php } ?>

	<div class="card__content">

		<?php
		if ( $posts_query->have_posts() ) : ?>

			<?php if ( $posts_layout == 'list_simple' || $posts_layout == 'list_simple_hor' || $posts_layout == 'list_simple_1st_ext' || $posts_layout == 'list_thumb_sm' ) : ?>
			<div class="card"><div class="card__content">
			<?php endif; ?>

			<div class="<?php echo esc_attr( $posts_classes ); ?>">

				<?php /* Start the Loop */
				$counter = 0;
				set_query_var( 'excerpt_size', $excerpt_size );
				while ( $posts_query->have_posts() ) : $posts_query->the_post();
					$counter++;

					if ( $posts_layout == 'list_simple_1st_ext' ) {
						if ( $counter == 1 ) {
							include( locate_template( 'template-parts/content-blog-list-simple-ext.php' ) );
						} else {
							include( locate_template( 'template-parts/content-' . $post_template . '.php' ) );
						}
					} else {
						if ( '' == $post_template ) {
							include( locate_template( 'template-parts/content.php' ) );
						} else {
							include( locate_template( 'template-parts/content-' . $post_template . '.php' ) );
						}
					}

				endwhile; ?>

			</div><!-- .posts -->

			<?php if ( $posts_layout == 'list_simple' || $posts_layout == 'list_simple_hor' || $posts_layout == 'list_simple_1st_ext' || $posts_layout == 'list_thumb_sm' ) : ?>
			</div></div>
			<?php endif; ?>

			<?php
			// Offset
			if ( ! empty( $offset ) ) {
				$total_rows   = max( 0, $posts_query->found_posts - $offset );
				$total_pages  = ceil( $total_rows / $items_per_page );
			} else {
				$total_pages  = $posts_query->max_num_pages;
				$current_page = max( 1, get_query_var( $paged_val ) );
			}
			// adds pagination if enabled
			if ( ! empty( $add_pagination ) ) :
				$pagination_args = array(
					'total'        => $total_pages,
					'current'      => $current_page,
					'prev_text'    => '<i class="fa fa-angle-left"></i>',
					'next_text'    => '<i class="fa fa-angle-right"></i>',
					'type'         => 'list',
				);

				// Add an anchor if ID set
				// can be useful on page reload scrolling to the element
				if ( ! empty( $el_id ) ) {
					$pagination_args['add_fragment'] = '#' . esc_attr( $el_id );
				}
				echo paginate_links( $pagination_args );

			endif;
			?>

			<?php // Reset the global $the_post as this query will have stomped on it
			wp_reset_postdata(); ?>

		<?php else :

			get_template_part( 'template-parts/content', 'none' );

		endif; ?>

	</div>



</div>
<!-- Post Loop / End -->
