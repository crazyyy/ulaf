<?php
/**
 * Cart Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.9.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' ); ?>

<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
	<?php do_action( 'woocommerce_before_cart_table' ); ?>

	<div class="sp-scrollable-table-wrapper">
		<table class="shop_table shop_table_responsive cart heading-font woocommerce-cart-form__contents" cellspacing="0">
			<thead>
			<tr>
				<?php if ( splash_is_layout( 'sccr' ) ) : ?>
					<th class="product-remove">&nbsp;</th>
				<?php endif; ?>
				<th class="product-thumbnail">
				<?php
				if ( ! splash_is_layout( 'sccr' ) ) {
					esc_html_e( 'Product', 'splash' );}
				?>
				</th>
				<th class="product-name"><?php ( ! splash_is_layout( 'sccr' ) ) ? esc_html_e( 'Name', 'splash' ) : esc_html_e( 'Product', 'splash' ); ?></th>
				<th class="product-price"><?php esc_html_e( 'Price', 'splash' ); ?></th>
				<th class="product-quantity"><?php esc_html_e( 'Quantity', 'splash' ); ?></th>
				<th class="product-subtotal"><?php esc_html_e( 'Total', 'splash' ); ?></th>
				<?php if ( ! splash_is_layout( 'sccr' ) ) : ?>
					<th class="product-remove">&nbsp;</th>
				<?php endif; ?>
			</tr>
			</thead>
			<tbody>
			<?php do_action( 'woocommerce_before_cart_contents' ); ?>

			<?php
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
				$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
				/**
				 * Filter the product name.
				 *
				 * @since 2.1.0
				 * @param string $product_name Name of the product in the cart.
				 * @param array $cart_item The product in the cart.
				 * @param string $cart_item_key Key for the product in the cart.
				 */
				if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
					$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
					?>
					<tr class="woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

						<?php if ( splash_is_layout( 'sccr' ) ) : ?>
							<td class="product-remove">
								<?php
								echo wp_kses_post(
									apply_filters(
										'woocommerce_cart_item_remove_link',
										sprintf(
											'<a href="%s" class="remove" title="%s" data-product_id="%s" data-product_sku="%s"><i class="fa fa-close"></i></a>',
											esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
											__( 'Remove this item', 'splash' ),
											esc_attr( $product_id ),
											esc_attr( $_product->get_sku() )
										),
										$cart_item_key
									)
								);
								?>
							</td>
						<?php endif; ?>

						<td class="product-thumbnail">
							<?php
							$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

							if ( ! $_product->is_visible() ) {
								echo wp_kses_post( $thumbnail );
							} else {
								echo wp_kses_post( sprintf( '<a href="%s">%s</a>', esc_url( $_product->get_permalink( $cart_item ) ), $thumbnail ) );
							}
							?>
						</td>

						<td class="product-name" data-title="<?php esc_html_e( 'Product', 'splash' ); ?>">
							<?php
							if ( ! $_product->is_visible() ) {
								echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_title(), $cart_item, $cart_item_key ) . '&nbsp;' );
							} else {
								echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $_product->get_permalink( $cart_item ) ), $_product->get_title() ), $cart_item, $cart_item_key ) );
							}

							// Meta data
							echo wp_kses_post( wc_get_formatted_cart_item_data( $cart_item ) );

							// Backorder notification
							if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
								echo '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'splash' ) . '</p>';
							}
							?>
						</td>

						<td class="product-price" data-title="<?php esc_html_e( 'Price', 'splash' ); ?>">
							<?php
							echo wp_kses_post( apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ) );
							?>
						</td>

						<td class="product-quantity" data-title="<?php esc_html_e( 'Quantity', 'splash' ); ?>">
							<?php
							if ( $_product->is_sold_individually() ) {
								$product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
							} else {
								$product_quantity = woocommerce_quantity_input(
									array(
										'input_name'  => "cart[{$cart_item_key}][qty]",
										'input_value' => $cart_item['quantity'],
										'max_value'   => $_product->get_max_purchase_quantity(),
										'min_value'   => '0',
									),
									$_product,
									false
								);
							}

							$allowed_html = array(
								'div'   => array(
									'class' => array(),
									'id'    => array(),
								),
								'label' => array(
									'class' => array(),
									'id'    => array(),
									'for'   => array(),
								),
								'input' => array(
									'type'         => array(),
									'name'         => array(),
									'value'        => array(),
									'class'        => array(),
									'id'           => array(),
									'min'          => array(),
									'max'          => array(),
									'size'         => array(),
									'placeholder'  => array(),
									'aria-label'   => array(),
									'required'     => array(),
									'readonly'     => array(),
									'disabled'     => array(),
									'inputmode'    => array(),
									'autocomplete' => array(),
								),
							);

							echo wp_kses( apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ), $allowed_html );
							?>
						</td>

						<td class="product-subtotal" data-title="<?php esc_html_e( 'Total', 'splash' ); ?>">
							<?php
							echo wp_kses_post( apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ) );
							?>
						</td>

						<?php if ( ! splash_is_layout( 'sccr' ) ) : ?>
							<td class="product-remove">
								<?php
								echo wp_kses_post(
									apply_filters(
										'woocommerce_cart_item_remove_link',
										sprintf(
											'<a href="%s" class="remove" title="%s" data-product_id="%s" data-product_sku="%s"><i class="fa fa-close"></i></a>',
											esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
											esc_html__( 'Remove this item', 'splash' ),
											esc_attr( $product_id ),
											esc_attr( $_product->get_sku() )
										),
										$cart_item_key
									)
								);
								?>
							</td>
						<?php endif; ?>
					</tr>
					<?php
				}
			}

			do_action( 'woocommerce_cart_contents' );
			?>
			<tr>
				<td colspan="6" class="actions">

					<?php if ( wc_coupons_enabled() ) { ?>
						<div class="coupon">

							<label for="coupon_code">
								<?php esc_html_e( 'Coupon', 'splash' ); ?>:</label>
							<input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'splash' ); ?>"/>
							<?php if ( ! splash_is_layout( 'bb' ) ) : ?>
							<span class="button-input-wrap 
								<?php
								if ( splash_is_layout( 'sccr' ) ) {
									echo 'button-gray btn-only-border';}
								?>
							">
							<?php endif; ?>
								<input type="submit" name="apply_coupon" value="<?php esc_attr_e( 'Apply', 'splash' ); ?>"/>
							<?php if ( ! splash_is_layout( 'bb' ) ) : ?>
								</span>
						<?php endif; ?>

							<?php do_action( 'woocommerce_cart_coupon' ); ?>
						</div>
					<?php } ?>

					<div class="stm-actions-right">
						<?php if ( ! splash_is_layout( 'bb' ) && ! splash_is_layout( 'volleyball' ) && ! splash_is_layout( 'rugby' ) ) : ?>
						<span class="button btn-md">
					<?php endif; ?>
						<input type="submit" class="button" name="update_cart" value="<?php esc_attr_e( 'Update Cart', 'splash' ); ?>"/>
					<?php if ( ! splash_is_layout( 'bb' ) && ! splash_is_layout( 'volleyball' ) && ! splash_is_layout( 'rugby' ) ) : ?>
						</span>
					<?php endif; ?>
						<?php if ( ! splash_is_layout( 'sccr' ) ) : ?>
							<div class="wc-proceed-to-checkout">
								<?php do_action( 'woocommerce_proceed_to_checkout' ); ?>
							</div>
						<?php endif; ?>
					</div>

					<?php do_action( 'woocommerce_cart_actions' ); ?>

					<?php wp_nonce_field( 'woocommerce-cart' ); ?>
				</td>
			</tr>

			<?php do_action( 'woocommerce_after_cart_contents' ); ?>
			</tbody>
		</table>
	</div>
	<?php do_action( 'woocommerce_after_cart_table' ); ?>
</form>

<?php do_action( 'woocommerce_before_cart_collaterals' ); ?>

<div class="cart-collaterals">
	<?php
	/**
	 * Cart collaterals hook.
	 *
	 * @hooked woocommerce_cross_sell_display
	 * @hooked woocommerce_cart_totals - 10
	 */
	do_action( 'woocommerce_cart_collaterals' );
	?>
</div>

<?php do_action( 'woocommerce_after_cart' ); ?>
