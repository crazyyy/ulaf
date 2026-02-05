<?php

class bt_bb_single_product extends BT_BB_Element {

	function handle_shortcode( $atts, $content ) {
		extract( shortcode_atts( apply_filters( 'bt_bb_extract_atts', array(
			'product_id'        		=> '',
			'product_image'      		=> '',
			'product_title'        		=> '',
			'product_price'      		=> '',
			'button_width'				=> ''
		) ), $atts, $this->shortcode ) );
		
		if ( class_exists( 'WooCommerce' ) && $product_id != '' ) {
			$product = wc_get_product( $product_id );
		} else {
			$product = false;
		}
		$product_exists = ( $product != false ) ? true : false;
		
		$class = array( $this->shortcode, 'woocommerce' );

		if ( !$product_exists ) {
			$class[] = "btNoWooProduct";
		}

		if ( $el_class != '' ) {
			$class[] = $el_class;
		}

		$id_attr = '';
		if ( $el_id != '' ) {
			$id_attr = ' ' . 'id="' . esc_attr( $el_id ) . '"';
		}

		$style_attr = '';
		if ( $el_style != '' ) {
			$style_attr = ' ' . 'style="' . esc_attr( $el_style ) . '"';
		}

		if ( $button_width != '' ) {
			$class[] = $this->prefix . 'button_width' . '_' . $button_width;
		}
		
		if ( $product_title == '' && $product_exists ) {
			$product_title = $product->get_title();
		}
		
		if ( $product_price == '' && $product_exists ) {
			$product_price = $product->get_price_html();
		}
		
		if ( $product_image == '' ) {
			if ( $product_exists ) $product_image = $product->get_image( 'shop_catalog' );
		} else {
			$post_image = get_post( $product_image );
			if ( $post_image == '' ) return;
		
			$image = wp_get_attachment_image_src( $product_image, "full" );
			$caption = $post_image->post_excerpt;
			
			$image = $image[0];
			if ( $caption == '' ) {
				$caption = $product_title;
			}
			$product_image = '<img src="' . esc_url( $image ) . '" alt="' . esc_attr( $caption ) . '" title="' . esc_attr( $caption ) . '" >';
		}

		$class = apply_filters( $this->shortcode . '_class', $class, $atts );		

		$output = '<div class="' . esc_attr( implode( ' ', $class ) ) . '"' . $style_attr . '' . $id_attr . '>';
			
			$output .= '<div class="' . esc_attr( $this->shortcode . '_image' ) . '">';
				$output .= '<div class="' . esc_attr( $this->shortcode . '_image_link' ) . '"><a href="' . get_permalink($product_id) . '" target="_self">' . $product_image . '</a></div>';
			$output .= '</div>';
			
			$output .= '<div class="' . esc_attr( $this->shortcode . '_content' ) . '">';
				if ( $product_title != '' ) $output .= '<div class="' . esc_attr( $this->shortcode . '_title' ) . '"><a href="' . get_permalink($product_id) . '" target="_self">' . do_shortcode('[bt_bb_headline headline="' . esc_attr( $product_title ) . '" html_tag="h4" size="small" ignore_fe_editor="true"]' ) . '</a></div>';
				
				if ( $product_price != '' ) $output .= '<div class="' . esc_attr( $this->shortcode . '_price' ) . '"><span>' . $product_price . '</span></div>';

				if (  $product_exists ) {
					$output .= '<div class="' . esc_attr( $this->shortcode . '_price_cart' ) . '">';
						$output .= do_shortcode( '[add_to_cart id="' . esc_attr( $product_id ) . '" style="" show_price="false"]' );
					$output .= '</div>';
				}
				
			$output .= '</div>';
		$output .= '</div>';

		$output = apply_filters( 'bt_bb_general_output', $output, $atts );
		$output = apply_filters( $this->shortcode . '_output', $output, $atts );

		return $output;

	}

	function map_shortcode() {

		$color_scheme_arr = bt_bb_get_color_scheme_param_array();

		if ( class_exists( 'WooCommerce' )  ) {
			$args = array(
				'limit' 		=> -1,
				'orderby' 		=> 'title',
				'order' 		=> 'ASC',
			);
			$query = new WC_Product_Query( $args );
			$products = $query->get_products();
			$products_arr = array();
			$products_arr[ 'Not selected' ] = 0;
			foreach($products as $product) {
				$products_arr[ $product->get_name() ] = $product->get_id();
			}
		} else {
			$products_arr = array();
		}
		
		bt_bb_map( $this->shortcode, array( 'name' => esc_html__( 'Single product', 'campo' ), 'description' => esc_html__( 'Single product', 'campo' ),  'icon' => $this->prefix_backend . 'icon' . '_' . $this->shortcode,
			'params' => array(
				array( 'param_name' => 'product_id', 'type' => 'dropdown', 'heading' => esc_html__( 'Product', 'campo' ), 'preview' => true, 'value' => $products_arr ),
				array( 'param_name' => 'product_title', 'type' => 'textfield', 'preview' => true, 'heading' => esc_html__( 'Custom product title', 'campo' ) ),
				array( 'param_name' => 'product_price', 'type' => 'textfield', 'heading' => esc_html__( 'Custom product price', 'campo' ) ),
				array( 'param_name' => 'product_image', 'preview' => true, 'type' => 'attach_image', 'heading' => esc_html__( 'Custom product image', 'campo' ) ),
				array( 'param_name' => 'button_width', 'type' => 'dropdown', 'heading' => esc_html__( 'Button width', 'campo' ),
					'value' => array(
						esc_html__( 'Inline', 'campo' ) 	=> '',
						esc_html__( 'Full', 'campo' ) 	=> 'full'
					)
				)
			)
		) );
	}
}