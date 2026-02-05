<?php

class bt_bb_special_headline extends BT_BB_Element {

	function handle_shortcode( $atts, $content ) {
		extract( shortcode_atts( apply_filters( 'bt_bb_extract_atts_' . $this->shortcode, array(
			'headline'      				=> '',
			'headline_on_side'      		=> '',
			'html_tag'      				=> 'h4',
			'side_html_tag'      			=> 'h4',
			
			'size'     						=> 'medium',
			'size_on_side'     				=> 'medium',
			'border_color'          		=> '',
			'headline_on_side_color'        => '',
			'headline_color'        		=> '',
			
			'superheadline' 				=> '',
			'subheadline'   				=> ''
		) ), $atts, $this->shortcode ) );

		$superheadline = html_entity_decode( $superheadline, ENT_QUOTES, 'UTF-8' );
		$subheadline = html_entity_decode( $subheadline, ENT_QUOTES, 'UTF-8' );
		$headline = html_entity_decode( $headline, ENT_QUOTES, 'UTF-8' );
		$headline_on_side = html_entity_decode( $headline_on_side, ENT_QUOTES, 'UTF-8' );

		
		$class = array( $this->shortcode );
		$data_override_class = array();
		
		if ( $el_class != '' ) {
			$class[] = $el_class;
		}

		$id_attr = '';
		if ( $el_id != '' ) {
			$id_attr = ' ' . 'id="' . esc_attr( $el_id ) . '"';
		}

		if ( $border_color != '' ) {
			$class[] = $this->prefix . 'border_color' . '_' . $border_color;
		}

		if ( $headline_on_side_color != '' ) {
			$class[] = $this->prefix . 'headline_on_side_color' . '_' . $headline_on_side_color;
		}

		if ( $headline_color != '' ) {
			$class[] = $this->prefix . 'headline_color' . '_' . $headline_color;
		}

		$style_attr = '';
		$el_style = apply_filters( $this->shortcode . '_style', $el_style, $atts );
		if ( $el_style != '' ) {
			$style_attr = ' ' . 'style="' . esc_attr( $el_style ) . '"';
		}

		$this->responsive_data_override_class(
			$class, $data_override_class,
			array(
				'prefix' => $this->prefix,
				'param' => 'size',
				'value' => $size
			)
		);

		$this->responsive_data_override_class(
			$class, $data_override_class,
			array(
				'prefix' => $this->prefix,
				'param' => 'size_on_side',
				'value' => $size_on_side
			)
		);

		do_action( $this->shortcode . '_before_extra_responsive_param' );
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
		$class_attr = implode( ' ', $class );
		
		if ( $el_class != '' ) {
			$class_attr = $class_attr . ' ' . $el_class;
		}

		$output = '<div' . $id_attr . ' class="' . esc_attr( $class_attr ) . '"' . $style_attr . ' data-bt-override-class="' . htmlspecialchars( json_encode( $data_override_class, JSON_FORCE_OBJECT ), ENT_QUOTES, 'UTF-8' ) . '">';

			if ( $headline_on_side != '' ) $output .= '<div class="' . esc_attr( $this->shortcode . '_on_side' ) . '">' . do_shortcode('[bt_bb_headline headline="' . esc_attr( $headline_on_side ) . '" html_tag="'. esc_attr( $side_html_tag ) .'" size="' . esc_attr( $size_on_side ) . '" ignore_fe_editor="true"]' ) . '</div>';

			$output .= '<div class="' . esc_attr( $this->shortcode . '_content') . '">';

				if ( $superheadline != '' ) $output .= '<div class="' . esc_attr( $this->shortcode ) . '_superheadline">' . $superheadline . '</div>';
				if ( $headline != '' ) $output .= '<div class="' . esc_attr( $this->shortcode . '_headline' ) . '">' . do_shortcode('[bt_bb_headline headline="' . esc_attr( $headline ) . '" html_tag="'. esc_attr( $html_tag ) .'" size="' . esc_attr( $size ) . '" ignore_fe_editor="true"]' ) . '</div>';
				if ( $subheadline != '' ) $output .= '<div class="' . esc_attr( $this->shortcode ) . '_subheadline">' . $subheadline . '</div>';

			$output .= '</div>';

		$output .= '</div>';
		
		$output = apply_filters( 'bt_bb_general_output', $output, $atts );
		$output = apply_filters( $this->shortcode . '_output', $output, $atts );

		return $output;

	}

	function map_shortcode() {

		$color_scheme_arr = bt_bb_get_color_scheme_param_array();

		bt_bb_map( $this->shortcode, array( 'name' => esc_html__( 'Special headline', 'campo' ), 'description' => esc_html__( 'Headline with custom Google fonts', 'campo' ), 'icon' => $this->prefix_backend . 'icon' . '_' . $this->shortcode, 
			'params' => array(
				array( 'param_name' => 'headline_on_side', 'type' => 'textfield', 'preview' => true, 'preview_strong' => true, 'heading' => esc_html__( 'Headline on side', 'campo' ) ),
				array( 'param_name' => 'headline', 'type' => 'textarea', 'heading' => esc_html__( 'Headline', 'campo' ), 'preview' => true, 'preview_strong' => true ),
				
				array( 'param_name' => 'superheadline', 'type' => 'textfield', 'heading' => esc_html__( 'Superheadline', 'campo' ) ),
				array( 'param_name' => 'subheadline', 'type' => 'textarea', 'heading' => esc_html__( 'Subheadline', 'campo' ) ),
				array( 'param_name' => 'side_html_tag', 'type' => 'dropdown', 'default' => 'h4', 'heading' => esc_html__( 'Side HTML tag', 'campo' ), 
					'value' => array(
						esc_html__( 'h1', 'campo' ) 	=> 'h1',
						esc_html__( 'h2', 'campo' ) 	=> 'h2',
						esc_html__( 'h3', 'campo' ) 	=> 'h3',
						esc_html__( 'h4', 'campo' ) 	=> 'h4',
						esc_html__( 'h5', 'campo' ) 	=> 'h5',
						esc_html__( 'h6', 'campo' ) 	=> 'h6'
				) ),
				array( 'param_name' => 'html_tag', 'type' => 'dropdown', 'default' => 'h4', 'heading' => esc_html__( 'HTML tag', 'campo' ), 
					'value' => array(
						esc_html__( 'h1', 'campo' ) 	=> 'h1',
						esc_html__( 'h2', 'campo' ) 	=> 'h2',
						esc_html__( 'h3', 'campo' ) 	=> 'h3',
						esc_html__( 'h4', 'campo' ) 	=> 'h4',
						esc_html__( 'h5', 'campo' ) 	=> 'h5',
						esc_html__( 'h6', 'campo' ) 	=> 'h6'
				) ),
				array( 'param_name' => 'size', 'type' => 'dropdown', 'default' => 'medium', 'heading' => esc_html__( 'Size', 'campo' ), 'description' => esc_html__( 'Predefined heading sizes, independent of html tag', 'campo' ), 'responsive_override' => true,
					'value' => array(
						esc_html__( 'Inherit', 'campo' ) 		=> 'inherit',
						esc_html__( 'Extra Small', 'campo' ) 	=> 'extrasmall',
						esc_html__( 'Small', 'campo' ) 		=> 'small',
						esc_html__( 'Medium', 'campo' ) 		=> 'medium',
						esc_html__( 'Normal', 'campo' ) 		=> 'normal',
						esc_html__( 'Large', 'campo' ) 		=> 'large',
						esc_html__( 'Extra large', 'campo' ) 	=> 'extralarge',
						esc_html__( 'Huge', 'campo' ) 		=> 'huge'
					)
				),
				array( 'param_name' => 'size_on_side', 'type' => 'dropdown', 'default' => 'medium', 'heading' => esc_html__( 'Size on side', 'campo' ), 'description' => esc_html__( 'Predefined heading sizes, independent of html tag', 'campo' ), 'responsive_override' => true,
					'value' => array(
						esc_html__( 'Inherit', 'campo' ) 		=> 'inherit',
						esc_html__( 'Extra Small', 'campo' ) 	=> 'extrasmall',
						esc_html__( 'Small', 'campo' ) 		=> 'small',
						esc_html__( 'Medium', 'campo' ) 		=> 'medium',
						esc_html__( 'Normal', 'campo' ) 		=> 'normal',
						esc_html__( 'Large', 'campo' ) 		=> 'large',
						esc_html__( 'Extra large', 'campo' ) 	=> 'extralarge',
						esc_html__( 'Huge', 'campo' ) 		=> 'huge'
					)
				),
				array( 'param_name' => 'border_color', 'type' => 'dropdown', 'heading' => esc_html__( 'Border color', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ),
					'value' => array(
						esc_html__( 'Inherit', 'campo' ) 					=> '',
						esc_html__( 'Accent color', 'campo' ) 			=> 'accent',
						esc_html__( 'Alternate color', 'campo' ) 			=> 'alternate',
						esc_html__( 'Light color', 'campo' ) 				=> 'light',
						esc_html__( 'Dark color', 'campo' ) 				=> 'dark'
					)
				),
				array( 'param_name' => 'headline_on_side_color', 'type' => 'dropdown', 'heading' => esc_html__( 'Headline on side color', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ),
					'value' => array(
						esc_html__( 'Inherit', 'campo' ) 					=> '',
						esc_html__( 'Accent color', 'campo' ) 			=> 'accent',
						esc_html__( 'Alternate color', 'campo' ) 			=> 'alternate',
						esc_html__( 'Light color', 'campo' ) 				=> 'light',
						esc_html__( 'Dark color', 'campo' ) 				=> 'dark'
					)
				),
				array( 'param_name' => 'headline_color', 'type' => 'dropdown', 'heading' => esc_html__( 'Headline color', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ),
					'value' => array(
						esc_html__( 'Inherit', 'campo' ) 					=> '',
						esc_html__( 'Accent color', 'campo' ) 			=> 'accent',
						esc_html__( 'Alternate color', 'campo' ) 			=> 'alternate',
						esc_html__( 'Light color', 'campo' ) 				=> 'light',
						esc_html__( 'Dark color', 'campo' ) 				=> 'dark'
					)
				),
			)
		) );
	}
}