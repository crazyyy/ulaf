<?php

class bt_bb_counter extends BT_BB_Element {

	function handle_shortcode( $atts, $content ) {
		
		extract( shortcode_atts( apply_filters( 'bt_bb_extract_atts_' . $this->shortcode, array(
			'icon'					=> '',
			'number'   				=> '',
			'text'					=> '',
			'size'     				=> '',
			'icon_color_scheme' 	=> ''
		) ), $atts, $this->shortcode ) );
		
		$class = array();
		$data_override_class = array();
		
		$class[] = 'bt_bb_counter_holder';

		$id_attr = '';
		if ( $el_id != '' ) {
			$id_attr = ' ' . 'id="' . esc_attr( $el_id ) . '"';
		}

		if ( $icon == '' ) {
			$class[] = "btNoIcon";
		}

		if ( $text != '' ) {
			$class[] = "btWithText";
		}

		$icon_color_scheme_id = NULL;
		if ( is_numeric ( $icon_color_scheme ) ) {
			$icon_color_scheme_id = $icon_color_scheme;
		} else if ( $icon_color_scheme != '' ) {
			$icon_color_scheme_id = bt_bb_get_color_scheme_id( $icon_color_scheme );
		}
		$icon_color_scheme_colors = bt_bb_get_color_scheme_colors_by_id( $icon_color_scheme_id - 1 );
		if ( $icon_color_scheme_colors ) $el_style .= '; --icon-primary-color:' . $icon_color_scheme_colors[0] . '; --icon-secondary-color:' . $icon_color_scheme_colors[1] . ';';
		if ( $icon_color_scheme != '' ) $class[] = $this->prefix . 'icon_color_scheme_' .  $icon_color_scheme_id;

		
		$this->responsive_data_override_class(
			$class, $data_override_class,
			array(
				'prefix' => $this->prefix,
				'param' => 'size',
				'value' => $size
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

		$style_attr = '';
		if ( $el_style != '' ) {
			$style_attr = ' ' . 'style="' . esc_attr( $el_style ) . '"';
		}

		$icon_html = bt_bb_icon::get_html( $icon, '' );
		
		$strlen = mb_strlen( $number, 'UTF-8' );
		$number = $this->msplit( $number );

		$output = '';
		$output .= '<div' . $id_attr . ' class="' . esc_attr( $class_attr ) . '"' . $style_attr . ' data-bt-override-class="' . htmlspecialchars( json_encode( $data_override_class, JSON_FORCE_OBJECT ), ENT_QUOTES, 'UTF-8' ) . '">';

			// ICON
			if ( $icon != '' ) $output .= '<div class="' . esc_attr( $this->shortcode . '_icon' ) . '">' . $icon_html . '</div>';

			// DIGIT
			if ( $number != '' || $text != '' ) $output .= '<div class="' . esc_attr( $this->shortcode . '_content' ) . '">';
				$output .= '<span class="bt_bb_counter animate" data-digit-length="' . esc_attr( $strlen ) . '">';
					for ( $i = 0; $i < $strlen; $i++ ) {
						
						if ( ctype_digit( $number[ $i ] ) ) {
							$output .= '<span class="onedigit p' . ( $strlen - $i ) . ' d' . $number[ $i ] . '" data-digit="' . esc_attr( $number[ $i ] ) . '">';
								for ( $j = 0; $j <= 9; $j++ ) {
									$output .= '<span class="n' . $j . '">' . $j . '</span>';
								}
								$output .= '<span class="n0">0</span>';	
							$output .= '</span>';
						} else {
							$output .= '<span class="onedigit p' . ( $strlen - $i ) . ' d0" data-digit="' . esc_attr( $number[ $i ] ) . '">';
								for ( $j = 0; $j <= 9; $j++ ) {
									$output .= '<span class="n' . $j . '"> &nbsp; </span>';
								}
								$output .= '<span class="n0 t">' . $number[ $i ] . '</span>';
							$output .= '</span>';
						}
					}
				$output .= '</span>';

				// TEXT
				if ( $text != '' ) $output .= '<span class="' . esc_attr( $this->shortcode . '_text' ) . '">' . nl2br( $text ) . '</span>';

			$output .= '</div>';

		$output .= '</div>';
		
		$output = apply_filters( 'bt_bb_general_output', $output, $atts );
		$output = apply_filters( $this->shortcode . '_output', $output, $atts );
			
		return $output;
	}
	
	function msplit( $str, $len = 1 ) {
		$arr = array();
		$length = mb_strlen( $str, 'UTF-8' );
		for ( $i = 0; $i < $length; $i += $len ) {
			$arr[] = mb_substr( $str, $i, $len, 'UTF-8' );
		}
		return $arr;
	}

	function map_shortcode() {

		$color_scheme_arr = bt_bb_get_color_scheme_param_array();

		bt_bb_map( $this->shortcode, array( 'name' => esc_html__( 'Counter', 'campo' ), 'description' => esc_html__( 'Animated counter', 'campo' ),  
			'icon' => $this->prefix_backend . 'icon' . '_' . $this->shortcode,
			'params' => array(
				array( 'param_name' => 'icon', 'type' => 'iconpicker', 'heading' => esc_html__( 'Icon', 'campo' ), 'preview' => true ),
				array( 'param_name' => 'number', 'type' => 'textfield', 'heading' => esc_html__( 'Number', 'campo' ), 'preview' => true ),
				array( 'param_name' => 'text', 'type' => 'textarea', 'heading' => esc_html__( 'Text', 'campo' ) ),

				array( 'param_name' => 'size', 'type' => 'dropdown', 'heading' => esc_html__( 'Size', 'campo' ), 'preview' => true, 'responsive_override' => true,
					'value' => array(
						esc_html__( 'Extra small', 'campo' ) 	=> 'xsmall',
						esc_html__( 'Small', 'campo' ) 		=> 'small',
						esc_html__( 'Normal', 'campo' ) 		=> 'normal',
						esc_html__( 'Large', 'campo' ) 		=> 'large',
						esc_html__( 'Extra large', 'campo' ) 	=> 'xlarge'		
				) ),
				array( 'param_name' => 'icon_color_scheme', 'type' => 'dropdown', 'heading' => esc_html__( 'Icon color scheme', 'campo' ), 'value' => $color_scheme_arr, 'description' => esc_html__( 'Choose icon colors - Define color schemes in Bold Builder settings or define accent and alternate colors in theme customizer (if avaliable)', 'campo' ) )
			)
		) );
	}
}