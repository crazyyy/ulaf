<?php

class bt_bb_card_image extends BT_BB_Element {

	function handle_shortcode( $atts, $content ) {
		extract( shortcode_atts( apply_filters( 'bt_bb_extract_atts', array(
			'image'      					=> '',
			'size'							=> '',
			'alignment'  					=> '',
			
			'url'    						=> '',
			'target' 						=> '',
			
			'color_scheme' 					=> '',
			'background_color'   			=> '',
			'border'   						=> '',
			'padding'                		=> '',
			'blur'                			=> '',
			'hover_style'					=> '',
			'background_overlay'			=> '',
			'shape'							=> '',
			'shadow'						=> '',
			'hover_image'      				=> '',

			'tag_text'      				=> ''
			
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

		if ( $image == '' ) {
			$class[] = 'btNoImage';
		}

		if ( $hover_image != '' ) {
			$class[] = 'btWithHoverImage';
		}

		if ( $url != '' ) {
			$class[] = 'btWithLink';
		}

		if ( $blur != '' ) {
			$class[] = 'bt_bb_blur_background';
		}

		if ( $shadow != '' ) {
			$class[] = $this->prefix . 'shadow' . '_' . $shadow;
		}

		if ( $alignment != '' ) {
			$class[] = $this->prefix . 'alignment' . '_' . $alignment;
		}

		if ( $background_color != '' ) {
			$el_style = $el_style . 'background-color:' . $background_color . ';';
		}

		$this->responsive_data_override_class(
			$class, $data_override_class,
			array(
				'prefix' => $this->prefix,
				'param' => 'padding',
				'value' => $padding
			)
		);
		
		$color_scheme_id = NULL;
		if ( is_numeric ( $color_scheme ) ) {
			$color_scheme_id = $color_scheme;
		} else if ( $color_scheme != '' ) {
			$color_scheme_id = bt_bb_get_color_scheme_id( $color_scheme );
		}
		$color_scheme_colors = bt_bb_get_color_scheme_colors_by_id( $color_scheme_id - 1 );
		if ( $color_scheme_colors ) $el_style .= '; --card-image-primary-color:' . $color_scheme_colors[0] . '; --card-image-secondary-color:' . $color_scheme_colors[1] . ';';
		if ( $color_scheme != '' ) $class[] = $this->prefix . 'color_scheme_' .  $color_scheme_id;

		if ( $background_overlay != '' ) {
			$class[] = $this->prefix . 'background_overlay' . '_' . $background_overlay;
		}

		if ( $shape != '' ) {
			$class[] = $this->prefix . 'shape' . '_' . $shape;
		}

		if ( $hover_style != '' ) {
			$class[] = $this->prefix . 'hover_style' . '_' . $hover_style;
		}

		if ( $border != '' ) {
			$class[] = $this->prefix . 'border' . '_' . $border;
		}

		$style_attr = '';
		if ( $el_style != '' ) {
			$style_attr = ' ' . 'style="' . esc_attr( $el_style ) . '"';
		}

		$el_bg_style = '';
		if ( $background_color != '' ) {
			if ( strpos( $background_color, '#' ) !== false ) {
				$background_color = bt_bb_hex2rgb( $background_color );
				if ( $opacity == '' ) {
					$opacity = 1;
				}
				$el_bg_style .= 'background-color:rgba(' . $background_color[0] . ', ' . $background_color[1] . ', ' . $background_color[2] . ', ' . $opacity . ');';
			} else {
				$el_bg_style .= 'background-color:' . $background_color . ';';
			}
		}

		$content = do_shortcode( $content );

		$link = bt_bb_get_permalink_by_slug( $url );


		$class = apply_filters( $this->shortcode . '_class', $class, $atts );
		$class_attr = implode( ' ', $class );

		if ( $el_class != '' ) {
			$class_attr = $class_attr . ' ' . $el_class;
		}

		$background_style_attr = '';
		if ( $el_bg_style != '' ) {
			$background_style_attr = ' ' . 'style="' . esc_attr( $el_bg_style ) . '"';
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
				$output .= '<a href="' . esc_url( $link ) . '" ' . $target_attr . ' class="btCardLink"></a>';
			}

			// TEXT
			if ( $tag_text != '' ) $output .= '<span class="' . esc_attr( $this->shortcode . '_tag_text' ) . '">' . $tag_text . '</span>';

			// BACKGROUND IMAGE
			if ( $image != '' ) {
				$output .=  '<div class="' . esc_attr( $this->shortcode . '_image') . '">' . do_shortcode( '[bt_bb_image image="' . esc_attr( $image ) . '" size="' . esc_attr( $size ) . '" lazy_load="no" ignore_fe_editor="true"]' ) . '</div>';	
			}

			// HOVER BACKGROUND IMAGE
			if ( $hover_image != '' ) {
				$output .=  '<div class="' . esc_attr( $this->shortcode . '_hover_image') . '">' . do_shortcode( '[bt_bb_image image="' . esc_attr( $hover_image ) . '" size="' . esc_attr( $size ) . '" lazy_load="no" ignore_fe_editor="true"]' ) . '</div>';	
			}

			// INNER CONTENT
			if ( $content != '' ) $output .= '<div class="' . esc_attr( $this->shortcode . '_content_inner' ) . '">' . ( $content ) . '</div>';

		$output .= '</div>';


		$output = apply_filters( 'bt_bb_general_output', $output, $atts );
		$output = apply_filters( $this->shortcode . '_output', $output, $atts );

		return $output;

	}

	function map_shortcode() {

		$color_scheme_arr = bt_bb_get_color_scheme_param_array();
		
		bt_bb_map( $this->shortcode, array( 'name' => esc_html__( 'Card Image', 'campo' ), 'description' => esc_html__( 'Card with image and text', 'campo' ), 'icon' => $this->prefix_backend . 'icon' . '_' . $this->shortcode, 'container' => 'vertical', 'toggle' => true, 'accept' => array( 'bt_bb_button' => true, 'bt_bb_headline' => true, 'bt_bb_icon' => true, 'bt_bb_text' => true, 'bt_bb_separator' => true, 'bt_bb_special_headline' => true, 'bt_bb_quote' => true, 'bt_bb_service' => true, 'bt_bb_image' => true ),
			'params' => array(
				array( 'param_name' => 'image', 'type' => 'attach_image', 'preview' => true, 'heading' => esc_html__( 'Background image', 'campo' ) 
				),
				array( 'param_name' => 'size', 'type' => 'dropdown', 'heading' => esc_html__( 'Background image size', 'campo' ), 
					'value' => bt_bb_get_image_sizes()
				),
				array( 'param_name' => 'alignment', 'default' => '', 'type' => 'dropdown', 'heading' => esc_html__( 'Content alignment', 'campo' ), 
					'value' => array(
						esc_html__( 'Top', 'campo' ) 				=> '',
						esc_html__( 'Middle', 'campo' ) 			=> 'middle',
						esc_html__( 'Bottom', 'campo' ) 			=> 'bottom'
					)
				),
				array( 'param_name' => 'padding', 'type' => 'dropdown', 'default' => '', 'heading' => esc_html__( 'Content padding', 'campo' ), 'preview' => true, 'responsive_override' => true,
					'value' => array(
						esc_html__( 'Default', 'campo' ) 			=> 'none',
						esc_html__( 'Normal', 'campo' ) 			=> 'normal',
						esc_html__( 'Double', 'campo' ) 			=> 'double',
						esc_html__( 'Text Indent', 'campo' ) 		=> 'text_indent',
						esc_html__( '0px', 'campo' ) 				=> '0px',
						esc_html__( '5px', 'campo' ) 				=> '5px',
						esc_html__( '10px', 'campo' ) 			=> '10px',
						esc_html__( '15px', 'campo' ) 			=> '15px',
						esc_html__( '20px', 'campo' ) 			=> '20px',
						esc_html__( '25px', 'campo' ) 			=> '25px',
						esc_html__( '30px', 'campo' ) 			=> '30px',
						esc_html__( '35px', 'campo' ) 			=> '35px',
						esc_html__( '40px', 'campo' ) 			=> '40px',
						esc_html__( '45px', 'campo' ) 			=> '45px',
						esc_html__( '50px', 'campo' ) 			=> '50px',
						esc_html__( '55px', 'campo' ) 			=> '55px',
						esc_html__( '60px', 'campo' ) 			=> '60px',
						esc_html__( '65px', 'campo' ) 			=> '65px',
						esc_html__( '70px', 'campo' ) 			=> '70px',
						esc_html__( '75px', 'campo' ) 			=> '75px',
						esc_html__( '80px', 'campo' ) 			=> '80px',
						esc_html__( '85px', 'campo' ) 			=> '85px',
						esc_html__( '90px', 'campo' ) 			=> '90px',
						esc_html__( '95px', 'campo' ) 			=> '95px',
						esc_html__( '100px', 'campo' ) 			=> '100px'
					)
				),
				array( 'param_name' => 'tag_text', 'type' => 'textfield', 'heading' => esc_html__( 'Tag text', 'campo' ) ),

				array( 'param_name' => 'url', 'type' => 'link', 'heading' => esc_html__( 'URL', 'campo' ), 'preview' => true, 'description' => esc_html__( 'Enter full or local URL (e.g. https://www.bold-themes.com or /pages/about-us), post slug (e.g. about-us), #lightbox to open current image in full size or search for existing content.', 'campo' ), 'group' => esc_html__( 'URL', 'campo' ) ),
				array( 'param_name' => 'target', 'type' => 'dropdown', 'group' => esc_html__( 'URL', 'campo' ), 'heading' => esc_html__( 'Target', 'campo' ),
					'value' => array(
						esc_html__( 'Self (open in same tab)', 'campo' ) 	=> '_self',
						esc_html__( 'Blank (open in new tab)', 'campo' ) 	=> '_blank',
					)
				),
				
				
				array( 'param_name' => 'color_scheme', 'type' => 'dropdown', 'heading' => esc_html__( 'Color scheme', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ), 'value' => $color_scheme_arr, 'preview' => true ),

				array( 'param_name' => 'background_color', 'preview' => true, 'type' => 'colorpicker', 'heading' => esc_html__( 'Background color', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ) ),
				array( 'param_name' => 'border', 'default' => '', 'type' => 'dropdown', 'heading' => esc_html__( 'Border', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ),
					'value' => array(
						esc_html__( 'No', 'campo' ) 								=> '',
						esc_html__( 'Gray solid border (1px)', 'campo' ) 			=> 'gray'
					)
				),

				array( 'param_name' => 'shape', 'type' => 'dropdown', 'heading' => esc_html__( 'Shape', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ),
					'value' => array(
						esc_html__( 'Square', 'campo' ) 					=> '',
						esc_html__( 'Soft Rounded', 'campo' ) 			=> 'soft-rounded',
						esc_html__( 'Hard Rounded', 'campo' ) 			=> 'hard-rounded'
					) 
				),
				array( 'param_name' => 'blur', 'type' => 'dropdown', 'default' => '', 'group' => esc_html__( 'Design', 'campo' ), 'heading' => esc_html__( 'Blur background image', 'campo' ),
					'value' => array(
						esc_html__( 'No', 'campo' ) 						=> '',
						esc_html__( 'Yes', 'campo' )						=> 'show'
					)
				),
				array( 'param_name' => 'background_overlay', 'type' => 'dropdown', 'heading' => esc_html__( 'Background overlay', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ), 
					'value' => array(
						esc_html__( 'No overlay', 'campo' )    			=> '',
						esc_html__( 'Light bottom gradient', 'campo' )	=> 'light_gradient',
						esc_html__( 'Dark bottom gradient', 'campo' )		=> 'dark_gradient'
					)
				),
				array( 'param_name' => 'shadow', 'type' => 'dropdown', 'heading' => esc_html__( 'Shadow', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ),
					'value' => array(
						esc_html__( 'Hide', 'campo' ) 			=> '',
						esc_html__( 'Show', 'campo' ) 			=> 'show',
						esc_html__( 'Show on hover', 'campo' ) 	=> 'on_hover',
						esc_html__( 'Show & zoom on hover', 'campo' ) 	=> 'on_hover_zoom'
					)
				),
				array( 'param_name' => 'hover_style', 'type' => 'dropdown', 'heading' => esc_html__( 'Hover style', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ), 'description' => esc_html__( 'Choose background image hover style.', 'campo' ),
					'value' => array(
						esc_html__( 'None', 'campo' ) 						=> '',
						esc_html__( 'Accent bottom gradient', 'campo' ) 		=> 'accent_bottom_gradient',
						esc_html__( 'Alternate bottom gradient', 'campo' ) 	=> 'alternate_bottom_gradient',
						esc_html__( 'Light bottom gradient', 'campo' ) 		=> 'light_bottom_gradient',
						esc_html__( 'Dark bottom gradient', 'campo' ) 		=> 'dark_bottom_gradient'
					)
				),
				array( 'param_name' => 'hover_image', 'type' => 'attach_image', 'group' => esc_html__( 'Design', 'campo' ), 'heading' => esc_html__( 'Hover background image', 'campo' ) 
				),
				
				)
			)
		);
	}
}