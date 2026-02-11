<?php
/**
 * WooCommerce Functions
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.7.0
 */

$alchemists_data = get_option( 'alchemists_data' );

if ( get_option( 'woo_first_activation' ) == false ){
	add_option( 'woo_first_activation', 'hotcake', '', 'no' );
}

if ( alchemists_wc_exists() == true && get_option( 'woo_first_activation' ) !== 'activated' ) add_action( 'init', 'alchemists_woocommerce_defaults', 1 );


// remove default styles
add_filter( 'woocommerce_enqueue_styles', '__return_false' );

// remove title
add_filter( 'woocommerce_show_page_title', '__return_false' );

add_action( 'wp_enqueue_scripts', 'alchemists_custom_style_select2', 100 );
function alchemists_custom_style_select2() {
	if ( class_exists( 'woocommerce' ) ) {
		wp_dequeue_style( 'select2' );
		wp_deregister_style( 'select2' );
	}
}

// Remove default styles for Color Filters plugin
if ( ! function_exists( 'alchemists_dequeue_color_filters_styles' ) ) {
	function alchemists_dequeue_color_filters_styles() {
		wp_dequeue_style( 'color-filters' );
	}
	add_action( 'wp_enqueue_scripts', 'alchemists_dequeue_color_filters_styles', 9999 );
}

// Add mini cart dropdown to header
add_action( 'wp_enqueue_scripts', 'alchemists_add_mini_cart' );
function alchemists_add_mini_cart() {
	wp_enqueue_script( 'wc-cart-fragments' );
}

// Remove actions
remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' , 10 );


/**
 * Define image sizes
 */
function alchemists_woocommerce_defaults() {

	if ( alchemists_sp_preset( 'esports' ) ) {
		$catalog_height = '380';
	} else {
		$catalog_height = '280';
	}

	$catalog = array(
		'width'   => '280', // px
		'height'  => $catalog_height, // px
		'crop'    => 1      // true
	);

	$single = array(
		'width'   => '424', // px
		'height'  => '544', // px
		'crop'    => 0     // true
	);

	$thumbnail = array(
		'width'   => '70',  // px
		'height'  => '70', // px
		'crop'    => 1     // false
	);

	// Image sizes
	update_option( 'shop_catalog_image_size', $catalog ); 		// Product category thumbs
	update_option( 'shop_single_image_size', $single ); 		  // Single product image
	update_option( 'shop_thumbnail_image_size', $thumbnail ); // Image gallery thumbs
	update_option( 'woocommerce_frontend_css', false);
	update_option( 'woocommerce_enable_lightbox', false);
	update_option( 'woocommerce_single_image_crop', 'no');

	update_option( 'woo_first_activation', 'activated' );

}



/**
 * Page: Checkout
 */
add_filter( 'woocommerce_order_button_html', 'alchemists_placer_order_btn_class');
function alchemists_placer_order_btn_class( $btn_class ) {

	$order_button_text = apply_filters( 'woocommerce_order_button_text', esc_html__( 'Place order', 'alchemists' ) );

	$btn_classes = array(
		'button',
		'btn-lg',
		'btn-block'
	);

	if ( alchemists_sp_preset('football') ) {
		$btn_classes[] = 'btn-primary-inverse';
	} else {
		$btn_classes[] = 'btn-primary';
	}

	$btn_class = '<input type="submit" class="' . esc_attr( implode( ' ', $btn_classes ) ) . '" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '" />';

	return $btn_class;
}


/**
 * Widget: Cart
 */

// Output the view cart button.
remove_action( 'woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_button_view_cart', 10 );
if ( ! function_exists( 'alchemists_widget_shopping_cart_button_view_cart' ) ) {
	function alchemists_widget_shopping_cart_button_view_cart() {
		echo '<a href="' . esc_url( wc_get_cart_url() ) . '" class="btn btn-default wc-forward">' . esc_html__( 'View cart', 'alchemists' ) . '</a>';
	}
}
add_action( 'woocommerce_widget_shopping_cart_buttons', 'alchemists_widget_shopping_cart_button_view_cart', 10 );

// Output the proceed to checkout button.
remove_action( 'woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_proceed_to_checkout', 20 );
if ( ! function_exists( 'alchemists_widget_shopping_cart_proceed_to_checkout' ) ) {
	function alchemists_widget_shopping_cart_proceed_to_checkout() {
		echo '<a href="' . esc_url( wc_get_checkout_url() ) . '" class="btn btn-primary-inverse checkout wc-forward">' . esc_html__( 'Checkout', 'alchemists' ) . '</a>';
	}
}
add_action( 'woocommerce_widget_shopping_cart_buttons', 'alchemists_widget_shopping_cart_proceed_to_checkout', 20 );



/**
 * Header Cart
 */

// Ajaxify cart in the Header
add_filter( 'woocommerce_add_to_cart_fragments', 'alchemists_header_add_to_cart_fragment' );
if ( ! function_exists( 'alchemists_header_add_to_cart_fragment' ) ) {
	function alchemists_header_add_to_cart_fragment( $fragments ) {

		$alchemists_data = get_option( 'alchemists_data' );
		$icon_custom_cart = isset( $alchemists_data['alchemists__header-shopping-cart-icon-custom'] ) ? $alchemists_data['alchemists__header-shopping-cart-icon-custom'] : '';

		ob_start();

		$product_count = sprintf('%d', WC()->cart->cart_contents_count, WC()->cart->cart_contents_count );
		?>
		<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="info-block__link-wrapper" title="<?php esc_attr_e( 'View your shopping cart', 'alchemists' ); ?>">
			<?php if ( ! empty( $icon_custom_cart ) ) : ?>
				<span class="df-icon-custom"><?php echo wp_kses_post( $icon_custom_cart ); ?></span>
			<?php else : ?>
				<?php if ( alchemists_sp_preset( 'esports' ) ) : ?>
					<svg role="img" class="df-icon df-icon--shopping-cart">
						<use xlink:href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/esports/icons-esports.svg#cart" />
					</svg>
				<?php else : ?>
					<div class="df-icon-stack df-icon-stack--bag">
						<svg role="img" class="df-icon df-icon--bag">
							<use xlink:href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/icons-basket.svg#bag"/>
						</svg>
						<svg role="img" class="df-icon df-icon--bag-handle">
							<use xlink:href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/icons-basket.svg#bag-handle"/>
						</svg>
					</div>
				<?php endif; ?>
			<?php endif; ?>
			<h6 class="info-block__heading"><?php esc_html_e( 'Your Bag', 'alchemists' ); ?> (<?php printf( _n( '%s item', '%s items', $product_count, 'alchemists' ), $product_count ); ?>)</h6>
			<span class="info-block__cart-sum"><?php echo WC()->cart->get_cart_total(); ?></span>
		</a>
		<?php

		$fragments['a.info-block__link-wrapper'] = ob_get_clean();

		return $fragments;
	}
}



/*
 * Change the entry title of the endpoints that appear in My Account Page - WooCommerce 2.6
 * Using the_title filter
 */
function alchemists_woo_endpoint_title( $title, $id ) {
	if ( is_wc_endpoint_url( 'downloads' ) && in_the_loop() ) { // add your endpoint urls
		$title = esc_html__( 'Downloads', 'alchemists' );
	}
	elseif ( is_wc_endpoint_url( 'orders' ) && in_the_loop() ) {
		$title = esc_html__( 'Orders', 'alchemists' );
	}
	elseif ( is_wc_endpoint_url( 'edit-account' ) && in_the_loop() ) {
		$title = esc_html__( 'Account Details', 'alchemists' );
	}
	elseif ( is_wc_endpoint_url( 'edit-address' ) && in_the_loop() ) {
		$title = esc_html__( 'Addresses', 'alchemists' );
	}
	elseif ( is_wc_endpoint_url( 'payment-methods' ) && in_the_loop() ) {
		$title = esc_html__( 'Payment Methods', 'alchemists' );
	}
	elseif ( is_wc_endpoint_url( 'edit-account' ) && in_the_loop() ) {
		$title = esc_html__( 'Account Details', 'alchemists' );
	}

	return $title;
}
add_filter( 'the_title', 'alchemists_woo_endpoint_title', 10, 2 );



/**
 * Products
 */

// Ordering Heading
function alchemists_before_shop_loop() {
	$output = '<div class="card card--clean">';
	$output .= '<header class="card__header card__header--shop-filter">';
	$output .= '<div class="shop-filter">';
	echo !empty( $output ) ? $output : '';
}
add_action ( 'woocommerce_before_shop_loop', 'alchemists_before_shop_loop', 10 );


// Filter params - before
function alchemists_before_shop_loop_filter_params_before() {
	$output = '<ul class="shop-filter__params">';
	echo !empty( $output ) ? $output : '';
}
add_action ( 'woocommerce_before_shop_loop', 'alchemists_before_shop_loop_filter_params_before', 25 );

// Filter params - end
function alchemists_before_shop_loop_filter_params_end() {
	$output = '</ul>';
	echo !empty( $output ) ? $output : '';
}
add_action ( 'woocommerce_before_shop_loop', 'alchemists_before_shop_loop_filter_params_end', 31 );


// Filter Ordering - before
function alchemists_before_shop_loop_filter_wrap_ordering_before() {
	$output = '<li class="shop-filter__control">';
	echo !empty( $output ) ? $output : '';
}
add_action ( 'woocommerce_before_shop_loop', 'alchemists_before_shop_loop_filter_wrap_ordering_before', 27 );

// Filter Ordering - after
function alchemists_before_shop_loop_filter_wrap_ordering_after() {
	$output = '</li>';
	echo !empty( $output ) ? $output : '';
}
add_action ( 'woocommerce_before_shop_loop', 'alchemists_before_shop_loop_filter_wrap_ordering_after', 30 );

function alchemists_shop_filter_end() {
	$output = '</div>';
	$output .= '</header>';
	$output .= '<div class="card__content">';
	echo !empty( $output ) ? $output : '';
}
add_action ( 'woocommerce_before_shop_loop', 'alchemists_shop_filter_end', 100 );

function alchemists_after_shop_loop() {
	$output = '</div>';
	$output .= '</div>';
	echo !empty( $output ) ? $output : '';
}
add_action ( 'woocommerce_after_shop_loop', 'alchemists_after_shop_loop', 100 );



/**
 * Get current users preference
 * @return int
 */
function alchemists_get_products_per_page(){

	global $woocommerce;
	$per_page = get_option( 'wc_glt_count', '6,12,24' );

	$per_page_array = array();
	$per_page_array = explode( ',', $per_page );

	$default = $per_page_array[0];
	$count = $default;
	$options = alchemists_get_products_per_page_options();

	// capture form data and store in session
	if(isset($_POST['alchemists-woocommerce-products-per-page'])){

		// set products per page from dropdown
		$products_max = intval($_POST['alchemists-woocommerce-products-per-page']);
		if($products_max != 0 && $products_max >= -1){

			if(is_user_logged_in()){

				$user_id = get_current_user_id();
				$limit = get_user_meta( $user_id, '_product_per_page', true );

				if(!$limit){
					add_user_meta( $user_id, '_product_per_page', $products_max);
				}else{
					update_user_meta( $user_id, '_product_per_page', $products_max, $limit);
				}
			}

			$woocommerce->session->jc_product_per_page = $products_max;
			return $products_max;
		}
	}

	// load product limit from user meta
	if(is_user_logged_in() && !isset($woocommerce->session->jc_product_per_page)){

		$user_id = get_current_user_id();
		$limit = get_user_meta( $user_id, '_product_per_page', true );

		if ( array_key_exists( $limit, $options ) ) {
			// Make sure WooCommerce session is initialized
			if (null !== WC()->session) {
				WC()->session->set('jc_product_per_page', $limit);
				return $limit;
			}
		}
	}

	// load product limit from session
	if(isset($woocommerce->session->jc_product_per_page)){

		// set products per page from woo session
		$products_max = intval($woocommerce->session->jc_product_per_page);
		if($products_max != 0 && $products_max >= -1){
			return $products_max;
		}
	}

	return $count;
}
add_filter('loop_shop_per_page','alchemists_get_products_per_page');

/**
 * Fetch list of avaliable options
 * @return array
 */
function alchemists_get_products_per_page_options(){
	$per_page = get_option( 'wc_glt_count', '6,12,24' );
	$per_page_array = array();
	$per_page_array = explode( ',', $per_page );

	foreach ( $per_page_array as $per_page_single ) {
		$per_page_array_new[ $per_page_single ] = sprintf( esc_html__( 'Show %s per page', 'alchemists' ), $per_page_single );
	}

	$options = apply_filters( 'alchemists_products_per_page', $per_page_array_new);

	return $options;
}

/**
 * Display dropdown form to change amount of products displayed
 * @return void
 */
function alchemists_woocommerce_products_per_page(){

	$options = alchemists_get_products_per_page_options();

	$current_value = alchemists_get_products_per_page();
	?>
	<li class="shop-filter__control">
		<form action="#" method="POST" class="woocommerce-products-per-page">
			<select name="alchemists-woocommerce-products-per-page" onchange="this.form.submit()">
			<?php foreach($options as $value => $name): ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected($value, $current_value); ?>><?php echo esc_html( $name ); ?></option>
			<?php endforeach; ?>
			</select>
		</form>
	</li>
	<?php
}

add_action('woocommerce_before_shop_loop', 'alchemists_woocommerce_products_per_page', 26);






/**
 * Shop: Product
 */

remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );

add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_link_open', 1 );
add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_link_close', 99 );

// Add Product Categories
if ( ! function_exists( 'alchemists_template_loop_add_categories' ) ) {
	function alchemists_template_loop_add_categories( $category ) {
		global $product;
		echo wc_get_product_category_list( $product->get_id(), ', ', '<span class="product__category">', '</span>' );
	}
}
add_action('woocommerce_shop_loop_item_title', 'alchemists_template_loop_add_categories', 8);

// Show the product title in the product loop.
remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
if ( ! function_exists( 'alchemists_template_loop_product_title' ) ) {
	function alchemists_template_loop_product_title() {
		echo '<h2 class="product__title">';
			echo '<a href="' . get_the_permalink() . '">';
				echo get_the_title();
			echo '</a>';
		echo '</h2>';
	}
}
add_action('woocommerce_shop_loop_item_title', 'alchemists_template_loop_product_title', 10);


// Display the average rating in the loop.
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
if ( ! function_exists( 'alchemists_template_loop_rating' ) ) {
	function alchemists_template_loop_rating() {
		echo '<div class="product__ratings">';
			wc_get_template( 'loop/rating.php' );
		echo '</div>';
	}
}
add_action('woocommerce_shop_loop_item_title', 'alchemists_template_loop_rating', 11);


// Wrap Product Loop - Add to Cart button
if ( ! function_exists( 'alchemists_template_loop_add_to_cart_before' ) ) {
	function alchemists_template_loop_add_to_cart_before() {
		echo '<footer class="product__footer">';
	}
}
add_action('woocommerce_after_shop_loop_item', 'alchemists_template_loop_add_to_cart_before', 9);

if ( ! function_exists( 'alchemists_template_loop_add_to_cart_after' ) ) {
	function alchemists_template_loop_add_to_cart_after() {
		echo '</footer>';
	}
}
add_action('woocommerce_after_shop_loop_item', 'alchemists_template_loop_add_to_cart_after', 20);


// Get the add to cart template for the loop.
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
if ( ! function_exists( 'alchemists_template_loop_add_to_cart' ) ) {
	function alchemists_template_loop_add_to_cart( $args = array() ) {
		global $product;

		if ( $product ) {
			$defaults = array(
				'quantity' => 1,
				'class'    => implode( ' ', array_filter( array(
					'btn',
					'product_type_' . $product->get_type(),
					$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
					$product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : '',
					alchemists_sp_preset( 'basketball' ) ? 'btn-primary' : 'btn-primary-inverse',
				) ) ),
				'attributes' => array(
					'data-product_id'  => $product->get_id(),
					'data-product_sku' => $product->get_sku(),
					'aria-label'       => $product->add_to_cart_description(),
					'rel'              => 'nofollow',
				),
			);

			$args = apply_filters( 'woocommerce_loop_add_to_cart_args', wp_parse_args( $args, $defaults ), $product );

			wc_get_template( 'loop/add-to-cart.php', $args );
		}
	}
}
add_action('woocommerce_after_shop_loop_item', 'alchemists_template_loop_add_to_cart', 10);


// Add Excerpt
add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_single_excerpt', 5);


// Add View Button
if ( ! function_exists( 'alchemists_template_loop_product_add_view_btn' ) ) {
	function alchemists_template_loop_product_add_view_btn() {
		$btn_classes = array(
			'btn',
			'product__view'
		);

		$btn_content = '<i class="icon-eye"></i>';

		if ( alchemists_sp_preset( 'basketball' ) || alchemists_sp_preset( 'soccer' ) ) {
			array_push( $btn_classes, 'btn-default', 'btn-inverse', 'btn-single-icon' );
		} elseif ( alchemists_sp_preset( 'esports' ) ) {
			array_push( $btn_classes, 'btn-primary' );
			$btn_content = esc_html__( 'View Product', 'alchemists' );
		} else {
			array_push( $btn_classes, 'btn-default', 'btn-outline', 'btn-single-icon' );
		}

		echo '<a href="' . get_the_permalink() . '" class="' . implode( ' ', $btn_classes ) . '">' . $btn_content . '</a>';
	}
}
add_action('woocommerce_after_shop_loop_item', 'alchemists_template_loop_product_add_view_btn', 11);


// Add buttons to image
if ( alchemists_sp_preset( 'esports' ) ) {

	// Wrap Product Loop - Add to Cart button
	if ( ! function_exists( 'alchemists_template_add_btn_wrapper_before' ) ) {
		function alchemists_template_add_btn_wrapper_before() {
			echo '<div class="product__overlay"><div class="product__btns">';
		}
	}
	add_action('woocommerce_before_shop_loop_item_title', 'alchemists_template_add_btn_wrapper_before', 100);

	if ( ! function_exists( 'alchemists_template_add_btn_wrapper_after' ) ) {
		function alchemists_template_add_btn_wrapper_after() {
			echo '</div></div>';
		}
	}
	add_action('woocommerce_before_shop_loop_item_title', 'alchemists_template_add_btn_wrapper_after', 110);

	// Add 'Add to cart' button
	add_action('woocommerce_before_shop_loop_item_title', 'alchemists_template_loop_add_to_cart', 101);
	// Add 'View' button
	add_action('woocommerce_before_shop_loop_item_title', 'alchemists_template_loop_product_add_view_btn', 102);
}



/**
 * Add Custom Field to Product Categories
 */

// Product Categories - Create page
if ( ! function_exists( 'alchemists_taxonomy_add_new_meta_field' ) ) {
	function alchemists_taxonomy_add_new_meta_field() { ?>
		<div class="form-field">
			<label for="alc_meta_abbr"><?php esc_html_e( 'Abbreviation', 'alchemists'); ?></label>
			<input type="text" name="alc_meta_abbr" id="alc_meta_abbr">
			<p class="description"><?php esc_html_e( 'Used on the Shop Grid layout behind the Product Image', 'alchemists'); ?></p>
		</div>
		<?php
	}
}

// Product Categories - Edit page
if ( ! function_exists( 'alchemists_taxonomy_edit_meta_field' ) ) {
	function alchemists_taxonomy_edit_meta_field( $term ) {

		//getting term ID
		$term_id = $term->term_id;

		// retrieve the existing value(s) for this meta field.
		$alc_meta_abbr = get_term_meta($term_id, 'alc_meta_abbr', true);
		?>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="alc_meta_abbr"><?php esc_html_e('Abbreviation', 'alchemists'); ?></label></th>
			<td>
				<input type="text" name="alc_meta_abbr" id="alc_meta_abbr" value="<?php echo esc_attr($alc_meta_abbr) ? esc_attr($alc_meta_abbr) : ''; ?>">
				<p class="description"><?php esc_html_e('Used on the Shop Grid layout behind the Product Image', 'alchemists'); ?></p>
			</td>
		</tr>
		<?php
	}
}

add_action('product_cat_add_form_fields', 'alchemists_taxonomy_add_new_meta_field', 10, 1);
add_action('product_cat_edit_form_fields', 'alchemists_taxonomy_edit_meta_field', 10, 1);

// Save extra taxonomy fields callback function.
if ( ! function_exists( 'alchemists_save_taxonomy_custom_meta' ) ) {
	function alchemists_save_taxonomy_custom_meta($term_id) {

		$alc_meta_abbr = filter_input( INPUT_POST, 'alc_meta_abbr' );

		update_term_meta($term_id, 'alc_meta_abbr', $alc_meta_abbr);
	}
}

add_action('edited_product_cat', 'alchemists_save_taxonomy_custom_meta', 10, 1);
add_action('create_product_cat', 'alchemists_save_taxonomy_custom_meta', 10, 1);



/**
 * Product: Sale
 */

if ( alchemists_sp_preset( 'soccer' ) ) {
	remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
	add_action( 'woocommerce_before_shop_loop_item', 'woocommerce_show_product_loop_sale_flash', 10 );
}


/**
 * Single Product: Summary
 */
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );

if ( ! function_exists( 'alchemists_single_product_summary_before' ) ) {
	function alchemists_single_product_summary_before() {
		echo '<header class="product__header">';
			echo '<div class="product__header-inner">';
	}
}
add_action( 'woocommerce_single_product_summary', 'alchemists_single_product_summary_before', 0 );

// Add Product Categories
if ( ! function_exists( 'alchemists_single_product_summary_add_categories' ) ) {
	function alchemists_single_product_summary_add_categories( $category ) {
		global $product;
		echo wc_get_product_category_list( $product->get_id(), ', ', '<span class="product__category">', '</span>' );
	}
}
add_action('woocommerce_single_product_summary', 'alchemists_single_product_summary_add_categories', 4);

if ( ! function_exists( 'alchemists_single_product_summary_inner_after' ) ) {
	function alchemists_single_product_summary_inner_after() {
		echo '</div>';
	}
}
add_action( 'woocommerce_single_product_summary', 'alchemists_single_product_summary_inner_after', 15 );

add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 15 );


if ( ! function_exists( 'alchemists_single_product_summary_after' ) ) {
	function alchemists_single_product_summary_after() {
		echo '</header>';
	}
}
add_action( 'woocommerce_single_product_summary', 'alchemists_single_product_summary_after', 19 );



/**
 * Single Product: Tab - Reviews
 */

if ( ! function_exists( 'alchemists_review_author_avatar_before' ) ) {
	function alchemists_review_author_avatar_before() {
		echo '<figure class="comment__author-avatar">';
	}
}
add_action( 'woocommerce_review_before', 'alchemists_review_author_avatar_before', 9 );

if ( ! function_exists( 'alchemists_review_author_avatar_after' ) ) {
	function alchemists_review_author_avatar_after() {
		echo '</figure>';
	}
}
add_action( 'woocommerce_review_before', 'alchemists_review_author_avatar_after', 11 );

if ( ! function_exists( 'alchemists_review_author_info_before' ) ) {
	function alchemists_review_author_info_before() {
		echo '<div class="comment__author-info">';
	}
}
add_action( 'woocommerce_review_before', 'alchemists_review_author_info_before', 20 );

if ( ! function_exists( 'alchemists_review_author_info_after' ) ) {
	function alchemists_review_author_info_after() {
		echo '</div>';
	}
}
add_action( 'woocommerce_review_before', 'alchemists_review_author_info_after', 90 );

remove_action( 'woocommerce_review_before_comment_meta', 'woocommerce_review_display_rating', 10 );
add_action( 'woocommerce_review_before', 'woocommerce_review_display_rating', 30 );

remove_action( 'woocommerce_review_meta', 'woocommerce_review_display_meta', 10 );
add_action( 'woocommerce_review_before', 'woocommerce_review_display_meta', 20 );

// change sale-flash
add_filter( 'woocommerce_sale_flash', 'alchemists_single_product_flash' );
if ( ! function_exists( 'alchemists_single_product_flash' ) ) {
	function alchemists_single_product_flash() {
		return '<span class="onsale"><span class="onsale__inner">' . esc_html__( 'Sale!', 'alchemists' ) . '</span></span>';
	}
}


/**
 * Single Product: Related
 */

// set the number of related products
add_filter( 'woocommerce_output_related_products_args', 'alchemists_related_products_args' );
if ( ! function_exists( 'alchemists_related_products_args' ) ) {
	function alchemists_related_products_args( $args ) {
		$alchemists_data = get_option( 'alchemists_data' );
		$related_per_page  = isset( $alchemists_data['alchemists__shop-related-per-page'] ) ? $alchemists_data['alchemists__shop-related-per-page'] : '4';
		$args['posts_per_page'] = $related_per_page;
		return $args;
	}
}

// set the layout of related products
if ( ! function_exists( 'alchemists_product_loop_start' ) ) {
	function alchemists_product_loop_start() {

		$alchemists_data = get_option( 'alchemists_data' );
		$related_layout  = isset( $alchemists_data['alchemists__shop-related-columns'] ) ? $alchemists_data['alchemists__shop-related-columns'] : '4';

		$GLOBALS['woocommerce_loop']['loop'] = 0;
		echo '<ul class="products grid products--grid-' . esc_attr( $related_layout ) . '">';
	}
}
