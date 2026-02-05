<?php

class bt_bb_schedule extends BT_BB_Element {

	function handle_shortcode( $atts, $content ) {
		extract( shortcode_atts( apply_filters( 'bt_bb_extract_atts_' . $this->shortcode, array(
			'color_scheme' 			=> '',
			'style'        			=> '',
			'shape'        			=> '',
			'current_color_scheme' 	=> ''
		) ), $atts, $this->shortcode ) );
		
		$content_elements_path = get_parent_theme_file_uri( 'bold-page-builder/content_elements/bt_bb_schedule/' );

		wp_enqueue_script( 
			'bt_bb_schedule_js',
			$content_elements_path . 'bt_bb_schedule.js',
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
		
		$color_scheme_id = NULL;
		if ( is_numeric ( $color_scheme ) ) {
			$color_scheme_id = $color_scheme;
		} else if ( $color_scheme != '' ) {
			$color_scheme_id = bt_bb_get_color_scheme_id( $color_scheme );
		}
		$color_scheme_colors = bt_bb_get_color_scheme_colors_by_id( $color_scheme_id - 1 );
		if ( $color_scheme_colors ) $el_style .= '; --schedule-primary-color:' . $color_scheme_colors[0] . '; --schedule-secondary-color:' . $color_scheme_colors[1] . ';';
		if ( $color_scheme != '' ) $class[] = $this->prefix . 'color_scheme_' .  $color_scheme_id;


		$current_color_scheme_id = NULL;
		if ( is_numeric ( $current_color_scheme ) ) {
			$current_color_scheme_id = $current_color_scheme;
		} else if ( $current_color_scheme != '' ) {
			$current_color_scheme_id = bt_bb_get_color_scheme_id( $current_color_scheme );
		}
		$current_color_scheme_colors = bt_bb_get_color_scheme_colors_by_id( $current_color_scheme_id - 1 );
		if ( $current_color_scheme_colors ) $el_style .= '; --current-primary-color:' . $current_color_scheme_colors[0] . '; --current-secondary-color:' . $current_color_scheme_colors[1] . ';';
		if ( $current_color_scheme != '' ) $class[] = $this->prefix . 'current_color_scheme_' .  $current_color_scheme_id;

		$style_attr = '';
		$el_style = apply_filters( $this->shortcode . '_style', $el_style, $atts );
		if ( $el_style != '' ) {
			$style_attr = ' ' . 'style="' . esc_attr( $el_style ) . '"';
		}

		if ( $style != '' ) {
			$class[] = $this->prefix . 'style' . '_' . $style;
		}		

		if ( $shape != '' ) {
			$class[] = $this->prefix . 'shape' . '_' . $shape;
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

		$content = do_shortcode( $content );

		$output = '';

		$output .= '<div' . $id_attr . ' class="' . implode( ' ', $class ) . '"' . $style_attr . '>' . $content . '</div>';
		
		$output = apply_filters( 'bt_bb_general_output', $output, $atts );
		$output = apply_filters( $this->shortcode . '_output', $output, $atts );

		return $output;

	}

	function map_shortcode() {
		
		$color_scheme_arr = bt_bb_get_color_scheme_param_array();			
		
		bt_bb_map( $this->shortcode, array( 'name' => esc_html__( 'Schedule', 'campo' ), 'description' => esc_html__( 'Schedule container', 'campo' ), 'container' => 'vertical', 'toggle' => true, 'accept' => array( 'bt_bb_schedule_item' => true ), 'icon' => $this->prefix_backend . 'icon' . '_' . $this->shortcode,
			'params' => array(
				array( 'param_name' => 'color_scheme', 'type' => 'dropdown', 'heading' => esc_html__( 'Color scheme', 'campo' ), 'description' => esc_html__( 'Define color schemes in Bold Builder settings or define accent and alternate colors in theme customizer (if avaliable)', 'campo' ), 'value' => $color_scheme_arr, 'preview' => true ),
				array( 'param_name' => 'style', 'type' => 'dropdown', 'heading' => esc_html__( 'Style', 'campo' ), 'preview' => true,
					'value' => array(
						esc_html__( 'Outline (1px border tickness)', 'campo' ) 		=> 'outline_1px',
						esc_html__( 'Outline (2px border tickness)', 'campo' ) 		=> 'outline',
						esc_html__( 'Simple', 'campo' ) 								=> 'simple'
					)
				),
				array( 'param_name' => 'shape', 'type' => 'dropdown', 'heading' => esc_html__( 'Shape', 'campo' ),
					'value' => array(
						esc_html__( 'Square', 'campo' ) 			=> 'square',
						esc_html__( 'Soft Rounded', 'campo' ) 	=> 'rounded',
						esc_html__( 'Hard Rounded', 'campo' ) 	=> 'round'
					)
				),
				array( 'param_name' => 'current_color_scheme', 'type' => 'dropdown', 'heading' => esc_html__( 'Current color scheme', 'campo' ), 'description' => esc_html__( 'Define color schemes in Bold Builder settings or define accent and alternate colors in theme customizer (if avaliable)', 'campo' ), 'value' => $color_scheme_arr, 'preview' => true ),
			)
		) );
	}
}