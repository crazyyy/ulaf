<?php
/*TOP BAR*/
$top_bar_enable = get_theme_mod( 'top_bar_enable', true );
if ( $top_bar_enable ) {
	get_template_part( 'partials/header/top-bar' );
}

/*Header Settings*/
$header_background = get_theme_mod( 'header_background' );
$menu_top_margin   = get_theme_mod( 'menu_top_margin', 0 );

$transparent_header    = 'stm-non-transparent-header';
$transparent_header_on = get_post_meta( get_the_id(), 'transparent_header', true );

if ( ! empty( $transparent_header_on ) ) {
	$transparent_header = 'stm-transparent-header';
}

$header_position      = 'stm-header-static';
$header_position_mode = get_theme_mod( 'header_position', false );

if ( $header_position_mode ) {
	$header_position = 'stm-header-fixed-mode';
}

/*breadcrumbs settings*/
$is_shop             = false;
$is_product          = false;
$is_product_category = false;
$show_breadcrumbs    = true;
$page_hide_bc        = get_post_meta( get_the_ID(), 'page_breadcrumbs', true );

if ( ! get_theme_mod( 'pages_show_breadcrumbs', true ) ) {
	$show_breadcrumbs = false;
}

if ( ! empty( $page_hide_bc ) && 'on' === $page_hide_bc ) {
	$show_breadcrumbs = false;
}

if ( function_exists( 'is_shop' ) && is_shop() ) {
	$is_shop = true;
}

if ( function_exists( 'is_product_category' ) && is_product_category() ) {
	$is_product_category = true;
}

if ( function_exists( 'is_product' ) && is_product() ) {
	$is_product = true;
}

if ( ! class_exists( 'WooCommerce' ) ) {
	$is_product_category = false;
	$is_product          = false;
}

if ( function_exists( 'WC' ) ) {
	$woocommerce_shop_page_id = wc_get_cart_url();
	$top_bar_enable_cart      = get_theme_mod( 'top_bar_enable_cart', true );
}

/*breadcrumbs settings*/
?>

<div class="stm-header <?php echo esc_attr( $transparent_header ); ?> <?php echo esc_attr( $header_position ); ?> stm-header-four">

	<div class="stm-header-inner">
		<?php if ( ! empty( $header_background ) ) : ?>
			<div class="stm-header-background" style="background-image: url('<?php echo esc_url( $header_background ); ?>')"></div>
		<?php endif; ?>
		<div class="container stm-header-container">
			<div class="stm-main-menu">
				<div class="stm-main-menu-unit
				<?php
				if ( isset( $search_enabled ) ) {
					echo esc_attr( $search_enabled );
				}
				?>
				" style="margin-top: <?php echo intval( $menu_top_margin ); ?>px;">
					<ul class="header-menu stm-list-duty heading-font clearfix <?php echo ( wp_is_mobile() ) ? 'mobile-menu-bsbl' : ''; ?>">
						<?php
						wp_nav_menu(
							array(
								'menu'           => 'primary',
								'theme_location' => 'primary',
								'depth'          => 5,
								'container'      => false,
								'menu_class'     => 'header-menu clearfix',
								'items_wrap'     => '%3$s',
								'link_before'    => '<span>',
								'link_after'     => '</span>',
								'fallback_cb'    => false,
								'stm_megamenu'   => true,
							)
						);
						?>
					</ul>

					<?php if ( ! empty( $woocommerce_shop_page_id ) && $top_bar_enable_cart ) : ?>
						<a class="stm-cart-mobile heading-font" href="<?php echo esc_url( $woocommerce_shop_page_id ); ?>" title="<?php esc_html_e( 'Watch shop items', 'splash' ); ?>">
							<span class="list-label heading-font"><?php esc_html_e( 'Cart', 'splash' ); ?></span>
						</a>
					<?php endif; ?>
				</div>
				<div class="stm-menu-socials">
					<?php
					$stm_socials = splash_socials( 'footer_socials' );
					if ( ! empty( $stm_socials ) ) {
						?>
						<ul class="footer-bottom-socials stm-list-duty">
							<?php
							$i = 0;
							foreach ( $stm_socials as $key => $value ) :
								if ( $i <= 3 ) :
									?>
									<li class="stm-social-<?php echo esc_attr( $key ); ?>">
										<a href="<?php echo esc_attr( $value ); ?>" target="_blank">
											<i class="fa fa-<?php echo esc_attr( $key ); ?>"></i>
										</a>
									</li>
									<?php
									$i ++;
								endif;
								?>
							<?php endforeach; ?>
						</ul>
						<?php
					}
					?>
				</div>
				<div class="stm-menu-footer">
					<?php
					$footer_left_text  = get_theme_mod( 'footer_left_text', esc_html__( '<a href="https://splash.stylemixthemes.com/" target="_blank">Splash</a>', 'splash' ) );
					$footer_right_text = str_replace( 'StylemixThemes', "<a target='_blank' href='https://themeforest.net/item/splash-basketball-sports-wordpress-theme/16751749'><span class='stm-white'>StylemixThemes</span></a>", get_theme_mod( 'footer_right_text', esc_html__( 'Theme by Stylemix Themes.', 'splash' ) ) );

					if ( ! empty( $footer_left_text ) ) {
						echo wp_kses_post( $footer_left_text );
					}
					if ( ! empty( $footer_right_text ) ) {
						echo wp_kses_post( $footer_right_text );
					}
					?>
				</div>
			</div>
		</div>

		<?php
		if ( $show_breadcrumbs && 'header_4' === get_theme_mod( 'header_type', 'header_1' ) ) {
			/*Breadcrumbs*/
			if ( $is_shop || $is_product || $is_product_category ) {
				?>
				<div class="stm-breadcrumbs-unit normal_font">
					<div class="container">
						<?php woocommerce_breadcrumb(); ?>
					</div>
				</div>

				<?php
			} else {
				if ( function_exists( 'bcn_display' ) ) {
					?>
					<div class="stm-breadcrumbs-unit normal_font">
						<div class="container">
							<div class="navxtBreads">
								<?php bcn_display(); ?>
							</div>
						</div>
					</div>
					<?php
				}
			}
		}
		?>
	</div>
	<div class="stm-menu-overlay-fullscreen"></div>
</div>
