<?php
/**
 * Order Customer Details
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/order/order-details-customer.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.7.0
 *
 * @var WC_Order $order
 */

defined( 'ABSPATH' ) || exit;

$show_shipping = ! wc_ship_to_billing_address_only() && $order->needs_shipping_address();
$cols          = ( splash_is_layout( 'baseball' ) ) ? 'col-md-6' : 'col-md-5';
?>

<section class="woocommerce-customer-details">
<?php if ( ! splash_is_layout( 'bb' ) ) : ?>
</div>
<div class="col-xs-12 col-sm-12 <?php echo esc_attr( $cols ); ?>">
	<header><h3><?php esc_html_e( 'Customer Details', 'splash' ); ?></h3></header>
<?php else : ?>
	<header><h2><?php esc_html_e( 'Customer Details', 'splash' ); ?></h2></header>
<?php endif; ?>

<table class="woocommerce-table woocommerce-table--customer-details shop_table customer_details">
	<?php if ( $order->get_customer_note() ) : ?>
		<tr>
			<th><?php esc_html_e( 'Note:', 'splash' ); ?></th>
			<td><?php echo esc_html( wptexturize( $order->get_customer_note() ) ); ?></td>
		</tr>
	<?php endif; ?>

	<?php if ( $order->get_billing_email() ) : ?>
		<tr>
			<th><?php esc_html_e( 'Email:', 'splash' ); ?></th>
			<td><?php echo esc_html( $order->get_billing_email() ); ?></td>
		</tr>
	<?php endif; ?>

	<?php if ( $order->get_billing_phone() ) : ?>
		<tr>
			<th><?php esc_html_e( 'Telephone:', 'splash' ); ?></th>
			<td><?php echo esc_html( $order->get_billing_phone() ); ?></td>
		</tr>
	<?php endif; ?>

	<?php do_action( 'woocommerce_order_details_after_customer_details', $order ); ?>
</table>

<?php if ( ! splash_is_layout( 'bb' ) ) : ?>
	</div>
</div>
<?php endif; ?>

<?php if ( ! wc_ship_to_billing_address_only() && $order->needs_shipping_address() ) : ?>

<div class="woocommerce-columns woocommerce-columns--2 woocommerce-columns--addresses col2-set addresses">
	<div class="woocommerce-column woocommerce-column--1 woocommerce-column--billing-address col-1">

<?php endif; ?>

<header class="title">
	<h3 class="woocommerce-column__title"><?php esc_html_e( 'Billing Address', 'splash' ); ?></h3>
</header>
<address>
	<?php
	$address = $order->get_formatted_billing_address();

	if ( $address ) {
		echo wp_kses_post( $address );
	} else {
		echo esc_html__( 'N/A', 'splash' );
	}
	?>
</address>
<?php if ( splash_is_layout( 'baseball' ) ) : ?>
	<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="stm-btn-shop button-bg heading-font with_bg">
		<?php echo esc_html__( 'Back to shop', 'splash' ); ?>
	</a>
<?php endif; ?>
<?php if ( ! wc_ship_to_billing_address_only() && $order->needs_shipping_address() ) : ?>

	</div><!-- /.col-1 -->
	<div class="woocommerce-column woocommerce-column--2 woocommerce-column--shipping-address col-2">
		<header class="title">
			<h3 class="woocommerce-column__title"><?php esc_html_e( 'Shipping Address', 'splash' ); ?></h3>
		</header>
		<address>
			<?php
			$address = $order->get_formatted_shipping_address();
			if ( $address ) {
				echo wp_kses_post( splash_sanitize_text_field( $address ) );
			} else {
				echo esc_html__( 'N/A', 'splash' );
			}
			?>
			<?php if ( $order->get_shipping_phone() ) : ?>
				<p class="woocommerce-customer-details--phone"><?php echo esc_html( $order->get_shipping_phone() ); ?></p>
			<?php endif; ?>
		</address>
	</div><!-- /.col-2 -->
</div><!-- /.col2-set -->

<?php endif; ?>
</section>
