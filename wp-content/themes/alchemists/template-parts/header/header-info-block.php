<?php
/**
 * Template part for Header Info Block.
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.0.0
 * @version   4.7.0
 */

$shopping_cart        = isset( $alchemists_data['alchemists__header-shopping-cart'] ) ? esc_html( $alchemists_data['alchemists__header-shopping-cart'] ) : true;
$shopping_cart_on     = isset( $alchemists_data['alchemists__header-shopping-cart-on-action'] ) ? esc_html( $alchemists_data['alchemists__header-shopping-cart-on-action'] ) : 'on_hover';
$icon_custom_primary  = isset( $alchemists_data['alchemists__header-secondary-info-1-icon-custom'] ) ? $alchemists_data['alchemists__header-secondary-info-1-icon-custom'] : '';
$icon_custom_secondary = isset( $alchemists_data['alchemists__header-secondary-info-2-icon-custom'] ) ? $alchemists_data['alchemists__header-secondary-info-2-icon-custom'] : '';
$icon_custom_cart = isset( $alchemists_data['alchemists__header-shopping-cart-icon-custom'] ) ? $alchemists_data['alchemists__header-shopping-cart-icon-custom'] : '';

$email_1 = isset( $alchemists_data['alchemists__header-secondary-info-1-email'] ) ? $alchemists_data['alchemists__header-secondary-info-1-email'] : '';
$email_2 = isset( $alchemists_data['alchemists__header-secondary-info-2-email'] ) ? $alchemists_data['alchemists__header-secondary-info-2-email'] : '';

$email_1_label = isset( $alchemists_data['alchemists__header-secondary-info-1-label'] ) ? $alchemists_data['alchemists__header-secondary-info-1-label'] : '';
$email_2_label = isset( $alchemists_data['alchemists__header-secondary-info-2-label'] ) ? $alchemists_data['alchemists__header-secondary-info-2-label'] : '';

$custom_txt_1_label = isset( $alchemists_data['alchemists__header-secondary-info-1-txt'] ) ? $alchemists_data['alchemists__header-secondary-info-1-txt'] : '';
$custom_txt_2_label = isset( $alchemists_data['alchemists__header-secondary-info-2-txt'] ) ? $alchemists_data['alchemists__header-secondary-info-2-txt'] : '';

// check if Primary Email Address is an email address or link
if ( filter_var( $email_1, FILTER_VALIDATE_EMAIL ) ) {
	$email_1_attr = 'mailto:' . $email_1;
} elseif ( filter_var( $email_1, FILTER_VALIDATE_URL ) ) {
	$email_1_attr = esc_url( $email_1 );
} else {
	$email_1_attr = 'tel:' . $email_1;
}

// check if Secondary Email Address is an email address or link
if ( filter_var( $email_2, FILTER_VALIDATE_EMAIL ) ) {
	$email_2_attr = 'mailto:' . $email_2;
} elseif ( filter_var( $email_2, FILTER_VALIDATE_URL ) ) {
	$email_2_attr = esc_url( $email_2 );
} else {
	$email_2_attr = 'tel:' . $email_2;
}
?>

<ul class="info-block info-block--header">

	<?php // Primary Email
	if ( isset( $alchemists_data['alchemists__header-secondary-info-1'] ) && $alchemists_data['alchemists__header-secondary-info-1'] == 1 ) : ?>
	<li class="info-block__item info-block__item--contact-primary">

		<?php if ( !empty( $icon_custom_primary ) ) : ?>
			<span class="df-icon-custom"><?php echo wp_kses_post( $icon_custom_primary ); ?></span>
		<?php else : ?>
			<?php if ( alchemists_sp_preset('soccer') ) { ?>
				<svg role="img" class="df-icon df-icon--whistle">
					<use xlink:href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/icons-soccer.svg#whistle"/>
				</svg>
			<?php } elseif ( alchemists_sp_preset('football') ) { ?>
				<svg role="img" class="df-icon df-icon--football-helmet">
					<use xlink:href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/football/icons-football.svg#football-helmet"/>
				</svg>
			<?php } else { ?>
				<svg role="img" class="df-icon df-icon--jersey">
					<use xlink:href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/icons-basket.svg#jersey"/>
				</svg>
			<?php } ?>
		<?php endif; ?>

		<h6 class="info-block__heading"><?php echo esc_html( $email_1_label ); ?></h6>
		<a class="info-block__link" href="<?php echo esc_url( $email_1_attr ); ?>">
			<?php
			if ( $custom_txt_1_label ) {
				echo esc_html( $custom_txt_1_label );
			} else {
				echo esc_html( alchemists_remove_protocol( $email_1 ) );
			}
			?>
		</a>
	</li>
	<?php endif; ?>

	<?php // Secondary Email
	if ( isset( $alchemists_data['alchemists__header-secondary-info-2'] ) && $alchemists_data['alchemists__header-secondary-info-2'] == 1 ) : ?>
	<li class="info-block__item info-block__item--contact-secondary">

		<?php if ( !empty( $icon_custom_secondary ) ) : ?>
			<span class="df-icon-custom"><?php echo wp_kses_post( $icon_custom_secondary ); ?></span>
		<?php else : ?>

			<?php if ( alchemists_sp_preset( 'soccer' ) ) { ?>
				<svg role="img" class="df-icon df-icon--soccer-ball">
					<use xlink:href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/icons-soccer.svg#soccer-ball"/>
				</svg>
			<?php } elseif ( alchemists_sp_preset( 'football' ) ) { ?>
				<svg role="img" class="df-icon df-icon--football-ball">
					<use xlink:href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/football/icons-football.svg#football-ball"/>
				</svg>
			<?php } else { ?>
				<svg role="img" class="df-icon df-icon--basketball">
					<use xlink:href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/icons-basket.svg#basketball"/>
				</svg>
			<?php } ?>
		<?php endif; ?>

		<h6 class="info-block__heading">
			<?php echo esc_html( $email_2_label ); ?>
		</h6>
		<a class="info-block__link" href="<?php echo esc_url( $email_2_attr ); ?>">
			<?php
			if ( $custom_txt_2_label ) {
				echo esc_html( $custom_txt_2_label );
			} else {
				echo esc_html( alchemists_remove_protocol( $email_2 ) );
			}
			?>
		</a>
	</li>
	<?php endif; ?>

	<?php
	// Shopping Cart
	if ( alchemists_woo_exists() && $shopping_cart ) :

	$shopping_cart_classes = array(
		'info-block__item',
		'info-block__item--shopping-cart',
		'has-children',
	);

	if ( 'on_click' == $shopping_cart_on ) {
		$shopping_cart_classes[] = 'js-info-block__item--onclick';
	} else {
		$shopping_cart_classes[] = 'js-info-block__item--onhover';
	}

	$product_count = sprintf('%d', WC()->cart->cart_contents_count, WC()->cart->cart_contents_count ); ?>
	<li class="<?php echo esc_attr( implode( ' ', $shopping_cart_classes ) ); ?>">
		<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="info-block__link-wrapper" title="<?php esc_attr_e( 'View your shopping cart', 'alchemists' ); ?>">

			<?php if ( !empty( $icon_custom_cart ) ) : ?>
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

		<div class="header-cart-dropdown">
			<div class="widget_shopping_cart_content"></div>
		</div>

	</li>
	<?php endif; ?>

</ul>
