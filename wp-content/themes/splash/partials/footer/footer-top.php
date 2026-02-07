<?php
if(splash_is_layout('esport')){
    get_template_part('partials/footer/footer-top-esport');
}

if ( is_active_sidebar( 'footer' )) {
	if ( empty( $_wp_sidebars_widgets ) ) :
		$_wp_sidebars_widgets = get_option( 'sidebars_widgets', array() );
	endif;

	$sidebars_widgets_count = $_wp_sidebars_widgets;
	$sidebar_count = count( $sidebars_widgets_count[ 'footer' ] );

	$sidebar_class = '';
	if($sidebar_count <= 4) {
		if ($sidebar_count == 1)
			$sidebar_class = 'single';
		else
			$sidebar_class = 'less_4';
	} elseif($sidebar_count > 8) {
		$sidebar_class = 'more_8';
	}

	$soccer_two_footer = $sidebar_count == 3 ? 'sidebar_3' : '';

	$footer_cols_class = 'stm-cols-';
	switch ( get_theme_mod('footer_style', '') ) {
		case 'footer_style_three':
			$footer_cols_class = 'stm-single-col';
		break;
		case 'footer_style_two':
			$footer_cols_class .= '3';
		break;
		default:
			$footer_cols_class .= get_theme_mod('footer_sidebar_count', 4);
	}	
	?>

	<div id="footer-main">
		<div class="footer-widgets-wrapper <?php echo esc_attr($sidebar_class . ' ' . $soccer_two_footer); ?>">
			<div class="container">
				<div class="widgets <?php echo esc_attr($footer_cols_class) ?> clearfix">
					<?php dynamic_sidebar( 'footer' ); ?>
				</div>
			</div>
		</div>
	</div>

<?php } ?>