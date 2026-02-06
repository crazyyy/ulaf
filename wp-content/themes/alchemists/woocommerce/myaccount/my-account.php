<?php
/**
 * My Account page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/my-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.5.0
 */

defined( 'ABSPATH' ) || exit;

$alchemists_data = get_option( 'alchemists_data' );
$nav_type = isset( $alchemists_data['alchemists__shop-account-nav-type'] ) ? $alchemists_data['alchemists__shop-account-nav-type'] : 'vertical';
?>

<?php if ( 'horizontal' == $nav_type ) : ?>

	<?php

	/**
	 * My Account navigation.
	 *
	 * @since 2.6.0
	 */
	do_action( 'woocommerce_account_navigation' ); ?>

	<div class="card card--lg alc-account-content alc-account-content--hor">
		<header class="card__header">
			<h4><?php the_title(); ?></h4>
		</header>
		<div class="card__content">
			<?php
				/**
				 * My Account content.
				 *
				 * @since 2.6.0
				 */
				do_action( 'woocommerce_account_content' );
			?>
		</div>
	</div>

<?php else : ?>

	<div class="row">
		<?php

		/**
		 * My Account navigation.
		 *
		 * @since 2.6.0
		 */
		do_action( 'woocommerce_account_navigation' ); ?>

		<div class="col-lg-8 alc-account-content alc-account-content--default">
			<div class="card card--lg">
				<header class="card__header">
					<h4><?php the_title(); ?></h4>
				</header>
				<div class="card__content">
					<?php
						/**
						 * My Account content.
						 *
						 * @since 2.6.0
						 */
						do_action( 'woocommerce_account_content' );
					?>
				</div>
			</div>
		</div>
	</div><!-- .row -->
<?php endif; ?>
