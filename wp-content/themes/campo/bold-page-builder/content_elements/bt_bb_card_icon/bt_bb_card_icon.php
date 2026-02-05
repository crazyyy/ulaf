<?php

class bt_bb_card_icon extends BT_BB_Element {

	function handle_shortcode( $atts, $content ) {
		extract( shortcode_atts( apply_filters( 'bt_bb_extract_atts', array(
			'icon'						=> '',
			'title'							=> '',
			'html_tag'						=> 'h4',
			'button_text'					=> '',
			
			'url'							=> '',
			'target' 						=> '',
			
			'background_color'				=> '',
			'color_scheme' 					=> '',
			'blur'							=> '',
			'padding'						=> 'normal',
			'shape'							=> '',
			'gradient'						=> '',
			'shadow'						=> '',
			'icon_color'					=> '',
			'icon_size'						=> 'xhuge',
			'button_icon_color'				=> '',
			'border'						=> '',
			'border_color'					=> '',
			'hover_color_scheme' 			=> ''
			
		) ), $atts, $this->shortcode ) );
		
		$class = array( $this->shortcode );
		$data_override_class = array();

		$title = html_entity_decode( $title, ENT_QUOTES, 'UTF-8' );

		if ( $el_class != '' ) {
			$class[] = $el_class;
		}

		$id_attr = '';
		if ( $el_id != '' ) {
			$id_attr = ' ' . 'id="' . esc_attr( $el_id ) . '"';
		}

		if ( $url != '' ) {
			$class[] = 'btWithLink';
		}

		if ( $background_color != '' ) {
			$el_style = $el_style . 'background-color:' . $background_color . ';';
		}

		if ( $blur != '' ) {
			$class[] = 'bt_bb_blur_background';
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
		if ( $color_scheme_colors ) $el_style .= '; --card-primary-color:' . $color_scheme_colors[0] . '; --card-secondary-color:' . $color_scheme_colors[1] . ';';
		if ( $color_scheme != '' ) $class[] = $this->prefix . 'color_scheme_' .  $color_scheme_id;


		$hover_color_scheme_id = NULL;
		if ( is_numeric ( $hover_color_scheme ) ) {
			$hover_color_scheme_id = $hover_color_scheme;
		} else if ( $hover_color_scheme != '' ) {
			$hover_color_scheme_id = bt_bb_get_color_scheme_id( $hover_color_scheme );
		}
		$hover_color_scheme_colors = bt_bb_get_color_scheme_colors_by_id( $hover_color_scheme_id - 1 );
		if ( $hover_color_scheme_colors ) $el_style .= '; --hover-primary-color:' . $hover_color_scheme_colors[0] . '; --hover-secondary-color:' . $hover_color_scheme_colors[1] . ';';
		if ( $hover_color_scheme != '' ) $class[] = $this->prefix . 'hover_color_scheme_' .  $hover_color_scheme_id;

		if ( $shape != '' ) {
			$class[] = $this->prefix . 'shape' . '_' . $shape;
		}

		if ( $gradient != '' ) {
			$class[] = $this->prefix . 'gradient' . '_' . $gradient;
		}

		if ( $shadow != '' ) {
			$class[] = $this->prefix . 'shadow' . '_' . $shadow;
		}

		if ( $icon_color != '' ) {
			$class[] = $this->prefix . 'icon_color' . '_' . $icon_color;
		}

		if ( $border != '' ) {
			$class[] = $this->prefix . 'border' . '_' . $border;
		}

		if ( $border_color != '' ) {
			$class[] = $this->prefix . 'border_color' . '_' . $border_color;
		}

		if ( $button_icon_color != '' ) {
			$class[] = $this->prefix . 'button_icon_color' . '_' . $button_icon_color;
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
				$output .= '<a href="' . esc_url( $link ) . '" ' . $target_attr . ' class="btCardLink"></a>';
			}

			// ICON
			if ( $icon != '' ) $output .= '<div class="' . esc_attr( $this->shortcode . '_icon' ) . '">' . do_shortcode( '[bt_bb_icon icon="' . esc_attr( $icon ) . '" size="' . esc_attr( $icon_size ) . '" style="borderless" shape="square" ignore_fe_editor="true"]' ) . '</div>';

			// HEADLINE
			if ( $title != '' )	$output .= '<div class="' . esc_attr( $this->shortcode . '_title' ) . '">' . do_shortcode('[bt_bb_headline headline="' . esc_attr( $title ) . '" html_tag="'. esc_attr( $html_tag ) .'" size="medium" ignore_fe_editor="true"]' ) . '</div>';

			// BUTTON
			if ( $button_text != '' )	$output .= '<div class="' . esc_attr( $this->shortcode . '_button' ) . '">' . do_shortcode('[bt_bb_button text="' . esc_attr( $button_text ) . '"  icon="remixiconssystem_e92f" icon_position="left" size="normal" style="clean" url="" target="_self" ignore_fe_editor="true"]' ) . '</div>';

		$output .= '</div>';


		$output = apply_filters( 'bt_bb_general_output', $output, $atts );
		$output = apply_filters( $this->shortcode . '_output', $output, $atts );

		return $output;

	}

	function map_shortcode() {
		
		$color_scheme_arr = bt_bb_get_color_scheme_param_array();

		bt_bb_map( $this->shortcode, array( 'name' => esc_html__( 'Card Icon', 'campo' ), 'description' => esc_html__( 'Card with icon and text', 'campo' ), 'icon' => $this->prefix_backend . 'icon' . '_' . $this->shortcode,
			'params' => array(
				
				array( 'param_name' => 'icon', 'type' => 'iconpicker', 'heading' => esc_html__( 'Icon', 'campo' ), 'preview' => true ),
				array( 'param_name' => 'title', 'type' => 'textfield', 'heading' => esc_html__( 'Title', 'campo' ), 'preview' => true ),
				array( 'param_name' => 'button_text', 'type' => 'textfield', 'heading' => esc_html__( 'Button text', 'campo' ) ),
				array( 'param_name' => 'padding', 'type' => 'dropdown', 'heading' => esc_html__( 'Inner padding', 'campo' ), 'preview' => true, 'responsive_override' => true,
					'value' => array(
						esc_html__( 'No padding', 'campo' ) 	=> 'none',
						esc_html__( 'Normal', 'campo' ) 		=> 'normal',
						esc_html__( 'Double', 'campo' ) 		=> 'double',
						esc_html__( 'Text Indent', 'campo' ) 	=> 'text_indent',
						esc_html__( '5px', 'campo' ) 			=> '5px',
						esc_html__( '10px', 'campo' ) 		=> '10px',
						esc_html__( '15px', 'campo' ) 		=> '15px',
						esc_html__( '20px', 'campo' ) 		=> '20px',
						esc_html__( '25px', 'campo' ) 		=> '25px',
						esc_html__( '30px', 'campo' ) 		=> '30px',
						esc_html__( '35px', 'campo' ) 		=> '35px',
						esc_html__( '40px', 'campo' ) 		=> '40px',
						esc_html__( '45px', 'campo' ) 		=> '45px',
						esc_html__( '50px', 'campo' ) 		=> '50px',
						esc_html__( '55px', 'campo' ) 		=> '55px',
						esc_html__( '60px', 'campo' ) 		=> '60px',
						esc_html__( '65px', 'campo' ) 		=> '65px',
						esc_html__( '70px', 'campo' ) 		=> '70px',
						esc_html__( '75px', 'campo' ) 		=> '75px',
						esc_html__( '80px', 'campo' ) 		=> '80px',
						esc_html__( '85px', 'campo' ) 		=> '85px',
						esc_html__( '90px', 'campo' ) 		=> '90px',
						esc_html__( '95px', 'campo' ) 		=> '95px',
						esc_html__( '100px', 'campo' ) 		=> '100px'
					)
				),
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
				
				
				array( 'param_name' => 'background_color', 'preview' => true, 'type' => 'colorpicker', 'heading' => esc_html__( 'Background color', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ) ),
				array( 'param_name' => 'color_scheme', 'type' => 'dropdown', 'heading' => esc_html__( 'Color scheme', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ), 'value' => $color_scheme_arr, 'preview' => true ),
				array( 'param_name' => 'hover_color_scheme', 'type' => 'dropdown', 'heading' => esc_html__( 'Hover color scheme', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ), 'value' => $color_scheme_arr, 'preview' => true ),
				array( 'param_name' => 'shape', 'type' => 'dropdown', 'heading' => esc_html__( 'Shape', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ),
					'value' => array(
						esc_html__( 'Square', 'campo' ) 			=> '',
						esc_html__( 'Soft Rounded', 'campo' ) 	=> 'soft_rounded',
						esc_html__( 'Hard Rounded', 'campo' ) 	=> 'hard_rounded'
					)
				),
				array( 'param_name' => 'shadow', 'type' => 'dropdown', 'heading' => esc_html__( 'Shadow', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ),
					'value' => array(
						esc_html__( 'Hide', 'campo' ) 			=> '',
						esc_html__( 'Show', 'campo' ) 			=> 'show',
						esc_html__( 'Show on hover', 'campo' ) 	=> 'on_hover'
					)
				),
				array( 'param_name' => 'blur', 'type' => 'dropdown', 'default' => '', 'group' => esc_html__( 'Design', 'campo' ), 'heading' => esc_html__( 'Background blur', 'campo' ),
					'value' => array(
						esc_html__( 'No', 'campo' ) 						=> '',
						esc_html__( 'Yes', 'campo' )						=> 'show'
					)
				),
				array( 'param_name' => 'gradient', 'type' => 'dropdown', 'default' => '', 'group' => esc_html__( 'Design', 'campo' ), 'heading' => esc_html__( 'Background gradient', 'campo' ),
					'value' => array(
						esc_html__( 'No', 'campo' ) 						=> '',
						esc_html__( 'Light gradient', 'campo' )			=> 'light',
						esc_html__( 'Dark gradient', 'campo' )			=> 'dark',
						esc_html__( 'Accent gradient', 'campo' )			=> 'accent',
						esc_html__( 'Alternate gradient', 'campo' )		=> 'alternate'
					)
				),

				array( 'param_name' => 'icon_color', 'type' => 'dropdown', 'heading' => esc_html__( 'Icon color', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ),
					'value' => array(
						esc_html__( 'Inherit', 'campo' ) 					=> '',
						esc_html__( 'Accent color', 'campo' ) 			=> 'accent',
						esc_html__( 'Alternate color', 'campo' ) 			=> 'alternate',
						esc_html__( 'Light color', 'campo' ) 				=> 'light',
						esc_html__( 'Dark color', 'campo' ) 				=> 'dark'
					)
				),
				array( 'param_name' => 'icon_size', 'type' => 'dropdown', 'default' => '', 'group' => esc_html__( 'Design', 'campo' ), 'default' => 'huge', 'heading' => esc_html__( 'Icon size', 'campo' ),
					'value' => array(
						esc_html__( 'Extra small', 'campo' ) 		=> 'xsmall',
						esc_html__( 'Small', 'campo' ) 			=> 'small',
						esc_html__( 'Normal', 'campo' ) 			=> 'normal',
						esc_html__( 'Large', 'campo' ) 			=> 'large',
						esc_html__( 'Extra large', 'campo' ) 		=> 'xlarge',
						esc_html__( 'Huge', 'campo' ) 			=> 'huge',
						esc_html__( 'Extra huge', 'campo' ) 		=> 'xhuge'
					)
				),
				array( 'param_name' => 'button_icon_color', 'type' => 'dropdown', 'heading' => esc_html__( 'Button icon color', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ),
					'value' => array(
						esc_html__( 'Inherit', 'campo' ) 					=> '',
						esc_html__( 'Accent color', 'campo' ) 			=> 'accent',
						esc_html__( 'Alternate color', 'campo' ) 			=> 'alternate',
						esc_html__( 'Light color', 'campo' ) 				=> 'light',
						esc_html__( 'Dark color', 'campo' ) 				=> 'dark'
					)
				),
				array( 'param_name' => 'border', 'default' => '', 'type' => 'dropdown', 'heading' => esc_html__( 'Border', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ),
					'value' => array(
						esc_html__( 'Yes', 'campo' ) 			=> '',
						esc_html__( 'No', 'campo' ) 			=> 'hide'
					)
				),
				array( 'param_name' => 'border_color', 'type' => 'dropdown', 'heading' => esc_html__( 'Border color', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ),
					'value' => array(
						esc_html__( 'Gray color', 'campo' ) 			=> 'gray',
						esc_html__( 'Very light gray color', 'campo' ) => 'very_light_gray',
						esc_html__( 'Accent color', 'campo' ) 		=> 'accent',
						esc_html__( 'Alternate color', 'campo' ) 		=> 'alternate',
						esc_html__( 'Light color', 'campo' ) 			=> 'light',
						esc_html__( 'Dark color', 'campo' ) 			=> 'dark'
					)
				),
			) )
		);
	}
}