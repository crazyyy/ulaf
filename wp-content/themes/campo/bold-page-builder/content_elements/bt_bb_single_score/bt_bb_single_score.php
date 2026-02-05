<?php

class bt_bb_single_score extends BT_BB_Element {

	function handle_shortcode( $atts, $content ) {
		extract( shortcode_atts( apply_filters( 'bt_bb_extract_atts', array(
			'image'      				=> '',
			'supertitle'      				=> '',
			'title'      					=> '',
			'subtitle'      				=> '',
			'html_tag'						=> 'h4',
			'url'    						=> '',
			'target' 						=> '',
			'shape'							=> '',
			'shadow'						=> '',
			'size'     						=> 'medium',
			'border'						=> '',
			'border_color'					=> '',
			'gradient'						=> 'light'
			
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

		if ( $gradient != '' ) {
			$class[] = $this->prefix . 'gradient' . '_' . $gradient;
		}

		if ( $border != '' ) {
			$class[] = $this->prefix . 'border' . '_' . $border;
		}

		if ( $border_color != '' ) {
			$class[] = $this->prefix . 'border_color' . '_' . $border_color;
		}

		if ( $url != '' ) {
			$class[] = 'btWithLink';
		}

		$link = bt_bb_get_permalink_by_slug( $url );
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

			// LINK
			if ( $link != '' ) {
				$target_attr = ' target="_self" ';
				if ( $target != '' ) {
					$target_attr = ' ' . 'target="' . esc_attr( $target ) . '"';
				}
				$output .= '<a href="' . esc_url( $link ) . '" ' . $target_attr . ' class="bt_bb_single_score_link"></a>';
			}

			// IMAGE 
			if ( $image != '' ) $output .=  '<div class="' . esc_attr( $this->shortcode . '_image') . '">' . do_shortcode( '[bt_bb_image image="' . esc_attr( $image ) . '" size="boldthemes_small" ignore_fe_editor="true"]' ) . '</div>';

			// TITLE
			if ( $title != '' ) $output .= '<div class="' . esc_attr( $this->shortcode . '_title' ) . '">' . do_shortcode('[bt_bb_headline headline="' . esc_attr( $title ) . '" superheadline="' . esc_attr( $supertitle ) . '" subheadline="' . esc_attr( $subtitle ) . '" html_tag="'. esc_attr( $html_tag ) .'" size="' . esc_attr( $size ) . '" ignore_fe_editor="true"]' ) . '</div>';


			$output .=  '<div class="' . esc_attr( $this->shortcode . '_content') . '">' . $content . '</div>';

		$output .= '</div>';


		$output = apply_filters( 'bt_bb_general_output', $output, $atts );
		$output = apply_filters( $this->shortcode . '_output', $output, $atts );

		return $output;

	}

	function map_shortcode() {
		
		bt_bb_map( $this->shortcode, array( 'name' => esc_html__( 'Single Score', 'campo' ), 'description' => esc_html__( 'Single score with image and text', 'campo' ), 'icon' => $this->prefix_backend . 'icon' . '_' . $this->shortcode, 'accept' => array( 'bt_bb_single_score_item' => true ), 'container' => 'vertical',
			'params' => array(
				array( 'param_name' => 'image', 'type' => 'attach_image', 'preview' => true, 'heading' => esc_html__( 'Image', 'campo' ) ),
				array( 'param_name' => 'supertitle', 'type' => 'textfield', 'heading' => esc_html__( 'Superheadline', 'campo' ) ),
				array( 'param_name' => 'title', 'type' => 'textarea', 'preview' => true, 'heading' => esc_html__( 'Title', 'campo' ) ),
				array( 'param_name' => 'subtitle', 'type' => 'textfield', 'heading' => esc_html__( 'Subheadline', 'campo' ) ),
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
				array( 'param_name' => 'size', 'type' => 'dropdown', 'default' => 'medium', 'group' => esc_html__( 'Design', 'campo' ), 'heading' => esc_html__( 'Size', 'campo' ), 'description' => esc_html__( 'Predefined heading sizes, independent of html tag', 'campo' ), 'responsive_override' => true,
					'value' => array(
						esc_html__( 'Inherit', 'campo' ) 		=> 'inherit',
						esc_html__( 'Extra Small', 'campo' ) 	=> 'extrasmall',
						esc_html__( 'Small', 'campo' ) 		=> 'small',
						esc_html__( 'Medium', 'campo' ) 		=> 'medium',
						esc_html__( 'Normal', 'campo' ) 		=> 'normal'
					)
				),
				array( 'param_name' => 'border', 'default' => '', 'type' => 'dropdown', 'heading' => esc_html__( 'Border', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ),
					'value' => array(
						esc_html__( 'No', 'campo' ) 			=> '',
						esc_html__( 'Yes', 'campo' ) 			=> 'show'
					)
				),
				array( 'param_name' => 'border_color', 'type' => 'dropdown', 'heading' => esc_html__( 'Border color', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ),
					'value' => array(
						esc_html__( 'Gray color', 'campo' ) 			=> 'gray',
						esc_html__( 'Accent color', 'campo' ) 		=> 'accent',
						esc_html__( 'Alternate color', 'campo' ) 		=> 'alternate',
						esc_html__( 'Light color', 'campo' ) 			=> 'light',
						esc_html__( 'Dark color', 'campo' ) 			=> 'dark'
					)
				),
				array( 'param_name' => 'gradient', 'type' => 'dropdown', 'default' => 'light', 'group' => esc_html__( 'Design', 'campo' ), 'heading' => esc_html__( 'Background gradient', 'campo' ),
					'value' => array(
						esc_html__( 'No', 'campo' ) 						=> '',
						esc_html__( 'Light gradient', 'campo' )			=> 'light',
						esc_html__( 'Dark gradient', 'campo' )			=> 'dark'
					)
				),
			) )
		);
	}
}