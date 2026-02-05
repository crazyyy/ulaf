<?php 

/**
 * Template part for displaying widgets in footer
 *
 * Displays the footer widget area.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 */

/* Skip sidebar if not active, but keep it in customizer (to allow preview) */
if ( ! is_active_sidebar( 'footer_widgets' ) ) {
	return;
}
?>
	<div class="site-footer-widgets" style="<?php echo esc_attr( boldthemes_get_style_from_color_scheme( [ 'css_var1' => '--primary-color', 'css_var2' => '--secondary-color', 'color_scheme_id' => boldthemes_get_option( 'footer_widgets_color_scheme' ) ] ) ); ?>">
		<aside class="widget-area">
			<div class="widget-area-inner">
				<?php dynamic_sidebar( 'footer_widgets' ); ?>
			</div><!-- .widget-area -->
		</aside><!-- .widget-area -->
	</div><!-- .site-footer-widgets -->
