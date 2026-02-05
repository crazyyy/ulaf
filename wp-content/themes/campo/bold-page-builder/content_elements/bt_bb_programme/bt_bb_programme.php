<?php

class bt_bb_programme extends BT_BB_Element {

	function handle_shortcode( $atts, $content ) {
		extract( shortcode_atts( apply_filters( 'bt_bb_extract_atts_' . $this->shortcode, array(
			'location'   			=> '',
			'time'       	 		=> '',
			'title'       	 		=> '',
			'icon'         			=> '',
			'title_size'   			=> ''

		) ), $atts, $this->shortcode ) );

		$title = html_entity_decode( $title, ENT_QUOTES, 'UTF-8' );

		$class = array( $this->shortcode );

		if ( $el_class != '' ) {
			$class[] = $el_class;
		}

		$id_attr = '';
		if ( $el_id != '' ) {
			$id_attr = ' ' . 'id="' . esc_attr( $el_id ) . '"';
		}

		if ( $title_size != '' ) {
			$class[] = $this->prefix . 'title_size' . '_' . $title_size;
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

		$output = '<div' . $id_attr . ' class="' . esc_attr( $class_attr ) . '" ' . $style_attr . '>';

			if ( $icon != '' ) $output .= '<div class="' . esc_attr( $this->shortcode . '_icon' ) . '">' . do_shortcode( '[bt_bb_icon icon="' . esc_attr( $icon ) . '" size="large" style="borderless" ignore_fe_editor="true"]' ) . '</div>';

			$output .=  '<div class="' . esc_attr( $this->shortcode . '_content') . '">';

				if ( $location != '' ) $output .= '<div class="' . esc_attr( $this->shortcode ) . '_location">' . do_shortcode( '[bt_bb_icon size="xsmall" icon="remixiconsmap_e947" style="borderless" text="' . esc_attr( $location ) . '" text_size="16px" ignore_fe_editor="true"]' ) . '</div>';

				$output .=  '<div class="' . esc_attr( $this->shortcode . '_content_inner') . '">';
					if ( $time != '' ) $output .= '<div class="' . esc_attr( $this->shortcode ) . '_time">' . $time . '</div>';
					if ( $title != '' ) $output .= '<div class="' . esc_attr( $this->shortcode ) . '_title">' . $title . '</div>';
				$output .= '</div>';

			$output .= '</div>';
			
		$output .= '</div>';

		
		$output = apply_filters( 'bt_bb_general_output', $output, $atts );
		$output = apply_filters( $this->shortcode . '_output', $output, $atts );
			
		return $output;

	}


	function map_shortcode() {

		if ( function_exists('boldthemes_get_icon_fonts_bb_array') ) {
			$icon_arr = boldthemes_get_icon_fonts_bb_array();
		} else {
			require_once( dirname(__FILE__) . '/../../content_elements_misc/fa_icons.php' );
			require_once( dirname(__FILE__) . '/../../content_elements_misc/s7_icons.php' );
			$icon_arr = array( 'Font Awesome' => bt_bb_fa_icons(), 'S7' => bt_bb_s7_icons() );
		}

		bt_bb_map( $this->shortcode, array( 'name' => esc_html__( 'Programme', 'campo' ), 'description' => esc_html__( 'Programme with icon and title', 'campo' ), 'icon' => $this->prefix_backend . 'icon' . '_' . $this->shortcode, 'highlight' => true,
			'params' => array(
				array( 'param_name' => 'location', 'type' => 'textfield', 'heading' => esc_html__( 'Location', 'campo' ) ),
				array( 'param_name' => 'time', 'type' => 'textfield', 'heading' => esc_html__( 'Time', 'campo' ) ),
				array( 'param_name' => 'title', 'preview' => true, 'type' => 'textfield', 'heading' => esc_html__( 'Title', 'campo' ) ),
				array( 'param_name' => 'icon', 'type' => 'iconpicker', 'heading' => esc_html__( 'Icon', 'campo' ), 'value' => $icon_arr, 'preview' => true ),
				array( 'param_name' => 'title_size', 'type' => 'dropdown', 'heading' => esc_html__( 'Title size', 'campo' ), 
					'value' => array(
						esc_html__( 'Small', 'campo' ) 			=> '',
						esc_html__( 'Normal', 'campo' ) 			=> 'normal',
						esc_html__( 'Large', 'campo' ) 			=> 'large'
					)
				),
			))
		);
	}
}