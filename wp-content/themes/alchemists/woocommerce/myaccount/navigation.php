<?php
/**
 * My Account navigation
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/navigation.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$alchemists_data = get_option( 'alchemists_data' );
$nav_type        = isset( $alchemists_data['alchemists__shop-account-nav-type'] ) ? $alchemists_data['alchemists__shop-account-nav-type'] : 'vertical';

do_action( 'woocommerce_before_account_navigation' );

if ( 'horizontal' == $nav_type ) : ?>

<!-- Account Filter -->
<nav class="content-filter content-filter--boxed content-filter--highlight-side content-filter--label-left alc-account-nav alc-account-nav--hor" aria-label="<?php esc_attr_e( 'Account pages', 'alchemists' ); ?>">
	<div class="content-filter__inner">
		<a href="#" class="content-filter__toggle"></a>
		<ul class="content-filter__list">
			<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
			<li class="<?php echo wc_get_account_menu_item_classes( $endpoint ); ?> content-filter__item">
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>" class="content-filter__link" <?php echo wc_is_current_account_menu_item( $endpoint ) ? 'aria-current="page"' : ''; ?>>
					<small><?php esc_html_e( 'Your Account', 'alchemists' ); ?></small><?php echo esc_html( $label ); ?></a>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>
</nav>
<!-- Account Filter / End -->

<?php else : ?>

<!-- Account Navigation -->
<div class="col-lg-4 alc-account-nav alc-account-nav--default">
	<div class="card">
		<header class="card__header">
			<h4><?php esc_html_e( 'Welcome Back!', 'alchemists' ); ?></h4>
		</header>
		<div class="card__content">
			<nav class="df-account-navigation" aria-label="<?php esc_attr_e( 'Account pages', 'alchemists' ); ?>">
				<ul>
					<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
						<li class="<?php echo wc_get_account_menu_item_classes( $endpoint ); ?> df-account-navigation__link">
							<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>" <?php echo wc_is_current_account_menu_item( $endpoint ) ? 'aria-current="page"' : ''; ?>>
								<?php echo esc_html( $label ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</nav>
		</div>
	</div>
</div>
<!-- Account Navigation / End -->

<?php endif; ?>

<?php do_action( 'woocommerce_after_account_navigation' ); ?>
