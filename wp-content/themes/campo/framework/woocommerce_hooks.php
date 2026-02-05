<?php

/* Actions and filters */

remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);

add_action( 'after_setup_theme', 'boldthemes_woocommerce_support' );
add_action('woocommerce_before_main_content', 'boldthemes_woo_wrapper_start', 10);
add_action('woocommerce_after_main_content', 'boldthemes_woo_wrapper_end', 10);



/* Loop filters and actions */
add_filter( 'body_class', 'boldthemes_woocommerce_body_class_columns' );
//add_filter( 'loop_shop_per_page', 'boldthemes_loop_shop_per_page', 20 );
//add_filter( 'loop_shop_columns', 'boldthemes_loop_shop_columns', 4 );
add_action( 'woocommerce_after_shop_loop_item', 'boldthemes_after_shop_loop_item' );

/* Product filters and actions  */
add_filter( 'woocommerce_product_thumbnails_columns', 'boldthemes_thumb_cols' );
add_filter( 'woocommerce_output_related_products_args', 'boldthemes_related_products_args' );
add_filter( 'woocommerce_add_to_cart_fragments', 'boldthemes_add_to_cart_fragment' );

/* Remove title, breadcrumbs, categories, rating and tags */

add_filter( 'woocommerce_show_page_title', '__return_false' );
remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );	
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10);
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );

/* Custom title, after summary content*/

add_action( 'woocommerce_single_product_summary', 'boldthemes_woo_shop_single_title', 5 );
add_action( 'woocommerce_after_add_to_cart_form', 'boldthemes_woo_after_add_to_cart_form', 10, 2 ); 

/* Fix content wrappers */

function boldthemes_woo_wrapper_end() {
  echo '</main><!-- main.site-main -->';
}

/* Custom title */

function boldthemes_woo_shop_single_title() {
	if ( boldthemes_get_option( 'default_headline_height' ) == 'none' ):
		$prefix = boldthemes_get_prefix();
		echo( '<header class="entry-header single">');
			printf( '<div class="entry-meta entry-super-meta">%1$s</div><!-- .entry-super-meta -->', boldthemes_the_post_meta( $prefix, boldthemes_get_option( 'shop_single_show_superheadline' ) ) );
			printf( '<%1$s class="page-title">%2$s</%1$s>', esc_attr( boldthemes_get_option( 'default_headline_h_tag' ) ), the_title( '', '', false ) );
			printf( '<div class="entry-meta entry-sub-meta">%1$s</div><!-- .entry-sub-meta -->', boldthemes_the_post_meta( $prefix, boldthemes_get_option( 'shop_single_show_subheadline' ) ) );
		echo( '</header><!-- .entry-header -->');		
	endif;
}

/* Custom title */

function boldthemes_woo_after_add_to_cart_form() {
	$prefix = boldthemes_get_prefix();
	printf( '<footer class="entry-footer"><div class="entry-meta entry-footer-meta">%1$s</div><!-- .entry-footer-meta --></footer><!-- .entry-footer -->', boldthemes_the_post_meta( $prefix, boldthemes_get_option( 'shop_single_show_bottom' ) ) );  
}

function boldthemes_woo_wrapper_start() {
  echo '<main id="primary" class="site-main"><!-- main.site-main -->';
}

/* Update counter in shoppong cart */

function boldthemes_add_to_cart_fragment( $fragments ) {
	global $woocommerce;
	$fragments['.widget_shopping_cart .widgettitle'] = '<h2 class="widgettitle">Cart <span class="widgetcounter">' . $woocommerce->cart->cart_contents_count . '</span></h2>';
 	return $fragments;
 }

/**
 * WooCommerce support
 */
if ( ! function_exists( 'boldthemes_woocommerce_support' ) ) {
	function boldthemes_woocommerce_support() {
		add_theme_support( 'woocommerce' );
		add_theme_support( 'wc-product-gallery-lightbox' );
		add_theme_support( 'wc-product-gallery-zoom' );	
	}
}

/**
 * WooCommerce related products
 */

if ( ! function_exists( 'boldthemes_related_products_args' ) ) {
	function boldthemes_related_products_args( $args ) {
		$columns	= boldthemes_get_option( 'shop_single_related_products_columns' );
		$rows		= boldthemes_get_option( 'shop_single_related_products_rows' );;
		
		$args['posts_per_page'] = $columns*$rows; 
		$args['columns']		= $columns; 
		return $args;
	}
}

/* Remove product tabs */
if ( ! function_exists( 'boldthemes_woo_remove_product_tabs' ) ) {
	function boldthemes_woo_remove_product_tabs( $tabs ) {
		unset( $tabs['reviews'] ); // Remove the reviews tab
		return $tabs;
	}
}

/* Woocommerce hooks */



/* Product columns body class */
if ( ! function_exists( 'boldthemes_woocommerce_body_class_columns' ) ) {
	function boldthemes_woocommerce_body_class_columns( $classes ) {
		
		if ( 
			in_array( 
				'woocommerce/woocommerce.php',
				apply_filters( 'active_plugins', get_option( 'active_plugins' ) )
			)
		) {
			if ( is_shop() || is_product_taxonomy() || is_product_category() || is_product_tag() ) {
				/*$cols   = $boldthemes_get_option( 'shop_list_loop_shop_columns' );
				$classes[] = 'product-columns-' . $cols;*/
			}
		}
		return $classes;
	}
}
			
if ( ! function_exists( 'boldthemes_thumb_cols' ) ) {
	function boldthemes_thumb_cols() {
		return 3; // .last class applied to every 4th thumbnail
	}
}

/*if ( ! function_exists( 'boldthemes_loop_shop_columns' ) ) {
	function boldthemes_loop_shop_columns() {
		$cols	= boldthemes_get_option( 'shop_list_loop_shop_columns' );
		return $cols; 
	}
}*/

if ( ! function_exists( 'boldthemes_after_shop_loop_item' ) ) {
	function boldthemes_after_shop_loop_item() {
		$prefix = boldthemes_get_prefix();
		//echo '<div class="woocommerce-loop-product-after">'; 
			boldthemes_entry_footer( $prefix, boldthemes_get_option( $prefix . '_list_show_bottom' ) );
		//echo '</div>'; 
	}
}

/*if ( ! function_exists( 'boldthemes_loop_shop_per_page' ) ) {
	function boldthemes_loop_shop_per_page( $per_page ) {
		$cols		= boldthemes_get_option( 'shop_list_loop_shop_columns' );
		$rows		= boldthemes_get_option( 'shop_list_loop_shop_rows' );	
		$per_page	= $cols * $rows; 		
		return 	$per_page;
	}
}*/

if ( ! function_exists( 'boldthemes_thumb_size' ) ) {
	function boldthemes_thumb_size() {
		return 'thumbnail'; // .last class applied to every 4th thumbnail
	}
}