<?php

class bt_bb_single_event extends BT_BB_Element {

	function handle_shortcode( $atts, $content ) {
		extract( shortcode_atts( apply_filters( 'bt_bb_extract_atts', array(
			'day'  							=> '',
			'month'  						=> '',
			'image'      					=> '',
			'title'      					=> '',
			'subtitle'      				=> '',
			'html_tag'      				=> 'h4',
			'url'    						=> '',
			'target' 						=> '',
			'shape'							=> '',
			'shadow'						=> ''
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

		if ( $shape != '' ) {
			$class[] = $this->prefix . 'shape' . '_' . $shape;
		}

		if ( $shadow != '' ) {
			$class[] = $this->prefix . 'shadow' . '_' . $shadow;
		}

		$link = bt_bb_get_permalink_by_slug( $url );

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

			// LINK
			if ( $link != '' ) {
				$target_attr = ' target="_self" ';
				if ( $target != '' ) {
					$target_attr = ' ' . 'target="' . esc_attr( $target ) . '"';
				}
				$output .= '<a href="' . esc_url( $link ) . '" ' . $target_attr . ' class="bt_bb_event_link"></a>';
			}

			// DAY & MONTH
			$output .= '<div class="' . esc_attr( $this->shortcode . '_date' ) . '">';
				if ( $day != '' ) $output .= '<div class="' . esc_attr( $this->shortcode . '_day' ) . '"><span>' . $day . '</span></div>';
				if ( $month != '' ) $output .= '<div class="' . esc_attr( $this->shortcode . '_month' ) . '"><span>' . $month . '</span></div>';
			$output .= '</div>';

			// IMAGE
			if ( $image != '' ) $output .=  '<div class="' . esc_attr( $this->shortcode . '_image') . '">' . do_shortcode( '[bt_bb_image image="' . esc_attr( $image ) . '" size="boldthemes_small" ignore_fe_editor="true"]' ) . '</div>';

			// TITLE 
			$output .= '<div class="' . esc_attr( $this->shortcode . '_details' ) . '">';
				if ( $title != '' ) $output .= '<div class="' . esc_attr( $this->shortcode . '_title' ) . '">' . do_shortcode('[bt_bb_headline headline="' . esc_attr( $title ) . '" html_tag="'. esc_attr( $html_tag ) .'" size="medium" ignore_fe_editor="true"]' ) . '</div>';
				if ( $subtitle != '' ) $output .= '<div class="' . esc_attr( $this->shortcode . '_subtitle' ) . '"><span>' . $subtitle . '</span></div>';
			$output .= '</div>';
			


		$output .= '</div>';


		$output = apply_filters( 'bt_bb_general_output', $output, $atts );
		$output = apply_filters( $this->shortcode . '_output', $output, $atts );

		return $output;

	}

	function map_shortcode() {
		
		bt_bb_map( $this->shortcode, array( 'name' => esc_html__( 'Single event', 'campo' ), 'description' => esc_html__( 'Single event with details', 'campo' ), 'icon' => $this->prefix_backend . 'icon' . '_' . $this->shortcode,
			'params' => array(
				array( 'param_name' => 'day', 'type' => 'textfield', 'heading' => esc_html__( 'Day', 'campo' ) ),
				array( 'param_name' => 'month', 'type' => 'textfield', 'heading' => esc_html__( 'Month', 'campo' ) ),
				
				array( 'param_name' => 'image', 'type' => 'attach_image', 'preview' => true, 'heading' => esc_html__( 'Image', 'campo' ) ),
				array( 'param_name' => 'title', 'type' => 'textarea', 'preview' => true, 'heading' => esc_html__( 'Title', 'campo' ) ),
				array( 'param_name' => 'subtitle', 'type' => 'textfield', 'heading' => esc_html__( 'Subtitle', 'campo' ) ),

				array( 'param_name' => 'html_tag', 'type' => 'dropdown', 'default' => 'h4', 'heading' => esc_html__( 'HTML title tag', 'campo' ),
					'value' => array(
						esc_html__( 'h1', 'campo' ) 				=> 'h1',
						esc_html__( 'h2', 'campo' )	 			=> 'h2',
						esc_html__( 'h3', 'campo' ) 				=> 'h3',
						esc_html__( 'h4', 'campo' ) 				=> 'h4',
						esc_html__( 'h5', 'campo' ) 				=> 'h5',
						esc_html__( 'h6', 'campo' ) 				=> 'h6'
				) ),

				array( 'param_name' => 'url', 'type' => 'link', 'heading' => esc_html__( 'URL', 'campo' ), 'preview' => true, 'description' => esc_html__( 'Enter full or local URL (e.g. https://www.bold-themes.com or /pages/about-us), post slug (e.g. about-us), #lightbox to open current image in full size or search for existing content.', 'campo' ), 'group' => esc_html__( 'URL', 'campo' ) ),
				array( 'param_name' => 'target', 'type' => 'dropdown', 'group' => esc_html__( 'URL', 'campo' ), 'heading' => esc_html__( 'Target', 'campo' ),
					'value' => array(
						esc_html__( 'Self (open in same tab)', 'campo' ) 	=> '_self',
						esc_html__( 'Blank (open in new tab)', 'campo' ) 	=> '_blank',
					)
				),
				
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
				)
			)
		);
	}
}