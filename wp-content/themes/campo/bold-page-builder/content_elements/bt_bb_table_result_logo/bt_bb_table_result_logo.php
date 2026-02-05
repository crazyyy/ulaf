<?php

class bt_bb_table_result_logo extends BT_BB_Element {

	function handle_shortcode( $atts, $content ) {
		extract( shortcode_atts( apply_filters( 'bt_bb_extract_atts_' . $this->shortcode, array(
			'team'  	=> '',
			'logo'      => ''
		) ), $atts, $this->shortcode ) );
		
		$class = array( $this->shortcode );  
		
		if ( $el_class != '' ) {
			$class[] = $el_class;
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

		if ( $logo != '' ) {
			if ( is_numeric( $logo ) ) {
				$logo = wp_get_attachment_image_src( $logo, 'thumbnail' );
				$logo = $logo[0];
			} else {
				$logo = esc_url_raw( $logo );
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

		$output = '';

		$output .= '#replace_id .bt_bb_table_value span[data-value="' . esc_attr( $team ) . '"]:before {  content: " "; background-image: url("' . $logo . '"); } ';
		$output .= '#replace_id .bt_bb_result_value span[data-value="' . esc_attr( $team ) . '"]:before {  content: " "; background-image: url("' . $logo . '"); } ';
		
		$output = '<div class="' . implode( ' ', $class ) . '">' . $output . '</div>';
		
		$output = apply_filters( 'bt_bb_general_output', $output, $atts );
		$output = apply_filters( $this->shortcode . '_output', $output, $atts );

		return $output;
	}

	function map_shortcode() {
		bt_bb_map( $this->shortcode, array( 'name' => esc_html__( 'Team logo', 'campo' ), 'description' => esc_html__( 'Team logos in standings table', 'campo' ), 'icon' => $this->prefix_backend . 'icon' . '_' . $this->shortcode, 'responsive_override' => false, 'as_child' => array( 'only' => 'bt_bb_table, bt_bb_result' ),
			'params' => array(
				array( 'param_name' => 'team', 'type' => 'textfield', 'heading' => esc_html__( 'Team name', 'campo' ), 'preview' => true ),
				array( 'param_name' => 'logo', 'type' => 'attach_image', 'heading' => esc_html__( 'Team logo', 'campo' ), 'preview' => true )
			)
		) );
	}
}