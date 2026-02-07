<?php
/**
 * Cart totals
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart-totals.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see 	    http://docs.woothemes.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.3.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if(splash_is_af() || splash_is_layout("baseball")):
?>
<div class="container cart_totals <?php if ( WC()->customer->has_calculated_shipping() ) echo 'calculated_shipping'; ?>">

	<?php do_action( 'woocommerce_before_cart_totals' ); ?>

	<div class="row">
		
		<div class="col-md-6 col-sm-6">
			<div class="stm-cart-totals">
				<<?php echo (splash_is_layout("baseball")) ? "h3" : "h4"; ?>><?php esc_html_e( 'Cart Totals', 'splash' ); ?></<?php echo (splash_is_layout("baseball")) ? "h3" : "h4"; ?>>

				<table cellspacing="0" class="shop_table shop_table_responsive stm-shop-">

					<tr class="cart-subtotal">
						<th><?php esc_html_e( 'Subtotal', 'splash' ); ?></th>
						<td data-title="<?php esc_html_e( 'Subtotal', 'splash' ); ?>"><?php wc_cart_totals_subtotal_html(); ?></td>
					</tr>

					<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>

						<?php do_action( 'woocommerce_cart_totals_before_shipping' ); ?>

						<tr class="shipping">
							<th><?php esc_html_e( 'Shipping', 'splash' ); ?></th>
							<td><?php wc_cart_totals_shipping_html(); ?></td>
						</tr>

						<?php do_action( 'woocommerce_cart_totals_after_shipping' ); ?>

					<?php elseif ( WC()->cart->needs_shipping() ) : ?>

						<tr class="shipping">
							<th><?php esc_html_e( 'Shipping', 'splash' ); ?></th>
							<td><?php woocommerce_shipping_calculator(); ?></td>
						</tr>

					<?php endif; ?>

					<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
						<tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
							<th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
							<td data-title="<?php wc_cart_totals_coupon_label( $coupon ); ?>"><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
						</tr>
					<?php endforeach; ?>

					<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
						<tr class="fee">
							<th><?php echo esc_html( $fee->name ); ?></th>
							<td data-title="<?php echo esc_html( $fee->name ); ?>"><?php wc_cart_totals_fee_html( $fee ); ?></td>
						</tr>
					<?php endforeach; ?>

					<?php if ( wc_tax_enabled() && 'excl' === WC()->cart->tax_display_cart ) :
						$taxable_address = WC()->customer->get_taxable_address();
						$estimated_text  = WC()->customer->is_customer_outside_base() && ! WC()->customer->has_calculated_shipping()
								? sprintf( ' <small>(' . esc_html__( 'estimated for %s', 'splash' ) . ')</small>', WC()->countries->estimated_for_prefix( $taxable_address[0] ) . WC()->countries->countries[ $taxable_address[0] ] )
								: '';

						if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
							<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
								<tr class="tax-rate tax-rate-<?php echo sanitize_title( $code ); ?>">
									<th><?php echo esc_html( $tax->label ) . $estimated_text; ?></th>
									<td data-title="<?php echo esc_html( $tax->label ); ?>"><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr class="tax-total">
								<th><?php echo esc_html( WC()->countries->tax_or_vat() ) . $estimated_text; ?></th>
								<td data-title="<?php echo esc_html( WC()->countries->tax_or_vat() ); ?>"><?php wc_cart_totals_taxes_total_html(); ?></td>
							</tr>
						<?php endif; ?>
					<?php endif; ?>

					<?php do_action( 'woocommerce_cart_totals_before_order_total' ); ?>

					<tr class="order-total">
						<th><?php esc_html_e( 'Total', 'splash' ); ?></th>
						<td data-title="<?php esc_html_e( 'Total', 'splash' ); ?>"><?php wc_cart_totals_order_total_html(); ?></td>
					</tr>

					<?php do_action( 'woocommerce_cart_totals_after_order_total' ); ?>

				</table>
				<div class="wc-proceed-to-checkout" >
					<?php do_action( 'woocommerce_proceed_to_checkout' ); ?>
				</div>
			</div>

			<?php do_action( 'woocommerce_after_cart_totals' ); ?>
		</div>
		<div class="col-md-6 col-sm-6"></div>
	</div>
</div>
<?php else: ?>
	<div class="container cart_totals <?php if ( WC()->customer->has_calculated_shipping() ) echo 'calculated_shipping'; ?>">
		
		<?php do_action( 'woocommerce_before_cart_totals' ); ?>
		
		<div class="row">
			<div class="col-md-6 col-sm-6">
				<div class="stm-shipping-cart-calc">
					<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
						
						<?php do_action( 'woocommerce_cart_totals_before_shipping' ); ?>
						
						<?php wc_cart_totals_shipping_html(); ?>
						
						<?php do_action( 'woocommerce_cart_totals_after_shipping' ); ?>
					
					<?php elseif ( WC()->cart->needs_shipping() && 'yes' === get_option( 'woocommerce_enable_shipping_calc' ) ) : ?>
						
						<div class="shipping">
							<<?php echo (splash_is_layout("baseball")) ? "h3" : "h4"; ?>><?php esc_html_e( 'Shipping', 'splash' ); ?></<?php echo (splash_is_layout("baseball")) ? "h3" : "h4"; ?>>
							<div data-title="<?php esc_html_e( 'Shipping', 'splash' ); ?>"><?php woocommerce_shipping_calculator(); ?></div>
						</div>
					
					<?php endif; ?>
				</div>
			</div>
			<div class="col-md-6 col-sm-6">
				<div class="stm-cart-totals">
					<<?php echo (splash_is_layout("baseball")) ? "h3" : "h4"; ?>><?php esc_html_e( 'Cart Totals', 'splash' ); ?></<?php echo (splash_is_layout("baseball")) ? "h3" : "h4"; ?>>
					
					<table cellspacing="0" class="shop_table shop_table_responsive stm-shop-">
						
						<tr class="cart-subtotal">
							<th><?php esc_html_e( 'Subtotal', 'splash' ); ?></th>
							<td data-title="<?php esc_html_e( 'Subtotal', 'splash' ); ?>"><?php wc_cart_totals_subtotal_html(); ?></td>
						</tr>
						
						<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
							<?php $stm_shipping = WC()->cart->get_cart_shipping_total(); ?>
							<?php if(!empty($stm_shipping)): ?>
								<tr class="stm-shipping-cost">
									<th><?php esc_html_e('Shipping:', 'splash'); ?></th>
									<td data-title="<?php esc_html_e('Shipping', 'splash'); ?>"><?php echo wp_kses_post($stm_shipping); ?></td>
								</tr>
							<?php endif; ?>
						<?php endif; ?>
						
						<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
							<tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
								<th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
								<td data-title="<?php wc_cart_totals_coupon_label( $coupon ); ?>"><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
							</tr>
						<?php endforeach; ?>
						
						<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
							<tr class="fee">
								<th><?php echo esc_html( $fee->name ); ?></th>
								<td data-title="<?php echo esc_html( $fee->name ); ?>"><?php wc_cart_totals_fee_html( $fee ); ?></td>
							</tr>
						<?php endforeach; ?>
						
						<?php if ( wc_tax_enabled() && 'excl' === WC()->cart->tax_display_cart ) :
							$taxable_address = WC()->customer->get_taxable_address();
							$estimated_text  = WC()->customer->is_customer_outside_base() && ! WC()->customer->has_calculated_shipping()
								? sprintf( ' <small>(' . esc_html__( 'estimated for %s', 'splash' ) . ')</small>', WC()->countries->estimated_for_prefix( $taxable_address[0] ) . WC()->countries->countries[ $taxable_address[0] ] )
								: '';
							
							if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
								<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
									<tr class="tax-rate tax-rate-<?php echo sanitize_title( $code ); ?>">
										<th><?php echo esc_html( $tax->label ) . $estimated_text; ?></th>
										<td data-title="<?php echo esc_html( $tax->label ); ?>"><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
									</tr>
								<?php endforeach; ?>
							<?php else : ?>
								<tr class="tax-total">
									<th><?php echo esc_html( WC()->countries->tax_or_vat() ) . $estimated_text; ?></th>
									<td data-title="<?php echo esc_html( WC()->countries->tax_or_vat() ); ?>"><?php wc_cart_totals_taxes_total_html(); ?></td>
								</tr>
							<?php endif; ?>
						<?php endif; ?>
						
						<?php do_action( 'woocommerce_cart_totals_before_order_total' ); ?>
						
						<tr class="order-total">
							<th><?php esc_html_e( 'Total', 'splash' ); ?></th>
							<td data-title="<?php esc_html_e( 'Total', 'splash' ); ?>"><?php wc_cart_totals_order_total_html(); ?></td>
						</tr>
						
						<?php do_action( 'woocommerce_cart_totals_after_order_total' ); ?>
					
					</table>
					<?php if(splash_is_layout("sccr") || splash_is_layout("hockey")) : ?>
						<div class="wc-proceed-to-checkout">
							<?php do_action( 'woocommerce_proceed_to_checkout' ); ?>
						</div>
					<?php endif; ?>
				</div>
				
				<?php do_action( 'woocommerce_after_cart_totals' ); ?>
			</div>
		</div>
	</div>
<?php endif;?>
