<?php

class bt_bb_single_score_item extends BT_BB_Element {

	function handle_shortcode( $atts, $content ) {
		extract( shortcode_atts( apply_filters( 'bt_bb_extract_atts', array(
			'score'      					=> '',
			'text'      					=> ''
			
		) ), $atts, $this->shortcode ) );
		
		$class = array( $this->shortcode );
		$data_override_class = array();

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

		$class = apply_filters( $this->shortcode . '_class', $class, $atts );
		$class_attr = implode( ' ', $class );

		if ( $el_class != '' ) {
			$class_attr = $class_attr . ' ' . $el_class;
		}

		$style_attr = '';
		if ( $el_style != '' ) {
			$style_attr = ' ' . 'style="' . esc_attr( $el_style ) . '"';
		}

		$output = '<div' . $id_attr . ' class="' . esc_attr( $class_attr ) . '" ' . $style_attr . ' data-bt-override-class="' . htmlspecialchars( json_encode( $data_override_class, JSON_FORCE_OBJECT ), ENT_QUOTES, 'UTF-8' ) . '">';

			// SCORE
			$output .= '<div class="' . esc_attr( $this->shortcode . '_score' ) . '">';
				if ( $score != '' ) $output .= '<div class="' . esc_attr( $this->shortcode . '_score_number' ) . '">' . do_shortcode( '[bt_bb_counter number="' .  esc_attr( $score ) . '" ignore_fe_editor="true"]' ) . '</div>';
				if ( $text != '' ) $output .= '<span class="' . esc_attr( $this->shortcode . '_text' ) . '">' . $text . '</span>';
			$output .= '</div>';

		$output .= '</div>';


		$output = apply_filters( 'bt_bb_general_output', $output, $atts );
		$output = apply_filters( $this->shortcode . '_output', $output, $atts );

		return $output;

	}

	function map_shortcode() {
		
		bt_bb_map( $this->shortcode, array( 'name' => esc_html__( 'Single Score Item', 'campo' ), 'description' => esc_html__( 'Single score Item with text', 'campo' ), 'as_child' => array( 'only' => 'bt_bb_single_score' ), 'icon' => $this->prefix_backend . 'icon' . '_' . $this->shortcode, 'accept' => array( 'bt_bb_section' => false, 'bt_bb_row' => false, 'bt_bb_column' => false, 'bt_bb_column_inner' => false, 'bt_bb_tabs' => false, 'bt_bb_tab_item' => false, 'bt_bb_accordion' => false, 'bt_bb_accordion_item' => false, 'bt_bb_cost_calculator_item' => false, 'bt_cc_group' => false, 'bt_cc_multiply' => false, 'bt_cc_item' => false, 'bt_bb_content_slider_item' => false, 'bt_bb_google_maps_location' => false, '_content' => false ), 'as_child' => array( 'only' => 'bt_bb_single_score' ),
			'params' => array(
				array( 'param_name' => 'score', 'type' => 'textfield', 'preview' => true, 'heading' => esc_html__( 'Score', 'campo' ) ),
				array( 'param_name' => 'text', 'type' => 'textfield', 'preview' => true, 'heading' => esc_html__( 'Text', 'campo' ) ),
			) )
		);
	}
}