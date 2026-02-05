<?php

class bt_bb_call_to_action extends BT_BB_Element {

	function handle_shortcode( $atts, $content ) {
		extract( shortcode_atts( apply_filters( 'bt_bb_extract_atts_' . $this->shortcode, array(
			'supertitle'   			=> '',
			'title'       	 		=> '',
			'icon'         			=> '',
			'color_scheme' 			=> '',
			'url'          			=> '',
			'target'       			=> '',
			'border'       			=> ''

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

		$this->responsive_data_override_class(
			$class, $data_override_class,
			array(
				'prefix' => $this->prefix,
				'param' => 'border',
				'value' => $border
			)
		);

		$color_scheme_id = NULL;
		if ( is_numeric ( $color_scheme ) ) {
			$color_scheme_id = $color_scheme;
		} else if ( $color_scheme != '' ) {
			$color_scheme_id = bt_bb_get_color_scheme_id( $color_scheme );
		}
		$color_scheme_colors = bt_bb_get_color_scheme_colors_by_id( $color_scheme_id - 1 );
		if ( $color_scheme_colors ) $el_style .= '; --call-to-action-primary-color:' . $color_scheme_colors[0] . '; --call-to-action-secondary-color:' . $color_scheme_colors[1] . ';';
		if ( $color_scheme != '' ) $class[] = $this->prefix . 'color_scheme_' .  $color_scheme_id;


		$link = bt_bb_get_permalink_by_slug( $url );

		$class = apply_filters( $this->shortcode . '_class', $class, $atts );
		
		if ( $el_class != '' ) {
			$class_attr = $class_attr . ' ' . $el_class;
		}
	
		$style_attr = '';
		if ( $el_style != '' ) {
			$style_attr = ' ' . 'style="' . esc_attr( $el_style ) . '"';
		}

		$output = '<div' . $id_attr . ' class="' . esc_attr( implode( ' ', $class ) ) . '" ' . $style_attr . ' data-bt-override-class="' . htmlspecialchars( json_encode( $data_override_class, JSON_FORCE_OBJECT ), ENT_QUOTES, 'UTF-8' ) . '">';

			if ( $link != '' ) {
				$target_attr = ' target="_self" ';
				if ( $target != '' ) {
					$target_attr = ' ' . 'target="' . esc_attr( $target ) . '"';
				}
				$output .= '<a href="' . esc_url( $link ) . '" ' . $target_attr . ' class="btLink"></a>';
			}
			
			if ( $supertitle != '' ) $output .= '<div class="' . esc_attr( $this->shortcode ) . '_supertitle">' . $supertitle . '</div>';

			$output .=  '<div class="' . esc_attr( $this->shortcode . '_content') . '">';

				if ( $icon != '' ) $output .= '<div class="' . esc_attr( $this->shortcode . '_icon' ) . '">' . do_shortcode( '[bt_bb_icon icon="' . esc_attr( $icon ) . '" size="large" style="borderless" ignore_fe_editor="true"]' ) . '</div>';

				if ( $title != '' ) $output .= '<div class="' . esc_attr( $this->shortcode ) . '_content_title">' . $title . '</div>';

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

		$color_scheme_arr = bt_bb_get_color_scheme_param_array();

		bt_bb_map( $this->shortcode, array( 'name' => esc_html__( 'Call To Action', 'campo' ), 'description' => esc_html__( 'Call To Action with icon and title', 'campo' ), 'icon' => $this->prefix_backend . 'icon' . '_' . $this->shortcode, 'highlight' => true,
			'params' => array(
				array( 'param_name' => 'supertitle', 'type' => 'textfield', 'heading' => esc_html__( 'Supertitle', 'campo' ) ),
				array( 'param_name' => 'title', 'preview' => true, 'type' => 'textfield', 'heading' => esc_html__( 'Title', 'campo' ) ),
				array( 'param_name' => 'icon', 'type' => 'iconpicker', 'heading' => esc_html__( 'Icon', 'campo' ), 'value' => $icon_arr, 'preview' => true ),
				array( 'param_name' => 'border', 'type' => 'dropdown', 'default' => '', 'heading' => esc_html__( 'Border on left', 'campo' ), 'responsive_override' => true,
					'value' => array(
						esc_html__( 'No', 'campo' ) 						=> 'hide',
						esc_html__( 'Yes', 'campo' )						=> 'show'
					)
				),
				array( 'param_name' => 'color_scheme', 'type' => 'dropdown', 'heading' => esc_html__( 'Color scheme', 'campo' ), 'value' => $color_scheme_arr, 'preview' => true ),
				
				array( 'param_name' => 'url', 'type' => 'link', 'heading' => esc_html__( 'URL', 'campo' ), 'group' => esc_html__( 'URL', 'campo' ), 'description' => esc_html__( 'Enter full or local URL (e.g. https://www.bold-themes.com or /pages/about-us) or post slug (e.g. about-us) or search for existing content.', 'campo' ) ),
				array( 'param_name' => 'target', 'type' => 'dropdown', 'group' => esc_html__( 'URL', 'campo' ), 'heading' => esc_html__( 'Target', 'campo' ),
					'value' => array(
						esc_html__( 'Self (open in same tab)', 'campo' ) => '_self',
						esc_html__( 'Blank (open in new tab)', 'campo' ) => '_blank',
					)
				),
			))
		);
	}
}