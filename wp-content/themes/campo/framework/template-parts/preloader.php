<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 */
 
  
$prefix = boldthemes_get_prefix();

if ( boldthemes_get_option( 'enable_preloader' ) == '1' ) { ?>
	<div id="preloader" style="<?php echo esc_attr( boldthemes_get_style_from_color_scheme( [ 'css_var1' => '--primary-color', 'css_var2' => '--secondary-color', 'color_scheme_id' => boldthemes_get_option( 'preloader_color_scheme' ) ] ) ); ?>">
		<div class="preloader-background"></div>
		<div class="preloader-content">
			<div class="preloader-logo-holder"><?php echo ( boldthemes_logo( 'preloader_logo' ) ); ?></div>
			<div class="preloader-animation">
				<div class="preloader-animation-element-1"></div>
				<div class="preloader-animation-element-2"></div>
				<div class="preloader-animation-element-3"></div>
				<div class="preloader-animation-element-4"></div>
				<div class="preloader-animation-element-5"></div>
				<div class="preloader-animation-element-6"></div>
				<div class="preloader-animation-element-7"></div>
				<div class="preloader-animation-element-8"></div>
				<div class="preloader-animation-element-9"></div>
			</div>
			<div class="preloader-content"><p><?php echo wp_kses_post( boldthemes_get_option( 'preloader_text' ) ); ?></p></div>
		</div>
	</div><!-- /.preloader -->
<?php } ?>
