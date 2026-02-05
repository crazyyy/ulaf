<?php

class bt_bb_floating_image extends BT_BB_Element {

	function handle_shortcode( $atts, $content ) {
		extract( shortcode_atts( apply_filters( 'bt_bb_extract_atts', array(
			'image'      					=> '',
			'horizontal_position'  			=> 'left',
			'vertical_position'  			=> 'top',
			'animation_style'  				=> 'ease_out',
			'animation_delay'  				=> 'default',
			'animation_duration'  			=> '',
			'animation_speed'  				=> '1.0',
			'lazy_load'  					=> 'no'
		) ), $atts, $this->shortcode ) );
		
		wp_enqueue_script(
			'bt_bb_floating_image',
			get_template_directory_uri() . '/bold-page-builder/content_elements/bt_bb_floating_image/bt_bb_floating_image.js',
			array( 'jquery' ),
			'',
			true
		);

		$class = array( $this->shortcode );

		if ( $el_class != '' ) {
			$class[] = $el_class;
		}

		$id_attr = '';
		if ( $el_id != '' ) {
			$id_attr = ' ' . 'id="' . esc_attr( $el_id ) . '"';
		}
		if ( $horizontal_position != '' ) {
			$class[] = $this->shortcode . '_horizontal_position' . '_' . $horizontal_position;
		}
		if ( $vertical_position != '' ) {
			$class[] = $this->shortcode . '_vertical_position' . '_' . $vertical_position;
		}
		if ( $animation_delay != '' ) {
			$class[] = $this->shortcode . '_animation_delay' . '_' . $animation_delay;
		}
		if ( $animation_duration != '' ) {
			$class[] = $this->shortcode . '_animation_duration' . '_' . $animation_duration;
		}
		if ( $animation_style != '' ) {
			$class[] = $this->shortcode . '_animation_style' . '_' . $animation_style;
		}
		
		$class = apply_filters( $this->shortcode . '_class', $class, $atts );		
		$class_attr = implode( ' ', $class );
		
		if ( $el_class != '' ) {
			$class_attr = $class_attr . ' ' . $el_class;
		}
	
		$style_attr = '';
		if ( $el_style != '' ) {
			$style_attr = ' ' . 'style="' . esc_attr( $el_style ) . '"';
		}


		$output = '';

		
		if ( $image != '' ) {
			$output .=  '<div class="' . esc_attr( $this->shortcode . '_image') . '" data-speed="' . esc_attr( $animation_speed ) . '">' . do_shortcode( '[bt_bb_image image="' . esc_attr( $image ) . '" size="full" lazy_load="' . esc_attr( $lazy_load ) . '" ignore_fe_editor="true"]' ) . '</div>';	
		}
		

		$output = '<div' . $id_attr . ' class="' . esc_attr( implode( ' ', $class ) ) . '"' . $style_attr . ' data-speed="' . esc_attr( $animation_speed ) . '">' . ( $output ) . '</div>';

		$output = apply_filters( 'bt_bb_general_output', $output, $atts );
		$output = apply_filters( $this->shortcode . '_output', $output, $atts );

		return $output;

	}

	function map_shortcode() {

		bt_bb_map( $this->shortcode, array( 'name' => esc_html__( 'Floating image', 'campo' ), 'description' => esc_html__( 'Absolute positioned floating image', 'campo' ), 'icon' => $this->prefix_backend . 'icon' . '_' . $this->shortcode,
			'params' => array(
				array( 'param_name' => 'image', 'type' => 'attach_image', 'preview' => true, 'heading' => esc_html__( 'Image', 'campo' ) 
				),
				array( 'param_name' => 'vertical_position', 'preview' => true, 'default' => '', 'type' => 'dropdown', 'heading' => esc_html__( 'Vertical position', 'campo' ), 
					'value' => array(
						esc_html__( 'Default', 'campo' ) 				=> 'default',
						esc_html__( 'Top (absolute)', 'campo' ) 		=> 'top',
						esc_html__( 'Middle (absolute)', 'campo' ) 	=> 'middle',
						esc_html__( 'Bottom (absolute)', 'campo' ) 	=> 'bottom'
					)
				),
				array( 'param_name' => 'horizontal_position', 'preview' => true, 'default' => '', 'type' => 'dropdown', 'heading' => esc_html__( 'Horizontal position', 'campo' ), 
					'value' => array(
						esc_html__( 'Default', 'campo' ) 				=> 'default',
						esc_html__( 'Left (absolute)', 'campo' ) 		=> 'left',
						esc_html__( 'Center (absolute)', 'campo' ) 	=> 'center',
						esc_html__( 'Right (absolute)', 'campo' ) 	=> 'right'
					)
				),
				array( 'param_name' => 'lazy_load', 'type' => 'dropdown', 'default' => 'no', 'heading' => esc_html__( 'Lazy load this image', 'campo' ),
					'value' => array(
						esc_html__( 'No', 'campo' ) 	=> 'no',
						esc_html__( 'Yes', 'campo' ) 	=> 'yes'
					)
				),
				array( 'param_name' => 'animation_style', 'preview' => true, 'default' => 'ease_out', 'type' => 'dropdown', 'group' => esc_html__( 'Animation', 'campo' ), 'heading' => esc_html__( 'Animation style (check https://easings.net/en)', 'campo' ), 
					'value' => array(
						esc_html__( 'Ease out (default)', 'campo' ) 		=> 'ease_out',
						esc_html__( 'Ease out sine', 'campo' ) 			=> 'ease_out_sine',
						esc_html__( 'Ease in', 'campo' ) 					=> 'ease_in',
						esc_html__( 'Ease in sine', 'campo' ) 			=> 'ease_in_sine',
						esc_html__( 'Ease in out', 'campo' ) 				=> 'ease_in_out',
						esc_html__( 'Ease in out sine', 'campo' ) 		=> 'ease_in_out_sine',
						esc_html__( 'Ease in out bounce', 'campo' ) 		=> 'ease_in_out_back'
					)
				),
				array( 'param_name' => 'animation_delay', 'default' => '', 'type' => 'dropdown', 'group' => esc_html__( 'Animation', 'campo' ), 'heading' => esc_html__( 'Animation delay', 'campo' ), 
					'value' => array(
						esc_html__( 'Default', 'campo' ) 				=> 'default',
						esc_html__( '0ms', 'campo' ) 					=> '0',
						esc_html__( '100ms', 'campo' ) 				=> '100',
						esc_html__( '200ms', 'campo' ) 				=> '200',
						esc_html__( '300ms', 'campo' ) 				=> '300',
						esc_html__( '400ms', 'campo' ) 				=> '400',
						esc_html__( '500ms', 'campo' ) 				=> '500',
						esc_html__( '600ms', 'campo' ) 				=> '600',
						esc_html__( '700ms', 'campo' ) 				=> '700',
						esc_html__( '800ms', 'campo' ) 				=> '800',
						esc_html__( '900ms', 'campo' ) 				=> '900',
						esc_html__( '1000ms', 'campo' ) 				=> '1000'
					)
				),
				array( 'param_name' => 'animation_duration', 'preview' => true, 'default' => '', 'type' => 'dropdown', 'group' => esc_html__( 'Animation', 'campo' ), 'heading' => esc_html__( 'Animation duration', 'campo' ), 
					'value' => array(
						esc_html__( 'Default', 'campo' ) 				=> 'default',
						esc_html__( '0ms', 'campo' ) 					=> '0',
						esc_html__( '100ms', 'campo' ) 				=> '100',
						esc_html__( '200ms', 'campo' ) 				=> '200',
						esc_html__( '300ms', 'campo' ) 				=> '300',
						esc_html__( '400ms', 'campo' ) 				=> '400',
						esc_html__( '500ms', 'campo' ) 				=> '500',
						esc_html__( '600ms', 'campo' ) 				=> '600',
						esc_html__( '700ms', 'campo' ) 				=> '700',
						esc_html__( '800ms', 'campo' ) 				=> '800',
						esc_html__( '900ms', 'campo' ) 				=> '900',
						esc_html__( '1000ms', 'campo' ) 				=> '1000',
						esc_html__( '1100ms', 'campo' ) 				=> '1100',
						esc_html__( '1200ms', 'campo' ) 				=> '1200',
						esc_html__( '1300ms', 'campo' ) 				=> '1300',
						esc_html__( '1400ms', 'campo' ) 				=> '1400',
						esc_html__( '1500ms', 'campo' ) 				=> '1500',
						esc_html__( '2000ms', 'campo' ) 				=> '2000',
						esc_html__( '2500ms', 'campo' ) 				=> '2500',
						esc_html__( '3000ms', 'campo' ) 				=> '3000',
						esc_html__( '3500ms', 'campo' ) 				=> '3500',
						esc_html__( '4000ms', 'campo' ) 				=> '4000',
						esc_html__( '5000ms', 'campo' ) 				=> '5000',
						esc_html__( '6000ms', 'campo' ) 				=> '6000'
					)
				),
				array( 'param_name' => 'animation_speed', 'preview' => true, 'default' => '1.0', 'type' => 'dropdown', 'group' => esc_html__( 'Animation', 'campo' ), 'heading' => esc_html__( 'Animation s', 'campo' ), 
					'value' => array(
						esc_html__( '0.4 (very short)', 'campo' ) 		=> '0.4',
						esc_html__( '0.6', 'campo' ) 						=> '0.6',
						esc_html__( '0.8', 'campo' ) 						=> '0.8',
						esc_html__( '1.0', 'campo' ) 						=> '1.0',
						esc_html__( '1.2 (default)', 'campo' ) 			=> '1.2',
						esc_html__( '1.4', 'campo' ) 						=> '1.4',
						esc_html__( '1.6 (long)', 'campo' ) 				=> '1.6',
						esc_html__( '1.8', 'campo' ) 						=> '1.8',
						esc_html__( '2.0 (very long)', 'campo' ) 			=> '2.0',
						esc_html__( '2.5 (very very long)', 'campo' ) 	=> '2.5'
					)
				)
			)
		) );
	}
}