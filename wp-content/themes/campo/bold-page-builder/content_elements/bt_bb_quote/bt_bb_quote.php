<?php
class bt_bb_quote extends BT_BB_Element {

	function handle_shortcode( $atts, $content ) {
		extract( shortcode_atts( apply_filters( 'bt_bb_extract_atts_' . $this->shortcode, array(
			'quote'					=> '',
			'line'					=> '',
			'line_color'			=> '',
			'size'					=> ''
		) ), $atts, $this->shortcode ) );

		$quote = html_entity_decode( $quote, ENT_QUOTES, 'UTF-8' );
		
		$class = array( $this->shortcode );
		
		if ( $el_class != '' ) {
			$class[] = $el_class;
		}
		
		$id_attr = '';
		if ( $el_id != '' ) {
			$id_attr = ' ' . 'id="' . esc_attr( $el_id ) . '"';
		}
		
		$style_attr = '';
		if ( $el_style != '' ) {
			$style_attr = ' ' . 'style="' . esc_attr( $el_style ) . '"';
		}

		if ( $line != '' ) {
			$class[] = $this->prefix . 'line_' . $line;
		}

		if ( $size != '' ) {
			$class[] = $this->prefix . 'size_' . $size;
		}

		if ( $line_color != '' ) {
			$class[] = $this->prefix . 'line_color_' . $line_color;
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

		
		$output = '<div' . $id_attr . ' class="' . esc_attr( $class_attr ) . '"' . $style_attr . '>';
			
			// ICON
			$output .= '<div class="' . esc_attr( $this->shortcode . '_icon' ) . '"><span></span></div>';

			// TEXT
			if ( $quote != '' ) $output .= '<div class="' . esc_attr( $this->shortcode . '_text' ) . '"><span>' . $quote . '</span></div>';

		$output .= '</div>';


		
		$output = apply_filters( 'bt_bb_general_output', $output, $atts );
		$output = apply_filters( $this->shortcode . '_output', $output, $atts );
		
		return $output;
		
	}
	
	function map_shortcode() {
		
		bt_bb_map( $this->shortcode, array( 'name' => esc_html__( 'Quote', 'campo' ), 'description' => esc_html__( 'Quote', 'campo' ), 'icon' => $this->prefix_backend . 'icon' . '_' . $this->shortcode,
			'params' => array( 
				array( 'param_name' => 'quote', 'type' => 'textarea', 'heading' => esc_html__( 'Text', 'campo' ) ),
				array( 'param_name' => 'line', 'type' => 'dropdown', 'heading' => esc_html__( 'Line on side', 'campo' ), 'preview' => true,
					'value' => array(
						esc_html__( 'Show', 'campo' ) 	=> '',		
						esc_html__( 'Hide', 'campo' ) 	=> 'hide'
					)
				),
				array( 'param_name' => 'line_color', 'type' => 'dropdown', 'heading' => esc_html__( 'Color line', 'campo' ), 'preview' => true,
					'value' => array(
						esc_html__( 'Inherit', 'campo' ) 			=> '',		
						esc_html__( 'Accent color', 'campo' ) 	=> 'accent',
						esc_html__( 'Alternate color', 'campo' ) 	=> 'alternate'
					)
				),
				array( 'param_name' => 'size', 'type' => 'dropdown', 'heading' => esc_html__( 'Text size', 'campo' ), 'preview' => true,
					'value' => array(
						esc_html__( 'Small', 'campo' ) 		=> 'small',		
						esc_html__( 'Normal', 'campo' ) 		=> '',
						esc_html__( 'Large', 'campo' ) 		=> 'large'
					)
				),
			)
		) );
	}
}
