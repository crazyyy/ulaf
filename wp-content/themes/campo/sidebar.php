<?php
/**
 * The sidebar containing the main widget area
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 */
 
/* Skip sidebar if not active, but keep it in customizer (to allow preview) */
if ( ( ( ! is_active_sidebar( 'primary_widget_area' ) && ! is_active_sidebar( 'primary_widget_area_shop' ) ) || boldthemes_get_option( 'sidebar_position' ) == 'none' ) && !is_customize_preview() ) {
	return;
}

if ( function_exists( 'is_woocommerce' ) && ( is_woocommerce() || is_checkout() || is_cart() ) && is_active_sidebar( 'primary_widget_area_shop' ) ) { ?>	
	<aside id="secondary" class="widget-area">
		<div class="widget-area-inner">
			<?php dynamic_sidebar( 'primary_widget_area_shop' ); ?>
		</div><!-- div.widget-area-inner -->
	</aside><!-- aside.widget-area -->
<?php
} else {
	if ( is_active_sidebar( 'primary_widget_area' ) ) { ?>
	<aside id="secondary" class="widget-area">
		<div class="widget-area-inner">
			<?php dynamic_sidebar( 'primary_widget_area' ); ?>
		</div><!-- div.widget-area-inner -->
	</aside><!-- aside.widget-area -->
<?php
	}
}


?>


