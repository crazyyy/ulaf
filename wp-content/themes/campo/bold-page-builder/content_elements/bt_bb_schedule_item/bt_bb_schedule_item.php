<?php

class bt_bb_schedule_item extends BT_BB_Element {

	function handle_shortcode( $atts, $content ) {
		extract( shortcode_atts( apply_filters( 'bt_bb_extract_atts_' . $this->shortcode, array(
			'title' 			=> '',
			'selected_day'		=> ''
		) ), $atts, $this->shortcode ) );
		
		$class = array( $this->shortcode );

		if ( $el_class != '' ) {
			$class[] = $el_class;
		}

		$id_attr = '';
		if ( $el_id != '' ) {
			$id_attr = ' ' . 'id="' . esc_attr( $el_id ) . '"';
		}

		if ( $selected_day != '' ) {
			$selected_day_arr = preg_split( '/\s+/', $selected_day );
			if ( !empty( $selected_day_arr ) ) {
				if ( in_array( strtolower(date('D')), array_map('strtolower', $selected_day_arr) ) ){
					$class[] = 'btCurrrentDay';
				}
			}
		}

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

		$output = '<div class="' . esc_attr( $class_attr ) . '" ' . $style_attr . '>';
			if ( $title != '' ) $output .= '<div class="' . esc_attr( $this->shortcode ) . '_title">' . $title . '</div>';
			if ( $content != '' ) $output .= '<div class="bt_bb_schedule_item_content">' . wpautop( do_shortcode( $content ) ) . '</div>';
		$output .= '</div>';
		
		$output = apply_filters( 'bt_bb_general_output', $output, $atts );
		$output = apply_filters( $this->shortcode . '_output', $output, $atts );
		
		return $output;

	}
	
	function add_params() {
		// removes default params from BT_BB_Element
	}

	function map_shortcode() {
		bt_bb_map( $this->shortcode, array( 'name' => esc_html__( 'Schedule Item', 'campo' ), 'description' => esc_html__( 'Single schedule element', 'campo' ), 'container' => 'vertical', 'toggle' => true, 'accept' => array( 'bt_bb_section' => false, 'bt_bb_row' => false, 'bt_bb_column' => false, 'bt_bb_column_inner' => false, 'bt_bb_tabs' => false, 'bt_bb_tab_item' => false, 'bt_bb_accordion' => false, 'bt_bb_accordion_item' => false, 'bt_bb_cost_calculator_item' => false, 'bt_cc_group' => false, 'bt_cc_multiply' => false, 'bt_cc_item' => false, 'bt_bb_content_slider_item' => false, 'bt_bb_google_maps_location' => false, '_content' => false ), 'accept_all' => true, 'as_child' => array( 'only' => 'bt_bb_schedule' ), 'icon' => $this->prefix_backend . 'icon' . '_' . $this->shortcode,
			'params' => array(
				array( 'param_name' => 'title', 'type' => 'textfield', 'heading' => esc_html__( 'Title', 'campo' ), 'preview' => true, 'preview_strong' => true ),
				array( 'param_name' => 'selected_day', 'type' => 'dropdown', 'heading' => esc_html__( 'Selected day', 'campo' ), 'preview' => true,
					'value' => array(
						esc_html__( 'None', 'campo' )			=> '',
						esc_html__( 'Monday', 'campo' )		=> 'mon',
						esc_html__( 'Tuesday', 'campo' )		=> 'tue',
						esc_html__( 'Wednesday', 'campo' )	=> 'wed',
						esc_html__( 'Thursday', 'campo' )		=> 'thu',
						esc_html__( 'Friday', 'campo' )		=> 'fri',
						esc_html__( 'Saturday', 'campo' )		=> 'sat',
						esc_html__( 'Sunday', 'campo' )		=> 'sun',
					)
				),
			)
		) );
	}
}