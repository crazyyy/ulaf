<?php
/**
 * The template for displaying product widget entries.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-widget-product.php.
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.5.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

if ( ! is_a( $product, 'WC_Product' ) ) {
	return;
}

$attributes   = array();

// Post Styling Options
$alchemists_data = get_option( 'alchemists_data' );
$product_gradient = isset( $alchemists_data['alchemists__product-gradient'] ) ? $alchemists_data['alchemists__product-gradient'] : 1;

if ( $product_gradient ) {
	if ( alchemists_sp_preset( 'soccer' ) ) {
		$product_color_1_default = '#ffffff';
		$product_color_2_default = '#ffffff';
	} else {
		$product_color_1_default = '#fe2b00';
		$product_color_2_default = '#f7d500';
	}

	// Post Styling Options
	$product_color_1   = get_field('product_grad_color_1') ? get_field('product_grad_color_1') : $product_color_1_default;
	$product_color_2   = get_field('product_grad_color_2') ? get_field('product_grad_color_2') : $product_color_2_default;

	$attributes[] = 'style="background-image: linear-gradient(to left top, ' . $product_color_1 . ', ' . $product_color_2 . ');"';
}

?>

<li class="products-list__item">
	<?php do_action( 'woocommerce_widget_product_item_start', $args ); ?>

	<figure class="products-list__product-thumb" <?php echo implode( ' ', $attributes ); ?>>
		<a href="<?php echo esc_url( $product->get_permalink() ); ?>">
			<?php echo wp_kses_post( $product->get_image() ); // PHPCS:Ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</a>
	</figure>
	<div class="products-list__inner">
		<?php echo wc_get_product_category_list( $product->get_id(), ', ', '<span class="products-list__product-cat">', '</span>' ); ?>
		<h5 class="products-list__product-title">
			<a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo wp_kses_post( $product->get_name() ); ?></a>
		</h5>
		<?php if ( ! empty( $show_rating ) ) : ?>
			<div class="products-list__product-ratings">
			<?php echo wc_get_rating_html( $product->get_average_rating() ); // PHPCS:Ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		<?php endif; ?>
		<div class="products-list__product-amount">
			<?php echo wp_kses_post( $product->get_price_html() ); // PHPCS:Ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	</div>

	<?php do_action( 'woocommerce_widget_product_item_end', $args ); ?>
</li>
