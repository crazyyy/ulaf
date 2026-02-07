<?php
/**
 * Single Product Image
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/product-image.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.0.0
 */

defined( 'ABSPATH' ) || exit;

// Note: `wc_get_gallery_image_html` was added in WC 3.3.2 and did not exist prior. This check protects against theme overrides being used on older versions of WC.
if ( ! function_exists( 'wc_get_gallery_image_html' ) ) {
	return;
}

global $post, $product;
$columns           = apply_filters( 'woocommerce_product_thumbnails_columns', 4 );
$thumbnail_size    = apply_filters( 'woocommerce_product_thumbnails_large_size', 'full' );
$post_thumbnail_id = get_post_thumbnail_id( $post->ID );
$full_size_image   = wp_get_attachment_image_src( $post_thumbnail_id, 'full' );
$image_title       = get_post_field( 'post_excerpt', $post_thumbnail_id );
$placeholder       = has_post_thumbnail() ? 'with-images' : 'without-images';
$wrapper_classes   = apply_filters(
	'woocommerce_single_product_image_gallery_classes',
	array(
		'woocommerce-product-gallery',
		'woocommerce-product-gallery--' . $placeholder,
		'woocommerce-product-gallery--columns-' . absint( $columns ),
		'images',
	)
);
$allowed_html      = array(
	'div' => array(
		'class' => array(),
		'id'    => array(),
	),
	'a'   => array(
		'class' => array(),
		'id'    => array(),
		'href'  => array(),
	),
	'img' => array(
		'src'      => array(),
		'alt'      => array(),
		'class'    => array(),
		'id'       => array(),
		'role'     => array(),
		'style'    => array(),
		'sizes'    => array(),
		'width'    => array(),
		'height'   => array(),
		'srcset'   => array(),
		'loading'  => array(),
		'decoding' => array(),
	),
);
?>
<div class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $wrapper_classes ) ) ); ?>" data-columns="<?php echo esc_attr( $columns ); ?>" style="opacity: 0; transition: opacity .25s ease-in-out;">
	<figure class="woocommerce-product-gallery__wrapper">
		<?php
		$attributes = array(
			'title'                   => $image_title,
			'data-src'                => $full_size_image[0],
			'data-large_image'        => $full_size_image[0],
			'data-large_image_width'  => $full_size_image[1],
			'data-large_image_height' => $full_size_image[2],
		);

		if ( has_post_thumbnail() ) {
			$html  = '<div data-thumb="' . get_the_post_thumbnail_url( $post->ID, 'shop_thumbnail' ) . '" class="woocommerce-product-gallery__image"><a href="' . esc_url( $full_size_image[0] ) . '">';
			$html .= get_the_post_thumbnail( $post->ID, 'shop_single', $attributes );
			$html .= '</a></div>';
		} else {
			$html  = '<div class="woocommerce-product-gallery__image--placeholder">';
			$html .= sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src() ), esc_html__( 'Awaiting product image', 'splash' ) );
			$html .= '</div>';
		}

		echo wp_kses( apply_filters( 'woocommerce_single_product_image_thumbnail_html', $html, get_post_thumbnail_id( $post->ID ) ), $allowed_html );

		do_action( 'woocommerce_product_thumbnails' );
		?>
	</figure>
</div>

<script type="text/javascript">
	(function ($) {

		$(function () {

			var itemsCount = 3;
			<?php if ( ! splash_is_layout( 'sccr' ) ) : ?>
			itemsCount = 4;
			<?php endif; ?>

			$(document).ready(function () {
				<?php if ( ! splash_is_layout( 'baseball' ) ) : ?>

				var owl = $(".flex-control-thumbs");
				owl.owlCarousel({
					items: 3,
					dots: false,
					autoplay: false,
					slideBy: 1,
					loop: false,
					navText: '',
					margin: 4,
					animateOut: 'slideOutUp',
					animateIn: 'slideInUp',
					responsive: {
						560: {
							items: 4,
							slideBy: 1
						},
						768: {
							items: 3,
							slideBy: 1
						},
						1024: {
							items: itemsCount,
							slideBy: 1
						}
					}
				});

				<?php else : ?>
				$(".woocommerce-product-gallery").find("ol").removeClass().addClass("productVerticalThumbs");
				$('ol.productVerticalThumbs').slick({
					arrows: false,
					dots: false,
					vertical: true,
					verticalSwiping: true,
					infinite: false,
					slidesToShow: 6,
					slidesToScroll: 1,
					swipe: true,
					swipeToSlide: true
				});
				<?php endif; ?>
			});
		});

	})(jQuery);
</script>
