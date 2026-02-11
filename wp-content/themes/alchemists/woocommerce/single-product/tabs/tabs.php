<?php
/**
 * Single Product tabs
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/tabs/tabs.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filter tabs and allow third parties to add their own.
 *
 * Each tab is an array containing title, callback and priority.
 *
 * @see woocommerce_default_product_tabs()
 */
$product_tabs = apply_filters( 'woocommerce_product_tabs', array() );

if ( ! empty( $product_tabs ) ) : ?>

	<?php if ( alchemists_sp_preset('football') ) : ?>

		<div class="product-tabs row">
			<div class="col-lg-4">
				<div class="card">
					<div class="card__content">
						<nav class="df-account-navigation">
							<ul class="nav" role="tablist">
								<?php
								$i = 0;
								foreach ( $product_tabs as $key => $product_tab ) :
									$product_tab_link_is_active = '';
									if ( $i == 0 ) {
										$product_tab_link_is_active = 'active';
									} ?>
									<li role="presentation" class="nav-item df-account-navigation__link">
										<a href="#tab-<?php echo esc_attr( $key ); ?>" role="tab" aria-controls="tab-<?php echo esc_attr( $key ); ?>" class="nav-link <?php echo esc_attr( $product_tab_link_is_active ); ?>" data-toggle="tab"><small><?php echo esc_html_x( 'Product', 'single product: subtitle', 'alchemists' ); ?></small><?php echo apply_filters( 'woocommerce_product_' . $key . '_tab_title', esc_html( $product_tab['title'] ), $key ); ?></a>
									</li>
									<?php $i++; ?>
								<?php endforeach; ?>
							</ul>
						</nav>
					</div>
				</div>
			</div>
			<div class="col-lg-8">
				<div class="tab-content">
					<?php
					$i = 0;
					foreach ( $product_tabs as $key => $product_tab ) :

						$product_tab_pane_is_active = '';
						if ( $i == 0 ) {
							$product_tab_pane_is_active = 'show active';
						}
						?>

						<div class="woocommerce-Tabs-panel woocommerce-Tabs-panel--<?php echo esc_attr( $key ); ?> tab-pane fade <?php echo esc_attr( $product_tab_pane_is_active ); ?>" id="tab-<?php echo esc_attr( $key ); ?>" role="tabpanel">
							<div class="card">
								<div class="card__content">
								<?php
								if ( isset( $product_tab['callback'] ) ) {
									call_user_func( $product_tab['callback'], $key, $product_tab );
								}
								?>
								</div>
							</div>
						</div>
						<?php $i++; ?>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	<?php else : ?>

		<?php
		$product_tabs_classes = array(
			'product-tabs',
			'card'
		);

		if ( alchemists_sp_preset( 'esports' ) ) {
			$product_tabs_classes[] = 'card--lg';
		} else {
			$product_tabs_classes[] = 'card--xlg';
		}
		?>

		<div class="<?php echo esc_attr( implode( ' ', $product_tabs_classes ) ); ?>">
			<ul class="nav nav-tabs nav-justified nav-product-tabs" role="tablist">
				<?php
				$i = 0;
				foreach ( $product_tabs as $key => $product_tab ) :
					$product_tab_link_is_active = '';
					if ( $i == 0 ) {
						$product_tab_link_is_active = 'active';
					} ?>
					<li class="nav-item">
						<a href="#tab-<?php echo esc_attr( $key ); ?>" class="nav-link <?php echo esc_attr( $product_tab_link_is_active ); ?>" role="tab" data-toggle="tab"><small><?php echo esc_html_x( 'Product', 'single product: subtitle', 'alchemists' ); ?></small><?php echo apply_filters( 'woocommerce_product_' . $key . '_tab_title', esc_html( $product_tab['title'] ), $key ); ?></a>
					</li>
					<?php $i++; ?>
				<?php endforeach; ?>
			</ul>
			<div class="tab-content card__content">
				<?php
				$i = 0;
				foreach ( $product_tabs as $key => $product_tab ) :

					$product_tab_pane_is_active = '';
					if ( $i == 0 ) {
						$product_tab_pane_is_active = 'show active';
					}
					?>

					<div class="woocommerce-Tabs-panel woocommerce-Tabs-panel--<?php echo esc_attr( $key ); ?> tab-pane fade <?php echo esc_attr( $product_tab_pane_is_active ); ?>" id="tab-<?php echo esc_attr( $key ); ?>" role="tabpanel">
						<?php
						if ( isset( $product_tab['callback'] ) ) {
							call_user_func( $product_tab['callback'], $key, $product_tab );
						}
						?>
					</div>
					<?php $i++; ?>
				<?php endforeach; ?>
			</div>

		</div>

	<?php endif; ?>

	<?php do_action( 'woocommerce_product_after_tabs' ); ?>


<?php endif; ?>
