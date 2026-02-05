<?php

class bt_bb_google_maps_location extends BT_BB_Element {

	function handle_shortcode( $atts, $content ) {
		extract( shortcode_atts( apply_filters( 'bt_bb_extract_atts_' . $this->shortcode, array(
			'latitude'  => '',
			'longitude' => '',
			'icon'      => '',
			'shape'		=> '',
			'shadow'	=> '',
			'border'	=> ''
		) ), $atts, $this->shortcode ) );
		
        $class_master = 'bt_bb_map_location'; 
		
		$class = array( $this->shortcode, $class_master );  
		
		if ( $el_class != '' ) {
			$class[] = $el_class;
		}

		if ( $shape != '' ) {
			$class[] = $this->prefix . 'shape' . '_' . $shape;
		}

		if ( $shadow != '' ) {
			$class[] = $this->prefix . 'shadow' . '_' . $shadow;
		}
		
		if ( $border != '' ) {
			$class[] = $this->prefix . 'border' . '_' . $border;
		}
		
		if ( $content == '' ) {
			$class[] = $this->shortcode . '_without_content';
            $class[] = $class_master . '_without_content';
		}
		
		$id_attr = '';
		if ( $el_id != '' ) {
			$id_attr = ' ' . 'id="' . esc_attr( $el_id ) . '"';
		}

		$style_attr = '';
		$el_style = apply_filters( $this->shortcode . '_style', $el_style, $atts );
		if ( $el_style != '' ) {
			$style_attr = ' ' . 'style="' . esc_attr( $el_style ) . '"';
		}

		if ( $icon != '' ) {
			if ( is_numeric( $icon ) ) {
				$icon = wp_get_attachment_image_src( $icon, 'full' );
				$icon = $icon[0];				
			} else {
				$icon = esc_url_raw( $icon );
			}
		}

		foreach ( $this->extra_responsive_data_override_param as $p ) {
			if ( ! is_array( $atts ) || ! array_key_exists( $p, $atts ) ) continue;
			$this->responsive_data_override_class(
				$class, $data_override_class,
				apply_filters( $this->shortcode . '_responsive_data_override', array(
					'prefix' => $this->prefix,
					'param' => $p,
					'value' => $atts[ $p ],
				) )
			);
		}
		
		$class = apply_filters( $this->shortcode . '_class', $class, $atts );

		$output = '<div' . $id_attr . ' class="' . implode( ' ', $class ) . '"' . $style_attr . ' data-lat="' . esc_attr( $latitude ) . '" data-lng="' . esc_attr( $longitude ) . '" data-icon="' . esc_attr( $icon ) . '">' . do_shortcode( $content ) . '</div>';
		
		$output = apply_filters( 'bt_bb_general_output', $output, $atts );
		$output = apply_filters( $this->shortcode . '_output', $output, $atts );

		return $output;

	}

	function map_shortcode() {
		bt_bb_map( $this->shortcode, array( 'name' => esc_html__( 'Google Maps Location', 'campo' ), 'description' => esc_html__( 'Google Map Location', 'campo' ), 'container' => 'vertical', 'accept' => array( 'bt_bb_headline' => true, 'bt_bb_text' => true, 'bt_bb_button' => true, 'bt_bb_icon' => true, 'bt_bb_service' => true, 'bt_bb_service' => true, 'bt_bb_call_to_action' => true, 'bt_bb_image' => true, 'bt_bb_separator' => true, 'bt_bb_row_inner' => true ), 'icon' => $this->prefix_backend . 'icon' . '_' . $this->shortcode,
			'params' => array(
				array( 'param_name' => 'latitude', 'type' => 'textfield', 'heading' => esc_html__( 'Latitude', 'campo' ), 'preview' => true ),
				array( 'param_name' => 'longitude', 'type' => 'textfield', 'heading' => esc_html__( 'Longitude', 'campo' ), 'preview' => true ),
				array( 'param_name' => 'icon', 'type' => 'attach_image', 'heading' => esc_html__( 'Icon', 'campo' ), 'preview' => true ),

				array( 'param_name' => 'shape', 'type' => 'dropdown', 'heading' => esc_html__( 'Shape', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ),
					'value' => array(
						esc_html__( 'Square', 'campo' ) 			=> '',
						esc_html__( 'Soft Rounded', 'campo' ) 	=> 'soft-rounded',
						esc_html__( 'Hard Rounded', 'campo' ) 	=> 'hard-rounded'
					)
				),
				array( 'param_name' => 'shadow', 'type' => 'dropdown', 'heading' => esc_html__( 'Shadow', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ),
					'value' => array(
						esc_html__( 'No', 'campo' ) 				=> '',
						esc_html__( 'Yes', 'campo' ) 				=> 'show'
					)
				),
				array( 'param_name' => 'border', 'type' => 'dropdown', 'heading' => esc_html__( 'Border', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ),
					'value' => array(
						esc_html__( 'No', 'campo' ) 				=> '',
						esc_html__( 'Yes', 'campo' ) 				=> 'yes'
					)
				),
			)
		) );
	}
}