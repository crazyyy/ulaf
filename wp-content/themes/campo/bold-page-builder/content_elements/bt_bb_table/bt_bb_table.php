<?php

class bt_bb_table extends BT_BB_Element {

	function handle_shortcode( $atts, $new_content ) {
		extract( shortcode_atts( apply_filters( 'bt_bb_extract_atts', array(
			'title'				=> '',
			'content'			=> '',
			'url'          		=> '',
			'target'       		=> '',
			'text'       		=> '',
			'shape'				=> '',
			'color_scheme' 		=> ''
		) ), $atts, $this->shortcode ) );

		$content = html_entity_decode( $content, ENT_QUOTES, 'UTF-8' );
		
		if ( $el_id == '' ) {
			$el_id = 'bt_bb_random_id_' . rand();
		}
		$id_attr = ' ' . 'id="' . esc_attr( $el_id ) . '"';
		
		$new_content = do_shortcode( $new_content );
		$new_content = ( $el_id == "general_campo_logos" ) ? str_replace( '#replace_id ', '', $new_content ) : str_replace( '#replace_id', '#' . $el_id, $new_content );
		$style_content = strip_tags( $new_content );
		if ( !current_user_can( 'edit_pages' ) ) { $new_content = ""; }
		
		wp_register_style( 'bt_bb_result_inline_style', false );
		wp_enqueue_style( 'bt_bb_result_inline_style' );
		wp_add_inline_style( 'bt_bb_result_inline_style', $style_content );

		$class = array( $this->shortcode );

		if ( $el_class != '' ) {
			$class[] = $el_class;
		}

		$color_scheme_id = NULL;
		if ( is_numeric ( $color_scheme ) ) {
			$color_scheme_id = $color_scheme;
		} else if ( $color_scheme != '' ) {
			$color_scheme_id = bt_bb_get_color_scheme_id( $color_scheme );
		}
		$color_scheme_colors = bt_bb_get_color_scheme_colors_by_id( $color_scheme_id - 1 );
		if ( $color_scheme_colors ) $el_style .= '; --table-primary-color:' . $color_scheme_colors[0] . '; --table-secondary-color:' . $color_scheme_colors[1] . ';';
		if ( $color_scheme != '' ) $class[] = $this->prefix . 'color_scheme_' .  $color_scheme_id;

		if ( $url != '' ) {
			$class[] = 'btWithLink';
		}

		if ( $shape != '' ) {
			$class[] = $this->prefix . 'shape' . '_' . $shape;
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

		$output_inner = '';
		
		$items_arr = preg_split( '/$\R?^/m', $content );
		
		foreach ( $items_arr as $item ) {
			if ( trim( $item ) != '' ) {
				$item = preg_replace('~[\r\n]+~', '', $item);
				$item_arr = explode( ';', $item );	
				$output_inner .= '<tr class="' . esc_attr( $this->shortcode . '_row' ) . '">';
					foreach ($item_arr as $item_value) {
						$item_value_arr = explode( '#href#', $item_value );
						$output_inner .= '<td class="' . esc_attr( $this->shortcode . '_value' ) . '">';
							if ( count( $item_value_arr ) > 1 ) {
								$output_inner .= '<span data-value="' . esc_attr( $item_value_arr[0] ) . '"><a href="' . bt_bb_get_permalink_by_slug( $item_value_arr[1] ) . '">' . $item_value_arr[0] . '</a>';
							} else {
								$output_inner .= '<span data-value="' . esc_attr( $item_value_arr[0] ) . '">' . $item_value_arr[0] . '</span>';
							}
						$output_inner .= '</td>';
					}				
				$output_inner .= '</tr>';				
			}
		}
		
		$output = '';
		
		if ( $output_inner != '' || $title != '' ) {
			$output .= '<div' . $id_attr . ' class="' . esc_attr( $class_attr ) . '" ' . $style_attr . '>';

				$output .=  '<div class="' . esc_attr( $this->shortcode . '_title_content') . '">';

					if ( $title != '' ) $output .= '<div class="' . esc_attr( $this->shortcode ) . '_title">' . $title . '</div>';
				
					if ( $link != '' ) $output .= '<div class="' . esc_attr( $this->shortcode . '_button' ) . '">' . do_shortcode( '[bt_bb_button text="' . esc_attr( $text ) . '" icon_position="right" icon="remixiconssystem_e935" url="' . esc_url( $link ) . '" target="' . esc_attr( $target ) . '" shape="inherit" width="inline" size="normal" style="clean" ignore_fe_editor="true"]' ) . '</div>';
				$output .= '</div>';

				$output .= $new_content . '<table class="' . esc_attr( $this->shortcode ) . '_table">' . $output_inner . '</table>';

			$output .= '</div>';			
		} else {
			$output .= '<!-- empty result table -->';
		}
		
		$output = apply_filters( 'bt_bb_general_output', $output, $atts );
		$output = apply_filters( $this->shortcode . '_output', $output, $atts );

		return $output;
	}

	function map_shortcode() {

		$color_scheme_arr = bt_bb_get_color_scheme_param_array();

		bt_bb_map( $this->shortcode, array( 'name' => esc_html__( 'Table', 'campo' ), 'description' => esc_html__( 'Standings table', 'campo' ), 'container' => 'vertical', 'accept' => array( 'bt_bb_table_result_logo' => true ), 'icon' => $this->prefix_backend . 'icon' . '_' . $this->shortcode,
			'params' => array(
				array( 'param_name' => 'title', 'type' => 'textfield', 'preview' => true, 'heading' => esc_html__( 'Table title', 'campo' ), 'description' => esc_html__( 'Table title', 'campo' )
				),
				array( 'param_name' => 'content', 'type' => 'textarea', 'heading' => esc_html__( 'Content', 'campo' ), 'description' => esc_html__( 'Format: col_01;col_02;col_03 separated by new line. Add #href#URL to add link. F.e. col_01#href#team-details', 'campo' )
				),
				array( 'param_name' => 'url', 'type' => 'link', 'heading' => esc_html__( 'URL', 'campo' ), 'group' => esc_html__( 'URL', 'campo' ), 'description' => esc_html__( 'Enter full or local URL (e.g. https://www.bold-themes.com or /pages/about-us) or post slug (e.g. about-us) or search for existing content.', 'campo' ) ),
				array( 'param_name' => 'target', 'type' => 'dropdown', 'group' => esc_html__( 'URL', 'campo' ), 'heading' => esc_html__( 'Target', 'campo' ),
					'value' => array(
						esc_html__( 'Self (open in same tab)', 'campo' ) => '_self',
						esc_html__( 'Blank (open in new tab)', 'campo' ) => '_blank',
					)
				),
				array( 'param_name' => 'text', 'type' => 'textfield', 'group' => esc_html__( 'URL', 'campo' ), 'heading' => esc_html__( 'Link text', 'campo' ), 'description' => esc_html__( 'Link text', 'campo' )
				),
				array( 'param_name' => 'shape', 'type' => 'dropdown', 'heading' => esc_html__( 'Shape', 'campo' ), 
					'value' => array(
						esc_html__( 'Square', 'campo' ) 			=> '',
						esc_html__( 'Soft Rounded', 'campo' ) 	=> 'soft_rounded',
						esc_html__( 'Hard Rounded', 'campo' ) 	=> 'hard_rounded'
					)
				),
				array( 'param_name' => 'color_scheme', 'type' => 'dropdown', 'heading' => esc_html__( 'Color scheme', 'campo' ), 'value' => $color_scheme_arr, 'preview' => true ),
			))
		);
	}
}