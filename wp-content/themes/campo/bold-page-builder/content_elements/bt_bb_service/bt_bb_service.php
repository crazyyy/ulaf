<?php

class bt_bb_service extends BT_BB_Element {

	function handle_shortcode( $atts, $content ) {
		extract( shortcode_atts( apply_filters( 'bt_bb_extract_atts_' . $this->shortcode, array(
			'ai_prompt'    => '',
			'icon'         => '',
			'title'        => '',
			'text'         => '',
			'url'          => '',
			'target'       => '',
			'color_scheme' => '',
			'style'        => '',
			'size'         => '',
			'shape'        => '',
			'align'        	=> '',
			'title_size'   	=> '',
			'align_content' => ''
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
		
		$color_scheme_id = NULL;
		if ( is_numeric ( $color_scheme ) ) {
			$color_scheme_id = $color_scheme;
		} else if ( $color_scheme != '' ) {
			$color_scheme_id = bt_bb_get_color_scheme_id( $color_scheme );
		}
		$color_scheme_colors = bt_bb_get_color_scheme_colors_by_id( $color_scheme_id - 1 );
		if ( $color_scheme_colors ) $el_style .= '; --primary-color:' . $color_scheme_colors[0] . '; --secondary-color:' . $color_scheme_colors[1] . ';';
		if ( $color_scheme != '' ) $class[] = $this->prefix . 'color_scheme_' .  $color_scheme_id;

		$style_attr = '';
		$el_style = apply_filters( $this->shortcode . '_style', $el_style, $atts );
		if ( $el_style != '' ) {
			$style_attr = ' ' . 'style="' . esc_attr( $el_style ) . '"';
		}		

		if ( $style != '' ) {
			$class[] = $this->prefix . 'style' . '_' . $style;
		}

		$this->responsive_data_override_class(
			$class, $data_override_class,
			array(
				'prefix' => $this->prefix,
				'param' => 'size',
				'value' => $size
			)
		);

		if ( $shape != '' ) {
			$class[] = $this->prefix . 'shape' . '_' . $shape;
		}

		if ( $text == '' ) {
			$class[] = 'btNoText';
		}

		if ( $title == '' ) {
			$class[] = 'btNoTitle';
		}

		$this->responsive_data_override_class(
			$class, $data_override_class,
			array(
				'prefix' => $this->prefix,
				'param' => 'title_size',
				'value' => $title_size
			)
		);
		
		if ( $target != '' ) {
			$class[] = $this->prefix . 'target' . $target;
		}
		
		if ( $target == '_lightbox' ) {
			$class[] = 'bt_bb_use_lightbox';
			$target = '_blank';
		}

		$this->responsive_data_override_class(
			$class, $data_override_class,
			array(
				'prefix' => $this->prefix,
				'param' => 'align',
				'value' => $align
			)
		);

		$this->responsive_data_override_class(
			$class, $data_override_class,
			array(
				'prefix' => $this->prefix,
				'param' => 'align_content',
				'value' => $align_content
			)
		);
		
		$link = bt_bb_get_url( $url );

		$icon_title = wp_strip_all_tags($title);
		
		$icon = bt_bb_icon::get_html( $icon, '', $link, $icon_title, $target );

		if ( $link != '' ) {
			if ( $title != '' ) $title = '<a href="' . esc_url( $link ) . '" target="' . esc_attr( $target ) . '">' . $title . '</a>';
		}
		
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

		$output = $icon;

		$output .= '<div class="' . esc_attr( $this->shortcode ) . '_content">';
			if ( $title != '' ) $output .= '<div class="' . esc_attr( $this->shortcode ) . '_content_title">' . nl2br( $title ) . '</div>';
			if ( $text != '' ) $output .= '<div class="' . esc_attr( $this->shortcode ) . '_content_text">' . nl2br( $text ) . '</div>';
		$output .= '</div>';

		$output = '<div' . $id_attr . ' class="' . esc_attr( implode( ' ', $class ) ) . '"' . $style_attr . ' data-bt-override-class="' . htmlspecialchars( json_encode( $data_override_class, JSON_FORCE_OBJECT ), ENT_QUOTES, 'UTF-8' ) . '">' . $output . '</div>';
		
		$output = apply_filters( 'bt_bb_general_output', $output, $atts );
		$output = apply_filters( $this->shortcode . '_output', $output, $atts );

		return $output;

	}

	function map_shortcode() {

		$color_scheme_arr = bt_bb_get_color_scheme_param_array();

		bt_bb_map( $this->shortcode, array( 'name' => esc_html__( 'Service', 'campo' ), 'description' => esc_html__( 'Icon with text (and AI help)', 'campo' ), 'icon' => $this->prefix_backend . 'icon' . '_' . $this->shortcode,
			'params' => array(
				array(
					'param_name' => 'ai_prompt',
					'type' => 'ai_prompt',
					'target' =>
						array(
							'title' => array( 'alias' => 'title', 'title' => esc_html__( 'Title', 'bold-builder' ) ),
							'text' => array( 'alias' => 'text', 'title' => esc_html__( 'Text', 'bold-builder' ) ),
						),
					'system_prompt' => 'You are a copywriter and your GOAL is to help users generate website content. Based on the user prompt generate title and text for the website page.',
				),
				array( 'param_name' => 'icon', 'type' => 'iconpicker', 'heading' => esc_html__( 'Icon', 'campo' ), 'preview' => true ),
				array( 'param_name' => 'title', 'type' => 'textarea', 'heading' => esc_html__( 'Title', 'campo' ), 'preview' => true ),
				array( 'param_name' => 'text', 'type' => 'textarea', 'heading' => esc_html__( 'Text', 'campo' ) ),
				array( 'param_name' => 'align', 'type' => 'dropdown', 'heading' => esc_html__( 'Icon position', 'campo' ), 'responsive_override' => true,
					'value' => array(
						esc_html__( 'Inherit', 'campo' ) 		=> 'inherit',
						esc_html__( 'Left', 'campo' ) 		=> 'left',
						esc_html__( 'Center', 'campo' ) 		=> 'center',
						esc_html__( 'Right', 'campo' ) 		=> 'right'
					)
				),
				array( 'param_name' => 'url', 'type' => 'link', 'heading' => esc_html__( 'URL', 'campo' ), 'group' => esc_html__( 'URL', 'campo' ), 'description' => esc_html__( 'Enter full or local URL (e.g. https://www.bold-themes.com or /pages/about-us) or post slug (e.g. about-us) or search for existing content.', 'campo' ) ),
				array( 'param_name' => 'target', 'type' => 'dropdown', 'heading' => esc_html__( 'Target', 'campo' ), 'group' => esc_html__( 'URL', 'campo' ),
					'value' => array(
						esc_html__( 'Self (open in same tab)', 'campo' ) 			=> '_self',
						esc_html__( 'Blank (open in new tab)', 'campo' ) 			=> '_blank',
						esc_html__( 'Lightbox (open in new layer)', 'campo' ) 	=> '_lightbox',
					)
				),
				array( 'param_name' => 'size', 'type' => 'dropdown', 'heading' => esc_html__( 'Icon size', 'campo' ), 'responsive_override' => true, 'preview' => true, 'group' => esc_html__( 'Design', 'campo' ),
					'value' => array(
						esc_html__( 'Extra small', 'campo' ) 		=> 'xsmall',
						esc_html__( 'Small', 'campo' ) 			=> 'small',
						esc_html__( 'Normal', 'campo' ) 			=> 'normal',
						esc_html__( 'Large', 'campo' ) 			=> 'large',
						esc_html__( 'Extra large', 'campo' ) 		=> 'xlarge',
						esc_html__( 'Huge', 'campo' ) 			=> 'huge'
					)
				),
				array( 'param_name' => 'title_size', 'type' => 'dropdown', 'heading' => esc_html__( 'Title size', 'campo' ), 'responsive_override' => true, 'preview' => true, 'group' => esc_html__( 'Design', 'campo' ),
					'value' => array(
						esc_html__( 'Extra small', 'campo' ) 		=> 'xsmall',
						esc_html__( 'Small', 'campo' ) 			=> 'small',
						esc_html__( 'Normal', 'campo' ) 			=> 'normal',
						esc_html__( 'Large', 'campo' ) 			=> 'large',
						esc_html__( 'Extra large', 'campo' ) 		=> 'xlarge',
						esc_html__( 'Huge', 'campo' ) 			=> 'huge'
					)
				),
				array( 'param_name' => 'color_scheme', 'type' => 'dropdown', 'heading' => esc_html__( 'Color scheme', 'campo' ), 'description' => esc_html__( 'Define color schemes in Bold Builder settings or define accent and alternate colors in theme customizer (if avaliable)', 'campo' ), 'value' => $color_scheme_arr, 'preview' => true, 'group' => esc_html__( 'Design', 'campo' ) ),
				array( 'param_name' => 'style', 'type' => 'dropdown', 'heading' => esc_html__( 'Icon style', 'campo' ), 'preview' => true, 'group' => esc_html__( 'Design', 'campo' ),
					'value' => array(
						esc_html__( 'Outline', 'campo' )	 		=> 'outline',
						esc_html__( 'Filled', 'campo' ) 			=> 'filled',
						esc_html__( 'Borderless', 'campo' ) 		=> 'borderless'
					)
				),
				array( 'param_name' => 'shape', 'type' => 'dropdown', 'heading' => esc_html__( 'Icon shape', 'campo' ), 'group' => esc_html__( 'Design', 'campo' ),
					'value' => array(
						esc_html__( 'Circle', 'campo' ) 			=> 'circle',
						esc_html__( 'Square', 'campo' ) 			=> 'square',
						esc_html__( 'Rounded Square', 'campo' ) 	=> 'round'
					)
				),
				array( 'param_name' => 'align_content', 'type' => 'dropdown', 'default' => '', 'heading' => esc_html__( 'Content alignment', 'campo' ), 'responsive_override' => true, 'group' => esc_html__( 'Design', 'campo' ),
					'value' => array(
						esc_html__( 'Top', 'campo' ) 				=> 'top',
						esc_html__( 'Center', 'campo' ) 			=> 'center',
						esc_html__( 'Bottom', 'campo' ) 			=> 'bottom'
					)
				),
			)
		) );
	}
}