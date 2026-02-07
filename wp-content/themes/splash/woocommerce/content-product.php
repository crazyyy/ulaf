<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product, $woocommerce_loop;
// Store loop count we're currently on
if ( empty( $woocommerce_loop['loop'] ) ) {
	$woocommerce_loop['loop'] = 0;
}

// Store column count for displaying the grid
if ( empty( $woocommerce_loop['columns'] ) ) {
	$woocommerce_loop['columns'] = apply_filters( 'loop_shop_columns', 4 );
}

// Ensure visibility
if ( ! $product || ! $product->get_data()['catalog_visibility'] ) {
	return;
}

// `crease loop count
$woocommerce_loop['loop'] ++;

$per_row = get_theme_mod('shop_archive_pp', 3);

$per_row = intval(12/$per_row);

// Extra post classes
$classes   = array();
$classes[] = 'col-md-' . $per_row;
if(splash_is_layout('sccr')){
	$classes[] = 'col-sm-4 col-xs-6 col-sx-6';
}
else if(splash_is_layout('soccer_two')){
	$classes[] = 'col-sm-6 col-xs-12';
}
else {
	$classes[] = 'col-sm-6 col-xs-6';
}
$classes[] = 'stm-single-product-loop';
if ( 0 === ( $woocommerce_loop['loop'] - 1 ) % $woocommerce_loop['columns'] || 1 === $woocommerce_loop['columns'] ) {
	$classes[] = 'first';
}
if ( 0 === $woocommerce_loop['loop'] % $woocommerce_loop['columns'] ) {
	$classes[] = 'last';
}
?>
<div <?php post_class( $classes ); ?>>

	<?php if(!splash_is_layout("sccr")) : ?>
	<div class="stm-product-content-loop-inner">
		<div class="stm-product-content-image stm-badge-wrapper">
			<?php
			/**
			 * woocommerce_before_shop_loop_item_title hook.
			 *stm-product-content-loop-inner
			 * @hooked woocommerce_show_product_loop_sale_flash - 10
			 * @hooked woocommerce_template_loop_product_thumbnail - 10
			 */

			do_action( 'woocommerce_before_shop_loop_item_title' );
			/**
			 * woocommerce_after_shop_loop_item hook.
			 *
			 * @hooked woocommerce_template_loop_product_link_close - 5
			 * @hooked woocommerce_template_loop_add_to_cart - 10
			 */
			?>
			<div class="stm-button-inner">
				<?php if(splash_is_af()): ?><span class="btn-secondary btn-style-4 button"><?php endif;?>
					<?php do_action( 'woocommerce_after_shop_loop_item' ); ?>
				<?php if(splash_is_af()): ?></span><?php endif; ?>
			</div>
		</div>

		<?php
		/**
		 * woocommerce_before_shop_loop_item hook.
		 *
		 * @hooked woocommerce_template_loop_product_link_open - 10
		 */
		do_action( 'woocommerce_before_shop_loop_item' );
		?>

			<div class="clearfix">
				<div class="title">

					<?php
					/**
					 * woocommerce_shop_loop_item_title hook.
					 *
					 * @hooked woocommerce_template_loop_product_title - 10
					 */
					do_action( 'stm_woocommerce_shop_loop_item_title' ); ?>
					<p><?php echo wp_kses_post( get_the_excerpt() ); ?></p>
				</div>

				<div class="meta">
					<?php
					/**
					 * woocommerce_after_shop_loop_item_title hook.
					 *
					 * @hooked woocommerce_template_loop_rating - 5
					 * @hooked woocommerce_template_loop_price - 10
					 */
					do_action( 'woocommerce_after_shop_loop_item_title' ); ?>
				</div>
			</div>
		</a>
	</div>
	<?php else : ?>
	<div class="stm-product-content-loop-inner">
	<?php
		$product_id = $product->get_id();
		$product = new WC_Product( $product_id );
		$image = splash_get_thumbnail_url($product_id, 0, 'stm-270-370') ;
		$currency = get_woocommerce_currency_symbol();

		$price = $product->get_price();
		$stock      = $product->is_in_stock() ? 'true' : 'false';
		$onsale     = $product->is_on_sale() ? 'true' : 'false';
		if ( $stock == "true" ) {

			$output = '<div class="' . esc_attr( implode( ' ', get_post_class( array(), $product_id ) ) ) . '">';

			$output .= '<a href="' . esc_url( get_the_permalink( $product_id ) ) . '">';
			if ( $onsale == 'true' ) {
				$output .= '<span class="onsale normal_font"></span>';
			}
			$output .= woocommerce_get_product_thumbnail();
			

			$output .= '<div class="product_header clearfix">';
			$output .= '<h3>' . get_the_title( $product_id ) . '</h3>';
			//$output .= '<h3><a href="' . esc_url( get_the_permalink( $product_id ) ) . '">' . get_the_title( $product_id ) . '</a></h3>';
			//$output .= '<a class="reviews" href="' . esc_url( get_the_permalink( $product_id ) ) . '#reviews">' . sprintf( __( '%s reviews', 'splash' ), $product->get_rating_count() ) . '</a>';
			$output .= '</div>';
			$output .= '</a>';

			$output .= '<div class="product_info clearfix">';
			$categories = wp_get_post_terms( $product_id, 'product_cat' );
			if ( $categories ) {
				$output .= '<a href="' . esc_url( get_term_link( $categories[0] ) ) . '" class="category">' . $categories[0]->name . '</a>';
			}

			if ( $rating_html = $product->get_average_rating() ) {
				$output .= $rating_html;
			}

			$output .= '</div>';

			$output .= '<div class="product_footer clearfix heading-font">';
			if ( $price_html = $product->get_price_html() ) {
				$output .= '<span class="price">' . $price_html . '</span>';
			}
			$output .= sprintf( '<a href="%s" rel="nofollow" data-product_id="%s" data-product_sku="%s" class="%s product_type_%s">%s</a>',
				esc_url( get_permalink( wc_get_page_id( 'shop' ) ) . '?add-to-cart=' . $product_id ),
				esc_attr( $product->get_id() ),
				esc_attr( $product->get_sku() ),
				$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
				esc_attr( $product->get_type() ), '<i class="fa fa-cart-arrow-down"></i>' . esc_html__( 'ADD', 'splash' ), $product );

			$output .= '</div>';

			$output .= '</div>';
			echo splash_sanitize_text_field($output);
		}

	?>
	</div>
	<?php endif;?>
</div>