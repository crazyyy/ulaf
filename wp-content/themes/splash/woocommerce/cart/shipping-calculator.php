<?php
/**
 * Shipping Calculator
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/shipping-calculator.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;

if ( 'no' === get_option( 'woocommerce_enable_shipping_calc' ) || ! WC()->cart->needs_shipping() ) {
	return;
}

wp_enqueue_script( 'wc-country-select' );

do_action( 'woocommerce_before_shipping_calculator' ); ?>

<form class="woocommerce-shipping-calculator" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
	<?php
	if ( splash_is_layout( 'baseball' ) ) :
		?>
		<p
			class="stm-ship-calc-text"><?php echo esc_html__( 'Please use the shipping calculator to see available shipping methods.', 'splash' ); ?></p><?php endif; ?>
	<p 
	<?php
	if ( ! splash_is_af() && ! splash_is_layout( 'baseball' ) ) {
		echo "style='display: none;'";
	}
	?>
	><a href="#" class="shipping-calculator-button"><?php esc_html_e( 'Calculate Shipping', 'splash' ); ?></a></p>

	<section class="shipping-calculator-form">

		<?php if ( apply_filters( 'woocommerce_shipping_calculator_enable_country', true ) ) : ?>
			<p class="form-row form-row-wide" id="calc_shipping_country_field">
				<select name="calc_shipping_country" id="calc_shipping_country" class="country_to_state"
						rel="calc_shipping_state">
					<option value=""><?php esc_html_e( 'Select a country&hellip;', 'splash' ); ?></option>
					<?php
					foreach ( WC()->countries->get_shipping_countries() as $key => $value ) {
						echo '<option value="' . esc_attr( $key ) . '"' . selected( WC()->customer->get_shipping_country(), esc_attr( $key ), false ) . '>' . esc_html( $value ) . '</option>';
					}
					?>
				</select>
			</p>
		<?php endif; ?>

		<?php if ( apply_filters( 'woocommerce_shipping_calculator_enable_state', true ) ) : ?>

			<p class="form-row form-row-wide" id="calc_shipping_state_field">
				<?php
				$current_cc = WC()->customer->get_shipping_country();
				$current_r  = WC()->customer->get_shipping_state();
				$states     = WC()->countries->get_states( $current_cc );

				// Hidden Input
				if ( is_array( $states ) && empty( $states ) ) {

					?>
					<input type="hidden" name="calc_shipping_state" id="calc_shipping_state" placeholder="<?php esc_attr_e( 'State / county', 'splash' ); ?>" data-placeholder="<?php esc_attr_e( 'State / county', 'splash' ); ?>" />
					<?php

					// Dropdown Input
				} elseif ( is_array( $states ) ) {

					?>
					<span>
					<select name="calc_shipping_state" class="state_select" id="calc_shipping_state"
							placeholder="<?php esc_attr_e( 'State / county', 'splash' ); ?>">
							<option value=""><?php esc_html_e( 'Select a state&hellip;', 'splash' ); ?></option>
							<?php
							foreach ( $states as $ckey => $cvalue ) {
								echo '<option value="' . esc_attr( $ckey ) . '" ' . selected( $current_r, $ckey, false ) . '>' . esc_html( $cvalue ) . '</option>';
							}
							?>
						</select>
					</span>
					<?php

					// Standard Input
				} else {

					?>
					<input type="text" class="input-text" value="<?php echo esc_attr( $current_r ); ?>" placeholder="<?php esc_attr_e( 'State / County', 'splash' ); ?>" name="calc_shipping_state" id="calc_shipping_state" />
					<?php

				}
				?>
			</p>

		<?php endif; ?>

		<?php if ( apply_filters( 'woocommerce_shipping_calculator_enable_city', true ) ) : ?>

			<p class="form-row form-row-wide" id="calc_shipping_city_field">
				<input type="text" class="input-text"
					   value="<?php echo esc_attr( WC()->customer->get_shipping_city() ); ?>"
					   placeholder="<?php esc_attr_e( 'City', 'splash' ); ?>" name="calc_shipping_city"
					   id="calc_shipping_city"/>
			</p>

		<?php endif; ?>

		<?php if ( apply_filters( 'woocommerce_shipping_calculator_enable_postcode', true ) ) : ?>

			<p class="form-row form-row-wide" id="calc_shipping_postcode_field">
				<input type="text" class="input-text"
					   value="<?php echo esc_attr( WC()->customer->get_shipping_postcode() ); ?>"
					   placeholder="<?php esc_attr_e( 'Postcode / ZIP', 'splash' ); ?>" name="calc_shipping_postcode"
					   id="calc_shipping_postcode"/>
			</p>

		<?php endif; ?>

		<p>
			<button type="submit" name="calc_shipping" value="1"
					class="<?php echo ( ! splash_is_layout( 'sccr' ) ) ? 'button' : 'button-gray btn-only-border'; ?>"><?php esc_html_e( 'Update Totals', 'splash' ); ?></button>
		</p>
		<?php wp_nonce_field( 'woocommerce-shipping-calculator', 'woocommerce-shipping-calculator-nonce' ); ?>
		<?php wp_nonce_field( 'woocommerce-cart' ); ?>
	</section>
</form>


<?php if ( ! splash_is_af() && ! splash_is_layout( 'baseball' ) ) : ?>
	<style>
		.shipping-calculator-form {
			display: block !important;
		}
	</style>
<?php endif; ?>


<?php do_action( 'woocommerce_after_shipping_calculator' ); ?>
