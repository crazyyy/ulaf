<?php


// COLOR SCHEME
if ( is_file( dirname(__FILE__) . '/../../../../../plugins/bold-page-builder/content_elements_misc/misc.php' ) ) {
	require_once( dirname(__FILE__) . '/../../../../../plugins/bold-page-builder/content_elements_misc/misc.php' );
}

if ( function_exists('bt_bb_get_color_scheme_param_array') ) {
	$color_scheme_arr = bt_bb_get_color_scheme_param_array();
} else {
	$color_scheme_arr = array();
}



// SECTION - NEGATIVE MARGIN, SHAPE, BACKGROUND OVERLAY
if ( function_exists( 'bt_bb_remove_params' ) ) {
	bt_bb_remove_params( 'bt_bb_section', 'background_overlay' );
}

if ( function_exists( 'bt_bb_remove_params' ) ) {
	bt_bb_remove_params( 'bt_bb_table_result_logo', 'responsive' );
	bt_bb_remove_params( 'bt_bb_table_result_logo', 'animation' );
	bt_bb_remove_params( 'bt_bb_table_result_logo', 'el_class' );
	bt_bb_remove_params( 'bt_bb_table_result_logo', 'el_id' );
	bt_bb_remove_params( 'bt_bb_table_result_logo', 'el_style' );
}

if ( function_exists( 'bt_bb_add_params' ) ) {
	bt_bb_add_params( 'bt_bb_section', array(
		array( 'param_name' => 'negative_margin', 'type' => 'dropdown', 'default' => '', 'heading' => esc_html__( 'Negative margin', 'campo' ), 'responsive_override' => true,
			'value' => array(
				esc_html__( 'No margin', 'campo' ) 		=> 'none',
				esc_html__( 'Extra small', 'campo' ) 		=> 'extra_small',
				esc_html__( 'Small', 'campo' ) 			=> 'small',		
				esc_html__( 'Normal', 'campo' ) 			=> 'normal',
				esc_html__( 'Medium', 'campo' ) 			=> 'medium',
				esc_html__( 'Large', 'campo' ) 			=> 'large',
				esc_html__( 'Extra large', 'campo' ) 		=> 'extra_large',
				esc_html__( 'Huge', 'campo' ) 			=> 'huge'
			)
		),
		array( 'param_name' => 'background_overlay', 'type' => 'dropdown', 'heading' => esc_html__( 'Background overlay', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ), 
			'value' => array(
				esc_html__( 'No overlay', 'campo' )    				=> '',
				esc_html__( 'Light stripes', 'campo' ) 				=> 'light_stripes',
				esc_html__( 'Dark stripes', 'campo' )  				=> 'dark_stripes',
				esc_html__( 'Light solid', 'campo' )	 				=> 'light_solid',
				esc_html__( 'Dark solid', 'campo' )	  				=> 'dark_solid',
				esc_html__( 'Light gradient', 'campo' )	  			=> 'light_gradient',
				esc_html__( 'Dark gradient', 'campo' )	  			=> 'dark_gradient',
				esc_html__( 'Light gray bottom gradient', 'campo' )	=> 'light_bottom_gray_gradient',
				esc_html__( 'Dark gray bottom gradient', 'campo' )	=> 'dark_bottom_gray_gradient',
				esc_html__( 'Accent bottom gradient', 'campo' )		=> 'accent_bottom_gradient',
				esc_html__( 'Alternate bottom gradient', 'campo' )	=> 'alternate_bottom_gradient'
			)
		),
		
		array( 'param_name' => 'shape', 'type' => 'dropdown',  'preview' => true,  'heading' => esc_html__( 'Shape', 'campo' ), 'group' => esc_html__( 'Shape', 'campo' ),
			'value' => array(
				esc_html__( 'Square', 'campo' ) 			=> '',
				esc_html__( 'Soft Rounded', 'campo' ) 	=> 'soft-rounded',
				esc_html__( 'Hard Rounded', 'campo' ) 	=> 'hard-rounded'
			)
		),
		array( 'param_name' => 'top_left_shape', 'type' => 'checkbox', 'value' => array( esc_html__( 'Yes', 'campo' ) => 'top_left_shape' ), 'group' => esc_html__( 'Shape', 'campo' ), 'heading' => esc_html__( 'Top Left', 'campo' )
		),
		array( 'param_name' => 'top_right_shape', 'type' => 'checkbox', 'group' => esc_html__( 'Shape', 'campo' ), 'value' => array( esc_html__( 'Yes', 'campo' ) => 'top_right_shape' ), 'heading' => esc_html__( 'Top Right', 'campo' )
		),
		array( 'param_name' => 'bottom_left_shape', 'type' => 'checkbox', 'group' => esc_html__( 'Shape', 'campo' ), 'value' => array( esc_html__( 'Yes', 'campo' ) => 'bottom_left_shape' ), 'heading' => esc_html__( 'Bottom Left', 'campo' )
		),
		array( 'param_name' => 'bottom_right_shape', 'type' => 'checkbox', 'group' => esc_html__( 'Shape', 'campo' ), 'value' => array( esc_html__( 'Yes', 'campo' ) => 'bottom_right_shape' ), 'heading' => esc_html__( 'Bottom Right', 'campo' )
		),
		
	));
}

add_filter( 'bt_bb_section_extra_responsive_data_override_param', function( $params ) { 
	$params[] = 'negative_margin';
	return $params;
});


function campo_bt_bb_section_class( $class, $atts ) {
	if ( isset( $atts['shape'] ) && $atts['shape'] != '' ) {
		$class[] = 'bt_bb_shape' . '_' . $atts['shape'];
	}
	if ( isset( $atts['top_left_shape'] ) && $atts['top_left_shape'] != '' ) {
		$class[] = 'bt_bb_top_left_shape';
	}
	if ( isset( $atts['top_right_shape'] ) && $atts['top_right_shape'] != '' ) {
		$class[] = 'bt_bb_top_right_shape';
	}
	if ( isset( $atts['bottom_left_shape'] ) && $atts['bottom_left_shape'] != '' ) {
		$class[] = 'bt_bb_bottom_left_shape';
	}
	if ( isset( $atts['bottom_right_shape'] ) && $atts['bottom_right_shape'] != '' ) {
		$class[] = 'bt_bb_bottom_right_shape';
	}
	return $class;
}

add_filter( 'bt_bb_section_class', 'campo_bt_bb_section_class', 10, 2 );



// ROW - SHAPE, BACKGROUND IMAGE
if ( function_exists( 'bt_bb_add_params' ) ) {
	bt_bb_add_params( 'bt_bb_row', array(
		array( 'param_name' => 'background_image', 'type' => 'attach_image',  'preview' => true, 'heading' => esc_html__( 'Background image', 'campo' ), 'group' => esc_html__( 'General', 'campo' ) ),
		array( 'param_name' => 'shape', 'type' => 'dropdown', 'heading' => esc_html__( 'Shape', 'campo' ), 'preview' => true, 'group' => esc_html__( 'Design', 'campo' ),
			'value' => array(
				esc_html__( 'Square', 'campo' ) 					=> '',
				esc_html__( 'Soft Rounded', 'campo' ) 			=> 'soft-rounded',
				esc_html__( 'Hard Rounded', 'campo' ) 			=> 'hard-rounded'
			)
		),
		array( 'param_name' => 'negative_margin', 'type' => 'dropdown', 'default' => '', 'heading' => esc_html__( 'Negative margin', 'campo' ), 'responsive_override' => true,
			'value' => array(
				esc_html__( 'No margin', 'campo' ) 		=> 'none',
				esc_html__( 'Extra small', 'campo' ) 		=> 'extra_small',
				esc_html__( 'Small', 'campo' ) 			=> 'small',		
				esc_html__( 'Normal', 'campo' ) 			=> 'normal',
				esc_html__( 'Medium', 'campo' ) 			=> 'medium',
				esc_html__( 'Large', 'campo' ) 			=> 'large',
				esc_html__( 'Extra large', 'campo' ) 		=> 'extra_large',
				esc_html__( 'Huge', 'campo' ) 			=> 'huge'
			)
		),
	));
}

add_filter( 'bt_bb_row_extra_responsive_data_override_param', function( $params ) { 
	$params[] = 'negative_margin';
	return $params;
});

function campo_bt_bb_row_class( $class, $atts ) {
	if ( isset( $atts['shape'] ) && $atts['shape'] != '' ) {
		$class[] = 'bt_bb_shape' . '_' . $atts['shape'];
	}
	if ( isset( $atts['background_image'] ) && $atts['background_image'] != '' ) {
		$class[] = 'bt_bb_row_with_bg_image';
	}
	return $class;
}

function campo_bt_bb_row_style( $style_attr, $atts ) {
	if ( isset( $atts['background_image'] ) && $atts['background_image'] != '' ) {
		$background_image = wp_get_attachment_image_src( $atts['background_image'], 'full' );
		$background_image_url = $background_image[0];
		$background_image_style = 'background-image:url("' . $background_image_url . '");';
		$style_attr .= $background_image_style;
	}
	return $style_attr;
}

add_filter( 'bt_bb_row_class', 'campo_bt_bb_row_class', 10, 2 );
add_filter( 'bt_bb_row_style', 'campo_bt_bb_row_style', 10, 2 );



// COLUMN - SHADOW, SHAPE, TRIANGLE DETAIL, BORDERS
if ( function_exists( 'bt_bb_add_params' ) ) {
	bt_bb_add_params( 'bt_bb_column', array(
		array( 'param_name' => 'shadow', 'type' => 'dropdown', 'heading' => esc_html__( 'Shadow', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ),
			'value' => array(
				esc_html__( 'No', 'campo' ) 						=> '',
				esc_html__( 'Yes', 'campo' ) 						=> 'show'
			)
		),
		array( 'param_name' => 'shape', 'type' => 'dropdown', 'heading' => esc_html__( 'Shape', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ), 'preview' => true,
			'value' => array(
				esc_html__( 'Square', 'campo' ) 					=> '',
				esc_html__( 'Soft Rounded', 'campo' ) 			=> 'soft-rounded',
				esc_html__( 'Hard Rounded', 'campo' ) 			=> 'hard-rounded'
			)
		),
		array( 'param_name' => 'right_triangle', 'type' => 'dropdown', 'heading' => esc_html__( 'Right triangle', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ), 'responsive_override' => true,
			'value' => array(
				esc_html__( 'No', 'campo' ) 					=> 'no',
				esc_html__( 'Yes', 'campo' ) 					=> 'yes'
			)
		),
		array( 'param_name' => 'left_triangle', 'type' => 'dropdown', 'heading' => esc_html__( 'Left triangle', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ), 'responsive_override' => true,
			'value' => array(
				esc_html__( 'No', 'campo' ) 					=> 'no',
				esc_html__( 'Yes', 'campo' ) 					=> 'yes'
			)
		),

		array( 'param_name' => 'border_top', 'type' => 'dropdown', 'heading' => esc_html__( 'Top border', 'campo' ), 'default' => '', 'group' => esc_html__( 'Border', 'campo' ), 'responsive_override' => true,
			'value' => array(
				esc_html__( 'No', 'campo' ) 	=> 'none',
				esc_html__( 'Yes', 'campo' ) 	=> 'yes'
			)
		),
		array( 'param_name' => 'border_bottom', 'type' => 'dropdown', 'heading' => esc_html__( 'Bottom border', 'campo' ), 'default' => '', 'group' => esc_html__( 'Border', 'campo' ), 'responsive_override' => true,
			'value' => array(
				esc_html__( 'No', 'campo' ) 	=> 'none',
				esc_html__( 'Yes', 'campo' ) 	=> 'yes'
			)
		),
		array( 'param_name' => 'border_right', 'type' => 'dropdown', 'heading' => esc_html__( 'Right border', 'campo' ), 'default' => '', 'group' => esc_html__( 'Border', 'campo' ), 'responsive_override' => true,
			'value' => array(
				esc_html__( 'No', 'campo' ) 	=> 'none',
				esc_html__( 'Yes', 'campo' ) 	=> 'yes'
			)
		),
		array( 'param_name' => 'border_left', 'type' => 'dropdown', 'heading' => esc_html__( 'Left border', 'campo' ), 'default' => '', 'group' => esc_html__( 'Border', 'campo' ), 'responsive_override' => true,
			'value' => array(
				esc_html__( 'No', 'campo' ) 	=> 'none',
				esc_html__( 'Yes', 'campo' ) 	=> 'yes'
			)
		),
		array( 'param_name' => 'border_color', 'type' => 'dropdown', 'heading' => esc_html__( 'Border color', 'campo' ), 'group' => esc_html__( 'Border', 'campo' ),
			'value' => array(
				esc_html__( 'Accent color', 'campo' ) 		=> '',
				esc_html__( 'Alternate color', 'campo' ) 		=> 'alternate',
				esc_html__( 'Light color', 'campo' ) 			=> 'light',
				esc_html__( 'Dark color', 'campo' ) 			=> 'dark',
				esc_html__( 'Gray color', 'campo' ) 			=> 'gray',
				esc_html__( 'Beige color', 'campo' ) 			=> 'beige'
			)
		),
		array( 'param_name' => 'border_thickness', 'type' => 'dropdown', 'heading' => esc_html__( 'Border tickness', 'campo' ), 'group' => esc_html__( 'Border', 'campo' ), 
			'value' => array(
				esc_html__( '1px', 'campo' ) 	=> '1px',
				esc_html__( '2px', 'campo' ) 	=> '2px',
				esc_html__( '3px', 'campo' ) 	=> '3px',
				esc_html__( '4px', 'campo' ) 	=> '4px',
				esc_html__( '5px', 'campo' ) 	=> '5px',
				esc_html__( '10px', 'campo' ) => '10px',
				esc_html__( '15px', 'campo' ) => '15px',
				esc_html__( '20px', 'campo' ) => '20px'
			)
		),
	));
}

add_filter( 'bt_bb_column_extra_responsive_data_override_param', function( $params ) { 
	$params[] = 'right_triangle';
	return $params;
});

add_filter( 'bt_bb_column_extra_responsive_data_override_param', function( $params ) { 
	$params[] = 'left_triangle';
	return $params;
});


add_filter( 'bt_bb_column_extra_responsive_data_override_param', function( $params ) { 
	$params[] = 'border_top';
	return $params;
});

add_filter( 'bt_bb_column_extra_responsive_data_override_param', function( $params ) { 
	$params[] = 'border_bottom';
	return $params;
});

add_filter( 'bt_bb_column_extra_responsive_data_override_param', function( $params ) { 
	$params[] = 'border_right';
	return $params;
});

add_filter( 'bt_bb_column_extra_responsive_data_override_param', function( $params ) { 
	$params[] = 'border_left';
	return $params;
});

function campo_bt_bb_column_class( $class, $atts ) {
	if ( isset( $atts['shadow'] ) && $atts['shadow'] != '' ) {
		$class[] = 'bt_bb_shadow' . '_' . $atts['shadow'];
	}
	if ( isset( $atts['shape'] ) && $atts['shape'] != '' ) {
		$class[] = 'bt_bb_shape' . '_' . $atts['shape'];
	}
	if ( isset( $atts['border_thickness'] ) && $atts['border_thickness'] != '' ) {
		$class[] = 'bt_bb_border_thickness' . '_' . $atts['border_thickness'];
	}
	if ( isset( $atts['border_color'] ) && $atts['border_color'] != '' ) {
		$class[] = 'bt_bb_border_color' . '_' . $atts['border_color'];
	}
	return $class;
}

add_filter( 'bt_bb_column_class', 'campo_bt_bb_column_class', 10, 2 );



// INNER COLUMN - SHADOW, SHAPE
if ( function_exists( 'bt_bb_add_params' ) ) {
	bt_bb_add_params( 'bt_bb_column_inner', array(
		array( 'param_name' => 'shadow', 'type' => 'dropdown', 'heading' => esc_html__( 'Shadow', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ),
			'value' => array(
				esc_html__( 'No', 'campo' ) 						=> '',
				esc_html__( 'Yes', 'campo' ) 						=> 'show'
			)
		),
		array( 'param_name' => 'shape', 'type' => 'dropdown', 'heading' => esc_html__( 'Shape', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ), 'preview' => true,
			'value' => array(
				esc_html__( 'Square', 'campo' ) 					=> '',
				esc_html__( 'Soft Rounded', 'campo' ) 			=> 'soft-rounded',
				esc_html__( 'Hard Rounded', 'campo' ) 			=> 'hard-rounded'
			)
		),

		array( 'param_name' => 'border_top', 'type' => 'dropdown', 'heading' => esc_html__( 'Top border', 'campo' ), 'default' => '', 'group' => esc_html__( 'Border', 'campo' ), 'responsive_override' => true,
			'value' => array(
				esc_html__( 'No', 'campo' ) 	=> 'none',
				esc_html__( 'Yes', 'campo' ) 	=> 'yes'
			)
		),
		array( 'param_name' => 'border_bottom', 'type' => 'dropdown', 'heading' => esc_html__( 'Bottom border', 'campo' ), 'default' => '', 'group' => esc_html__( 'Border', 'campo' ), 'responsive_override' => true,
			'value' => array(
				esc_html__( 'No', 'campo' ) 	=> 'none',
				esc_html__( 'Yes', 'campo' ) 	=> 'yes'
			)
		),
		array( 'param_name' => 'border_right', 'type' => 'dropdown', 'heading' => esc_html__( 'Right border', 'campo' ), 'default' => '', 'group' => esc_html__( 'Border', 'campo' ), 'responsive_override' => true,
			'value' => array(
				esc_html__( 'No', 'campo' ) 	=> 'none',
				esc_html__( 'Yes', 'campo' ) 	=> 'yes'
			)
		),
		array( 'param_name' => 'border_left', 'type' => 'dropdown', 'heading' => esc_html__( 'Left border', 'campo' ), 'default' => '', 'group' => esc_html__( 'Border', 'campo' ), 'responsive_override' => true,
			'value' => array(
				esc_html__( 'No', 'campo' ) 	=> 'none',
				esc_html__( 'Yes', 'campo' ) 	=> 'yes'
			)
		),
		array( 'param_name' => 'border_color', 'type' => 'dropdown', 'heading' => esc_html__( 'Border color', 'campo' ), 'group' => esc_html__( 'Border', 'campo' ),
			'value' => array(
				esc_html__( 'Accent color', 'campo' ) 		=> '',
				esc_html__( 'Alternate color', 'campo' ) 		=> 'alternate',
				esc_html__( 'Light color', 'campo' ) 			=> 'light',
				esc_html__( 'Dark color', 'campo' ) 			=> 'dark',
				esc_html__( 'Gray color', 'campo' ) 			=> 'gray'
			)
		),
		array( 'param_name' => 'border_thickness', 'type' => 'dropdown', 'heading' => esc_html__( 'Border tickness', 'campo' ), 'group' => esc_html__( 'Border', 'campo' ), 
			'value' => array(
				esc_html__( '1px', 'campo' ) 	=> '1px',
				esc_html__( '2px', 'campo' ) 	=> '2px',
				esc_html__( '3px', 'campo' ) 	=> '3px',
				esc_html__( '4px', 'campo' ) 	=> '4px',
				esc_html__( '5px', 'campo' ) 	=> '5px',
				esc_html__( '10px', 'campo' ) => '10px',
				esc_html__( '15px', 'campo' ) => '15px',
				esc_html__( '20px', 'campo' ) => '20px'
			)
		),
	));
}


add_filter( 'bt_bb_column_inner_extra_responsive_data_override_param', function( $params ) { 
	$params[] = 'border_top';
	return $params;
});

add_filter( 'bt_bb_column_inner_extra_responsive_data_override_param', function( $params ) { 
	$params[] = 'border_bottom';
	return $params;
});

add_filter( 'bt_bb_column_inner_extra_responsive_data_override_param', function( $params ) { 
	$params[] = 'border_right';
	return $params;
});

add_filter( 'bt_bb_column_inner_extra_responsive_data_override_param', function( $params ) { 
	$params[] = 'border_left';
	return $params;
});

function campo_bt_bb_column_inner_class( $class, $atts ) {
	if ( isset( $atts['shadow'] ) && $atts['shadow'] != '' ) {
		$class[] = 'bt_bb_shadow' . '_' . $atts['shadow'];
	}
	if ( isset( $atts['shape'] ) && $atts['shape'] != '' ) {
		$class[] = 'bt_bb_shape' . '_' . $atts['shape'];
	}
	if ( isset( $atts['border_thickness'] ) && $atts['border_thickness'] != '' ) {
		$class[] = 'bt_bb_border_thickness' . '_' . $atts['border_thickness'];
	}
	if ( isset( $atts['border_color'] ) && $atts['border_color'] != '' ) {
		$class[] = 'bt_bb_border_color' . '_' . $atts['border_color'];
	}
	return $class;
}

add_filter( 'bt_bb_column_inner_class', 'campo_bt_bb_column_inner_class', 10, 2 );



// HEADLINE - DASH
if ( function_exists( 'bt_bb_remove_params' ) ) {
	bt_bb_remove_params( 'bt_bb_headline', 'dash' );
}

if ( function_exists( 'bt_bb_add_params' ) ) {
	bt_bb_add_params( 'bt_bb_headline', array(
		array( 'param_name' => 'dash', 'type' => 'dropdown', 'heading' => esc_html__( 'Dash', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ), 
			'value' => array(
				esc_html__( 'None', 'campo' ) 			=> 'none',
				esc_html__( 'Top', 'campo' ) 				=> 'top',
				esc_html__( 'Bottom', 'campo' ) 			=> 'bottom',
				esc_html__( 'Top and bottom', 'campo' ) 	=> 'top_bottom',
				esc_html__( 'On side (out)', 'campo' ) 	=> 'side',
				esc_html__( 'On side (in)', 'campo' ) 	=> 'side_in'
			)
		),
		array( 'param_name' => 'dash_color', 'type' => 'dropdown', 'heading' => esc_html__( 'Dash color', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ),
			'value' => array(
				esc_html__( 'Inherit', 'campo' ) 				=> '',
				esc_html__( 'Accent color', 'campo' ) 		=> 'accent',
				esc_html__( 'Alternate color', 'campo' ) 		=> 'alternate',
				esc_html__( 'Light color', 'campo' ) 			=> 'light',
				esc_html__( 'Dark color', 'campo' ) 			=> 'dark'
			)
		),
		array( 'param_name' => 'supertitle_color', 'type' => 'dropdown', 'heading' => esc_html__( 'Supertitle color', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ),
			'value' => array(
				esc_html__( 'Inherit', 'campo' ) 				=> '',
				esc_html__( 'Accent color', 'campo' ) 		=> 'accent',
				esc_html__( 'Alternate color', 'campo' ) 		=> 'alternate',
				esc_html__( 'Light color', 'campo' ) 			=> 'light',
				esc_html__( 'Dark color', 'campo' ) 			=> 'dark'
			)
		),
		array( 'param_name' => 'subtitle_style', 'type' => 'dropdown', 'heading' => esc_html__( 'Subtitle style', 'campo' ), 'group' => esc_html__( 'Font', 'campo' ),
			'value' => array(
				esc_html__( 'Inherit', 'campo' ) 				=> '',
				esc_html__( 'Italic', 'campo' ) 				=> 'italic',
				esc_html__( 'Oblique', 'campo' ) 				=> 'oblique'
			)
		),
	));
}

function campo_bt_bb_headline_class( $class, $atts ) {
	if ( isset( $atts['dash_color'] ) && $atts['dash_color'] != '' ) {
		$class[] = 'bt_bb_dash_color' . '_' . $atts['dash_color'];
	}
	if ( isset( $atts['headline'] ) && $atts['headline'] == '' ) {
		$class[] = 'btNoHeadline';
	}
	if ( isset( $atts['supertitle_color'] ) && $atts['supertitle_color'] != '' ) {
		$class[] = 'bt_bb_supertitle_color' . '_' . $atts['supertitle_color'];
	}
	if ( isset( $atts['subtitle_style'] ) && $atts['subtitle_style'] != '' ) {
		$class[] = 'bt_bb_subtitle_style' . '_' . $atts['subtitle_style'];
	}
	return $class;
}

add_filter( 'bt_bb_headline_class', 'campo_bt_bb_headline_class', 10, 2 );



// BUTTON - STYLE, ICON COLOR SCHEME
if ( function_exists( 'bt_bb_remove_params' ) ) {
	bt_bb_remove_params( 'bt_bb_button', 'style' );
}

if ( function_exists( 'bt_bb_add_params' ) ) {
	bt_bb_add_params( 'bt_bb_button', array(
		array( 'param_name' => 'style', 'type' => 'dropdown', 'heading' => esc_html__( 'Style', 'campo' ), 'preview' => true, 'weight' => 2, 'group' => esc_html__( 'Design', 'campo' ),
			'value' => array(
				esc_html__( 'Outline', 'campo' ) 				=> 'outline',
				esc_html__( 'Filled', 'campo' ) 				=> 'filled',
				esc_html__( 'Clean', 'campo' ) 				=> 'clean',
				esc_html__( 'Outline (2 colors)', 'campo' ) 	=> 'outline_two_colors'
			)
		),
		array( 'param_name' => 'icon_color_scheme', 'type' => 'dropdown', 'heading' => esc_html__( 'Icon color scheme', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ), 'value' => $color_scheme_arr ),
	));
}

function campo_bt_bb_button_class( $class, $atts ) {
	if ( isset( $atts['icon_color_scheme'] ) && $atts['icon_color_scheme'] != '' ) {
		$class[] = 'bt_bb_icon_color_scheme' . '_' . bt_bb_get_color_scheme_id( $atts['icon_color_scheme'] );
	}
	return $class;
}

function campo_bt_bb_button_style( $style, $atts ) {
	if ( isset( $atts['icon_color_scheme'] ) && $atts['icon_color_scheme'] != '' ) {
	
		$icon_color_scheme_id = NULL;
		if ( is_numeric ( $atts['icon_color_scheme'] ) ) {
			$icon_color_scheme_id = $atts['icon_color_scheme'];
		} else if ( $atts['icon_color_scheme'] != '' ) {
			$icon_color_scheme_id = bt_bb_get_color_scheme_id( $atts['icon_color_scheme'] );
		}
		$icon_color_scheme_colors = bt_bb_get_color_scheme_colors_by_id( $icon_color_scheme_id - 1 );
		if ( $icon_color_scheme_colors ) $style .= '; --icon-primary-color:' . $icon_color_scheme_colors[0] . '; --icon-secondary-color:' . $icon_color_scheme_colors[1] . ';';
	}
	return $style;
}


add_filter( 'bt_bb_button_class', 'campo_bt_bb_button_class', 10, 2 );
add_filter( 'bt_bb_button_style', 'campo_bt_bb_button_style', 10, 2 );


// ICON - STYLE
if ( function_exists( 'bt_bb_remove_params' ) ) {
	bt_bb_remove_params( 'bt_bb_icon', 'style' );
}
if ( function_exists( 'bt_bb_remove_params' ) ) {
	bt_bb_remove_params( 'bt_bb_icon', 'size' );
}

if ( function_exists( 'bt_bb_add_params' ) ) {
	bt_bb_add_params( 'bt_bb_icon', array(
		array( 'param_name' => 'style', 'type' => 'dropdown', 'heading' => esc_html__( 'Style', 'campo' ), 'preview' => true, 'group' => esc_html__( 'Design', 'campo' ),
			'value' => array(
				esc_html__( 'Outline', 'campo' ) 				=> 'outline',
				esc_html__( 'Filled', 'campo' ) 				=> 'filled',
				esc_html__( 'Borderless', 'campo' ) 			=> 'borderless',
				esc_html__( 'Outline (2 colors)', 'campo' ) 	=> 'outline_two_colors'
			)
		),
		array( 'param_name' => 'size', 'type' => 'dropdown', 'default' => 'small', 'heading' => esc_html__( 'Size', 'campo' ), 'preview' => true, 'group' => esc_html__( 'Design', 'campo' ), 'responsive_override' => true,
			'value' => array(
				esc_html__( 'Extra small', 'campo' ) 		=> 'xsmall',
				esc_html__( 'Small', 'campo' ) 			=> 'small',
				esc_html__( 'Normal', 'campo' ) 			=> 'normal',
				esc_html__( 'Medium', 'campo' ) 			=> 'medium',
				esc_html__( 'Large', 'campo' ) 			=> 'large',
				esc_html__( 'Extra large', 'campo' ) 		=> 'xlarge',
				esc_html__( 'Huge', 'campo' ) 			=> 'huge',
				esc_html__( 'Extra huge', 'campo' ) 		=> 'xhuge'
			)
		),
		array( 'param_name' => 'text_size', 'type' => 'dropdown', 'heading' => esc_html__( 'Text size', 'campo' ),
			'value' => array(
				esc_html__( 'Default', 'campo' ) 				=> '',
				esc_html__( '12px', 'campo' ) 				=> '12px',
				esc_html__( '13px', 'campo' ) 				=> '13px',
				esc_html__( '14px', 'campo' ) 				=> '14px',
				esc_html__( '15px', 'campo' ) 				=> '15px',
				esc_html__( '16px', 'campo' ) 				=> '16px',
				esc_html__( '17px', 'campo' ) 				=> '17px',
				esc_html__( '18px', 'campo' ) 				=> '18px',
				esc_html__( '19px', 'campo' ) 				=> '19px'
			)
		),
		array( 'param_name' => 'align_content', 'type' => 'dropdown', 'default' => 'center', 'heading' => esc_html__( 'Content alignment', 'campo' ), 'responsive_override' => true, 
			'value' => array(
				esc_html__( 'Top', 'campo' ) 				=> 'top',
				esc_html__( 'Center', 'campo' ) 			=> 'center',
				esc_html__( 'Bottom', 'campo' ) 			=> 'bottom'
			)
		),
	));
}

add_filter( 'bt_bb_icon_extra_responsive_data_override_param', function( $params ) { 
	$params[] = 'align_content';
	return $params;
});

function campo_bt_bb_icon_class( $class, $atts ) {
	if ( isset( $atts['text_size'] ) && $atts['text_size'] != '' ) {
		$class[] = 'bt_bb_text_size' . '_' . $atts['text_size'];
	}
	return $class;
}
add_filter( 'bt_bb_icon_class', 'campo_bt_bb_icon_class', 10, 2 );




// CUSTOM MENU - WEIGHT, FONT SIZE, TEXT DECORATION
if ( function_exists( 'bt_bb_remove_params' ) ) {
	bt_bb_remove_params( 'bt_bb_custom_menu', 'font_weight' );
}

if ( function_exists( 'bt_bb_add_params' ) ) {
	bt_bb_add_params( 'bt_bb_custom_menu', array(
		array( 'param_name' => 'weight', 'type' => 'dropdown', 'heading' => esc_html__( 'Font weight', 'campo' ),
			'value' => array(
				esc_html__( 'Default', 'campo' ) 		=> '',
				esc_html__( 'Normal', 'campo' ) 		=> 'normal',
				esc_html__( 'Bold', 'campo' ) 		=> 'bold',
				esc_html__( 'Bolder', 'campo' ) 		=> 'bolder',
				esc_html__( 'Lighter', 'campo' ) 		=> 'lighter',
				esc_html__( 'Light', 'campo' ) 		=> 'light',
				esc_html__( 'Thin', 'campo' ) 		=> 'thin',
				esc_html__( '100', 'campo' ) 			=> '100',
				esc_html__( '200', 'campo' ) 			=> '200',
				esc_html__( '300', 'campo' ) 			=> '300',
				esc_html__( '400', 'campo' ) 			=> '400',
				esc_html__( '500', 'campo' ) 			=> '500',
				esc_html__( '600', 'campo' ) 			=> '600',
				esc_html__( '700', 'campo' ) 			=> '700',
				esc_html__( '800', 'campo' ) 			=> '800',
				esc_html__( '900', 'campo' ) 			=> '900'
			)
		),
		array( 'param_name' => 'font_size', 'type' => 'dropdown', 'default' => '', 'heading' => esc_html__( 'Font size', 'campo' ),
			'value' => array(
				esc_html__( 'Default', 'campo' ) 				=> '',
				esc_html__( '12px', 'campo' ) 				=> '12px',
				esc_html__( '13px', 'campo' ) 				=> '13px',
				esc_html__( '14px', 'campo' ) 				=> '14px',
				esc_html__( '15px', 'campo' ) 				=> '15px',
				esc_html__( '16px', 'campo' ) 				=> '16px',
				esc_html__( '17px', 'campo' ) 				=> '17px'
			)
		),
		array( 'param_name' => 'text_decoration', 'type' => 'dropdown', 'heading' => esc_html__( 'Links decoration', 'campo' ),
			'value' => array(
				esc_html__( 'None', 'campo' ) 				=> '',
				esc_html__( 'Underline', 'campo' ) 			=> 'underline'
			)
		),
		array( 'param_name' => 'color_scheme', 'type' => 'dropdown', 'heading' => esc_html__( 'Color scheme', 'campo' ), 'value' => $color_scheme_arr ),
	));
}

function campo_bt_bb_custom_menu_class( $class, $atts ) {
	if ( isset( $atts['weight'] ) && $atts['weight'] != '' ) {
		$class[] = 'bt_bb_font_weight' . '_' . $atts['weight'];
	}
	if ( isset( $atts['font_size'] ) && $atts['font_size'] != '' ) {
		$class[] = 'bt_bb_font_size' . '_' . $atts['font_size'];
	}
	if ( isset( $atts['style'] ) && $atts['style'] != '' ) {
		$class[] = 'bt_bb_style' . '_' . $atts['style'];
	}
	if ( isset( $atts['text_decoration'] ) && $atts['text_decoration'] != '' ) {
		$class[] = 'bt_bb_text_decoration' . '_' . $atts['text_decoration'];
	}
	if ( isset( $atts['color_scheme'] ) && $atts['color_scheme'] != '' ) {
		$class[] = 'bt_bb_color_scheme' . '_' . bt_bb_get_color_scheme_id( $atts['color_scheme'] );
	}
	return $class;
}

function campo_bt_bb_custom_menu_style( $style, $atts ) {
	if ( isset( $atts['color_scheme'] ) && $atts['color_scheme'] != '' ) {
	
		$color_scheme_id = NULL;
		if ( is_numeric ( $atts['color_scheme'] ) ) {
			$color_scheme_id = $atts['color_scheme'];
		} else if ( $atts['color_scheme'] != '' ) {
			$color_scheme_id = bt_bb_get_color_scheme_id( $atts['color_scheme'] );
		}
		$color_scheme_colors = bt_bb_get_color_scheme_colors_by_id( $color_scheme_id - 1 );
		if ( $color_scheme_colors ) $style .= '; --custom-menu-primary-color:' . $color_scheme_colors[0] . '; --custom-menu-secondary-color:' . $color_scheme_colors[1] . ';';
	}
	return $style;
}

add_filter( 'bt_bb_custom_menu_class', 'campo_bt_bb_custom_menu_class', 10, 2 );
add_filter( 'bt_bb_custom_menu_style', 'campo_bt_bb_custom_menu_style', 10, 2 );



// GOOGLE MAPS - SHAPE, BORDER
if ( function_exists( 'bt_bb_add_params' ) ) {
	bt_bb_add_params( 'bt_bb_google_maps', array(
		array( 'param_name' => 'shape', 'type' => 'dropdown', 'heading' => esc_html__( 'Shape', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ),
			'value' => array(
				esc_html__( 'Square', 'campo' ) 			=> '',
				esc_html__( 'Soft Rounded', 'campo' ) 	=> 'soft-rounded',
				esc_html__( 'Hard Rounded', 'campo' ) 	=> 'hard-rounded'
			)
		),
		array( 'param_name' => 'border', 'type' => 'dropdown', 'heading' => esc_html__( 'Border', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ),
			'value' => array(
				esc_html__( 'No', 'campo' ) 				=> '',
				esc_html__( 'Yes', 'campo' ) 				=> 'yes'
			)
		),
		array( 'param_name' => 'border_color', 'type' => 'dropdown', 'heading' => esc_html__( 'Border color', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ),
			'value' => array(
				esc_html__( 'Gray color', 'campo' ) 			=> '',
				esc_html__( 'Accent color', 'campo' ) 		=> 'accent',
				esc_html__( 'Alternate color', 'campo' ) 		=> 'alternate',
				esc_html__( 'Light color', 'campo' ) 			=> 'light',
				esc_html__( 'Dark color', 'campo' ) 			=> 'dark'
			)
		),
		array( 'param_name' => 'triangle', 'type' => 'dropdown', 'heading' => esc_html__( 'Triangle', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ),
			'value' => array(
				esc_html__( 'No', 'campo' ) 				=> '',
				esc_html__( 'Yes', 'campo' ) 				=> 'yes'
			)
		),
		array( 'param_name' => 'map_controls', 'type' => 'dropdown', 'heading' => esc_html__( 'Map controls', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ),
			'value' => array(
				esc_html__( 'Show', 'campo' ) 				=> '',
				esc_html__( 'Hide', 'campo' ) 				=> 'hide'
			)
		),
		array( 'param_name' => 'location_width', 'type' => 'dropdown', 'heading' => esc_html__( 'Location width', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ),
			'value' => array(
				esc_html__( '1/3', 'campo' ) 				=> '',
				esc_html__( '1/2', 'campo' ) 				=> '2'
			)
		),
	));
}

function campo_bt_bb_google_maps_class( $class, $atts ) {
	if ( isset( $atts['shape'] ) && $atts['shape'] != '' ) {
		$class[] = 'bt_bb_shape' . '_' . $atts['shape'];
	}
	if ( isset( $atts['border'] ) && $atts['border'] != '' ) {
		$class[] = 'bt_bb_border' . '_' . $atts['border'];
	}
	if ( isset( $atts['border_color'] ) && $atts['border_color'] != '' ) {
		$class[] = 'bt_bb_border_color' . '_' . $atts['border_color'];
	}
	if ( isset( $atts['triangle'] ) && $atts['triangle'] != '' ) {
		$class[] = 'bt_bb_triangle' . '_' . $atts['triangle'];
	}
	if ( isset( $atts['map_controls'] ) && $atts['map_controls'] != '' ) {
		$class[] = 'bt_bb_map_controls' . '_' . $atts['map_controls'];
	}
	if ( isset( $atts['location_width'] ) && $atts['location_width'] != '' ) {
		$class[] = 'bt_bb_location_width' . '_' . $atts['location_width'];
	}
	return $class;
}

add_filter( 'bt_bb_google_maps_class', 'campo_bt_bb_google_maps_class', 10, 2 );




// IMAGE - SHAPE
if ( function_exists( 'bt_bb_remove_params' ) ) {
	bt_bb_remove_params( 'bt_bb_image', 'shape' );
}
if ( function_exists( 'bt_bb_remove_params' ) ) {
	bt_bb_remove_params( 'bt_bb_image', 'hover_style' );
}
if ( function_exists( 'bt_bb_add_params' ) ) {
	bt_bb_add_params( 'bt_bb_image', array(
		array( 'param_name' => 'shape', 'type' => 'dropdown', 'heading' => esc_html__( 'Shape', 'campo' ),
			'value' => array(
				esc_html__( 'Square', 'campo' ) 			=> 'square',
				esc_html__( 'Low Rounded', 'campo' ) 		=> 'low-rounded',
				esc_html__( 'Soft Rounded', 'campo' ) 	=> 'soft-rounded',
				esc_html__( 'Hard Rounded', 'campo' ) 	=> 'hard-rounded',
				esc_html__( 'Medium Rounded', 'campo' ) 	=> 'medium-rounded'
			)
		),
		array( 'param_name' => 'hover_style', 'type' => 'dropdown', 'heading' => esc_html__( 'Mouse hover style', 'campo' ), 'group' => esc_html__( 'URL', 'campo' ),
			'value' => array(
				esc_html__( 'Simple', 'campo' ) 					=> 'simple',
				esc_html__( 'Flip', 'campo' ) 					=> 'flip',
				esc_html__( 'Zoom in', 'campo' ) 					=> 'zoom-in',
				esc_html__( 'To grayscale', 'campo' ) 			=> 'to-grayscale',
				esc_html__( 'From grayscale', 'campo' ) 			=> 'from-grayscale',
				esc_html__( 'Zoom in to grayscale', 'campo' ) 	=> 'zoom-in-to-grayscale',
				esc_html__( 'Zoom in from grayscale', 'campo' ) 	=> 'zoom-in-from-grayscale',
				esc_html__( 'Scroll', 'campo' ) 					=> 'scroll',
				esc_html__( 'Shadow', 'campo' ) 					=> 'shadow',
				esc_html__( 'Shadow & zoom in', 'campo' ) 		=> 'shadow-zoom-in'
			)
		),
	));
}


// SLIDER - ARROWS COLOR SCHEME, LINES COLOR SCHEME
if ( function_exists( 'bt_bb_remove_params' ) ) {
	bt_bb_remove_params( 'bt_bb_content_slider', 'show_dots' );
}
if ( function_exists( 'bt_bb_remove_params' ) ) {
	bt_bb_remove_params( 'bt_bb_content_slider', 'arrows_size' );
}

if ( function_exists( 'bt_bb_add_params' ) ) {
	bt_bb_add_params( 'bt_bb_content_slider', array(
		array( 'param_name' => 'arrows_position', 'type' => 'dropdown', 'heading' => esc_html__( 'Arrows position', 'campo' ), 'group' => esc_html__( 'Navigation', 'campo' ),
			'value' => array(
				esc_html__( 'On side', 'campo' ) 				=> '',
				esc_html__( 'Outside slider', 'campo' ) 		=> 'outside',
				esc_html__( 'On top (right)', 'campo' ) 		=> 'top_right'
			)
		),
		array( 'param_name' => 'arrows_size', 'type' => 'dropdown', 'preview' => true, 'default' => 'normal', 'heading' => esc_html__( 'Arrows size', 'campo' ), 'group' => esc_html__( 'Navigation', 'campo' ),
			'value' => array(
				esc_html__( 'No arrows', 'campo' ) 		=> 'no_arrows',
				esc_html__( 'Small', 'campo' ) 			=> 'small',
				esc_html__( 'Normal', 'campo' ) 			=> 'normal',
				esc_html__( 'Large', 'campo' ) 			=> 'large'
			)
		),
		array( 'param_name' => 'arrows_color_scheme', 'type' => 'dropdown', 'heading' => esc_html__( 'Arrows color scheme', 'campo' ), 'group' => esc_html__( 'Navigation', 'campo' ), 'value' => $color_scheme_arr ),
		array( 'param_name' => 'show_dots', 'type' => 'dropdown', 'heading' => esc_html__( 'Dots position', 'campo' ), 'group' => esc_html__( 'Navigation', 'campo' ),
			'value' => array(
				esc_html__( 'Bottom', 'campo' ) 			=> 'bottom',
				esc_html__( 'Below', 'campo' ) 			=> 'below',
				esc_html__( 'Middle bottom', 'campo' ) 	=> 'bottom_middle',
				esc_html__( 'On side (right)', 'campo' ) 	=> 'side_right',
				esc_html__( 'Hide', 'campo' ) 			=> 'hide'
			)
		),
		array( 'param_name' => 'navigation_color_scheme', 'type' => 'dropdown', 'heading' => esc_html__( 'Dots color scheme', 'campo' ), 'group' => esc_html__( 'Navigation', 'campo' ), 'value' => $color_scheme_arr ),
	));
}

function campo_bt_bb_content_slider_class( $class, $atts ) {
	if ( isset( $atts['navigation_color_scheme'] ) && $atts['navigation_color_scheme'] != '' ) {
		$class[] = 'bt_bb_navigation_color_scheme' . '_' . bt_bb_get_color_scheme_id( $atts['navigation_color_scheme'] );
	}
	if ( isset( $atts['arrows_color_scheme'] ) && $atts['arrows_color_scheme'] != '' ) {
		$class[] = 'bt_bb_arrows_color_scheme' . '_' . bt_bb_get_color_scheme_id( $atts['arrows_color_scheme'] );
	}
	if ( isset( $atts['arrows_position'] ) && $atts['arrows_position'] != '' ) {
		$class[] = 'bt_bb_arrows_position' . '_' . $atts['arrows_position'];
	}
	return $class;
}

function campo_bt_bb_content_slider_style( $style, $atts ) {
	if ( isset( $atts['navigation_color_scheme'] ) && $atts['navigation_color_scheme'] != '' ) {
	
		$navigation_color_scheme_id = NULL;
		if ( is_numeric ( $atts['navigation_color_scheme'] ) ) {
			$navigation_color_scheme_id = $atts['navigation_color_scheme'];
		} else if ( $atts['navigation_color_scheme'] != '' ) {
			$navigation_color_scheme_id = bt_bb_get_color_scheme_id( $atts['navigation_color_scheme'] );
		}
		$navigation_color_scheme_colors = bt_bb_get_color_scheme_colors_by_id( $navigation_color_scheme_id - 1 );
		if ( $navigation_color_scheme_colors ) $style .= '; --navigation-primary-color:' . $navigation_color_scheme_colors[0] . '; --navigation-secondary-color:' . $navigation_color_scheme_colors[1] . ';';
	}

	if ( isset( $atts['arrows_color_scheme'] ) && $atts['arrows_color_scheme'] != '' ) {
	
		$arrows_color_scheme_id = NULL;
		if ( is_numeric ( $atts['arrows_color_scheme'] ) ) {
			$arrows_color_scheme_id = $atts['arrows_color_scheme'];
		} else if ( $atts['arrows_color_scheme'] != '' ) {
			$arrows_color_scheme_id = bt_bb_get_color_scheme_id( $atts['arrows_color_scheme'] );
		}
		$arrows_color_scheme_colors = bt_bb_get_color_scheme_colors_by_id( $arrows_color_scheme_id - 1 );
		if ( $arrows_color_scheme_colors ) $style .= '; --arrows-primary-color:' . $arrows_color_scheme_colors[0] . '; --arrows-secondary-color:' . $arrows_color_scheme_colors[1] . ';';
	}
	return $style;
}

add_filter( 'bt_bb_content_slider_class', 'campo_bt_bb_content_slider_class', 10, 2 );
add_filter( 'bt_bb_content_slider_style', 'campo_bt_bb_content_slider_style', 10, 2 );


// SLIDER ITEM - OVERLAY
if ( function_exists( 'bt_bb_remove_params' ) ) {
	bt_bb_remove_params( 'bt_bb_content_slider_item', 'background_overlay' );
}
if ( function_exists( 'bt_bb_add_params' ) ) {
	bt_bb_add_params( 'bt_bb_content_slider_item', array(
		array( 'param_name' => 'background_overlay', 'type' => 'dropdown', 'heading' => esc_html__( 'Background overlay', 'campo' ), 
			'value' => array(
				esc_html__( 'No overlay', 'campo' )   				=> '',
				esc_html__( 'Light stripes', 'campo' ) 				=> 'light_stripes',
				esc_html__( 'Dark stripes', 'campo' )  				=> 'dark_stripes',
				esc_html__( 'Light solid', 'campo' )	  				=> 'light_solid',
				esc_html__( 'Dark solid', 'campo' )	  				=> 'dark_solid',
				esc_html__( 'Light gradient', 'campo' )				=> 'light_gradient',
				esc_html__( 'Dark gradient', 'campo' ) 				=> 'dark_gradient',
				esc_html__( 'Light gray bottom gradient', 'campo' )	=> 'light_bottom_gray_gradient',
				esc_html__( 'Alternate bottom gradient', 'campo' )	=> 'alternate_bottom_gradient'
			)
		),
	));
}


// TABS - NEGATIVE MARGIN
if ( function_exists( 'bt_bb_add_params' ) ) {
	bt_bb_add_params( 'bt_bb_tabs', array(
		array( 'param_name' => 'negative_margin', 'type' => 'dropdown', 'default' => '', 'heading' => esc_html__( 'Negative margin', 'campo' ), 'responsive_override' => true,
			'value' => array(
				esc_html__( 'No margin', 'campo' ) 		=> 'none',
				esc_html__( 'Extra small', 'campo' ) 		=> 'extra_small',
				esc_html__( 'Small', 'campo' ) 			=> 'small',		
				esc_html__( 'Normal', 'campo' ) 			=> 'normal',
				esc_html__( 'Medium', 'campo' ) 			=> 'medium',
				esc_html__( 'Large', 'campo' ) 			=> 'large',
				esc_html__( 'Extra large', 'campo' ) 		=> 'extra_large',
				esc_html__( 'Huge', 'campo' ) 			=> 'huge'
			)
		),
	));
}

add_filter( 'bt_bb_tabs_extra_responsive_data_override_param', function( $params ) {
	$params[] = 'negative_margin';
	return $params;
});



// TEXT - FONT SIZE
if ( function_exists( 'bt_bb_add_params' ) ) {
	bt_bb_add_params( 'bt_bb_text', array(
		array( 'param_name' => 'font_size', 'type' => 'dropdown', 'preview' => true, 'heading' => esc_html__( 'Font size', 'campo' ),
			'value' => array(
				esc_html__( 'Default', 'campo' ) 				=> '',
				esc_html__( '12px', 'campo' ) 				=> '12px',
				esc_html__( '13px', 'campo' ) 				=> '13px',
				esc_html__( '14px', 'campo' ) 				=> '14px',
				esc_html__( '15px', 'campo' ) 				=> '15px',
				esc_html__( '16px', 'campo' ) 				=> '16px',
				esc_html__( '17px', 'campo' ) 				=> '17px'
			)
		),
	));
}

function campo_bt_bb_text_class( $class, $atts ) {
	if ( isset( $atts['font_size'] ) && $atts['font_size'] != '' ) {
		$class[] = 'bt_bb_font_size' . '_' . $atts['font_size'];
	}
	return $class;
}

add_filter( 'bt_bb_text_class', 'campo_bt_bb_text_class', 10, 2 );




// CONTACT FORM 7 - BUTTON COLOR SCHEME AND STYLE, INPUT STYLE, PLACEHOLDER STYLE
if ( function_exists( 'bt_bb_add_params' ) ) {
	bt_bb_add_params( 'bt_bb_contact_form_7', array(
		array( 'param_name' => 'input_style', 'type' => 'dropdown', 'heading' => esc_html__( 'Input style', 'campo' ), 
			'value' => array(
				esc_html__( 'Filled', 'campo' ) 					=> '',
				esc_html__( 'Underline', 'campo' ) 				=> 'underline'
			)
		),
		array( 'param_name' => 'input_color_scheme', 'preview' => true, 'type' => 'dropdown', 'heading' => esc_html__( 'Input color scheme', 'campo' ), 'value' => $color_scheme_arr ),
		array( 'param_name' => 'placeholder_style', 'type' => 'dropdown', 'heading' => esc_html__( 'Placeholder style', 'campo' ), 
			'value' => array(
				esc_html__( 'Semi transparent', 'campo' ) 			=> '',
				esc_html__( 'Full visibility', 'campo' ) 				=> 'full'
			)
		),

		array( 'param_name' => 'button_style', 'type' => 'dropdown', 'heading' => esc_html__( 'Submit button style', 'campo' ), 
			'value' => array(
				esc_html__( 'Filled', 'campo' ) 			=> '',
				esc_html__( 'Outline', 'campo' ) 			=> 'outline',
				esc_html__( 'Outline (2 colors)', 'campo' ) 			=> 'outline_two_colors',
				esc_html__( 'Clean', 'campo' ) 			=> 'clean'
			)
		),
		array( 'param_name' => 'color_scheme', 'preview' => true, 'type' => 'dropdown', 'heading' => esc_html__( 'Submit button color scheme', 'campo' ), 'value' => $color_scheme_arr ),
		array( 'param_name' => 'button_font_weight', 'type' => 'dropdown', 'heading' => esc_html__( 'Submit button font weight', 'campo' ), 
			'value' => array(
				esc_html__( 'Default', 'campo' ) 		=> '',
				esc_html__( 'Normal', 'campo' ) 		=> 'normal',
				esc_html__( 'Bold', 'campo' ) 		=> 'bold',
				esc_html__( 'Bolder', 'campo' ) 		=> 'bolder',
				esc_html__( 'Lighter', 'campo' ) 		=> 'lighter',
				esc_html__( 'Light', 'campo' ) 		=> 'light',
				esc_html__( 'Thin', 'campo' ) 		=> 'thin',
				esc_html__( '100', 'campo' ) 			=> '100',
				esc_html__( '200', 'campo' ) 			=> '200',
				esc_html__( '300', 'campo' ) 			=> '300',
				esc_html__( '400', 'campo' ) 			=> '400',
				esc_html__( '500', 'campo' ) 			=> '500',
				esc_html__( '600', 'campo' ) 			=> '600',
				esc_html__( '700', 'campo' ) 			=> '700',
				esc_html__( '800', 'campo' ) 			=> '800',
				esc_html__( '900', 'campo' ) 			=> '900'
			)
		),
	));
}

function campo_bt_bb_contact_form_7_class( $class, $atts ) {
	if ( isset( $atts['color_scheme'] ) && $atts['color_scheme'] != '' ) {
		$class[] = 'bt_bb_color_scheme' . '_' . bt_bb_get_color_scheme_id( $atts['color_scheme'] );
	}
	if ( isset( $atts['input_color_scheme'] ) && $atts['input_color_scheme'] != '' ) {
		$class[] = 'bt_bb_input_color_scheme' . '_' . bt_bb_get_color_scheme_id( $atts['input_color_scheme'] );
	}
	if ( isset( $atts['button_style'] ) && $atts['button_style'] != '' ) {
		$class[] = 'bt_bb_button_style' . '_' . $atts['button_style'];
	}
	if ( isset( $atts['button_font_weight'] ) && $atts['button_font_weight'] != '' ) {
		$class[] = 'bt_bb_button_font_weight' . '_' . $atts['button_font_weight'];
	}
	if ( isset( $atts['input_style'] ) && $atts['input_style'] != '' ) {
		$class[] = 'bt_bb_input_style' . '_' . $atts['input_style'];
	}
	if ( isset( $atts['placeholder_style'] ) && $atts['placeholder_style'] != '' ) {
		$class[] = 'bt_bb_placeholder_style' . '_' . $atts['placeholder_style'];
	}
	return $class;
}

function campo_bt_bb_contact_form_7_style( $style, $atts ) {
	if ( isset( $atts['color_scheme'] ) && $atts['color_scheme'] != '' ) {
	
		$color_scheme_id = NULL;
		if ( is_numeric ( $atts['color_scheme'] ) ) {
			$color_scheme_id = $atts['color_scheme'];
		} else if ( $atts['color_scheme'] != '' ) {
			$color_scheme_id = bt_bb_get_color_scheme_id( $atts['color_scheme'] );
		}
		$color_scheme_colors = bt_bb_get_color_scheme_colors_by_id( $color_scheme_id - 1 );
		if ( $color_scheme_colors ) $style .= '; --primary-color:' . $color_scheme_colors[0] . '; --secondary-color:' . $color_scheme_colors[1] . ';';
	}

	if ( isset( $atts['input_color_scheme'] ) && $atts['input_color_scheme'] != '' ) {
	
		$input_color_scheme_id = NULL;
		if ( is_numeric ( $atts['input_color_scheme'] ) ) {
			$input_color_scheme_id = $atts['input_color_scheme'];
		} else if ( $atts['input_color_scheme'] != '' ) {
			$input_color_scheme_id = bt_bb_get_color_scheme_id( $atts['input_color_scheme'] );
		}
		$input_color_scheme_colors = bt_bb_get_color_scheme_colors_by_id( $input_color_scheme_id - 1 );
		if ( $input_color_scheme_colors ) $style .= '; --input-primary-color:' . $input_color_scheme_colors[0] . '; --input-secondary-color:' . $input_color_scheme_colors[1] . ';';
	}
	return $style;
}

add_filter( 'bt_bb_contact_form_7_class', 'campo_bt_bb_contact_form_7_class', 10, 2 );
add_filter( 'bt_bb_contact_form_7_style', 'campo_bt_bb_contact_form_7_style', 10, 2 );



// CSS POST GRID - Image shape
if ( function_exists( 'bt_bb_add_params' ) ) {
	bt_bb_add_params( 'bt_bb_css_post_grid', array(
		array( 'param_name' => 'image_shape', 'type' => 'dropdown', 'heading' => esc_html__( 'Image shape', 'campo' ), 
			'value' => array(
				esc_html__( 'Square', 'campo' ) 			=> 'square',
				esc_html__( 'Soft Rounded', 'campo' ) 	=> 'rounded',
				esc_html__( 'Hard Rounded', 'campo' ) 	=> 'round'
			)
		),
	));
}

function campo_bt_bb_css_post_grid_class( $class, $atts ) {
	if ( isset( $atts['image_shape'] ) && $atts['image_shape'] != '' ) {
		$class[] = 'bt_bb_image_shape' . '_' . $atts['image_shape'];
	}
	return $class;
}

add_filter( 'bt_bb_css_post_grid_class', 'campo_bt_bb_css_post_grid_class', 10, 2 );


/* FRONT END 
---------------------------------------------------------- */

/* OLD ELEMENTS */
function bt_bb_fe_new_params( $elements_array) {
	$elements_array[ 'bt_bb_headline' ][ 'params' ][ 'supertitle_color' ] = array( 'ajax_filter' => array( 'class' ) );
	$elements_array[ 'bt_bb_headline' ][ 'params' ][ 'subtitle_style' ] = array( 'ajax_filter' => array( 'class' ) );
	$elements_array[ 'bt_bb_headline' ][ 'params' ][ 'dash_color' ] = array( 'ajax_filter' => array( 'class' ) );

	$elements_array[ 'bt_bb_button' ][ 'params' ][ 'icon_color_scheme' ] = array( 'ajax_filter' => array( 'class', 'style' ) );

	$elements_array[ 'bt_bb_icon' ][ 'params' ][ 'text_size' ] = array( 'ajax_filter' => array( 'class' ) );

	$elements_array[ 'bt_bb_service' ][ 'params' ][ 'title_size' ] = array( 'ajax_filter' => array( 'class', 'data-bt-override-class' ) );
	$elements_array[ 'bt_bb_service' ][ 'params' ][ 'align_content' ] = array( 'ajax_filter' => array( 'class', 'data-bt-override-class' ) );

	$elements_array[ 'bt_bb_counter' ][ 'params' ][ 'icon' ] = array();
	$elements_array[ 'bt_bb_counter' ][ 'params' ][ 'text' ] = array( 'js_handler' => array( 'target_selector' => '.bt_bb_counter_text', 'type' => 'inner_html_nl2br' ) );
	$elements_array[ 'bt_bb_counter' ][ 'params' ][ 'icon_color_scheme' ] = array( 'ajax_filter' => array( 'class', 'style' ) );

	$elements_array[ 'bt_bb_price_list' ][ 'params' ][ 'blur' ] = array( 'ajax_filter' => array( 'class' ) );
	$elements_array[ 'bt_bb_price_list' ][ 'params' ][ 'shape' ] = array( 'ajax_filter' => array( 'class' ) );
	$elements_array[ 'bt_bb_price_list' ][ 'params' ][ 'border' ] = array( 'ajax_filter' => array( 'class' ) );
	$elements_array[ 'bt_bb_price_list' ][ 'params' ][ 'border_color' ] = array( 'ajax_filter' => array( 'class' ) );
	$elements_array[ 'bt_bb_price_list' ][ 'params' ][ 'title_color_scheme' ] = array( 'ajax_filter' => array( 'class', 'style' ) );

	$elements_array[ 'bt_bb_latest_posts' ][ 'params' ][ 'shape' ] = array( 'ajax_filter' => array( 'class' ) );
	$elements_array[ 'bt_bb_latest_posts' ][ 'params' ][ 'title_text_transform' ] = array( 'ajax_filter' => array( 'class' ) );
	$elements_array[ 'bt_bb_latest_posts' ][ 'params' ][ 'title_size' ] = array( 'ajax_filter' => array( 'class' ) );
	$elements_array[ 'bt_bb_latest_posts' ][ 'params' ][ 'shape' ] = array( 'ajax_filter' => array( 'class' ) );
	$elements_array[ 'bt_bb_latest_posts' ][ 'params' ][ 'title_color_scheme' ] = array( 'ajax_filter' => array( 'class', 'style' ) );
	$elements_array[ 'bt_bb_latest_posts' ][ 'params' ][ 'color_scheme' ] = array( 'ajax_filter' => array( 'class', 'style' ) );
	$elements_array[ 'bt_bb_latest_posts' ][ 'params' ][ 'border' ] = array( 'ajax_filter' => array( 'class' ) );
	$elements_array[ 'bt_bb_latest_posts' ][ 'params' ][ 'border_color' ] = array( 'ajax_filter' => array( 'class' ) );
	$elements_array[ 'bt_bb_latest_posts' ][ 'params' ][ 'title_lines' ] = array( 'ajax_filter' => array( 'class' ) );

	$elements_array[ 'bt_bb_service' ][ 'params' ][ 'title_size' ] = array( 'ajax_filter' => array( 'class', 'data-bt-override-class' ) );
	$elements_array[ 'bt_bb_service' ][ 'params' ][ 'align_content' ] = array( 'ajax_filter' => array( 'class', 'data-bt-override-class' ) );
	
    return $elements_array;
}
add_filter( 'bt_bb_fe_elements', 'bt_bb_fe_new_params' );


/* NEW ELEMENTS */
function campo_bt_bb_fe( $elements ) {

	$elements[ 'bt_bb_call_to_action' ] = array(
		'edit_box_selector' => '',
		'params' => array(
			'icon'					=> array(),
			'supertitle'			=> array( 'js_handler' => array( 'target_selector' => '.bt_bb_call_to_action_supertitle', 'type' => 'inner_html' ) ),
			'title'					=> array( 'js_handler' => array( 'target_selector' => '.bt_bb_call_to_action_content .bt_bb_call_to_action_content_title', 'type' => 'inner_html' ) ),
			'url'					=> array( 'js_handler'  => array( 'target_selector' => 'a.btLink', 'type' => 'attr', 'attr' => 'href' ) ),
			'target'				=> array( 'js_handler'  => array( 'target_selector' => 'a.btLink', 'type' => 'attr', 'attr' => 'target' ) ),
			'border'				=> array( 'ajax_filter' => array( 'class' ) ), 
			'color_scheme'			=> array( 'ajax_filter' => array( 'class', 'style' ) ), 
		),
	);

	$elements[ 'bt_bb_card_icon' ] = array(
		'edit_box_selector' => '',
		'params' => array(
			'icon'					=> array(),
			'title'        			=> array( 'js_handler' => array( 'target_selector' => '.bt_bb_card_icon_title', 'type' => 'inner_html' ) ),
			'button_text'			=> array( 'js_handler' => array( 'target_selector' => '.bt_bb_card_icon_button .bt_bb_button a .bt_bb_button_text', 'type' => 'inner_html' ) ),
			'url'					=> array( 'js_handler'  => array( 'target_selector' => 'a.btCardLink', 'type' => 'attr', 'attr' => 'href' ) ),
			'target'				=> array( 'js_handler'  => array( 'target_selector' => 'a.btCardLink', 'type' => 'attr', 'attr' => 'target' ) ),
			'shape'					=> array( 'ajax_filter' => array( 'class' ) ),
			'color_scheme'			=> array( 'ajax_filter' => array( 'class', 'style' ) ), 
			'shadow'				=> array( 'ajax_filter' => array( 'class' ) ),
			'gradient'				=> array( 'ajax_filter' => array( 'class' ) ),
			'blur'					=> array( 'ajax_filter' => array( 'class' ) ),
			'icon_color'			=> array( 'ajax_filter' => array( 'class' ) ),
			'icon_size'				=> array(),
			'button_icon_color'		=> array( 'ajax_filter' => array( 'class' ) ),
			'border'				=> array( 'ajax_filter' => array( 'class' ) ),
			'border_color'			=> array( 'ajax_filter' => array( 'class' ) ),
			'hover_color_scheme'	=> array( 'ajax_filter' => array( 'class', 'style' ) ), 
		),
	);

	$elements[ 'bt_bb_card_image' ] = array(
		'edit_box_selector' => '',
		'params' => array(
			'color_scheme'			=> array( 'ajax_filter' => array( 'class', 'style' ) ), 
			'border'				=> array( 'ajax_filter' => array( 'class' ) ),
			'blur'					=> array( 'ajax_filter' => array( 'class' ) ),
			'hover_style'			=> array( 'ajax_filter' => array( 'class' ) ),
			'shape'					=> array( 'ajax_filter' => array( 'class' ) ),
			'shadow'				=> array( 'ajax_filter' => array( 'class' ) ), 
		),
	);

	$elements[ 'bt_bb_post_slider' ] = array(
		'edit_box_selector' => '',
		'params' => array(
			'gap'					=> array( 'ajax_filter' => array( 'class' ) ), 
			'shape'					=> array( 'ajax_filter' => array( 'class' ) ), 
			'image_shape'			=> array( 'ajax_filter' => array( 'class' ) ), 
			'item_color_scheme'		=> array( 'ajax_filter' => array( 'class', 'style' ) ), 
			'arrows_size'			=> array( 'ajax_filter' => array( 'class' ) ), 
			'show_dots'				=> array( 'ajax_filter' => array( 'class' ) ), 
			'navigation_position'	=> array( 'ajax_filter' => array( 'class' ) ), 
			'navigation_color_scheme'	=> array( 'ajax_filter' => array( 'class', 'style' ) ), 
			'arrows_color_scheme'		=> array( 'ajax_filter' => array( 'class', 'style' ) ), 
		),
	);

	$elements[ 'bt_bb_programme' ] = array(
		'edit_box_selector' => '',
		'params' => array(
			'icon'					=> array(),
			'location'				=> array(),
			'time'					=> array(),
			'title'					=> array(),
		),
	);

	$elements[ 'bt_bb_quote' ] = array(
		'edit_box_selector' => '',
		'params' => array(
			'quote'				=> array( 'js_handler' => array( 'target_selector' => '.bt_bb_quote_text', 'type' => 'inner_html' ) ),
			'line'				=> array( 'ajax_filter' => array( 'class' ) ),
			'line_color'		=> array( 'ajax_filter' => array( 'class' ) ),
			'size'				=> array( 'ajax_filter' => array( 'class' ) ),
		),
	);

	$elements[ 'bt_bb_result' ] = array(
		'edit_box_selector' => '',
		'params' => array(
			'title'					=> array( 'js_handler'  => array( 'target_selector' => '.bt_bb_result_title', 'type' => 'inner_html' ) ),
			'content'				=> array(),
			'marked'				=> array(),
			'shape' 				=> array( 'ajax_filter' => array( 'class' ) ),
			'color_scheme'			=> array( 'ajax_filter' => array( 'class', 'style' ) ), 
		),
	);

	$elements[ 'bt_bb_schedule' ] = array(
		'edit_box_selector' => '',
		'params' => array(
			'style'					=> array( 'ajax_filter' => array( 'class' ) ),
			'shape'					=> array( 'ajax_filter' => array( 'class' ) ),
			'color_scheme'			=> array( 'ajax_filter' => array( 'class', 'style' ) ), 
			'current_color_scheme'	=> array( 'ajax_filter' => array( 'class', 'style' ) ), 
		),
	);

	$elements[ 'bt_bb_score' ] = array(
		'edit_box_selector' => '',
		'params' => array(
			'day'					=> array( 'js_handler' => array( 'target_selector' => '.bt_bb_score_date .bt_bb_score_day span', 'type' => 'inner_html' ) ),
			'month'					=> array( 'js_handler' => array( 'target_selector' => '.bt_bb_score_date .bt_bb_score_month span', 'type' => 'inner_html' ) ),
			'image_01'				=> array( 'js_handler' => array( 'target_selector' => '', 'type' => 'image' ) ),
			'score_01'				=> array(),
			'detail_01'				=> array( 'js_handler' => array( 'target_selector' => '.bt_bb_score_details .bt_bb_score_score_01 .bt_bb_score_detail_01', 'type' => 'inner_html' ) ),
			'image_02'				=> array( 'js_handler' => array( 'target_selector' => '', 'type' => 'image' ) ),
			'score_02'				=> array(),
			'detail_02'				=> array( 'js_handler' => array( 'target_selector' => '.bt_bb_score_details .bt_bb_score_score_02 .bt_bb_score_detail_02', 'type' => 'inner_html' ) ),
			'shape'					=> array( 'ajax_filter' => array( 'class' ) ),
			'shadow'				=> array( 'ajax_filter' => array( 'class' ) ),
			'date_color_scheme'		=> array( 'ajax_filter' => array( 'class', 'style' ) ),
			'color_scheme'			=> array( 'ajax_filter' => array( 'class', 'style' ) ), 
		),
	);

	$elements[ 'bt_bb_single_event' ] = array(
		'edit_box_selector' => '',
		'params' => array(
			'day'					=> array( 'js_handler' => array( 'target_selector' => '.bt_bb_single_event_date .bt_bb_single_event_day span', 'type' => 'inner_html' ) ),
			'month'					=> array( 'js_handler' => array( 'target_selector' => '.bt_bb_single_event_date .bt_bb_single_event_month span', 'type' => 'inner_html' ) ),
			'image'					=> array(),
			'title'					=> array(),
			'subtitle'				=> array( 'js_handler' => array( 'target_selector' => '.bt_bb_single_event_details .bt_bb_single_event_subtitle span', 'type' => 'inner_html' ) ),
			'url'					=> array( 'js_handler'  => array( 'target_selector' => 'a.bt_bb_event_link', 'type' => 'attr', 'attr' => 'href' ) ),
			'target'				=> array( 'js_handler'  => array( 'target_selector' => 'a.bt_bb_event_link', 'type' => 'attr', 'attr' => 'target' ) ),
			'shape'					=> array( 'ajax_filter' => array( 'class' ) ), 
			'shadow'				=> array( 'ajax_filter' => array( 'class' ) ), 
		),
	);

	$elements[ 'bt_bb_single_score' ] = array(
		'edit_box_selector' => '',
		'params' => array(
			'image'					=> array(),
			'supertitle'			=> array(),
			'title'					=> array(),
			'subtitle'				=> array(),
			'url'					=> array( 'js_handler'  => array( 'target_selector' => 'a.bt_bb_single_score_link', 'type' => 'attr', 'attr' => 'href' ) ),
			'target'				=> array( 'js_handler'  => array( 'target_selector' => 'a.bt_bb_single_score_link', 'type' => 'attr', 'attr' => 'target' ) ),
			'shape'					=> array( 'ajax_filter' => array( 'class' ) ), 
			'shadow'				=> array( 'ajax_filter' => array( 'class' ) ), 
			'size'					=> array(),
			'gradient'				=> array( 'ajax_filter' => array( 'class' ) ), 
			'border'				=> array( 'ajax_filter' => array( 'class' ) ), 
			'border_color'			=> array( 'ajax_filter' => array( 'class' ) ), 
		),
	);

	$elements[ 'bt_bb_special_headline' ] = array(
		'edit_box_selector' => '',
		'params' => array(
			'headline'					=> array(),
			'headline_on_side'			=> array(),
			'superheadline'				=> array(),
			'subheadline'				=> array(),
			'size'						=> array( 'ajax_filter' => array( 'class' ) ), 
			'size_on_side'				=> array( 'ajax_filter' => array( 'class' ) ), 
			'border_color'				=> array( 'ajax_filter' => array( 'class' ) ), 
			'headline_on_side_color'	=> array( 'ajax_filter' => array( 'class' ) ), 
			'headline_color'			=> array( 'ajax_filter' => array( 'class' ) ), 
		),
	);

	$elements[ 'bt_bb_table' ] = array(
		'edit_box_selector' => '',
		'params' => array(
			'title'					=> array( 'js_handler'  => array( 'target_selector' => '.bt_bb_table_title', 'type' => 'inner_html' ) ),
			'content'				=> array(),
			'shape' 				=> array( 'ajax_filter' => array( 'class' ) ),
			'text'					=> array(),
			'url'					=> array(),
			'target'				=> array(),
		),
	);

	$elements[ 'bt_bb_contact_form_7' ] = array(
		'edit_box_selector' => '',
		'params' => array(
			'input_style' 			=> array( 'ajax_filter' => array( 'class' ) ),
			'input_color_scheme'	=> array( 'ajax_filter' => array( 'class', 'style' ) ), 
			'placeholder_style' 	=> array( 'ajax_filter' => array( 'class' ) ),
			'button_style' 			=> array( 'ajax_filter' => array( 'class' ) ),
			'color_scheme'			=> array( 'ajax_filter' => array( 'class', 'style' ) ), 
			'button_font_weight' 	=> array( 'ajax_filter' => array( 'class' ) ),
		),
	);


	return $elements;
}

add_filter( 'bt_bb_fe_elements', 'campo_bt_bb_fe' );