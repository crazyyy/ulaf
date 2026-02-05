<?php

class bt_bb_score extends BT_BB_Element {

	function handle_shortcode( $atts, $content ) {
		extract( shortcode_atts( apply_filters( 'bt_bb_extract_atts', array(
			'day'  							=> '',
			'month'  						=> '',
			'image_01'      				=> '',
			'image_02'      				=> '',
			'score_01'      				=> '',
			'detail_01'      				=> '',
			'score_02'      				=> '',
			'detail_02'      				=> '',

			'shape'							=> '',
			'shadow'						=> '',
			'date_color_scheme' 			=> '',
			'color_scheme' 					=> ''
			
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

		if ( $shape != '' ) {
			$class[] = $this->prefix . 'shape' . '_' . $shape;
		}

		if ( $day == '' && $month == '' ) {
			$class[] = 'btNoDate';
		}

		if ( $shadow != '' ) {
			$class[] = $this->prefix . 'shadow' . '_' . $shadow;
		}


		$date_color_scheme_id = NULL;
		if ( is_numeric ( $date_color_scheme ) ) {
			$date_color_scheme_id = $date_color_scheme;
		} else if ( $date_color_scheme != '' ) {
			$date_color_scheme_id = bt_bb_get_color_scheme_id( $date_color_scheme );
		}
		$date_color_scheme_colors = bt_bb_get_color_scheme_colors_by_id( $date_color_scheme_id - 1 );
		if ( $date_color_scheme_colors ) $el_style .= '; --date-primary-color:' . $date_color_scheme_colors[0] . '; --date-secondary-color:' . $date_color_scheme_colors[1] . ';';
		if ( $date_color_scheme != '' ) $class[] = $this->prefix . 'date_color_scheme_' .  $date_color_scheme_id;



		$color_scheme_id = NULL;
		if ( is_numeric ( $color_scheme ) ) {
			$color_scheme_id = $color_scheme;
		} else if ( $color_scheme != '' ) {
			$color_scheme_id = bt_bb_get_color_scheme_id( $color_scheme );
		}
		$color_scheme_colors = bt_bb_get_color_scheme_colors_by_id( $color_scheme_id - 1 );
		if ( $color_scheme_colors ) $el_style .= '; --score-primary-color:' . $color_scheme_colors[0] . '; --score-secondary-color:' . $color_scheme_colors[1] . ';';
		if ( $color_scheme != '' ) $class[] = $this->prefix . 'color_scheme_' .  $color_scheme_id;


		$content = do_shortcode( $content );


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

			// DAY & MONTH
			$output .= '<div class="' . esc_attr( $this->shortcode . '_date' ) . '">';
				if ( $day != '' ) $output .= '<div class="' . esc_attr( $this->shortcode . '_day' ) . '"><span>' . $day . '</span></div>';
				if ( $month != '' ) $output .= '<div class="' . esc_attr( $this->shortcode . '_month' ) . '"><span>' . $month . '</span></div>';
			$output .= '</div>';

			
			$output .= '<div class="' . esc_attr( $this->shortcode . '_details' ) . '">';
				// IMAGE 01
				if ( $image_01 != '' ) $output .=  '<div class="' . esc_attr( $this->shortcode . '_image_01') . '">' . do_shortcode( '[bt_bb_image image="' . esc_attr( $image_01 ) . '" size="boldthemes_small" ignore_fe_editor="true"]' ) . '</div>';

				// SCORE 01
				$output .= '<div class="' . esc_attr( $this->shortcode . '_score_01' ) . '">';
					if ( $score_01 != '' ) $output .= '<div class="' . esc_attr( $this->shortcode . '_number' ) . '">' . do_shortcode( '[bt_bb_counter number="' .  esc_attr( $score_01 ) . '" ignore_fe_editor="true"]' ) . '</div>';
					if ( $detail_01 != '' ) $output .= '<span class="' . esc_attr( $this->shortcode . '_detail_01' ) . '">' . $detail_01 . '</span>';
				$output .= '</div>';

				// INNER CONTENT
				if ( $content != '' ) $output .= '<div class="' . esc_attr( $this->shortcode . '_content_inner' ) . '">' . ( $content ) . '</div>';
				
				// SCORE 02
				$output .= '<div class="' . esc_attr( $this->shortcode . '_score_02' ) . '">';
					if ( $score_02 != '' ) $output .= '<div class="' . esc_attr( $this->shortcode . '_number' ) . '">' . do_shortcode( '[bt_bb_counter number="' .  esc_attr( $score_02 ) . '" ignore_fe_editor="true"]' ) . '</div>';
					if ( $detail_02 != '' ) $output .= '<span class="' . esc_attr( $this->shortcode . '_detail_02' ) . '">' . $detail_02 . '</span>';
				$output .= '</div>';

				// IMAGE 02
				if ( $image_02 != '' ) $output .=  '<div class="' . esc_attr( $this->shortcode . '_image_02') . '">' . do_shortcode( '[bt_bb_image image="' . esc_attr( $image_02 ) . '" size="boldthemes_small" ignore_fe_editor="true"]' ) . '</div>';

			$output .= '</div>';


		$output .= '</div>';


		$output = apply_filters( 'bt_bb_general_output', $output, $atts );
		$output = apply_filters( $this->shortcode . '_output', $output, $atts );

		return $output;

	}

	function map_shortcode() {

		$color_scheme_arr = bt_bb_get_color_scheme_param_array();
		
		bt_bb_map( $this->shortcode, array( 'name' => esc_html__( 'Score', 'campo' ), 'description' => esc_html__( 'Score with image and text', 'campo' ), 'icon' => $this->prefix_backend . 'icon' . '_' . $this->shortcode, 'container' => 'vertical', 'toggle' => true, 'accept' => array( 'bt_bb_button' => true,  'bt_bb_text' => true, 'bt_bb_separator' => true ),
			'params' => array(
				array( 'param_name' => 'day', 'type' => 'textfield', 'heading' => esc_html__( 'Day', 'campo' ) ),
				array( 'param_name' => 'month', 'type' => 'textfield', 'heading' => esc_html__( 'Month', 'campo' ) ),
				
				array( 'param_name' => 'image_01', 'type' => 'attach_image', 'preview' => true, 'heading' => esc_html__( 'Image 01', 'campo' ) ),
				array( 'param_name' => 'image_02', 'type' => 'attach_image', 'heading' => esc_html__( 'Image 02', 'campo' ) ),
				array( 'param_name' => 'score_01', 'type' => 'textfield', 'preview' => true, 'heading' => esc_html__( 'Score 01', 'campo' ) ),
				array( 'param_name' => 'score_02', 'type' => 'textfield', 'preview' => true, 'heading' => esc_html__( 'Score 02', 'campo' ) ),
				array( 'param_name' => 'detail_01', 'type' => 'textfield', 'heading' => esc_html__( 'Detail 01', 'campo' ) ),
				array( 'param_name' => 'detail_02', 'type' => 'textfield', 'heading' => esc_html__( 'Detail 02', 'campo' ) ),

				array( 'param_name' => 'shape', 'type' => 'dropdown', 'heading' => esc_html__( 'Shape', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ),
					'value' => array(
						esc_html__( 'Square', 'campo' ) 					=> '',
						esc_html__( 'Soft Rounded', 'campo' ) 			=> 'soft-rounded',
						esc_html__( 'Hard Rounded', 'campo' ) 			=> 'hard-rounded'
					) 
				),
				array( 'param_name' => 'shadow', 'type' => 'dropdown', 'heading' => esc_html__( 'Shadow', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ),
					'value' => array(
						esc_html__( 'Hide', 'campo' ) 			=> '',
						esc_html__( 'Show', 'campo' ) 			=> 'show',
						esc_html__( 'Show on hover', 'campo' ) 	=> 'on_hover'
					)
				),
				array( 'param_name' => 'date_color_scheme', 'type' => 'dropdown', 'heading' => esc_html__( 'Date color scheme', 'campo' ), 'value' => $color_scheme_arr, 'description' => esc_html__( 'Choose date colors - Define color schemes in Bold Builder settings or define accent and alternate colors in theme customizer (if avaliable)', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ) ),
				array( 'param_name' => 'color_scheme', 'type' => 'dropdown', 'heading' => esc_html__( 'Color scheme', 'campo' ), 'value' => $color_scheme_arr, 'description' => esc_html__( 'Choose date colors - Define color schemes in Bold Builder settings or define accent and alternate colors in theme customizer (if avaliable)', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ) ),
			) )
		);
	}
}