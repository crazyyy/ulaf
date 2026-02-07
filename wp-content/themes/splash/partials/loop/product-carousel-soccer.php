<?php
global $post;

$product_id = $post->ID;
if ( class_exists( 'WooCommerce' ) ):
	$product = new WC_Product( $product_id );
	$image = (!splash_is_af()) ? splash_get_thumbnail_url($product_id, 0, 'stm-570-350') : splash_get_thumbnail_url($product_id, 0, 'stm-270-370') ;
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
endif;