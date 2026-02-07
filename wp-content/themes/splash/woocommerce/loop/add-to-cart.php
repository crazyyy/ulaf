<?php
/**
 * Loop Add to Cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/add-to-cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     9.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

$cl  = 'btn-secondary btn-style-4';
$clb = 'button';

if ( splash_is_af() ) {
	$cl  = '';
	$clb = '';
}

echo wp_kses_post(
	apply_filters(
		'woocommerce_loop_add_to_cart_link',
		sprintf(
			'<a rel="nofollow" href="%s" data-quantity="%s" data-product_id="%s" data-product_sku="%s" class="' . $cl . ' %s">%s</a>',
			esc_url( $product->add_to_cart_url() ),
			esc_attr( isset( $args['quantity'] ) ? $args['quantity'] : 1 ),
			esc_attr( $product->get_id() ),
			esc_attr( $product->get_sku() ),
			esc_attr( isset( $class ) ? $class : $clb ),
			esc_html( $product->add_to_cart_text() )
		),
		$product
	)
);
